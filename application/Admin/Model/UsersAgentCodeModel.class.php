<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class UsersAgentCodeModel extends AdminModelBaseModel {

    public $table_name = 'users_agent_code';

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

    public function add($data = array()){
        $add_data = array(
            'uid' => intval($data['uid']),   // 用户id
            'code' => trim($data['code']),   // 	用户名
            'tenant_id' => intval($data['tenant_id']),   // 租户id
        );
        $find_result = M($this->table_name)->where(['uid'=>intval($data['uid'])])->find();
        if(!$find_result){
            $res = M($this->table_name)->add($add_data);
        }else{
            $res = '数据已存在：'.$data['uid'];
        }
        return $res;
    }

    public function update($uid, $data = array()){
        $update_data = array(
            'tenant_id' => intval($data['tenant_id']),   // 租户id
        );
        $res = M($this->table_name)->where(['uid'=>intval($uid)])->save($update_data);
        return $res;
    }

    public function getAgentCodeListGtUid($uid, $limit = 0, $field = '*'){
        if(!$limit){
            return [];
        }
        $result = M($this->table_name)->field($field)->where(['uid' => ['gt', intval($uid)]])->order('uid asc')->limit($limit)->select();
        return $result;
    }

    public function getUserAgentCodeInfoWithUid($uid, $tenant_id = null, $field = '*'){
        if($tenant_id){
            $result = M($this->table_name)->field($field)->where(['uid' => intval($uid), 'tenant_id' => intval($tenant_id)])->find();
        }else{
            $result = M($this->table_name)->field($field)->where(['uid' => intval($uid)])->find();
        }
        return $result;
    }

}
