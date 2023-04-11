<?php

use api\Common\CustRedis;

class Model_Shotvideo extends PhalApi_Model_NotORM {


    private  static function isLike($uid,$videoId){
        // 是否喜欢
        $is_like  = DI()->notorm->users_video_like
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 1 ")
            ->fetchOne();
        if ($is_like){
            return 1;
        }else{
            return 0;
        }
    }

    private  static function isCollection($uid,$videoId){
        // 是否收藏
        $is_collection  = DI()->notorm->users_video_collection
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 1 ")
            ->fetchOne();
        if ($is_collection){
           return 1;
        }else{
            return 0;
        }
    }
    private  static function isBuy($uid,$videoId){
        // 是否购买
       
        $is_buy  = DI()->notorm->users_video_buy
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 1 ")
            ->fetchOne();
        if ($is_buy){
            return 1;
        }else{
            return 0;
        }
    }
    private  static function isAttention($uid,$videoUid){
        // 是否关注
        $touids =DI()->notorm->users_attention
            ->where("uid= '{$uid}' and  touid  = '{$videoUid}'")
            ->fetchOne();
        if ($touids){
            return 1;
        }else{
            return 0;
        }
    }

    private  static function isDownload($uid,$videoId){
        // 是否下载
        $is_download = DI()->notorm->video_download
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 1 ")
            ->fetchOne();
        if ($is_download){
            return 1;
        }else{
            return 0;
        }
    }

	/* 发布视频 */
	public function setVideo($data) {
		$uid=$data['uid'];

		//获取后台配置的初始曝光值
		/*$configPri=getConfigPri();
		$data['show_val']=$configPri['show_val'];

		if($configPri['video_audit_switch']==0){*/
			$data['status']=1;
		//}
		$result= DI()->notorm->video->insert($data);
		return $result;
	}

	/* 评论/回复 */
    public function setComment($data) {
    	$videoid=$data['videoid'];

    	//var_dump($videoid);exit;
		/* 更新 视频 */
		DI()->notorm->video
            ->where("id = '{$videoid}'")
		 	->update( array('comments' => new NotORM_Literal("comments + 1") ) );

        DI()->notorm->video_comments
            ->insert($data);

		$videoinfo=DI()->notorm->video
					->select("comments")
					->where('id=?',$videoid)
					->fetchOne();

		$count=DI()->notorm->video_comments
					->where("commentid='{$data['commentid']}'")
					->count();
		$rs=array(
			'comments'=>$videoinfo['comments'],
			'replys'=>$count,
		);

		return $rs;
    }

	/* 阅读 */
	public function addView($uid,$videoid){
		/*$view=DI()->notorm->users_video_view
				->select("id")
				->where("uid='{$uid}' and videoid='{$videoid}'")
				->fetchOne();

		if(!$view){
			DI()->notorm->users_video_view
						->insert(array("uid"=>$uid,"videoid"=>$videoid,"addtime"=>time() ));

			DI()->notorm->users_video
				->where("id = '{$videoid}'")
				->update( array('view' => new NotORM_Literal("view + 1") ) );
		}*/

		/*//用户看过的视频存入redis中
		$readLists=DI()->redis -> Get('readvideo_'.$uid);
		$readArr=array();
		if($readLists){
			$readArr=json_decode($readLists,true);
			if(!in_array($videoid,$readArr)){
				$readArr[]=$videoid;
			}
		}else{
			$readArr[]=$videoid;
		}

        DI()->redis -> Set('readvideo_'.$uid,json_encode($readArr));*/

		DI()->notorm->users_video
				->where("id = '{$videoid}'")
				->update( array('views' => new NotORM_Literal("views + 1") ) );

		return 0;
	}
	/* 点赞 */
	public function addLike($uid,$videoid,$game_tenant_id){
		$rs=array(
			'islike'=>'0',
			'likes'=>'0',
		);
        $tenant_id = getTenantId();
		$video=DI()->notorm->video
				->select("likes,uid,thumb")
				->where("id = '{$videoid}'")
				->fetchOne();

        if(!$video){
            return 1001;
        }
       /* if($video['uid']==$uid){
            return 1002;//不能给自己点赞
        }*/
        $like=DI()->notorm->users_video_like
            ->select("id")
            ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id ='{$game_tenant_id}' and video_type =1")
            ->fetchOne();
        if($like){
            DI()->notorm->users_video_like
                ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id='{$game_tenant_id}' and video_type=1 ")
                ->delete();

            DI()->notorm->video
                ->where("id = '{$videoid}' and likes>0")
                ->update( array('likes' => new NotORM_Literal("likes - 1") ) );
            $rs['islike']='0';
        }else{
            DI()->notorm->users_video_like
                ->insert(array("uid"=>$uid,"videoid"=>$videoid,"addtime"=>time(),"tenant_id"=>$game_tenant_id,'video_type'=>1 ));

            DI()->notorm->video
                ->where("id = '{$videoid}'")
                ->update( array('likes' => new NotORM_Literal("likes + 1") ) );
            $rs['islike']='1';
        }

        Cache_Video::getInstance()->delShortVideoInfoCache($tenant_id, $videoid);
        $video = Cache_Video::getInstance()->getShortVideoInfo($tenant_id, $videoid);
        $video=DI()->notorm->video
                ->select("collection,uid,thumb,likes")
                ->where("id = '{$videoid}'")
                ->fetchOne();
        $rs['likes']=$video['likes'];
        if($video['uid'] != $uid && $rs['islike'] == '1') {
            $rs['sendVideoLikeProfit_result'] = Model_Like::getInstance()->sendVideoLikeProfit(1, $videoid, $uid,$video['uid'], $video);
        }
        return $rs;
    }

    /* 收藏*/
    public function addCollection($uid,$videoid,$game_tenant_id){
        $rs=array(
            'iscollection'=>'0',
            'collection'=>'0',
        );
        $video=DI()->notorm->video
            ->select("likes,uid,thumb")
            ->where("id = '{$videoid}'")
            ->fetchOne();

        if(!$video){
            return 1001;
        }
        if($video['uid']==$uid){
            return 1002;//不能给自己点赞
        }
        $like=DI()->notorm->users_video_collection
            ->select("id")
            ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id ='{$game_tenant_id}' and video_type =1")
            ->fetchOne();
        if($like){
            DI()->notorm->users_video_collection
                ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id='{$game_tenant_id}' and video_type=1 ")
                ->delete();

            DI()->notorm->video
                ->where("id = '{$videoid}' and collection>0")
                ->update( array('collection' => new NotORM_Literal("collection - 1") ) );
            $rs['iscollection']='0';
        }else{
            DI()->notorm->users_video_collection
                ->insert(array("uid"=>$uid,"videoid"=>$videoid,"addtime"=>time(),"tenant_id"=>$game_tenant_id,'video_type'=>1 ));

            DI()->notorm->video
                ->where("id = '{$videoid}'")
                ->update( array('collection' => new NotORM_Literal("collection + 1") ) );
            $rs['iscollection']='1';
        }

        $video=DI()->notorm->video
            ->select("collection,uid,thumb")
            ->where("id = '{$videoid}'")
            ->fetchOne();

        $rs['collection']=$video['collection'];

        return $rs;
    }
    /*删除视频标签*/
    public function  delVideolabel($data){
        $rs =  DI()->notorm->video_label
            ->where("uid='{$data['uid']}' and label='{$data['label']}'")
            ->update(array('is_delete'=>2));
        return $rs;
    }
    /* 获取视频分类 */
    public function getVideoclassify($uid){
        $tenant_id = getTenantId();
        $list = Cache_VideoClassify::getInstance()->getShortVideoClassifyList($tenant_id);

        return $list;
    }
	/* 踩 */
	public function addStep($uid,$videoid){
		$rs=array(
			'isstep'=>'0',
			'steps'=>'0',
		);
		$like=DI()->notorm->users_video_step
						->select("id")
						->where("uid='{$uid}' and videoid='{$videoid}'")
						->fetchOne();
		if($like){
			DI()->notorm->users_video_step
						->where("uid='{$uid}' and videoid='{$videoid}'")
						->delete();

			DI()->notorm->users_video
				->where("id = '{$videoid}' and steps>0")
				->update( array('steps' => new NotORM_Literal("steps - 1") ) );
			$rs['isstep']='0';
		}else{
			DI()->notorm->users_video_step
						->insert(array("uid"=>$uid,"videoid"=>$videoid,"addtime"=>time() ));

			DI()->notorm->users_video
				->where("id = '{$videoid}'")
				->update( array('steps' => new NotORM_Literal("steps + 1") ) );
			$rs['isstep']='1';
		}

		$video=DI()->notorm->users_video
				->select("steps")
				->where("id = '{$videoid}'")
				->fetchOne();
		$rs['steps']=$video['steps'];
		return $rs;
	}

    /* 分享 */
    public function addShare($uid,$videoid){


        $rs=array(
            'isshare'=>'0',
            'shares'=>'0',
        );
        DI()->notorm->users_video
            ->where("id = '{$videoid}'")
            ->update( array('shares' => new NotORM_Literal("shares + 1") ) );
        $rs['isshare']='1';


        $video=DI()->notorm->users_video
            ->select("shares")
            ->where("id = '{$videoid}'")
            ->fetchOne();
        $rs['shares']=$video['shares'];

        return $rs;
    }

    /* 拉黑视频 */
    public function setBlack($uid,$videoid){
        $rs=array(
            'isblack'=>'0',
        );
        $like=DI()->notorm->users_video_black
            ->select("id")
            ->where("uid='{$uid}' and videoid='{$videoid}'")
            ->fetchOne();
        if($like){
            DI()->notorm->users_video_black
                ->where("uid='{$uid}' and videoid='{$videoid}'")
                ->delete();
            $rs['isshare']='0';
        }else{
            DI()->notorm->users_video_black
                ->insert(array("uid"=>$uid,"videoid"=>$videoid,"addtime"=>time() ));
            $rs['isshare']='1';
        }
        return $rs;
    }


    /* 评论/回复 点赞 */
    public function addCommentLike($uid,$commentid,$game_tenant_id){
        $rs=array(
            'islike'=>'0',
            'likes'=>'0',
        );

        //根据commentid获取对应的评论信息
        $commentinfo=DI()->notorm->video_comments
            ->where("id='{$commentid}'")
            ->fetchOne();

        if(!$commentinfo){
            return 1001;
        }

        $like=DI()->notorm->users_video_comments_like
            ->select("id")
            ->where("uid='{$uid}' and commentid='{$commentid}' and tenant_id ='{$game_tenant_id}' and video_type =1")
            ->fetchOne();

        if($like){
            DI()->notorm->users_video_comments_like
                ->where("uid='{$uid}' and commentid='{$commentid}' and tenant_id ='{$game_tenant_id}' and video_type =1")
                ->delete();

            DI()->notorm->video_comments
                ->where("id = '{$commentid}' and likes>0")
                ->update( array('likes' => new NotORM_Literal("likes - 1") ) );
            $rs['islike']='0';

        }else{
            DI()->notorm->users_video_comments_like
                ->insert(array("uid"=>$uid,"commentid"=>$commentid,"addtime"=>time(),"touid"=>$commentinfo['uid'],"videoid"=>$commentinfo['videoid']
                ,"tenant_id"=>$game_tenant_id,'video_type'=>1));

            DI()->notorm->video_comments
                ->where("id = '{$commentid}'")
                ->update( array('likes' => new NotORM_Literal("likes + 1") ) );
            $rs['islike']='1';
        }

        $video=DI()->notorm->video_comments
            ->select("likes")
            ->where("id = '{$commentid}'")
            ->fetchOne();

        //获取视频信息
        $videoinfo=DI()->notorm->users_video->select("thumb")->where("id='{$commentinfo['videoid']}'")->fetchOne();

        $rs['likes']=$video['likes'];

        return $rs;
    }

