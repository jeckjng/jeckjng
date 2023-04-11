<?php
namespace Api\Controller;
use Admin\Model\UsersAgentModel;
use Admin\Model\UsersModel;
use Think\Controller;
use Api\Controller\AdminApiBaseController;

class UsersAgentController extends AdminApiBaseController {

    public function __construct(){
        parent::__construct();
    }

    /*
    * 移除代理表不存在的用户数据
    * */
    public function remove_agent_if_not_exist(){
        $param = $_REQUEST;

        $redis = connectionRedis();
        $cache_key = 'remove_agent_if_not_exist';
        $last_id = $redis->get($cache_key);
        $last_id = $last_id && $last_id >= 0 ? $last_id : 0;
        $list = UsersAgentModel::getInstance()->getAgentListGtId($last_id,10000,'id,uid');
        $data['last_id'] = $last_id;
        $data['getLastSql'] = UsersAgentModel::getInstance()->getLastSql();
        $data['act_getLastSql'] = [];
        $last_id = count($list) > 0 ? end($list)['id'] : $last_id;
        $redis->set($cache_key, $last_id, 60*5);
        $count = 0;
        foreach ($list as $key=>$val){
            $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($val['uid'], null,'id,tenant_id');
            if(!isset($user_info['id']) || !$user_info['id'] || !isset($user_info['tenant_id'])  || !$user_info['tenant_id']){
                UsersAgentModel::getInstance()->deleteWithUid($val['uid']);
                $act_getLastSql = UsersAgentModel::getInstance()->getLastSql();
                array_push($data['act_getLastSql'], $act_getLastSql);
                $count++;
            }
        }
        $data['count'] = $count;
        $this->out_put($data, 20000,'success method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($param));
    }

    /*
    * 用户不在代理表则新增
    * */
    public function add_agent_if_user_not_exist(){
        $param = $_REQUEST;
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : 0;

        $redis = connectionRedis();
        $cache_key = 'add_agent_if_user_not_exist';
        $last_id = $redis->get($cache_key);
        $last_id = $last_id && $last_id >= 0 ? $last_id : 0;
        $list = UsersModel::getInstance()->getUserListGtId($last_id,10000,'id,user_login,pid,user_type,tenant_id,create_time', [2,5,6,7,8], $tenant_id);
        $data['last_id'] = $last_id;
        $data['getLastSql'] = UsersModel::getInstance()->getLastSql();
        $data['act_getLastSql'] = [];
        $last_id = count($list) > 0 ? end($list)['id'] : $last_id;
        $redis->set($cache_key, $last_id, 60*5);
        $count = 0;
        foreach ($list as $key=>$val){
            $user_agent_info = UsersAgentModel::getInstance()->getUserAgentInfoWithUidAndTid($val['id'], null,'id,uid,one_uid,tenant_id');
            if(!$user_agent_info){
                $parent_user_agent_info = $val['pid'] ? UsersAgentModel::getInstance()->getWithUid($val['pid']) : [];
                UsersAgentModel::getInstance()->add(array(
                    'uid' => intval($val['id']),   // 用户id
                    'user_login' => trim($val['user_login']),   // 	用户名
                    'one_uid' => $val['pid'],   // 上级用户id
                    'two_uid' => isset($parent_user_agent_info['two_uid']) ? intval($parent_user_agent_info['one_uid']) : 0,   // 	上上级id
                    'three_uid' => isset($parent_user_agent_info['three_uid']) ? intval($parent_user_agent_info['two_uid']) : 0,   // 上上上级id
                    'four_uid' => isset($parent_user_agent_info['four_uid']) ? intval($parent_user_agent_info['three_uid']) : 0,   // 上上上上级id
                    'five_uid' => isset($parent_user_agent_info['five_uid']) ? intval($parent_user_agent_info['four_uid']) : 0,   // 上上上上上级id
                    'addtime' => strtotime($val['create_time']),   // 时间
                    'tenant_id' => intval($val['tenant_id']),   // 租户id
                    'user_type' => intval($val['user_type']),   // 用户类型：用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号
                ));
                $act_getLastSql = UsersAgentModel::getInstance()->getLastSql();
                array_push($data['act_getLastSql'], $act_getLastSql);
                $count++;
            }else if($user_agent_info['one_uid'] == '0' && $val['pid']){
                $parent_user_agent_info = $val['pid'] ? UsersAgentModel::getInstance()->getWithUid($val['pid']) : [];
                UsersAgentModel::getInstance()->update($val['id'], array(
                    'uid' => intval($val['id']),   // 用户id
                    'user_login' => trim($val['user_login']),   // 	用户名
                    'one_uid' => $val['pid'],   // 上级用户id
                    'two_uid' => isset($parent_user_agent_info['two_uid']) ? intval($parent_user_agent_info['one_uid']) : 0,   // 	上上级id
                    'three_uid' => isset($parent_user_agent_info['three_uid']) ? intval($parent_user_agent_info['two_uid']) : 0,   // 上上上级id
                    'four_uid' => isset($parent_user_agent_info['four_uid']) ? intval($parent_user_agent_info['three_uid']) : 0,   // 上上上上级id
                    'five_uid' => isset($parent_user_agent_info['five_uid']) ? intval($parent_user_agent_info['four_uid']) : 0,   // 上上上上上级id
                    'addtime' => strtotime($val['create_time']),   // 时间
                    'tenant_id' => intval($val['tenant_id']),   // 租户id
                    'user_type' => intval($val['user_type']),   // 用户类型：用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号
                ));
                $act_getLastSql = UsersAgentModel::getInstance()->getLastSql();
                array_push($data['act_getLastSql'], $act_getLastSql);
                $count++;
            }
        }
        $data['count'] = $count;
        $this->out_put($data, 20000,'success method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($param));
    }

}
