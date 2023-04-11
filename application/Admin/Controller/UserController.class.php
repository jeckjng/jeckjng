<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class UserController extends AdminbaseController{
	protected $users_model,$role_model;
	
	function _initialize() {
		parent::_initialize();
		$this->users_model = D("Common/Users");
		$this->role_model = D("Common/Role");
	}
	function index(){
		$count=$this->users_model->where(array("user_type"=>1))->count();
		$page = $this->page($count, 20);
		$users = $this->users_model
		->where(array("user_type"=>1))
		->order("create_time DESC")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();

        $role_user_list = M("role_user")->select();
		
		$roles_src=$this->role_model->select();
		$roles=array();
		foreach ($roles_src as $r){
			$roleid=$r['id'];
			$roles["$roleid"]=$r;
		}
		foreach ($users as $k=>$u){
            if(!empty($u['tenant_id'])){
                $tenantInfo=getTenantInfo($u['tenant_id']);
                if(!empty($tenantInfo)){
                    $users[$k]['tenant_name']=$tenantInfo['name'];
                }
            }
            $role_name = '';
            foreach ($role_user_list as $key=>$val){
                if($val['user_id'] == $u['id']){
                    $role_name = $roles[$val['role_id']]['name'];
                }
            }
            $users[$k]['role_name'] = $role_name;
        }
		$this->assign("page", $page->show('Admin'));
		$this->assign("roles",$roles);
		$this->assign("users",$users);
		$this->display();
	}
	
	
	function add(){
		$roles=$this->role_model->where("status=1")->order("id desc")->select();
       // $tenant_list = M('tenant')->where(['id'=>['gt',0]])->field('id,name')->select(); // 彩票租户列表
        $tenant_list= getTenantList();
		$this->assign("roles",$roles);
        $this->assign('tenant_list',$tenant_list);
		$this->display();
	}
	
	function add_post(){
	    try{
            if(IS_POST){
                $param = I("post.");
                if(mb_strlen($param['user_nicename']) < 2 || mb_strlen($param['user_nicename']) > 32){
                    $this->error("昵称长度不合法，长度范围：2 - 32");
                }
                if(!$param['user_login']){
                    $this->error("请输入用户名");
                }
                if(!$param['user_pass']){
                    $this->error("请输入密码");
                }
                if(!$param['tenant_id']){
                    $this->error("请选择租户");
                }
                if(mb_strlen($param['user_pass']) < 6){
                    $this->error("密码长度不能小于6位");
                }
                if(mb_strlen($param['user_email']) > 64){
                    $this->error("邮箱地址过长");
                }

                $tenant = getTenantList($param['tenant_id']); // M("tenant")->where("id={$param['tenant_id']}")->find();
                if (empty($tenant)){
                    $this->error("没有该租户id！");
                }
                $userinfo = M("users")->where(['user_type'=>1,'user_login'=>$param['user_login']])->find();
                if ($userinfo){
                    $this->error("该账号已存在");
                }

                if(!empty($param['role_id']) && is_array($param['role_id'])){
                    $role_ids=$param['role_id'];
                    unset($param['role_id']);
                    if ($this->users_model->create()) {

                        $this->users_model->tenant_id=$tenant['id'];
                        $this->users_model->game_tenant_id=$tenant['game_tenant_id'];
                        $this->users_model->act_uid = get_current_admin_id();
                        $result=$this->users_model->add();
                        $uid = $this->users_model->getLastInsID();
                        if ($result!==false) {
                            $role_user_model=M("RoleUser");
                            $role_id = isset($role_ids[0])?$role_ids[0]:1;//是否选择角色，没有默认超级管理员
                            $role_user_model->add(array("role_id"=>$role_id,"user_id"=>$uid));

                            $action="添加管理员：{$uid}";
                            setAdminLog($action);
                            $this->success("添加成功！", U("user/index"));
                        } else {
                            $this->error("添加失败！");
                        }
                    } else {
                        $this->error('请选择角色');
                    }
                }else{
                    $this->error("请为此用户指定角色！");
                }
            }
        }catch (\Exception $e){
	        $msg = $e->getMessage();
	        setAdminLog('添加后台账号失败: '.$msg);
            $this->error("操作失败");
        }
	}
	
	
	function edit(){
		$id= intval(I("get.id"));

		$roles=$this->role_model->where("status=1")->order("id desc")->select();

		$role_user_model=M("RoleUser");
		$role_ids=$role_user_model->where(array("user_id"=>$id))->getField("role_id",true);

        $info = M('users')->where(array("id"=>$id))->find();

        $tenant_list = getTenantList(); // M('tenant')->where(['id'=>['gt',0]])->field('id,name')->select(); // 彩票租户列表

        $this->assign("roles",$roles);
        $this->assign("role_ids",$role_ids);
		$this->assign('info',$info);
        $this->assign('tenant_list',$tenant_list);
		$this->display();
	}
	
	function edit_post(){
        try{
            if (IS_POST) {
                $param = I("post.");
                if(empty($param['role_id']) || !is_array($param['role_id'])){
                    $this->error('请选择角色');
                }
                if(!$param['id']){
                    $this->error('缺少参数');
                }
                if(!$param['tenant_id']){
                    $this->error("请选择租户");
                }
                if(mb_strlen($param['user_nicename']) < 2 || mb_strlen($param['user_nicename']) > 32){
                    $this->error("昵称长度不合法，长度范围：2 - 32");
                }
                if(mb_strlen($param['user_email']) > 64){
                    $this->error("邮箱地址过长");
                }
                $tenant = getTenantList($param['tenant_id']); // M("tenant")->where("id={$param['tenant_id']}")->find();
                if (empty($tenant)){
                    $this->error("没有该租户id！");
                }

                $data = array(
                    'user_nicename' => $param['user_nicename'],
                    'user_email' => $param['user_email'],
                    'tenant_id' => $tenant['id'],
                    'game_tenant_id' => $tenant['game_tenant_id'],
                    'act_uid' => get_current_admin_id(),
                    'mtime' => time(),
                );

                if($param['user_pass']){
                    if(mb_strlen($param['user_pass']) < 6){
                        $this->error("密码长度不能小于6位");
                    }
                    $data['user_pass'] = setPass($param['user_pass']);
                }

                $up_res = M('users')->where(['id'=>intval($param['id'])])->save($data);

                if ($up_res!==false) {
                    $role_ids=$param['role_id'];
                    $uid=intval($param['id']);
                    $role_user_model=M("RoleUser");
                    $role_user_model->where(array("user_id"=>$uid))->delete();
                    $role_id = isset($role_ids[0])?$role_ids[0]:1;//是否选择角色，没有默认超级管理员
                    $role_user_model->add(array("role_id"=>$role_id,"user_id"=>$uid));

                    $action="修改后台账号：{$uid}";
                    setAdminLog($action);
                    $this->success("操作成功",U('index'));
                } else {
                    $this->error("操作失败");
                }
            }else{
                $this->error("请求方式错误");
            }
        }catch (\Exception $e){
            $msg = $e->getMessage();
            setAdminLog('修改后台账号失败: '.$msg);
            $this->error("操作失败");
        }
	}

    /*
     * 修改密码
     * */
    public function editpwd(){
        if (IS_POST) {
            $param = I("post.");
            if(!isset($param['id']) || !$param['id']){
                $this->error('缺少参数');
            }
            if(!$param['old_pwd']){
                $this->error('请输入旧密码');
            }
            if(!$param['new_pwd'] || mb_strlen($param['new_pwd']) < 6){
                $this->error('请输入6位字符以上的新密码');
            }
            if($param['new_pwd'] != $param['new_check_pwd']){
                $this->error('新密码不一致');
            }
            if($param['old_pwd'] == $param['new_pwd']){
                $this->error('新密码不能和旧密码一样');
            }

            $user = M('users')->where(['id'=>$param['id']])->field('user_pass')->find();
            if(!$user){
                $this->error('找不到该用户');
            }
            if($user['user_pass'] != setPass($param['old_pwd'])){
                $this->error('旧密码错误');
            }

            try{
                M('users')->where(['id'=>$param['id']])->save(['user_pass'=>setPass($param['new_pwd'])]);
                setAdminLog('管理员修改密码成功【'.$param['id'].'】');
            }catch (\Exception $e){
                $msg = $e->getMessage();
                setAdminLog('管理员修改密码失败【'.$param['id'].'】');
                $this->error('修改密码失败');
            }
            delUserInfoCache($param['id']);
            session('ADMIN_ID',null);
            $this->success("操作成功");
        }

        $this->assign('id',I("get.id"));
        $this->display();
    }
	
	/**
	 *  删除
	 */
	function delete(){
		$id = intval(I("get.id"));
		if($id==1){
			$this->error("最高管理员不能删除！");
		}
		
		if ($this->users_model->where("id=$id")->delete()!==false) {
			M("RoleUser")->where(array("user_id"=>$id))->delete();
            $action="删除管理员：{$id}";
                    setAdminLog($action);
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	
	function userinfo(){
		$id=get_current_admin_id();
		$user=$this->users_model->where(array("id"=>$id))->find();
		$this->assign($user);
		$this->display();
	}
	
	function userinfo_post(){
		if (IS_POST) {
			$_POST['id']=get_current_admin_id();
			$create_result=$this->users_model
			->field("user_login,user_email,last_login_ip,last_login_time,create_time,user_activation_key,user_status,role_id,score,user_type",true)//排除相关字段
			->create();
			if ($create_result) {
				if ($this->users_model->save()!==false) {
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->users_model->getError());
			}
		}
	}
	
	    function ban(){
        $id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->users_model->where(array("id"=>$id,"user_type"=>1))->setField('user_status','0');
    		if ($rst) {
                $action="停用管理员：{$id}";
                    setAdminLog($action);
    			$this->success("管理员停用成功！", U("user/index"));
    		} else {
    			$this->error('管理员停用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    
    function cancelban(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = $this->users_model->where(array("id"=>$id,"user_type"=>1))->setField('user_status','1');
    		if ($rst) {
                $action="启用管理员：{$id}";
                    setAdminLog($action);
    			$this->success("管理员启用成功！", U("user/index"));
    		} else {
    			$this->error('管理员启用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    function rechargelog(){
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("users_recharge_log");
        $count=$video_model->count();
        $page = $this->page($count, 20);
        $orderstr="id DESC";


        $lists = $video_model
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach($lists as $k=>$v){
            $lists[$k]['addtime']=date('Y-m-d H:i:s',$v['addtime']);

        }
        // var_dump($lists);exit;
        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->display();
    }
    public function addrecharge(){
        $this->display();

    }
    public function add_recharge(){
	    if(IS_POST){
            $video=M("users_recharge_log");
            $video->create();

            $money=$_POST['money'];
            $owner_uid=$_POST['owner_uid'];
            if($money==""){
                $this->error("请输入金额");
                return;
            }
            if($owner_uid==""||!is_numeric($owner_uid)){
                $this->error("请填写用户id");
                return;
            }
            //判断用户是否存在
            $ownerInfo=M("users")->where("user_type=2 and id={$owner_uid}")->find();
            if(!$ownerInfo){
                $this->error("用户uid不存在");
                return;
            }
            $video->uid=$owner_uid;
            $coin = $ownerInfo['coin'] + $money;
            $rst = $this->users_model->where(array("id"=>$owner_uid))->setField('coin',$coin);



            $arr['addtime']=time();
            $arr['uid']=$owner_uid;
            $arr['money']= intval($money);
            $arr['is_delete']= '1';
            $arr['user_login']=$ownerInfo['user_login'];
            $arr['operate_name']=$_SESSION['name'];

            $result = $video->add($arr);
          

            if($result){
                $this->success('添加成功','/Admin/User/rechargelog',3);
            }else{
                $this->error('添加失败');
            }

        }
    }




}