    /* 热门视频 */
    public function getVideoList($uid,$p,$url){

        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;

        $classifyinfo=DI()->notorm->video
            ->select("count(classify) as classifynums,classify")
            ->group("classify desc")
            ->limit($start,$nums)
            ->fetchAll();


        $model = new Model_User();
        /*if (!empty($uid)){
            $rs = $model->getBaseInfo($uid);
            if ($rs['user_type'] == 4){
                $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = 'vip0'")->fetchOne();
                $nums =  $level_name_jurisdiction['watch_number'];
                if ($p>1){
                    return ['code'=> 800,'num'=>$nums];
                }
            }else{
                $nums=20;
            }
        }*/


            $video=DI()->notorm->video
                ->select("*")
                ->order("RAND()")
                ->where('status = 2')
                ->limit($start,$nums)
                ->order("RAND()")
                ->fetchAll();


        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach($video as $k=>$v){

            $userinfo=getUserInfo($v['uid']);
            if(!$userinfo){
                $userinfo['user_nicename']="已删除";
            }

            $video[$k]['is_collection'] = static::isCollection($uid,$v['id']);
            $video[$k]['is_like'] = static::isLike($uid,$v['id']);
            $video[$k]['is_download'] = static::isDownload($uid,$v['id']);
            $video[$k]['is_follow'] = static::isAttention($uid,$v['uid']);
            $video[$k]['userinfo']=$userinfo;
            $video[$k]['datetime']=datetime($v['addtime']);
            if ($v['origin'] != 3) {
                if ($url) {
                    $video[$k]['href'] = $url . $v[$paly_url['viode_table_field']];
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
                }else{

                    $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
                }
            }

        }

        $video['count'] = $classifyinfo;
        return $video;
    }

