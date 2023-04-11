<?php
use GuzzleHttp\Client;
use api\Common\CustRedis;
use api\Library\Upload\Aliyunoss;

/*
 * 正负数相互转换（支持小数）
 */
function plus_minus_conversion($number = 0){
    return $number > 0 ? -1 * $number : abs($number);
}
function getGameUserBalance($gameTenantId,$gameUserId){
    $rs=array('code'=>0,'msg'=>'');
    $url = '';
    $params = array();

    try {
        $client= new Client();
        //余额查询请求地址
        $tenantInfo=getTenantInfo(getTenantId());

        $url=$tenantInfo['balance_query_url'];

        $config=getConfigPri();
        $tokenKey=$config['balance_key'];
        $token=$gameTenantId.$gameUserId.$tokenKey;
        $token=md5($token);
        $params=array(
            'tId'=>$gameTenantId,
            'custId'=>$gameUserId,
            'token'=>$token
        );
//        file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/balance_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 获取余额参数信息:'.json_encode($params)."\r\n",FILE_APPEND);
//        file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/balance_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 获取余额链接信息:'.json_encode($url)."\r\n",FILE_APPEND);
        $res=$client->request('POST',$url,['timeout'=>20,'form_params'=>$params]);

        $code=$res->getStatusCode();

        if($code!=200){
            logapi(['url'=>$url,'params'=>$params,'code'=>$code,'body'=>(String) $res->getBody()],'【获取java余额异常】');  // 接口日志记录
//            file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/balance_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 获取余额结果信息:'.$code.",\n url: ".$url.",\n params: ".json_encode($params)."\r\n",FILE_APPEND);
            //请求异常
            $rs['msg']='请求异常';
            $rs['code']=$code;
            return $rs;
        }
        $body=(String) $res->getBody();
        $result= json_decode($body,true);
//        file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/balance_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 返回余额信息:'.json_encode($result).",\n url: ".$url.",\n params: ".json_encode($params)."\r\n",FILE_APPEND);

        if(empty($result['money']) && $result['money'] !== 0){
            logapi(['url'=>$url,'params'=>$params,'code'=>$code,'body'=>$body],'【请求返回异常】');  // 接口日志记录
            //请求返回异常
            $rs['msg']=$result['exMessage'];
            $rs['code']=$result['exCode'];
            return $rs;
        }

        //moeny转换为coin的比例,单位为 钻石/元
        $moneyRate=$config['money_rate'];

        //money单位为厘
        $rs['money']=floatval($result['money']);
        $rs['nowithdrawable_coin']= isset($result['nowithdrawable_coin']) ? floatval($result['nowithdrawable_coin']) : 0.00;
        //厘转元,向下取整
        $rs['coin']=floor($rs['money']/1000*$moneyRate);
        $rs['nowithdrawable_coin']=floor($rs['nowithdrawable_coin']/1000*$moneyRate);
        return $rs;
    }catch (Exception $ex){
        DI()->logger->error("查询余额接口请求异常:".$ex->getMessage().",\n url: ".$url.",\n params: ".json_encode($params));
        logapi(['url'=>$url,'params'=>$params],'【查询余额接口请求异常】'.$ex->getMessage());  // 接口日志记录
        //请求异常
        $rs['msg']='请求异常';
        $rs['code']=500;
        return $rs;
    }


}

function addGameUserBalance($useridGame,$useridLive,$tidGame,$tidLive,$usernickname,$amount,$diamond,$type,$detail,$roomid,$anchorid,$anchorname,$anchorfromid,$anchorformname,$tId,$custId,$custAnchorid,$anthor_total,$anchorTenantid,$familyhead_id=0,$familyhead_money=0,$level=0,$bettingMoney=0){
    //1、增加余额  2、减少余额
    $billType=1;
    //变动来源，这里写死为LIVE
    $from='LIVE';
    $paramArray=array(
        'billType'=>$billType,
        'useridGame'=>$useridGame,
        'useridLive'=>$useridLive,
        'tidGame'=>$tidGame,
        'tidLive'=>$tidLive,
        'usernickname'=>$usernickname,
        'amount'=>$amount,
        'diamond'=>$diamond,
        'from'=>$from,
        'type'=>$type,
        'detail'=>$detail,
        'roomid'=>$roomid,
        'anchorid'=>$anchorid,
        'anchorname'=>$anchorname,
        'anchorfromid'=>$anchorfromid,
        'anchorfromname'=>$anchorformname,
        'tId'=>$tId,
        'custId'=>$custId,
        'custAnchorid'=>$custAnchorid,
        'anthorTotal'=>$anthor_total,
        'anchorTenantid' =>$anchorTenantid,
        'familyhead_id'=>$familyhead_id,
        'familyhead_money' =>$familyhead_money,
        'liveLeve' =>$level,
        'bettingMoney' => $bettingMoney
    );
    return updateGameUserBalance($paramArray);
}

/**
 * @param $useridGame 游戏系统用户ID
 * @param $useridLive 直播系统用户ID
 * @param $tidGame 游戏平台租户ID
 * @param $tidLive 直播平台租户ID
 * @param $usernickname 直播系统用户昵称
 * @param $amount 变动金额  RMB（单位元）
 * @param $diamond 变动钻石数量
 * @param $type 消费类型   1、打赏  2、付费房间  3、计时房间  4、发红包   5、收红包
 * @param $detail 消费明细  1、礼物名称   2、房间号码   3、房间号码+计时时间段  4、"发红包"  5、“收红包”
 * @param $roomid 直播间号码
 * @param $anchorid 主播ID
 * @param $anchorname 主播名称
 * @param $anchorfromid 主播所属租户平台ID
 * @param $anchorformname 主播所属租户平台名称
 * @param $tId 平台租户id
 * @param $custId 平台客户id
 * @return array
 */
function reduceGameUserBalance($useridGame,$useridLive,$tidGame,$tidLive,$usernickname,$amount,$diamond,$type,$detail,$roomid,$anchorid,$anchorname,$anchorfromid,$anchorformname,$tId,$custId,$custAnchorid,$anthor_total,$anchorTenantid,$familyhead_id=0,$familyhead_money=0,$level=0,$bettingMoney=0){
    //1、增加余额  2、减少余额
    $billType=2;
    //变动来源，这里写死为LIVE
    $from='LIVE';
    //先转正数
    $amount=abs($amount);
    //金额转负数
    $amount=plus_minus_conversion($amount);
    $paramArray=array(
        'billType'=>$billType,
        'useridGame'=>$useridGame,
        'useridLive'=>$useridLive,
        'tidGame'=>$tidGame,
        'tidLive'=>$tidLive,
        'usernickname'=>$usernickname,
        'amount'=>$amount,
        'diamond'=>$diamond,
        'from'=>$from,
        'type'=>$type,
        'detail'=>$detail,
        'roomid'=>$roomid,
        'anchorid'=>$anchorid,
        'anchorname'=>$anchorname,
        'anchorfromid'=>$anchorfromid,
        'anchorfromname'=>$anchorformname,
        'tId'=>$tId,
        'custId'=>$custId,
        'custAnchorid'=>$custAnchorid,
        'anthorTotal'=>$anthor_total,
        'anchorTenantid' =>$anchorTenantid,
        'familyhead_id'=>$familyhead_id,
        'familyhead_money' =>$familyhead_money,
        'liveLeve' =>$level,
        'bettingMoney' => $bettingMoney

    );
    return updateGameUserBalance($paramArray);
}

function updateGameUserBalance($paramArray){
    $rs=array('code'=>0,'msg'=>'');
    try {
        $billType=$paramArray['billType'];
        $useridGame=$paramArray['useridGame'];
        $useridLive=$paramArray['useridLive'];
        $tidGame=$paramArray['tidGame'];
        $tidLive=$paramArray['tidLive'];
        $usernickname=$paramArray['usernickname'];
        $amount=$paramArray['amount'];
        $diamond=$paramArray['diamond'];
        $from=$paramArray['from'];
        $type=$paramArray['type'];
        $detail=$paramArray['detail'];
        $roomid=$paramArray['roomid'];
        $anchorid=$paramArray['anchorid'];
        $anchorname=$paramArray['anchorname'];
        $anchorfromid=$paramArray['anchorfromid'];
        $anchorformname=$paramArray['anchorfromname'];
        $tId=$paramArray['tId'];
        $custId=$paramArray['custId'];
        $custAnchorid=$paramArray['custAnchorid'];
        $anthor_total=$paramArray['anthorTotal'];
        $anchorTenantid =$paramArray['anchorTenantid'];

        $client= new Client();
        //余额更新请求地址
        $tenantInfo=getTenantInfo(getTenantId());
        $url=$tenantInfo['balance_update_url'];
        $config=getConfigPri();
        $tokenKey=$config['balance_key'];
        $tokenStr=$tId.$custId.$useridGame.$useridLive.$tidGame.$tidLive.$usernickname.$amount.$diamond.$from.$tokenKey;
        $token=md5($tokenStr);
        $paramArray['token']=$token;
        $res=$client->request('POST',$url,['timeout'=>20,'form_params'=>$paramArray]);
        $code=$res->getStatusCode();
        if($code!=200){
            logapi(['url'=>$url,'params'=>$paramArray,'code'=>$code,'body'=>(String) $res->getBody()],'【更新java余额异常】');  // 接口日志记录
            //请求异常
            $rs['msg']='请求异常';
            $rs['code']=$code;
            return $rs;
        }
        $body=(String) $res->getBody();
        $result= json_decode($body,true);
//        logapi(['url'=>$url,'params'=>$paramArray,'code'=>$code,'body'=>$result],'【更新余额参数】');  // 接口日志记录

        if(!empty($result['exCode'])){
            logapi(['url'=>$url,'params'=>$paramArray,'code'=>$code,'body'=>$body],'【更新java余额返回异常】');  // 接口日志记录
            //接口返回码判断
            //请求返回异常
            $rs['msg']=$result['exMessage'];
            $rs['code']=$result['exCode'];
            return $rs;
        }

        return $rs;
    }catch (Exception $ex){
        DI()->logger->error("更新余额接口请求异常:".$ex->getMessage());
        //请求异常
        $rs['msg']='请求异常';
        $rs['code']=500;
        return $rs;
    }


}


/**
 * 开始数据库事务
 */
function beginTransaction(){
    DI()->notorm->beginTransaction('db_appapi');
}

/**
 * 提交数据库事务
 */
function commitTransaction(){
    DI()->notorm->commit('db_appapi');
}

/**
 * 回滚数据库事务
 */
function rollbackTransaction(){
    DI()->notorm->rollback('db_appapi');
}

/**
 * 获取前台配置
 */
function getFrontConfig(){
    $config=getConfigPri();
    //过滤敏感消息

    unset($config['cache_switch']);
    unset($config['cache_time']);
    unset($config['ihuyi_account']);
    unset($config['ihuyi_ps']);
    unset($config['jpush_key']);
    unset($config['jpush_secret']);
    unset($config['userlist_time']);
    unset($config['barrage_fee']);
    unset($config['auth_islimit']);
    unset($config['level_islimit']);
    unset($config['level_limit']);
    unset($config['cash_rate']);
    unset($config['push_url']);
    unset($config['pull_url']);
    unset($config['chatserver']);
    unset($config['aliapp_switch']);
    unset($config['aliapp_partner']);
    unset($config['aliapp_seller_id']);
    unset($config['aliapp_key_android']);
    unset($config['aliapp_key_ios']);
    unset($config['wx_switch']);
    unset($config['wx_appid']);
    unset($config['wx_appsecret']);
    unset($config['wx_mchid']);
    unset($config['wx_key']);
    unset($config['aliapp_check']);
    unset($config['aliapp_pc']);
    unset($config['login_wx_pc_appid']);
    unset($config['login_wx_pc_appsecret']);
    unset($config['login_sina_pc_akey']);
    unset($config['login_sina_pc_skey']);
    unset($config['wx_switch_pc']);
    unset($config['cash_min']);
    unset($config['login_wx_appid']);
    unset($config['login_wx_appsecret']);
    unset($config['ios_sandbox']);
    unset($config['jpush_sandbox']);
    unset($config['jpush_sys_roomid']);
    unset($config['auth_key']);
    unset($config['auth_length']);
    unset($config['cdn_switch']);
    unset($config['tx_appid']);
    unset($config['tx_bizid']);
    unset($config['tx_push_key']);
    unset($config['tx_push']);
    unset($config['tx_pull']);
    unset($config['qn_ak']);
    unset($config['qn_sk']);
    unset($config['qn_hname']);
    unset($config['qn_push']);
    unset($config['qn_pull']);
    unset($config['bonus_switch']);
    unset($config['ws_push']);
    unset($config['ws_pull']);
    unset($config['ws_apn']);
    unset($config['wy_appkey']);
    unset($config['wy_appsecret']);
    unset($config['ady_push']);
    unset($config['ady_pull']);
    unset($config['ady_hls_pull']);
    unset($config['ady_apn']);
    unset($config['sendcode_switch']);
    unset($config['iplimit_switch']);
    unset($config['iplimit_times']);
    unset($config['game_banker_limit']);
    unset($config['game_switch']);
    unset($config['game_pump']);
    unset($config['game_odds']);
    unset($config['game_odds_p']);
    unset($config['game_odds_u']);
    unset($config['agorakitid']);
    unset($config['auction_switch']);
    unset($config['shut_time']);
    unset($config['kick_time']);
    unset($config['agent_switch']);
    unset($config['distribut1']);
    unset($config['distribut2']);
    unset($config['distribut3']);
    unset($config['reg_reward']);
    unset($config['family_switch']);
    unset($config['cash_start']);
    unset($config['cash_end']);
    unset($config['um_apikey']);
    unset($config['um_apisecurity']);
    unset($config['um_appkey_android']);
    unset($config['um_appkey_ios']);
    unset($config['video_audit_switch']);
    unset($config['video_showtype']);
    unset($config['comment_weight']);
    unset($config['like_weight']);
    unset($config['share_weight']);
    unset($config['show_val']);
    unset($config['hour_minus_val']);
    unset($config['qiniu_accesskey']);
    unset($config['qiniu_secretkey']);
    unset($config['qiniu_bucket']);
    unset($config['qiniu_domain']);
    unset($config['qiniu_domain_url']);
    unset($config['cloudtype']);
    unset($config['srs_push_url']);
    unset($config['srs_pull_url']);
    unset($config['srs_flv_pull_url']);
    unset($config['srs_push_key']);
    unset($config['srs_pull_key']);

    unset($config['anchor_tenant_profit_ratio']);
    unset($config['user_tenant_profit_ratio']);
    unset($config['anchor_profit_ratio']);

    unset($config['money_rate']);
    unset($config['balance_key']);

    return $config;
}

function getTenantInfoFromDomain($domain){
 /*   $key='tenant_'.$domain;
    $tenantInfo= getcaches($key);
    if(!$tenantInfo){
        $tenantInfo= DI()->notorm->tenant->select("*")->where("status='1' and site='?'",$domain)->fetchOne();
        setcaches($key,$tenantInfo);
    }*/

    $tenantInfo = array();
    $tenant_list = getTenantList();
    foreach ($tenant_list as $key=>$val){
        if($val['status'] == 1 && $val['site'] == $domain){
            $tenantInfo = $val;
        }
    }

    return $tenantInfo;
}
function getTenantInfoFromGameTenantId($gameTenantId){
/*    $key='tenant_gameTenantId_'.$gameTenantId;
    $tenantInfo= getcaches($key);
    if(!$tenantInfo){
        $tenantInfo= DI()->notorm->tenant->select("*")->where("status='1' and game_tenant_id=? ",$gameTenantId)->fetchOne();
        setcaches($key,$tenantInfo);
    }
    */

    $tenantInfo = array();
    $tenant_list = getTenantList();
    foreach ($tenant_list as $key=>$val){
        if($val['status'] == 1 && $val['game_tenant_id'] == $gameTenantId){
            $tenantInfo = $val;
        }
    }

    return $tenantInfo;
}
function getPlatformTenantInfo(){
/*    $platformTenantId=1;
    $key='tenant_'.$platformTenantId;
    $tenantInfo= getcaches($key);
    if(!$tenantInfo){
        $tenantInfo= DI()->notorm->tenant->select("*")->where("status='1' and id=? ",$platformTenantId)->fetchOne();
        setcaches($key,$tenantInfo);
    }*/

    $tenantInfo = array();
    $tenant_list = getTenantList();
    foreach ($tenant_list as $key=>$val){
        if($val['status'] == 1){
            $tenantInfo = $val;
        }
    }

    return $tenantInfo;
}

function getTenantId(){
    return $_SESSION['tenantId'];
}
function getGameTenantId(){
    return $_SESSION['gameTenantId'];
}

//区分前端接口的tenantId
function getTenantIds(){
    return $_SESSION['tenantIds'];
}

function getGameTenantIds(){
    return $_SESSION['gameTenantIds'];
}


	/* Redis链接 */
	function connectionRedis(){
		$REDIS_HOST= DI()->config->get('app.REDIS_HOST');
		$REDIS_AUTH= DI()->config->get('app.REDIS_AUTH');
		$REDIS_PORT= DI()->config->get('app.REDIS_PORT');
		$REDIS_DBINDEX=DI()->config->get('app.REDIS_DBINDEX');

		$redis = new Redis();
		$redis -> pconnect($REDIS_HOST,$REDIS_PORT);
		$redis -> auth($REDIS_AUTH);
		$redis ->select($REDIS_DBINDEX);


		return $redis;
	}
function  connectRedis(){
	    $REDIS_HOST= DI()->config->get('app.REDIS_HOST');
		$REDIS_AUTH= DI()->config->get('app.REDIS_AUTH');
		$REDIS_PORT= DI()->config->get('app.REDIS_PORT');
		$REDIS_DBINDEX=DI()->config->get('app.REDIS_DBINDEX');

    $redis = new Redis();
    $redis -> connect($REDIS_HOST,$REDIS_PORT);
    $redis -> auth($REDIS_AUTH);
    $redis ->select($REDIS_DBINDEX);

    return $redis;
}


	/* 设置缓存 */
	function setcache($key,$info){
		$config=getConfigPri();
		if($config['cache_switch']!=1){
			return 1;
		}

		DI()->redis->set($key,json_encode($info));
		DI()->redis->setTimeout($key, $config['cache_time']); 

		return 1;
	}	
	/* 设置缓存 可自定义时间*/
	function setcaches($key,$info,$time=0){
		DI()->redis->set($key,json_encode($info));
        if($time > 0){
            DI()->redis->setTimeout($key, $time); 
        }
		
		return 1;
	}
	/* 获取缓存 */
	function getcache($key){
		$config=getConfigPri();

		if($config['cache_switch']!=1){
			$isexist=false;
		}else{
			$isexist=DI()->redis->Get($key);
		}

		return json_decode($isexist,true);
	}		
	/* 获取缓存 不判断后台设置 */
	function getcaches($key){

		$isexist=DI()->redis->Get($key);
		
		return json_decode($isexist,true);
	}
	/* 删除缓存 */
	function delcache($key){
		$isexist=DI()->redis->delete($key);
		return 1;
	}

