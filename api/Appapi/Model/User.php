<?php

use api\Common\CustRedis;

class Model_User extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
     * 根据用户id、tenant_id获取用户信息
     * */
    public function getUserInfoWithIdAndTid($uid, $tenant_id = null, $field = '*'){
        if($tenant_id){
            $result = DI()->notorm->users->select($field)->where(['id' => intval($uid), 'tenant_id' => intval($tenant_id)])->fetchOne();
        }else{
            $result = DI()->notorm->users->select($field)->where(['id' => intval($uid)])->fetchOne();
        }
        return $result;
    }

	/* 用户全部信息 */
	public function getBaseInfo($uid) {
        $info = getUserInfo($uid);

        if($info){
            $info['level']=getLevel($info['consumption']);
            $info['exp_can_speak'] = exp_can_speak($info['consumption']);
            $info['level_info']=getLevelInfo($info['consumption']);
            $info['level_anchor']=getLevelAnchor($info['votestotal']);
            $info['lives']=getLives($uid);
            $info['follows']=getFollows($uid);
            $info['fans']=getFans($uid);

            $ratemoney = $this->getdayRate($info['id']);
            $info['rate_daymoney']=$ratemoney['rate_daymoney'];
            $info['rate_allmoney']=$ratemoney['rate_allmoney'];
            $info['seven_rate']=$ratemoney['seven_rate'].'%';

            $info['vip']=getUserVip($uid);
            $info['liang']=getUserLiang($uid);
            $time = strtotime(date('Y-m-d',time()));
            $info['long_view_times']= DI()->notorm->video_watch_record->where("uid=? and video_type='2' and addtime >= '{$time}'" ,$uid)->count();
            $info['short_view_times']= DI()->notorm->video_watch_record->where("uid=? and video_type='1' and addtime >= '{$time}' ",$uid)->count();
            $short_view_times_total= DI()->notorm->video_watch_record->where("uid=? and video_type='1' " ,$uid)->count(); // 短视频总共观看次数
            $long_view_times_total  = DI()->notorm->video_watch_record->where("uid=? and video_type='2'  ",$uid)->count();// 长视频视频总共观看次数



            $info['total_view_times']=    DI()->notorm->video_watch_record->where("uid='{$uid}'")->count();;
            $user_model = new Model_Login();
            $vip_info = $user_model->getUserjurisdiction($uid,$info['user_type']);
            $info['watch_number'] = $vip_info['watch_number']!=0 ? ($vip_info['watch_number']+ $info['watch_num']) : $vip_info['watch_number'];
            $short_surplus_view_times = $info['watch_number'] - $short_view_times_total;
            if ($short_surplus_view_times < 0){
                $short_surplus_view_times = 0;
            }
            $long_surplus_view_times =  $vip_info['watch_number'] - $long_view_times_total;
            if ($long_surplus_view_times < 0){
                $long_surplus_view_times = 0;
            }
            $info['short_surplus_view_times'] = $short_surplus_view_times;
            $info['long_surplus_view_times'] = $long_surplus_view_times;
            $info['watch_duration'] = $vip_info['watch_duration']!=0 ? ($vip_info['watch_duration']+$info['watch_time']) : $vip_info['watch_duration'];
            $info['level_addtime'] = $vip_info['level_addtime'];
            $info['level_endtime'] = $vip_info['level_endtime'];
            $info['new_level'] = $vip_info['new_level'] ;
            $info['level_name'] =  $vip_info['level_name'] ;
            $info['nft_rate'] =  $vip_info['nft_rate'] ;
            $info['user_vip_checking_level'] = $vip_info['user_vip_checking_level'];
            $info['user_vip_status'] = $vip_info['user_vip_status'];
            $info['user_vip_action_type'] = $vip_info['user_vip_action_type'];
            $info['user_vip_create_time'] = $vip_info['user_vip_create_time'];
            $info['user_vip_update_time'] = $vip_info['user_vip_update_time'];
            $info['user_vip_refund_time'] = $vip_info['user_vip_refund_time'];
            $info['level_name_jurisdiction'] = $vip_info['level_name_jurisdiction'];

            $info['withdrawable_coin'] = $info['coin'];
            $info['totalcoin'] = bcadd($info['withdrawable_coin'],$info['nowithdrawable_coin'],2);
            $info['beauty'] = isset($info['beauty']) ? $info['beauty'] : '';

            $info['appointment_collect'] =  DI()->notorm->appointment_collect->where("uid = {$uid}")->count();
            $info['appointment_browse'] = DI()->notorm->appointment_browse_log->where("uid = {$uid}")->count();

            //判读是否有 首充豪礼信息，如果有，鼓足进场特效优先取豪礼信息中的贵族等级
            $charge_noble =get_user_chargecar($info['id']);
            if($charge_noble && isset($charge_noble['nobel_id']) && isset($charge_noble['nobel_id']) != null ){
                $noblelist = getNobleList(getTenantId());
                foreach ($noblelist as $key => $val){
                    if($charge_noble['nobel_id'] == $val['id']){
                        $info['noble'] = $val;
                    }
                }
            }else{
                $info['noble'] = getUserNoble($info['id']);
            }

            // $count_long=DI()->notorm->video_long
            //     ->where('uid=?',$uid)
            //     ->where('status=?',2)
            //     ->count();
            // $count_shot=DI()->notorm->video
            //     ->where('uid=?',$uid)
            //     ->where('status=?',2)
            //     ->count();
            // $info['video_count'] = $count_long+$count_shot;


            $info['watch_num'];
            unset($info['watch_time']);
        }

		return $info;
	}
			
	/* 判断昵称是否重复 */
	public function checkName($uid,$name){
        $tenantId=getTenantId();
		$isexist=DI()->notorm->users
					->select('id')
					->where('id!=? and user_nicename=? and tenant_id=?',$uid,$name,$tenantId)
					->fetchOne();
		if($isexist){
			return 0;
		}else{
			return 1;
		}
	}

    // 检查invite code是否重复
    public function checkInviteCode($code){
        $isexist=DI()->notorm->users
        ->select('id')
        ->where(' agent_code=? ',$code)
        ->fetchOne();
        if($isexist){
            return 0;
        }else{
            return 1;
        }
    }

    public function getInviteCode($uid){
        $code = DI()->notorm->users
        ->select('agent_code')
        ->where('id=? ',$uid)
        ->fetchOne();
        $tenantId=getTenantId();
        $temp = DI()->notorm->platform_config->select("shop_h5_url,top_index_host")->where("tenant_id='{$tenantId}'")->fetchOne();
        $code['shop_h5_url'] = $temp['shop_h5_url'];
        $code['top_index_host'] = $temp['top_index_host'];
        return $code;
    }

	
	/* 修改信息 */
	public function userUpdate($uid,$fields){
        unset($fields['score']);
        unset($fields['coin']);
        unset($fields['consumption']);
        unset($fields['votes']);
        unset($fields['votestotal']);
        
        if(!$fields){
            return false;
        }

		$res = DI()->notorm->users
					->where('id=?',$uid)
					->update($fields);
        delUserInfoCache($uid);
        return $res;
	}

	/* 修改密码 */
	public function updatePass($uid,$oldpass,$pass){
		$userinfo=DI()->notorm->users
					->select("user_pass")
					->where('id=?',$uid)
					->fetchOne();
		$oldpass=setPass($oldpass);							
	/*	if($userinfo['user_pass']!=$oldpass){
			return 1003;
		}		*/
		$newpass=setPass($pass);
		return DI()->notorm->users
					->where('id=?',$uid)
					->update( array( "user_pass"=>$newpass ) );
	}

    /* 校验支付密码 */
    public function checkPaymentPassword($uid, $password){
        $user_info = DI()->notorm->users->select("payment_password,payment_password_err_count,salt")->where('id=?',intval($uid))->fetchOne();
        if(!$user_info){
            return array('code' => 2112, 'msg' => codemsg(2112), 'info' => ['用户不存在']);
        }
        if($user_info['payment_password_err_count'] > 5){
            return array('code' => 2116, 'msg' => codemsg(2116), 'info' => ['输入错误密码次数过多，已被锁定，请重置支付密码']);
        }

        if (mb_strlen($password) != 6) {
            DI()->notorm->users->where('id=?',intval($uid))->update(['payment_password_err_count' => new NotORM_Literal("payment_password_err_count + 1")]);
            if($user_info['payment_password_err_count'] >= 4){
                return array('code' => 2116, 'msg' => codemsg(2116), 'info' => ['输入错误密码次数过多，已被锁定，请重置支付密码']);
            }
            return array('code' => 2111, 'msg' => codemsg(2111), 'info' => ['密码长度必须是6']);
        }

        $payment_password = signPaymentPassword($password, $user_info['salt']);
        if($payment_password != $user_info['payment_password']){
            DI()->notorm->users->where('id=?',intval($uid))->update(['payment_password_err_count' => new NotORM_Literal("payment_password_err_count + 1")]);
            if($user_info['payment_password_err_count'] >= 4){
                return array('code' => 2116, 'msg' => codemsg(2116), 'info' => ['输入错误密码次数过多，已被锁定，请重置支付密码']);
            }
            return array('code' => 2114, 'msg' => codemsg(2114), 'info' => ['支付密码错误']);
        }
        DI()->notorm->users->where('id=?',intval($uid))->update(['payment_password_err_count' => 0]);
        return array('code' => 0, 'msg' => '', 'info' => ['校验成功']);
    }

    /* 修改支付密码 */
    public function updatePaymentPassword($uid, $old_password, $password, $confirm_password){
        if ($password != $confirm_password) {
            return array('code' => 2110, 'msg' => codemsg(2110), 'info' => ['密码不一致']);
        }
        if (mb_strlen($password) != 6) {
            return array('code' => 2111, 'msg' => codemsg(2111), 'info' => ['密码长度必须是6']);
        }

        $user_info = DI()->notorm->users->select("user_pass,payment_password,salt")->where('id=?',intval($uid))->fetchOne();
        if(!$user_info){
            return array('code' => 2112, 'msg' => codemsg(2112), 'info' => ['用户不存在']);
        }

        $old_password = signPaymentPassword($old_password, $user_info['salt']);
        if($user_info['payment_password'] != $old_password){
            return array('code' => 2115, 'msg' => codemsg(2115), 'info' => ['原支付密码错误']);
        }

        $salt = $user_info['salt'] ? $user_info['salt'] : createSalt();
        $payment_password = signPaymentPassword($password, $salt);

        $update_data = array( "payment_password"=>$payment_password, 'payment_password_err_count'=>0);
        if(!$user_info['salt']){
            $update_data['salt'] = $salt;
        }

        try {
            $result = DI()->notorm->users->where('id=?',intval($uid))->update($update_data);
        }catch (\Exception $e){
            logapi('【修改支付密码-失败】uid: '.$uid, $e->getMessage());
            return array('code' => 2034, 'msg' => codemsg(2034), 'info' => [$e->getMessage()]);
        }
        delUserInfoCache($uid);
        return array('code' => 0, 'msg' => '', 'info' => ['操作成功']);
    }

    /* 重置支付密码 */
    public function resetPaymentPassword($uid, $login_password, $password, $confirm_password){
        if ($password != $confirm_password) {
            return array('code' => 2110, 'msg' => codemsg(2110), 'info' => ['密码不一致']);
        }
        if (mb_strlen($password) != 6) {
            return array('code' => 2111, 'msg' => codemsg(2111), 'info' => ['密码长度必须是6']);
        }

        $user_info = DI()->notorm->users->select("user_pass,payment_password,salt")->where('id=?',intval($uid))->fetchOne();
        if(!$user_info){
            return array('code' => 2112, 'msg' => codemsg(2112), 'info' => ['用户不存在']);
        }
        $login_password = setPass($login_password);
        if($user_info['user_pass'] != $login_password){
            return array('code' => 2113, 'msg' => codemsg(2113), 'info' => ['登录密码错误']);
        }
        $salt = $user_info['salt'] ? $user_info['salt'] : createSalt();
        $payment_password = signPaymentPassword($password, $salt);

        $update_data = array( "payment_password"=>$payment_password, 'payment_password_err_count'=>0);
        if(!$user_info['salt']){
            $update_data['salt'] = $salt;
        }

        try {
            $result = DI()->notorm->users->where('id=?',intval($uid))->update($update_data);
        }catch (\Exception $e){
            logapi('【重置支付密码-失败】uid: '.$uid, $e->getMessage());
            return array('code' => 2034, 'msg' => codemsg(2034), 'info' => [$e->getMessage()]);
        }
        delUserInfoCache($uid);
        return array('code' => 0, 'msg' => '', 'info' => ['操作成功']);
    }

    /* 我的钻石 */
	public function getBalance($uid){
	    $result=array();
	    $userInfo= getUserInfo($uid);
        $tenantId=getTenantId();
        $tenantinfo = getTenantInfo($tenantId);

        if($tenantinfo['site_id'] == 2) {
            $result['coin'] = $userInfo['coin'];
            $result['withdrawable_coin'] = $userInfo['coin'];
            $result['nowithdrawable_coin'] = $userInfo['nowithdrawable_coin'];
            $result['totalcoin'] = bcadd($result['withdrawable_coin'],$result['nowithdrawable_coin'],2);
        }else{
            $rs = getGameUserBalance($userInfo['game_tenant_id'], $userInfo['game_user_id']);
            if ($rs['code'] != 0) {
                //如果code不等于0为请求失败
                //请求失败时余额返回0
                $result['coin'] = 0;
                $result['withdrawable_coin'] = 0;
                $result['nowithdrawable_coin'] = 0;
                $result['totalcoin'] = 0;
            }
            else {
                $result['coin'] = $rs['coin'];
                $result['withdrawable_coin'] = $rs['coin'];
                $result['nowithdrawable_coin'] = $rs['nowithdrawable_coin'];
                $result['totalcoin'] = bcadd($result['withdrawable_coin'],$result['nowithdrawable_coin'],2);
            }
        }
	    return $result;

//		return DI()->notorm->users
//				->select("coin")
//				->where('id=?',$uid)
//				->fetchOne();
	}
	
	/* 充值规则 */
	public function getChargeRules(){

		$rules= DI()->notorm->charge_rules
				->select('id,coin,money,money_ios,product_id,give')
				->order('orderno asc')
				->fetchAll();

		return 	$rules;
	}
    
	/* 我的收益 */
	public function getProfit($uid){
		$info= DI()->notorm->users
				->select("votes,votestotal")
				->where('id=?',$uid)
				->fetchOne();

		$config=getConfigPri();
		
		//提现比例
		$cash_rate=$config['cash_rate'];
        $cash_start=$config['cash_start'];
		$cash_end=$config['cash_end'];
		$cash_max_times=$config['cash_max_times'];
		//剩余票数
		$votes=$info['votes'];
        
		//总可提现数
		$total=(string)floor($votes/$cash_rate);

        if($cash_max_times){
            $tips='每月'.$cash_start.'-'.$cash_end.'号可进行提现申请，收益将在'.($cash_end+1).'-'.($cash_end+5).'号统一发放，每月只可提现'.$cash_max_times.'次';
        }else{
            $tips='每月'.$cash_start.'-'.$cash_end.'号可进行提现申请，收益将在'.($cash_end+1).'-'.($cash_end+5).'号统一发放';
        }
        
		$rs=array(
			"votes"=>$votes,
			"votestotal"=>$info['votestotal'],
			"total"=>$total,
			"cash_rate"=>$cash_rate,
			"tips"=>$tips,
		);
		return $rs;
	}	
	/* 提现  */
	public function setCash($data){
        
        $nowtime=time();
        
        $uid=$data['uid'];
        $userInfo=getUserInfo($uid);
        $tenantId=$userInfo['tenant_id'];
        $accountid=$data['accountid'];
        $cashvote=$data['cashvote'];
        
        $config=getConfigPri();
        $cash_start=$config['cash_start'];
        $cash_end=$config['cash_end'];
        $cash_max_times=$config['cash_max_times'];
        
        $day=(int)date("d",$nowtime);

        if($day < $cash_start || $day > $cash_end){
            return 1005;
        }
        
        //本月第一天
        $month=date('Y-m-d',strtotime(date("Ym",$nowtime).'01'));
        $month_start=strtotime(date("Ym",$nowtime).'01');

        //本月最后一天
        $month_end=strtotime("{$month} +1 month");

        if($cash_max_times){
            $isexist=DI()->notorm->users_cashrecord
                    ->where('uid=? and addtime > ? and addtime < ?',$uid,$month_start,$month_end)
                    ->count();
            if($isexist >= $cash_max_times){
                return 1006;
            }
        }
        
		$isrz=DI()->notorm->users_auth
				->select("status")
				->where('uid=?',$uid)
				->fetchOne();
		if(!$isrz || $isrz['status']!=1){
			return 1003;
		}
        
        /* 钱包信息 */
		$accountinfo=DI()->notorm->users_cash_account
				->select("*")
				->where('id=?',$accountid)
				->fetchOne();
        if(!$accountinfo){
            return 1006;
        }
        

		//提现比例
		$cash_rate=$config['cash_rate'];
		/* 最低额度 */
		$cash_min=$config['cash_min'];

		//提现钱数
        $money=floor($cashvote/$cash_rate);

		if($userInfo['coin'] < $cash_min){
			return 1004;
		}
		
		$cashvotes=$money*$cash_rate;
        
        
        $ifok=DI()->notorm->users
            ->where('id = ? and coin>=?', $uid,$cashvotes)
            ->update(array('coin' => new NotORM_Literal("coin - {$cashvotes}")) );
        delUserInfoCache($uid);
        if(!$ifok){
            return 1001;
        }

        $order_id = generater();
		
		$data=array(
            "type"=>'expend',
            "action"=>'withdraw',
			"uid"=>$uid,
            'user_login' => $userInfo['user_login'],
			"money"=>$money,
			"votes"=>$cashvotes,
			"addtime"=>$nowtime,
			"uptime"=>$nowtime,
			"account_bank"=>$accountinfo['account_bank'],
			"account"=>$accountinfo['account'],
			"name"=>$accountinfo['name'],
            "tenant_id"=>$tenantId,
            'order_id'=>$order_id
		);

        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord($data);

        $rs['order_id'] = $order_id;
		return $rs;
	}
	
	/* 关注 */
	public function setAttent($uid,$touid){
		$isexist=DI()->notorm->users_attention
					->select("*")
					->where('uid=? and touid=?',$uid,$touid)
					->fetchOne();
		if($isexist){
            $date = date('Y-m-d',time() );
            $redis = connectionRedis();
            $times = $redis->hGet('attention', $uid.'_'.$touid.'_'.$date);
        /*    if($times>=2){
                return 1002;
            }*/
            if(empty($times)){

                $redis->hSet('attention', $uid.'_'.$touid.'_'.$date,1);
            }else{
                $redis->hSet('attention', $uid.'_'.$touid.'_'.$date,$times+1);
            }

            DI()->notorm->users
                ->where('id=?',$touid)
                ->update(array( "fans"=>new NotORM_Literal("fans -1") ));;
            DI()->notorm->users
                ->where('id=?',$uid)
                ->update(array( "follows"=>new NotORM_Literal("follows - 1") ));;


			DI()->notorm->users_attention
				->where('uid=? and touid=?',$uid,$touid)
				->delete();

			return 0;
		}else{
			DI()->notorm->users_black
				->where('uid=? and touid=?',$uid,$touid)
				->delete();

            DI()->notorm->users
                ->where('id=?',$touid)
                ->update(array( "fans"=>new NotORM_Literal("fans + 1") ));;
            DI()->notorm->users
                ->where('id=?',$uid)
                ->update(array( "follows"=>new NotORM_Literal("follows + 1") ));;

			DI()->notorm->users_attention
				->insert(array("uid"=>$uid,"touid"=>$touid));
			return 1;
		}			 
	}	
	
	/* 拉黑 */
	public function setBlack($uid,$touid){
		$isexist=DI()->notorm->users_black
					->select("*")
					->where('uid=? and touid=?',$uid,$touid)
					->fetchOne();
		if($isexist){
			DI()->notorm->users_black
				->where('uid=? and touid=?',$uid,$touid)
				->delete();
			return 0;
		}else{
			DI()->notorm->users_attention
				->where('uid=? and touid=?',$uid,$touid)
				->delete();
			DI()->notorm->users_black
				->insert(array("uid"=>$uid,"touid"=>$touid));

			return 1;
		}			 
	}
	
	/* 关注列表 */
	public function getFollowsList($uid,$touid,$p){
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;
		$touids=DI()->notorm->users_attention
					->select("touid")
					->where('uid=?',$touid)
					->limit($start,$pnum)
					->fetchAll();
		foreach($touids as $k=>$v){
			$userinfo=getUserInfo($v['touid']);
			if($userinfo){
				if($uid==$touid){
                    $count_long=DI()->notorm->video_long
                        ->where('uid=?',$v['touid'])
                        ->count();
                    $count_shot=DI()->notorm->video
                        ->where('uid=?',$v['touid'])
                        ->count();
					$isattent=1;
				}else{
					$isattent=isAttention($uid,$v['touid']);
				}
				$userinfo['isattention']=$isattent;
                $userinfo['video_count']=$count_long+$count_shot;
				$touids[$k]=$userinfo;
			}else{
				DI()->notorm->users_attention->where('uid=? or touid=?',$v['touid'],$v['touid'])->delete();
				unset($touids[$k]);
			}
		}		
		$touids=array_values($touids);
		return $touids;
	}
	
	/* 粉丝列表 */
	public function getFansList($uid,$touid,$p){
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;
		$touids=DI()->notorm->users_attention
					->select("uid")
					->where('touid=?',$touid)
					->limit($start,$pnum)
					->fetchAll();
		foreach($touids as $k=>$v){
			$userinfo=getUserInfo($v['uid']);
			if($userinfo){
				$userinfo['isattention']=isAttention($uid,$v['uid']);
				$touids[$k]=$userinfo;
			}else{
				DI()->notorm->users_attention->where('uid=? or touid=?',$v['uid'],$v['uid'])->delete();
				unset($touids[$k]);
			}
			
		}		
		$touids=array_values($touids);
		return $touids;
	}	

	/* 黑名单列表 */
	public function getBlackList($uid,$touid,$p){
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;
		$touids=DI()->notorm->users_black
					->select("touid")
					->where('uid=?',$touid)
					->limit($start,$pnum)
					->fetchAll();
		foreach($touids as $k=>$v){
			$userinfo=getUserInfo($v['touid']);
			if($userinfo){
				$touids[$k]=$userinfo;
			}else{
				DI()->notorm->users_black->where('uid=? or touid=?',$v['touid'],$v['touid'])->delete();
				unset($touids[$k]);
			}
		}
		$touids=array_values($touids);
		return $touids;
	}
	
	/* 直播记录 */
	public function getLiverecord($touid,$p){
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;
		$record=DI()->notorm->users_liverecord
					->select("id,uid,nums,starttime,endtime,title,city")
					->where('uid=?',$touid)
					->order("id desc")
					->limit($start,$pnum)
					->fetchAll();
		foreach($record as $k=>$v){
			$record[$k]['datestarttime']=date("Y.m.d",$v['starttime']);
			$record[$k]['dateendtime']=date("Y.m.d",$v['endtime']);
            $cha=$v['endtime']-$v['starttime'];
			$record[$k]['length']=getSeconds($cha);
		}						
		return $record;						
	}	
	
		/* 个人主页 */
	public function getUserHome($uid,$touid){
		$info=getUserInfo($touid);				

		$info['follows']=(string)getFollows($touid);
		$info['fans']=(string)getFans($touid);
		$info['isattention']=(string)isAttention($uid,$touid);
		$info['isblack']=(string)isBlack($uid,$touid);
		$info['isblack2']=(string)isBlack($touid,$uid);
		
		/* 贡献榜前三 */
		$rs=array();
		$rs=DI()->notorm->users_coinrecord
				->select("uid,sum(totalcoin) as total")
				->where('action="sendgift" and touid=?',$touid)
				->group("uid")
				->order("total desc")
				->limit(0,3)
				->fetchAll();
		foreach($rs as $k=>$v){
			$userinfo=getUserInfo($v['uid']);
			$rs[$k]['avatar']=$userinfo['avatar'];
		}		
		$info['contribute']=$rs;	
		
        /* 视频数 */

		if($uid==$touid){  //自己的视频（需要返回视频的状态前台显示）
			$where=" uid={$uid} and isdel='0' and status=1  and is_ad=0";
		}else{  //访问其他人的主页视频
            $videoids_s=getVideoBlack($uid);
			$where="id not in ({$videoids_s}) and uid={$touid} and isdel='0' and status=1  and is_ad=0";
		}
        
		$videonums=DI()->notorm->users_video
				->where($where)
				->count();
        if(!$videonums){
            $videonums=0;
        }

        $info['videonums']=(string)$videonums;
        /* 直播数 */
        $livenums=DI()->notorm->users_liverecord
					->where('uid=?',$touid)
					->count();
                    
        $info['livenums']=$livenums;        
		/* 直播记录 */
		$record=array();
		$record=DI()->notorm->users_liverecord
					->select("id,uid,nums,starttime,endtime,title,city")
					->where('uid=?',$touid)
					->order("id desc")
					->limit(0,20)
					->fetchAll();
		foreach($record as $k=>$v){
			$record[$k]['datestarttime']=date("Y.m.d",$v['starttime']);
			$record[$k]['dateendtime']=date("Y.m.d",$v['endtime']);
            $cha=$v['endtime']-$v['starttime'];
            $record[$k]['length']=getSeconds($cha);
		}		
		$info['liverecord']=$record;	
		return $info;
	}
	
	/* 贡献榜 */
	public function getContributeList($touid,$p){
		if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;

		$rs=array();
		$rs=DI()->notorm->users_coinrecord
				->select("uid,sum(totalcoin) as total")
				->where('touid=?',$touid)
				->group("uid")
				->order("total desc")
				->limit($start,$pnum)
				->fetchAll();
				
		foreach($rs as $k=>$v){
			$rs[$k]['userinfo']=getUserInfo($v['uid']);
		}		
		
		return $rs;
	}
	
	/* 设置分销 */
	public function setDistribut($uid,$code){
        
        $isexist=DI()->notorm->users_agent
				->select("*")
				->where('uid=?',$uid)
				->fetchOne();
        if($isexist){
            return 1004;
        }

        $userInfo=getUserInfo($uid);
        $tenantId=$userInfo['tenant_id'];
        
		$oneinfo=DI()->notorm->users_agent_code
				->select("uid")
				->where('code=? and uid!=? and tenant_id=? ',$code,$uid,$tenantId)
				->fetchOne();
		if(!$oneinfo){
			return 1002;
		}


		
		$agentinfo=DI()->notorm->users_agent
				->select("*")
				->where('uid=?',$oneinfo['uid'])
				->fetchOne();
		if(!$agentinfo){
			$agentinfo=array(
				'uid'=>$oneinfo['uid'],
				'one_uid'=>0,
				'two_uid'=>0,
                'three_uid'=>0,
                'four_uid'=>0,
			);
		}
        /* 判断对方是否自己下级 */
        if($agentinfo['one_uid']==$uid  || $agentinfo['two_uid']==$uid ){
            return 1003;
        }
		
		$data=array(
			'uid'=>$uid,
			'one_uid'=>$agentinfo['uid'],
			'two_uid'=>$agentinfo['one_uid'],
			'three_uid'=>$agentinfo['two_uid'],
            'four_uid'=>$agentinfo['three_uid'],
            'five_uid'=>$agentinfo['four_uid'],
			'addtime'=>time(),
            'tenant_id'=>$tenantId
		);
		DI()->notorm->users_agent->insert($data);
		return 0;
	}
    
    
    /* 印象标签 */
    public function getImpressionLabel(){
        
        $key="getImpressionLabel";
		$list=getcaches($key);
		if(!$list){
            $list=DI()->notorm->impression_label
				->select("*")
				->order("orderno asc,id desc")
				->fetchAll();
            foreach($list as $k=>$v){
                $list[$k]['colour']='#'.$v['colour'];
            }
                
			setcaches($key,$list); 
		}

        return $list;
    }       
    /* 用户标签 */
    public function getUserLabel($uid,$touid){
        $list=DI()->notorm->users_label
				->select("label")
                ->where('uid=? and touid=?',$uid,$touid)
				->fetchOne();
                
        return $list;
        
    }    

    /* 设置用户标签 */
    public function setUserLabel($uid,$touid,$labels){
        $nowtime=time();
        $isexist=DI()->notorm->users_label
				->select("*")
                ->where('uid=? and touid=?',$uid,$touid)
				->fetchOne();
        if($isexist){
            $rs=DI()->notorm->users_label
                ->where('uid=? and touid=?',$uid,$touid)
				->update(array( 'label'=>$labels,'uptime'=>$nowtime ) );
        }else{
            $data=array(
                'uid'=>$uid,
                'touid'=>$touid,
                'label'=>$labels,
                'addtime'=>$nowtime,
                'uptime'=>$nowtime,
            );
            $rs=DI()->notorm->users_label->insert($data);
        }
                
        return $rs;
        
    }    
    
    /* 获取我的标签 */
    public function getMyLabel($uid){
        $rs=array();
        $list=DI()->notorm->users_label
				->select("label")
                ->where('touid=?',$uid)
				->fetchAll();
        $label=array();
        foreach($list as $k=>$v){
            $v_a=preg_split('/,|，/',$v['label']);
            $v_a=array_filter($v_a);
            if($v_a){
                $label=array_merge($label,$v_a);
            }
            
        }

        if(!$label){
            return $rs;
        }
        
        
        $label_nums=array_count_values($label);
        
        $label_key=array_keys($label_nums);
        
        $labels=$this->getImpressionLabel();
        
        $order_nums=array();
        foreach($labels as $k=>$v){
            if(in_array($v['id'],$label_key)){
                $v['nums']=(string)$label_nums[$v['id']];
                $order_nums[]=$v['nums'];
                $rs[]=$v;
            }
        }
        
        array_multisort($order_nums,SORT_DESC,$rs);
        
        return $rs;
        
    }   
    
    /* 获取关于我们列表 */
    public function getPerSetting(){
        $rs=array();
        $tenantId=getTenantId();
        $list=DI()->notorm->posts
				->select("id,post_title")
                ->where("type='2' and tenant_id=?",$tenantId)
                ->order('orderno asc')
				->fetchAll();
        foreach($list as $k=>$v){
            
            $rs[]=array('id'=>'0','name'=>$v['post_title'],'thumb'=>'' ,'href'=>get_upload_path("/index.php?g=portal&m=page&a=index&id={$v['id']}"));
        }
        
        return $rs;
    }
    
    /* 提现账号列表 */
    public function getUserAccountList($uid){
        
        $list=DI()->notorm->users_cash_account
                ->select("*")
                ->where('uid=?',$uid)
                ->order("addtime desc")
                ->fetchAll();
                
        return $list;
    }

    /* 设置提账号 */
    public function setUserAccount($data){
        
        $rs=DI()->notorm->users_cash_account
                ->insert($data);
                
        return $rs;
    }

    /* 删除提账号 */
    public function delUserAccount($data){
        
        $rs=DI()->notorm->users_cash_account
                ->where($data)
                ->delete();
                
        return $rs;
    }

    /* 删除提账号 */
    public function deleteZmobile($data){
        $game_user = explode(',',$data );

        $where = '';
        foreach($game_user as $key => $value){
            if ($key == count($game_user)-1){
                $where .= "'". $value."'";
            }else{
                $where .= "'". $value."',";
            }
        }

        $where = " game_user_id in ($where)";

        $rs=DI()->notorm->users
            ->where($where)
            ->delete();

        return $rs;
    }
    
	/* 登录奖励信息 */
	public function LoginBonus($uid){
		$rs=array(
			'bonus_switch'=>'0',
			'bonus_day'=>'0',
			'count_day'=>'0',
			'bonus_list'=>array(),
		);
		$configpri=getConfigPri();
		if(!$configpri['bonus_switch']){
			return $rs;
		}
		$rs['bonus_switch']=$configpri['bonus_switch'];

		
		/* 获取登录设置 */
        $key='loginbonus';
		$list=getcaches($key);
		if(!$list){
            $list=DI()->notorm->loginbonus
					->select("day,coin")
					->fetchAll();
			if($list){
				setcaches($key,$list);
			}
		}
		$rs['bonus_list']=$list;
		$bonus_coin=array();
		foreach($list as $k=>$v){
			$bonus_coin[$v['day']]=$v['coin'];
		}

		/* 登录奖励 */
		$signinfo=DI()->notorm->users_sign
					->select("bonus_day,bonus_time,count_day")
					->where('uid=?',$uid)
					->fetchOne();
		if(!$signinfo){
			$signinfo=array(
				'bonus_day'=>0,
				'bonus_time'=>0,
				'count_day'=>0,
			);
        }
        $nowtime=time();
        if($nowtime - $signinfo['bonus_time'] > 60*60*24){
            $signinfo['count_day']=0;
        }
        $rs['count_day']=(string)$signinfo['count_day'];

		if($nowtime>$signinfo['bonus_time']){
			//更新
			$bonus_time=strtotime(date("Ymd",$nowtime))+60*60*24;
			$bonus_day=$signinfo['bonus_day'];
			if($bonus_day>6){
				$bonus_day=0;
			}
			$bonus_day++;
            $coin=$bonus_coin[$bonus_day];
            
			if($coin){
                $rs['bonus_day']=(string)$bonus_day;
            }
			
		}
		return $rs;
	}
    
	/* 获取登录奖励 */
	public function getLoginBonus($uid){
		$rs=0;
		$configpri=getConfigPri();
		if(!$configpri['bonus_switch']){
			return $rs;
		}
		
		/* 获取登录设置 */
        $key='loginbonus';
		$list=getcaches($key);
		if(!$list){
            $list=DI()->notorm->loginbonus
					->select("day,coin")
					->fetchAll();
			if($list){
				setcaches($key,$list);
			}
		}

		$bonus_coin=array();
		foreach($list as $k=>$v){
			$bonus_coin[$v['day']]=$v['coin'];
		}

		$userInfo=getUserInfo($uid);
		$tenantId=$userInfo['tenant_id'];
		$isadd=0;
		/* 登录奖励 */
		$signinfo=DI()->notorm->users_sign
					->select("bonus_day,bonus_time")
					->where('uid=?',$uid)
					->fetchOne();
		if(!$signinfo){
			$isadd=1;
			$signinfo=array(
				'bonus_day'=>0,
				'bonus_time'=>0,
			);
        }
		$nowtime=time();
		if($nowtime>$signinfo['bonus_time']){
			//更新
			$bonus_time=strtotime(date("Ymd",$nowtime))+60*60*24;
			$bonus_day=$signinfo['bonus_day'];
			$count_day=$signinfo['count_day'];
			if($bonus_day>6){
				$bonus_day=0;
			}
            if($nowtime - $signinfo['bonus_time'] > 60*60*24){
                $count_day=0;
            }
			$bonus_day++;
			$count_day++;
            
 
            if($isadd){
                DI()->notorm->users_sign
                    ->insert(array("uid"=>$uid,"bonus_time"=>$bonus_time,"bonus_day"=>$bonus_day,"count_day"=>$count_day,"tenant_id"=> $tenantId));
            }else{
                DI()->notorm->users_sign
                    ->where('uid=?',$uid)
                    ->update(array("bonus_time"=>$bonus_time,"bonus_day"=>$bonus_day,"count_day"=>$count_day ));
            }
            
            $coin=$bonus_coin[$bonus_day];
            
			if($coin){
                DI()->notorm->users
                    ->where('id=?',$uid)
                    ->update(array( "coin"=>new NotORM_Literal("coin + {$coin}") ));
                delUserInfoCache($uid);

                /* 记录 */
                $insert=array("type"=>'income',"action"=>'loginbonus',"uid"=>$uid,"touid"=>$uid,"giftid"=>$bonus_day,"giftcount"=>'0',"totalcoin"=>$coin,"showid"=>'0',
                    "addtime"=>$nowtime,"tenant_id"=>$tenantId );
                $coinrecordModel = new Model_Coinrecord();
                $coinrecordModel->addCoinrecord($insert);
            }
            $rs=1;
		}
		
		return $rs;

	}

    /* 邀请码 */
    public function invitationCode($uid){

        $list = DI()->notorm->users_agent_code
            ->select("code,tenant_id")
            ->where('uid=?',$uid)
            ->fetchOne();

        $user_info = DI()->notorm->users
            ->select("zone,tenant_id")
            ->where('id=?',$uid)
            ->fetchOne();
        $list['zone'] = $user_info['zone'];
        return $list;

    }

    /* 投注记录写入直播后台 */

    public function setBetrecord($data){
        try {
            $redis = connectionRedis();
            beginTransaction();
            //TODO 计算分润
            $config = getConfigPub();
            //主播所属租户分润比例
            $anchor_tenant_profit_ratio = $config['anchor_platform_profit_ratio'] / 100;
            //消费者所属租户分润比例
            $user_tenant_profit_ratio = $config['user_platform_profit_ratio'] / 100;
            //主播分润比例
            $anchor_profit_ratio = $config['anchor_platform_ratio'] / 100;
            //配置比例转换成金额
            $anthor_total = round($data['totalcoin'], 3);
            //租户分润金额
            $tenant_total = $anchor_tenant_profit_ratio * $anthor_total;
            //消费者所属分润金额
            $tenantuser_total = $user_tenant_profit_ratio * $anthor_total;


            $liveUserInfo = getUserInfo($data['touid']);
            $userInfo = getUserInfo($data['uid']);
            $liveUserTenantInfo = getTenantInfo($liveUserInfo['tenant_id']);
            $anchortenant_name = $liveUserTenantInfo['name'];
            $userTenantInfo = getTenantInfo($userInfo['tenant_id']);
            $sendtenant_name = $userTenantInfo['name'];


            $res = $this->setCommission($data['uid'], $data['touid'], $anthor_total, $anchor_profit_ratio);

            $anthor_total = $res['anthor_total'];
            $family_total = $res['family_total'];

            $familyhead_id    =    $res['family_id'];
            $familyhead_money = $family_total - $anthor_total;

            $total  =   round($data['totalcoin']*$config['money_rate'],3);
            $consumption = $userInfo['consumption'] + $total;
            $level = intval(getLevel($consumption));
            $updateResult = array('level'=>$level,'consumption'=>$consumption);
            logapi(['bet info '=>$updateResult],'【下注接口日志】');  // 接口日志记录
            // 用户等级升级加成，根据加成的比例，增加用户等级经验
            $user_noble = getUserNoble($data['uid']);
            $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($total,(1+$user_noble['upgrade_speed']/100),2) : $total;
            //增加消费总额
            DI()->notorm->users
                ->where('id = ?', $data['uid'])
                ->update(array('consumption' => new NotORM_Literal("consumption + {$u_consumption}"),'userlevel' =>$level ) );
            delUserInfoCache($data['uid']);
            $userInfo = getUserInfo($data['uid']);
            // 累计消费变动，通知前端
            $redis->zAdd('user_consumptionchange',1,json_encode(['uid'=>$data['uid'],'liveuid'=>$data['touid'],'level'=>getLevel($userInfo['consumption']),'consumption'=>$userInfo['consumption'],'exp_can_speak'=>exp_can_speak($userInfo['consumption'])]));

            //记录打赏列表
            $profitinsert = array(
                'status' => '0',
                'uid' => $data['touid'],//直播会员ID
                'addtime' => time(),
                'anchor_tenant' => $liveUserInfo['tenant_id'],
                'send_tenant' => $userInfo['tenant_id'],
                'anchor_money' => $tenant_total,
                "send_money" => $tenantuser_total,
                'anthor_total' => $anthor_total,
                'family_total' => $family_total,
                'is_type' => 2,
            );

            $isTeanntid = DI()->notorm->profit_sharing->insert($profitinsert);


            $datadetail = array(
                'uid' => $data['uid'],
                'user_login' => $userInfo['user_nicename'],
                'addtime' => time(),
                'amount' => $data['giftcount'],
                'content' => $data['playname'],
                'money' => round($data['totalcoin'], 3),
                'type'=>4,

            );


            /**
             * 主播收入记录
             */
            $insertanchor=array(
                "type"=>'income',
                "action"=>$data['action'],
                "uid"=>$data['touid'],
                "touid"=>$data['touid'] ,
                "giftid"=>1,
                "giftcount"=>1,
                "totalcoin"=>$anthor_total*$config['money_rate'],
                "showid"=>$data['showid'],
                "addtime"=>time(),
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
                    "action"=>$data['action'],
                    "uid"=> $res['family_user_id'],
                    "touid"=>$data['touid'] ,
                    "giftid"=>1,
                    "giftcount"=>1,
                    "totalcoin"=>$familyhead_money*$config['money_rate'],
                    "showid"=>$data['showid'],
                    "addtime"=>time(),
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

            // 针对直播租户写入数据
            $isAnchorteant = DI()->notorm->users_share
                ->select("*")
                ->where('anchor_id = ? and status=0 and consumption_name=?', $liveUserInfo['tenant_id'],$sendtenant_name)
                ->fetchOne();

            if (!$isAnchorteant) {
                $shareinsert = array(
                    'status' => '0',
                    'addtime' => time(),
                    'beneficiary' => $anchortenant_name,
                    'consumption_name' => $sendtenant_name,
                    'anchor_id' => $liveUserInfo['tenant_id'],
                    'tenant_id' => $liveUserInfo['tenant_id'],
                    'money' => $tenant_total,
                    'type' => 1, // 类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播

                );
                $rs = DI()->notorm->users_share->insert($shareinsert);

                $datadetail['rent'] = $tenant_total;
                $datadetail['rent_percent'] = $anchor_tenant_profit_ratio;
                $datadetail['share_id'] = $rs['id'];
                DI()->notorm->users_sharedetail->insert($datadetail);

            } else {
                DI()->notorm->users_share
                    ->where('anchor_id = ?  and consumption_name=?', $liveUserInfo['tenant_id'],$sendtenant_name)
                    ->update(array('money' => new NotORM_Literal("money + {$tenant_total}")));
                $datadetail['rent'] = $tenant_total;
                $datadetail['rent_percent'] = $anchor_tenant_profit_ratio;
                $datadetail['share_id'] = $isAnchorteant['id'];
                DI()->notorm->users_sharedetail->insert($datadetail);

            }


            // 针对消费写入数据
            $issendTeanntid = DI()->notorm->users_share
                ->select("*")
                ->where('anchor_id = ? and status=0 and consumption_name = ? ', $userInfo['tenant_id'],$sendtenant_name)
                ->fetchOne();

            if (!$issendTeanntid) {

                $shareinsert = array(
                    'status' => '0',
                    'addtime' => time(),
                    'beneficiary' => $sendtenant_name,
                    'consumption_name' => $sendtenant_name,
                    'tenant_id' => $userInfo['tenant_id'],
                    'anchor_id' => $userInfo['tenant_id'],
                    'money' => $tenantuser_total,
                    'type' => 2, // 类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播

                );
                $rs = DI()->notorm->users_share->insert($shareinsert);
                $datadetail['rent'] = $tenantuser_total;
                $datadetail['rent_percent'] = $user_tenant_profit_ratio;
                $datadetail['share_id'] = $rs['id'];

                DI()->notorm->users_sharedetail->insert($datadetail);

            } else {
                DI()->notorm->users_share
                    ->where('anchor_id = ? and consumption_name=? ', $userInfo['tenant_id'],$sendtenant_name)
                    ->update(array('money' => new NotORM_Literal("money + {$tenantuser_total}")));

                $datadetail['rent'] = $tenantuser_total;
                $datadetail['rent_percent'] = $user_tenant_profit_ratio;
                $datadetail['share_id'] = $issendTeanntid['id'];
                DI()->notorm->users_sharedetail->insert($datadetail);

            }


            // 针对主播写入数据
            $isAnchorid = DI()->notorm->users_share
                ->select("*")
                ->where('anchor_id = ? and status=0 and consumption_name=?', $liveUserInfo['game_user_id'],$sendtenant_name)
                ->fetchOne();
            if (!$isAnchorid) {
                $shareinsert = array(
                    'status' => '0',
                    'addtime' => time(),
                    'beneficiary' => $liveUserInfo['user_nicename'],
                    'consumption_name' => $sendtenant_name,
                    'anchor_id' => $liveUserInfo['game_user_id'],
                    'tenant_id' => $liveUserInfo['tenant_id'],
                    'money' => $anthor_total,
                    'type' => 3, // 类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播
                );
                $rs = DI()->notorm->users_share->insert($shareinsert);
                $datadetail['rent'] = $anthor_total;
                $datadetail['rent_percent'] = $res['anthor_rent'];
                $datadetail['share_id'] = $rs['id'];

                DI()->notorm->users_sharedetail->insert($datadetail);

            } else {
                DI()->notorm->users_share
                    ->where('anchor_id = ? and consumption_name=? ', $liveUserInfo['game_user_id'],$sendtenant_name)
                    ->update(array('money' => new NotORM_Literal("money + {$anthor_total}")));
                $datadetail['rent'] = $anthor_total;
                $datadetail['rent_percent'] = $res['anthor_rent'];
                $datadetail['share_id'] = $isAnchorid['id'];
                DI()->notorm->users_sharedetail->insert($datadetail);
            }


            //判断是否有家族上级
            if (isset($res['familyhead_info']) && is_array($res['familyhead_info'])) {
                // 针对主播对应的家族长写入数据
                $isfamilyhead = DI()->notorm->users_share
                    ->select("*")
                    ->where('anchor_id = ? and status=0 and consumption_name=?', $res['familyhead_info']['game_user_id'],$sendtenant_name)
                    ->fetchOne();

                if (!$isfamilyhead) {
                    $shareinsert = array(
                        'status' => '0',
                        'addtime' => time(),
                        'beneficiary' => $res['familyhead_info']['user_login'],
                        'consumption_name' => $sendtenant_name,
                        'anchor_id' => $res['familyhead_info']['game_user_id'],
                        'tenant_id' => $res['familyhead_info']['tenant_id'],
                        'money' => $familyhead_money,
                        'type' => 4, // 类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播  4：家族长
                    );
                    $rs = DI()->notorm->users_share->insert($shareinsert);
                    $datadetail['rent'] = $familyhead_money;
                    $datadetail['rent_percent'] = $res['family_rent'];
                    $datadetail['share_id'] = $rs['id'];

                    DI()->notorm->users_sharedetail->insert($datadetail);

                } else {
                    DI()->notorm->users_share
                        ->where('anchor_id = ? and consumption_name=? ', $res['familyhead_info']['game_user_id'],$sendtenant_name)
                        ->update(array('money' => new NotORM_Literal("money + {$familyhead_money}")));
                    $datadetail['rent'] = $familyhead_money;
                    $datadetail['rent_percent'] = $res['family_rent'];
                    $datadetail['share_id'] = $isfamilyhead['id'];
                    DI()->notorm->users_sharedetail->insert($datadetail);
                }

            }

            $data['totalcoin'] = round($data['totalcoin'], 3) * $config['money_rate'];
            $data['type'] = 'expend';
            $data['anthor_total'] = $anthor_total;
            $data['family_total'] = $family_total;
            $data['tenantuser_total'] = $tenantuser_total;
            $data['tenant_total'] = $tenant_total;
            $data['familyhead_total']= $familyhead_money;


            $data['cd_ratio'] = '1:' . floatval($config['money_rate']);

            $rs = $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($data);

            $useridGame=$userInfo['game_user_id'];
            $useridLive=$userInfo['id'];
            $tidGame=$userInfo['game_tenant_id'];
            $tidLive=$userInfo['tenant_id'];
            $usernickname=$userInfo['user_nicename'];
            //金额=钻石/转换比例四舍五入
            $amount=0;
            $diamond=$data['totalcoin']*$config['money_rate'];
            $type=1;
            $detail=$data['playname'];
            $roomid=$data['touid'];
            $anchorid=$data['touid'];
            $anchorname=$liveUserInfo['user_nicename'];
            $anchorfromid=$liveUserInfo['tenant_id'];
            $anchorformname=$liveUserTenantInfo['name'];
            $tId=$userInfo['game_tenant_id'];
            $custId=$userInfo['game_user_id'];
            $custAnchorid=$liveUserInfo['game_user_id'];
            $anchorTenantid=$liveUserInfo['game_tenant_id'];
            $bettingMoney=round($data['totalcoin'],2) ;


            $updateResult= reduceGameUserBalance($useridGame,$useridLive,$tidGame,$tidLive,$usernickname,$amount,$diamond,$type,$detail,$roomid,$anchorid,$anchorname,$anchorfromid,$anchorformname,$tId,$custId,$custAnchorid,$anthor_total,$anchorTenantid,$familyhead_id,$familyhead_money,$level,$bettingMoney);
            if($updateResult['code']!=0){
                if($updateResult['code']==1002){
                    rollbackTransaction();
                    //调用失败,回滚事务,并返回网络错误
                    return 1009;
                }
            }else{
                commitTransaction();
            }

            // http 请求到golang的socketio
//            $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//            $http_post_res = http_post($url,['EventType' => 'ConsumptionChange','Message' => json_encode(['Uid'=>$data['uid'],'Liveuid'=>$data['touid'],'Level'=>getLevel($userInfo['consumption']),'Consumption'=>$userInfo['consumption'],'ExpCanSpeak'=>exp_can_speak($userInfo['consumption'])])]);

            return array( "level" => getLevel($userInfo['consumption']), "coin" => $userInfo['coin']);
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("赠送礼物异常:".$ex->getMessage());
            //调用失败,回滚事务,并返回余额不足错误
            return 1001;
        }


    }


    /* 主播认证 */
    public function applyBecomeLive($uid,$fields){

        if(!$fields){
            return false;
        }
        $data = DI()->notorm->users_auth

            ->where('uid=? and status < 2 ',$uid)
            ->fetchOne();

        if ($data){
            return 1004;
        }
        return DI()->notorm->users_auth
            ->insert($fields);
    }

    public  function incomeExpenditure($uid,$p){
        $game_action=array(
            '0'=>'',
            '1'=>'智勇三张',
            '2'=>'海盗船长',
            '3'=>'转盘',
            '4'=>'开心牛仔',
            '5'=>'二八贝',
        );
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;


        $lists = DI()->notorm->users_coinrecord
            ->where("uid = '{$uid}'")
            ->order("addtime DESC,id DESC")
            ->limit($start,$nums)
            ->fetchAll();

        foreach($lists as $k=>$v){
            $lists[$k]['addtime']  = date('Y-m-d H:i:s',$v['addtime']);
//            $userinfo=DI()->notorm->users->select("user_nicename")->where("id='{$v['uid']}'")->fetchOne();
            $lists[$k]['userinfo'] = getUserInfo($v['uid']);
//            $touserinfo=DI()->notorm->users->select("user_nicename")->where("id='{$v['touid']}'")->fetchOne();
            $lists[$k]['touserinfo'] = getUserInfo($v['touid']);
            $action=$v['action'];
            if($action=='sendgift'){
                $giftinfo=DI()->notorm->gift->select("giftname")->where("id='{$v['giftid']}'")->fetchOne();
                $lists[$k]['giftinfo']= $giftinfo;
            }elseif ($action=='getgift'){
                $giftinfo=DI()->notorm->gift->select("giftname")->where("id='{$v['giftid']}'")->fetchOne();
                $lists[$k]['giftinfo']= $giftinfo;
            } else if($action=='loginbonus'){
                $giftinfo['giftname']='第'.$v['giftid'].'天';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='sendbarrage'){
                $giftinfo['giftname']='弹幕';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='game_bet' || $action=='game_return' || $action=='game_win' || $action=='game_brokerage' || $action=='game_banker'){
                $info= DI()->notorm->game->select('action')->where("id={$v['giftid']}")->fetchOne();
                $giftinfo['giftname']=$game_action[$info['action']];
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='set_deposit'){
                $giftinfo['giftname']='上庄扣除';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='deposit_return'){
                $giftinfo['giftname']='下庄退还';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='roomcharge'){
                $giftinfo['giftname']='房间扣费';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='timecharge'){
                $giftinfo['giftname']='计时扣费';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='buyvip'){
                $info= DI()->notorm-> vip->select("name")->where("id='{$v['giftid']}'")->fetchOne();
                $giftinfo['giftname']='购买'.$info['name'];
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='buycar'){
                $info= DI()->notorm-> car->select("name")->where("id='{$v['giftid']}'")->fetchOne();
                $giftinfo['giftname']=$info['name'];
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='buyliang'){
                $info= DI()->notorm-> liang->select("name")->where("id='{$v['giftid']}'")->fetchOne();
                $giftinfo['giftname']=$info['name'];
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='sendred'){
                $giftinfo['giftname']='发送红包';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='robred'){
                $giftinfo['giftname']='抢红包';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='returnred'){
                $giftinfo['giftname']='红包退回';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='buyguard'){
                $info= DI()->notorm-> guard->select("name")->where("id='{$v['giftid']}'")->fetchOne();
                $giftinfo['giftname']=$info['name'];
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='reg_reward'){
                $giftinfo['giftname']='注册奖励';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='like_video_rebate') {
                $giftinfo['giftname'] = '点赞收益';
                $lists[$k]['giftinfo'] = $giftinfo;
            }else if($action=='otherlogin_reword'){
                    $giftinfo['giftname']='非首次登陆奖励';
                    $lists[$k]['giftinfo']= $giftinfo;
            } else if($action=='buy_shot_video') {
                $giftinfo['giftname'] = '视频收益';
                $lists[$k]['giftinfo'] = $giftinfo;
            } else if($action=='bar'){
                    $giftinfo['giftname']='贴吧悬赏金';
                    $lists[$k]['giftinfo']= $giftinfo;
            } else if($action=='bar_income'){
                $giftinfo['giftname']='贴吧最佳评论';
                $lists[$k]['giftinfo']= $giftinfo;
            }elseif($action == 'bar_return'){
                $giftinfo['giftname']='贴吧求片驳回';
                $lists[$k]['giftinfo']= $giftinfo;
            } elseif($action == 'charge_withdrawn'){
                $giftinfo['giftname']='不可提现转提现';
                $lists[$k]['giftinfo']= $giftinfo;
            }elseif($action == 'video_uplode_reward'){
                $giftinfo['giftname']='上传视频收益';
                $lists[$k]['giftinfo']= $giftinfo;
            }elseif($action == 'agent_video_uplode_reward'){
                $giftinfo['giftname']='购买视频代理收益';
                $lists[$k]['giftinfo']= $giftinfo;
            }elseif($action == 'agent_likes_video'){
                $giftinfo['giftname']='点赞视频代理收益';
                $lists[$k]['giftinfo']= $giftinfo;
            }elseif($action == 'vip_refund '){
                $giftinfo['giftname']='退回vip';
                $lists[$k]['giftinfo']= $giftinfo;
            }elseif($action == 'vip_upgrade_refund'){
                $giftinfo['giftname']='升级vip';
                $lists[$k]['giftinfo']= $giftinfo;
            } elseif($action == 'redpackge'){
                $giftinfo['giftname']='红包收入';
                $lists[$k]['giftinfo']= $giftinfo;
            }
            else{
                $giftinfo['giftname']='未知';
                $lists[$k]['giftinfo']= $giftinfo;
            }

            $tenantInfo=getTenantInfo($v['tenant_id']);
            if(!empty($tenantInfo)){
                $lists[$k]['tenant_name']=$tenantInfo['name'];
            }
            $lists[$k]['totalcoin'] = floatval($v['totalcoin']);
            $lists[$k]['tenant_total'] = floatval($v['tenant_total']);
            $lists[$k]['family_total'] = floatval($v['family_total']);
            $lists[$k]['anthor_total'] = floatval($v['anthor_total']);
            $lists[$k]['after_balance'] = floatval($v['after_balance']);
        }
        return $lists;
    }
    public  function incomeExpenditurenew($uid,$p,$type){

        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;

        if($type==1){
            $lists = DI()->notorm->users_coinrecord
                ->where("uid = '{$uid}' and action in ('charge','offline_charge','manual_charge')")
                ->order("addtime DESC,id DESC")
                ->limit($start,$nums)
                ->fetchAll();
        }else{
            $lists = DI()->notorm->users_coinrecord
                ->where("uid = '{$uid}' and action in ('buy_video','buy_longvideovip','withdraw','charge','video_uplode_reward','offline_charge','manual_charge')")
                ->order("addtime DESC,id DESC")
                ->limit($start,$nums)
                ->fetchAll();
        }




        return $lists;
    }
    public  function incomeUploadvideo($uid,$p){

        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;

        $lists = DI()->notorm->users_coinrecord
            ->where("uid = '{$uid}' and action  = 'video_uplode_reward'")
            ->order("addtime DESC,id DESC")
            ->limit($start,$nums)
            ->fetchAll();
        $today_starttime =  strtotime(date('Y-m-d').'00:00:00');
        $today_income= DI()->notorm->users_coinrecord
            ->where("uid = '{$uid}' and action  = 'video_uplode_reward'")
            ->where("addtime >".$today_starttime)
            ->sum('totalcoin');
        $total_income= DI()->notorm->users_coinrecord
            ->where("uid = '{$uid}' and action  = 'video_uplode_reward'")
            ->sum('totalcoin');
        $res['today_income'] = isset($today_income)? $today_income:'0.00';
        $res['total_income'] = isset($total_income)? $total_income:'0.00';
        $res['lists'] =$lists;
        return $res;
    }
    /* 主播认证 */
    public function getLiveInfo($uid,$live_id){


        $users_vip = DI()->notorm->users_vip

            ->where('uid=?',$uid)
            ->fetchOne(); // 查vipid
        $users_vip_info = DI()->notorm->vip
            ->where("id ='{$users_vip['vip_id']}'")
            ->fetchOne();
        $liveInfo =  DI()->notorm->users_auth->where("uid = '{$live_id}'")  ->fetchOne();
        if ($users_vip_info['is_super_member'] ){

            $userInfo  = DI()->notorm->users->where("id = '{$live_id}'")  ->fetchOne();
            $liveInfo['sex'] = $userInfo['sex'];
            $liveInfo['birthday'] = $userInfo['birthday'];
            $liveInfo['signature'] = $userInfo['signature'];
            $liveInfo['province'] = $userInfo['province'];
            $liveInfo['city'] = $userInfo['city'];
            $liveInfo['avatar'] = $userInfo['avatar'];
        }else{
            $liveInfo['sex'] = '';
            $liveInfo['birthday'] =  '';
            $liveInfo['signature'] =  '';
            $liveInfo['province'] =  '';
            $liveInfo['city'] =  '';
            $liveInfo['real_name'] = '';
            $liveInfo['mobile'] = '';
            $liveInfo['wchat'] = '';
            $liveInfo['avatar'] = '';

        }

        return $liveInfo;

    }

    public function getAllSub($puid,$data=array(),$num=0){
        $num++;
        $sub = DI()->notorm->users_agent->where('one_uid=?',intval($puid))->select("uid")->order('uid desc')->fetchAll();
        if($num==1){
            $data['directly_sub'] = array();
            $data['undirectly_sub'] = array();
        }
        foreach ($sub as $key=>$val){
            if($num==1){
                array_push($data['directly_sub'],$val);
            }else{
                array_push($data,$val);
            }
            $sub = $this->getAllSub($val['uid'],array(),$num);
            if(is_array($sub) && count($sub)>0){
                if($num==1){
                    $data['undirectly_sub'] = array_merge($data['undirectly_sub'],$sub);
                }else{
                    $data = array_merge($data,$sub);
                }
            }

        }

        return $data;
    }


    public function getSubUser($uid,$room_id)
    {
        $sub = $this->getAllSub($uid);
        $info = array(
            'directly_sub' => $sub['directly_sub'],
            'undirectly_sub' => $sub['undirectly_sub'],
            'all_sub' => array_merge($sub['directly_sub'],$sub['undirectly_sub']),
        );
        $all_sub_ids = array_keys(array_column($info['all_sub'],null,'uid'));

        $user_list=DI()->notorm->users
            ->select("id,user_nicename,avatar_thumb")
            ->where('id',$all_sub_ids)
            ->fetchAll();
        $user_list = array_column($user_list,null,'id');

        if($room_id){
            $friend_list = DI()->notorm->users_chatroom_friends->select("sub_uid")->where('status!=2 and room_id=?',intval($room_id))->fetchAll();
            $friend_ids = count($friend_list)>0 ? array_keys(array_column($friend_list,null,'sub_uid')) : array();
            foreach ($info['directly_sub'] as $key=>$val){
                if(in_array($val['uid'],$friend_ids)){
                    $info['directly_sub'][$key]['is_friend'] = 1;
                }else{
                    $info['directly_sub'][$key]['is_friend'] = 0;
                }
            }
            foreach ($info['undirectly_sub'] as $key=>$val){
                if(in_array($val['uid'],$friend_ids)){
                    $info['undirectly_sub'][$key]['is_friend'] = 1;
                }else{
                    $info['undirectly_sub'][$key]['is_friend'] = 0;
                }
            }
        }

        foreach ($info['directly_sub'] as $key=>$val){
            $info['directly_sub'][$key]['id'] = intval($val['uid']);
            unset($info['directly_sub'][$key]['uid']);
            if(isset($user_list[$val['uid']])){
                $info['directly_sub'][$key]['user_nicename'] = $user_list[$val['uid']]['user_nicename'];
                $info['directly_sub'][$key]['avatar_thumb'] = $user_list[$val['uid']]['avatar_thumb'];
            }else{
                $info['directly_sub'][$key]['user_nicename'] = '';
                $info['directly_sub'][$key]['avatar_thumb'] = '';
            }
        }
        foreach ($info['undirectly_sub'] as $key=>$val){
            $info['undirectly_sub'][$key]['id'] = intval($val['uid']);
            unset($info['undirectly_sub'][$key]['uid']);
            if(isset($user_list[$val['uid']])){
                $info['undirectly_sub'][$key]['user_nicename'] = $user_list[$val['uid']]['user_nicename'];
                $info['undirectly_sub'][$key]['avatar_thumb'] = $user_list[$val['uid']]['avatar_thumb'];
            }else{
                $info['undirectly_sub'][$key]['user_nicename'] = '';
                $info['undirectly_sub'][$key]['avatar_thumb'] = '';
            }
        }

        $info['all_sub'] = array_merge($info['directly_sub'],$info['undirectly_sub']);

        return $info;
    }

    public function userAction($uid, $json_data)
    {
        $code = codemsg();
        if (!is_json($json_data)) {
            $json_data = stripslashes($json_data);
            if (!is_json($json_data)) {
                return array('code' => 2055, 'msg' => $code['2055'], 'info' => array());
            }
        }
        $json_arr = json_decode($json_data, true);

        $users_vip_info = DI()->notorm->users_vip->where('uid=? and endtime>?',intval($uid),time())->order('grade desc')->fetchOne();
        $grade = isset($users_vip_info['grade']) ? $users_vip_info['grade'] : 1; // 默认是vip1

        $integral_config = DI()->notorm->integral_config->where('type=2 and vip=?', intval($grade))->fetchOne();
        if(!$integral_config){
            return array('code' => 2056, 'msg' => $code['2056'], 'info' => array());
        }
        if (is_array($json_arr)) {
            foreach ($json_arr as $key => $val) {
                if (!is_json($val) && is_string($val)) {
                    $val = stripslashes($val);
                    if (!is_json($val)) {
                        continue;
                    }
                }
                $val = is_array($val) ? $val : json_decode($val, true);
                if (isset($val['action_type']) && isset($val['action_time']) && isset($val['ctime'])) {
                    $this->record_user_action($integral_config, $uid, $val['action_type'], $val['action_time'], $val['ctime'], $grade);
                }else{
                    return array('code' => 2055, 'msg' => $code['2055'], 'info' => array());
                }
            }
            delUserInfoCache($uid);
        }
        return array('code' => 0, 'msg' => '', 'info' => array());
    }

    public function record_user_action($integral_config,$uid,$action_type,$action_time,$ctime,$vip_grade){
        $integral = 0;
        foreach (json_decode($integral_config['val'],true) as $key=>$val){
            if($val['k']==$action_type && $val['val']>0){
                $integral = $val['val'];
            }
        }

        foreach ($ctime as $key=>$val){
            $u_info = DI()->notorm->users->select('user_login, integral,addup_integral')->where(' id=? ',$uid)->fetchOne();
            $tenantId = getTenantId();
            $data = array(
                'uid' => intval($uid),
                'user_login' => $u_info['user_login'],
                'vip' => intval($vip_grade),
                'addup_integral'=>intval($u_info['addup_integral']+$integral),
                'action_type' => intval($action_type),
                'action_time' => intval($action_time),
                'tenant_id' => intval($tenantId),
                'start_integral'=>intval($u_info['integral']),
                'change_integral'=>intval($integral),
                'end_integral'=>intval($u_info['integral']+$integral),
                'ctime' => intval($val),
                'giveout_time' => time(),
            );

            $rs = DI()->notorm->users_action->insert($data);
            if(!$rs){
                return false;
            }

            if($integral <= 0){
                return false;
            }

            $prefix= DI()->config->get('dbs.tables.__default__.prefix');
            DI()->notorm->users->query('UPDATE '.$prefix.'users SET integral = integral+'.$integral.',addup_integral = addup_integral+'.$integral.' WHERE id = '.$uid,array('integral'=>$integral));

            $action_type_list = actionType();
            $integraldata=array(
                'uid'=>intval($uid),
                'start_integral'=>intval($u_info['integral']),
                'change_integral'=>intval($integral),
                'end_integral'=>intval($u_info['integral']+$integral),
                'act_type'=>3, // 用户行为
                'status'=>1,
                'remark'=>$action_type_list[$action_type],
                'ctime'=>time(),
                'act_uid'=>intval($uid),
                'tenant_id'=>intval($tenantId),
            );
            $rs = DI()->notorm->integral_log->insert($integraldata);
        }
        if(!isset($rs) || !$rs){
            return false;
        }
        return true;
    }

    public function  setCommission($uid,$liveuid,$anthor_total,$anchor_profit_ratio)
    {
        $authinfo = DI()->notorm->commission_set
            ->select("*")
            ->where('uid=?', $liveuid)
            ->fetchOne();
        //设置了主播分成，使用主播分成，反之，家族分成就是主播分成
        if ($authinfo) {
            $anthor_totals = round($authinfo['anchor_betcommission'] / 100 * $anthor_total, 3); //主播分成


            $config = getConfigPub();
            $family_totals = round($config['anchor_platform_ratio'] / 100 * $anthor_total, 3);//家族分成
            $res['anthor_rent']  =    round($authinfo['anchor_betcommission'] / 100 , 3);
            $res['family_rent']  =    round(($config['anchor_platform_ratio']-$authinfo['anchor_betcommission']) / 100 , 3);

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
                    if(isset($userfamily['familyid'])){
                        $familysinfo=DI()->notorm->users->select("*")->where('id=?',$family['uid'])->fetchOne();
                        $family_id = $familysinfo['game_user_id'];
                        $family_user_id = $family['uid'];
                    }else{
                        $family_id   =  0;
                    }

                }else{
                    $family_id   =  0;
                }


            }else{
                $family_id   =  0;
            }


        } else {

            $anthor_totals = round($anchor_profit_ratio * $anthor_total, 3);
            $family_totals = round($anchor_profit_ratio * $anthor_total, 3);
            $res['anthor_rent']  =    round($anchor_profit_ratio , 3);
            $family_id   =  0;
        }
        $res['anthor_total'] = $anthor_totals; //主播分成
        $res['family_total'] = $family_totals; //家族分成

        $res['family_id']    = $family_id;     //家族长分成 ,对应java那边的id字段
        $res['family_user_id']    = $family_user_id;     //家族长分成，对应直播后台的id字段

        $res['familyhead_info']    = $familysinfo;

        return $res;
    }

    public function savebeauty($uid,$client,$data_param)
    {
        if (!is_json($data_param)) {
            $data_param = stripslashes($data_param);
            if (!is_json($data_param)) {
                return array('code' => 2055, 'msg' => codemsg('2055'), 'info' => array());
            }
        }

        if(mb_strlen($data_param) > 5000){
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => array('lenght'=>mb_strlen($data_param)));
        }
        $data = json_decode($data_param,true);
        $userinfo = getUserInfo($uid);
        $beauty = is_array($userinfo['beauty']) ? $userinfo['beauty'] : (is_json($userinfo['beauty']) ? json_decode($userinfo['beauty'],true) : []);
        foreach ($beauty as $key=>$val){
            if(!in_array($key,['client_1','client_2','client_3','client_4'])){
                unset($beauty[$key]);
            }
        }
        $beauty['client_'.$client] = $data;
        $res=DI()->notorm->users->where('id=?',$uid)->update(['beauty'=>json_encode($beauty)]);
        delUserInfoCache($uid);
        if(!($res >= 0)){
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => array());
        }
        return array('code' => 0, 'msg' => '', 'info' => array());
    }

    /*  // 再模糊搜索的 会员列表去掉模糊搜索
      if(count($list) < $pnum){
          $pnum = $pnum - count($list);
          if($whichTenant == 1){ //彩票租户
              $where_3 = " (game_user_id like '{$keystring}%' or reverse(game_user_id) like reverse('%{$keystring}')) or (user_nicename like '{$keystring}%' or reverse(user_nicename) like reverse('%{$keystring}')) ";
          }else{
              $where_3 = " (id like '{$keystring}%' or reverse(id) like reverse('%{$keystring}')) or (user_nicename like '{$keystring}%' or reverse(user_nicename) like reverse('%{$keystring}'))";
          }
          $list_2 = DI()->notorm->users_family
              ->select('uid,game_user_id,user_nicename')
              ->where(['tenant_id'=>$tenant_id])->where($where_3)->limit($start,$pnum)->fetchAll();
          foreach ($list_2 as $key=>$val){
              if($uid && $val['uid'] != $uid){
                  array_push($list,$val);
              }
          }
      }*/
    public function searchUser($uid,$keystring,$p){
        $redis = connectionRedis();
        $tenant_id = getTenantId();
        $configpri=getConfigPri();

        if(!$keystring){
            return array('code' => 0, 'msg' => '', 'info' => array());
        }

        $p = $p >= 1 ? $p : 1;
        $pnum=20;
        $start=($p-1)*$pnum;

        $whichTenant= whichTenat(getGameTenantId());

        // 先搜索id的
        $where_1 = $whichTenant == 1 ? " game_user_id = '{$keystring}' " : " id = '{$keystring}' "; // 1 彩票租户
        $list = DI()->notorm->users
            ->select('id,user_nicename,avatar,avatar_thumb,sex,votestotal,consumption,game_user_id')
            ->where(['tenant_id'=>$tenant_id])->where($where_1)->order('id desc')->limit($start,$pnum)->fetchAll();
        foreach ($list as $key=>$val){
            if($uid && $val['uid'] == $uid){
                unset($list[$key]);
            }
        }

        // 再搜索昵称的
        if(count($list) < $pnum){
            $pnum = $pnum - count($list);
            $list_2 = DI()->notorm->users
                ->select('id,user_nicename,avatar,avatar_thumb,sex,votestotal,consumption,game_user_id')
                ->where(['tenant_id'=>$tenant_id, 'user_nicename'=>$keystring])->order('id desc')->limit($start,$pnum)->fetchAll();
            foreach ($list_2 as $key=>$val){
                if(!$uid || ($val['uid'] != $uid)){
                    array_push($list,$val);
                }
            }
        }

        $live_list = array();
        if(count($list) > 0){
            $uids = array_keys(array_column($list,null,'id'));
            $uids = implode(',',$uids);

            $live_list = DI()->notorm->users_live
                ->select('uid,islive,stream,title,city,thumb,pull,isvideo,type,type_val,game_action,goodnum,anyway,ishot,isrecommend,liveclassid,top,ly_recommend')
                ->where(['tenant_id'=>$tenant_id])
                ->where("uid in ({$uids})")
                ->order('uid desc')
                ->fetchAll();

            $liveing_set_list = DI()->notorm->liveing_set ->select("*") ->where(" tenant_id=? ",$tenant_id) ->order("id desc")->fetchAll();
            foreach ($live_list as $key=>$val){
                $live_list[$key]['game_info'] = getLiveGameInfo($liveing_set_list,$val['uid'],$tenant_id);
            }

            $live_list = count($live_list)>0 ? array_column($live_list,null,'uid') : array();
        }

        foreach ($list as $key=>$val){
            if(isset($live_list[$val['id']]) && $live_list[$val['id']]['islive']==1){
                $list[$key]['haslive'] = 1;
                foreach ($live_list[$val['id']] as $k=>$v){
                    $list[$key][$k] = $v;
                }
                $list[$key]['thumb'] = $list[$key]['thumb'] ? $list[$key]['thumb'] : $val['avatar'];
            }else{
                $list[$key]['haslive'] = 0;
            }
            $list[$key]['level_anchor'] = getLevelAnchor($val['votestotal']);
        }

        // 屏蔽主播断开连接的直播间
        $disconnect = $redis->zRange('disconnect_'.$tenant_id,0,100000000);
        foreach ($list as $key=>$val){
            if(is_array($disconnect) && count($disconnect) > 0 && in_array($val['uid'],$disconnect)){
                $list[$key]['haslive'] = 0;
            }
        }

        return array('code' => 0, 'msg' => '', 'info' => $list);

    }

    public function getUserLevel($uid){
        $redis = connectionRedis();

        $user_info = getUserInfo($uid);
        $data['level_info'] = getLevelInfo($user_info['consumption']);
        $data['next_level_info'] = getNextLevelInfo($user_info['consumption']);
        $data['level_info']['consumption'] = $user_info['consumption'];
        $data['level_info']['exp_to_next'] = $data['next_level_info']['experience']-$user_info['consumption'];

        $data['anchor_level_info'] = getAnchorLevelInfo($user_info['votestotal']);
        $data['anchor_next_level_info'] = getAnchorNextLevelInfo($user_info['votestotal']);
        $data['anchor_level_info']['votestotal'] = $user_info['votestotal'];
        $data['anchor_level_info']['exp_to_next'] = $data['anchor_next_level_info']['experience']-$user_info['votestotal'];

        return array('code' => 0, 'msg' => '', 'info' => $data);

    }
    public function getGameuserinfo($game_user_id,$game_tenant_id){

        $gameuserinfo =DI()->notorm->users
            ->select('*')
            ->where('game_user_id=? and game_tenant_id=?',$game_user_id,$game_tenant_id)
            ->fetchOne();


        return !empty($gameuserinfo)?$gameuserinfo:[];

    }
    public function charge_withdrawn($uid){
        $redis = connectRedis();
        $length = $redis->lLen($uid.'_reward_time');
        $redisList = $redis->lRange($uid.'_reward_time',0,$length);
        $totalAmount = 0;
        $info = getUserInfo($uid);

        foreach ($redisList as $value){
            $amount = $redis->get($uid.'_'.$value.'_reward') ;

            if ($amount > 0){
                $totalAmount = bcadd($totalAmount,$amount,2);
            }else{
                $redis->lRem($uid.'_reward_time',$value,0);
            }
        }
        $coin = $info['nowithdrawable_coin']-$totalAmount;
        if ($coin>0){
            DI()->notorm->users ->where("id = {$uid}")
                ->update( array('coin' => new NotORM_Literal("coin +{$coin}")
                ,'nowithdrawable_coin' => new NotORM_Literal("nowithdrawable_coin -{$coin}")) );
            $insert=array(
                "type"=>'move',
                "action"=>'charge_withdrawn',
                "uid"=>$uid,
                'user_login' => $info['user_login'],
                'user_type' => $info['user_type'],
                "giftid"=>0,
                "totalcoin"=>$coin,
                "addtime"=>time(),
                'tenant_id' =>getTenantId(),
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);
            delUserInfoCache($uid);
        }else{
            return ['code'=> 1000,'msg'=>'暂无可转为可提现余额'];
        }

        $info = getUserInfo($uid);
        if($info){
            $info['level']=getLevel($info['consumption']);
            $info['exp_can_speak'] = exp_can_speak($info['consumption']);
            $info['level_info']=getLevelInfo($info['consumption']);
            $info['level_anchor']=getLevelAnchor($info['votestotal']);
            $info['lives']=getLives($uid);
            $info['follows']=getFollows($uid);
            $info['fans']=getFans($uid);
            $info['vip']=getUserVip($uid);
            $info['liang']=getUserLiang($uid);
            $time = strtotime(date('Y-m-d',time()));
            $info['long_view_times']= DI()->notorm->video_watch_record->where("uid=? and video_type='2' and addtime >= '{$time}'" ,$uid)->count();
            $info['short_view_times']= DI()->notorm->video_watch_record->where("uid=? and video_type='1' and addtime >= '{$time}' ",$uid)->count();
            $short_view_times_total= DI()->notorm->video_watch_record->where("uid=? and video_type='1' " ,$uid)->count(); // 短视频总共观看次数
            $long_view_times_total  = DI()->notorm->video_watch_record->where("uid=? and video_type='2'  ",$uid)->count();// 长视频视频总共观看次数

            $info['total_view_times']=    DI()->notorm->video_watch_record->where("uid='{$uid}'")->count();;
            $user_model = new Model_Login();
            $vip_info = $user_model->getUserjurisdiction($uid,$info['user_type']);
            $info['watch_number'] = $vip_info['watch_number']!=0 ? ($vip_info['watch_number']+ $info['watch_num']) : $vip_info['watch_number'];
            $short_surplus_view_times = $info['watch_number'] - $short_view_times_total;
            if ($short_surplus_view_times < 0){
                $short_surplus_view_times = 0;
            }
            $long_surplus_view_times =  $vip_info['watch_number'] - $long_view_times_total;
            if ($long_surplus_view_times < 0){
                $long_surplus_view_times = 0;
            }
            $info['short_surplus_view_times'] = $short_surplus_view_times;
            $info['long_surplus_view_times'] = $long_surplus_view_times;
            $info['watch_duration'] = $vip_info['watch_duration']!=0 ? ($vip_info['watch_duration']+$info['watch_time']) : $vip_info['watch_duration'];
            $info['level_addtime'] = $vip_info['level_addtime'];
            $info['level_endtime'] = $vip_info['level_endtime'];
            $info['new_level'] = $vip_info['new_level'] ;
            $info['level_name'] =  $vip_info['level_name'] ;
            $info['user_vip_status'] = $vip_info['user_vip_status'];
            $info['user_vip_action_type'] = $vip_info['user_vip_action_type'];
            $info['level_name_jurisdiction'] = $vip_info['level_name_jurisdiction'];
            $info['withdrawable_coin'] = $info['coin'];
            $info['totalcoin'] = bcadd($info['withdrawable_coin'],$info['nowithdrawable_coin'],2);
            $info['beauty'] = isset($info['beauty']) ? $info['beauty'] : '';
            $info['noble'] = getUserNoble($info['id']);

            unset($info['watch_num']);
            unset($info['watch_time']);
        }


        return ['code'=> 1,'info'=>$info];

    }
    public function charge_gift($uid){

        $tenant_id = getTenantId();
        $key = 'getGiftList_'.$tenant_id;
        $giftlist = getcache($key);
        if(!$giftlist){
            $domain = new Domain_Live();
            $giftlist=$domain->getGiftList();
            setcaches($key,$giftlist, 60*60*24*7);
        }
        $nobelist = getNobleList($tenant_id);
        $carlist = get_carlist($tenant_id);

        $lists = DI()->notorm->charge_gift
            ->select("*")
            ->where('tenant_id =? and is_open = 1 ',$tenant_id)
            ->order('orderno asc,id desc')
            ->fetchAll();
        if(!empty($lists)){
            foreach ($lists as $key=>$val) {
                foreach ($giftlist as $key1 => $val1) {
                    if ($val['gift_id'] == $val1['id']) {
                        $lists[$key]['gift_name'] = $val1['giftname'];
                        $lists[$key]['gift_thumb'] = $val1['gifticon'];
                    }
                }
                foreach ($nobelist as $key2 => $val2){
                    if($val['nobel_id'] == $val2['id']){
                        $lists[$key]['nobel_name'] = $val2['name'];
                        $lists[$key]['medal_thumb'] = $val2['medal'];
                    }
                }
                foreach ($carlist as $key3 => $val3){
                    if($val['car_id'] == $val3['id']){
                        $lists[$key]['car_name'] = $val3['name'];
                        $lists[$key]['car_thumb'] = $val3['thumb'];
                    }
                }
            }

            return array('code' => 0, 'msg' => '', 'info' => $lists);
        }


        return array('code' => 0, 'msg' => '', 'info' => $lists);
    }
    public function chargegift_send($game_user_id,$price){
        $tenant_id = getTenantId();
        $price = $price/1000;
        $chargeinfo = DI()->notorm->charge_gift->select("*")->where(' tenant_id = ? and is_open = 1',$tenant_id)->fetchAll();
        foreach ($chargeinfo as $key=>$value){
            $arr[$value['id']] = $value['price'];
        }
        rsort($arr);
        foreach ($arr as $value){
            if($price>= $value){
               $limit_price = $value;
               break;
            }
        }
        if(!$limit_price){
            return array('code' => 1001, 'msg' => '获取不到对应的首冲 ID', 'info' => []);
        }else{
            $chargeinfo = DI()->notorm->charge_gift->select("*")->where('price =? and tenant_id = ? ',$limit_price,$tenant_id)->fetchOne();
            if(empty($chargeinfo)){
                return array('code' => 1001, 'msg' => '获取不到对应的首冲 ID', 'info' => []);
            }
        }

        $uidinfo = DI()->notorm->users->select("id")->where('game_user_id =? ',$game_user_id)->fetchOne();
        $uid = $uidinfo['id'];
        logapi(json_encode(array('game_user_id'=>$game_user_id,'price'=>$price,'time'=>date('Y-m-d H:i:s',time())),JSON_UNESCAPED_UNICODE),'首充回调');


        //判断是否已经领取
        $userchargeinfo = DI()->notorm->users_chargegift->select("*")->where('uid =? and  game_user_id=?',$uid,$game_user_id)->fetchOne();
        if($userchargeinfo){
            return array('code' => 1001, 'msg' => '您已经领取过首充豪礼，请勿重复领取', 'info' => []);
        }else{
            $time = time();
            $usercharge = array(
                'game_user_id'=>$game_user_id,
                'uid'=>$uid,
                'gift_id'=>$chargeinfo['gift_id'],
                'car_id'=>$chargeinfo['car_id'],
                'nobel_id'=>$chargeinfo['nobel_id'],
                'gift_nums'=>$chargeinfo['gift_num'],
                'nobel_endtime'=>$time+86400*$chargeinfo['nobel_days'],
                'car_endtime'=>$time+86400*$chargeinfo['car_num'],
                'addtime'=>$time,
                'tenant_id'=>$chargeinfo['tenant_id'],
            );
            $rs=DI()->notorm->users_chargegift->insert($usercharge);
            if(!$rs){
                return array('code' => 1001, 'msg' => '操作失败', 'info' => []);
            }
        }


        return array('code' => 0, 'msg' => '操作成功', 'info' => []);
    }
    public function chargegift_list($uid){

        $tenant_id = getTenantId();
        $key = 'getGiftList_'.$tenant_id;
        $giftlist = getcache($key);
        if(!$giftlist){
            $domain = new Domain_Live();
            $giftlist=$domain->getGiftList();
            setcaches($key,$giftlist, 60*60*24*7);
        }
        $nobelist = getNobleList($tenant_id);
        $carlist = get_carlist($tenant_id);

        $lists = DI()->notorm->users_chargegift
            ->select("*")
            ->where('tenant_id =? and uid =? ',$tenant_id,$uid)
            ->fetchAll();
        if(!empty($lists)){
            foreach ($lists as $key=>$val) {
                foreach ($giftlist as $key1 => $val1) {
                    if ($val['gift_id'] == $val1['id']) {
                        $lists[$key]['gift_name'] = $val1['giftname'];
                        $lists[$key]['gift_thumb'] = $val1['gifticon'];
                        $lists[$key]['gift_price'] = $val1['needcoin'];
                    }
                }
                foreach ($nobelist as $key2 => $val2){
                    if($val['nobel_id'] == $val2['id']){
                        $lists[$key]['nobel_name'] = $val2['name'];
                        $lists[$key]['nobel_thumb'] = $val2['medal'];
                        $lists[$key]['nobel_price'] = $val2['price'];
                    }
                }
                foreach ($carlist as $key3 => $val3){
                    if($val['car_id'] == $val3['id']){
                        $lists[$key]['car_name'] = $val3['name'];
                        $lists[$key]['car_thumb'] = $val3['thumb'];
                        $lists[$key]['car_price'] = $val3['needcoin'];
                    }
                }

                $lists[$key]['car_endtime'] =  date('Y-m-d H:i:s',$val['car_endtime']);     //坐骑到期时间
                $lists[$key]['nobel_endtime'] =  date('Y-m-d H:i:s',$val['nobel_endtime']); //贵族到期天数
                $lists[$key]['car_leftdays'] =    ceil( ($val['car_endtime']-time())/86400);      //坐骑剩余天数
                $lists[$key]['nobel_leftdays'] =   ceil( ($val['nobel_endtime']-time())/86400);   //贵族剩余天数
            }

            return array('code' => 0, 'msg' => '', 'info' => $lists);
        }


        return array('code' => 0, 'msg' => '', 'info' => $lists);
    }

    public  function transfer($uid,$touid,$amount,$user_nicename){
        $code = codemsg();
        $tenant_id = getTenantId();
        $userModel = new Model_User();
        $tidInfo = $userModel->getUserInfoWithIdAndTid($touid, $tenant_id);
        if (!$tidInfo){
            return array('code' => 2101, 'msg' => $code['2101'], 'info' => array());
        }
        if ($tidInfo['user_nicename'] != $user_nicename){
            return array('code' => 2099, 'msg' => $code['2099'], 'info' => array());
        }
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid, $tenant_id);

        if ($userInfo['user_nicename'] == $user_nicename){
            return array('code' => 2100, 'msg' => $code['2100'], 'info' => array());
        }
        if ($userInfo['coin']< $amount){
            return array('code' => 2006, 'msg' => $code['2006'], 'info' => array());
        }

       DI()->notorm->users
            ->where('id = ?', $uid)
            ->update(array('coin' => new NotORM_Literal("coin - {$amount}")) );
        delUserInfoCache($uid);
        DI()->notorm->users
            ->where('id = ?', $touid)
            ->update(array('coin' => new NotORM_Literal("coin + {$amount}")) );
        delUserInfoCache($touid);
        $id = DI()->notorm->user_transfer->insert(['uid'=>$uid,'toid'=>$touid,'amount'=>$amount,'addtime'=>time(),'status'=>1,'tenant_id' =>getTenantId(),]);
        $insert=array(
            "type"=>'income',
            "action"=>'transfer_add',
            "uid"=>$touid,
            'user_login' => $tidInfo['user_login'],
            'user_type' => $tidInfo['user_type'],
            "giftid"=>0,
            "pre_balance" => floatval($tidInfo['coin']),
            "totalcoin"=>$amount,
            "after_balance" => floatval(bcadd($tidInfo['coin'], $amount,4)),
            "addtime"=>time(),
            'tenant_id' =>getTenantId(),
            'remark' => '来自用户id:'.$uid.'的转入'
        );
        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord($insert);
        delUserInfoCache($touid);
        $insert=array(
            "type"=>'expend',
            "action"=>'transfer_sub',
            "uid"=>$uid,
            'user_login' => $userInfo['user_login'],
            'user_type' => $userInfo['user_type'],
            "giftid"=>0,
            "pre_balance" => floatval($userInfo['coin']),
            "totalcoin"=>$amount,
            "after_balance" => floatval(bcadd($userInfo['coin'], -abs($amount),4)),
            "addtime"=>time(),
            'tenant_id' =>getTenantId(),
            'remark' => '转入到用户id:'.$touid,
        );
        $coinrecordModel->addCoinrecord($insert);

        return array('code' => 0,'msg' =>'操作成功', 'info' => array());

    }

    public function findUser($userdata){
        $code = codemsg();
        $tenant_id = getTenantId();
        if(is_numeric($userdata)){
            $where = "  id = {$userdata} and tenant_id = {$tenant_id} and user_type in(2,5,6)  ";
        }else{
            $userdata = $this->unicodeDecode(stripcslashes($userdata));
            $where = "  user_nicename = '{$userdata}' and tenant_id = {$tenant_id} and user_type in(2,5,6)  ";
        }

        $list = DI()->notorm->users
            ->select('id,user_login,user_nicename,avatar')
            ->where($where)->fetchAll();
        if(!$list){
            return array('code' => 0,'msg' =>'会员不存在', 'info' => $list);
        }
        return array('code' => 0,'msg' =>'查询成功', 'info' => $list);
    }

    public function getregUrl(){
        $tenant_id = getTenantId();

        $data  = DI()->notorm->users_reg_url->where("tenant_id = {$tenant_id} and  status  = 1 ")
            ->fetchAll();
        return array('code' => 0,'msg' =>'查询成功', 'info' => $data);
    }

    public function unicodeDecode($unicode_str)
    {
        $json = '{"str":"' . $unicode_str . '"}';
        $arr = json_decode($json, true);
        if (empty($arr)) return '';
        return $arr['str'];
    }
    //余额宝转出

    public  function transferOutyuebao($uid,$amount,$data,$type){

        $code = codemsg();
        $tenant_id = getTenantId();
        $userInfo = DI()->notorm->users
            ->select('yeb_balance,user_login,user_type,coin')
            ->where(['tenant_id'=>$tenant_id,'id' =>$uid])->fetchOne();
        if ($userInfo['yeb_balance']< $amount){
            return array('code' => 2006, 'msg' => $code['2006'], 'info' => array());
        }
        //转出到余额
        if($type==1){
            //更新数据
            DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('coin' => new NotORM_Literal("coin + {$amount}"),'yeb_balance' => new NotORM_Literal("yeb_balance - {$amount}")) );
            delUserInfoCache($uid);
            //写入日志
            $id = DI()->notorm->user_transfer_yuebao->insert(
                [
                    'uid'=>$uid,
                    'user_login'=>$userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    'amount'=>$amount,
                    'mark'=>'手动转出米利宝',
                    'addtime'=>time(),
                    'status'=>1,     //处理完成
                    'type'=>1,       //转出到余额
                    'tenant_id' =>getTenantId(),
               ]
            );
            $insert=array(
                "type"=>'income',
                "action"=>'yuebaoout_coin',
                "uid"=>$uid,
                'user_login'=>$userInfo['user_login'],
                'user_type'=>$userInfo['user_type'],
                "pre_balance" => floatval($userInfo['coin']),
                "after_balance" => floatval(bcadd($userInfo['coin'], $amount,4)),
                "giftid"=>0,
                "totalcoin"=>$amount,
                "addtime"=>time(),
                'tenant_id' =>getTenantId(),
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);

        }
        //转出到银行卡
        if($type==2){
            //更新数据
            DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('yeb_balance' => new NotORM_Literal("yeb_balance - {$amount}")) );
            delUserInfoCache($uid);
            //写入日志
            $id = DI()->notorm->user_transfer_yuebao->insert(
                [
                    'uid'=>$uid,
                    'user_login'=>$userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    'amount'=>$amount,
                    'mark'=>'银行卡手动转出米利宝',
                    'addtime'=>time(),
                    'status'=>0,     //处理完成
                    'type'=>2,       //转出到银行卡
                    'tenant_id' =>getTenantId(),
                    'bankname'=>$data['bankname'],
                    'banknumber'=>$data['banknumber'],
                    'realname'=>$data['realname'],
                    'phonenumber'=>$data['phonenumber'],
                ]
            );
         /*   $insert=array(
                "type"=>'expend',
                "action"=>'yuebaoout_bank',
                "uid"=>$uid,
                'user_login'=>$userInfo['user_login'],
                'user_type'=>$userInfo['user_type'],
                "giftid"=>0,
                "totalcoin"=>$amount,
                "addtime"=>time(),
                'tenant_id' =>getTenantId(),
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);*/
            return array('code' => 0,'msg' =>'转出成功，等待后台审核！', 'info' => array());
        }

        return array('code' => 0,'msg' =>'操作成功', 'info' => array());

    }

    //转入到米利宝
    public  function transferInyuebao($uid,$amount,$data,$type){

        $code = codemsg();
        $tenant_id = getTenantId();
        $userInfo = DI()->notorm->users
            ->select('coin,user_type,user_login')
            ->where(['tenant_id'=>$tenant_id,'id' =>$uid])->fetchOne();

        //余额转入米利宝
        if($type==1){
            if ($userInfo['coin']< $amount){
                return array('code' => 2006, 'msg' => $code['2006'], 'info' => array());
            }
            //更新数据
            DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('coin' => new NotORM_Literal("coin - {$amount}"),'yeb_balance' => new NotORM_Literal("yeb_balance + {$amount}")) );
            delUserInfoCache($uid);
            //写入日志
            $id = DI()->notorm->user_transfer_yuebao->insert(
                [
                    'uid'=>$uid,
                    'user_login'=>$userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    'amount'=>$amount,
                    'mark'=>'手动转入米利宝',
                    'addtime'=>time(),
                    'status'=>1,     //处理完成
                    'type'=>3,       //转出到余额
                    'tenant_id' =>getTenantId(),
                ]
            );
            $insert=array(
                "type"=>'expend',
                "action"=>'yuebaoin_coin',
                "uid"=>$uid,
                'user_login'=>$userInfo['user_login'],
                'user_type'=>$userInfo['user_type'],
                "pre_balance" => floatval($userInfo['coin']),
                "after_balance" => floatval(bcsub($userInfo['coin'], $amount,4)),
                "giftid"=>0,
                "totalcoin"=>$amount,
                "addtime"=>time(),
                'tenant_id' =>getTenantId(),
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);

        }
        //银行卡转入
        if($type==2){
            //更新数据
           /* DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('yeb_balance' => new NotORM_Literal("yeb_balance + {$amount}")) );
            delUserInfoCache($uid);*/
            //写入日志
            $id = DI()->notorm->user_transfer_yuebao->insert(
                [
                    'uid'=>$uid,
                    'user_login'=>$userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    'amount'=>$amount,
                    'mark'=>'银行卡手动转入米利宝',
                    'addtime'=>time(),
                    'status'=>0,     //处理完成
                    'type'=>4,       //银行卡转入
                    'tenant_id' =>getTenantId(),
                    'bankname'=>$data['bankname'],
                    'banknumber'=>$data['banknumber'],
                    'realname'=>$data['realname'],
                    'phonenumber'=>$data['phonenumber'],
                ]
            );
          /*  $insert=array(
                "type"=>'expend',
                "action"=>'yuebaoin_bank',
                "uid"=>$uid,
                'user_login'=>$userInfo['user_login'],
                'user_type'=>$userInfo['user_type'],
                "giftid"=>0,
                "totalcoin"=>$amount,
                "addtime"=>time(),
                'tenant_id' =>getTenantId(),
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);*/
            return array('code' => 0,'msg' =>'转入成功，等待后台审核！', 'info' => array());
        }

        return array('code' => 0,'msg' =>'操作成功', 'info' => array());

    }

    public function settlementYuebao(){
        $s = strtotime(date('Y-m-d').'00:00:00');

        logapi(['result  '=>$s ],'【米利宝利息结算】');
        try {
            beginTransaction();
            $tenant_id = getTenantId();

            $config = getConfigPri();
            $daymoney = $config['yuebao_rate'] / 365;
            $coinrecordModel = new Model_Coinrecord();

            $userinfo = DI()->notorm->users
                ->select("id,yeb_balance,coin,tenant_id,user_type,user_login")
                ->where("tenant_id = {$tenant_id} and  yeb_balance>0 ")
                ->fetchAll();
            foreach ($userinfo as $key => $value) {
                $user_sum=DI()->notorm->user_transfer_yuebao
                    ->select("sum(amount) as total")
                    ->where('uid=?  and addtime>=? and status !=2  and type in(1,2)',$value['id'],$s)
                    ->fetchOne();
                if($user_sum['total']){
                    $money_out = $user_sum['total'];
                }else{
                    $money_out = 0;
                }
                $user_suminin=DI()->notorm->user_transfer_yuebao
                    ->select("sum(amount) as total")
                    ->where('uid=?  and addtime>=?  and status =1 and type in(3,4) ',$value['id'],$s)
                    ->fetchOne();
                if($user_suminin['total']){
                    $money_in = $user_suminin['total'];
                }else{
                    $money_in = 0;
                }

                //昨天的本金，当前米利宝加上转出的，减去转入的
                $yeb_balance = $value['yeb_balance']+$money_out-$money_in;
                $rate_money = $yeb_balance * $daymoney;
                $insert = array(
                    "uid" => $value['id'],
                    "user_login" => $value['user_login'],
                    "user_type" => $value['user_type'],
                    "date" => date("Ymd", strtotime("-1 day")),
                    "rate_money" => $rate_money,
                    "addtime" => time(),
                    'tenant_id' => getTenantId(),
                );
                DI()->notorm->yuebao_rate->insert($insert);
                //更新余额宝金额
                DI()->notorm->users
                    ->where('id = ?', $value['id'])
                    ->update(array('yeb_balance' => new NotORM_Literal("yeb_balance + {$rate_money}")));
               /* $insert=array(
                    "type"=>'income',
                    "action"=>'yuebao_rate',
                    "uid"=>$value['id'],
                    "user_login" => $value['user_login'],
                    'user_type'=>$value['user_type'],
                    "pre_balance" => floatval($value['coin']),
                    "totalcoin"=>$rate_money,
                    "after_balance" => floatval(bcadd($value['coin'], $rate_money,4)),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $coinrecordModel->addCoinrecord($insert);*/
                delUserInfoCache($value['id']);
            }
            commitTransaction();
            return array('code' => 0, 'msg' => '操作成功', 'info' => []);
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("米利宝利息结算 异常:".$ex->getMessage());
            return array('code' => 1001, 'msg' => '领取失败', 'info' => []);
        }
    }
    public function transferToyuebaoauto(){
        $config = getConfigPub();
        if($config['transfer_auto'] == 0){
            return array('code' => 1001, 'msg' => '未开启自动转账', 'info' => []);
        }

        try {
            beginTransaction();
            $tenant_id = getTenantId();
            $userinfo = DI()->notorm->users
                ->select("id,coin,user_type,user_login")
                ->where("tenant_id = {$tenant_id} and  coin>0 ")
                ->fetchAll();
            if(empty($userinfo)){
                return array('code' => 0, 'msg' => '操作成功', 'info' => []);
            }
            foreach ($userinfo as $key => $value) {
                //更新数据
                DI()->notorm->users
                    ->where('id = ?', $value['id'])
                    ->update(array('coin' => new NotORM_Literal("coin - {$value['coin']}"),'yeb_balance' => new NotORM_Literal("yeb_balance + {$value['coin']}")) );
                delUserInfoCache($value['id']);
                //写入日志
                $id = DI()->notorm->user_transfer_yuebao->insert(
                    [
                        'uid'=> $value['id'],
                        'user_login'=>$value['user_login'],
                        'amount'=>$value['coin'],
                        'addtime'=>time(),
                        'user_type'=>$value['user_type'],
                        'mark'=>'自动转入米利宝',
                        'status'=>1,     //处理完成
                        'type'=>3,       //转出到余额
                        'tenant_id' =>getTenantId(),
                    ]
                );
                $insert=array(
                    "type"=>'expend',
                    "action"=>'yuebaoin_coin',
                    "uid"=>$value['id'],
                    'user_login'=>$value['user_login'],
                    'user_type'=>$value['user_type'],
                    "giftid"=>0,
                    "totalcoin"=>$value['coin'],
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $coinrecordModel = new Model_Coinrecord();
                $coinrecordModel->addCoinrecord($insert);
            }
            commitTransaction();
            return array('code' => 0, 'msg' => '操作成功', 'info' => []);
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("米利宝自动转入 异常:".$ex->getMessage());
            return array('code' => 1001, 'msg' => '米利宝自动转入异常', 'info' => []);
        }
    }
    public function getdayRate($uid){
        $times = date('Ymd',strtotime("-1 day"));
        $yueinfobyuid = DI()->notorm->yuebao_rate->select("id,rate_money")->where(" date = {$times} and uid=? " ,$uid)->fetchOne();
        $yueinfo['rate_daymoney']=$yueinfobyuid?$yueinfobyuid['rate_money']:0;
        $yueinfoall=DI()->notorm->yuebao_rate
            ->select("sum(rate_money) as total")
            ->where('uid=?',$uid)
            ->fetchAll();
        $config = getConfigPri();
        if(empty($config['yuebao_rate'])){
            $config['yuebao_rate'] = 0;
        }
        $seven_rate =sprintf("%.4f",$config['yuebao_rate']/365*7);

        $yueinfo['rate_allmoney']=$yueinfoall[0]['total']==null?0:$yueinfoall[0]['total'];
        $yueinfo['seven_rate']=$seven_rate*100;
        return $yueinfo;

    }

    public function openYuebao($type,$uid){
        $tenant_id = getTenantId();
        //查询余额宝是否开启
      //  logapi(['bet info '=>$uid],'【开通余额宝1】');
        if($type==1){
            $userinfo=DI()->notorm->users
                ->select("yeb_isopen")
                ->where("tenant_id = {$tenant_id} and  id={$uid} ")
                ->fetchOne();
           // logapi(['bet info '=>$userinfo],'【开通余额宝】');
            return array('code' => 0,'msg' =>'操作成功', 'info' => array('yeb_isopen'=>$userinfo['yeb_isopen']));
        }
        if($type==2){
            //更新余额宝金额
            DI()->notorm->users
                ->where("tenant_id = {$tenant_id} and  id={$uid} ")
                ->update(array('yeb_isopen'=>1) );
            delUserInfoCache($uid);
        }
        return array('code' => 0,'msg' =>'操作成功', 'info' => []);
    }

    public function getMySubUserList($uid, $type){
        $info['one_sub_count'] = 0;
        $info['one_sub_count'] = 0;
        $list = array();
        $one_sub_list = DI()->notorm->users_agent->select("uid")->where("one_uid = ?", intval($uid))->fetchAll();
        $one_sub_uids = count($one_sub_list) > 0 ? array_column($one_sub_list,'uid',null) : [];
        $one_sub_user_list = count($one_sub_uids) > 0 ? DI()->notorm->users->select("id as uid, user_nicename")->where("id in(".implode(',', $one_sub_uids).")")->fetchAll() : [];

        $two_sub_user_list = array();
        if(in_array($type, [0,2])){
            $two_sub_list = count($one_sub_uids) > 0 ? DI()->notorm->users_agent->select("uid")->where("one_uid in(".implode(',', $one_sub_uids).")")->fetchAll() : [];
            $two_sub_uids = count($two_sub_list) > 0 ? array_column($two_sub_list,'uid',null) : [];
            $two_sub_user_list = count($two_sub_uids) > 0 ? DI()->notorm->users->select("id as uid, user_nicename")->where("id in(".implode(',', $two_sub_uids).")")->fetchAll() : [];
        }

        switch ($type){
            case 0:
                foreach ($one_sub_user_list as $key=>$val){
                    $temp['uid'] = intval($val['uid']);
                    $temp['user_nicename'] = $val['user_nicename'];
                    $temp['type'] = 1;
                    array_push($list, $temp);
                }
                foreach ($two_sub_user_list as $key=>$val){
                    $temp['uid'] = intval($val['uid']);
                    $temp['user_nicename'] = $val['user_nicename'];
                    $temp['type'] = 2;
                    array_push($list, $temp);
                }
                $info['one_sub_count'] = count($one_sub_user_list);
                $info['two_sub_user_list'] = count($two_sub_user_list);
                break;
            case 1:
                foreach ($one_sub_user_list as $key=>$val){
                    $temp['uid'] = intval($val['uid']);
                    $temp['user_nicename'] = $val['user_nicename'];
                    $temp['type'] = 1;
                    array_push($list, $temp);
                }
                $info['one_sub_count'] = count($one_sub_user_list);
                break;
            case 2:
                foreach ($two_sub_user_list as $key=>$val){
                    $temp['uid'] = intval($val['uid']);
                    $temp['user_nicename'] = $val['user_nicename'];
                    $temp['type'] = 2;
                    array_push($list, $temp);
                }
                $info['two_sub_user_list'] = count($two_sub_user_list);
                break;
        }
        $info['list'] = $list;
        return array('code' => 0,'msg' =>'操作成功', 'info' => [$info]);
    }

    public function nftConsumption($uid,$amount, $type){
        try {
            beginTransaction();
            $code = codemsg();
            $tenant_id = getTenantId();
            $userModel = new Model_User();
            $coinrecordModel = new Model_Coinrecord();
            $userInfo = $userModel->getUserInfoWithIdAndTid($uid, $tenant_id);
            if(!$userInfo){
                return array('code' =>1003, 'msg' => '会员不存在', 'info' => []);
            }
            if($type==1){
                if($userInfo['nftwithdrawable_coin'] < $amount){
                    return array('code' => 1004, 'msg' => '剩余冻结余额小于操作金额', 'info' => []);
                }
                DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('coin' => new NotORM_Literal("coin + {$amount}"),'nftwithdrawable_coin' => new NotORM_Literal("nftwithdrawable_coin-{$amount}")));
                //写入消费详情
                $insert=array(
                    "type"=>'income',
                    "action"=>'nft_add',
                    "uid"=>$uid,
                    'user_login'=>$userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    "giftid"=>0,
                    "pre_balance" => floatval($userInfo['coin']),
                    "totalcoin"=>$amount,
                    "after_balance" => floatval(bcadd($userInfo['coin'], $amount,4)),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $coinrecordModel->addCoinrecord($insert);
            }

            if($type==2){
                if ($userInfo['coin']< $amount){
                    return array('code' => 2006, 'msg' => $code['2006'], 'info' => array());
                }
                DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('coin' => new NotORM_Literal("coin - {$amount}"),'nftwithdrawable_coin' => new NotORM_Literal("nftwithdrawable_coin + {$amount}")));
                //写入消费详情
                $insert=array(
                    "type"=>'expend',
                    "action"=>'nft_reduce',
                    "uid"=>$uid,
                    'user_login' => $userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    "giftid"=>0,
                    "pre_balance" => floatval($userInfo['coin']),
                    "totalcoin"=>$amount,
                    "after_balance" => floatval(bcadd($userInfo['coin'], -abs($amount),4)),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $coinrecordModel->addCoinrecord($insert);
            }
            //第二种：审核通过 ： 冻结金额减去
            if($type==3){
                if($userInfo['nftwithdrawable_coin'] < $amount){
                    return array('code' => 1004, 'msg' => '剩余冻结余额小于操作金额', 'info' => []);
                }
                DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('nftwithdrawable_coin' => new NotORM_Literal("nftwithdrawable_coin - {$amount}")));
            }
            //第四种：只加余额
            if($type==4){
                DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('coin' => new NotORM_Literal("coin + {$amount}")));
                //写入消费详情
                $insert=array(
                    "type"=>'income',
                    "action"=>'nft_add',
                    "uid"=>$uid,
                    'user_login' => $userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    "giftid"=>0,
                    "pre_balance" => floatval($userInfo['coin']),
                    "totalcoin"=>$amount,
                    "after_balance" => floatval(bcadd($userInfo['coin'], $amount,4)),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $coinrecordModel->addCoinrecord($insert);
            }
            //第五种：只减去余额
            if($type==5){
                DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('coin' => new NotORM_Literal("coin - {$amount}")));
                //写入消费详情
                $insert=array(
                    "type"=>'expend',
                    "action"=>'nft_reduce',
                    "uid"=>$uid,
                    'user_login' => $userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    "giftid"=>0,
                    "pre_balance" => floatval($userInfo['coin']),
                    "totalcoin"=>$amount,
                    "after_balance" => floatval(bcadd($userInfo['coin'], -abs($amount),4)),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $coinrecordModel->addCoinrecord($insert);
            }
            delUserInfoCache($uid);
            $userInfo = getUserInfo($uid);
            commitTransaction();
            return array('code' => 0, 'msg' => '操作成功', 'info' => ['nftwithdrawable_coin'=>$userInfo['nftwithdrawable_coin']]);
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("nft消费接口 异常:".$ex->getMessage());
            return array('code' => 1001, 'msg' => '操作失败', 'info' => []);
        }
    }
    public function lotteryConsumption($uid,$amount, $type){

        // 冻结金额判断处理
        $freeze_user_balance_cachekey = 'freeze_user_balance_lotCons_'.$type.$uid;

        try {
            beginTransaction();
            $code = codemsg();
            $tenant_id = getTenantId();
            $userModel = new Model_User();
            $coinrecordModel = new Model_Coinrecord();
            $userInfo = $userModel->getUserInfoWithIdAndTid($uid, $tenant_id);
            if(!$userInfo){
                return array('code' =>1003, 'msg' => '会员不存在', 'info' => []);
            }

            $coin =  $userInfo['coin']*1000;
            $time_stamp = time() * 1000;
            $tenantconfig = getTenantInfo($tenant_id);
            $cpinfo = [];
            //进入cp页面，转入金额到java端
            if($type==1){
                // 冻结金额判断处理
                if(($userInfo['coin'] - floatval(CustRedis::getInstance()->get($freeze_user_balance_cachekey))) <= 0){
                    return array('code' =>2006, 'msg' => codemsg(2006), 'info' => []);
                }
                CustRedis::getInstance()->set($freeze_user_balance_cachekey, $userInfo['coin'], 60);

                //如果amount 为0， 不转入金额到java，只拿路径
                if($amount == 0){
                    $coin = 0;
                }
                $lotteryparms = array(
                    'tid' => $tenantconfig['lottery_id'],
                    'chan_code' => 'qipaiapp',
                    'time_stamp' => $time_stamp,
                    'key' => md5($time_stamp.'ZZDRSJINYLDMHXFH'),
                    'user_s' => $userInfo['user_login'],
                    'amount'=>$coin,
                    'orderId' => $time_stamp,
                    'nick_name' => $userInfo['user_nicename'],
                );

                $url = $tenantconfig['lottery_url'].'/domain_tenant/domain_addr/get3';
                $lotteryinfo = curPost($url,$lotteryparms);
                $lotteryinfo = json_decode($lotteryinfo,true);
                if(isset($lotteryinfo['result']) && $lotteryinfo['result'] == 'Y' ){
                    $cpinfo['url'] = $lotteryinfo['url'];
                    $cpinfo['token'] = $lotteryinfo['token'];
                    if($coin >0){
                        //写入消费详情
                        $insert=array(
                            "type"=>'expend',
                            "action"=>'lottery_reduce',
                            "uid"=>$uid,
                            'user_login' => $userInfo['user_login'],
                            'user_type'=>$userInfo['user_type'],
                            "giftid"=>0,
                            "pre_balance" => floatval($userInfo['coin']),
                            "totalcoin"=> $userInfo['coin'],
                            "after_balance" => 0,
                            "addtime"=>time(),
                            'tenant_id' =>getTenantId(),
                        );
                        $coinrecordModel->addCoinrecord($insert);
                        DI()->notorm->users
                            ->where('id = ?', $uid)
                            ->update(array('coin' => 0));

                        CustRedis::getInstance()->del($freeze_user_balance_cachekey);
                    }
                }else{
                    CustRedis::getInstance()->del($freeze_user_balance_cachekey);
                    return array('code' => 1004, 'msg' => '余额转入java失败', 'info' => []);
                }

            }
            //回收余额
            if($type==2){
                   $lotteryparms = array(
                       'tid' => $tenantconfig['lottery_id'],
                       'time_stamp' => $time_stamp,
                       'key' => md5($time_stamp.'ZZDRSJINYLDMHXFH'),
                       'cust_name' => $userInfo['user_login'],
                       'amount'=>0,
                       'orderId' => $time_stamp,
                       'nick_name' => $userInfo['user_nicename'],
                   );

                $url = $tenantconfig['lottery_url'].'/cust/transferOut';
                $lotteryinfo = curPost($url,$lotteryparms);
                $lotteryinfo = json_decode($lotteryinfo,true);
                if(isset($lotteryinfo['result']) && $lotteryinfo['result'] == 'Y'){
                    $amount  =$lotteryinfo['amount']/1000;
                    //写入消费详情
                    $insert=array(
                        "type"=>'income',
                        "action"=>'lottery_add',
                        "uid"=>$uid,
                        'user_login' => $userInfo['user_login'],
                        'user_type'=>$userInfo['user_type'],
                        "giftid"=>0,
                        "pre_balance" => floatval($userInfo['coin']),
                        "totalcoin"=>$amount,
                        "after_balance" => floatval(bcadd($userInfo['coin'], $amount,4)),
                        "addtime"=>time(),
                        'tenant_id' =>getTenantId(),
                    );
                    $coinrecordModel->addCoinrecord($insert);
                    DI()->notorm->users
                        ->where('id = ?', $uid)
                        ->update(array('coin' => new NotORM_Literal("coin + {$amount}")));
                }else{
                    return array('code' => $lotteryinfo['exCode'], 'msg' => $lotteryinfo['exMessage'], 'info' => []);
                }

            }

            commitTransaction();

            delUserInfoCache($uid);
            CustRedis::getInstance()->del($freeze_user_balance_cachekey);
            return array('code' => 0, 'msg' => '操作成功', 'info' => $cpinfo);
        }catch (Exception $ex){
            rollbackTransaction();
            CustRedis::getInstance()->del($freeze_user_balance_cachekey);
            DI()->logger->error("cp消费接口 异常:".$ex->getMessage());
            return array('code' => 1001, 'msg' => '操作失败', 'info' => []);
        }
    }


    public function shopConsumption($uid,$amount, $type, $shoppingVoucherId){
        try {
            beginTransaction();
            $code = codemsg();
            $userModel = new Model_User();
            $coinrecordModel = new Model_Coinrecord();
            $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
            if(!$userInfo){
                return array('code' =>1003, 'msg' => '会员不存在', 'info' => []);
            }
            $tenant_id = $userInfo['tenant_id'];
            if($type==1){
                logapi([' test info '=>$amount],'【测试退款】');  // 接口日志记录
                DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('coin' => new NotORM_Literal("coin + {$amount}")));
                //写入消费详情
                $insert=array(
                    "type"=>'income',
                    "action"=>'shop_add',
                    "uid"=>$uid,
                    'user_login' => $userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    "giftid"=>0,
                    "pre_balance" => floatval($userInfo['coin']),
                    "totalcoin"=>$amount,
                    "after_balance" => floatval(bcadd($userInfo['coin'], $amount,4)),
                    "addtime"=>time(),
                    'tenant_id' => intval($tenant_id),
                );
                $coinrecordModel->addCoinrecord($insert);
            }

            if($type==2){
                if ($userInfo['coin']< $amount){
                    return array('code' => 2006, 'msg' => $code['2006'], 'info' => array());
                }
                DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('coin' => new NotORM_Literal("coin - {$amount}")));
                $remark = '';
                if($shoppingVoucherId){
                    Model_ShoppingVoucher::getInstance()->updateStatusToUsedWithIds($tenant_id, $uid, $shoppingVoucherId);
                    $remark = '购物券id：'.$shoppingVoucherId;
                }
                //写入消费详情
                $insert=array(
                    "type"=>'expend',
                    "action"=>'shop_reduce',
                    "uid"=>$uid,
                    'user_type'=>$userInfo['user_type'],
                    "giftid"=>0,
                    "pre_balance" => floatval($userInfo['coin']),
                    "totalcoin"=>$amount,
                    "after_balance" => floatval(bcadd($userInfo['coin'], -abs($amount),4)),
                    "addtime"=>time(),
                    'tenant_id' => intval($tenant_id),
                    'remark' => trim($remark),
                );
                $coinrecordModel->addCoinrecord($insert);
            }

            delUserInfoCache($uid);
            commitTransaction();
            return array('code' => 0, 'msg' => '操作成功', 'info' => []);
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("shop消费接口 异常:".$ex->getMessage());
            return array('code' => 1001, 'msg' => '操作失败', 'info' => []);
        }
    }

    public function shopuserConsumption($uid, $amount, $type, $shoptoken, $ids, $shop_order_id,$cg_order_id, $shop_order_no, $cg_order_no){

        logapi([
            'uid'=>$uid,
            'amount'=>$amount,
            'type'=>$type,
            'shoptoken'=>$shoptoken,
            'ids'=>$ids,
            'shop_order_id'=>$shop_order_id,
            'cg_order_id'=>$cg_order_id,
            'shop_order_no'=>$shop_order_no,
            'cg_order_no'=>$cg_order_no,
        ], '【shopuserConsumption】');

        try {
            beginTransaction();
            $code = codemsg();
            $tenant_id = getTenantId();
            $userInfo = Model_User::getInstance()->getUserInfoWithIdAndTid($uid);
            if(!$userInfo){
                return array('code' =>1003, 'msg' => '会员不存在', 'info' => []);
            }
            $tenant_id = $userInfo['tenant_id'];
            $tenantconfig = getTenantInfo($tenant_id);

            if($type==1){
                // 查询判断是否已经存在数据
                $existShopManagerInfo = Model_ShopManager::getInstance()->getInfo($tenant_id, $shop_order_id);
                if($existShopManagerInfo){
                    logapi([
                        'uid'=>$uid,
                        'amount'=>$amount,
                        'type'=>$type,
                        'shoptoken'=>$shoptoken,
                        'ids'=>$ids,
                        'shop_order_id'=>$shop_order_id,
                        'cg_order_id'=>$cg_order_id,
                        'shop_order_no'=>$shop_order_no,
                        'cg_order_no'=>$cg_order_no,
                    ], '【重复确认收货】'.$shop_order_id);
                    return array('code' => 0, 'msg' => '操作成功', 'info' => ['重复确认收货']);
                }
                $shopOrderPurchaseInfo = Model_ShopOrderPurchase::getInstance()->getInfo($tenant_id, $shop_order_id);
                $insert=array(
                    "uid"=>$uid,
                    'user_login' => $userInfo['user_login'],
                    'user_type' => intval($userInfo['user_type']),
                    'shop_order_id' => intval($shop_order_id),
                    'cg_order_id' => $shopOrderPurchaseInfo ? intval($shopOrderPurchaseInfo['cg_order_id']) : 0,
                    "amount"=>$amount,
                    "goods_purchase_price" => $shopOrderPurchaseInfo ? floatval($shopOrderPurchaseInfo['goods_purchase_price']) : 0.00,
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                    'game_tenant_id'=>$tenantconfig['game_tenant_id'],
                    'cg_order_no' => $cg_order_no,
                    'shop_order_no' => $shop_order_no,
                );
                DI()->notorm->shop_manager->insert($insert);
                /*DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('coin' => new NotORM_Literal("coin + {$amount}")));
                //写入消费详情
                $insert=array(
                    "type"=>'income',
                    "action"=>'shopuser_add',
                    "uid"=>$uid,
                    'user_login' => $userInfo['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    "giftid"=>0,
                    "pre_balance" => floatval($userInfo['coin']),
                    "totalcoin"=>$amount,
                    "after_balance" => floatval(bcadd($userInfo['coin'], $amount,4)),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $coinrecordModel->addCoinrecord($insert);*/
            }
            if($type==2){
                if ($userInfo['coin']< $amount){
                    return array('code' => 2006, 'msg' => $code['2006'], 'info' => array());
                }
                if(empty($shoptoken) || empty($ids)){
                    return array('code' => 1002, 'msg' => '缺少代付token获取 订单id', 'info' => []);
                }
                if(count(explode(',', $ids)) > 50){
                    return array('code' => 2129, 'msg' => $code['2129'], 'info' => []);
                }

                $url = $tenantconfig['daifu_url'].'/api.php?s=VideoOrder/OrderPay&application=app';
                $lotteryparms = array(
                    'ids' => $ids,
                    'token' => $shoptoken,
                );
                $http_post_res = http_post($url,$lotteryparms);

                logapi([
                    'url'=>$url,
                    'lotteryparms'=>$lotteryparms,
                    'http_post_res'=>$http_post_res,
                ], '【采购支付 请求代发 日志】');

                if(isset($http_post_res['code']) && $http_post_res['code'] == 0 ){
                    DI()->notorm->users
                        ->where('id = ?', $uid)
                        ->update(array('coin' => new NotORM_Literal("coin - {$amount}")));

                    // 采购订单号
                    $shop_order_no = '';
                    $shop_order_id_cg_order_info = [];
                    if(isset($http_post_res['data']) && isset($http_post_res['data']['shop_order_id_cg_order_info'])){
                        $shop_order_id_cg_order_info = $http_post_res['data']['shop_order_id_cg_order_info'];
                        foreach ($http_post_res['data']['shop_order_id_cg_order_info'] as $shop_id=>$cg_order_info){
                            $shop_order_no .= $cg_order_info['shop_order_no'].',';
                        }
                    }
                    $shop_order_no = trim($shop_order_no,',');
                    $cg_order_id = trim($cg_order_id,',');
                    //写入消费详情
                    $insert=array(
                        "type"=>'expend',
                        "action"=>'shopuser_reduce',
                        "uid"=>$uid,
                        'user_login' => $userInfo['user_login'],
                        'user_type'=>$userInfo['user_type'],
                        "giftid"=>0,
                        "pre_balance" => floatval($userInfo['coin']),
                        "totalcoin"=>$amount,
                        "after_balance" => floatval(bcadd($userInfo['coin'], -abs($amount),4)),
                        "addtime"=>time(),
                        'tenant_id' =>getTenantId(),
                        'order_id'=>$cg_order_id,
                        'shop_order_no'=>$shop_order_no,
                    );
                    Model_Coinrecord::getInstance()->addCoinrecord($insert);

                    // 生成采购订单金额记录
                    if(isset($http_post_res['data']) && isset($http_post_res['data']['order_purchase_price'])){
                        foreach ($http_post_res['data']['order_purchase_price'] as $shop_id=>$goods_purchase_price){
                            $cg_order_id = isset($shop_order_id_cg_order_info[$shop_id]) ? $shop_order_id_cg_order_info[$shop_id]['id'] : 0;
                            $shop_order_no = isset($shop_order_id_cg_order_info[$shop_id]) ? $shop_order_id_cg_order_info[$shop_id]['shop_order_no'] : 0;
                            $cg_order_no = isset($shop_order_id_cg_order_info[$shop_id]) ? $shop_order_id_cg_order_info[$shop_id]['order_no'] : 0;
                            $result = Model_ShopOrderPurchase::getInstance()->add($tenant_id, $shop_id, $goods_purchase_price, $cg_order_id, $shop_order_no, $cg_order_no);
                        }
                    }
                }else{
                    logapi([
                        'url'=>$url,
                        'lotteryparms'=>$lotteryparms,
                        'http_post_res'=>$http_post_res,
                    ], '【采购支付 请求代发 失败】');
                    return array('code' => $http_post_res['code'], 'msg' => $http_post_res['msg'], 'info' => []);
                }
            }
            delUserInfoCache($uid);
            commitTransaction();
            return array('code' => 0, 'msg' => '操作成功', 'info' => []);
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("店铺会员消费接口 异常:".$ex->getMessage());
            logapi([
                'uid'=>$uid,
                'amount'=>$amount,
                'type'=>$type,
                'shoptoken'=>$shoptoken,
                'ids'=>$ids,
                'shop_order_id'=>$shoptoken,
                'cg_order_id'=>$cg_order_id,
                'er_msg'=>$ex->getMessage()
            ], '【店铺会员消费接口 异常】');
            return array('code' => 1001, 'msg' => '操作失败', 'info' => []);
        }
    }


    //商城缴纳保证金
    public function shopuserBondpay($uid,$amount, $type,$shoptoken){
        try {
            beginTransaction();
            $code = codemsg();
            $tenant_id = getTenantId();
            $userModel = new Model_User();
            $coinrecordModel = new Model_Coinrecord();
            $userInfo = $userModel->getUserInfoWithIdAndTid($uid, $tenant_id);
            if(!$userInfo){
                return array('code' =>1003, 'msg' => '会员不存在', 'info' => []);
            }
            $tenantconfig = getTenantInfo($tenant_id);

            if($type==1){
                if(empty($shoptoken) ){
                    return array('code' => 1002, 'msg' => '缺少token获取 ', 'info' => []);
                }
                if ($userInfo['coin']< $amount){
                    return array('code' => 2006, 'msg' => $code['2006'], 'info' => array());
                }
                $url = trim(trim($tenantconfig['shop_url'], '/')).'/api.php?s=shop/BondPay&application=app';
                $bondparms = array(
                    'token' => $shoptoken,
                );
                $http_post_res = http_post($url,$bondparms);
                if(isset($http_post_res['code']) && $http_post_res['code'] == 0 ){
                    DI()->notorm->users
                        ->where('id = ?', $uid)
                        ->update(array('coin' => new NotORM_Literal("coin - {$amount}")));
                    //写入消费详情
                    $insert=array(
                        "type"=>'expend',
                        "action"=>'shop_bondpay',
                        "uid"=>$uid,
                        'user_type'=>$userInfo['user_type'],
                        'user_login' => $userInfo['user_login'],
                        "giftid"=>0,
                        "pre_balance" => floatval($userInfo['coin']),
                        "totalcoin"=>$amount,
                        "after_balance" => floatval(bcadd($userInfo['coin'], -abs($amount),4)),
                        "addtime"=>time(),
                        'tenant_id' =>getTenantId(),
                    );
                    $coinrecordModel->addCoinrecord($insert);
                }else{
                    return array('code' => $http_post_res['code'], 'msg' => $http_post_res['msg'], 'info' => []);
                }
            }
            delUserInfoCache($uid);
            commitTransaction();
            return array('code' => 0, 'msg' => '操作成功', 'info' => []);
        }catch (Exception $ex){
            rollbackTransaction();
            logapi(['uid'=>$uid, 'amount'=>$amount, 'type'=>$type, '$shoptoken'=>$shoptoken, 'er_msg'=>$ex->getMessage()], '【缴纳消费接口 异常】');
            return array('code' => 1001, 'msg' => '操作失败', 'info' => []);
        }
    }
    public function bindUser($uid,$user_login,$user_pass,$source,$tenantId,$zone,$agent_code){
        $user_pass=setPass($user_pass);
        $userInfo = getUserInfo($uid);
        if ($userInfo['user_type']!= 4){
            return 2123;
        }
        $gameTenantId=getGameTenantId();
        $configpri=getConfigPri();
        $data=array(
            'user_login' => $user_login,
            'mobile' =>$user_login,
            'user_nicename' =>empty($nicename)?'user'.substr($user_login,-4):$nicename,
            'user_pass' =>$user_pass,
            'last_login_ip' => $_SERVER['REMOTE_ADDR'],
            'create_time' => date("Y-m-d H:i:s"),
            'user_status' => 1,
            "user_type"=>2,//会员
            "source"=>$source,
            "tenant_id"=>$tenantId,
            'game_tenant_id'=>$gameTenantId,
            'zone'=>$zone,
            'pids'=>0,
            'pid'=> 0,

        );
        $isexist=DI()->notorm->users
            ->select('id')
            ->where('(game_tenant_id=? or tenant_id=? ) and user_login=? and user_type  in (2,5,6,8) ',$gameTenantId,$tenantId,$user_login)
            ->fetchOne();
        if($isexist){
            return 1006;
        }

        if($agent_code){

            $agent_uid = DI()->notorm->users
            ->select('id')->where("agent_code=?",$agent_code)->fetchOne();
            if(empty($agent_uid['id'])){
                return 1007;
            }

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
            // $oneinfo=DI()->notorm->users_agent_code->where('code=? and tenant_id=? ',$agent_code,$tenantId)->fetchOne();

            // if($oneinfo) {
            //     $oneinfo = DI()->notorm->users_agent_code->select("uid")->where('code=?', $agent_code)->fetchOne();
            //     if (!$oneinfo) {
            //         return 1002;
            //     }
            // }
            // $agentinfo = getAgentInfo($oneinfo['uid']);
            // $agentUser = getUserInfo($oneinfo['uid']);
            // if($agentUser['user_type'] == 4){
            //     return 2122;
            // }
            // if (!$agentinfo) {
            //     $agentinfo = array('uid' => $oneinfo['uid'], 'one_uid' => 0, 'two_uid' => 0, 'three_uid' => 0, 'four_uid' => 0,);
            // }
            // $dataagent = array(
            //     'uid' => $uid,
            //     'user_login' => $user_login,
            //     'one_uid' => $agentinfo['uid'],
            //     'two_uid' => $agentinfo['one_uid'],
            //     'three_uid' => $agentinfo['two_uid'],
            //     'four_uid' => $agentinfo['three_uid'],
            //     'five_uid' => $agentinfo['four_uid'],
            //     'addtime' => time(),
            //     'tenant_id' => $tenantId,
            // );
            // DI()->notorm->users_agent->insert($dataagent);
            // $puser =DI()->notorm->users
            //     ->select('id,pids,user_type')
            //     ->where('id=? ',$oneinfo['uid'])
            //     ->fetchOne();
            // $data['pids'] =$puser['pids'].','.$puser['id'];
            // $data['pid'] = $puser['id'];
            // $lower_level_count = DI()->notorm->users->where("pid =  {$oneinfo['uid']} ")->count();
            // $lower_level_count = $lower_level_count +1;

            // $activity = DI()->notorm ->activity_config->where(" per_num = '{$lower_level_count}'and  type =3 and tenant_id = {$tenantId} ")->fetchOne();
            // if ($activity){  // 推荐奖励
            //     $agentUserCoin = DI()->notorm->users->where("id  = '{$oneinfo['uid']}'")
            //         ->select('coin')
            //         ->fetchOne();
            //     $awardLog = [
            //         'uid' => $oneinfo['uid'],
            //         'type' => 3 ,// 邀请好友注册奖励
            //         'amount' => $activity['reward'],
            //         'original_balance' =>  $agentUserCoin['coin'],
            //         'back_balance' => bcadd($agentUserCoin['coin'],$activity['reward'],2),
            //         'addtime' => time(),
            //         'tuid' =>  $uid,
            //         'data_type' => 1,
            //         'award_name'=>'邀请好友注册'
            //     ];
            //     $res = DI()->notorm->award_log->insert($awardLog);
            //     $coinrecordData = [
            //         'type' => 'income',
            //         'user_type' => $data['user_type'],
            //         'giftid' => $res,
            //         'uid'=> $oneinfo['uid'],
            //         'addtime' => time(),
            //         'tenant_id' => $data['tenant_id'],
            //         'action' => 'invite_award',
            //         'pre_balance'=> $agentUser['coin'],
            //         'after_balance'=> bcadd($activity['reward'],$agentUser['coin'],2),
            //         'totalcoin' => $activity['reward'],//金额
            //         "giftcount" => 1,
            //         'is_withdrawable' => 1,
            //     ];
            //     DI()->notorm->users->where("id  = '{$oneinfo['uid']}'")->update(array( "coin"=>new NotORM_Literal("coin + {$activity['reward']}") ));

            //     delUserInfoCache($oneinfo['uid']);
            //     DI()->notorm ->users_coinrecord->insert($coinrecordData);  //  账变记录
            // }
            // yhTaskFinish($oneinfo['uid'],getTaskConfig('task_5'));
        }else{
            $dataagent=array(
                'uid'=>$uid,
                'user_login'=>$user_login,
                'one_uid'=>0,
                'two_uid'=>0,
                'three_uid'=>0,
                'four_uid'=>0,
                'five_uid'=>0,
                'addtime'=>time(),
                'tenant_id'=>$tenantId,
            );
            DI()->notorm->users_agent->insert($dataagent);
        }
        yhTaskFinish($uid,getTaskConfig('task_1'));
        $rs=DI()->notorm->users->where("id  = '{$uid}'")->update($data);
        delUserInfoCache($uid);
        return  1;

    }

    public  function sign_in($uid){
        $todayTime = strtotime(date('Y-m-d',time()));
        $is_sign = DI()->notorm->users_sign_log->where("addtime > {$todayTime} and uid = {$uid}")->fetchOne();
        if ($is_sign){
            return  ['code'=> 2118, 'msg' => codemsg(2118)];
        }
        $userInfo  = getUserInfo($uid);
        if ($userInfo['user_type'] == 4){
            return  ['code'=> 2219, 'msg' => codemsg(2219)];
        }
        $yTime = $todayTime - 86400;
        $yes_is_sign = DI()->notorm->users_sign_log->where("addtime < {$todayTime} and uid = {$uid}  and  addtime > {$yTime} ")->fetchOne();

        $signLog = [
            'uid'=> $uid,
            'times' => 1,
            'addtime' => time(),
        ] ;
        if ($yes_is_sign){
            if ($yes_is_sign['times'] < 7){
                $signLog['times']  =  $yes_is_sign['times']+ 1;
            }
        }
        DI()->notorm->users_sign_log->insert($signLog);


        $config = getConfigPub();
        $tenantId = getTenantId();
        $singSet  = DI()->notorm->sign_set->where("tenant_id = {$tenantId}")->fetchOne();
        switch ($signLog['times']){
            case  1 :
                $amount =  $singSet['first_times'];
                break;
            case  2 :
                $amount =  $singSet['second_times'];
                break;
            case  3 :
                $amount =  $singSet['third_times'];
                break;
            case  4:
                $amount =  $singSet['fourth_times'];
                break;
            case  5:
                $amount =  $singSet['fifth_times'];
                break;
            case  6:
                $amount =  $singSet['sixth_times'];
                break;
            case  7:
                $amount =  $singSet['seventh_times'];
                break;
        }

        if ($config['sing_award_type'] == 1){
            $awardLog = [
                'uid' => $uid,
                'type' => 2 ,// 签到
                'amount' => $amount,
                'original_balance' =>  $userInfo['turntable_times'],
                'back_balance' => $userInfo['turntable_times'] +$amount,
                'data_type'=> 3,
                'addtime' => time(),
                'award_name'=>'签到'

            ];
            DI()->notorm->users->where("id  = '{$uid}'")->update(array( "turntable_times"=>new NotORM_Literal("turntable_times + {$amount}") ));
            $res = DI()->notorm->award_log->insert($awardLog);
        }else{
            $awardLog = [
                'uid' => $uid,
                'type' => 2 ,// 签到
                'amount' => $amount,
                'original_balance' =>  $userInfo['coin'],
                'back_balance' => $userInfo['coin'] +$amount,
                'data_type'=> 1,
                'addtime' => time(),
                'award_name'=>'签到'

            ];
            $res = DI()->notorm->award_log->insert($awardLog);
            DI()->notorm->users->where("id  = '{$uid}'")->update(array( "coin"=>new NotORM_Literal("coin + {$amount}") ));
            $coinrecordData = [
                'type' => 'income',
                'user_type' => $userInfo['user_type'],
                'giftid' => $res,
                'uid'=>$uid,
                'addtime' => time(),
                'tenant_id' => $userInfo['tenant_id'],
                'action' => 'sign',
                'pre_balance'=> $userInfo['coin'],
                'after_balance'=> bcadd($amount,$userInfo['coin'],2),
                'totalcoin' => $amount,//金额
                "giftcount" => 1,
                'is_withdrawable' => 1,
            ];
            DI()->notorm ->users_coinrecord->insert($coinrecordData);  //  账变记录
        }

        delUserInfoCache($uid);
        return ['code'=> 0,'msg'=>'操作成功'];
    }

     public  function signLog($uid){
         $signLog = DI()->notorm->users_sign_log->where("  uid = {$uid}  ")->order('addtime desc ')->fetchOne();

         if ($signLog){
             $todayTime = strtotime(date('Y-m-d',time()));
             $yTime = $todayTime - 86400;
             if ($signLog['addtime']>= $todayTime  ){ // 今天 签到
                 $signLog['is_sign_type'] = 1;
             }elseif (  $signLog['addtime'] >  $yTime && $signLog['addtime']< $todayTime ){ // 今日未签到 昨日签到
                 $signLog['is_sign_type'] = 2;
             }else{ //  断签
                 return [];
             }

         }else{ // 断签直接返回空
             return  [];
         }
         return  $signLog;

     }

     public function signSet(){
         $tenantId = getTenantId();
         $singSet  = DI()->notorm->sign_set->where("tenant_id = {$tenantId}")->fetchOne();
         return $singSet;
     }


     public function accessLog($uid){
         $tenantId = getTenantId();
         $todayTime = strtotime(date('Y-M-d'));
         $accessLog = DI()->notorm->access_log->where("tenant_id = {$tenantId} and  addtime >= {$todayTime} and uid = {$uid}")->fetchOne();
         $nowIp  = $_SERVER['REMOTE_ADDR'];
         $data['addtime'] = time();
         if ($accessLog){
             $berforArrayIp  = explode(',',$accessLog['ip']);
             if (!in_array($nowIp,$berforArrayIp)){
                 $berforArrayIp[] = $nowIp;
                 $data['ip'] = implode(',',$berforArrayIp);
             }
             DI()->notorm->access_log->where("id = {$accessLog['id']}")->updata($data);
         }else{
             $data['tenant_id'] = $tenantId;
             $data['uid'] = $uid;
             $data['ip'] = $nowIp;
         }
         return ['code'=> 0];
     }

    /* 判断昵称是否重复 */
    public function checkUserLogin($name){
        $tenantId=getTenantId();
        $isexist=DI()->notorm->users
            ->select('id')
            ->where('user_login=? and tenant_id=?',$name,$tenantId)
            ->fetchOne();
        if($isexist){
            return ['code'=>2127 , 'msg' => codemsg(2127)];
        }else{
            return ['code'=> 0];
        }
    }
    public function goodsToshopowner(){
        $config = getConfigPub();
        //获取当前时间，七天前的时间戳
        $addtime = time() - 60*60*24*7;


        try {
            beginTransaction();
            $tenant_id = getTenantId();
            $shopmanager = DI()->notorm->shop_manager
                ->select("*")
                ->where("addtime <= {$addtime} and  status  = 0 ")
                ->fetchAll();
            if(empty($shopmanager)){
                return array('code' => 0, 'msg' => '没有要结算的发货单', 'info' => []);
            }
            foreach ($shopmanager as $key => $value) {
                $userInfo= getUserInfo($value['uid']);
                //更新数据
                DI()->notorm->shop_manager
                    ->where('id = ?', $value['id'])
                    ->update(array('status' =>1,'operatetime'=>time()) );
                DI()->notorm->users
                    ->where('id = ?', $value['uid'])
                    ->update(array('coin' => new NotORM_Literal("coin + {$value['amount']}")) );
                delUserInfoCache($value['uid']);
                //写入消费详情
                $insert=array(
                    "type"=>'income',
                    "action"=>'shopuser_add',
                    "uid"=> $value['uid'],
                    'user_login' => $value['user_login'],
                    'user_type'=>$userInfo['user_type'],
                    "giftid"=>0,
                    "pre_balance" => floatval($userInfo['coin']),
                    "totalcoin"=>$value['amount'],
                    "after_balance" => floatval(bcadd($userInfo['coin'], $value['amount'],4)),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                    'order_id' => $value['cg_order_no'],
                    'shop_order_no' => $value['shop_order_no'],
                );
                $coinrecordModel = new Model_Coinrecord();
                $coinrecordModel->addCoinrecord($insert);


                // 计算利润，切给上级代理返佣
                $trading_profit = bcsub($value['amount'], $value['goods_purchase_price'], 2);
                if($value['goods_purchase_price'] > 0 && $trading_profit > 0){
                    $result = Model_Common::getInstance()->AgentRebate($value['uid'], $trading_profit, 0, 'income', 1,'trading_profit_rebate',5, '', $value['shop_order_no'], $value['cg_order_no']);
                }
            }
            commitTransaction();
            return array('code' => 0, 'msg' => '收货账单结算成功', 'info' => []);
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("收货账单结算 异常:".$ex->getMessage());
            return array('code' => 1001, 'msg' => '收货账单结算 异常', 'info' => []);
        }
    }

    public function summaryAgent(){
        try{
            $time = date("Y-m-d",Strtotime ("-1 day"));
            $time = Strtotime($time);
            $data = DI()->notorm->users_invite->select("agent_id as uid,agent_id,count('id') as count")->where("addtime>=? and addtime<? and status=? ",$time,$time+86400,0)->group("agent_id")->fetchAll();
            if(!empty($data)){

                foreach($data as $v){
                   if($v['count'] >= 10){
                    DI()->notorm->users
                        ->where('id = ?', $v['agent_id'])
                        ->update(array('coin' => new NotORM_Literal("coin + 30")) );
                        $coin_record = array(
                            "type" => "income",
                            "action" => "reg_reward",
                            "uid" => $v['uid'],
                            "user_type"=>2,
                            "touid" => $v['uid'],
                            "giftid"=>1,
                            "giftcount" => 1,
                            "pre_balance" => 0,
                            "totalcoin"=>30,
                            "tenant_id" => getTenantId(),
                            "cd_ratio" => "1:1",
                            "is_withdrawable"=>1
                        );

                   }elseif($v['count'] >= 7 && $v['count']<10){
                        DI()->notorm->users
                            ->where('id = ?', $v['agent_id'])
                            ->update(array('coin' => new NotORM_Literal("coin + 15")) );
                            $coin_record = array(
                                "type" => "income",
                                "action" => "reg_reward",
                                "uid" => $v['uid'],
                                "user_type"=>2,
                                "touid" => $v['uid'],
                                "giftid"=>1,
                                "giftcount" => 1,
                                "pre_balance" => 0,
                                "totalcoin"=>15,
                                "tenant_id" => getTenantId(),
                                "cd_ratio" => "1:1",
                                "is_withdrawable"=>1
                            );
                   }elseif($v['count'] >= 3 && $v['count']<7){
                        DI()->notorm->users
                            ->where('id = ?', $v['agent_id'])
                            ->update(array('coin' => new NotORM_Literal("coin + 3")) );
                            $coin_record = array(
                                "type" => "income",
                                "action" => "reg_reward",
                                "uid" => $v['uid'],
                                "user_type"=>2,
                                "touid" => $v['uid'],
                                "giftid"=>1,
                                "giftcount" => 1,
                                "pre_balance" => 0,
                                "totalcoin"=>3,
                                "tenant_id" => getTenantId(),
                                "cd_ratio" => "1:1",
                                "is_withdrawable"=>1
                            );
                   }
                }
                DI()->notorm->users_coinrecord->insert($coin_record);
            }

            DI()->notorm->users_invite->where("addtime>=? and addtime<? and status=? ",$time,$time+86400,0)->update(array('status'=>1));
            
        }catch (Exception $ex){
            echo $ex->getMessage();
            DI()->logger->error("收货账单结算 异常:".$ex->getMessage());
        }
        
    }

    public function summaryDownload(){
        try{
            $time = date("Y-m-d",Strtotime ("-1 day"));
            $time = Strtotime($time);
            $data = DI()->notorm->users_download->select("uid,invite_num")->where("create_at>=? and create_at<? and status=? ",$time,$time+86400,0)->fetchAll();
            if(!empty($data)){
                foreach($data as $v){
                    if($v['invite_num'] < 3){
                        $coin_record = array(
                            "type" => "income",
                            "action" => "reg_reward",
                            "uid" => $v['uid'],
                            "user_type"=>2,
                            "touid" => $v['uid'],
                            "giftid"=>1,
                            "giftcount" => 1,
                            "pre_balance" => 0,
                            "totalcoin"=>1,
                            "tenant_id" => getTenantId(),
                            "cd_ratio" => "1:1",
                            "is_withdrawable"=>1
                        );

                        DI()->notorm->users
                            ->where('id = ?', $v['uid'])
                            ->update(array('coin' => new NotORM_Literal("coin + 1")) );
                    }
                   if($v['invite_num'] >= 10){
                        $coin_record = array(
                            "type" => "income",
                            "action" => "reg_reward",
                            "uid" => $v['uid'],
                            "user_type"=>2,
                            "touid" => $v['uid'],
                            "giftid"=>1,
                            "giftcount" => 1,
                            "pre_balance" => 0,
                            "totalcoin"=>30,
                            "tenant_id" => getTenantId(),
                            "cd_ratio" => "1:1",
                            "is_withdrawable"=>1
                        );
                        DI()->notorm->users
                            ->where('id = ?', $v['uid'])
                            ->update(array('coin' => new NotORM_Literal("coin + 30")) );
                   }elseif($v['invite_num'] >= 7 && $v['invite_num']<10){
                        $coin_record = array(
                            "type" => "income",
                            "action" => "reg_reward",
                            "uid" => $v['uid'],
                            "user_type"=>2,
                            "touid" => $v['uid'],
                            "giftid"=>1,
                            "giftcount" => 1,
                            "pre_balance" => 0,
                            "totalcoin"=>15,
                            "tenant_id" => getTenantId(),
                            "cd_ratio" => "1:1",
                            "is_withdrawable"=>1
                        );
                        DI()->notorm->users
                            ->where('id = ?', $v['uid'])
                            ->update(array('coin' => new NotORM_Literal("coin + 15")) );
                   }elseif($v['invite_num'] >= 3 && $v['invite_num']<7){

                        $coin_record = array(
                            "type" => "income",
                            "action" => "reg_reward",
                            "uid" => $v['uid'],
                            "user_type"=>2,
                            "touid" => $v['uid'],
                            "giftid"=>1,
                            "giftcount" => 1,
                            "pre_balance" => 0,
                            "totalcoin"=>3,
                            "tenant_id" => getTenantId(),
                            "cd_ratio" => "1:1",
                            "is_withdrawable"=>1
                        );

                        DI()->notorm->users
                            ->where('id = ?', $v['uid'])
                            ->update(array('coin' => new NotORM_Literal("coin + 3")) );
                        
                   }
                 DI()->notorm->users_coinrecord->insert( $coin_record);
                }
            }
           
            DI()->notorm->users_download->where("create_at>=? and create_at<? and status=? ",$time,$time+86400,0)->update(array('status'=>1));
            
        }catch (Exception $ex){
            echo $ex->getMessage();
            DI()->logger->error("收货账单结算 异常:".$ex->getMessage());
        }
    }

   public function addDownload($code){
        if(empty($code)){
            return false;
        }
        $invitor =DI()->notorm->users
                      ->select('id')
                      ->where(' agent_code=? ',$code)
                      ->fetchOne();

        if(empty($invitor)){
            return false;
        }
        $time = date("Y-m-d",Strtotime ("-1 day"));
        $time = Strtotime($time);
        //一天内只能有一条数据
        $data = DI()->notorm->users_download->select("uid,invite_num")->where("uid=? and create_at>=? and status=? ",$invitor['id'],$time+86400,0)->fetchAll();
        if(!empty($data)){
            DI()->notorm->users_download->where("create_at>=? and status=? ",$time+86400,0)->update(array('invite_num' => new NotORM_Literal("invite_num + 1")) );
        }else{
            $insert = array(
                "uid"=>$invitor['id'],
                "invite_num" => 1,
                "create_at" => time(),
                "status" => 0
            );
            DI()->notorm->users_download->insert($insert);
        }
        return true;
   }

   public function AddCoin($uid){
        $user = DI()->notorm->users->where("id  = '{$uid}'")->fetchOne();
        if(empty($user)){
            return false;
        }
        $record = DI()->notorm->users_coinrecord->where("uid=? and action='download_h5'",$uid)->fetchOne();
        if($record){
            return false;
        }
        $coin_record = array(
            "type" => "income",
            "action" => "download_h5",
            "uid" => $uid,
            "user_type"=>2,
            "touid" => $uid,
            "giftid"=>1,
            "giftcount" => 1,
            "pre_balance" => 0,
            "totalcoin"=>30,
            "tenant_id" => getTenantId(),
            "cd_ratio" => "1:1",
            "is_withdrawable"=>1
        );
        DI()->notorm->users_coinrecord->insert( $coin_record);
        DI()->notorm->users
            ->where('id = ?', $uid)
            ->update(array('coin' => new NotORM_Literal("coin + 1")) );
   }

   public function getInvitedList($uid,$p){
        if($p<1){
            $p=1;
        }
        $pnum=50;
        $start=($p-1)*$pnum;
        $uids =DI()->notorm->users_invite
                    ->select("invited_id")
                    ->where('agent_id=?',$uid)
                    ->limit($start,$pnum)
                    ->fetchAll();

        foreach($uids as $k=>$v){
            $userinfo=getUserInfo($v['invited_id']);
            if($userinfo){
                    $userinfo['avatar'] = get_protocal().'://' . $_SERVER['HTTP_HOST'].$userinfo['avatar'];
                    $count_long=DI()->notorm->video_long
                        ->where('uid=?',$v['invited_id'])
                        ->count();
                    $count_shot=DI()->notorm->video
                        ->where('uid=?',$v['invited_id'])
                        ->count();
                $userinfo['video_count']=$count_long+$count_shot;
                $uids[$k]=$userinfo;
            }
        }		
        $touids=array_values($uids);
        return $touids;
   }

   public function getAttentList($uid,$page){
        $p =$page;
        if($page<1){
            $p=1;
        }
        $pnum=50;
        $start=($p-1)*$pnum;
        $uids =DI()->notorm->users_attention
                    ->select("touid")
                    ->where('uid=?',$uid)
                    ->limit($start,$pnum)
                    ->fetchAll();
        foreach($uids as $k=>$v){
            $userinfo=getUserInfo($v['touid']);
            unset($userinfo['can_be_withdrawn']);
            unset($userinfo['is_set_payment_password']);
            unset($userinfo['seeking_slice_bonus_min']);
            unset($userinfo['seeking_slice_bonus_max']);
            unset($userinfo['is_set_payment_password']);
            if($userinfo){
                    $userinfo['avatar'] = get_protocal().'://' . $_SERVER['HTTP_HOST'].$userinfo['avatar'];
                    $count_long=DI()->notorm->video_long
                        ->where('uid=?',$v['touid'])
                        ->count();
                    $count_shot=DI()->notorm->video
                        ->where('uid=?',$v['touid'])
                        ->count();
                $userinfo['video_count']=$count_long+$count_shot;
                $uids[$k]=$userinfo;
            }
        }		
        $touids=array_values($uids);
        return $touids;
   }

}

