<?php

class Model_Charge extends PhalApi_Model_NotORM {
	/* 订单号 */
	public function getOrderId($changeid,$orderinfo) {
		
		$charge=DI()->notorm->charge_rules->select('*')->where('id=?',$changeid)->fetchOne();
		
		if(!$charge || $charge['coin']!=$orderinfo['coin'] || ($charge['money']!=$orderinfo['money']  && $charge['money_ios']!=$orderinfo['money'] )){
			return 1003;
		}
		
		$orderinfo['coin_give']=$charge['give'];
		

		$result= DI()->notorm->users_charge->insert($orderinfo);

		return $result;
	}

	public  function getChargeChannel($uid,$reg_key='',$reg_url='',$vip_id='',$game_tenant_id){

        $config=getConfigPub();
        /*if(date('H',time())<$config['charge_hour_star'] || date('H',time())>$config['charge_hour_end']){
            return array('code' => 2051, 'msg' => $code['2051'], 'info' => array('charge_hour_star'=>$config['charge_hour_star'],'charge_hour_end'=>$config['charge_hour_end']));
        }*/
        $getTenantId  = getTenantId();
        $userInfo  = getUserInfo($uid);
        if ($game_tenant_id == 106 || $game_tenant_id == 101 || $game_tenant_id == 111){  // 不需要用户充值金额分配等级
            $prefix= DI()->config->get('dbs.tables.__default__.prefix');
            $result= DI()->notorm->channel
                ->queryAll("select c.*,v.is_virtual  from  {$prefix}channel as c left join {$prefix}rate v on c.coin_id=v.id where c.status = 1 and  c.tenant_id = {$getTenantId}  ORDER BY c.type asc");

        }else{

            $rechargeLevel = DI()->notorm->recharge_level
                ->where("  status = 1 and  min_amount <= '{$userInfo['recharge_total']}' and max_amount >= '{$userInfo['recharge_total']}' and  tenant_id ='{$getTenantId}'")

                ->fetchAll();
            $channelId = [];
            foreach ($rechargeLevel as $rechargeLevelValue ){
                $everyChannel = explode(',',$rechargeLevelValue['channel_id']);
                $channelId= array_merge($channelId,$everyChannel);
            }
            $channelId =  array_unique($channelId);


            if ($channelId){
                $everyChannelString = implode(',',$channelId);

                $prefix= DI()->config->get('dbs.tables.__default__.prefix');
                $result= DI()->notorm->channel
                    ->queryAll("select c.*,v.is_virtual  from  {$prefix}channel as c left join {$prefix}rate v on c.coin_id=v.id where c.status = 1 and  c.tenant_id = {$getTenantId} and c.id in ({$everyChannelString}) ORDER BY c.type asc");

                if ($game_tenant_id == 104){
                    $reg_url = DI()->notorm->users_reg_url
                        ->where( "reg_key = '{$reg_key}' and status = 1 ")

                        //->where("reg_url = '{$reg_url}' and reg_key = '{$reg_key}' and  status  = 1 ")
                        ->fetchOne();


                    if ($reg_url){
                        foreach ($result as  $key => $value){
                            $reg_urlArray = explode(',',$value['reg_url_id']); // 渠道配置域名
                            if (!in_array($reg_url['id'],$reg_urlArray)){ // 不在配置内删除
                                unset($result[$key]);
                            }
                            $vipIdArray =  explode(',',$value['vip_id']);
                            if (!in_array($vip_id,$vipIdArray)){ // 不在配置内删除
                                unset($result[$key]);
                            }
                        }
                    }
                    $result  = array_values($result);
                }
            }else{
                $result =[];
            }
        }


        return array('code' => 0, 'msg' => '', 'info' => $result);
    }


    public  function addOrder($data){
        $result= DI()->notorm->users_charge->insert($data);

        return $result;
    }
    public  function findOrder($data){
        $result= DI()->notorm->users_charge->where("orderno = '{$data['orderid']}'")->fetchOne();
        return $result;
    }
    /**
     * 回调充值订单记录三方支付订单号
     * @param $orderno
     * @param $trade_no
     */
    function addTradeNo($data){
     
        $result = DI()->notorm->users_charge->where('orderno=?',$data['orderid'])->fetchOne();
        if ($result){
            DI()->notorm->users_charge->where('orderno=?',$data['orderid'])->update(['trade_no'=>$data['transaction_id'],"status"=>2,"operated_by"=>'系统回调']);
        }
        $userData = [
            'coin' => new NotORM_Literal("coin + {$data['amount']}"),
        ];
        $balance = DI()->notorm->users->where("id  = '{$data['uid']}' ")->update($userData);

        $userInfo=getUserInfo($data['uid']);
        $tenantId=$userInfo['tenant_id'];

        $data=array(
            "type"=>'income',
            "action"=>'charge',
            "uid"=>$data['uid'],
            'user_login' => $userInfo['user_login'],
            "money"=>$data['uid'],
            "addtime"=>time(),
            "uptime"=>time(),
            "tenant_id"=>$tenantId,
            'order_id'=>$data['amount'],
            "pre_balance" => floatval($userInfo['coin']),
            "totalcoin" =>$data['amount'],
            "after_balance" => floatval(bcadd($userInfo['coin'], $data['amount'],4)),
            'order_id'=>$data['orderid']
        );

        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord($data);
        delUserInfoCache($data['uid']);

    }

