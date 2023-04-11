<?php
//session_start();
class Model_Home extends PhalApi_Model_NotORM {

	/* 轮播 */
	public function getSlide($tenantId,$cat_name){

		$rs=DI()->notorm->slide
			->select("slide_pic,slide_url")
			->where("slide_status='1' and cat_name=? and tenant_id=? ",$cat_name,$tenantId)
			->order("listorder asc")
			->fetchAll();
		foreach($rs as $k=>$v){
			$rs[$k]['slide_pic']=get_upload_path($v['slide_pic']);
		}				

		return $rs;				
	}

	/* 热门  旧的 不用了*/
   /* public function getHot_old($p,$tenantId,$liveclassid,$ishot,$isrecommend) {
        $redis = connectionRedis();

        $configpri=getConfigPri();
        $prefix= DI()->config->get('dbs.tables.__default__.prefix');
        $tenantinfo = getTenantInfo($tenantId);
        if($tenantinfo['site_id'] == 2){
            $where=" l.islive= '1' and u.ishot='1' and (l.tenant_id='{$tenantId}' ) ";
            if($liveclassid){
                $where=" l.islive= '1' and u.ishot='1' and l.tenant_id='{$tenantId}'  and  l.liveclassid='{$liveclassid}'";
            }
            if($ishot){
                $where=" l.islive= '1' and u.ishot='1' and l.tenant_id='{$tenantId}'  and  l.ishot='{$ishot}'";
            }
            if($isrecommend){
                $where=" l.islive= '1' and u.ishot='1' and l.tenant_id='{$tenantId}'  and  l.isrecommend='{$isrecommend}'";
            }
        }else{
            $where=" l.islive= '1' and u.ishot='1' and (l.tenant_id='{$tenantId}' or l.isshare=1)";
            if($liveclassid){
                $where=" l.islive= '1' and u.ishot='1' and (l.tenant_id='{$tenantId}' or l.isshare=1) and l.liveclassid='{$liveclassid}'";
            }
            if($ishot){
                $where=" l.islive= '1' and u.ishot='1' and (l.tenant_id='{$tenantId}' or l.isshare=1) and l.ishot='{$ishot}'";
            }
            if($isrecommend){
                $where=" l.islive= '1' and u.ishot='1' and (l.tenant_id='{$tenantId}' or l.isshare=1) and l.isrecommend='{$isrecommend}'";
            }
        }
        // 屏蔽主播断开连接的直播间
        $disconnect = $redis->zRange('disconnect_'.$tenantId,0,100000000);
        if(is_array($disconnect) && count($disconnect) > 0){
            $where .= " and (l.uid NOT IN (".implode(',',$disconnect).")) ";
        }

        $topsort = ' toptime desc,';
        if($p!=null)
        {
            if($p<1){
                $p=1;
            }
            if($p!=1){
                $endtime=$_SESSION['hot_starttime'];
                if($endtime){
                    $where.=" and starttime < {$endtime}";
                }

            }
            if($p<1){
                $p=1;
            }
            $pnum=50;
            $start=($p-1)*$pnum;
            $result=DI()->notorm->users_live
                ->queryAll("select l.uid,l.avatar,l.avatar_thumb,l.pull,l.flvpull,l.pushpull_id,l.user_nicename,l.title,l.city,l.stream,l.thumb,l.isvideo,l.type,l.type_val,l.game_action,l.goodnum,l.anyway,u.sex,u.votestotal,u.consumption,u.game_user_id,l.ishot,l.isrecommend,l.hotorderno,l.liveclassid,l.top,l.ly_recommend,l.game_recommend,l.label_name from {$prefix}users_live l left join {$prefix}users u on l.uid=u.id where {$where} order by l.hotorderno asc,l.hotvotes desc,l.starttime desc limit {$start},{$pnum}");

            if($isrecommend){ // 只有推荐tab列表，展示光年推荐和游戏推荐的数据
                $where = " l.islive= '1' and u.ishot='1' and (l.tenant_id='{$tenantId}' or l.isshare=1) and (l.top=1 or l.ly_recommend=1 or l.game_recommend=1)";
                // 屏蔽主播断开连接的直播间
                if(is_array($disconnect) && count($disconnect) > 0){
                    $where .= " and (l.uid NOT IN (".implode(',',$disconnect).")) ";
                }
                $top_ly_recommend = DI()->notorm->users_live
                    ->queryAll("select l.uid,l.avatar,l.avatar_thumb,l.pull,l.flvpull,l.pushpull_id,l.user_nicename,l.title,l.city,l.stream,l.thumb,l.isvideo,l.type,l.type_val,l.game_action,l.goodnum,l.anyway,u.sex,u.votestotal,u.consumption,u.game_user_id,l.ishot,l.isrecommend,l.hotorderno,l.liveclassid,l.top,l.ly_recommend,l.game_recommend,l.label_name from {$prefix}users_live l left join {$prefix}users u on l.uid=u.id where {$where} order by {$topsort} l.hotorderno asc,l.hotvotes desc,l.starttime desc limit {$start},{$pnum}");

                }else{
    //                $where = " l.islive= '1' and u.ishot='1' and (l.tenant_id='{$tenantId}' or l.isshare=1) and l.top=1"; // // 需求 497 置顶的直播间，在前端的多个标签中，只在推荐标签下生效，其他标签无置顶
                 $top_ly_recommend = [];
                }
        }else{
            //如果不传分页参数则不分页
            $result=DI()->notorm->users_live
                ->queryAll("select l.uid,l.avatar,l.avatar_thumb,l.pull,l.flvpull,l.pushpull_id,l.user_nicename,l.title,l.city,l.stream,l.thumb,l.isvideo,l.type,l.type_val,l.game_action,l.goodnum,l.anyway,u.sex,u.votestotal,u.consumption,u.game_user_id,l.ishot,l.isrecommend,l.hotorderno,l.liveclassid,l.top,l.ly_recommend,l.game_recommend,l.label_name from {$prefix}users_live l left join {$prefix}users u on l.uid=u.id where {$where} order by l.hotorderno asc,l.hotvotes desc,l.starttime desc");

            if($isrecommend){ // 只有推荐tab列表，展示光年推荐和游戏推荐的数据
                $where = " l.islive= '1' and u.ishot='1' and (l.tenant_id='{$tenantId}' or l.isshare=1) and (l.top=1 or l.ly_recommend=1 or l.game_recommend=1)";

                // 屏蔽主播断开连接的直播间
                if(is_array($disconnect) && count($disconnect) > 0){
                    $where .= " and (l.uid NOT IN (".implode(',',$disconnect).")) ";
                }
                $top_ly_recommend = DI()->notorm->users_live
                    ->queryAll("select l.uid,l.avatar,l.avatar_thumb,l.pull,l.flvpull,l.pushpull_id,l.user_nicename,l.title,l.city,l.stream,l.thumb,l.isvideo,l.type,l.type_val,l.game_action,l.goodnum,l.anyway,u.sex,u.votestotal,u.consumption,u.game_user_id,l.ishot,l.isrecommend,l.hotorderno,l.liveclassid,l.top,l.ly_recommend,l.game_recommend,l.label_name from {$prefix}users_live l left join {$prefix}users u on l.uid=u.id where {$where} order by {$topsort} l.hotorderno asc,l.hotvotes desc,l.starttime desc");
            }else{
                // $where = " l.islive= '1' and u.ishot='1' and (l.tenant_id='{$tenantId}' or l.isshare=1) and l.top=1"; // 需求 497 置顶的直播间，在前端的多个标签中，只在推荐标签下生效，其他标签无置顶
                $top_ly_recommend = [];
            }
         }

        $rs=DI()->notorm->liveing_set
            ->select("*")
            ->where(" tenant_id=? ",$tenantId)
            ->order("id desc")
            ->fetchAll();

        $top_ly_recommend = array_reverse($top_ly_recommend);
        if(count($top_ly_recommend) > 0){
            $top = array();
            $recommend = array();
            $arr = array();
            foreach ($top_ly_recommend as $key=>$val){
                array_push($arr,md5(json_encode($val)));
                if($val['top']==1){
                    array_push($top,$val);
                }else{
                    array_push($recommend,$val);
                }
            }
            foreach($result as $key=>$val){
                if(in_array(md5(json_encode($val)),$arr)){
                    unset($result[$key]); // 去重处理
                }
            }
            foreach ($recommend as $key=>$val){
                array_unshift($result,$val);
            }
            foreach ($top as $key=>$val){
                array_unshift($result,$val);
            }
        }

        $PushpullModel = new Model_Pushpull();
        foreach($result as $k=>$v){
            $nums=DI()->redis->zCard('user_'.$v['stream']);

            $v['nums']=(string)$nums + getLiveNumsDefault($v['stream']);

            $v['level']=getLevel($v['consumption']);
            $v['level_anchor']=getLevelAnchor($v['votestotal']);

            $v['game']=getGame($v['game_action']);

            $v['avatar']=get_upload_path($v['avatar']);
            $v['avatar_thumb']=get_upload_path($v['avatar_thumb']);
            if(!$v['thumb']){
                $v['thumb']=get_upload_path($v['avatar']);
            }
            if($v['isvideo']==0){
                $PushpullInfo = $PushpullModel->getPushpullInfoWithId($v['pushpull_id']);
                $v['pull'] = $PushpullModel->PrivateKeyA('rtmp',$v['stream'],0,$PushpullInfo);
                $v['flvpull'] = $PushpullModel->PrivateKeyA('http',$v['stream'].'.flv',0,$PushpullInfo);
           }

            if($v['type']==1){
                $v['type_val']='';
            }
        

            $result[$k]=$v;
            $result[$k]['game_info'] = getLiveGameInfo($rs,$v['uid'],$tenantId);
        }
        if($result){
            $last=end($result);
            $_SESSION['hot_starttime']=isset($last['starttime'])?$last['starttime']:'';
        }

        return $result;
    }*/