/**
 * 删除所有与特定格式匹配的key
 * @param $key
 * @return int
 */
function delPatternCacheKeys($key){
    $redis=connectionRedis();
    $keys=$redis->keys($key);
    $redis->delete($keys);
    return 1;
}

	/* 同系统函数 array_column   php版本低于5.5.0 时用  */
	function array_column2($input, $columnKey, $indexKey = NULL){
		$columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
		$indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
		$indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
		$result = array();
 
		foreach ((array)$input AS $key => $row){ 
			if ($columnKeyIsNumber){
				$tmp = array_slice($row, $columnKey, 1);
				$tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
			}else{
				$tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
			}
			if (!$indexKeyIsNull){
				if ($indexKeyIsNumber){
					$key = array_slice($row, $indexKey, 1);
					$key = (is_array($key) && ! empty($key)) ? current($key) : NULL;
					$key = is_null($key) ? 0 : $key;
				}else{
					$key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
				}
			}
			$result[$key] = $tmp;
		}
		return $result;
	}
	
	/* 密码检查 */
	function passcheck($user_pass) {
		$num = preg_match("/^[a-zA-Z]+$/",$user_pass);
		$word = preg_match("/^[0-9]+$/",$user_pass);
		$check = preg_match("/^[a-zA-Z0-9]{6,12}$/",$user_pass);
		if($num || $word ){
			return 2;
		}else if(!$check){
			return 0;
		}		
		return 1;
	}
	/* 检验手机号 */
	function checkMobile($mobile,$country='CN'){
        if(preg_match(shortmsg()['phone_pattern'][$country],$mobile)){
            return 1;
        }else{
            return 0;
        }
	}

	/* 获取shortmsg数据 */
    function shortmsg(){
        $arr = include(dirname(__FILE__) .'/./shortmsg.php');
        return $arr;
    }

	/* 随机数 */
	function random($length = 6 , $numeric = 0) {
		PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
		if($numeric) {
			$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
		} else {
			$hash = '';
			$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
			$max = strlen($chars) - 1;
			for($i = 0; $i < $length; $i++) {
				$hash .= $chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}
	/* 发送验证码 */
	function sendCode($mobile,$code){
		$rs=array();
		$config = getConfigPri();
        
        if(!$config['sendcode_switch']){
            $rs['code']=667;
			$rs['msg']='123456';
            return $rs;
        }
        
		/* 互亿无线 */
		$target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
		$content="您的验证码是：".$code."。请不要把验证码泄露给其他人。";
		$post_data = "account=".$config['ihuyi_account']."&password=".$config['ihuyi_ps']."&mobile=".$mobile."&content=".rawurlencode($content);
		//密码可以使用明文密码或使用32位MD5加密
		$gets = xml_to_array(Post($post_data, $target));
//        file_put_contents(API_ROOT.'/Runtime/sendCode_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 提交参数信息 gets:'.json_encode($gets)."\r\n",FILE_APPEND);
		if($gets['SubmitResult']['code']==2){
            setSendcode(array('type'=>'1','account'=>$mobile,'content'=>$content));
			$rs['code']=0;
		}else{
			$rs['code']=1002;
			//$rs['msg']=$gets['SubmitResult']['msg'];
			$rs['msg']="获取失败";
		} 
		return $rs;
	}


    /* 云片-发送验证码 */
    function sendCodeYP($mobile,$code,$country="CN"){
        $rs=array();
        $config = getConfigPri();

        if(!$config['sendcode_switch']){
            $rs['code']=667;
            $rs['msg']='123456';
            return $rs;
        }
        $apikey = $config['yp_apikey'];
        $content="您的验证码是：".$code."。请不要把验证码泄露给其他人。";
        $ch = curl_init();
        /* 设置验证方式 */
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8','Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 需要对value进行编码
        $data = array('tpl_id' => '4054598', 'tpl_value' => ('#code#').
            '='.urlencode($code), 'apikey' => $apikey, 'mobile' => $mobile);
//        file_put_contents(API_ROOT.'/Runtime/sendCode_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 云片-提交参数信息 gets:'.json_encode($data)."\r\n",FILE_APPEND);
        $ret = tpl_send($ch,$data);
//        file_put_contents(API_ROOT.'/Runtime/sendCode_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 云片-结果 gets:'.$ret."\r\n",FILE_APPEND);
        if ($ret === FALSE) {
            $rs['code']=500;
            $rs['msg'] =$code;
            return $rs;
        } else {
            $result = json_decode($ret, true);
            if ($result['code'] == 0){
                $rs['code']=0;
                $rs['msg'] =$code;
                setSendcode(array('type'=>'1','account'=>$mobile,'content'=>$content));
                return $rs;
            }else{
                $rs['code']=1002;
                $rs['msg'] =$code;
                return $rs;
            }
        }
        return $array;
    }

    //模板发送验证码
    function tpl_send($ch,$data){
        //curl_setopt($ch, CURLOPT_INTERFACE,"172.31.44.150");
        curl_setopt ($ch, CURLOPT_URL,
            'https://sms.yunpian.com/v2/sms/tpl_single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        // 检查是否有错误发生
        if(curl_errno($ch))
        {
            file_put_contents("/data/otc/dxjt.txt", 'curl-error:' . curl_error($ch).PHP_EOL, FILE_APPEND);
        }
        return $result;
    }

    /* 易盾-发送验证码 */
    function sendCodeYD($mobile,$code,$country='CN'){
        $rs=array();
        $config = getConfigPri();

        if(!$config['sendcode_switch']){
            $rs['code']=667;
            $rs['msg']='123456';
            return $rs;
        }

        $content="您的验证码是：".$code."。请不要把验证码泄露给其他人。";
        /* 网易易盾 */
        $json_param["code"] = $code;
        $params = array(
            "templateId" => $config['yd_templateid'],
            "mobile" => $mobile,
            "paramType" => "json",
            // 转换成json字符串
            "params" => json_encode($json_param),
            // 国际短信对应的国际编码(非国际短信接入请注释掉该行代码)
//             "internationalCode" => '', // "对应的国家编码",
        );
        $params['internationalCode'] = shortmsg()['yd'][$country];
        $ret = check($params);
//        file_put_contents(API_ROOT.'/Runtime/sendCode_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').'易盾-结果 gets:'.$ret."\r\n",FILE_APPEND);
        if ($ret === FALSE) {
            $rs['code']=500;
            $rs['msg'] =$code;
            return $rs;
        } else {
            $result = json_decode($ret, true);
            if ($result['code'] == 200){
                $rs['code']=0;
                $rs['msg'] =$code;
                setSendcode(array('type'=>'1','account'=>$mobile,'content'=>$content));
                return $rs;
            }else{
                $rs['code']=1002;
                $rs['msg'] =$code;
                return $rs;
            }
        }
    }

    /**
     * 易盾短信发送在线检测请求接口简单封装
     * $params 请求参数
     */
    function check($params)
    {
        $config = getConfigPri();
        $params["secretId"] = $config['yd_secretid'];
        $params["businessId"] = $config['yd_businessid'];
        $params["version"] = "v2";
        $params["timestamp"] = sprintf("%d", round(microtime(true) * 1000));// time in milliseconds
        $params["nonce"] = sprintf("%d", rand()); // random int
        $params = toUtf8($params);
        $params["signature"] = gen_signature($config['yd_secretkey'], $params);
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'timeout' => 5, // read timeout in seconds
                'content' => http_build_query($params),
            ),
        );
//        file_put_contents(API_ROOT.'/Runtime/sendCode_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 易盾-提交参数信息 gets:'.json_encode($options)."\r\n",FILE_APPEND);
//        $context = stream_context_create($options);
//        $result = file_get_contents("https://sms.dun.163.com/v2/sendsms", false, $context);
//
        $url = "https://sms.dun.163.com/v2/sendsms";
        $result = Post($params, $url);
        logapi([$url, $params, $result],'获取注册短信验证码 网易易盾 请求和结果');
        if($result === false){
            $sign_key = '230eb23718974713afa2eb12002d70b6-c11301ff38b407c4510a3cc42abf4419-ADCAFD57C5A9A808DB3DC2B90CB3B80F-66ACC0D685695CD8C9520D19F0E2BECC';
            $data = array(
                'url' => $url,
                'data' => json_encode($params),
            );;
            ksort($data);
            $sign_str = '';
            foreach ($data as $key=>$val){
                if($key == 'sign'){
                    continue;
                }
                $sign_str .= $key.'='.$val.'&';
            }
            $sign_str .= 'd='.date('Y-m-d').'&';
            $sign_str .= '&key='.$sign_key;
            $sign = md5($sign_str);
            $data['sign'] = $sign;
            setcaches('Api_ShortMessage_send_message', $data, 60);
            $http_post_res = http_post('http://'.$_SERVER['HTTP_HOST'].'/Api/ShortMessage/send_message', $data);
            logapi([$url, $data, $http_post_res],'send_message 获取注册短信验证码 请求和结果');
            if(isset($http_post_res['data'])){
                $result = json_encode($http_post_res['data']);
            }
        }

        return $result;
    }

    /**
     * 易盾-将输入数据的编码统一转换成utf8
     * @params 输入的参数
     */
    function toUtf8($params)
    {
        $utf8s = array();
        foreach ($params as $key => $value) {
            $utf8s[$key] = is_string($value) ? mb_convert_encoding($value, "utf8", INTERNAL_STRING_CHARSET) : $value;
        }
        return $utf8s;
    }

    /**
     * 易盾-计算参数签名
     * $params 请求参数
     * $secretKey secretKey
     */
    function gen_signature($secretKey, $params)
    {
        ksort($params);
        $buff = "";
        foreach ($params as $key => $value) {
            if ($value !== null) {
                $buff .= $key;
                $buff .= $value;
            }
        }
        $buff .= $secretKey;
        return md5($buff);
    }

    function Post($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
	}
	
	function xml_to_array($xml){
		$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
			$count = count($matches[0]);
			for($i = 0; $i < $count; $i++){
			$subxml= $matches[2][$i];
			$key = $matches[1][$i];
				if(preg_match( $reg, $subxml )){
					$arr[$key] = xml_to_array( $subxml );
				}else{
					$arr[$key] = $subxml;
				}
			}
		}
		return $arr;
	}
	/* 发送验证码 */
    
    /* curl get请求 */
    function curl_post($url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
	}
    
	/* 检测文件后缀 */
	function checkExt($filename){
		$config=array("jpg","png","jpeg");
		$ext   =   pathinfo(strip_tags($filename), PATHINFO_EXTENSION);
		 
		return empty($config) ? true : in_array(strtolower($ext), $config);
	}	    
	/* 密码加密 */
	function setPass($pass){
		$authcode='rCt52pF2cnnKNB3Hkp';
		$pass="###".md5(md5($authcode.$pass));
		return $pass;
	}

/* 支付密码加密 */
function signPaymentPassword($payment_password, $salt){
    return md5(md5($salt.$payment_password));
}

/* 加密盐 */
function createSalt(){
    return time().uniqid();
}

/* 去除NULL 判断空处理 主要针对字符串类型*/
	function checkNull($checkstr){
        $checkstr=trim($checkstr);
		$checkstr=urldecode($checkstr);
        if(get_magic_quotes_gpc()==0){
			$checkstr=addslashes($checkstr);
		}
		//$checkstr=htmlspecialchars($checkstr);
		//$checkstr=filterEmoji($checkstr);
		if( strstr($checkstr,'null') || (!$checkstr && $checkstr!=0 ) ){
			$str='';
		}else{
			$str=$checkstr;
		}
		return $str;	
	}
	/* 去除emoji表情 */
	function filterEmoji($str){
		$str = preg_replace_callback(
			'/./u',
			function (array $match) {
				return strlen($match[0]) >= 4 ? '' : $match[0];
			},
			$str);
		return $str;
	}

/*
 * rawurlencode
 *  emoji 处理
 * */
function cust_unicode($str){
    if(!function_exists('mb_strlen')){
        return rawurlencode($str);
    }
    $strEncode = '';
    $length = mb_strlen($str,'utf-8');
    for ($i=0; $i < $length; $i++) {
        $_tmpStr = mb_substr($str,$i,1,'utf-8');
        if(strlen($_tmpStr) >= 4){
            $strEncode .= rawurlencode($_tmpStr);
        }else{
            $strEncode .= $_tmpStr;
        }
    }
    return $strEncode;
}

/* 根据租户id获取租户信息 */
function getTenantInfo($tenantId) {
   /* $key=$tenantId.'_'.'getTenantInfo';
    $tenantInfo=getcaches($key);
    if(!$tenantInfo){
        $tenantInfo=DI()->notorm->tenant->select("*")->where("id=?",$tenantId)->fetchOne();
        setcaches($key,$tenantInfo);
    }*/
    $tenantInfo = getTenantList($tenantId);
    return 	$tenantInfo;
}


/* 获取平台公共配置 */
function getConfigPub($tenantId=null) {
    $tenantId = $tenantId ? $tenantId : getTenantId();
    $key=$tenantId.'_'.'getPlatformConfig';
    $config=getcaches($key);
    if(!$config){
        /**
         * 基于平台公有设置和私有设置字段唯一的前提,此处把两个表的配置合起来
         */
        $platformConfig= DI()->notorm->platform_config->select("*")->where('tenant_id="'.$tenantId.'"')->fetchOne();
        $tenantConfig=DI()->notorm->tenant_config->select("*")->where('tenant_id="'.$tenantId.'"')->fetchOne();

        $tenantConfig = is_array($tenantConfig) ? $tenantConfig : array();
        $config=array_merge($platformConfig,$tenantConfig);

        setcaches($key,$config);
    }

    if(is_array($config['live_time_coin'])){

    }else if($config['live_time_coin']){
        $config['live_time_coin']=preg_split('/,|，/',$config['live_time_coin']);
    }else{
        $config['live_time_coin']=array();
    }
    if (isset($config['live_type'])){
        if(is_array($config['live_type'])){

        }else if($config['live_type']){
            $live_type=preg_split('/,|，/',$config['live_type']);
            foreach($live_type as $k=>$v){
                $live_type[$k]=preg_split('/;|；/',$v);
            }
            $config['live_type']=$live_type;
        }
    }else{
        $config['live_type']=array();
    }

    if(isset($config['login_type'])){
        if(is_array($config['login_type'])){

        }else if($config['login_type']){
            $config['login_type']=preg_split('/,|，/',$config['login_type']);
        }

    }else{
        $config['login_type']=array();
    }

    if(isset($config['share_type'])){
        if(is_array($config['share_type'])){

        }else if($config['share_type']){
            $config['share_type']=preg_split('/,|，/',$config['share_type']);
        }
    }
    else{
        $config['share_type']=array();
    }

    if(isset($config['game_switch'])){
        if(is_array($config['game_switch'])){

        }else if($config['game_switch']){
            $config['game_switch']=preg_split('/,|，/',$config['game_switch']);
        }
    }else{
        $config['game_switch']=array();
    }

    $config['cash_account_type'] = explode(',', $config['cash_account_type']);

    return 	$config;
}

/* 获取租户私密配置 */
function getConfigPri($tenantId = null) {
    $tenantId = $tenantId ? $tenantId : getTenantId();
    $key=$tenantId.'_'.'getTenantConfig';
    $config=getcaches($key);
    if(!$config){
        $platformConfig= DI()->notorm->platform_config->select("*")->where('tenant_id="'.$tenantId.'"')->fetchOne();

        $tenantConfig=DI()->notorm->tenant_config->select("*")->where('tenant_id="'.$tenantId.'"')->fetchOne();
        if(empty($tenantConfig)){
            $tenantConfig['defult_value'] = 'test';  //如果是超级用户，没有对应的租户设置，默认取个值
        }

        $config=array_merge($platformConfig,$tenantConfig);

        setcaches($key,$config);
    }

    if(is_array($config['live_time_coin'])){

    }else if($config['live_time_coin']){
        $config['live_time_coin']=preg_split('/,|，/',$config['live_time_coin']);
    }else{
        $config['live_time_coin']=array();
    }

    if(is_array($config['live_type'])){

    }else if($config['live_type']){
        $live_type=preg_split('/,|，/',$config['live_type']);
        foreach($live_type as $k=>$v){
            $live_type[$k]=preg_split('/;|；/',$v);
        }
        $config['live_type']=$live_type;
    }else{
        $config['live_type']=array();
    }

    if(is_array($config['login_type'])){

    }else if($config['login_type']){
        $config['login_type']=preg_split('/,|，/',$config['login_type']);
    }else{
        $config['login_type']=array();
    }

    if(is_array($config['share_type'])){

    }else if($config['share_type']){
        $config['share_type']=preg_split('/,|，/',$config['share_type']);
    }else{
        $config['share_type']=array();
    }

    if(is_array($config['game_switch'])){

    }else if($config['game_switch']){
        $config['game_switch']=preg_split('/,|，/',$config['game_switch']);
    }else{
        $config['game_switch']=array();
    }

    $config['cash_account_type'] = explode(',', $config['cash_account_type']);

    return 	$config;
}



	/* 公共配置 */
//	function getConfigPub() {
//		$key='getConfigPub';
//		$config=getcaches($key);
//		if(!$config){
//			$config= DI()->notorm->config
//					->select('*')
//					->where(" id ='1'")
//					->fetchOne();
//			setcaches($key,$config);
//		}
//        if(is_array($config['live_time_coin'])){
//
//        }else if($config['live_time_coin']){
//            $config['live_time_coin']=preg_split('/,|，/',$config['live_time_coin']);
//        }else{
//            $config['live_time_coin']=array();
//        }
//
//        if(is_array($config['login_type'])){
//
//        }else if($config['login_type']){
//            $config['login_type']=preg_split('/,|，/',$config['login_type']);
//        }else{
//            $config['login_type']=array();
//        }
//
//        if(is_array($config['share_type'])){
//
//        }else if($config['share_type']){
//            $config['share_type']=preg_split('/,|，/',$config['share_type']);
//        }else{
//            $config['share_type']=array();
//        }
//
//        if(is_array($config['live_type'])){
//
//        }else if($config['live_type']){
//            $live_type=preg_split('/,|，/',$config['live_type']);
//            foreach($live_type as $k=>$v){
//                $live_type[$k]=preg_split('/;|；/',$v);
//            }
//            $config['live_type']=$live_type;
//        }else{
//            $config['live_type']=array();
//        }
//		return 	$config;
//	}
	
	/* 私密配置 */
