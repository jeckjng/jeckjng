<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_Common extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getLevelList() {
        $list = getLevelList();
        return array('code' => 0, 'msg' =>'', 'info' => $list);
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
        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord($users_coinrecord2); // 点赞收益资金记录记录
        $longVideo  = new Model_Longvideo();
        $longVideo->AgencyCommission($authorUid,$config['video_likes_amount'],$profit, $coinType,$is_withdrawable,'agent_likes_video',3 , $video_info);
    }

    public function getAwardLogList($uid,$p) {
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        $list = DI()->notorm->award_log
            ->select("*")
            ->where("uid='{$uid}'")
            ->order("addtime desc")
            ->limit($start,$nums)
            ->fetchAll();
        $userList = DI()->notorm->users
            ->select("id")
            ->where('id',array_column($list,'tuid'))
            ->fetchPairs('id','user_login');
        foreach ($list as $key=>$value){
            if ($value['tuid']){
                $list[$key]['user_login'] = $userList[$value['tuid']] ?: '-';
            }
        }
        return $list;
    }

    /**
     * 代理返佣
     * @param $uid // 用户id
     * @param $price  // 金额
     * @param $operation_id  // 操作id
     * @param $type //  账变明细：收支类型
     * @param $is_withdrawable  // 账变明细：1 可提现金额 2 不可提现(为转为可提现) 3（不可提现已转为可提现）
     * @param $action  // 账变明细：收支行为
     * @param $agentType  // 1  任务  ，2 购买视频  ， 3 点赞视频，4 上传视频, 5 卖出商品利润返佣
     * @param $remark //  账变明细：	备注
     * @return bool
     */
    public function AgentRebate($uid, $price, $operation_id = 0, $type, $is_withdrawable, $action, $agentType, $remark = '', $shop_order_no = '', $cg_order_id = ''){
        $userinfo = Cache_Users::getInstance()->getUserInfoCache($uid);
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
            $agent_uid = Model_UsersAgent::getInstance()->getSuperiorUid($userinfo['tenant_id'], $current_uid);

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
                $agentData['uid'] = $uid;
                $agentData['pid'] = $agent_uid;
                $agentData['addtime'] = time();
                $agentData['level'] =$key+1;
                $agentData['type'] = intval($agentType);
                $agentData['operation_id'] = intval($operation_id);
                $agentData['status'] = 1;
                $agentData['total_amount'] = $price;
                $agentData['rate'] = $RebateConfByLevel[$key+1]['rate'];
                $agentData['amount'] = $rebate;
                $agentData['tenant_id'] = $agentInfo['tenant_id'];

                DI()->notorm->users->where('id = ?', $agent_uid)->update(array(
                        'agent_total_income' => new NotORM_Literal("agent_total_income +  {$rebate}"),
                        'coin' => new NotORM_Literal("coin + {$rebate}")
                    ) ); // 扣除当前用户收益

                $pre_balance = $agentInfo['coin'];
                $after_balance = bcadd($agentInfo['coin'], $rebate,4);

                $agentRewardId = DI()->notorm->agent_reward ->insert($agentData);// 代理记录

                $coinrecordData=[
                    'type' => $type,
                    'uid' => $agent_uid,
                    'user_login' => $agentInfo['user_login'],
                    "user_type" => intval($agentInfo['user_type']),
                    'giftid' => $agentRewardId,
                    'addtime' => time(),
                    'tenant_id' => $agentInfo['tenant_id'],
                    'action' => $action,
                    "pre_balance" => floatval($pre_balance),
                    'totalcoin' => $rebate,//金额
                    "after_balance" => floatval($after_balance),
                    "giftcount" => 1,
                    'is_withdrawable' => intval($is_withdrawable),
                    'remark' => $remark,
                    'order_id' => $cg_order_id,
                    'shop_order_no' => $shop_order_no,
                ];
                Model_Coinrecord::getInstance()->addCoinrecord($coinrecordData); //  账变记录
                delUserInfoCache($agent_uid);
            }
        }

        return true;
    }


}