    /* 热门 */
    public function getHot($p,$tenantId,$liveclassid,$ishot,$isrecommend) {
        $redis = connectionRedis();
        $list = $this->getUserLiveList($tenantId, $p, $liveclassid, $ishot, $isrecommend);

        $liveingSetList = Cache_LiveingSet::getInstance()->getLiveingSetListCache($tenantId);

        $data_list = array();
        $PushpullModel = new Model_Pushpull();
        foreach($list as $k=>$v){
            $user_info = Cache_Users::getInstance()->getUserInfoCache($v['uid']);

            $nums = $redis->zCard('user_'.$v['stream']);

            if($v['isvideo']==0){
                $PushpullInfo = $PushpullModel->getPushpullInfoWithId($v['pushpull_id']);
                $v['pull'] = $PushpullModel->PrivateKeyA('rtmp',$v['stream'],0,$PushpullInfo);
                $v['flvpull'] = $PushpullModel->PrivateKeyA('http',$v['stream'].'.flv',0,$PushpullInfo);
                $v['m3u8pull'] = $PushpullModel->PrivateKeyA('http',$v['stream'].'.m3u8',0,$PushpullInfo);
            }
            if(empty($v['label_name'])){
                $label_name = $redis->hGet("label_uid",$v['uid']);
                $v['label_name'] = $label_name;
            }

            $temp = array(
                'uid' => $v['uid'],
                'avatar' => get_upload_path($user_info['avatar']),
                'avatar_thumb' => get_upload_path($user_info['avatar_thumb']),
                'sex' => $user_info['sex'],
                'votestotal' => $user_info['votestotal'],
                'game_user_id' => $user_info['game_user_id'],
                'level' => getLevel($user_info['consumption']),
                'level_anchor' => getLevelAnchor($user_info['votestotal']),
                'islive' => $v['islive'],
                'tenant_id' => $v['tenant_id'],
                'is_football' => $v['is_football'],
                'football_live_match_id' => $v['football_live_match_id'],
                'football_live_time_stamp' => $v['football_live_time_stamp'],
                'pull' => $v['pull'],
                'flvpull' => $v['flvpull'],
                'm3u8pull' => $v['m3u8pull'],
                'pushpull_id' => $v['pushpull_id'],
                'user_nicename' => $v['user_nicename'],
                'title' => rawurldecode($v['title']),
                'city' => $v['city'],
                'stream' => $v['stream'],
                'thumb' => $v['thumb'] ? $v['thumb'] : get_upload_path($user_info['avatar']),
                'isvideo' => $v['isvideo'],
                'type' => $v['type'],
                'type_val' => $v['type'] != 1 ? $v['type_val'] : '',
                'game_action' => $v['game_action'],
                'goodnum' => $v['goodnum'],
                'anyway' => $v['anyway'],
                'ishot' => $v['ishot'],
                'isrecommend' => $v['isrecommend'],
                'hotorderno' => $v['hotorderno'],
                'liveclassid' => $v['liveclassid'],
                'top' => $v['top'],
                'ly_recommend' => $v['ly_recommend'],
                'game_recommend' => $v['game_recommend'],
                'label_name' => $v['label_name'],
                'nums' => (string)($nums + getLiveNumsDefault($v['stream'])),
                'game' => getGame($v['game_action']),
                'game_info' => getLiveGameInfo($liveingSetList,$v['uid'],$tenantId),
            );
            array_push($data_list,$temp);
        }
        return $data_list;
    }

