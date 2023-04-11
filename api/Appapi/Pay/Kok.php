<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/6/12
 * Time: 13:30
 */
class  Pay_Kok{
    public  function  pay($uid,$data,$amount,$game_tenant_id){
        $config=getConfigPub();
        logapi(['入口kok信息'=>$uid], '【入口kok信息】');
        $channel = DI()->notorm->channel->where("id = '{$data['channel_id']}'")->fetchOne();
        $coinInfo = DI()->notorm->rate->where(" id = '{$channel['coin_id']}'")->fetchOne();
        $user_info = getUserInfo($uid);
        $dataArray['uid'] = $uid;
        $dataArray['user_login'] = $user_info['user_login'];
        $dataArray['user_type'] = $user_info['user_type'];
        $dataArray['money'] = $amount;
        $dataArray['currency_code'] = $coinInfo['code'];
        $dataArray['rnb_money'] = bcdiv($amount,$coinInfo['rate'],4);
        $dataArray['orderno'] = $this->getOrderid();
        $dataArray['actual_money'] =  $dataArray['rnb_money'];
        //$dataArray['actual_money'] =bcsub( $dataArray['rnb_money'], bcmul( $dataArray['rnb_money'],bcdiv($data['service_charge'],100,4),4),4);
        $dataArray['coin'] =  $dataArray['rnb_money'];
        $dataArray['upstream_service_money'] = bcmul( $dataArray['rnb_money'],bcdiv($data['service_charge'],100,4),4);
        $dataArray['upstream_service_rate'] = $data['service_charge'];
        $dataArray['rate'] = $coinInfo['rate'];
        $dataArray['channel_id'] = $data['channel_id'];
        $dataArray['account_channel_id'] = $data['id'];
        $dataArray['type'] = 1;
        $dataArray['status'] = 1;
        $dataArray['addtime'] = time();
        $dataArray['tenant_id'] = getTenantId();;
     /*   if ($dataArray['coin']< 1){
            return array('status' => 0,'msg'=>'充值金额过小');
        }*/
        $chargeModel = new \Model_Charge();

        $native = array(
            "pay_memberid" => $data['mer_id'],
            "pay_orderid" =>  $dataArray['orderno'],
            "pay_amount" => $amount,
            "pay_applydate" => date('Y-m-d h:i:s'),
            "pay_bankcode" => $data['account_code'],
            "pay_notifyurl" => $data['notify_ip'],
            "pay_callbackurl" =>$data['callbackurl'],

        );
        ksort($native);
        $md5str ="";
        foreach ($native as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }

        $Md5key = $data['secret_key'];
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        $native["pay_md5sign"] = $sign;
        $native['format'] = "json";
        $native['pay_productname'] ='VIP基础服务';
     
        logapi(['请求kok信息'=>$native], '【请求kok信息】');

        $url = $data['url'];
        $info = self::curl_post($url,$native);;

        $info = json_decode($info,true);
        logapi(['返回kok信息'=>$info,], '【返回kok信息】');
        if ($info['status'] =='success'){
            $chargeModel->addOrder($dataArray);
            return array('status' => 1,'msg'=>$info['data']['payurl']);

        }else{
            return array('status' => 0,'msg'=>$info['msg']);
        }

    }
    protected function createSign($apikey, $data)
    {
        ksort($data);
        $sign_str = "";
        foreach ($data as $key => $val) {
            if (!empty($val) && $key != 'attach' && $key != 'callbackUrl') {
                $sign_str .= $key . "=" . $val . "&";
            }
        }
        $sign = strtoupper(md5($sign_str . "key=" . $apikey));
        return $sign;
    }
    public static function curl_post($url,$postData){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        // 执行后不直接打印出来
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        // 设置请求方式为post
        curl_setopt($ch,CURLOPT_POST,true);
        // post的变量
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
        // 请求头，可以传数组
        if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        }
        curl_setopt($ch, CURLOPT_HEADER,0);
        // 跳过证书检查
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        // 不从证书中检查SSL加密算法是否存在
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        $output=curl_exec($ch);
        curl_close($ch);

        return $output;
    }
    public  function notify($rs,$data,$channel_account_info){ // 第一参数为 订单数据 第二个参数为回调数据 第三个参数为渠道数据

        $update = array(
            'data' => array(),
            'message' => 'fial',
            'code' => 0
        );
        if ($rs['status'] != 1){
            $update['message'] = 'order already pay';
            return   $update;
        }
        $channel = DI()->notorm->channel->where("id = '{$channel_account_info['channel_id']}'")->fetchOne();
        $coinInfo = DI()->notorm->rate->where(" id = '{$channel['coin_id']}'")->fetchOne();

        $returnArray = array( // 返回字段
            "memberid" => $data["memberid"], // 商户ID
            "orderid" =>  $data["orderid"], // 订单号
            "amount" =>  $data["amount"], // 交易金额
            "datetime" =>  $data["datetime"], // 交易时间
            "transaction_id" =>  $data["transaction_id"], // 支付流水号
            "returncode" => $data["returncode"],
        );
        $md5key = $channel_account_info['secret_key'];
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $data['uid'] = $rs['uid'];


        $sign = strtoupper(md5($md5str . "key=" . $md5key));
        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["returncode"] == "00") {
                $model = new Model_Charge();
                $model->addTradeNo($data);

                exit("ok"); 
            }
        }else{
            $update = array(
                'data' =>[],
                'message' => 'fail',
                'code' => 1001
            );

            return $update;
        }

    }
    /* 获取订单号 */
    protected function getOrderid(){
        $orderid=date('YmdHis').rand(100000,999999);
        return $orderid;
    }

}