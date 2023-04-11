<?php

/**
 * VIP管理
 */
namespace Admin\Controller;
use Admin\Model\UsersModel;
use Common\Controller\AdminbaseController;
use Common\Controller\CustRedis;

class VipController extends AdminbaseController {

	public $long=array(
		'1'=>'1个月',
		'3'=>'3个月',
		'6'=>'6个月',
		'12'=>'12个月',
	);

    private $user_vip_status_list = array(
        '4' => array(
            'name' => '审核中',
            'color' => '#f00',
        ),
        '1' => array(
            'name' => '生效中',
            'color' => '#090',
        ),
        '2' => array(
            'name' => '申请退款中',
            'color' => '#F60',
        ),
        '3' => array(
            'name' => '已退款',
            'color' => '#999',
        ),
    );

    private $user_vip_action_type_list = array(
        '1' => array(
            'name' => '购买',
            'color' => '#090',
        ),
        '2' => array(
            'name' => '升级',
            'color' => '#2196f3',
        ),
    );

    function index(){	
    	$Vip=M("vip");
    	$count=$Vip->where('tenant_id="'.getTenantIds().'"')->count();
    	$page = $this->page($count, 20);
    	$lists = $Vip
    	->order("orderno asc")
    	->limit($page->firstRow . ',' . $page->listRows)
            ->where('tenant_id="'.getTenantIds().'"')
    	->select();
        $this->assign('lists', $lists);
    	$this->assign('lists', $lists);
    	$this->assign('long', $this->long);
    	$this->assign("page", $page->show('Admin'));

    	$this->display();
    }