    public  function  updateOrder($rs,$data){
        $data['updatetime'] = time();
        $result= DI()->notorm->users_charge->where("orderno  = '{$rs['orderno']}' and status = 1 ")->update($data);
        if ($result !== false){
            $u_info =  DI()->notorm->users->where(['id'=>intval($rs['uid'])])->select('id,tenant_id,recharge_num')->fetchOne();
            $coin = $rs['coin'] +$rs['coin_give'];
            $userData = [
                'coin' => new NotORM_Literal("coin + {$coin}"),
                'recharge_num'=> new NotORM_Literal("recharge_num + 1") ,
                'recharge_total' => new NotORM_Literal("recharge_total + {$rs['rnb_money']}"),
                'actual_recharge_total' => new NotORM_Literal("actual_recharge_total + {$rs['actual_money']}"),

                ];
            if($u_info['recharge_num']==0){
                $userData['firstrecharge_coin'] = $coin;
            }
            $balance = DI()->notorm->users->where("id  = '{$rs['uid']}' ")->update($userData);
            if ($rs['is_buy_vip'] == 1){
                $nowTime =  time();
                $userVip = DI()->notorm->users_vip->where("id = '{$rs['buy_log_id']}'")->fetchOne(); // 最高等级会员
                $vipInfo = DI()->notorm->vip->where("id = '{$userVip['vip_id']}'")->fetchOne(); //购买的会员等级信息
                $endtime = strtotime("+" . $vipInfo['length'] . " month", $nowTime);
                if ($vipInfo['give_data']) {
                    $endtime = strtotime("+{$vipInfo['give_data']} day", $endtime);
                }
                DI()->notorm->users_vip->where("id = '{$rs['buy_log_id']}'")->update(['endtime'=>$endtime ]);
                DI()->notorm->users->where("id = '{$rs['uid']}'")->update(array('coin' => new NotORM_Literal("coin - {$userVip['price']}")));
            }

            activity_reward($rs['uid'],$u_info['recharge_num'],$coin,$u_info['tenant_id']);
            delUserInfoCache($rs['uid']);
            return $balance;
        }

    }

    public  function orderList($uid,$status,$p){
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        $where = "uid = '{$uid}'";
        if ($status) {
            $where .= " and status = '{$status}'";

        }
        $result = DI()->notorm->users_charge->where($where)
            ->order('addtime DESC')
            ->limit($start,$nums)
            ->fetchAll();
        foreach ($result as $key => $value){
            if ($value['type'] ==1){
                $channelInfo = DI()->notorm->channel_account->where("id = '{$value['account_channel_id']}'")->fetchOne();
                $result[$key]['name'] = $channelInfo['name'];
                $result[$key]['reception_name'] = $channelInfo['reception_name'];
            }else{
                $channelInfo = DI()->notorm->offlinepay->where("id = '{$value['account_channel_id']}'")->fetchOne();
                $result[$key]['channel_name'] = $channelInfo['name'];
                $result[$key]['reception_name'] = $channelInfo['reception_name'];
            }
            if ($value['type'] == 3){
                $rateInfo = getRateList($value['tenant_id'])[$value['currency_code']];
                $result[$key]['name'] = '后台手动充值';
                $result[$key]['reception_name'] = '后台手动充值';
                $result[$key]['coin_name'] = $rateInfo['name'];
                $result[$key]['coin_code'] = $value['currency_code'];
            }else{
                $channel= DI()->notorm->channel->where("id = '{$value['channel_id']}'")->fetchOne();
                $coinInfo = DI()->notorm->rate->where("id = '{$channel['coin_id']}'")->fetchOne();
                $result[$key]['coin_name'] = $coinInfo['name'];
                $result[$key]['coin_code'] = $coinInfo['code'];
            }
            $result[$key]['addtime']  = date('Y-m-d H:i:s',$value['addtime']);
            $result[$key]['money'] = floatval($value['money']);
            $result[$key]['rnb_money'] = floatval($value['rnb_money']);
            $result[$key]['coin'] = floatval($value['coin']);
            $result[$key]['actual_money'] = floatval($value['actual_money']);
        }
        return $result;
    }
    public function chargePaynew($uid,$channel_id,$amount,$game_tenant_id){
        $order_id = generater();
        try {
            beginTransaction();
            $userInfo=getUserInfo($uid);
            $order_data = array(
                'order_id'=>$order_id,
                'uid'=>$uid,
                'channel_id'=>$channel_id,
                'amount'=>$amount,
                'game_tenant_id'=>$game_tenant_id,
                'addtime'=>time(),
                'status'=>2,
                'type'=>1,
            );
            $result= DI()->notorm->charge_order->insert($order_data);


            $tenantId=$userInfo['tenant_id'];
            $data=array(
                "type"=>'income',
                "action"=>'charge',
                "uid"=>$uid,
                'user_login' => $userInfo['user_login'],
                "money"=>$amount,
                "addtime"=>time(),
                "uptime"=>time(),
                "tenant_id"=>$tenantId,
                'order_id'=>$order_id,
                "pre_balance" => floatval($userInfo['coin']),
                "totalcoin" => $amount,
                "after_balance" => floatval(bcadd($userInfo['coin'], $amount,4)),
            );

            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($data);
            //更新余额
            DI()->notorm->users
                ->where('id = ? ', $uid)
                ->update(array(
                    'coin' => new NotORM_Literal("coin + {$amount}"),
            ));
            delUserInfoCache($uid);
            if(!$result){
                rollbackTransaction();
                return 1001;
            }
            else{
                commitTransaction();
               return array( "uid" =>$uid, "order_id" => $order_id,'amount'=>$amount,'status'=>1);
            }
        }catch (\Exception $e){
            return 1002;
        }


    }
}


