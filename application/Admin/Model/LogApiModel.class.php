<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class LogApiModel extends AdminModelBaseModel {

    public $table_name = 'log_api';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function add($ct='', $remark='', $tenant_id = 0){
        if(!$ct && !$remark){
            return 'ct remark 为空';
        }
        $ct = in_array(gettype($ct),['array','object']) ? json_encode($ct,JSON_UNESCAPED_UNICODE) : $ct;
        $remark = in_array(gettype($remark),['array','object']) ? json_encode($remark,JSON_UNESCAPED_UNICODE) : $remark;
        $logdata = array(
            'service' => CONTROLLER_NAME.'.'.ACTION_NAME,
            'root_url' => get_protocal().'://'.$_SERVER['HTTP_HOST'],
            'uri' => $_SERVER['REQUEST_URI'],
            'ct' => $ct,
            'remark' => $remark,
            'ip' => get_client_ip(),
            'method' => $_SERVER['REQUEST_METHOD'],
            'tenant_id' => intval($tenant_id),
            'ctime' => time(),
        );
        $result = M($this->table_name)->add($logdata);
        return $result;
    }

}