//	function getConfigPri() {
//		$key='getConfigPri';
//		$config=getcaches($key);
//		if(!$config){
//			$config= DI()->notorm->config_private
//					->select('*')
//					->where(" id ='1'")
//					->fetchOne();
//			setcaches($key,$config);
//		}
//
//        if(is_array($config['game_switch'])){
//
//        }else if($config['game_switch']){
//            $config['game_switch']=preg_split('/,|，/',$config['game_switch']);
//        }else{
//            $config['game_switch']=array();
//        }
//		return 	$config;
//	}
	
	/**
	 * 返回带协议的域名
	 */
	function get_host(){
        $config = getTenantInfo(getTenantId());
		return $config['site'];
	}	
	
	/**
	 * 转化数据库保存的文件路径，为可以访问的url
	 */
	function get_upload_path($file){
        if($file==''){
            return $file;
        }
		if(strpos($file,"http")===0){
			return html_entity_decode($file);
		}else if(strpos($file,"/")===0){
			$filepath= get_host().$file;
            //$filepath= $_SERVER['HTTP_HOST'].$file;
			return html_entity_decode($filepath);
		}else{
			$space_host= DI()->config->get('app.Qiniu.space_host');
            
			$filepath=$space_host."/".$file;
            //$filepath=$_SERVER['HTTP_HOST']."/".$file;
			return html_entity_decode($filepath);
		}
	}
	
	/* 判断是否关注 */
	function isAttention($uid,$touid) {
		$isexist=DI()->notorm->users_attention
					->select("*")
					->where('uid=? and touid=?',$uid,$touid)
					->fetchOne();
		if($isexist){
			return  1;
		}
        return  0;
	}
	/* 是否黑名单 */
	function isBlack($uid,$touid) {	
		$isexist=DI()->notorm->users_black
				->select("*")
				->where('uid=? and touid=?',$uid,$touid)
				->fetchOne();
		if($isexist){
			return 1;
		}
        return 0;
	}	
	
	/* 判断权限 */
	function isAdmin($uid,$liveuid) {
		if($uid==$liveuid){
			return 50;
		}
		$isuper=isSuper($uid);
		if($isuper){
			return 60;
		}
		$isexist=DI()->notorm->users_livemanager
					->select("*")
					->where('uid=? and liveuid=?',$uid,$liveuid)
					->fetchOne();
		if($isexist){
			return  40;
		}
		
		return  30;
			
	}	
	/* 判断账号是否超管 */
	function isSuper($uid){
		$isexist=DI()->notorm->users_super
					->select("*")
					->where('uid=?',$uid)
					->fetchOne();
		if($isexist){
			return 1;
		}			
		return 0;
	}

    /* 判断是独立还是彩票租户 */
    function whichTenat($game_tenant_id) {
/*        $tenantinfos=DI()->notorm->tenant
            ->select('site_id')
            ->where('game_tenant_id = ? ', $game_tenant_id)
            ->fetchOne();*/

        $tenantinfos = array();
        $tenant_list = getTenantList();
        foreach ($tenant_list as $key=>$val){
            if($val['status'] == 1 && $val['game_tenant_id'] == $game_tenant_id){
                $tenantinfos = $val;
            }
        }

        if($tenantinfos){
            return $tenantinfos['site_id'];
        }else{
            return  0;
        }
    }
	/* 判断token */
	function checkToken($uid,$token) {

		$userinfo=getcaches("token_".$uid);
		if(!$userinfo){
			$userinfo=DI()->notorm->users
						->select('token,expiretime,tenant_id')
						->where('id = ? and user_type !="1"', $uid)
						->fetchOne();	
			setcaches("token_".$uid,$userinfo);
		}
        $tenantId=getTenantId();
		if($userinfo['token']!=$token || $userinfo['expiretime']<time() || $userinfo['tenant_id']!=$tenantId){
			return 700;
		}else{
			return 	0;				
		} 		
	}

    /* 租户用户基本信息,查询当前租户用户或公共主播 */
    function getTenantUserInfo($uid,$tenantId) {
        /**
         * TODO 修改为查询缓存
         */
        $key=$tenantId."_"."userinfo_".$uid;
        $info = getcaches($key);
        if(!$info){
            $info= DI()->notorm->users
                ->select(Cache_Users::getInstance()->fields)
                ->where("id='{$uid}' and user_type != 1 and (tenant_id='$tenantId' or isshare='1' )")->fetchOne();
            if($info){
                $info['beauty'] = is_json($info['beauty']) ? json_decode($info['beauty'],true) : $info['beauty'];
                $info['level']=getLevel($info['consumption']);
                $info['level_anchor']=getLevelAnchor($info['votestotal']);

                $info['vip']=getUserVip($uid);
                $info['liang']=getUserLiang($uid);
            }else{
                //返回空
            }
        }


        if($info){
            setcaches($tenantId."_"."userinfo_".$uid, $info,7*24*60*60);
        }

        return 	$info;
    }
	
	/* 用户基本信息 */
	function getUserInfo($uid,$type=0) {
		$info = Cache_Users::getInstance()->getUserInfoCache($uid);
        $configInfo = getConfigPub();

        /**
         * 获取 不可转可提现的不可提余额
         */
        $redis = connectRedis();
        $length = $redis->lLen($uid.'_reward_time');
        $redisList = $redis->lRange($uid.'_reward_time',0,$length);
        $totalAmount = 0;
        foreach ($redisList as $value){
            $amount = $redis->get($uid.'_'.$value.'_reward') ;
            if ($amount > 0){
                $totalAmount = bcadd($totalAmount,$amount,2);
            }else{
                $redis->lRem($uid.'_reward_time',$value,0);
            }
        }
        $info['can_be_withdrawn']  =  strval (bcsub ($info['nowithdrawable_coin'] , $totalAmount,2));
        $info['is_open_seeking_slice'] =  $configInfo['is_open_seeking_slice'];// 贴吧是否开启
        $info['posting_strategy'] =  $configInfo['posting_strategy'];// 贴吧发帖策略
        $info['comment_strategy'] =  $configInfo['comment_strategy'];// 贴吧发帖策略
        $info['seeking_slice_strategy'] =  $configInfo['seeking_slice_strategy'];// 贴吧发帖策略
        $info['push_strategy'] =  $configInfo['push_strategy'];// 贴吧发帖策略
        $info['seeking_slice_bonus_min'] =  $configInfo['seeking_slice_bonus_min'];// 贴吧发帖策略
        $info['seeking_slice_bonus_max'] =  $configInfo['seeking_slice_bonus_max'];// 贴吧发帖策略
        $info['avatar'] = get_upload_path($info['avatar']); // 头像
        $info['avatar_thumb'] = get_upload_path($info['avatar_thumb']); // 头像缩略图
        $count_long=DI()->notorm->video_long
            ->where('uid=?',$uid)
            ->where('status=?',2)
            ->count();
        $count_shot=DI()->notorm->video
            ->where('uid=?',$uid)
            ->where('status=?',2)
            ->count();
        $info['video_count'] = $count_long+$count_shot;
        // 获取用户vip过期时间
        $endTime = DI()->notorm->users_buy_longvip
        ->where('uid=?',$uid)->order("id desc")->select('endtime,vip_level')->fetchOne();
        $info["vip_expire"] = 0;
        if(!empty($endTime['endtime'])){
            if($endTime['endtime']>time()){
               $info["vip_expire"] = ceil(($endTime['endtime']-time())/86400);
               $info["vip_grade"] = $endTime['vip_level'];
               $info['userlevel'] = $endTime['vip_level'];
            }
            
        }else{
            $info["vip_grade"] = '';
        }
        // $tenantId=getTenantId();//租户ID
        // $vip_longgrade=DI()->notorm->vip_longgrade
        // ->where('status=1 and tenant_id = '.$tenantId)
        // ->select('vip_grade,name')
        // ->order('id asc')
        // ->fetchAll();
        // if(!empty($vip_longgrade)){
        //     foreach($vip_longgrade as $v){
        //         if($info['userlevel']==$v['vip_grade']){
        //             $info['userlevel'] = $v['name'];
        //         }
        //     }
        // }
        // if(empty($info['userlevel'])){
        //     $info['userlevel'] = '';
        // }
        if($info){
            $info['is_set_payment_password'] = $info['payment_password'] ? 1 : 0;
            unset($info['payment_password']);
            $info['level']=(string)getLevel($info['consumption']);
            $info['level'] = $info['level'] ? $info['level'] : '0';
            $info['level_anchor']=(string)getLevelAnchor($info['votestotal']);
            $levelist = array_column(getLevelList(),null,'levelid');
            $userexperlevel = $levelist[$info['level']];
            $info['level_thumb'] = $userexperlevel['thumb'];
            $info['level_colour'] = $userexperlevel['colour'];
            $info['level_thumb_mark'] = $userexperlevel['thumb_mark'];
//            $info['vip']=getUserVip($uid);
            $info['liang']=getUserLiang($uid);
        }else if($type==1){
            return 	$info;
        }else{
            $info['id']=$uid;
            $info['user_nicename']='用户不存在';
            $info['avatar']=get_upload_path('/default.jpg');
            $info['avatar_thumb']=get_upload_path('/default_thumb.jpg');
            $info['coin']='0';
            $info['sex']='0';
            $info['signature']='';
            $info['consumption']='0';
            $info['votestotal']='0';
            $info['province']='';
            $info['city']='';
            $info['birthday']='';
            $info['issuper']='0';
            $info['level']='1';
            $info['level_anchor']='1';

            $info['level_thumb'] = '';
            $info['level_colour'] = '';
            $info['level_thumb_mark'] = '';
        }
        return 	$info;
	}
	
	/* 会员等级 */
    
	function getLevelList($levelid=null){
        $tenant_id = getTenantId();
        $key='level_'.$tenant_id;
		$level=getcaches($key);
		if(!$level){
			$level=DI()->notorm->experlevel
					->select("*")
                    ->where(['tenant_id'=>$tenant_id])
					->order("experience asc")
					->fetchAll();
            foreach($level as $k=>$v){
                $v['thumb']=get_upload_path($v['thumb']);
                if($v['colour']){
                    $v['colour']='#'.$v['colour'];
                }else{
                    $v['colour']='#ffdd00';
                }
                $level[$k]=$v;
            }
			setcaches($key,$level);			 
		}
		if($levelid && count($level) > 0){
		    $level_list = array_column($level,null,'levelid');
            return isset($level_list[$levelid]) ? $level_list[$levelid] : [];
        }
        
        return $level;
    }
	function getLevel($experience){
		$levelid=1;
		$level=getLevelList();

		foreach($level as $k=>$v){
		    if($experience >= $v['experience']){
                $levelid=$v['levelid'];
            }
		}
		return $levelid;
	}

	/*
	 * 还差多少钻石可以发言
	 * */
    function exp_can_speak($experience){
        $speak_level = intval(getConfigPri()['speak_level']);
        if($speak_level <= 0){
            return 0;
        }
        $leveinfo = getLevelList($speak_level);
        $exp_experience = $leveinfo['experience']-$experience;

        return $exp_experience >= 0 ? $exp_experience : 0;
    }

    /*
     * 获取用户经验等级信息
     * */
    function getLevelInfo($experience){
        $info = [];
        $level=getLevelList();
        foreach($level as $k=>$v){
            if($experience >= $v['experience']){
                $info = $v;
            }
        }
        return $info;
    }

    /*
    * 获取下一个用户经验等级信息
    * */
    function getNextLevelInfo($experience){
        $info = [];
        $level=getLevelList();
        $num = 0;
        foreach($level as $k=>$v){
            if($experience >= $v['experience']){
                $num++;
                $info = $v;
            }
        }
        $info = count($level) > $num ? $level[$num] : $info;

        return $info;
    }

    /*
    * 获取主播经验等级信息
    * */
    function getAnchorLevelInfo($experience){
        $info = [];
        $level=getLevelAnchorList();
        foreach($level as $k=>$v){
            if($experience >= $v['experience']){
                $info = $v;
            }
        }
        return $info;
    }

    /*
    * 获取下一个主播经验等级信息
    * */
    function getAnchorNextLevelInfo($experience){
        $info = [];
        $level=getLevelAnchorList();
        $num = 0;
        foreach($level as $k=>$v){
            if($experience >= $v['experience']){
                $num++;
                $info = $v;
            }
        }
        $info = count($level) > $num ? $level[$num] : $info;
        return $info;
    }

/* 主播等级 */
	function getLevelAnchorList($levelid=null){
        $tenant_id = getTenantId();
		$key='levelanchor_'.$tenant_id;
		$level=getcaches($key);
		if(!$level){
			$level=DI()->notorm->experlevel_anchor
					->select("*")
                    ->where(['tenant_id'=>$tenant_id])
					->order("experience asc")
					->fetchAll();
            foreach($level as $k=>$v){
                $v['thumb']=get_upload_path($v['thumb']);
                $v['thumb_mark']=get_upload_path($v['thumb_mark']);
            }
			setcaches($key,$level);			 
		}
        if($levelid && count($level) > 0){
            $level_list = array_column($level,null,'levelid');
            return isset($level_list[$levelid]) ? $level_list[$levelid] : [];
        }
        
        return $level;
    }
	function getLevelAnchor($experience){
		$levelid=1;
        $level=getLevelAnchorList();

		foreach($level as $k=>$v){
            if($experience >= $v['experience']){
                $levelid=$v['levelid'];
            }
		}
		return $levelid;
	}

	/* 统计 直播 */
	function getLives($uid) {
		/* 直播中 */
		$count1=DI()->notorm->users_live
				->where('uid=? and islive="1"',$uid)
				->count();
		/* 回放 */
		$count2=DI()->notorm->users_liverecord
					->where('uid=? ',$uid)
					->count();
		return 	$count1+$count2;
	}		
	
	/* 统计 关注 */
	function getFollows($uid) {
		$count=DI()->notorm->users_attention
				->where('uid=? ',$uid)
				->count();
		return 	$count;
	}			
	
	/* 统计 粉丝 */
	function getFans($uid) {
		$count=DI()->notorm->users_attention
				->where('touid=? ',$uid)
				->count();
		return 	$count;
	}		
	/**
	*  @desc 根据两点间的经纬度计算距离
	*  @param float $lat 纬度值
	*  @param float $lng 经度值
	*/
	function getDistance($lat1, $lng1, $lat2, $lng2){
		$earthRadius = 6371000; //近似地球半径 单位 米
		 /*
		   Convert these degrees to radians
		   to work with the formula
		 */

		$lat1 = ($lat1 * pi() ) / 180;
		$lng1 = ($lng1 * pi() ) / 180;

		$lat2 = ($lat2 * pi() ) / 180;
		$lng2 = ($lng2 * pi() ) / 180;


		$calcLongitude = $lng2 - $lng1;
		$calcLatitude = $lat2 - $lat1;
		$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
		$calculatedDistance = $earthRadius * $stepTwo;
		
		$distance=$calculatedDistance/1000;
		if($distance<10){
			$rs=round($distance,2);
		}else if($distance > 1000){
			$rs='1000';
		}else{
			$rs=round($distance);
		}
		return $rs.'km';
	}
	/* 判断账号是否禁用 */
	function isBan($uid){
		$status=DI()->notorm->users
					->select("user_status")
					->where('id=?',$uid)
					->fetchOne();
		if(!$status || $status['user_status']==0){
			return 0;
		}
		return 1;
	}
	/* 是否认证 */
	function isAuth($uid){
        $auth_info = DI()->notorm->family_auth
            ->select("status,ct_type")
            ->where('uid=?',$uid)
            ->fetchOne();
        if($auth_info && $auth_info['status']==1){
            return $auth_info;
        }
		$auth_info = DI()->notorm->users_auth
					->select("status,ct_type")
					->where('uid=?',$uid)
					->fetchOne();
		if($auth_info && $auth_info['status']==1){
			return $auth_info;
		}
		return 0;
	}
	/* 过滤字符 */
	function filterField($field){
		$configpri=getConfigPri();
		
		$sensitive_field=$configpri['sensitive_field'];
		
		$sensitive=explode(",",$sensitive_field);
		$replace=array();
		$preg=array();
		foreach($sensitive as $k=>$v){
			if($v){
				$re='';
				$num=mb_strlen($v);
				for($i=0;$i<$num;$i++){
					$re.='*';
				}
				$replace[$k]=$re;
				$preg[$k]='/'.$v.'/';
			}else{
				unset($sensitive[$k]);
			}
		}
		
		return preg_replace($preg,$replace,$field);
	}
	/* 时间差计算 */
	function datetime($time){
		$cha=time()-$time;
		$iz=floor($cha/60);
		$hz=floor($iz/60);
		$dz=floor($hz/24);
		/* 秒 */
		$s=$cha%60;
		/* 分 */
		$i=floor($iz%60);
		/* 时 */
		$h=floor($hz/24);
		/* 天 */
		
		if($cha<60){
			return $cha.'秒前';
		}else if($iz<60){
			return $iz.'分钟前';
		}else if($hz<24){
			return $hz.'小时'.$i.'分钟前';
		}else if($dz<30){
			return $dz.'天前';
		}else{
			return date("Y-m-d",$time);
		}
	}		
	/* 时长格式化 */
	function getSeconds($cha,$type=0){	 
		$iz=floor($cha/60);
		$hz=floor($iz/60);
		$dz=floor($hz/24);
		/* 秒 */
		$s=$cha%60;
		/* 分 */
		$i=floor($iz%60);
		/* 时 */
		$h=floor($hz/24);
		/* 天 */
		
        if($type==1){
            if($s<10){
                $s='0'.$s;
            }
            if($i<10){
                $i='0'.$i;
            }

            if($h<10){
                $h='0'.$h;
            }
            
            if($hz<10){
                $hz='0'.$hz;
            }
            return $hz.':'.$i.':'.$s;
        }
        
        
		if($cha<60){
			return $cha.'秒';
		}else if($iz<60){
			return $iz.'分钟'.$s.'秒';
		}else if($hz<24){
			return $hz.'小时'.$i.'分钟'.$s.'秒';
		}else if($dz<30){
			return $dz.'天'.$h.'小时'.$i.'分钟'.$s.'秒';
		}
	}	

	/* 数字格式化 */
	function NumberFormat($num){
		if($num<10000){

		}else if($num<1000000){
			$num=round($num/10000,2).'万';
		}else if($num<100000000){
			$num=round($num/10000,1).'万';
		}else if($num<10000000000){
			$num=round($num/100000000,2).'亿';
		}else{
			$num=round($num/100000000,1).'亿';
		}
		return $num;
	}
/* 数字格式化  小数位进一保留一位 */
function  NumberCeil($num){

    if ($num>=100000000){
        $num=bcdiv(ceil($num/10000000),10,1).'Y';

    }elseif ($num>=10000 && $num<100000000 ){

        $num=bcdiv(ceil($num/1000),10,1).'W';
    }
    return strval($num);

}


