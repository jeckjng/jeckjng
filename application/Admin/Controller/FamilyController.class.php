<?php

/**
 * 家族
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class FamilyController extends AdminbaseController {
    function index(){
		//$map['state']=array('neq',3);
		if($_REQUEST['start_time']!='')
		{
			$map['addtime']=array("gt",strtotime($_REQUEST['start_time']));
			$_GET['start_time']=$_REQUEST['start_time'];
		}	 
		if($_REQUEST['end_time']!='')
		{		 
			$map['addtime']=array("lt",strtotime($_REQUEST['end_time']));
			$_GET['end_time']=$_REQUEST['end_time'];
		}
		if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' )
		{ 
			$map['addtime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'])));
			$_GET['start_time']=$_REQUEST['start_time'];
			$_GET['end_time']=$_REQUEST['end_time'];
		}
		if($_REQUEST['keyword']!='')
		{
			$map['uid|user_login']=array("like","%".$_REQUEST['keyword']."%");
			$_GET['keyword']=$_REQUEST['keyword'];
		}
        if($_REQUEST['game_user_id']!=''){
            $map['game_user_id']=$_REQUEST['game_user_id'];
            $_GET['game_user_id']=$_REQUEST['game_user_id'];
        }

        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }else{
            //租户id条件
            $map['tenant_id']=getTenantIds();
        }
    	$auth=M("family");
    	$count=$auth->where($map)->count();
    	$page = $this->page($count);

    	$lists = $auth
			->where($map)
			->order("addtime DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
		foreach($lists as $k=>$v){
            if(!$v['game_user_id']){
                $userinfo = getUserInfo($v['uid']);
                if($userinfo['game_user_id']){
                    $lists[$k]['game_user_id'] = $userinfo['game_user_id'];
                    M("family")->where(['id'=>$v['id']])->save(['game_user_id'=>$userinfo['game_user_id']]);
                }
            }
		}
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
	}

    function profit(){
		$uid=intval($_GET['uid']);
		$tenantId=getTenantIds();

		$map=array();
		$map['tenant_id']=$tenantId;
		
		$Family=M('family');
		$User_family=M("users_family");
		$ufamilyinfo=$User_family->where("uid={$uid} and tenant_id={$tenantId}")->find();
		if($ufamilyinfo){
			$map['uid']=$uid;
		}else{
			$familyinfo=$Family->where("uid={$uid} and tenant_id={$tenantId}")->find();
			$map['familyid']=$familyinfo['id'];
		}

    	$family_profit=M('family_profit');
    	$count=$family_profit->where($map)->count();
    	$page = $this->page($count, 20);
		$total_family=$family_profit->where($map)->sum("profit");
		$total_anthor=$family_profit->where($map)->sum("profit_anthor");
		if(!$total_family){
			$total_family=0;
		}
		if(!$total_anthor){
			$total_anthor=0;
		}
    	$lists = $family_profit
			->field("*")
			->where($map)
			->order("addtime DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			foreach($lists as $k=>$v){
				   $userinfo=M("users")->field("user_nicename")->where("id='$v[uid]'")->find();
				   $lists[$k]['userinfo']= $userinfo; 
			}			
    	$this->assign('total_family', $total_family);
    	$this->assign('total_anthor', $total_anthor);
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
	}


    function cash(){
		$uid=intval($_GET['uid']);
		$tenantId=getTenantIds();

		$User_family=M("users_family");
		$Cashrecord=M('users_cashrecord');
		$Users=M('users');
		$map=array();
		$map['uid']=$uid;
		$map['tenant_id']=$tenantId;

		$ufamilyinfo=$User_family->where("uid={$uid} and tenant_id={$tenantId}")->find();
		if($ufamilyinfo){
			$map['addtime']=array('gt',$ufamilyinfo['addtime']);
		}

    	
		
    	$count=$Cashrecord->where($map)->count();
    	$page = $this->page($count, 20);
		$total=0;
    	$lists = $Cashrecord
			->where($map)
			->order("addtime DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			foreach($lists as $k=>$v){
				   $userinfo=$Users->field("user_nicename")->where("id='$v[uid]'")->find();
				   $lists[$k]['userinfo']= $userinfo; 
				   if($v['status']==1){
					   $total+=$v['money'];
				   }
			}			

    	$this->assign('total', $total);
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
	}
	function edit()
	{
		$id=intval($_GET['id']);
		$tenantId=getTenantIds();
		if($id){
            //判断是否为超级管理员
            $role_id=$_SESSION['role_id'];
            if($role_id==1){
                $family=M("family")->where("id=%d ",$id)->find();
            }else{
                //租户id条件
                $family=M("family")->where("id=%d and tenant_id=%d",$id,$tenantId)->find();
            }

            $u_info = getUserInfo($family['uid']);
            $family['game_user_id'] = $family['game_user_id'] ? $family['game_user_id'] : $u_info['game_user_id'];
			$this->assign('family', $family);						
		}else
		{				
			$this->error('数据传入失败！');
		}								  
		$this->display();
	}
	function edit_post()
	{

        if(IS_POST)
        {
            $id=$_POST['id'];
            $uid=$_POST['uid'];
            $family_name=$_REQUEST['family_name'];
            $family_introduction=$_REQUEST['family_introduction'];
            $tenantId=getTenantIds();
            if(empty($uid)){
                $this->error('请输入会员uid');
            }
            if(empty($family_name)){
                $this->error('请输入家族名称');
            }
            if(empty($family_introduction)){
                $this->error('请输入家族简介');
            }
            $family=M("family")->where("id=%d ",$id)->find();

            $users=M("users")->where("game_user_id=%d and tenant_id=%d ",$uid,$family['tenant_id'])->find();

            if(empty($users)){
                $this->error('没有该会员或者该会员不属于这个当前租户');
            }
            $familys=M("family")->where("uid=%d ",$users['id'])->select();
            foreach ($familys as $key=>$val){
                if($val['id'] != $id){
                    $this->error("该会员已经添加，请编辑!");
                }
            }

            $family=M("family");
            $family->create();
            $family->uid=$users['id'];
            $family->game_user_id=$users['game_user_id'];
            $family->user_login=$users['user_login'];
            $family->family_name=$family_name;
            $family->family_introduction=$family_introduction;

            $family->operate_time=time();
            $family->tenant_id=$users['tenant_id'];
            $family->operate_name= $_SESSION['name'];

            $result=$family->save();

            if($result!==false)
            {
                $action="修改家族：{$uid}";
                setAdminLog($action);
                $this->success('编辑成功',U('index'));
            }else
            {
                $this->error('编辑失败');
            }


        }
	}
	function disable()
	{
		$id=intval($_GET['id']);
        $tenantId=getTenantIds();
		if($id)
		{
			$result=M("family")->where("id=%d and tenant_id=%d",$id,$tenantId)->setField("disable", "1");
			if($result!==false)
			{
                $action="禁用家族：{$id}";
                    setAdminLog($action);
				$this->success('禁用成功');
			}
			else
			{
				$this->error('禁用失败');
			}			
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display();		
	}
	function enable()
	{
		$id=intval($_GET['id']);
        $tenantId=getTenantIds();
		if($id)
		{
			$result=M("family")->where("id=%d and tenant_id=%d",$id,$tenantId)->setField("disable", "0");
			if($result!==false)
			{
                $action="启用家族：{$id}";
                    setAdminLog($action);
				$this->success('启用成功');
			}
			else
			{
				$this->error('启用失败');
			}			
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display();		
	}
	function del()
	{
		$id=intval($_GET['id']);
        $tenantId=getTenantIds();
		if($id)
		{
			$data=array(
				'state'=>3,
				'signout'=>2,
				'signout_istip'=>2,
			);
			$user_family=M("users_family")->where("familyid=%d and tenant_id=%d",$id,$tenantId)->save($data);
			$user_family=M("family_profit")->where("familyid=%d and tenant_id=%d",$id,$tenantId)->delete();
			$data2=array(
				'state'=>3,
			);
			$result=M("family")->where("id=%d and tenant_id=%d",$id,$tenantId)->save($data2);
			if($result!==false)
			{
                $action="删除家族：{$id}";
                    setAdminLog($action);
				$this->success('删除成功');
			}
			else
			{
				$this->error('删除失败');
			}			
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display();		
	}
	function users()
	{
		//$map['state']=array('neq',3);
        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }
        else{
            //租户id条件
            $map['tenant_id']=getTenantIds();
        }
        
        if($_REQUEST['state']!='')
		{
			$map['state']=array(array('neq',3),$_REQUEST['state']); 
			$_GET['state']=$_REQUEST['state'];
		}	
        
		if($_REQUEST['start_time']!='')
		{
			$map['addtime']=array("gt",strtotime($_REQUEST['start_time']));
			$_GET['start_time']=$_REQUEST['start_time'];
		}	 
		if($_REQUEST['end_time']!='')
		{		 
			$map['addtime']=array("lt",strtotime($_REQUEST['end_time']));
			$_GET['end_time']=$_REQUEST['end_time'];
		}
		if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' )
		{ 
			$map['addtime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'])));
			$_GET['start_time']=$_REQUEST['start_time'];
			$_GET['end_time']=$_REQUEST['end_time'];
		}
        if($_REQUEST['keyword1']!='')
        {
            $map['family_name|familyid']=array("like","%".$_REQUEST['keyword1']."%");
            $_GET['keyword1']=$_REQUEST['keyword1'];
        }
        if($_REQUEST['keyword2']!='')
        {
            $map['uid|user_login']=array("like","%".$_REQUEST['keyword2']."%");
            $_GET['keyword2']=$_REQUEST['keyword2'];
        }
        if($_REQUEST['game_user_id']!=''){
            $map['game_user_id']=$_REQUEST['game_user_id'];
            $_GET['game_user_id']=$_REQUEST['game_user_id'];
        }

        if($_SESSION['admin_type'] == 1){ // 家族后他账号，只展示此家族的会员
            $admin_info = getUserInfo($_SESSION['ADMIN_ID']);
            if($admin_info['familyids']){
                $map['familyid']=array("in",explode(',',$admin_info['familyids']));
            }else{
                $this->assign('admin_type', $_SESSION['admin_type']);
                $this->assign('lists', []);
                $this->assign('formget', $_GET);
                $this->assign("page", $this->page(0)->show('Admin'));
                $this->display();
                return ;
            }
        }

        $auth=M("users_family");
    	$Users=M("users");
    	$Family=M("family");
    	$count=$auth->where($map)->count();
    	$page = $this->page($count);
    	$lists = $auth
			->where($map)
			->order("addtime DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
		foreach($lists as $k=>$v){
			$userinfo=getUserInfo($v['uid']);
			$lists[$k]['userinfo']= $userinfo; 
			$family=$Family->where("id='$v[familyid]'")->find();
			$lists[$k]['family']= $family;
            if(!$v['game_user_id'] && $userinfo['game_user_id']){
                $lists[$k]['game_user_id'] = $userinfo['game_user_id'];
                M("users_family")->where(['id'=>$v['id']])->save(['game_user_id'=>$userinfo['game_user_id']]);
            }
            $config = getConfigPri($v['tenant_id']);
            $lists[$k]['chatserver'] = $config['chatserver'];
		}

        $this->assign('admin_type', $_SESSION['admin_type']);
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));    	
    	$this->display();
	}
	function users_edit()
	{
		$id=intval($_GET['id']);
        $tenantId=getTenantIds();
		if($id){
			$user_family=M("users_family")->where("id={$id} and tenant_id={$tenantId}")->find();
			$userinfo=M("users")->field("user_nicename")->where("id=%d and tenant_id=%d",$user_family['uid'],$tenantId)->find();
			$user_family['userinfo']=$userinfo;
			$family=M("family")->field("name,divide_family")->where("id=%d and tenant_id=%d",$user_family['familyid'],$tenantId)->find();
			$user_family['family_nicename']=$family['name'];
			$user_family['family_devide']=$family['divide_family'];
			$this->assign("user_family", $user_family);						
		}else
		{				
			$this->error('数据传入失败！');
		}								  
		$this->display();
	}
	function users_edit_post()
	{
		if(IS_POST)
		{
            $tenantId=getTenantIds();
			$user_family=M("users_family");
			$user_family->create();
			$user_family->where("id=%d and tenant_id=%d",$user_family['id'],$tenantId);
			$user_family->uptime=time();
			$user_family=$user_family->save(); 
			if($user_family!==false)
			{
				$uid=$_POST['uid'];

                $action="修改家族成员信息：{$uid}";
                    setAdminLog($action);
				$this->success('修改成功');
			}
			else
			{
				$this->error('修改失败');
			}					 
		}		
	}
	function users_del()
	{
		$id=intval($_GET['id']);
        $tenantId=getTenantIds();
		if($id)
		{
            //判断是否为超级管理员
            $role_id=$_SESSION['role_id'];
            if($role_id==1){
                $info=M("users_family")->where("id=%d ",$id)->delete();
            }else{
                //租户id条件
                $info=M("users_family")->where("id=%d and tenant_id=%d",$id,$tenantId)->delete();
            }

            if($info!==false){
                M("family")->where(['id'=>$_GET['familyid']])->setDec('nums',1);
                $action="删除家族成员：{$info['uid']}";
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
    function add()
    {
        $this->display();
    }
    function add_post()
    {
        if(IS_POST){
            $uid=$_POST['uid'];
            $family_name=$_REQUEST['family_name'];
            $family_introduction=$_REQUEST['family_introduction'];
            $tenantId=getTenantIds();
            if(empty($uid)){
                $this->error('请输入会员uid');
            }
            if(empty($family_name)){
                $this->error('请输入家族名称');
            }
            if(empty($family_introduction)){
                $this->error('请输入家族简介');
            }

            $tenantId=getTenantIds();
            //判断是否为超级管理员
            $role_id=$_SESSION['role_id'];
            if($role_id==1){
                $users=M("users")->where("game_user_id=%d",$uid)->find();
            }else{
                //租户id条件
                //根据登录账号区分 彩票租户和独立租户
                $whichTenant  = whichTenat();
                if($whichTenant == 2 ){
                    $users=M("users")->where("id=%d and tenant_id=%d ",$uid,$tenantId)->find();
                }else{
                    $users=M("users")->where("game_user_id=%d and tenant_id=%d ",$uid,$tenantId)->find();
                }

            }
            if(empty($users)){
                $this->error('没有该会员或者该会员不属于这个当前租户');
            }

            $familys=M("family")->where("uid=%d ",$users['id'])->find();
            if($familys){
                $this->error('该会员已经添加，请勿重复添加！');
            }

            $family=M("family");
            $family->create();
            $family->uid=$users['id'];
            $family->game_user_id=!empty($users['game_user_id'])?$users['game_user_id']:101;
            $family->user_login=$users['user_login'];
            $family->family_name=$family_name;
            $family->family_introduction=$family_introduction;
            $family->nums=0;
            $family->addtime=time();
            $family->tenant_id=$users['tenant_id'];

            $result=$family->add();

            if($result!==false){
                $action="添加家族：{$uid}";
                setAdminLog($action);
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
    }

	function users_add(){
        $map = [];
        //判断是否为超级管理员
        if($_SESSION['role_id'] != 1){
            $map['tenant_id']=getTenantIds();
        }

        if($_SESSION['admin_type'] == 1){ // 家族后台账号
            $admin_info = getUserInfo($_SESSION['ADMIN_ID']);
            if($admin_info['familyids']){
                $map['id']=array("in",explode(',',$admin_info['familyids']));
            }else{
                $this->assign('family_list',[]);
                $this->display();
                return;
            }
        }

        $family_list = M('family')->where($map)->field('id,family_name')->order('id desc')->select();
        $family_list = array_column($family_list,'family_name','id');

        $this->assign('family_list',$family_list);
		$this->display();	
	}

	function users_add_post()
	{
		if(IS_POST){
			$uid=$_REQUEST['uid'];
			$familyid=$_REQUEST['familyid'];
            $tenantId=getTenantIds();
			if($uid!=""&&$familyid!=""){
                $family=M("family")->where("id=".$familyid)->find();
                if(!$family){
                    $this->error('该家族不存在');
                }
                //根据登录账号区分 彩票租户和独立租户
                $whichTenant  = whichTenat();
                if($whichTenant == 2 ){
                    $users=M("users")->where("id=%d and tenant_id=%d and user_type  in(2,5,6)",$uid,$family['tenant_id'])->find();
                }else{
                    $users=M("users")->where("game_user_id=%d and tenant_id=%d and user_type in(2,5,6)",$uid,$family['tenant_id'])->find();
                }

                if(!$users){
                    $this->error('该成员不存在');
                }
                $isfamily=M("family")->where("uid=".$users['id'])->find();
                if($isfamily){
                    $this->error('该用户已是家族长');
                }

                if($users['tenant_id'] != $family['tenant_id']){
                    $this->error('该家族所在租户没有该成员');
                }

                $user_family=M("users_family");

                $isexist = $user_family->where("uid={$users['id']}")->find();
                if($isexist){
                    $this->error('该用户已加入家族');
                }
                //主播后台账号，只能给自己绑定的家族，添加会员
                if($_SESSION['admin_type'] == 1){
                    $userinfo =M("users")->where("user_login='".$_SESSION['name']."'")->find();
                    $author = array();
                    if(isset($userinfo['familyids'])){
                        $domain = strstr($userinfo['familyids'], ',');
                        if(!$domain){
                            $author[] = $userinfo['familyids'];
                        }else{
                            $author = explode(',',$userinfo['familyids']);
                        }
                    }

                    if (!in_array($familyid,$author)){
                        $this->error('你无权给其他家族，添加会员！');
                    }
                }

                //更新家族长绑定的人数
                $data['nums'] = array('exp','nums+1');
                $res = M("family")->where(['id'=>$familyid])->save($data);

                $commission = $this->getCommisson($family['tenant_id'],$users['id']);

                $user_family->create();
                $user_family->uid=$users['id'];
                $user_family->game_user_id=!empty($users['game_user_id'])?$users['game_user_id']:0;;
                $user_family->familyid=$familyid;
                $user_family->user_login=$users['user_login'];
                $user_family->family_name=$family['family_name'];
                $user_family->addtime=time();
                $user_family->tenant_id=$users['tenant_id'];
                $user_family->gift_send=  $commission['gift_send'];
                $user_family->bet_send=  $commission['bet_send'];
                $user_family->operate_name=$_SESSION['name'];
                $result=$user_family->add();
                if($result!==false){
                    $action="添加家族成员：{$uid}";
                    setAdminLog($action);
                    $this->success('添加成功');
                }else{
                    $this->error('添加失败');
                }
			}else{
				$this->error('成员ID与家族名称不能为空');
			}
		}		
	}

    /*
     * 禁播
     * */
    public function forbidlive(){
        $id=intval($_GET['id']);
        $status=intval($_GET['status']);


        if($_SESSION['admin_type'] == 1 && $status == 0) { // 家族后他账号，只展示此家族的会员
            $this->error('家族长账号，不能取消禁播！');
        }
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

	public function getCommisson($tenant_id, $uid){
        $config = getConfigPub($tenant_id);

        $commission =M('commission_set')->where("uid={$uid}")->find();
        if(empty($commission)){
            $arr['gift_send'] = '0';
            $arr['bet_send'] = '0';
        }else{
            $arr['gift_send'] = $config['anchor_profit_ratio'] - $commission['anchor_commission'];
            $arr['bet_send'] =  $config['anchor_platform_ratio'] - $commission['anchor_betcommission'];
        }
        return $arr;

    }

	/*
	 * 身份认证
	 * */
    public function auth(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = [];
        if(isset($param['status']) && $param['status']!=''){
            $map['status']=$param['status'];
        }
        if(isset($param['start_time']) && $param['start_time']!=''){
            $map['addtime']=array("gt",strtotime($param['start_time']));
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime']=array("lt",strtotime($param['end_time'].' 23:59:59'));
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time']!='' && $param['end_time']!='' ){
            $map['addtime']=array("between",array(strtotime($param['start_time']),strtotime($param['end_time'].' 23:59:59')));
        }
        if(isset($param['keyword']) && $param['keyword']!=''){
            $map['uid|mobile']=$param['keyword'];
        }
        if(isset($param['game_user_id']) && $param['game_user_id']!=''){
            $map['game_user_id']=$param['game_user_id'];
        }
        if(isset($param['ct_type']) && $param['ct_type']!=''){
            $map['ct_type']=$param['ct_type'];
        }

        if ($_SESSION['role_id'] != 1){
            $map['tenant_id'] =  getTenantIds();
        }
        if($_SESSION['admin_type'] == 1){ // 家族后他账号，只展示此家族的会员
            $admin_info = getUserInfo($_SESSION['ADMIN_ID']);
            if($admin_info['familyids']){
                $map['familyid']=array("in",explode(',',$admin_info['familyids']));
            }else{
                $this->assign('admin_type', $_SESSION['admin_type']);
                $this->assign('lists', []);
                $this->assign('formget', $_GET);
                $this->assign("page", $this->page(0)->show('Admin'));
                $this->display();
                return ;
            }
        }

        $auth=M("family_auth");
        $count=$auth->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $auth
            ->where($map)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach($lists as $k=>$v){
            $userinfo = getUserInfo($v['uid']);
            $lists[$k]['userinfo'] = $userinfo;
            if(!$v['game_user_id'] && $userinfo['game_user_id']){
                $lists[$k]['game_user_id'] = $userinfo['game_user_id'];
                M("family_auth")->where(['id'=>$v['id']])->save(['game_user_id'=>$userinfo['game_user_id']]);
            }
        }
        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign('ct_type_list', ct_type_list());
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
     * 新增认证
     * */
    public function auth_add(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['uid']) || !$param['uid']){
                $this->error('会员ID不能为空');
            }
            if(!isset($param['mobile']) || !$param['mobile']){
                $this->error('手机号码不能为空');
            }
            if(mb_strlen($param['mobile']) < 1 || mb_strlen($param['mobile']) > 20){
                $this->error('手机号码不合法，长度范围：1-20 任意字符');
            }
            if(!isset($param['anchor_photo']) || !$param['anchor_photo']){
                $this->error('主播照片不能为空');
            }
            if(!isset($param['anchor_video']) || !$param['anchor_video']){
                $this->error('主播视频不能为空');
            }
            if(!isset($param['status'])){
                $this->error('请选择审核状态');
            }
            if(mb_strlen($param['remark']) > 200){
                $this->error('备注太长，最长200个任意字符');
            }
            $id_type=$param['id_type'];
            if($id_type == 1) { // 1 直播会员ID， 2 彩票会员ID
                $user_info = M("users")->where("id={$param['uid']}")->find();
                $map['uid'] = $param['uid'];
            }else{
                $user_info = M("users")->where("game_user_id={$param['uid']}")->find();
                $map['game_user_id'] = $param['uid'];
            }

            if(!$user_info){
                $this->error('该会员不存在');
            }
            $users_family = M("users_family")->where("uid={$user_info['id']}")->find();
            if(!$users_family){
                $this->error('该成员不存在');
            }


            if ($_SESSION['role_id'] != 1){
                $map['tenant_id']=getTenantId();
            }
            $authInfo=M("family_auth")->where($map)->find();
            if($authInfo){
                $this->error('身份已存在');
            }

            $data = array(
                'uid' => $user_info['id'],
                'game_user_id' => isset($user_info['game_user_id'])?$user_info['game_user_id']:0,
                'familyid' => $users_family['familyid'],
                'mobile' => $param['mobile'],
                'anchor_photo' => $param['anchor_photo'],
                'anchor_video' => $param['anchor_video'],
                'status' => $param['status'],
                'ct_type' => $param['ct_type'],
                'tenant_id' => $user_info['tenant_id'],
                'remark' => $param['remark'],
                'addtime' => time(),
                'uptime' => time(),
            );

            try{
                $res = M("family_auth")->add($data);
            }catch (\Exception $e){
                $error_msg = $e->getMessage();
                setAdminLog('新增家族成员身份认证信息 失败，'.$error_msg);
            }

            if(isset($res) && $res){
                if($param['status']=='1'){
                    $action="新增家族成员身份认证信息：{$param['uid']} - 通过";
                }else if($param['status']=='2'){
                    $action="新增家族成员身份认证信息：{$param['uid']} - 拒绝";
                }else{
                    $action="新增家族成员身份认证信息：{$param['uid']} - 审核中";
                }
                setAdminLog($action);
                $this->success('操作成功',U('auth'));
            }else{
                $this->error('操作失败');
            }
        }

        $is_show = $_SESSION['role_id'] == 5 ? 0 : 1; // 家族长新增和编辑的时候，审核状态他只能选择 处理中，租户有成功和失败的选择
        $this->assign('is_show',$is_show);
        $this->assign('ct_type_list', ct_type_list());
        $this->display();
    }

    /*
     * 编辑认证
     * */
    public function auth_edit(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['id']) || !$param['id']){
                $this->error('ID不能为空');
            }
            if(!isset($param['mobile']) || !$param['mobile']){
                $this->error('手机号码不能为空');
            }
            if(mb_strlen($param['mobile']) < 1 || mb_strlen($param['mobile']) > 20){
                $this->error('手机号码不合法，长度范围：1-20 任意字符');
            }
            if(!isset($param['anchor_photo']) || !$param['anchor_photo']){
                $this->error('主播照片不能为空');
            }
            if(!isset($param['anchor_video']) || !$param['anchor_video']){
                $this->error('主播视频不能为空');
            }
            if(!isset($param['status'])){
                $this->error('请选择审核状态');
            }
            if(mb_strlen($param['remark']) > 200){
                $this->error('备注太长，最长200个任意字符');
            }

            $data = array(
                'mobile' => $param['mobile'],
                'anchor_photo' => $param['anchor_photo'],
                'anchor_video' => $param['anchor_video'],
                'status' => $param['status'],
                'ct_type' => $param['ct_type'],
                'remark' => $param['remark'],
                'uptime' => time(),
            );

            try{
                $res = M("family_auth")->where(['id'=>$param['id']])->save($data);
            }catch (\Exception $e){
                $error_msg = $e->getMessage();
            }
            if(isset($res) && $res){
                if($param['status']=='1'){
                    $action="编辑家族成员身份认证信息：{$param['uid']} - 通过";
                }else if($param['status']=='2'){
                    $action="编辑家族成员身份认证信息：{$param['uid']} - 拒绝";
                }else{
                    $action="编辑家族成员身份认证信息：{$param['uid']} - 审核中";
                }
                setAdminLog($action);
                $this->success('操作成功',U('auth'));
            }else{
                $this->error('操作失败');
            }
        }
        $id=intval($_GET['id']);
        if(!$id){
            $this->error('数据传入失败！');
        }
        $auth=M("family_auth")->where(['id'=>$id])->find();
        $is_show = $_SESSION['role_id'] == 5 ? 0 : 1; // 家族长新增和编辑的时候，审核状态他只能选择 处理中，租户有成功和失败的选择

        $this->assign('is_show',$is_show);
        $this->assign('auth', $auth);
        $this->assign('ct_type_list', ct_type_list());
        $this->display();
    }

    /*
     * 族长后台账号列表
     * */
    public function admin_patriarch()
    {
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map['user_type'] = 1;
        $map['admin_type'] = 1;
        if ($_SESSION['ADMIN_ID'] != 1){
            $map['tenant_id'] =  getTenantIds();
        }

        if(isset($param['start_time']) && $param['start_time']!=''){
            $map['ctime']=array("gt",strtotime($param['start_time']));
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['ctime']=array("lt",strtotime($param['end_time']));
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time']!='' && $param['end_time']!='' ){
            $map['ctime']=array("between",array(strtotime($param['start_time']),strtotime($param['end_time'])));
        }
        if($param['keyword']!=''){
            $map['uid|user_login']=$param['keyword'];
        }

        $Users = M("users");
        $count = $Users->where($map)->count();
        $page = $this->page($count);
        $lists = $Users->where($map)
            ->order("create_time DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $familyids = [];
        foreach($lists as $k=>$v){
            $lists[$k]['familyids'] = explode(',',$v['familyids']);
            $familyids = array_merge($familyids,$lists[$k]['familyids']);
        }
        $familyids = array_unique($familyids);
        if(count($familyids) > 0){
            $familylist = M("family")->where(["id"=>['in',$familyids]])->field('id,uid,family_name')->select();
            $familylist = count($familylist) > 0 ? array_column($familylist,null,'id') : $familylist;
        }

        foreach($lists as $k=>$v){
            $family_name = '';
            if(count($v['familyids'])>0){
                foreach($v['familyids'] as $kk=>$vv){
                    $family_name .= isset($familylist) && isset($familylist[$vv]) ? '  '.$familylist[$vv]['family_name'] : '';
                }
            }
            $lists[$k]['family_name'] = trim($family_name,' ');
        }


        $this->assign('lists', $lists);
        $this->assign('formget', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
   * 新增账户
   * */
    public function admin_patriarch_add(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['user_login']) || !$param['user_login']){
                $this->error('后台账号不能为空');
            }
            if(!preg_match('/^[_0-9a-zA-Z]{6,12}$/i',$param['user_login'])){
                $this->error('账号不合法，请输入 6 - 12 个数字和英文字符 , 不支持其他字符');
            }
            if(!isset($param['user_pass']) || !$param['user_pass']){
                $this->error('密码不能为空');
            }
            if(!preg_match('/^[_0-9a-zA-Z]{6,12}$/i',$param['user_pass'])){
                $this->error('密码不合法，请输 6 - 12 个数字和英文字符 , 不支持其他字符');
            }
            if(mb_strlen($param['remark']) > 100){
                $this->error('备注内容，最长100 个任意字符');
            }
            $user = M("users")->where(['user_type'=>1,'user_login'=>$param['user_login']])->find();
            if($user){
                $this->error('该账号已存在');
            }
            if($_SESSION['role_id'] == 1){
                $this->error('非租户管理员，不可以新增');
            }

            $data = array(
                'user_login' => $param['user_login'],
                'user_pass' => sp_password($param['user_pass']),
                'remark' => $param['remark'],

                'user_nicename' => $param['user_login'],
                'user_type' => 1,
                'admin_type' => 1,
                'tenant_id' => getTenantIds(),
                'game_tenant_id' => getGameTenantIds(),
                'operate_name' => $_SESSION['name'],
                'create_time'=>date('Y-m-d H:i:s', time()),
            );

            try{
                $res = M("users")->add($data);
                $uid =  M("users")->getLastInsID();
                $role_user_model=M("RoleUser");

                $role_user_model->add(array("role_id"=>5,"user_id"=>$uid));

            }catch (\Exception $e){
                $error_msg = $e->getMessage();
            }

            if(isset($res) && $res){
                $action="新增族长后台账号：{$param['user_login']}";
                setAdminLog($action);
                $this->success('操作成功',U('admin_patriarch'));
            }else{
                $this->error('操作失败');
            }
        }

        $this->display();
    }

    /*
    * 热度清除
    * */
    public function clearheat(){
        if(IS_AJAX){
            $param = I('param.');
            if(!isset($param['id']) || !$param['id']){
                $this->error('参数不能为空');
            }

            $family_user =  M("users_family")->where(['id'=>$param['id']])->find();
            $user_info = getUserInfo($family_user['uid']);

            if(!$user_info['votestotal'] || $user_info['votestotal']<=0){
                $this->error('热度值为0，不需要清除');
            }

            try{
                M()->startTrans();

                M("users")->where(['id'=>$family_user['uid']])->save(['votestotal'=>0]);
                $data = array(
                    'uid' => $family_user['uid'],
                    'familyid' => $family_user['familyid'],
                    'votestotal' => $user_info['votestotal'],
                    'act_uid' => $_SESSION['ADMIN_ID'],
                    'ctime' => time(),
                );
                M("clearheat_log")->add($data);
                M()->commit();
            }catch (\Exception $e){
                M()->rollback();
                $error_msg = $e->getMessage();
                setAdminLog('热度清除失败: '.$error_msg);
                $this->error('操作失败');
            }

            delUserInfoCache($family_user['uid']);
            setAdminLog($action="热度清除：{$family_user['user_login']}");
            $this->success('操作成功');
        }
    }


    /*
	 * 热度清除记录
	 * */
    public function clearheat_log(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = [];
        if(isset($param['start_time']) && $param['start_time']!=''){
            $map['ctime']=array("gt",strtotime($param['start_time']));
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['ctime']=array("lt",strtotime($param['end_time'].' 23:59:59'));
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time']!='' && $param['end_time']!='' ){
            $map['ctime']=array("between",array(strtotime($param['start_time']),strtotime($param['end_time'].' 23:59:59')));
        }
        if(isset($param['family']) && $param['family'] !=''){
            $family = M("family")->where(['id|family_name'=>$param['family']])->find();
            $map['familyid'] = $family ? $family['id'] : $param['family'];
        }
        if(isset($param['user']) && $param['user'] !=''){
            $users_family = M("users_family")->where(['uid|user_login'=>$param['user']])->find();
            $map['uid'] = $users_family ? $users_family['uid'] : $param['user'];
        }

        if ($_SESSION['role_id'] != 1){
            $map['tenant_id'] =  getTenantIds();
        }
        $count=M("clearheat_log")->where($map)->count();
        $page = $this->page($count, 20);
        $lists = M("clearheat_log")
            ->where($map)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $familyids = array_keys(array_column($lists,null,'familyid'));
        $family_list = count($familyids) > 0 ?  M("family")->where($map)->field('id,family_name')->select() : [];
        $family_list = count($family_list) > 0 ? array_column($family_list,null,'id') : [];
        foreach($lists as $k=>$v){
            $lists[$k]['userinfo']= getUserInfo($v['uid']);
            $lists[$k]['family_name']= isset($family_list[$v['familyid']]) ? $family_list[$v['familyid']]['family_name'] : '-';
            $lists[$k]['act_uid']= getUserInfo($v['act_uid'])['user_login'];
        }
        $this->assign('lists', $lists);
        $this->assign('formget', $param);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    /*
    * 家族配置
    * */
    public function set_family(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['id']) || count($param['id'])<=0){
                $this->error('ID不能为空');
            }
            if(!isset($param['familyids']) || count($param['familyids'])<=0){
                $this->error('请选择');
            }
            $familyids = implode(',',$param['familyids']);

            $data = array(
                'familyids' => $familyids,
            );

            try{
                $res = M("users")->where(['id'=>$param['id']])->save($data);
            }catch (\Exception $e){
                $error_msg = $e->getMessage();
            }

            if(isset($res) && $res >= 0){
                $action="家族配置：{$param['id']}";
                setAdminLog($action);
                delUserInfoCache($param['id']);
                $this->success('操作成功',U('admin_patriarch'));
            }else{
                $this->error('操作失败');
            }
        }
        $id=intval($_GET['id']);
        if(!$id){
            $this->error('数据传入失败！');
        }
        $info = getUserInfo($id);
        $info['familyids'] = explode(',',$info['familyids']);

        $map['tenant_id']=$info['tenant_id'];
        $family=M("family");
        $count=$family->where($map)->count();
        $page = $this->page($count);
        $list = $family->where($map)
                    ->order("addtime DESC")
                    ->limit($page->firstRow . ',' . $page->listRows)
                    ->select();

        $this->assign('info', $info);
        $this->assign('list', $list);
        $this->display();
    }

    /*
    * 修改密码
    * */
    public function admin_patriarch_edit(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['id']) || !$param['id']){
                $this->error('ID不能为空');
            }
            if(!isset($param['user_pass']) || !$param['user_pass']){
                $this->error('密码不能为空');
            }
            if(!preg_match('/^[_0-9a-zA-Z]{6,12}$/i',$param['user_pass'])){
                $this->error('密码不合法，请输入 6 - 12 个数字和英文字符 , 不支持其他字符');
            }
            if(mb_strlen($param['remark']) > 100){
                $this->error('备注内容，最长100 个任意字符');
            }

            $data = array(
                'user_pass' => sp_password($param['user_pass']),
                'remark' => $param['remark'],
                'operate_name' => $_SESSION['name'],
                'mtime' => time(),
            );

            try{
                $res = M("users")->where(['id'=>$param['id']])->save($data);
            }catch (\Exception $e){
                $error_msg = $e->getMessage();
            }

            if(isset($res) && $res >= 0){
                $action="修改密码：{$param['id']}";
                setAdminLog($action);
                delUserInfoCache($param['id']);
                $this->success('操作成功',U('admin_patriarch'));
            }else{
                $this->error('操作失败');
            }
        }
        $id=intval($_GET['id']);
        if(!$id){
            $this->error('数据传入失败！');
        }
        $auth = getUserInfo($id);
        $this->assign('info', $auth);
        $this->display();
    }
     public  function del_admin_user()
    {
        $id=intval($_GET['id']);
        if($id){
            $map['id'] = $id;
            if($_SESSION['role_id'] != 1){
                $map['tenant_id'] = getTenantIds();
            }
            $map['user_type'] = 1;
            $map['admin_type'] = 1;
            $result=M("users")->where($map)->delete();
            if($result){
                $action="删除家族长后台账号：{$id}";
                setAdminLog($action);
                delUserInfoCache($id);
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

}