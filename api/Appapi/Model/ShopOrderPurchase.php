<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_ShopOrderPurchase extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    // 生成采购订单金额记录
    public function add($tenant_id, $shop_order_id, $goods_purchase_price, $cg_order_id, $shop_order_no, $cg_order_no){
        $exist = DI()->notorm->shop_order_purchase->where('tenant_id = ? and shop_order_id=?', intval($tenant_id), intval($shop_order_id))->fetchOne();
        if($exist){
            return '已存在';
        }
        $data = [
            'tenant_id' => intval($tenant_id),
            'shop_order_id' => intval($shop_order_id),
            'goods_purchase_price' => floatval($goods_purchase_price),
            'cg_order_id' => intval($cg_order_id),
            'shop_order_no' => $shop_order_no,
            'cg_order_no' => $cg_order_no,
            'create_time' => time(),
        ];
        $result = DI()->notorm->shop_order_purchase->insert($data);
        return $result;
    }

    // 获取采购订单金额记录信息
    public function getInfo($tenant_id, $shop_order_id){
        $result = DI()->notorm->shop_order_purchase->where('tenant_id = ? and shop_order_id=?', intval($tenant_id), intval($shop_order_id))->fetchOne();
        return $result;
    }


}