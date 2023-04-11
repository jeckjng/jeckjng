<?php

use api\Common\CustRedis;

class Cache_LikeConfig extends PhalApi_Model_NotORM {

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
    public function getLikeConfigInfo($tenant_id, $id = null){
        $tenant_id = intval($tenant_id);
        $cachekey = 'like_config_'.$tenant_id;
        $info = CustRedis::getInstance()->get($cachekey);
        if(!$info){
            $where_id = $id ? ' and id = '.intval($id) : '';
            $info = DI()->notorm->like_config->where('tenant_id = ? '.$where_id, $tenant_id)->fetchOne();
            if($info){
                CustRedis::getInstance()->set($cachekey, json_encode($info), 60*60*24*7);
            }
        }else{
            $info = json_decode($info, true);
        }
        return $info;
    }

}
