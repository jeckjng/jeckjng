<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
/**
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PublicController extends AdminbaseController {

    function _initialize() {
        C(S('sp_dynamic_config'));//加载动态配置
	}
    
    //后台登陆界面
    public function login() {
        $config=getConfigPub();
        $this->assign("config",$config);
    	if(isset($_SESSION['ADMIN_ID'])){//已经登录

    		
    		$this->success(L('LOGIN_SUCCESS'),U("Index/index"));
    	}else{
    	    $site_admin_url_password =C("SP_SITE_ADMIN_URL_PASSWORD");
    	    $upw=session("__SP_UPW__");
    		if(!empty($site_admin_url_password) && $upw!=$site_admin_url_password){
    			redirect(__ROOT__."/");
    		}else{
    		    session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__",true);
    			$this->display(":login");
    		}
    	}
    }
    
    public function logout(){
    	session('ADMIN_ID',null);
    	redirect(__ROOT__."/admin");
    }
    
    public function dologin(){
        $login_page_showed_success=session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__");
        if(!$login_page_showed_success){
            $this->error('login error!', U('login'));
        }
    	$name = I("post.username");
    	if(empty($name)){
    		$this->error(L('USERNAME_OR_EMAIL_EMPTY'));
    	}
    	$pass = I("post.password");
    	if(empty($pass)){
    		$this->error(L('PASSWORD_REQUIRED'));
    	}
    	$verrify = I("post.verify");
    	if(empty($verrify)){
    		$this->error(L('CAPTCHA_REQUIRED'));
    	}
    	//验证码
    	if(!sp_check_verify_code()){
    		$this->error(L('CAPTCHA_NOT_RIGHT'));
    	}else{
    		$user = D("Common/Users");
    		if(strpos($name,"@")>0){//邮箱登陆
    			$where['user_email']=$name;
    		}else{
    			$where['user_login']=$name;
    		}
    		//增加租户id条件
            //$where['tenant_id']=getTenantIds();

    		
    		$result = $user->where($where)->find();
    		if(!empty($result) &&  in_array($result['user_type'],[1,8] )){
    			if(sp_compare_password($pass,$result['user_pass'])){
    				
    				$role_user_model=M("RoleUser");
    				
    				$role_user_join = C('DB_PREFIX').'role as b on a.role_id =b.id';
    				
    				$groups=$role_user_model->alias("a")->join($role_user_join)->where(array("user_id"=>$result["id"],"status"=>1))->getField("role_id",true);

    				if( $result["id"]!=1 && ( empty($groups) || empty($result['user_status']) ) ){
    					$this->error(L('USE_DISABLED'));
    				}
    				//登入成功页面跳转
    				$_SESSION["ADMIN_ID"]=$result["id"];
    				$_SESSION['name']=$result["user_login"];
                    $_SESSION['role_id']=$groups[0];

                    $_SESSION['tenantId']=$result['tenant_id'];
                    $_SESSION['gameTenantId']=$result['game_tenant_id'];

                    $_SESSION['tenantIds']=$result['tenant_id'];
                    $_SESSION['gameTenantIds']=$result['game_tenant_id'];
                    $_SESSION['admin_type']=$result['admin_type'];

    				$result['last_login_ip']=get_client_ip(0,true);
    				$result['last_login_time']=date("Y-m-d H:i:s");
    				$user->save($result);
                    $action="后台登陆";
                    setAdminLog($action,13);
    				setcookie("admin_username",$name,time()+30*24*3600,"/");
                    delUserInfoCache($result["id"]);
    				if($result['admin_type'] == 1 ){

                        $this->success(L('LOGIN_SUCCESS'),U("Index/indexfamily"));
                    }else{
                        $this->success(L('LOGIN_SUCCESS'),U("Index/index"));
                    }

    			}else{
    				$this->error(L('PASSWORD_NOT_RIGHT'));
    			}
    		}else{
    			$this->error(L('USERNAME_NOT_EXIST'));
    		}
    	}
    }

}