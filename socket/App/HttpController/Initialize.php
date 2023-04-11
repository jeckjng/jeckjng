<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Mysqli\Config;
use EasySwoole\Mysqli\Mysqli;

class Initialize extends Controller
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

    /*
    * 判断是否为json
    * */
    static public function is_json($string) {
        if(is_string($string)){
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }
        return false;
    }

}