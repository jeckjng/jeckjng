<?php

class Domain_Charge {
	public function getOrderId($changeid,$orderinfo) {
		$rs = array();

		$model = new Model_Charge();
		$rs = $model->getOrderId($changeid,$orderinfo);

		return $rs;
	}

	public  function getChargeChannel($uid,$reg_key,$reg_url,$vip_id,$game_tenant_id){

        $model = new Model_Charge();
        $rs = $model->getChargeChannel($uid,$reg_key,$reg_url,$vip_id,$game_tenant_id);

        return $rs;
    }
    public  function getChargeAccountChannel($uid,$game_tenant_id,$channel_id,$amount,$type){
        $model = new Model_ChannelAccount();
        $rs = $model->getChargeAccountChannel($uid,$game_tenant_id,$channel_id,$amount,$type);
        return $rs;
    }
    public  function chargePay($uid,$account_channel_id,$amount,$game_tenant_id,$vip_id){
        $model = new Model_ChannelAccount();
        $rs = $model->chargePay($uid,$account_channel_id,$amount,$game_tenant_id,$vip_id);
        return $rs;
    }
    public  function chargePaynew($uid,$channel_id,$amount,$game_tenant_id){
        $model = new Model_Charge();
        $rs = $model->chargePaynew($uid,$channel_id,$amount,$game_tenant_id);
        return $rs;
    }
    
    public  function notify($data){
        logapi(['回调数据'=>$data], '【回调数据】');
        $channelModel = new Model_ChannelAccount();
        $result = $channelModel->notify($data);
      
        if ($result['code'] == 1){
            $result['message']="succ";
        }else{
            $result['message'];
        }
        return $result['message'];
      /*  $model = new Model_Charge();
        $rs = $model->findOrder($data);
        $channelModel = new Model_ChannelAccount();
        $result = $channelModel->notify($rs,$data);
        if ($result['code'] == 1){
           $model->updateOrder($rs,$result['data']);
        }else{
            $result['message'];
        }
        return $result['message'];*/
    }

    public  function getAccountChannel($uid,$channel_id,$reg_key,$reg_url,$vip_id,$game_tenant_id){

        $model = new Model_ChannelAccount();
        $rs = $model->getAccountChannel($uid,$channel_id,$reg_key,$reg_url,$vip_id,$game_tenant_id);
        return $rs;
    }

    public function orderList($uid,$status,$p){
        $model = new Model_Charge();
        $rs = $model->orderList($uid,$status,$p);
        return $rs;
    }


    public  function offlinpay($uid,$account_channel_id,$amount,$game_tenant_id,$user_real_name,$img,$vip_id){
        $model = new Model_ChannelAccount();
        $rs = $model->offlinpay($uid,$account_channel_id,$amount,$game_tenant_id,$user_real_name,$img,$vip_id);
        return $rs;
    }
}
