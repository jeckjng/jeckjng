<?php

/**
 * 后台Controller
 */
namespace api\Common;

class Redis{

    protected static $instance;

    static function instance(...$args)
    {
        if(!isset(self::$instance)){
            try {
                $REDIS_HOST= DI()->config->get('app.REDIS_HOST');
                $REDIS_AUTH= DI()->config->get('app.REDIS_AUTH');
                $REDIS_PORT= DI()->config->get('app.REDIS_PORT');
                $REDIS_DBINDEX=DI()->config->get('app.REDIS_DBINDEX');
                $redis = new \Redis();
                $redis->pconnect($REDIS_HOST,$REDIS_PORT);
                $redis->auth($REDIS_AUTH);
                $redis->select($REDIS_DBINDEX);

                self::$instance = $redis;
            }catch (\Exception $e){
                echo '连接Redis失败: '.$e->getMessage()."\n";
            }
        }
        return self::$instance;
    }

}