<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_Coinrecord extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function addCoinrecord($param = array()) {
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
            'order_id' => isset($param['order_id']) ? trim($param['order_id']) : '',   // 代发(采购)订单号
            'shop_order_no' => isset($param['shop_order_no']) ? trim($param['shop_order_no']) : '',   // 商城订单号
        );

        $res = DI()->notorm->users_coinrecord->insert($data);
        return $res;
    }


}