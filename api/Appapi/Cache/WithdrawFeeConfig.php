<?php

use api\Common\CustRedis;

class Cache_WithdrawFeeConfig extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 根据用户id获取信息
    * */
    public function getWithdrawFeeConfigList($tenant_id){
        $tenant_id = intval($tenant_id);
        $cachekey = 'withdraw_fee_config_'.$tenant_id;
        $list = CustRedis::getInstance()->get($cachekey);
        if(!$list){
            $list = DI()->notorm->withdraw_fee_config->where('tenant_id=?', $tenant_id)->order('amount desc, id asc')->fetchAll();
            CustRedis::getInstance()->set($cachekey, json_encode($list), 60*60*24*7);
        }else{
            $list = json_decode($list, true);
            if(count($list) > 0){
                array_multisort(array_column($list, 'amount'), SORT_DESC, $list);
            }
        }
        return $list;
    }

}
