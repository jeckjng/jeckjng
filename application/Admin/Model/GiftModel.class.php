<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class GiftModel extends AdminModelBaseModel {

    public $table_name = 'gift';

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
        $list = M($this->table_name)->field('id,gifticon,swf')->select();
        foreach ($list as $key=>$val){
            $update_data = array();
            if(strpos($val['gifticon'],'http') !== false){
                $gifticon = str_domain_replace($val['gifticon']);
                if($val['gifticon'] != $gifticon && $gifticon){
                    $update_data['gifticon'] = $gifticon;
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
