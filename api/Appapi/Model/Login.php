<?php

class Model_Login extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    protected $fields = '';

    public function __construct()
    {
        $this->fields = Cache_Users::getInstance()->fields;
    }

    /* 内嵌会员登录 只认游戏系统用户id */
    public function internalUserLogin($game_user_id,$user_login,$user_pass,$tenantId,$zone,$avatar,$avatar_thumb,$user_nicename,$last_login_ip) {
        $redis = connectionRedis();
        $user_pass=setPass($user_pass);

        $info=DI()->notorm->users
            ->select($this->fields)
            ->where('game_user_id=? and user_type in (2,5,6) and tenant_id=? and zone=?',$game_user_id,$tenantId,$zone)
            ->fetchOne();
        if(!$info){
            return 1001;
        }
        unset($info['user_pass']);
        if($info['user_status']=='0'){
            return 1002;
        }
        unset($info['user_status']);

        $info['isreg']='0';
        $info['isagent']='0';

        if($info['last_login_time']==''){
            $info['isreg']='1';
            $info['isagent']='1';
        }

        $configpri=getConfigPri();
        if($configpri['agent_switch']==0){
            $info['isagent']='0';
        }

        /* 是否代理 */
        $isexist=DI()->notorm->users_agent
            ->select('uid')
            ->where('uid=?',$info['id'])
            ->fetchOne();
        if($isexist){
            $info['isagent']='0';
        }

        $info['level']=getLevel($info['consumption']);
        $info['exp_can_speak'] = exp_can_speak($info['consumption']);
        $info['level_anchor']=getLevelAnchor($info['votestotal']);

        $token=md5(md5($info['id'].$user_login.time()));

        $info['token']=$token;

        /*	更新直播系统用户名和密码 */

        if(empty($avatar) || empty($avatar_thumb)){
            $data=array(
                'user_login'=>$user_login,
                'user_pass'=>$user_pass,
                'user_nicename'=>$user_nicename
            );
        }else{
            $data=array(
                'user_login'=>$user_login,
                'user_pass'=>$user_pass,
                'avatar'=>$avatar,
                'avatar_thumb'=>$avatar_thumb,
                'user_nicename'=>$user_nicename
            );
        }


        //如果不需要更新传空array
        //$data=array();


        $this->updateToken($info['id'],$token,$data,$last_login_ip);

        //更新后重新查看数据
        $avatarinfo=DI()->notorm->users
            ->select('id,avatar','avatar_thumb','user_nicename,user_type,user_login')
            ->where('game_user_id=? and user_type in(2,5,6) and tenant_id=? and zone=?',$game_user_id,$tenantId,$zone)
            ->fetchOne();
        $info['avatar']=$avatarinfo['avatar'];
        $info['avatar_thumb']=$avatarinfo['avatar_thumb'];
        $info['user_login']=$avatarinfo['user_login'];
        $info['user_nicename']=$avatarinfo['user_nicename'];
        $vip_info = $this->getUserjurisdiction($avatarinfo['id'],$avatarinfo['user_type']);
        $info['watch_number'] =  $vip_info['watch_number'];
        $info['watch_duration'] =  $vip_info['watch_duration'];
        $info['level_addtime'] = $vip_info['level_addtime'];
        $info['level_endtime'] = $vip_info['level_endtime'];
        $info['new_level'] = $vip_info['new_level'] ;
        $info['level_name'] =  $vip_info['level_name'] ;
        $info['user_vip_checking_level'] = $vip_info['user_vip_checking_level'];
        $info['user_vip_status'] =  $vip_info['user_vip_status'];
        $info['user_vip_action_type'] =  $vip_info['user_vip_action_type'];
        $info['user_vip_create_time'] = $vip_info['user_vip_create_time'];
        $info['user_vip_update_time'] = $vip_info['user_vip_update_time'];
        $info['user_vip_refund_time'] = $vip_info['user_vip_refund_time'];
        $info['level_name_jurisdiction'] = $vip_info['level_name_jurisdiction'];
        $info['watchnum_show_ad_video'] = $vip_info['watchnum_show_ad_video'];
        $info['beauty'] = is_json($info['beauty']) ? json_decode($info['beauty'],true) : $info['beauty'];
        $info['noble'] = getUserNoble($info['id']);

        $redis->set($token,json_encode(getUserInfo($info['id']))); // socket获取用户信息使用

        unset($info['payment_password']);

        return $info;
    }


    /* 会员登录 */
    public function userLogin($user_login,$user_pass,$tenantId,$zone) {
        $user_pass=setPass($user_pass);
        $info=DI()->notorm->users
            ->select($this->fields.',user_pass')
            ->where('user_login=? and user_type in(2,5,6,7) and tenant_id=? ',$user_login,$tenantId)
            ->fetchOne();
        if(!$info || $info['user_pass'] != $user_pass){
            return 1001;
        }
        unset($info['user_pass']);
        if($info['user_status']=='0'){
            return 1002;
        }
        unset($info['user_status']);

        $info['is_set_payment_password'] = $info['payment_password'] ? 1 : 0;
        unset($info['payment_password']);

        $info['isreg']='0';
        $info['isagent']='0';
        $info['withdrawable_coin'] = $info['coin'];
        $info['totalcoin'] = bcadd($info['withdrawable_coin'],$info['nowithdrawable_coin'],2);

        if($info['last_login_time']==''){
            $info['isreg']='1';
            $info['isagent']='1';
        }

        $configpri=getConfigPri();
        if($configpri['agent_switch']==0){
            $info['isagent']='0';
        }
        $uid=$info['id'];
        /* 是否代理 */
        $isexist=DI()->notorm->users_agent
            ->select('uid')
            ->where('uid=?',$uid)
            ->fetchOne();
        if($isexist){
            $info['isagent']='0';
        }
        $vip_info = $this->getUserjurisdiction($uid,$info['user_type']);
        $info['avatar'] = get_upload_path($info['avatar']); // 头像
        $info['avatar_thumb'] = get_upload_path($info['avatar_thumb']); // 头像缩略图
        $info['watch_number'] = $vip_info['watch_number']!=0 ? ($vip_info['watch_number']+$info['watch_num']) : $vip_info['watch_number'];
        $info['watch_duration'] = $vip_info['watch_duration']!=0 ? ($vip_info['watch_duration']+$info['watch_time']) : $vip_info['watch_duration'];
        unset($info['watch_num']);
        unset($info['watch_time']);
        $info['level_addtime'] = $vip_info['level_addtime'];
        $info['level_endtime'] = $vip_info['level_endtime'];
        $info['new_level'] = $vip_info['new_level'];
        $info['level_name'] =  $vip_info['level_name'];
        $info['user_vip_checking_level'] = $vip_info['user_vip_checking_level'];
        $info['user_vip_status'] = $vip_info['user_vip_status'];
        $info['user_vip_action_type'] = $vip_info['user_vip_action_type'];
        $info['user_vip_create_time'] = $vip_info['user_vip_create_time'];
        $info['user_vip_update_time'] = $vip_info['user_vip_update_time'];
        $info['user_vip_refund_time'] = $vip_info['user_vip_refund_time'];
        $info['level_name_jurisdiction'] = $vip_info['level_name_jurisdiction'];
        $info['limit_upload_video_count'] = $vip_info['limit_upload_video_count'];
        $info['level']=getLevel($info['consumption']);
        $info['exp_can_speak'] = exp_can_speak($info['consumption']);
        $info['level_anchor']=getLevelAnchor($info['votestotal']);
        $info['beauty'] = is_json($info['beauty']) ? json_decode($info['beauty'],true) : $info['beauty'];
        $info['noble'] = getUserNoble($info['id']);

        $info['appointment_collect'] =  DI()->notorm->appointment_collect->where("uid = {$uid}")->count();
        $info['appointment_browse'] = DI()->notorm->appointment_browse_log->where("uid = {$uid}")->count();

        $token=md5(md5($info['id'].$user_login.time()));
        $info['token']=$token;

        $this->updateToken($info['id'],$token);

        $tenantinfo = getTenantInfo($tenantId);
        $configInfo = getConfigPub();

        $info['is_open_seeking_slice'] =  $configInfo['is_open_seeking_slice'];// 贴吧是否开启
        $info['posting_strategy'] =  $configInfo['posting_strategy'];// 贴吧发帖策略
        $info['comment_strategy'] =  $configInfo['comment_strategy'];// 贴吧发帖策略
        $info['seeking_slice_strategy'] =  $configInfo['seeking_slice_strategy'];// 贴吧发帖策略
        $info['push_strategy'] =  $configInfo['push_strategy'];// 贴吧发帖策略
        $info['seeking_slice_bonus_min'] =  $configInfo['seeking_slice_bonus_min'];// 贴吧发帖策略
        $info['seeking_slice_bonus_max'] =  $configInfo['seeking_slice_bonus_max'];// 贴吧发帖策略
        $redis = connectRedis();
        $length = $redis->lLen($uid.'_reward_time');
        $redisList = $redis->lRange($uid.'_reward_time',0,$length);
        $totalAmount = 0;
        foreach ($redisList as $value){
            $amount = $redis->get($uid.'_'.$value.'_reward') ;
            if ($amount > 0){
                $totalAmount = bcadd($totalAmount,$amount,2);
            }else{
                $redis->lRem($uid.'_reward_time',$value,0);
            }
        }
        $info['can_be_withdrawn']  =  strval (bcsub ($info['nowithdrawable_coin'] , $totalAmount,2));
        if($tenantinfo['site_id'] == 2){ // 只有独立租户才赠送
            $this->loginreward($uid,2,$info); // 登录赠送
            $info['login_num'] = getUserInfo($info['id'])['login_num'];
        }
        return $info;
    }

    /*
     * 登录赠送处理
     * $type 1注册，2登录
     * */
    public function loginreward($uid,$type=null,$user_info=array()){
        $redis = connectionRedis();
        $userModel = new Model_User();
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
        $loginreward = DI()->notorm->task_loginreward->select('*')->where('type=?',1)->fetchOne();
        $users_coinre_coin = array('type'=>'income','uid'=>$uid,'addtime'=>time(),'tenant_id'=>$user_info['tenant_id']);
        $users_coinre_withd_coding = array('type'=>'inccoding','uid'=>$uid,'addtime'=>time(),'tenant_id'=>$user_info['tenant_id']);
        $users_coinre_nowithd_coin = array('type'=>'income_nowithdraw','uid'=>$uid,'addtime'=>time(),'tenant_id'=>$user_info['tenant_id']);
        $login_num = 0;
        $timeout = strtotime(date('Y-m-d 23:59:59')) - time();
        if($type==1){
            $prob_rwc = array(
                array('val' => 'reg_withdrawable_coin','prob' => 5),
                array('val' => 'reg_withdrawable_coin2','prob' => 15),
                array('val' => 'reg_withdrawable_coin3','prob' => 80),
            );
            $prob_rnc = array(
                array('val' => 'reg_nowithdrawable_coin','prob' => 5),
                array('val' => 'reg_nowithdrawable_coin2','prob' => 15),
                array('val' => 'reg_nowithdrawable_coin3','prob' => 80),
            );

            $coin = $loginreward[getProbVal($prob_rwc)];
            $withd_coding = $loginreward['reg_withdrawable_coding'];
            $nowithd_coin = $loginreward[getProbVal($prob_rnc)];

            $users_coinre_coin['action'] = 'reg_reward';
            $users_coinre_withd_coding['action'] = 'reg_reward';
            $users_coinre_nowithd_coin['action'] = 'reg_reward';
            $redis->set('day_login_reward_'.$uid.date('Y-m-d'),1,$timeout);
        }
        if($type==2){
            $login_num = 1;
            if($user_info['login_num']==0){
                DI()->notorm->users->where(['id'=>intval($uid)])->update(array(
                    'login_num'=>new NotORM_Literal("login_num+".$login_num)
                ));
                delUserInfoCache($uid);
                return false;
            }else{
                if($redis->get('day_login_reward_'.$uid.date('Y-m-d'))){ // 非首次登录奖励，每天只送一次，送过后就不再送
                    DI()->notorm->users->where(['id'=>intval($uid)])->update(array(
                        'login_num'=>new NotORM_Literal("login_num+".$login_num)
                    ));
                    delUserInfoCache($uid);
                    return false;
                }
                $redis->set('day_login_reward_'.$uid.date('Y-m-d'),1,$timeout);
                $prob_owc = array(
                    array('val' => 'otherlog_withdrawable_coin','prob' => 5),
                    array('val' => 'otherlog_withdrawable_coin2','prob' => 15),
                    array('val' => 'otherlog_withdrawable_coin3','prob' => 80),
                );
                $prob_onc = array(
                    array('val' => 'otherlog_nowithdrawable_coin','prob' => 5),
                    array('val' => 'otherlog_nowithdrawable_coin2','prob' => 15),
                    array('val' => 'otherlog_nowithdrawable_coin3','prob' => 80),
                );
                $coin = $loginreward[getProbVal($prob_owc)];
                $withd_coding = $loginreward['otherlog_withdrawable_coding'];
                $nowithd_coin = $loginreward[getProbVal($prob_onc)];

                $users_coinre_coin['action'] = 'otherlogin_reword';
                $users_coinre_withd_coding['action'] = 'otherlogin_reword';
                $users_coinre_nowithd_coin['action'] = 'otherlogin_reword';
            }
        }
        $users_coinre_coin['totalcoin'] = floatval($coin);
        $users_coinre_withd_coding['totalcoin'] = intval($withd_coding);
        $users_coinre_nowithd_coin['totalcoin'] = intval($nowithd_coin);

        $users_coinre_coin['pre_balance'] = $userInfo['coin'];
        $users_coinre_withd_coding['pre_balance'] = $userInfo['withdrawable_coding'];
        $users_coinre_nowithd_coin['pre_balance'] = $userInfo['nowithdrawable_coin'];

        $users_coinre_coin['after_balance'] = floatval(bcadd($userInfo['coin'], $coin,4));
        $users_coinre_withd_coding['after_balance'] = floatval(bcadd($userInfo['withdrawable_coding'], $withd_coding,4));
        $users_coinre_nowithd_coin['after_balance'] = floatval(bcadd($userInfo['nowithdrawable_coin'], $nowithd_coin,4));

        $users_coinre_coin['user_login'] = $userInfo['user_login'];
        $users_coinre_withd_coding['user_login'] = $userInfo['user_login'];
        $users_coinre_nowithd_coin['user_login'] = $userInfo['user_login'];

        $users_coinre_coin['user_type'] = $userInfo['user_type'];
        $users_coinre_withd_coding['user_type'] = $userInfo['user_type'];
        $users_coinre_nowithd_coin['user_type'] = $userInfo['user_type'];

        if($coin>0){
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($users_coinre_coin); // 可提现金币变动记录
        }
        if($withd_coding>0){
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($users_coinre_withd_coding); // 可提现金币打码量
        }
        if($nowithd_coin>0){
            $redisCone = connectRedis();
            $keytime = time();
            $config  =getConfigPub();
            $redisCone->lPush($uid . '_reward_time', $keytime);// 存用户 时间数据key
            $amount = $redisCone->get($uid . '_' . $keytime.'_reward');
            $totalAmount = bcadd($nowithd_coin, $amount, 2);
            $redisCone->set($uid . '_' . $keytime.'_reward', $totalAmount);// 存佣金
            $expireTime = time() + $config['withdrawal_time'] * 86400;
            /** 86400*/
            $redisCone->expireAt($uid . '_' . $keytime.'_reward', $expireTime);// 设置过去时间
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($users_coinre_nowithd_coin); // 不可提现金币
        }

       DI()->notorm->users->where(['id'=>intval($uid)])->update(array(
            'login_num'=>new NotORM_Literal("login_num+".intval($login_num)),
            'coin'=>new NotORM_Literal("coin+".intval($coin)),
            'nowithdrawable_coin'=>new NotORM_Literal("nowithdrawable_coin+".intval($nowithd_coin)),
           'withdrawable_coding'=>new NotORM_Literal("withdrawable_coding+".intval($withd_coding)),
        ));
        delUserInfoCache($uid);
        return false;
    }

    public function loginmessage($user_login,$zone,$tenantId){

        $info=DI()->notorm->users
            ->select($this->fields)
            ->where('mobile=? and user_type in(2,4,5,6) and tenant_id=? and zone = ?',$user_login,$tenantId,$zone)
            ->fetchOne();
        if(!$info){
            return false;
        }
            unset($info['user_pass']);
            if($info['user_status']=='0'){
                return 1002;
            }
            unset($info['user_status']);
    
            $info['is_set_payment_password'] = $info['payment_password'] ? 1 : 0;
            unset($info['payment_password']);
    
            $info['isreg']='0';
            $info['isagent']='0';
            $info['withdrawable_coin'] = $info['coin'];
            $info['totalcoin'] = bcadd($info['withdrawable_coin'],$info['nowithdrawable_coin'],2);
    
            if($info['last_login_time']==''){
                $info['isreg']='1';
                $info['isagent']='1';
            }
            
            $configpri=getConfigPri();
            if($configpri['agent_switch']==0){
                $info['isagent']='0';
            }
            $uid=$info['id'];
            /* 是否代理 */
            $isexist=DI()->notorm->users_agent
                ->select('uid')
                ->where('uid=?',$uid)
                ->fetchOne();
            if($isexist){
                $info['isagent']='0';
            }
            $vip_info = $this->getUserjurisdiction($uid,$info['user_type']);
            $info['avatar'] = get_upload_path($info['avatar']); // 头像
            $info['avatar_thumb'] = get_upload_path($info['avatar_thumb']); // 头像缩略图
            $info['watch_number'] = $vip_info['watch_number']!=0 ? ($vip_info['watch_number']+$info['watch_num']) : $vip_info['watch_number'];
            $info['watch_duration'] = $vip_info['watch_duration']!=0 ? ($vip_info['watch_duration']+$info['watch_time']) : $vip_info['watch_duration'];
            unset($info['watch_num']);
            unset($info['watch_time']);
            $info['level_addtime'] = $vip_info['level_addtime'];
            $info['level_endtime'] = $vip_info['level_endtime'];
            $info['new_level'] = $vip_info['new_level'];
            $info['level_name'] =  $vip_info['level_name'];
            $info['user_vip_checking_level'] = $vip_info['user_vip_checking_level'];
            $info['user_vip_status'] = $vip_info['user_vip_status'];
            $info['user_vip_action_type'] = $vip_info['user_vip_action_type'];
            $info['user_vip_create_time'] = $vip_info['user_vip_create_time'];
            $info['user_vip_update_time'] = $vip_info['user_vip_update_time'];
            $info['user_vip_refund_time'] = $vip_info['user_vip_refund_time'];
            $info['level_name_jurisdiction'] = $vip_info['level_name_jurisdiction'];
            $info['limit_upload_video_count'] = $vip_info['limit_upload_video_count'];
            $info['level']=getLevel($info['consumption']);
            $info['exp_can_speak'] = exp_can_speak($info['consumption']);
            $info['level_anchor']=getLevelAnchor($info['votestotal']);
            $info['beauty'] = is_json($info['beauty']) ? json_decode($info['beauty'],true) : $info['beauty'];
            $info['noble'] = getUserNoble($info['id']);
            
            //$info['appointment_collect'] =  DI()->notorm->appointment_collect->where("uid = {$uid}")->count();
            //$info['appointment_browse'] = DI()->notorm->appointment_browse_log->where("uid = {$uid}")->count();
    
            $token=md5(md5($info['id'].$user_login.time()));
            $info['token']=$token;
    
            $this->updateToken($info['id'],$token);
            
            $tenantinfo = getTenantInfo($tenantId);
            $configInfo = getConfigPub();
            
            $info['is_open_seeking_slice'] =  $configInfo['is_open_seeking_slice'];// 贴吧是否开启
            $info['posting_strategy'] =  $configInfo['posting_strategy'];// 贴吧发帖策略
            $info['comment_strategy'] =  $configInfo['comment_strategy'];// 贴吧发帖策略
            $info['seeking_slice_strategy'] =  $configInfo['seeking_slice_strategy'];// 贴吧发帖策略
            $info['push_strategy'] =  $configInfo['push_strategy'];// 贴吧发帖策略
            $info['seeking_slice_bonus_min'] =  $configInfo['seeking_slice_bonus_min'];// 贴吧发帖策略
            $info['seeking_slice_bonus_max'] =  $configInfo['seeking_slice_bonus_max'];// 贴吧发帖策略
            $redis = connectRedis();
            $length = $redis->lLen($uid.'_reward_time');
            $redisList = $redis->lRange($uid.'_reward_time',0,$length);
            $totalAmount = 0;
            foreach ($redisList as $value){
                $amount = $redis->get($uid.'_'.$value.'_reward') ;
                if ($amount > 0){
                    $totalAmount = bcadd($totalAmount,$amount,2);
                }else{
                    $redis->lRem($uid.'_reward_time',$value,0);
                }
            }
            $info['can_be_withdrawn']  =  strval (bcsub ($info['nowithdrawable_coin'] , $totalAmount,2));
            if($tenantinfo['site_id'] == 2){ // 只有独立租户才赠送
                $this->loginreward($uid,2,$info); // 登录赠送
                $info['login_num'] = getUserInfo($info['id'])['login_num'];
            }
            return $info;
        // $info['isreg']='0';
        // $info['isagent']='0';

        // if($info['last_login_time']==''){
        //     $info['isreg']='1';
        //     $info['isagent']='1';
        // }

        // $configpri=getConfigPri();
        // if($configpri['agent_switch']==0){
        //     $info['isagent']='0';
        // }
        // $uid=$info['id'];
        // /* 是否代理 */
        // $isexist=DI()->notorm->users_agent
        //     ->select('uid')
        //     ->where('uid=?',$uid)
        //     ->fetchOne();
        // if($isexist){
        //     $info['isagent']='0';
        // }
        // $vip_info = $this->getUserjurisdiction($uid,$info['user_type']);
        // $info['watch_number'] =  $vip_info['watch_number'];
        // $info['watch_duration'] =  $vip_info['watch_duration'];
        // $info['level_addtime'] = $vip_info['level_addtime'];
        // $info['level_endtime'] = $vip_info['level_endtime'];
        // $info['new_level'] = $vip_info['new_level'] ;
        // $info['level_name'] =  $vip_info['level_name'] ;
        // $info['user_vip_status'] = $vip_info['user_vip_status'];
        // $info['user_vip_action_type'] = $vip_info['user_vip_action_type'];
        // $info['level_name_jurisdiction'] = $vip_info['level_name_jurisdiction'];
        // $info['watchnum_show_ad_video'] = $vip_info['watchnum_show_ad_video'];
        // $info['level']=getLevel($info['consumption']);
        // $info['level_anchor']=getLevelAnchor($info['votestotal']);

        // $token=md5(md5($info['id'].$user_login.time()));

        // $info['token']=$token;


        // $this->updateToken($info['id'],$token);


        // return $info;
    }
    /*虚拟 会员登录 */
    public function userLoginvutar($user_login,$user_pass,$zone) {

        $user_pass=setPass($user_pass);
        $tenant_id = getTenantId();
        $info=DI()->notorm->users
            ->select($this->fields.',user_pass')
            ->where('user_login=? and user_type="3" and tenant_id = ?',$user_login,$tenant_id)
            ->fetchOne();
        if(!$info || $info['user_pass'] != $user_pass){
            return 1001;
        }
        unset($info['user_pass']);

        $info['level'] = getLevel($info['consumption']);

        $token=md5(md5($info['id'].$user_login.time()));

        $info['token']=$token;


        $this->updateTokenvutar($info['id'],$token);

        return $info;
    }


    /* 会员注册 */
    public function userReg($user_login,$user_pass,$source,$tenantId,$zone,$game_user_id,$nicename,$avatar,$avatar_thumb,$agent_code,$reg_url ='',$reg_key='') {

        $user_pass=setPass($user_pass);

        //获取游戏租户id
        $gameTenantId=getGameTenantId();

        $configpri=getConfigPri();
        $round_number = mt_rand(1,200);
        if ($round_number >100){
            $round_avatar =  $round_number.'.png';
        }else{
            $round_avatar =  $round_number.'.jpg';
        }
        $reg_reward= empty($configpri['reg_reward'])? $configpri['reg_reward']:0;
        $salt = createSalt();
        $data=array(
            'user_login' => $user_login,
            'mobile' =>$user_login,
            'user_nicename' =>empty($nicename)?'user'.substr($user_login,-4):$nicename,
            'user_pass' =>$user_pass,
            'payment_password' => '',
            'salt' => $salt,
            'signature' =>'这家伙很懒，什么都没留下',
            'avatar' => empty($avatar) ? '/public/images/head_'.$round_avatar : $avatar,
            'avatar_thumb' => empty($avatar_thumb) ? '/public/images/head_'.$round_avatar : $avatar_thumb,
            'last_login_ip' => $_SERVER['REMOTE_ADDR'],
            'create_time' => date("Y-m-d H:i:s"),
            'ctime' => time(),
            'user_status' => 1,
            "user_type"=>2,//会员
            "source"=>$source,
            "coin"=>$reg_reward,
            "tenant_id"=>$tenantId,
            'game_user_id'=> $game_user_id,
            'game_tenant_id'=>$gameTenantId,
            'zone'=>$zone,
            'pids'=>0,
            'pid'=> 0,
            'reg_url'=> $reg_url,
            'reg_key'=> $reg_key,
        );
        $isexist=DI()->notorm->users
            ->select('id')
            ->where(' (game_tenant_id=? or tenant_id=? ) and user_login=? and user_type  in (2,5,6) ',$gameTenantId,$tenantId,$user_login)
            ->fetchOne();

        if(!$isexist){
            //游戏系统用户id唯一检查
            $isexist=DI()->notorm->users
                ->select('id')
                ->where(' (game_tenant_id=? or tenant_id=?) and game_user_id=? and user_type in (2,5,6) ',$gameTenantId,$tenantId,$game_user_id)
                ->fetchOne();

        }

        if($isexist){
            return 1006;
        }

        // 如果有邀请码，则处理
        if($agent_code){
            //
            $agent_uid = DI()->notorm->users
            ->select('id')->where("agent_code=?",$agent_code)->fetchOne();
            if(empty($agent_uid['id'])){
                return 1007;
            }

            // $oneinfo=DI()->notorm->users_agent_code->where('code=? and tenant_id=? ',$agent_code,$tenantId)->fetchOne();
            // var_dump($oneinfo);exit;
            // if(!$oneinfo){
            //     $oneinfo=DI()->notorm->users_agent_code->select("uid")->where('code=?',$agent_code)->fetchOne();
            //     if(!$oneinfo){
            //         return 1002;
            //     }
            // }
            // $puser =DI()->notorm->users
            //     ->select('id,pids,user_type')
            //     ->where('id=? ',$oneinfo['uid'])
            //     ->fetchOne();
            // $data['pids'] =$puser['pids'].','.$puser['id'];
            // $data['pid'] = $puser['id'];
        }

        if ($data){
            $rs=DI()->notorm->users->insert($data);

        }
            
        if(!$rs){
            return 1007;
        }
        $uid=$rs['id'];

        /**
         用户注册，默认vip1一条
         */
        // $endtime = strtotime ("+1 day", strtotime($data['create_time']));
       /* $vipInfo  =DI()->notorm->vip->where("orderno =1")->order('id asc')->fetchOne();
        $data = array(
            'uid' =>$uid,
            'addtime' => time(),
            'endtime' => $endtime,
            'tenant_id' => getTenantId(),
            'vip_id' => $vipInfo['id'],
            'grade' =>1,
        );

        DI()->notorm->users_vip->insert($data);*/
        $code=$this->createCode();
        $code_info=array('uid'=>$uid,'code'=>$code,'tenant_id'=>$tenantId);
        $isexist=DI()->notorm->users_agent_code->select("*")->where('uid = ?',$uid)->fetchOne();
        if($isexist){
            DI()->notorm->users_agent_code->where('uid = ?',$uid)->update($code_info);
        }else{
            DI()->notorm->users_agent_code->insert($code_info);
        }
        // 如果有邀请码，则处理
        if($agent_code){
            if($agent_uid['id']){
                //
                $agent_data = array(
                    "agent_id" => $agent_uid['id'],
                    "invited_id" => $uid,
                    "addtime" => time(),
                    "status" => 0
                );
                DI()->notorm->users_invite->insert($agent_data);
            }
            
            // $agentinfo=getAgentInfo($oneinfo['uid']);
            // if(!$agentinfo){
            //     $agentinfo=array('uid'=>$oneinfo['uid'],'one_uid'=>0,'two_uid'=>0,'three_uid'=>0,'four_uid'=>0,);
            // }
            // $data=array(
            //     'uid'=>$uid,
            //     'user_login'=>$user_login,
            //     'one_uid'=>$agentinfo['uid'],
            //     'two_uid'=>$agentinfo['one_uid'],
            //     'three_uid'=>$agentinfo['two_uid'],
            //     'four_uid'=>$agentinfo['three_uid'],
            //     'five_uid'=>$agentinfo['four_uid'],
            //     'addtime'=>time(),
            //     'tenant_id'=>$tenantId,
            //     'user_type'=>$rs['user_type'],
            // );
            // DI()->notorm->users_agent->insert($data);

            // 获取积分注册配置
            // $integral_config = DI()->notorm->integral_config->select("*")->where('type=?',1)->fetchOne();
            // if(!$integral_config){
            //     $integral_config = ['level_1' => 0,'level_2' => 0,'level_3' => 0,'level_4' => 0,'level_5' => 0];
            // }
            // Cache_UsersAgent::getInstance()->delUserAllSubCache($tenantId, $agentinfo['uid']); // 移除用户-所有下级uid-缓存
            // // 计算上级用户积分
            // $this->addIntegralLog($rs,$agentinfo['uid'],$integral_config['level_1']);
            // if($agentinfo['one_uid']>0){
            //     $this->addIntegralLog($rs,$agentinfo['one_uid'],$integral_config['level_2']);
            //     Cache_UsersAgent::getInstance()->delUserAllSubCache($tenantId, $agentinfo['one_uid']); // 移除用户-所有下级uid-缓存
            // }
            // if($agentinfo['two_uid']>0){
            //     $this->addIntegralLog($rs,$agentinfo['two_uid'],$integral_config['level_3']);
            //     Cache_UsersAgent::getInstance()->delUserAllSubCache($tenantId, $agentinfo['two_uid']); // 移除用户-所有下级uid-缓存
            // }
            // if($agentinfo['three_uid']>0){
            //     $this->addIntegralLog($rs,$agentinfo['three_uid'],$integral_config['level_4']);
            //     Cache_UsersAgent::getInstance()->delUserAllSubCache($tenantId, $agentinfo['three_uid']); // 移除用户-所有下级uid-缓存
            // }
            // if($agentinfo['four_uid']>0){
            //     $this->addIntegralLog($rs,$agentinfo['four_uid'],$integral_config['level_5']);
            //     Cache_UsersAgent::getInstance()->delUserAllSubCache($tenantId, $agentinfo['four_uid']); // 移除用户-所有下级uid-缓存
            // }
        }else{
            $data=array(
                'uid'=>$uid,
                'user_login'=>$user_login,
                'one_uid'=>0,
                'two_uid'=>0,
                'three_uid'=>0,
                'four_uid'=>0,
                'five_uid'=>0,
                'addtime'=>time(),
                'tenant_id'=>$tenantId,
                'user_type'=>$rs['user_type'],
            );
            DI()->notorm->users_agent->insert($data);
        }
        $tenantinfo = getTenantInfo($tenantId);
        if($tenantinfo['site_id'] == 2){ // 只有独立租户才赠送
            $this->loginreward($uid,1,$rs); // 注册赠送
        }

        return 1;
    }

    /*
     * 用户积分记录
     * */
    public function addIntegralLog($reg_u_info,$up_uid,$change_integral){
        $up_info = getUserInfo($up_uid);
        $integraldata=array(
            'uid'=>$up_info['id'],
            'start_integral'=>$up_info['integral'],
            'change_integral'=>$change_integral,
            'end_integral'=>($up_info['integral']+$change_integral),
            'act_type'=>1,
            'status'=>1,
            'remark'=>$reg_u_info['user_login'].' 注册',
            'ctime'=>time(),
            'act_uid'=>$reg_u_info['id'],
            'tenant_id'=>$reg_u_info['tenant_id'],
        );
        DI()->notorm->users->where(['id'=>intval($up_info['id'])])->update(array('integral'=>new NotORM_Literal("integral+".$change_integral)));
        delUserInfoCache($up_info['id']);
        $res = DI()->notorm->integral_log->insert($integraldata);
        return $res;
    }

    /* 找回密码 */
    public function userFindPass($user_login,$user_pass,$zone){
        $tenantId=getTenantId();
        $isexist=DI()->notorm->users
            ->select('id')
            ->where('user_login=? and user_type in (2,5,6) and tenant_id=? and zone=?',$user_login,$tenantId,$zone)
            ->fetchOne();
        if(!$isexist){
            return 1006;
        }
        $user_pass=setPass($user_pass);

        return DI()->notorm->users
            ->where('id=?',$isexist['id'])
            ->update(array('user_pass'=>$user_pass));

    }

    /* 第三方会员登录 */
    public function userLoginByThird($openid,$type,$nickname,$avatar,$source) {
        $tenantId=getTenantId();
        $info=DI()->notorm->users
            ->select($this->fields)
            ->where('openid=? and login_type=? and tenant_id=?',$openid,$type,$tenantId)
            ->fetchOne();
        $configpri=getConfigPri();
        if(!$info){
            /* 注册 */
            $user_pass='youyukeji';
            $user_pass=setPass($user_pass);
            $user_login=$type.'_'.time().rand(100,999);

            if(!$nickname){
                $nickname=$type.'用户-'.substr($openid,-4);
            }else{
                $nickname=urldecode($nickname);
            }
            if(!$avatar){

                $avatar=$configpri['user_default_avatar'];
                $avatar_thumb=$configpri['user_default_avatar_thumb'];
            }else{
                $avatar=urldecode($avatar);
                $avatar_a=explode('/',$avatar);
                $avatar_a_n=count($avatar_a);
                if($type=='qq'){
                    $avatar_a[$avatar_a_n-1]='100';
                    $avatar_thumb=implode('/',$avatar_a);
                }else if($type=='wx'){
                    $avatar_a[$avatar_a_n-1]='64';
                    $avatar_thumb=implode('/',$avatar_a);
                }else{
                    $avatar_thumb=$avatar;
                }

            }
            $reg_reward=$configpri['reg_reward'];
            $data=array(
                'user_login' => $user_login,
                'user_nicename' =>$nickname,
                'user_pass' =>$user_pass,
                'signature' =>'这家伙很懒，什么都没留下',
                'avatar' =>$avatar,
                'avatar_thumb' =>$avatar_thumb,
                'last_login_ip' =>$_SERVER['REMOTE_ADDR'],
                'create_time' => date("Y-m-d H:i:s"),
                'ctime' => time(),
                'user_status' => 1,
                'openid' => $openid,
                'login_type' => $type,
                "user_type"=>2,//会员
                "source"=>$source,
                "coin"=>$reg_reward,
                "tenant_id"=>$tenantId
            );

            $rs=DI()->notorm->users->insert($data);

            $uid=$rs['id'];
            if($reg_reward>0){
                $insert=array("type"=>'income',"action"=>'reg_reward',"uid"=>$uid,"touid"=>$uid,"giftid"=>0,"giftcount"=>1,"totalcoin"=>$reg_reward,"showid"=>0,"addtime"=>time(),"tenant_id"=>$tenantId );
                $coinrecordModel = new Model_Coinrecord();
                $coinrecordModel->addCoinrecord($insert);
            }

            $code=$this->createCode();
            $code_info=array('uid'=>$uid,'code'=>$code,'tenant_id'=>$tenantId);
            $isexist=DI()->notorm->users_agent_code
                ->select("*")
                ->where('uid = ?',$uid)
                ->fetchOne();
            if($isexist){
                DI()->notorm->users_agent_code->where('uid = ?',$uid)->update($code_info);
            }else{
                DI()->notorm->users_agent_code->insert($code_info);
            }

            $info['id']=$uid;
            $info['user_nicename']=$data['user_nicename'];
            $info['avatar']=get_upload_path($data['avatar']);
            $info['avatar_thumb']=get_upload_path($data['avatar_thumb']);
            $info['sex']='2';
            $info['signature']=$data['signature'];
            $info['coin']='0';
            $info['login_type']=$data['login_type'];
            $info['province']='';
            $info['city']='';
            $info['birthday']='';
            $info['consumption']='0';
            $info['user_status']=1;
            $info['last_login_time']='';
        }else{
            if(!$avatar){
                $avatar=$configpri['user_default_avatar'];
                $avatar_thumb=$configpri['user_default_avatar_thumb'];
            }else{
                $avatar=urldecode($avatar);
                $avatar_a=explode('/',$avatar);
                $avatar_a_n=count($avatar_a);
                if($type=='qq'){
                    $avatar_a[$avatar_a_n-1]='100';
                    $avatar_thumb=implode('/',$avatar_a);
                }else if($type=='wx'){
                    $avatar_a[$avatar_a_n-1]='64';
                    $avatar_thumb=implode('/',$avatar_a);
                }else{
                    $avatar_thumb=$avatar;
                }

            }

            $info['avatar']=$avatar;
            $info['avatar_thumb']=$avatar_thumb;

            $data=array(
                'avatar' =>$avatar,
                'avatar_thumb' =>$avatar_thumb,
            );

        }

        if($info['user_status']=='0'){
            return 1001;
        }
        unset($info['user_status']);

        $info['isreg']='0';
        $info['isagent']='0';
        if($info['last_login_time']=='' ){
            $info['isreg']='1';
            $info['isagent']='1';
        }

        if($configpri['agent_switch']==0){
            $info['isagent']='0';
        }

        /* 是否代理 */
        $isexist=DI()->notorm->users_agent
            ->select('uid')
            ->where('uid=?',$uid)
            ->fetchOne();
        if($isexist){
            $info['isagent']='0';
        }

        unset($info['last_login_time']);

        $info['level']=getLevel($info['consumption']);

        $info['level_anchor']=getLevelAnchor($info['votestotal']);

        $token=md5(md5($info['id'].$openid.time()));

        $info['token']=$token;
        $info['avatar']=get_upload_path($info['avatar']);
        $info['avatar_thumb']=get_upload_path($info['avatar_thumb']);
        $info['tenant_id']=$tenantId;

        $this->updateToken($info['id'],$token);

        return $info;
    }

    /* 更新token 登陆信息 */
    public function updateToken($uid,$token,$data=array(),$last_login_ip='') {
        $expiretime=time()+60*60*24*300;
        if (empty($last_login_ip)){
            $last_login_ip = get_client_ip();
        }
        $updateData=array("token"=>$token, "expiretime"=>$expiretime ,'last_login_time' => date("Y-m-d H:i:s"), "last_login_ip"=>$last_login_ip );
        $updateData=array_merge($data,$updateData);

        $userInfo=getUserInfo($uid);

        DI()->notorm->users
            ->where('id=?',$uid)
            ->update($updateData);

        $token_info=array(
            'uid'=>$uid,
            'token'=>$token,
            'expiretime'=>$expiretime,
            'tenant_id'=>$userInfo['tenant_id']
        );

        setcaches("token_".$uid,$token_info);

        delUserInfoCache($uid);
        return 1;
    }
    /* 虚拟更新token 登陆信息 */
    public function updateTokenvutar($uid,$token,$data=array(), $tenant_id = 0) {
        $expiretime=time()+60*60*24*300;

        $updateData=array("token"=>$token, "expiretime"=>$expiretime ,'last_login_time' => date("Y-m-d H:i:s"), "last_login_ip"=>$_SERVER['REMOTE_ADDR'] );
        $updateData=array_merge($data,$updateData);

        if(!$tenant_id){
            $userInfo = DI()->notorm->users->where('id=?', $uid)->fetchOne(); // getUserInfo($uid);
            $tenant_id = $userInfo['tenant_id'];
        }

        DI()->notorm->users
            ->where('id=?',$uid)
            ->update($updateData);

        $token_info=array(
            'uid'=>$uid,
            'token'=>$token,
            'expiretime'=>$expiretime,
            'tenant_id'=>$tenant_id
        );
        setcaches("token_".$uid,$token_info);

        return 1;
    }


    /* 生成邀请码 */
    public function createCode($len=6,$format='ALL2'){
        $is_abc = $is_numer = 0;
        $password = $tmp ='';
        $tenantId=getTenantId();
        switch($format){
            case 'ALL':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
            case 'ALL2':
                $chars='ABCDEFGHJKLMNPQRSTUVWXYZ0123456789';
                break;
            case 'CHAR':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 'NUMBER':
                $chars='0123456789';
                break;
            default :
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
        }

        while(strlen($password)<$len){
            $tmp =substr($chars,(mt_rand()%strlen($chars)),1);
            if(($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR'){
                $is_numer = 1;
            }
            if(($is_abc <> 1 && preg_match('/[a-zA-Z]/',$tmp)) || $format == 'NUMBER'){
                $is_abc = 1;
            }
            $password.= $tmp;
        }
        if($is_numer <> 1 || $is_abc <> 1 || empty($password) ){
            $password = self::createCode($len,$format);
        }

        if($password!=''){

            $oneinfo=DI()->notorm->users_agent_code
                ->select("uid")
                ->where("code=? and tenant_id=?",$password,$tenantId)
                ->fetchOne();

            if(!$oneinfo){
                return $password;
            }
        }
        $password = self::createCode($len,$format);
        return $password;
    }

    /* 更新极光ID */
    public function upUserPush($uid,$pushid){

        $tenantId=getTenantId();
        $isexist=DI()->notorm->users_pushid
            ->select('*')
            ->where('uid=?',$uid)
            ->fetchOne();
        if(!$isexist){
            DI()->notorm->users_pushid->insert(array('uid'=>$uid,'pushid'=>$pushid,'tenant_id'=>$tenantId));
        }else if($isexist['pushid']!=$pushid){
            DI()->notorm->users_pushid->where('uid=?',$uid)->update(array('pushid'=>$pushid,'tenant_id'=>$tenantId));
        }
        return 1;
    }

    public function userGetcode($user_login){
        $user=DI()->notorm->users
            ->select('id')
            ->where('user_login=?',$user_login)
            ->fetchOne();
        $userinfo = getUserInfo($user['id']);
        $code=DI()->notorm->users_agent_code
            ->select('code')
            ->where('uid=?',$user['id'])
            ->fetchOne();
        $userinfo['agent_code'] = $code['code'];
        $token=md5(md5($user['id'].$user_login.time()));
        $userinfo['token']=$token;
        $vip_info = $this->getUserjurisdiction($user['id'],2);

        $userinfo['watch_number'] =  $vip_info['watch_number'];
        $userinfo['watch_duration'] =  $vip_info['watch_duration'];
        $userinfo['level_addtime'] = $vip_info['level_addtime'];
        $userinfo['level_endtime'] = $vip_info['level_endtime'];
        $userinfo['level_name'] =  $vip_info['level_name'];
        $userinfo['new_level'] =  $vip_info['new_level'];
        $userinfo['user_vip_status'] = $vip_info['user_vip_status'];
        $userinfo['user_vip_action_type'] = $vip_info['user_vip_action_type'];
        $userinfo['watchnum_show_ad_video'] = $vip_info['watchnum_show_ad_video'];
        $this->updateTokenvutar($user['id'],$token);
        return $userinfo;


    }

    public  function invutar(){

        $timeinfo = date('YmdHis',time()).mt_rand(1000,9999);
        $round_number = mt_rand(1,200);
        if ($round_number >100){
            $round_avatar =  $round_number.'.png';
        }else{
            $round_avatar =  $round_number.'.jpg';
        }
        $avatar = '/public/images/h5_avatar.jpg';
        $data['user_login']= $timeinfo;
        $data['mobile']= $timeinfo;
        $data['user_nicename']= 'visitor'.substr($data['user_login'],-4);
        $data['user_pass'] = 	setPass('abc123456');
        $data['signature'] ='这家伙很懒，什么都没留下';
        $data['avatar'] = $avatar;
        $data['avatar_thumb'] = $avatar;
        $data['last_login_ip'] =$_SERVER['REMOTE_ADDR'];
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['ctime'] = time();
        $data['user_status'] = 1;
        $data['user_type']=4;   // 第四类型，标识 游客
        $data['source'] ='app';
        $data["coin"]=0;
        $data["tenant_id"]= getTenantId();

        $data['game_tenant_id']=getGameTenantId();
        $data['zone']=86;

        $res_info = DI()->notorm->users->insert($data);
        $info = DI()->notorm->users->select($this->fields.',user_pass')->where("id = ?", $res_info['id'])->fetchOne();
        if(!$info){
            sleep(1);
            $info = DI()->notorm->users->select($this->fields.',user_pass')->where("id = ?", $res_info['id'])->fetchOne();
        }

        // $info['avatar'] = get_upload_path($info['avatar']);

        $token=md5(md5($info['id'].$info['user_login'].time()));
        $info['token']=$token;
        $this->updateTokenvutar($info['id'],$token, [], $info['tenant_id']);
        $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = 'vip0'")->fetchOne();
        $info['watch_number'] =  $level_name_jurisdiction['watch_number'] + $info['watch_num'];
        $info['watch_duration'] =  $level_name_jurisdiction['watch_duration'] + $info['watch_time'];
        $info['watchnum_show_ad_video'] = 0;
        if (empty($level_name_jurisdiction['jurisdiction_id'])){
            $info['level_name_jurisdiction'] = array();
        }else{
            $level_name_jurisdiction_all =  DI()->notorm->reception_meun->where("id in ({$level_name_jurisdiction['jurisdiction_id']})")->fetchAll();
            $info['level_name_jurisdiction'] = $level_name_jurisdiction_all;
        }
        $code=$this->createCode();
        $code_info=array('uid'=>$info['id'],'code'=>$code,'tenant_id'=>getTenantId());
        $isexist=DI()->notorm->users_agent_code->select("*")->where('uid = ?',$info['id'])->fetchOne();
        if($isexist){
            DI()->notorm->users_agent_code->where('uid = ?',$info['id'])->update($code_info);
        }else{
            DI()->notorm->users_agent_code->insert($code_info);
        }
        return $info;
    }


    public  function getUserjurisdiction($uid,$user_type){
        $userVip = getUserVipInfo($uid);
        $userVipChecking = getUserVipCheckingInfo($uid);
        $vipinfo = array();
        $config  = getConfigPub();
        $tenant_id = getTenantId();
        if ($userVip){ // 如果是vip
            if ($config['vip_model'] ==1){
                $nft_rate=DI()->notorm->vip_grade->select("nft_rate")->where("vip_grade ='{$userVip['grade']}' and  tenant_id = '{$tenant_id}'")
                    ->fetchOne();
                $vipinfo = getVipInfo($userVip['vip_id']);
                $vipinfo['level_name'] = $vipinfo['name'];
                $vipinfo['new_level'] = $vipinfo['orderno'];
                $vipinfo['nft_rate'] = isset($nft_rate['nft_rate']) ? $nft_rate['nft_rate']:'0';
                $vipinfo['user_vip_status'] = $userVipChecking ? intval($userVipChecking['status']) : intval($userVip['status']);
                $vipinfo['user_vip_action_type'] = $userVipChecking ? intval($userVipChecking['action_type']) : intval($userVip['action_type']);
            }else{
                $vipinfo=DI()->notorm->vip_grade->select("*")->where("vip_grade ='{$userVip['grade']}' and  tenant_id = '{$tenant_id}'")
                   ->fetchOne();
                $vipinfo['level_name'] = $vipinfo['name'];
                $vipinfo['new_level'] = $vipinfo['vip_grade'];
                $vipinfo['nft_rate'] = $vipinfo['nft_rate'];
                $vipinfo['user_vip_status'] = $userVipChecking ? intval($userVipChecking['status']) : intval($userVip['status']);
                $vipinfo['user_vip_action_type'] = $userVipChecking ? intval($userVipChecking['action_type']) : intval($userVip['action_type']);
            }
            $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = '{$vipinfo['name']}'  and  tenant_id = '{$tenant_id}'")->fetchOne();

            if (empty($level_name_jurisdiction['jurisdiction_id'])){
                $vipinfo['level_name_jurisdiction'] = array();
            }else{
                $level_name_jurisdiction_all =  DI()->notorm->reception_meun->where("id in ({$level_name_jurisdiction['jurisdiction_id']})")->fetchAll();
                $vipinfo['level_name_jurisdiction'] = $level_name_jurisdiction_all;
            }
            $vipinfo['watch_number'] =  $level_name_jurisdiction['watch_number'];
            $vipinfo['watch_duration'] =  $level_name_jurisdiction['watch_duration'];
            $vipinfo['level_addtime'] = date('Y-m-d H:i:s',$userVip['addtime']);
            $vipinfo['level_endtime'] = date('Y-m-d H:i:s',$userVip['endtime']);
            $vipinfo['watchnum_show_ad_video'] = $level_name_jurisdiction['watchnum_show_ad_video'];
            $vipinfo['user_vip_create_time'] = date('Y-m-d H:i:s', $userVip['addtime']);
            $vipinfo['user_vip_update_time'] = date('Y-m-d H:i:s', $userVip['updated_time']);
            $vipinfo['user_vip_refund_time'] = $userVip['refund_time'] ? date('Y-m-d H:i:s', $userVip['refund_time']) : '';
        }else{
            $vipinfo['new_level'] = '0';
            $vipinfo['nft_rate']  = '0';
            $vipinfo['level_name'] = $config['initial_membership'];
            $vipinfo['user_vip_status'] = $userVipChecking ? intval($userVipChecking['status']) : 0;
            $vipinfo['user_vip_action_type'] = $userVipChecking ? intval($userVipChecking['action_type']) : 0;
            if (in_array($user_type,[2,5,6,7])){
                $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = 'vip0' and  tenant_id = '{$tenant_id}' ")->fetchOne();
                if (empty($level_name_jurisdiction['jurisdiction_id'])){
                    $vipinfo['level_name_jurisdiction'] = array();
                }else{
                    $level_name_jurisdiction_all =  DI()->notorm->reception_meun->where("id in ({$level_name_jurisdiction['jurisdiction_id']})")->fetchAll();
                    $vipinfo['level_name_jurisdiction'] = $level_name_jurisdiction_all;
                }
            }elseif ($user_type == 4){
                $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = 'vip0' and  tenant_id = '{$tenant_id}'")->fetchOne();
                if (empty($level_name_jurisdiction['jurisdiction_id'])){
                    $vipinfo['level_name_jurisdiction'] = array();
                }else{
                    $level_name_jurisdiction_all =  DI()->notorm->reception_meun->where("id in ({$level_name_jurisdiction['jurisdiction_id']})")->fetchAll();
                    $vipinfo['level_name_jurisdiction'] = $level_name_jurisdiction_all;
                }
            }
            $vipinfo['watch_number'] =  isset($level_name_jurisdiction['watch_number']) ? $level_name_jurisdiction['watch_number'] : '0';
            $vipinfo['watch_duration'] =  isset($level_name_jurisdiction['watch_duration']) ? $level_name_jurisdiction['watch_duration'] : '0';
            $vipinfo['level_addtime'] = '0';
            $vipinfo['level_endtime'] = '0';
            $vipinfo['watchnum_show_ad_video'] = isset($level_name_jurisdiction['watchnum_show_ad_video']) ? $level_name_jurisdiction['watchnum_show_ad_video'] : '0';
            $vipinfo['limit_upload_video_count'] = isset($level_name_jurisdiction['limit_upload_video_count']) ? intval($level_name_jurisdiction['limit_upload_video_count']) : 0;
            $vipinfo['user_vip_create_time'] = '';
            $vipinfo['user_vip_update_time'] = '';
            $vipinfo['user_vip_refund_time'] = '';
        }
        $vipinfo['user_vip_checking_level'] = $userVipChecking ? intval($userVipChecking['grade']) : 0;
        return $vipinfo;
    }

    /*
    * 首次登录赠送
    * $type 2登录
    * */
    public function firlogregReward($uid,$type)
    {
        $redis = connectionRedis();
        $userModel = new Model_User();
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
        if($userInfo['user_type'] == 4) { // 游客不允许操作
            return array('code' => 2005, 'msg' => codemsg('2005'), 'info' => array());
        }
        if($userInfo['isglr'] == 1) { // 已经领取首次登录奖励
            return array('code' => 2092, 'msg' => codemsg('2092'), 'info' => array());
        }
        if($userInfo['login_num'] > 1 || whichTenat($userInfo['game_tenant_id']) == 1 || $type != 2) { // 只有第一次登录的独立租户才赠送,且赠送过的不再赠送
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => array());
        }
        $loginreward = DI()->notorm->task_loginreward->select('*')->where('type=?',1)->fetchOne();
        $users_coinre_coin = array('type'=>'income','uid'=>$uid,'addtime'=>time(),'tenant_id'=>$userInfo['tenant_id']);
        $users_coinre_withd_coding = array('type'=>'inccoding','uid'=>$uid,'addtime'=>time(),'tenant_id'=>$userInfo['tenant_id']);
        $users_coinre_nowithd_coin = array('type'=>'income_nowithdraw','uid'=>$uid,'addtime'=>time(),'tenant_id'=>$userInfo['tenant_id']);

        $reward_level = array(
            array('val' => '1','prob' => 5),
            array('val' => '2','prob' => 15),
            array('val' => '3','prob' => 80),
        );
        $reward_level = getProbVal($reward_level);

        $coin = $reward_level == '1' ? $loginreward['firstlog_withdrawable_coin'] : $loginreward['firstlog_withdrawable_coin'.$reward_level];
        $withd_coding = $loginreward['firstlog_withdrawable_coding'];
        $nowithd_coin = $reward_level == '1' ? $loginreward['firstlog_nowithdrawable_coin'] : $loginreward['firstlog_nowithdrawable_coin'.$reward_level];

        $users_coinre_coin['action'] = 'firstlogin_reword';
        $users_coinre_withd_coding['action'] = 'firstlogin_reword';
        $users_coinre_nowithd_coin['action'] = 'firstlogin_reword';

        $users_coinre_coin['totalcoin'] = floatval($coin);
        $users_coinre_withd_coding['totalcoin'] = intval($withd_coding);
        $users_coinre_nowithd_coin['totalcoin'] = intval($nowithd_coin);

        $users_coinre_coin['pre_balance'] = $userInfo['coin'];
        $users_coinre_withd_coding['pre_balance'] = $userInfo['withdrawable_coding'];
        $users_coinre_nowithd_coin['pre_balance'] = $userInfo['nowithdrawable_coin'];

        $users_coinre_coin['after_balance'] = floatval(bcadd($userInfo['coin'], $coin,4));
        $users_coinre_withd_coding['after_balance'] = floatval(bcadd($userInfo['withdrawable_coding'], $withd_coding,4));
        $users_coinre_nowithd_coin['after_balance'] = floatval(bcadd($userInfo['nowithdrawable_coin'], $nowithd_coin,4));


        $users_coinre_coin['user_login'] = $userInfo['user_login'];
        $users_coinre_withd_coding['user_login'] = $userInfo['user_login'];
        $users_coinre_nowithd_coin['user_login'] = $userInfo['user_login'];

        $users_coinre_coin['user_type'] = $userInfo['user_type'];
        $users_coinre_withd_coding['user_type'] = $userInfo['user_type'];
        $users_coinre_nowithd_coin['user_type'] = $userInfo['user_type'];

        if($coin>0){
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($users_coinre_coin); // 可提现金币变动记录
        }
        if($withd_coding>0){
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($users_coinre_withd_coding); // 可提现金币打码量
        }
        if($nowithd_coin>0){
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($users_coinre_nowithd_coin); // 不可提现金币
        }
        DI()->notorm->users->where(['id'=>intval($uid)])->update(array(
            'coin'=>new NotORM_Literal("coin+".$coin),
            'nowithdrawable_coin'=>new NotORM_Literal("nowithdrawable_coin+".$nowithd_coin),
            'withdrawable_coding'=>new NotORM_Literal("withdrawable_coding+".$withd_coding),
            'isglr' => 1,
        ));
        delUserInfoCache($uid);

        if($nowithd_coin>0){
            $redisCone = connectRedis();
            $keytime = time();
            $config  =getConfigPub();
            $redisCone->lPush($uid . '_reward_time', $keytime);// 存用户 时间数据key
            $amount = $redisCone->get($uid . '_' . $keytime.'_reward');
            $totalAmount = bcadd($nowithd_coin, $amount, 2);
            $redisCone->set($uid . '_' . $keytime.'_reward', $totalAmount);// 存佣金
            $expireTime = time() + $config['withdrawal_time'] * 86400;
            /** 86400*/
            $redisCone->expireAt($uid . '_' . $keytime.'_reward', $expireTime);// 设置过去时间
        }
        return array('code' => 0, 'msg' => '', 'info' => array([
            'coin'=>$coin,
            'nowithdrawable_coin'=>$nowithd_coin,
            'withdrawable_coding'=>$withd_coding,
            'reward_level' => $reward_level,
        ]));
    }

}



