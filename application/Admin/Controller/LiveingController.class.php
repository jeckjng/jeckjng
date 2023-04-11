<?php

/**
 * 直播记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Common\Controller\CustRedis;
use Admin\Cache\AutoLiveUserCache;

class LiveingController extends AdminbaseController {

    protected $auto_live_user_type_list = array(
        '1'=>'足球',
    );

    function index(){
        $param = I('param.');
        $map=array();
        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']!=''){
                $map['tenant_id'] = $param['tenant_id'];
            }else{
                $param['tenant_id'] = '';
            }
        }else{
            $tenant_id = getTenantIds();
            $param['tenant_id'] = $tenant_id;
            $map['tenant_id'] = $tenant_id;
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $config=getConfigPri();

        if($_REQUEST['start_time']!=''){
            $map['starttime']=array("gt",strtotime($_REQUEST['start_time']));
        }

        if($_REQUEST['end_time']!=''){
           $map['starttime']=array("lt",strtotime($_REQUEST['end_time'].' 23:59:59'));
        }
        if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
             $map['starttime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'].' 23:59:59')));
        }

        if($_REQUEST['uid']!=''){
             $map['uid']=$_REQUEST['uid'];
        }
        if($_REQUEST['game_user_id'] != ''){
            $map['game_user_id'] = $_REQUEST['game_user_id'];
        }

        if($_REQUEST['islive']!=''){
            $map['islive']=$_REQUEST['islive'];
        }
        if($_REQUEST['ishot']!=''){
            $map['ishot']=$_REQUEST['ishot'];
        }
        if($_REQUEST['isrecommend']!=''){
            $map['isrecommend']=$_REQUEST['isrecommend'];
        }
        if($_REQUEST['top']!=''){
            $map['top']=$_REQUEST['top'];
        }
        if($_REQUEST['ly_recommend']!=''){
            $map['ly_recommend']=$_REQUEST['ly_recommend'];
        }
        if($_REQUEST['game_recommend']!=''){
            $map['game_recommend']=$_REQUEST['game_recommend'];
        }
			
    	$live=M("users_live");
    	$Coinrecord=M("users_coinrecord");
    	$count=$live->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $live
    	->where($map)
    	->order("hotorderno,starttime DESC")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();

        // 屏蔽主播断开连接的直播间
        $disconnect_list = CustRedis::getInstance()->zRange('disconnect_'.$tenant_id,0,100000000);
        $disconnect_list = is_array($disconnect_list) ? $disconnect_list : array();

        $Livepushpull = new LivepushpullController();
        foreach($lists as $k=>$v){
             $userinfo = getUserInfo($v['uid']); // M("users")->field("user_nicename,avatar,game_user_id")->where("id='{$v['uid']}'")->find();
             if(!$v['thumb']){
                    $v['thumb']=get_upload_path($userinfo['avatar']);
             }
            $v['game_user_id'] = $userinfo['game_user_id'];
             $v['userinfo']=$userinfo;
             /* 本场总收益 */
             $totalcoin=$Coinrecord->where("type='expend' and touid={$v['uid']} and showid={$v['showid']} and action != 'bet' ")->sum('totalcoin');
             if(!$totalcoin){
                $totalcoin=0;
             }
             /* 送礼物总人数 */
             $total_info=$Coinrecord->where("type='expend' and touid={$v['uid']} and showid={$v['showid']} and action != 'bet' ")->select();
             $total_num = array();
             foreach ($total_info as $key=>$value){
                 if(!in_array($value['uid'],$total_num)){
                     $total_num[] = $value['uid'];
                 }
             }
             $total_nums = count($total_num);
             if(!$total_nums){
                $total_nums=0;
             }
             /* 人均 */
             $total_average=0;
             if($totalcoin && $total_nums){
                $total_average=round($totalcoin/$total_nums,2);
             }

             /* 人数 */
            $nums = CustRedis::getInstance()->zCard('user_'.$v['stream']);

            $v['totalcoin']=$totalcoin;
            $v['total_nums']=$total_nums;
            $v['total_average']=$total_average;
            $v['nums']=$nums;

            $pushpull_info = $Livepushpull->getPushpullInfoWithId($v['pushpull_id']);
            if($v['isvideo']==0 && !empty($pushpull_info)){
                $v['pull'] = $Livepushpull->PrivateKeyA('rtmp',$v['stream'],0,$pushpull_info);
                $v['flvpull'] = $Livepushpull->PrivateKeyA(get_protocal(),$v['stream'].'.flv',0,$pushpull_info);
                $v['m3u8pull'] = $Livepushpull->PrivateKeyA(get_protocal(),$v['stream'].'.m3u8',0,$pushpull_info);
            }
            $v['pushpull_name'] = isset($pushpull_info['name']) ? $pushpull_info['name'] : ($v['pushpull_id']==0 ? '-' : $v['pushpull_id']);

            if(!empty($v['tenant_id'])){
                $tenantInfo=getTenantInfo($v['tenant_id']);
                if(!empty($tenantInfo)){
                    $v['tenant_name']=$tenantInfo['name'];
                }
            }
            $config = getConfigPri($v['tenant_id']);
            $v['chatserver'] = $config['chatserver'];
            $v['socket_type'] = $config['socket_type'];
            $v['cuttitle'] = strlen($v['title']) > 40 ? substr($v['title'],0,40).'...' : $v['title'];
            $v['cutpull'] = strlen($v['pull']) > 100 ? substr($v['pull'],0,100).'...' : $v['pull'];
            $lists[$k]=$v;
            $lists[$k]['stop_time']  = time2string($v['stop_time']);
            // 屏蔽主播断开连接的直播间
            $lists[$k]['disconnect']  = in_array($v['uid'], $disconnect_list) ? 1 : 0;
        }
        $domain = strstr($_SERVER['SERVER_NAME'], 'jxmm168');

        if($domain){
            $host_address = '18.142.148.178';
        }else{
            $host_address = '54.169.182.156';
        }

        /*
         * 家族后台账号，只显示该账号包括的后台账号的家族里面的主播
         */
        if($_SESSION['admin_type'] == 1){
            $userinfo =M("users")->where("user_login='".$_SESSION['name']."'")->find();

            $author = array();
            if($userinfo['familyids']){
                $domain = strstr($userinfo['familyids'], ',');
                if(!$domain){
                    $users_family =M("users_family")->where("familyid=".$userinfo['familyids']."")->select();
                    foreach ($users_family as $key=>$value){
                        $author[] = $value['user_login'];
                    }
                }else{
                    $familyid = explode(',',$userinfo['familyids']);
                    foreach ($familyid as $value){
                        $users_family =M("users_family")->where("familyid=".$value."")->select();
                        foreach ($users_family as $key=>$value){
                            $author[] = $value['user_login'];
                        }
                    }
                }

            }

        }


        foreach ($lists as $key=>$value){
            if($_SESSION['admin_type'] == 1){
                if(!in_array($value['user_nicename'],$author)){
                    unset($lists[$key]);
                }
            }

        }
        if($_SESSION['admin_type'] == 1){
            $page = $this->page(count($lists), 20);
        }

    	$this->assign('config', $config);
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('httporigin',$_SERVER['SERVER_NAME']);
        $this->assign('hostaddress',$host_address);
        $this->assign('propellingserver',$config['propellingserver']);
        $this->assign('param',$param);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
    	
        $liveclass=M("live_class")->getfield('id,name');
        $liveclass[0]='默认分类';
        $this->assign('liveclass', $liveclass);
    	$this->display();
    }
    //排序
    public function listorders() {
        $tenantId=getTenantIds();
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['hotorderno'] = intval($r);
            $res = M("users_live")->where(array('uid' => $key))->save($data);
            setUserLiveListCache($key); // 设置直播列表缓存
        }

        $status = true;
        if ($status) {
            $action="更新直播热度排序";
            setAdminLog($action);
            delPatternCacheKeys($tenantId.'_'."getHot_"."*");
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
	function del(){
		$uid=intval($_GET['uid']);
        $live_info = M("users_live")->where("uid=%d",$uid)->find();
        $tenantId = $live_info['tenant_id'];
		if($uid){
			$result=M("users_live")->where("uid=%d and tenant_id=%d",$uid,$tenantId)->delete();
            if($result){
                delPatternCacheKeys($tenantId.'_'."getHot_"."*");
                $config = getConfigPub($tenantId);
                $url = $config['go_admin_url'].'/admin/v1/live_room/delete_room';
                $res = http_post($url,['Uid'=>$uid]);
                delUserLiveListCache($tenantId,$uid); // 移出redis直播列表缓存
                $this->success('删除成功');
             }else{
                $this->error('删除失败');
             }
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display();				
	}
    function updatehot(){

        $uid=intval($_GET['uid']);

        if(isset($_GET['ishot'])){
            $data['ishot'] =  intval($_GET['ishot']);
        }
       if(isset($_GET['isrecommend'])){
           $data['isrecommend'] =  intval($_GET['isrecommend']);
       }
        if(isset($_GET['top'])){
            $data['top'] = intval($_GET['top']);
            $data['toptime'] = time();
        }
        if(isset($_GET['ly_recommend'])){
            $data['ly_recommend'] =  intval($_GET['ly_recommend']);
        }
        if(isset($_GET['game_recommend'])){
            $data['game_recommend'] =  intval($_GET['game_recommend']);
        }

        $tenantId=getTenantIds();
        $role_id=$_SESSION['role_id'];
        if($uid){
            if($role_id==1){
                $result = M("users_live")->where(array('uid' => $uid))->save($data);
            }else{
                //租户id条件
                $result = M("users_live")->where("uid=%d and tenant_id=%d",$uid,$tenantId)->save($data);
            }

            if($result){
                delPatternCacheKeys($tenantId.'_'."getHot_"."*");
                setUserLiveListCache($uid); // 设置直播列表缓存
                $this->success('更改成功');
            }else{
                $this->error('更改失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }
    function updateroomhot(){

        $uid=intval($_GET['uid']);

        if(isset($_GET['isrecommendroom'])){
            $data['isrecommendroom'] =  intval($_GET['isrecommendroom']);
        }

        $tenantId=getTenantIds();
        $role_id=$_SESSION['role_id'];
        if($uid){
            if($role_id==1){
                $result = M("users_live")->where(array('uid' => $uid))->save($data);
            }
            else{
                //租户id条件
                $result = M("users_live")->where("uid=%d and tenant_id=%d",$uid,$tenantId)->save($data);
            }

            if($result){
                delPatternCacheKeys($tenantId.'_'."getHot_"."*");
                setUserLiveListCache($uid); // 设置直播列表缓存
                $this->success('更改成功');
            }else{
                $this->error('更改失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function add(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $liveclass = M("live_class")->where(['tenant_id'=>$tenant_id])->order('orderno asc,id desc')->select();

        $config = getConfigPub();

        $this->assign('liveclass', $liveclass);
        $this->assign('trywatchtime', $config['trywatchtime']);
        $this->assign('tickets_limit_min', $config['tickets_limit_min']);
        $this->assign('tickets_limit_max', $config['tickets_limit_max']);
        $this->assign('ct_type_list', ct_type_list());
        $this->assign('tenant_list',getTenantList());
        $this->assign('tenant_id', $tenant_id);
        $this->assign('param', $param);
        $this->display();				
	}	
	function add_post(){
        $redis = connectionRedis();
		if(IS_POST){
            $param = I('post.');
			$nowtime=time();
			$uid=(int)$_POST['uid'];
            $id_type=I('id_type');
			$pull=urldecode($_POST['pull']);
            $flvpull = urldecode($_POST['flvpull']);
            $m3u8pull = urldecode($_POST['m3u8pull']);
			$type=$_POST['type'];
			$type_val=$_POST['type_val'];
			$anyway=$_POST['anyway'];
			$liveclassid=I('liveclassid');
//			$thumb=$_POST['thumb'];
			$tenantId=getTenantIds();

            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

   /*         if($_FILES){
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

                //开始上传
                if ($info) {
                    //上传成功
                    //写入附件数据库信息

                    if(isset($info['thumb']['url'])){
                        $thumb = $info['thumb']['url'];
                        $thumb = str_replace("http","https",$thumb );

                    }else{
                        $thumb=$thumb;
                    }
                } else {
                    //上传失败，返回错误
                    $this->error($upload->getError());
                }

            }*/

			$User=M("users");
			$live=M("users_live");

            //判断是否为超级管理员
            $role_id=$_SESSION['role_id'];
            if($role_id==1){
                // 1 直播会员ID， 2 彩票会员ID
                $userinfo = $id_type == 1 ? getUserInfo($uid) : $User->field("id,user_nicename,avatar,avatar_thumb,tenant_id,isshare,game_user_id,user_login")->where(['game_user_id'=>$uid])->find();
            }else{
                if($id_type == 1){ // 1 直播会员ID， 2 彩票会员ID
                    $userinfo=$User->field("id,user_nicename,avatar,avatar_thumb,tenant_id,isshare,game_user_id,user_login")->where(['id'=>$uid,'tenant_id'=>$tenantId])->find();
                }else{
                    $userinfo=$User->field("id,user_nicename,avatar,avatar_thumb,tenant_id,isshare,game_user_id,user_login")->where(['game_user_id'=>$uid,'tenant_id'=>$tenantId])->find();
                }
            }

			if(!$userinfo || !isset($userinfo['user_login'])){
				$this->error('用户不存在');
			}

            if($tenant_id != $userinfo['tenant_id']){
                $this->error('该租户下用户不存在');
            }

            $config = getConfigPub($userinfo['tenant_id']);
            $tryWatchTime = $config['trywatchtime'];
            if($type == '2'){
                if(!($type_val >= $config['tickets_limit_min'] && $type_val <= $config['tickets_limit_max'])){
                    $this->error('请输入范围内的价格：'.$config['tickets_limit_min'].' - '.$config['tickets_limit_max']);
                }
            }

            $uid = $userinfo['id'];

			$liveinfo=$live->field('uid,islive')->where("uid={$uid}")->find();
			if($liveinfo['islive']==1){
				$this->error('该用户正在直播');
			}
            $userliveinfo=$User->field('isforbidlive')->where("id={$uid}")->find();
            if($userliveinfo['isforbidlive'] == 1 ){
                $this->error('该用户已经禁播');
            }

            $pushpull_type = 1; // 直播线路类型：1.默认，2.黄播,3.绿播,4.赌播
            $auth_info = M('family_auth')->field("status,ct_type")->where(['uid'=>$uid])->find();
            if($auth_info && $auth_info['status']==1){
                $pushpull_type = $auth_info['ct_type'];
            }else{
                $auth_info = M('users_auth')->field("status,ct_type")->where(['uid'=>$uid])->find();
                if($auth_info && $auth_info['status']==1){
                    $pushpull_type = $auth_info['ct_type'];
                }
            }

			$title=$_POST['title'];
			$stream=$uid.'_'.$nowtime;
			$data=array(
				"uid"=>$uid,
				"user_nicename"=>$userinfo['user_nicename'],
				"avatar"=>get_upload_path($userinfo['avatar']),
				"avatar_thumb"=>get_upload_path($userinfo['avatar_thumb']),
				"showid"=>$nowtime,
				"starttime"=>$nowtime,
				"title"=>$title,
				"province"=>'',
				"city"=>'广州市',
				"stream"=>$stream,
                "thumb" => urldecode(html_entity_decode(trim($param['thumb']))),
                "pushpull_type" => intval($pushpull_type),
                "is_football" => intval($param['is_football']),
                "football_live_match_id" => trim($param['football_live_match_id']),
                "football_live_time_stamp" => time(),
				"pull"=>trim($pull),
                "flvpull"=>trim($flvpull),
                "m3u8pull"=>trim($m3u8pull),
				"lng"=>'',
				"lat"=>'',
				"type"=>$type,
				"type_val"=>$type_val,
				"isvideo"=>1,
				"islive"=>1,
				"anyway"=>$anyway,
				"liveclassid"=>$liveclassid,
                "tenant_id"=>$userinfo['tenant_id'],
                "game_user_id"=>$userinfo['game_user_id'] ? $userinfo['game_user_id'] : 0,
                "isshare"=>$userinfo['isshare'],
                "tryWatchTime"=>$tryWatchTime,
                'isrecommend' => 1, // 主播开播以后自动上推荐
                "act_uid" => get_current_admin_id(),
			);

			 if($liveinfo){
				$result=$live->where("uid={$uid}")->save($data); 
			 }else{
				$result=$live->add($data); 
			 }
            $livelog['uid'] = $uid;
            $livelog['starttime'] = $nowtime;
            $livelog['stream'] = $stream;
            $livelog['status'] = 0;
            M("liveing_log")->add($livelog);

            if($result!==false){
                delPatternCacheKeys($tenantId.'_'."getHot_"."*");
                $redis->zRem('disconnect_'.$tenantId,$uid);

//                $url = $config['go_admin_url'].'/admin/v1/live_room/create_room';
//                $res = http_post($url,['Uid'=>$uid,'Stream'=>$stream,'TenantId'=>$userinfo['tenant_id']]);
                setUserLiveListCache($uid, 'create'); // 设置直播列表缓存
                $this->success('添加成功',U('index'));
            }else{
                $this->error('添加失败');
            }
		}			
	}		
	function edit(){
        $param = I('param.');
		$uid=intval($_GET['uid']);
        $live_info = M("users_live")->where("uid=%d",$uid)->find();
        $tenantId = $live_info['tenant_id'];
		if(!$uid){
            $this->error('数据传入失败！');
		}

        $live = M("users_live")->where("uid={$uid} and tenant_id={$tenantId}")->find();
        $liveclass = M("live_class")->where(['tenant_id'=>intval($live_info['tenant_id'])])->order('orderno asc,id desc')->select();

        $config = getConfigPub($tenantId);

        $this->assign('live', $live);
        $this->assign('liveclass', $liveclass);
        $this->assign('trywatchtime', $config['trywatchtime']);
        $this->assign('tickets_limit_min', $config['tickets_limit_min']);
        $this->assign('tickets_limit_max', $config['tickets_limit_max']);
        $this->assign('ct_type_list', ct_type_list());
        $this->assign('tenant_id', $tenantId);
		$this->display();
	}
	
	function edit_post(){
        $redis = connectionRedis();
		if(IS_POST){
		    $param = I('post.');
		    $uid=intval($param['uid']);
            $live_info = M("users_live")->where("uid=%d",$uid)->find();
            $tenantId = $live_info['tenant_id'];
            $config = getConfigPub();

            if($param['pushpull_type'] == ''){
                $this->error('请输入选择线路分类');
            }

            if($param['type'] == '2'){
                if(!($param['type_val'] >= $config['tickets_limit_min'] && $param['type_val'] <= $config['tickets_limit_max'])){
                    $this->error('请输入范围内的价格：'.$config['tickets_limit_min'].' - '.$config['tickets_limit_max']);
                }
            }

            $data = array(
                "liveclassid" => $param['liveclassid'],
                "type" => $param['type'],
                "tryWatchTime" => intval($param['tryWatchTime']),
                "type_val" => $param['type_val'],
                "pushpull_type" => intval($param['pushpull_type']),
                "is_football" => intval($param['is_football']),
                "football_live_match_id" => trim($param['football_live_match_id']),
                "pull" => urldecode(html_entity_decode(trim($param['pull']))),
                "flvpull" => urldecode(html_entity_decode(trim($param['flvpull']))),
                "m3u8pull"=> urldecode(html_entity_decode(trim($param['m3u8pull']))),
                "title" => $param['title'],
                "thumb" => urldecode(html_entity_decode(trim($param['thumb']))),
                "anyway" => $param['anyway'],
                "act_uid" => get_current_admin_id(),
                "mtime" => time(),
            );

            $result = M("users_live")->where('uid=%d and tenant_id=%d',$uid,$tenantId)->save($data);
            if($result!==false){
                delPatternCacheKeys($tenantId.'_'."getHot_"."*");
                setUserLiveListCache($uid); // 设置直播列表缓存

//                $url = $config['go_admin_url'].'/admin/v1/live_room/create_room';
//                $res = http_post($url,['Uid'=>$uid,'Stream'=>$live_info['stream'],'TenantId'=>$live_info['tenant_id']]);

                if($live_info['isvideo'] == 1 && $live_info['islive'] == 1){
                    $redis->zRem('disconnect_'.$tenantId,$uid);
                }
                $this->success('修改成功', U('index', array('tenant_id'=>$tenantId)));
            }else{
                $this->error('修改失败');
            }
		}			
	}
    function editroomtype(){
        $uid=intval($_GET['uid']);
        $live_info = M("users_live")->where("uid=%d",$uid)->find();
        $tenantId = $live_info['tenant_id'];
        if($uid){
            $live=M("users_live")->where("uid={$uid} and tenant_id={$tenantId}")->find();

            $liveclass=M("live_class")->order('orderno asc,id desc')->select();
            $this->assign('liveclass', $liveclass);


            $this->assign('live', $live);
        }else{
            $this->error('数据传入失败！');
        }
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $config = getConfigPub($tenant_id);

        $this->assign('trywatchtime', $config['trywatchtime']);
        $this->assign('tickets_limit_min', $config['tickets_limit_min']);
        $this->assign('tickets_limit_max', $config['tickets_limit_max']);
        $this->assign('chatserver', $config['chatserver']);
        $this->display();
    }
    function editroomtype_post(){
        $redis = connectionRedis();

        if(IS_POST){
            $uid=intval($_POST['uid']);
            $live_info = M("users_live")->where("uid=%d",$uid)->find();
            $tenantId = $live_info['tenant_id'];
            $stream = $_POST['stream'];

            $config = getConfigPub();
            if($_POST['type'] == '0'){
                $_POST['type_val'] = '';
                $_POST['tryWatchTime'] = '';
            }

            if($_POST['type'] == '2'){
                if(!($_POST['type_val'] >= $config['tickets_limit_min'] && $_POST['type_val'] <= $config['tickets_limit_max'])){
                    $msg = '请输入范围内的价格：'.$config['tickets_limit_min'].' - '.$config['tickets_limit_max'];
                    $data=array(
                        "status"=>0,
                        "data"=>$tenantId,
                        "msg"=>$msg
                    );
                    echo json_encode($data);
                }
            }
            $result = M("users_live")->where('uid=%d and tenant_id=%d',$uid,$tenantId)->save($_POST);
            if($result!==false){
                delPatternCacheKeys($tenantId.'_'."getHot_"."*");
                delPatternCacheKeys('live_watchtime_'.$stream.'*');
                setUserLiveListCache($uid); // 设置直播列表缓存

//                $config = getConfigPri($live_info['tenant_id']);
//                if ($config['go_admin_url']) {
//                    $url = $config['go_admin_url'] . '/admin/v1/live_room/broadcast_system_event';
//                    $res = http_post($url, ['EventType' => 'UpdataLivetype', 'Message' => json_encode([
//                        'Uid' => $uid,
//                        'Stream' => $live_info['stream'],
//                        'Type' => intval($_POST['type']),
//                        'TypeVal' => $_POST['type_val'],
//                        'TryWatchTime' => intval($_POST['tryWatchTime']),
//                    ])]);
//                }

                $redis->zRem('disconnect_'.$tenantId,$uid);
                $data=array(
                    "status"=>1,
                    "data"=>$tenantId,
                    "msg"=>'修改成功'
                );
                echo json_encode($data);
            }else{
                $data=array(
                    "status"=>0,
                    "data"=>$tenantId,
                    "msg"=>'修改失败'
                );
            }
        }
    }
    function editlabel(){
        $uid=intval($_GET['uid']);
        $live_info = M("users_live")->where("uid=%d",$uid)->find();
        $tenantId = $live_info['tenant_id'];
        if($uid){
            $live=M("users_live")->where("uid={$uid} and tenant_id={$tenantId}")->find();

            $liveclass=M("live_class")->order('orderno asc,id desc')->select();
            $this->assign('liveclass', $liveclass);


            $this->assign('live', $live);
        }else{
            $this->error('数据传入失败！');
        }


        $this->display();
    }
    function editlabel_post(){
        if(IS_POST){
            $param = I('post.');
            $uid=intval($param['uid']);
            $live_info = M("users_live")->where("uid=%d",$uid)->find();
            $tenantId = $live_info['tenant_id'];
            $data = array(
                "label_name" => $param['label_name'],
            );
            $redis=connectionRedis();
            $result = M("users_live")->where('uid=%d and tenant_id=%d',$uid,$tenantId)->save($data);
            if($result!==false){
                delPatternCacheKeys($tenantId.'_'."getHot_"."*");
                setUserLiveListCache($uid); // 设置直播列表缓存
                $insetredis = $redis->hSet("label_uid",$uid, $param['label_name']);
                $this->success('修改成功',U('index'));
            }else{
                $this->error('修改失败');
            }
        }
    }
	public  function liveconfig(){
        $uid = $_GET['uid'];
        $userinfo = M("users")->field("game_tenant_id")->where("id='{$uid}'")->find();
        $lottery = M('lottery_config')->where(array('tenant_id' => $userinfo['game_tenant_id']))->select();
        $this->assign('lottery', $lottery);
        $users_live_info = M("users_live")->field("uid,lottery_id,recommend_lottery_id,show_game_entry,show_offers,show_dragon_assistant,show_reward_reporting,enable_follow")->where("uid='{$uid}'")->find();
        $users_live_info['lottery_id'] = explode(',',  $users_live_info['lottery_id']);
        $users_live_info['recommend_lottery_id'] = explode(',',  $users_live_info['recommend_lottery_id']);
        $this->assign('users_live_info', $users_live_info);
        $this->display();

    }

    public  function set_liveconfig(){

        $data = array();
        if ($_POST['lottery_id']){
            $data['lottery_id'] = implode(',',$_POST['lottery_id']);
        }
        if ($_POST['recommend_lottery_id']){
            $data['recommend_lottery_id'] = implode(',',$_POST['recommend_lottery_id']);
        }
        $data['show_game_entry'] = $_POST['show_game_entry'];
        $data['show_offers'] = $_POST['show_offers'];
        $data['show_dragon_assistant'] = $_POST['show_dragon_assistant'];
        $data['show_reward_reporting'] = $_POST['show_reward_reporting'];
        $data['enable_follow'] = $_POST['enable_follow'];
        $update = M("users_live") -> where(array('uid' =>$_POST['uid']))->save($data);
        if ($update !== false){
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }

    /*
     * 进入直播间公告
     * */
    public  function enterroom_notice(){
        if(IS_POST){
            $param = I("post.");
            if(!isset($param['id']) || !$param['id']){
                $this->error('参数错误');
            }
            if(!$param['zh']){
                $this->error('中文不能为空');
            }
            if(mb_strlen($param['zh']) > 1000){
                $this->error('中文长度已超出长度1000');
            }

            $info = M("language")->where(array('id' => intval($param['id'])))->find();
            $tenant_id = $info['tenant_id'];

            $data = array(
                'zh' => $param['zh'],
                'en' => $param['en'],
                'vn' => $param['vn'],
                'th' => $param['th'],
                'my' => $param['my'],
                'ind' => $param['ind'],
                'act_uid' => get_current_admin_id(),
                'mtime' => time(),
            );
            try{
                M("language")->where(array('id' => intval($param['id'])))->save($data);
            }catch (\Exception $e){
                setAdminLog('修改进入直播间公告失败：'.$e->getMessage());
                $this->error('操作失败');
            }
            delcache('enterroom_notice_'.getTenantIds());
            getEnterroomNotice(getTenantIds()); // 通过获取让数据有缓存

//            $config = getConfigPub($tenant_id);
//            $url = $config['go_admin_url'].'/admin/v1/live_sync_cache/del_enterroom_notice_cache';
//            $res = http_post($url,['TenantId'=>$tenant_id]);
            $this->success('操作成功',U('enterroom_notice'));
        }

        $tenant_id = getTenantIds();
        $info = M('language')->where(['type'=>1,'tenant_id'=>$tenant_id])->find();
        if(!$info){
            M('language')->add(['type' => 1,'tenant_id' => $tenant_id, 'act_uid' => get_current_admin_id(), 'ctime' => time()]);
            $info = M('language')->where(['type'=>1,'tenant_id'=>$tenant_id])->find();
        }

        $this->assign('info',$info);
        $this->display();
    }

    public function adminstoplive(){
        $param = I('post.');
        if(IS_POST && isset($param['golang_event'])) {
            $live_info = getUserLiveInfo($param['uid']);
            $config = getConfigPri($live_info['tenant_id']);
            if ($config['go_admin_url']) {
                $url = $config['go_admin_url'] . '/admin/v1/live_room/broadcast_system_event';
                $res = http_post($url, ['EventType' => 'Servercloselive', 'Message' => json_encode(['Uid' => $param['uid']])]);
                $del_room_url = $config['go_admin_url'].'/admin/v1/live_room/delete_room';
                $del_room_res = http_post($del_room_url,['Uid'=>$param['uid'],'TenantId'=>$live_info['tenant_id']]);
            }
            $this->success($res);
        }

        $id=intval($_GET['id']);
        $status=intval($_GET['status']);
        if ($id) {
            $live_info = M('users_live')->where(['uid'=>$id])->find();
            $stream = $live_info['stream'];
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
            delUserLiveListCache($live_info['tenant_id'], $id); // 移出redis直播列表缓存
            $this->success(['msg'=>'操作成功！','stopRoomUrl'=>$stopRoomUrl]);

        } else {
            $this->error('数据传入失败！');
        }
    }

    public function auto_live_user(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = $tenant_id;
        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login'] != ''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['game_user_id']) && $param['game_user_id'] != ''){
            $map['game_user_id'] = $param['game_user_id'];
        }

        $count = M('auto_live_user')->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = M('auto_live_user')->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        $type_list = $this->auto_live_user_type_list;
        foreach ($list as $key=>$val){
            $list[$key]['type_name'] = isset($type_list[$val['type']]) ? $type_list[$val['type']] : $val['type'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->display();
    }

    public function auto_live_user_add(){
        if(IS_POST){
            $param = I('param.');
            if($param['uid'] == ''){
                $this->error('请选输入用户ID');
            }
            $tenant_id = intval($param['tenant_id']);
            $user_info = [];
            if($param['id_type'] == 1){
                $user_info = M('users')->where(['tenant_id'=>$tenant_id, 'user_type'=>['neq','1'], 'id'=>intval($param['uid'])])->find();
            }else if($param['id_type'] == 2){
                $user_info = M('users')->where(['tenant_id'=>$tenant_id, 'user_type'=>['neq','1'], 'game_user_id'=>intval($param['uid'])])->find();
            }

            if(empty($user_info)){
                $this->error('用戶不存在');
            }
            if(empty($user_info['game_user_id'])){
                $this->error('用戶的彩票会员ID不存在，请更换用户');
            }

            $insert_data = array(
                'tenant_id' => intval($user_info['tenant_id']),
                'operated_by' => get_current_admin_user_login(),
                'type' => isset($param['type']) ? intval($param['type']) : 1,
                'create_time' => time(),
                'uid' => $user_info['id'],
                'user_login' => $user_info['user_login'],
                'game_user_id' => intval($user_info['game_user_id']),
                'thumb' => urldecode(trim($param['thumb'])),
            );

            try{
                M('auto_live_user')->add($insert_data);
            }catch (\Exception $e){
                setAdminLog('添加 自动开播用户 失败：'.$e->getMessage());
                $this->error('操作失败');
            }

            AutoLiveUserCache::getInstance()->delAutoLiveUserListCache($tenant_id); // 清除自动开播用户列表缓存
            setAdminLog('添加 自动开播用户 成功 '.json_encode($param, JSON_UNESCAPED_UNICODE));
            $this->success('操作成功', U('auto_live_user', array('tenant_id'=>$tenant_id)));
        }

        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $this->assign('type_list', $this->auto_live_user_type_list);
        $this->assign('tenant_list',getTenantList());
        $this->assign('tenant_id', $tenant_id);
        $this->assign('param', $param);
        $this->display();
    }

    public function auto_live_user_edit(){
        if(IS_POST){
            $param = I('param.');
            if($param['id'] == ''){
                $this->error('参数错误');
            }

            $info = M('auto_live_user')->where(['id'=>intval($param['id'])])->find();
            if(empty($info)){
                $this->error('数据不存在');
            }
            $tenant_id = $info['tenant_id'];
            $update_data = array(
                'thumb' => urldecode(trim($param['thumb'])),
                'operated_by' => get_current_admin_user_login(),
                'update_time' => time(),
            );

            try{
                M('auto_live_user')->where(['id'=>intval($param['id'])])->save($update_data);
            }catch (\Exception $e){
                setAdminLog('编辑 自动开播用户 失败：'.$e->getMessage());
                $this->error('操作失败');
            }

            AutoLiveUserCache::getInstance()->delAutoLiveUserListCache($tenant_id); // 清除自动开播用户列表缓存
            setAdminLog('编辑 自动开播用户 成功 '.json_encode($param, JSON_UNESCAPED_UNICODE));
            $this->success('操作成功', U('auto_live_user', array('tenant_id'=>$tenant_id)));
        }

        $param = I('param.');
        if(!$param['id'] || empty($param['id'])){
            $this->error('参数错误');
        }
        $info = M('auto_live_user')->where(['id'=>intval($param['id'])])->find();

        $this->assign('info', $info);
        $this->assign('param', $param);
        $this->display();
    }

    public function auto_live_user_del(){
        $param = I('param.');
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('参数错误');
        }
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $info = M('auto_live_user')->where(['id'=>intval($id)])->find();
        if(!$info){
            $this->error('数据不存在');
        }
        if(getRoleId() != 1 && $tenant_id != $info['tenant_id']){
            $this->error('操作不合法');
        }

        try{
            M('auto_live_user')->where(['id'=>intval($id)])->delete();
        }catch (\Exception $e){
            setAdminLog('删除 自动开播用户 失败：'.$e->getMessage());
            $this->error('操作失败');
        }
        AutoLiveUserCache::getInstance()->delAutoLiveUserListCache($tenant_id); // 清除自动开播用户列表缓存
        setAdminLog('删除 自动开播用户 成功【'.$id.'】');

        $this->success('操作成功', U('auto_live_user',array('tenant_id'=>$info['tenant_id'])));

    }


}
