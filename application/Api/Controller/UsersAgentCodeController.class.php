<?php
namespace Api\Controller;
use Admin\Model\UsersAgentCodeModel;
use Admin\Model\UsersModel;
use Think\Controller;
use Api\Controller\AdminApiBaseController;

class UsersAgentCodeController extends AdminApiBaseController {

    public function __construct(){
        parent::__construct();
    }

    /*
    * 移除邀请码表不存在的用户数据
    * */
    public function remove_invitation_if_not_exist(){
        $redis = connectionRedis();
        $cache_key = 'remove_invitation_if_not_exist';
        $last_uid = $redis->get($cache_key);
        $last_uid = $last_uid && $last_uid >= 0 ? $last_uid : 0;
        $list = UsersAgentCodeModel::getInstance()->getAgentCodeListGtUid($last_uid,10000);
        $data['last_id'] = $last_uid;
        $data['getLastSql'] = UsersAgentCodeModel::getInstance()->getLastSql();
        $data['act_getLastSql'] = [];
        $last_uid = count($list) > 0 ? end($list)['uid'] : $last_uid;
        $redis->set($cache_key, $last_uid, 60*5);
        $count = 0;
        foreach ($list as $key=>$val){
            if($val['code'] == ''){
                UsersAgentCodeModel::getInstance()->deleteWithUid($val['uid']);
                $act_getLastSql = UsersAgentCodeModel::getInstance()->getLastSql();
                array_push($data['act_getLastSql'], $act_getLastSql);
                $count++;
                continue;
            }
            $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($val['uid'], null,'id,tenant_id');
            if(!isset($user_info['id']) || !$user_info['id'] || !isset($user_info['tenant_id'])  || !$user_info['tenant_id']){
                UsersAgentCodeModel::getInstance()->deleteWithUid($val['uid']);
                $act_getLastSql = UsersAgentCodeModel::getInstance()->getLastSql();
                array_push($data['act_getLastSql'], $act_getLastSql);
                $count++;
            }
        }
        $data['count'] = $count;
        $this->out_put($data, 20000,'success method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($param));
    }

    /*
     * 用户不在邀请码表则新增
     * */
    public function add_invitation_if_user_not_exist(){
        $param = $_REQUEST;
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : 0;

        $redis = connectionRedis();
        $cache_key = 'add_invitation_if_user_not_exist';
        $last_id = $redis->get($cache_key);
        $last_id = $last_id && $last_id >= 0 ? $last_id : 0;
        $list = UsersModel::getInstance()->getUserListGtId($last_id,10000,'id,tenant_id', [2,5,6,7,8], $tenant_id);
        $data['last_id'] = $last_id;
        $data['getLastSql'] = UsersModel::getInstance()->getLastSql();
        $data['act_getLastSql'] = [];
        $last_id = count($list) > 0 ? end($list)['id'] : $last_id;
        $redis->set($cache_key, $last_id, 60*5);
        $count = 0;
        foreach ($list as $key=>$val){
            $user_agent_code_info = UsersAgentCodeModel::getInstance()->getUserAgentCodeInfoWithUid($val['id'], null,'uid,code,tenant_id');
            if(!$user_agent_code_info){
                $code = createCode();
                UsersAgentCodeModel::getInstance()->add(array(
                    'uid' => intval($val['id']),   // 用户id
                    'code' => trim($code),   // 	用户名
                    'tenant_id' => intval($val['tenant_id']),   // 租户id
                ));
                $act_getLastSql = UsersAgentCodeModel::getInstance()->getLastSql();
                array_push($data['act_getLastSql'], $act_getLastSql);
                $count++;
            }else if($user_agent_code_info['tenant_id'] == '0' && $val['tenant_id']){
                UsersAgentCodeModel::getInstance()->update($val['id'], array(
                    'tenant_id' => intval($val['tenant_id']),   // 租户id
                ));
                $act_getLastSql = UsersAgentCodeModel::getInstance()->getLastSql();
                array_push($data['act_getLastSql'], $act_getLastSql);
                $count++;
            }
        }
        $data['count'] = $count;
        $this->out_put($data, 20000,'success method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($param));
    }

}
