<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class UsersCoinrecordModel extends AdminModelBaseModel {

    public $table_name = 'users_coinrecord';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function addCoinrecord($param = array()){
        if(isset($param['uid']) && !isset($param['user_type'])){
            $user_info = getUserInfo($param['uid']);
            $param['user_type'] = $user_info['user_type'];
        }
        if(!isset($param['uid']) && isset($param['touid']) && !isset($param['user_type'])){
            $user_info = getUserInfo($param['touid']);
            $param['user_type'] = $user_info['user_type'];
        }

        $data = array(
            'type' => trim($param['type']),   // 收支类型
            'action' => trim($param['action']),   // 收支行为
            'uid' => isset($param['uid']) ? intval($param['uid']) : 0,   // 用户ID
            'user_login' => isset($param['user_login']) ? trim($param['user_login']) : '',   // 用户类型账号
            'user_type' => isset($param['user_type']) ? intval($param['user_type']) : 0,   // 用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号
            'touid' => isset($param['touid']) ? intval($param['touid']) : 0,   // 对方ID
            'giftid' => isset($param['giftid']) ? intval($param['giftid']) : 0,   // 行为对应ID
            'giftcount' => isset($param['giftcount']) ? intval($param['giftcount']) : 0,   // 数量
            "pre_balance" => isset($param['pre_balance']) ? floatval($param['pre_balance']) : 0, // 变动前余额
            'totalcoin' => isset($param['totalcoin']) ? floatval($param['totalcoin']) : 0,   // 总价
            "after_balance" => isset($param['after_balance']) ? floatval($param['after_balance']) : 0, // 变动后余额
            'showid' => isset($param['showid']) ? intval($param['showid']) : 0,   // 直播标识
            'addtime' => time(),   // 添加时间
            'game_banker' => isset($param['game_banker']) ? intval($param['game_banker']) : 0,   // 庄家ID
            'game_action' => isset($param['game_action']) ? intval($param['game_action']) : 0,   // 游戏类型
            'mark' => isset($param['mark']) ? intval($param['mark']) : 0,   // 标识，1表示热门礼物，2表示守护礼物
            'tenant_id' => isset($param['tenant_id']) ? intval($param['tenant_id']) : 0,   // 租户id
            'receive_tenant_id' => isset($param['receive_tenant_id']) ? intval($param['receive_tenant_id']) : 0,   // 接收方租户id
            'anthor_total' => isset($param['anthor_total']) ? floatval($param['anthor_total']) : 0,   // 主播分润
            'tenant_total' => isset($param['tenant_total']) ? floatval($param['tenant_total']) : 0,   // 主播所在平台分润
            'family_total' => isset($param['family_total']) ? floatval($param['family_total']) : 0,   // 家族分成
            'tenantuser_total' => isset($param['tenantuser_total']) ? floatval($param['tenantuser_total']) : 0,   // 消费者所属平台分润
            'playname' => isset($param['playname']) ? trim($param['playname']) : '',   // 彩票玩法类型
            'cd_ratio' => isset($param['cd_ratio']) ? trim($param['cd_ratio']) : '1:1',   // 金币砖石比例
            'familyhead_total' => isset($param['familyhead_total']) ? floatval($param['familyhead_total']) : 0,   // 家族长分成
            'is_withdrawable' => isset($param['is_withdrawable']) ? intval($param['is_withdrawable']) : 1,   // 1  可提现金额  2 不可提现(为转为可提现) 3（不可提现已转为可提现）
            'remark' => isset($param['remark']) ? trim($param['remark']) : '',   // 备注
            'order_id'=>isset($param['order_id']) ? trim($param['order_id']) : '',   // 订单号
        );

        $res = M($this->table_name)->add($data);
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

}
