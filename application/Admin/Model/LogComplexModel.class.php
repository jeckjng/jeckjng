<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class LogComplexModel extends AdminModelBaseModel {

    public $table_name = 'log_complex';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getLastSql(){
        $res = M($this->table_name)->getLastSql();
        return $res;
    }

    /**
     * 新增综合日志记录
     */
    public function add($ct, $remark, $type = 1, $tenant_id = 0, $uid = 0, $user_acount = '', $admin_id = 0, $admin_acount = '', $ip = '')
    {
        if(!$ct && !$remark){
            return 'ct remark 为空';
        }
        $ct = in_array(gettype($ct),['array','object']) ? json_encode($ct,JSON_UNESCAPED_UNICODE) : $ct;
        $remark = in_array(gettype($remark),['array','object']) ? json_encode($remark,JSON_UNESCAPED_UNICODE) : $remark;
        $ip = $ip ? $ip : get_client_ip();
        $logdata = array(
            'ct' => $ct,
            'remark' => $remark,
            'type' => intval($type),
            'tenant_id' => intval($tenant_id),
            'ctime' => time(),
            'uid' => intval($uid),
            'user_acount' => trim($user_acount),
            'admin_id' => intval($admin_id),
            'admin_acount' => trim($admin_acount),
            'ip' => trim($ip),
        );

        $result = M($this->table_name)->add($logdata);
        return $result;
    }

    /**
     * 删除综合日志记录
     */
    public function delete($where)
    {
        $result = M($this->table_name)->where($where)->delete();
        return $result;
    }

}
