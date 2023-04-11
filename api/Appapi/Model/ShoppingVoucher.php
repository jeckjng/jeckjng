<?php

class Model_ShoppingVoucher extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

	public function add($data = array()) {
        $data = array(
            'tenant_id' => intval($data['tenant_id']),   // 租户id
            'create_time' => time(),   // 创建时间
            'uid' => intval($data['uid']),   // 用户ID
            'user_login' => $data['user_login'],   // 用户ID
            'user_type' => intval($data['user_type']),   // 用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号
            'amount' => $data['amount'],   // 金额
            'status' => 1,   // 状态：1.未使用，2.已使用
            'datenum' => $data['datenum'],   // 批次
        );

        $res = DI()->notorm->shopping_voucher->insert($data);
        return $res;
    }

    public function updateStatusToUsedWithIds($tenant_id, $uid, $ids) {
        $ids = array_filter(explode(',', $ids));
        foreach ($ids as $key=>$val){
            if(empty($ids)){
                return 'id 为空';
            }
            DI()->notorm->shopping_voucher
                ->where('tenant_id = ? and uid = ? and id = ? and status = 1', intval($tenant_id),  intval($uid), intval($val))
                ->update([
                    'status' => 2,
                    'update_time' => time(),
                ]);
        }

        return true;
    }

    public function getOneShoppingVoucherInfo($tenant_id, $uid, $amount) {
        $info = DI()->notorm->shopping_voucher
            ->where('tenant_id = ? and uid = ? and status = 1 and amount > 0 and amount <= ?', intval($tenant_id),  intval($uid), floatval($amount))
            ->order('amount desc')
            ->fetchOne();
        return $info;
    }

    public function getShoppingVoucherList($uid) {
        $tenant_id = getTenantId();
        $list = DI()->notorm->shopping_voucher
            ->where('tenant_id = ? and uid = ? and status = 1', intval($tenant_id),  intval($uid))
            ->order('amount desc')
            ->fetchAll();

        $data = array();
        foreach ($list as $key=>$val){
            $temp = array(
                'id' => intval($val['id']),
                'amount' => floatval($val['amount']),
            );
            array_push($data, $temp);
        }

        return array('code' => 0, 'msg' => '', 'info' => $data);
    }


}
