<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class UsersAgentModel extends AdminModelBaseModel {

    public $table_name = 'users_agent';

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
            'user_login' => trim($data['user_login']),   // 	用户名
            'one_uid' => isset($data['one_uid']) ? intval($data['one_uid']) : 0,   // 上级用户id
            'two_uid' => isset($data['two_uid']) ? intval($data['two_uid']) : 0,   // 	上上级id
            'three_uid' => isset($data['three_uid']) ? intval($data['three_uid']) : 0,   // 上上上级id
            'four_uid' => isset($data['four_uid']) ? intval($data['four_uid']) : 0,   // 上上上上级id
            'five_uid' => isset($data['five_uid']) ? intval($data['five_uid']) : 0,   // 上上上上上级id
            'addtime' => time(),   // 时间
            'tenant_id' => intval($data['tenant_id']),   // 租户id
            'user_type' => intval($data['user_type']),   // 用户类型：用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号
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
            'user_login' => trim($data['user_login']),   // 	用户名
            'one_uid' => isset($data['one_uid']) ? intval($data['one_uid']) : 0,   // 上级用户id
            'two_uid' => isset($data['two_uid']) ? intval($data['two_uid']) : 0,   // 	上上级id
            'three_uid' => isset($data['three_uid']) ? intval($data['three_uid']) : 0,   // 上上上级id
            'four_uid' => isset($data['four_uid']) ? intval($data['four_uid']) : 0,   // 上上上上级id
            'five_uid' => isset($data['five_uid']) ? intval($data['five_uid']) : 0,   // 上上上上上级id
            'tenant_id' => intval($data['tenant_id']),   // 租户id
            'user_type' => intval($data['user_type']),   // 用户类型：用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号
        );
        $res = M($this->table_name)->where(['uid'=>intval($uid)])->save($update_data);
        return $res;
    }

    public function getWithUid($uid){
        $res = M($this->table_name)->where(['uid'=>intval($uid)])->find();
        return $res;
    }

    public function updateUserTypeWithUid($uid, $UserType){
        $res = M($this->table_name)->where(['uid'=>intval($uid)])->save(['user_type'=>intval($UserType)]);
        return $res;
    }

    public function autoUpdateUserType(){
        $model = M($this->table_name);
        $redis = connectionRedis();
        $list = $model->field('id,uid,user_type')->where(['user_type'=>0])->limit(1000)->order('id desc')->select();
        $count = 0;
        foreach ($list as $key=>$val){
            if($val['user_type'] == '0' && !$redis->hGet('user_type_is_empty',$val['uid'])){
                $user_info = getUserInfo($val['uid']);
                if(isset($user_info['user_type']) && $user_info['user_type'] != '0'){
                    $model->where(['id'=>$val['id']])->save(['user_type'=>$user_info['user_type']]);
                    $count++;
                }else{
                    $redis->hSet('user_type_is_empty', $val['uid'], 1);
                    $redis->expire('user_type_is_empty',60*60*7);
                }
            }
        }
        return $count;
    }

    public function getAgentListGtId($id, $limit = 0, $field = '*'){
        if(!$limit){
            return [];
        }
        $result = M($this->table_name)->field($field)->where(['id' => ['gt', intval($id)]])->order('id asc')->limit($limit)->select();
        return $result;
    }

    public function getUserAgentInfoWithUidAndTid($uid, $tenant_id = null, $field = '*'){
        if($tenant_id){
            $result = M($this->table_name)->field($field)->where(['uid' => intval($uid), 'tenant_id' => intval($tenant_id)])->find();
        }else{
            $result = M($this->table_name)->field($field)->where(['uid' => intval($uid)])->find();
        }
        return $result;
    }

    public function getAllSuperiorUid($tenant_id, $uid, $data=array()){
        $sub = M($this->table_name)->where( ['tenant_id'=>intval($tenant_id), 'uid'=>intval($uid)])->field("one_uid")->select();
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
        $sub = M($this->table_name)->where(['tenant_id'=>intval($tenant_id), 'one_uid'=>intval($puid)])->field("uid")->select();
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
