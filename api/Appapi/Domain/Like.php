<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_Like
{
    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getLikeConfig($uid)
    {
        $rs = Model_Like::getInstance()->getLikeConfig($uid);
        return $rs;
    }

    public function payLikeDeposit($uid, $id)
    {
        $rs = Model_Like::getInstance()->payLikeDeposit($uid, $id);
        return $rs;
    }

    public function refundLikeDeposit($uid)
    {
        $rs = Model_Like::getInstance()->refundLikeDeposit($uid);
        return $rs;
    }

}