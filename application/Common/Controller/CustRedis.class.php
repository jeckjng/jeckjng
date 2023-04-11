<?php

/**
 * 后台Controller
 */
namespace Common\Controller;

class CustRedis{

    protected static $instance;

    static function getInstance()
    {
        if(!isset(self::$instance)){
            try {
                $redis = new \Redis();
                $redis -> pconnect(C('REDIS_HOST'),C('REDIS_PORT'));
                $redis -> auth(C('REDIS_AUTH'));
                $redis ->select(C('REDIS_DBINDEX'));
                self::$instance = $redis;
            }catch (\Exception $e){
                echo '连接Redis失败: '.$e->getMessage()."\n";
            }
        }
        return self::$instance;
    }

}