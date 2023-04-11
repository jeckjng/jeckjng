<?php

use api\Common\CustRedis;

class Model_Live extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }


    /* 创建房间 */
	public function createRoom($uid,$data) {
        $data['title'] = cust_unicode($data['title']);
        $isuser=DI()->notorm->users
            ->select("isforbidlive")
            ->where('id=?',$uid)
            ->fetchOne();
        if(isset($isuser) && $isuser['isforbidlive'] == 1 ){
            return array('code' => 2062, 'msg' => codemsg('2062'), 'info' => array());
        }

		$isexist=DI()->notorm->users_live
					->select("uid")
					->where('uid=?',$uid)
					->fetchOne();
		if($isexist){
            /* 判断存在的记录是否为直播状态 */
            if($isexist['isvideo']==0 && $isexist['islive']==1){
                /* 若存在未关闭的直播 关闭直播 */
                $this->stopRoom($uid,$isexist['stream']);

                /* 加入 */
                $rs=DI()->notorm->users_live->insert($data);
            }else{
                /* 更新 */
                $rs=DI()->notorm->users_live->where('uid = ?', $uid)->update($data);
            }
		}else{
			/* 加入 */
			$rs=DI()->notorm->users_live->insert($data);
		}
		if(!$rs){
			return $rs;
		}
        setUserLiveListCache($uid, 'create'); // 设置直播列表缓存
       
        //判断是否还有未完成的直播日志，有的话，直接清除
        $isliveinglog=DI()->notorm->liveing_log
            ->select("uid")
            ->where('uid=?  and  status=0 and stream=? ',$uid,$data['stream'])
            ->fetchOne();

		if($isliveinglog){
            DI()->notorm->liveing_log->where('uid=? and status=0 and stream=? ',intval($uid),$data['stream'])->delete();
        }

        $livelog['uid'] = $uid;
        $livelog['starttime'] = $data['starttime'];
        $livelog['stream'] = $data['stream'];
        $livelog['status'] = 0;
        DI()->notorm->liveing_log->insert($livelog);

        // 清除直播间管理员
        DI()->notorm->users_livemanager->where('liveuid=?',intval($uid))->delete();
		return 1;
	}
	
	/* 主播粉丝 */
    public function getFansIds($touid) {
        
        $list=array();
		$fansids=DI()->notorm->users_attention
					->select("uid")
					->where('touid=?',$touid)
					->fetchAll();
                    
        if($fansids){
            $uids=array_column($fansids,'uid');
            
            $pushids=DI()->notorm->users_pushid
					->select("pushid")
					->where('uid',$uids)
					->fetchAll();
            $list=array_column($pushids,'pushid');
            $list=array_filter($list);
        }
        return $list;
    }	
	
	/* 修改直播状态 */
	public function changeLive($uid,$stream,$status){

		if($status==1){
            $info=DI()->notorm->users_live
                    ->select("*")
					->where('uid=? and stream=?',$uid,$stream)
                    ->fetchOne();
            if($info){
                DI()->notorm->users_live
					->where('uid=? and stream=?',$uid,$stream)
					->update(array("islive"=>1));
                setUserLiveListCache($uid); // 设置直播列表缓存
            }
			return $info;
		}else{
			$this->stopRoom($uid,$stream);
			return 1;
		}
	}
	
	/* 修改直播状态 */
	public function changeLiveType($uid,$stream,$data){
        $res = DI()->notorm->users_live
				->where('uid=? and stream=?',$uid,$stream)
				->update( $data );
        setUserLiveListCache($uid); // 设置直播列表缓存
        return $res;
	}
	
	/*
     * 关播
	 * $liveself 是否是主播自己关播，false 不是，true 是
	 */
	public function stopRoom($uid,$stream,$liveself=false,$acttype='',$is_forbidden=0) {
        $key='stopRoom_'.$stream;
        $info = getcaches($key);
        if($info){
            return $info;
        }

	    $redis = connectionRedis();
	    $userInfo=getUserInfo($uid);
	    $tenantId=$userInfo['tenant_id'];

		$info=DI()->notorm->users_live
				->select("uid,showid,starttime,title,province,city,stream,lng,lat,type,type_val,liveclassid,tenant_id,game_user_id,stop_time,thumb")
				->where('uid=? and stream=? ',$uid,$stream)
				->fetchOne();
        if(!$info){
            $rs['length']=0;
            $rs['nums']=0;
            $rs['votes']=0;
            return $rs;
        } else {
            $configpri=getConfigPri();
		    // 收费房间最低直播时间,开启门票发房间或者及时房间是。若直播时间不足无法结束直播
		    if(in_array($info['type'],[2,3]) && $liveself === true && !$acttype && (time()-$info['starttime']) < $configpri['charoom_min_livetime']*60){
		       return array('code' => 2080, 'msg' => codemsg('2080'), 'info' => array((time()-$info['starttime']),$configpri['charoom_min_livetime']));
            }

            //更新暂停直播统计时长
            $stopinfo =DI()->notorm->users_stoplog->select("pause_time")->where(' stream=? and status=0 ',$stream)->fetchOne();

            if($stopinfo){
                $resdata['recover_time'] = time();
                $resdata['stop_time'] = time()-$stopinfo['pause_time'];
                $resdata['status'] = 1;
                DI()->notorm->users_stoplog->where('stream=? and status=0 ',$info['stream'])->update($resdata);
            }


            /*
             * 恢复直播统计累计暂停时长
             */
            //已经恢复的直播
            $info['stop_time'] =    DI()->notorm->users_stoplog->where(['stream'=>$info['stream'],'status=1'])->sum('stop_time');;
            $info['stop_time'] = $info['stop_time']?$info['stop_time']:0;
            $info['stop_time'] =  $info['stop_time'] < 60 ?$info['stop_time']:30;
            DI()->notorm->liveing_log->where(['stream'=>$info['stream']])->update(['stop_time'=>$info['stop_time']]);


			DI()->notorm->users_live->where('uid=?',$uid)->delete();

            logapi([
                'req_param'=>array_merge($_POST, $_GET),
                'func_param'=>['uid'=>$uid, 'stream'=>$stream, 'liveself'=>$liveself, 'acttype'=>$acttype, 'is_forbidden'=>$is_forbidden]
            ], '【关闭直播间】'.$stream);

			$nowtime=time();
			$info['endtime']=$nowtime;
			$info['time']=date("Y-m-d",$info['showid']);
            $votes=DI()->notorm->users_coinrecord
                ->where("uid !=? and touid=? and showid=? and action != 'bet' and type='expend' ",$uid,$uid,$info['showid'])
                ->sum('totalcoin');
			$info['votes']=0;
			if($votes){
				$info['votes']=$votes;
			}
			//2021.01.13 修改为累计观众
            //在线观众数
			//$nums=DI()->redis->zCard('user_'.$stream);
            //累计观众数

            $livelog['totalcoin']=$info['votes'];
            $livelog['uid'] = $uid;
            $livelog['endtime'] = $nowtime;

            try{
                DI()->notorm->liveing_log->where("stream = '".$stream."'")->update($livelog);
            }catch (\Exception $e){
                logapi(['err_msg'=>$e->getMessage(), 'update_data'=>$livelog], '【关闭直播间后，更新liveing_log失败】'.$stream);
            }

            $nums=DI()->redis->zCard('user_'.$stream."_count");
            DI()->redis->hDel("livelist",$uid);
			DI()->redis->delete($uid.'_zombie');
			DI()->redis->delete($uid.'_zombie_uid');
			DI()->redis->delete('attention_'.$uid);
			DI()->redis->delete('user_'.$stream);
            //清理打赏氛围数据
            DI()->redis->hDel("autovotes",$uid);
			//清理累计观众集合
            DI()->redis->delete('user_'.$stream."_count");
			$info['nums']=$nums;

			try{
                $info['title'] = cust_unicode($info['title']);
                $result = DI()->notorm->users_liverecord->insert($info);
                if(!$result){
                    logapi($info, '【关闭直播间后，新增直播记录失败】'.$uid);
                }
            }catch (\Exception $e){
                logapi(['err_msg'=>$e->getMessage(), 'insert_data'=>$info], '【关闭直播间后，新增直播记录失败】'.$uid);
            }

            /* 游戏处理 */
			$game=DI()->notorm->game
				->select("*")
				->where('stream=? and liveuid=? and state=?',$stream,$uid,"0")
				->fetchOne();
			$total=array();
            $red_model= new Model_Red();
            $redList = $red_model->getRedList($uid, $info['showid']);
            // 清理红包

            $config = getConfigPub();
            $money_rate = $config['money_rate'];

            foreach ($redList as $value) {

                $red_coin = $value['coin'] - $value['coin_rob'];
                $red_num = $value['nums'] - $value['nums_rob'];
                DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array(
                        'coin' => new NotORM_Literal("coin + {$red_coin}"),
                        'consumption' => new NotORM_Literal("consumption - {$red_coin}"),
                        ));
                DI()->redis->delete('red_user_winning_'.$stream.'_'.$value['id']);
                DI()->redis->delete('red_list_'.$stream.'_'.$value['id']);
                if ($red_coin > 0){
                    DI()->notorm->red
                        ->select("*")
                        ->where('id = ?  ',$value['id'])
                        ->update(array('status' => 3));// 红包没抢完退货
                    $red_insert=array(
                        "type"=>"income",
                        "action"=>'returnred',
                        "uid"=>$uid,
                        "touid"=>$uid,
                        "giftid"=>$value['id'],
                        "giftcount"=>$red_num,
                        "totalcoin"=> $red_coin,
                        "showid"=>0,
                        "addtime"=>time(),
                        "tenant_id"=>$tenantId ,

                    );
                    $coinrecordModel = new Model_Coinrecord();
                    $coinrecordModel->addCoinrecord($red_insert);
                }


            }


			if($game)
			{
				$total=DI()->notorm->users_gamerecord
					->select("uid,sum(coin_1 + coin_2 + coin_3 + coin_4 + coin_5 + coin_6) as total")
					->where('gameid=?',$game['id'])
					->group('uid')
					->fetchAll();
				foreach($total as $k=>$v){
					DI()->notorm->users
						->where('id = ?', $v['uid'])
						->update(array('coin' => new NotORM_Literal("coin + {$v['total']}")));
					delcache('userinfo_'.$v['uid']);

					$gameUserInfo=getUserInfo($v['uid']);
					
					$insert=array("type"=>'income',"action"=>'game_return',"uid"=>$v['uid'],"touid"=>$v['uid'],"giftid"=>$game['id'],"giftcount"=>1,"totalcoin"=>$v['total'],"showid"=>0,"addtime"=>$nowtime,"tenant_id"=>$gameUserInfo['tenant_id'] );
                    Model_Coinrecord::getInstance()->addCoinrecord($insert);
				}

				DI()->notorm->game
					->where('id = ?', $game['id'])
					->update(array('state' =>'3','endtime' => time() ) );
				$brandToken=$stream."_".$game["action"]."_".$game['starttime']."_Game";
				DI()->redis->delete($brandToken);
			}
            
            /* 下庄处理 */
            $action=4;
            $key='banker_'.$action.'_'.$stream;
            
            $list=DI()->redis->hGetAll($key);
            
            foreach($list as $k=>$v){
                $data=json_decode($v,true);
                $uid=$k;
                
                DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('coin' => new NotORM_Literal("coin + {$data['deposit']}")));
                delcache('userinfo_'.$uid);
                $addtime=time();
                $gameUserInfo=getUserInfo($uid);
                $insert=array("type"=>'income',"action"=>'deposit_return',"uid"=>$uid,"touid"=>$uid,"giftid"=>0,"giftcount"=>1,"totalcoin"=>$data['deposit'],"showid"=>0,"addtime"=>$addtime ,"game_action"=>4,"game_banker"=>0,'tenant_id'=>$gameUserInfo['tenant_id'] );
                Model_Coinrecord::getInstance()->addCoinrecord($insert);
                
            }
		}
		//针对封禁主播处理
        if($is_forbidden==1){
                /* 主播封禁，禁言，禁播，会员禁用 */
                DI()->notorm->users->where('id=? ',$uid)->update(array('user_status'=>0,'isforbidlive'=>1,'isshutup'=>1));
                $redis-> hSet('user_shutup',$uid,1);
                $redis-> hSet('user_forbidlive',$uid,1);

        }
        $redis->zRem('watching_num'.$tenantId,$uid); // 当前租户的在线人数处理
        $redis->hDel('live_api_heart_time',$uid);
        delcache('live_watchtime_'.$stream.'*'); //清楚缓存的观看时间
        $redis->hDel('live_socket_heart_time',$uid);
        $redis->hSet('author_thumb',$uid,$info['thumb']);
         $redis->hSet('author_title',$uid,$info['title']);
        $cha=$info['endtime'] - $info['starttime']-$info['stop_time'];//减去暂停时间
        $rs['length']=getSeconds($cha,1);
        $rs['nums']=$info['nums'];
        $rs['votes']=$info['votes'];

        $redis->zRem('disconnect_'.$tenantId,$uid);
        delUserLiveListCache($tenantId,$uid); // 移出redis直播列表缓存

        // 如果不是用户自己关播，主播端则从缓存获取关播操作的返回值（直播时长等、、、）
        $key = 'stopRoom_'.$stream;
        setcaches($key, $rs, 60*60*24);

        $rs['stop_end'] = 1;
		return $rs;
	}
	/* 关播信息 */
	public function stopInfo($stream){
		
		$rs=array(
			'nums'=>"0",
			'length'=>"0",
			'votes'=>"0",
		);
		
		$stream2=explode('_',$stream);
		$liveuid=$stream2[0];
		$starttime=$stream2[1];
		$liveinfo=DI()->notorm->users_liverecord
					->select("starttime,endtime,nums,votes")
					->where('uid=? and starttime=?',$liveuid,$starttime)
					->fetchOne();
		if($liveinfo){
            $cha=$liveinfo['endtime'] - $liveinfo['starttime'];
			$rs['length']=getSeconds($cha,1);
			$rs['nums']=$liveinfo['nums'];
		}
		if($liveinfo['votes']){
			$rs['votes']=$liveinfo['votes'];
		}
		return $rs;
	}
	
	/* 直播状态 */
	public function checkLive($uid,$liveuid,$stream,$language_id){
		$islive=DI()->notorm->users_live
					->select("islive,type,type_val,starttime,tryWatchTime,title")
					->where('uid=? and stream=?',$liveuid,$stream)
					->fetchOne();

		/*if (!$islive){
		    return 2038;
        }*/
		if(!$islive || $islive['islive']==0){
			return 1005;
		}
		$redis = connectionRedis();
        // 若该直播间配置10秒试看，用户已看5秒，退出，则下次还可以再看5秒。在其他直播间则根据其他直播间的试看设置来限制。
        if($islive['type'] != '0'){
            $live_watchtime_key = 'live_watchtime_'.$stream.$uid;
            $watchtime = $redis->get($live_watchtime_key);
            if($watchtime === false){
                $watchtime = $islive['tryWatchTime'];
                $redis->set($live_watchtime_key,$islive['tryWatchTime'],60*60*24*7);
            }
            $left_watchtime = $watchtime > 0 ? $watchtime : 0;
        }

        $rs['left_watchtime'] = isset($left_watchtime) ? $left_watchtime : 0;
		$rs['type']=$islive['type'];
		$rs['type_val']='0';
		$rs['type_msg']='';
        $rs['tryWatchTime']=$islive['tryWatchTime'];
        //所有房间返回房间类型
        $rs['title']=$islive['title'];
			$userinfo=DI()->notorm->users
					->select("issuper")
					->where('id=?',$uid)
					->fetchOne();
			if($userinfo && $userinfo['issuper']==1){

                if($islive['type']==6){
                    
                    return 1007;
                }
				$rs['type']='0';
				$rs['type_val']='0';
				$rs['type_msg']='';
				
				return $rs;
			}

		if($islive['type']==1){
			$rs['type_msg']=md5($islive['type_val']);

		}else if($islive['type']==2){
            $language1 = DI()->config->get('language.need_pay');
            $language2 = DI()->config->get('language.zhuan_shi');

            $rs['type_msg'] = $language1[$language_id].$islive['type_val'].$language2[$language_id];//'本房间为收费房间，需支付'.$islive['type_val'].'钻石'
			//$rs['type_msg']='本房间为收费房间，需支付'.$islive['type_val'].'钻石';
			$rs['type_val']=$islive['type_val'];
			$isexist=DI()->notorm->users_coinrecord
						->select('id')
						->where('uid=? and touid=? and showid=? and action="roomcharge" and type="expend"',$uid,$liveuid,$islive['starttime'])
						->fetchOne();
			if($isexist){
                $rs['type']='0';
			    $rs['type_msg']='本房间为收费房间，已付款';
                $rs['tryWatchTime']=0;
			}
		}else if($islive['type']==3){
			$rs['type_val']=$islive['type_val'];
			//$rs['type_msg']='本房间为计时房间，每分钟需支付'.$islive['type_val'].'钻石';
            $language1 = DI()->config->get('language.jishi_pay');
            $language2 = DI()->config->get('language.zhuan_shi');
            $rs['type_msg'] = $language1[$language_id].$islive['type_val'].$language2[$language_id];//'本房间为计时房间，每分钟需支付'.$islive['type_val'].'钻石';
		}




		
		return $rs;
		
	}
	
	/* 用户余额 */
	public function getUserCoin($uid){
        $result=array();
        $userInfo= getUserInfo($uid);
        $rs= getGameUserBalance($userInfo['game_tenant_id'],$userInfo['game_user_id']);

        if($rs['code']!=0){
            //如果code不等于0为请求失败
            //请求失败时余额返回0
            $result['coin']=0;
        }
        else{
            $result['coin']=$rs['coin'];
        }
        return $result;


//		$userinfo=DI()->notorm->users
//					->select("coin")
//					->where('id=?',$uid)
//					->fetchOne();
//		return $userinfo;
	}

    /* 独立租户房间扣费 */
    public function roomChargealone($uid,$token,$liveuid,$stream){
        $redis = connectionRedis();
        $islive=DI()->notorm->users_live
            ->select("islive,type,type_val,starttime")
            ->where('uid=? and stream=?',$liveuid,$stream)
            ->fetchOne();
        if(!$islive || $islive['islive']==0){
            return 1005;
        }
        if($islive['type']==0 || $islive['type']==1 ){
            return 1006;
        }

        $userinfo=DI()->notorm->users
            ->select("token,expiretime,coin")
            ->where('id=?',$uid)
            ->fetchOne();
        if($userinfo['token']!=$token || $userinfo['expiretime']<time()){
            return 700;
        }
        $liveUserInfo=getUserInfo($liveuid);
        $userInfo=getUserInfo($uid);

        $total=$islive['type_val'];
        if($total<=0){
            return 1007;
        }
        $config=getConfigPub();

        // 用户等级升级加成，根据加成的比例，增加用户等级经验
        $user_noble = getUserNoble($uid);
        $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($total,(1+$user_noble['upgrade_speed']/100),2) : $total;
        /* 更新用户余额 消费 */
        $ifok=DI()->notorm->users
            ->where('id = ? and coin >= ?', $uid,$total)
            ->update(array(
                'coin' => new NotORM_Literal("coin - {$total}"),
                'consumption' => new NotORM_Literal("consumption + {$u_consumption}")
            ));
        delUserInfoCache($uid);
        $userInfo = getUserInfo($uid);
        // 累计消费变动，通知前端
        $redis->zAdd('user_consumptionchange',1,json_encode(['uid'=>$uid,'liveuid'=>$liveuid,'level'=>getLevel($userInfo['consumption']),'consumption'=>$userInfo['consumption'],'exp_can_speak'=>exp_can_speak($userInfo['consumption'])]));
        if(!$ifok){
            return 1008;
        }
        // http 请求到golang的socketio
//        $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//        $http_post_res = http_post($url,['EventType' => 'ConsumptionChange','Message' => json_encode(['Uid'=>$uid,'Liveuid'=>$liveuid,'Level'=>getLevel($userInfo['consumption']),'Consumption'=>$userInfo['consumption'],'ExpCanSpeak'=>exp_can_speak($userInfo['consumption'])])]);

        $action='roomcharge';
        if($islive['type']==3){
            $action='timecharge';
        }

        $giftid=0;
        $giftcount=0;
        $showid=$islive['starttime'];
        $addtime=time();


        /* 分销 */
        setAgentProfit($uid,$total);
        /* 分销 */
        $liveUserTenantInfo=getTenantInfo($liveUserInfo['tenant_id']);
        if ($liveUserTenantInfo['site_id'] == 1) { // 集成
            if ($liveUserInfo['tenant_id'] == $userInfo['tenant_id']) {
                //同平台主播所属租户分润比例
                $anchor_platform_profit_ratio = $config['anchor_platform_profit_ratio'] / 100;
                //同平台主播所属租户分润比例主播分润比例
                $anchor_platform_ratio = $config['anchor_platform_ratio'] / 100;
                //租户分润金额
                $tenant_total = $anchor_platform_profit_ratio * $total;
                //主播分润金额
                $anthor_total = $anchor_platform_ratio * $total;
            } else {
                //主播所属租户分润比例
                $anchor_tenant_profit_ratio = $config['anchor_tenant_profit_ratio'] / 100;
                //消费者所属租户分润比例
                $user_tenant_profit_ratio = $config['user_tenant_profit_ratio'] / 100;
                //主播分润比例
                $anchor_profit_ratio = $config['anchor_profit_ratio'] / 100;
                //租户分润金额
                $tenant_total = $anchor_tenant_profit_ratio * $total;
                //消费者所属分润金额
                $tenantuser_total = $user_tenant_profit_ratio * $total;
                //主播分润金额
                $anthor_total = $anchor_profit_ratio * $total;

            }
        }else{
            //主播所属租户分润比例
            $anchor_tenant_profit_ratio = $config['anchor_tenant_profit_ratio'] / 100;
            //消费者所属租户分润比例
            $user_tenant_profit_ratio = $config['user_tenant_profit_ratio'] / 100;
            //主播分润比例
            $anchor_profit_ratio = $config['anchor_profit_ratio'] / 100;
            //租户分润金额
            $tenant_total = $anchor_tenant_profit_ratio * $total;
            //消费者所属分润金额
            $tenantuser_total = $user_tenant_profit_ratio * $total;
            //主播分润金额
            $anthor_total = $anchor_profit_ratio * $total;
        }
        /* 更新直播 映票 累计映票 */
        DI()->notorm->users
            ->where('id = ?', $liveuid)
            ->update(
                array('votes' => new NotORM_Literal("votes + {$anthor_total}"),
                    'votestotal' => new NotORM_Literal("votestotal + {$anthor_total}"),
                    'coin' => new NotORM_Literal("coin + {$anthor_total}"),
                ));
        delUserInfoCache($liveuid);
        $insert_votes=[
            'type'=>'income',
            'action'=>$action,
            'uid'=>$liveuid,
            'votes'=>$total,
            'addtime'=>time(),
            'tenant_id' =>$liveUserInfo['tenant_id']
        ];
        DI()->notorm->users_voterecord->insert($insert_votes);

        /* 更新直播 映票 累计映票 */
        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord(array(
                "type"=>'expend',
                "action"=>$action,
                "uid"=>$uid,
                "touid"=>$liveuid,
                "giftid"=>$giftid,
                "giftcount"=>$giftcount,
                "totalcoin"=>$total,
                "showid"=>$showid,
                "addtime"=>$addtime,
                'tenant_id' =>$userInfo['tenant_id'],
                'anthor_total'=>$anthor_total,
                'tenant_total'=>$tenant_total,
                'tenantuser_total'=>$tenantuser_total,
                'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],

            ));
        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord(array(
                "type"=>'income',
                "action"=>$action,
                "uid"=>$liveuid,
                "touid"=> $liveuid ,
                "giftid"=>$giftid,
                "giftcount"=>$giftcount,
                "totalcoin"=>$anthor_total,
                "showid"=>$showid,
                "addtime"=>$addtime,
                'tenant_id' =>$liveUserInfo['tenant_id'],
                'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],
            ));

        return array( "level" => getLevel($userInfo['consumption']), "coin" => $userInfo['coin']);
    }

    /* 彩票租户房间扣费 */
	public function roomCharge($uid,$token,$liveuid,$stream){
        try {
            $redis = connectionRedis();
            $islive=DI()->notorm->users_live
                ->select("islive,type,type_val,starttime")
                ->where('uid=? and stream=?',$liveuid,$stream)
                ->fetchOne();
            if(!$islive || $islive['islive']==0){
                return 1005;
            }

            if($islive['type']==0 || $islive['type']==1 ){
                return 1006;
            }

            $userinfo=DI()->notorm->users
                ->select("token,expiretime,coin")
                ->where('id=?',$uid)
                ->fetchOne();
            if($userinfo['token']!=$token || $userinfo['expiretime']<time()){
                return 700;
            }

            $liveUserInfo=getUserInfo($liveuid);
            $userInfo=getUserInfo($uid);
            $config=getConfigPub();
            $liveUserTenantInfo=getTenantInfo($liveUserInfo['tenant_id']);
            $anchortenant_name=$liveUserTenantInfo['name'];
            $userTenantInfo=getTenantInfo($userInfo['tenant_id']);
            $sendtenant_name=$userTenantInfo['name'];

            $total=$islive['type_val'];
            if($total<=0){
                return 1007;
            }

            //开始数据库事务
            beginTransaction();

            //查询余额
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
                //余额不足
                return 1008;
            }
            //处理完数据库操作之后再调用接口
            //如果接口返回错误则回滚事务

            $consumption = $userInfo['consumption'] +$total;
            $level = intval(getLevel($consumption));

            // 用户等级升级加成，根据加成的比例，增加用户等级经验
            $user_noble = getUserNoble($uid);
            $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($total,(1+$user_noble['upgrade_speed']/100),2) : $total;
            //增加消费总额
            $ifok=DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('consumption' => new NotORM_Literal("consumption + {$u_consumption}"),'userlevel' =>$level) );
            delUserInfoCache($uid);
            $userInfo = getUserInfo($uid);
            // 累计消费变动，通知前端
            $redis->zAdd('user_consumptionchange',1,json_encode(['uid'=>$uid,'liveuid'=>$liveuid,'level'=>getLevel($userInfo['consumption']),'consumption'=>$userInfo['consumption'],'exp_can_speak'=>exp_can_speak($userInfo['consumption'])]));
            if(!$ifok){
                return 1008;
            }

            $action='roomcharge';
            if($islive['type']==3){
                $action='timecharge';
            }

            $giftid=0;
            $giftcount=0;
            $showid=$islive['starttime'];
            $addtime=time();


            /* 分销 */
            //setAgentProfit($uid,$total);
            /* 分销 */

            //主播所属租户分润比例
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
            //主播分润金额
            $anthor_total  =    $res['anthor_total'];
            $family_total  =    $res['family_total'];


            //家族长及时到账
            $familyhead_id    =    $res['family_id'];
            $familyhead_money =    $family_total-$anthor_total;

            //记录打赏列表
            $profitinsert = array(
                'uid'=>$liveuid,   //直播会员ID
                'status'=>'0',
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

            /* 更新直播 映票 累计映票 */
            DI()->notorm->users
                ->where('id = ?', $liveuid)
                ->update( array('votes' => new NotORM_Literal("votes + {$total}"),'votestotal' => new NotORM_Literal("votestotal + {$total}") ));
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

            /* 更新直播 映票 累计映票 */
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord(array(
                    "type"=>'expend',"action"=>$action,"uid"=>$uid,"touid"=>$liveuid,"giftid"=>$giftid,
                    "giftcount"=>$giftcount,"totalcoin"=>$total,"showid"=>$showid,"addtime"=>$addtime,
                    "tenant_id"=>$userInfo['tenant_id'],'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],'anthor_total'=>$anthor_total,
                    'tenant_total'=>$tenant_total,'tenantuser_total'=>$tenantuser_total,'family_total'=>$family_total,
                    "cd_ratio"=>'1:'.floatval($config['money_rate']),'familyhead_total'=>$familyhead_money,
                ));


            /**
             * 主播收入记录
             */
            $insertanchor=array(
                "type"=>'income',
                "action"=>$action,
                "uid"=>$liveuid,
                "touid"=>$liveuid ,
                "giftid"=>$giftid,
                "giftcount"=>$giftcount,
                "totalcoin"=>$anthor_total*$config['money_rate'],
                "showid"=>$showid,
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
                    "giftcount"=>$giftcount,
                    "totalcoin"=>$familyhead_money*$config['money_rate'],
                    "showid"=>$showid,
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
                'amount'=>  $giftcount,
                'content'=> '收费房间',
                'money'=> round($total/$config['money_rate'],3),
                'type'=>3,

            );

            //主播平台分润
            $this-> zhubo_tenant($liveUserInfo,$anchortenant_name,$tenant_total,$anchor_tenant_profit_ratio,$datadetail,$sendtenant_name);

            //消费平台分润
            $this-> xiaofei_tenant($userInfo,$sendtenant_name,$tenantuser_total,$user_tenant_profit_ratio,$datadetail);
            //主播分润
            $this->zhubo_update($liveUserInfo,$anthor_total,$res,$datadetail,$sendtenant_name);

            //家族长分润
            $this->family_update($res,$familyhead_money,$datadetail,$sendtenant_name);


            $rs['coin']=$coin-$total;

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

            $type=0;
            $detail='';
            if($action==='roomcharge'){
                //付费房间
                $detail='门票扣费';
                $type=1;

            }else if($action==='timecharge'){
                //计时房间
                $detail='计时扣费';
                $type=1;
            }

            $roomid=$liveuid;
            $anchorid=$liveuid;
            $anchorname=$liveUserInfo['user_nicename'];
            $anchorfromid=$liveUserInfo['tenant_id'];
            $anchorformname=$liveUserTenantInfo['name'];
            $tId=$userInfo['game_tenant_id'];
            $custId=$userInfo['game_user_id'];
            $custAnchorid=$liveUserInfo['game_user_id'];
            $anchorTenantid=$liveUserInfo['game_tenant_id'];


            $updateResult= reduceGameUserBalance($useridGame,$useridLive,$tidGame,$tidLive,$usernickname,$amount,$diamond,$type,$detail,$roomid,$anchorid,$anchorname,$anchorfromid,$anchorformname,$tId,$custId,$custAnchorid,$anthor_total,$anchorTenantid,$familyhead_id,$familyhead_money,$level);

            if($updateResult['code']!=0){
                rollbackTransaction();
                //调用失败,回滚事务,并返回余额不足错误
                return 1008;
            }
            else{
                commitTransaction();
            }
            // http 请求到golang的socketio
//            $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//            $http_post_res = http_post($url,['EventType' => 'ConsumptionChange','Message' => json_encode(['Uid'=>$uid,'Liveuid'=>$liveuid,'Level'=>getLevel($userInfo['consumption']),'Consumption'=>$userInfo['consumption'],'ExpCanSpeak'=>exp_can_speak($userInfo['consumption'])])]);

            return array( "level" => getLevel($userInfo['consumption']), "coin" => $userInfo['coin']);
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("房间扣费异常:".$ex->getMessage());
            //调用失败,回滚事务,并返回余额不足错误
            return 1008;
        }

		
	}
	
	/* 判断是否僵尸粉 */
	public function isZombie($uid) {
        $userinfo=DI()->notorm->users
					->select("iszombie")
					->where("id='{$uid}'")
					->fetchOne();
		
		return $userinfo['iszombie'];				
    }
	
	/* 僵尸粉 */
    public function getZombie($stream,$where) {

		$ids= DI()->notorm->users_zombie
            ->select('uid')
            ->where("uid not in {$where}")
			->limit(0,10)
            ->fetchAll();	

		$info=array();

		if($ids){
            foreach($ids as $k=>$v){
                
                $userinfo=getUserInfo($v['uid'],1);
                if(!$userinfo){
                    DI()->notorm->users_zombie->where("uid={$v['uid']}")->delete();
                    continue;
                }
                
                $info[]=$userinfo;

                $score='0.'.($userinfo['level']+100).'1';
				DI()->redis -> zAdd('user_'.$stream,$score,$v['uid']);
            }	
		}
		return 	$info;		
    }
	
	/* 礼物列表 */
	public function getGiftList(){

		$rs=DI()->notorm->gift
			->select("*")
            ->where(['tenant_id'=>intval(getTenantId())])
			->order("orderno asc,addtime desc")
			->fetchAll();
		foreach($rs as $k=>$v){
			$rs[$k]['gifticon_mini']=get_upload_path($v['gifticon_mini']);
            $rs[$k]['swf']=get_upload_path($v['swf']);
            $rs[$k]['gifticon']=get_upload_path($v['gifticon']);
		}	

		return $rs;
	}
    /*
      独立租户赠送礼物
    */

    public function sendGiftalone($uid,$liveuid,$stream,$giftid,$giftcount) {
        $redis = connectionRedis();
        /* 礼物信息 */
        $giftinfo=DI()->notorm->gift
            ->select("type,mark,giftname,gifticon,needcoin,swftype,swf,swftime")
            ->where('id=?',$giftid)
            ->fetchOne();
        if(!$giftinfo){
            /* 礼物信息不存在 */
            return array('code' => 1002, 'msg' => '礼物信息不存在', 'info' => '');
        }
        $total= $giftinfo['needcoin']*$giftcount;
        $config=getConfigPub();

        $addtime=time();
        $type='expend';
        $action='sendgift';
        /* 更新用户余额 消费 */
        $ifok =DI()->notorm->users->where('id = ?  ', $uid)->select('coin')->fetchOne();
        if ($ifok['coin'] >=$total ){
            // 用户等级升级加成，根据加成的比例，增加用户等级经验
            $user_noble = getUserNoble($uid);
            $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($total,(1+$user_noble['upgrade_speed']/100),2) : $total;
            DI()->notorm->users
                ->where('id = ? and coin >=?', $uid,$total)
                ->update(array(
                    'coin' => new NotORM_Literal("coin - {$total}"),
                    'consumption' => new NotORM_Literal("consumption + {$u_consumption}")
                ) );
            delUserInfoCache($uid);
            $userInfo = getUserInfo($uid);
            // 累计消费变动，通知前端
            $redis->zAdd('user_consumptionchange',1,json_encode(['uid'=>$uid,'liveuid'=>$liveuid,'level'=>getLevel($userInfo['consumption']),'consumption'=>$userInfo['consumption'],'exp_can_speak'=>exp_can_speak($userInfo['consumption'])]));
            // http 请求到golang的socketio
//            $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//            $http_post_res = http_post($url,['EventType' => 'ConsumptionChange','Message' => json_encode(['Uid'=>$uid,'Liveuid'=>$liveuid,'Level'=>getLevel($userInfo['consumption']),'Consumption'=>$userInfo['consumption'],'ExpCanSpeak'=>exp_can_speak($userInfo['consumption'])])]);
        }else{
            /* 余额不足 */
            return array('code' => 1001, 'msg' => '余额不足', 'info' => '');
        }

        $liveUserInfo=getUserInfo($liveuid);
        $userInfo=getUserInfo($uid);

        /* 分销 */
        setAgentProfit($uid,$total);
        /* 分销 */

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


       // var_dump($tenant_total);var_dump($tenantuser_total);var_dump($anthor_total);exit;
        /* 更新直播 魅力值 累计魅力值 */
        $istouid =DI()->notorm->users
            ->where('id = ?', $liveuid)
            ->update(
                array('votes' => new NotORM_Literal("votes + {$anthor_total}"),
                    'votestotal' => new NotORM_Literal("votestotal + {$anthor_total}"),
                    'coin' =>  new NotORM_Literal("coin + {$anthor_total}"),
                ));
        if ($res['family_id'] != 0) {
            DI()->notorm->users
                ->where('id = ?', $familyhead_id)
                ->update(array('coin' => new NotORM_Literal("coin + {$familyhead_money}")));
        }
        delUserInfoCache($familyhead_id);
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

        /**
         *  用户消费记录
         */
        $insert=array(
            "type"=>$type,
            "action"=>$action,
            "uid"=>$uid,
            "touid"=>$liveuid,
            "giftid"=>$giftid,
            "giftcount"=>$giftcount,
            "totalcoin"=>$total,
            "showid"=>$showid,
            "mark"=>$giftinfo['mark'],
            "addtime"=>$addtime,
            'tenant_id' =>$userInfo['tenant_id'],
            'anthor_total'=>$anthor_total,
            'tenant_total'=>$tenant_total,
            'tenantuser_total'=>$tenantuser_total,
            'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],
            'anthor_total' => $anthor_total,
            'tenant_total' => $tenant_total,
            'tenantuser_total' => $tenantuser_total,
            'family_total' => $family_total,
            "cd_ratio" => '1:' . floatval($config['money_rate']),
            'familyhead_total' => $familyhead_money,

        );
        $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);
        /**
         * 主播收入记录
         */
        $insert=array(
            "type"=>'income',
            "action"=>$action,
            "uid"=>$liveuid,
            "touid"=>$liveuid ,
            "giftid"=>$giftid,
            "giftcount"=>$giftcount,
            "totalcoin"=>$anthor_total* $config['money_rate'],
            "showid"=>$showid,
            "mark"=>$giftinfo['mark'],
            "addtime"=>$addtime,
            'tenant_id' =>$liveUserInfo['tenant_id'],
            'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],
            'anthor_total' => $anthor_total,
            'tenant_total' => $tenant_total,
            'tenantuser_total' => $tenantuser_total,
            'family_total' => $family_total,
            "cd_ratio" => '1:' . floatval($config['money_rate']),
            'familyhead_total' => $familyhead_money,
        );
        $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);
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
        $userinfo2 =DI()->notorm->users
            ->select('consumption,coin')
            ->where('id = ?', $uid)
            ->fetchOne();

        $level=getLevel($userinfo2['consumption']);

        /* 更新主播热门 */
        if($giftinfo['mark']==1){
            DI()->notorm->users_live
                ->where('uid = ?', $liveuid)
                ->update( array('hotvotes' => new NotORM_Literal("hotvotes + {$total}") ));
        }

        DI()->redis->zIncrBy('user_'.$stream,$total,$uid);

        /* 清除缓存 */
        delCache("userinfo_".$uid);
        delCache("userinfo_".$liveuid);

        $votestotal=$this->getVotes($liveuid);

        $gifttoken=md5(md5($action.$uid.$liveuid.$giftid.$giftcount.$total.$showid.$addtime.rand(100,999)));

        $swf=$giftinfo['swf'] ? get_upload_path($giftinfo['swf']):'';

        $result=array("uid"=>$uid,"giftid"=>$giftid,"type"=>$giftinfo['type'],"giftcount"=>$giftcount,"totalcoin"=>$total,"giftname"=>$giftinfo['giftname'],"gifticon"=>get_upload_path($giftinfo['gifticon']),"swftime"=>$giftinfo['swftime'],"swftype"=>$giftinfo['swftype'],"swf"=>$swf,"level"=>$level,"coin"=>$userinfo2['coin'],"votestotal"=>$votestotal,"gifttoken"=>$gifttoken);

        return $result;
    }
    /*
         虚拟用户独立租户赠送礼物
       */

    public function sendGiftvutar($uid,$liveuid,$stream,$giftid,$giftcount) {

        /* 礼物信息 */
        $giftinfo=DI()->notorm->gift
            ->select("type,mark,giftname,gifticon,needcoin,swftype,swf,swftime")
            ->where('id=?',$giftid)
            ->fetchOne();
        if(!$giftinfo){
            /* 礼物信息不存在 */
            return 1002;
        }

        $total= $giftinfo['needcoin']*$giftcount;

        $addtime=time();
        $type='expend';
        $action='sendgift';



        $liveUserInfo=getUserInfo($liveuid);
        $userInfo=getUserInfo($uid);





        $stream2=explode('_',$stream);
        $showid=$stream2[1];





        DI()->redis->zIncrBy('user_'.$stream,$total,$uid);

        /* 清除缓存 */
        delCache("userinfo_".$uid);
        delCache("userinfo_".$liveuid);

        $votestotal=$this->getVotes($liveuid);

        $gifttoken=md5(md5($action.$uid.$liveuid.$giftid.$giftcount.$total.$showid.$addtime.rand(100,999)));

        $swf=$giftinfo['swf'] ? get_upload_path($giftinfo['swf']):'';

        $result=array("uid"=>$uid,"giftid"=>$giftid,"type"=>$giftinfo['type'],"giftcount"=>$giftcount,"totalcoin"=>$total,"giftname"=>$giftinfo['giftname'],"gifticon"=>get_upload_path($giftinfo['gifticon']),"swftime"=>$giftinfo['swftime'],"swftype"=>$giftinfo['swftype'],"swf"=>$swf,"votestotal"=>$votestotal,"gifttoken"=>$gifttoken);

        return $result;
    }


    /* 彩票租户赠送礼物 */
	public function sendGift($uid,$liveuid,$stream,$giftid,$giftcount,$send_type) {

        try {
            $redis = connectionRedis();
            //开始数据库事务
            beginTransaction();

            $config=getConfigPub();

            $liveUserInfo=getUserInfo($liveuid);
            $userInfo=getUserInfo($uid);
            $liveUserTenantInfo=getTenantInfo($liveUserInfo['tenant_id']);
            $anchortenant_name=$liveUserTenantInfo['name'];
            $userTenantInfo=getTenantInfo($userInfo['tenant_id']);
            $sendtenant_name=$userTenantInfo['name'];
            /* 礼物信息 */
            $giftinfo=DI()->notorm->gift
                ->select("type,mark,giftname,gifticon,needcoin,swftype,swf,swftime")
                ->where('id=?',$giftid)
                ->fetchOne();
            if(!$giftinfo){
                /* 礼物信息不存在 */
                return 1002;
            }

            $total= $giftinfo['needcoin']*$giftcount;

            $addtime=time();
            $type='expend';
            $action='sendgift';

            //针对首充礼物处理，不做任何数据处理，只展示效果
            if($send_type == 1){
                $chargeinfo =DI()->notorm->users_chargegift
                    ->select("*")
                    ->where('uid=?',$uid)
                    ->fetchOne();
                if(empty($chargeinfo)){
                    return 1005;
                }
                //查询余额
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
                DI()->notorm->users_chargegift
                    ->where('uid = ?', $uid)
                    ->update(array('gift_nums' => new NotORM_Literal("gift_nums - 1")) );

                $votestotal=$this->getVotes($liveuid);
                $consumption = $userInfo['consumption'];
                $level = intval(getLevel($consumption));
                $stream2=explode('_',$stream);
                $showid=$stream2[1];
                $gifttoken=md5(md5($action.$uid.$liveuid.$giftid.$giftcount.$total.$showid.$addtime.rand(100,999)));
                $swf=$giftinfo['swf'] ? get_upload_path($giftinfo['swf']):'';
                $result=array("uid"=>$uid,"giftid"=>$giftid,"type"=>$giftinfo['type'],"giftcount"=>$giftcount,"totalcoin"=>$total,"giftname"=>$giftinfo['giftname'],"gifticon"=>get_upload_path($giftinfo['gifticon']),"swftime"=>$giftinfo['swftime'],"swftype"=>$giftinfo['swftype'],"swf"=>$swf,"level"=>$level,"coin"=>$coin,"votestotal"=>$votestotal,"gifttoken"=>$gifttoken);
                commitTransaction();
                return $result;
            }


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
                //余额不足
                return array('code' => 1001, 'msg' => codemsg('2006'), 'info' => array($coin,$total));
            }

            //分销和家族分销功能关闭 ,暂时保留

            /* 分销 */
            //setAgentProfit($uid,$total);
            /* 分销 */


            //TODO 计算分润
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
                "giftcount"=>$giftcount,"totalcoin"=>$total,
                "showid"=>$showid,"mark"=>$giftinfo['mark'],"addtime"=>$addtime,'tenant_id' =>$userInfo['tenant_id'],
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
                "giftcount"=>$giftcount,
                "totalcoin"=>$anthor_total*$config['money_rate'],
                "showid"=>$showid,
                "mark"=>$giftinfo['mark'],
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
                    "giftcount"=>$giftcount,
                    "totalcoin"=>$familyhead_money*$config['money_rate'],
                    "showid"=>$showid,
                    "mark"=>$giftinfo['mark'],
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
                'amount'=>  $giftcount,
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

            $votestotal=$this->getVotes($liveuid);

            $gifttoken=md5(md5($action.$uid.$liveuid.$giftid.$giftcount.$total.$showid.$addtime.rand(100,999)));

            $swf=$giftinfo['swf'] ? get_upload_path($giftinfo['swf']):'';

            $result=array("uid"=>$uid,"giftid"=>$giftid,"type"=>$giftinfo['type'],"giftcount"=>$giftcount,"totalcoin"=>$total,"giftname"=>$giftinfo['giftname'],"gifticon"=>get_upload_path($giftinfo['gifticon']),"swftime"=>$giftinfo['swftime'],"swftype"=>$giftinfo['swftype'],"swf"=>$swf,"level"=>$level,"coin"=>$coin-$total,"votestotal"=>$votestotal,"gifttoken"=>$gifttoken);

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
            $detail='赠送礼物';
            $roomid=$liveuid;
            $anchorid=$liveuid;
            $anchorname=$liveUserInfo['user_nicename'];
            $anchorfromid=$liveUserInfo['tenant_id'];
            $anchorformname=$liveUserTenantInfo['name'];
            $tId=$userInfo['game_tenant_id'];
            $custId=$userInfo['game_user_id'];
            $custAnchorid=$liveUserInfo['game_user_id'];
            $anchorTenantid=$liveUserInfo['game_tenant_id'];


            $updateResult= reduceGameUserBalance($useridGame,$useridLive,$tidGame,$tidLive,$usernickname,$amount,$diamond,$type,$detail,$roomid,$anchorid,$anchorname,$anchorfromid,$anchorformname,$tId,$custId,$custAnchorid,$anthor_total,$anchorTenantid,$familyhead_id,$familyhead_money,$level);
            if($updateResult['code']!=0){
                if($updateResult['code']==1002){
                    rollbackTransaction();
                    //调用失败,回滚事务,并返回网络错误
                    logapi(['updateResult'=>$updateResult],'【更新java余额失败，返回网络错误】');  // 接口日志记录
                    return 1009;
                }
            }
            else{
                commitTransaction();

                /* 更新主播热门 */
                if($giftinfo['mark']==1){
                    DI()->notorm->users_live
                        ->where('uid = ?', $liveuid)
                        ->update( array('hotvotes' => new NotORM_Literal("hotvotes + {$total}") ));
                }

                DI()->redis->zIncrBy('user_'.$stream,$total,$uid);

            }
            // http 请求到golang的socketio
//            $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//            $http_post_res = http_post($url,['EventType' => 'ConsumptionChange','Message' => json_encode(['Uid'=>$uid,'Liveuid'=>$liveuid,'Level'=>getLevel($userInfo['consumption']),'Consumption'=>$userInfo['consumption'],'ExpCanSpeak'=>exp_can_speak($userInfo['consumption'])])]);

            return $result;
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("赠送礼物异常:".$ex->getMessage());
            //调用失败,回滚事务,并返回余额不足错误
            return array('code' => 1001, 'msg' => codemsg('2006'), 'info' => array("赠送礼物异常:".$ex->getMessage()));
        }
	}		
	
	/* 彩票租户发送弹幕 */
	public function sendBarrage($uid,$liveuid,$stream,$giftid,$giftcount,$content) {
	    try{
	        $redis = connectionRedis();
            //开始数据库事务
            beginTransaction();

            $config=getConfigPub();

            $liveUserInfo=getUserInfo($liveuid);
            $userInfo=getUserInfo($uid);
            $liveUserTenantInfo=getTenantInfo($liveUserInfo['tenant_id']);
            $configpri=getConfigPri();
            $anchortenant_name=$liveUserTenantInfo['name'];
            $userTenantInfo=getTenantInfo($userInfo['tenant_id']);
            $sendtenant_name=$userTenantInfo['name'];

            $giftinfo=array(
                "giftname"=>'弹幕扣费',
                "gifticon"=>'',
                "needcoin"=>$configpri['barrage_fee'],
                "mark" => 0,
            );

            $total= $giftinfo['needcoin']*$giftcount;
            $addtime=time();
            $type='expend';
            $action='sendbarrage';

            if($total>0){

                $consumption = $userInfo['consumption'] +$total;
                $level = intval(getLevel($consumption));
                // 用户等级升级加成，根据加成的比例，增加用户等级经验
                $user_noble = getUserNoble($uid);
                $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($total,(1+$user_noble['upgrade_speed']/100),2) : $total;
                //增加消费总额
                $ifok=DI()->notorm->users
                    ->where('id = ?', $uid)
                    ->update(array('consumption' => new NotORM_Literal("consumption + {$u_consumption}"),'userlevel' =>$level) );
                delUserInfoCache($uid);
                $userInfo = getUserInfo($uid);
                // 累计消费变动，通知前端
                $redis->zAdd('user_consumptionchange',1,json_encode(['uid'=>$uid,'liveuid'=>$liveuid,'level'=>getLevel($userInfo['consumption']),'consumption'=>$userInfo['consumption'],'exp_can_speak'=>exp_can_speak($userInfo['consumption'])]));
                if(!$ifok){
                    return 1001;
                }
                //查询余额
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
                    //余额不足
                    return 1001;
                }

                //TODO 计算分润
                //主播所属租户分润比例
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
                //主播分润金额
                $anthor_total  =    $res['anthor_total'];
                $family_total  =    $res['family_total'];


                //家族长及时到账
                $familyhead_id    =    $res['family_id'];
                $familyhead_money =    $family_total-$anthor_total;

                //记录打赏列表
                $profitinsert = array(
                    'uid'=>$liveuid,   //直播会员ID
                    'status'=>'0',
                    'addtime'=>time(),
                    'anchor_tenant'=>$liveUserInfo['tenant_id'],
                    'send_tenant'=>$userInfo['tenant_id'],
                    'anchor_money'=>$tenant_total,
                    "send_money"=>$tenantuser_total,
                    'anthor_total' =>$anthor_total,
                    'family_total' =>$family_total,
                    'is_type'=>1,
                );

                $isprofig= DI()->notorm->profit_sharing->insert($profitinsert);

                /* 更新直播 魅力值 累计魅力值 */
                $istouid =DI()->notorm->users
                    ->where('id = ?', $liveuid)
                    ->update( array(
                        'votes' => new NotORM_Literal("votes + {$total}"),
                        'votestotal' => new NotORM_Literal("votestotal + {$total}")
                    ));
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
                if(!$showid){
                    $showid=0;
                }

                /* 写入记录 或更新 */
                $insert=array(
                    "type"=>$type,"action"=>$action,"uid"=>$uid,"touid"=>$liveuid,
                    "giftid"=>$giftid,"giftcount"=>$giftcount,"totalcoin"=>$total,
                    "showid"=>$showid,"addtime"=>$addtime,'tenant_id' =>$userInfo['tenant_id'],
                    'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],'anthor_total'=>$anthor_total,
                    'tenant_total'=>$tenant_total,'tenantuser_total'=>$tenantuser_total,'family_total'=>$family_total,
                    "cd_ratio"=>'1:'.floatval($config['money_rate']),'familyhead_total'=>$familyhead_money,
                );
                $isup=$coinrecordModel = new Model_Coinrecord();
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
                    "giftcount"=>$giftcount,
                    "totalcoin"=>$anthor_total*$config['money_rate'],
                    "showid"=>$showid,
                    "mark"=>$giftinfo['mark'],
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
                        "giftcount"=>$giftcount,
                        "totalcoin"=>$familyhead_money*$config['money_rate'],
                        "showid"=>$showid,
                        "mark"=>$giftinfo['mark'],
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
                    'amount'=>  $giftcount,
                    'content'=> '发送弹幕',
                    'money'=> round($total/$config['money_rate'],3),
                    'type'=>2,

                );

                //主播平台分润
                $this-> zhubo_tenant($liveUserInfo,$anchortenant_name,$tenant_total,$anchor_tenant_profit_ratio,$datadetail,$sendtenant_name);

                //消费平台分润
                $this-> xiaofei_tenant($userInfo,$sendtenant_name,$tenantuser_total,$user_tenant_profit_ratio,$datadetail);
                //主播分润
                $this->zhubo_update($liveUserInfo,$anthor_total,$res,$datadetail,$sendtenant_name);

                //家族长分润
                $this->family_update($res,$familyhead_money,$datadetail,$sendtenant_name);

                $userinfo2 =DI()->notorm->users
                    ->select('consumption,coin')
                    ->where('id = ?', $uid)
                    ->fetchOne();

                $level=getLevel($userinfo2['consumption']);

                /* 清除缓存 */
                delCache("userinfo_".$uid);
                delCache("userinfo_".$liveuid);
            }

            $votestotal=$this->getVotes($liveuid);

            $barragetoken=md5(md5($action.$uid.$liveuid.$giftid.$giftcount.$total.$showid.$addtime.rand(100,999)));

            $result=array("uid"=>$uid,"content"=>$content,"giftid"=>$giftid,"giftcount"=>$giftcount,"totalcoin"=>$total,"giftname"=>$giftinfo['giftname'],"gifticon"=>$giftinfo['gifticon'],"level"=>$level,"coin"=>$userinfo2['coin'],"votestotal"=>$votestotal,"barragetoken"=>$barragetoken);

            //调用余额更新接口
            $money_rate=$configpri['money_rate'];

            $useridGame=$userInfo['game_user_id'];
            $useridLive=$userInfo['id'];
            $tidGame=$userInfo['game_tenant_id'];
            $tidLive=$userInfo['tenant_id'];
            $usernickname=$userInfo['user_nicename'];
            //金额=钻石/转换比例四舍五入
            $amount=round($total/$money_rate,2) ;
            $diamond=$total;
            $type=1;
            $detail=$giftinfo['giftname'];
            $roomid=$liveuid;
            $anchorid=$liveuid;
            $anchorname=$liveUserInfo['user_nicename'];
            $anchorfromid=$liveUserInfo['tenant_id'];
            $anchorformname=$liveUserTenantInfo['name'];
            $tId=$userInfo['game_tenant_id'];
            $custId=$userInfo['game_user_id'];
            $custAnchorid=$liveUserInfo['game_user_id'];
            $anchorTenantid=$liveUserInfo['game_tenant_id'];

            if($total>0) {
                $updateResult = reduceGameUserBalance($useridGame, $useridLive, $tidGame, $tidLive, $usernickname, $amount, $diamond, $type, $detail, $roomid, $anchorid, $anchorname, $anchorfromid, $anchorformname, $tId, $custId,$custAnchorid,$anthor_total,$anchorTenantid,$familyhead_id,$familyhead_money,$level);
                if($updateResult['code']!=0){
                    rollbackTransaction();
                    //调用失败,回滚事务,并返回余额不足错误
                    return 1001;
                }
            }
            commitTransaction();
            // http 请求到golang的socketio