    // 根据参数获取直播列表返回
    public function getUserLiveList($tenant_id, $p, $liveclassid, $ishot, $isrecommend){
        $list = getUserLiveList($tenant_id);
        $count = count($list);
        if($count <= 0){
            return [];
        }
        $limit = 50;
        if(!$p){
            $p_start = 0;
            $p_end = $count - 1;
        }else{
            $p = $p >= 1 ? $p : 1;
            $p_start = $count <= $limit ? 0 : ($p-1) * $limit;
            $p_end = $count <= $limit ? ($count-1) : ($p * $limit - 1);
        }

        $data_list = array();
        $top = array();
        $untop_butsort = array();
        $unsort = array();

        $liveclassid_list = array();
        $ishot_list = array();
        $isrecommend_list = array();
        $ly_recommend_list = array();
        $game_recommend_list = array();

        // 屏蔽主播断开连接的直播间
        $redis = connectionRedis();
        $disconnect = $redis->zRange('disconnect_'.$tenant_id,0,100000000);
        $disconnect_list = is_array($disconnect) ? $disconnect : array();

        foreach ($list as $key=>$val){
            if(!is_array($val)){
                continue;
            }
            // 屏蔽主播断开连接的直播间
            if($val['islive'] != 1 || in_array($val['uid'],$disconnect_list)){
                continue;
            }

            if($val['liveclassid'] == $liveclassid){
                array_push($liveclassid_list,$val);
            }

            if($val['top'] == 1){
                array_push($top,$val);
            }else if($val['ishot'] == 1){
                array_push($untop_butsort,$val);
                array_push($ishot_list,$val);
            }else if($val['isrecommend'] == 1){
                array_push($untop_butsort,$val);
                array_push($isrecommend_list,$val);
            }else if($val['ly_recommend'] == 1){
                array_push($untop_butsort,$val);
                array_push($ly_recommend_list,$val);
            }else if($val['game_recommend'] == 1){
                array_push($untop_butsort,$val);
                array_push($game_recommend_list,$val);
            }else{
                array_push($unsort,$val);
            }
        }

        // 数组随机处理
        shuffle($top);
        shuffle($untop_butsort);
        shuffle($unsort);

        shuffle($liveclassid_list);
        shuffle($ishot_list);
        shuffle($isrecommend_list);
        shuffle($ly_recommend_list);
        shuffle($game_recommend_list);

        if($liveclassid){
            return array_slice($liveclassid_list, $p_start, $limit);
        }
        if($ishot){
            return array_slice($ishot_list, $p_start, $limit);
        }
        // 推荐tab列表，展示: 置顶、光年推荐、游戏推荐
        if($isrecommend){
            $top_num = 0;
            foreach ($top as $key=>$val){
                if($top_num >= $p_start && $top_num <= $p_end){
                    array_push($data_list,$val);
                }
                $top_num ++;
            }
            $ly_recom_num = 0;
            foreach ($ly_recommend_list as $key=>$val){
                if($ly_recom_num >= $p_start && $ly_recom_num <= $p_end){
                    array_push($data_list,$val);
                }
                $ly_recom_num ++;
            }
            $isrecom_num = 0;
            foreach ($isrecommend_list as $key=>$val){
                if($isrecom_num >= $p_start && $isrecom_num <= $p_end){
                    array_push($data_list,$val);
                }
                $isrecom_num ++;
            }
            return $data_list;
        }

        $num = 0;
        foreach ($top as $key=>$val){
            if($num >= $p_start && $num <= $p_end){
                array_push($data_list,$val);
            }
            $num ++;
        }
        foreach ($untop_butsort as $key=>$val){
            if($num >= $p_start && $num <= $p_end){
                array_push($data_list,$val);
            }
            $num ++;
        }
        foreach ($unsort as $key=>$val){
            if($num >= $p_start && $num <= $p_end){
                array_push($data_list,$val);
            }
            $num ++;
        }

        return $data_list;
    }
	
		/* 关注列表 */
    public function getFollow($uid,$p,$tenantId) {
        $rs=array(
            'title'=>'你还没有关注任何主播',
            'des'=>'赶快去关注自己喜欢的主播吧~',
            'list'=>array(),
        );
        if($p<1){
            $p=1;
        }
		$result=array();
		$pnum=50;
		$start=($p-1)*$pnum;
		$configpri=getConfigPri();
		$touid=DI()->notorm->users_attention
				->select("touid")
				->where('uid=?',$uid)
				->fetchAll();

		$liveUidArray=array();
        $touids=array();
				
		if($touid){
            $rs['title']='你关注的主播没有开播';
            $rs['des']='赶快去看看其他主播的直播吧~';
            $where=" islive='1' ";					
            if($p!=1){
                $endtime=$_SESSION['follow_starttime'];
                if($endtime){
                    $start=0;
                    $where.=" and starttime < {$endtime}";
                }
                
            }	
        
			$touids=array_column($touid,"touid");
			$touidss=implode(",",$touids);
			$where.=" and uid in ({$touidss}) and (tenant_id='{$tenantId}' or isshare='1')";
			$result=DI()->notorm->users_live
					->select("uid,avatar,avatar_thumb,user_nicename,title,city,stream,pull,thumb,isvideo,type,type_val,game_action,goodnum,anyway,starttime")
					->where($where)
					->order("starttime desc")
					->limit($start,$pnum)
					->fetchAll();
		}	
		foreach($result as $k=>$v){
			$nums=DI()->redis->zCard('user_'.$v['stream']);
			$v['nums']=(string)($nums + getLiveNumsDefault($v['stream']));
            array_push($liveUidArray,$v['uid']);
			
			$userinfo=getUserInfo($v['uid']);
			$v['sex']=$userinfo['sex'];
			$v['level']=$userinfo['level'];
			$v['level_anchor']=$userinfo['level_anchor'];
			
			$v['game']=getGame($v['game_action']);
			
			$v['avatar']=$v['avatar'];
			$v['avatar_thumb']=$v['avatar_thumb'];
			if(!$v['thumb']){
				$v['thumb']=$v['avatar'];
			}
			/*if($v['isvideo']==0 && $configpri['cdn_switch']!=5){
				$v['pull']=PrivateKeyA('rtmp',$v['stream'],0);
			}*/
			if($v['type']==1){
				$v['type_val']='';
			}
			$v['islive']='1';
            $result[$k]=$v;
		}	

		if($result){
			$last=end($result);
			$_SESSION['follow_starttime']=$last['starttime'];
		}

		//搜索未开播的用户合并列表
        $noLiveUidArray=array_diff($touids,$liveUidArray);
        $noLiveUserInfoArray=array();
		foreach ($noLiveUidArray as $noLiveUid){
		    $noLiveUserInfo=array();
            $userinfo=getUserInfo($noLiveUid);

            $noLiveUserInfo['uid']=$userinfo['id'];
            $noLiveUserInfo['user_nicename']=$userinfo['user_nicename'];
            $noLiveUserInfo['avatar']= get_upload_path($userinfo['avatar']);
            $noLiveUserInfo['avatar_thumb']=get_upload_path($userinfo['avatar_thumb']);

            $noLiveUserInfo['title']='';
            $noLiveUserInfo['city']='';
            $noLiveUserInfo['stream']='';
            $noLiveUserInfo['isvideo']='';
            $noLiveUserInfo['type']='';
            $noLiveUserInfo['type_val']='';
            $noLiveUserInfo['game_action']='';
            $noLiveUserInfo['goodnum']='';
            $noLiveUserInfo['anyway']='';
            $noLiveUserInfo['starttime']='';
            $noLiveUserInfo['nums']='';
            $noLiveUserInfo['sex']=$userinfo['sex'];
            $noLiveUserInfo['level']=$userinfo['level'];
            $noLiveUserInfo['level_anchor']=$userinfo['level_anchor'];
            $noLiveUserInfo['game']='';

            $noLiveUserInfo['islive']='0';

            array_push($noLiveUserInfoArray,$noLiveUserInfo);
        }

		$result= array_merge($result,$noLiveUserInfoArray);



        
        $rs['list']=$result;

		return $rs;					
    }
		
