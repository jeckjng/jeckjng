<?php

class Model_Guard extends PhalApi_Model_NotORM {
	/* 守护用户列表 */
	public function getGuardList($data) {
        
        $rs=array();
        
        $liveuid=$data['liveuid'];

        $nowtime=time();
        $w=date('w',$nowtime); 
        //获取本周开始日期，如果$w是0，则表示周日，减去 6 天 
        $first=1;
        //周一
        $week=date('Y-m-d H:i:s',strtotime( date("Ymd",$nowtime)."-".($w ? $w - $first : 6).' days')); 
        $week_start=strtotime( date("Ymd",$nowtime)."-".($w ? $w - $first : 6).' days'); 

        //本周结束日期 
        //周天
        $week_end=strtotime("{$week} +1 week");

        $tenant_id = getTenantId();
        $guard_list = $this->getList($tenant_id);

        //获取守护机器人
        $robotuserInfo = $this->getGuardrobot($liveuid);
		$lists=DI()->notorm->guard_users
                    ->select('uid,type')
                    ->where('liveuid=? and endtime>?',$liveuid,time())
                    //->order("type desc")
                    ->fetchAll();
        $list =  array_merge($lists,$robotuserInfo);
        foreach($list as $k=>$v){
            $userinfo=getUserInfo($v['uid']);
            if(isset($v['user_type'])){
                $levelinfo = getLevelInfo($v['consumption']);
            }else{
                $levelinfo = getLevelInfo($userinfo['consumption']);
            }
            $userinfos['id']=$userinfo['id'];
            $userinfos['user_login']=$userinfo['user_login'];
            $userinfos['user_nicename']=$userinfo['user_nicename'];
            $userinfos['avatar']=$userinfo['avatar'];
            $userinfos['avatar_thumb']=$userinfo['avatar_thumb'];
            $userinfos['levelid']=$levelinfo['levelid'];
            $userinfos['levelname']=$levelinfo['levelname'];
            $userinfos['levelthumb']=$levelinfo['thumb'];
            $userinfos['guard_type']=$v['type'];
            if(isset($v['user_type'])){
                $userinfos['contribute']= $v['contribute'];
                $nobel  = getNoblevirtual($v['uid']);
            }else{
                $userinfos['contribute']=$this->getWeekContribute($v['uid'],$week_start,$week_end);
                $nobel = getUserNoble($v['uid']);
            }

            if(is_array($nobel)){
                $userinfos['medal'] = $nobel['medal'];
            }else{
                $userinfos['medal'] ='';
            }
            foreach ($guard_list as $key=>$value){
                if ($v['type'] == $value['type']){
                    $userinfos['guard_img']=$value['guard_img'];
                    continue;
                }
            }
            $rs[]=$userinfos;
        }

        $rss = array_column($rs,'contribute');
        array_multisort($rss,SORT_DESC,$rs);


		return $rs;
	}			
    
    public function getWeekContribute($uid,$starttime=0,$endtime=0){
        $contribute='0';
        if($uid>0){
            $where="action in ('sendgift','buyguard') and uid = {$uid}";
            if($starttime>0 ){
               $where.=" and addtime > {$starttime}";
            }
            if($endtime>0 ){
               $where.=" and addtime < {$endtime}";
            }
            
            $contribute=DI()->notorm->users_coinrecord
                    ->where($where)
                    ->sum('totalcoin');
            if(!$contribute){
                $contribute=0;
            }
        }
        
        return (string)$contribute;
    }

