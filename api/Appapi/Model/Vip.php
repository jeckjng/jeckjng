<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */

use api\Common\CustRedis;

class Model_Vip extends PhalApi_Model_NotORM {


    public function vipList() {

        $config  = getConfigPub();
        $tenantId  = getTenantId();
        if ($config['vip_model'] ==1){
            $list=DI()->notorm->vip->select('name')
                ->where("tenant_id  = {$tenantId}")
                ->group('name,orderno')
                ->order('orderno asc')
                ->fetchAll();
            $data = [];
            foreach ($list as $key => $value){
                $data[] = DI()->notorm->vip->where("name = '{$value['name']}' and coin > 0 and  tenant_id = {$tenantId} ")->fetchAll();
            }
            return $data;
        }else{
            $list=DI()->notorm->vip_grade
                ->where("status = 1  and tenant_id = {$tenantId}")
                ->order('vip_grade asc')
                ->fetchAll();
            return $list ;
        }

    }
    public  function buyVip($uid,$vip_id,$coin,$game_tenant_id){
        $config  = getConfigPub();
        $model = new Model_User();
        $rs = $model->getBaseInfo($uid);
        if ($rs['user_type'] == 4) {
            return 800;
        }
        $userModel = new Model_User();
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
        if($userInfo['user_type'] == 7){ // 测试账号，不能购买
            return array('code' => 402, 'msg' => codemsg('402'), 'info' => array('测试账号，不能购买'));
        }

        if ($config['vip_model'] ==1) { // 购买模式

            $vipInfo = DI()->notorm->vip->where("id = '{$vip_id}'")->fetchOne(); //购买的会员等级信息

            if ($coin != $vipInfo['coin']) {
                return 1001;
            }

            $userCoin = DI()->notorm->users->where('coin')->where("id = '{$uid}'")->fetchOne();
            if ($userCoin['coin'] * 100 < $coin * 100) {
                return 1002;
            }

            $data = array();
            /** 会员购买记录**/
            $userVip = DI()->notorm->users_vip->where("uid = '{$uid}'")->order('grade desc')->fetchOne(); // 最高等级会员
            $nowTime = time();
            if ($userVip) { // 购买过会员
                if ($userVip['grade'] > $vipInfo['orderno']) { // 购买的等级是否小于一起历史的等级
                    return 1003;
                }
                if ($userVip['grade'] == $vipInfo['orderno']) {  // 此次购买的会员等级和历史购买最高等级相同

                    if ($userVip['is_free'] == 1) { // 判断是否领取过免费的会员
                        return 1004;
                    }

                    if ($userVip['endtime'] > $nowTime) { // vip还为过期续费
                        $endtime = strtotime("+{$vipInfo['length']} month", $userVip['endtime']);
                        if ($vipInfo['give_data']) {
                            $endtime = strtotime("+{$vipInfo['give_data']} day", $endtime);
                        }
                        $data['endtime'] = $endtime;
                    } else { //  vip 过期续费
                        $endtime = strtotime("+{$vipInfo['length']} month", $nowTime);
                        if ($vipInfo['give_data']) {
                            $endtime = strtotime("+{$vipInfo['give_data']} day", $endtime);
                        }
                        $data['addtime'] = time();
                        $data['endtime'] = $endtime;
                    }
                    if ($vipInfo['coin'] == 0) { // 是否为免费会员福利
                        $data['is_free'] = 1;
                    }
                    $data['vip_id'] = $vip_id;
                    DI()->notorm->users_vip->where("id = '{$userVip['id']}'")->update($data);

                } else { // 买更高级的会员
                    $endtime = strtotime("+" . $vipInfo['length'] . " month", $nowTime);
                    if ($vipInfo['give_data']) {
                        $endtime = strtotime("+{$vipInfo['give_data']} day", $endtime);
                    }
                    $data = array(
                        'uid' => $uid,
                        'addtime' => $nowTime,
                        'endtime' => $endtime,
                        'tenant_id' => getTenantId(),
                        'grade' => $vipInfo['orderno'],
                        'vip_id' => $vip_id,
                        'price' => $coin
                    );
                    DI()->notorm->users_vip->insert($data);
                }

                $historyVip = DI()->notorm->users_vip->where("uid = '{$uid}' and endtime > '{$nowTime}'")->order('grade desc')->fetchAll(); // 获取用户为过期全部没过期的vip历史
                foreach ($historyVip as $historyVipKey => $historyVipValue) {
                    if ($historyVipKey > 0) {  // 此时用户的用户等级不参与计算

                        $historyVipData['endtime'] = strtotime("+" . $vipInfo['length'] . " month", $historyVipValue['endtime']);
                        if ($vipInfo['give_data']) {
                            $historyVipData['endtime'] = strtotime("+{$vipInfo['give_data']} day", $historyVipData['endtime']);
                        };
                        DI()->notorm->users_vip->where("id = '{$historyVipValue['id']}'")->update(array('endtime' => $historyVipData));
                    }
                }
            } else { // 没购买过会员

                $endtime = strtotime("+" . $vipInfo['length'] . " month", $nowTime);
                if ($vipInfo['give_data']) {
                    $endtime = strtotime("+{$vipInfo['give_data']} day", $nowTime);
                }
                $data = array(
                    'uid' => $uid,
                    'addtime' => time(),
                    'endtime' => $endtime,
                    'tenant_id' => getTenantId(),
                    'grade' => $vipInfo['orderno'],
                    'vip_id' => $vip_id,
                    'price' => $coin,
                );
                DI()->notorm->users_vip->insert($data);
            }

            $istouid = DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('coin' => new NotORM_Literal("coin - {$coin}")));
            $insert = array(
                "type" => 'expend',
                "action" => 'buyvip',
                "uid" => $uid,
                'user_login' => $userInfo['user_login'],
                "user_type" => intval($userInfo['user_type']),
                "giftid" => $vip_id,
                "pre_balance" => floatval($userInfo['coin']),
                "totalcoin" => $coin,
                "after_balance" => floatval(bcadd($userInfo['coin'], -abs($coin),4)),
                "addtime" => time(),
                'tenant_id' => getTenantId(),
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);
            delUserInfoCache( $uid);
        }else{ // 保证金模式
            $action  ='buyvip';
            $vipInfo = DI()->notorm->vip_grade->where("id = '{$vip_id}'")->fetchOne(); //购买的会员等级信息
            if ($coin != $vipInfo['price']) {
                return 1001;
            }
            $isRefunding = DI()->notorm->users_vip->where("uid = '{$uid}' and status = 2 ")->fetchOne(); // 查找vip 在用等级
            if ($isRefunding){
                return 1006;
            }
            $isChecking = DI()->notorm->users_vip->where("uid = '{$uid}' and status = 4 ")->fetchOne(); // 查找vip 正在审核中的
            if ($isChecking){
                return array('code' => 2109, 'msg' => codemsg('2109'), 'info' => ['追加正在审核中']);
            }
            if ($vipInfo['upgrade_need_sub_user_vip_count'] > 0 && $vipInfo['upgrade_need_sub_user_vip_grade']) {
                $sub_list = DI()->notorm->users_agent->select("uid")->where("one_uid = ?", intval($uid))->fetchAll();
                $sub_uids = count($sub_list) > 0 ? array_column($sub_list,'uid',null) : [];
                $sub_user_vip_count = count($sub_uids) > 0 ? DI()->notorm->users_vip->where("uid in(".implode(',', $sub_uids).") and status in(1,2) and grade >= ?", $vipInfo['upgrade_need_sub_user_vip_grade'])->count() : 0; //下级创作者数量
                if($vipInfo['upgrade_need_sub_user_vip_count'] > $sub_user_vip_count){
                    return array('code' => 2108, 'msg' => codemsg('2108'), 'info' => ['下级创作者数量不足',$vipInfo['upgrade_need_sub_user_vip_count'],$sub_user_vip_count]);
                }
            }

            $userVip = DI()->notorm->users_vip->where("uid = '{$uid}' and status = 1 ")->fetchOne(); // 查找vip 在用等级
            $userCoin = DI()->notorm->users->where('coin')->where("id = '{$uid}'")->fetchOne();

            if ($userVip){ // 如果有购买过vip
                if (bcadd($userCoin['coin'] * 100,$userVip['price']*100) < $coin * 100) {
                    return 1002;
                }
                if ($userVip['grade'] == $vipInfo['vip_grade'] ){
                    return 1005;
                }
                if ($userVip['grade']>$vipInfo['vip_grade']   ){
                    return 1007;
                }

                $actual_amount = bcsub($coin, $userVip['price'],2);
                $action = 'vip_upgrade_refund';
                $action_type = 2; // 操作类型：1.直接购买，升级
            }else{
                $actual_amount = $coin;
                $action_type = 1; // 操作类型：1.直接购买，升级
            }

            if ($userCoin['coin'] * 100 < $actual_amount * 100) {
                return 1002;
            }
            DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('coin' => new NotORM_Literal("coin - {$actual_amount}"),
                    'vip_margin' => new NotORM_Literal("vip_margin + {$actual_amount}"),
                ));