		/* 最新 */
    public function getNew($lng,$lat,$p,$tenantId) {
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;
		$where=" islive='1' and (tenant_id='{$tenantId}' or isshare='1') ";

		if($p!=1){
			$endtime=$_SESSION['new_starttime'];
            if($endtime){
                $where.=" and starttime < {$endtime}";
            }
		}
		$configpri=getConfigPri();
		$result=DI()->notorm->users_live
				->select("uid,avatar,avatar_thumb,user_nicename,title,city,stream,lng,lat,pull,thumb,isvideo,type,type_val,game_action,goodnum,anyway,starttime")
				->where($where)
				->order("starttime desc")
				->limit($start,$pnum)
				->fetchAll();	
		foreach($result as $k=>$v){
			$nums=DI()->redis->zCard('user_'.$v['stream']);
			$v['nums']=(string)$nums;
			
			$userinfo=getUserInfo($v['uid']);
			$v['sex']=$userinfo['sex'];
			$v['level']=$userinfo['level'];
			$v['level_anchor']=$userinfo['level_anchor'];
			
			$v['game']=getGame($v['game_action']);
			
			$v['avatar']=get_upload_path($v['avatar']);
			$v['avatar_thumb']=get_upload_path($v['avatar_thumb']);
			if(!$v['thumb']){
				$v['thumb']=get_upload_path($v['avatar']);
			}
		/*	if($v['isvideo']==0 && $configpri['cdn_switch']!=5){
				$v['pull']=PrivateKeyA('rtmp',$v['stream'],0);
			}*/
			
			if($v['type']==1){
				$v['type_val']='';
			}
			
			$distance='好像在火星';
			if($lng!='' && $lat!='' && $v['lat']!='' && $v['lng']!=''){
				$distance=getDistance($lat,$lng,$v['lat'],$v['lng']);
			}else if($v['city']){
				$distance=$v['city'];	
			}
			
			$v['distance']=$distance;
			unset($v['lng']);
			unset($v['lat']);
            
            $result[$k]=$v;
			
		}		
		if($result){
			$last=end($result);
			$_SESSION['new_starttime']=$last['starttime'];
		}

		return $result;
    }
		
		/* 搜索 */
    public function search($uid,$key,$p,$tenantId) {
        if($p<1){
            $p=1;
        }
		$pnum=10;
		$start=($p-1)*$pnum;
		$where=" user_type in (2,5,6) and tenant_id= '{$tenantId}'  and ( id=? or user_nicename like ?  and id!=?) ";


        $arr = array();
		$result=DI()->notorm->users
				->select("id,user_nicename,avatar,sex,signature,consumption,votestotal")
				->where($where,$key,'%'.$key.'%',$uid)
				->order("id desc")
				->limit($start,$pnum)
				->fetchAll();
		foreach($result as $k=>$v){
			$v['level']=(string)getLevel($v['consumption']);
			$v['level_anchor']=(string)getLevelAnchor($v['votestotal']);
            $result[$k]['isattention']=(string)isAttention($uid,$v['id']);
			$v['avatar']= get_upload_path($v['avatar']);
			unset($v['consumption']);
            $arr[] = $v['id'];
            $resultlong =DI()->notorm->video_long
                ->select("*")
                ->where(' uid = '.$v['id'].' and status= 2')
                ->order("id desc")
                ->limit(2)
                ->fetchAll();
            $result[$k]['longvideo'] = $resultlong;

		}
       /* $arr = implode(",",$arr);
		$where = '  uid in( '.$arr.')';
        $resultlong =DI()->notorm->video_long
            ->select("*")
            ->where($where)
            ->order("id desc")
            ->limit($start,$pnum)
            ->fetchAll();
        foreach($result as $k=>$v){
            $count = 0;
            foreach($resultlong as $k1=> $v1){
                if($v['id'] == $v1['uid']){
                    $count++;
                    if($count>2){
                        break;
                    }else{
                        $result[$k]['longvideo'] = $v1;
                    }
                }
            }
        }*/
		return $result;
    }
	
	/* 附近 */
    public function getNearby($lng,$lat,$p,$tenantId) {
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;
		$where=" islive='1' and lng!='' and lat!='' and (tenant_id='{$tenantId}' or isshare='1')";
		$configpri=getConfigPri();
		$result=DI()->notorm->users_live
				->select("uid,avatar,avatar_thumb,user_nicename,title,province,city,stream,lng,lat,pull,isvideo,thumb,islive,type,type_val,game_action,goodnum,anyway,getDistance('{$lat}','{$lng}',lat,lng) as distance")
				->where($where)
                ->order("distance asc")
                ->limit($start,$pnum)
				->fetchAll();	
		foreach($result as $k=>$v){
			$nums=DI()->redis->zCard('user_'.$v['stream']);
			$v['nums']=(string)$nums;
			
			$userinfo=getUserInfo($v['uid']);
			$v['sex']=$userinfo['sex'];
			$v['level']=$userinfo['level'];
			$v['level_anchor']=$userinfo['level_anchor'];
			
			$v['game']=getGame($v['game_action']);
			
			$v['avatar']=get_upload_path($v['avatar']);
			$v['avatar_thumb']=get_upload_path($v['avatar_thumb']);
			if(!$v['thumb']){
				$v['thumb']=get_upload_path($v['avatar']);
			}
			/*if($v['isvideo']==0 && $configpri['cdn_switch']!=5){
				$v['pull']=PrivateKeyA('rtmp',$v['stream'],0);
			}*/
			
			if($v['type']==1){
				$v['type_val']='';
			}
            if($v['distance']>1000){
                $v['distance']=1000;
            }
            $v['distance']=$v['distance'].'km';

            $result[$k]=$v;
		}
		
		return $result;
    }


	/* 推荐 */
	public function getRecommend($tenantId){

		$result=DI()->notorm->users
				->select("id,user_nicename,avatar,avatar_thumb")
				->where("isrecommend='1' and (tenant_id='{$tenantId}' or isshare='1')")
				->order("votestotal desc")
				->limit(0,12)
				->fetchAll();
		foreach($result as $k=>$v){
			$v['avatar']=get_upload_path($v['avatar']);
			$v['avatar_thumb']=get_upload_path($v['avatar_thumb']);
			$fans=getFans($v['id']);
			$v['fans']='粉丝 · '.$fans;
            
            $result[$k]=$v;
		}
		return  $result;
	}
	/* 关注推荐 */
	public function attentRecommend($uid,$touids){
		//$users=$this->getRecommend();
		//$users=explode(',',$touids);
        file_put_contents('./attentRecommend.txt',date('Y-m-d H:i:s').' 提交参数信息 touids:'.$touids."\r\n",FILE_APPEND);
        $users=preg_split('/,|，/',$touids);
		foreach($users as $k=>$v){
			$touid=$v;
            file_put_contents('./attentRecommend.txt',date('Y-m-d H:i:s').' 提交参数信息 touid:'.$touid."\r\n",FILE_APPEND);
			if($touid && !isAttention($uid,$touid)){
				DI()->notorm->users_black
					->where('uid=? and touid=?',$uid,$touid)
					->delete();
				DI()->notorm->users_attention
					->insert(array("uid"=>$uid,"touid"=>$touid));
			}
			
		}
		return 1;
	}

	/*获取收益排行榜*/

