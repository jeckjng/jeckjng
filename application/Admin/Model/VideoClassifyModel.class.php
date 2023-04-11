<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class VideoClassifyModel extends AdminModelBaseModel {

    public $table_name = 'video_classify';

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

    public function getShortVideoClassifyList($tenant_id){
        $data = M($this->table_name)->where(['tenant_id' => intval($tenant_id)])->order("sort asc, id asc")->select();
        return $data;
    }


}
