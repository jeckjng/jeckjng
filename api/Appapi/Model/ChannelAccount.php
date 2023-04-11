<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/6/11
 * Time: 16:04
 */

class Model_ChannelAccount extends PhalApi_Model_NotORM{

    public static $payAddress = array(
        'Pay_Test' => 'lufei',
        'Pay_Helianshen' => '和连胜',
        'Pay_Kok' => 'KOK通道商户',
    );
    public  function getChargeAccountChannel($uid,$game_tenant_id,$channel_id,$amount,$type){

        $data = DI()->notorm->channel_account->where("tenant_id = '{$game_tenant_id}' and  channel_id = '{$channel_id}'")->order('sort desc')->fetchOne();
        $key = '';
        if (in_array($data['name'],self::$payAddress)){
            $key = array_search($data['name'], self::$payAddress);
        }
        if (empty($key)){
            return 1002;
        }
        $payClass = new $key();
        $result = $payClass->pay($uid,$data,$amount,$game_tenant_id,$type);
        return $result;
    }
    public  function notify($data){
        $model = new Model_Charge();
        $rs = $model->findOrder($data);
  
        $channel_account_info = DI()->notorm->channel_account->where(" id = '{$rs['account_channel_id']}'")->fetchOne();
        $key = '';
        if (in_array($channel_account_info['name'],self::$payAddress)){
            $key = array_search($channel_account_info['name'], self::$payAddress);
        }
        if (empty($key)){
            $update = array(
                'data' => array(),
                'message' => 'fail',
                'code' => '3' // 支付类不存在
            );
            return  $update;
        }
        $payClass = new $key();
       return  $payClass->notify($rs,$data,$channel_account_info);
    }

    public  function chargePay($uid,$account_channel_id,$amount,$game_tenant_id,$vip_id){
         $data = DI()->notorm->channel_account->where("  id = '{$account_channel_id}'")->fetchOne();
         $key = '';

          if (in_array($data['name'],self::$payAddress)){
              $key = array_search($data['name'], self::$payAddress);
          }
          if (empty($key)){
              return 1002;
          }
       
          $payClass = new $key();
          $result = $payClass->pay($uid,$data,$amount,$game_tenant_id,$vip_id);
      /*  $payClass = new Model_Charge();
        $result = $payClass->chargePaynew($uid,$account_channel_id,$amount,$game_tenant_id);*/

        return $result;
    }
    