	public function profitList($uid,$type,$p){
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;

		switch ($type) {
            case 'yesterday':
                //获取昨日开始结束时间
                $yesterStart=strtotime(date("Y-m-d",strtotime("-1 day")));
                $yesterEnd=strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")));
                $where=" addtime >={$yesterStart} and addtime<={$yesterEnd} and ";
                break;

			case 'day':
				//获取今天开始结束时间
				$dayStart=strtotime(date("Y-m-d"));
				$dayEnd=strtotime(date("Y-m-d 23:59:59"));
				$where=" addtime >={$dayStart} and addtime<={$dayEnd}  and ";

			break;
            case 'lastweek':
                //获取上周开始结束时间
                $lastweekStart=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
                $lastweekEnd=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                $where=" addtime >={$lastweekStart} and addtime<={$lastweekEnd} and ";
                break;
			case 'week':
                $w=date('w'); 
                //获取本周开始日期，如果$w是0，则表示周日，减去 6 天 
                $first=1;
                //周一
                $week=date('Y-m-d H:i:s',strtotime( date("Ymd")."-".($w ? $w - $first : 6).' days')); 
                $week_start=strtotime( date("Ymd")."-".($w ? $w - $first : 6).' days'); 

                //本周结束日期 
                //周天
                $week_end=strtotime("{$week} +1 week")-1;
                
				$where=" addtime >={$week_start} and addtime<={$week_end}  and ";

			break;

            case 'lastmonth':
                $lastmonthStart =mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) );
                $lastmonthEnd =  mktime ( 23, 59, 59, date ( "m" ), date ( "t" ), date ( "Y" ) );
                $where=" addtime >={$lastmonthStart} and addtime<={$lastmonthEnd} and ";
                break;

			case 'month':
                //本月第一天
                $month=date('Y-m-d',strtotime(date("Ym").'01'));
                $month_start=strtotime(date("Ym").'01');

                //本月最后一天
                $month_end=strtotime("{$month} +1 month")-1;

				$where=" addtime >={$month_start} and addtime<={$month_end}  and";

			break;

			case 'total':
				$where=" ";
			break;
			
