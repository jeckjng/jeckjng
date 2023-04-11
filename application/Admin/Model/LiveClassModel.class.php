<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class LiveClassModel extends AdminModelBaseModel {

    public $table_name = 'live_class';

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

    public function getListWithTenantId($tenant_id, $limit = 10000, $field = '*'){
        $list = M($this->table_name)->field($field)->where([
            'tenant_id' => intval($tenant_id),
        ])->limit($limit)->order('id desc')->select();
        return $list;
    }

}
