<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_VdeoClassify extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getShortVideoClassifyList($tenant_id){
        $data = DI()->notorm->video_classify->where('type =1 and tenant_id = ?', intval($tenant_id))->order("sort asc, id asc")->fetchAll();
        return $data;
    }


}