			default:
				//获取今天开始结束时间
				$dayStart=strtotime(date("Y-m-d"));
				$dayEnd=strtotime(date("Y-m-d 23:59:59"));
				$where=" addtime >={$dayStart} and addtime<={$dayEnd} and ";
			break;
		}




		$where.=" type='expend' and action in ('sendgift','sendbarrage')";

		$prefix= DI()->config->get('dbs.tables.__default__.prefix');
      //  echo "select sum(r.totalcoin) as totalcoin,r.touid as uid,u.votestotal,u.user_nicename,u.avatar_thumb,u.sex from {$prefix}users_coinrecord r left join {$prefix}users u on r.touid=u.id where {$where}  group by r.touid order by totalcoin desc limit {$start},{$pnum}";exit;

        $result=DI()->notorm->users_coinrecord
            ->select('sum(totalcoin) as totalcoin,touid as uid')
            ->group('touid')->order('totalcoin desc')
            ->limit($start,$pnum)
            ->where($where)
            ->fetchAll();
        /**
             * 分组查询 有问题
             *
             */
			//->queryAll("select sum(r.totalcoin) as totalcoin,r.touid as uid,u.votestotal,u.user_nicename,u.avatar_thumb,u.sex from {$prefix}users_coinrecord r left join {$prefix}users u on r.touid=u.id where {$where}  group by r.touid order by totalcoin desc limit {$start},{$pnum}");


		foreach ($result as $k => $v) {
            $userInfo =DI()->notorm->users
                ->select('votestotal,avatar_thumb,user_nicename,sex')
                ->where("id = {$v['uid']}")    ->fetchOne();

            $result[$k]['levelAnchor']=getLevelAnchor($userInfo['votestotal']); //主播等级
            $result[$k]['isAttention']=isAttention($uid,$v['uid']);//判断当前用户是否关注了该主播
            $result[$k]['avatar_thumb']=get_upload_path($userInfo['avatar_thumb']);
            $result[$k]['sex']= $userInfo['sex'];
            $result[$k]['user_nicename']= $userInfo['user_nicename'];

            $result[$k]['noble'] = getUserNoble($v['uid']);
		}


		return $result;
	}



	/*获取消费排行榜*/
	public function consumeList($uid,$type,$p,$touid){
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;

		switch ($type) {
			case 'day':
				//获取今天开始结束时间
				$dayStart=strtotime(date("Y-m-d"));
				$dayEnd=strtotime(date("Y-m-d 23:59:59"));
				$where=" addtime >={$dayStart} and addtime<={$dayEnd} and ";

			break;
            
            case 'week':
                $w=date('w'); 
                //获取本周开始日期，如果$w是0，则表示周日，减去 6 天 
                $first=1;
                //周一
                $week=date('Y-m-d H:i:s',strtotime( date("Ymd")."-".($w ? $w - $first : 6).' days')); 
                $week_start=strtotime( date("Ymd")."-".($w ? $w - $first : 6).' days'); 

                //本周结束日期 
                //周天
                $week_end=strtotime("{$week} +1 week")-1;
                
				$where=" addtime >={$week_start} and addtime<={$week_end} and ";

			break;

			case 'month':
                //本月第一天
                $month=date('Y-m-d',strtotime(date("Ym").'01'));
                $month_start=strtotime(date("Ym").'01');

                //本月最后一天
                $month_end=strtotime("{$month} +1 month")-1;

				$where=" addtime >={$month_start} and addtime<={$month_end} and ";

			break;

			case 'total':
				$where=" ";
			break;
			
			default:
				//获取今天开始结束时间
				$dayStart=strtotime(date("Y-m-d"));
				$dayEnd=strtotime(date("Y-m-d 23:59:59"));
				$where=" addtime >={$dayStart} and addtime<={$dayEnd} and ";
			break;
		}


       if(!empty($touid)){
           $where.=" touid ={$touid} and ";
       }

		$where.=" type='expend' and action in ('sendgift','sendbarrage')";

		$prefix= DI()->config->get('dbs.tables.__default__.prefix');


             $result=DI()->notorm->users_coinrecord
                 ->select('sum(totalcoin) as totalcoin, uid')
                 ->group('uid')->order('totalcoin desc')
                 ->limit($start,$pnum)
                 ->where($where)
                 ->fetchAll();
        //生成 六个 假会员，合并到真实会员中
        $voteuid = getLiveVotestotalDefault($touid);
        logapi(['updateResult'=>$voteuid],'【返回机器人总额】');  // 接口日志记录
        $userRoot =  getRobot($touid,$type,$voteuid);
        $result =  array_merge($result,$userRoot);
        //重新根据贡献值排序
        $results = array_column($result,'totalcoin');
        array_multisort($results,SORT_DESC,$result);



        foreach ($result as $k => $v) {
            $userInfo =DI()->notorm->users
                ->select('avatar_thumb,user_nicename,sex,consumption,user_type')
                ->where("id = {$v['uid']}")    ->fetchOne();
            if($userInfo['user_type'] ==3 ){
                $result[$k]['level']=getLevel($userInfo['consumption']+rand(1,1000)); //用户等级
            }else{
                $result[$k]['level']=getLevel($userInfo['consumption']); //用户等级
            }
        /*    $result[$k]['levelAnchor']=getLevelAnchor($userInfo['votestotal']); //主播等级*/
            $result[$k]['isAttention']=isAttention($uid,$v['uid']);//判断当前用户是否关注了该主播
            $result[$k]['avatar_thumb']=get_upload_path($userInfo['avatar_thumb']);
            $result[$k]['user_nicename']= $userInfo['user_nicename'];
            if($userInfo['user_type'] ==3 ){
                $result[$k]['noble']  = getNoblevirtual($v['uid']);

            }else{
                $result[$k]['noble'] = getUserNoble($v['uid']);
            }

        }

			/*->queryAll("select sum(r.totalcoin) as totalcoin,r.uid as uid,u.consumption,u.user_nicename,u.avatar_thumb,u.sex from {$prefix}users_coinrecord r left join {$prefix}users u on r.uid=u.id where {$where}  group by r.uid order by totalcoin desc limit {$start},{$pnum}");
*/

		/*foreach ($result as $k => $v) {
			$v['level']=getLevel($v['consumption']); //用户等级
			$v['isAttention']=isAttention($uid,$v['uid']);//判断当前用户是否关注了该用户
			$v['avatar_thumb']=get_upload_path(str_replace('public/','',$v['avatar_thumb']));
			$v['totalcoin']=$v['totalcoin'];
			unset($v['consumption']);
            
            $result[$k]=$v;

		}*/


		return $result;
	}
    /*获取消费总排行榜*/
    public function consumeListall($uid,$type,$p){
        if($p<1){
            $p=1;
        }
        $pnum=50;
        $start=($p-1)*$pnum;

        switch ($type) {
            case 'yesterday':
                //获取昨日开始结束时间
                $yesterStart=strtotime(date("Y-m-d",strtotime("-1 day")));
                $yesterEnd=strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")));
                $where=" addtime >={$yesterStart} and addtime<={$yesterEnd} and ";

                break;
            case 'day':
                //获取今天开始结束时间
                $dayStart=strtotime(date("Y-m-d"));
                $dayEnd=strtotime(date("Y-m-d 23:59:59"));
                $where=" addtime >={$dayStart} and addtime<={$dayEnd} and ";

                break;
            case 'lastweek':
                //获取上周开始结束时间
                $lastweekStart=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
                $lastweekEnd=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                $where=" addtime >={$lastweekStart} and addtime<={$lastweekEnd} and ";
                break;
           case 'week':
                $w=date('w');
                //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
                $first=1;
                //周一
                $week=date('Y-m-d H:i:s',strtotime( date("Ymd")."-".($w ? $w - $first : 6).' days'));
                $week_start=strtotime( date("Ymd")."-".($w ? $w - $first : 6).' days');

                //本周结束日期
                //周天
                $week_end=strtotime("{$week} +1 week")-1;

                $where=" addtime >={$week_start} and addtime<={$week_end} and ";
                break;
            case 'lastmonth':
                $lastmonthStart =mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) );
                $lastmonthEnd =  mktime ( 23, 59, 59, date ( "m" ), date ( "t" ), date ( "Y" ) );
                $where=" addtime >={$lastmonthStart} and addtime<={$lastmonthEnd} and ";
                break;
            case 'month':
                //本月第一天
                $month=date('Y-m-d',strtotime(date("Ym").'01'));
                $month_start=strtotime(date("Ym").'01');

                //本月最后一天
                $month_end=strtotime("{$month} +1 month")-1;

                $where=" addtime >={$month_start} and addtime<={$month_end} and ";

                break;

            case 'total':
                $where=" ";
                break;

            default:
                //获取今天开始结束时间
                $dayStart=strtotime(date("Y-m-d"));
                $dayEnd=strtotime(date("Y-m-d 23:59:59"));
                $where=" addtime >={$dayStart} and addtime<={$dayEnd} and ";
                break;
        }
        $where.=" type='expend' and action in ('sendgift','sendbarrage')";

        $result=DI()->notorm->users_coinrecord
            ->select('sum(totalcoin) as totalcoin, uid')
            ->group('uid')->order('totalcoin desc')
            ->limit($start,$pnum)
            ->where($where)
            ->fetchAll();

        foreach ($result as $k => $v) {
            $userInfo =DI()->notorm->users
                ->select('avatar_thumb,user_nicename,sex,consumption,user_type')
                ->where("id = {$v['uid']}")    ->fetchOne();
            $result[$k]['level']=getLevel($userInfo['consumption']); //用户等级
            $result[$k]['isAttention']=isAttention($uid,$v['uid']);//判断当前用户是否关注了该主播
            $result[$k]['avatar_thumb']=get_upload_path($userInfo['avatar_thumb']);
            $result[$k]['user_nicename']= $userInfo['user_nicename'];
            $result[$k]['noble'] = getUserNoble($v['uid']);
        }
        return $result;
    }


    /* 分类下直播 */
    public function getClassLive($liveclassid,$p,$tenantId) {
        if($p<1){
            $p=1;
        }
		$pnum=50;
		$start=($p-1)*$pnum;
		$where=" islive='1' and liveclassid={$liveclassid} and (tenant_id='{$tenantId}'  and isshare='1' ) ";
        $configpri=getConfigPri();
		if($p!=1){
			$endtime=$_SESSION['getClassLive_starttime'];
            if($endtime){
                $where.=" and starttime < {$endtime}";
            }
			
		}
		$last_starttime=0;
		$result=DI()->notorm->users_live
				->select("uid,avatar,avatar_thumb,user_nicename,title,city,stream,pull,thumb,isvideo,type,type_val,game_action,goodnum,anyway,starttime")
				->where($where)
				->order("starttime desc")
				->limit($start,$pnum)
				->fetchAll();
        $result['sql'] =  DI()->notorm->users_live
            ->select("uid,avatar,avatar_thumb,user_nicename,title,city,stream,pull,thumb,isvideo,type,type_val,game_action,goodnum,anyway,starttime")
            ->where($where)
            ->order("starttime desc")
            ->limit($start,$pnum)
            ->__tostring();
		foreach($result as $k=>$v){
			$nums=DI()->redis->zCard('user_'.$v['stream']);
			$v['nums']=(string)$nums;
			
			$userinfo=getUserInfo($v['uid']);
			$v['sex']=$userinfo['sex'];
			$v['level']=$userinfo['level'];
			$v['level_anchor']=$userinfo['level_anchor'];
			
			$v['game']=getGame($v['game_action']);
			
			$v['avatar']=get_upload_path($v['avatar']);
			$v['avatar_thumb']=get_upload_path($v['avatar_thumb']);
			if(!$v['thumb']){
				$v['thumb']=get_upload_path($v['avatar']);
			}
			
			if($v['type']==1){
				$v['type_val']='';
			}

            $result[$k]=$v;
		}		
		if($result){
            $last=end($result);
			$_SESSION['getClassLive_starttime']=$last['starttime'];
		}

		return $result;
    }
    /* 根据租户id，获取正在直播的直播间 */
    public function getLive($tenantId) {

        $where=" islive='1'and tenant_id='{$tenantId}' ";

        $result=DI()->notorm->users_live
            ->select("uid,avatar,avatar_thumb,user_nicename,title,city,stream,pull,thumb,isvideo,type,type_val,game_action,goodnum,anyway,starttime,top,ly_recommend,pushpull_type")
            ->where($where)
            ->order("top desc,starttime desc")
            ->fetchAll();
  /*      $uids='';
        foreach ($result as $key=>$value){
            $uids .= $value['uid'] . ',';
        }
        $uids = rtrim($uids, ',');*/


        return $result;
    }

    public function getBetinfo($tenantId){
        $where=" tenant_id='{$tenantId}' ";

        $result =DI()->notorm->bet_config
            ->select("id,name,playname,tenant_id,game_tenant_id,tenant_name,loss_rate")
            ->where($where)
            ->order("add_time desc")
            ->fetchAll();
        

        return $result;
    }
    /* 根据租户id，获取推荐直播间 */
    public function recommendRoom($tenantId) {
        $where=" tenant_id='{$tenantId}' ";
        $result=DI()->notorm->recommend
            ->select("*")
            ->where($where)
            ->order("add_time desc")
            ->fetchAll();

        return $result;
    }
    /* 根据租户id，获取正在推送的任务信息 */
    public function getAutotask($tenantId) {
        $where="   status=1 ";
        $result=DI()->notorm->atmosphere_live
            ->select("id")
            ->where($where)
            ->order("addtime desc")
            ->fetchAll();
        $taskid = '';
        foreach ($result as $key=>$value){
            $taskid.=$value['id'].',';
        }
        $taskid = rtrim($taskid, ',');


        return $taskid;
    }

    public function shareCollcet() {
        $start = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $end = mktime(23,59,59,date('m'),date('d')-1,date('Y'));
       /* $todoystart = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $todoyend = mktime(23,59,59,date('m'),date('d'),date('Y'));*/

        $where="    status=0 and is_type=1 and addtime  BETWEEN {$start} and {$end} ";
        $result=DI()->notorm->profit_sharing
            ->select("*")
            ->where($where)
            ->order("addtime desc")
            ->fetchAll();
        $collect = array();   //分成报表
        $uidcollect = array();  //主播报表
       foreach ($result as $key=>$value){
           $collect[$value['anchor_tenant']]['zb_share'] +=$value['anchor_money'];
           $collect[$value['send_tenant']]['zb_share'] +=$value['send_money'];
           $collect[$value['anchor_tenant']]['zbzb_share'] +=$value['anthor_total'];
           $collect[$value['anchor_tenant']]['zbjz_share'] +=$value['family_total'];
           $uidcollect[$value['uid']]['gift_money'] +=$value['anthor_total'];
           $uidcollect[$value['uid']]['tatal_money'] +=$value['anthor_total'];

        }
         //统计彩票投注
        $wheres="    status=0 and is_type=2 and addtime  BETWEEN {$start} and {$end} ";
        $result=DI()->notorm->profit_sharing
            ->select("*")
            ->where($wheres)
            ->order("addtime desc")
            ->fetchAll();
        foreach ($result as $key=>$value){
            $collect[$value['anchor_tenant']]['cp_share'] +=$value['anchor_money'];
            $collect[$value['send_tenant']]['cp_share'] +=$value['send_money'];
            $collect[$value['anchor_tenant']]['cpzb_share'] +=$value['anthor_total'];
            $collect[$value['anchor_tenant']]['cpjz_share'] +=$value['family_total'];
            $uidcollect[$value['uid']]['bet_money'] +=$value['anthor_total'];
            $uidcollect[$value['uid']]['tatal_money'] +=$value['anthor_total'];

        }
//        file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/collect_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 调试1:'.json_encode($uidcollect)."\r\n",FILE_APPEND);
        //$datetoday = date("Y-m-d");
        $datetoday = date("Y-m-d",strtotime("-1 day"));
        //统计主播总收益
        foreach($uidcollect as $key =>$value) {
            $wheres="    collet_day = '{$datetoday}' and uid = {$key} ";
            $isbasicsalary=DI()->notorm->users_basicsalary
                ->select("*")
                ->where($wheres)
                ->fetchOne();
//            file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/collect_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 调试1:'.$wheres."\r\n",FILE_APPEND);
//            file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/collect_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 调试1:'.json_encode($isbasicsalary)."\r\n",FILE_APPEND);

            $userinfo = getUserInfo($key);

            $livedata['collet_day'] = $datetoday;
            $livedata['uid'] = $key;
            $livedata['status'] = 3;   //如果主播 底薪未结算，不显示
            $livedata['tatal_money'] = $value['tatal_money'];
            $livedata['bet_money'] = $value['bet_money'];
            $livedata['gift_money'] = $value['gift_money'];
            $livedata['addtime'] = time();
            $livedata['user_login']  = isset($userinfo['user_login'])?$userinfo['user_login']:$userinfo['user_nicename'];
            if($isbasicsalary){
                $livedata['tatal_money']  =  $isbasicsalary['money'] +  $value['tatal_money'];
                if($isbasicsalary['money']){
                    $livedata['status'] = 0;
                }
                $result=DI()->notorm->users_basicsalary->where($wheres)->update($livedata);
            }
        }



       foreach($collect as $key =>$value) {
           $where="    collet_day='{$datetoday}' and  tenant_id={$key} ";
           $iscollect=DI()->notorm->profit_daysharing
               ->select("*")
               ->where($where)
               ->fetchOne();
           $tenantinfo = getTenantInfo($key);

           $data['collet_day'] = $datetoday;
           $data['tenant_id']  = $key;
           $data['tenant_name']  = $tenantinfo['name'];
           $data['zb_share']  = $value['zb_share'];
           $data['zbzb_share']  = $value['zbzb_share'];
           $data['zbjz_share']  = $value['zbjz_share'];
           $data['cp_share']  = $value['cp_share'];
           $data['cpzb_share']  = $value['cpzb_share'];
           $data['cpjz_share']  = $value['cpjz_share'];
           $data['addtime'] = time();
         if($iscollect){
             $result=DI()->notorm->profit_daysharing->where($where)->update($data);
         }else{
             $result=DI()->notorm->profit_daysharing->insert($data);
         }
       }
        return $result;
    }


    public function basicsalaryCollcet() {

        //昨天开始时间
        $start = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $end = mktime(23,59,59,date('m'),date('d')-1,date('Y'));



        //获取所有设置的主播uid
        $commissioninfo=DI()->notorm->commission_set
            ->select("*")
            ->order("addtime desc")
            ->fetchAll();
        $authoruid = array();
        $authorinfo = array();
        foreach ($commissioninfo as $kye=>$value){
            $authoruid[] = $value['uid'];
            $authorinfo[ $value['uid']]['hour_money'] = $value['hour_money'];
            $authorinfo[ $value['uid']]['hour_limit'] = $value['hour_limit'];
            $authorinfo[ $value['uid']]['gift_limit'] = $value['gift_limit'];

        }


        $result=DI()->notorm->liveing_log
            ->select("*")
            ->where('status = 0 and starttime < '.$end)
            ->order('uid')
            ->fetchAll();

        $res = array();
        $ress = array();
        foreach ($result as $key=>$value){
            if(empty($value['endtime'])){
                //还没有结束的直播
                if($value['starttime'] < $start){     //只有两种情况，一种 直播开始时间小于今天的日期开始，统计整天
                    $timecharge = 86400;
                }elseif($value['starttime'] > $start && $value['starttime'] < $end){   //一种 直播开始时间位于 今天开始和结束之间
                    $timecharge = $end-$value['starttime'];
                }
            }else{
                //已经结束的直播
                if($value['starttime'] < $start && $value['endtime'] > $end){      //跨两天以上处理
                    $timecharge = 86400;                   //直接取一整天
                }elseif($value['starttime'] < $start){     //如果直播开始时间小于今天日期的开始时间，那么今天的时长等于 结束之间 减去 今天开始时间
                    $timecharge = $value['endtime']-$start;
                }elseif($value['endtime'] > $end){          //如果直播结束时间大于今天日期的最后时间，那么今天的时长等于 今天最后时间 减去 开始时间
                    $timecharge = $end-$value['starttime'];
                }else {
                    $timecharge = $value['endtime']-$value['starttime'];
                }
               // var_dump($value['stream']);
                $ress[] = $value['stream'];

            }

            $timecharge = $timecharge-$value['stop_time'];
            $res[$value['uid']]['totalcoin'] += $value['totalcoin'];
            $res[$value['uid']]['timecharge'] += $timecharge;
        }



        $collect = array();
       // $datetoday = date("Y-m-d");
        $datetoday = date("Y-m-d",strtotime("-1 day"));
        foreach ($res as $key=>$value){
         if(in_array($key,$authoruid)){
//             file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/data/Log/collect_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 是否包含:'.json_encode($res)."\r\n",FILE_APPEND);

             $hour_total = time2string($value['timecharge']);//01:00:00  格式化时长显示
             $hour_totals =round($value['timecharge']/3600,5);//1  直接显示时长

             $money = round($hour_totals*$authorinfo[$key]['hour_money'],2);
             $userinfo = getUserInfo($key);

             $data['collet_day'] = $datetoday;
             $data['tenant_id'] = intval($userinfo['tenant_id']);
             $data['uid']  = $key;
             $data['user_login']  = isset($userinfo['user_login'])?$userinfo['user_login']:$userinfo['user_nicename'];
             $data['money']  = $money;
             $data['hour_limit']  = $authorinfo[$key]['hour_limit'];
             $data['hour_total']  = $hour_total;
             $data['gift_limit']  = $authorinfo[$key]['gift_limit'];
             $data['gift_total']  = $value['totalcoin'];
             $data['status']  =   0;
             $data['addtime']  = time();
             $data['tatal_money']  = $money;
             if($value['totalcoin']<$authorinfo[$key]['gift_limit']){
                 $data['money']  = null;
                 $data['status']  = 3;
                 $data['tatal_money']  = null;
                 $result=DI()->notorm->users_basicsalary->insert($data);
                 continue;
             }
             //判断你时间长度是否满足条件
             if($hour_totals<$authorinfo[$key]['hour_limit']){
                 $data['money']  = null;
                 $data['status']  = 3;
                 $data['tatal_money']  = null;
                 $result=DI()->notorm->users_basicsalary->insert($data);
                 continue;
             }
             $result=DI()->notorm->users_basicsalary->insert($data);


         }else{
             $hour_total = time2string($value['timecharge']);//01:00:00  格式化时长显示
             $hour_totals =round($value['timecharge']/3600,5);//1  直接显示时长

             $money = round($hour_totals*$authorinfo[$key]['hour_money'],2);
             $userinfo = getUserInfo($key);


             if($value['totalcoin']<$authorinfo[$key]['gift_limit']){
                 continue;
             }
             //判断你时间长度是否满足条件
             if($hour_totals<$authorinfo[$key]['hour_limit']){
                 continue;
             }
             $data['collet_day'] = $datetoday;
             $data['tenant_id'] = intval($userinfo['tenant_id']);
             $data['uid']  = $key;
             $data['user_login']  = isset($userinfo['user_login'])?$userinfo['user_login']:$userinfo['user_nicename'];
             $data['hour_limit']  = $authorinfo[$key]['hour_limit'];
             $data['hour_total']  = $hour_total;
             $data['gift_limit']  = $authorinfo[$key]['gift_limit'];
             $data['gift_total']  = $value['totalcoin'];
             $data['money']  = null;
             $data['status']  = 3;
             $data['addtime']  = time();
             $data['tatal_money']  = null;
             $result=DI()->notorm->users_basicsalary->insert($data);



         }

        }
        //更新已经结算的的数据
        if(!empty($ress)){
            foreach ($ress as $value ){
                DI()->notorm->liveing_log->where("stream = '".$value."'")->update(array('status'=>1));
            }
        }


        return $result;
    }
    public function consumptionCollcet() {

        //昨天开始时间
        $start = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $end = mktime(23,59,59,date('m'),date('d')-1,date('Y'));
        $action= "('sendgift','sendbarrage','roomcharge','timecharge')";
        $where="    addtime  BETWEEN {$start} and {$end} and action in {$action}";

        $configpri=getConfigPri();
        //获取所有设置的主播uid
        $coinrecordinfo=DI()->notorm->users_coinrecord
            ->select("uid,totalcoin,tenant_id,tenantuser_total")
            ->where($where)
            ->order("addtime desc")
            ->fetchAll();


        $authorinfo = array();
        foreach ($coinrecordinfo as $key=>$value){
            $authorinfo[$value['uid']]['totalcoin'] += $value['totalcoin'];
            $authorinfo[$value['uid']]['tenantuser_total'] += $value['tenantuser_total'];
            $authorinfo[$value['uid']]['tenant_id'] = $value['tenant_id'];


        }


        foreach ($authorinfo as $key=>$value){
            //$datetoday = date("Y-m-d");
            $datetoday = date("Y-m-d",strtotime("-1 day"));
            $tenantinfo  = getTenantInfo($value['tenant_id']);
            $userinfo = getUserInfo($key);
            $data['uid'] = $key;
            $data['user_login'] = isset($userinfo['user_login'])?$userinfo['user_login']:$userinfo['user_nicename'];
            $data['totalcoin'] = round($value['totalcoin']/$configpri['money_rate'],2);
            $data['collet_day'] = $datetoday;
            $data['tenant_name'] = $tenantinfo['name'];
            $data['tenantuser_total'] = $value['tenantuser_total'];
            $data['addtime'] = time();
            $data['action'] = '消费';


            $where="    collet_day = '{$datetoday}' and uid = {$key} ";
            $isconsumption=DI()->notorm->consumption_collect
                ->select("*")
                ->where($where)
                ->fetchOne();

            if($isconsumption){
                $result=DI()->notorm->consumption_collect->where($where)->update($data);
            }else{
                $data['tenant_id'] = intval($userinfo['tenant_id']);
                $result=DI()->notorm->consumption_collect->insert($data);
            }

        }


        return $result;
    }

    public function appHeartbeat($version, $client, $uid){
        $redis = connectionRedis();
        $userinfo = getUserInfo($uid);
        $update_data = array();
        if(isset($userinfo['version']) && $userinfo['version'] != $version){
            $update_data['version'] = $version;
        }
        if(isset($userinfo['client']) && $userinfo['client'] != $client){
            $update_data['client'] = $client;
        }
        if(!empty($update_data)){
            $update_data['mtime'] = time();
            DI()->notorm->users->where(['id'=>intval($uid)])->update($update_data);
            delUserInfoCache($uid);
        }

        return array('code' => 0, 'msg' => '', 'info' => array());
    }

    public function updateVersion($version, $client, $uid){
        $redis = connectionRedis();
        $userinfo = getUserInfo($uid);
        $update_data = array();
        if(isset($userinfo['version']) && $userinfo['version'] != $version){
            $update_data['version'] = $version;
        }
        if(isset($userinfo['client']) && $userinfo['client'] != $client){
            $update_data['client'] = $client;
        }
        if(!empty($update_data)){
            $update_data['mtime'] = time();
            DI()->notorm->users->where(['id'=>intval($uid)])->update($update_data);
            delUserInfoCache($uid);
        }

        return array('code' => 0, 'msg' => '', 'info' => array());
    }






}