//            $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//            $http_post_res = http_post($url,['EventType' => 'ConsumptionChange','Message' => json_encode(['Uid'=>$uid,'Liveuid'=>$liveuid,'Level'=>getLevel($userInfo['consumption']),'Consumption'=>$userInfo['consumption'],'ExpCanSpeak'=>exp_can_speak($userInfo['consumption'])])]);

            return $result;
        } catch (Exception $ex){
	        rollbackTransaction();
            DI()->logger->error("发送弹幕异常:".$ex->getMessage());
                //调用失败,回滚事务,并返回余额不足错误
            return 1001;
        }
}
    /* 独立租户发送弹幕 */
    public function sendBarragealone($uid,$liveuid,$stream,$giftid,$giftcount,$content) {
        try {
            $redis = connectionRedis();
            $configpri = getConfigPri();

            $giftinfo = array(
                "giftname" => '弹幕',
                "gifticon" => '',
                "needcoin" => $configpri['barrage_fee'],
                "mark" => '0',
            );

            $total = $giftinfo['needcoin'] * $giftcount;

            $addtime = time();
            $type = 'expend';
            $action = 'sendbarrage';
            $liveUserInfo = getUserInfo($liveuid);
            $userInfo = getUserInfo($uid);
            $config = getConfigPub();

            if ($total > 0) {
                // 用户等级升级加成，根据加成的比例，增加用户等级经验
                $user_noble = getUserNoble($uid);
                $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($total,(1+$user_noble['upgrade_speed']/100),2) : $total;
                /* 更新用户余额 消费 */
                $ifok = DI()->notorm->users
                    ->where('id = ? and coin >=?', $uid, $total)
                    ->update(array(
                        'coin' => new NotORM_Literal("coin - {$total}"),
                        'consumption' => new NotORM_Literal("consumption + {$u_consumption}")
                    ));
                delUserInfoCache($uid);
                $userInfo = getUserInfo($uid);
                // 累计消费变动，通知前端
                $redis->zAdd('user_consumptionchange',1,json_encode(['uid'=>$uid,'liveuid'=>$liveuid,'level'=>getLevel($userInfo['consumption']),'consumption'=>$userInfo['consumption'],'exp_can_speak'=>exp_can_speak($userInfo['consumption'])]));
                if (!$ifok) {
                    /* 余额不足 */
                    return 1001;
                }

                /* 分销 */
                setAgentProfit($uid, $total);
                /* 分销 */

                //TODO 计算分润
                //判断送礼账号和主播账号是否是同一个平台
                $liveUserTenantInfo = getTenantInfo($liveUserInfo['tenant_id']);
                if ($liveUserTenantInfo['site_id'] == 1) { // 集成
                    if ($liveUserInfo['tenant_id'] == $userInfo['tenant_id']) {
                        //同平台主播所属租户分润比例
                        $anchor_platform_profit_ratio = $config['anchor_platform_profit_ratio'] / 100;
                        //同平台主播所属租户分润比例主播分润比例
                        $anchor_platform_ratio = $config['anchor_platform_ratio'] / 100;
                        //租户分润金额
                        $tenant_total = $anchor_platform_profit_ratio * $total;
                        //主播分润金额
                        $anthor_total = $anchor_platform_ratio * $total;
                    } else {
                        //主播所属租户分润比例
                        $anchor_tenant_profit_ratio = $config['anchor_tenant_profit_ratio'] / 100;
                        //消费者所属租户分润比例
                        $user_tenant_profit_ratio = $config['user_tenant_profit_ratio'] / 100;
                        //主播分润比例
                        $anchor_profit_ratio = $config['anchor_profit_ratio'] / 100;
                        //租户分润金额
                        $tenant_total = $anchor_tenant_profit_ratio * $total;
                        //消费者所属分润金额
                        $tenantuser_total = $user_tenant_profit_ratio * $total;
                        //主播分润金额
                        $anthor_total = $anchor_profit_ratio * $total;

                    }
                } else {
                    $anchor_tenant_profit_ratio = $config['anchor_tenant_profit_ratio'] / 100;
                    //消费者所属租户分润比例
                    $user_tenant_profit_ratio = $config['user_tenant_profit_ratio'] / 100;
                    //主播分润比例
                    $anchor_profit_ratio = $config['anchor_profit_ratio'] / 100;
                    //租户分润金额
                    $tenant_total = $anchor_tenant_profit_ratio * $total;
                    //消费者所属分润金额
                    $tenantuser_total = $user_tenant_profit_ratio * $total;
                    //主播分润金额
                    $anthor_total = $anchor_profit_ratio * $total;
                }

                /* 更新直播 魅力值 累计魅力值 */
                $istouid = DI()->notorm->users
                    ->where('id = ?', $liveuid)
                    ->update(array(
                        'votes' => new NotORM_Literal("votes + {$anthor_total}"),
                        'votestotal' => new NotORM_Literal("votestotal + {$anthor_total}"),
                        'coin' => new NotORM_Literal("coin + {$anthor_total}"),
                    ));
                delUserInfoCache($liveuid);
                $insert_votes = [
                    'type' => 'income',
                    'action' => $action,
                    'uid' => $liveuid,
                    'votes' => $total,
                    'addtime' => time(),
                    'tenant_id' => $liveUserInfo['tenant_id']
                ];
                DI()->notorm->users_voterecord->insert($insert_votes);

                $stream2 = explode('_', $stream);
                $showid = $stream2[1];
                if (!$showid) {
                    $showid = 0;
                }

                /* 写入记录 或更新 */
                $insert = array(
                    "type" => $type, "action" => $action, "uid" => $uid, "touid" => $liveuid,
                    "giftid" => $giftid, "giftcount" => $giftcount, "totalcoin" => $total,
                    "showid" => $showid, "addtime" => $addtime, 'tenant_id' => $userInfo['tenant_id'],
                    'anthor_total' => $anthor_total,
                    'tenant_total' => $tenant_total,
                    'tenantuser_total' => $tenantuser_total,
                    'receive_tenant_id' => $liveUserInfo['game_tenant_id'],
                );
                $isup = $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);

                /**
                 * 主播收入记录
                 */
                $insert = array(
                    "type" => 'income', "action" => $action, "uid" => $liveuid, "touid" => $liveuid,
                    "giftid" => $giftid, "giftcount" => $giftcount, "totalcoin" => $anthor_total,
                    "showid" => $showid, "mark" => $giftinfo['mark'], "addtime" => $addtime, 'tenant_id' => $liveUserInfo['tenant_id'],
                    'receive_tenant_id'=>$liveUserInfo['game_tenant_id'],
                );
                $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);

                $userinfo2 = DI()->notorm->users
                    ->select('consumption,coin')
                    ->where('id = ?', $uid)
                    ->fetchOne();

                $level = getLevel($userinfo2['consumption']);

                /* 清除缓存 */
                delCache("userinfo_" . $uid);
                delCache("userinfo_" . $liveuid);
            }

            $votestotal = $this->getVotes($liveuid);

            $barragetoken = md5(md5($action . $uid . $liveuid . $giftid . $giftcount . $total . $showid . $addtime . rand(100, 999)));

            $result = array("uid" => $uid, "content" => $content, "giftid" => $giftid, "giftcount" => $giftcount, "totalcoin" => $total, "giftname" => $giftinfo['giftname'], "gifticon" => $giftinfo['gifticon'], "level" => $level, "coin" => $userinfo2['coin'], "votestotal" => $votestotal, "barragetoken" => $barragetoken);
        }catch (Exception $ex){
            DI()->logger->error("发送弹幕异常:".$ex->getMessage());
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => ["发送弹幕异常:".$ex->getMessage()]);
        }
        // http 请求到golang的socketio