    /* 守护信息列表 */
    public function getList($tenantId){
		$list=DI()->notorm->guard
                    ->select('id,name,type,coin,guard_img,length_type,is_gift,is_shutup')
                    ->where('tenant_id = '.$tenantId)
                    ->order("orderno asc")
                    ->fetchAll();

        return $list;
    }
    /* 彩票租户购买守护 */
    public function buyGuard($data) {

        try {
            $redis = connectionRedis();
            //开始数据库事务
            beginTransaction();
            $uid=$data['uid'];
            $liveuid=$data['liveuid'];
            $stream=$data['stream'];
            $guardid=$data['guardid'];

            $liveUserInfo=getUserInfo($liveuid);
            $userInfo=getUserInfo($uid);
            $liveUserTenantInfo=getTenantInfo($liveUserInfo['tenant_id']);
            $anchortenant_name=$liveUserTenantInfo['name'];
            $userTenantInfo=getTenantInfo($userInfo['tenant_id']);
            $sendtenant_name=$userTenantInfo['name'];


            /* 守护信息 */
            $guardinfo=DI()->notorm->guard
                ->select('*')
                ->where('id=?',$guardid)
                ->fetchOne();
            if(!$guardinfo){
                $rs['code'] = 1001;
                $rs['msg'] = '守护信息不存在';
                return $rs;
            }
            $total= $guardinfo['coin'];



            $addtime=time();
            $isexist=DI()->notorm->guard_users
                ->select('*')
                ->where('uid = ? and liveuid=?', $uid,$liveuid)
                ->fetchOne();
            if($isexist && $isexist['endtime'] > $addtime && $isexist['type'] > $guardinfo['type'] ){
                $rs['code'] = 1004;
                $rs['msg'] = '不能购买比当前守护，更低的守护了';
                return $rs;
            }



            $addtime=time();
            $isexist=DI()->notorm->guard_users
                ->select('*')
                ->where('uid = ? and liveuid=?', $uid,$liveuid)
                ->fetchOne();

            if($isexist && $isexist['endtime'] > $addtime && $isexist['type'] > $guardinfo['type'] ){
                $rs['code'] = 1004;
                $rs['msg'] = '已经是尊贵守护了，不能购买普通守护';
                return $rs;
            }
            $endtime=$addtime + $guardinfo['length_time'];
            if($isexist){
                if($isexist['type'] == $guardinfo['type'] && $isexist['endtime'] > $addtime){
                    /* 同类型未到期 只更新到期时间 */
                    DI()->notorm->guard_users
                        ->where('id = ? ', $isexist['id'])
                        ->update( array('endtime' => new NotORM_Literal("endtime + {$guardinfo['length_time']}")));
                    $result['msg']='续费成功';

                }else{
                    $data=array(
                        'type'=>$guardinfo['type'],
                        'endtime'=>$endtime,
                        'addtime'=>$addtime,
                        'guard_effect'=>$guardinfo['guard_effect'],
                    );
                    DI()->notorm->guard_users
                        ->where('id = ? ', $isexist['id'])
                        ->update( $data );
                }
            }else{
                $data=array(
                    'uid'=>$uid,
                    'liveuid'=>$liveuid,
                    'type'=>$guardinfo['type'],
                    'endtime'=>$endtime,
                    'addtime'=>$addtime,
                    'tenant_id'=>getTenantId(),
                    'guard_effect'=>$guardinfo['guard_effect'],

                );
                DI()->notorm->guard_users
                    ->insert( $data );

            }

            $type='expend';
            $action='buyguard';
            $giftid= $guardinfo['id'];



            $consumption = $userInfo['consumption'] +$total;
            $level = intval(getLevel($consumption));

            // 用户等级升级加成，根据加成的比例，增加用户等级经验
            $user_noble = getUserNoble($uid);
            $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($total,(1+$user_noble['upgrade_speed']/100),2) : $total;
            //增加消费总额
            $ifok=DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('consumption' => new NotORM_Literal("consumption + {$u_consumption}"),'userlevel' =>$level ) );
            delUserInfoCache($uid);
            $userInfo = getUserInfo($uid);
            // 累计消费变动，通知前端
            $redis->zAdd('user_consumptionchange',1,json_encode(['uid'=>$uid,'liveuid'=>$liveuid,'level'=>getLevel($userInfo['consumption']),'consumption'=>$userInfo['consumption'],'exp_can_speak'=>exp_can_speak($userInfo['consumption'])]));
            if(!$ifok){
                return array('code' => 1001, 'msg' => codemsg('2006'), 'info' => array("增加消费总额 失败"));
            }
            //查询余额
             $coinInfo=getGameUserBalance($userInfo['game_tenant_id'],$userInfo['game_user_id']);

            if($coinInfo['code']!=0){
                //如果code不等于0为请求失败
                //请求失败时余额返回0
                $coin=0;
            }
            else{
                $coin=$coinInfo['coin'];
            }
            if($coin<$total){
                //余额不足
                return array('code' => 1001, 'msg' => codemsg('2006'), 'info' => array($coin,$total));
            }

            //分销和家族分销功能关闭 ,暂时保留

            /* 分销 */
            //setAgentProfit($uid,$total);
            /* 分销 */


            //TODO 计算分润
            $config=getConfigPub();
            $anchor_tenant_profit_ratio=$config['anchor_tenant_profit_ratio']/100;
            //消费者所属租户分润比例
            $user_tenant_profit_ratio=$config['user_tenant_profit_ratio']/100;
            //主播分润比例
            $anchor_profit_ratio=$config['anchor_profit_ratio']/100;
            //配置比例转换成金额
            $anthor_total=round($total/$config['money_rate'],3);
            //租户分润金额
            $tenant_total = $anchor_tenant_profit_ratio*$anthor_total;
            //消费者所属分润金额
            $tenantuser_total = $user_tenant_profit_ratio*$anthor_total;

            $res = $this->setCommission($uid,$liveuid,$anthor_total,$anchor_profit_ratio);

            $anthor_total  =    $res['anthor_total'];
            $family_total  =    $res['family_total'];
            //家族长及时到账
            $familyhead_id    =    $res['family_id'];
            $familyhead_money =    $family_total-$anthor_total;

            //记录打赏列表
            $profitinsert = array(
                'status'=>'0',
                'uid'=>$liveuid,   //直播会员ID
                'addtime'=>time(),
                'anchor_tenant'=>$liveUserInfo['tenant_id'],
                'send_tenant'=>$userInfo['tenant_id'],
                'anchor_money'=>$tenant_total,
                "send_money"=>$tenantuser_total,
                'anthor_total' =>$anthor_total,
                'family_total' =>$family_total,
                'is_type'=>1,
            );

            $isTeanntid= DI()->notorm->profit_sharing->insert($profitinsert);


            /* 更新直播 魅力值 累计魅力值 */
            $istouid =DI()->notorm->users
                ->where('id = ?', $liveuid)
                ->update( array('votes' => new NotORM_Literal("votes + {$anthor_total}"),'votestotal' => new NotORM_Literal("votestotal + {$total}") ));
            delUserInfoCache($liveuid);
            $insert_votes=[
                'type'=>'income',
                'action'=>$action,
                'uid'=>$liveuid,
                'votes'=>$anthor_total,
                'addtime'=>time(),
                'tenant_id' =>$liveUserInfo['tenant_id']
            ];
            DI()->notorm->users_voterecord->insert($insert_votes);

            $stream2=explode('_',$stream);
            $showid=$stream2[1];

            $insert=array(
                "type"=>$type,"action"=>$action,
                "uid"=>$uid,"touid"=>$liveuid,"giftid"=>$giftid,
                "giftcount"=>1,"totalcoin"=>$total,
                "showid"=>$showid,"mark"=>0,"addtime"=>$addtime,'tenant_id' =>$userInfo['tenant_id'],
                'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],'anthor_total'=>$anthor_total,
                'tenant_total'=>$tenant_total,'tenantuser_total'=>$tenantuser_total,'family_total'=>$family_total,
                "cd_ratio"=>'1:'.floatval($config['money_rate']),'familyhead_total'=>$familyhead_money
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);

            /**
             * 主播收入记录
             */
            $insertanchor=array(
                "type"=>'income',
                "action"=>$action,
                "uid"=>$liveuid,
                "touid"=>$liveuid ,
                "giftid"=>$giftid,
                "giftcount"=>1,
                "totalcoin"=>$anthor_total*$config['money_rate'],
                "showid"=>$showid,
                "mark"=>0,
                "addtime"=>$addtime,
                'tenant_id' =>$liveUserInfo['tenant_id'],
                'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],
                'anthor_total'=>$anthor_total,
                'tenant_total'=>$tenant_total,
                'tenantuser_total'=>$tenantuser_total,
                'family_total'=>$family_total,
                "cd_ratio"=>'1:'.floatval($config['money_rate']),
                'familyhead_total'=>$familyhead_money,
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insertanchor);
            //家族长及时到账

            if($res['family_id'] != 0){

                /**
                 * 家族长收入记录
                 */
                $insertfamily=array(
                    "type"=>'income',
                    "action"=>$action,
                    "uid"=>$res['family_user_id'],
                    "touid"=>$liveuid ,
                    "giftid"=>$giftid,
                    "giftcount"=>1,
                    "totalcoin"=>$familyhead_money*$config['money_rate'],
                    "showid"=>$showid,
                    "mark"=>0,
                    "addtime"=>$addtime,
                    'tenant_id' =>$liveUserInfo['tenant_id'],
                    'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],
                    'anthor_total'=>$anthor_total,
                    'tenant_total'=>$tenant_total,
                    'tenantuser_total'=>$tenantuser_total,
                    'family_total'=>$family_total,
                    "cd_ratio"=>'1:'.floatval($config['money_rate']),
                    'familyhead_total'=>$familyhead_money,
                );

                $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insertfamily);
            }
            //分润详情
            $datadetail = array(
                'uid'=>  $uid,
                'user_login'=>  $userInfo['user_nicename'],
                'addtime'=>  time(),
                'amount'=>  1,
                'content'=> '发送礼物',
                'money'=> round($total/$config['money_rate'],3),
                'type'=>1,

            );

