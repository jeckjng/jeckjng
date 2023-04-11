<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class LiveingLogModel extends AdminModelBaseModel {

    public $table_name = 'liveing_log';

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

    public function add($insert_data){
        $res = M($this->table_name)->add($insert_data);
        return $res;
    }


}
