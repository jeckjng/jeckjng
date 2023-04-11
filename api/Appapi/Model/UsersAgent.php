<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_UsersAgent extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
     * 获取上一级用户id
     * */
    public function getSuperiorUid($tenant_id, $uid){
        $info = DI()->notorm->users_agent->where('tenant_id = ? and uid=?', intval($tenant_id), intval($uid))->select("one_uid")->fetchOne();
        return $info['one_uid'];
    }

    public function getAllSuperiorUid($tenant_id, $uid, $data=array()){
        $sub = DI()->notorm->users_agent->where('tenant_id = ? and uid=?', intval($tenant_id), intval($uid))->select("one_uid")->fetchAll();
        foreach ($sub as $key=>$val){
            if($val['one_uid']){
                array_push($data, intval($val['one_uid']));
            }
            $sub = $this->getAllSuperiorUid($tenant_id, $val['one_uid'], array());
            if(is_array($sub) && count($sub)>0){
                $data = array_merge($data, $sub);
            }
        }
        return $data;
    }

    public function getAllSubUid($tenant_id, $puid, $data=array()){
        $sub = DI()->notorm->users_agent->where('tenant_id = ? and one_uid=?', intval($tenant_id), intval($puid))->select("uid")->fetchAll();
        foreach ($sub as $key=>$val){
            if($val['uid']) {
                array_push($data, intval($val['uid']));
            }
            $sub = $this->getAllSubUid($tenant_id, $val['uid'], array());
            if(is_array($sub) && count($sub)>0){
                $data = array_merge($data,$sub);
            }
        }
        return $data;
    }


}