            //主播平台分润
            $this-> zhubo_tenant($liveUserInfo,$anchortenant_name,$tenant_total,$anchor_tenant_profit_ratio,$datadetail,$sendtenant_name);

            //消费平台分润
            $this-> xiaofei_tenant($userInfo,$sendtenant_name,$tenantuser_total,$user_tenant_profit_ratio,$datadetail);
            //主播分润
            $this->zhubo_update($liveUserInfo,$anthor_total,$res,$datadetail,$sendtenant_name);

            //家族长分润
            $this->family_update($res,$familyhead_money,$datadetail,$sendtenant_name);




            /* 清除缓存 */
            delCache("userinfo_".$uid);
            delCache("userinfo_".$liveuid);



            //调用余额更新接口
            $money_rate=$config['money_rate'];

            $useridGame=$userInfo['game_user_id'];
            $useridLive=$userInfo['id'];
            $tidGame=$userInfo['game_tenant_id'];
            $tidLive=$userInfo['tenant_id'];
            $usernickname=$userInfo['user_nicename'];
            //金额=钻石/转换比例四舍五入
            $amount=round($total/$money_rate,2) ;
            $diamond=$total;
            $type=1;
            $detail='购买守护';
            $roomid=$liveuid;
            $anchorid=$liveuid;
            $anchorname=$liveUserInfo['user_nicename'];
            $anchorfromid=$liveUserInfo['tenant_id'];
            $anchorformname=$liveUserTenantInfo['name'];
            $tId=$userInfo['game_tenant_id'];
            $custId=$userInfo['game_user_id'];
            $custAnchorid=$liveUserInfo['game_user_id'];
            $anchorTenantid=$liveUserInfo['game_tenant_id'];
            $guard=DI()->notorm->guard_users
                ->select('type,endtime')
                ->where('uid = ? and liveuid=?', $uid,$liveuid)
                ->fetchOne();
            if($guard && isset($guard['type'])){
                $guardlist=DI()->notorm->guard
                    ->select('id,position_first,position_second,guard_effect,guard_img')
                    ->where('type=?', $guard['type'])
                    ->fetchOne();
            }
            $guard = array_merge($guard,$guardlist);
            $guard_nums=$this->getGuardNums($liveuid);
            $votestotal=$this->getVotes($liveuid);
            $result['info']=array("uid"=>$uid,"totalcoin"=>$total,"level"=>$level,"coin"=>$coin-$total,"votestotal"=>$votestotal,"guard_nums"=>intval($guard_nums),"type"=>intval($guard['type']),'endtime'=>date("Y.m.d H:m:s",$guard['endtime']),'guard_img'=>$guard['guard_img']);


