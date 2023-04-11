<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/6/12
 * Time: 13:30
 */
class  Pay_Test{
    public  function  pay($uid,$data,$amount,$game_tenant_id){
        $config=getConfigPub();

        $channel = DI()->notorm->channel->where("id = '{$data['channel_id']}'")->fetchOne();
        $coinInfo = DI()->notorm->rate->where(" id = '{$channel['coin_id']}'")->fetchOne();
        $user_info = getUserInfo($uid);
        $dataArray['uid'] = $uid;
        $dataArray['user_login'] = $user_info['user_login'];
        $dataArray['user_type'] = $user_info['user_type'];
        $dataArray['money'] = $amount;
        $dataArray['currency_code'] = $coinInfo['code'];
        $dataArray['rnb_money'] = bcdiv($amount,$coinInfo['rate'],4);
        $dataArray['orderno'] = getOrderid($uid);
        $dataArray['actual_money'] =bcsub( $dataArray['rnb_money'], bcmul( $dataArray['rnb_money'],bcdiv($data['service_charge'],100,4),4),4);
        $dataArray['coin'] =  bcmul($dataArray['actual_money'],$config['money_rate'],4);
        $dataArray['upstream_service_money'] = bcmul( $dataArray['rnb_money'],bcdiv($data['service_charge'],100,4),4);
        $dataArray['upstream_service_rate'] = $data['service_charge'];
        $dataArray['rate'] = $coinInfo['rate'];
        $dataArray['channel_id'] = $data['channel_id'];
        $dataArray['account_channel_id'] = $data['id'];
        $dataArray['type'] = 1;
        $dataArray['status'] = 1;
        $dataArray['addtime'] = time();
        $dataArray['tenant_id'] = getTenantId();;
        if ($dataArray['coin']< 1){
            return array('status' => 0,'msg'=>'充值金额过小');
        }
        $chargeModel = new \Model_Charge();

        $native = array(
            'amount'        => bcmul($amount,1000,4), //厘
            'applyDate'    => date("Y-m-d H:i:s", $dataArray['addtime']),  //订单时间
            'channelCode'  => $data['account_code'], //支付编码
            'ip'            => $_SERVER["REMOTE_ADDR"],
            'merId'        => $data['mer_id'],//商户号
            'notifyUrl'    =>  $_SERVER["HTTP_HOST"].'/api/public/?service=Charge.notify',
            'orderId'      =>   $dataArray['orderno'],
            'currency'      => $coinInfo['code'],
        );
        ksort($native);
        $md5str = "";
        foreach ($native as $key => $val) {
            if($val != ''){
                $md5str = $md5str . $key . "=" . $val . "&";
            }

        }
        $sign = strtoupper(md5($md5str . "key=" . $data['secret_key']));
        $native["sign"] = $sign;
        $native["attach"] = $sign;
        $native["callbackUrl"] =  $_SERVER["HTTP_HOST"].'/api/public/?service=Charge.notify';
        $url = $data['url'];
        $info = self::curl_post($url,$native);;

        $info = json_decode($info,true);
        /*if ($info['code'] == 200){
            $dataArray['trade_no'] = $info['result']['orderid'];
        };*/
        if ($info['code'] ==200 ){
            $chargeModel->addOrder($dataArray);
            return array('status' => 1,'msg'=>$info['result']['url']);

        }else{
            return array('status' => 0,'msg'=>$info['result']['msg']);
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
            return   $update;
        }
        $channel = DI()->notorm->channel->where("id = '{$channel_account_info['channel_id']}'")->fetchOne();
        $coinInfo = DI()->notorm->rate->where(" id = '{$channel['coin_id']}'")->fetchOne();

        $native = array(
            'amount'        =>$data['amount'] , //厘
            'applyDate'    => $data['applyDate'],  //订单时间
            'channelCode'  => $channel_account_info['account_code'], //支付编码
            'merId'        => $channel_account_info['mer_id'],//商户号
            'orderId'      =>   $data['orderId'],
            'currency'      => $coinInfo['code'],
            'outTradeId'  => $data['outTradeId'],
            'returnCode' => $data['returnCode']
        );

        if ($data['actualAmount']){
            $native['actualAmount'] = $data['actualAmount'];
        }
        ksort($native);
        $md5str = "";
        foreach ($native as $key => $val) {
            if($val != ''){
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
        $sign = strtoupper(md5($md5str . "key=" . $channel_account_info['secret_key']));
        if ($rs['status'] == 1) { // 待处理状态
            if ($data['returnCode'] == 200 &&$sign == $data['sign'] ) {
                addTradeNo($data['orderId'],$data['outTradeId']);
                $update = array(
                    'data' => array('trade_no' => $data['outTradeId'], 'status' => 2, 'updatetime' => time()),
                    'message' => 'success',
                    'code' => 1
                );
            }

        }
        return $update;
    }
}