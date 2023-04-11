<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class UsersModel extends AdminModelBaseModel {

    public $table_name = 'users';
    public $effect_user_types = array(2,5,6,7,8);
    public $all_user_types = array(2,3,4,5,6,7,8);

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

    public function getUserInfoWithIdAndTid($uid, $tenant_id = null, $field = '*'){
        if($tenant_id){
            $result = M($this->table_name)->field($field)->where(['id' => intval($uid), 'tenant_id' => intval($tenant_id)])->find();
        }else{
            $result = M($this->table_name)->field($field)->where(['id' => intval($uid)])->find();
        }
        return $result;
    }

    public function getUserInfoWithUserLoginAndTid($user_login, $tenant_id = null, $field = '*'){
        if($tenant_id){
            $result = M($this->table_name)->field($field)->where(['tenant_id' => intval($tenant_id), 'user_login' => trim($user_login)])->find();
        }else{
            $result = M($this->table_name)->field($field)->where(['user_login' => trim($user_login)])->find();
        }
        return $result;
    }

    public function getUserInfoWithIdOrAccountAndTid($uid, $user_login, $tenant_id, $field = '*'){
        $where = array();
        if($uid){
            $where['id'] = intval($uid);
        }
        if($user_login){
            $where['user_login'] = trim($user_login);
        }
        $where['tenant_id'] = intval($tenant_id);
        $result = M($this->table_name)->field($field)->where($where)->find();
        return $result;
    }

    public function getUserListLtId($id, $limit = 0, $field = '*', $user_type = [2,5,6,7,8]){
        if(!$limit){
            return [];
        }
        $result = M($this->table_name)->field($field)->where(['id' => ['lt', intval($id)], 'user_type'=>['in',$user_type]])->order('id desc')->limit($limit)->select();
        return $result;
    }

    public function getUserListGtId($id, $limit = 0, $field = '*', $user_type = [2,5,6,7,8], $tenant_id = 0){
        if(!$limit){
            return [];
        }
        $where = ['id' => ['gt', intval($id)], 'user_type'=>['in',$user_type]];
        if($tenant_id){
            $where['tenant_id'] = intval($tenant_id);
        }

        $result = M($this->table_name)->field($field)->where($where)->order('id asc')->limit($limit)->select();
        return $result;
    }

    public function updatePid($uid, $pid){
        if(!$uid){
            return false;
        }
        $update_data = array(
            'pid' => intval($pid),   // 上级用户id
        );
        $res = M($this->table_name)->where(['id'=>intval($uid)])->save($update_data);
        return $res;
    }

}