            $updateResult= reduceGameUserBalance($useridGame,$useridLive,$tidGame,$tidLive,$usernickname,$amount,$diamond,$type,$detail,$roomid,$anchorid,$anchorname,$anchorfromid,$anchorformname,$tId,$custId,$custAnchorid,$anthor_total,$anchorTenantid,$familyhead_id,$familyhead_money,$level);
            if($updateResult['code']!=0){
                  if($updateResult['code']==1002){
                      rollbackTransaction();
                      //调用失败,回滚事务,并返回网络错误
                      logapi(['updateResult'=>$updateResult],'【购买更新java余额失败】');  // 接口日志记录
                      return 1009;
                  }

            }else{
                commitTransaction();
                $key='getUserGuard_'.$uid.'_'.$liveuid;
                setcaches($key,$guard);
                return $result;
            }

        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("购买守护异常:".$ex->getMessage());
            //调用失败,回滚事务,并返回余额不足错误
            return array('code' => 1001, 'msg' => codemsg('2006'), 'info' => array("购买守护异常:".$ex->getMessage()));
        }
    }
    /* 独立租户购买守护 */
    public function buyGuardalone($data){
        $rs = array('code' => 0, 'msg' => '购买成功', 'info' => array());
        try {
            $redis = connectionRedis();
            //开始数据库事务
            beginTransaction();
            $uid = $data['uid'];
            $liveuid = $data['liveuid'];
            $stream = $data['stream'];
            $guardid = $data['guardid'];

            $guardinfo = DI()->notorm->guard
                ->select('*')
                ->where('id=?', $guardid)
                ->fetchOne();
            if (!$guardinfo) {
                $rs['code'] = 1001;
                $rs['msg'] = '守护信息不存在';
                return $rs;
            }

            $addtime = time();
            $isexist = DI()->notorm->guard_users
                ->select('*')
                ->where('uid = ? and liveuid=?', $uid, $liveuid)
                ->fetchOne();
            if ($isexist && $isexist['endtime'] > $addtime && $isexist['type'] > $guardinfo['type']) {
                //获取已经购买的 等级
                $guardname= DI()->notorm->guard->select('name')->where('type=?', $isexist['type'])->fetchOne();
                $rs['code'] = 1004;
                $rs['msg'] = '您为当前主播的'.$guardname['name'].'了，不能购买更低等级的守护';
                return $rs;
            }

            $type = 'expend';
            $action = 'buyguard';
            $giftid = $guardinfo['id'];
            $total = $guardinfo['coin'];

            /* 更新用户余额 消费 */
            $isok = DI()->notorm->users
                ->where('id = ? and coin>=?', $uid, $total)
                ->update(array('coin' => new NotORM_Literal("coin - {$total}"), 'consumption' => new NotORM_Literal("consumption + {$total}")));
            if (!$isok) {
                $rs['code'] = 1002;
                $rs['msg'] = '余额不足';
                return $rs;
            }


            $config = getConfigPub();
            $liveUserInfo = getUserInfo($liveuid);
            $userInfo = getUserInfo($uid);
            $liveUserTenantInfo = getTenantInfo($liveUserInfo['tenant_id']);
            $anchortenant_name = $liveUserTenantInfo['name'];
            $userTenantInfo = getTenantInfo($userInfo['tenant_id']);
            $sendtenant_name = $userTenantInfo['name'];
            //TODO 计算分润
            $anchor_tenant_profit_ratio = $config['anchor_tenant_profit_ratio'] / 100;
            //消费者所属租户分润比例
            $user_tenant_profit_ratio = $config['user_tenant_profit_ratio'] / 100;
            //主播分润比例
            $anchor_profit_ratio = $config['anchor_profit_ratio'] / 100;
            //配置比例转换成金额
            $anthor_total = round($total / $config['money_rate'], 3);
            //租户分润金额
            $tenant_total = $anchor_tenant_profit_ratio * $anthor_total;
            //消费者所属分润金额
            $tenantuser_total = $user_tenant_profit_ratio * $anthor_total;

            $res = $this->setCommission($uid, $liveuid, $anthor_total, $anchor_profit_ratio);

            $anthor_total = $res['anthor_total'];
            $family_total = $res['family_total'];
            //家族长及时到账
            $familyhead_id = $res['family_id'];
            $familyhead_money = $family_total - $anthor_total;

            DI()->notorm->users
                ->where('id = ?', $liveuid)
                ->update(array('coin' => new NotORM_Literal("coin + {$anthor_total}")));
            if ($res['family_id'] != 0) {
                DI()->notorm->users
                    ->where('id = ?', $familyhead_id)
                    ->update(array('coin' => new NotORM_Literal("coin + {$familyhead_money}")));
            }


            //记录打赏列表
            $profitinsert = array(
                'status' => '0',
                'uid' => $liveuid,   //直播会员ID
                'addtime' => time(),
                'anchor_tenant' => $liveUserInfo['tenant_id'],
                'send_tenant' => $userInfo['tenant_id'],
                'anchor_money' => $tenant_total,
                "send_money" => $tenantuser_total,
                'anthor_total' => $anthor_total,
                'family_total' => $family_total,
                'is_type' => 1,
            );

            $isTeanntid = DI()->notorm->profit_sharing->insert($profitinsert);

            $showid = 0;
            if ($stream) {
                $stream2 = explode('_', $stream);
                $showid = $stream2[1];
                if (!$showid) {
                    $showid = 0;
                }
            }
            /**
             * 主播收入记录
             */
            $insertanchor = array(
                "type" => 'income',
                "action" => $action,
                "uid" => $liveuid,
                "touid" => $liveuid,
                "giftid" => $giftid,
                "giftcount" => 1,
                "totalcoin" => $anthor_total * $config['money_rate'],
                "showid" => $showid,
                "mark" => '2',
                "addtime" => $addtime,
                'tenant_id' => $liveUserInfo['tenant_id'],
                'receive_tenant_id' => $liveUserInfo['game_tenant_id'],
                'anthor_total' => $anthor_total,
                'tenant_total' => $tenant_total,
                'tenantuser_total' => $tenantuser_total,
                'family_total' => $family_total,
                "cd_ratio" => '1:' . floatval($config['money_rate']),
                'familyhead_total' => $familyhead_money,
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insertanchor);
            //家族长及时到账

            if ($res['family_id'] != 0) {

                /**
                 * 家族长收入记录
                 */
                $insertfamily = array(
                    "type" => 'income',
                    "action" => $action,
                    "uid" => $res['family_user_id'],
                    "touid" => $liveuid,
                    "giftid" => $giftid,
                    "giftcount" => 1,
                    "totalcoin" => $familyhead_money * $config['money_rate'],
                    "showid" => $showid,
                    "mark" => '2',
                    "addtime" => $addtime,
                    'tenant_id' => $liveUserInfo['tenant_id'],
                    'receive_tenant_id' => $liveUserInfo['game_tenant_id'],
                    'anthor_total' => $anthor_total,
                    'tenant_total' => $tenant_total,
                    'tenantuser_total' => $tenantuser_total,
                    'family_total' => $family_total,
                    "cd_ratio" => '1:' . floatval($config['money_rate']),
                    'familyhead_total' => $familyhead_money,
                );

                $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insertfamily);
            }
            //分润详情
            $datadetail = array(
                'uid' => $uid,
                'user_login' => $userInfo['user_nicename'],
                'addtime' => time(),
                'amount' => 1,
                'content' => '购买守护',
                'money' => round($total / $config['money_rate'], 3),
                'type' => 1,

            );

            //主播平台分润
            $this->zhubo_tenant($liveUserInfo, $anchortenant_name, $tenant_total, $anchor_tenant_profit_ratio, $datadetail, $sendtenant_name);

            //消费平台分润
            $this->xiaofei_tenant($userInfo, $sendtenant_name, $tenantuser_total, $user_tenant_profit_ratio, $datadetail);
            //主播分润
            $this->zhubo_update($liveUserInfo, $anthor_total, $res, $datadetail, $sendtenant_name);

            //家族长分润
            $this->family_update($res, $familyhead_money, $datadetail, $sendtenant_name);


            /* 清除缓存 */
            delCache("userinfo_" . $uid);
            delCache("userinfo_" . $liveuid);
            /* 分销 */
            /*setAgentProfit($uid,$total);*/

            DI()->notorm->users
                ->where('id = ?', $liveuid)
                ->update(array('votes' => new NotORM_Literal("votes + {$total}"), 'votestotal' => new NotORM_Literal("votestotal + {$total}")));

            $insert_votes = [
                'type' => 'income',
                'action' => $action,
                'uid' => $liveuid,
                'votes' => $total,
                'addtime' => time(),
            ];
            DI()->notorm->users_voterecord->insert($insert_votes);


            $insert = array(
                "type" => $type,
                "action" => $action,
                "uid" => $uid,
                "touid" => $liveuid,
                "giftid" => $giftid,
                "giftcount" => $total,
                "totalcoin" => $total,
                "showid" => $showid,
                "addtime" => $addtime,
                'tenant_id' => $liveUserInfo['tenant_id'],
                'receive_tenant_id' => $liveUserInfo['game_tenant_id'],
                'anthor_total' => $anthor_total,
                'tenant_total' => $tenant_total,
                'tenantuser_total' => $tenantuser_total,
                'family_total' => $family_total,
                "cd_ratio" => '1:' . floatval($config['money_rate']),
                'familyhead_total' => $familyhead_money,
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);

            $addtime = time();
            $isexist = DI()->notorm->guard_users
                ->select('*')
                ->where('uid = ? and liveuid=?', $uid, $liveuid)
                ->fetchOne();
            if ($isexist && $isexist['endtime'] > $addtime && $isexist['type'] > $guardinfo['type']) {
                $rs['code'] = 1004;
                $rs['msg'] = '已经是尊贵守护了，不能购买普通守护';
                return $rs;
            }
            $endtime = $addtime + $guardinfo['length_time'];
            if ($isexist) {

                if ($isexist['type'] == $guardinfo['type'] && $isexist['endtime'] > $addtime) {
                    /* 同类型未到期 只更新到期时间 */
                    DI()->notorm->guard_users
                        ->where('id = ? ', $isexist['id'])
                        ->update(array('tenant_id'=>$userInfo['tenant_id'],'endtime' => new NotORM_Literal("endtime + {$guardinfo['length_time']}")));
                    $rs['msg'] = '续费成功';
                } else {
                    $data = array(
                        'type' => $guardinfo['type'],
                        'endtime' => $endtime,
                        'addtime' => $addtime,
                        'tenant_id'=>$userInfo['tenant_id'],
                    );
                    DI()->notorm->guard_users
                        ->where('id = ? ', $isexist['id'])
                        ->update($data);
                }
            } else {
                $data = array(
                    'uid' => $uid,
                    'liveuid' => $liveuid,
                    'type' => $guardinfo['type'],
                    'endtime' => $endtime,
                    'addtime' => $addtime,
                    'tenant_id'=>$userInfo['tenant_id'],
                );
                DI()->notorm->guard_users
                    ->insert($data);

            }

            /* 清除缓存 */
            delCache("userinfo_" . $uid);
            delCache("userinfo_" . $liveuid);

            $userinfo2 = DI()->notorm->users
                ->select('consumption,coin')
                ->where('id = ?', $uid)
                ->fetchOne();

            $level = getLevel($userinfo2['consumption']);

            $guard = DI()->notorm->guard_users
                ->select('type,endtime')
                ->where('uid = ? and liveuid=?', $uid, $liveuid)
                ->fetchOne();
            $key = 'getUserGuard_' . $uid . '_' . $liveuid;
            setcaches($key, $guard);

            $liveuidinfo = DI()->notorm->users
                ->select('votestotal')
                ->where('id = ?', $liveuid)
                ->fetchOne();

            $guard_nums = $this->getGuardNums($liveuid);

            $info = array(
                'coin' => $userinfo2['coin'],
                'votestotal' => $liveuidinfo['votestotal'],
                'guard_nums' => $guard_nums,
                'level' => (string)$level,
                'total' => (string)$total,
                'type' => $guard['type'],
                'endtime' => date("Y.m.d", $guard['endtime']),
                'guard_img' =>   $guardinfo['guard_img'],
            );

            commitTransaction();
            $rs['info']=$info;
            return $rs;
        }catch (Exception $ex){
                rollbackTransaction();
                DI()->logger->error("购买守护异常:".$ex->getMessage());
                //调用失败,回滚事务,并返回余额不足错误
                return array('code' => 1001, 'msg' => codemsg('2006'), 'info' => array("购买守护异常:".$ex->getMessage()));
            }
        

        
    }
    
    /* 获取用户守护信息 */
    public function getUserGuard($uid,$liveuid){
        $rs=[];
        $key='getUserGuard_'.$uid.'_'.$liveuid;
        $guardinfo=getcaches($key);
        if(1){
            $guardinfo=DI()->notorm->guard_users
					->select('type,endtime')
					->where('uid = ? and liveuid=?', $uid,$liveuid)
					->fetchOne();
            if($guardinfo && isset($guardinfo['type'])){
                $guardlist=DI()->notorm->guard
                    ->select('id,position_first,position_second,guard_effect,guard_img,is_gift,is_shutup,giftarr')
                    ->where('type=?', $guardinfo['type'])
                    ->fetchOne();
            }

            $guardinfo = array_merge($guardinfo,$guardlist);

            setcaches($key,$guardinfo);
        }
        $nowtime=time();
                    
        if($guardinfo && $guardinfo['endtime']>$nowtime){
            $rs=array(
                'guard_id'=>$guardinfo['id'],
                'type'=>$guardinfo['type'],
                'guard_img'=>$guardinfo['guard_img'],
                'endtime'=>date("Y.m.d H:m:s",$guardinfo['endtime']),
                'guard_effect'=>isset($guardinfo['guard_effect'])?$guardinfo['guard_effect']:'',
                'position_first'=>$guardinfo['position_first'],  //守护礼物特效坐标1
                'position_second'=>$guardinfo['position_second'],//守护礼物特效坐标2
                'is_gift'=>$guardinfo['is_gift'],  //守护是否禁止送礼
                'is_shutup'=>$guardinfo['is_shutup'],//守护是否防发言 被踢
                'giftarr'=>$guardinfo['giftarr'],
            );
        }
        return $rs;
    }
    
    /* 获取主播守护总数 */
    public function getGuardNums($liveuid){

        $nums=DI()->notorm->guard_users
					->where('liveuid=? and endtime>? ',$liveuid,time())
					->count();
        $redis = connectionRedis();
        $nums_robot = $redis->hGet('guard_robot_nums',$liveuid);
        return (string)$nums+$nums_robot;
    }
    public function  setCommission($uid,$liveuid,$anthor_total,$anchor_profit_ratio){
        $authinfo=DI()->notorm->commission_set
            ->select("*")
            ->where('uid=?',$liveuid)
            ->fetchOne();
        //设置了主播分成，使用主播分成，反之，家族分成就是主播分成
        if($authinfo){
            $anthor_totals = round($authinfo['anchor_commission']/100*$anthor_total,3); //主播分成
            $config=getConfigPub();
            $family_totals = round( $config['anchor_profit_ratio'] / 100*$anthor_total,3);//家族分成
            $res['anthor_rent']  =    round($authinfo['anchor_commission'] / 100 , 3);
            $res['family_rent']  =    round(($config['anchor_profit_ratio']-$authinfo['anchor_commission']) / 100 , 3);

            if($authinfo['uid']){
                $userfamily=DI()->notorm->users_family
                    ->select("*")
                    ->where('uid=?',$authinfo['uid'])
                    ->fetchOne();
                if(isset($userfamily['familyid'])){
                    $family=DI()->notorm->family
                        ->select("*")
                        ->where('id=?',$userfamily['familyid'])
                        ->fetchOne();
                    $tenantId = getTenantId();
                    $tenantinfo = getTenantInfo($tenantId);

                    if(isset($userfamily['familyid'])){
                        if ($tenantinfo['site_id'] == 2){//独立会员

                            $family_id = $family['uid'];
                            $family_user_id = $family['uid'];
                            $familyhead_info = DI()->notorm->users->select("*")->where('id=?',$family['uid'])->fetchOne();

                        }else{
                            $familysinfo=DI()->notorm->users->select("game_user_id")->where('id=?',$family['uid'])->fetchOne();
                            $family_id = $familysinfo['game_user_id'];
                            $family_user_id = $family['uid'];
                            $familyhead_info = DI()->notorm->users->select("*")->where('id=?',$family['uid'])->fetchOne();

                        }

                    }else{
                        $family_id   =  0;
                    }

                }else{
                    $family_id   =  0;
                }


            }else{
                $family_id   =  0;
            }



        }else{

            $anthor_totals = round($anchor_profit_ratio*$anthor_total,3);
            $family_totals  = round($anchor_profit_ratio*$anthor_total,3);
            $family_id   =  0;
            $family_user_id = 0;
            $res['anthor_rent']  =    round($anchor_profit_ratio , 3);
            $familyhead_info  = '';
        }
        $res['anthor_total'] = $anthor_totals; //主播分成
        $res['family_total'] = $family_totals; //家族分成
        $res['family_id']    = $family_id;     //家族长分成 ,对应java那边的id字段
        $res['family_user_id']    = $family_user_id;     //家族长分成，对应直播后台的id字段

        $res['familyhead_info']    = $familyhead_info;
        return $res;
    }
    public function zhubo_tenant($liveUserInfo,$anchortenant_name,$tenant_total,$anchor_tenant_profit_ratio,$datadetail,$sendtenant_name){
        // 针对直播租户写入数据

        $isAnchorteant= DI()->notorm->users_share
            ->select("*")
            ->where('anchor_id = ? and status=0 and consumption_name=? ', $liveUserInfo['tenant_id'],$sendtenant_name)
            ->fetchOne();

        if(!$isAnchorteant){
            $shareinsert = array(
                'status'=>'0',
                'addtime'=>time(),
                'beneficiary' => $anchortenant_name,
                'consumption_name' => $sendtenant_name,
                'anchor_id'=> $liveUserInfo['tenant_id'],
                'tenant_id'=>$liveUserInfo['tenant_id'],
                'money' =>$tenant_total,
                'type'=>1, // 类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播

            );
            $rs = DI()->notorm->users_share->insert($shareinsert);

            $datadetail['rent'] =  $tenant_total;
            $datadetail['rent_percent'] =  $anchor_tenant_profit_ratio;
            $datadetail['share_id'] =  $rs['id'];
            DI()->notorm->users_sharedetail->insert($datadetail);

        }else{
            DI()->notorm->users_share
                ->where('anchor_id = ? and consumption_name= ? ', $liveUserInfo['tenant_id'],$sendtenant_name)
                ->update( array('money' => new NotORM_Literal("money + {$tenant_total}")));
            $datadetail['rent'] =  $tenant_total;
            $datadetail['rent_percent'] =  $anchor_tenant_profit_ratio;
            $datadetail['share_id'] =  $isAnchorteant['id'];
            DI()->notorm->users_sharedetail->insert($datadetail);

        }

    }

    public function xiaofei_tenant($userInfo,$sendtenant_name,$tenantuser_total,$user_tenant_profit_ratio,$datadetail){
        // 针对消费写入数据
        $issendTeanntid= DI()->notorm->users_share
            ->select("*")
            ->where('anchor_id = ? and status=0 and consumption_name=? ', $userInfo['tenant_id'],$sendtenant_name)
            ->fetchOne();

        if(!$issendTeanntid){

            $shareinsert = array(
                'status'=>'0',
                'addtime'=>time(),
                'beneficiary' => $sendtenant_name,
                'consumption_name' => $sendtenant_name,
                'tenant_id'=>$userInfo['tenant_id'],
                'anchor_id'=> $userInfo['tenant_id'],
                'money' =>$tenantuser_total,
                'type'=>2, // 类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播

            );
            $rs = DI()->notorm->users_share->insert($shareinsert);
            $datadetail['rent'] =  $tenantuser_total;
            $datadetail['rent_percent'] =  $user_tenant_profit_ratio;
            $datadetail['share_id'] =  $rs['id'];

            DI()->notorm->users_sharedetail->insert($datadetail);

        }else{
            DI()->notorm->users_share
                ->where('anchor_id = ? and consumption_name=? ', $userInfo['tenant_id'],$sendtenant_name)
                ->update( array('money' => new NotORM_Literal("money + {$tenantuser_total}")));

            $datadetail['rent'] =  $tenantuser_total;
            $datadetail['rent_percent'] =  $user_tenant_profit_ratio;
            $datadetail['share_id'] =  $issendTeanntid['id'];
            DI()->notorm->users_sharedetail->insert($datadetail);

        }
    }


    public function zhubo_update($liveUserInfo,$anthor_total,$res,$datadetail,$sendtenant_name){
        // 针对主播写入数据
        $isAnchorid= DI()->notorm->users_share
            ->select("*")
            ->where('anchor_id = ? and status=0 and consumption_name=? ', $liveUserInfo['game_user_id'],$sendtenant_name)
            ->fetchOne();
        if(!$isAnchorid){
            $shareinsert = array(
                'status'=>'0',
                'addtime'=>time(),
                'beneficiary' => $liveUserInfo['user_nicename'],
                'consumption_name' => $sendtenant_name,
                'anchor_id'=> $liveUserInfo['game_user_id'],
                'tenant_id'=>$liveUserInfo['tenant_id'],
                'money' =>$anthor_total,
                'type'=>3, // 类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播
            );
            $rs = DI()->notorm->users_share->insert($shareinsert);
            $datadetail['rent'] =  $anthor_total;
            $datadetail['rent_percent'] =  $res['anthor_rent'];
            $datadetail['share_id'] =  $rs['id'];

            DI()->notorm->users_sharedetail->insert($datadetail);

        }else{
            DI()->notorm->users_share
                ->where('anchor_id = ? and  consumption_name=? ', $liveUserInfo['game_user_id'],$sendtenant_name)
                ->update( array('money' => new NotORM_Literal("money + {$anthor_total}")));
            $datadetail['rent'] =  $anthor_total;
            $datadetail['rent_percent'] =  $res['anthor_rent'];
            $datadetail['share_id'] =  $isAnchorid['id'];
            DI()->notorm->users_sharedetail->insert($datadetail);
        }
    }

    public function family_update($res,$familyhead_money,$datadetail,$sendtenant_name){

        //判断是否有家族上级
        if(isset($res['familyhead_info']) && is_array($res['familyhead_info']) ){
            // 针对主播对应的家族长写入数据
            $isfamilyhead= DI()->notorm->users_share
                ->select("*")
                ->where('anchor_id = ? and status=0  and  consumption_name=? ', $res['familyhead_info']['game_user_id'],$sendtenant_name)
                ->fetchOne();

            if(!$isfamilyhead){
                $shareinsert = array(
                    'status'=>'0',
                    'addtime'=>time(),
                    'beneficiary' =>  $res['familyhead_info']['user_login'],
                    'consumption_name' => $sendtenant_name,
                    'anchor_id'=> $res['familyhead_info']['game_user_id'],
                    'tenant_id'=>$res['familyhead_info']['tenant_id'],
                    'money' =>$familyhead_money,
                    'type'=>4, // 类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播  4：家族长
                );
                $rs = DI()->notorm->users_share->insert($shareinsert);
                $datadetail['rent'] =  $familyhead_money;
                $datadetail['rent_percent'] =  $res['family_rent'];
                $datadetail['share_id'] =  $rs['id'];

                DI()->notorm->users_sharedetail->insert($datadetail);

            }else{
                DI()->notorm->users_share
                    ->where('anchor_id = ?  and  consumption_name=? ', $res['familyhead_info']['game_user_id'],$sendtenant_name)
                    ->update( array('money' => new NotORM_Literal("money + {$familyhead_money}")));
                $datadetail['rent'] =  $familyhead_money;
                $datadetail['rent_percent'] =  $res['family_rent'];
                $datadetail['share_id'] =  $isfamilyhead['id'];
                DI()->notorm->users_sharedetail->insert($datadetail);
            }

        }
    }
    /* 主播总映票 */
    public function getVotes($liveuid){
        $userinfo = getUserInfo($liveuid);
        return $userinfo['votestotal'];
    }
    /* 守护列表机器人数据 */
    public function getGuardrobot($liveuid){


        $robotuserInfo = getcaches('guard_robot_'.$liveuid);
        if(!$robotuserInfo){
            $count = DI()->notorm->users->where("user_type = 3")->count();
            $guard_number = rand(10,20);
            $total = getLiveVotestotalDefault($liveuid);
            $rand_contribute = getRand($guard_number,$total,1,intval($total/10));
            $redis = connectionRedis();
            $redis->hSet('guard_robot_nums',$liveuid,$guard_number);
            $start_user = rand(0,$count-$guard_number);
            $robotuserInfo =DI()->notorm->users
                ->select('id as uid,user_type')
                ->where("user_type = 3")
                ->limit($start_user,$guard_number)
                ->fetchAll();

            foreach ($robotuserInfo as $key=>$value){
                $robotuserInfo[$key]['type'] = rand(0,2);                  //守护等级随机
                $robotuserInfo[$key]['contribute'] = $rand_contribute[$key];         //贡献随机
                $robotuserInfo[$key]['consumption'] = rand(1,2000);        //总消费随机，用来获取等级
            }
            setcaches('guard_robot_'.$liveuid,$robotuserInfo,60*60);
        }
        return $robotuserInfo;
    }
}
