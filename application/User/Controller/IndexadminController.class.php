<?php

/**
 * 会员
 */
namespace User\Controller;
use Common\Controller\AdminbaseController;
use function PHPSTORM_META\elementType;
use Admin\Model\CommonModel;

class IndexadminController extends AdminbaseController {

    private $user_status_list = array(
        '0' => array(
            'name' => '禁用',
            'color' => '#f00',
        ),
        '1' => array(
            'name' => '正常',
            'color' => '#090',
        ),
        '2' => array(
            'name' => '未验证',
            'color' => '#999',
        ),
    );

	protected $users_model;

	public function _initialize() {
		parent::_initialize();
		$this->users_model = D("Common/Users");
	}

    public function index(){
        $param = I('param.');
        $conifg = getConfigPub(getTenantIds());

        $map=array();
        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']){
                $map['tenant_id'] = intval($param['tenant_id']);
            }
        }else{
            //租户id条件
            $map['tenant_id'] = intval(getTenantIds());
        }

        $param['tenant_id'] = isset($param['tenant_id']) ? $param['tenant_id'] : '';;

        $map['user_type'] = $conifg['is_use_visitor'] == '0' ? ['in',[2,3,5,6,7]] : ['in',[2,3,4,5,6,7]];

        $param['vip_grade'] = isset($param['vip_grade']) ? $param['vip_grade'] : '';

        if(isset($param['vip_grade']) && $param['vip_grade']!=''){
            $map['userlevel'] = $param['vip_grade'];
        }

        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : '';      
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['ctime'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = '';
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['ctime'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = '';
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['ctime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $param['start_time'] = '';
            $param['end_time'] = '';
        }

        if(isset($param['iszombie']) && $param['iszombie']!=''){
            $map['iszombie']=$param['iszombie'];
        }
        if(isset($param['user_type']) && $param['user_type'] != ''){
            $map['user_type']=$param['user_type'];
        }
        if($map['user_type'] == '4' && $conifg['is_use_visitor'] == '0'){
            $map[] = ['user_type'=> ['in', [2,3,5,6,7]]];
        }

        if(isset($param['pid']) && $param['pid']!=''){
            $map['pid']=$param['pid'];
        }
        if(isset($param['isban']) && $param['isban']!=''){
            $map['user_status']=$param['isban'];
        }
        if(isset($param['issuper']) && $param['issuper']!=''){
            $map['issuper']=$param['issuper'];
        }
        if(isset($param['source']) && $param['source']!=''){
            $map['source']=$param['source'];
        }
        if(isset($param['ishot']) && $param['ishot']!=''){
            $map['ishot']=$param['ishot'];
        }
        if(isset($param['iszombiep']) && $param['iszombiep']!=''){
            $map['iszombiep']=$param['iszombiep'];
        }
        if(isset($param['id']) && $param['id']!=''){
            $map['id']=['in',explode('|',trim($param['id']))];
        }
        if(isset($param['user_login']) && $param['user_login']!=''){
            $map['user_login']=$param['user_login'];
        }
        if(isset($param['game_user_id']) && $param['game_user_id']!=''){
            $map['game_user_id']=$param['game_user_id'];
        }
        if(isset($param['mobile']) && $param['mobile']!=''){
            $map['mobile']=$param['mobile'];
        }
        $vip_grade = M("vip_longgrade")->where("status=1")->field("vip_grade,name")->select();
        $Agent_code=M('users_agent_code');
        $users_model=$this->users_model;
        $count=$users_model->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $users_model
            ->field('id,user_login,user_pass,user_nicename,avatar,last_login_ip,last_login_time,create_time,ctime,user_status,user_type,
                     coin,nowithdrawable_coin,consumption,votes,votestotal,isrecommend,isrecord,iszombie,iszombiep,issuper,ishot,source,
                     tenant_id,isshare,issendred,isprivatemsg,game_user_id,game_tenant_id,integral,watch_num,watch_time,withdrawable_money,
                     isshutup,isforbidlive,isjurisdiction,is_allow_post,is_allow_comment,is_allow_seeking_slice,is_allow_push_slice,version,client,watchtime,follows,fans,
                     recharge_total,is_certification,certification_name,pid,vip_margin,yeb_balance,upload_video_profit_status,grab_red_packet_status,rebate_status,userlevel')
            ->where($map)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
//        echo $users_model->getLastSql()."<br>";
        $uid = array();
        $user_vip_array = array();
        $client_list = client_list();
        $user_type_list = user_type_list();
        $user_status_list = $this->user_status_list;
        $rechargeLevel = M("rechargeLevel")->select();
        foreach($lists as $k=>$v){
            $lists[$k]['avatar'] = get_upload_path($v['avatar']); // 头像
            $lists[$k]['avatar_thumb'] = get_upload_path($v['avatar_thumb']); // 头像缩略图
            $uid[] = $v['id'];
            $tenantInfo=getTenantInfo($v['tenant_id']);
            if(!empty($tenantInfo)){
                $v['tenant_name']=$tenantInfo['name'];
            }
            if(in_array($v['user_type'],[2,3,4,5,6])){
                $user_vip_array[] = $v['id'];
            }else{
            }
            $user_vip_info = getUserVipInfo($v['id'], $v['tenant_id']);

            $config = getConfigPri($v['tenant_id']);
            $v['chatserver'] = $config['chatserver'];
            $v['socket_type'] = $config['socket_type'];
            $v['client'] = isset($client_list[$v['client']]) ? $client_list[$v['client']] : '-';
            $v['watchtime']  = time2string($v['watchtime']);
            $lists[$k]=$v;
            $rechargeLevelName = [];
            foreach ($rechargeLevel as $rechargeLevelvalue){
                if ($v['recharge_total']>= $rechargeLevelvalue['min_amount'] && $v['recharge_total']<= $rechargeLevelvalue['max_amount'] && $v['tenant_id'] == $rechargeLevelvalue['tenant_id']){
                    $rechargeLevelName[] = $rechargeLevelvalue['name'];
                }
            }

            $lists[$k]['user_level_title'] = implode('和',$rechargeLevelName);
            $lists[$k]['user_type_name'] = '<span style="color: '.$user_type_list[$v['user_type']]['color'].';">'.$user_type_list[$v['user_type']]['name'].'</span>';
            $lists[$k]['coin'] = floatval($v['coin']);
            $lists[$k]['nowithdrawable_coin'] = floatval($v['nowithdrawable_coin']);
            $lists[$k]['consumption'] = floatval($v['consumption']);
            $lists[$k]['votestotal'] = floatval($v['votestotal']);
            $lists[$k]['integral'] = floatval($v['integral']);
            $lists[$k]['recharge_total'] = floatval($v['recharge_total']);
            $lists[$k]['user_status_name'] = '<span style="color: '.$user_status_list[$v['user_status']]['color'].';">'.$user_status_list[$v['user_status']]['name'].'</span>';
            $lists[$k]['user_vip_level_name'] = $user_vip_info ? getVipGradeList($v['tenant_id'])[$user_vip_info['grade']]['name'] : '';
            $lists[$k]['vip_margin'] = floatval($v['vip_margin']);
            $lists[$k]['yeb_balance'] = floatval($v['yeb_balance']);
            //$lists[$k]['userlevel_name'] = "普通会员";
            if(!empty($vip_grade)){
                foreach($vip_grade as $grade){
                    if($lists[$k]['userlevel']==$grade['vip_grade']){
                        $lists[$k]['userlevel_name'] = $grade['name'];
                    }
                }
            }
        }
        if(!empty($uid)){
            $codeArray  = $Agent_code->where(array('uid'=>array('in',$uid)))->field('uid,code')->select();
            $codeInfoArray = array();
            foreach ($codeArray  as $codeInfo){
                $codeInfoArray[$codeInfo['uid']] = $codeInfo['code'];
            }

            $user_vip_array_list = array();
            if ($user_vip_array){ // 用户当前等级
                $user_vip = M("users_vip")->where(array('uid'=>array('in',$user_vip_array),'endtime'=>array('gt',time())))->field('uid,grade,count(uid)')->group('uid,grade')->order('grade asc')->select();
                $user_vip_array_list = array_column($user_vip,null,'uid');
            }

            $vip_grade_list = M("vip_grade")->select(); // 获取全部等级
            $vip_grade_list = array_column($vip_grade_list,null,'vip_grade');
            $jurisdiction_list = M('users_jurisdiction')->field('id,grade,watch_number,watch_duration,vip_grade_id')->select(); // 对应等级权限
            $jurisdiction = array_column($jurisdiction_list,null,'vip_grade_id');
            $shotLikeNumber  = M('video')->where(array('uid'=>array('in',$uid),'status'=>2))->group('uid')->field('uid,sum(likes) as likes_number')->select();

            if ($shotLikeNumber){
                $shotLikeNumberByUid = array_column($shotLikeNumber,null,'uid');
            }
            $longLikeNumber  = M('video_long')->where(array('uid'=>array('in',$uid),'status'=>2))->group('uid')->field('uid,sum(likes) as likes_number')->select();
            if ($longLikeNumber){
                $longLikeNumberByUid = array_column($longLikeNumber,null,'uid');
            }

            $users_agent_list = M('users_agent')->where(array('uid'=>array('in',$uid)))->field('uid,one_uid')->select();
            $users_agent_list = count($users_agent_list) > 0 ? array_column($users_agent_list, null, 'uid') : [];

            foreach($lists as $user_key=>$user_value){
                if(in_array($user_value['user_type'],[2,5,6,7])){ // 正式用户
                    if ($user_vip_array_list[$user_value['id']]){
                        $lists[$user_key]['watch_time'] = $user_value['watch_time']+  $jurisdiction[$vip_grade_list[$user_vip_array_list[$user_value['id']]['grade']]['id']]['watch_duration'];
                        $lists[$user_key]['watch_num'] = $user_value['watch_num'] +  $jurisdiction[$vip_grade_list[$user_vip_array_list[$user_value['id']]['grade']]['id']]['watch_number'];
                    }else{ // 默认vip 权限
                        $lists[$user_key]['watch_num']   = $jurisdiction[$vip_grade_list[1]['id']]['watch_number'] + $user_value['watch_num'];
                        $lists[$user_key]['watch_time']  =  $jurisdiction[$vip_grade_list[1]['id']]['watch_duration'] + $user_value['watch_time'];
                    }
                }else{ // 游客
                    $lists[$user_key]['watch_num'] = $jurisdiction[0]['watch_number'] + $user_value['watch_num'];
                    $lists[$user_key]['watch_time']  =  $jurisdiction[0]['watch_duration'] + $user_value['watch_time'];
                }
                $shotNumber =  isset($shotLikeNumberByUid[$user_value['id']])?$shotLikeNumberByUid[$user_value['id']]['likes_number']:0;
                $longNumber =  isset($shotLikeNumberByUid[$user_value['id']])?$longLikeNumberByUid[$user_value['id']]['likes_number']:0;
                $lists[$user_key]['likes'] = $shotNumber+ $longNumber;
                $lists[$user_key]['code'] = $codeInfoArray[$user_value['id']];
                if(!$user_value['pid']){
                    $lists[$user_key]['pid'] = isset($users_agent_list[$user_value['id']]) ? $users_agent_list[$user_value['id']]['one_uid'] : $user_value['pid'];
                }
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign("vip_grade",$vip_grade);
        $this->assign('lists', $lists);
    	$this->assign('param', $param);
    	$this->assign('count', $count);
    	$this->assign("page", $page->show("Admin"));
        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id', getRoleId());
        $this->assign('auth_access_json', json_encode(getAuthAccessList(getRoleId())));
    	$this->display(":index");
    }

    public function del(){
    	$id=intval($_GET['id']);
    	if ($id) {
            $userinfo=M("Users")->field("user_login")->where(array("id"=>$id))->find();
    		$rst = M("Users")->where(array("id"=>$id,"user_type"=>['in',[2,3,4,5,6,7]]))->delete();
    		if ($rst!==false) {
                    $action="删除会员：{$id} - {$userinfo['user_login']}";
                    setAdminLog($action);
					/* 删除认证 */
					M("users_auth")->where("uid='{$id}'")->delete();
                    /* 删除直播记录 */
					M("users_liverecord")->where("uid='{$id}'")->delete();
					/* 删除房间管理员 */
					M("users_livemanager")->where("uid='{$id}' or liveuid='{$id}'")->delete();
					/*  删除黑名单*/
					M("users_black")->where("uid='{$id}' or touid='{$id}'")->delete();
					/* 删除关注记录 */
					M("users_attention")->where("uid='{$id}' or touid='{$id}'")->delete();
					/* 删除僵尸 */
					M("users_zombie")->where("uid='{$id}'")->delete();
					/* 删除超管 */
					M("users_super")->where("uid='{$id}'")->delete();
					/* 删除会员 */
					M("users_vip")->where("uid='{$id}'")->delete();
					/* 删除分销关系 */
					M("users_agent")->where("uid='{$id}' or one_uid={$id}")->delete();
                    /* 删除分销邀请码 */
					M("users_agent_code")->where("uid='{$id}'")->delete();
					/* 删除坐骑 */
					M("users_car")->where("uid='{$id}'")->delete();
					/* 删除家族关系 */
					M("users_family")->where("uid='{$id}'")->delete();

                    /* 删除推送PUSHID */
					M("users_pushid")->where("uid='{$id}'")->delete();
                    /* 删除钱包账号 */
					M("users_cash_account")->where("uid='{$id}'")->delete();

                    /* 删除游戏下注记录 */
					M("users_gamerecord")->where("uid='{$id}'")->delete();

                    /* 删除自己的标签 */
					M("users_label")->where("touid='{$id}'")->delete();

					/* 家族长处理 */
					$isexist=M("family")->field("id")->where("uid={$id}")->find();
					if($isexist){
						$data=array(
							'state'=>3,
							'signout'=>2,
							'signout_istip'=>2,
						);
						M("users_family")->where("familyid={$isexist['id']}")->save($data);
						M("family_profit")->where("familyid={$isexist['id']}")->delete();
						M("family_profit")->where("id={$isexist['id']}")->delete();
					}
                /* 清除redis缓存 */
                delcache("userinfo_".$id,"token_".$id);
    			$this->success("会员删除成功！");
    		} else {
    			$this->error('会员删除失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    function ban(){
    	$id=intval($_GET['id']);
        if(!$id){
            $this->error('数据传入 失败！');
        }
        try{
            $rst = M("Users")->where(array("id"=>$id))->setField('user_status','0');
            if (!$rst) {
                throw new \Exception(json_encode([
                        'where'=>["id"=>$id],
                        'update_data'=>['user_status', '1']
                    ]).' $rst: '.json_encode($rst));
            }
        }catch (\Exception $e){
            setAdminLog("会员拉黑 失败 ".$id.' '.$e->getMessage());
            return  $this->error('会员拉黑 失败！');
        }

        $nowtime = time();
        $redis = connectionRedis();
        $time = $nowtime + 60 * 60 * 1;
        $live = M("users_live")->field("uid")->where("islive='1'")->select();
        foreach ($live as $k => $v) {
            $redis->hSet($v['uid'] . 'shutup', $id, $time);
        }

        setAdminLog("会员拉黑 成功 ".$id);
        $this->success("会员拉黑 成功！");
    }

    function cancelban(){
    	$id=intval($_GET['id']);
    	if(!$id){
            $this->error('数据传入 失败！');
        }

        try{
            $rst = M("Users")->where(array("id"=>$id))->setField('user_status','1');
            if (!$rst) {
                throw new \Exception(json_encode([
                        'where'=>["id"=>$id],
                        'update_data'=>['user_status', '1']
                    ]).' $rst: '.json_encode($rst));
            }
        }catch (\Exception $e){
            setAdminLog("启用会员 失败 ".$id.' '.$e->getMessage());
            return  $this->error('会员启用 失败！');
        }

        setAdminLog("启用会员 成功 ".$id);
        $this->success("会员启用 成功！");
    }

	function cancelsuper(){
    	$id=intval($_GET['id']);
        $user_info = getUserInfo($id);
        $tenantId = $user_info['tenant_id'];
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id,"tenant_id"=>$tenantId))->setField('issuper','0');
			$rst = M("users_super")->where("uid='{$id}'")->delete();
    		if ($rst!==false) {
                $action="取消超管会员：{$id}";
                setAdminLog($action);
				$redis = connectionRedis();
				$redis  -> hDel('super',$id);
				$redis -> close();
    			$this->success("会员取超管成功！");
    		} else {
    			$this->error('会员取消超管失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    function super(){
    	$id=intval($_GET['id']);
    	$user_info = getUserInfo($id);
    	$tenantId = $user_info['tenant_id'];
    	if ($id) {
			$rst = M("Users")->where(array("id"=>$id,"tenant_id"=>$tenantId))->setField('issuper','1');
    		$rst = M("users_super")->add(array('uid'=>$id,'addtime'=>time(),'tenant_id'=>$tenantId));
    		if ($rst!==false) {
                $action="设置超管会员：{$id}";
                setAdminLog($action);
				$redis = connectionRedis();
				$redis  -> hset('super',$id,'1');
				$redis -> close();
    			$this->success("会员设置超管成功！");
    		} else {
    			$this->error('会员设置超管失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

	function cancelhot(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('ishot','0');
    		if ($rst!==false) {
                $action="取消热门会员：{$id}";
                setAdminLog($action);
    			$this->success("会员取消热门成功！");
    		} else {
    			$this->error('会员取消热门失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    function hot(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('ishot','1');
    		if ($rst!==false) {
                $action="设置热门会员：{$id}";
                setAdminLog($action);
    			$this->success("会员设置热门成功！");
    		} else {
    			$this->error('会员设置热门失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

	function cancelrecommend(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('isrecommend','0');
    		if ($rst!==false) {
                $action="取消推荐会员：{$id}";
                setAdminLog($action);
    			$this->success("会员取消推荐成功！");
    		} else {
    			$this->error('会员取消推荐失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    function recommend(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('isrecommend','1');
    		if ($rst!==false) {
                $action="设置推荐会员：{$id}";
                setAdminLog($action);
    			$this->success("会员推荐成功！");
    		} else {
    			$this->error('会员推荐失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    function cancelzombie(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('iszombie','0');
    		if ($rst!==false) {
                $action="关闭会员僵尸粉：{$id}";
                setAdminLog($action);
    			$this->success("关闭成功！");
    		} else {
    			$this->error('关闭失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    function zombie(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('iszombie','1');
    		if ($rst!==false) {
                $action="开启会员僵尸粉：{$id}";
                setAdminLog($action);
    			$this->success("开启成功！");
    		} else {
    			$this->error('开启失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    function zombieall(){
    	$iszombie=intval($_GET['iszombie']);

    		$rst = M("Users")->where("user_type='2'")->setField('iszombie',$iszombie);
    		if ($rst!==false) {
                if($iszombie==1){
                    $action="开启全部会员僵尸粉";
                }else{
                    $action="关闭全部会员僵尸粉";
                }

                setAdminLog($action);
    			$this->success("操作成功！");
    		} else {
    			$this->error('操作失败！');
    		}

    }

    function cancelzombiep(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('iszombiep','0');
    		if ($rst!==false) {
                $action="关闭僵尸粉会员：{$id}";
                setAdminLog($action);
				M("users_zombie")->where("uid='{$id}'")->delete();
    			$this->success("关闭成功！");
    		} else {
    			$this->error('关闭失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    function zombiep(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('iszombiep','1');
    		if ($rst!==false) {
                $action="开启僵尸粉会员：{$id}";
                setAdminLog($action);
				$users_zombie=M("users_zombie");
				$isexist=$users_zombie->where("uid={$id}")->find();
				if(!$isexist){
					$users_zombie->add(array("uid"=>$id));
				}
    			$this->success("开启成功！");
    		} else {
    			$this->error('开启失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }


    function cancelshare(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if ($id) {
            $rst = M("Users")->where(array("id"=>$id))->setField('isshare','0');
            $rst2=M("users_live")->where("uid=%d",$id)->setField('isshare','0');
            $key1='userinfo_'.$id;
            $userinfo =getUserInfo($id);
            $userinfo['isshare'] = 0;
            $rs = setCaches($key1,$userinfo);
            if ($rst!==false) {
                $action="关闭公共主播会员：{$id}";
                setAdminLog($action);
                $this->success("关闭成功！");
            } else {
                $this->error('关闭失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    function share(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if ($id) {
            $rst = M("Users")->where(array("id"=>$id))->setField('isshare','1');
            $rst2=M("users_live")->where("uid=%d",$id)->setField('isshare','1');
            $key1='userinfo_'.$id;
            $userinfo =getUserInfo($id);
            $userinfo['isshare'] = 1;
            $rs = setCaches($key1,$userinfo);
            if ($rst!==false) {
                $action="开启公共主播会员：{$id}";
                setAdminLog($action);
                $this->success("开启成功！");
            } else {
                $this->error('开启失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    function cancelsendred(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if ($id) {
            $rst = M("Users")->where(array("id"=>$id,"tenant_id"=>$tenantId))->setField('issendred','0');
            if ($rst!==false) {
                $action="关闭发送红包会员：{$id}";
                setAdminLog($action);
                $this->success("关闭成功！");
            } else {
                $this->error('关闭失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    function sendred(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if ($id) {
            $rst = M("Users")->where(array("id"=>$id,"tenant_id"=>$tenantId))->setField('issendred','1');
            if ($rst!==false) {
                $action="开启发送红包会员：{$id}";
                setAdminLog($action);
                $this->success("开启成功！");
            } else {
                $this->error('开启失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    function cancelprivatemsg(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if ($id) {
            $rst = M("Users")->where(array("id"=>$id,"tenant_id"=>$tenantId))->setField('isprivatemsg','0');
            if ($rst!==false) {
                $action="关闭发送私信会员：{$id}";
                setAdminLog($action);
                $this->success("关闭成功！");
            } else {
                $this->error('关闭失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    function privatemsg(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if ($id) {
            $rst = M("Users")->where(array("id"=>$id,"tenant_id"=>$tenantId))->setField('isprivatemsg','1');
            if ($rst!==false) {
                $action="开启发送私信会员：{$id}";
                setAdminLog($action);
                $this->success("开启成功！");
            } else {
                $this->error('开启失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    //批量设置僵尸粉
    public function zombiepbatch() {
		$iszombiep=intval($_GET['iszombiep']);
		$ids = $_POST['ids'];
		$tids=join(",",$_POST['ids']);
		$users_zombie=M("users_zombie");
		$rst = M("Users")->where("id in ({$tids}) and user_type='2'")->setField('iszombiep',$iszombiep);
		if ($rst!==false) {
			if($iszombiep==1){
				foreach($ids as $k=>$v){
					$isexist=$users_zombie->where("uid={$v}")->find();
					if(!$isexist){
						$users_zombie->add(array("uid"=>$v));
					}

				}
				$action="开启会员僵尸粉：{$tids}";
			}else{
				$users_zombie->where("uid in ({$tids})")->delete();
                $action="关闭会员僵尸粉：{$tids}";
			}
            setAdminLog($action);
			$this->success("设置成功！");
		} else {
			$this->error('设置失败！');
		}
    }

    function cancelrecord(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('isrecord','0');
    		if ($rst!==false) {
                $action="关闭会员回放：{$id}";
                setAdminLog($action);
    			$this->success("关闭成功！");
    		} else {
    			$this->error('关闭失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    function record(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('isrecord','1');
    		if ($rst!==false) {
                $action="开启会员回放：{$id}";
                setAdminLog($action);
    			$this->success("开启成功！");
    		} else {
    			$this->error('开启失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    function recordall(){
    	$isrecord=intval($_GET['isrecord']);

    		$rst = M("Users")->where("user_type='2'")->setField('isrecord',$isrecord);
    		if ($rst!==false) {
                if($isrecord==1){
                    $action="开启全部会员回放：";
                }else{
                    $action="关闭全部会员回放：";
                }

                setAdminLog($action);
    			$this->success("操作成功！");
    		} else {
    			$this->error('操作失败！');
    		}

    }

    public function add(){
        $country_code_list = country_code_list();

        $this->assign("country_code_list", $country_code_list);
        $this->display(":add");
    }
    
    public function add_post(){
        if(IS_POST){
            $param = I('post.');
            $pid = $_POST['pid'];

            if ($pid != 0) {
                $pUserInfo = M("users")->where(['id' => $pid, 'tenant_id' => getTenantIds()])->find();
                if (!$pUserInfo) {
                    $this->error('上级用户id 不存在');
                }
            }
            $user=$this->users_model;
            $user_login=I('user_login');
            if($user_login==''){
                $this->error('手机号不能为空');
            }
            if(mb_strlen($user_login) < 6){
                $this->error('手机号长度不能小于6位');
            }
            $isexist=M("users")->field("id")->where("user_login='{$user_login}'")->find();
            if ($_POST['code']) {
                if (strlen($_POST['code'])!= 6){
                    return $this->error('邀请码为6为');
                }
                $oneinfo = M("users_agent_code")->field("uid")->where(['code' => $_POST['code']])->find();
                if ($oneinfo) {
                    return $this->error('此邀请码已存在');
                }
            }
            if($isexist){
                $this->error('手机号已存在');
            }
            if( $user->create()){
                if ($pid != 0){
                    /*$pUserInfo=M("users")->where(['id'=>$pid,'tenant_id'=>getTenantIds()])->find();
                    if (!$pUserInfo){
                        $this->error('上级用户id 不存在');
                    }*/
                    $user->pid = $pid;
                    $user->pids = $pUserInfo['pids'].','.$pid;
                    $pusers_agent  = M('users_agent')->where(['uid'=>$pid])->find();
                    if ($pusers_agent){
                        $data= [
                            //'uid'=>$id,
                            'user_login'=> $user_login,
                            'one_uid'=>$pid,
                            'two_uid' => $pusers_agent['one_uid'],
                            'three_uid' => $pusers_agent['two_uid'],
                            'four_uid' => $pusers_agent['three_uid'],
                            'five_uid' => $pusers_agent['four_uid'],
                            'tenant_id' => getTenantIds(),
                            //'user_type'=>$_POST['user_type'],
                            'user_type'=>2,
                        ];
                    }else{
                        $data= [
                            // 'uid'=>$id,
                            'user_login'=>$user_login,
                            'one_uid'=>$pid,
                            'two_uid' => 0,
                            'three_uid' => 0,
                            'four_uid' => 0,
                            'five_uid' => 0,
                            'tenant_id' => getTenantIds(),
                            //'user_type'=>$_POST['user_type'],
                            'user_type'=>2,
                        ];
                    }
                }else{
                    $user->pid = $pid;
                    $user->pids = $pid;
                    $data= [
                        //  'uid'=>$id,
                        'user_login'=> $user_login,
                        'one_uid'=>0,
                        'two_uid' => 0,
                        'three_uid' => 0,
                        'four_uid' => 0,
                        'five_uid' => 0,
                        'tenant_id' => getTenantIds(),
                        //'user_type'=>$_POST['user_type'],
                        'user_type'=>2,
                    ];
                }
                
                if ( $_POST['is_certification'] == 1){
                    if ($_POST['certification_name'] ==''){
                        $this->error('请输入官方蓝V账号名称');
                    }
                    $user->is_certification = $_POST['is_certification'];
                }else{
                    $user->is_certification  = 0;
                }
                $user->user_login  = $user_login;
                $user->mobile = $user_login;
                $user->certification_name = $_POST['certification_name'];
                $user->user_type = $_POST['user_type'];
                $user->user_pass=setPass($_POST['user_pass']);
                $configpri=getConfigPri();

                if(!empty($_FILES)){
                    $savepath=date('Ymd').'/';
                    //上传处理类
                    $config=array(
                        'rootPath' => './'.C("UPLOADPATH"),
                        'savePath' => $savepath,
                        'maxSize' => 11048576,
                        'saveName'   =>    array('uniqid',''),
                        'exts'       =>    array('svga'),
                        'autoSub'    =>    false,
                    );
                    $upload = new \Think\Upload($config);//
                    $info=$upload->upload();

                    ;
                    //开始上传
                    if ($info) {
                        //上传成功
                        //写入附件数据库信息
                        if(!empty($info['avatar'])){
                            $avatar = $info['avatar']['url'];
                            $avatar = str_replace("http","https",$avatar );
                            $user->avatar=$avatar;
                            $user->avatar_thumb=$avatar;
                        }else{
                            $round_number = mt_rand(1,200);
                            if ($round_number >100){
                                $round_avatar =  $round_number.'.png';
                            }else{
                                $round_avatar =  $round_number.'.jpg';
                            }
                            $user->avatar = '/public/images/head_'.$round_avatar;
                            $user->avatar_thumb = '/public/images/head_'.$round_avatar;;
                        }
                    } else {
                        //上传失败，返回错误
                        $this->error($upload->getError());
                    }
                }else{
                    // $round_number = mt_rand(1,200);
                    // if ($round_number >100){
                    //     $round_avatar =  $round_number.'.png';
                    // }else{
                    //     $round_avatar =  $round_number.'.jpg';
                    // }
                    // $user->avatar = '/public/images/head_'.$round_avatar;
                    // $user->avatar_thumb = '/public/images/head_'.$round_avatar;
                    $user->avatar = '/public/images/h5_avatar.jpg';
                    $user->avatar_thumb = '/public/h5_avatar.jpg';
                    
                }
                $user->tenant_id =getTenantIds();
                $user->game_tenant_id =getGameTenantId();
                $user->zone = $param['zone'];
                $user->create_time=date('Y-m-d H:i:s',time());
                $user->ctime = time();
                $result=$user->add();
                if($result!==false){
                    $user_id = M("users")->getLastInsID();
                    if ($_POST['code']){
                        $code = $_POST['code'];
                    }else{
                        $code=createCode();
                    }
                    $code_info=array('uid'=>$user_id,'code'=>$code,'tenant_id'=>getTenantIds());
                    $data['uid'] = $user_id;
                    M('users_agent')->add($data);
                    $Agent_code=M('users_agent_code');
                    $isexist=$Agent_code->field("uid")->where(['uid'=>$user_id])->find();
                    if($isexist){
                        $Agent_code->where(['uid'=>$user_id])->save($code_info);
                    }else{
                        $Agent_code->add($code_info);
                    }

                    $action="添加会员：{$user_id}";
                    setAdminLog($action);
                    $this->success('添加成功');
                }else{
                    $this->error('添加失败');
                }

            }else{
                $this->error($this->users_model->getError());
            }
        }
    }

    public function edit(){
        $id=intval($_GET['id']);
        if($id){
            $userinfo=M("users")->where(['id'=>$id])->find();
            $oneinfo=M("users_agent_code")->field("code")->where(['uid'=>$id ])->find();
            $userinfo['code']= $oneinfo['code'];
            $this->assign('userinfo', $userinfo);
        }else{
            $this->error('数据传入失败！');
        }
        $this->display(":edit");
    }

    public function edit_post(){
        if(IS_POST){
            $id= $_POST['id'];
            $pid = $_POST['pid'];
            $userinfo=M("users")->where(['id'=>$id])->find();
            if ($pid != 0) {
                if ($pid != $userinfo['pid']) {
                    $pUserInfo = M("users")->where(['id' => $pid,'tenant_id'=>getTenantIds()])->find();
                    if (!$pUserInfo) {
                        $this->error('上级用户id不存在');
                    }
                    if ($id == $pid){
                        $this->error('上级用户id不能是自身');
                    }
                    $pidsArray  =  explode(',',$pUserInfo['pids']);
                    if (in_array($id,$pidsArray)){
                        $this->error('填写的上级用户id是此用户的下级');
                    }
                }
            }
            $user=M("users");
            $user->create();
            $avatar=$_POST['avatar'];

            if ($_POST['code']){
                if (strlen($_POST['code'])!= 6){
                    return $this->error('邀请码为6为');
                }
                $oneinfo=M("users_agent_code")->field("uid")->where(['code'=>$_POST['code'],'uid'=>['neq',$id] ])->find();
                if ($oneinfo){
                    return $this->error('此邀请码已存在');
                }

                $isexist=M("users_agent_code")->field("uid")->where(['uid'=>$id])->find();
                if($isexist){
                    M("users_agent_code")->where(['uid'=>$id])->save(['code'=>$_POST['code']]);
                }else{
                    $code_info=array('uid'=>$id,'code'=>$_POST['code'],'tenant_id'=>getTenantIds());
                    M("users_agent_code")->add($code_info);
                }

            }else{
                return $this->error('此邀请码不能为空');
            }
            if ($pid != 0){
                if ($pid != $userinfo['pid']){
                    $user->pid = $pid;
                    $user->pids = $pUserInfo['pids'].','.$pid;
                    $users_agent  = M('users_agent')->where(['uid'=>$id])->find();
                    $pusers_agent  = M('users_agent')->where(['uid'=>$pid])->find();
                    if ($pusers_agent){
                        $data= [
                            'uid'=>$id,
                            'user_login'=> $userinfo['user_login'],
                            'one_uid'=>$pid,
                            'two_uid' => $pusers_agent['one_uid'],
                            'three_uid' => $pusers_agent['two_uid'],
                            'four_uid' => $pusers_agent['three_uid'],
                            'five_uid' => $pusers_agent['four_uid'],
                            'tenant_id' => getTenantIds(),
                        ];
                    }else{
                        $data= [
                            'uid'=>$id,
                            'user_login'=> $userinfo['user_login'],
                            'one_uid'=>$pid,
                            'two_uid' => 0,
                            'three_uid' => 0,
                            'four_uid' => 0,
                            'five_uid' => 0,
                            'tenant_id' => getTenantIds(),
                        ];
                    }
                    if ($users_agent){
                        M('users_agent')->where(['id'=>$users_agent['id']])->save($data);
                    }else{
                        M('users_agent')->add($data);
                    }
                }
            }else{
                $user->pid = $pid;
                $user->pids = $pid;
                $users_agent  = M('users_agent')->where(['uid'=>$id])->find();
                $data= [
                    'uid'=>$id,
                    'user_login'=> $userinfo['user_login'],
                    'one_uid'=>0,
                    'two_uid' => 0,
                    'three_uid' => 0,
                    'four_uid' => 0,
                    'five_uid' => 0,
                    'tenant_id' => getTenantIds()
                ];
                if ($users_agent){
                    M('users_agent')->where(['id'=>$users_agent['id']])->save($data);
                }else{
                    M('users_agent')->add($data);
                }
            }

            $configpri=getConfigPri();
            if($avatar==''){
                $round_number = mt_rand(1,200);
                if ($round_number >100){
                    $round_avatar =  $round_number.'.png';
                }else{
                    $round_avatar =  $round_number.'.jpg';
                }
                $user->avatar = '/public/images/head_'.$round_avatar;
                $user->avatar_thumb = '/public/images/head_'.$round_avatar;;

            }else if(strpos($avatar,'http')===0){
                /* 绝对路径 */
                $user->avatar=  $avatar;
                $user->avatar_thumb=  $avatar;
            }else if(strpos($avatar,'/')===0){
                /* 本地图片 */
                $user->avatar=  $avatar;
                $user->avatar_thumb=  $avatar;
            }else{
                /* 七牛 */
                //$user->avatar=  $avatar.'?imageView2/2/w/600/h/600'; //600 X 600
                //$user->avatar_thumb=  $avatar.'?imageView2/2/w/200/h/200'; // 200 X 200
            }
            $user->fans = $_POST['fans'];
            $user->follows = $_POST['follows'];
            $user->is_certification = $_POST['is_certification'];
            if ( $_POST['is_certification'] == 1){
                if ($_POST['certification_name'] ==''){
                    $this->error('请输入官方蓝V账号名称');
                }
            }
            $user->certification_name = $_POST['certification_name'];
            $user->user_type = $_POST['user_type'];
            $result=$user->where(['id'=>$id])->save();
            if($result!==false){
                $this->delCache($id);
                $action="修改会员信息：{$user->getLastSql()}";
                setAdminLog($action);
                if($userinfo['user_type'] != $_POST['user_type']){
                    CommonModel::getInstance()->updateUserTypeWithUid($userinfo['id'], $_POST['user_type']);
                }
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
    }

    /*
     * 修改会员登录密码
     * */
    public function editpassword(){
        if(IS_POST) {
            $uid = trim(I('uid'));
            $password = trim(I('password'));
            $check_password = trim(I('check_password'));
            if(!$uid){
                $this->error('请先确认需要修改的会员信息');
            }
            if(!$password){
                $this->error('请输入登录密码');
            }
            if($password != $check_password){
                $this->error('两次密码不同');
            }
            $check = passcheck($password);

            if($check==0){
                $this->error('密码格式不规范，请输入6-12位数字与字母');
            }else if($check==2){
                $this->error('密码不能纯数字或纯字母');
            }

            $userinfo = getUserInfo($uid);
            $role_id=$_SESSION['role_id'];
            if($role_id!=1){
                $tenantId=getTenantIds();
                if($tenantId != $userinfo['tenant_id']){
                    $this->error('只能修改当前租户下的会员密码');
                }
            }
            $tenantInfo=getTenantInfo($userinfo['tenant_id']);
            if($tenantInfo['site_id'] != 2){
                $this->error('只能修改独立租户下的会员密码');
            }

            $user_pass=setPass($password);
            $res = M("Users")->where(['id'=>intval($uid)])->save(['user_pass'=>$user_pass]);
            if(!$res){
                $msg = $res==0 ? '修改密码失败！新密码和原密码相同' : '修改密码失败！';
                $this->error($msg);
            }
            $this->success("操作成功！",'/User/indexadmin/index');
        }
        $this->display(":editpassword");
    }

    /*
	 * 修改会员支付密码
	 * */
    public function edit_payment_password(){
        if(IS_POST) {
            $param = I('post.');
            $uid = trim($param['uid']);
            $password = trim($param['password']);
            $confirm_password = trim($param['confirm_password']);
            if(!$uid){
                $this->error('请先确认需要修改的会员信息');
            }
            if(!$password){
                $this->error('请输入支付密码');
            }
            if($password != $confirm_password){
                $this->error('两次密码不同');
            }

            if(!preg_match("/^[0-9]{6}$/", $password)){
                $this->error('密码格式不规范，请输入6位数字密码');
            }

            $user_info = M('users')->field('id,user_pass,payment_password,salt,tenant_id')->where(['id'=>intval($uid)])->find();
            $role_id=$_SESSION['role_id'];
            if($role_id!=1){
                $tenantId=getTenantIds();
                if($tenantId != $user_info['tenant_id']){
                    $this->error('只能修改当前租户下的会员支付密码');
                }
            }
            $tenantInfo=getTenantInfo($user_info['tenant_id']);
            if($tenantInfo['site_id'] != 2){
                $this->error('只能修改独立租户下的会员支付密码');
            }

            $salt = $user_info['salt'] ? $user_info['salt'] : createSalt();
            $payment_password = signPaymentPassword($password, $salt);

            $update_data = array( "payment_password"=>$payment_password, 'payment_password_err_count'=>0);
            if(!$user_info['salt']){
                $update_data['salt'] = $salt;
            }

            try {
                M("Users")->where(['id'=>intval($uid)])->save($update_data);
            }catch (\Exception $e){
                setAdminLog('【修改会员支付密码-失败】'.$e->getMessage());
                $this->error('修改会员支付密码失败');
            }
            delUserInfoCache($uid);
            setAdminLog('【修改会员支付密码-成功】'.json_encode($param));
            $this->success("操作成功！");
        }
        $this->display(":edit_payment_password");
    }

    /*
     * 搜索会员
     * */
    public function searchuser(){
        $user = trim(I('user'));
        if(!$user){
            $this->error('请先确认需要修改的会员信息');
        }
        $userinfo = M("Users")->where(['id|user_login'=>array("eq",$user)])->find();
        if($userinfo){
            $role_id=$_SESSION['role_id'];
            if($role_id!=1){
                $tenantId=getTenantIds();
                if($tenantId != $userinfo['tenant_id']){
                    $this->error('只能修改当前租户下的会员密码');
                }
            }

            $tenantInfo=getTenantInfo($userinfo['tenant_id']);
            if($tenantInfo['site_id'] != 2){
                $this->error('只能修改独立租户下的会员密码');
            }
            $userinfo['tenant_name'] = !empty($tenantInfo) ? $tenantInfo['name'] : $userinfo['tenant_id'];
            $userinfo['user_type'] = user_type_name($userinfo['user_type']);
            $this->success($userinfo);
        }else{
            $this->error('请输入正确的会员ID或用户名');
        }
    }

	/* 生成邀请码 */
    public function createCode(){
		$code=createCode();
		$rs=array('info'=>$code);
		echo json_encode($rs);
		exit;
	}
    public function delCache($uid){
        $key='userinfo_'.$uid;
        delcache($key);
    }

    public  function import()
    {
        $tmp_name = $_FILES ['file']['tmp_name'];

        $path_info = pathinfo($_FILES ['file']['name']);
        $extension = $path_info['extension'];

        if (!in_array($extension, ['xls', 'xlsx'])) {
            $this->error('请上传excel文件');
        }
        if (is_uploaded_file($tmp_name)) {
            /*设置上传路径*/
            $save_dir = './data/upload/';
            /*以时间来命名上传的文件*/
            $filename = date('Ymdhis');
            $basename = $filename . "." . $extension;
            $savePath = $save_dir . $basename;
            /*是否上传成功*/
            if (!move_uploaded_file($tmp_name, $savePath)) {
                $this->error('上传失败');
            }
            vendor("PHPExcel.PHPExcel.IOFactory");
            $iofactory = new \PHPExcel_IOFactory();
            $objReader = $iofactory::createReaderForFile($savePath);
            $objPHPExcel = $objReader->load($savePath);
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $data = array();

            try {
                M()->startTrans();
                for ($row = 2; $row <= $highestRow; $row++) {
                    $data['user_login']  = $sheet->getCellByColumnAndRow(0, $row)->getValue();
                    if (!$data['user_login']){
                        M("users")->rollback();
                        unlink($savePath); // 删除上传的文件
                        $this->error('第'.$row.'行，用户名不能为空,请编辑后在导入');
                    }
                    if(mb_strlen($data['user_login']) < 6){
                        M("users")->rollback();
                        unlink($savePath); // 删除上传的文件
                        $this->error('第'.$row.'行，用户名长度不能小于6位');
                    }
                    $data['zone']  = $sheet->getCellByColumnAndRow(16, $row)->getValue();
                    if (!$data['zone']){
                        M("users")->rollback();
                        unlink($savePath); // 删除上传的文件
                        $this->error('第'.$row.'行，区号不能为空,请编辑后在导入');
                    }

                    if ( M("users")->where(array('user_login'=>$data['user_login'] ))->find()){
                        M("users")->rollback();
                        unlink($savePath); // 删除上传的文件
                        $this->error('第'.$row.'行，用户名'. $data['user_login'].'已存在,请编辑后在导入');
                    }
                    $user_pass  = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                    if ($user_pass){
                        $data['user_pass']  = setPass($user_pass);
                    }else{
                        $data['user_pass']  = setPass('abc123456');;
                    }
                    $data['user_nicename'] = 'user'.$sheet->getCellByColumnAndRow(2, $row)->getValue();
                    $data['user_email']  = empty($sheet->getCellByColumnAndRow(3, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(3, $row)->getValue();
                    $data['user_url']  = empty($sheet->getCellByColumnAndRow(4, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(4, $row)->getValue();
                    $round_number = mt_rand(1,200);
                    if ($round_number >100){
                        $round_avatar =  $round_number.'.png';
                    }else{
                        $round_avatar =  $round_number.'.jpg';
                    }
                    $data['avatar'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/public/images/head_'.$round_avatar;
                    $data ['avatar_thumb'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/'.'/public/images/head_'.$round_avatar;
                    $data['sex']  = empty($sheet->getCellByColumnAndRow(5, $row)->getValue()) ?1:$sheet->getCellByColumnAndRow(5, $row)->getValue();
                    //$data['birthday']  = empty($sheet->getCellByColumnAndRow(6, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(6, $row)->getValue();
                    $data['signature']  = empty($sheet->getCellByColumnAndRow(6, $row)->getValue()) ?'这家伙很懒，什么都没留下':$sheet->getCellByColumnAndRow(6, $row)->getValue();
                    $data['last_login_ip']  = empty($sheet->getCellByColumnAndRow(7, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(7, $row)->getValue();
                    /* if ( $sheet->getCellByColumnAndRow(9, $row)->getValue()){
                         $last_login_time  = $sheet->getCellByColumnAndRow(9, $row)->getValue();
                     }

                     $data[$i]['create_time']  = empty($sheet->getCellByColumnAndRow(10, $row)->getValue()) ?date("Y-m-d H:i:s"):$sheet->getCellByColumnAndRow(10, $row)->getValue();*/

                    /*
                     $data[$i]['user_activation_key']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                    $data[$i]['user_status']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();*/
                    $data['create_time'] = date("Y-m-d H:i:s");
                    $data['ctime'] = time();
                    $data['score']  =  empty($sheet->getCellByColumnAndRow(8, $row)->getValue()) ?0:$sheet->getCellByColumnAndRow(8, $row)->getValue();
                    $data['user_type'] = $sheet->getCellByColumnAndRow(9, $row)->getValue();
                    if(!in_array($data['user_type'], [2,5,6])){
                        M("users")->rollback();
                        unlink($savePath); // 删除上传的文件
                        $this->error('第'.$row.'行，用户类型不正确');
                    }
                    //   $data['coin']  = empty($sheet->getCellByColumnAndRow(10, $row)->getValue()) ?0:$sheet->getCellByColumnAndRow(10, $row)->getValue();
                    $data['mobile']  = empty($sheet->getCellByColumnAndRow(10, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(11, $row)->getValue();
                    /* $data[$i]['token']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                       $data[$i]['expiretime']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                       $data[$i]['consumption']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                       $data[$i]['votes']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                       $data[$i]['votestotal']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();*/
                    $data['province']  = empty($sheet->getCellByColumnAndRow(11, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(12, $row)->getValue();
                    $data['city']  = empty($sheet->getCellByColumnAndRow(12, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(13, $row)->getValue();
                    /* $data[$i]['isrecommend']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                     $data[$i]['openid']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                     $data[$i]['login_type']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                     $data[$i]['iszombie']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                     $data[$i]['isrecord']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                     $data[$i]['iszombiep']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                     $data[$i]['issuper']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                     $data[$i]['ishot']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                     $data[$i]['goodnum']  = $sheet->getCellByColumnAndRow($col, $row)->getValue();*/
                    $data['source']  = $sheet->getCellByColumnAndRow(13, $row)->getValue();
                    $data['source'] = $data['source'] ? $data['source'] : 'pc';
                    $data['tenant_id'] = getTenantIds();

                    $data['game_tenant_id']=getGameTenantIds();
                    $data['integral']  = $sheet->getCellByColumnAndRow(14, $row)->getValue();
                    $father_user = $sheet->getCellByColumnAndRow(15, $row)->getValue();
                    $father_user = $father_user ? $father_user : 0;
                    $father_user_info = $father_user ? M("users")->where(array('id'=>$father_user))->find() : null;
                    if ($father_user_info){
                        $data['pids'] = $father_user_info['pids'].','.$father_user;
                        $data['pid'] = $father_user;
                    }else{
                        $data['pids'] = 0;
                        $data['pid'] = $father_user;
                    }

                    $data['fans'] = $sheet->getCellByColumnAndRow(17, $row)->getValue();
                    $data['fans'] = $data['fans'] && $data['fans'] >= 0 ? $data['fans'] : 0;
                    $data['follows'] = $sheet->getCellByColumnAndRow(18, $row)->getValue();
                    $data['follows'] = $data['follows'] && $data['follows'] >= 0 ? $data['follows'] : 0;

                    M("users")->add($data);
                    $user_id = M("users")->getLastInsID();
                    if ($father_user){
                        if ($father_user_info){
                            $father_user_array = M("users_agent")->where(array('uid'=>$father_user))->find();
                            if ($father_user_array) {
                                $new_user_array = array(
                                    'uid' =>  $user_id,
                                    'user_login'=>$data['user_login'],
                                    'one_uid'=>$father_user_array['uid'],
                                    'two_uid'=>$father_user_array['one_uid'],
                                    'three_uid'=>$father_user_array['two_uid'],
                                    'four_uid'=>$father_user_array['three_uid'],
                                    'five_uid'=>$father_user_array['four_uid'],
                                    'addtime'=>time(),
                                    'tenant_id'=>getTenantIds(),
                                    'user_type'=>$data['user_type'],
                                );
                                // 获取积分注册配置
                                $integral_config = M('integral_config')->where(array('type'=>1))->find();
                                if(!$integral_config){
                                    $integral_config = ['level_1' => 100,'level_2' => 50,'level_3' => 25,'level_4' => 12,'level_5' => 6];
                                }

                                $u_info = M('users')->where(array('id'=> $father_user_array['uid']))->field('integral')->find();
                                M('users')->where(array('id'=> $father_user_array['uid']))->setInc('integral',$integral_config['level_1']);
                                $integraldata=array(
                                    'uid'=>$father_user_array['uid'],
                                    'start_integral'=>$u_info['integral'],
                                    'change_integral'=>$integral_config['level_1'],
                                    'end_integral'=>($u_info['integral']+$integral_config['level_1']),
                                    'change_type'=>64,
                                    'act_type'=>1,
                                    'status'=>1,
                                    'remark'=>$data['user_login'].' 导入',
                                    'ctime'=>time(),
                                    'act_uid'=>$user_id,
                                    'tenant_id'=>getTenantIds(),
                                );
                                M('integral_log')->add($integraldata);

                                if($father_user_array['one_uid']>0){
                                    $u_info = M('users')->where(array('id'=> $father_user_array['one_uid']))->field('integral')->find();
                                    M('users')->where(array('id'=> $father_user_array['one_uid']))->setInc('integral',$integral_config['level_2']);
                                    $integraldata=array(
                                        'uid'=>$father_user_array['one_uid'],
                                        'start_integral'=>$u_info['integral'],
                                        'change_integral'=>$integral_config['level_2'],
                                        'end_integral'=>($u_info['integral']+$integral_config['level_2']),
                                        'change_type'=>64,
                                        'act_type'=>1,
                                        'status'=>1,
                                        'remark'=>$data['user_login'].' 导入',
                                        'ctime'=>time(),
                                        'act_uid'=>$user_id,
                                        'tenant_id'=>getTenantIds(),
                                    );
                                    M('integral_log')->add($integraldata);
                                }
                                if($father_user_array['two_uid']>0){
                                    $u_info = M('users')->where(array('id'=> $father_user_array['two_uid']))->field('integral')->find();
                                    M('users')->where(array('id'=> $father_user_array['two_uid']))->setInc('integral',$integral_config['level_3']);
                                    $integraldata=array(
                                        'uid'=>$father_user_array['two_uid'],
                                        'start_integral'=>$u_info['integral'],
                                        'change_integral'=>$integral_config['level_3'],
                                        'end_integral'=>($u_info['integral']+$integral_config['level_3']),
                                        'change_type'=>64,
                                        'act_type'=>1,
                                        'status'=>1,
                                        'remark'=>$data['user_login'].' 导入',
                                        'ctime'=>time(),
                                        'act_uid'=>$user_id,
                                        'tenant_id'=>getTenantIds(),
                                    );
                                    M('integral_log')->add($integraldata);
                                }
                                if($father_user_array['three_uid']>0){
                                    $u_info = M('users')->where(array('id'=> $father_user_array['three_uid']))->field('integral')->find();
                                    M('users')->where(array('id'=> $father_user_array['three_uid']))->setInc('integral',$integral_config['level_4']);
                                    $integraldata=array(
                                        'uid'=>$father_user_array['three_uid'],
                                        'start_integral'=>$u_info['integral'],
                                        'change_integral'=>$integral_config['level_4'],
                                        'end_integral'=>($u_info['integral']+$integral_config['level_4']),
                                        'change_type'=>64,
                                        'act_type'=>1,
                                        'status'=>1,
                                        'remark'=>$data['user_login'].' 导入',
                                        'ctime'=>time(),
                                        'act_uid'=>$user_id,
                                        'tenant_id'=>getTenantIds(),
                                    );
                                    M('integral_log')->add($integraldata);
                                }
                                if($father_user_array['four_uid']>0){
                                    $u_info = M('users')->where(array('id'=> $father_user_array['three_uid']))->field('integral')->find();
                                    M('users')->where(array('id'=> $father_user_array['three_uid']))->setInc('integral',$integral_config['level_5']);
                                    $integraldata=array(
                                        'uid'=>$father_user_array['four_uid'],
                                        'start_integral'=>$u_info['integral'],
                                        'change_integral'=>$integral_config['level_5'],
                                        'end_integral'=>($u_info['integral']+$integral_config['level_5']),
                                        'change_type'=>64,
                                        'act_type'=>1,
                                        'status'=>1,
                                        'remark'=>$data['user_login'].' 导入',
                                        'ctime'=>time(),
                                        'act_uid'=>$user_id,
                                        'tenant_id'=>getTenantIds(),
                                    );
                                    M('integral_log')->add($integraldata);
                                }
                            }else{

                                $new_user_array = array(
                                    'uid'=>$user_id,
                                    'user_login'=>$data['user_login'],
                                    'one_uid'=>0,
                                    'two_uid'=>0,
                                    'three_uid'=>0,
                                    'four_uid'=>0,
                                    'five_uid'=>0,
                                    'addtime'=>time(),
                                    'tenant_id'=>getTenantIds(),
                                    'user_type'=>$data['user_type'],
                                );
                            }
                        }
                    }else{
                        $new_user_array=array(
                            'uid'=>$user_id,
                            'user_login'=>$data['user_login'],
                            'one_uid'=>0,
                            'two_uid'=>0,
                            'three_uid'=>0,
                            'four_uid'=>0,
                            'five_uid'=>0,
                            'addtime'=>time(),
                            'tenant_id'=>getTenantIds(),
                            'user_type'=>$data['user_type'],
                        );
                    }
                    M("users_agent")->add($new_user_array);
                    $code=createCode();
                    $ruselt = M('users_agent_code')->add(array('uid' =>$user_id,'code'=> $code,'tenant_id'=>getTenantIds() ));
                }

                if ($user_id && $ruselt){
                    M("users")->commit();
                }
            }catch (\Exception $e){
                M()->rollback();
                setAdminLog('【导入用户】-失败：'.$e->getMessage());
                unlink($savePath); // 删除上传的文件
                $this->error('【导入用户】-失败');
            }
            unlink($savePath); // 删除上传的文件
            $this->success('导入成功','/User/Indexadmin/index');
        }
    }

    public  function bandcard(){
        $param = I('param.');
	    $map = [];
        if(isset($param['user_login']) && $param['user_login'] != ''){
            $user_login = $param['user_login'];
            $this->assign('user_login', $user_login);
            $uid = M('users')->where(array('user_login'=>$user_login))->getField('id');
            $map['uid'] =$uid;
        }
        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid'] = $param['uid'];
            $this->assign('uid', $param['uid']);
        }

        if(getRoleId() == 1){
        }else{
            //租户id条件
            $map['tenant_id']= getTenantIds();
        }

        $count= M('bank_card')->where($map)->count();
        $page = $this->page($count, 20);
        $band_card_list =  M('bank_card')->where($map) ->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach ( $band_card_list as $key => $value) {
            $band_card_list[$key]['user_login'] = M('users')->where(array('id'=>$value['uid']))->getField('user_login');
            $band_card_list[$key]['addtime'] = date('Y-m-d:H:i:s',$value['addtime']);
        }
        $this->assign('count', $count);
        $this->assign("page", $page->show("Admin"));
        $this->assign('lists', $band_card_list);
        $this->display(":bandcard");

    }
    public  function deletebandcard(){
	    $id = $_GET['id'];
        if ( M('bank_card')->where(array('id' => $id))->delete()){

            $this->success('删除成功');
        }else{
            $this->error('删除失败');

        }
    }

    /*
     * 代理层级
     * */
    public function agent(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = array();
        $agent_type = isset($param['agent']) ? $param['agent'] : '';
        if(isset($param['id']) && $param['id']!=''){
            switch ($agent_type){
                case 1:
                    $map['uid']=$param['id'];
                    break;
                case 2:
                    $map['one_uid']=$param['id'];
                    break;
                case 3:
                    $map['one_uid']=$param['id'];
                    break;
            }
        }

        if(isset($param['user_login']) && $param['user_login']!=''){
            switch ($agent_type){
                case 1:
                    $map['user_login']=$param['user_login'];
                    break;
                case 2:
                    $map['one_uid'] = M('users')->where(array('user_login' => $_REQUEST['user_login']))->getField('id');
                    break;
                case 3:
                    $map['one_uid'] = M('users')->where(array('user_login' => $_REQUEST['user_login']))->getField('id');
                    break;
            }
        }
        $noparam = false;
        if((!isset($param['id']) && !isset($param['user_login'])) || ($param['id']=='' && $param['user_login']=='')){
            $map['one_uid'] = 0;
            $noparam = true;
        }

        $res = $this->getAentList($map,$agent_type,$noparam);

        $this->assign('formget', $param);
        $this->assign("list", $res['list']);
        $this->assign('pall', $res['pall']);
        $this->assign("pid", (isset($map['one_uid']) ? $map['one_uid'] : 0));
        $this->display(":agent");
    }

    /*
     * 代理层级加载下级
     * */
    public function agentchilds(){
        $pid = I('pid');
        $p = I('p')>0 ? I('p') : 1;
        $_GET['p'] = $p;

        $map['one_uid'] = $pid ? $pid : 0;

        $res = $this->getAentList($map);

        $data = $res;
        $data['pid'] = $pid;
        $data['p'] = $p;
        $data['pall'] = $res['pall'];
        $this->ajaxReturn($data);
    }

    public function getAentList($map,$agent_type='',$noparam=false){
        if($_SESSION['role_id']!=1){
            $map['tenant_id'] = intval(getTenantId()); //租户id条件
        }
        $map['user_type'] = ['in',[0,2,5,6,7]];
        $agent = M('users_agent');
        $count = $agent->where($map)->count();
        $num = 100;
        $page = $this->page($count, $num);
        $list = $agent->where($map)->field('id,uid,user_login,one_uid,two_uid,three_uid,four_uid,five_uid')
                ->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

        $ids = array_keys(array_column($list,'uid','uid'));
        $a_list = count($ids)>0 ? $agent->where(['one_uid'=>['in',$ids]])->field('count(id) as count,one_uid')->group('one_uid')->select() : array();
        $a_list = count($a_list)>0 ? array_column($a_list,null,'one_uid') : array();

        $user_info_list = count($ids)>0 ? M('users')->where(['id'=>['in',$ids]])->field('id,user_nicename')->select() : array();
        $user_info_list = count($user_info_list)>0 ? array_column($user_info_list,null,'id') : array();

        $allids = array();
        foreach ($list as $key=>$val) {
            if($val['one_uid']){
                $info=getAgentInfo($val['one_uid']);
                if(isset($info['user_login'])){
                    $list[$key]['one_login'] = $info['user_login'];
                }else{
                    array_push($allids,$val['one_uid']);
                }
            }
            if($val['two_uid']){
                $info=getAgentInfo($val['two_uid']);
                if(isset($info['user_login'])){
                    $list[$key]['two_login'] = $info['user_login'];
                }else {
                    array_push($allids, $val['two_uid']);
                }
            }
            if($val['three_uid']){
                $info=getAgentInfo($val['three_uid']);
                if(isset($info['user_login'])){
                    $list[$key]['three_login'] = $info['user_login'];
                }else {
                    array_push($allids, $val['three_uid']);
                }
            }
            if($val['four_uid']){
                $info=getAgentInfo($val['four_uid']);
                if(isset($info['user_login'])){
                    $list[$key]['four_login'] = $info['user_login'];
                }else {
                    array_push($allids, $val['four_uid']);
                }
            }
            if($val['five_uid']){
                $info=getAgentInfo($val['five_uid']);
                if(isset($info['user_login'])){
                    $list[$key]['five_login'] = $info['user_login'];
                }else {
                    array_push($allids, $val['five_uid']);
                }
            }
        }

        $allu_list = count($allids)>0 ? $agent->where(['uid'=>['in',$allids]])->field('uid,user_login')->select() : array();
        $allu_list = count($allu_list)>0 ? array_column($allu_list,null,'uid') : array();

        foreach ($list as $key=>$val) {
            $list[$key]['user_nicename'] = isset($user_info_list[$val['uid']]) ? $user_info_list[$val['uid']]['user_nicename'] : $val['uid'];

            setAgentInfo($val['uid'],$val); // 设置代理详情缓存
            $list[$key]['one_uid'] = $val['one_uid'] ? $val['one_uid'] : '';
            $list[$key]['two_uid'] = $val['two_uid'] ? $val['two_uid'] : '';
            $list[$key]['three_uid'] = $val['three_uid'] ? $val['three_uid'] : '';
            $list[$key]['four_uid'] = $val['four_uid'] ? $val['four_uid'] : '';
            $list[$key]['five_uid'] = $val['five_uid'] ? $val['five_uid'] : '';

            $list[$key]['one_login'] = isset($val['one_login']) ? $val['one_login'] : (isset($allu_list[$val['one_uid']]) ? $allu_list[$val['one_uid']]['user_login'] : '');
            $list[$key]['two_login'] = isset($val['two_login']) ? $val['two_login'] : (isset($allu_list[$val['two_uid']]) ? $allu_list[$val['two_uid']]['user_login'] : '');
            $list[$key]['three_login'] = isset($val['three_login']) ? $val['three_login'] : (isset($allu_list[$val['three_uid']]) ? $allu_list[$val['three_uid']]['user_login'] : '');
            $list[$key]['four_login'] = isset($val['four_login']) ? $val['four_login'] : (isset($allu_list[$val['four_uid']]) ? $allu_list[$val['four_uid']]['user_login'] : '');
            $list[$key]['five_login'] = isset($val['five_login']) ? $val['five_login'] : (isset($allu_list[$val['five_uid']]) ? $allu_list[$val['five_uid']]['user_login'] : '');

            if($agent_type){
                $list[$key]['childscount'] = (($noparam== true || $agent_type==2) && isset($a_list[$val['uid']]['count'])) ? $a_list[$val['uid']]['count'] : 0;
            }else{
                $list[$key]['childscount'] = isset($a_list[$val['uid']]['count']) ? $a_list[$val['uid']]['count'] : 0;
            }
        }

        $data['list'] = $list;
        $data['pall'] = ceil($count/$num);
        return $data;
    }


    protected function _get_level($id, $array = array(), $i = 0) {
	    if ($i >= 5){
            return  $i;
        }
        if ($array[$id]['one_uid']==0){
            return  $i;
        }else{
            $i++;
            return $this->_get_level($array[$id]['id'],$array,$i);
        }


    }

     public  function export(){
         $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T');
         $xlsCell  = array(
             array('user_name','用户名'),
             array('pass',"密码"),
             array('nickname','昵称'),
             array('email','邮箱'),
             array('user_url','用户个人网站'),
             array('sex','性别0：保密，1：男；2：女'),
             array('signature','个性签名'),
             array('last_login_ip','最后登录ip'),
             array('score','用户积分'),
             array('user_type','用户类型（2.真实用户，5.包装账号，6.代管账号，7.测试账号，8.代理账号）'),
            // array('coin','资金(余额)'),
             array('mobile','手机号'),
             array('province','省份'),
             array('city','城市'),
             array('source','注册来源（pc.PC，android.安卓APP，ios.苹果APP）'),
             array('integral','积分'),
             array('pid','上级用户id'),

             array('zone','区号（国家代码，如：86）'),
             array('fans','粉丝数'),
             array('follows','关注数'),


         );
         $xlsName = 'user';
         $xlsData = [];
         exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
     }

    public  function shutup(){
        $id=intval($_GET['id']);
        $status=intval($_GET['status']);

        if ($id) {
            $rst = M("Users")->where(array("id"=>$id))->setField('isshutup',$status);
            if ($rst!==false) {
                $redis = connectionRedis();
                if($status == 1 ){
                    $redis-> hSet('user_shutup',$id,1);
                }else{
                    $redis-> hSet('user_shutup',$id,0);
                }


                $action="禁言：{$id}";
                setAdminLog($action);
                delUserInfoCache($id);
                $this->success("设置成功！");
            } else {
                $this->error('设置失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    public function forbidlive(){
        $param = I('post.');
        if(IS_POST && isset($param['golang_event'])) {
            $live_info = getUserLiveInfo($param['uid']);
            $config = getConfigPri($live_info['tenant_id']);
            if ($config['go_admin_url']) {
                $url = $config['go_admin_url'] . '/admin/v1/live_room/broadcast_system_event';
                $res = http_post($url, ['EventType' => 'Closelive', 'Message' => json_encode(['Uid' => $param['uid']])]);
            }
            $this->success($res);
        }

        $id=intval($_GET['id']);
        $status=intval($_GET['status']);

        if ($id) {
            $stream = M('users_live')->where(['uid'=>$id])->getField('stream');
            $stopRoomUrl = '';
            if($stream){
                $u_info= M("users")->field("game_tenant_id,token")->where(['id'=>$id])->find();
                $stopRoomUrl = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/api/public/?service=Live.StopRoom&game_tenant_id='.$u_info['game_tenant_id'].'&uid='.$id.'&token='.$u_info['token'].'&stream='.$stream.'&acttype=amdin_stop';
            }

            // 关播
            if(isset($_GET['acttype']) && $_GET['acttype']=='amdin_stop'){
                if(!$stopRoomUrl){
                    $this->success(['msg'=>'操作成功！','stopres'=>'前端已经处理关播事件']);
                }
                $stopres = file_get_contents($stopRoomUrl);
                $stopres = is_json($stopres) ? json_decode($stopres,true) : $stopres;
                if($stopres['data']['code'] == 700){
                    if($u_info['expiretime']<time()){
                        M("users")->where(['id'=>$id])->save(['expiretime'=>(time()+60*60*24*300)]);
                    }
                    delcache("token_".$id);
                    $stopres = file_get_contents($stopRoomUrl);
                    $stopres = is_json($stopres) ? json_decode($stopres,true) : $stopres;
                }
                $this->success(['msg'=>'操作成功！','stopres'=>$stopres]);
            }

            $rst = M("Users")->where(array("id"=>$id))->setField('isforbidlive',$status);
            if ($rst!==false) {
                $redis = connectionRedis();
                if($status == 1 ){
                    $action="禁播：{$id}";
                    $redis-> hSet('user_forbidlive',$id,1);
                }else{
                    $action="解除禁播：{$id}";
                    $redis-> hSet('user_forbidlive',$id,0);
                }


                setAdminLog($action);
                delUserInfoCache($id);
                $this->success(['msg'=>'操作成功！','stopRoomUrl'=>$stopRoomUrl]);
            } else {
                $this->error(['msg'=>'操作失败！','rst'=>$rst]);
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    public  function edits(){

        $id = '';
        if ($_GET['id']){
            $id = $_GET['id'];
            $labels = array(
                  0=>'普通房间',
                  1=>'密码房间',
                  2=>'门票房间',
                  3=>'计时房间',
            );
            $userinfo = M("Users")->field('roomtype_name')->where(array("id"=>$id))->find();

            $userlabels = $userinfo['roomtype_name']!='' ? explode(',',$userinfo['roomtype_name']) : [];

            $this->assign("id",$id);
            $this->assign("labels",$labels);
            $this->assign("userlabels",$userlabels);

            $this->display();
        }


    }
    public  function edits_post(){

        if ($_POST['id']){
            $names = isset($_POST['label']) ? implode(',',$_POST['label']) : '';
            $rst = M("Users")->where(array("id"=>$_POST['id']))->setField('roomtype_name',$names);

            $this->success("设置成功！");
        }


    }
    /**
     * 修改聊天是权限
    **/
    public function jurisdiction()
    {
        $id=intval($_GET['id']);
        $status=intval($_GET['status']);
        if($id){
            if(M("Users")->where(array("id"=>$id))->setField('isjurisdiction',$status)){
                $this->success("设置成功！");
            }
        }
        $this->error('设置失败');
    }

    /*
     * 增加累计消费，正数为加，负数为减
     * */
    public function add_consumption(){
        if (IS_POST){
            $param = I('post.');
            if(!isset($param['id']) || !$param['id']){
                $this->error('参数错误');
            }
            if(!isset($param['consumption']) || !$param['consumption']){
                $this->error('请输入累计消费值');
            }
            $user_info = M("Users")->where(array("id"=>$param['id']))->find();
            if(!$user_info){
                $this->error('会员不存在');
            }
            if(($user_info['consumption'] + $param['consumption']) < 0){
                $this->error('改变值不能小于 -'.$user_info['consumption']);
            }

            try{
                M("Users")->where(array("id"=>$param['id']))->setInc('consumption',$param['consumption']);
            }catch (\Exception $e){
                setAdminLog('改变累计消费失败：'.$e->getMessage());
                $this->success("操作失败");
            }
            $this->success("操作成功");
        }
        $id = I('id');
        if(!$id){
            $this->error('参数错误');
        }
        $this->assign('param',I('param.'));
        $this->display();
    }
    public function bar()
    {
        $id=intval($_GET['id']);
        $status=intval($_GET['status']);
        $field =  $_GET['field'];
        if($id){
            if(M("Users")->where(array("id"=>$id))->setField($field,$status)){
                $this->success("设置成功！");
            }
        }
        $this->error('设置失败');
    }

    public  function batchUpdate(){
        if (IS_POST){
            $param = I('post.');
            if(!isset($param['uid']) || !$param['uid']){
                $this->error('参数错误');
            }
            if(!isset($param['amount']) || !$param['amount']){
                $this->error('参数错误');
            }
            $uidArray = explode(',',$param['uid']);

            $amountArray = explode(',',$param['amount']);
            if (count($uidArray)> count($amountArray)){
                $this->error('用户个数大于修改值个数');
            }
            if (count($uidArray)<count($amountArray)){
                $this->error('用户个数小于于修改值个数');
            }
            $userinfo =  M("Users")->where(["id"=>['in', $uidArray],'tenant_id'=>getTenantIds()])->field('id')->select();
           $ids = array_column($userinfo,'id');

            $notuserId = [];
            $type = $param['type'];
            if ($type == 1){
                $typeNmae = '粉丝数';
                foreach ($uidArray as $key =>  $value){
                    if (in_array($value,$ids)){
                        $result =  M("Users")->where(array("id"=>$value))->save(['fans'=> $amountArray[$key]]);
                        delUserInfoCache($value);
                    }else{
                        $notuserId[] = $value;
                    }
                }
            }else{
                $typeNmae = '关注数';
                foreach ($uidArray as $key =>  $value) {
                    if (in_array($value, $ids)) {
                        $result = M("Users")->where(array("id" => $value))->save(['follows' => $amountArray[$key]]);
                        delUserInfoCache($value);
                    } else {
                        $notuserId[] = $value;
                    }
                }
            }

            $action="修改用户{$typeNmae} ,id:{$param['uid']},值:{$param['amount']}";
            setAdminLog($action);
            if ($notuserId){
                $msg = implode(',',$notuserId);
                return $this->error("会员".$msg .'不存在其他账号已修改');
            }

           return $this->error("操作成功");
        }
        $this->display(":batchUpdate");
    }


    public  function userexport(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());
        $conifg = getConfigPub($tenant_id);

        $map=array();
        $map['user_type'] = $conifg['is_use_visitor'] == '0' ? ['in',[2,3,5,6,7]] : ['in',[2,3,4,5,6,7]];
        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']){
                $map['tenant_id']=$param['tenant_id'];
            }
        }else{
            //租户id条件
            $map['tenant_id']=$tenant_id;
        }
        if(isset($param['user_type']) && $param['user_type'] != ''){
            $map['user_type']=$param['user_type'];
        }
        if($map['user_type'] == '4' && $conifg['is_use_visitor'] == '0'){
            $map[] = ['user_type'=> ['in', [2,3,5,6,7]]];
        }

        $lists = M('Users')
            ->field('id,
                    user_login,
                   user_nicename,
                   last_login_ip,
                   last_login_time,
                   create_time,
                   coin,
                   yeb_balance,
                   follows,
                   fans,
                   vip_margin,
                   recharge_total')
            ->where($map)
            ->order("id DESC")
            ->select();
        $uidArray = array_column($lists,'id');

        $codeArray  = M('users_agent_code')->where(array('uid'=>array('in',$uidArray)))->field('uid,code')->select();
        $codeInfoArray = array_column($codeArray,null,'uid');

        $shotLikeNumber  = M('video')->where(array('uid'=>array('in',$uidArray)))->group('uid')->field('uid,sum(likes) as likes_number')->select();
        if ($shotLikeNumber){
            $shotLikeNumberByUid = array_column($shotLikeNumber,null,'uid');
        }
        $longLikeNumber  = M('video_long')->where(array('uid'=>array('in',$uidArray)))->group('uid')->field('uid,sum(likes) as likes_number')->select();

        if ($longLikeNumber){
            $longLikeNumberByUid = array_column($longLikeNumber,null,'uid');
        }

        foreach ($lists as $key =>$value){
            $shotNumber =  isset($shotLikeNumberByUid[$value['id']])?$shotLikeNumberByUid[$value['id']]['likes_number']:0;
            $longNumber =  isset($shotLikeNumberByUid[$value['id']])?$longLikeNumberByUid[$value['id']]['likes_number']:0;
            $lists[$key]['likes'] = $shotNumber+$longNumber;
            $lists[$key]['code'] = $codeInfoArray[$value['id']]['code'];

            $lists[$key]['user_nicename']  =removeEmoji($value['user_nicename']);
           // $lists[$key]['user_nicename'] =  iconv("gb2312//ignore", "utf-8", iconv('utf-8','gb2312//ignore',$value['user_nicename']));;
        }
        $xlsName = "Excel";
        /*   $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H','I','J','K','L');
          */

        $header=array(
            'title' => array(
                'id'=>'ID:15',
                'user_login'=>'用户名:30',
                'user_nicename'=>'昵称:30',
                'last_login_ip'=>'最后登录ip',
                'create_time'=>'注册时间',
                'last_login_time'=>'最后的登录时间:20',
                'coin'=>'余额:20',
                'yeb_balance'=>'余额宝:20',
                'recharge_total'=>'充值金额:15',
                'follows'=>'关注数:15',
                'fans'=>'粉丝数:15',
                'likes'=>'作品点赞数:15',
                'code'=>'邀请码:15',

            ),
        );

        $filename="用户";
        $return_url = count($lists) > 10000 ? true : false;
        $excel_filname = $return_url == true ? $filename.'-'.md5(json_encode($map)) : $filename." (".count($lists)."条)-".date('Y-m-d H-i-s');
        include EXTEND_PATH ."util/UtilPhpexcel.php";
        $Phpexcel = new \UtilPhpexcel();
        $excel_filname = iconv("utf-8", "gb2312", $excel_filname);
        $downurl = "/".$Phpexcel::export_excel_v2($lists, $header,$excel_filname, $return_url);
        if($downurl){
            $output_filename = $filename." (".count($lists)."条)-".date('Y-m-d H-i-s');
            header('pragma:public');
            header("Content-Disposition:attachment;filename=".$output_filename.".xls"); //下载文件，filename 为文件名
            echo file_get_contents($downurl);
            exit;
        }

        $this->success("导出成功",$downurl);

    }

    public function fansexport(){
        $cellName = array('A','B','C',);
        $xlsCell  = array(
            array('id','用户id'),
            array('fans',"增加粉丝数"),
            array('follows','增加关注数'),

        );
        $xlsName = 'user_add_fans_add_follows';
        $xlsData = [];
        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
    }
    public function fansimport(){
        $tmp_file = $_FILES ['file'] ['tmp_name'];


        $type = strstr( $_FILES ['file']['name'],'.');
        if ($type != '.xls' && $type != '.xlsx') {
            $this->error('请上传excel文件');
        }
        if (is_uploaded_file($tmp_file)) {

            /*设置上传路径*/
            $savePath = './data/upload/';
            /*以时间来命名上传的文件*/
            $str = date('Ymdhis');
            $file_name = $str . "." . $type;
            /*是否上传成功*/
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $savePath . $file_name)) {
                $this->error('上传失败');
            }
            vendor("PHPExcel.PHPExcel.IOFactory");
            $iofactory = new \PHPExcel_IOFactory();
            $objReader = $iofactory::createReaderForFile($savePath . $file_name);
            $objPHPExcel = $objReader->load($savePath . $file_name);
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $data = array();
            M("users")->startTrans();

            for ($row = 2; $row <= $highestRow; $row++) {
                $data['id']  = $sheet->getCellByColumnAndRow(0, $row)->getValue();
                if (!$data['id']){
                    M("users")->rollback();
                    $this->error('第'.$row.'行，用户不能为空,请编辑后在导入');
                }

                $userInfo = M("users")->where(['id'=>$data['id']  ])->find();
                if (!$userInfo){
                    M("users")->rollback();
                    $this->error('第'.$row.'行，'.$data['id'].'不存在,请编辑后在导入');
                }
                $fans = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                $follows = $sheet->getCellByColumnAndRow(2, $row)->getValue();
                if (!is_numeric($fans)){
                    M("users")->rollback();
                    $this->error('第'.$row.'行，'.'粉丝数不为数字,请编辑后在导入');
                }
                if (!is_numeric($follows)){
                    M("users")->rollback();
                    $this->error('第'.$row.'行，'.'关注数不为数字,请编辑后在导入');
                }
               $result =  M("users")->where(['id'=>$data['id']  ])->save(
                   array('fans'=>array('exp','fans+'.$fans),
                    'follows'=>array('exp','follows+'.$follows))
                );
                delUserInfoCache($data['id']);
               if ($result === false){
                   M("users")->rollback();
               }
            }

        }


        M("users")->commit();
        $this->success('导入成功');
    }

    public  function add_bank_card(){
        $id = $_REQUEST['id'];
        $bankList = M('bank')->select();
        if (IS_POST){
            $bank_number = $_POST['bank_number'];
            if($bank_number==""||!is_numeric($bank_number)){
                return;   $this->error("请填写正确的银行卡号，");
            }
            $phone = $_POST['phone'];
            if (strlen($phone)>11){
                return;   $this->error("手机号码不能超过11位");
            }
            if($phone==""||!is_numeric($phone)){
                return;   $this->error("请填写正确的银行卡号，");
            }
            $real_name = $_POST['real_name'];
            if($real_name==""){
                return;   $this->error("请填写真实姓名");
            }
            $userBank  = M('bank_card')->where(['uid'=>$_POST['uid'] ,'status'=>1])->find();

            $infoBank  = M('bank_card')->where(['bank_number'=>$bank_number ,'status'=>1])->find();
            if ($infoBank){
                $this->error('此银行卡已被绑定');
            }
            if ($userBank){
                if($userBank['real_name'] != $real_name){
                    $this->error('银行真实姓名与以前绑定的不一致');
                }
            }
            $bankByIdList = array_column($bankList,null,'id');
            $data = [
                'tenant_id' => getTenantIds(),
                'uid' => $_POST['uid'],
                'phone' => $phone,
                'bank_number' => $bank_number,
                'real_name' => $real_name,
                'addtime' => time(),
                'bank_name' => $bankByIdList[$_POST['bank_id']]['bank_name'],
                'bank_id' => $_POST['bank_id'],
                'status' => 1,
            ];
            $result  = M('bank_card')->add($data);
           $addId =  M('bank_card')->getLastInsID();
            if ($result !== false){
                $action="添加银行卡：{$addId}";
                setAdminLog($action);
                return $this->success('添加成功');
            }else{
                return $this->error('添加失败');
            }
        }
        $this->assign('bank_list',$bankList);

        $this->assign('id',$id);
        $this->display(":add_bank_card");

    }

    public function edit_bank_card(){
        $id = $_REQUEST['id'];
        $bankList = M('bank')->select();
        $bank_card_info  = M('bank_card')->where(['id'=> $id])->find();
        if (IS_POST){
            $bank_number = $_POST['bank_number'];
            if($bank_number==""||!is_numeric($bank_number)){
                return;   $this->error("请填写正确的银行卡号，");
            }
            $phone = $_POST['phone'];
            if($phone==""||!is_numeric($phone)){
                return;   $this->error("请填写正确的银行卡号");
            }
            if (strlen($phone)>11){
                return;   $this->error("手机号码不能超过11位");
            }
            $real_name = $_POST['real_name'];
            if($real_name==""){
                return;   $this->error("请填写真实姓名");
            }
            $userBank  = M('bank_card')->where(['uid'=>$id ,'status'=>1])->find();
            $infoBank  = M('bank_card')->where(['bank_number'=>$bank_number ,'status'=>1,'id'=> ['neq',$id]])->find();
            if ($infoBank){
                $this->error('此银行卡已被绑定');
            }
            if ($userBank){
                if($userBank['real_name'] != $real_name){
                    $this->error('银行真实姓名与以前绑定的不一致');
                }
            }
            $bankByIdList = array_column($bankList,null,'id');
            $data = [
                'tenant_id' => getTenantIds(),
                'uid' => $_POST['uid'],
                'phone' => $phone,
                'bank_number' => $bank_number,
                'real_name' => $real_name,
                'addtime' => time(),
                'bank_name' => $bankByIdList[$_POST['bank_id']]['bank_name'],
                'bank_id' => $_POST['bank_id'],
                'status' => 1,
            ];

            $result  = M('bank_card')->where(['id'=> $id])->save($data);
            if ($result !== false){
                $action="修改银行卡：{$id}";
                setAdminLog($action);
                return $this->success('修改成功');
            }else{
                return $this->error('修改失败');
            }
        }

        $this->assign('bank_list',$bankList);
        $this->assign('id',$id);
        $this->assign('bank_card_info',$bank_card_info);
        $this->display(":edit_bank_card");



    }

    /*
     * 设置 上传视频收益 状态
     * */
    public function upload_video_profit_status(){
        if(!IS_AJAX){
            $this->error('请求方式错误');
        }
        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            $this->error('参数id错误');
        }
        if(!isset($param['val'])){
            $this->error('缺失参数val');
        }
        $data = ['operate_name'=>get_current_admin_user_login(), 'mtime'=>time(), 'upload_video_profit_status'=>intval($param['val'])];
        $actioin = $param['val'] == 1 ? '【开启上传视频收益】' : '【关闭上传视频收益】';
        try {
            M("Users")->where(array("id"=>intval($param['id'])))->save($data);
        }catch (\Exception $e){
            setAdminLog( $actioin.'失败 | '.json_encode($param), 3);
            $this->error('"操作失败');
        }
        setAdminLog($actioin.'成功 | '.json_encode($param), 3);
        $this->success("操作成功");
    }

    /*
     * 设置 抢红包 状态
     * */
    public function grab_red_packet_status(){
        if(!IS_AJAX){
            $this->error('请求方式错误');
        }
        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            $this->error('参数id错误');
        }
        if(!isset($param['val'])){
            $this->error('缺失参数val');
        }
        $data = ['operate_name'=>get_current_admin_user_login(), 'mtime'=>time(), 'grab_red_packet_status'=>intval($param['val'])];
        $actioin = $param['val'] == 1 ? '【开启抢红包】' : '【关闭抢红包】';
        try {
            M("Users")->where(array("id"=>intval($param['id'])))->save($data);
        }catch (\Exception $e){
            setAdminLog( $actioin.'失败 | '.json_encode($param), 3);
            $this->error('"操作失败');
        }
        setAdminLog($actioin.'成功 | '.json_encode($param), 3);
        $this->success("操作成功");
    }


    /*
     * 设置 代理返点 状态
     * */
    public function rebate_status(){
        if(!IS_AJAX){
            $this->error('请求方式错误');
        }
        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            $this->error('参数id错误');
        }
        if(!isset($param['val'])){
            $this->error('缺失参数val');
        }
        $data = ['operate_name'=>get_current_admin_user_login(), 'mtime'=>time(), 'rebate_status'=>intval($param['val'])];
        $actioin = $param['val'] == 1 ? '【开启代理返点】' : '【关闭代理返点】';
        try {
            M("Users")->where(array("id"=>intval($param['id'])))->save($data);
        }catch (\Exception $e){
            setAdminLog( $actioin.'失败 | '.json_encode($param), 3);
            $this->error('"操作失败');
        }
        setAdminLog($actioin.'成功 | '.json_encode($param), 3);
        $this->success("操作成功");
    }


}
