<?php
/**
 * Created by PhpStorm.
 * User: 哎哟哎哟
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_Usdt {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public  function bindingUsdtAddress($uid, $address, $network_type, $qrcode){
        $rs = Model_Usdt::getInstance()->bindingUsdtAddress($uid, $address, $network_type, $qrcode);
        return $rs;
    }
    public  function myUsdtAddress($uid){
        $rs = Model_Usdt::getInstance()->myUsdtAddress($uid);
        return $rs;
    }

}