/**
	*  @desc 获取推拉流地址
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKeyA($host,$stream,$type){
		$configpri=getConfigPri();
		$cdn_switch=$configpri['cdn_switch'];
		//$cdn_switch=3;
		switch($cdn_switch){
			case '1':
				$url=PrivateKey_ali($host,$stream,$type);
				break;
			case '2':
				$url=PrivateKey_tx($host,$stream,$type);
				break;
			case '3':
				$url=PrivateKey_qn($host,$stream,$type);
				break;
			case '4':
				$url=PrivateKey_ws($host,$stream,$type);
				break;
			case '5':
				$url=PrivateKey_wy($host,$stream,$type);
				break;
			case '6':
				$url=PrivateKey_ady($host,$stream,$type);
				break;
			case '7':
				//自定义服务器
				//$url='rtmp://120.25.106.132:1935/live/livestream';
				$url=PrivateKey_customSrs($host,$stream,$type);
				break;
            case '8': // 青点云
                $url=PrivateKey_qdy($host,$stream,$type);
                break;
            case '9': // 腾讯云 rtmps
                $url=PrivateKey_tx_rtmps($host,$stream,$type);
                break;
		}

		
		return $url;
	}

	/**
	 * 自定义的srs流服务器鉴权
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	 */
	function PrivateKey_customSrs($host,$stream,$type){
		$configpri=getConfigPri();
		$push=$configpri['srs_push_url'];
		$pull=$configpri['srs_pull_url'];
		$key_push=$configpri['srs_push_key'];
		$key_pull=$configpri['srs_pull_key'];
		$flv_pull=$configpri['srs_flv_pull_url'];
		$time=time();

		if($type==1){
			$domain=$host.'://'.$push;
		}else{
			if(strpos($stream,'.flv')){
				$domain=$host.'://'.$flv_pull;
			}
			else{
				$domain=$host.'://'.$pull;
			}
			
		}

		$filename="/".$stream;

		if($type==1){
            if($key_push!=''){
                $sstring = $filename."-".$time."-0-0-".$key_push;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
			if($auth_key){
				$auth_key='?'.$auth_key;
			}
			$url=$domain.$filename.$auth_key;
		}else{
            if($key_pull!=''){
                $sstring = $filename."-".$time."-0-0-".$key_pull;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
			if($auth_key){
				$auth_key='?'.$auth_key;
			}
			$url=$domain.$filename.$auth_key;
		}
		
		return $url;
	}
	
	/**
	*  @desc 阿里云直播A类鉴权
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_ali($host,$stream,$type){
		$configpri=getConfigPri();
		$push=$configpri['push_url'];
		$pull=$configpri['pull_url'];
		$key_push=$configpri['auth_key_push'];
		$length_push=$configpri['auth_length_push'];
		$key_pull=$configpri['auth_key_pull'];
		$length_pull=$configpri['auth_length_pull'];
        
		if($type==1){
			$domain=$host.'://'.$push;
			$time=time() + $length_push;
		}else{
			$domain=$host.'://'.$pull;
			$time=time() + $length_pull;
		}
		
		$filename="/5showcam/".$stream;

		if($type==1){
            if($key_push!=''){
                $sstring = $filename."-".$time."-0-0-".$key_push;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
			if($auth_key){
				$auth_key='?'.$auth_key;
			}
			$url=$domain.$filename.$auth_key;
		}else{
            if($key_pull!=''){
                $sstring = $filename."-".$time."-0-0-".$key_pull;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
			if($auth_key){
				$auth_key='?'.$auth_key;
			}
			$url=$domain.$filename.$auth_key;
		}
		
		return $url;
	}
	
	/**
	*  @desc 腾讯云推拉流地址
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_tx($host,$stream,$type){
		$configpri=getConfigPri();
		$bizid=$configpri['tx_bizid'];
		$push_url_key=$configpri['tx_push_key'];
		$push=$configpri['tx_push'];
		$pull=$configpri['tx_pull'];
		$stream_a=explode('.',$stream);
		$streamKey = $stream_a[0];
		$ext = isset($stream_a[1])?$stream_a[1]:'';
		
		//$live_code = $bizid . "_" .$streamKey;      	
		$live_code = $streamKey;      	
		$now_time = time() + 3*60*60;
		$txTime = dechex($now_time);

		$txSecret = md5($push_url_key . $live_code . $txTime);
		$safe_url = "&txSecret=" .$txSecret."&txTime=" .$txTime;		

		if($type==1){
			//$push_url = "rtmp://" . $bizid . ".livepush2.myqcloud.com/live/" .  $live_code . "?bizid=" . $bizid . "&record=flv" .$safe_url;	可录像
			$url = "rtmp://{$push}/live/" . $live_code . "?bizid=" . $bizid . "" .$safe_url;	
		}else{
			//$url = "http://{$pull}/live/" . $live_code . ".flv";
			//$url = "$host.://{$pull}/live/" . $live_code . ".flv";

			if(strpos($stream,'.flv')){
				$url = $host."://{$pull}/live/" . $live_code . ".flv";
			}
			else{
				$url = $host."://{$pull}/live/" . $live_code;
			}
		}
		
		return $url;
	}

/**
 *  @desc 腾讯云推拉流地址 rtmps
 *  @param string $host 协议，如:http、rtmp
 *  @param string $stream 流名,如有则包含 .flv、.m3u8
 *  @param int $type 类型，0表示播流，1表示推流
 */
function PrivateKey_tx_rtmps($host,$stream,$type){
    $configpri=getConfigPri();
    $bizid=$configpri['tx_rtmps_bizid'];
    $push_url_key=$configpri['tx_rtmps_push_key'];
    $push=$configpri['tx_rtmps_push'];
    $pull=$configpri['tx_rtmps_pull'];
    $stream_a=explode('.',$stream);
    $streamKey = $stream_a[0];
    $ext = isset($stream_a[1])?$stream_a[1]:'';

    //$live_code = $bizid . "_" .$streamKey;
    $live_code = $streamKey;
    $now_time = time() + 3*60*60;
    $txTime = dechex($now_time);

    $txSecret = md5($push_url_key . $live_code . $txTime);
    $safe_url = "&txSecret=" .$txSecret."&txTime=" .$txTime;

    if($type==1){
        if($host == 'rtmp'){ // 只有推流域名是rtpms，拉流还是rtpm
            $host = 'rtmps';
        }

        //$push_url = "rtmp://" . $bizid . ".livepush2.myqcloud.com/live/" .  $live_code . "?bizid=" . $bizid . "&record=flv" .$safe_url;	可录像
        $url = $host."://{$push}/live/" . $live_code . "?bizid=" . $bizid . "" .$safe_url;

//        $url = $host."://{$push}/live/" . $live_code; // 面跟了那些参数之后就推不上去了，跟参数只能使用rtmp推流
    }else{
        //$url = "http://{$pull}/live/" . $live_code . ".flv";
        //$url = "$host.://{$pull}/live/" . $live_code . ".flv";

        if(strpos($stream,'.flv')){
            $url = $host."://{$pull}/live/" . $live_code . ".flv";
        }
        else{
            $url = $host."://{$pull}/live/" . $live_code;
        }
    }

    return $url;
}

/**
	*  @desc 七牛云直播
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_qn($host,$stream,$type){
		
		$configpri=getConfigPri();
		$ak=$configpri['qn_ak'];
		$sk=$configpri['qn_sk'];
		$hubName=$configpri['qn_hname'];
		$push=$configpri['qn_push'];
		$pull=$configpri['qn_pull'];
		$stream_a=explode('.',$stream);
		$streamKey = $stream_a[0];
		$ext = $stream_a[1];

		if($type==1){
			$time=time() +60*60*10;
			//RTMP 推流地址
			$url = \Qiniu\Pili\RTMPPublishURL($push, $hubName, $streamKey, $time, $ak, $sk);
		}else{
			if($ext=='flv'){
				$pull=str_replace('pili-live-rtmp','pili-live-hdl',$pull);
				//HDL 直播地址
				$url = \Qiniu\Pili\HDLPlayURL($pull, $hubName, $streamKey);
			}else if($ext=='m3u8'){
				$pull=str_replace('pili-live-rtmp','pili-live-hls',$pull);
				//HLS 直播地址
				$url = \Qiniu\Pili\HLSPlayURL($pull, $hubName, $streamKey);
			}else{
				//RTMP 直播放址
				$url = \Qiniu\Pili\RTMPPlayURL($pull, $hubName, $streamKey);
			}
		}
				
		return $url;
	}
	
	/**
	*  @desc 网宿推拉流
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_ws($host,$stream,$type){
		$configpri=getConfigPri();
		if($type==1){
			$domain=$host.'://'.$configpri['ws_push'];
			//$time=time() +60*60*10;
		}else{
			$domain=$host.'://'.$configpri['ws_pull'];
			//$time=time() - 60*30 + $configpri['auth_length'];
		}
		
		$filename="/".$configpri['ws_apn']."/".$stream;

		$url=$domain.$filename;
		
		return $url;
	}
	
	/**网易cdn获取拉流地址**/
	function PrivateKey_wy($host,$stream,$type){
		$configpri=getConfigPri();
		$appkey=$configpri['wy_appkey'];
		$appSecret=$configpri['wy_appsecret'];
		$nonce =rand(1000,9999);
		$curTime=time();
		$var=$appSecret.$nonce.$curTime;
		$checkSum=sha1($appSecret.$nonce.$curTime);
		
		$header =array(
			"Content-Type:application/json;charset=utf-8",
			"AppKey:".$appkey,
			"Nonce:" .$nonce,
			"CurTime:".$curTime,
			"CheckSum:".$checkSum,
		);
        if($type==1){
            $url='https://vcloud.163.com/app/channel/create';
            $paramarr = array(
                "name"  =>$stream,
                "type"  =>0,
            );
        }else{
            $url='https://vcloud.163.com/app/address';
            $paramarr = array(
                "cid"  =>$stream,
            );
        }
        $paramarr=json_encode($paramarr);

		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL, $url);
		curl_setopt($curl,CURLOPT_HEADER, 0);
		curl_setopt($curl,CURLOPT_HTTPHEADER, $header); 
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_POST, 1);
		curl_setopt($curl,CURLOPT_POSTFIELDS, $paramarr);
		$data = curl_exec($curl);
		curl_close($curl);
		$rs=json_decode($data,1);
		return $rs;
	}
	
	/**
	*  @desc 奥点云推拉流
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_ady($host,$stream,$type){
		$configpri=getConfigPri();
		$stream_a=explode('.',$stream);
		$streamKey = $stream_a[0];
		$ext = $stream_a[1];

		if($type==1){
			$domain=$host.'://'.$configpri['ady_push'];
			//$time=time() +60*60*10;
			$filename="/".$configpri['ady_apn'].'/'.$stream;
			$url=$domain.$filename;
		}else{
			if($ext=='m3u8'){
				$domain=$host.'://'.$configpri['ady_hls_pull'];
				//$time=time() - 60*30 + $configpri['auth_length'];
				$filename="/".$configpri['ady_apn']."/".$stream;
				$url=$domain.$filename;
			}else{
				$domain=$host.'://'.$configpri['ady_pull'];
				//$time=time() - 60*30 + $configpri['auth_length'];
				$filename="/".$configpri['ady_apn']."/".$stream;
				$url=$domain.$filename;
			}
		}
				
		return $url;
	}

/**
 *  @desc 青点云推拉流（copy 网宿推拉流）
 *  @param string $host 协议，如:http、rtmp
 *  @param string $stream 流名,如有则包含 .flv、.m3u8
 *  @param int $type 类型，0表示播流，1表示推流
 */
function PrivateKey_qdy($host,$stream,$type){
    $configpri=getConfigPri();
    if($type==1){
        $domain=$host.'://'.$configpri['qdy_push'];
    }else{
        $domain=$host.'://'.$configpri['qdy_pull'];
    }
    $filename="/"."mb/".$stream;
    $url=$domain.$filename;
    return $url;
}