//        $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//        $http_post_res = http_post($url,['EventType' => 'ConsumptionChange','Message' => json_encode(['Uid'=>$uid,'Liveuid'=>$liveuid,'Level'=>getLevel($userInfo['consumption']),'Consumption'=>$userInfo['consumption'],'ExpCanSpeak'=>exp_can_speak($userInfo['consumption'])])]);

        return $result;
	}

	/* 设置/取消 管理员 */
	public function setAdmin($liveuid,$touid){
					
		$isexist=DI()->notorm->users_livemanager
					->select("*")
					->where('uid=? and  liveuid=?',$touid,$liveuid)
					->fetchOne();			
		if(!$isexist){
			$count =DI()->notorm->users_livemanager
						->where('liveuid=?',$liveuid)
						->count();	
			if($count>=5){
				return 1004;
			}		
			$rs=DI()->notorm->users_livemanager
					->insert(array("uid"=>$touid,"liveuid"=>$liveuid) );	
			if($rs!==false){
				return 1;
			}else{
				return 1003;
			}				
			
		}else{
			$rs=DI()->notorm->users_livemanager
				->where('uid=? and  liveuid=?',$touid,$liveuid)
				->delete();		
			if($rs!==false){
				return 0;
			}else{
				return 1003;
			}						
		}
	}
	
	/* 管理员列表 */
	public function getAdminList($liveuid){
		$rs=DI()->notorm->users_livemanager
						->select("uid")
						->where('liveuid=?',$liveuid)
						->fetchAll();	
		foreach($rs as $k=>$v){
			$rs[$k]=getUserInfo($v['uid']);
		}	

        $info['list']=$rs;
        $info['nums']=(string)count($rs);
        $info['total']='5';
		return $info;
	}
    
	/* 举报类型 */
	public function getReportClass(){
		return  DI()->notorm->users_report_classify
                    ->select("*")
					->order("orderno asc")
					->fetchAll();
	}
	
	/* 举报 */
	public function setReport($uid,$touid,$content){
	    $touUserInfo=getUserInfo($touid);
		return  DI()->notorm->users_report
				->insert(array("uid"=>$uid,"touid"=>$touid,'content'=>$content,'addtime'=>time() ) );	
	}
	
	/* 主播总映票 */
	public function getVotes($liveuid){
		$userinfo = getUserInfo($liveuid);
		return $userinfo['votestotal'];
	}
    /* 主播打赏氛围 */
    public function getReward($liveuid){

        $rewardsinfo=DI()->notorm->atmosphere_reward
            ->select("time_start,time_end,coin_start,coin_end")
            ->where('uid=?',$liveuid)
            ->fetchOne();
        if (!empty($rewardsinfo)){
            return $rewardsinfo;
        }else{
            return '';
        }

    }
	
	/* 超管关闭直播间 */
	public function superStopRoom($uid,$token,$liveuid,$type){
        $redis = connectionRedis();
		$userinfo=DI()->notorm->users
					->select("token,expiretime,issuper,tenant_id")
					->where('id=? ',$uid)
					->fetchOne();
		$liveUserInfo=getUserInfo($liveuid);
		if($userinfo['token']!=$token || $userinfo['expiretime']<time()){
			return 700;				
		} 	

		//非同租户超管无法关闭
		if($userinfo['issuper']==0 || $liveUserInfo['tenant_id']!=$userinfo['tenant_id']){
			return 1001;
		}
		
		if($type==1){
			/* 关闭并禁用 */
			DI()->notorm->users->where('id=? ',$liveuid)->update(array('user_status'=>0));
		}

        $info = DI()->notorm->users_live->where('uid=?',$liveuid)->fetchOne();

        /* 若存在未关闭的直播 关闭直播 */
        return $this->stopRoom($liveuid, $info['stream']);
//
//		$info=DI()->notorm->users_live
//				->select("uid,showid,starttime,title,province,city,stream,lng,lat,type,type_val,tenant_id,game_user_id")
//				->where('uid=? and islive="1"',$liveuid)
//				->fetchOne();
//		if($info){
//			$nowtime=time();
//			$stream=$info['stream'];
//			$info['endtime']=$nowtime;
//
//			$nums=DI()->redis->zCard('user_'.$stream);
//			DI()->redis->hDel("livelist",$liveuid);
//			DI()->redis->delete($liveuid.'_zombie');
//			DI()->redis->delete($liveuid.'_zombie_uid');
//			DI()->redis->delete('attention_'.$liveuid);
//			DI()->redis->delete('user_'.$stream);
//
//			$info['nums']=$nums;
//			$result=DI()->notorm->users_liverecord->insert($info);
//		}
//		DI()->notorm->users_live->where('uid=?',$liveuid)->delete();
//        $redis->zRem('watching_num'.$liveUserInfo['tenant_id'],$uid); // 当前租户的在线人数处理
//        delcache('live_watchtime_'.$stream.'*'); //清楚缓存的观看时间
//		return 0;
		
	}
    
    /* 获取用户本场贡献 */
    public function getContribut($uid,$liveuid,$showid){
        $sum=DI()->notorm->users_coinrecord
				->where('action="sendgift" and uid=? and touid=? and showid=? ',$uid,$liveuid,$showid)
				->sum('totalcoin');
        if(!$sum){
            $sum=0;
        }
        
        return (string)$sum;
    }
    /* 修改直播状态 */
    public function updateLivestatus($liveuid,$stream,$old_status,$status){
        $redis = connectionRedis();
        $config = getConfigPub();
        $islive=DI()->notorm->users_live
            ->select("uid,islive,type,type_val,starttime,tryWatchTime,title,avatar,stream")
            ->where('uid=? and stream=? and islive=?',$liveuid,$stream,$old_status)
            ->fetchOne();

        if(!$islive && $status == 1){ // 恢复直播，继续上次直播 -》 确定，通知前端
            $liveinfo = DI()->notorm->users_live->where('uid=? and stream=?',$liveuid,$stream) ->fetchOne();
            if($liveinfo){
                logapi(json_encode(array('uid'=>$liveuid,'stream'=>$stream,'time'=>date('Y-m-d H:i:s',time())),JSON_UNESCAPED_UNICODE),'手动恢复直播日志1');
                $redis->hSet('live_api_heart_time',$liveuid,time());
                $redis->zAdd('user_livestatustolive',1,json_encode(['uid'=>$liveuid]));
                // http 请求到golang的socketio
//                $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//                $http_post_res = http_post($url,['EventType' => 'LiveStatusToLive','Message' => json_encode(['Uid'=>$liveuid])]);
            }
        }
        if(!$islive || $islive['islive']==0){
            return 1005;
        }

        if($islive){
            $data['islive'] = intval($status);
            if($status == 2){
                $data['pause_time'] = time();
            }else{
                $data['recover_time'] = time();
            }
            $updatestatus =  DI()->notorm->users_live->where('uid=? ',$liveuid)->update($data);
            delPatternCacheKeys(getTenantId().'_'."getHot_"."*");
            if($status == 1){ // 恢复直播，继续上次直播 -》 确定，通知前端
                $redis->hSet('live_api_heart_time',$liveuid,time());
                $redis->zAdd('user_livestatustolive',1,json_encode(['uid'=>$liveuid]));
                logapi(json_encode(array('uid'=>$liveuid,'stream'=>$stream,'time'=>date('Y-m-d H:i:s',time())),JSON_UNESCAPED_UNICODE),'手动恢复直播日志2');

                // http 请求到golang的socketio
//                $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//                $http_post_res = http_post($url,['EventType' => 'LiveStatusToLive','Message' => json_encode(['Uid'=>$liveuid])]);

                //更新暂停直播统计时长
                $stopinfo =DI()->notorm->users_stoplog->select("pause_time")->where(' stream=? and status=0 ',$stream)->fetchOne();
                if($stopinfo){
                    $resdata['recover_time'] = time();
                    $resdata['stop_time'] = time()-$stopinfo['pause_time'];
                    $resdata['status'] = 1;
                    DI()->notorm->users_stoplog->where('stream=? and status=0 ',$islive['stream'])->update($resdata);

                }

                /*
                 * 恢复直播统计累计暂停时长
                 */
                //已经恢复的直播
                $stoptime_alread =    DI()->notorm->users_stoplog->where(['stream'=>$islive['stream'],'status=1'])->sum('stop_time');;
                $stoptime_alread =   $stoptime_alread ? $stoptime_alread:0;

                //更新直播记录表的暂停时长字段
                DI()->notorm->users_live->where(['uid'=>$liveuid])->update(['stop_time'=>$stoptime_alread]);
                DI()->notorm->liveing_log->where(['stream'=>$islive['stream']])->update(['stop_time'=>$stoptime_alread]);

                $rs['recovertime']=time()-$islive['starttime']-$stoptime_alread;
            }else{
                //写入暂停日志表，用于统计暂停时长
                $resdata['uid'] =  $liveuid;
                $resdata['stream'] =  $islive['stream'];
                $resdata['pause_time'] =  time();
                $resdata['status'] =  0;
                DI()->notorm->users_stoplog->insert($resdata);
                logapi(json_encode(array('uid'=>$liveuid,'stream'=>$stream,'time'=>date('Y-m-d H:i:s',time())),JSON_UNESCAPED_UNICODE),'手动暂停直播日志');

                logapi(json_encode($resdata,JSON_UNESCAPED_UNICODE),'手动暂停写入暂停时间');
            }
        }

        $rs['type']=$islive['type'];
        $rs['type_val']=$islive['type_val'];
        $rs['tryWatchTime']=$islive['tryWatchTime'];
        $rs['title']=$islive['title'];

        setUserLiveListCache($liveuid); // 设置直播列表缓存

        return $rs;

    }
    /* 获取是否存在直播 */
    public function confirmLivestatus($liveuid,$version){
        //更新version值
        $userinfo = getUserInfo($liveuid);
        $update_data = array();
        if(isset($userinfo['version']) && $userinfo['version'] != $version && $version != null){
            $update_data['version'] = $version;
        }

        if(!empty($update_data)){
            DI()->notorm->users->where(['id'=>intval($liveuid)])->update($update_data);
            delUserInfoCache($liveuid);
        }

        $islive=DI()->notorm->users_live
            ->select("islive,isvideo,type,type_val,starttime,tryWatchTime,title,stream,pushpull_id,push,pull,is_football,football_live_time_stamp")
            ->where('uid=?',$liveuid)
            ->fetchOne();
        if (!$islive){
            return 2038;
        }

        if(($islive && $islive['islive']==1) || ($islive && $islive['islive']==2) ){
            //主播开播前检测有满足条件直播间的时候，获取token值，并且更新token值的数据
            $userinfo = getUserInfo($liveuid);
            $tokenifnfo = getcache('token_'.$liveuid);
            DI()->redis  -> set($tokenifnfo['token'],json_encode($userinfo));
            if(($islive && $islive['islive']==1)  ){
                DI()->notorm->users_live->where(['uid'=>intval($liveuid)])->update(['islive'=>2,'pause_time'=>time()]);
                $islive['islive'] = 2;
                setUserLiveListCache($liveuid); // 设置直播列表缓存
            }
            return $islive;
        }

        return 2058;
    }
    /* 获取是否存在直播 */
    public function confirmLive($liveuid){
        $islive=DI()->notorm->users_live
            ->select("islive,isvideo,type,type_val,starttime,tryWatchTime,title,stream,pushpull_id,push,pull")
            ->where('uid=?',$liveuid)
            ->fetchOne();
        if (!$islive){
            return 2038;
        }

        if(($islive && $islive['islive']==1) || ($islive && $islive['islive']==2) ){
            //主播开播前检测有满足条件直播间的时候，获取token值，并且更新token值的数据
            $userinfo = getUserInfo($liveuid);
            $tokenifnfo = getcache('token_'.$liveuid);
            DI()->redis  -> set($tokenifnfo['token'],json_encode($userinfo));
            return $islive;
        }

        return 2058;
    }
    /* 获取用户本场贡献 */
    public function getBetinfo($tenant_id){
        $betinfo =DI()->notorm->bet_config
            ->where('tenant_id=? ',$tenant_id)
            ->fetchAll();

       $randbetid =  rand(0,count($betinfo)-1);
       return $betinfo[$randbetid];
    }

    public  function liveInfo($liveuid){
        $islive=DI()->notorm->users_live
            ->select("uid,lottery_id,recommend_lottery_id,show_game_entry,show_offers,show_dragon_assistant,show_reward_reporting,enable_follow")
            ->where('uid=?',$liveuid)
            ->fetchOne();
        $lottery = DI()->notorm->lottery_config ->where("id in ({$islive['lottery_id']})")->fetchAll();
        $islive['lottery_array']=  $lottery;
        $recommend_lottery = DI()->notorm->lottery_config ->where("id in ({$islive['recommend_lottery_id']})")->fetchAll();
        $islive['recommend_lottery_array'] = $recommend_lottery;
        return $islive;

    }
    /* 创建房间 */
    public function liveset($data) {
        $isexist=DI()->notorm->liveing_set
            ->select("uid")
            ->where('uid=?',$data['uid'])
            ->fetchOne();
        if($isexist){
            return 1001;
        }else{
            /* 加入 */
            $rs=DI()->notorm->liveing_set->insert($data);
        }


        if(!$rs){
            return $rs;
        }
        return 1;
    }

    public function autoUpdatevotes() {
        $rewardinfo=DI()->notorm->atmosphere_reward
            ->select("*")
            ->fetchAll();
        if (!empty($rewardinfo)){
            foreach ($rewardinfo as $key => $value){
                $coin_add =  rand($value['coin_start'],$value['coin_end']);
                $uidcoin = DI()->redis->hGet("autovotes",$value['uid']);
                DI()->redis->hSet("autovotes",$value['uid'],$coin_add+$uidcoin);
            }
        }
      /*  $stop_list = DI()->notorm->users_live->select('uid,stream')->where(['islive'=>2,'isvideo'=>0])->fetchAll();

        if($stop_list){
            foreach ($stop_list as $key=>$val){

             //已经恢复的直播
             $stoptime_alread =    DI()->notorm->users_stoplog->where(['stream'=>$val['stream'],'status=1'])->sum('stop_time');;
             //暂停中的直播
             $stoptime_stopping =    DI()->notorm->users_stoplog->select('uid,pause_time')->where(['stream'=>$val['stream'],'status=0'])->fetchOne();;
             if($stoptime_stopping){
                 $stoptime = time()-$stoptime_stopping['pause_time'];
             }else{
                 $stoptime = 0;
             }
             $stop_time = $stoptime+$stoptime_alread;
             //更新直播记录表的暂停时长字段
             DI()->notorm->users_live->where(['uid'=>intval($val['uid'])])->update(['stop_time'=>$stop_time]);

            }
        }*/

        return 1;
    }
    public function keywordcheck($uid,$content) {
        $tenant_id = getTenantId();
        $keywordinfo = DI()->notorm->user_keywordset->select("*")->where('tenant_id = ? ', intval($tenant_id))->fetchOne();

        $shutup_keyword = DI()->redis -> hGet( 'shutup_keyword', $uid);
        $outroom_keyword = DI()->redis -> hGet( 'outroom_keyword', $uid);

        //禁言次数 次数
        if(isset($keywordinfo['shut_times']) && $keywordinfo['shut_times'] > 0 && $keywordinfo['shut_times'] <= $shutup_keyword){
            return 1002;
        }

        // 触发踢出房间 次数
        if(isset($keywordinfo['shut_times']) && $keywordinfo['outroom_times'] > 0 && $keywordinfo['outroom_times'] <= $outroom_keyword){
            return 1003;
        }

        if(empty($keywordinfo)){
            return 200;
        }else{
            $keywordinfo = DI()->notorm->user_keywordset->select("*")->where('tenant_id = ? and content = ?', intval($tenant_id), trim($content))->fetchOne();

            $content = str_replace(" ", "",  trim($content));
            //查询是否为空
            if(empty($keywordinfo['content'])){
                return 200;
            }else{
               $keylist =  explode(',',$keywordinfo['content']);

               foreach ($keylist as $key =>$value){
                   //匹配字符串
                   if (preg_match("/^.*(?i)".$value.".*$/", $content)) {
                       //触发后写入数据库，记录触发次数
                       $res = $this->updatekeyword($uid,$content,$keywordinfo);
                       return $res;
                   } else {
                       continue;
                   }
               }
                return 200;
            }
        }
        return 200;
    }
    /*
     * 触发关键字写入数据库
     */
    public  function updatekeyword($uid,$content,$keywordinfo){
        //获取累计的禁言次数和剔除房间次数
        $shutup_keyword = DI()->redis -> hGet( 'shutup_keyword',$uid);
        $outroom_keyword = DI()->redis -> hGet( 'outroom_keyword',$uid);


        $userInfo = getUserInfo($uid);
        $data['tenant_id'] = intval($userInfo['tenant_id']);
        $data['uid'] = $uid;
        $data['user_name'] = $userInfo['user_login'];
        $data['shut_times'] = $shutup_keyword+1;
        $data['outroom_times'] = $outroom_keyword+1;
        $data['content'] = $content;
        $data['addtime'] = time();
        $rs=DI()->notorm->user_keyword->insert($data);
        //更新redis数据

        if($keywordinfo['shut_times'] <= $shutup_keyword+1){
            DI()->redis -> hSet( 'shutup_keyword',$uid,$shutup_keyword+1);
            DI()->redis -> hSet( 'outroom_keyword',$uid,$outroom_keyword+1);
            return 1002;
        }
        if($keywordinfo['outroom_times'] <= $outroom_keyword+1){
            DI()->redis -> hSet( 'shutup_keyword',$uid,$shutup_keyword+1);
            DI()->redis -> hSet( 'outroom_keyword',$uid,$outroom_keyword+1);
            return 1003;
        }

        DI()->redis -> hSet( 'shutup_keyword',$uid,$shutup_keyword+1);
        DI()->redis -> hSet( 'outroom_keyword',$uid,$outroom_keyword+1);

        CustRedis::getInstance()->expire('shutup_keyword', 60*60*7);
        CustRedis::getInstance()->expire('shutup_keyword', 60*60*7);

        return 1001;

    }

    public function addAnchornamecard($uid,$declaration,$position,$limit_price,$telephone,$is_open,$type) {
        $data['uid'] =$uid;
        $data['declaration'] =$declaration;
        $data['position'] =$position;
        $data['limit_price'] = $limit_price;
        $data['telephone'] = $telephone;
        $data['addtime'] = time();
        $data['is_open'] = $is_open;
        $data['type'] = $type;

        $isexist=DI()->notorm->user_authinfo
            ->select("uid")
            ->where('uid=?',$uid)
            ->fetchOne();
        if($isexist){
            $rs=DI()->notorm->user_authinfo->where('uid = ?', $uid)->update($data);
        }else{
            $rs=DI()->notorm->user_authinfo->insert($data);
        }

        return 200;


    }

    public function getAnchornamecards($uid,$liveuid) {
        $authinfo=DI()->notorm->user_authinfo
            ->select("*")
            ->where('uid=?',$liveuid)
            ->fetchOne();
        //获取该会员对该主播的送礼值
        $userinfo=DI()->notorm->users_coinrecord
            ->where("uid=? and touid=? and type='expend' and action = 'sendgift'",$uid,$liveuid)
            ->sum('totalcoin');

        if (empty($authinfo)){
            return  1002;
        }else{
            $authinfo['consumption'] = isset($userinfo)?$userinfo:0.00;
          /*  if($authinfo['consumption']<$authinfo['limit_price']){
                  return  1001;
            }*/
        }
        return $authinfo;


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

    public function getCarList($uid)
    {
        $list = get_carlist(getTenantId());
        $data = array();
        foreach ($list as $key=>$val){
            $data[] = array(
                'id'    => $val['id'],
                'name'  => $val['name'],
                'thumb' => $val['thumb'],
                'swf'   => $val['swf'],
                'swftime'   => $val['swftime'],
                'needcoin'  => $val['needcoin'],
                'words'     => $val['words'],
                'type'     => $val['type'],
                );
        }
        return array('code' => 0, 'msg' => '', 'info' => $data);
    }

    public function getUserCarList($uid)
    {
        $list = get_user_carlist($uid);
        $data = array();
        $user_noble = getUserNoble($uid);
        $no = true;
        foreach ($list as $key=>$val){
            if($val['type'] == 1){
                $no = false;
            }
            $carinfo = get_carlist(getTenantId(),$val['carid']);
            if($val['carid'] != 0 && empty($carinfo)){
                continue;
            }
            if($val['type'] == 1 && is_array($user_noble)){
                if($val['carid'] == 0){
                    DI()->notorm->users_car->where(['id'=>$val['id']])->update(['carid' => $user_noble['car_id']]);
                }
                if($user_noble['exclu_car'] == 0){
                    continue;
                }
                $val['carid'] = $user_noble['car_id'];
                $carinfo['name'] = $user_noble['exclu_car_name'];
                $carinfo['thumb'] = $user_noble['exclu_car_bagicon'];
                $carinfo['swf'] = $user_noble['exclu_car_swf'];
                $carinfo['swftime'] = $user_noble['exclu_car_swftime'];
                $carinfo['needcoin'] = $user_noble['price'];
                $carinfo['words'] = $user_noble['exclu_car_words'];
            }
            $data[] = array(
                'id'    => $val['id'],
                'carid' => $val['carid'],
                'endtime' => $val['endtime'],
                'lefttime'=> ($val['endtime'] - time()),
                'status'  => $val['status'],
                'name'  => $carinfo['name'],
                'thumb' => $carinfo['thumb'],
                'swf'   => $carinfo['swf'],
                'swftime'   => $carinfo['swftime'],
                'needcoin'  => $carinfo['needcoin'],
                'words'     => $carinfo['words'],
                'type'      => $val['type'],
            );
        }
        if($no === true && is_array($user_noble) && $user_noble['exclu_car'] != 0){
            // 加入到用户坐骑列表或更新结束时间
            $usercar_info = DI()->notorm->users_car->where('uid=? and type=1',$uid)->fetchOne();
            if(!$usercar_info){
                // 加入到用户坐骑列表
                $usercar_info = DI()->notorm->users_car->insert([
                    'uid' => $uid,
                    'carid' => $user_noble['car_id'],
                    'endtime' => $user_noble['etime'],
                    'status' => 0,
                    'addtime' => time(),
                    'tenant_id' => getTenantId(),
                    'type' => 1,
                ]);
                delUserCarlistCache($uid);
                $data[] = array(
                    'id'    => $usercar_info['id'],
                    'carid' => $usercar_info['car_id'],
                    'endtime' => $usercar_info['endtime'],
                    'lefttime'=> ($usercar_info['endtime'] - time()),
                    'status'  => $usercar_info['status'],
                    'name'  => $user_noble['exclu_car_name'],
                    'thumb' => $user_noble['exclu_car_bagicon'],
                    'swf'   => $user_noble['exclu_car_swf'],
                    'swftime'   => $user_noble['exclu_car_swftime'],
                    'needcoin'  => $user_noble['price'],
                    'words'     => $user_noble['exclu_car_words'],
                    'type'      => $usercar_info['type'],
                );
            }
        }

        return array('code' => 0, 'msg' => '', 'info' => $data);
    }

    /*
     * 彩票租户购买坐骑
     * */
    public function buyCar($uid,$carid)
    {
        try {
            //开始数据库事务
            beginTransaction();
            $carinfo = get_carlist(getTenantId(),$carid);
            if(!$carinfo){
                return array('code' => 2055, 'msg' => codemsg('2055'), 'info' => []);
            }
            $total = $carinfo['needcoin'];
            $userInfo = getUserInfo($uid);
            //查询余额
            $coinInfo=getGameUserBalance($userInfo['game_tenant_id'],$userInfo['game_user_id']);
            if($coinInfo['code']!=0){ //如果code不等于0为请求失败， //请求失败时余额返回0
                $coin=0;
            }else{
                $coin=$coinInfo['coin'];
            }
            if($coin<$total){ //余额不足
                return array('code' => 2006, 'msg' => codemsg('2006'), 'info' => [$coin,$total]); // 金币不足,这时候需要用户去充值
            }

            $config = getConfigPub();

            $usercar = DI()->notorm->users_car->select("*")->where('uid=? and carid=?',$uid,$carid)->fetchOne();
            if($usercar){
                $end_time = $usercar['endtime'] > time() ? strtotime('+1 month',$usercar['endtime']) : strtotime('+1 month',time());
                $res = DI()->notorm->users_car->where('uid=? and carid=?',$uid,$carid)->update(['endtime'=>$end_time]);
            }else{
                $end_time = strtotime('+1 month',time());
                $data = array(
                    'uid' => $uid,
                    'carid' => $carid,
                    'endtime' => $end_time,
                    'status' => 0,
                    'addtime' => time(),
                    'tenant_id' => getTenantId(),
                );
                $res = DI()->notorm->users_car->insert($data);;
            }
            if(!$res){
                return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => []);
            }
            delUserCarlistCache($uid);
            // 用户等级升级加成，根据加成的比例，增加用户等级经验
            $user_noble = getUserNoble($uid);
            $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($total,(1+$user_noble['upgrade_speed']/100),2) : $total;
            //增加消费总额
            DI()->notorm->users->where('id=?',$uid)->update(['consumption' => new NotORM_Literal("consumption + {$u_consumption}")]);
            $insert=array(
                "type"=>'expend',
                "action"=>'buycar',
                "uid"=>$uid,
                "totalcoin"=>$carinfo['needcoin'],
                "addtime"=>time(),
                'tenant_id' =>getTenantId(),
                "cd_ratio"=>'1:'.floatval($config['money_rate']),
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);

            $config=getConfigPub();
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
            $detail='坐骑购买';
            $roomid='';
            $anchorid='';
            $anchorname='';
            $anchorfromid='';
            $anchorformname='';
            $tId=$userInfo['game_tenant_id'];
            $custId=$userInfo['game_user_id'];
            $custAnchorid=0; // 不需要给主播分成，传0
            $anchorTenantid=0; // 不需要给主播分成，传0
            $anthor_total = '';

            $updateResult= reduceGameUserBalance($useridGame,$useridLive,$tidGame,$tidLive,$usernickname,$amount,$diamond,$type,$detail,$roomid,$anchorid,$anchorname,$anchorfromid,$anchorformname,$tId,$custId,$custAnchorid,$anthor_total,$anchorTenantid);
            if($updateResult['code']!=0){
                rollbackTransaction();
                //调用失败,回滚事务,并返回网络错误
                return array('code' => 2057, 'msg' => codemsg('2057'), 'info' => [$updateResult]);
            }
            commitTransaction();
        }catch (Exception $ex){
            rollbackTransaction();
            DI()->logger->error("购买坐骑异常: ".$ex->getMessage());
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => ["购买坐骑异常: ".$ex->getMessage()]);
        }
        delUserInfoCache($uid);
        return array('code' => 0, 'msg' => '', 'info' => []);
    }

    /*
     * 独立租户购买坐骑
     * */
    public function buyCaralone($uid,$carid)
    {
        $carinfo = get_carlist(getTenantId(),$carid);
        if(!$carinfo){
            return array('code' => 2055, 'msg' => codemsg('2055'), 'info' => []);
        }
        $total = $carinfo['needcoin'];
        $user_info = getUserInfo($uid);
        if($user_info['coin'] * 100 < $carinfo['needcoin'] * 100){
            return array('code' => 2006, 'msg' => codemsg('2006'), 'info' => [$user_info['coin'],$total]); // 金币不足,这时候需要用户去充值
        }

        $usercar = DI()->notorm->users_car->select("*")->where('uid=? and carid=?',$uid,$carid)->fetchOne();
        if($usercar){
            $end_time = $usercar['endtime'] > time() ? strtotime('+1 month',$usercar['endtime']) : strtotime('+1 month',time());
            $res = DI()->notorm->users_car->where('uid=? and carid=?',$uid,$carid)->update(['endtime'=>$end_time]);
        }else{
            $end_time = strtotime('+1 month',time());
            $data = array(
                'uid' => $uid,
                'carid' => $carid,
                'endtime' => $end_time,
                'status' => 0,
                'addtime' => time(),
                'tenant_id' => getTenantId(),
            );
            $res = DI()->notorm->users_car->insert($data);;
        }
        if(!$res){
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => []);
        }
        delUserCarlistCache($uid);
        // 用户等级升级加成，根据加成的比例，增加用户等级经验
        $user_noble = getUserNoble($uid);
        $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($total,(1+$user_noble['upgrade_speed']/100),2) : $total;
        //增加消费总额，自身余额减少
        DI()->notorm->users->where('id=?',$uid)->update([
            'coin' => new NotORM_Literal("coin - {$total}"),
            'consumption' => new NotORM_Literal("consumption + {$u_consumption}"),
        ]);
        $insert=array(
            "type"=>'expend',
            "action"=>'buycar',
            "uid"=>$uid,
            "totalcoin"=>$carinfo['needcoin'],
            "addtime"=>time(),
            'tenant_id' =>getTenantId(),
        );
        $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);
        delUserInfoCache($uid);
        return array('code' => 0, 'msg' => '', 'info' => []);
    }

    public function rideCar($uid,$id)
    {
        DI()->notorm->users_car->where('uid=?',$uid)->update(['status'=>0]);
        $res = DI()->notorm->users_car->where('uid=? and id=?',$uid,$id)->update(['status'=>1]);
        if(!$res){
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => []);
        }
        delUserCarlistCache($uid);
        return array('code' => 0, 'msg' => '', 'info' => []);
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

    public function getroomtype($uid)
    {
        $labels = array(
            '0'=>'普通房间',
            '1'=>'密码房间',
            '2'=>'门票房间',
            '3'=>'计时房间',
        );
        $userinfo  = DI()->notorm->users->select("roomtype_name")->where(array("id" => $uid))->fetchOne();
        $roomtype_name = $userinfo['roomtype_name'];

        $config = getConfigPub();
        $live_type = array();
        foreach ($config['live_type'] as $key=>$val){
            if(isset($labels[$key])){
                array_push($live_type,$val[0]);
            }
        }

        $res = [];
        if ($roomtype_name!=''){
            $roomtype_name = explode(',', $roomtype_name);
            foreach ($roomtype_name as $key=>$val){
                if(isset($labels[$val]) && in_array($val,$live_type)){
                    array_push($res,$labels[$val]);
                }
            }
         }
         return $res;
    }

    public function leaveRoom($uid,$liveuid,$stream,$watchtime){
        $redis = connectionRedis();
        $tenantId = getTenantId();

        //退出房间，统计总共观看时长
        $start_watch= $redis->hGet('user_watchtime',$uid);
        if($start_watch){
            $user_watchtime = time()-$start_watch;
            $redis->hDel('user_watchtime',$uid);
            DI()->notorm->users
                ->where('id = ?', $uid)
                ->update(array('watchtime' => new NotORM_Literal("watchtime + {$user_watchtime}") ));
        }

        $redis->zRem('watching_num'.$tenantId,$uid); // 当前租户的在线人数处理

        $live_watchtime_key = 'live_watchtime_'.$stream.$uid;
        $r_watchtime = $redis->get($live_watchtime_key);

        if($r_watchtime === false){
            return array('code' => 0, 'msg' => '', 'info' => array(($r_watchtime-$watchtime)));
        }
        $redis->decrBy($live_watchtime_key,$watchtime);

        return array('code' => 0, 'msg' => '', 'info' => array(($r_watchtime-$watchtime)));
    }

    public function getLiveGameInfo($uid,$liveuid){
        try{
            $redis = connectionRedis();
            $tenant_id = getTenantId();

            $list = DI()->notorm->liveing_set->select("*")->where(" tenant_id=? ",$tenant_id)->order("id desc")->fetchAll();
            $info = getLiveGameInfo($list,$liveuid,$tenant_id);

            return array('code' => 0, 'msg' => '', 'info' => array($info));
        }catch (\Exception $e){
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => array($e->getMessage()));
        }

        return array('code' => 0, 'msg' => '', 'info' => array());
    }

    public function getEnterroomNotice($uid,$language_id){
        $redis = connectionRedis();
        $tenant_id = getTenantId();

        $data = getEnterroomNotice($tenant_id);
        foreach ($data as $key=>$val){
            $data[$key] = str_replace("\n","<br>",$val);
        }

        $languagetype = getLanguageType($language_id);
        if($language_id && $languagetype){
            $notice = $data[getLanguageType($language_id)];
        }else{
            $notice = $data;
        }
        return array('code' => 0, 'msg' => $redis->get('http_root_url'), 'info' => array('notice'=>$notice));

    }

    /* 获取直播信息 */
    public function getLiveInfo($liveuid){
         $info=DI()->notorm->users_live
                ->select("*")
                ->where('uid=?',$liveuid)
                ->fetchOne();
        return empty($info) ? array() : $info;
    }

    /* 获取贵族列表 */
    public function getNobleList($uid){
        $redis = connectionRedis();
        $tenant_id = getTenantId();

        $list = getNobleList($tenant_id);
        $list = empty($list) ? array() : $list;

        $carlist = get_carlist(getTenantId());
        $carlist = count($carlist) > 0 ? array_column($carlist,null,'id') : [];

        $info = array();
        foreach ($list as $key=>$val){
            unset($val['id']);
            unset($val['act_uid']);
            unset($val['tenant_id']);
            unset($val['ctime']);
            unset($val['mtime']);
            
            $car_info = isset($carlist[$val['car_id']]) ? $carlist[$val['car_id']] : [];
            $val['exclu_car_bagicon'] = isset($car_info['thumb']) ? $car_info['thumb'] : '';
            $val['exclu_car_swf'] = isset($car_info['swf']) ? $car_info['swf'] : '';
            $val['exclu_car_swftime'] = isset($car_info['swftime']) ? $car_info['swftime'] : '';
            $val['exclu_car_words'] = isset($car_info['words']) ? $car_info['words'] : '';

            $val['exclu_allnum'] = 12;
            $val['exclu_currnum'] = 0;

            if(!empty($val['medal'])){
                $val['exclu_currnum'] += 1;
            }
            if($val['special_effect'] == 1){
                $val['exclu_currnum'] += 1;
            }
            if($val['golden_light'] == 1){
                $val['exclu_currnum'] += 1;
            }
            if($val['exclu_custsevice'] == 1){
                $val['exclu_currnum'] += 1;
            }

            if(!empty($val['avatar_frame'])){
                $val['exclu_currnum'] += 1;
            }
            if($val['upgrade_speed'] > 0){
                $val['exclu_currnum'] += 1;
            }
            if($val['broadcast'] == 1){
                $val['exclu_currnum'] += 1;
            }
            if(!empty($val['pubchat_bgskin'])){
                $val['exclu_currnum'] += 1;
            }

            if($val['enter_stealth'] == 1){
                $val['exclu_currnum'] += 1;
            }
            if($val['exclu_car'] == 1){
                $val['exclu_currnum'] += 1;
            }
            if($val['ranking_stealth'] == 1){
                $val['exclu_currnum'] += 1;
            }
            if($val['prevent_mute'] == 1){
                $val['exclu_currnum'] += 1;
            }

            array_push($info,$val);
        }

        return array('code' => 0, 'msg' => '', 'info' => $info);
    }

    /*
     * 集成租户开通贵族或续费
     * */
    public function buyNoble($uid,$liveuid,$level,$type)
    {
        $redis = connectionRedis();
        $tenant_id = getTenantId();
        $noble_info = getNobleList($tenant_id,$level);
        if(!$noble_info){
            return array('code' => 2055, 'msg' => codemsg('2055'), 'info' => []);
        }
        $users_noble_info = DI()->notorm->users_noble->where(['uid'=>intval($uid)])->fetchOne();
        $user_info = getUserInfo($uid);

        logapi(array_merge($_GET,$_POST, $user_info),'【集成租户开通贵族或续费】');

        try{
            beginTransaction();
            $stime = time();
            $etime = strtotime ("+1 month", $stime);
            $config = getConfigPub();
            //查询余额
            $coinInfo=getGameUserBalance($user_info['game_tenant_id'],$user_info['game_user_id']);
            if($coinInfo['code']!=0){ //如果code不等于0为请求失败， //请求失败时余额返回0
                $coin=0;
            }else{
                $coin=$coinInfo['coin'];
            }
            $price = $type == 1 ? $noble_info['price'] : $noble_info['renewal_price'];
            $buyNobleType = $type;
            $handsel = $type == 1 ? $noble_info['handsel'] : $noble_info['renewal_handsel']; // 赠送
            if($coin < $price){
                return array('code' => 2026, 'msg' => codemsg('2026'), 'info' => []);
            }
            $users_noble_log_type = 0; // 开通方式：1.正常开通，2.续费，3.升级
            if($type == 1){
                if($users_noble_info){
                    if($users_noble_info['level'] > $noble_info['level'] && $users_noble_info['etime'] > time()){
                        return array('code' => 2093, 'msg' => codemsg('2093'), 'info' => []);
                    }
                    if($users_noble_info['level'] == $noble_info['level'] && $users_noble_info['etime'] > time()){
                        return array('code' => 2094, 'msg' => codemsg('2094'), 'info' => []);
                    }
                    $users_noble_log_type = 3;
                    $res = DI()->notorm->users_noble->where(['id'=>$users_noble_info['id']])->update([
                        'noble_id' => $noble_info['id'],
                        'level' => $noble_info['level'],
                        'stime' => $stime,
                        'etime' => $etime,
                        'mtime' => time(),
                    ]);
                }else{
                    $users_noble_log_type = 1;
                    $res = DI()->notorm->users_noble->insert([
                        'uid' => $uid,
                        'user_login' => $user_info['user_login'],
                        'game_user_id' => $user_info['game_user_id'] ? $user_info['game_user_id'] : 0,
                        'noble_id' => $noble_info['id'],
                        'level' => $noble_info['level'],
                        'tenant_id' => $user_info['tenant_id'],
                        'stime' => $stime,
                        'etime' => $etime,
                        'ctime' => time(),
                    ]);
                }
            }else{
                if(!$users_noble_info){
                    return array('code' => 2095, 'msg' => codemsg('2095'), 'info' => []);
                }
                if($users_noble_info['level'] != $noble_info['level']){
                    return array('code' => 2096, 'msg' => codemsg('2096'), 'info' => []);
                }
                if($users_noble_info['etime'] > time()){
                    $stime = $users_noble_info['etime'];
                    $etime = strtotime ("+1 month", $stime);
                }
                $users_noble_log_type = 2;
                $res = DI()->notorm->users_noble->where(['id'=>$users_noble_info['id']])->update([
                    'noble_id' => $noble_info['id'],
                    'level' => $noble_info['level'],
                    'stime' => $stime,
                    'etime' => $etime,
                    'mtime' => time(),
                ]);
            }
            if($res){
                // 开通记录
                DI()->notorm->users_noble_log->insert([
                    'uid' => $uid,
                    'user_login' => $user_info['user_login'],
                    'game_user_id' => $user_info['game_user_id'] ? $user_info['game_user_id'] : 0,
                    'noble_id' => $noble_info['id'],
                    'level' => $noble_info['level'],
                    'type' => $users_noble_log_type,
                    'price' => $price,
                    'handsel' => $handsel,
                    'tenant_id' => $user_info['tenant_id'],
                    'ctime' => time(),
                ]);
                // 用户等级升级加成，根据加成的比例，增加用户等级经验
                $user_noble = getUserNoble($uid);
                $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($price,(1+$user_noble['upgrade_speed']/100),2) : $price;
                //增加消费总额
                DI()->notorm->users->where('id=?',$uid)->update(['consumption' => new NotORM_Literal("consumption + {$u_consumption}")]);

                // 加入到用户坐骑列表或更新结束时间
                $usercar_info = DI()->notorm->users_car->where('uid=? and type=1',$uid)->fetchOne();
                if($usercar_info){
                    DI()->notorm->users_car->where('id=? ',$usercar_info['id'])->update(['carid'=>$noble_info['car_id'],'endtime'=>$etime]);
                }else{
                    DI()->notorm->users_car->insert([
                       'uid' => $uid,
                       'carid' => $noble_info['car_id'],
                       'endtime' => $etime,
                       'status' => 0,
                       'addtime' => time(),
                       'tenant_id' => getTenantId(),
                       'type' => 1,
                   ]);
                }
                // 有专属座驾为 是，则开通后自动使用爵位专属坐骑
                if($noble_info['exclu_car'] == 1){
                   DI()->notorm->users_car->where('uid=?',$uid)->update(['status'=>0]);
                   DI()->notorm->users_car->where('uid=? and type=1',$uid)->update(['status'=>1]);
               }
                $insert=array(
                    "type"=>'expend',
                    "action" => $type == 1 ? 'buyNoble' : 'renewalNoble', // buyNoble 开通贵族, renewalNoble 续费贵族
                    "uid"=>$uid,
                    "totalcoin"=>$price,
                    "addtime"=>time(),
                    'tenant_id' =>$tenant_id,
                    "cd_ratio"=>'1:'.floatval($config['money_rate']),
                );
                Model_Coinrecord::getInstance()->addCoinrecord($insert);

                $config=getConfigPub();
                //调用余额更新接口
                $money_rate=$config['money_rate'];
                $total = $price;

                $useridGame=$user_info['game_user_id'];
                $useridLive=$user_info['id'];
                $tidGame=$user_info['game_tenant_id'];
                $tidLive=$user_info['tenant_id'];
                $usernickname=$user_info['user_nicename'];
                //金额=钻石/转换比例四舍五入
                $amount=round($total/$money_rate,2) ;
                $diamond=$total;
                $type=1;
                $detail='贵族购买';
                $roomid='';
                $anchorid='';
                $anchorname='';
                $anchorfromid='';
                $anchorformname='';
                $tId=$user_info['game_tenant_id'];
                $custId=$user_info['game_user_id'];
                $custAnchorid=0; // 不需要给主播分成，传0
                $anchorTenantid=0; // 不需要给主播分成，传0
                $anthor_total = '';

                $updateResult= reduceGameUserBalance($useridGame,$useridLive,$tidGame,$tidLive,$usernickname,$amount,$diamond,$type,$detail,$roomid,$anchorid,$anchorname,$anchorfromid,$anchorformname,$tId,$custId,$custAnchorid,$anthor_total,$anchorTenantid);
                if($updateResult['code']!=0){
                    rollbackTransaction();
                    //调用失败,回滚事务,并返回网络错误
                    return array('code' => 2057, 'msg' => codemsg('2057'), 'info' => [$updateResult]);
                }

                // 赠送记录处理
                if($handsel > 0){
                    $insert=array(
                        "type"=>'income',
                        "action" => $buyNobleType == 1 ? 'buyNobleHandsel' : 'renewalNobleHandsel', // buyNoble 开通贵族, renewalNoble 续费贵族
                        "uid"=>$uid,
                        "totalcoin"=>$handsel,
                        "addtime"=>time(),
                        'tenant_id' =>$tenant_id,
                        "cd_ratio"=>'1:'.floatval($config['money_rate']),
                    );
                    Model_Coinrecord::getInstance()->addCoinrecord($insert);
                    //调用余额更新接口
                    $money_rate=$config['money_rate'];
                    $total = $handsel;

                    $useridGame=$user_info['game_user_id'];
                    $useridLive=$user_info['id'];
                    $tidGame=$user_info['game_tenant_id'];
                    $tidLive=$user_info['tenant_id'];
                    $usernickname=$user_info['user_nicename'];
                    //金额=钻石/转换比例四舍五入
                    $amount=round($total/$money_rate,2) ;
                    $familyhead_id = $user_info['game_user_id'];
                    $familyhead_money = $amount;
                    $user_level = intval(getLevel($user_info['consumption'] - $price + $handsel));
                    $amount = 0;
                    $diamond=$total;
                    $type=1;
                    $detail= $buyNobleType == 1 ? '开通贵族赠送' : '续费贵族赠送';
                    $roomid='';
                    $anchorid='';
                    $anchorname='';
                    $anchorfromid='';
                    $anchorformname='';
                    $tId=$user_info['game_tenant_id'];
                    $custId=$user_info['game_user_id'];
                    $custAnchorid=0; // 不需要给主播分成，传0
                    $anchorTenantid=0; // 不需要给主播分成，传0
                    $anthor_total = '';

                    $updateResult= addGameUserBalance($useridGame,$useridLive,$tidGame,$tidLive,$usernickname,$amount,$diamond,$type,$detail,$roomid,$anchorid,$anchorname,$anchorfromid,$anchorformname,$tId,$custId,$custAnchorid,$anthor_total,$anchorTenantid,$familyhead_id,$familyhead_money,$user_level);
                    if($updateResult['code']!=0){
                        rollbackTransaction();
                        //调用失败,回滚事务,并返回网络错误
                        return array('code' => 2057, 'msg' => codemsg('2057'), 'info' => [$updateResult]);
                    }
                }
            }
            commitTransaction();
        }catch (\Exception $e){
            rollbackTransaction();
            $remark = $type == 1 ? '开通贵族失败' : '续费贵族失败';
            logapi(array_merge($_GET,$_POST),'【'.$remark.'】'.$e->getMessage());
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => [$remark.": ".$e->getMessage()]);
        }
        delUserCarlistCache($uid);
        delUserInfoCache($uid);
        delUserNoble($uid);
        if($liveuid && ($noble_info['special_effect'] == 1 || $noble_info['broadcast'] == 1)){
            $user_opennoble = array(
                'uid' => $uid,
                'liveuid' => $liveuid,
                'uname' => $user_info['user_nicename'],
                'name' => $noble_info['name'],
                'name_color' => $noble_info['name_color'],
                'special_effect' => $noble_info['special_effect'],
                'special_effect_swf' => $noble_info['special_effect_swf'],
                'special_effect_swftime' => $noble_info['special_effect_swftime'],
                'broadcast' => $noble_info['broadcast'],
            );
            $redis->zAdd('user_opennoble',1,json_encode($user_opennoble));
            // http 请求到golang的socketio
//            $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//            $http_post_res = http_post($url,['EventType' => 'OpenNoble','Message' => json_encode($user_opennoble)]);
        }
        return array('code' => 0, 'msg' => '', 'info' => []);
    }

    /*
    * 独立租户开通贵族或续费
    * */
    public function buyNoblealone($uid,$liveuid,$level,$type)
    {
        $redis = connectionRedis();
        $tenant_id = getTenantId();
        $config = getConfigPub();
        $noble_info = getNobleList($tenant_id,$level);
        if(!$noble_info){
            return array('code' => 2055, 'msg' => codemsg('2055'), 'info' => []);
        }
        $users_noble_info = DI()->notorm->users_noble->where(['uid'=>intval($uid)])->fetchOne();
        $user_info = getUserInfo($uid);
        try{
            beginTransaction();
            $stime = time();
            $etime = strtotime ("+1 month", $stime);

            $price = $type == 1 ? $noble_info['price'] : $noble_info['renewal_price'];
            $buyNobleType = $type;
            $handsel = $type == 1 ? $noble_info['handsel'] : $noble_info['renewal_handsel']; // 赠送
            if($user_info['coin'] < $price){
                return array('code' => 2026, 'msg' => codemsg('2026'), 'info' => []);
            }
            $users_noble_log_type = 0; // 开通方式：1.正常开通，2.续费，3.升级
            if($type == 1){
                if($users_noble_info){
                    if($users_noble_info['level'] > $noble_info['level'] && $users_noble_info['etime'] > time()){
                        return array('code' => 2093, 'msg' => codemsg('2093'), 'info' => []);
                    }
                    if($users_noble_info['level'] == $noble_info['level'] && $users_noble_info['etime'] > time()){
                        return array('code' => 2094, 'msg' => codemsg('2094'), 'info' => []);
                    }
                    $users_noble_log_type = 3;
                    $res = DI()->notorm->users_noble->where(['id'=>$users_noble_info['id']])->update([
                        'noble_id' => $noble_info['id'],
                        'level' => $noble_info['level'],
                        'stime' => $stime,
                        'etime' => $etime,
                        'mtime' => time(),
                    ]);
                }else{
                    $users_noble_log_type = 1;
                    $res = DI()->notorm->users_noble->insert([
                        'uid' => $uid,
                        'user_login' => $user_info['user_login'],
                        'game_user_id' => $user_info['game_user_id'] ? $user_info['game_user_id'] : 0,
                        'noble_id' => $noble_info['id'],
                        'level' => $noble_info['level'],
                        'tenant_id' => $user_info['tenant_id'],
                        'stime' => $stime,
                        'etime' => $etime,
                        'ctime' => time(),
                    ]);
                }
            }else{
                if(!$users_noble_info){
                    return array('code' => 2095, 'msg' => codemsg('2095'), 'info' => []);
                }
                if($users_noble_info['level'] != $noble_info['level']){
                    return array('code' => 2096, 'msg' => codemsg('2096'), 'info' => []);
                }
                if($users_noble_info['etime'] > time()){
                    $stime = $users_noble_info['etime'];
                    $etime = strtotime ("+1 month", $stime);
                }
                $users_noble_log_type = 2;
                $res = DI()->notorm->users_noble->where(['id'=>$users_noble_info['id']])->update([
                    'noble_id' => $noble_info['id'],
                    'level' => $noble_info['level'],
                    'stime' => $stime,
                    'etime' => $etime,
                    'mtime' => time(),
                ]);
            }
            if($res){
                // 开通记录
                DI()->notorm->users_noble_log->insert([
                    'uid' => $uid,
                    'user_login' => $user_info['user_login'],
                    'game_user_id' => $user_info['game_user_id'] ? $user_info['game_user_id'] : 0,
                    'noble_id' => $noble_info['id'],
                    'level' => $noble_info['level'],
                    'type' => $users_noble_log_type,
                    'price' => $price,
                    'handsel' => $handsel,
                    'tenant_id' => $user_info['tenant_id'],
                    'ctime' => time(),
                ]);
                // 用户等级升级加成，根据加成的比例，增加用户等级经验
                $user_noble = getUserNoble($uid);
                $u_consumption = is_array($user_noble) && isset($user_noble['upgrade_speed']) ? bcmul($price,(1+$user_noble['upgrade_speed']/100),2) : $price;
                //增加消费总额，自身余额减少
                DI()->notorm->users->where('id=?',$uid)->update([
                    'coin' => new NotORM_Literal("coin - {$price}"),
                    'consumption' => new NotORM_Literal("consumption + {$u_consumption}"),
                ]);
                // 加入到用户坐骑列表或更新结束时间
                $usercar_info = DI()->notorm->users_car->where('uid=? and type=1', $uid)->fetchOne();
                if ($usercar_info) {
                    DI()->notorm->users_car->where('id=? ', $usercar_info['id'])->update(['carid' => $noble_info['car_id'], 'endtime' => $etime]);
                } else {
                    DI()->notorm->users_car->insert([
                        'uid' => $uid,
                        'carid' => $noble_info['car_id'],
                        'endtime' => $etime,
                        'status' => 0,
                        'addtime' => time(),
                        'tenant_id' => getTenantId(),
                        'type' => 1,
                    ]);
                }
                // 有专属座驾为 是，则开通后自动使用爵位专属坐骑
                if($noble_info['exclu_car'] == 1){
                    DI()->notorm->users_car->where('uid=?', $uid)->update(['status' => 0]);
                    DI()->notorm->users_car->where('uid=? and type=1', $uid)->update(['status' => 1]);
                }
                $insert=array(
                    "type" => 'expend',
                    "action" => $type == 1 ? 'buyNoble' : 'renewalNoble', // buyNoble 开通贵, renewalNoble 续费贵族
                    "uid" =>$uid,
                    "totalcoin" => $price,
                    "addtime" => time(),
                    'tenant_id' => $tenant_id,
                );
                Model_Coinrecord::getInstance()->addCoinrecord($insert);

                //返利，自身余额增加
                DI()->notorm->users->where('id=?',$uid)->update([
                    'coin' => new NotORM_Literal("coin + {$handsel}"),
                ]);
                // 赠送记录处理
                if($handsel > 0){
                    $insert=array(
                        "type"=>'income',
                        "action" => $buyNobleType == 1 ? 'buyNobleHandsel' : 'renewalNobleHandsel', // buyNoble 开通贵族, renewalNoble 续费贵族
                        "uid"=>$uid,
                        "totalcoin"=>$handsel,
                        "addtime"=>time(),
                        'tenant_id' =>$tenant_id,
                    );
                    Model_Coinrecord::getInstance()->addCoinrecord($insert);
                }
            }
            commitTransaction();
        }catch (\Exception $e){
            rollbackTransaction();
            $remark = $type == 1 ? '开通贵族失败' : '续费贵族失败';
            logapi(array_merge($_GET,$_POST),'【'.$remark.'】'.$e->getMessage());
            return array('code' => 2034, 'msg' => codemsg('2034'), 'info' => [$remark.": ".$e->getMessage()]);
        }
        delUserCarlistCache($uid);
        delUserInfoCache($uid);
        delUserNoble($uid);
        if($liveuid && ($noble_info['special_effect'] == 1 || $noble_info['broadcast'] == 1)){
            $user_opennoble = array(
                'uid' => $uid,
                'liveuid' => $liveuid,
                'uname' => $user_info['user_nicename'],
                'name' => $noble_info['name'],
                'name_color' => $noble_info['name_color'],
                'special_effect' => $noble_info['special_effect'],
                'special_effect_swf' => $noble_info['special_effect_swf'],
                'special_effect_swftime' => $noble_info['special_effect_swftime'],
                'broadcast' => $noble_info['broadcast'],
            );
            $redis->zAdd('user_opennoble',1,json_encode($user_opennoble));
            // http 请求到golang的socketio
//            $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//            $http_post_res = http_post($url,['EventType' => 'OpenNoble','Message' => json_encode($user_opennoble)]);
        }
        return array('code' => 0, 'msg' => '', 'info' => []);
    }

    /*
   * 获取贵族配置
   * */
    public function getNobleSetting($uid)
    {
        $tenant_id = getTenantId();
        $info = getNobleSetting($tenant_id);
        $info['details'] = htmlspecialchars_decode($info['details']);
        return array('code' => 0, 'msg' => '', 'info' => array($info));
    }

    /*
    * 定时处理主播在线状态
    * */
    public function ExeculationLivestatus()
    {
        $redis = connectionRedis();
        // app添加的主播列表
        $liveuid_list = DI()->notorm->users_live->select('uid,user_nicename,avatar,tenant_id,stream')->where(['islive'=>1,'isvideo'=>0])->fetchAll();
        $execlution = array();
        foreach ($liveuid_list as $key=>$val){
            $api_time = $redis->hGet('live_api_heart_time',$val['uid']);
            $socket_time = $redis->hGet('live_socket_heart_time',$val['uid']);
            $api_time = $api_time ? $api_time : 0;
            $socket_time = $socket_time ? $socket_time : 0;
            if( (time() - $api_time) > 120 && (time() - $socket_time) > 120){ // 大于30秒钟，则把状态置为暂停中

                logapi(json_encode(array('uid'=>$val['uid'],'stream'=>$val['stream']),JSON_UNESCAPED_UNICODE),'直播自动暂停修改状态 暂停中');
                DI()->notorm->users_live->where(['uid'=>intval($val['uid'])])->update(['islive'=>2,'pause_time'=>time()]);
                // 异常断开改为暂停，通知前端
                $redis->zAdd('user_livestatustopause',1,json_encode(['uid'=>$val['uid']]));
                // http 请求到golang的socketio
                $config = getConfigPub($val['tenant_id']);
//                $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//                $http_post_res = http_post($url,['EventType' => 'LiveStatusToPause','Message' => json_encode(['Uid'=>$val['uid']])]);

                array_push($execlution,$val);

                $isstoplog=DI()->notorm->users_stoplog
                    ->select("uid")
                    ->where('uid = ? and stream =? and status = 0 ', $val['uid'],$val['stream'])
                    ->fetchOne();
                if(!$isstoplog){
                    logapi(json_encode(array('uid'=>$val['uid'],'stream'=>$val['stream']),JSON_UNESCAPED_UNICODE),'直播自动暂停数据写入');
                    //写入暂停日志表，用于统计暂停时长
                    $resdata['uid'] =  $val['uid'];
                    $resdata['stream'] =  $val['stream'];
                    $resdata['pause_time'] =  time();
                    $resdata['status'] =  0;
                    DI()->notorm->users_stoplog->insert($resdata);
                    logapi(json_encode($resdata,JSON_UNESCAPED_UNICODE),'心跳检测写入暂停时间');
                }

            }
            setUserLiveListCache($val['uid']); // 设置直播列表缓存
        }
        // 后台添加的主播列表
        $liveuid_list = DI()->notorm->users_live->select('uid,user_nicename,avatar,tenant_id,stream')->where(['islive'=>1,'isvideo'=>1])->fetchAll();
        foreach ($liveuid_list as $key=>$val){
            setUserLiveListCache($val['uid']); // 设置直播列表缓存
        }
        return array('code' => 0, 'msg' => '', 'info' => [$execlution]);
    }

    /*
     * 直播暂停超时后自动关播
     * */
    public function LiveTimeOut(){
        $redis = connectionRedis();
        $liveuid_list = DI()->notorm->users_live->select('uid,stream,tenant_id,pause_time')->where(['islive'=>2,'isvideo'=>0])->limit(50)->fetchAll();
        $execlution = array();
        foreach ($liveuid_list as $key=>$val){
            $config = getConfigPub($val['tenant_id']);
            if($config['livet_timeout'] > 0 && $val['pause_time'] > 0 && (time() - $val['pause_time']) > $config['livet_timeout']){
                $socket_time = $redis->hGet('live_socket_heart_time',$val['uid']);
                $socket_time = $socket_time ? $socket_time : 0;
                if((time() - $socket_time) > 15){ // 如果心跳不正常了说明异常断开暂停，直接关播
                    logapi(json_encode(array('uid'=>$val['uid'],'stream'=>$val['stream']),JSON_UNESCAPED_UNICODE),'直播暂停超时后自动关播数据');
                    $this->stopRoom($val['uid'],$val['stream']);
                }else{
                    // 心跳正常，通知前端关播
                    $redis->zAdd('user_closelive',1,json_encode(['uid'=>$val['uid']]));
                    // http 请求到golang的socketio
                    $config = getConfigPub($val['tenant_id']);
//                    $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
//                    $http_post_res = http_post($url,['EventType' => 'Closelive','Message' => json_encode(['Uid'=>$val['uid']])]);
                }
                array_push($execlution,$val['uid'].' | '.(time() - $val['pause_time'])." | ".$config['livet_timeout']);
            }
        }
        if(!$redis->get('logapi_LiveTimeOut')){
            $redis->set('logapi_LiveTimeOut',100,3600);
            logapi(json_encode($execlution,JSON_UNESCAPED_UNICODE),'直播暂停超时后自动关播定时器检测');
        }

        return array('code' => 0, 'msg' => '', 'info' => $execlution);
    }

    /*
    * 更新直播数据
    * */
    public function updateLivetype($uid,$stream,$type,$type_val,$tryWatchTime){
        $data['type'] =  $type;
        $data['type_val'] =  $type_val;
        $data['tryWatchTime'] =  $tryWatchTime;

        $isexist=DI()->notorm->users_live
            ->select("uid")
            ->where('uid = ? and stream =? and islive=1', $uid,$stream)
            ->fetchOne();

        if($isexist){
            /* 更新 */
            $rs=DI()->notorm->users_live->where('uid = ? and stream =? ', $uid,$stream)->update($data);
            setUserLiveListCache($uid); // 设置直播列表缓存
        }else{
            $rs = 1003;
        }

        return $rs;

    }


    /*
    * 更新足球视频直播比赛数据
    * */
    public function updateFootBallLiveInfo($liveuid, $stream, $data = array()){
        $update_data = array();
        if(isset($data['football_live_match_id'])){
            $update_data['football_live_match_id'] = $data['football_live_match_id'];
        }
        if(isset($data['pull'])){
            $update_data['pull'] = $data['pull'];
        }
        if(isset($data['flvpull'])){
            $update_data['flvpull'] = $data['flvpull'];
        }
        if(isset($data['m3u8pull'])){
            $update_data['m3u8pull'] = $data['m3u8pull'];
        }
        if(empty($update_data)){
            return true;
        }
        $update_data['football_live_time_stamp'] = time();

        try{
            DI()->notorm->users_live->where('uid = ? and stream =? ', $liveuid, $stream)->update($update_data);
        }catch (\Exception $e){
            logapi([$liveuid, $stream, $update_data, $e->getMessage()],'更新足球视频直播比赛数据 失败');
            return false;
        }
        setUserLiveListCache($liveuid); // 设置直播列表缓存
        return true;
    }


}
