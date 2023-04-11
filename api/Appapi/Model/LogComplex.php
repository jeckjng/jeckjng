<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_LogComplex extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
     * 综合错误日志记录
     * $type 类型：1.默认，2.红包	, 300.点赞
     * */
    public function add($ct, $remark, $type = 1, $tenant_id = 0, $uid = 0, $user_acount = '', $admin_id = 0, $admin_acount = '') {

        $ct = in_array(gettype($ct),['array','object']) ? json_encode($ct,JSON_UNESCAPED_UNICODE) : $ct;
        $remark = in_array(gettype($remark),['array','object']) ? json_encode($remark,JSON_UNESCAPED_UNICODE) : $remark;

        $data = array(
            'ct' => $ct,
            'remark' => $remark,
            'type' => intval($type),
            'tenant_id' => intval($tenant_id),
            'ctime' => time(),
            'uid' => intval($uid),
            'user_acount' => trim($user_acount),
            'admin_id' => intval($admin_id),
            'admin_acount' => trim($admin_acount),
            'ip' => trim(get_client_ip()),
        );

        $res = DI()->notorm->log_complex->insert($data);
        return $res;
    }


}