<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 23:38
 */
class Model_Bankcard extends PhalApi_Model_NotORM
{
    public function bindingBank($data, $uid, $real_name, $bank_number){
        $rs=DI()->notorm->bank_card
            ->where("uid = ? and bank_number = ?", intval($uid), $bank_number)->fetchOne();
        if ($rs){
            return 1001;
        }
        $userBank = DI()->notorm->bank_card->where("uid = '{$data['uid']}'")->fetchOne();
        if ($userBank){
            if($userBank['real_name'] != $data['real_name']){
                return 1003;
            }
        }

        $config = getConfigPub();
        // 同一银行卡绑定限制
        if($config['bank_bind_limit_count'] > 0 && $config['bank_bind_limit_day'] > 0 ){
            $start_time = time() - $config['bank_bind_limit_day'] * 60*60*24;
            $end_time = time();
            $bank_card_bind_count = DI()->notorm->bank_card->where("bank_number = ? and addtime >= ? and addtime <= ? and uid != ?", $bank_number, $start_time, $end_time, intval($uid))->count();
            if(($bank_card_bind_count+1) > $config['bank_bind_limit_count']){
                return array('code' => 600, 'msg' => codemsg(600), 'info' => []);
            }
        }
        // 同一姓名绑定限制
        if($config['bank_realname_bind_limit_count'] > 0 && $config['bank_realname_bind_limit_day'] > 0 ){
            $start_time = time() - $config['bank_realname_bind_limit_day'] * 60*60*24;
            $end_time = time();
            $bank_real_name_bind_count = DI()->notorm->bank_card->where("real_name = ? and addtime >= ? and addtime <= ? and uid != ?", $real_name, $start_time, $end_time, intval($uid))->count();
            if(($bank_real_name_bind_count+1) > $config['bank_realname_bind_limit_day']){
                return array('code' => 601, 'msg' => codemsg(601), 'info' => []);
            }
        }
        // 同一会员绑定限制
        if($config['bank_bind_count_one_user'] > 0){
            $one_user_bind_count = DI()->notorm->bank_card->where("uid = ?", intval($uid))->count();
            if(($one_user_bind_count+1) > $config['bank_bind_count_one_user']){
                return array('code' => 622, 'msg' => codemsg(622), 'info' => []);
            }
        }

        $data['status'] = 1;
        $data['addtime'] = time();
        $rs=DI()->notorm->bank_card
            ->insert($data);

        if(!$rs) {
            return 1000;
        }
        return $rs;
    }

    public function  myBlanCard($uid,$game_tenant_id){

        $tenant_id =getTenantId();
        $res=DI()->notorm->bank_card
            ->where("uid = '{$uid}' and tenant_id ='{$tenant_id}'")
            ->fetchAll();

        return  $res;
    }
}
