<?php
//session_start();
class Api_Login extends PhalApi_Api { 
	public function getRules() {
        return array(
			'userLogin' => array(
                'user_login' => array('name' => 'user_login', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '6',  'max'=>'30', 'desc' => '账号'),
				'user_pass' => array('name' => 'user_pass', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '密码'),
				'pushid' => array('name' => 'pushid', 'type' => 'string', 'desc' => '极光ID'),
                'zone' => array('name' => 'zone', 'type' => 'string', 'min' => 1,    'desc' => '区号'),
				//'code' => array('name' => 'code', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '验证码'),
            ),
            'userLoginvutar' => array(
                'user_login' => array('name' => 'user_login', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '6',  'max'=>'30', 'desc' => '账号'),
                'user_pass' => array('name' => 'user_pass', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '密码'),
                 'zone' => array('name' => 'zone', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '区号'),
                //'code' => array('name' => 'code', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '验证码'),
            ),
            'internalUserLoginOrReg' => array(
                'game_user_id' => array('name' => 'game_user_id', 'type' => 'string', 'min' => 1, 'require' => true, 'max'=>'30', 'desc' => '游戏系统账号id'),
                'user_login' => array('name' => 'user_login', 'type' => 'string',  'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '直播系统账号名'),
                'user_pass' => array('name' => 'user_pass', 'type' => 'string', 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '直播系统帐号密码'),
                'user_nicename' => array('name' => 'user_nicename', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'50', 'desc' => '直播系统帐号昵称'),
                'avatar' => array('name' => 'avatar', 'type' => 'string', 'min' => 0, 'require' => false,  'min' => '0',  'max'=>'255', 'desc' => '头像'),
                'avatar_thumb' => array('name' => 'avatar_thumb', 'type' => 'string', 'min' => 0, 'require' => false,  'min' => '0',  'max'=>'255', 'desc' => '头像小图'),
                'source' => array('name' => 'source', 'type' => 'string',  'default'=>'pc', 'desc' => '来源设备'),
                'pushid' => array('name' => 'pushid', 'type' => 'string', 'desc' => '极光ID'),
                'sign' => array('name' => 'sign', 'type' => 'String', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'255', 'desc' => '签名字符串'),
                'last_login_ip' => array('name' => 'last_login_ip', 'type' => 'string', 'desc' => '极光ID')
            ),
            'importUserLogin' => array(
                'game_user_id' => array('name' => 'game_user_id', 'type' => 'string', 'min' => 1, 'require' => true, 'max'=>'30', 'desc' => '游戏系统账号id'),
                'user_login' => array('name' => 'user_login', 'type' => 'string',  'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '直播系统账号名'),
                'user_pass' => array('name' => 'user_pass', 'type' => 'string', 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '直播系统帐号密码'),
                'user_nicename' => array('name' => 'user_nicename', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'50', 'desc' => '直播系统帐号昵称'),
                'avatar' => array('name' => 'avatar', 'type' => 'string', 'min' => 0, 'require' => false,  'min' => '0',  'max'=>'255', 'desc' => '头像'),
                'avatar_thumb' => array('name' => 'avatar_thumb', 'type' => 'string', 'min' => 0, 'require' => false,  'min' => '0',  'max'=>'255', 'desc' => '头像小图'),
                'source' => array('name' => 'source', 'type' => 'string',  'default'=>'pc', 'desc' => '来源设备'),
                'pushid' => array('name' => 'pushid', 'type' => 'string', 'desc' => '极光ID'),
                'sign' => array('name' => 'sign', 'type' => 'String', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'255', 'desc' => '签名字符串')
            ),

			'userReg' => array(
                'user_login' => array('name' => 'user_login', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '6',  'max'=>'30', 'desc' => '账号'),
                'user_pass' => array('name' => 'user_pass', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '密码'),
				'user_pass2' => array('name' => 'user_pass2', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '确认密码'),
                'code' => array('name' => 'code', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '验证码'),
                'agent_code' => array('name' => 'agent_code', 'type' => 'string', 'default'=>'','require' => false, 'desc' => '邀请码'),

                'source' => array('name' => 'source', 'type' => 'string',  'default'=>'pc', 'desc' => '来源设备'),
                'zone' => array('name' => 'zone', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '区号'),

            ),
            'userAutomaticReg' => array(

                'source' => array('name' => 'source', 'type' => 'string',  'default'=>'pc', 'desc' => '来源设备'),
                'reg_key' => array('name' => 'key', 'type' => 'string',  'default'=>'', 'desc' => '域名key'),
                'reg_url' => array('name' => 'reg_url', 'type' => 'string',  'default'=>'', 'desc' => '注册域名'),

            ),


            'userFindPass' => array(
                'user_login' => array('name' => 'user_login', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '账号'),
				'user_pass' => array('name' => 'user_pass', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '密码'),
				'user_pass2' => array('name' => 'user_pass2', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '确认密码'),
                'code' => array('name' => 'code', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '验证码'),
                'zone' => array('name' => 'zone', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '区号'),
            ),
			'userLoginByThird' => array(
                'openid' => array('name' => 'openid', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '第三方openid'),
                'type' => array('name' => 'type', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '第三方标识'),
                'nicename' => array('name' => 'nicename', 'type' => 'string',   'default'=>'',  'desc' => '第三方昵称'),
                'avatar' => array('name' => 'avatar', 'type' => 'string',  'default'=>'', 'desc' => '第三方头像'),
                'sign' => array('name' => 'sign', 'type' => 'string',  'default'=>'', 'desc' => '签名'),
                'source' => array('name' => 'source', 'type' => 'string',  'default'=>'pc', 'desc' => '来源设备'),
                'pushid' => array('name' => 'pushid', 'type' => 'string', 'desc' => '极光ID'),

            ),

			'getCode' => array(
				'mobile' => array('name' => 'mobile', 'type' => 'string', 'min' => 1, 'require' => true,  'desc' => '手机号'),
                'sign' => array('name' => 'sign', 'type' => 'string',  'default'=>'', 'desc' => '签名'),
                'zone' => array('name' => 'zone', 'type' => 'string', 'min' => 1, 'require' => true,  'default'=>'86', 'desc' => '区号：86, 63 ...'),
			),
            'loginCode' => array(
                'mobile' => array('name' => 'mobile', 'type' => 'string', 'min' => 1, 'require' => true,  'desc' => '手机号'),
                'sign' => array('name' => 'sign', 'type' => 'string',  'default'=>'', 'desc' => '签名'),
                'zone' => array('name' => 'zone', 'type' => 'string', 'min' => 1, 'require' => true,  'default'=>'86', 'desc' => '区号：86, 63 ...'),
            ),
			'getForgetCode' => array(
				'mobile' => array('name' => 'mobile', 'type' => 'string', 'min' => 1, 'require' => true,  'desc' => '手机号'),
                'sign' => array('name' => 'sign', 'type' => 'string',  'default'=>'', 'desc' => '签名'),
                'zone' => array('name' => 'zone', 'type' => 'string', 'min' => 1, 'require' => true,  'default'=>'86', 'desc' => '区号：86, 63 ...'),
			),
            'getUnionid' => array(
				'code' => array('name' => 'code', 'type' => 'string','desc' => '微信code'),

			),

            'logout' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
			),
            'invutar' => array(
            ),
            'loginmessage' => array(
                'mobile' => array('name' => 'mobile', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '6',  'max'=>'30', 'desc' => '账号'),
                'code' => array('name' => 'code', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '验证码'),
                'zone' => array('name' => 'zone', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '区号'),

            ),
            'firlogregReward' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true,   'desc' => '类型：2登录'),
            ),
        );

	}

    /**
     * @desc 用于内嵌app用户登录
     * 内嵌app用户登录
     * 作为内嵌模块时,接收第三方传来的用户名,密码,昵称,头像信息进行登录操作,如不存在则自动注册并且登录
     */
    public function internalUserLoginOrReg(){
        $rs = array('code' => 0, 'msg' => '注册成功', 'info' => array());
        $game_user_id=checkNull($this->game_user_id);
        $user_login=checkNull($this->user_login);
        $user_pass=checkNull($this->user_pass);
        $user_nicename=checkNull($this->user_nicename);
        $tenant_id=checkNull( getTenantId());
        $pushid=checkNull($this->pushid);
        $source=checkNull($this->source);
        $sign=checkNull($this->sign);
        $avatar=checkNull($this->avatar);
        $avatar_thumb=checkNull($this->avatar_thumb);
        $last_login_ip = real_ip();
        $language_id=isset($_REQUEST['language_id'])?$_REQUEST['language_id']:101;


        /**
         * TODO 签名校验
         */
        $tenantInfo=getTenantInfo($tenant_id);
        if(is_null($tenantInfo)){
            $rs['code'] = 1001;
            $rs['msg'] = '租户不存在';
            return $rs;
        }
        $checkData=array(
            'user_login'=>$user_login,
            'user_pass'=>$user_pass,
            'game_user_id'=>$game_user_id
        );

        $issign=checkSign($checkData,$sign);
        if(!$issign){
            $rs['code']=1001;
            $rs['msg']='签名错误';
            return $rs;
        }
        $zone = 86;
        $domain = new Domain_Login();
        $info = $domain->internalUserLogin($game_user_id,$user_login,$user_pass,$tenant_id,$zone,$avatar,$avatar_thumb,$user_nicename,$last_login_ip);
        if($info==1001){
            //用户不存在,自动注册
            $info = $domain->userReg($user_login,$user_pass,$source,$tenant_id,$zone,$game_user_id,$user_nicename,$avatar,$avatar_thumb);

            if($info==1006){
                $language = DI()->config->get('language.phonenumber_already');
                $rs['code'] = 1006;
                $rs['msg'] = $language[$language_id];//该手机号或帐号已被注册;
                return $rs;
            }else if($info==1007){
                $rs['code'] = 1007;
                $rs['msg'] = '注册失败，请重试';
                return $rs;
            }
            //注册完后重新登录一遍
            //$info = $domain->internalUserLogin($game_user_id,$user_login,$user_pass,$tenant_id,$zone);
            $info = $domain->internalUserLogin($game_user_id,$user_login,$user_pass,$tenant_id,$zone,$avatar,$avatar_thumb,$user_nicename,$last_login_ip);

        }else if($info==1002){
            $rs['code'] = 1002;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }

        $rs['info'][0] = $info;

        //更新极光推送id
        if($info!=1001 && $pushid){
            $domain->upUserPush($info['id'],$pushid);
        }

        return $rs;
    }
    /**
     * @desc 用于内嵌app用户登录
     * 内嵌app用户登录
     * 作为内嵌模块时,接收第三方传来的用户名,密码,昵称,头像信息进行登录操作,如不存在则自动注册并且登录
     */
    public function importUserLogin(){
        $rs = array('code' => 0, 'msg' => '注册成功', 'info' => array());
        $game_user_id=checkNull($this->game_user_id);
        $user_login=checkNull($this->user_login);
        $user_pass=checkNull($this->user_pass);
        $user_nicename=checkNull($this->user_nicename);
        $tenant_id=checkNull( getTenantId());
        $pushid=checkNull($this->pushid);
        $source=checkNull($this->source);
        $sign=checkNull($this->sign);
        $avatar=checkNull($this->avatar);
        $avatar_thumb=checkNull($this->avatar_thumb);

        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }

        /**
         * TODO 签名校验
         */
        $tenantInfo=getTenantInfo($tenant_id);
        if(is_null($tenantInfo)){
            $rs['code'] = 1001;
            $rs['msg'] = '租户不存在';
            return $rs;
        }
        $checkData=array(
            'user_login'=>$user_login,
            'user_pass'=>$user_pass,
            'game_user_id'=>$game_user_id
        );

        $issign=checkSign($checkData,$sign);
        /*if(!$issign){
            $rs['code']=1001;
            $rs['msg']='签名错误';
            return $rs;
        }*/
        $zone = 86;
        $domain = new Domain_Login();
       // $info = $domain->internalUserLogin($game_user_id,$user_login,$user_pass,$tenant_id,$zone,$avatar,$avatar_thumb,$user_nicename);
       // if($info==1001){
            //用户不存在,自动注册
            $info = $domain->userReg($user_login,$user_pass,$source,$tenant_id,$zone,$game_user_id,$user_nicename,$avatar,$avatar_thumb);

            if($info==1006){
                $language = DI()->config->get('language.phonenumber_already');
                $rs['code'] = 1006;
                $rs['msg'] = $language[$language_id];//该手机号或帐号已被注册;
                return $rs;
            }else if($info==1007){
                $rs['code'] = 1007;
                $rs['msg'] = '注册失败，请重试';
                return $rs;
            }
            //注册完后重新登录一遍
            //$info = $domain->internalUserLogin($game_user_id,$user_login,$user_pass,$tenant_id,$zone);
            //$info = $domain->internalUserLogin($game_user_id,$user_login,$user_pass,$tenant_id,$zone,$avatar,$avatar_thumb,$user_nicename);



        $rs['info'][0] = $info;

        return $rs;
    }
    /**
     * 会员登陆 需要密码
     * @desc 用于用户登陆信息
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 用户信息
     * @return string info[0].id 用户ID
     * @return string info[0].user_nicename 昵称
     * @return string info[0].avatar 头像
     * @return string info[0].avatar_thumb 头像缩略图
     * @return string info[0].sex 性别
     * @return string info[0].signature 签名
     * @return string info[0].coin 用户余额
     * @return string info[0].login_type 注册类型
     * @return string info[0].level 等级(老的)
     * @return string info[0].new_level 会员等级(新)
     * @return string info[0].level_name 会员等级名称
     * @return string info[0].level_addtime 兑换时间
     * @return string info[0].level_endtime 过期时间
     * @return string info[0].province 省份
     * @return string info[0].city 城市
     * @return string info[0].birthday 生日
     * @return string info[0].token 用户Token
     * @return int info[0].user_vip_status 用户vip状态：0.未缴纳，1.生效中，2.退款中，3.已退款，4.审核中
     * @return string info[0].user_vip_create_time 购买时间
     * @return string info[0].user_vip_update_time 更新时间
     * @return string info[0].user_vip_refund_time 退款时间
     * @return int info[0].user_vip_checking_level 正在审核中的vip等级
     */
    public function userLogin() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		$user_login=checkNull($this->user_login);
		$user_pass=checkNull($this->user_pass);
		$pushid=checkNull($this->pushid);
        $zone=checkNull($this->zone);
		$tenantId=getTenantId();

        $domain = new Domain_Login();
        $info = $domain->userLogin($user_login,$user_pass,$tenantId,$zone);

		if($info==1001){
			$rs['code'] = 1001;
            $rs['msg'] = '账号或密码错误';
            return $rs;
		}else if($info==1002){
			$rs['code'] = 1002;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
		}
        $info['user_pass'] = $user_pass;
        $rs['info'][0] = $info;

        if($pushid){
             $domain->upUserPush($info['id'],$pushid);
        }


        return $rs;
    }
   /**
     * 会员注册
     * @desc 用于用户注册信息
     * @return int code 操作码，0表示成功
     * @return array info 用户信息
     * @return string info[0].id 用户ID
     * @return string info[0].user_nicename 昵称
     * @return string info[0].avatar 头像
     * @return string info[0].avatar_thumb 头像缩略图
     * @return string info[0].sex 性别
     * @return string info[0].signature 签名
     * @return string info[0].coin 用户余额
     * @return string info[0].login_type 注册类型
     * @return string info[0].level 等级
     * @return string info[0].province 省份
     * @return string info[0].city 城市
     * @return string info[0].birthday 生日
     * @return string info[0].token 用户Token
     * @return string info[0].agent_code 邀请码
     * @return string msg 提示信息
     */
    public function userReg() {
        $rs = array('code' => 0, 'msg' => '注册成功', 'info' => array());
        $redis = connectionRedis();

		$user_login=checkNull($this->user_login);
		$user_pass=checkNull($this->user_pass);
		$user_pass2=checkNull($this->user_pass2);
		$source=checkNull($this->source);
		$code=checkNull($this->code);
		$tenantId=getTenantId();
        $language_id=$this->language_id;
        $zone=checkNull($this->zone);
        $agent_code=checkNull($this->agent_code);

        $sm_key = 'shortmsg_'.$_SESSION['session_id'].$user_login;
        $sm_data = getcache($sm_key);
        $config = getConfigPri();

        if($config['sendcode_switch']){
            if(!$sm_data){
                $rs['code'] = 2067;
                $rs['msg'] = codemsg('2067');
                return $rs;
            }
            if(!$sm_data || !$sm_data['reg_mobile'] || !$sm_data['reg_mobile_code']){
                $rs['code'] = 2067;
                $rs['msg'] = codemsg('2067');
                return $rs;
            }
            if($user_login!=$sm_data['reg_mobile']){
                $rs['code'] = 2068;
                $rs['msg'] = codemsg('2068');
                return $rs;
            }
            if($code!=$sm_data['reg_mobile_code']){
                $rs['code'] = 2069;
                $rs['msg'] = codemsg('2069');
                return $rs;
            }
        }

        if (empty($language_id)){
            $language_id = 101;
        }
		if($user_pass!=$user_pass2){
            $language = DI()->config->get('language.userreg_passcheck');
            $rs['code'] = 1003;
            $rs['msg'] = $language[$language_id];//两次输入的密码不一致
            return $rs;
		}

		$check = passcheck($user_pass);

		if($check==0){
            $language = DI()->config->get('language.userreg_passlength');
            $rs['code'] = 1004;
            $rs['msg'] = $language[$language_id];//密码6-12位数字与字母
            return $rs;
        }else if($check==2){
            $language = DI()->config->get('language.userreg_onlynum');
            $rs['code'] = 1005;//
            $rs['msg'] = $language[$language_id];//密码不能纯数字或纯字母
            return $rs;
        }
		$domain = new Domain_Login();
		$info = $domain->userReg($user_login,$user_pass,$source,$tenantId,$zone,null,null,null,null,$agent_code);

		if($info==1006){
            $language = DI()->config->get('language.userreg_already');
			$rs['code'] = 1006;
            $rs['msg'] = $language[$language_id];//该手机号已被注册
            return $rs;
		}else if($info==1002){
            $language = DI()->config->get('language.agent_code_error');
            $rs['code'] = 1002;
            $rs['msg'] = $language[$language_id];//邀请码错误
            return $rs;
        }else if($info==1007){
            $language = DI()->config->get('language.userreg_fail');
			$rs['code'] = 1007;
            $rs['msg'] = $language[$language_id];//注册失败，请重试
            return $rs;
		}
        $info = $domain->userGetcode($user_login);

        $rs['info'][0] = $info;

		$redis->del($sm_key);

        $language = DI()->config->get('language.userregsucc');//注册成功
        $rs['msg'] = $language[$language_id];
        return $rs;
    }
	/**
     * 会员找回密码
     * @desc 用于会员找回密码
     * @return int code 操作码，0表示成功，1表示验证码错误，2表示用户密码不一致,3短信手机和登录手机不一致 4、用户不存在 801 密码6-12位数字与字母
     * @return array info
     * @return string msg 提示信息
     */
    public function userFindPass() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $redis = connectionRedis();

		$user_login=checkNull($this->user_login);
		$user_pass=checkNull($this->user_pass);
		$user_pass2=checkNull($this->user_pass2);
		$code=checkNull($this->code);
        $zone=checkNull($this->zone);

        $sm_key = 'shortmsg_'.$_SESSION['session_id'].$user_login;
        $sm_data = getcache($sm_key);

        $config = getConfigPri();

        if($config['sendcode_switch']){
            if(!$sm_data || !$sm_data['forget_mobile'] || !$sm_data['forget_mobile_code']){
                $rs['code'] = 2067;
                $rs['msg'] = codemsg('2067');
                return $rs;
            }
            if($user_login!=$sm_data['forget_mobile']){
                $rs['code'] = 2068;
                $rs['msg'] = codemsg('2068');
                return $rs;
            }
            if($code!=$sm_data['forget_mobile_code']){
                $rs['code'] = 2069;
                $rs['msg'] = codemsg('2069');
                return $rs;
            }
        }else{
            if($code != '123456'){
                $rs['code'] = 2069;
                $rs['msg'] = codemsg('2069');
                return $rs;
            }
        }

		if($user_pass!=$user_pass2){
            $rs['code'] = 1003;
            $rs['msg'] = '两次输入的密码不一致';
            return $rs;
		}

		$check = passcheck($user_pass);
		if($check== 0 ){
            $rs['code'] = 1004;
            $rs['msg'] = '密码6-12位数字与字母';
            return $rs;
        }else if($check== 2){
            $rs['code'] = 1005;
            $rs['msg'] = '密码不能纯数字或纯字母';
            return $rs;
        }

		$domain = new Domain_Login();
        $info = $domain->userFindPass($user_login,$user_pass,$zone);

		if($info==1006){
			$rs['code'] = 1006;
            $rs['msg'] = '该帐号不存在';
            return $rs;
		}else if($info===false){
			$rs['code'] = 1007;
            $rs['msg'] = '重置失败，请重试';
            return $rs;
		}

		$_SESSION['forget_mobile'] = '';
		$_SESSION['forget_mobile_code'] = '';
		$_SESSION['forget_mobile_expiretime'] = '';

        return $rs;
    }

    /**
     * 第三方登录
     * @desc 用于用户登陆信息
     * @return int code 操作码，0表示成功
     * @return array info 用户信息
     * @return string info[0].id 用户ID
     * @return string info[0].user_nicename 昵称
     * @return string info[0].avatar 头像
     * @return string info[0].avatar_thumb 头像缩略图
     * @return string info[0].sex 性别
     * @return string info[0].signature 签名
     * @return string info[0].coin 用户余额
     * @return string info[0].login_type 注册类型
     * @return string info[0].level 等级
     * @return string info[0].province 省份
     * @return string info[0].city 城市
     * @return string info[0].birthday 生日
     * @return string info[0].token 用户Token
     * @return string msg 提示信息
     */
    public function userLoginByThird() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
		$openid=checkNull($this->openid);
		$type=checkNull($this->type);
		$nicename=checkNull($this->nicename);
		$avatar=checkNull($this->avatar);
		$source=checkNull($this->source);
		$sign=checkNull($this->sign);
		$pushid=checkNull($this->pushid);


        $checkdata=array(
            'openid'=>$openid
        );

        $issign=checkSign($checkdata,$sign);
        if(!$issign){
            $rs['code']=1001;
			$rs['msg']='签名错误';
			return $rs;
        }



        $domain = new Domain_Login();
        $info = $domain->userLoginByThird($openid,$type,$nicename,$avatar,$source);

        if($info==1001){
            $rs['code'] = 1001;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
		}

        $rs['info'][0] = $info;

        if($pushid){
            $domain->upUserPush($info['id'],$pushid);
        }

        return $rs;
    }

    /**
     * 获取登录短信验证码
     * @desc 用于登录获取短信验证码
     * @return int code 操作码，0表示成功,2发送失败
     * @return array info
     * @return string msg 提示信息
     */
    public function loginCode() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $redis = connectionRedis();

        $mobile = checkNull($this->mobile);
        $sign = checkNull($this->sign);
        $zone = $this->zone;
        $country = $zone ? country($zone)['locale'] : 'CN'; // 默认中国大陆手机号
        if(!$country || !isset(shortmsg()['yd'][$country])){
            $rs['code']=2063;
            $rs['msg']=codemsg('2063');
            return $rs;
        }
        $sm_key = 'shortmsg_'.$_SESSION['session_id'].$mobile;
        $sm_data = getcache($sm_key);

        $ismobile=checkMobile($mobile,$country);
        if(!$ismobile){
            $rs['code']=2071;
            $rs['msg']=codemsg('2071');
            return $rs;
        }

        $checkdata=array(
            'mobile'=>$mobile
        );

        // $issign=checkSign($checkdata,$sign);
        // if(!$issign){
        //     $rs['code']=2072;
        //     $rs['msg']=codemsg('2072');
        //     return $rs;
        // }

        //$where="user_login='{$mobile}'";
        $where="mobile='{$mobile}'";

        $checkuser = checkUser($where);

        if(!$checkuser){
            $rs['code']=2065;
            $rs['msg']=codemsg('2065');
            return $rs;
        }

        if($sm_data && $sm_data['login_mobile']==$mobile){
            $rs['code']=2073;
            $rs['msg']=codemsg('2073');
            return $rs;
        }

        $limit = ip_limit($mobile);
        if( $limit == 1){
            $rs['code']=2074;
            $rs['msg']=codemsg('2074');
            return $rs;
        }
        $mobile_code = random(6,1);

        /* 发送验证码 */
        $result=sendCodeYD($mobile,$mobile_code,$country);
        if($result['code']==0){
            $shortmsg_data['login_mobile'] = $mobile;
            $shortmsg_data['login_mobile_code'] = $mobile_code;
            $rs['code']=0;
            $rs['msg']=codemsg('2075').'：'.$result['msg'];
        }else if($result['code']==667){
            $shortmsg_data['login_mobile'] = $mobile;
            $shortmsg_data['login_mobile_code'] = $result['msg'];
            $rs['code']=0;
            $rs['msg']=codemsg('2075').'：'.$result['msg'];
        }else{
            $result=sendCodeYP($mobile, $mobile_code, $country);
            if($result['code']==0){
                $shortmsg_data['login_mobile'] = $mobile;
                $shortmsg_data['login_mobile_code'] = $mobile_code;
                $rs['code']=0;
                $rs['msg']=codemsg('2075').'：'.$result['msg'];
            }else if($result['code']==667){
                $shortmsg_data['login_mobile'] = $mobile;
                $shortmsg_data['login_mobile_code'] = $result['msg'];
                $rs['code']=0;
                $rs['msg']=codemsg('2075').'：'.$result['msg'];
            }else{
                $rs['code']=2076;
                $rs['msg']=codemsg('2076');
            }
        }
        $redis->set($sm_key,json_encode($shortmsg_data),60*5);
        return $rs;
    }

    /**
	 * 获取注册短信验证码
	 * @desc 用于注册获取短信验证码
	 * @return int code 操作码，0表示成功,2发送失败
	 * @return array info
	 * @return string msg 提示信息
	 */
	public function getCode() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$redis = connectionRedis();

		$mobile = checkNull($this->mobile);
		$sign = checkNull($this->sign);
        $zone = $this->zone;
        $country = $zone ? country($zone)['locale'] : 'CN'; // 默认中国大陆手机号
        if(!$country || !isset(shortmsg()['yd'][$country])){
            $rs['code']=2063;
            $rs['msg']=codemsg('2063');
            return $rs;
        }

        $sm_key = 'shortmsg_'.$_SESSION['session_id'].$mobile;
        $sm_data = getcache($sm_key);

		$ismobile=checkMobile($mobile,$country);
		if(!$ismobile){
			$rs['code']=2071;
			$rs['msg']=codemsg('2071');
			return $rs;
		}

        // $checkdata=array(
        //     'mobile'=>$mobile
        // );

        // $issign=checkSign($checkdata,$sign);
        // if(!$issign){
        //     $rs['code']=2072;
		// 	$rs['msg']=codemsg('2072');
		// 	return $rs;
        // }

        $where="user_login='{$mobile}'";

		$checkuser = checkUser($where);

        if($checkuser){
            $rs['code']=2077;
			$rs['msg']=codemsg('2077');
			return $rs;
        }

		if($sm_data && $sm_data['reg_mobile']==$mobile){
			$rs['code']=2073;
			$rs['msg']=codemsg('2073');
			return $rs;
		}

        $limit = ip_limit($mobile);
		if( $limit == 1){
			$rs['code']=2074;
			$rs['msg']=codemsg('2074');
			return $rs;
		}
		$mobile_code = random(6,1);

		/* 发送验证码 */
 		$result=sendCodeYD($mobile,$mobile_code,$country);
		if($result['code']==0){
            $shortmsg_data['reg_mobile'] = $mobile;
            $shortmsg_data['reg_mobile_code'] = $mobile_code;
            $rs['code']=0;
            $rs['msg']=codemsg('2075').'：'.$result['msg'];
		}else if($result['code']==667){
            $shortmsg_data['reg_mobile'] = $mobile;
            $shortmsg_data['reg_mobile_code'] = $result['msg'];
            $rs['code']=0;
			$rs['msg']=codemsg('2075').'：'.$result['msg'];
		}else{
            $result=sendCodeYP($mobile, $mobile_code, $country);
            if($result['code']==0){
                $shortmsg_data['reg_mobile'] = $mobile;
                $shortmsg_data['reg_mobile_code'] = $mobile_code;
                $rs['code']=0;
                $rs['msg']=codemsg('2075').'：'.$result['msg'];
            }else if($result['code']==667){
                $shortmsg_data['reg_mobile'] = $mobile;
                $shortmsg_data['reg_mobile_code'] = $result['msg'];
                $rs['code']=0;
                $rs['msg']=codemsg('2075').'：'.$result['msg'];
            }else{
                $rs['code']=2076;
                $rs['msg']=codemsg('2076');
            }
		}
        $redis->set($sm_key,json_encode($shortmsg_data),60*5);
		return $rs;
	}

	/**
	 * 获取找回密码短信验证码
	 * @desc 用于找回密码获取短信验证码
	 * @return int code 操作码，0表示成功,2发送失败
	 * @return array info
	 * @return string msg 提示信息
	 */
	public function getForgetCode() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$redis = connectionRedis();

		$mobile = checkNull($this->mobile);
		$sign = checkNull($this->sign);
        $zone = $this->zone;
        $country = $zone ? country($zone)['locale'] : 'CN'; // 默认中国大陆手机号
        if(!$country || !isset(shortmsg()['yd'][$country])){
            $rs['code']=2063;
            $rs['msg']=codemsg('2063');
            return $rs;
        }

        $sm_key = 'shortmsg_'.$_SESSION['session_id'].$mobile;
        $sm_data = getcache($sm_key);

        $ismobile=checkMobile($mobile,$country);
		if(!$ismobile){
			$rs['code']=2071;
			$rs['msg']=codemsg('2071');
			return $rs;
		}

        $checkdata=array(
            'mobile'=>$mobile
        );

        $issign=checkSign($checkdata,$sign);
        if(!$issign){
            $rs['code']=2072;
			$rs['msg']=codemsg('2072');
			return $rs;
        }

        $where="user_login='{$mobile}'";
        $checkuser = checkUser($where);

        if(!$checkuser){
            $rs['code']=2065;
			$rs['msg']=codemsg('2065');
			return $rs;
        }

		if($sm_data && $sm_data['forget_mobile']==$mobile){
			$rs['code']=2073;
			$rs['msg']=codemsg('2073');
			return $rs;
		}

        $limit = ip_limit($mobile);
		if( $limit == 1){
			$rs['code']=2074;
			$rs['msg']=codemsg('2074');
			return $rs;
		}
		$mobile_code = random(6,1);

        /* 发送验证码 */
        $result=sendCodeYD($mobile,$mobile_code,$country);
        if($result['code']==0){
            $shortmsg_data['forget_mobile'] = $mobile;
            $shortmsg_data['forget_mobile_code'] = $mobile_code;
            $rs['code']=0;
            $rs['msg']=codemsg('2075').'：'.$result['msg'];
        }else if($result['code']==667){
            $shortmsg_data['forget_mobile'] = $mobile;
            $shortmsg_data['forget_mobile_code'] = $mobile_code;
            $rs['code']=0;
            $rs['msg']=codemsg('2075').'：'.$result['msg'];
        }else{
            $result=sendCodeYP($mobile, $mobile_code, $country);
            if($result['code']==0){
                $shortmsg_data['forget_mobile'] = $mobile;
                $shortmsg_data['forget_mobile_code'] = $mobile_code;
                $rs['code']=0;
                $rs['msg']=codemsg('2075').'：'.$result['msg'];
            }else if($result['code']==667){
                $shortmsg_data['forget_mobile'] = $mobile;
                $shortmsg_data['forget_mobile_code'] = $mobile_code;
                $rs['code']=0;
                $rs['msg']=codemsg('2075').'：'.$result['msg'];
            }else{
                $rs['code']=2076;
                $rs['msg']=codemsg('2076');
            }
        }
        $redis->set($sm_key,json_encode($shortmsg_data),60*5);
		return $rs;
	}

	/**
	 * 获取微信登录unionid
	 * @desc 用于获取微信登录unionid
	 * @return int code 操作码，0表示成功,2发送失败
	 * @return array info
	 * @return string info[0].unionid 微信unionid
	 * @return string msg 提示信息
	 */
    public function getUnionid(){

        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $code=checkNull($this->code);

        if($code==''){
            $rs['code']=1001;
			$rs['msg']='参数错误';
			return $rs;

        }

        //$configpri=getConfigPri();

        //$AppID = $configpri['login_wx_appid'];
        //$AppSecret = $configpri['login_wx_appsecret'];
        $AppID = 'wxbee8d98b9852d612';
        $AppSecret = 'f9d4f74d9412691eeb271dc7632f24b6';
        /* 获取token */
        //$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$AppID}&secret={$AppSecret}&code={$code}&grant_type=authorization_code";
        $url="https://api.weixin.qq.com/sns/jscode2session?appid={$AppID}&secret={$AppSecret}&js_code={$code}&grant_type=authorization_code";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        $json =  curl_exec($ch);
        curl_close($ch);
        $arr=json_decode($json,1);
        //file_put_contents('./getUnionid.txt',date('Y-m-d H:i:s').' 提交参数信息 code:'.json_encode($code)."\r\n",FILE_APPEND);
        //file_put_contents('./getUnionid.txt',date('Y-m-d H:i:s').' 提交参数信息 arr:'.json_encode($arr)."\r\n",FILE_APPEND);
        if($arr['errcode']){
            $rs['code']=1003;
			$rs['msg']='配置错误';
            //file_put_contents('./getUnionid.txt',date('Y-m-d H:i:s').' 提交参数信息 arr:'.json_encode($arr)."\r\n",FILE_APPEND);
			return $rs;
        }



        /* 小程序 绑定到 开放平台 才有 unionid  否则 用 openid  */
        $unionid=$arr['unionid'];

        if(!$unionid){
            //$rs['code']=1002;
			//$rs['msg']='公众号未绑定到开放平台';
			//return $rs;

            $unionid=$arr['openid'];
        }

        $rs['info'][0]['unionid'] = $unionid;
        return $rs;
    }

	/**
	 * 退出
	 * @desc 用于用户退出 注销极光
	 * @return int code 操作码，0表示成功
	 * @return array info
	 * @return string msg 提示信息
	 */
	public function logout() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = $this->uid;
		$token=checkNull($this->token);
        $language_id=$_REQUEST['language_id'];
        if (empty($language_id)){
            $language_id = 101;
        }
		$checkToken=checkToken($uid,$token);
		if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
		}



		$info = userLogout($uid);


		return $rs;
	}
    /**
     * 虚拟会员登陆 需要密码
     * @desc 用于用户登陆信息
     * @return int code 操作码，0表示成功
     * @return array info 用户信息
     * @return string info[0].id 用户ID
     * @return string info[0].user_nicename 昵称
     * @return string info[0].avatar 头像
     * @return string info[0].avatar_thumb 头像缩略图
     * @return string info[0].sex 性别
     * @return string info[0].signature 签名
     * @return string info[0].coin 用户余额
     * @return string info[0].login_type 注册类型
     * @return string info[0].level 等级
     * @return string info[0].province 省份
     * @return string info[0].city 城市
     * @return string info[0].birthday 生日
     * @return string info[0].token 用户Token
     * @return string msg 提示信息
     */
    public function userLoginvutar() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $user_login=checkNull($this->user_login);
        $user_pass=checkNull($this->user_pass);
        $zone=checkNull($this->zone);


        $domain = new Domain_Login();
        $info = $domain->userLoginvutar($user_login,$user_pass,$zone);

        if($info==1001){
            $rs['code'] = 1001;
            $rs['msg'] = '账号或密码错误';
            return $rs;
        }else if($info==1002){
            $rs['code'] = 1002;
            $rs['msg'] = '该账号已被禁用';
            return $rs;
        }else if($info==1004){
            $rs['code'] = 1004;
            $rs['msg'] = '该站点暂无游客模式';
            return $rs;
        }

        $rs['info'][0] = $info;




        return $rs;
    }
    /**
     * 虚拟会员登陆
     * @desc 虚拟会员登陆(游客登录)
     * @return int code 操作码，0表示成功
     * @return array info 用户信息
     * @return string info[0].id 用户ID
     * @return string info[0].user_nicename 昵称
     * @return string info[0].avatar 头像
     *  @return string info[0].user_type  3 虚拟用户
     * @return string info[0].avatar_thumb 头像缩略图
     * @return string info[0].sex 性别
     * @return string info[0].signature 签名
     * @return string info[0].coin 用户余额
     * @return string info[0].login_type 注册类型
     * @return string info[0].level 等级
     * @return string info[0].province 省份
     * @return string info[0].city 城市
     * @return string info[0].birthday 生日
     * @return string info[0].token 用户Token
     * @return string msg 提示信息
     */
    public  function invutar(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $domain = new Domain_Login();
        $info = $domain->invutar();
        $rs['info'][0] = $info;
        return $rs;
    }


    /**
     * 会员登陆 验证码登录
     * @desc 用于用户登陆信息
     * @return int code 操作码，0表示成功
     * @return array info 用户信息
     * @return string info[0].id 用户ID
     * @return string info[0].user_nicename 昵称
     * @return string info[0].avatar 头像
     * @return string info[0].avatar_thumb 头像缩略图
     * @return string info[0].sex 性别
     * @return string info[0].signature 签名
     * @return string info[0].coin 用户余额
     * @return string info[0].login_type 注册类型
     * @return string info[0].level 等级(老的)
     *   @return string info[0].new_level 会员等级(新)
     * @return string info[0].level_name 会员等级名称
     * @return string info[0].level_addtime 兑换时间
     * @return string info[0].level_endtime 过期时间
     * @return string info[0].province 省份
     * @return string info[0].city 城市
     * @return string info[0].birthday 生日
     * @return string info[0].token 用户Token
     * @return string msg 提示信息
     */
    public function loginmessage() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $redis = connectionRedis();

        $mobile=checkNull($this->mobile);
        $code=checkNull($this->code);
        $zone=checkNull($this->zone);
        $tenantId=getTenantId();
        $tenantInfo=getTenantInfo($tenantId);
        if(is_null($tenantInfo)){
            $rs['code'] = 2064;
            $rs['msg'] = codemsg('2064');
            return $rs;
        }
        $where="mobile='{$mobile}'";

        $checkuser = checkUser($where);

        if(!$checkuser){
            $rs['code']=2065;
            $rs['msg']=codemsg('2065');
            return $rs;
        }

        $sm_key = 'shortmsg_'.$_SESSION['session_id'].$mobile;
        $sm_data = getcache($sm_key);

        if(!$sm_data){
            $rs['code'] = 2066;
            $rs['msg'] = codemsg('2066');
            return $rs;
        }
        if(!$sm_data['login_mobile'] || !$sm_data['login_mobile_code']){
            $rs['code'] = 2067;
            $rs['msg'] = codemsg('2067');
            return $rs;
        }
        if($mobile!=$sm_data['login_mobile']){
            $rs['code'] = 2068;
            $rs['msg'] = codemsg('2068');
            return $rs;
        }
        if($code!=$sm_data['login_mobile_code']){
            $rs['code'] = 2069;
            $rs['msg'] = codemsg('2069');
            return $rs;
        }

        $domain = new Domain_Login();
        $info = $domain->loginmessage($mobile,$zone,$tenantId);
        if(!$info){
            $rs['code'] = 0;
            $rs['msg'] = "登录失败";
            return $rs;
        }
        if($info==1002){
            $rs['code'] = 2070;
            $rs['msg'] = codemsg('2070');
            return $rs;
        }

        $rs['info'][0] = $info;
        $redis->del($sm_key);
        /*if($pushid){
            $domain->upUserPush($info['id'],$pushid);
        }*/

        return $rs;
    }

    /**
     * 注册和首次登录奖励
     * @desc 用于注册和首次登录奖励
     * @return string msg 提示信息
     * @return array info
     * @return string info[0].coin 可提现钻石
     * @return string info[0].nowithdrawable_coin 不可提现钻石
     * @return string info[0].withdrawable_coding 可提现钻石打码量
     * @return string info[0].reward_level 奖励等级：1 一等奖，2 二等奖，3 三等奖
     */
    public function firlogregReward(){
        $rs = array('code' => 0, 'msg' => '奖励成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $type = $this->type;
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }
        $domain = new Domain_Login();
        $info = $domain->firlogregReward($uid,$type);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }
    /**
     * 自动注册
     * @desc 自动注册
     * @return string msg 提示信息

     */
    public function userAutomaticReg(){
        $domain = new Domain_Login();
        $tenantId=getTenantId();
        $user_login= randUserName();
        $user_pass = '123456';
        $language_id=isset($_REQUEST['language_id'])?$_REQUEST['language_id']:101;
        $source=checkNull($this->source);
        $reg_key =checkNull($this->reg_key);
        $reg_url =checkNull($this->reg_url);
        $info = $domain->userReg($user_login,$user_pass,$source,$tenantId,'86',null,null,null,null,'',$reg_url,$reg_key);
        if($info==1006){
            $language = DI()->config->get('language.userreg_already');
            $rs['code'] = 1006;
            $rs['msg'] = $language[$language_id];//该手机号已被注册
            return $rs;
        }else if($info==1002){
            $language = DI()->config->get('language.agent_code_error');
            $rs['code'] = 1002;
            $rs['msg'] = $language[$language_id];//邀请码错误
            return $rs;
        }else if($info==1007){
            $language = DI()->config->get('language.userreg_fail');
            $rs['code'] = 1007;
            $rs['msg'] = $language[$language_id];//注册失败，请重试
            return $rs;
        }
        $info = $domain->userGetcode($user_login);
        $info['user_pass'] =  $user_pass;
        $rs['info'][0] = $info;
        $language = DI()->config->get('language.userregsucc');//注册成功
        $rs['msg'] = $language[$language_id];
        return $rs;
    }

}
