<?php

class Model_Withdrawal extends PhalApi_Model_NotORM {
    public function getCoin() {
        $tenant_id = getTenantId();
        $list=DI()->notorm->rate
            ->where('tenant_id = ? and status = 1 ', intval($tenant_id))
            ->order('sort asc')
            ->fetchAll();
        return $list;
    }

    public function applyWithdrawal($uid ,$bank_id, $coin, $amount, $game_tenant_id, $type, $cash_account_type, $cash_network_type, $virtual_coin_address, $qr_code_url) {
        $code = codemsg();
        $config = getConfigPub();
        $tenant_id = getTenantId();
        $coin_rateInfo = [];
        $userModel = new Model_User();
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
        if($userInfo['user_type'] == 7){ // 测试账号，不能提现
            return array('code' => 401, 'msg' => $code['401'], 'info' => array('测试账号，不能操作'));
        }
        if($userInfo['user_status'] != 1){ // 账号被禁用，不能提现
            return array('code' => 407, 'msg' => codemsg(407), 'info' => array('账号被禁用，不能操作'));
        }
        $amount_str = strval($amount);
        if(strpos($amount_str, '.') !== false){ // 提现金额必须为整数
            return array('code' => 2120, 'msg' => codemsg(2120), 'info' => array());
        }
        // 提现账号类型：1.银行卡，2.USDT （默认银行卡提现）
        $cash_account_type = empty($cash_account_type) ? 1 : $cash_account_type;
        switch ($cash_account_type){
            case 1 :
                if(empty($bank_id) || empty($coin)){
                    return array('code' => 703, 'msg' => $code['703'], 'info' => array("bank_id/coin cant't empty"));
                }
                $coin_rateInfo = DI()->notorm->rate->where("id ='{$coin}'")->fetchOne();
                if (!$coin_rateInfo){
                    return array('code' => 2105, 'msg' => $code['2105'], 'info' => array('提现币种不存在rate_id: '.$coin));
                }
                $rnb_money = bcdiv($amount,$config['money_rate'],4);// 人民币金额
                $money = bcmul($rnb_money,$coin_rateInfo['rate'],4);// 提现币种金额
                $coin_amount = $amount; // 钻石金额
                break;
            case 2 :
                if(empty($cash_network_type) || empty($virtual_coin_address) || empty($qr_code_url)){
                    return array('code' => 703, 'msg' => $code['703'], 'info' => array("cash_network_type/virtual_coin_address/qr_code_url cant't empty"));
                }
                if(!in_array($cash_network_type, array('TRC20','ERC20'))){
                    return array('code' => 703, 'msg' => $code['703'], 'info' => array("cash_network_type error"));
                }
                $coin_rateInfo = DI()->notorm->rate->where("tenant_id = ? and code = 'USDT'", intval($tenant_id))->fetchOne();
                if (!$coin_rateInfo){
                    return array('code' => 2105, 'msg' => $code['2105'], array('提现币种USDT不存在'));
                }
                $rnb_money = bcdiv($amount,$coin_rateInfo['rate'],4);// 人民币金额
                $money = $amount; // 提现币种金额
                $coin_amount = bcdiv($amount,$coin_rateInfo['rate'] * $config['money_rate'],4); // 钻石金额
                break;
            default:
                return array('code' => 703, 'msg' => $code['703'], 'info' => array("cash_account_type error"));
        }
        $day = date('d');
        if ($day < $config['cash_start'] ||  $day >$config['cash_end'] ){
            return array('code' => 1001,'msg' =>'不在提现日期内', 'info' => array('cash_start'=>$config['cash_start'], 'cash_end'=>$config['cash_end']));
        }
        if(date('H',time())<$config['cash_hour_star'] || date('H',time())>$config['cash_hour_end']){
            return array('code' => 2052, 'msg' => $code['2052'], 'info' => array('cash_hour_star'=>$config['cash_hour_star'],'cash_hour_end'=>$config['cash_hour_end']));
        }
        if($config['cash_check']==1){
            $cashrecord_0 =  DI()->notorm->users_cashrecord->where(['uid'=>intval($uid),'status'=>0])->count();
            if($cashrecord_0 > 0){
                return array('code' => 2053, 'msg' => $code['2053'], 'info' => array());
            }
            if($config['cash_nosucc'] > 0){
                $cashrecord_2 =  DI()->notorm->users_cashrecord->where(['uid'=>intval($uid),'status'=>2])->count();
                if($cashrecord_2 >= $config['cash_nosucc']){
                    return array('code' => 2054, 'msg' => $code['2054'], 'info' => array('cash_nosucc'=>$config['cash_nosucc']));
                }
            }
        }

        if ($rnb_money *1000 < $config['cash_min'] *1000 ){
            return array('code' => 1002,'msg' =>'提现最低金额不够', 'info' => array('cash_min'=>$config['cash_min']));
        }

        if ($type ==1 ){
           if ($userInfo['coin'] < $coin_amount){
               return array('code' => 1005,'msg' =>'余额不足', 'info' => array());
           }
            $updateDate = array(
                'coin' => new NotORM_Literal("coin - '{$coin_amount}'"),
                'frozen_coin' => new NotORM_Literal("frozen_coin +'{$coin_amount}'"),
                );
            $pre_balance = $userInfo['coin'];
            $after_balance = bcadd($userInfo['coin'], -abs($coin_amount),4);
        }else{
            if ($userInfo['votes'] < $coin_amount){
                return array('code' => 1005,'msg' =>'余额不足', 'info' => array());
            }
            $updateDate = array(
                'votes' => new NotORM_Literal("votes - '{$coin_amount}'"),
                'frozen_votes' => new NotORM_Literal("frozen_votes +'{$coin_amount}'"),
            );
            $pre_balance = $userInfo['votes'];
            $after_balance = bcadd($userInfo['votes'], -abs($coin_amount),4);
        }
        if ($game_tenant_id != 106){
            $rechargeLevel = DI()->notorm->recharge_level
                ->where("  status = 1 and  min_amount <= '{$userInfo['recharge_total']}' and max_amount >= '{$userInfo['recharge_total']}' and  tenant_id ='{$userInfo['tenant_id']}'")
                ->fetchAll();
            $countArray = [];
            $amountArray = [];

            foreach ($rechargeLevel as $rechargeLevelValue ){
                $countArray[] = $rechargeLevelValue['every_day_count'];
                $amountArray[] = $rechargeLevelValue['every_day_amount'];
            }
            $count = 0 ;
            $UserAmount = 0;
            if ($countArray){
                $count = max($countArray);
            }

            if ($amountArray){
                $UserAmount = max($amountArray);
            }
            $startTime = strtotime(date('Y-m-d 00:00:00'));
            $endTime  =  strtotime(date('Y-m-d 23:59:59'));

            $todayCount = DI()->notorm->users_cashrecord->where("uid = '{$uid}' and status in (0,1)
          and  addtime >= {$startTime} 
          and  addtime <= {$endTime} ")->count();


            if ($todayCount >= $count ){
                return array('code' => 2106,'msg' => $code['2106'], 'info' => array('单日提现次数已达上限：'.$count));
            }
            //  $todayAmount = DI()->notorm->users_cashrecord->where("uid' = '{$uid}' and status in (0,1) and  addtime > {$startTime} and  addtime < {$endTime} ")->sum('rnb_money');
            if ($rnb_money > $UserAmount ){
                return array('code' => 2107,'msg' => $code['2107'], 'info' => array('单日每次提现金额上限：'.$UserAmount));
            }
        }

        /* if ($money*1000 < $coin_amount*1000 ){
             return array('code' => 1005,'msg' =>'余额不足' );
         }*/

        // 计算手续费和到账金额
        $received_money = $money;
        $service_fee = 0;
        $withdrawFeeConfigList = Cache_WithdrawFeeConfig::getInstance()->getWithdrawFeeConfigList($tenant_id);
        $feeInfo = array();
        foreach ($withdrawFeeConfigList as $key=>$val){
            if($amount <= $val['amount']){
                $feeInfo = $val;
            }
        }
        if(!empty($feeInfo) && $feeInfo['fee'] > 0){
            $service_fee = $feeInfo['type'] == 1 ? round(bcmul($money, $feeInfo['fee']/100, 1), 0) : $feeInfo['fee'];
            $received_money = bcsub($money, $service_fee, 4);
        }

        // 提现账号类型：1.银行卡，2.USDT （默认银行卡提现）
        $cash_account_type = empty($cash_account_type) ? 1 : $cash_account_type;
        $agent_user_info = Cache_Users::getInstance()->getUserInfoCache($userInfo['pid']);
        $superior_uid = 0;
        $superior_user_login = '';
        $superior_user_type = 0;
        if(!empty($agent_user_info)){
            $superior_uid = intval($userInfo['pid']);
            $superior_user_login = $agent_user_info['user_login'];
            $superior_user_type = intval($agent_user_info['user_type']);
        }

        switch ($cash_account_type){
            case 1 :
                $bankInfo =DI()->notorm->bank_card
                    ->where("id ='{$bank_id}' and uid ='{$uid}'")
                    ->fetchOne();
                if (!$bankInfo){
                    return array('code' => 1004,'msg' =>'请选择自己绑定的银行', 'info' => array());
                }

                $data = array(
                    'uid' => $uid,
                    'user_login' => $userInfo['user_login'],
                    'user_type' => intval($userInfo['user_type']),
                    'superior_uid' => $superior_uid,
                    'superior_user_login' => $superior_user_login,
                    'superior_user_type' => $superior_user_type,
                    'money' => $money,
                    'received_money' => $received_money,
                    'service_fee' => $service_fee,
                    'orderno' => getTxorder($uid),
                    'status' => 0,
                    'addtime' => time(),
                    'account_bank' => $bankInfo['bank_name'],
                    'account' => $bankInfo['bank_number'],
                    'name' =>  $bankInfo['real_name'],
                    'tenant_id' => getTenantId(),
                    'bank_id' => $bankInfo['id'],
                    'rate' => $coin_rateInfo['rate'],
                    'rnb_money' => $rnb_money,
                    'votes' =>  $coin_amount,
                    'currency' => $coin_rateInfo['name'],
                    'pay_coin' => $type,
                    'coin_number' => $coin_amount,
                    'currency_code' => $coin_rateInfo['code']
                );
                break;
            case 2 :
                $data = array(
                    'uid' => $uid,
                    'user_login' => $userInfo['user_login'],
                    'user_type' => intval($userInfo['user_type']),
                    'superior_uid' => $superior_uid,
                    'superior_user_login' => $superior_user_login,
                    'superior_user_type' => $superior_user_type,
                    'money' => $money,
                    'received_money' => $received_money,
                    'service_fee' => $service_fee,
                    'orderno' => getTxorder($uid),
                    'status' => 0,
                    'addtime' => time(),
                    'account_bank' => '',
                    'account' => '',
                    'name' => '',
                    'cash_account_type' => intval($cash_account_type),
                    'cash_network_type' => $cash_network_type,
                    'virtual_coin_address' => $virtual_coin_address,
                    'qr_code_url' => $qr_code_url,
                    'tenant_id' => intval(getTenantId()),
                    'bank_id' => 0,
                    'rate' => $coin_rateInfo['rate'],
                    'rnb_money' => $rnb_money,
                    'votes' =>  $coin_amount,
                    'currency' => $coin_rateInfo['name'],
                    'pay_coin' => $type,
                    'coin_number' => $coin_amount,
                    'currency_code' => $coin_rateInfo['code']
                );
                break;
        }
        if(!isset($data)){
            return array('code' => 703, 'msg' => $code['703'], 'info' => array("cash_account_type error"));
        }

        try{
            beginTransaction();
            $order = DI()->notorm->users_cashrecord->insert($data);
            $user =DI()->notorm->users->where("id = '{$uid}'")->update( $updateDate );
            $coinrecordModel = new Model_Coinrecord();
            $insert_data = array(
                'type' => 'expend',
                'uid' => intval($uid),
                'user_login' => $userInfo['user_login'],
                "user_type" => intval($userInfo['user_type']),
                'addtime' => time(),
                'tenant_id' => intval(getTenantId()),
                'action' => 'withdrawn_apply',
                "pre_balance" => floatval($pre_balance),
                'totalcoin' => floatval($coin_amount),
                "after_balance" => floatval($after_balance),
            );
            $coinrecordModel->addCoinrecord($insert_data);
            if ($order !== false && $user !==false ){
                commitTransaction();
            }else{
                rollbackTransaction();
                return array('code' => 1006,'msg' =>'申请失败', 'info' =>[] );
            }
        }catch (\Exception $e){
            rollbackTransaction();
            return array('code' => 1006,'msg' =>'申请失败', 'info' =>[$e->getMessage()] );
        }
        delUserInfoCache($uid);
        return array('code' => 0,'msg' =>'申请成功', 'info' => array());
    }

    public  function withdrawalLog($uid,$status,$p){
        $where  = 'uid = '.$uid;
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        if ($status==='0'){
            $where.= ' and status = 0';
        }elseif ($status){
            $where.= ' and status = '.$status;
        }
        $list = DI()->notorm->users_cashrecord->where($where)->order('addtime desc')->limit($start,$nums)->fetchAll();
        foreach ($list as $key => $value){
            $list[$key]['addtime']  = date('Y-m-d H:i:s',$value['addtime']);
            if ($value['uptime']){
                $list[$key]['uptime']  = date('Y-m-d H:i:s',$value['uptime']);
            }
            $list[$key]['money'] = floatval($value['money']);
            $list[$key]['received_money'] = floatval($value['received_money']);
            $list[$key]['service_fee'] = floatval($value['service_fee']);
            $list[$key]['coin_number'] = floatval($value['coin_number']);
            $list[$key]['rnb_money'] = floatval($value['rnb_money']);
            $list[$key]['votes'] = floatval($value['votes']);
        }
        return $list;
    }
}