/* 游戏类型 */
    function getGame($action){
        $game_action=array(
            '0'=>'',
            '1'=>'智勇三张',
            '2'=>'海盗船长',
            '3'=>'转盘',
            '4'=>'开心牛仔',
            '5'=>'二八贝',
        );
        
        return $game_action[$action];
    }

	
	/* 获取用户VIP */
	function getUserVip($uid){
		$rs=array(
			'type'=>'0',
		);
		$user_vip_info = getUserVipInfo($uid);
		if(!$user_vip_info){
            return $rs;
        }else{
            $rs['type']='1';
        }
		return $rs;
	}

    /* 获取vip详情 */
    function getVipInfo($vip_id){
        $vipinfo=getcaches('vipinfo_'.$vip_id);
        if(!$vipinfo){
            $vipinfo=DI()->notorm->vip->select("*")->where('id=?',$vip_id)->fetchOne();
            if($vipinfo){
                setcaches('vipinfo_'.$vip_id,$vipinfo);
            }
        }
        return $vipinfo;
    }

    /*
     * 获取坐骑列表
     * */
    function get_carlist($tenant_id,$id=''){
        $id = strval($id);
        $list=getcaches('carlist_'.$tenant_id);
        if(!$list){
            $list=DI()->notorm->car
                ->select("*")
                ->where(['tenant_id'=>intval($tenant_id)])
                ->order("orderno asc,id desc")
                ->fetchAll();
            foreach ($list as $key=>$val){
                $list[$key]['thumb'] = get_upload_path($val['thumb']);
                $list[$key]['swf']   = get_upload_path($val['swf']);
            }
            if($list){
                setcaches('carlist_'.$tenant_id,$list);
            }
        }
        if($id != ''){
            $carlist = array_column($list,null,'id');
            return isset($carlist[$id]) ? $carlist[$id] : [];
        }
        return $list;
    }

    /*
     * 清除坐骑缓存
     * */
    function delCarlistCache($tenant_id){
        delcache('carlist_'.$tenant_id);
    }

    /*
     * 获取用户坐骑列表
     * */
    function get_user_carlist($uid){
        $list=getcaches('user_carlist_'.$uid);
        if(!$list){
            $list=DI()->notorm->users_car
                ->select("*")
                ->where('uid=?',$uid)
                ->order("addtime desc")
                ->fetchAll();
            if($list){
                setcaches('user_carlist_'.$uid,$list);
            }
        }
        return $list;
    }

    /*
     * 清除用户坐骑缓存
     * */
    function delUserCarlistCache($uid){
        delcache('user_carlist_'.$uid);
    }

	/* 获取用户坐骑 */
	function getUserCar($uid){
		$rs=array(
			'id'=>'0',
			'swf'=>'',
			'swftime'=>'0',
			'words'=>'',
		);
        //判断是否有豪礼送坐骑，如果有，优先展示
        $chargeinfo = get_user_chargecar($uid);
        if($chargeinfo){
            $info=get_carlist(getTenantId(),$chargeinfo['car_id']);

            $rs['id']=$info['id'];
            $rs['swf']=get_upload_path($info['swf']) ;
            $rs['swftime']=$info['swftime'];
            $rs['words']=$info['words'];
            return  $rs;
        }

		$usercarinfo = null;
        $list = get_user_carlist($uid);
		foreach ($list as $key=>$val){
		    if($val['status']==1){
                $usercarinfo = $val;
            }
        }
        $noble = getUserNoble($uid);
        if(is_array($noble) && $noble['car_id'] == $usercarinfo['id'] && $noble['exclu_car'] == 0){ // 贵族专属坐骑未开启
            return $rs;
        }
		if($usercarinfo){
			if($usercarinfo['endtime']<= time()){
				return $rs;
			}
			$carid = ($usercarinfo['carid'] == 0 && $usercarinfo['type'] == 1) ? $noble['car_id'] : $usercarinfo['carid'];
			$info=get_carlist(getTenantId(),$carid);
            if($info){
                $rs['id']=$info['id'];
                $rs['swf']=get_upload_path($info['swf']) ;
                $rs['swftime']=$info['swftime'];
                $rs['words']=$info['words'];
            }
		}
		return $rs;
	}

	/* 获取用户靓号 */
	function getUserLiang($uid){
		$rs=array(
			'name'=>'0',
		);
		$nowtime=time();
		$key='liang_'.$uid;
		$isexist=getcaches($key);
		if(!$isexist){
			$isexist=DI()->notorm->liang
						->select("*")
						->where('uid=? and status=1 and state=1',$uid)
						->fetchOne();	
			if($isexist){
				setcaches($key,$isexist);
			}
		}
		if($isexist){
			$rs['name']=$isexist['name'];
		}
		
		return $rs;
	}
	
	/* 三级分销 */
	function setAgentProfit($uid,$total){
				/* 分销 */
		$distribut1=0;
		$distribut2=0;
		$distribut3=0;
		$configpri=getConfigPri();
		if($configpri['agent_switch']==1){
			$agent=DI()->notorm->users_agent
				->select("*")
				->where('uid=?',$uid)
				->fetchOne();
			$isinsert=0;
			/* 一级 */
			if($agent['one_uid'] && $configpri['distribut1']){
				$distribut1=$total*$configpri['distribut1']*0.01;
				$profit=DI()->notorm->users_agent_profit
					->select("*")
					->where('uid=?',$agent['one_uid'])
					->fetchOne();
				if($profit){
					DI()->notorm->users_agent_profit
						->where('uid=?',$agent['one_uid'])
						->update(array('one_profit' => new NotORM_Literal("one_profit + {$distribut1}")));
				}else{
					DI()->notorm->users_agent_profit
						->insert(array('uid'=>$agent['one_uid'],'one_profit' =>$distribut1 ));
				}
				DI()->notorm->users
						->where('id=?',$agent['one_uid'])
						->update(array('votes' => new NotORM_Literal("votes + {$distribut1}")));
                delUserInfoCache($agent['one_uid']);
				$isinsert=1;
			}
			/* 二级 */
			if($agent['two_uid'] && $configpri['distribut2']){
				$distribut2=$total*$configpri['distribut2']*0.01;
				$profit=DI()->notorm->users_agent_profit
					->select("*")
					->where('uid=?',$agent['two_uid'])
					->fetchOne();
				if($profit){
					DI()->notorm->users_agent_profit
						->where('uid=?',$agent['two_uid'])
						->update(array('two_profit' => new NotORM_Literal("two_profit + {$distribut2}")));
				}else{
					DI()->notorm->users_agent_profit
						->insert(array('uid'=>$agent['two_uid'],'two_profit' =>$distribut2 ));
				}
				DI()->notorm->users
						->where('id=?',$agent['two_uid'])
						->update(array('votes' => new NotORM_Literal("votes + {$distribut2}")));
                delUserInfoCache($agent['two_uid']);
				$isinsert=1;
			}
			/* 三级 */
			/* if($agent['three_uid'] && $configpri['distribut3']){
				$distribut3=$total*$configpri['distribut3']*0.01;
				$profit=DI()->notorm->users_agent_profit
					->select("*")
					->where('uid=?',$agent['three_uid'])
					->fetchOne();
				if($profit){
					DI()->notorm->users_agent_profit
						->where('uid=?',$agent['three_uid'])
						->update(array('three_profit' => new NotORM_Literal("three_profit + {$distribut3}")));
				}else{
					DI()->notorm->users_agent_profit
						->insert(array('uid'=>$agent['three_uid'],'three_profit' =>$distribut3 ));
				}
				DI()->notorm->users
						->where('id=?',$agent['three_uid'])
						->update(array('votes' => new NotORM_Literal("votes + {$distribut3}")));
			    delUserInfoCache($agent['three_uid']);
				$isinsert=1;
			} */
			
			if($isinsert==1){
				$data=array(
					'uid'=>$uid,
					'total'=>$total,
					'one_uid'=>$agent['one_uid'],
					'two_uid'=>$agent['two_uid'],
					'three_uid'=>$agent['three_uid'],
					'one_profit'=>$distribut1,
					'two_profit'=>$distribut2,
					'three_profit'=>$distribut3,
					'addtime'=>time(),
				);
				
				DI()->notorm->users_agent_profit_recode->insert( $data );
				
			}
		}
		return 1;
		
	}
    
    /* 家族分成 */
    function setFamilyDivide($liveuid,$total){
        $configpri=getConfigPri();
	
		$anthor_total=$total;
		/* 家族 */
		if($configpri['family_switch']==1){
			$users_family=DI()->notorm->users_family
							->select("familyid,divide_family")
							->where('uid=? and state=2',$liveuid)
							->fetchOne();

			if($users_family){
				$familyinfo=DI()->notorm->family
							->select("uid,divide_family")
							->where('id=?',$users_family['familyid'])
							->fetchOne();
                if($familyinfo){
                    $divide_family=$familyinfo['divide_family'];

                    /* 主播 */
                    if( $users_family['divide_family']>=0){
                        $divide_family=$users_family['divide_family'];
                        
                    }
                    $family_total=$total * $divide_family * 0.01;
                    
                        $anthor_total=$total - $family_total;
                        $addtime=time();
                        $time=date('Y-m-d',$addtime);
                        DI()->notorm->family_profit
                               ->insert(array("uid"=>$liveuid,"time"=>$time,"addtime"=>$addtime,"profit"=>$family_total,"profit_anthor"=>$anthor_total,"total"=>$total,"familyid"=>$users_family['familyid']));

                    if($family_total){
                        
                        DI()->notorm->users
                                ->where('id = ?', $familyinfo['uid'])
                                ->update( array( 'votes' => new NotORM_Literal("votes + {$family_total}")  ));
                        delUserInfoCache($familyinfo['uid']);
                    }
                }
			}	
		}
        return $anthor_total;
    }
	
	/* ip限定 */
	function ip_limit($mobile){
		$configpri=getConfigPri();
		if($configpri['iplimit_switch']==0){
			return 0;
		}
		$date = date("Ymd");
        $origin_ip = get_client_ip();
		$ip = ip2long($origin_ip) ;
		$isexist=DI()->notorm->getcode_limit_ip
				->select('ip,date,times')
				->where(' ip=? ',$ip) 
				->fetchOne();
		if(!$isexist){
			$data=array(
				"ip" => $ip,
				"date" => $date,
				"times" => 1,
                "tenant_id" => intval(getTenantId()),
                "mobile" => $mobile,
                "origin_ip" => $origin_ip,
			);
			$isexist=DI()->notorm->getcode_limit_ip->insert($data);
			return 0;
		}elseif($date == $isexist['date'] && $isexist['times'] >= $configpri['iplimit_times'] ){
			return 1;
		}else{
			if($date == $isexist['date']){
				$isexist=DI()->notorm->getcode_limit_ip
						->where(' ip=? ',$ip) 
						->update(array('times'=> new NotORM_Literal("times + 1 "), "tenant_id" => intval(getTenantId()),  "mobile" => $mobile, "origin_ip" => $origin_ip));
				return 0;
			}else{
				$isexist=DI()->notorm->getcode_limit_ip
						->where(' ip=? ',$ip)
						->update(array('date'=> $date, 'times'=>1, "tenant_id" => intval(getTenantId()),  "mobile" => $mobile, "origin_ip" => $origin_ip));
				return 0;
			}
		}	
	}	
    
    /* 验证码记录 */
    function setSendcode($data){
        if($data){
            $data['addtime']=time();
            DI()->notorm->sendcode->insert($data);
        }
    }

    /* 验证码记录 */
    function checkUser($where){
        if($where==''){
            return 0;
        }

        $isexist=DI()->notorm->users->where($where)->fetchOne();
        
        if($isexist){
            return 1;
        }
        
        return 0;
    }
    
    /* 直播分类 */
    function getLiveClass(){
        $tenant_id = getTenantId();
        $key="getLiveClass_".$tenant_id;
		$list=getcaches($key);
		if(!$list){
            $list=DI()->notorm->live_class
                    ->select("*")
                    ->where('tenant_id', $tenant_id)
                    ->order("orderno asc,id desc")
                    ->fetchAll();
            foreach($list as $k=>$v){
                $list[$k]['thumb']=get_upload_path($v['thumb']);
            }
			setcaches($key,$list); 
		}
        return $list;        
        
    }

    /* 校验签名 */
    function checkSign($data,$sign){
        $key=DI()->config->get('app.sign_key');
        $str='';
        ksort($data);
        foreach($data as $k=>$v){
            $str.=$k.'='.$v.'&';
        }
        $str.=$key;
        $newsign=md5($str);
        if($sign==$newsign){
            return 1;
        }
        return 0;
    }

    /**
     * 校验上游系统签名
     * @param $data
     * @param $sign
     * @return int
     */
    function checkSupSign($data,$sign){
        $key=DI()->config->get('app.superior_sign_key');
        $str='';
        ksort($data);
        foreach($data as $k=>$v){
            $str.=$k.'='.$v.'&';
        }
        $str.=$key;
        $newsign=md5($str);

        if($sign==$newsign){
            return 1;
        }
        return 0;
    }

    /**
     * 校验租户app请求签名
     * @param $data
     * @param $sign
     * @param $tenantId
     * @return int
     */
    function checkTenantAppSign($data,$sign,$tenantId){
        $key=getTenantInfo($tenantId)['appkey'];
        $str='';
        ksort($data);
        foreach($data as $k=>$v){
            $str.=$k.'='.$v.'&';
        }
        $str.=$key;
        $newsign=md5($str);

        if($sign==$newsign){
            return 1;
        }
        return 0;
    }



    /* 用户退出，注销PUSH */
    function userLogout($uid){
        $list=DI()->notorm->users_pushid
                ->where('uid=?',$uid)
                ->delete();
        return 1;
    }
    
	/*获取音乐信息*/
	function getMusicInfo($user_nicename,$musicid){

		$res=DI()->notorm->users_music->select("id,title,author,img_url,length,file_url,use_nums")->where("id=?",$musicid)->fetchOne();

		if(!$res){
			$res=array();
			$res['id']='0';
			$res['title']='';
			$res['author']='';
			$res['img_url']='';
			$res['length']='00:00';
			$res['file_url']='';
			$res['use_nums']='0';
			$res['music_format']='@'.$user_nicename.'创作的原声';

		}else{
			$res['music_format']=$res['title'].'--'.$res['anchor'];
			$res['img_url']=get_upload_path($res['img_url']);
			$res['file_url']=get_upload_path($res['file_url']);
		}

		

		return $res;

	}

	/*距离格式化*/
	function distanceFormat($distance){
		if($distance<1000){
			return $distance.'米';
		}else{

			if(floor($distance/10)<10){
				return number_format($distance/10,1);  //保留一位小数，会四舍五入
			}else{
				return ">10千米";
			}
		}
	}

	/* 视频是否点赞 */
	function ifLike($uid,$videoid,$type =1){
		$like=DI()->notorm->users_video_like
				->select("id")
				->where("uid='{$uid}' and videoid='{$videoid}' ")
				->fetchOne();

		if($like){
			return 1;
		}else{
			return 0;
		}	
	}

	/* 视频是否踩 */
	function ifStep($uid,$videoid){
		$like=DI()->notorm->users_video_step
				->select("id")
				->where("uid='{$uid}' and videoid='{$videoid}'")
				->fetchOne();
		if($like){
			return 1;
		}else{
			return 0;
		}	
	}

	/* 腾讯COS处理 */
    function setTxUrl($url){
        
        if(!strstr($url,'myqcloud')){
            return $url;
        }
        
        $url_a=parse_url($url);
        
        $file=$url_a['path'];
        $signkey='Shanghai0912'; //腾讯云后台设置（控制台->存储桶->域名管理->CDN鉴权配置->鉴权Key）
        $now_time = time();
        $sign=md5($signkey.$file.$now_time);
        
        return $url.'?sign='.$sign.'&t='.$now_time;
        
    }
    
    /* 拉黑视频名单 */
	function getVideoBlack($uid){
		$videoids=array('0');
		$list=DI()->notorm->users_video_black
						->select("videoid")
						->where("uid='{$uid}'")
						->fetchAll();
		if($list){
			$videoids=array_column($list,'videoid');
		}
		
		$videoids_s=implode(",",$videoids);
		
		return $videoids_s;
	}



/* 判断主播是否认证 */
function checkauth($uid){
    //$authid=M("users_auth")->where("uid='{$uid}'")->getField("status");
    $authid=  DI()->notorm->users_auth
        ->select("status")
        ->where("uid='{$uid}'")
        ->fetchOne();

        return $authid;

}
function  verifyBankCard($number){
    $arr_no = str_split($number);
    $last_n = $arr_no[count($arr_no) - 1];
    krsort($arr_no);
    $i = 1;
    $total = 0;
    foreach ($arr_no as $n) {
        if ($i % 2 == 0) {
            $ix = $n * 2;
            if ($ix >= 10) {
                $nx = 1 + ($ix % 10);
                $total += $nx;
            } else {
                $total += $ix;
            }
        } else {
            $total += $n;
        }
        $i++;
    }
    $total -= $last_n;
    $total *= 9;

    if ($last_n == ($total % 10)) {
        return 0;
    }
    return 1002;
}

/**
 *  验证身份证号
 * @param $idcard
 * @return bool
 */
function checkIdCard($idcard){

    // 只能是18位

    if(strlen($idcard)!=18){

        return false;

    }

    // 取出本体码

    $idcard_base = substr($idcard, 0, 17);

    // 取出校验码

    $verify_code = substr($idcard, 17, 1);

    // 加权因子

    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

    // 校验码对应值

    $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

    // 根据前17位计算校验码

    $total = 0;

    for($i=0; $i<17; $i++){

        $total += substr($idcard_base, $i, 1)*$factor[$i];

    }

    // 取模

    $mod = $total % 11;

    // 比较校验码

    if($verify_code == $verify_code_list[$mod]){

        return true;

    }else{

        return false;

    }

}
function getOrderid($uid){
    $orderid=$uid.'_'.date('YmdHis').rand(100,999);
    return $orderid;
}
function getTxorder($uid){
    $orderid='tx_'.$uid.'_'.date('YmdHis').rand(100,999);
    return $orderid;
}

/* 获取代理下线成员
 * $data 用户
 */
function all_sub($data,$puid){
    $directly_sub = array();
    foreach ($data as $key=>$val){
        if(in_array($val['one_uid'],$puid)){
            array_push($directly_sub,$val['uid']);
            unset($data[$key]);
        }
    }
    if(count($directly_sub) > 0 && count($data) > 0){
        $d_sub = all_sub($data,$directly_sub);
        $directly_sub = array_merge($directly_sub,$d_sub);
    }
    return array_unique($directly_sub);
}

function Sec2Time($time){
    if(is_numeric($time)){
        $time = round($time,2);
        $timeArray = explode('.',$time);
        $time = $timeArray[0];
        $value = array(
            "hours" => '00',
            "minutes" => '00',
            "seconds" => '00',
            'millisecond' => '0'
        );


        if($time >= 3600){
            $value["hours"] = floor($time/3600);
            if (  $value["hours"]< 10 && $value["hours"]>0 ){
                $value["hours"]  = '0'. $value["hours"];
            }
            $time = ($time%3600);
        }
        if($time >= 60){
            $value["minutes"] = floor($time/60);
            if ( $value["minutes"]< 10 && $value["minutes"]>0 ){
                $value["minutes"]  = '0'. $value["minutes"];
            }
            $time = ($time%60);
        }
        $value["seconds"] = floor($time);
        if ( $value["seconds"]< 10  && $value["seconds"]>0){
            $value["seconds"]  = '0'. $value["seconds"];
        }
        ;
        $t=  $value["hours"] .":". $value["minutes"] .":".$value["seconds"].":".$timeArray[1];
        return $t;

    }else{
        return (bool) FALSE;
    }
}

/* 积分明细变更类型 */
function actionType(){
    return array(
        '1' => '首页（点击）',
        '2' => '游戏（点击）',
        '3' => '直播（点击）',
        '4' => '我的（点击）',
        '5' => '启动页广告',
        '6' => '啪啪（点击）',
        '7' => '首页推荐（观看）',
        '8' => '首页精选（点击）',
        '9' => '首页点赞排行（点击）',
        '10' => '首页最新上传（点击）',
        '11' => '首页周榜（点击）',
        '12' => '首页月榜（点击）',
        '13' => '首页最高人气（点击）',
        '14' => '首页最多下载（点击）',
        '15' => '首页最多观看（点击）',
        '16' => '首页广告（点击）',
        '17' => '游戏广告（点击）',
        '18' => '直播广告（点击）',
        '19' => '直播房间X（点击）',
        '20' => '直播标签N（点击）',
        '21' => '啪啪标签（点击）',
        '22' => '啪啪搜索（搜索）',
        '23' => '啪啪下载（点击）',
        '24' => '啪啪历史记录（点击）',
        '25' => '啪啪广告（点击跳转）',
        '26' => '啪啪查看更多（点击）',
        '27' => '啪啪视频列表（点击视频）',
        '28' => '我的设置（点击）',
        '29' => '我的修改昵称（修改昵称）',
        '30' => '我的设置（修改密码）',
        '31' => '我的设置（清除缓存）',
        '32' => '我的设置（检查更新）',
        '33' => '我的设置（用户协议）',
        '34' => '我的设置（退出）',
        '35' => '我的钱包（点击）',
        '36' => '我的钱包（充值）',
        '37' => '我的钱包（提现）',
        '38' => '我的广告（点击）',
        '39' => '我的观看历史（点击）',
        '40' => '我的VIP（VIP购买）',
        '41' => '我的视频（点击）',
        '42' => '我的视频长视频（播放）',
        '43' => '我的视频长视频（上传）',
        '44' => '我的视频短视频（播放）',
        '45' => '我的视频短视频（上传）',
        '46' => '我的收支（查看充值）',
        '47' => '我的收支（查看收支）',
        '48' => '我的收支（查看提现）',
        '49' => '我的收藏长视频（查看）',
        '50' => '我的收藏长视频（播放）',
        '51' => '我的收藏短视频（查看）',
        '52' => '我的收藏短视频（播放）',
        '53' => '我的下载长视频（查看）',
        '54' => '我的下载长视频（播放）',
        '55' => '我的下载短视频（查看）',
        '56' => '我的下载短视频（播放）',
        '57' => '我的福利（查看）',
        '58' => '我的福利（兑换）',
        '59' => '我的常见问题（查看）',
        '60' => '我的推广详情（查看）',
        '61' => '我的推广详情（保存图片）',
        '62' => '我的推广详情（复制链接）',
        '63' => '我的反馈（查看）',
        '64' => '我的反馈（提交）',
        '65' => '我的关注（查看）',
        '66' => '我的关注（播放）',
        '67' => '我的开播（认证）',
        '68' => '我的开播（开播）',
        '69' => '播放数量（记录）',
        '70' => '播放时长（记录）',
        '71' => '播放关注（关注）',
        '72' => '播放点赞（点赞）',
        '73' => '播放评论（评论）',
        '74' => '播放收藏（收藏）',
        '75' => '播放下载（下载）',
        '76' => '播放广告（查看）',
        '77' => '设置语言',
    );
}


function play_or_download_url($type =1 ){
    $tenantId  =  getTenantId();
    $playback_address_info =  DI()->notorm->playback_address
       ->where("is_enable = 1 and type = '{$type}' and tenant_id = '{$tenantId}' ")
        ->fetchOne();

    return $playback_address_info;
}

