<?php

use api\Common\CustRedis;

class Cache_LiveingSet extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 根据租户id获取直播间设置列表信息
    * */
    public function getLiveingSetListCache($tenant_id){
        $cachekey = "liveing_set_list_".$tenant_id;
        $list = CustRedis::getInstance()->get($cachekey);
        if(!$list){
            $list = DI()->notorm->liveing_set->where('tenant_id = ?', $tenant_id)->fetchAll();
            CustRedis::getInstance()->set($cachekey, json_encode($list), 60*60*24*7);
        }else{
            $list = json_decode($list, true);
        }
        return $list;
    }


}
