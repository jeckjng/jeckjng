<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class NobleModel extends AdminModelBaseModel {

    public $table_name = 'noble';

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
        $list = M($this->table_name)->field('id,medal,knighthoodcard,special_effect_swf,avatar_frame,exclu_car_nobleicon')->select();
        foreach ($list as $key=>$val){
            $update_data = array();
            if(strpos($val['medal'],'http') !== false){
                $medal = str_domain_replace($val['medal']);
                if($val['medal'] != $medal && $medal){
                    $update_data['medal'] = $medal;
                }
            }
            if(strpos($val['knighthoodcard'],'http') !== false){
                $knighthoodcard = str_domain_replace($val['knighthoodcard']);
                if($val['knighthoodcard'] != $knighthoodcard && $knighthoodcard){
                    $update_data['knighthoodcard'] = $knighthoodcard;
                }
            }
            if(strpos($val['special_effect_swf'],'http') !== false){
                $special_effect_swf = str_domain_replace($val['special_effect_swf']);
                if($val['special_effect_swf'] != $special_effect_swf && $special_effect_swf){
                    $update_data['special_effect_swf'] = $special_effect_swf;
                }
            }
            if(strpos($val['avatar_frame'],'http') !== false){
                $avatar_frame = str_domain_replace($val['avatar_frame']);
                if($val['avatar_frame'] != $avatar_frame && $avatar_frame){
                    $update_data['avatar_frame'] = $avatar_frame;
                }
            }
            if(strpos($val['exclu_car_nobleicon'],'http') !== false){
                $exclu_car_nobleicon = str_domain_replace($val['exclu_car_nobleicon']);
                if($val['exclu_car_nobleicon'] != $exclu_car_nobleicon && $exclu_car_nobleicon){
                    $update_data['exclu_car_nobleicon'] = $exclu_car_nobleicon;
                }
            }
            if(!empty($update_data)){
                $res = M($this->table_name)->where(['id'=>$val['id']])->save($update_data);
            }
        }
        return true;
    }


}