/*
* 判断是否为json
* */
function is_json($string) {
    if(is_string($string)){
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    return false;
}


function getCutvideo($tenant_id){
    if(empty($_FILES)){
        return [];
    }
    $file = $_FILES['file'];        //文件信息
    $filename = $file['name'];      //本地文件名
    $tmpFile = $file['tmp_name'];   //临时文件名
    $fileType = $file['type'];      //文件类型

    $tenant_id = $tenant_id == 0 ? getTenantId() : $tenant_id;
    $config = getConfigPub($tenant_id);
    if(!$config['url_of_push_to_java_cut_video']){
        logapi([$_GET, $_POST, $_FILES],  '视频上传失败，未设置视频上传url');
        return ['视频上传失败，未设置视频上传url'];
    }
    $cut_video_url_array = explode("\n",$config['url_of_push_to_java_cut_video']);
    foreach ($cut_video_url_array as $key=>$val) {
        if (!$val || !trim($val)) {
            continue;
        }
        $url = trim($val, '/');
        $result = postUploadFile($url,$filename,$tmpFile,'text/plain');
        $result = json_decode($result,true);
        if (!isset($result['code']) || $result['code'] != 200){
            logapi([$_GET, $_POST, $_FILES],  '【视频上传】-失败-'.$url);
            continue;
        }
        if(isset($result['data']) && isset($result['data']['fileStoreKey']) && $result['data']['fileStoreKey']){
            return $result;
        }else{
            logapi([$_GET, $_POST, $_FILES],  '【视频上传】-失败-'.$url);
            continue;
        }
    }
    return [];
}

/**
 * @param string $url 请求地址
 * @param string $filename 文件名
 * @param string $path 文件临时路劲
 * @param string $type 文件类型
 * @return mixed
 */
function postUploadFile($url,$filename,$path,$type = 'text/plain')
{
    //php 5.5以上的用法
    if (class_exists('\CURLFile')) {
        $data = array(
            'file' => new \CURLFile(realpath($path), $type, $filename),
        );
    } else {
        //5.5以下会走到这步
        $data = array(
            'file'=>'@'.realpath($path).";type=".$type.";filename=".$filename,
        );
    }set_time_limit(0);
    ini_set('max_execution_time', '0');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    $return_data = curl_exec($ch);
    curl_close($ch);
    return $return_data;
}

/**
 * 回调充值订单记录三方支付订单号
 * @param $orderno
 * @param $trade_no
 */
function addTradeNo($orderno,$trade_no){
    $result = DI()->notorm->users_charge->where('orderno=?',$orderno)->fetchOne();
    if (!$result){
        DI()->notorm->users_charge->where('orderno=?',$orderno)->update(['trade_no'=>$trade_no]);
    }
}

/* 清除用户基本信息缓存 */
function delUserInfoCache($uid){
    $redis=connectionRedis();
    $res = $redis->del("userinfo_".$uid);
    return $res;
}

/* 清除用户vip信息缓存 */
function delUserVipInfoCache($uid){
    $redis=connectionRedis();
    $tenant_id = getTenantId();
    $res = $redis->hDel('user_vip_info_'.$tenant_id, $uid);
    $res = $redis->hDel('user_vip_checking_info_'.$tenant_id, $uid);
    return $res;
}

/* 获取用户有效vip */
function getUserVipInfo($uid){
    $redis = connectionRedis();
    $tenant_id = getTenantId();
    $config  = getConfigPub();
    $users_vip_info = $redis->hGet('user_vip_info_'.$tenant_id, $uid);
    if(!$users_vip_info) {
        if ($config['vip_model'] == 1) {
            $users_vip_info = DI()->notorm->users_vip
                ->where('uid=? and endtime>?', intval($uid), time())
                ->order('grade desc')
                ->fetchOne();
        } else {
            $users_vip_info = DI()->notorm->users_vip
                ->where('uid=? and status in(1,2)', intval($uid))
                ->order('grade desc')
                ->fetchOne();
        }
        if($users_vip_info){
            $redis->hSet('user_vip_info_'.$tenant_id, $uid, json_encode($users_vip_info));
        }
    }else{
        $users_vip_info = json_decode($users_vip_info, true);
    }
    return $users_vip_info;
}

/* 获取用户正在审核的vip信息 */
function getUserVipCheckingInfo($uid){
    $redis = connectionRedis();
    $tenant_id = getTenantId();
    $config  = getConfigPub();
    $check_info = $redis->hGet('user_vip_checking_info_'.$tenant_id, $uid);
    if(!$check_info) {
        if ($config['vip_model'] == 1) {

        } else {
            $check_info = DI()->notorm->users_vip
                ->where('uid=? and status in(4)', intval($uid))
                ->order('grade desc')
                ->fetchOne();
        }
        if($check_info){
            $redis->hSet('user_vip_checking_info_'.$tenant_id, $uid, json_encode($check_info));
        }
    }else{
        $check_info = json_decode($check_info, true);
    }
    return $check_info;
}

/* 获取汇率列表 */
function getRateList($tenant_id){
    $list=getcaches('ratelist_'.$tenant_id);
    if(!$list){
        $list = DI()->notorm->rate->where(['tenant_id'=>intval($tenant_id)])->order('sort asc, id asc')->fetchAll();
        $list = array_column($list,null,'code');
        setcaches('ratelist_'.$tenant_id,$list);
    }
    return $list;
}

/* 获取代理详情 */
function getAgentInfo($uid){
    $info=getcaches('agentinfo_'.$uid);
    if(!$info){
        $info = DI()->notorm->users_agent->where('uid=?',intval($uid))->fetchOne();
        setcaches('agentinfo_'.$uid,$info);
    }
    return $info;
}

function time2string($second){



$hour = floor($second/3600);

$second = $second%3600;//除去整小时之后剩余的时间

$minute = floor($second/60);

$second = $second%60;//除去整分钟之后剩余的时间


//返回字符串

return $hour.':'.$minute.':'.$second;

}

function codemsg($code=''){
    $code = is_numeric($code) ? strval($code) : $code;
    $arr = include(dirname(__FILE__) .'/./code.php');
    if($code == '' || $code == null || $code == 'undefined'){
        return $arr;
    }
    return isset($arr[$code]) ? $arr[$code] : $code;
}

function country($code){
    $code = is_numeric($code) ? strval($code) : $code;
    $arr = include(dirname(__FILE__) .'/./country.php');
    return isset($arr[$code]) ? $arr[$code] : ["sc"=>"","code"=>'',"pinyin"=>"","en"=>"","locale"=>"","tc"=>""];
}

/*
 * $data array 是二维数组，如：[['val'=>'reg_withdrawable_coin','prob'=>5], ['val'=>'reg_withdrawable_coin2','prob'=>15], ['val'=>'reg_withdrawable_coin2','prob'=>80]]
 * 根据传过来的概率返回随机的值
 * */
function getProbVal($data = array()){
    $pool = array();
    $count = 0;
    foreach ($data as $key=>$item){
        for($i=0;$i<$item['prob'];$i++){
            array_push($pool,$item['val']);
            $count++;
        }
    }
    return $count > 0 ? $pool[rand(0,$count-1)] : '';
}

/* 获取代理返佣详情缓存 */
function getAgentRebateConf($tenant_id){
    $info=getcaches('agent_rebate_conf_'.$tenant_id);
    if(!$info){
        $info = DI()->notorm->agent_proportion->where('tenant_id=?',intval($tenant_id))->fetchAll();
        setcaches('agent_rebate_conf_'.$tenant_id,$info);
    }
    return $info;
}

/* 获取直播间人数初始默认值 */
function getLiveNumsDefault($stream){
    $configpri = getConfigPri();
    $redis = connectionRedis();
    $live_nums = $redis->get('LiveNumsDefault_'.$stream);

    if($live_nums === false){
        $live_nums = rand($configpri['live_nums_min'],$configpri['live_nums_max']);
    }
    $redis->set('LiveNumsDefault_'.$stream,$live_nums,60*60*24);
    return $live_nums;
}

/* 获取直播间主播初始粉丝人数默认值 */
function getLiveFansDefault($liveuid){
    $configpri = getConfigPri();
    $redis = connectionRedis();
    $live_nums = $redis->get('LiveFansDefault_'.$liveuid);

    if($live_nums === false){
        $live_nums = rand($configpri['live_fans_min'],$configpri['live_fans_max']);
    }
    $redis->set('LiveFansDefault_'.$liveuid,$live_nums,60*60*24);
    return $live_nums;
}

/* 获取直播间主播初始收入打赏默认值 */
function getLiveVotestotalDefault($liveuid){
    $configpri = getConfigPri();
    $redis = connectionRedis();
    $live_nums = $redis->get('LiveVotestotalDefault_'.$liveuid);

    if($live_nums === false){
        $live_nums = rand($configpri['live_votestotal_min'],$configpri['live_votestotal_max']);
    }
    $redis->set('LiveVotestotalDefault_'.$liveuid,$live_nums,60*60*24);
    return $live_nums;
}

// api日志
function logapi($ct='',$remark=''){
    try {
        if($ct=='' || empty($ct) || $ct==null){
            return 'ct 为空';
        }
        $ct = in_array(gettype($ct),['array','object']) ? json_encode($ct,JSON_UNESCAPED_UNICODE) : $ct;
        $remark = in_array(gettype($remark),['array','object']) ? json_encode($remark,JSON_UNESCAPED_UNICODE) : $remark;
        $service = DI()->request->get('service', 'Default.Index');
        $logdata = array(
            'service' => $service,
            'root_url' => get_protocal().'://'.$_SERVER['HTTP_HOST'],
            'uri' => $_SERVER['REQUEST_URI'],
            'ct' => $ct, // json_encode(array_merge($_GET,$_POST)),
            'remark' => $remark,
            'ip' => get_client_ip(0, true),
            'method' => $_SERVER['REQUEST_METHOD'],
            'tenant_id' => intval(getTenantId()),
            'ctime' => time(),
        );
        DI()->notorm->log_api->insert($logdata);
        if(CustRedis::getInstance()->get('log_api_day_delete') != '200'){
            $tmonth_start = date('Y-m-d 00:00:00',strtotime(date("Y-m",time())));
            $month3_start = date('Y-m-d 00:00:00',strtotime('-2 month',strtotime($tmonth_start)));
            DI()->notorm->log_api->where('ctime < '.strtotime($month3_start))->delete();
            CustRedis::getInstance()->set('log_api_day_delete','200',60*60*24);
        }
    }catch (Exception $ex) {
        return '错误: '.$ex->getMessage();
    }
    return true;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv=true) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/*
 * 获取直播间设置的游戏配置信息
 * */
function getLiveGameInfo($list = array(),$uid,$tenantId){
    $game_info = (object)[];
    foreach ($list as $vlaues){
        if($uid == $vlaues['uid'] ){
            $vlaues['time_start'] = intval($vlaues['time_start'])*1000;
            $vlaues['time_end'] = intval($vlaues['time_end'])*1000;
            $game_info = $vlaues;
            return $game_info;
        }elseif ($vlaues['is_template']==1 && $tenantId== $vlaues['tenant_id']){
            $vlaues['time_start'] = intval($vlaues['time_start'])*1000;
            $vlaues['time_end'] = intval($vlaues['time_end'])*1000;
            $game_info = $vlaues;
            return $game_info;
        }
    }
    return $game_info;
}
/*
 * 获取会员列表所有上级
 */
function getfather($puid){
    $data = array();
    $father = DI()->notorm->users_agent->where('uid=?',intval($puid))->select("one_uid")->order('id desc')->fetchone();
    if(!empty($father) && $father['one_uid']>0){
        array_push($data,$father['one_uid']);
    }

 /*   if($father>0){
        foreach ($father as $key=>$val){
            if($val['one_uid']>0){
                array_push($data,$val['one_uid']);
            }
            $father = getfather($val['one_uid']);
            if(is_array($father) && count($father)>0){
                $data = array_merge($data,$father);

            }

        }

    }*/
    return $data;
}

/* 获取进入直播间公告 */
function getEnterroomNotice($tenant_id){
    $redis = connectionRedis();
    $info=getcaches('enterroom_notice_'.$tenant_id);
    if(!$info){
        $info = DI()->notorm->language->where('type=1 and tenant_id=?',intval($tenant_id))->fetchOne();
        $redis->set('enterroom_notice_'.$tenant_id,json_encode($info),60*60*24*30);
    }
    return $info;
}

/* 获取语言类型 */
function getLanguageType($language_id){
    $arr = array(
        '101' => 'zh',
        '102' => 'en',
        '103' => 'vn',
        '104' => 'th',
        '105' => 'my',
        '106' => 'ind',
    );
    return isset($arr[$language_id]) ? $arr[$language_id] : '';
}
/* 关注人数 */
function getFollownums($uid)
{

    return DI()->notorm->users_attention->where("uid='{$uid}' ")->count();
}
/* 粉丝人数 */
function getFansnums($uid)
{
    return  DI()->notorm->users_attention->where(" touid='{$uid}'")->count();
}

/**
 * 发帖数量查询
 */
function barNumLimited($uid){
    $userVip = getUserVipInfo($uid);
    if (empty($userVip)){
        $level_name  = 'vip0';
    }else{
        $vipInfo  =DI()->notorm->vip
            ->where("id = '{$userVip['vip_id']}'")
            ->fetchOne();
        $level_name  = $vipInfo['name'];

    }
    $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = '{$level_name}'")->select('jurisdiction_id,bar_number,bar_slice_number')->fetchOne();
    return $level_name_jurisdiction;
}


function getUserField($uid,$field){
    $info=DI()->notorm->users
     ->select($field)
        ->where('id=? ',$uid)
        ->fetchOne();
    if ($info){
        return  $info[$field];
    }
    return '';

}

/*
 * 获取推拉流多线路列表缓存
 * */
function getPushpullList(){
    $list=getcaches('live_pushpull_list');
    if(!$list){
        $list = DI()->notorm->livepushpull->where(['status'=>1])->order('id desc')->fetchAll();
        $list = array_column($list,null,'id');
        setcaches('live_pushpull_list', $list, 60*60*24*7);
    }
    return $list;
}

/*
 * http 请求转到 go
 * */
function http_to_go($url,$postData=[],$header=[],$timeOut = 5){
    $postData = json_encode($postData);
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    // 执行后不直接打印出来
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    // 设置请求方式为post
    curl_setopt($ch,CURLOPT_POST,1);
    // post的变量
//    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
    // 请求头，可以传数组
    if(!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    }else{
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            )
        );
    }
    curl_setopt($ch, CURLOPT_HEADER,0);
    curl_setopt($ch,CURLOPT_TIMEOUT,$timeOut);
    // 跳过证书检查
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    // 不从证书中检查SSL加密算法是否存在
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

    $output=curl_exec($ch);
    curl_close($ch);

    $output = is_json($output) ? json_decode($output,true) : $output;
    return $output;
}

/*
 * 获取贵族等级列表缓存
 * */
function getNobleList($tenant_id, $level = null){
    $list = getcaches('live_noble_list_'.$tenant_id);
    if(!$list){
        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl().goAdminRouter().'/live_noble/get_noble_level_list_all';
            $http_post_map = [
                'tenant_id' => intval(getTenantId()),
            ];
            $http_post_res = http_post($url, $http_post_map);
            $list = $http_post_res['Data'];
        }else{
            $list = DI()->notorm->noble->where(['tenant_id'=>intval($tenant_id)])->order('level asc')->fetchAll();
        }
        $list = count($list) > 0 ? array_column($list,null,'level') : [];
        foreach ($list as $key=>$val) {
            $list[$key]['medal'] = get_upload_path($val['medal']);
            $list[$key]['knighthoodcard'] = get_upload_path($val['knighthoodcard']);
            $list[$key]['special_effect_swf'] = get_upload_path($val['special_effect_swf']);
            $list[$key]['avatar_frame'] = get_upload_path($val['avatar_frame']);
            $list[$key]['exclu_car_nobleicon'] = get_upload_path($val['exclu_car_nobleicon']);
        }
        setcaches('live_noble_list_'.$tenant_id, $list, 60*60*24*7);
    }
    if($level){
        return isset($list[$level]) ? $list[$level] : [];
    }
    return $list;
}

/*
 * 清除用户贵族缓存信息
 * */
function delUserNoble($uid){
    delcache('user_noble_'.$uid);
}

/*
 * 获取用户贵族缓存信息
 * */
function getUserNobleInfo($uid){
    if(enableGolangReplacePhp() === true){
        // golang替换
        $game_tenant_id = getGameTenantId();
        $whichTenant= whichTenat($game_tenant_id);
        $third_tenant_name = $whichTenant==1 ? "meibo" : "alone";
        $param = array(
            'service' => 'Live.GetNobleUserInfo',
            'game_tenant_id' => 'GetNobleUserInfo',
            'language_id' => '101',
            'uid' => intval($uid),
        );
        $http_post_res = http_post(goAdminUrl().goAppRouter(),$param, ['third_tenant_name:'.$third_tenant_name, 'third_tenant_id:'.intval($game_tenant_id)]);
        $info = isset($http_post_res['data']) && isset($http_post_res['data']['info']) ? $http_post_res['data']['info'] : [];
    }else {
        $info = getcaches('user_noble_'.$uid);
        if (!$info) {
            $info = DI()->notorm->users_noble->where(['uid' => intval($uid)])->fetchOne();
            if (!empty($info)) {
                setcaches('user_noble_' . $uid, $info, 60 * 60 * 24 * 7);
            }
        }
    }
    return $info;
}

/*
 * 获取用户贵族信息
 * */
function getUserNoble($uid){
    $tenant_id = getTenantId();
    $usernoble_info = getUserNobleInfo($uid);
    $info = $usernoble_info && isset($usernoble_info['level']) && $usernoble_info['etime'] > time() ? getNobleList($tenant_id,$usernoble_info['level']) : [];
    $carlist = get_carlist(getTenantId());
    $carlist = count($carlist) > 0 ? array_column($carlist,null,'id') : [];

    if(!empty($info)){
        $info['stime'] = $usernoble_info['stime'];
        $info['etime'] = $usernoble_info['etime'];
        $info['stime_format'] = date('Y-m-d H:i:s',$info['stime']);
        $info['etime_format'] = date('Y-m-d H:i:s',$info['etime']);

        $car_info = isset($carlist[$info['car_id']]) ? $carlist[$info['car_id']] : [];
        $info['exclu_car_name'] = isset($car_info['name']) ? $car_info['name'] : '';
        $info['exclu_car_bagicon'] = isset($car_info['thumb']) ? $car_info['thumb'] : '';
        $info['exclu_car_swf'] = isset($car_info['swf']) ? $car_info['swf'] : '';
        $info['exclu_car_swftime'] = isset($car_info['swftime']) ? $car_info['swftime'] : '';
        $info['exclu_car_words'] = isset($car_info['words']) ? $car_info['words'] : '';

        unset($info['id']);
        unset($info['act_uid']);
        unset($info['tenant_id']);
        unset($info['ctime']);
        unset($info['mtime']);
    }
    return !empty($info) ? $info : (object)[];
}

/*
 * 移除短视频列表元素缓存
 * */
function delShortVideoListCache($tenant_id, $id){
    $redis = connectionRedis();
    $tenantList = getTenantList();
    if(count($tenantList) > 0){
        foreach ($tenantList as $key=>$val){
            $cachekey = 'short_video_list_'.$val['id'];
            $res = $redis->hDel($cachekey, $id);
        }
    }else{
        $cachekey = 'short_video_list_'.$tenant_id;
        $res = $redis->hDel($cachekey, $id);
    }
    return isset($res) ? $res : false;
}

/*
 * 根据租户id获取短视频列表
 * */
function getShortVideoList($tenant_id){
    $redis = connectionRedis();
    $cachekey = 'short_video_list_'.$tenant_id;
    $len = $redis->hLen($cachekey);
    if($len > 0){
        $list = $redis->hGetAll($cachekey);
        foreach ($list as $key => $val){
            $list[$key] = json_decode($val,true);
        }
    }else{
        $list = DI()->notorm->video->select('*')->where('tenant_id = ? and status = 2 and origin = 1', intval($tenant_id))->fetchAll();
        foreach ($list as $key=>$val){
            $redis->hSet($cachekey,$val['uid'],json_encode($val));
        }
    }
    return $list;
}

/*
 * 获取贵族配置
 * */
function getNobleSetting($tenant_id){
    $info = getcaches('live_noble_setting_'.$tenant_id);
    if(!$info){
        $info = DI()->notorm->noble_setting->select('status,details')->where(['tenant_id'=>intval($tenant_id)])->fetchOne();
        setcaches('live_noble_setting_'.$tenant_id,$info,60*60*24*30);
    }
    return $info;
}

/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
        return true;
    }
    return false;
}

/*
 * 获取网络请求协议
 * */
function get_protocal(){
    return is_ssl() ? 'https' : 'http';
}

function  get_date_time($time){
    $nowTime = time();
    if ($nowTime - $time <= 300 ){
        $timeString = '刚刚';
    }else if($nowTime - $time > 300  && $nowTime - $time   <= 600 ){
        $timeString = '5分钟前';
    }elseif($nowTime - $time > 600 && $nowTime - $time   <= 1800 ){
        $timeString = '10分钟前';
    }elseif($nowTime - $time > 1800 && $nowTime - $time   <= 3600 ){
        $timeString = '30分钟前';
    }elseif($nowTime - $time > 3600 && $nowTime - $time   <= 86400 ){
        $time =  floor(( $nowTime- $time)/3600);
        $timeString = $time.'小时前';
    }elseif($nowTime - $time > 86400 && $nowTime - $time   <= 172800){
        $time =  date('H:i');
        $todayTime =  strtotime('Y-m-d',$nowTime);
        $sub =  $nowTime - $todayTime;
        if ($sub - $time>86400){
            $timeString = '前天'.$time;
        }else{
            $timeString = '昨天'.$time;
        }
    }else{
        $timeString =  date('m-d',$time);
    }
    return $timeString;

}

/*
 * 获取文件存储缓存
 * */
