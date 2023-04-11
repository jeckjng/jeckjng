<?php

class Api_Charge extends PhalApi_Api {

	public function getRules() {
		return array(
			'getAliOrder' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'changeid' => array('name' => 'changeid', 'type' => 'int',  'require' => true, 'desc' => '充值规则ID'),
				'coin' => array('name' => 'coin', 'type' => 'string',  'require' => true, 'desc' => '钻石'),
				'money' => array('name' => 'money', 'type' => 'string', 'require' => true, 'desc' => '充值金额'),
			),
			'getWxOrder' => array( 
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'changeid' => array('name' => 'changeid', 'type' => 'string',  'require' => true, 'desc' => '充值规则ID'),
				'coin' => array('name' => 'coin', 'type' => 'string',  'require' => true, 'desc' => '钻石'),
				'money' => array('name' => 'money', 'type' => 'string', 'require' => true, 'desc' => '充值金额'),
			),
			'getIosOrder' => array(
				'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
				'changeid' => array('name' => 'changeid', 'type' => 'string',  'require' => true, 'desc' => '充值规则ID'),
				'coin' => array('name' => 'coin', 'type' => 'string',  'require' => true, 'desc' => '钻石'),
				'money' => array('name' => 'money', 'type' => 'string', 'require' => true, 'desc' => '充值金额'),
			),
            'getChargeChannel' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'reg_key' => array('name' => 'reg_key', 'type' => 'string',  'default'=>'', 'desc' => '域名key'),
                'reg_url' => array('name' => 'reg_url', 'type' => 'string',  'default'=>'', 'desc' => '注册域名'),
                'vip_Id' => array('name' => 'vip_Id', 'type' => 'string',  'default'=>'', 'desc' => '购买会员id'),


            ),
            'getAccountChannel' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'channel_id' => array('name' => 'channel_id', 'type' => 'string', 'require' => true, 'desc' => '支付大类'),
                'reg_key' => array('name' => 'reg_key', 'type' => 'string',  'default'=>'', 'desc' => '域名key'),
                'reg_url' => array('name' => 'reg_url', 'type' => 'string',  'default'=>'', 'desc' => '注册域名'),
                'vip_Id' => array('name' => 'vip_Id', 'type' => 'string',  'default'=>'', 'desc' => '购买会员id'),
            ),
            'pay' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'channel_id' => array('name' => 'channel_id', 'type' => 'string', 'require' => true, 'desc' => '渠道id'),
                'amount' => array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '渠道id'),
                'type' => array('name' => 'type', 'type' => 'string', 'require' => true, 'desc' => '1:线上2线下'),
            ),
            'chargePay' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'account_channel_id' => array('name' => 'account_channel_id', 'type' => 'string', 'require' => true, 'desc' => '渠道id'),
                'amount' => array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '渠道id'),
                'vip_id' => array('name' => 'vip_id', 'type' => 'string',  'desc' => '购买vip充值'),

            ),
            'chargePaynew' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'channel_id' => array('name' => 'channel_id', 'type' => 'string', 'require' => true, 'desc' => '渠道id,支付宝，微信或者银联'),
                'amount' => array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '充值金额'),
                'type' => array('name' => 'type', 'type' => 'string', 'require' => true, 'desc' => '1:线上2线下'),
            ),
            'notify' => array(
                'game_tenant_id' => array('name' => 'game_tenant_id', 'type' => 'string',),
            ),
            'orderList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'status' => array('name' => 'status', 'type' => 'string', 'desc' => '1:待支付，2支付成功，3支付失败'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),
            'offlinpay' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'account_channel_id' => array('name' => 'account_channel_id', 'type' => 'string', 'require' => true, 'desc' => '渠道id'),
                'amount' => array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '金额'),
                'name' => array('name' => 'name', 'type' => 'string', 'require' => true, 'desc' => '姓名'),
                'img' => array('name' => 'img', 'type' => 'string',  'desc' => '电子回单(图片url)'),
                'vip_id' => array('name' => 'vip_id', 'type' => 'string',  'desc' => '购买vip充值'),
            ),
		);
	}
	
	/* 获取订单号 */
	protected function getOrderid($uid){
		$orderid=$uid.'_'.date('YmdHis').rand(100,999);
		return $orderid;
	}

	/**
	 * 微信支付
	 * @desc 用于 微信支付 获取订单号
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0] 支付信息
	 * @return string msg 提示信息
	 */
	public function getWxOrder() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$changeid=$this->changeid;
		$coin=checkNull($this->coin);
		$money=checkNull($this->money);

		$orderid=$this->getOrderid($uid);
		$type=2;
		
		if($coin==0){
			$rs['code']=1002;
			$rs['msg']='信息错误';		
			return $rs;						
		}					
		
		$configpri = getConfigPri(); 
		$configpub = getConfigPub();
        $user_info = getUserInfo($uid);
        $to_user_info = $user_info;

		 //配置参数检测
					
		if($configpri['wx_appid']== "" || $configpri['wx_mchid']== "" || $configpri['wx_key']== ""){
			$rs['code'] = 1002;
			$rs['msg'] = '微信未配置';
			return $rs;					 
		}
		
		$orderinfo=array(
			"uid"=>$uid,
            "user_login" => $user_info['user_login'],
			"touid"=>$uid,
            'to_user_login' => $to_user_info['user_login'],
			"money"=>$money,
			"coin"=>$coin,
			"orderno"=>$orderid,
			"type"=>$type,
			"status"=>0,
			"addtime"=>time()
		);

		
		$domain = new Domain_Charge();
		$info = $domain->getOrderId($changeid,$orderinfo);
		if($info==1003){
			$rs['code']=1003;
			$rs['msg']='订单信息有误，请重新提交';
            return $rs;	
		}else if(!$info){
			$rs['code']=1001;
			$rs['msg']='订单生成失败';
            return $rs;	
		}

			 
		$noceStr = md5(rand(100,1000).time());//获取随机字符串
		$time = time();
			
		$paramarr = array(
			"appid"       =>   $configpri['wx_appid'],
			"body"        =>    "充值{$coin}虚拟币",
			"mch_id"      =>    $configpri['wx_mchid'],
			"nonce_str"   =>    $noceStr,
			"notify_url"  =>    $configpub['site'].'/Appapi/pay/notify_wx',
			"out_trade_no"=>    $orderid,
			"total_fee"   =>    $money*100, 
			"trade_type"  =>    "APP"
		);
		$sign = $this -> sign($paramarr,$configpri['wx_key']);//生成签名
		$paramarr['sign'] = $sign;
		$paramXml = "<xml>";
		foreach($paramarr as $k => $v){
			$paramXml .= "<" . $k . ">" . $v . "</" . $k . ">";
		}
		$paramXml .= "</xml>";
			 
		$ch = curl_init ();
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
		@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在  
		@curl_setopt($ch, CURLOPT_URL, "https://api.mch.weixin.qq.com/pay/unifiedorder");
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_POST, 1);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $paramXml);
		@$resultXmlStr = curl_exec($ch);
		if(curl_errno($ch)){
			//print curl_error($ch);
			file_put_contents('./wxpay.txt',date('y-m-d H:i:s').' 提交参数信息 ch:'.json_encode(curl_error($ch))."\r\n",FILE_APPEND);
		}
		curl_close($ch);

		$result2 = $this->xmlToArray($resultXmlStr);
        
        if($result2['return_code']=='FAIL'){
            $rs['code']=1005;
			$rs['msg']=$result2['return_msg'];
            return $rs;	
        }
		$time2 = time();
		$prepayid = $result2['prepay_id'];
		$sign = "";
		$noceStr = md5(rand(100,1000).time());//获取随机字符串
		$paramarr2 = array(
			"appid"     =>  $configpri['wx_appid'],
			"noncestr"  =>  $noceStr,
			"package"   =>  "Sign=WXPay",
			"partnerid" =>  $configpri['wx_mchid'],
			"prepayid"  =>  $prepayid,
			"timestamp" =>  $time2
		);
		$paramarr2["sign"] = $this -> sign($paramarr2,$configpri['wx_key']);//生成签名
		
		$rs['info'][0]=$paramarr2;
		return $rs;			
	}		
	
	/**
	* sign拼装获取
	*/
	protected function sign($param,$key){
		$sign = "";
		foreach($param as $k => $v){
			$sign .= $k."=".$v."&";
		}
		$sign .= "key=".$key;
		$sign = strtoupper(md5($sign));
		return $sign;
	
	}
	/**
	* xml转为数组
	*/
	protected function xmlToArray($xmlStr){
		$msg = array(); 
		$postStr = $xmlStr; 
		$msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA); 
		return $msg;
	}	
		
	/**
	 * 支付宝支付
	 * @desc 用于支付宝支付 获取订单号
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].orderid 订单号
	 * @return string msg 提示信息
	 */
	public function getAliOrder() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$changeid=$this->changeid;
		$coin=checkNull($this->coin);
		$money=checkNull($this->money);
		
		$orderid=$this->getOrderid($uid);
		$type=1;
		
		if($coin==0){
			$rs['code']=1002;
			$rs['msg']='信息错误';		
			return $rs;						
		}

        $user_info = getUserInfo($uid);
        $to_user_info = $user_info;

		$orderinfo=array(
			"uid"=>$uid,
            "user_login" => $user_info['user_login'],
			"touid"=>$uid,
            'to_user_login' => $to_user_info['user_login'],
			"money"=>$money,
			"coin"=>$coin,
			"orderno"=>$orderid,
			"type"=>$type,
			"status"=>0,
			"addtime"=>time()
		);
		
		$domain = new Domain_Charge();
		$info = $domain->getOrderId($changeid,$orderinfo);
		if($info==1003){
			$rs['code']=1003;
			$rs['msg']='订单信息有误，请重新提交';
		}else if(!$info){
			$rs['code']=1001;
			$rs['msg']='订单生成失败';
		}
		
		$rs['info'][0]['orderid']=$orderid;
		return $rs;
	}		

	/**
	 * 苹果支付
	 * @desc 用于苹果支付 获取订单号
	 * @return int code 操作码，0表示成功
	 * @return array info 
	 * @return string info[0].orderid 订单号
	 * @return string msg 提示信息
	 */
	public function getIosOrder() {
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		
		$uid=$this->uid;
		$changeid=$this->changeid;
		$coin=checkNull($this->coin);
		$money=checkNull($this->money);
		
		$orderid=$this->getOrderid($uid);
		$type=3;
		
		if($coin==0){
			$rs['code']=1002;
			$rs['msg']='信息错误';		
			return $rs;						
		}

		$configpri = getConfigPri();

        $user_info = getUserInfo($uid);
        $to_user_info = $user_info;
		
		$orderinfo=array(
			"uid"=>$uid,
            "user_login" => $user_info['user_login'],
			"touid"=>$uid,
            'to_user_login' => $to_user_info['user_login'],
			"money"=>$money,
			"coin"=>$coin,
			"orderno"=>$orderid,
			"type"=>$type,
			"status"=>0,
			"addtime"=>time(),
			"ambient"=>$configpri['ios_sandbox']
		);
		
		$domain = new Domain_Charge();
		$info = $domain->getOrderId($changeid,$orderinfo);
		if($info==1003){
			$rs['code']=1003;
			$rs['msg']='订单信息有误，请重新提交';
		}else if(!$info){
			$rs['code']=1001;
			$rs['msg']='订单生成失败';
		}

		$rs['info'][0]['orderid']=$orderid;
		return $rs;
	}
    /**
     * 获取支付类型
     * @desc 获取支付类型
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     * @return array info[0].channel_name 父渠道名称
     * @return array info[0].channel_id 父渠道名称
     * @return string info.charge_hour_star 充值时间（开始，单位：小时，code为2051时返回）
     * @return string info.charge_hour_end 充值时间（结束，单位：小时，code为2051时返回）
     */

        public  function getChargeChannel(){
        $rs = array('code' => 0, 'msg' => '支付类型列表', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $reg_key =checkNull($this->reg_key);
        $reg_url =checkNull($this->reg_url);
        $vip_id =checkNull($this->vip_Id);
        $domain = new Domain_Charge();
        $info = $domain->getChargeChannel($uid,$reg_key,$reg_url,$vip_id,$game_tenant_id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }
    /**
     * 获取支付具体通道
     * @desc 获取支付具体通道
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].reception_name 前端显示名称
     * @return string info[0].name 具体渠道名称 如前端展示名称不为空，请取前端显示名称
     * @return float info[0].select_amount  不为空是支付渠道的金额不能输入，改字段会返回支付的金额，多个会用英文逗号隔开
     * @return int info[0].channel_id 渠道id
     * @return string info[0].reception_name  前端显示名称
     * @return string info[0].name 具体渠道名称 如前端展示名称不为空，请取前端显示名称
     * @return int    info[0].is_virtual 是否是虚拟币 0不是 1是
     * @return string info[0].bank_name 银行名称
     * @return string info[0].bank_branch 开户支行
     * @return string info[0].bank_number 银卡卡号/虚拟币地址
     * @return string info[0].bank_user_name 持卡人姓名
     * @return string info[0].code 币种
     * @return string info[0].usdt_type 链类型
     * @return string info[0].usdt_address 链地址
     * @return float info[0].min_amount 最小金额
     * @return float info[0].max_amount 最大金额
     * @return string msg 提示信息
     */

    public  function getAccountChannel(){
        $rs = array('code' => 0, 'msg' => '支付类型列表', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $channel_id = $this->channel_id;
        $reg_key =checkNull($this->reg_key);
        $reg_url =checkNull($this->reg_url);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Charge();
        $vip_id =checkNull($this->vip_Id);
        $info = $domain->getAccountChannel($uid,$channel_id,$reg_key,$reg_url,$vip_id,$game_tenant_id);
        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 发起支付 （暂不使用）
     * @desc 发起支付
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[url] 支付跳转地址
     * @return string msg 提示信息
     */
    public  function  pay(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $channel_id = $this->channel_id;
        $amount = $this->amount;
        $type = $this->type;
        unset($this->uid,$this->token,$this->code);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;

        }
        $domain = new Domain_Charge();
        $info = $domain->getChargeAccountChannel($uid,$game_tenant_id,$channel_id,$amount,$type);
        if ($info['code'] == 200){
            $rs = array('code' => 0, 'msg' => '请求成功', 'info' => array('url' => $info['result']['url']));
        }else{
            $rs['msg'] = '请求失败';
        }
        return $rs;
    }
    /**
     * 发起支付
     * @desc 发起支付
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[url] 支付跳转地址
     * @return string msg 提示信息
     */

    public  function  chargePay(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $account_channel_id= $this->account_channel_id;
        $amount = $this->amount;
        $vip_id = $this->vip_id;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;

        }
        $userDomain = new Domain_User();
        $userInfo = $userDomain->getBaseInfo($uid);
        if ($userInfo['user_type'] ==4){
            $rs['code'] = 800;
            $rs['msg'] = '虚拟会员不能充值';
            return $rs;
        }
        $domain = new Domain_Charge();
        $info = $domain->chargePay($uid,$account_channel_id,$amount,$game_tenant_id,$vip_id);
        if ($info['status'] == 1){
            $rs['info']['url'] = $info['msg'];
        }else{
            $rs['code'] = 1003;
            $rs['msg'] = $info['msg'];
        }

        return $rs;

    }
    /**
     * 发起支付(新需求)
     * @desc 发起支付
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */

    public  function  chargePaynew(){
        $rs = array('code' => 0, 'msg' => '充值成功', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $channel_id= $this->channel_id;
        $amount = $this->amount;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;

        }
        $userDomain = new Domain_User();
        $userInfo = $userDomain->getBaseInfo($uid);
        if ($userInfo['user_type'] ==4){
            $rs['code'] = 800;
            $rs['msg'] = '虚拟会员不能充值';
            return $rs;
        }
        $domain = new Domain_Charge();
        $info = $domain->chargePaynew($uid,$channel_id,$amount,$game_tenant_id);
        if($info==1001){
            $rs['code'] = 1001;
            $rs['msg'] = '充值失败';
            return $rs;
        }

        $rs['info'] = $info;
        return $rs;

    }

    public function notify(){
	    $data = $_POST;
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/data/order.txt',json_encode($data,true));
        $domain = new Domain_Charge();
   ;
        $info = $domain->notify($data);
        echo $info;
        exit;

    }

    /**
     * 充值订单列表
     * @desc 充值订单列表
     * @return int code 操作码，0表示成功
     * @return array info
     * @return  info[0].money 支付金额
     * @return  info[0].rnb_money 人民币
     * @return  info[0].coin 钻石
     * @return  info[0].type 1：线上 2线下
     * @return  info[0].orderno 1：订单号
     * @return string msg 提示信息
     * @return  info[0].status` 1:待支付，2支付成功，3支付失败',
     * @return  info[0].addtime 1：支付时间
     * @return  info[0].updatetime 支付修改状态时间
     */
    public  function orderList(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);

        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;

        }
        $game_tenant_id = $this->game_tenant_id;
        $status = $this->status;
        $p = $this->p;
        $domain = new Domain_Charge();
        $info = $domain->orderList($uid,$status,$p);
        $rs['info'] = $info;
        return $rs;
    }
    /**
     * 发起支付
     * @desc 线下支付
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public  function  offlinpay(){
        $rs = array('code' => 0, 'msg' => '请求成功', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        $account_channel_id= $this->account_channel_id;
        $amount = $this->amount;
        $user_real_name = $this->name;
        $img= $this->img;
        $vip_id  = $this->vip_id;

        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $userDomain = new Domain_User();
        $userInfo = $userDomain->getBaseInfo($uid);
        if ($userInfo['user_type'] ==4){
            $rs['code'] = 800;
            $rs['msg'] = '虚拟会员不能充值';
            return $rs;
        }
        $domain = new Domain_Charge();
        $result = $domain->offlinpay($uid,$account_channel_id,$amount,$game_tenant_id,$user_real_name,$img,$vip_id);

        $rs['code'] = isset($result['code']) ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) ? $result['info'] : $rs['info'];

        return $rs;
    }



}