    public  function vip_grade(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = intval($tenant_id);
        if(isset($param['vip_grade']) && $param['vip_grade'] != ''){
            $map['vip_grade'] = $param['vip_grade'];
        }

        $count = M("vip_grade")->where($map)->count();
        $page = $this->page($count);
        $lists = M("vip_grade")
            ->order('vip_grade')
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $vip_grade_list = getVipGradeList($tenant_id);
        foreach ($lists as $key=>$val){
            $lists[$key]['upgrade_need_sub_user_vip_grade_name'] = $vip_grade_list[$val['upgrade_need_sub_user_vip_grade']] ? $vip_grade_list[$val['upgrade_need_sub_user_vip_grade']]['name'] : '';
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign('long', $this->long);
        $this->assign('config',getConfigPub($tenant_id));
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->display();
    }


    public  function vip_grade_add(){
        $param = I('param.');
        $id=I("id");
        $vip_grade_info = [];
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        if ($id){
            $vip_grade_info = M("vip_grade")->where(['id' => $id, 'tenant_id'=>intval($tenant_id)])->find();
        }

        $this->assign('config', getConfigPub($tenant_id));
        $this->assign('vip_grade_info', $vip_grade_info);
        $this->assign('vip_grade_list', getVipGradeList($tenant_id));
        $this->assign('id', $id);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('tenant_id',$tenant_id);
        $this->display();
    }
    public  function vip_grade_add_post(){
        if(IS_POST){
            $param = I('param.');
            $id=I("id");
            $vip_grade=I("vip_grade");
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            if (!$id){
                $Vip=M("vip_grade");
                $Vipjurisdiction=M("users_jurisdiction");
                $name=I("name");

                $is_super_member=I("is_super_member");
                $isexist=$Vip->where("name='{$name}' and tenant_id='{$tenant_id}' ")->find();

                $vip_grade =$Vip->where("vip_grade='{$vip_grade}'  and tenant_id='{$tenant_id}'  ")->find();
                if($isexist || $vip_grade ){
                    $this->error('已存在相同类型 等级会员');
                }
                $Vip->create();
                if ( I("uplode_video_num") == ''){
                    $Vip->uplode_video_num = 0;
                }
                $Vip->tenant_id = $tenant_id;
                $Vip->operation_by = get_current_admin_user_login();
                $result=$Vip->add();
                $Vipjurisdiction->add(array('grade'=>$name,'vip_grade_id' =>$result,'tenant_id' =>$tenant_id ));

                if($result!==false){
                    $action="添加保证金：{$result}";
                    setAdminLog($action);
                    delVipGradeListCache($tenant_id);
                    $this->success('添加成功', U('vip_grade'));
                }else{
                    $this->error('添加失败');
                }
            }else{
                $Vip=M("vip_grade");
                $Vipjurisdiction=M("users_jurisdiction");
                $name=I("name");
                $is_super_member=I("is_super_member");
                if (I("price") ){
                    $price = I("price");
                }else{
                    $price  = 0;
                }
                $info = M("vip_grade")->where(['id'=>intval($id)])->find();
                $result = $Vip->where(array('id' =>$id ))->save(array(
                        'operation_by' => get_current_admin_user_login(),
                        'name'=>$name,
                        'is_super_member' =>$is_super_member,
                        'uplode_video_num'=> I("uplode_video_num"),
                        'uplode_video_amount'=> I("uplode_video_amount"),
                        'price' => $price,
                        'status'=>I("status"),
                        'upgrade_need_sub_user_vip_count' => intval(I("upgrade_need_sub_user_vip_count")),
                        'upgrade_need_sub_user_vip_grade' => intval(I("upgrade_need_sub_user_vip_grade")),
                        'video_upload_reward_type' => intval(I("video_upload_reward_type")),
                        'nft_rate'=>I("nft_rate"),
                    ));
                $Vipjurisdiction->where(array('vip_grade_id' =>$id ))->save(array('grade'=>$name ));
                M("vip")->where(array('orderno' =>$vip_grade ))->save(
                    array('name'=>$name,'is_super_member' =>$is_super_member ));// orderno 对应vip等级
                if($result!==false){
                    $action="编辑保证金：{$result}";
                    setAdminLog($action);
                    delVipGradeListCache($info['tenant_id']);
                    $viplist = M("vip")->where(array('orderno' =>$vip_grade ))->field('id')->select();
                    foreach ($viplist as $key=>$value){
                        $this->delVipInfoCache($value['id']);
                    }
                    $this->success('修改成功', U('vip_grade'));
                }else{
                    $this->error('修改失败');
                }
            }
        }
    }
	function del(){
		$id=intval($_GET['id']);
		if($id){
			$result=M("vip")->delete($id);				
				if($result){
                    $action="删除VIP：{$id}";
                    setAdminLog($action);
                    $this->delVipInfoCache($id);
                    $this->success('删除成功');
				 }else{
						$this->error('删除失败');
				 }			
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display();				
	}		
    //排序
    public function listorders() { 
		
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("vip")->where(array('id' => $key))->save($data);
            $this->delVipInfoCache($key);
        }
				
        $status = true;
        if ($status) {
            $action="更新VIP排序";
            setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }	
    

	function add(){
        $this->assign('long', $this->long);
        $vip_grade=M("vip_grade");
        $vip_grade_list = $vip_grade->where('tenant_id="'.getTenantIds().'"')->select();
        $this->assign('vip_grade_list', $vip_grade_list);
        $this->display();
	}	
	function add_post(){
		if(IS_POST){			
			$Vip=M("vip");
			$name=I("name");
            $length=I("length");
            $tenantIds = getTenantIds();
			$isexist=$Vip->where("name='{$name}' and  length = '{$length}'  and tenant_id='{$tenantIds}'  ")->find();

			/*if ($name == 'vip1'){
			    $orderno = 1;
                $is_super_member= 0;
            }elseif ($name == 'vip2'){
                $orderno = 2;
                $is_super_member= 0;
            }else{
                $orderno = 3;
                $is_super_member= 1;
            }*/

            $vip_grade_list = M("vip_grade")->where(array('name'=>$name))->find();
            $is_super_member = $vip_grade_list['is_super_member'];
            $orderno = $vip_grade_list['vip_grade'];;
			if($isexist){
				$this->error('已存在相同类型 等级会员');
			}

			 $Vip->create();
			 $Vip->addtime=time();
			 $Vip->orderno =$orderno;
            $Vip->tenant_id =$tenantIds;
			 $Vip->is_super_member =$is_super_member;
			 $result=$Vip->add(); 
			 if($result!==false){
                $action="添加VIP：{$result}";
                setAdminLog($action);
                $this->success('添加成功');
			 }else{
                $this->error('添加失败');
			 }
		}			
	}		
	function edit(){
		$id=intval($_GET['id']);
		if($id){
			$vip=M("vip")->find($id);
			$this->assign('vip', $vip);
			$this->assign('long', $this->long);
		}else{				
			$this->error('数据传入失败！');
		}
        $vip_grade_list = M("vip_grade")->where('tenant_id="'.getTenantIds().'"')->select();
        $this->assign('vip_grade_list', $vip_grade_list);
		$this->display();				
	}
	
	function edit_post(){
		if(IS_POST){	
			$Vip=M("vip");

            $name=I("name");
            $length=I("length");
            $id=I("id");
            $isexist=$Vip->where("name='{$name}' and  length = '{$length}' and id!='{$id}'")->find();
            if($isexist){
                $this->error('已存在相同类型 等级会员');
            }
            $vip_grade_list = M("vip_grade")->where(array('name'=>$name))->find();
            $is_super_member = $vip_grade_list['is_super_member'];
            $orderno = $vip_grade_list['vip_grade'];;


            $Vip->create();
            $Vip->orderno =$orderno;
            $Vip->is_super_member =$is_super_member;
			$result=$Vip->save(); 
			if($result!==false){
                $action="修改VIP：{$id}";
                setAdminLog($action);
                $this->delVipInfoCache($id);
				$this->success('修改成功');
			}else{
				$this->error('修改失败');
			}
		}			
	}
		
    function user_index(){
        $param = I('param.');

        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid']=$param['uid'];
        }
        if(isset($param['status']) && $param['status'] != '-1'){
            $map['status']=$param['status'];
        }else{
            $param['status'] = '-1';
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        if($param['user_login']!=''){
            $userIdArray = M('users')
                ->where(array('user_login'=> $param['user_login']))
                ->field('id')
                ->select();
            if(!empty($userIdArray)){
                $uid = $userIdArray[0]['id'];
                $map['uid']=$uid;
            }
        }
        $model = M("users_vip");
        $tenantId=getTenantIds();
        $map['tenant_id'] = $tenantId;
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }else{
            $map['tenant_id'] = getTenantIds();
        }
        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
        $this->assign('config',$config);
        $count = $model->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $model
            ->order("endtime desc")
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $status_list = $this->user_vip_status_list;
        $action_type_list = $this->user_vip_action_type_list;
		foreach($lists as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
			$lists[$key]['userinfo'] = $userinfo;
            $lists[$key]['name'] = getVipGradeList($val['tenant_id'])[$val['grade']]['name'];
            $lists[$key]['status_name'] = '<span style="color: '.$status_list[$val['status']]['color'].';">'.$status_list[$val['status']]['name'].'</span>';
            $lists[$key]['price'] = floatval($val['price']);
            $lists[$key]['actual_amount'] = floatval($val['actual_amount']);
            $lists[$key]['action_type_name'] = '<span style="color: '.$action_type_list[$val['action_type']]['color'].';">'.$action_type_list[$val['action_type']]['name'].'</span>';
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

    	$this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param', $param);
        $this->assign('status_list', $status_list);
        $this->assign('user_type_list',user_type_list());
    	$this->display();
    }

    function user_del(){
		$id=intval($_GET['id']);
        $tenantId=getTenantIds();
		if($id){
            $data	=M("users_vip")->where("id={$id} and tenant_id={$tenantId}")->find();
			$result=M("users_vip")->where("id=%d and tenant_id=%d",$id,$tenantId)->delete();
			if($result){
                $action="删除用户VIP：{$data['uid']}";
                setAdminLog($action);
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}						
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display();				
	}		
			
    function user_add(){	
    	$this->display();
    }		
	function do_user_add(){

		if(IS_POST){	
			$uid=$_POST['uid'];
            $tenantId=getTenantIds();
            if($uid==''){
				$this->error('用户ID不能为空');
			}
			$isexist=M("users")->field("id")->where("id={$uid} and tenant_id={$tenantId}")->find();
			if(!$isexist){
				$this->error('该用户不存在');
			}
			
			$Vip_u=M("users_vip");
			$isexist2=$Vip_u->field("id")->where("uid={$uid}")->find();
			if($isexist2){
				$this->error('该用户已购买过会员');
			}
			
			$Vip_u->create();
			$Vip_u->tenant_id=$tenantId;
			$Vip_u->addtime=time();
			$Vip_u->endtime=strtotime($_POST['endtime']);
			$result=$Vip_u->add(); 
			if($result!==false){
                $action="添加用户VIP：{$uid}";
                setAdminLog($action);
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}				
    }		
    function user_edit(){

		$id=intval($_GET['id']);
        $tenantId=getTenantIds();
		if($id){
			$data	=M("users_vip")->where("id={$id} and tenant_id={$tenantId}")->find();
			$data['userinfo']=getUserInfo($data['uid']);
			if($data['userinfo']['tenant_id'] ===$tenantId){
                $this->assign('data', $data);
            }
			else{
                $this->error('数据传入失败！');
            }
		}else{				
			$this->error('数据传入失败！');
		}								      	
    	$this->display();
    }			
	function do_user_edit(){
		if(IS_POST){			
			$Vip_u=M("users_vip");
            $tenantId=getTenantIds();
            $data	=$Vip_u->where("id={$_POST['id']} and tenant_id={$tenantId}")->find();
            
			$Vip_u->create();
			$Vip_u->where("id=%d and tenant_id=%d",$_POST['id'],$tenantId);
			$Vip_u->endtime=strtotime($_POST['endtime']);
			$result=$Vip_u->save(); 
			if($result!==false){
                $action="修改用户VIP：{$data['uid']}";
                setAdminLog($action);
				$this->success('修改成功');
			}else{
				$this->error('修改失败');
			}
		}	
    }

    public  function vipjurisdiction(){
        $Vip=M("vip");
        $tenant_id = getTenantIds();
        $config = getConfigPri($tenant_id);
        $startVip = array('name'=> 'vip0');
        $lists  = M("vip_grade")->order('vip_grade')->where(['tenant_id'=> intval($tenant_id), 'status'=>1])->select();

        array_unshift($lists,$startVip);
        foreach ($lists as $list_key => $list_value){
            $vip_one = M('users_jurisdiction')->where(['tenant_id'=> intval($tenant_id), 'grade' =>$list_value['name']])->find();
            $lists[$list_key]['users_jurisdiction_id'] = $vip_one['jurisdiction_id'];
            $lists[$list_key]['jurisdiction_id'] = explode(',',$vip_one['jurisdiction_id']);
            $lists[$list_key]['watch_number'] = $vip_one['watch_number'];
            $lists[$list_key]['watch_duration'] = $vip_one['watch_duration'];
            $lists[$list_key]['watchnum_ad'] = $vip_one['watchnum_ad'];
            $lists[$list_key]['bar_number'] = $vip_one['bar_number'];
            $lists[$list_key]['bar_slice_number'] = $vip_one['bar_slice_number'];
            $lists[$list_key]['watchnum_show_ad_video'] = $vip_one['watchnum_show_ad_video'];
            $lists[$list_key]['limit_upload_video_count'] = $vip_one['limit_upload_video_count'];
        }

        $first_menu  = M('reception_meun')->where(array('parentid' => 0,'id'=>array('neq',18)))->select();

        foreach ($first_menu as $first_menu_value){
            $second_menu[$first_menu_value['id']] = M('reception_meun')->where(array('parentid'=>$first_menu_value['id']))->select();
        }
        foreach ($second_menu as $second_menu_value){
            foreach($second_menu_value as $son_menu_value){
                $third_menu[$son_menu_value['id']]  = M('reception_meun')->where(array('parentid' =>$son_menu_value['id'] ))->select();
            }

        }

        $this->assign('lists', $lists);
        $this->assign('config', $config);
        $this->assign('first_menu', $first_menu);
        $this->assign('second_menu', $second_menu);
        $this->assign('third_menu', $third_menu);
        $this->assign('tenant_id', $tenant_id);
        $this->display();
    }

    public  function vipjurisdictionset(){
        $param = I('post.');
        if(!isset($param['tenant_id']) || !$param['tenant_id']){
            $this->error('参数错误');
        }
        try{
            M()->startTrans();
            $Vip=M("vip");
            $startVip = array('name'=> 'vip0');
            $lists =   M("vip_grade")->where(['tenant_id'=>intval($param['tenant_id']), 'status'=>1])->select();
            array_unshift($lists,$startVip);
            foreach ($lists as $value){
                $data['operated_by'] = get_current_admin_user_login();
                $data['jurisdiction_id'] = implode(',',$_POST[$value['name']]);
                $data['watch_number'] = $_POST[$value['name'].'_number'];
                $data['watch_duration'] = $_POST[$value['name'].'_duration'];
                $data['watchnum_ad'] = $_POST[$value['name'].'_watchnum_ad'];
                $data['bar_number'] = $_POST[$value['name'].'_bar_number'];
                $data['bar_slice_number'] = $_POST[$value['name'].'_bar_slice_number'];
                $data['watchnum_show_ad_video'] = $_POST[$value['name'].'_watchnum_show_ad_video'];
                if(isset($_POST[$value['name'].'_limit_upload_video_count'])){
                    $data['limit_upload_video_count'] = $_POST[$value['name'].'_limit_upload_video_count'];
                }

                if($data['watch_number'] > 9999){
                    M()->rollback();
                    $this->error('操作失败，弹广告限制数量不能大于 9999');
                }
                if($data['watchnum_show_ad_video'] > 9999){
                    M()->rollback();
                    $this->error('操作失败，弹广告视频限制数量不能大于 9999');
                }
                if (empty( $data['jurisdiction_id'])){
                    $data['jurisdiction_id'] ='';
                }
                if($_POST[$value['name'].'_users_jurisdiction_id']){
                    $data['update_time'] = time();
                    M('users_jurisdiction')->where(['tenant_id'=> intval($param['tenant_id']), 'grade'=>$value['name']])->save($data);
                }else{
                    $data['create_time'] = time();
                    $data['tenant_id'] = getTenantIds();
                    $data['grade'] = $value['name'];
                    M('users_jurisdiction')->where(['tenant_id'=> intval($param['tenant_id']), 'grade'=>$value['name']])->add($data);
                }
            }
            M()->commit();
        }catch (\Exception $e){
            M()->rollback();
            setAdminLog('vip权限管理修改失败：'.$e->getMessage());
            $this->error('操作失败');
        }
        $this->success('操作成功');
    }

    /* 清除vip详情缓存 */
    public function delVipInfoCache($vip_id){
        $redis=connectionRedis();
        $res = $redis->del('vipinfo_'.$vip_id);
        return $res;
    }

    /*
     * 购买vip, 审核通过，或者拒绝
     * */
    public function user_vip_check(){
        if(IS_AJAX){
            $param = I('param.');
            $status = intval($param['status']);
            $id = intval($param['id']);
            $redis = connectRedis();
            $user_vip_check_action = $redis->get('user_vip_check_action_'.$id);
            if ($user_vip_check_action){
                $this->error('有人在操作，请稍后再操作或者等待1小时后再操作');
            }else{
                $redis->set('user_vip_check_action_'.$id, get_current_admin_id(), 60*60);
            }

            $info = M("users_vip")->where(['id' => $id])->find();
            if(!$info){
                $this->error('参数错误');
            }
            if ($info['status'] != 4){
                $redis->del('user_vip_check_action_'.$id);
                $this->error('状态已修改！');
            }

            try {
                M()->startTrans();
                if ($status == 1){
                    M("users_vip")->where(['id' => $id])->save(['status'=>1, 'operated_by' => get_current_admin_user_login(), 'updated_time' => time()]);
                    M("users_vip")->where(['uid' => intval($info['uid']), 'id'=>['neq',$id]])->save(['status'=>3]);
                    setAdminLog("【审核通过用户vip】".json_encode($param),9);
                    $redis->del('user_vip_check_action_'.$id);
                }else{
                    $userInfo = UsersModel::getInstance()->getUserInfoWithIdAndTid($info['uid']);
                    if(!$userInfo){
                        $this->error('用户不存在');
                    }
                    M("users_vip")->where(['id' => $id])->save(['status'=>3, 'operated_by' => get_current_admin_user_login(), 'updated_time' => time()]);
                    M('users')->where(['id' => $info['uid']])->save(['vip_margin' => ['exp', 'vip_margin-' . $info['actual_amount']], 'coin' => ['exp', 'coin+' . $info['actual_amount']]]);
                    $coinrecordData = [
                        'type' => 'income',
                        'uid' => $info['uid'],
                        'user_login' => $userInfo['user_login'],
                        'user_type' => $userInfo['user_type'],
                        'giftid' => $id,
                        'addtime' => time(),
                        'tenant_id' => $userInfo['tenant_id'],
                        'action' => 'vip_refund',
                        "pre_balance" => floatval($userInfo['coin']),
                        'totalcoin' => $info['actual_amount'], // 金额
                        "after_balance" => floatval(bcadd($userInfo['coin'], $info['actual_amount'],4)),
                        "giftcount" => 1,
                    ];
                    $this->addCoinrecord($coinrecordData);
                    delUserInfoCache($info['uid']);
                    setAdminLog("【审核通过用户vip】拒绝, 退回用户保证金".json_encode($param),9);
                    $redis->del('user_vip_check_action_'.$id);
                }
                M()->commit();
            }catch (\Exception $e){
                M()->rollback();
                setAdminLog("【审核通过用户vip】失败: ".$e->getMessage(),9);
                $redis->del('user_vip_check_action_'.$id);
                $this->error('操作失败');
            }
            CustRedis::getInstance()->hDel('user_vip_info_'.$info['tenant_id'], $info['uid']);
            CustRedis::getInstance()->hDel('user_vip_checking_info_'.$info['tenant_id'], $info['uid']);
            delUserVipInfoCache($info['tenant_id'], $info['uid']);
            $this->success("操作成功！");
        }else{
            $this->error('请求方式错误');
        }
    }

    /*
    * 申请退款, 审核通过，或者拒绝
    * */
    public function refundVip(){
        $id=intval($_GET['id']);
        $status = intval($_GET['status']);

        if($id){
            $redis = connectRedis();
            $vipId = $redis->get('vip_'.$id);
            if ($vipId){
                $this->error('状态有误！');
            }else{
                 $redis->set('vip_'.$id,time());
            }
            $info = M("users_vip")->where(['id' => $id])->find();
            if ($info['status']!= 2){
                $redis->del('vip_'.$id);
                $this->error('保证金状态已修改！');
            }
            if ($status ==3 ){
                $userInfo = UsersModel::getInstance()->getUserInfoWithIdAndTid($info['uid']);
                if(!$userInfo){
                    $this->error('用户不存在');
                }
                M("users_vip")->where(['id' => $id])->save(['status'=>3, 'operated_by' => get_current_admin_user_login(), 'updated_time' => time()]);
                M('users')->where(['id' => $info['uid']])->save(['vip_margin' => ['exp', 'vip_margin-' . $info['price']], 'coin' => ['exp', 'coin+' . $info['price']]]);
                $coinrecordData = [
                    'type' => 'income',
                    'uid' => $info['uid'],
                    'user_login' => $userInfo['user_login'],
                    'user_type' => $userInfo['user_type'],
                    'giftid' => $id,
                    'addtime' => time(),
                    'tenant_id' => $userInfo['tenant_id'],
                    'action' => 'vip_refund',
                    "pre_balance" => floatval($userInfo['coin']),
                    'totalcoin' => $info['price'],//金额
                    "after_balance" => floatval(bcadd($userInfo['coin'], $info['price'],4)),
                    "giftcount" => 1,
                ];
                $this->addCoinrecord($coinrecordData);
                $action="退回用户保证金：{$id}";
                delUserInfoCache($info['uid']);
                setAdminLog($action);
                $redis->del('vip_'.$id);
            }else{
                M("users_vip")->where(['id' => $id])->save(['status'=>1, 'operated_by' => get_current_admin_user_login(), 'updated_time' => time()]);
                $action="拒绝退回用户保证金：{$id}";
                setAdminLog($action);
                $redis->del('vip_'.$id);
            }
            CustRedis::getInstance()->hDel('user_vip_info_'.$info['tenant_id'], $info['uid']);
            CustRedis::getInstance()->hDel('user_vip_checking_info_'.$info['tenant_id'], $info['uid']);
        }else{
            $this->error('数据传入失败！');
        }
        delUserVipInfoCache($info['tenant_id'], $info['uid']);
        $this->success("操作成功！");
    }

    public  function vip_longgrade(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = intval($tenant_id);
        if(isset($param['vip_grade']) && $param['vip_grade'] != ''){
            $map['vip_grade'] = $param['vip_grade'];
        }

        $count = M("vip_longgrade")->where($map)->count();
        $page = $this->page($count);
        $lists = M("vip_longgrade")
            ->order('vip_grade')
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();



        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign('long', $this->long);
        $this->assign('config',getConfigPub($tenant_id));
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->display();
    }
    public  function vip_longgrade_add(){
        $param = I('param.');
        $id=I("id");
        $vip_grade_info = [];
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        if ($id){
            $vip_grade_info = M("vip_longgrade")->where(['id' => $id, 'tenant_id'=>intval($tenant_id)])->find();
        }

        $this->assign('config', getConfigPub($tenant_id));
        $this->assign('vip_grade_info', $vip_grade_info);
        $this->assign('vip_grade_list', getVipGradeList($tenant_id));
        $this->assign('id', $id);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('tenant_id',$tenant_id);
        $this->display();
    }
    public  function vip_longgrade_add_post(){

        if(IS_POST){
            $param = I('param.');
            $id=I("id");
            $vip_grade=I("vip_grade");
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            if (!$id){
                $Vip=M("vip_longgrade");

                $name=I("name");



                $vip_grade =$Vip->where("vip_grade='{$vip_grade}'  and tenant_id='{$tenant_id}'  ")->find();
                if( $vip_grade ){
                    $this->error('已存在相同类型 等级会员');
                }
                $Vip->create();
                $Vip->tenant_id = $tenant_id;
                $Vip->operation_by = get_current_admin_user_login();
                $result=$Vip->add();
                if($result!==false){
                    $this->success('添加成功', U('vip_longgrade'));
                }else{
                    $this->error('添加失败');
                }
            }else{
                $Vip=M("vip_longgrade");

                $name=I("name");

                if (I("price") ){
                    $price = I("price");
                }else{
                    $price  = 0;
                }
                $info = M("vip_longgrade")->where(['id'=>intval($id)])->find();
                $result = $Vip->where(array('id' =>$id ))->save(array(
                    'operation_by' => get_current_admin_user_login(),
                    'name'=>$name,
                    'is_forever_member' =>I("is_forever_member"),
                    'effect_days' =>I("effect_days"),
                    'price' => $price,
                    'status'=>I("status"),

                ));
                if($result!==false){

                    $this->success('修改成功', U('vip_longgrade'));
                }else{
                    $this->error('修改失败');
                }
            }
        }
    }

}