    /*
     * 标签分类视频
     * 每个用户缓存一段列表到redis，加个过期时间，缓存没有那就重新计算一次，缓存有就每次拿25多个出来给前端
     */
    public function getVideobyclassify($p, $classify, $uid){
        $tenant_id = getTenantId();

        $config_info = getConfigPub($tenant_id);
        $user_info = getUserInfo($uid);
        $vip_info = Model_Login::getInstance()->getUserjurisdiction($uid, $user_info['user_type']);

        $p = $p >= 1 ? $p : 1;
        $page_size = 25;

          if ($classify == '精选') {
               $user_jingxuan_video_list_key = 'short_video_jingxuan_list_' . $tenant_id  . $classify;
               $data = Cache_Video::getInstance()->getJIngxuanId($tenant_id, $uid, $classify);
               $start = ($p-1)*$page_size;
               $counts = $page_size*$p-1;
               $id_list = CustRedis::getInstance()->ZREVRANGE($user_jingxuan_video_list_key,$start,$counts);
               shuffle($id_list);

        }else if($classify == '关注') {
            $user_guanzhu_video_list_key = 'short_video_guanzhu_list_' . $tenant_id  . $uid;
            $data = Cache_Video::getInstance()->getGuanzhuId($tenant_id, $uid, $classify);
            $start = ($p-1)*$page_size;
            $counts = $page_size*$p-1;
            $id_list = CustRedis::getInstance()->ZREVRANGE($user_guanzhu_video_list_key,$start,$counts);

        }else{
                $agent_all_uids = null;
                if($config_info['agent_line_visible'] == 1){  // 网站设置 代理线可见
                    $all_superior_uid = Cache_UsersAgent::getInstance()->getUserAllSuperiorUid($tenant_id, $uid);
                    $all_sub_uid = Cache_UsersAgent::getInstance()->getUserAllSubUid($tenant_id, $uid);
                    $agent_all_uids = array_merge([$uid], $all_superior_uid, $all_sub_uid);
                }

                $classify_info = Cache_VideoClassify::getInstance()->getShortVideoClassifyInfo($tenant_id, $classify);
                if($classify_info['agent_line_visible'] == 1 && $config_info['agent_line_visible'] != 1){  // 短视频分类 代理线可见
                    $all_superior_uid = Cache_UsersAgent::getInstance()->getUserAllSuperiorUid($tenant_id, $uid);
                    $all_sub_uid = Cache_UsersAgent::getInstance()->getUserAllSubUid($tenant_id, $uid);
                    $agent_all_uids = array_merge([$uid], $all_superior_uid, $all_sub_uid);
                    $config_info['agent_line_visible'] = 1;
                }

                if($classify_info['is_lowerlevel'] == 1){ // 短视频分类 仅允许下级
                    $all_superior_uid = Cache_UsersAgent::getInstance()->getUserAllSuperiorUid($tenant_id, $uid);
                    $agent_all_uids = array_merge([$uid], $all_superior_uid);
                    $config_info['agent_line_visible'] = 1;
                }

                $user_play_video_list_key = 'user_will_play_short_video_list_'.$tenant_id.$uid.$classify;
                $user_paly_len = CustRedis::getInstance()->lLen($user_play_video_list_key);
                $user_paly_len = $user_paly_len >= 3 ? $user_paly_len : 0; // 如果小于3个视频，则重新计算列表
                if($user_paly_len == 0){
                    $size = 100;
                    $type_percent = ['private'=>40, 'public'=>40, 'rand'=>20];

                    // 根据百分比和总数量，计算各个类型的数量
                    $type_count_list = calculatePercentCount($size, $type_percent);

                    $list = array();
                    // 私有视频
                    $index = null;
                    $video_data_count = videoCacheCount();
                    for($i=0; $i<$type_count_list['private']; $i++){
                        if($video_data_count == 0){
                            continue;
                        }
                        $data = Cache_Video::getInstance()->getPrivateId($list, $tenant_id, $uid, $classify, $config_info['agent_line_visible'], $agent_all_uids, $index);
                        if($data['id']){
                            array_push($list, $data['id']);
                        }
                        $index = $data['index'];
                        $video_data_count = $data['video_data_count'];
                    }

                    // 公共视频
                    $index = null;
                    $video_data_count = videoCacheCount();
                    for($i=0; $i<$type_count_list['public']; $i++){
                        if($video_data_count == 0){
                            continue;
                        }
                        $data = Cache_Video::getInstance()->getPublicId($list, $tenant_id, $uid, $classify, $index);
                        if($data['id']){
                            array_push($list, $data['id']);
                        }
                        $index = $data['index'];
                        $video_data_count = $data['video_data_count'];
                    }
                    // 随机视频
                    for($i=0; $i<$size; $i++){
                        $private_or_public_id = Cache_Video::getInstance()->getPrivateOrPublicId($list, false,$tenant_id, $classify, $config_info['agent_line_visible'], $agent_all_uids);
                        if($private_or_public_id){
                            array_push($list, $private_or_public_id);
                        }
                    }

                    // 如果为空，则清除用户播放的视频缓存
                    if(empty($list)){
                        Cache_Users::getInstance()->delUserHasWatchVideoCache($uid);
                    }

                    $list = array_unique($list);
                    shuffle($list);

                    // 广告视频处理
                    $num = 0;
                    $new_list = array();
                    $watchnum_show_ad_video = isset($vip_info['watchnum_show_ad_video']) ? $vip_info['watchnum_show_ad_video'] : 0;
                    foreach ($list as $key=>$val){
                        array_push($new_list, $val);
                        $num++;
                        if($watchnum_show_ad_video > 0 && ($num % $watchnum_show_ad_video) == 0){
                            $advertise_id = Cache_Video::getInstance()->getAdvertiseId($tenant_id);
                            if($advertise_id){
                                array_push($new_list, $advertise_id);
                            }
                        }
                    }

                    foreach ($new_list as $key=>$val){
                        CustRedis::getInstance()->rPush($user_play_video_list_key, $val);
                    }
                    CustRedis::getInstance()->expire($user_play_video_list_key, 60*60*3);
                }

                //除去置顶视频的 其他视频 处理，将视频id 写入id_list

                $id_list = array();
                for($i=0; $i<($page_size-$config_info['top_short_video_count']); $i++){
                    $video_id = CustRedis::getInstance()->lPop($user_play_video_list_key);
                    if($video_id){
                        array_push($id_list, $video_id);
                    }
                }

                // 置顶视频处理
                if($p == 1){
                    for($i=0; $i<$config_info['top_short_video_count']; $i++) {
                        $top_id = Cache_Video::getInstance()->getTopId($tenant_id, $classify);
                        if($top_id && !in_array($top_id, $id_list)){
                            array_unshift($id_list, $top_id);
                        }
                    }
                }
        }
        
        //获取长视频可以在短视频展示的 列表
        $data_list = array();
        $long_list = array();
        $cachekey = 'long_video_list_'.$tenant_id;
        $longvideo_id = Cache_Video::getInstance()->getLongVideoidbatch($tenant_id);
   
        if($longvideo_id){
            $video_id = CustRedis::getInstance()->lPop($cachekey);
            array_push($long_list, $video_id);
        }




        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        $protocal_domain = get_protocal().'://' . $_SERVER['HTTP_HOST'];
        foreach ($id_list as $key=>$video_id){
            $video_info = Cache_Video::getInstance()->getShortVideoInfo($tenant_id, $video_id);
            if(!$video_info){
                continue;
            }
            $video_info['is_collection'] = static::isCollection($uid,$video_info['id']);
            $video_info['is_like'] = static::isLike($uid,$video_info['id']);
            $video_info['is_download'] = static::isDownload($uid,$video_info['id']);
            $video_info['is_follow'] = static::isAttention($uid,$video_info['uid']);
            $user_info = Cache_Users::getInstance()->getUserInfoCache($video_info['uid']);
            if(!$user_info){
                $userinfo['user_nicename']="已删除";
            }else{
                $userinfo['id'] = $user_info['id'];
                $userinfo['user_login'] = $user_info['user_login'];
                $userinfo['user_nicename'] = $user_info['user_nicename'];
                $userinfo['avatar'] = get_upload_path($user_info['avatar']);;
            }
            $video_info['userinfo'] = $userinfo;
            if ($video_info['origin'] != 3) {
                //$video_info['href'] = isset($video_info[$paly_url['viode_table_field']])?$paly_url['url']. $video_info[$paly_url['viode_table_field']]: $video_info['href'];
                $video_info['href'] = $video_info['href'] ? $protocal_domain. $video_info[$paly_url['viode_table_field']] : $video_info['href'];
                if($paly_url['name'] == 'minio' && strrpos($video_info['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $video_info['thumb'] = $paly_url['url'] .'/liveprod-store-1039'. $video_info['thumb'];
                }else{
                    $video_info['thumb'] = $paly_url['url'] . $video_info['thumb'];
                }
                $video_info['download_address'] = $download_url['url'] . $video_info[$download_url['viode_table_field']];
            }
            if($video_info['shoptype']==1 || $video_info['shoptype'] == 2){
                $shopinfo = DI()->redis -> hGet('videobindshop',intval($video_info['id']));
                $shopinfo = json_decode($shopinfo,true);
                $shopinfo['shoptype'] = $video_info['shoptype'];
                $shopinfo['shop_value'] = $video_info['shop_value'];
                $video_info['shopinfo'] = $shopinfo;
            }
            Cache_Video::getInstance()->setShortVideoWatchCache($uid, $video_id);
            array_push($data_list, $video_info);
        }
        //精选列表不展示长视频
        if($classify != '精选'){
            foreach ($long_list as $keys=>$video_ids){
                $video_info = Cache_Video::getInstance()->getLongVideoInfo($tenant_id, $video_ids);
                if(!$video_info){
                    continue;
                }
                $user_info = Cache_Users::getInstance()->getUserInfoCache($video_info['uid']);
                if(!$user_info){
                    $userinfo['user_nicename']="已删除";
                }else{
                    $userinfo['id'] = $user_info['id'];
                    $userinfo['user_login'] = $user_info['user_login'];
                    $userinfo['user_nicename'] = $user_info['user_nicename'];
                    $userinfo['avatar'] = get_upload_path($user_info['avatar']);;
                }
                $video_info['userinfo'] = $userinfo;
                if ($video_info['origin'] != 3) {
                    $video_info['href'] = $video_info['href'] ? $protocal_domain. $video_info[$paly_url['viode_table_field']] : $video_info['href'];
                    //$video_info['href'] = isset($video_info[$paly_url['viode_table_field']])?$paly_url['url']. $video_info[$paly_url['viode_table_field']]: $video_info['href'];
                    if($paly_url['name'] == 'minio' && strrpos($video_info['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                        $video_info['thumb'] = $paly_url['url'] .'/liveprod-store-1039'. $video_info['thumb'];
                    }else{
                        $video_info['thumb'] = $paly_url['url'] . $video_info['thumb'];
                    }
                    $video_info['download_address'] = $download_url['url'] . $video_info[$download_url['viode_table_field']];
                }

                array_push($data_list, $video_info);
            }
        }

        // 这个仅作为例子范湖给前端，golang接口那里会使用这个参数
        $lists_params = '{}';
        return array('code' => 0, 'msg' => '', 'info' => $data_list, 'lists_params' => $lists_params);
    }

    /* 关注人视频 */
    public function getAttentionVideo($uid,$p){
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;

        $video=array();
        $attention=DI()->notorm->users_attention
            ->select("touid")
            ->where("uid='{$uid}'")
            ->fetchAll();

        if($attention){

            $uids=array_column($attention,'touid');
            $touids=implode(",",$uids);

            $videoids_s=getVideoBlack($uid);
            $where="uid in ({$touids}) and id not in ({$videoids_s})  and isdel=0 and status=1";

            $video=DI()->notorm->users_video
                ->select("*")
                ->where($where)
                ->order("addtime desc")
                ->limit($start,$nums)
                ->fetchAll();


            if(!$video){
                return 0;
            }
            $download_url =  play_or_download_url(2);
            $paly_url = play_or_download_url(1);
            foreach($video as $k=>$v){
                $video[$k]['userinfo']=getUserInfo($v['uid']);
                $video[$k]['datetime']=datetime($v['addtime']);
                $video[$k]['comments']=NumberCeil($v['comments']);
                $video[$k]['likes']=NumberCeil($v['likes']);
                $video[$k]['steps']=NumberCeil($v['steps']);
                $video[$k]['islike']=(string)ifLike($uid,$v['id']);
                $video[$k]['isstep']=(string)ifStep($uid,$v['id']);
                $video[$k]['isdialect']='0';
                //$video[$k]['musicinfo']=getMusicInfo($video[$k]['userinfo']['user_nicename'],$v['music_id']);

                //$video[$k]['thumb']=get_upload_path($v['thumb']);
                $video[$k]['thumb_s']=get_upload_path($v['thumb_s']);
               // $video[$k]['href']=get_upload_path($v['href']);
                if ($v['origin'] != 3) {
                    $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
                }

            }

        }


        return $video;
    }

    /* 视频详情 */
    public function getVideo($uid,$videoid,$is_search,$url){

        $video = DI()->notorm->video
            ->select("*")
            ->where("id = {$videoid}")
            ->fetchOne();
        if(!$video){
            return 1000;
        }

      /*  $userinfo=getUserInfo($video['uid']);

        if(!$userinfo){
            $userinfo['user_nicename']="已删除";
        }*/
        if ($is_search == 1){
            $scorehot = DI()->redis->zScore('rank_shorhotsearch_list',$video['id']);
            if(!$scorehot){
                $rankhot=DI()->redis -> zAdd('rank_shorhotsearch_list',1,$video['id']);
            }else{
                $rankhot=DI()->redis -> zAdd('rank_shorhotsearch_list',$scorehot+1,$video['id']);
            }
        }
        DI()->notorm->video
            ->where("id = {$videoid}")
            ->update( array('hot_searches' => new NotORM_Literal("hot_searches + 1") ) );
        $video['is_buy'] = static::isBuy($uid,$video['id']);
        $video['is_collection'] = static::isCollection($uid,$video['id']);
        $video['is_like'] = static::isLike($uid,$video['id']);
        $video['is_download'] = static::isDownload($uid,$video['id']);
        $video['is_follow'] = static::isAttention($uid,$video['uid']);
        $video['userinfo']=getUserInfo($video['uid']);
        $video['datetime']=datetime($video['addtime']);
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);

        if ($video['origin'] != 3) {
            if ($url) {
                $video['href'] = $url . $video[$paly_url['viode_table_field']];
                $video['thumb'] = $paly_url['url'] . $video['thumb'];
                $video['download_address'] = $download_url['url'] .$video[$download_url['viode_table_field']];
            }else{

                $video['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $video[$paly_url['viode_table_field']];
                $video['thumb'] = $paly_url['url'] . $video['thumb'];
                $video['download_address'] = $download_url['url'] . $video[$download_url['viode_table_field']];
            }
        }

        return 	$video;
    }

    /* 评论列表 */
    public function getComments($uid,$videoid,$p,$game_tenant_id){
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        $comments=DI()->notorm->video_comments
            ->select("*")
            ->where("videoid='{$videoid}' and parentid='0' and tenant_id = '{$game_tenant_id}' and video_type = 1 ")
            ->order("addtime desc")
            ->limit($start,$nums)
            ->fetchAll();
        foreach($comments as $k=>$v){
            $comments[$k]['userinfo']=getUserInfo($v['uid']);
            $comments[$k]['datetime']=datetime($v['addtime']);
            $comments[$k]['likes']=NumberCeil($v['likes']);
            if($uid){
                $comments[$k]['islike']=(string)$this->ifCommentLike($uid,$v['id']);
            }else{
                $comments[$k]['islike']='0';
            }

            if($v['touid']>0){
                $touserinfo=getUserInfo($v['touid']);
            }
            if(!$touserinfo){
                $touserinfo=(object)array();
                $comments[$k]['touid']='0';
            }
            $comments[$k]['touserinfo']=$touserinfo;

            $count=DI()->notorm->video_comments
                ->where("commentid='{$v['id']}'")
                ->where("tenant_id={$game_tenant_id}")
                ->where("video_type=1")
                ->count();
            $comments[$k]['replys']=$count;

            /* 回复 */
            $reply=DI()->notorm->video_comments
                ->select("*")
                ->where("commentid='{$v['id']}'")
                ->where("tenant_id={$game_tenant_id}")
                ->where("video_type=1")
                ->order("addtime desc")
                ->fetchAll();
            foreach($reply as $k1=>$v1){

                $v1['userinfo']=getUserInfo($v1['uid']);
                $v1['datetime']=datetime($v1['addtime']);
                $v1['likes']=NumberCeil($v1['likes']);
                $v1['islike']=(string)$this->ifCommentLike($uid,$v1['id']);
                if($v1['touid']>0){
                    $touserinfo=getUserInfo($v1['touid']);
                }
                if(!$touserinfo){
                    $touserinfo=(object)array();
                    $v1['touid']='0';
                }

                if($v1['parentid']>0 && $v1['parentid']!=$v['id']){
                    $tocommentinfo=DI()->notorm->video_comments
                        ->select("content,at_info")
                        ->where("id='{$v1['parentid']}'")
                        ->fetchOne();
                }else{
                    $tocommentinfo=(object)array();
                    $touserinfo=(object)array();
                    $v1['touid']='0';
                }
                $v1['touserinfo']=$touserinfo;
                $v1['tocommentinfo']=$tocommentinfo;


                $reply[$k1]=$v1;
            }

            $comments[$k]['replylist']=$reply;
        }

        $commentnum=DI()->notorm->video_comments
            ->where("videoid='{$videoid}'")
            ->where("tenant_id={$game_tenant_id}")
            ->where("video_type=1")
            ->count();
        $rs=array(
            "comments"=>$commentnum,
            "commentlist"=>$comments,
        );

        return $rs;
    }

    /* 回复列表 */
    public function getReplys($uid,$commentid,$p){
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        $comments=DI()->notorm->video_comments
            ->select("*")
            ->where("commentid='{$commentid}'")
            ->order("addtime desc")
            ->limit($start,$nums)
            ->fetchAll();


        foreach($comments as $k=>$v){
            $comments[$k]['userinfo']=getUserInfo($v['uid']);
            $comments[$k]['datetime']=datetime($v['addtime']);
            $comments[$k]['likes']=NumberFormat($v['likes']);
            $comments[$k]['islike']=(string)$this->ifCommentLike($uid,$v['id']);
            if($v['touid']>0){
                $touserinfo=getUserInfo($v['touid']);
            }
            if(!$touserinfo){
                $touserinfo=(object)array();
                $comments[$k]['touid']='0';
            }



            if($v['parentid']>0 && $v['parentid']!=$commentid){
                $tocommentinfo=DI()->notorm->video_comments
                    ->select("content,at_info")
                    ->where("id='{$v['parentid']}'")
                    ->fetchOne();
            }else{

                $tocommentinfo=(object)array();
                $touserinfo=(object)array();
                $comments[$k]['touid']='0';

            }
            $comments[$k]['touserinfo']=$touserinfo;
            $comments[$k]['tocommentinfo']=$tocommentinfo;
        }

        return $comments;
    }



    /* 评论/回复 是否点赞 */
    public function ifCommentLike($uid,$commentid){
        $like=DI()->notorm->users_video_comments_like
            ->select("id")
            ->where("uid='{$uid}' and commentid='{$commentid}'")
            ->fetchOne();
        if($like){
            return 1;
        }else{
            return 0;
        }
    }

    /* 我的视频 */
    public function getMyVideo($uid,$p){
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;

        $video=DI()->notorm->video
            ->select("*")
            ->where('uid=? ',$uid)
            ->order('sort asc')
            ->order("addtime desc")
            ->limit($start,$nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach($video as $k=>$v){
            $video[$k]['userinfo']=getUserInfo($v['uid']);
            $video[$k]['datetime']=datetime($v['addtime']);
            $video[$k]['comments']=NumberFormat($v['comments']);
            $video[$k]['likes']=NumberFormat($v['likes']);
            $video[$k]['steps']=NumberFormat($v['steps']);
            $video[$k]['islike']='0';
            $video[$k]['isattent']='0';
            $video[$k]['isdialect']='0';
            //$video[$k]['musicinfo']=getMusicInfo($video[$k]['userinfo']['user_nicename'],$v['music_id']);
          //  $video[$k]['thumb']=get_upload_path($v['thumb']);
            $video[$k]['thumb_s']=get_upload_path($v['thumb_s']);
            //$video[$k]['href']=get_upload_path($v['href']);
            if ($v['origin'] != 3) {
                $video[$k]['href'] =  'https://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                $video[$k]['thumb'] = $paly_url['url']. $v['thumb'];
                $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
            }

        }


        return $video;
    }
    /* 删除视频 */
    public function del($uid,$videoid){

        $result=DI()->notorm->users_video
            ->select("*")
            ->where("id='{$videoid}' and uid='{$uid}'")
            ->fetchOne();
        if($result){
            // 删除 评论记录
            /*DI()->notorm->users_video_comments
                       ->where("videoid='{$videoid}'")
                       ->delete();
           //删除视频评论喜欢
           DI()->notorm->users_video_comments_like
                       ->where("videoid='{$videoid}'")
                       ->delete();

           // 删除  点赞
            DI()->notorm->users_video_like
                       ->where("videoid='{$videoid}'")
                       ->delete();
           //删除视频举报
           DI()->notorm->users_video_report
                       ->where("videoid='{$videoid}'")
                       ->delete();
           // 删除视频
            DI()->notorm->users_video
                       ->where("id='{$videoid}'")
                       ->delete();	*/

            //将喜欢的视频列表状态修改
            DI()->notorm->users_video_like
                ->where("videoid='{$videoid}'")
                ->update(array("status"=>0));

            DI()->notorm->users_video
                ->where("id='{$videoid}'")
                ->update( array( 'isdel'=>1 ) );
        }
        return 0;
    }

    /* 个人主页视频 */
    public function getHomeVideo($uid,$touid,$p){
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;


        if($uid==$touid){  //自己的视频（需要返回视频的状态前台显示）
            $where=" uid={$uid} and isdel='0' and status=1  and is_ad=0";
        }else{  //访问其他人的主页视频
            $videoids_s=getVideoBlack($uid);
            $where="id not in ({$videoids_s}) and uid={$touid} and isdel='0' and status=1  and is_ad=0";
        }


        $video=DI()->notorm->users_video
            ->select("*")
            ->where($where)
            ->order("addtime desc")
            ->limit($start,$nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach($video as $k=>$v){
            $video[$k]['userinfo']=getUserInfo($v['uid']);
            $video[$k]['datetime']=datetime($v['addtime']);
            $video[$k]['comments']=NumberFormat($v['comments']);
            $video[$k]['likes']=NumberFormat($v['likes']);
            $video[$k]['steps']=NumberFormat($v['steps']);
            $video[$k]['islike']=(string)ifLike($uid,$v['id']);
            $video[$k]['isstep']=(string)ifStep($uid,$v['id']);
            $video[$k]['isattent']=(string)isAttention($uid,$v['uid']);
            $video[$k]['isdialect']='0';
            //$video[$k]['musicinfo']=getMusicInfo($video[$k]['userinfo']['user_nicename'],$v['music_id']);

           // $video[$k]['thumb']=get_upload_path($v['thumb']);
            $video[$k]['thumb_s']=get_upload_path($v['thumb_s']);
            //$video[$k]['href']=get_upload_path($v['href']);
            if ($v['origin'] != 3) {
                $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
            }
        }

        return $video;

    }
    /* 举报 */
    public function report($data) {

        $video=DI()->notorm->users_video
            ->select("uid")
            ->where("id='{$data['videoid']}'")
            ->fetchOne();
        if(!$video){
            return 1000;
        }

        $data['touid']=$video['uid'];

        $result= DI()->notorm->users_video_report->insert($data);
        return 0;
    }




    public function getRecommendVideos($uid,$p,$isstart){
        if($p<1){
            $p=1;
        }
        $pnums=20;
        $start=($p-1)*$pnums;



        $configPri=getConfigPri();
        $video_showtype=$configPri['video_showtype'];





        if($video_showtype==0){ //随机

            if($p==1){
                DI()->redis -> delete('readvideo_'.$uid);
            }

            //去除看过的视频
            $where=array();
            $readLists=DI()->redis -> Get('readvideo_'.$uid);
            if($readLists){
                $where=json_decode($readLists,true);
            }

            $info=DI()->notorm->users_video
                ->where("isdel=0 and status=1 and is_ad=0")
                ->where('not id',$where)
                ->order("rand()")
                ->limit($pnums)

                ->fetchAll();
            $where1=array();
            foreach ($info as $k => $v) {
                if(!in_array($v['id'],$where)){
                    $where1[]=$v['id'];
                }
            }

            //将两数组合并
            $where2=array_merge($where,$where1);

            DI()->redis -> set('readvideo_'.$uid,json_encode($where2));



        }else{

            //获取私密配置里的评论权重和点赞权重
            $comment_weight=$configPri['comment_weight'];
            $like_weight=$configPri['like_weight'];
            $share_weight=$configPri['share_weight'];

            $prefix= DI()->config->get('dbs.tables.__default__.prefix');

            //热度值 = 点赞数*点赞权重+评论数*评论权重+分享数*分享权重
            //转化率 = 完整观看次数/总观看次数
            //排序规则：（曝光值+热度值）*转化率
            //曝光值从视频发布开始，每小时递减1，直到0为止


            /*废弃$info=DI()->notorm->users_video->queryAll("select *,format(watch_ok/views,2) as aaa, (ceil(comments *".$comment_weight." + likes *".$like_weight." + shares *".$share_weight.") + show_val)*format(watch_ok/views,2) as recomend from ".$prefix."users_video where isdel=0 and status=1  order by recomend desc,addtime desc limit ".$start.",".$pnums);*/

            $info=DI()->notorm->users_video
                ->select("*,(ceil(comments * ".$comment_weight." + likes * ".$like_weight." + shares * ".$share_weight.") + show_val)* if(format(watch_ok/views,2) >1,'1',format(watch_ok/views,2)) as recomend")
                ->where("isdel=0 and status=1 and is_ad=0")
                // ->where('not id',$where)
                ->order("recomend desc,addtime desc")
                ->limit($start,$pnums)
                ->fetchAll();
        }


        if(!$info){
            return 1001;
        }

        foreach ($info as $k => $v) {
            $info[$k]['userinfo']=getUserInfo($v['uid']);
            $info[$k]['datetime']=datetime($v['addtime']);
            $info[$k]['thumb']=get_upload_path($v['thumb']);
            $info[$k]['thumb_s']=get_upload_path($v['thumb_s']);
            $info[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']);
            if($uid<0){
                $info[$k]['islike']='0';
                $info[$k]['isattent']='0';

            }else{
                $info[$k]['islike']=(string)ifLike($uid,$v['id']);
                $info[$k]['isattent']=(string)isAttention($uid,$v['uid']);
            }


            //$info[$k]['musicinfo']=getMusicInfo($info[$k]['userinfo']['user_nicename'],$v['music_id']);
            $info[$k]['href']=get_upload_path($v['href']);
            if($v['ad_url']){
                $info[$k]['ad_url']=get_upload_path($v['ad_url']);
            }


            $info[$k]['isstep']='0'; //以下字段基本无用
            unset($info[$k]['status']);
            unset($info[$k]['ad_endtime']);
            unset($info[$k]['orderno']);
        }


        return $info;
    }

    /*获取附近的视频*/
    public function getNearby($uid,$lng,$lat,$p){
        if($p<1){
            $p=1;
        }
        $pnum=20;
        $start=($p-1)*$pnum;

        $prefix= DI()->config->get('dbs.tables.__default__.prefix');

        $info=DI()->notorm->users_video->queryAll("select *, round(6378.138 * 2 * ASIN(SQRT(POW(SIN(( ".$lat." * PI() / 180 - lat * PI() / 180) / 2),2) + COS(".$lat." * PI() / 180) * COS(lat * PI() / 180) * POW(SIN((".$lng." * PI() / 180 - lng * PI() / 180) / 2),2))) * 1000) AS distance FROM ".$prefix."users_video  where uid !=".$uid." and isdel=0 and status=1  and is_ad=0 order by distance asc,addtime desc limit ".$start.",".$pnum);

        if(!$info){
            return 1001;
        }


        foreach ($info as $k => $v) {
            $info[$k]['userinfo']=getUserInfo($v['uid']);
            $info[$k]['datetime']=datetime($v['addtime']);
            $info[$k]['thumb']=get_upload_path($v['thumb']);
            $info[$k]['thumb_s']=get_upload_path($v['thumb_s']);
            $info[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']);
            if($uid<0){
                $info[$k]['islike']='0';
                $info[$k]['isattent']='0';

            }else{
                $info[$k]['islike']=(string)ifLike($uid,$v['id']);
                $info[$k]['isattent']=(string)isAttention($uid,$v['uid']);
            }


            //$info[$k]['musicinfo']=getMusicInfo($info[$k]['userinfo']['user_nicename'],$v['music_id']);

            $info[$k]['href']=get_upload_path($v['href']);

            $info[$k]['distance']=distanceFormat($v['distance']);

            $info[$k]['isstep']='0'; //以下字段基本无用
            unset($info[$k]['status']);

        }

        return $info;
    }

    /* 举报分类列表 */
    public function getReportContentlist() {

        $reportlist=DI()->notorm->users_video_report_classify
            ->select("*")
            ->order("orderno asc")
            ->fetchAll();
        if(!$reportlist){
            return 1001;
        }

        return $reportlist;

    }

    /*更新视频看完次数*/
    public function setConversion($videoid,$uid,$game_tenant_id,$is_search,$is_record){
        
        $video=DI()->notorm->video
            ->select("uid,title,first_watch_time,watchtimes,comments,collection,likes")
            ->where("id='{$videoid}'")
            ->fetchOne();
        if(!$video){
            return ['code' => 1001];
        }
        //$model = new Model_User();
        $rs = DI()->notorm->users->where("id='{$uid}'")->select('user_type')->fetchOne();
        $watch_count =DI()->notorm->video_watch_record
            ->select("uid")
            ->where("uid='{$uid}'")
            ->where("video_type='1'")
            ->sum('watch_count');

        /* $count =DI()->notorm->video_watch_record
            ->select("uid")
                ->where("uid='{$uid}'")
                ->where("video_type='1'")
                ->count();*/
       /* if ($rs['user_type'] == 4) {
            $config = getConfigPri();
            if (!($config['notice_pernum'] > 0)){ // 优先使用网站设置-》游客配置的设置，如果设置0，则使用vip等级那里设置的
                $level_name = 'vip0';
                $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = '{$level_name}'")->fetchOne();
                if ($level_name_jurisdiction['watch_number'] != 0) {
                    if ($count >= $level_name_jurisdiction['watch_number']) {
                        return ['code' => 800];
                    }
                }
            }
        }*/
      /*  $time = time();
        $userVip=DI()->notorm->users_vip
            ->where("uid = '{$uid}' and  endtime >'{$time}'")
            ->order('grade desc')
            ->fetchOne();
        if (empty($userVip)){
            $level_name  = 'vip0';
        }else{
            $vipInfo  =DI()->notorm->vip
                ->where("id = '{$userVip['vip_id']}'")
                ->fetchOne();
            $level_name  = $vipInfo['name'];
        }

        $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = '{$level_name}'")->fetchOne();
        if ($level_name_jurisdiction['watch_number'] != 0 && $rs['user_type'] != 4) {
            $watch_number = $level_name_jurisdiction['watch_number'] + $rs['watch_num'];
            if ($count >= $watch_number) {
                return ['code' => 1002, 'msg' => $vipInfo['name'] . '只能观看' .  $level_name_jurisdiction['watch_number'] . '条视频'];
            }
        }*/
        if (empty($is_record)){
            if ($is_search == 1){
                $scorehot = DI()->redis->zScore('rank_shothotsearch_list',$video['title']);
                if(!$scorehot){
                    $rankhot=DI()->redis -> zAdd('rank_shothotsearch_list',1,$video['title']);
                }else{
                    $rankhot=DI()->redis -> zAdd('rank_shothotsearch_list',$scorehot+1,$video['title']);
                }
            }

            $records =DI()->notorm->video_watch_record
                ->select("uid")
                ->where("videoid='{$videoid}'")
                ->where("uid='{$uid}'")
                ->where("video_type='1'")
                ->fetchOne();

            //更新视频看完次数
            if ($video['first_watch_time']){
                $res=DI()->notorm->video
                    ->where("id = '{$videoid}' ")
                    ->update(
                        array('watchtimes' => new NotORM_Literal("watchtimes + 1"),'last_watch_time'=>time() )
                    );
            }else{
                $res=DI()->notorm->video
                    ->where("id = '{$videoid}' ")
                    ->update(
                        array('watchtimes' => new NotORM_Literal("watchtimes + 1"),
                            'first_watch_time'=>time(),
                            'last_watch_time'=>time(),
                        )
                    );
            }

            if(!$records){
                // 添加短视频观看次数
                $record['uid']= $uid;
                $record['videoid'] = $videoid;
                $record['watchtime'] = date('Y-m-d H:i:s',time());
                $record['tenant_id'] = $game_tenant_id;
                $record['video_type'] = 1;
                $record['addtime'] = time();
                $record['updatetime'] = time();
                $record['watch_count'] = 1;
                $insertrecord=DI()->notorm->video_watch_record
                    ->insert($record);
            }else{
                $insertrecord=DI()->notorm->video_watch_record
                    ->where("videoid='{$videoid}'")
                    ->where("uid='{$uid}'")
                    ->where("video_type='1'")
                    ->update( array('watch_count' => new NotORM_Literal("watch_count + 1"),'updatetime'=>time() ) );
            }
            $video =DI()->notorm->video
                ->select("watchtimes,comments,collection,likes")
                ->where("id='{$videoid}'")
                ->fetchOne();
        }
        $config = getConfigPri();
        if ($rs['user_type'] == 4) {
            // 优先使用网站设置-》游客配置的设置，如果设置0，则使用vip等级那里设置的
            $watchnum_ad = $config['notice_pernum'];
            if ($config['notice_pernum'] > 0 && $watch_count >= $watchnum_ad && ($watch_count % $watchnum_ad) == 0) {
                return array('code' => 2059, 'msg' => codemsg('2059'), 'info' => array(['ad_link'=>$config['ad_link'], 'ad_time'=>$config['ad_time'], 'ad_link_type'=>$config['ad_link_type']]));
            }
        }
        if($rs['user_type'] != 4){
            $time = time();
            $userVip=DI()->notorm->users_vip->where("uid = '{$uid}' and  endtime >'{$time}'")->order('grade desc')->fetchOne();
            if (empty($userVip)){
                $level_name  = 'vip0';
            }else{
                $vipInfo  =DI()->notorm->vip->where("id = '{$userVip['vip_id']}'")->fetchOne();
                $level_name  = $vipInfo['name'];
            }

            $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = '{$level_name}'")->fetchOne();
            $watchnum_ad = $level_name_jurisdiction['watchnum_ad'];
            if ($watchnum_ad > 0 && $watch_count >= $watchnum_ad && ($watch_count % $watchnum_ad) == 0) {
                return array('code' => 2061, 'msg' => codemsg('2061'), 'info' => array(['ad_link'=>$config['ad_link'], 'ad_time'=>$config['ad_time'], 'ad_link_type'=>$config['ad_link_type']]));
            }
        }
        $current = time();
        $user =DI()->notorm->users_buy_longvip
            ->select("vip_level")
            ->where("uid='{$uid}' and endtime>'{$current}'")
            ->fetchOne();
        if(empty($user)){
            $video['vip_level'] = 0;
        }else{
            $video['vip_level'] = 1;
        }
        return [$video];
    }

    /*  获取我的收藏*/
    public function getCollection($uid,$p,$game_tenant_id){
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        $comments=DI()->notorm->users_video_collection
            ->select("*")
            ->where("uid='{$uid}'  and tenant_id = '{$game_tenant_id}' and video_type = 1 ")
            ->order("addtime desc")
            ->limit($start,$nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach($comments as $k=>$v){
            $comments[$k]['userinfo']=getUserInfo($v['uid']);

            $videoInfo =DI()->notorm->video
                ->select("*")
                ->where("id='{$v['videoid']}'")
                ->fetchOne();
            if ($videoInfo && $videoInfo['origin'] != 3) {
                if($paly_url['name'] == 'minio' && strrpos($videoInfo['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $videoInfo['thumb'] = $paly_url['url']  .'/liveprod-store-1039'. $videoInfo['thumb'];
                }else{
                    $videoInfo['thumb'] = $paly_url['url'] . $videoInfo['thumb'];
                }
                $videoInfo['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $videoInfo[$paly_url['viode_table_field']];
                $videoInfo['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
            }
            if ($videoInfo){
                $videoInfo['is_collection'] = 1;
                $reply[] =  $videoInfo;
            }
        }
        return $reply;
    }

    public  function getMyShotVideo($uid,$game_tenant_id, $p,$status)
    {
        if ($p < 1) {
            $p = 1;
        }
        $nums = 20;
        $start = ($p - 1) * $nums;
        if ($status){
            $where = "uid='{$uid}'  and status = '{$status}'  ";
        }else{
            $where = "uid='{$uid}'  and status in(1,2,3)  ";
        }
        $video = DI()->notorm->video
            ->where($where)
            ->select("*")
            ->order("id desc")
            ->limit($start, $nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($video as $k => $v) {
            $video[$k]['comments'] = NumberFormat($v['comments']);
            $video[$k]['likes'] = NumberFormat($v['likes']);
            //$video[$k]['thumb'] = get_upload_path($v['thumb']);
            //$video[$k]['href'] = get_upload_path($v['href']);
            if ($v['origin'] != 3) {
                $video[$k]['href'] =  'https://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                if($paly_url['name'] == 'minio' && strrpos($v['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $video[$k]['thumb'] = $paly_url['url'] .'/liveprod-store-1039'. $v['thumb'];
                }else{
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                }
                $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
            }
        }
        return $video;
    }

    public function downloadVideo($uid,$videoid,$game_tenant_id){
        $video=DI()->notorm->video
            ->where("id = '{$videoid}'")
            ->fetchOne();

        if(!$video){
            return 1001;
        }
        if($video['uid']==$uid){
            return 1002;//不能下载自己的视频
        }
        $tenant_id = getTenantId();
        $like=DI()->notorm->video_download
            ->select("id")
            ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id ='{$game_tenant_id}' and video_type =1")
            ->fetchOne();
        if(!$like){ // 重复下载不计算
            DI()->notorm->video_download
                ->insert(array("uid"=>$uid,"videoid"=>$videoid,"addtime"=>time(),"tenant_id"=>$game_tenant_id,'video_type'=>1 ));

            DI()->notorm->video
                ->where("id = '{$videoid}'")
                ->update( array('download_times' => new NotORM_Literal("download_times + 1") ) );
        }

        return 0;
    }

    /**下载列表
     * @param $uid
     * @param $p
     * @param $game_tenant_id
     */
    public  function getMydownload($uid,$game_tenant_id,$p){

        if($p<1){
            $p=1;
        }
        $pnums=20;
        $start=($p-1)*$pnums;
        $res=DI()->notorm->video_download
            ->where("uid = '{$uid}' and video_type = 1")
            ->limit($start,$pnums)
            ->fetchAll();
        $reply = array();

        foreach($res as $k=>$v){
            $video=DI()->notorm->video
                ->select("*")
                ->where("id='{$v['videoid']}'")
                ->fetchOne();
            if ($video){
                $video['download_id'] = $v['id'];
                $reply[] = $video;
            }

        }

        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($reply as $key=>$value){
            if ($value['origin'] != 3) {
                $reply[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                $reply[$key]['thumb'] = $paly_url['url']. $value['thumb'];
                $reply[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
            }
        }
        return  $reply;
    }

    public  function guessLikeShotVide($uid,$p){

        if($p<1){
            $p=1;
        }
        $pnums=20;
        $start=($p-1)*$pnums;
        $res=DI()->notorm->video
            ->limit($start,$pnums)
            ->order('likes desc')
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($res as $key=>$value){
            if ($value['origin'] != 3) {
                $res[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                $res[$key]['thumb'] =$paly_url['url'] . $value['thumb'];
                $res[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
            }
        }
        return  $res;
    }

 /*   public  function getShotVideoLabel(){

        $res=DI()->notorm->video_label
            ->where('is_delete = 1')
            ->order('sort asc')
            ->fetchAll();
        $rs=array(
            "videoinfo"=>$res,
        );

        return  $rs;
    }*/
    public  function getShotVideoRecommend($uid,$p,$url){
        if($p<1){
            $p=1;
        }
        $pnums=20;
        $start=($p-1)*$pnums;
        $res=DI()->notorm->video
            ->order('RAND()')
            ->where("status = 2")
            //->where("status = 2 and uid != '{$uid}'")
            ->limit($start, $pnums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($res as $key => $value){
            $res[$key]['is_like'] = static::isLike($uid,$value['id']);
            $res[$key]['is_download'] = static::isDownload($uid,$value['id']);
            $res[$key]['is_follow'] = static::isAttention($uid,$value['uid']);
            $res[$key]['userinfo']=getUserInfo($value['uid']);
            $self_watch_count = DI()->notorm->video_watch_record->where("uid ='{$uid}' and videoid = '{$value['id']}' and video_type = 1")->fetchone();
            $res[$key]['self_watch_count']= $self_watch_count['watch_count'];
            if ($value['origin'] != 3) {
                if ($url) {
                    $res[$key]['href'] = $url . $value[$paly_url['viode_table_field']];
                    $res[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    $res[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }else{

                    $res[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                    $res[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    $res[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }
            }

        }
        return  $res;
    }


    //根据点赞数排序
    public  function getShotVideoByLikes($uid,$dataType,$cycle,$isSurge,$p){
        if($p<1){
            $p=1;
        }
        $prefix= DI()->config->get('dbs.tables.__default__.prefix');
        $pnums=10;
        $start=($p-1)*$pnums;
        $where = "status = 2 and uid != '{$uid}'";
        if ($dataType == 2){
            // 最新上传
            $order = 'check_date desc';
            $res =DI()->notorm->video
                ->where($where)
                ->order($order)
                ->limit($start,$pnums)
                ->fetchAll();
        }else{ // 点赞
            $time  = strtotime('-7 days');
            $map = "v.status = 2 and v.uid != '{$uid}' and  c.addtime > '{$time}' and c.video_type =1 ";
            $res  =DI()->notorm->users_video_like
                ->queryAll("select v.* ,count(videoid)  from  {$prefix}users_video_like   as c left join {$prefix}video v on c.videoid=v.id where {$map} GROUP BY videoid ORDER BY count(videoid) desc limit {$start},$pnums ");
            }
        if ($isSurge ==1){ // 人气飙升
            if ($cycle == 1){ // 周榜
                $time = strtotime('-7 days');
            }elseif ($cycle == 2){ // 月榜
                $time = strtotime('-30 days');
            }
            $map = "v.status = 2 and v.uid != '{$uid}' and  c.addtime > '{$time}' and c.video_type =1 ";
            $res  =DI()->notorm->users_video_collection
                ->queryAll("select v.* ,count(videoid)  from  {$prefix}users_video_collection   as c left join {$prefix}video v on c.videoid=v.id where {$map} GROUP BY videoid ORDER BY count(videoid) desc limit {$start},3");
                /*->where("addtime > '{$time}' and video_type =2 ")
                ->select("videoid,count('videoid') as count")
                ->order("count('videoid') desc")
                ->limit(0,3)
                ->group('videoid')->fetchAll();*/

        }
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($res as $key => $value){
            $res[$key]['is_like'] = static::isLike($uid,$value['id']);
            $res[$key]['is_download'] = static::isDownload($uid,$value['id']);
            $res[$key]['is_follow'] = static::isAttention($uid,$value['uid']);
            $res[$key]['userinfo']=getUserInfo($value['uid']);
            if ($value['origin'] != 3) {
                $res[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                $res[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                $res[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
            }
        }
        return  $res;
    }

    //
    public  function getHotLabelData($uid,$dataType,$p){
        if($p<1){
            $p=1;
        }
        $pnums=10;
        $start=($p-1)*$pnums;
        $where = "status = 2 and uid != '{$uid}'";
        $time = strtotime('-7 days');

        if ($dataType == 1){
            // 最高人气
            //$order = 'collection desc';
            $videoInfo = DI()->notorm->users_video_collection
                ->where(" addtime > '{$time}' and video_type =1 ")
                ->select("videoid,count('videoid') as count")
                ->order("count('videoid') desc")
                ->group('videoid')->fetchAll();
        }else if ($dataType == 2){
            $videoInfo = DI()->notorm->video_download
                ->where(" addtime > '{$time}' and video_type =1 ")
                ->select("videoid,count('videoid') as count")
                ->order("count('videoid') desc")
                ->group('videoid')->fetchAll();
        }else{
            $videoInfo = DI()->notorm->video_watch_record
                ->where(" addtime > '{$time}' and video_type =1 ")
                ->select("videoid,count('videoid') as count")
                ->order("count('videoid') desc")
                ->group('videoid')->fetchAll();
        }
        $videoId = array();
        foreach ($videoInfo as  $value){
            $videoId[] = $value['videoid'];
        }
        $videoId = implode(',',$videoId);

        if (!empty($videoId)){
            $where .= " and id  in ({$videoId})";
        }
        $labelArray = DI()->notorm->video_label
           ->where('type = 1')
            ->order('sort asc')
            ->fetchAll();
        $srintg= '';
        $res = [];
        if (!empty($labelArray)) {
            foreach ($labelArray as $key => $value) {
                if ($key == 0){
                    $srintg  = "label = '{$value['label']}'";
                }else{
                    $srintg.= " or label = '{$value['label']}'";
                }

            }
            $where.= "and ({$srintg})";

            $videoinfo = DI()->notorm->video
                ->select("*")
                ->where($where)
                ->order("id desc")
                ->limit($start, $pnums)
                ->fetchOne();
            if($videoinfo){
                $res[] = $videoinfo;
            }
            //$label = implode(',',$labelList);
           // $where .= "  and label in '{$labelList}'";  //上架且审核通过*/
        }
      /*  }else{
            $res=DI()->notorm->video
                ->where($where)
                ->order($order)
                ->limit($start, $pnums)
                ->fetchAll();
        }*/
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($res as $key => $value){
          /*  $res[$key]['is_like'] = static::isLike($uid,$value['id']);
            $res[$key]['is_download'] = static::isDownload($uid,$value['id']);
            $res[$key]['is_follow'] = static::isAttention($uid,$value['uid']);
            $res[$key]['userinfo']=getUserInfo($value['uid']);*/
            if ($value['origin'] != 3) {
                $res[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                $res[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                $res[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
            }
        }
        return  $res;
    }

    /* 热门视频 */
    public function getSearchContent($uid,$p,$searchcontent,$url){

        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        $where = ' status =2 ';
        if(!empty($searchcontent)){
            $where .= " and   (label like '%".$searchcontent."%'or    title like '%".$searchcontent."%' )";
        }


        $video=DI()->notorm->video
            ->select("*")
            ->order('sort asc')
            ->limit($start,$nums)
            ->where($where)
            ->fetchAll();

        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($video as $k => $v) {

            if ($v['origin'] != 3) {
                if ($url) {
                    $video[$k]['href'] = $url . $v[$paly_url['viode_table_field']];
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
                }else{

                    $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
                }
            }
        }
        return $video;
    }
    public  function getHotPerformer($uid,$tenant_id,$p){
        if ($p < 1) {
            $p = 1;
        }
        $nums = 20;
        $start = ($p - 1) * $nums;

        $video = DI()->notorm->video
            ->select("*")
            ->where('is_performer  = 1 and status = 2 ')
            ->order("watchtimes desc")
            ->limit($start, $nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($video as $k => $v) {
            $video[$k]['comments'] = NumberFormat($v['comments']);
            $video[$k]['likes'] = NumberFormat($v['likes']);
            //$video[$k]['thumb'] = get_upload_path($v['thumb']);
           // $video[$k]['href'] = get_upload_path($v['href']);
            if ($v['origin'] != 3) {
                $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] .$v[$paly_url['viode_table_field']];
                $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                $video[$k]['download_address'] = $download_url['url'] .  $v[$download_url['viode_table_field']];
            }
        }
        return $video;
    }


    public  function getHotVideo($uid,$tenant_id,$p,$url){
        if ($p < 1) {
            $p = 1;
        }
        $nums = 20;
        $start = ($p - 1) * $nums;

        $video = DI()->notorm->video
            ->select("*")
            ->where('status = 2')
            ->order("watchtimes desc")
            ->limit($start, $nums)
            ->fetchAll();
        $paly_url = play_or_download_url(1);
        $download_url =  play_or_download_url(2);
        foreach ($video as $k => $v) {
            $video[$k]['comments'] = NumberFormat($v['comments']);
            $video[$k]['watchtimes'] = NumberFormat($v['watchtimes']);
            //$video[$k]['likes'] = NumberFormat($v['likes']);
            //$video[$k]['thumb'] = get_upload_path($v['thumb']);

            if ($v['origin'] != 3) {
                if ($url) {
                    $video[$k]['href'] = $url . $v[$paly_url['viode_table_field']];
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
                }else{

                    $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
                }
            }



        }
        return $video;
    }

    public  function uploadVideo($uid,$label,$title,$desc,$region,$years){
        $tenant_id = getTenantId();
        $user_info = getUserInfo($uid);

        if($user_info['user_type'] == 4){ // 游客不能上传视频
            return array('code' => 505, 'msg' => codemsg('505'), 'info' => []);
        }
        if($user_info['user_type'] == 3){ // 虚拟会员不能上传视频
            return array('code' => 506, 'msg' => codemsg('506'), 'info' => []);
        }

        $exist_info = DI()->notorm->video->where('uid = ? and status in(-1,1,2) and title = ?', intval($uid), $title)->fetchOne();
        if($exist_info){
            return array('code' => 503, 'msg' => codemsg('503'), 'info' => []);
        }

        // 普通用户 限制上传视频数量
        $vip_info = Model_Login::getInstance()->getUserjurisdiction($uid, $user_info['user_type']);
        if($vip_info['new_level'] == '0'){
            $upload_count = DI()->notorm->video->where('uid = ?', intval($uid))->count();
            if($upload_count >= $vip_info['limit_upload_video_count']){
                return array('code' => 504, 'msg' => codemsg('504'), 'info' => []);
            }
        }

        $videoinfo = getCutvideo($tenant_id);
        if(isset($videoinfo['data']) && isset($videoinfo['data']['fileStoreKey']) && $videoinfo['data']['fileStoreKey']){
            $filestorekey = $videoinfo['data']['fileStoreKey'];
        }else{
            return array('code' => 500, 'msg' => codemsg('500'), 'info' => array());
        }

        $data = array(
            'uid' => $uid,
            'user_login' => $user_info['user_login'],
            'user_type' => intval($user_info['user_type']),
            'tenant_id' => intval($tenant_id),
            'classify' => $label,
            'title' => $title,

            'create_date' => date('Y-m-d H:i:s',time()),
            'create_time' => time(),
            'origin' => 1,
            'is_performer' => 0,
            'status' => 1,
            'filestorekey' => $filestorekey,
        );
        if ($region){
            $data['region'] =$region;
        }
        if ($years){
            $data['years'] =$years;
        }
        if ($desc){
            $data['desc'] =$desc;
        }
        try {
            $result = DI()->notorm->video->insert($data);
        }catch (\Exception $e){
            logapi($data, '【短视频新增】失败：'.$e->getMessage());
            return array('code' => 502, 'msg' => codemsg('502'), 'info' => []);
        }
        return array('code' => 0, 'msg' => '', 'info' => [['video_id'=>intval($result['id']), 'title'=>$result['title']]]);
    }

    public  function addNewVideo($uid,$classify,$title,$desc,$years){
        $tenant_id = getTenantId();
        $user_info = getUserInfo($uid);

        if($user_info['user_type'] == 4){ // 游客不能上传视频
            return array('code' => 505, 'msg' => codemsg('505'), 'info' => []);
        }
        if($user_info['user_type'] == 3){ // 虚拟会员不能上传视频
            return array('code' => 506, 'msg' => codemsg('506'), 'info' => []);
        }

        $config = getConfigPub($tenant_id);
        if(!$config['url_of_push_to_java_cut_video']){
            logapi([$config['url_of_push_to_java_cut_video']],  '【短视频新增】失败：未设置视频上传url');
            return array('code' => 502, 'msg' => codemsg('502'), 'info' => []);
        }
        $upload_video_url = explode("\n",$config['url_of_push_to_java_cut_video']);
        foreach ($upload_video_url as $key=>$val) {
            $val = trim($val);
            if (!$val) {
              unset($upload_video_url[$key]);
              continue;
            }
            $upload_video_url[$key] = $val;
        }

        $exist_info = DI()->notorm->video->where('uid = ? and status in(-1,1,2) and title = ?', intval($uid), $title)->fetchOne();
        /*if($exist_info){
            return array('code' => 503, 'msg' => codemsg('503'), 'info' => []);
        }*/

        // 普通用户 限制上传视频数量
      /*  $vip_info = Model_Login::getInstance()->getUserjurisdiction($uid, $user_info['user_type']);
        if($vip_info['new_level'] == '0'){
            $upload_count = DI()->notorm->video->where('uid = ?', intval($uid))->count();
            if($upload_count >= $vip_info['limit_upload_video_count']){
                return array('code' => 504, 'msg' => codemsg('504'), 'info' => []);
            }
        }*/

        $data = array(
            'uid' => $uid,
            'user_login' => $user_info['user_login'],
            'user_type' => intval($user_info['user_type']),
            'tenant_id' => intval($tenant_id),
            'classify' => $classify,
            'title' => $title,

            'create_date' => date('Y-m-d H:i:s',time()),
            'create_time' => time(),
            'origin' => 1,
            'is_performer' => 0,
            'status' => 1,
            'desc' => $desc ? $desc : '',
            'years' => $years ? $years : '',
            'filestorekey' => '',
        );
        try {
            $result = DI()->notorm->video->insert($data);
        }catch (\Exception $e){
            logapi($data, '【短视频新增】失败：'.$e->getMessage());
            return array('code' => 502, 'msg' => codemsg('502'), 'info' => []);
        }
        return array('code' => 0, 'msg' => '', 'info' => [['video_id'=>intval($result['id']), 'title'=>$result['title'], 'upload_video_url'=>$upload_video_url]]);
    }

    public  function updateVideoInfo($uid, $video_id, $file_store_key){
        $tenant_id = getTenantId();
        $file_store_key = trim($file_store_key);
        if(!$uid || !$video_id || !$file_store_key){
            return array('code' => 703, 'msg' => codemsg('703'), 'info' => []);
        }
        try {
            $data = array(
                'filestorekey' => $file_store_key,
            );
            DI()->notorm->video->where(['id'=>intval($video_id), 'uid'=>intval($uid), 'tenant_id'=>intval($tenant_id)])->update($data);
        }catch (\Exception $e){
            logapi($data, '【短视频信息更新】失败：'.$e->getMessage());
            return array('code' => 501, 'msg' => codemsg('501'), 'info' => []);
        }
        return array('code' => 0, 'msg' => '', 'info' => []);
    }

    public function getconcentrationVideo($p,$url){
        if ($p < 1) {
            $p = 1;
        }
        $nums = 20;
        $start = ($p - 1) * $nums;
        $res=DI()->notorm->video
            ->order('RAND()')
            ->where("status = 2 ")
            ->limit($start, $nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($res as $key => $value){
            if ($value['origin'] != 3) {
                if ($url) {
                    $res[$key]['href'] = $url . $value[$paly_url['viode_table_field']];
                    $res[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    $res[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }else{
                    $res[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                    $res[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    $res[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }
            }
            $self_watch_count = DI()->notorm->video_watch_record->where(" videoid = '{$value['id']}' and video_type = 1")->fetchone();
            $res[$key]['self_watch_count']= $self_watch_count['watch_count'];

        }
        return  $res;
    }

    public  function getAuthurVideo($uid,$liveuid, $p,$url)
    {
        if ($p < 1) {
            $p = 1;
        }
        $nums = 30;
        $start = ($p - 1) * $nums;
        $likecount = 0;
        $video = DI()->notorm->video
            ->select("*")
            ->where('uid=?  and status = 2 ', $liveuid)
            ->order("id desc")
            ->limit($start, $nums)
            ->fetchAll();

        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);

        foreach ($video as $k => $v) {
            $video[$k]['comments'] = NumberCeil($v['comments']);
            $video[$k]['likes'] = NumberCeil($v['likes']);
           // $likecount +=  NumberFormat($v['likes']);
            if ($v['origin'] != 3) {
                $video[$k]['href'] ='https://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];

            }
        }

        $videolong = DI()->notorm->video_long
            ->select("*")
            ->where('uid=?  and status = 2 ', $liveuid)
            ->order("id desc")
            ->limit($start, $nums)
            ->fetchAll();
        $videolikecount= DI()->notorm->video
            ->select("*")
            ->where('uid=?  and status = 2 ', $liveuid)
            ->order("id desc")
            ->sum('likes');

        $longlikecount= DI()->notorm->video_long
            ->select("*")
            ->where('uid=?  and status = 2 ', $liveuid)
            ->order("id desc")
            ->sum('likes');
        $likecount = NumberCeil ($videolikecount + $longlikecount);
        foreach ($videolong as $k => $v) {
            $videolong[$k]['comments'] = NumberCeil($v['comments']);
            $videolong[$k]['likes'] = NumberCeil($v['likes']);
           // $likecount +=  NumberFormat($v['likes']);
            if ($v['origin'] != 3) {
                $videolong[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                $videolong[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                $videolong[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];


            }
        }
       /* $follows=getFollownums($liveuid);
        $fans=getFansnums($liveuid);*/
        $follows  = getUserInfo($liveuid)['follows'];
        $fans  = getUserInfo($liveuid)['fans'];
        $is_certification  = getUserInfo($liveuid)['is_certification'];
        $certification_name = getUserInfo($liveuid)['certification_name'];
        $userinfo=DI()->notorm->users
            ->select("user_login,avatar,avatar_thumb")
            ->where("id='{$liveuid}'")
            ->fetchOne();

        $userinfo['avatar'] = get_upload_path($userinfo['avatar']);
        $userinfo['avatar_thumb'] = get_upload_path($userinfo['avatar_thumb']);
        $res['userinfo'] = $userinfo;
        $res['userinfo']['follows'] = NumberCeil($follows);
        $res['userinfo']['fans'] = NumberCeil($fans);
        $res['isAttention']=isAttention($uid,$liveuid);//判断当前用户是否关注了该主播
        $agentcode=DI()->notorm->users_agent_code
            ->select("code")
            ->where("uid='{$liveuid}'")
            ->fetchOne();
        $res['code'] = isset($agentcode['code'])?$agentcode['code']:'';
        $res['likes'] = $likecount;
        $res['is_certification'] = $is_certification;
        $res['certification_name'] = $certification_name;

        $res['shotvideo'] = $video;
        $res['videolong'] = $videolong;

        return $res;
    }

    public  function getMyVideoStatistics($uid){
        $shotVideoRevieweCount = DI()->notorm->video->where("uid = {$uid} and status = '1'")->count(); // 待审核
        $shotVideoPassCount = DI()->notorm->video->where("uid = {$uid} and status = '2'")->count(); // 审核通过
        $shotVideoRejectCount = DI()->notorm->video->where("uid = {$uid} and status = '3'")->count(); // 审核通过
        $longVideoRevieweCount = DI()->notorm->video->where("uid = {$uid} and status = '1'")->count(); // 待审核
        $longVideoPassCount = DI()->notorm->video->where("uid = {$uid} and status = '2'")->count(); // 审核通过
        $longVideoRejectCount = DI()->notorm->video->where("uid = {$uid} and status = '3'")->count(); // 审核通过
        return ['shotVideoRevieweCount'=> $shotVideoRevieweCount,
            'shotVideoPassCount' =>$shotVideoPassCount,
            'shotVideoRejectCount' => $shotVideoRejectCount,
            'longVideoRevieweCount' => $longVideoRevieweCount,
            'longVideoPassCount'  => $longVideoPassCount,
            'longVideoRejectCount' => $longVideoRejectCount,

        ];

    }
    /* 短视频购买*/
    public function buyShotvideo($uid,$videoid){
        $code = codemsg();
        $video=DI()->notorm->video
            ->select("id,title,buy_numbers,uid,thumb,price")
            ->where("id = '{$videoid}'")
            ->fetchOne();

        if(!$video){
            return 1001;
        }
        if(empty($video['price']) || $video['price'] == 0){
            return 1003;    //该视频是免费视频，无需购买
        }

        if($video['uid']==$uid){
            return 1002;//不能给自己购买
        }
        $videobuy=DI()->notorm->users_video_buy
            ->select("videoid")
            ->where("videoid = '{$videoid}' and uid = '{$uid}' and video_type = 1")
            ->fetchOne();
        if($videobuy){
            return 1004;//已经购买
        }
        $userModel = new Model_User();
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
        if($userInfo['user_type'] == 7){ // 测试账号，不能购买
            return array('code' => 402, 'msg' => $code['402'], 'info' => array('测试账号，不能购买'));
        }
        $author_userinfo = getUserInfo($video['uid']);
        if($author_userinfo['user_type'] == 7){ // 作者是测试账号，不能购买
            return array('code' => 403, 'msg' => $code['403'], 'info' => array('作者是测试账号，不能购买'));
        }

        $tenant_id = getTenantId();
        if($userInfo['coin']<$video['price']){
            return 1005;
        }
        DI()->notorm->users
            ->where('id = ? and coin >=?', $uid,$video['price'])
            ->update(array(
                'coin' => new NotORM_Literal("coin - {$video['price']}")
            ) ); // 扣除当前用户收益
            delUserInfoCache($uid);

            $buy_log = DI()->notorm->users_video_buy
                ->insert(array(
                    "uid"=>$uid,
                    "videoid"=>$videoid,
                    "addtime"=>time(),
                    "tenant_id"=>$userInfo['tenant_id'],
                    'video_type'=>1,
                    'price'=>$video['price'],
                    'user_login'=>$userInfo['user_login'],
                    'ex_user_price'=>$video['price'],
                    'ex_user_id'=>  $video['uid']
            ));
        //

        /*  $ratePrice=$this->setUserCoin($video['uid'],$video['price']);//返回的收益值
        DI()->notorm->users_video_buy
              ->insert(array("uid"=>$uid,
                  "videoid"=>$videoid,
                  "addtime"=>time(),
                  "tenant_id"=>$tenant_id,
                  'video_type'=>1,
                  'price'=>$video['price'],
                  'username'=>$userinfo['user_login'],
                  'ex_user_price'=>$ratePrice[0][0],
                  'one_price'=>$ratePrice[0][1],
                  'two_price'=>$ratePrice[0][2],
                  'three_price'=>$ratePrice[0][3],
                  'ex_users'=>$ratePrice[1][0],
                  'one_user'=>$ratePrice[1][1],
                  'two_user'=>$ratePrice[1][2],
                  'tree_user'=>$ratePrice[1][3],
              ));



            //记录到资金列表
        */
        DI()->notorm->video
            ->where("id = '{$videoid}'")
            ->update( array('buy_numbers' => new NotORM_Literal("buy_numbers + 1") ) );
        $rs['isbuy']='1';


        $config=getConfigPub(); // 获取 平台设置
        $authorUserInfo = $userModel->getUserInfoWithIdAndTid($video['uid']);
        if ($config['video_buy_amount_type'] == 1){   //  表示可余额进入可以提现余额
            DI()->notorm->users
                ->where('id = ? ', $video['uid'])
                ->update(array(
                    'coin' => new NotORM_Literal("coin + {$video['price']}")
                ));
            $pre_balance = $authorUserInfo['coin'];
            $after_balance = bcadd($authorUserInfo['coin'], $video['price'],4);
            //写入数据
            $is_withdrawable =1;
            $type = 'income';
        }else{
            DI()->notorm->users
                ->where('id = ?  ', $video['uid'])
                ->update(array(
                    'nowithdrawable_coin' => new NotORM_Literal("nowithdrawable_coin + {$video['price']}")
                ));
            $pre_balance = $authorUserInfo['nowithdrawable_coin'];
            $after_balance = bcadd($authorUserInfo['nowithdrawable_coin'], $video['price'],4);
            $is_withdrawable =2;
            $type = 'income_nowithdraw';

            $redis = connectRedis();
            $keytime = time();
            $redis->lPush($video['uid'] . '_reward_time', $keytime);// 存用户 时间数据key
            $amount = $redis->get($video['uid'] . '_' . $keytime.'_reward');
            $totalAmount = bcadd($video['price'], $amount, 2);
            $redis->set($video['uid'] . '_' . $keytime.'_reward', $totalAmount);// 存佣金
            $expireTime = time() + $config['withdrawal_time']  * 86400;;
            /** 86400*/
            $redis->expireAt($video['uid'] . '_' . $keytime.'_reward', $expireTime);// 设置过去时间
        }
        $insertanchor=array(  // 用户消费记录
            "type"=>'expend',
            "action"=>'buy_video',
            "uid"=>$uid,
            'user_login' => $userInfo['user_login'],
            "user_type" => intval($userInfo['user_type']),
            "touid"=>$video['uid'],
            "giftid"=>$buy_log,
            "giftcount"=>1,
            "pre_balance" => floatval($userInfo['coin']),
            "totalcoin"=>$video['price'],
            "after_balance" => floatval(bcadd($userInfo['coin'], -abs($video['price']),4)),
            "showid"=>0,
            "mark"=>'11',
            "addtime"=>time(),
            'tenant_id' =>$tenant_id,
            'remark' => '视频id: '.$video['id'].'<br>标题: '.$video['title'],
        );
        $coinrecordData=array( //作者收益
            'type' => $type,
            'uid' => $video['uid'],
            'user_login' => $authorUserInfo['user_login'],
            "user_type" => intval($authorUserInfo['user_type']),
            'giftid' =>$buy_log,
            'addtime' => time(),
            'tenant_id' => $authorUserInfo['tenant_id'],
            'action' =>'buy_shot_video',
            "pre_balance" => floatval($pre_balance),
            'totalcoin' => floatval($video['price']),//金额
            "after_balance" => floatval($after_balance),
            "giftcount"=>1,
            'is_withdrawable' =>$is_withdrawable,
            'remark' => '视频id: '.$video['id'].'<br>标题: '.$video['title'],
        );

        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord($insertanchor);// 扣除用户 金额

        // 普通用户 购买不产生收入
        $vip_info = Model_Login::getInstance()->getUserjurisdiction($video['uid'], $authorUserInfo['user_type']);
        if($vip_info['new_level'] == '0'){
            return  $rs;
        }

        $coinrecordModel->addCoinrecord($coinrecordData); // 添加作者金额
        $this->AgencyCommission($video['uid'],$video['price'],$buy_log,$type, $is_withdrawable,'agent_buy_video',3 , $video);
        return  $rs;
    }
    private function setUserCoin($uid,$price)
    {
        $agent=DI()->notorm->users_agent
            ->select('one_uid,two_uid,three_uid')
            ->where('uid=?',$uid)->fetchOne();
        //查询
        $ex_user=DI()->notorm->users->select('user_login')->where('id=?',$uid)->fetchOne();
        if(!$agent){
            $user_info = getUserInfo($uid);
            DI()->notorm->users_agent->insert(array('uid' =>$uid, 'user_login'=>$user_info['user_login'], 'addtime'=>time(), 'tenant_id'=>$user_info['tenant_id']));
            if( DI()->notorm->users
                ->where('id = ? ', $uid)
                ->update(['coin' => new NotORM_Literal("coin + {$price}")]) ) {
                $this->setCoinRecord($uid,$price,0);
                return [[$price,0,0,0],[$ex_user['user_login'].'('.$uid.')/'.$price,'','','']];
            } else{
                return  [[0,0,0,0],['','','','']];
            }

        }
        //获取上级设置
        $rate=DI()->notorm->users_video_rate
            ->select('rate,one_uid_rate,two_uid_rate,three_uid_rate')
            //->where('uid=?',$uid)
            ->fetchOne();
        //计算
        if(!$rate){
            DI()->notorm->users
                ->where('id = ? ', $uid)
                ->update(['coin' => new NotORM_Literal("coin + {$price}")]);
            $this->setCoinRecord($uid,$price,0);
            return [[$price,0,0,0],[$ex_user['user_login'].'('.$uid.')/'.$price,'','','']];
        }
        //开始计算
        //作者收益
        $_price=bcdiv(bcmul($price,$rate['rate']),100,2);
        $onePrice=($agent['one_uid']>0 && $rate['one_uid_rate']>0)?bcdiv(bcmul($price,$rate['one_uid_rate']),100,2):0;
        $twoPrice=($agent['two_uid']>0 && $rate['two_uid_rate'] >0)?bcdiv(bcmul($price,$rate['two_uid_rate']),100,2):0;
        $treePrice=($agent['three_uid']>0 && $rate['three_uid_rate']>0)?bcdiv(bcmul($price,$rate['three_uid_rate']),100,2):0;
        $exuser=$ex_user['user_login'].'('.$uid.')/'.$price;
        if($_price>0){
            DI()->notorm->users
                ->where('id = ? ', $uid)
                ->update(['coin' => new NotORM_Literal("coin + {$_price}")]);
            $exuser=$ex_user['user_login'].'('.$uid.')/'.$_price;
            $this->setCoinRecord($uid,$_price,0);
        }
        $ex_one='';
        if($onePrice > 0){
            DI()->notorm->users
                ->where('id = ? ', $agent['one_uid'])
                ->update(['coin' => new NotORM_Literal("coin + {$onePrice}")]);
            $ex_one_user=DI()->notorm->users->select('user_login')->where('id=?',$agent['one_uid'])->fetchOne();
            $ex_one= $ex_one_user['user_login'].'('.$agent['one_uid'].')/'.$onePrice;
            $this->setCoinRecord($agent['one_uid'],$onePrice,1);
        }
        $ex_two='';
        if($twoPrice > 0){
            DI()->notorm->users
                ->where('id = ? ', $agent['two_uid'])
                ->update(['coin' => new NotORM_Literal("coin + {$twoPrice}")]);
            $ex_two_user=DI()->notorm->users->select('user_login')->where('id=?',$agent['two_uid'])->fetchOne();
            $ex_two= $ex_two_user['user_login'].'('.$agent['two_uid'].')/'.$twoPrice;
            $this->setCoinRecord($agent['two_uid'],$twoPrice,2);
        }
        $ex_three='';
        if($treePrice > 0){
            DI()->notorm->users
                ->where('id = ? ', $agent['three_uid'])
                ->update(['coin' => new NotORM_Literal("coin + {$treePrice}")]);
            $ex_three_user=DI()->notorm->users->select('user_login')->where('id=?',$agent['three_uid'])->fetchOne();
            $ex_three= $ex_three_user['user_login'].'('.$agent['three_uid'].')/'.$treePrice;
            $this->setCoinRecord($agent['three_uid'],$treePrice,3);
        }
        return  [[$_price,$onePrice,$twoPrice,$treePrice],[$exuser,$ex_one,$ex_two,$ex_three]];
    }
    private function setCoinRecord($uid,$coin,$giftid)
    {
        $userModel = new Model_User();
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
        $tenantId=getTenantId();//租户ID
        //写入数据
        $coinData=[
            'type' => 'income',
            'uid' => $uid,
            'user_login' => $userInfo['user_login'],
            "user_type" => intval($userInfo['user_type']),
            'giftid' =>$giftid,
            'addtime' => time(),
            'tenant_id' => intval($tenantId),
            'action' => 'buy_shot_video',
            "pre_balance" => floatval($userInfo['coin']),
            'totalcoin' => floatval($coin),//金额
            "after_balance" => floatval(bcadd($userInfo['coin'], $coin,4)),
            "giftcount"=>1,
        ];
        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord($coinData);
    }

    /* 购买记录*/
    public function buyHistory($uid,$p){

        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;


        $buyvideo=DI()->notorm->users_video_buy
            ->select("*")
            ->where("uid = '{$uid}' and video_type=1")
            ->limit($start,$nums)
            ->fetchAll();

        if(!$buyvideo){
            $rs['shotvideo'] = [];
        }else{
            $arrid = array();
            foreach ($buyvideo as $key=>$value){
                $arrid[] = $value['videoid'];
            }
            $buyvideoById = array_column($buyvideo,null,'id');
            $arrid = implode(',',$arrid);
            $shotvideo=DI()->notorm->video
                ->select("*")
                ->where("id  in ({$arrid}) ")
                ->fetchAll();
            $download_url =  play_or_download_url(2);
            $paly_url = play_or_download_url(1);
            foreach($shotvideo as $k=>$v){

                $userinfo=getUserInfo($v['uid']);
                if(!$userinfo){
                    $userinfo['user_nicename']="已删除";
                }
                $shotvideo[$k]['buy_time'] = $buyvideoById[$v['id']]['addtime'];
                $shotvideo[$k]['userinfo']=$userinfo;

                if ($v['origin'] != 3) {
                    $shotvideo[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                    $shotvideo[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $shotvideo[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];

                }

            }
            $rs['shotvideo'] = $shotvideo;

        }
        $buylongvideo=DI()->notorm->users_video_buy
            ->select("*")
            ->where("uid = '{$uid}' and video_type=2")
            ->limit($start,$nums)
            ->fetchAll();

        if(!$buylongvideo){
            $rs['longvideo'] = [];
        }else{
            $arrids = array();
            foreach ($buylongvideo as $key=>$value){
                $arrids[] = $value['videoid'];
            }
            $buylongvideoById = array_column($buylongvideo,null,'id');
            $arrids = implode(',',$arrids);
            $longvideo=DI()->notorm->video_long
                ->select("*")
                ->where("id  in ({$arrids}) ")
                ->fetchAll();

            $download_url =  play_or_download_url(2);
            $paly_url = play_or_download_url(1);
            foreach($longvideo as $k=>$v){
                $longvideo[$k]['buy_time'] = $buylongvideoById[$value['id']]['addtime'];
                $userinfo=getUserInfo($v['uid']);
                if(!$userinfo){
                    $userinfo['user_nicename']="已删除";
                }


                $longvideo[$k]['userinfo']=$userinfo;
                if ($v['origin'] != 3) {
                    $longvideo[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                    $longvideo[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $longvideo[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];

                }

            }
            $rs['longvideo'] = $longvideo;

        }

        return $rs;
    }


    /**
     * @param $uid 用户id
     * @param $price  金额
     * @param $giftid  操作id
     * @param $type  //  income 可提现 ， income_nowithdraw不可提余额
     * @param $is_withdrawable  1可提现  2  不可提现
     * @param $action   agent_buy_video  购买视频代理收益
     *@param $agentType    1  任务  ，2 购买视频  ， 3 点赞视频，4 上传视频'
     * @return bool
     */
    public  function AgencyCommission($uid,$price,$giftid, $type,$is_withdrawable,$action,$agentType, $video_info){

        $userModel = new Model_User();
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
        $RebateConf = getAgentRebateConf($userInfo['tenant_id']);
        if(!$RebateConf){
            return  true;
        }


        $RebateConfByLevel =  array_column($RebateConf,null,'agent_level');
        $config=getConfigPub();
        if (!$config['agent_sum']){
            return  true;
        }
        $uids = explode(',',$userInfo['pids']);
        unset($uids[0]);
        if (empty($uids)){
            return  true;
        }
        if ($config['agent_sum']< count($uids)){
            $uids = array_slice($uids,-$config['agent_sum']);
        }
        $uids  =  array_reverse($uids);

        $coinrecordModel = new Model_Coinrecord();

        $redis = connectRedis();
        $i = 0;
        foreach ($uids as $key =>$value) {
            $agentuser_vip_info = getUserVipInfo($value);
            if (!empty($agentuser_vip_info) && in_array($agentuser_vip_info['status'], [2])) { // 退保证金申请中不能有收益，也不能抢红包，也不能理返点
                continue;
            }
            $agentInfo = $userModel->getUserInfoWithIdAndTid($value);
            if($agentInfo['user_type'] == 7){ // 测试账号，不做逻辑处理，直接返回
                continue;
            }
            if ($agentInfo['rebate_status'] == 0) { // 判断 代理返点 是否开启
                continue;
            }
            $rebate = bcmul($price, $RebateConfByLevel[$key + 1]['rate'] / 100, 2);
            if ($rebate) {
                $agentData['uid'] = $uid;
                $agentData['pid'] = $value;
                $agentData['addtime'] = time();
                $agentData['level'] = $key + 1;
                $agentData['type'] = $agentType;
                $agentData['operation_id'] = $giftid;
                $agentData['status'] = 1;
                $agentData['total_amount'] = $price;
                $agentData['rate'] = $RebateConfByLevel[$key+1]['rate'];
                $agentData['amount'] = $rebate;
                $agentData['tenant_id'] = $agentInfo['tenant_id'];
                if ($is_withdrawable == 1) {
                    M('users')->where(['id' => $value])->save(['agent_total_income' => ['exp', 'agent_total_income+' . $rebate],
                        'coin' => ['exp', 'coin+' . $rebate]]);
                    $pre_balance = $userInfo['coin'];
                    $after_balance = bcadd($userInfo['coin'], $rebate,4);
                } else {
                    M('users')->where(['id' => $value])->save(['agent_total_income' => ['exp', 'agent_total_income+' . $rebate],
                        'nowithdrawable_coin' => ['exp', 'nowithdrawable_coin+' . $rebate]]);
                    $pre_balance = $userInfo['agent_total_income'];
                    $after_balance = bcadd($userInfo['agent_total_income'], $rebate,4);
                    $keytime = time();
                    $redis->lPush($value . '_reward_time', $keytime);// 存用户 时间数据key
                    $amount = $redis->get($value . '_' . $keytime.'_reward');
                    $totalAmount = bcadd($rebate, $amount, 2);
                    $redis->set($value . '_' . $keytime.'_reward', $totalAmount);// 存佣金
                    $expireTime = time() + $config['withdrawal_time']  * 86400;
                    /** 86400*/
                    $redis->expireAt($value . '_' . $keytime.'_reward', $expireTime);// 设置过去时间
                }
                $agentRewardId = DI()->notorm->agent_reward->insert($agentData);// 代理记录
                $coinrecordData = [
                    'type' => $type,
                    'uid' => $value,
                    'user_login' => $agentInfo['user_login'],
                    "user_type" => intval($agentInfo['user_type']),
                    'giftid' => $agentRewardId,
                    'addtime' => time(),
                    'tenant_id' => $agentInfo['tenant_id'],
                    'action' => $action,
                    "pre_balance" => floatval($pre_balance),
                    'totalcoin' => $rebate,//金额
                    "after_balance" => floatval($after_balance),
                    "giftcount" => 1,
                    'is_withdrawable' => $is_withdrawable,
                    'remark' => '视频id: '.$video_info['id'].'<br>标题: '.$video_info['title'],
                ];
                $coinrecordModel->addCoinrecord($coinrecordData); //  账变记录
                delUserInfoCache($value);
                $i++ ;
            }
        }

        return true;
    }
    /*
     * 去绑定 商品id或者店铺id
     */
    public function bindShop($shoptype,$shop_value,$videoid,$shop_url)
    {
        $tenant_id = getTenantId();
        $tenantInfo=getTenantInfo($tenant_id);
        if( $shoptype == 1){

            $shopparms = array(
                'id' =>$shop_value,
            );
            $url = $tenantInfo['shop_url'].'/api.php?s=Goods/Detail';
            $shopinfo = http_post($url,$shopparms);

            if(isset($shopinfo['code']) && $shopinfo['code'] == 0){
                $videobindshop = array(
                    'title'=>$shopinfo['data']['goods']['title'],
                    'images'=>$shopinfo['data']['goods']['images'],
                    'price'=>$shopinfo['data']['goods']['price'],
                    'original_price'=>$shopinfo['data']['goods']['original_price'],
                    'shop_url'=>$shop_url,
                );
                DI()->notorm->video
                    ->where("id = '{$videoid}'")
                    ->update( array('shoptype' =>1,'shop_value' =>$shop_value, 'shop_url'=>$shop_url ) );
                DI()->redis->hSet("videobindshop",$videoid,json_encode($videobindshop));
                DI()->redis->Del( 'short_video_info_' . $tenant_id . $videoid);

                return array('code' =>0, 'msg' => '绑定商品ID成功', 'info' => []);
            }else{
                return array('code' =>1001, 'msg' => '绑定商品ID失败', 'info' => []);
            }

        }
        if($shoptype == 2){

                $shopparms = array(
                    'id' => $shop_value,
                );
                $url = $tenantInfo['shop_url'].'/api.php?s=plugins/index&pluginsname=shop&pluginscontrol=index&pluginsaction=detail';
                $shopinfo = http_post($url,$shopparms);
                $shopgoodsparms = array(
                    'shop_id' => $shop_value,
                );
                $url = $tenantInfo['shop_url'].'/api.php?s=plugins/index&pluginsname=shop&pluginscontrol=search&pluginsaction=DataList';
                $shopgoodsinfo = http_post($url,$shopgoodsparms);

                if(isset($shopinfo['code']) && $shopinfo['code'] == 0){
                    $videobindshop = array(
                        'title'=>$shopinfo['data']['shop']['name'],
                        'images'=>$shopinfo['data']['shop']['logo'],
                        'shop_url'=>$shop_url,
                    );
                    //绑定店铺商品
                    if(isset($shopgoodsinfo['code']) && $shopgoodsinfo['code'] == 0){
                        $videobindshop['goods'] = array(
                            'title'=>$shopgoodsinfo['data']['data'][0]['title'],
                            'images'=>$shopgoodsinfo['data']['data'][0]['images'],
                            'price'=>$shopgoodsinfo['data']['data'][0]['price'],
                        );
                    }else{
                        $videobindshop['goods'] = array(
                            'title'=>'',
                            'images'=>'',
                            'price'=>'',
                        );
                    }
                    DI()->notorm->video
                        ->where("id = '{$videoid}'")
                        ->update( array('shoptype' =>2,'shop_value' =>$shop_value, 'shop_url'=>$shop_url ) );
                    DI()->redis->hSet("videobindshop",$videoid,json_encode($videobindshop));
                    DI()->redis->Del( 'short_video_info_' . $tenant_id . $videoid);
                    return array('code' =>0, 'msg' => '绑定店铺ID成功', 'info' => []);
                }else{
                    return array('code' =>1002, 'msg' => '绑定店铺ID失败', 'info' => []);

                }
        }
    }

    // 获取刷过的视频总数
    public function getWatchVideoNum($uid, $type){
        if($type==0){
            $count = DI()->notorm->video_watch_record->where("uid=?",$uid)->group("videoid")->count();
        }else{
            $count = DI()->notorm->video_watch_record->where("uid=? and video_type=?",$uid,$type)->group("videoid")->count();
        }
        
        return $count;
    }

    //自动审核
    public function autoPass(){
       return DI()->notorm->video->where("status=1 and is_downloadable=1")->update( array('status' => 2 ) );
    }
}
