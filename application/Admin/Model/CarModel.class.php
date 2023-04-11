<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class CarModel extends AdminModelBaseModel {

    public $table_name = 'car';

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

    public function deleteWithUid($uid){
        $res = M($this->table_name)->where(['uid'=>$uid])->delete();
        return $res;
    }

    public function updateFileUrlDomain(){
        $list = M($this->table_name)->field('id,thumb,swf')->select();
        foreach ($list as $key=>$val){
            $update_data = array();
            if(strpos($val['thumb'],'http') !== false){
                $thumb = str_domain_replace($val['thumb']);
                if($val['thumb'] != $thumb && $thumb){
                    $update_data['thumb'] = $thumb;
                }
            }
            if(strpos($val['swf'],'http') !== false){
                $swf = str_domain_replace($val['swf']);
                if($val['swf'] != $swf && $swf){
                    $update_data['swf'] = $swf;
                }
            }
            if(!empty($update_data)){
                $res = M($this->table_name)->where(['id'=>$val['id']])->save($update_data);
            }
        }
        return true;
    }


}
