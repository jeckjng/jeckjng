<?php

use api\Common\CustRedis;

class Model_Like extends PhalApi_Model_NotORM
{
    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getLikeConfig($uid){
        $tenant_id = getTenantId();
        $like_config_info = Cache_LikeConfig::getInstance()->getLikeConfigInfo($tenant_id);
        $data = array(
            'id' => intval($like_config_info['id']),
            'reward_amount' => floatval($like_config_info['reward_amount']),
            'reward_count' => intval($like_config_info['reward_count']),
            'reward_type' => intval($like_config_info['reward_type']),
            'deposit' => floatval($like_config_info['deposit']),
        );

        return array('code' => 0, 'msg' => '', 'info' => $data);
    }

    public function payLikeDeposit($uid, $id){
        $tenant_id = getTenantId();
        $like_config_info = Cache_LikeConfig::getInstance()->getLikeConfigInfo($tenant_id, $id);
        if(!$like_config_info){
            return array('code' => 900, 'msg' => codemsg(900), 'info' => []);
        }
        $deposit = $like_config_info['deposit'];
        if($deposit == 0){
            return array('code' => 901, 'msg' => codemsg(901), 'info' => []);
        }
        $userInfo = Model_User::getInstance()->getUserInfoWithIdAndTid($uid, $tenant_id, 'id,coin,user_type,user_login');
        if($userInfo['coin'] < $deposit){
            return array('code' => 2006, 'msg' => codemsg(2006), 'info' => []);
        }
        $user_like_info = DI()->notorm->users_like->where('uid = ?', intval($uid))->fetchOne();
        if($user_like_info){
            if($user_like_info['status'] == 1){
                return array('code' => 903, 'msg' => codemsg(903), 'info' => []);
            }
            if($user_like_info['status'] == 2){
                return array('code' => 905, 'msg' => codemsg(905), 'info' => []);
            }
            if($user_like_info['status'] == 3){
                return array('code' => 906, 'msg' => codemsg(906), 'info' => []);
            }
        }

        try{
            //开始数据库事务
            beginTransaction();

            DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('coin' => new NotORM_Literal("coin - {$deposit}"),
                                'like_deposit' => new NotORM_Literal("like_deposit + {$deposit}"),
                        ));
            $users_like_data = array(
                'uid' => $uid,
                'user_login' => $userInfo['user_login'],
                'user_type' => $userInfo['user_type'],
                'create_time' => time(),
                'tenant_id' => $tenant_id,
                'like_config_id' => intval($like_config_info['id']),
                'status' => 1,
                'deposit' => floatval($deposit),
            );
            if($user_like_info){
                $users_like_data['update_time'] = time();
                DI()->notorm->users_like->where('uid = ?', intval($uid))->update($users_like_data);
            }else{
                DI()->notorm->users_like->insert($users_like_data);
            }

            $coinrecord_data = array(
                "type" => 'expend',
                "action" => 'pay_like_deposit',
                "uid" => $uid,
                'user_login' => $userInfo['user_login'],
                'user_type' => $userInfo['user_type'],
                "pre_balance" => floatval($userInfo['coin']),
                "totalcoin" => $deposit,
                "after_balance" => floatval(bcadd($userInfo['coin'], -abs($deposit),4)),
                "addtime" => time(),
                'tenant_id' => getTenantId(),
            );
            Model_Coinrecord::getInstance()->addCoinrecord($coinrecord_data);
            commitTransaction();
        }catch (\Exception $e){
            rollbackTransaction();
            Model_LogComplex::getInstance()->add($e->getMessage(),'【支付点赞保证金】失败',300, $tenant_id, $uid);
            return array('code' => 2, 'msg' => codemsg(2), 'info' => []);
        }
        delUserInfoCache($uid);
        Cache_UsersLike::getInstance()->delUsersLikeCache($uid);
        return array('code' => 0, 'msg' => '', 'info' => []);
    }

    public  function refundLikeDeposit($uid){
        $tenant_id = getTenantId();
        $user_like_info = DI()->notorm->users_like->where("uid = ?", intval($uid))->order('status = 2')->fetchOne();

        if (empty($user_like_info) || $user_like_info['status'] == 4){
            return array('code' => 902, 'msg' => codemsg(902), 'info' => []);
        }
        if ($user_like_info['status'] == 1){
            return array('code' => 903, 'msg' => codemsg(903), 'info' => []);
        }
        if ($user_like_info['status'] == 3){
            return array('code' => 904, 'msg' => codemsg(904), 'info' => []);
        }

        try{
            $res = DI()->notorm->users_like
                ->where("uid = ? and  status = 2 and  id = ?", intval($uid), intval($user_like_info['id']))
                ->update(['status'=> 3, 'refund_time'=>time(), 'update_time' => time()]);
            if(!$res){
                Model_LogComplex::getInstance()->add([
                    '"uid = ? and  status = 2 and  id = ?"',
                    'uid'=>$uid,
                    'id'=>$user_like_info['id'],
                    'update_date'=>['status'=> 3, 'refund_time'=>time(), 'update_time' => time()]
                ], '【点赞保证金退款申请】失败',300, $tenant_id, $uid);
                return array('code' => 2, 'msg' => codemsg(2), 'info' => []);
            }
        }catch (\Exception $e){
            Model_LogComplex::getInstance()->add($e->getMessage(),'【点赞保证金退款申请】失败',300, $tenant_id, $uid);
            return array('code' => 2, 'msg' => codemsg(2), 'info' => []);
        }

        Cache_UsersLike::getInstance()->delUsersLikeCache($uid);
        return array('code' => 0, 'msg' => '', 'info' => []);
    }


    public function sendVideoLikeProfit($type,$videoId,$likeUid,$authorUid, $video_info){
        $likeuser_vip_info = getUserVipInfo($likeUid);
        if (!empty($likeuser_vip_info) && in_array($likeuser_vip_info['status'], [2])) { // 退保证金申请中不能有收益，也不能抢红包，也不能理返点
            return '退保证金申请中不能有收益';
        }

        $likeUserInfo = Model_User::getInstance()->getUserInfoWithIdAndTid($likeUid);
        $authorUserInfo = Model_User::getInstance()->getUserInfoWithIdAndTid($authorUid);

        // 作者为普通用户 点赞不产生收入
        $vip_info = Model_Login::getInstance()->getUserjurisdiction($authorUid, $authorUserInfo['user_type']);
        if($vip_info['new_level'] == '0'){
            return '作者为普通用户 点赞不产生收入';
        }

        $profitRes = DI()->notorm->video_profit->select("id")->where("video_type = '{$type}' and video_id = '{$videoId}' and video_like_uid = '{$likeUid}'")->fetchOne();
        if ($profitRes){ // 已点赞直接返回
            return '已点赞直接返回';
        }
        if (!in_array($likeUserInfo['user_type'] ,[2,5,6])){
            return '用户类型不对';
        }

        $config=getConfigPub(); // 获取 平台设置
        if ($config['video_likes_amount']<=0){ // 点赞收益
            return '点赞收益 不能小于0';
        }
        if($authorUserInfo['user_type'] == 7){ // 测试用户的视频，不执行资金逻辑
            return '测试用户的视频，不执行资金逻辑';
        }

        if ($config['video_likes_amount_type'] == 1){//  为可提现余额
            $data = array(
                'coin'=>array('coin' => new NotORM_Literal("coin +".$config['video_likes_amount']) )
            );
            $pre_balance = $authorUserInfo['coin'];
            $after_balance = bcadd($authorUserInfo['coin'], $config['video_likes_amount'],4);
            $is_withdrawable  = 1;
            $coinType = 'income';
        }else{
            $data = array(
                'nowithdrawable_coin'=>array('nowithdrawable_coin' => new NotORM_Literal("nowithdrawable_coin +".$config['video_likes_amount']) )
            );
            $pre_balance = $authorUserInfo['nowithdrawable_coin'];
            $after_balance = bcadd($authorUserInfo['nowithdrawable_coin'], $config['video_likes_amount'],4);
            $is_withdrawable =2;
            $coinType  = 'income_nowithdraw';
            $redis = connectRedis();
            $keytime = time();
            $redis->lPush($authorUid . '_reward_time', $keytime);// 存用户 时间数据key
            $amount = $redis->get($authorUid . '_' . $keytime.'_reward');
            $totalAmount = bcadd($config['video_likes_amount'], $amount, 2);
            $redis->set($authorUid . '_' . $keytime.'_reward', $totalAmount);// 存佣金
            $expireTime = time() + $config['withdrawal_time'] * 86400;
            $redis->expireAt($authorUid . '_' . $keytime.'_reward', $expireTime);// 设置过期时间
        }
        DI()->notorm->users->where("id = '{$authorUid}'")->update($data);


        $tenantId=getTenantId();
        $profitData['video_uid'] = $authorUid;
        $profitData['video_user_login'] = $authorUserInfo['user_login'];
        $profitData['video_profit'] = $config['video_likes_amount'];
        $profitData['video_like_uid'] = $likeUid;
        $profitData['video_like_user_login'] = $likeUserInfo['user_login'];
        $profitData['video_id'] = $videoId;
        $profitData['video_type'] = $type;
        $profitData['create_time'] = time();
        $profitData['tenant_id'] = $tenantId;
        $profit  = DI()->notorm->video_profit->insert($profitData);
        $users_coinrecord2 = array(
            'type' => $coinType,
            'uid' => intval($authorUid),
            'user_login' => $authorUserInfo['user_login'],
            "user_type" => intval($authorUserInfo['user_type']),
            'giftid' => $profit,
            'addtime' => time(),
            'tenant_id' => intval($authorUserInfo['tenant_id']),
            'action' => 'like_video_rebate',
            "pre_balance" => floatval($pre_balance),
            'totalcoin' => floatval($config['video_likes_amount']),
            "after_balance" => floatval($after_balance),
            "giftcount"=>1,
            "cd_ratio"=>'1:'.floatval($config['money_rate']),
            'remark' => '视频id: '.$video_info['id'].'<br>标题: '.$video_info['title'],
        );
        Model_Coinrecord::getInstance()->addCoinrecord($users_coinrecord2); // 点赞收益资金记录记录
        $result = $this->AgencyCommission($authorUid,$config['video_likes_amount'],$profit, $coinType,$is_withdrawable,'agent_likes_video',3 , $video_info);
        return $result;
    }

    /*
     * 视频点赞收益
     * */