    public  function getAccountChannel($uid,$channel_id,$reg_key,$reg_url,$vip_id,$game_tenant_id){
        $channelInfo = DI()->notorm->channel->where("id = '{$channel_id}'")->fetchOne();
        $userInfo  = getUserInfo($uid);
        $getTenantId  = getTenantId();
        if ($game_tenant_id == 106 || $game_tenant_id == 101 || $game_tenant_id == 111){
            if ($channelInfo['type'] == 1){
                $data = DI()->notorm->channel_account
                    ->where("channel_id = '{$channel_id}'   and status = 1 and tenant_id ='{$getTenantId}' ")
                    ->select("id,`explain`,reception_name,select_amount,service_charge,vip_id,reg_url_id,float_amount")
                    ->fetchAll();

            }else{

                $prefix = DI()->config->get('dbs.tables.__default__.prefix');
                $data = DI()->notorm->offlinepay
                    ->queryAll("select a.*,c.is_virtual,c.code from {$prefix}offlinepay as a 
                      left join {$prefix}channel b on a.channel_id=b.id 
                      left join {$prefix}rate c on b.coin_id=c.id 
                      where a.channel_id = {$channel_id} and a.tenant_id = {$getTenantId} and a.status = 1   ");
            }
        }else{
            $rechargeLevel = DI()->notorm->recharge_level
                ->where("status = 1 and min_amount <= '{$userInfo['recharge_total']}' and max_amount >= '{$userInfo['recharge_total']}' and tenant_id ='{$getTenantId}'  ")
                ->fetchAll();
            $channelAccountIdsArray = [];
            if ($channelInfo['type'] == 1){
                foreach ($rechargeLevel as $rechargeLevelValue ){
                    $channelAccountid = explode(',',$rechargeLevelValue['channel_account_id']);
                    $channelAccountIdsArray= array_merge($channelAccountIdsArray,$channelAccountid);
                }
                if ($channelAccountIdsArray){
                    $channelAccountIdsArray =  array_unique($channelAccountIdsArray);
                    $channelAccountIdsString = implode(',',$channelAccountIdsArray);
                    $data = DI()->notorm->channel_account
                        ->where("channel_id = '{$channel_id}' and id in({$channelAccountIdsString}) and status = 1 and tenant_id ='{$getTenantId}' ")
                        ->select("id,`explain`,reception_name,select_amount,service_charge,vip_id,reg_url_id,float_amount")
                        ->fetchAll();
                }else{
                    $data =[];
                }
            }else{
                /*$data = DI()->notorm->offlinepay->where("channel_id = '{$channel_id}' and status = 1 ")
                   ->fetchAll();*/
                foreach ($rechargeLevel as $rechargeLevelValue ){
                    $channelAccountid = explode(',',$rechargeLevelValue['offlinepay_id']);
                    $channelAccountIdsArray= array_merge($channelAccountIdsArray,$channelAccountid);
                }
                if ($channelAccountIdsArray) {

                    $channelAccountIdsArray = array_unique($channelAccountIdsArray);
                    $channelAccountIdsString = implode(',', $channelAccountIdsArray);
                    $prefix = DI()->config->get('dbs.tables.__default__.prefix');
                    $data = DI()->notorm->offlinepay
                        ->queryAll("select a.*,c.is_virtual,c.code from {$prefix}offlinepay as a 
                        left join {$prefix}channel b on a.channel_id=b.id 
                        left join {$prefix}rate c on b.coin_id=c.id 
                        where a.channel_id = {$channel_id} and a.tenant_id = {$getTenantId} and a.status = 1 and a.id in ($channelAccountIdsString) ");
                }else{
                    $data =[];
                }

            }
        }

        if ($game_tenant_id == 104){
            $reg_url = DI()->notorm->users_reg_url
                ->where( "reg_key = '{$reg_key}' and status = 1 ")
                ->fetchOne();
            if ($reg_url){
                foreach ($data as  $key => $value){
                    $reg_urlArray = explode(',',$value['reg_url_id']); // 渠道配置域名
                    if (!in_array($reg_url['id'],$reg_urlArray)){ // 不在配置内删除
                        unset($data[$key]);
                    }
                    $vipIdArray =  explode(',',$value['vip_id']);
                    if (!in_array($vip_id,$vipIdArray)){ // 不在配置内删除
                        unset($data[$key]);
                    }
                }
            }
            $data  = array_values($data);
        }

        if ($channelInfo['type'] == 2){ // 线下银行卡充值
            foreach ($data as  $key => $val){
                if($val['limit_charge_total_money'] > 0 && $val['limit_charge_total_money'] <= $val['already_charge_total_money']){
                    unset($data[$key]); // 如果 线下银行卡充值 设置的 限制总充值金额 大于0，且 已经充值总金额 大于等于 限制总充值金额，则不返回前端展示
                }
            }
            $data = array_values($data);
        }

        return $data;
    }

    public  function offlinpay($uid,$account_channel_id,$amount,$game_tenant_id,$user_real_name,$img,$vip_id){
        $config=getConfigPub();
        $offlinepay_info = DI()->notorm->offlinepay->where("  id = '{$account_channel_id}'")->fetchOne();
        if($amount < $offlinepay_info['min_amount'] || $amount > $offlinepay_info['max_amount']){
            return array('code' => 602, 'msg' => codemsg(602), 'info' => ['min_amount'=>$offlinepay_info['min_amount'], 'max_amount'=>$offlinepay_info['max_amount']]);
        }

        $channel = DI()->notorm->channel->where("id = '{$offlinepay_info['channel_id']}'")->fetchOne();
        $coinInfo = DI()->notorm->rate->where(" id = '{$channel['coin_id']}'")->fetchOne();
        $user_info = getUserInfo($uid);

        $dataArray['uid'] = $uid;
        $dataArray['user_login'] = $user_info['user_login'];
        $dataArray['user_type'] = $user_info['user_type'];
        $dataArray['money'] = $amount;
        $dataArray['currency_code'] = $coinInfo['code'];
        $dataArray['rnb_money'] = bcdiv($amount,$coinInfo['rate'],4);
        $dataArray['orderno'] = getOrderid($uid);
        $dataArray['actual_money'] =bcsub( $dataArray['rnb_money'], bcmul( $dataArray['rnb_money'],bcdiv($offlinepay_info['service_charge'],100,4),4),4);
        $dataArray['coin'] =  $dataArray['actual_money'];
        $dataArray['upstream_service_money'] = bcmul( $dataArray['rnb_money'],bcdiv($offlinepay_info['service_charge'],100,4),4);
        $dataArray['upstream_service_rate'] = $offlinepay_info['service_charge'];
        $dataArray['rate'] = $coinInfo['rate'];
        $dataArray['channel_id'] = $offlinepay_info['channel_id'];
        $dataArray['account_channel_id'] = $offlinepay_info['id'];
        $dataArray['status'] = 1;
        $dataArray['user_real_name'] = $user_real_name;
        $dataArray['type'] = 2;
        $dataArray['addtime'] = time();
        if ($img){
            $dataArray['img'] = $img;
        }


        if ($vip_id){ // 充值购买vip
                $vipInfo = DI()->notorm->vip->where("id = '{$vip_id}'")->fetchOne(); //购买的会员等级信息
                if ($config['vip_model'] ==1) {
                    $nowTime = time();
                    $userVip = DI()->notorm->users_vip->where("uid = '{$uid}' and end_time > {$nowTime} ")->order('grade desc')->fetchOne(); // 最高等级会员

                    $vip_buy_id  = $userVip['id'];
                    if (empty($userVip)){
                        $data = array(
                            'uid' => $uid,
                            'addtime' => $nowTime,
                            'endtime' => 0,
                            'tenant_id' => getTenantId(),
                            'grade' => $vipInfo['orderno'],
                            'vip_id' => $vip_id,
                            'price' => $vipInfo['price'],
                        );
                        $vip_buy_id = DI()->notorm->users_vip->insert($data);
                    }else{
                        DI()->notorm->users_vip->where("id ={$vip_buy_id} ")->update(['price'=> $vipInfo['price']]);
                    }

                }
                $dataArray['is_buy_vip']  = 1;
                $dataArray['buy_log_id']  = $vip_buy_id;
            }

        $dataArray['tenant_id'] = getTenantId();;
        $chargeModel = new \Model_Charge();
        $result = $chargeModel->addOrder($dataArray);
        if($result){
            DI()->notorm->offlinepay->where("id = '{$account_channel_id}'")->update(array(
                'already_charge_total_money' => new NotORM_Literal("already_charge_total_money + {$amount}")
            ));
        }
        return $result;

    }

}