function getStorage(){
    $info = getcaches('storage_info');
    if(!$info){
        $data = DI()->notorm->options->select('*')->where(['option_name'=>'cmf_settings'])->fetchOne();
        $info = json_decode($data['option_value'],true);
        setcaches('storage_info',$info);
    }
    return $info;
}

/* 获取租户列表 */
function getTenantList($tenant_id = null) {
    $key = 'tenant_list_all';
    $list = false;//getcaches($key);
    if (!$list || empty($list)) {
        if(enableGolangReplacePhp() === true){
            $url = goAdminUrl().goAdminRouter().'/tenant/get_tenant_list_all';
            $http_post_map = [
                'third_tenant_name' => '',
            ];
            $http_post_res = http_post($url, $http_post_map);
            $list = $http_post_res['Data'];
        }else {
            $list = DI()->notorm->tenant->where('id > 0')->fetchAll();
        }
        setcaches($key, $list);
    }
    if($tenant_id){
        $list = array_column($list,null,'id');
        return isset($list[$tenant_id]) ? $list[$tenant_id] : [];
    }
    return 	$list;
}

/*
 * 设置直播列表缓存
 * */
function setUserLiveListCache($uid, $type = '', $num = 0){
    $num += 1;
    if($num > 5){
        return false;
    }
    $redis = connectionRedis();
    $info = DI()->notorm->users_live->select('*')->where(['uid'=>intval($uid),'islive'=>1])->fetchOne();
    if($info){
        if($info['isshare'] == 1){
            $tenantList = getTenantList();
            foreach ($tenantList as $key=>$val){
                $cachekey = 'user_live_list_'.$val['id'];
                $res = $redis->hSet($cachekey,$uid,json_encode($info));
            }
        }else{
            $cachekey = 'user_live_list_'.$info['tenant_id'];
            $res = $redis->hSet($cachekey,$uid,json_encode($info));
        }
        return isset($res) ? $res : false;
    }else{
        if($type != 'create'){
            $user_info = getUserInfo($uid);
            delUserLiveListCache($user_info['tenant_id'], $uid);
            return false;
        }

        $usleep_time = intval(0.2 * 1000000);
        usleep($usleep_time); // 延迟0.2秒
        $result = setUserLiveListCache($uid, $type, $num); // 递归5次，防止因为从库同步主库数据不及时导致没有查到数据
        if($result !== false){
            return $result;
        }
        if($num == 1){
            $user_info = getUserInfo($uid);
            delUserLiveListCache($user_info['tenant_id'], $uid);
        }
        return false;
    }
}

/*
 * 移除直播列表元素缓存
 * */
function delUserLiveListCache($tenant_id, $uid){
    $redis = connectionRedis();
    $tenantList = getTenantList();
    if(count($tenantList) > 0){
        foreach ($tenantList as $key=>$val){
            $cachekey = 'user_live_list_'.$val['id'];
            $res = $redis->hDel($cachekey, $uid);
        }
    }else{
        $cachekey = 'user_live_list_'.$tenant_id;
        $res = $redis->hDel($cachekey, $uid);
    }
    return isset($res) ? $res : false;
}

/*
 * 根据租户id获取直播列表
 * */
function getUserLiveList($tenant_id){
    $redis = connectionRedis();
    $cachekey = 'user_live_list_'.$tenant_id;
    $len = $redis->hLen($cachekey);
    if($len > 0){
        $list = $redis->hGetAll($cachekey);
        foreach ($list as $key => $val){
            $list[$key] = json_decode($val,true);
        }
    }else{
        $list = DI()->notorm->users_live->select('*')->where('islive = 1 and (tenant_id = ? or isshare = 1)',intval($tenant_id))->fetchAll();
        foreach ($list as $key=>$val){
            $redis->hSet($cachekey,$val['uid'],json_encode($val));
        }
    }
    return $list;
}

/*
 * 根据租户id获取直播列表
 * */
function getUserLiveInfo($tenant_id, $liveuid){
    $cachekey = 'user_live_list_'.$tenant_id;
    $info = CustRedis::getInstance()->hGet($cachekey, $liveuid);
    if(!$info){
        $info = DI()->notorm->users_live->select('*')->where('uid = ? and islive = 1 and (tenant_id = ? or isshare = 1)', intval($liveuid), intval($tenant_id))->fetchOne();
        if($info){
            CustRedis::getInstance()->hSet($cachekey, $liveuid, json_encode($info));
        }
    }else{
        $info = json_decode($info, true);
    }
    return $info;
}

function real_ip()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    return $ip;
}

/*
 * http post 请求
 * */
function http_post($url,$postData=[],$header=[],$timeOut = 15){
    if(strpos($url,'http') !== 0){
       return 'url 错误';
    }

    if(CustRedis::getInstance()->get('logapi_reqeuest_status') == 1){
        logapi([$url, $postData, $header],'【http_post 请求日志】');  // 接口日志记录
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
    if(!empty($header)){
        curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($curl);
    curl_close($curl);

    $output = is_json($output) ? json_decode($output,true) : $output;
    return $output;
}

/*
 * http get 请求
 * */
function http_get($url, $header=[], $timeOut = 15){
    if(strpos($url,'http') !== 0){
        return 'url 错误';
    }

    if(CustRedis::getInstance()->get('logapi_reqeuest_status') == 1){
        logapi([$url, $header, $header],'【http_get 请求日志】');  // 接口日志记录
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
    if(!empty($header)){
        curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($curl);
    curl_close($curl);

    $output = is_json($output) ? json_decode($output,true) : $output;
    return $output;
}

 function curPost($url,$data)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);//不抓取头部信息。只返回数据
    curl_setopt($curl, CURLOPT_TIMEOUT,1000);//超时设置
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//1表示不返回bool值
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));//重点
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    return $response;
}
/*
 * 获取kvconfig val redis缓存
 * */
function getKvconfigVal($tag, $key){
    $redis = connectionRedis();
    $val = $redis->hGet('kvconfig',$tag.'.'.$key);
    if(!$val){
        $info = DI()->notorm->kvconfig->select('*')->where(['tag'=>intval($tag), '`key`'=>trim($key)])->fetchOne();
        if($info){
            $val = $info['val'];
            $redis->hSet('kvconfig',$tag.'.'.$key, $val);
        }
    }
    if(!$val){
        $val = '';
    }
    return $val;
}

/*
 * 获取系统配置
 * */
function getSystemConf($key){
    return getKvconfigVal(1, $key);
}

/*
 * golang后台admin接口路径
 * */
function goAdminRouter(){
    return '/admin/v1';
}

/*
 * golang前端app接口路径
 * */
function goAppRouter(){
    return '/app/v1/public/index';
}

/*
 * golang后台admin接口地址
 * */
function goAdminUrl(){
    return trim(getSystemConf('go_admin_url'), '/');
}

/*
 * golang api app接口地址
 * */
function goAppUrl(){
    return trim(getSystemConf('go_app_url'), '/');
}

/*
 * 是否启用golang替换php代码（1.启用，0.不启用）
 * */
function enableGolangReplacePhp(){
    $enable_golang_replace_php = getSystemConf('enable_golang_replace_php');
    $go_admin_url = goAppUrl();
    if($enable_golang_replace_php == 1 && $go_admin_url){
        return true;
    }else{
        return false;
    }
}

/*
 * 请求golang接口
 * */
function goAppApiRqquest(){
    $game_tenant_id = getGameTenantId();
    $whichTenant= whichTenat($game_tenant_id);
    $third_tenant_name = $whichTenant==1 ? "meibo" : "alone";
    foreach ($_GET as $key=>$val){
        $_GET[$key] = urldecode($val);
    }
    $http_post_res = http_post(goAppUrl().goAppRouter(),array_merge($_GET,$_POST), ['ThirdTenantName:'.$third_tenant_name, 'ThirdTenantId:'.intval($game_tenant_id)]);
    return $http_post_res;
}

/*
 * 获取用户贵族缓存信息
 * */
function getNoblevirtual($uid){
    $info = getcaches('user_noble_virtual_'.$uid);

    if(!$info){
        $tenant_id = getTenantId();
        $list = getNobleList($tenant_id);
        $time_list = rand(1,count($list));

        $info =  $list[$time_list];
        setcaches('user_noble_virtual_'.$uid,$info,86400);
    }
    return $info;
}
/*
 * 获取机器人信息
 * */
function getRobot($touid,$type,$voteuid){

    $robotuserInfo = getcaches('user_robot_'.$touid.'_'.$type);
    logapi(['updateResult'=>$robotuserInfo],'【返回机器人榜单信息】');  // 接口日志记录
    if(!$robotuserInfo){
        $count = DI()->notorm->users->where("user_type = 3")->count();
        $start_user = rand(0,$count-6);


        if($type=='day'){
            $res  = getRand(6,intval($voteuid/4),1,intval($voteuid/21));
        }
        if($type=='week'){
            $res  = getRand(6,intval($voteuid/3),intval($voteuid/20),intval($voteuid/16));
        }
        if($type=='month') {
            $res  = getRand(6,intval($voteuid/2),intval($voteuid/15),intval($voteuid/10));
        }
        if($type=='total'){
            $res  = getRand(6,$voteuid,1,$voteuid);
        }


        $robotuserInfo =DI()->notorm->users
            ->select('consumption as totalcoin,id as uid')
            ->where("user_type = 3")
            ->limit($start_user,6)
            ->fetchAll();
        foreach ($robotuserInfo as $key => $value){
            $robotuserInfo[$key]['totalcoin'] = $res[$key];
        }

        setcaches('user_robot_'.$touid.'_'.$type,$robotuserInfo,86400);
    }
    return $robotuserInfo;

}

/*
 * 获取红包金额信息
 * */
function getRedinfo(){
    //获取批次
    $redis = connectRedis();
    $tenant_list = getTenantList();
    $info = array();
    foreach ($tenant_list as $tenant_key=>$tenant_val){
        $red_setting_list = getcaches('red_setting_list_'.$tenant_val['id']);
        if(!$red_setting_list){
            continue;
        }
        foreach ($red_setting_list as $key=>$value){
            $time_now = time();
            $timesend = date('YmdH', time()+2*60); // 提前2分钟生成
            $red_send_key = 'red_send_'.$timesend.'_'.$value['id'];
            $red_sendmark_key = 'red_sendmark_'.$timesend.'_'.$value['id'];

            $exist_red_sendmark = getcaches($red_sendmark_key);
            $info[$red_sendmark_key] = $exist_red_sendmark;
            if(!$exist_red_sendmark) {
                // 计算基准设置的红包金额
                $redis->del($red_send_key);
                $redis->del($red_sendmark_key);
                if ($value['red_total'] != '0' && $value['red_num'] != '0' && $value['money_max'] != '0' && $value['effect_time_start'] <= $time_now && $time_now <= $value['effect_time_end']) {
                    $red_send = _getRandomNumberArray($value['red_num'], $value['red_total'], $value['money_min'], $value['money_max']);
                    foreach ($red_send as $k => $money) {
                        $redis->lPush($red_send_key, $money);
                    }
                    $redis->expire($red_send_key, 60 * 60 * 24);
                    setcaches($red_sendmark_key, 1, 60 * 60 * 24);
                }
            }

            // 计算不同等级设置的红包金额
            $vip_conf = json_decode($value['vip_conf'], true);
            $vip_conf = is_array($vip_conf) ? $vip_conf : [];
            foreach ($vip_conf as $k => $v) {
                $red_send_k = 'red_send_' . $timesend . '_' . $value['id'] . '_' . trim($k, 'vip_grade_');
                $red_sendmark_k = 'red_sendmark_' . $timesend . '_' . $value['id'] . '_' . trim($k, 'vip_grade_');

                $exist_ed_sendmark_k = getcaches($red_sendmark_k);
                $info[$red_sendmark_k] = $exist_ed_sendmark_k;
                if($exist_ed_sendmark_k){
                    continue;
                }
                $redis->del($red_send_k);
                $redis->del($red_sendmark_k);
                if ($v['red_total'] == '0' || $v['red_num'] == '0' || $v['money_max'] == '0') {
                    continue;
                }
                if($v['red_total'] == '0' || $v['red_num'] == '0' || $v['money_max'] == '0'){
                    continue;
                }
                $vip_grade_red_send = _getRandomNumberArray($v['red_num'], $v['red_total'], $v['money_min'], $v['money_max']);
                foreach ($vip_grade_red_send as $vip_grade_red_send_k => $vip_grade_red_send_money) {
                    $redis->lPush($red_send_k, $vip_grade_red_send_money);
                }
                $redis->expire($red_send_k, 60 * 60 * 24);
                setcaches($red_sendmark_k, 1, 60 * 60 * 24);
            }
        }
    }

    return $info;
}

 function _getRandomNumberArray($times,$total , $min, $max)
{
    $data = array();
    if ($min * $times > $total) {
        return array();
    }
    if ($max * $times < $total) {
        return array();
    }
    while ($times >= 1) {
        $times--;
        $kmix = max($min, $total - $times * $max);
        $kmax = min($max, $total - $times * $min);
        $kAvg = $total / ($times + 1);
        //获取最大值和最小值的距离之间的最小值
        $kDis = min($kAvg - $kmix, $kmax - $kAvg);
        //获取0到1之间的随机数与距离最小值相乘得出浮动区间，这使得浮动区间不会超出范围
        $r = ((float)(rand(1, 10000) / 10000) - 0.5) * $kDis * 2;
        $k = round($kAvg + $r);
        $total -= $k;
        $data[] = abs($k);
    }
    return $data;
}
/*
 * 指定总数，随机生成 指定个数 的方法
 */
function getRand($count,$sum,$mix,$max){
    ini_set('memory_limit', "1024M");
    $i=1;
    while ($i <= $count) {

        $tmp[] = mt_rand($mix,$max);
        $i++;
    }
    $k = $sum / array_sum($tmp);
    $res = [];
    foreach ($tmp as $v) {
        $res[] =  (int) ($v*$k);
    }
    $assoc = $sum - array_sum($res);
    $last = array_pop($res);
    $res[] = $last + $assoc;
    while (max($res) > $max) {
        $tmax = max($res);
        $key = array_search($tmax, $res);
        if($key !== false){
            $res[$key] = mt_rand($mix,$max);
            $k = array_search( min($res), $res);
            $res[$k] = $tmax - $res[$key] + min($res);
        }
    }
    return $res;
}

/*
 * 清除用户所在的群聊房间room_ids缓存
 * */
function delChatRoomIdsCahche($tenant_id, $uid){
    delcache("chat_room_ids_".$tenant_id.$uid);
    return true;
}

/*
 * 获取用户所在的群聊房间room_ids
 * */
function getChatRoomIds($tenant_id, $uid){
    $key = "chat_room_ids_".$tenant_id.$uid;
    $room_ids = getcaches($key);
    if(!$room_ids){
        $self_list = DI()->notorm->users_chatroom->select("id as room_id")->where('tenant_id=? and roomtype=0 and uid=?', $tenant_id, $uid)->order('id asc')->fetchAll();
        $join_list = DI()->notorm->users_chatroom_friends->select("room_id")->where('tenant_id=? and roomtype=0 and status in(0,1) and sub_uid=?', $tenant_id, $uid)->fetchAll();
        $list = array_merge($self_list, $join_list);
        $room_ids = count($list) > 0 ? array_keys(array_column($list, null, 'room_id')) : [];
        if($room_ids){
            setcaches($key,$room_ids,7*24*60*60); // 缓存7天
        }
    }
    return $room_ids;
}

/*
 * 清除用户所在的私聊房间room_ids缓存
 * */
function delChatPrivateRoomIdsCahche($tenant_id, $uid){
    delcache("private_room_ids_".$tenant_id.$uid);
    return true;
}

/*
 * 获取用户所在的私聊房间room_ids
 * */
function getChatPrivateRoomIds($tenant_id, $uid){
    $key = "private_room_ids_".$tenant_id.$uid;
    $room_ids = getcaches($key);
    if(!$room_ids){
        $self_list = DI()->notorm->users_chatroom->select("id as room_id")->where('tenant_id=? and roomtype=1 and uid=?', intval($tenant_id), intval($uid))->order('id asc')->fetchAll();
        $join_list = DI()->notorm->users_chatroom_friends->select("room_id")->where('tenant_id=? and roomtype=1 and sub_uid=?', intval($tenant_id), intval($uid))->fetchAll();
        $list = array_merge($self_list, $join_list);
        $room_ids = count($list) > 0 ? array_keys(array_column($list, null, 'room_id')) : [];
        if($room_ids){
            setcaches($key,$room_ids,7*24*60*60); // 缓存7天
        }
    }
    return $room_ids;
}

/*
 * 清除聊天室成员uids缓存
 * */
function delChatRoomUidsCahche($tenant_id, $room_id){
    delcache("chat_room_uids_".$tenant_id.$room_id);
    return true;
}

/*
 * 获取聊天室成员uids
 * */
function getChatRoomUids($tenant_id, $room_id){
    $key = "chat_room_uids_".$tenant_id.$room_id;
    $uids = getcaches($key);
    if(!$uids){
        $uid_list = DI()->notorm->users_chatroom_friends->select("sub_uid as uid")->where('tenant_id=? and room_id=? and status in(0,1)', intval($tenant_id), intval($room_id))->fetchAll();
        $uids = count($uid_list) > 0 ? array_keys(array_column($uid_list, null, 'uid')) : [];
        if($uids){
            setcaches($key,$uids,7*24*60*60); // 缓存7天
        }
    }
    return $uids;
}

/*
* 获取首充豪礼用户坐骑
* */
function get_user_chargecar($uid){
    $list=getcaches('user_carcharge_'.$uid);
    if(!$list){
        $list=DI()->notorm->users_chargegift
            ->select("*")
            ->where('uid=? and tenant_id=? ',$uid,getTenantId())
            ->fetchOne();
        if($list){
            setcaches('user_carcharge_'.$uid,$list,24*60*60);
        }
    }
    return $list;
}

/* 随机数 */
function randUserName($length = 6 , $numeric = 0) {
    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
    if($numeric) {
        $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
    }
    $tenant_id = getTenantId();
    $userInfo = DI()->notorm->users
        ->where("tenant_id= '{$tenant_id}' and user_login = '{$hash}'")
        ->fetchOne();

    if ($userInfo){
        randUserName();
    }else{
        return $hash;
    }


}
function geturlType(){
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    return $http_type;

}

