<?php

class Domain_Login {

    public function internalUserLogin($game_user_id,$user_login,$user_pass,$tenantId,$zone,$avatar=null,$avatar_thumb=null,$user_nicename,$last_login_ip=null) {
        $rs = array();

        $model = new Model_Login();
        $rs = $model->internalUserLogin($game_user_id,$user_login,$user_pass,$tenantId,$zone,$avatar,$avatar_thumb,$user_nicename,$last_login_ip);

        return $rs;
    }

    public function userLogin($user_login,$user_pass,$tenantId,$zone) {
        $rs = array();

        $model = new Model_Login();
        $rs = $model->userLogin($user_login,$user_pass,$tenantId,$zone);

        return $rs;
    }

    public function loginmessage($user_login,$zone,$tenantId){
        $rs = array();
        $model = new Model_Login();
        $rs = $model->loginmessage($user_login,$zone,$tenantId);
        return $rs;
    }
    public function userLoginvutar($user_login,$user_pass,$zone) {
        $rs = array();

        $model = new Model_Login();
        $rs = $model->userLoginvutar($user_login,$user_pass,$zone);

        return $rs;
    }
    public function userReg($user_login,$user_pass,$source,$tenantId,$zone,$game_user_id=null,$nicename=null,$avatar=null,$avatar_thumb=null,$agent_code=null,$reg_url ='',$reg_key='') {
        $rs = array();
        $model = new Model_Login();
        $rs = $model->userReg($user_login,$user_pass,$source,$tenantId,$zone,$game_user_id,$nicename,$avatar,$avatar_thumb,$agent_code,$reg_url ,$reg_key);

        return $rs;
    }
    public  function userGetcode($user_login){
        $rs = array();
        $model = new Model_Login();
        $rs = $model->userGetcode($user_login);

        return $rs;
    }
	
    public function userFindPass($user_login,$user_pass,$zone) {
        $rs = array();
        $model = new Model_Login();
        $rs = $model->userFindPass($user_login,$user_pass,$zone);

        return $rs;
    }	

    public function userLoginByThird($openid,$type,$nickname,$avatar,$source) {
        $rs = array();

        $model = new Model_Login();
        $rs = $model->userLoginByThird($openid,$type,$nickname,$avatar,$source);

        return $rs;
    }

    public function upUserPush($uid,$pushid) {
        $rs = array();

        $model = new Model_Login();
        $rs = $model->upUserPush($uid,$pushid);

        return $rs;
    }
    public  function invutar(){
        $rs = array();

        $model = new Model_Login();
        $rs = $model->invutar();

        return $rs;
    }

    public  function firlogregReward($uid,$type)
    {
        $model = new Model_Login();
        $rs = $model->firlogregReward($uid,$type);

        return $rs;
    }

}
