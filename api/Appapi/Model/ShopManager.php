<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_ShopManager extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    // 获取记录信息
    public function getInfo($tenant_id, $shop_order_id){
        $result = DI()->notorm->shop_manager->where('tenant_id = ? and shop_order_id=?', intval($tenant_id), intval($shop_order_id))->fetchOne();
        return $result;
    }


}