function activity_reward($uid,$recharge_num,$coin,$tenant_id){
    if($recharge_num != 0 || $coin<=0){ // 非首次充值,不进行处理 | 金币小于等于0,不进行处理
        return false;
    }
    $agentinfo = getAgentInfo($uid);
    // type 类型：1 首充活动，2 分享活动
    $fc_act_con_list = DI()->notorm->activity_config ->where("type=1 and tenant_id = '{$tenant_id}' ")->order('sort_num asc')->feachAll();
    if(count($fc_act_con_list) > 0){
        $reward = $watnum = $wattime = 0;
        foreach ($fc_act_con_list as $key=>$val){
            if($val['min'] <= $coin){
                $reward = $val['reward'];
                $watnum = $val['watnum'];
                $wattime = $val['wattime'];
            }
        }
        $config=getConfigPub();

        // 更新用户数据
        if ($config['first_charge_award_amount_type'] ==1){
            if($reward>0 || $watnum>0 || $wattime>0) {
                DI()->notorm->users ->where(['id' => intval($uid)])->update([
                    'coin' => array('exp', 'coin+' . floatval($reward)),
                    'watch_num' => array('exp', 'watch_num+' . intval($watnum)),
                    'watch_time' => array('exp', 'watch_time+' . intval($wattime)),
                ]);
            }
            $actionType  = 'income';
        }else{
            if($reward>0 || $watnum>0 || $wattime>0) {
                DI()->notorm->users ->where(['id' => intval($uid)])->update([
                    'nowithdrawable_coin' => array('exp', 'nowithdrawable_coin+' . floatval($reward)),
                    'watch_num' => array('exp', 'watch_num+' . intval($watnum)),
                    'watch_time' => array('exp', 'watch_time+' . intval($wattime)),
                ]);
                $actionType  = 'income_nowithdraw';
                $redis = connectRedis();
                $keytime = time();
                $redis->lPush($uid . '_reward_time', $keytime);// 存用户 时间数据key
                $amount = $redis->get($uid . '_' . $keytime.'_reward');
                $totalAmount = bcadd($reward, $amount, 2);
                $redis->set($uid . '_' . $keytime.'_reward', $totalAmount);// 存佣金
                $expireTime = time() + $config['withdrawal_time'] * 86400;
                /** 86400*/
                $redis->expireAt($uid . '_' . $keytime.'_reward', $expireTime);// 设置过去时间
            }

        }

        // 可提现金币变动记录
        if($reward>0){
            DI()->notorm->users_coinrecord ->insert([
                'type' => $actionType,
                'uid' => intval($uid),
                'addtime' => time(),
                'tenant_id' => intval($tenant_id),
                'action' => 'firstrecharge',
                'totalcoin' => floatval($reward),
            ]);
        }
        // 活动赠送明细记录
        if($watnum>0 || $wattime>0){
            DI()->notorm->activity_reward_log ->insert([
                'type' => 1,
                'watnum' => intval($watnum),
                'wattime' => intval($wattime),
                'uid' => intval($uid),
                'user_login' => $agentinfo['user_login'],
                'user_type' => $agentinfo['user_type'],
                'reward' => floatval($reward),
                'tenant_id' => intval($tenant_id),
                'ctime' =>time(),
            ]);
        }
    }

    $parent_uid = isset($agentinfo['one_uid']) ? $agentinfo['one_uid'] : 0;
    $parent_info = $parent_uid ? getUserInfo($parent_uid): array();
    $child_uid = $parent_uid ? DI()->notorm->users_agent->where(['one_uid'=>intval($parent_uid)])->select('uid')->feachAll() : array();
    $child_uids = count($child_uid)>0 ? array_keys(array_column($child_uid,null,'uid')) : array();
    $share_act_con_list = DI()->notorm->activity_config->where("type=2  and tenant_id = '{$tenant_id}' ")->order('is_over asc,per_num asc')->feachAll()();

    if(isset($parent_info['id']) && count($child_uids) > 0 && count($share_act_con_list)>0){
        $reward_child_count = DI()->notorm->users->where([
            'id'=>['in',$child_uids],
            'firstrecharge_coin'=>['egt',floatval($share_act_con_list[0]['recom_frmin'])]
        ])->count();

        $tenant_id = $parent_info['tenant_id'];
        $reward = $watnum = $wattime = 0;
        foreach ($share_act_con_list as $key=>$val){
            if($val['recom_frmin'] <= $coin && $reward_child_count == $val['per_num']){
                $reward = $val['reward'];
                $watnum = $val['watnum'];
                $wattime = $val['wattime'];
            }
        }
        if ($config['share_award_amount_type'] ==1){ // 分享金额可提现
            if($reward>0 || $watnum>0 || $wattime>0) {
                DI()->notorm->users->where(['id'=>intval($parent_uid)])->update([
                    'coin' => array('exp','coin+'.$reward),
                    'watch_num' => array('exp','watch_num+'.$watnum),
                    'watch_time' => array('exp','watch_time+'.$wattime),
                ]);
                $actionType  = 'income';
            }
        }else{// 分享不金额可提现
            if($reward>0 || $watnum>0 || $wattime>0) {
                DI()->notorm->users->where(['id' => intval($parent_uid)])->update([
                    'nowithdrawable_coin' => array('exp', 'nowithdrawable_coin+' . $reward),
                    'watch_num' => array('exp', 'watch_num+' . $watnum),
                    'watch_time' => array('exp', 'watch_time+' . $wattime),
                ]);
                $actionType  = 'income_nowithdraw';
                $redis = connectRedis();
                $keytime = time();
                $redis->lPush($parent_uid . '_reward_time', $keytime);// 存用户 时间数据key
                $amount = $redis->get($parent_uid . '_' . $keytime.'_reward');
                $totalAmount = bcadd($reward, $amount, 2);
                $redis->set($parent_uid . '_' . $keytime.'_reward', $totalAmount);// 存佣金
                $expireTime = time() + $config['withdrawal_time'] * 86400;
                /** 86400*/
                $redis->expireAt($parent_uid . '_' . $keytime.'_reward', $expireTime);// 设置过去时间
                delUserInfoCache($parent_uid);
            }
        }

        // 更新用户数据

        // 可提现金币变动记录
        if($reward>0){
            DI()->notorm->users_coinrecord  ->insert([
                'type' => $actionType,
                'uid' => intval($parent_uid),
                'addtime' => time(),
                'tenant_id' => intval($tenant_id),
                'action' => 'share_firstrecharge',
                'totalcoin' => floatval($reward),
            ]);
        }
        // 活动赠送明细记录
        if($watnum>0 || $wattime>0) {
            DI()->notorm->activity_reward_log  ->insert([
                'type' => 2,
                'watnum' => intval($watnum),
                'wattime' => intval($wattime),
                'uid' => intval($parent_uid),
                'user_login' => $parent_info['user_login'],
                'user_type' => $parent_info['user_type'],
                'reward' => floatval($reward),
                'tenant_id' => intval($tenant_id),
                'ctime' => time(),
            ]);
        }
    }
    return true;
}
    /**
     *   同步数据到java端
     */
    function senduserNft($nftdate){
        try {
            $tenantInfo=getTenantInfo(getTenantId());
            $url=$tenantInfo['balance_nft_url'];
            $client = new Client();
            $paramArray = array(
                'zb_tid' => $nftdate['zb_tid'],
                'zb_id' => $nftdate['zb_id'],
                'cust_name'=>$nftdate['user_login'],
                'nick_name'=>$nftdate['user_nicename'],
                'cust_name' => 'desmond001',
                'nick_name' => 'desmond001',
                'domain_addr' => 'http://bqipaim.lg0808.com',
                'client_id' => 'react_client',
                'chan_code' => '',
            );
            //logapi(['params' => $paramArray], '【NFT接口返回参数】');
            $res = $client->request('POST', $url, ['timeout' => 20, 'form_params' => $paramArray]);
            //logapi(['res' => $res], '【NFT接口返回结果】');
            $code = $res->getStatusCode();
            if ($code != 200) {
                logapi(['code' => $code], '【NFT接口返回code】');
                //请求异常
                $rs['msg'] = 'NFT接口请求异常';
                $rs['code'] = $code;
                return $rs;
            } else {
                //请求成功
                $rs['msg'] = 'NFT接口请求成功';
                $rs['code'] = $code;
                return $rs;
            }
        }catch (Exception $ex){
            logapi(['url'=>$url,'params'=>$paramArray],'【NFT接口请求异常】'.$ex->getMessage());
            //请求异常
            $rs['msg']='NFT接口请求异常';
            $rs['code']=500;
            return $rs;
        }
    }

/*
* 获取置顶的视频列表
* */
function getTopVideoList($tenant_id){
    $list = getcaches('top_video_list_'.$tenant_id);
    if(!$list){
        $list = DI()->notorm->video
            ->select("*")
            ->where('`tenant_id` = ? and `status` = 2 and `top` = 1', intval($tenant_id))
            ->fetchAll();
        if($list){
            setcaches('top_video_list_'.$tenant_id, $list,60*60*24*7);
        }
    }
    $list = is_array($list) ? $list : array();
    shuffle($list);
    return $list;
}

/**
 * @param $uid
 * @param $type 奖励类型  1任务奖励、2签到奖励、3邀请好友注册奖励、4好友消费奖励、5转盘奖励
 * @param $number 数量
 * @param $data_type  1  金额  2  碎片
 * $award_name   奖品名称
 * $exchange_number  奖品完成数量
 */
function addAward($uid,$type,$number,$data_type,$award_name ='',$exchange_number = 0){
    if ($data_type == 2){
        $awardInfo  = DI()->notorm->award_log->where(['award_name'=>$award_name,'data_type'=>$data_type])->order('addtime desc')->fetchOne();
        if ($awardInfo){
            if ( $awardInfo['completion_value']> $awardInfo['back_balance']){
                DI()->notorm->award_log->where(['id'=>$awardInfo['id'] ])
                    ->update(
                        array('back_balance' => new NotORM_Literal("back_balance + {$number} ")),
                        array('original_balance' => $number),
                        array('addtime'=> time())
                        );
            }
            return $awardInfo['id'];
        }else{
            $awardLog = [
                'uid' => $uid,
                'type' => $type ,// 邀请好友注册奖励
                'amount' => $number,
                'original_balance' =>  0,
                'back_balance' => $number,
                'completion_value'=> $exchange_number,
                'addtime' => time(),
                'status' => 1,
                'data_type' => $data_type,
                'award_name' => $award_name,
            ];
        }

    }else if ($data_type == 1){
        $awardLog = [
            'uid' => $uid,
            'type' => $type ,
            'amount' => $number,
            'original_balance' =>  getUserInfo($uid)['coin'],
            'back_balance' =>  bcadd(getUserInfo($uid)['coin'],$number ,2),
            'completion_value'=> 0,
            'addtime' => time(),
            'status' => 1,
            'data_type' => $data_type,
            'award_name' => $award_name,
        ];

    }else if ($data_type == 3){
        $awardLog = [
            'uid' => $uid,
            'type' => $type ,
            'amount' => $number,
            'original_balance' =>  getUserInfo($uid)['turntable_times'],
            'back_balance' =>  bcadd(getUserInfo($uid)['turntable_times'],$number ,2),
            'completion_value'=> 0,
            'addtime' => time(),
            'status' => 1,
            'data_type' => $data_type,
            'award_name' => $award_name,
        ];

    }
    return DI()->notorm->award_log->insert($awardLog);
}

/**
 * 用户完成任务提交 修改任务状态为待提交
 * @param $uid 用户ID
 * @param $classification 任务分类
 * @return array
 */
function yhTaskFinish($uid,$classification){
    $user_task_id = DI()->notorm->yh_user_task->where('uid=? and status=? and classification=?',intval($uid),1,intval($classification))->fetchAll();
    if (!$user_task_id){
        return;
    }
    if (count($user_task_id) > 1){
        return DI()->notorm->yh_user_task_error->insert([
            'user_id'=>$uid,
            'classification'=>$classification,
            'error_msg'=>'会员当前分类存在多个任务',
            'error_data'=>json_encode($user_task_id),
        ]);
    }
    $dr = DI()->notorm->yh_user_task->where('id=?',intval($user_task_id[0]['id']))->update(['status'=>5]); //修改任务为待提交
    if (!$dr){
        return DI()->notorm->yh_user_task_error->insert([
            'user_id'=>$uid,
            'classification'=>$classification,
            'error_msg'=>'修改状态失败',
            'error_data'=>json_encode($user_task_id),
        ]);
    }
    return ;
}

/**
 * 获取任务分类ID
 * @param $key
 * @return int
 */
function getTaskConfig($key){
    $task = [
      'task_1'=>1,
      'task_2'=>2,
      'task_3'=>3,
      'task_4'=>4,
      'task_5'=>5,
    ];
    return $task[$key];
}

/*
 * 视频缓存保存数量
 * */
function videoCacheCount($count=5000){
    return $count;
}

/*
 * 根据百分比和总数量，计算各个类型的数量
 * $total_count int ，如：200
 * $type_list object ，如：['private'=>40, 'public'=>40, 'rand'=>20]; // val值可以设置2位小数，如：15.55
 * */
function calculatePercentCount($total_count, $type_percent = array()){
    $data = array();
    if(empty($type_percent)){
        return $data;
    }
    if($total_count <= 0){
        foreach ($type_percent as $key=>$val){
            $data[$key] = 0;
        }
        return $data;
    }
    $type_array = array();
    foreach ($type_percent as $key=>$val){
        $data[$key] = 0;
        for($i=0; $i<$val*100; $i++){
            array_push($type_array, $key);
        }
    }
    $type_array_count = count($type_array);
    for($i=0; $i<$total_count; $i++){
        $type_key = $type_array[mt_rand(0, ($type_array_count-1))];
        if(isset($data[$type_key])){
            $data[$type_key] += 1;
        }
    }
    return $data;
}

/*
 * 8 视频直播信息
 * 请求足球视频直播列表接口
 * */
function getFootballLiveList($football_live_base_url, $football_live_token){
    if(!$football_live_base_url || !$football_live_token){
        return array();
    }
    $url = trim($football_live_base_url, '/').'/soccer/api/live/video';
    $url .= '?is_streaming=1&time_stamp='.time();
    $http_get_res = http_get($url, ['token:'.trim($football_live_token)]);
    if(isset($http_get_res['code']) && $http_get_res['code'] == 0 && isset($http_get_res['result']) && count($http_get_res['result']) > 0){
        return $http_get_res['result'][0];
    }else{
        return array();
    }
}

/*
 * 8 视频直播信息
 * 请求足球视频直播详情接口
 * */
function getFootballLiveInfo($football_live_base_url, $football_live_token, $match_id){
    if(!$football_live_base_url || !$football_live_token || !$match_id){
        return [];
    }
    $url = trim($football_live_base_url, '/').'/soccer/api/live/video';
    $url .= '?is_streaming=1&time_stamp='.time().'&match_id='.$match_id;
    $http_get_res = http_get($url, ['token:'.trim($football_live_token)]);
    if(isset($http_get_res['code']) && $http_get_res['code'] == 0 && isset($http_get_res['result']) && count($http_get_res['result']) > 0){
        return $http_get_res['result'][0];
    }else{
        return [];
    }
}

/*
 * 图片/文件上传
 * $file 对应 $_FILES info信息, 如：$_FILES['file']
 * */
function upload_file($file){
    $data = ['url'=>'', 'url_thumb'=>'', 'path'=>'', 'type'=>''];
    $info = getStorage();
    if($info && isset($info['storage']) && isset($info['storage']['type']) && isset($info['storage'][$info['storage']['type']])){
        $data['type'] = $info['storage']['type'];
        $UPLOAD_TYPE_CONFIG = array(
            'accessKey' => $info['storage'][$data['type']]['accessKey'],
            'secretKey' => $info['storage'][$data['type']]['secretKey'],
            'domain' => $info['storage'][$data['type']]['domain'],
            'bucket' => $info['storage'][$data['type']]['bucket'],
            'upHost' => $info['storage'][$data['type']]['upHost'],
        );

        $tmp_name = $file['tmp_name'];
        $name = $file['name'];
        $pathinfo = pathinfo($name);
        $extension = isset($pathinfo['extension']) && $pathinfo['extension'] ? $pathinfo['extension'] : end(explode('.', $name));

        switch ($data['type']){
            case 'Local':
                //本地上传
                //设置上传路径 设置方法参考3.2
                DI()->ucloud->set('save_path', 'image/' . date("Ymd"));

                //新增修改文件名设置上传的文件名称
                // DI()->ucloud->set('file_name', $this->uid);

                //上传表单名
                $res = DI()->ucloud->upfile($file);

                $data['path'] = '/api/upload' . $res['file'];
                $data['url'] = get_protocal().'://'.$_SERVER['HTTP_HOST'].'/api/upload' . $res['file'];

                if(in_array(strtolower($extension), ['jpg', 'png', 'jpeg'])){
                    $files = '../upload' . $res['file'];
                    $newfiles = str_replace(".png", "_thumb.png", $files);
                    $newfiles = str_replace(".jpg", "_thumb.jpg", $newfiles);
                    $newfiles = str_replace(".gif", "_thumb.gif", $newfiles);
                    $PhalApi_Image = new Image_Lite();
                    //打开图片
                    $PhalApi_Image->open($files);
                    /**
                     * 可以支持其他类型的缩略图生成，设置包括下列常量或者对应的数字：
                     * IMAGE_THUMB_SCALING      //常量，标识缩略图等比例缩放类型
                     * IMAGE_THUMB_FILLED       //常量，标识缩略图缩放后填充类型
                     * IMAGE_THUMB_CENTER       //常量，标识缩略图居中裁剪类型
                     * IMAGE_THUMB_NORTHWEST    //常量，标识缩略图左上角裁剪类型
                     * IMAGE_THUMB_SOUTHEAST    //常量，标识缩略图右下角裁剪类型
                     * IMAGE_THUMB_FIXED        //常量，标识缩略图固定尺寸缩放类型
                     */

                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg

                    $PhalApi_Image->thumb(660, 660, IMAGE_THUMB_SCALING);
                    $PhalApi_Image->save($files);

                    $PhalApi_Image->thumb(200, 200, IMAGE_THUMB_SCALING);
                    $PhalApi_Image->save($newfiles);

                    $data['url'] = get_protocal().'://'.$_SERVER['HTTP_HOST'].'/api/upload' . $res['file']; //600 X 600
                    $data['url_thumb'] = str_replace(".png", "_thumb.png", $data['url']);
                    $data['url_thumb'] = str_replace(".jpg", "_thumb.jpg", $data['url_thumb']);
                    $data['url_thumb'] = str_replace(".gif", "_thumb.gif", $data['url_thumb']);
                }

                @unlink($file['tmp_name']);
                break;
            case 'Qiniu':
                //七牛
                $url = DI()->qiniu->uploadFile($file['tmp_name']);
                if (!empty($url)) {
                    $data['url'] = $url;
                    if(in_array(strtolower($extension), ['jpg', 'png', 'jpeg'])){
                        $data['url'] = $url . '?imageView2/2/w/600/h/600'; //600 X 600
                        $data['url_thumb'] = $url . '?imageView2/2/w/200/h/200'; // 200 X 200
                    }
                }
                break;
            case 'Aliyunoss':
                $Aliyunoss = new Aliyunoss($UPLOAD_TYPE_CONFIG);
                $data['url'] = $Aliyunoss->save($file);
                $data['url_thumb'] = $data['url'];
                break;
        }
    }

    return $data;
}
function generater(){
    $danhao = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return $danhao;
}
