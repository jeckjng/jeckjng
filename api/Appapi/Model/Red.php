<?php

class Model_Red extends PhalApi_Model_NotORM {
	/* 发布红包 */
	public function sendRed($data) {


        try {
            beginTransaction();
            $uid=$data['uid'];
            $liveuid=$data['liveuid'];
            $total=$data['coin'];
            $liveUserInfo=getUserInfo($liveuid);
            $userInfo=getUserInfo($uid);
            $liveUserTenantInfo=getTenantInfo($liveUserInfo['tenant_id']);
            if ($liveUserTenantInfo['site_id'] == 1){ // 集成
                $coinInfo=getGameUserBalance($userInfo['game_tenant_id'],$userInfo['game_user_id']);
                $coin=0;
                if($coinInfo['code']!=0){
                    //如果code不等于0为请求失败
                    //请求失败时余额返回0
                    $coin=0;
                }
                else{
                    $coin=$coinInfo['coin'];
                }
                if($coin<$total){
                    $rs['code']=2026;
                    $rs['msg']='余额不足';
                    return $rs;
                }
                $ifok=DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(
                        array('consumption' => new NotORM_Literal("consumption + {$total}")
                        ) );
            }else{
                $userCoin = DI()->notorm->users
                    ->select('coin')
                    ->where('id = ?', $uid)
                    ->fetchOne();
                if ($userCoin['coin']< $total){
                    $rs['code']=2026;
                    $rs['msg']='余额不足';
                    return $rs;
                }

                $ifok=DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array(
                        'consumption' => new NotORM_Literal("consumption + {$total}"),
                        'coin' => new NotORM_Literal("coin - {$total}")
                    ) );
            }

            $result = DI()->notorm->red->insert($data);
            if(!$result){
                $rs['code']=1009;
                $rs['msg']='发送失败，请重试';
                return $rs;
            }

            $type='expend';
            $action='sendred';
            $uid=$data['uid'];
            $giftid=$result['id'];
            $giftcount=1;
            $total=$data['coin'];
            $showid=$data['showid'];
            $addtime=$data['addtime'];
            $insert=array(
                "type"=>$type,
                "action"=>$action,
                "uid"=>$uid,
                'user_login' => $userInfo['user_login'],
                'user_type' => $userInfo['user_type'],
                "touid"=>$uid,
                "giftid"=>$giftid,
                "giftcount"=>$giftcount,
                "totalcoin"=>$total,
                "showid"=>$showid,
                "addtime"=>$addtime,
                "tenant_id"=>$data['tenant_id'],
                );
            $is_success =  $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);
            //调用余额更新接口
        if ($liveUserTenantInfo['site_id'] == 1) {
            $config=getConfigPub();
            $money_rate = $config['money_rate'];

            $useridGame = $userInfo['game_user_id'];
            $useridLive = $userInfo['id'];
            $tidGame = $userInfo['game_tenant_id'];
            $tidLive = $userInfo['tenant_id'];
            $usernickname = $userInfo['user_nicename'];
            //金额=钻石/转换比例四舍五入
            $amount = round($total / $money_rate, 2);
            $diamond = $total;
            $type = 4;
            $detail = '发红包';
            $roomid = $liveuid;
            $anchorid = $liveuid;
            $anchorname = $liveUserInfo['user_nicename'];
            $anchorfromid = $liveUserInfo['tenant_id'];
            $anchorformname = $liveUserTenantInfo['name'];
            $tId = $userInfo['game_tenant_id'];
            $custId = $userInfo['game_user_id'];
            $updateResult = reduceGameUserBalance($useridGame, $useridLive, $tidGame, $tidLive, $usernickname, $amount, $diamond, $type, $detail, $roomid, $anchorid, $anchorname, $anchorfromid, $anchorformname, $tId, $custId);
            if($updateResult['code']!=0){
                /* rollbackTransaction();*/
                //调用失败,回滚事务,并返回余额不足错误
                $rs['code']=1009;
                $rs['msg']='余额不足';
                return $rs;
            }
            else{
                commitTransaction();
                return  $result;
            }
        }
        if($is_success === false || $result === false ){
              rollbackTransaction();
              $rs['code']=1009;
              $rs['msg']='发送失败，请重试';
                return $rs;
        } else{
            commitTransaction();
            return $result;
        }
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("发红包异常:".$ex->getMessage());
            $rs['code']=1009;
            $rs['msg']='发送失败，请重试';
            return $rs;
        }
	}		
    
    /* 红包列表 */
    public function getRedList($liveuid,$showid){
        $list=DI()->notorm->red
                ->select("*")
                ->where('liveuid = ? and showid= ? and status = 0',$liveuid,$showid)
                ->order('addtime desc')
                ->fetchAll();
        return $list;
    }

	/* 抢红包 */
	public function robRed($data) {

       try {
            beginTransaction();
            $type='income';
            $action='robred';
            $uid=$data['uid'];
            $liveuid=$data['liveuid'];
            $giftid=$data['redid'];
            $giftcount=1;
            $total=$data['coin'];
            $showid=$data['showid'];
            $addtime=$data['addtime'];
            unset($data['showid']);
            $liveUserInfo=getUserInfo($liveuid);
            $userInfo=getUserInfo($uid);
            $liveUserTenantInfo=getTenantInfo($liveUserInfo['tenant_id']);
            $insert=array(
                "type"=>$type,
                "action"=>$action,
                "uid"=>$uid,
                'user_login' => $userInfo['user_login'],
                'user_type' => $userInfo['user_type'],
                "touid"=>$uid,
                "giftid"=>$giftid,
                "giftcount"=>$giftcount,
                "totalcoin"=>$total,
                "showid"=>$showid,
                "addtime"=>$addtime,
                "tenant_id"=>$data['tenant_id'],
                );

            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);

            $result= DI()->notorm->red_record->insert($data);

           DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('coin' => new NotORM_Literal("coin + {$total}") ) );

           $redInfo = DI()->notorm->red  ->where('id = ?', $giftid)->fetchOne();
           if ($redInfo['nums_rob'] == 1){
               DI()->notorm->red
                   ->where('id = ?', $giftid)
                   ->update(array('coin_rob' => new NotORM_Literal("coin_rob + {$total}") ,'nums_rob' => new NotORM_Literal("nums_rob + 1"),'status' => 1 ) );

           }else{
               DI()->notorm->red
                   ->where('id = ?', $giftid)
                   ->update(array('coin_rob' => new NotORM_Literal("coin_rob + {$total}") ,'nums_rob' => new NotORM_Literal("nums_rob + 1") ) );
           }

            //调用余额更新接口
            if ($liveUserTenantInfo['site_id'] == 1) {
                $config = getConfigPub();
                $money_rate = $config['money_rate'];

                $useridGame = $userInfo['game_user_id'];
                $useridLive = $userInfo['id'];
                $tidGame = $userInfo['game_tenant_id'];
                $tidLive = $userInfo['tenant_id'];
                $usernickname = $userInfo['user_nicename'];
                //金额=钻石/转换比例四舍五入
                $amount = round($total / $money_rate, 2);
                $diamond = $total;
                $type = 5;
                $detail = '收红包';
                $roomid = $liveuid;
                $anchorid = $liveuid;
                $anchorname = $liveUserInfo['user_nicename'];
                $anchorfromid = $liveUserInfo['tenant_id'];
                $anchorformname = $liveUserTenantInfo['name'];
                $tId = $userInfo['game_tenant_id'];
                $custId = $userInfo['game_user_id'];
                $updateResult = addGameUserBalance($useridGame, $useridLive, $tidGame, $tidLive, $usernickname, $amount, $diamond, $type, $detail, $roomid, $anchorid, $anchorname, $anchorfromid, $anchorformname, $tId, $custId);
                if($updateResult['code']!=0){
                    rollbackTransaction();
                    //调用失败,回滚事务
                    $rs['code']=1001;
                    $rs['msg']='领取失败';
                    return $rs;
                }
            }
            if($result=== false){
                rollbackTransaction();
                //调用失败,回滚事务
                $rs['code']=1001;
                $rs['msg']='领取失败';
                return $rs;
            }
            else{
               commitTransaction();
                return $result;
            }

       }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("抢红包异常:".$ex->getMessage());
            $rs['code']=1001;
            $rs['msg']='领取失败';
            return $rs;
       }


	}			

    /* 抢红包列表 */
    public function getRedRobList($redid){
        $list=DI()->notorm->red_record
                ->select("*")
                ->where('redid = ?',$redid)
                ->order('addtime desc')
                ->fetchAll();
        return $list;
    }
    
    /* 红包信息 */
    public function getRedInfo($redid){
        $redinfo=DI()->notorm->red
                ->select("*")
                ->where('id = ? ',$redid )
                ->fetchOne();
        if($redinfo){
            unset($redinfo['showid']);
            unset($redinfo['liveuid']);
            unset($redinfo['effecttime']);
            unset($redinfo['addtime']);
            unset($redinfo['status']);
        }
        return $redinfo;
        
    }

    public  function isobtain($uid,$redid){
        $is_obtain = DI()->notorm->red_record->where("uid = '{$uid}'and redid = '{$redid}'")->fetchOne();
        if ($is_obtain){
            return 2024;
        }else{
            return 1;
        }
    }
    /* 用户抢红包接口 */
    public function sendRedpacket($uid, $red_packet_id){
        $info = getUserInfo($uid);
        if ($info['grab_red_packet_status'] == 0) { // 判断 代理返点 是否开启
            return 1004;
        }
        if($info['user_type'] == 7){ // 测试账号，不能抢红包
            return array('code' => 404, 'msg' => codemsg(404), 'info' => array());
        }
        if($info['user_type'] == 4){ // 游客账号，不能抢红包
            return array('code' => 406, 'msg' => codemsg(406), 'info' => array());
        }

        //判断是否是 创作者, 默认普通会员也可以抢
        $user_model = new Model_Login();
        $vip_info = $user_model->getUserjurisdiction($uid,$info['user_type']);
        if (in_array($vip_info['user_vip_status'], [2])) { // 退保证金申请中不能有收益，也不能抢红包，也不能理返点
            return 1004;
        }

        $redis = connectRedis();
        $tenant_id =getTenantId();
        $time_now = time();
        if($red_packet_id){
            $redsetting = DI()->notorm->red_setting->where(" tenant_id = ? and id = ? ", intval($tenant_id), intval($red_packet_id))->fetchOne();
            if(!$redsetting){
                return array('code' => 703, 'msg' => codemsg(703), 'info' => array(''));
            }
            if($redsetting['effect_time_start'] > $time_now || $time_now > $redsetting['effect_time_end']){
                return 1005;
            }
        }else{
            $redsetting = DI()->notorm->red_setting->where(" tenant_id = ? and effect_time_start <= ? and effect_time_end >= ? and FIND_IN_SET('".date('H')."',red_time)", intval($tenant_id), time(), time())->order('id asc')->fetchOne();
            if(!$redsetting){
                return 1005;
            }
            $red_packet_id = $redsetting['id'];
        }

        $timecheck = date('H', time());
        if(strpos($redsetting['red_time'],$timecheck) == false && strpos($redsetting['red_time'],$timecheck) !== 0 ){
            return 1004;
        }

        $timesend = date('YmdH', time());
        $hour_time =  strtotime(date('Y-m-d H:00:00',time()));
        $result = array('time'=>time(),'starttime'=>$hour_time+$redsetting['second_time']*60,'endtime'=>$hour_time+$redsetting['second_time']*60+$redsetting['win_time']*60);
        //如果当前时间小于开始时间，或者 当前时间大于结束时间，返回感谢参与
        if(time()<$hour_time+$redsetting['second_time']*60 || time()>$hour_time+$redsetting['second_time']*60+$redsetting['win_time']*60){
            logapi(['params'=>$result],'【时间不满足条件】');
            return 1004;
        }

        // 指定用户，是指定的会员才能领取对应红包
        if($redsetting['uids']){
            $uid_list = explode(',', $redsetting['uids']);
            if(!in_array($uid, $uid_list)){
                return 1004;
            }
        }

        // 如果用户等级在对应的等级，并且红包设置正常，则领取对应的红包金额，反正则领取基准设置的红包金额
        $vip_conf = json_decode($redsetting['vip_conf'], true);
        $vip_conf = is_array($vip_conf) ? $vip_conf : [];
        foreach ($vip_conf as $k=>$v){
            $vip_grade = trim($k,'vip_grade_');
            if($vip_grade == $vip_info['new_level']){
                $red_send_k = 'red_send_'.$timesend.'_'.$red_packet_id."_".trim($k,'vip_grade_');

                //判断是否领取
                $red_usermark_key = 'red_usermark_'.$timesend.'_'.$red_packet_id;
                $reduser = $redis->hGet($red_usermark_key, $uid);
                if ($reduser){
                    return 1001;
                }

                // 解决获取到0，然后就删除该批次红包，造成红包没有领完
                $money = $redis->lPop($red_send_k);
                if($money === '0'){
                    $money = $redis->lPop($red_send_k);
                    if($money === '0'){
                        $money = $redis->lPop($red_send_k);
                        if($money === '0'){
                            return 1004;
                        }
                    }
                }
                if($money == false){
                    //抢完红包，删除该批次所有生成的redis数据，减轻redis压力
                    delPatternCacheKeys($red_send_k);
                    return  1002;//红包抢完了
                }else{
                    $money = floatval(bcmul($money, $v['multiple'], 4));
                    //写入标识
                    $redis->hSet($red_usermark_key, $uid, $money);
                    $redis->expire($red_usermark_key,60*60*24);
                    if($redsetting['type'] == 1){ // 金钱
                        //写入队列
                        $redis->lPush('red_user_'.$tenant_id,$uid.','.$money.','.$timesend.','.$tenant_id);
                    }
                    if($redsetting['type'] == 2){ // 购物券
                        Model_ShoppingVoucher::getInstance()->add([
                            'tenant_id'=>$tenant_id,
                            'uid'=>$uid,
                            'user_login'=>$info['user_login'],
                            'user_type'=>$info['user_type'],
                            'amount'=>$money,
                            'datenum'=>$timesend
                        ]);
                    }
                }
                return $money;
            }
        }

        $key = 'red_send_'.$timesend.'_'.$red_packet_id;
        //判断是否领取
        $red_usermark_key = 'red_usermark_'.$timesend.'_'.$red_packet_id;
        $reduser = $redis->hGet($red_usermark_key, $uid);
        if ($reduser){
            return 1001;
        }

        // 解决获取到0，然后就删除该批次红包，造成红包没有领完
        $money = $redis->lPop($key);
        if($money === '0'){
            $money = $redis->lPop($key);
            if($money === '0'){
                $money = $redis->lPop($key);
                if($money === '0'){
                    return 1004;
                }
            }
        }
        if($money == false){
            //抢完红包，删除该批次所有生成的redis数据，减轻redis压力
            delPatternCacheKeys($key);
            //delPatternCacheKeys($key1);
            return  1002;//红包抢完了
        }else{
            $money = floatval(bcmul($money, $redsetting['multiple'], 4));
            //写入标识
            $redis->hSet($red_usermark_key, $uid, $money);
            $redis->expire($red_usermark_key,60*60*24);
            if($redsetting['type'] == 1){ // 金钱
                //写入队列
                $redis->lPush('red_user_'.$tenant_id,$uid.','.$money.','.$timesend.','.$tenant_id);
            }
            if($redsetting['type'] == 2){ // 购物券
                Model_ShoppingVoucher::getInstance()->add([
                    'tenant_id'=>$tenant_id,
                    'uid'=>$uid,
                    'user_login'=>$info['user_login'],
                    'user_type'=>$info['user_type'],
                    'amount'=>$money,
                    'datenum'=>$timesend
                ]);
            }
        }
        return $money;

    }

    /* 红包数据入库 */
    public function sendRedcrontab(){
        $redis = connectRedis();
        $coinrecordModel = new Model_Coinrecord();
        $tenant_list = getTenantList();
        $is_success = [];
        foreach ($tenant_list as $tenant_key=>$tenant_val){
            $tenant_id = $tenant_val['id'];
            $key = 'red_user_'.$tenant_id;
            $redinfo = $redis->lPop($key);

            // $redinfo = '1185532,6,2022080808,32';
            if(!$redinfo){
                continue;
            }

            $redinfo = explode(',',$redinfo);
            $uid = $redinfo[0];
            $user_info = getUserInfo($uid);
            $change_coin = $redinfo[1];

            $insert=array(
                "uid"=>$redinfo[0],
                'user_login' => $user_info['user_login'],
                "user_type"=>$user_info['user_type'],
                "datenum"=>$redinfo[2],
                "pre_balance" => floatval($user_info['coin']),
                "coin" => $change_coin,
                "after_balance" => floatval(bcadd($user_info['coin'], $change_coin,4)),
                "tenant_id"=>$redinfo[3],
                "addtime"=>time(),
            );
            $is_success =  DI()->notorm->red_record_detail->insert($insert);
            DI()->notorm->users
                ->where('id = ?', $redinfo[0])
                ->update(
                    array('coin' => new NotORM_Literal("coin + {$redinfo[1]}")
                    ) );
            $action='redpackge';

            /**
             * 用户红包收入
             */
            $insertanchor=array(
                "type"=>'income',
                "action"=>$action,
                "uid"=>$redinfo[0],
                'user_login' => $user_info['user_login'],
                "user_type"=>$user_info['user_type'],
                "touid"=>$redinfo[0] ,
                "giftid"=>1,
                "giftcount"=>1,
                "pre_balance" => floatval($user_info['coin']),
                "totalcoin" => $change_coin,
                "after_balance" => floatval(bcadd($user_info['coin'], $change_coin,4)),
                "showid"=>'1',
                "mark"=>1,
                "addtime"=>time(),
                'tenant_id' =>$tenant_id,
                'receive_tenant_id'=>$tenant_id,
            );
            $coinrecordModel->addCoinrecord($insertanchor);
            delUserInfoCache($uid);
        }
        return $is_success;

    }
}