            $data = array(
                'uid' => $uid,
                'user_type' => $userInfo['user_type'],
                'addtime' => time(),
                'endtime' => time(),
                'tenant_id' => getTenantId(),
                'grade' => $vipInfo['vip_grade'],
                'vip_id' => $vip_id,
                'price' => $coin,
                'actual_amount' => $actual_amount,
                'action_type' => $action_type,
            );
            $id  = DI()->notorm->users_vip->insert($data);
            $insert = array(
                "type" => 'expend',
                "action" => $action,
                "uid" => $uid,
                'user_login' => $userInfo['user_login'],
                'user_type' => $userInfo['user_type'],
                "giftid" => $id,
                "pre_balance" => floatval($userInfo['coin']),
                "totalcoin" => $actual_amount,
                "after_balance" => floatval(bcadd($userInfo['coin'], -abs($actual_amount),4)),
                "addtime" => time(),
                'tenant_id' => getTenantId(),
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);
        }
        delUserVipInfoCache($uid);
        return true;
    }

    public  function welfareList(){
        $list=DI()->notorm->welfare
            ->where('status = 1')
            ->fetchAll();
        return $list;
    }

    public  function exchangeWelfare($uid,$welfare_id,$consignee,$phone,$address,$game_tenant_id){
        $welfareInfo = DI()->notorm->welfare->where("id = '{$welfare_id}'") ->fetchOne();
        $userIntegral = DI()->notorm->users->select('user_nicename,integral')->where("id = '{$uid}'") ->fetchOne();
        if ($userIntegral['integral']< $welfareInfo['integral']){
            return 1001;
        }
        $data = array(
            'uid' =>$uid ,
            'welfare_id' =>$welfare_id ,
            'consignee' =>$consignee ,
            'phone' =>$phone ,
            'address' =>$address ,
            'status' =>1 ,
            'addtime' => time(),
            'tenant_id' =>getTenantId(),
        );
        $list=DI()->notorm->welfare_exchange_log->insert($data);

        $integraldata=array(
            'uid'=>$uid,
            'start_integral'=>$userIntegral['integral'],
            'change_integral'=>$welfareInfo['integral'],
            'end_integral'=>($userIntegral['integral']-$welfareInfo['integral']),
            'act_type'=>2, // 兑换
            'status'=>1,
            'remark'=>$userIntegral['user_nicename'].' 福利兑换',
            'ctime'=>time(),
            'act_uid'=>$uid,
            'tenant_id'=>getTenantId(),
        );
        DI()->notorm->integral_log->insert($integraldata);

        DI()->notorm->users->where("id = '{$uid}'")  ->update( array('integral' => new NotORM_Literal("integral - '{$welfareInfo['integral']}'") ));
        delUserInfoCache($uid);
        return true;
    }

    public  function exchangeWelfareLog($uid,$p){
        if($p<1){
            $p=1;
        }
        $pnums=20;
        $start=($p-1)*$pnums;
        $list=DI()->notorm->welfare_exchange_log->where("uid = '{$uid}'")
            ->limit($start,$pnums)
            ->fetchAll();
        foreach ($list as $key =>$value){
            $welfareInfo = DI()->notorm->welfare->where("id = '{$value['welfare_id']}'") ->fetchOne();
            $list[$key]['welfare_name'] = $welfareInfo['name'];
            $list[$key]['img'] = $welfareInfo['img'];
            $list[$key]['integral'] = $welfareInfo['integral'];
            $list[$key]['desc'] = $welfareInfo['desc'];
            $list[$key]['addtime'] = date('Y-m-d h:i:s',$value['addtime']);
        }
        return $list;
    }


    public  function welfareInfo($welfare_id){
        $welfareInfo = DI()->notorm->welfare->where("id = '{$welfare_id}'") ->fetchOne();

        return $welfareInfo;
    }

    public  function freeVip($uid){
        $userVip=DI()->notorm->users_vip
            ->where("uid = '{$uid}'")
            ->order('grade desc')
            ->fetchOne();

        if ($userVip['is_free'] ==1){
            return [ 'code' => '1001','msg'=> '您已领取过此等级VIP'];
        }
        $vip=DI()->notorm->vip
            ->where("coin = 0  and  orderno = '{$userVip['grade']}' ")
            ->fetchOne();
        if ($vip){
            $vip_endtime = strtotime ("+".$vip['length']." month", $userVip['endtime']);
            if ($userVip['give_data']){
                $vip_endtime = strtotime ("+{$userVip['give_data']} day",  $vip_endtime);
            };
            $vip['vip_end_time']  =  date( "Y-m-d h:i:s",$vip_endtime);
        }

        $data = [
            'is_free' => 1,
            'endtime' => $vip_endtime,
            ];
        $userVip=DI()->notorm->users_vip
            ->where("uid = '{$uid}'")->update($data);
        return $vip;
    }

    public  function refundVip($uid){
        $userVip = DI()->notorm->users_vip
            ->where("uid = '{$uid}'")
            ->order('status = 1 and addtime  desc')
            ->fetchOne();
        $tenant_id = getTenantId();

        if (empty($userVip)){
            return 1001;
        }
        if ($userVip['status'] ==2){
            return 1002;
        }
        if ($userVip['status'] ==3){
            return 1001;
        }
        DI()->notorm->users_vip
            ->where("uid = '{$uid}' and  status = 1 and  id = '{$userVip['id']}'")
            ->update(['status'=> 2, 'refund_time'=>time()]);

        CustRedis::getInstance()->hDel('user_vip_info_'.$tenant_id, $uid);
        CustRedis::getInstance()->hDel('user_vip_checking_info_'.$tenant_id, $uid);

        return true ;
    }
}