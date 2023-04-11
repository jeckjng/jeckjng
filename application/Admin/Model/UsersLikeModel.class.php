<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class UsersLikeModel extends AdminModelBaseModel {

    public $table_name = 'users_like';

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


}
