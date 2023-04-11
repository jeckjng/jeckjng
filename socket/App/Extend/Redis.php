<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 2021/1/30
 * Time: 0:23
 */

namespace App\Extend;

class Redis
{
    private static $instance;


    /**
     *  单列模式实列化对象
     **/
    static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    static public function clientRedis()
    {
        return $redis = new \EasySwoole\Redis\Redis(new \EasySwoole\Redis\Config\RedisConfig([
            'host' => '10.0.126.128',
            'port' => '6380',
            'auth' => '123456',
            'db' => 1,
            'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_NONE
        ]));
    }
}