/*    public function sendVideoLikeProfit($type,$videoId,$likeUid,$authorUid, $video_info){
        $likeUserInfo = Model_User::getInstance()->getUserInfoWithIdAndTid($likeUid);
        $authorUserInfo = Model_User::getInstance()->getUserInfoWithIdAndTid($authorUid);
        $config = getConfigPub(); // 获取 平台设置

        $usersLikeInfo = Cache_UsersLike::getInstance()->getUsersLikeInfo($authorUid);
        if(!$usersLikeInfo || $usersLikeInfo['status'] != 2){
            return '未支付点赞保证金，点赞不产生收入';
        }
        $likeConfigInfo = Cache_LikeConfig::getInstance()->getLikeConfigInfo($video_info['tenant_id']);
        if(!$likeConfigInfo || $likeConfigInfo['reward_amount'] == 0){
            return '点赞保证金配置不存在或者点赞奖励为0，点赞不产生收入';
        }
        $reward_amount = $likeConfigInfo['reward_amount'];

        if (!in_array($likeUserInfo['user_type'] ,[2,5,6])){
            return '用户类型不对';
        }
        if($authorUserInfo['user_type'] == 7){ // 测试用户的视频，不执行资金逻辑
            return '测试用户的视频，不执行资金逻辑';
        }
        $profitRes = DI()->notorm->video_profit->select("id")->where("video_type = '{$type}' and video_id = '{$videoId}' and video_like_uid = '{$likeUid}'")->fetchOne();
        if ($profitRes){ // 已点赞直接返回
            return '已点赞直接返回';
        }
        $video_profit_where = 'video_uid = '.$authorUid.' ';
        if($likeConfigInfo['reward_type'] == 2){ // 奖励金额模式：1.总次数，2.每天
            $video_profit_where .= ' and create_time >= '.strtotime(date('Y-m-d 00:00:00')).' and create_time <= '.strtotime(date('Y-m-d 23:59:59'));
        }
        $video_profit_count = DI()->notorm->video_profit->where($video_profit_where)->count();
        if($video_profit_count >= $likeConfigInfo['reward_count']){
            return '点赞奖励次数已达上限';
        }

        if ($likeConfigInfo['reward_amount_type'] == 1){//  为可提现余额
            $data = array(
                'coin'=>array('coin' => new NotORM_Literal("coin +".$reward_amount) )
            );
            $pre_balance = $authorUserInfo['coin'];
            $after_balance = bcadd($authorUserInfo['coin'], $reward_amount,4);
            $is_withdrawable  = 1;
            $coinType = 'income';
        }else{
            $data = array(
                'nowithdrawable_coin'=>array('nowithdrawable_coin' => new NotORM_Literal("nowithdrawable_coin +".$reward_amount) )
            );
            $pre_balance = $authorUserInfo['nowithdrawable_coin'];
            $after_balance = bcadd($authorUserInfo['nowithdrawable_coin'], $reward_amount,4);
            $is_withdrawable =2;
            $coinType  = 'income_nowithdraw';
            $keytime = time();
            CustRedis::getInstance()->lPush($authorUid . '_reward_time', $keytime);// 存用户 时间数据key
            $amount = CustRedis::getInstance()->get($authorUid . '_' . $keytime.'_reward');
            $totalAmount = bcadd($reward_amount, $amount, 2);
            CustRedis::getInstance()->set($authorUid . '_' . $keytime.'_reward', $totalAmount);// 存佣金
            $expireTime = time() + $config['withdrawal_time'] * 86400;
            CustRedis::getInstance()->expireAt($authorUid . '_' . $keytime.'_reward', $expireTime);// 设置过期时间
        }
        DI()->notorm->users->where("id = '{$authorUid}'")->update($data);


        $tenantId=getTenantId();
        $profitData['video_uid'] = $authorUid;
        $profitData['video_user_login'] = $authorUserInfo['user_login'];
        $profitData['video_profit'] = $reward_amount;
        $profitData['video_like_uid'] = $likeUid;
        $profitData['video_like_user_login'] = $likeUserInfo['user_login'];
        $profitData['video_id'] = $videoId;
        $profitData['video_type'] = $type;
        $profitData['create_time'] = time();
        $profitData['tenant_id'] = $tenantId;
        $profit  = DI()->notorm->video_profit->insert($profitData);
        $users_coinrecord2 = array(
            'type' => $coinType,
            'uid' => intval($authorUid),
            'user_login' => $authorUserInfo['user_login'],
            "user_type" => intval($authorUserInfo['user_type']),
            'giftid' => $profit,
            'addtime' => time(),
            'tenant_id' => intval($authorUserInfo['tenant_id']),
            'action' => 'like_video_rebate',
            "pre_balance" => floatval($pre_balance),
            'totalcoin' => floatval($reward_amount),
            "after_balance" => floatval($after_balance),
            "giftcount"=>1,
            "cd_ratio"=>'1:'.floatval($config['money_rate']),
            'remark' => '视频id: '.$video_info['id'].'<br>标题: '.$video_info['title'],
        );
        Model_Coinrecord::getInstance()->addCoinrecord($users_coinrecord2); // 点赞收益资金记录记录
        $result = $this->AgencyCommission($authorUid,$reward_amount,$profit, $coinType,$is_withdrawable,'agent_likes_video',3 , $video_info);
        return $result;
    }*/

    /**
     * @param $uid 用户id
     * @param $price  金额
     * @param $giftid  操作id
     * @param $type  //  income 可提现 ， income_nowithdraw不可提余额
     * @param $is_withdrawable  1可提现  2  不可提现
     * @param $action   agent_buy_video  购买视频代理收益  , 点赞代理 agent_likes_video
     *@param $agentType    1  任务  ，2 购买视频  ， 3 点赞视频，4 上传视频'
     * @return bool
     */
    public function AgencyCommission($uid,$price,$giftid, $type,$is_withdrawable ,$action,$agentType, $video_info){
        $userinfo = getUserInfo($uid);
        $RebateConf = getAgentRebateConf($userinfo['tenant_id']);
        if(!$RebateConf){
            return  true;
        }

        $RebateConfByLevel =  array_column($RebateConf,null,'agent_level');

        $config = getConfigPub();// 代理 层级
        if (!$config['agent_sum']){
            return  true;
        }

        $current_uid = $uid;
        for($i=0; $i<$config['agent_sum']; $i++){
            $key = $i;
            $agent_uid = Model_UsersAgent::getInstance()->getSuperiorUid($video_info['tenant_id'], $current_uid);
            if(!$agent_uid){
                return 'current_uid: '.$current_uid.', agent_uid is none: '.$agent_uid;
            }
            $current_uid = $agent_uid;

            $agentInfo = Model_User::getInstance()->getUserInfoWithIdAndTid($agent_uid);
            $agentuser_vip_info = getUserVipInfo($agent_uid);
            if (!empty($agentuser_vip_info) && in_array($agentuser_vip_info['status'], [2])) { // 退保证金申请中不能有收益，也不能抢红包，也不能理返点
                continue;
            }
            if ($agentInfo['rebate_status'] == 0) { // 判断 代理返点 是否开启
                continue;
            }
            if($agentInfo['user_type'] == 7){ // 测试账号，不做逻辑处理，直接返回
                continue;
            }
            $rebate = bcmul($price,$RebateConfByLevel[$key+1]['rate']/100,2);
            if ($rebate> 0){
                $agentData['uid']= $uid;
                $agentData['pid']= $agent_uid;
                $agentData['addtime']= time();
                $agentData['level']=$key+1;
                $agentData['type']= $agentType;
                $agentData['operation_id']= $giftid;
                $agentData['status']= 1;
                $agentData['total_amount']= $price;
                $agentData['rate']= $RebateConfByLevel[$key+1]['rate'];
                $agentData['amount']= $rebate;
                $agentData['tenant_id']= $agentInfo['tenant_id'];
                if ($is_withdrawable == 1){
                    DI()->notorm->users
                        ->where('id = ?', $agent_uid)
                        ->update(array(
                            'agent_total_income' => new NotORM_Literal("agent_total_income +  {$rebate}"),
                            'coin' => new NotORM_Literal("coin + {$rebate}")
                        ) ); // 扣除当前用户收益
                    $pre_balance = $agentInfo['coin'];
                    $after_balance = bcadd($agentInfo['coin'], $rebate,4);
                }else{
                    DI()->notorm->users
                        ->where('id = ?  ', $agent_uid)
                        ->update(array(
                            'agent_total_income' => new NotORM_Literal("agent_total_income +  {$rebate}"),
                            'nowithdrawable_coin' => new NotORM_Literal("nowithdrawable_coin + {$rebate}")
                        ) ); // 扣除当前用户收益
                    $pre_balance = $agentInfo['nowithdrawable_coin'];
                    $after_balance = bcadd($agentInfo['nowithdrawable_coin'], $rebate,4);
                    $keytime = time();
                    CustRedis::getInstance()->lPush($agent_uid . '_reward_time', $keytime);// 存用户 时间数据key
                    $amount = CustRedis::getInstance()->get($agent_uid . '_' . $keytime.'_reward');
                    $totalAmount = bcadd($rebate, $amount, 2);
                    CustRedis::getInstance()->set($agent_uid . '_' . $keytime.'_reward', $totalAmount);// 存佣金
                    $expireTime = time() + $config['withdrawal_time']* 86400 ;
                    CustRedis::getInstance()->expireAt($agent_uid . '_' . $keytime.'_reward', $expireTime);// 设置过期时间
                }
                $agentRewardId = DI()->notorm->agent_reward ->insert($agentData);// 代理记录

                $coinrecordData=[
                    'type' => $type,
                    'uid' => $agent_uid,
                    'user_login' => $agentInfo['user_login'],
                    "user_type" => intval($agentInfo['user_type']),
                    'giftid' =>$agentRewardId,
                    'addtime' => time(),
                    'tenant_id' => $agentInfo['tenant_id'],
                    'action' =>$action,
                    "pre_balance" => floatval($pre_balance),
                    'totalcoin' => $rebate,//金额
                    "after_balance" => floatval($after_balance),
                    "giftcount"=>1,
                    'is_withdrawable' =>$is_withdrawable,
                    'remark' => '视频id: '.$video_info['id'].'<br>标题: '.$video_info['title'],
                ];
                Model_Coinrecord::getInstance()->addCoinrecord($coinrecordData); //  账变记录
                delUserInfoCache($agent_uid);
            }
        }

        return true;
    }


}
