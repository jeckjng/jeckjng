<?php

class Model_Longvideo extends PhalApi_Model_NotORM {
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
    private  static function isLike($uid,$videoId){
        // 是否喜欢
        $is_like  = DI()->notorm->users_video_like
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 2 ")
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
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 2 ")
            ->fetchOne();
        if ($is_collection){
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
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 2 ")
            ->fetchOne();
        if ($is_download){
            return 1;
        }else{
            return 0;
        }
    }

	/* 评论/回复 */
    public function setComment($data) {
    	$videoid=$data['videoid'];

    	//var_dump($videoid);exit;
		/* 更新 视频 */
		DI()->notorm->video_long
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
		$video=DI()->notorm->video_long
				->select("likes,uid,thumb")
				->where("id = '{$videoid}'")
				->fetchOne();

        if(!$video){
            return 1001;
        }
        /*if($video['uid']==$uid){
            return 1002;//不能给自己点赞
        }*/
        $like=DI()->notorm->users_video_like
            ->select("id")
            ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id ='{$game_tenant_id}' and video_type =2")
            ->fetchOne();
        if($like){
            DI()->notorm->users_video_like
                ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id='{$game_tenant_id}' and video_type=2 ")
                ->delete();

            DI()->notorm->video_long
                ->where("id = '{$videoid}' and likes>0")
                ->update( array('likes' => new NotORM_Literal("likes - 1") ) );
            $rs['islike']='0';
        }else{
            DI()->notorm->users_video_like
                ->insert(array("uid"=>$uid,"videoid"=>$videoid,"addtime"=>time(),"tenant_id"=>$game_tenant_id,'video_type'=>2 ));

            DI()->notorm->video_long
                ->where("id = '{$videoid}'")
                ->update( array('likes' => new NotORM_Literal("likes + 1") ) );
            $rs['islike']='1';
            //yhTaskFinish($uid,getTaskConfig('task_4'));
        }

        $video=DI()->notorm->video_long
            ->select("id,title,likes,uid,thumb")
            ->where("id = '{$videoid}'")
            ->fetchOne();

        $rs['likes']=$video['likes'];
        if($video['uid'] !=$uid &&   $rs['islike']== '1') {
            Model_Like::getInstance()->sendVideoLikeProfit(2, $videoid, $uid,$video['uid'], $video);
        }
        return $rs;
    }
    /*删除视频标签*/
    public function  delVideolabel($data){
        $rs =  DI()->notorm->video_label
            ->where("uid='{$data['uid']}' and label='{$data['label']}'")
            ->update(array('is_delete'=>2));
        return $rs;
    }
    /* 获取视频标签 */
    public function getVideohomelabel($uid,$label){
        if(!empty($label)){
            $video=DI()->notorm->video_label_long
                ->select("*")
                ->where("is_delete = 1")
                ->where("label = '".$label."'")
                ->order('sort asc')
                ->fetchAll();
        }else{
            $video=DI()->notorm->video_label_long
                ->select("*")
                ->where("is_delete = 1")
                ->order('sort asc')
                ->fetchAll();
        }

        foreach($video as $key=>$value){
            // var_dump($value);exit;
            $videoifno=DI()->notorm->video_long_classify
                ->select("classify")
                ->where("label  ='".$value['label']."'")
                ->fetchAll();
            // var_dump($videoifno);exit;
            $video[$key]['classify'] =array_values($videoifno);

        }

        return $video;
    }
    /* 获取视频标签 */
    public function getVideolabel($uid,$label){
        if(empty($label)){
            $video=DI()->notorm->video_label_long
                ->select("*")
                ->order("sort asc ")
                ->where("is_delete = 1")
                ->fetchAll();
        }else{
            $video=DI()->notorm->video_label_long
                ->select("*")
                ->order("sort asc ")
                ->where("is_delete = 1")
                ->where("label  ='".$label."'")
                ->fetchAll();
        }

        $data = array();
        foreach($video as $key=>$value){
            // var_dump($value);exit;
            $videoifno=DI()->notorm->video_long_classify
                ->select("*")
                ->order("sort asc ")
                ->where("label  ='".$value['label']."'")
                ->fetchAll();
           // var_dump($videoifno);exit;
           // $video[$key]['classify'] = array_values($videoifno);
            $data['data'.$key][] = $value;
            foreach ($videoifno as $k=>$v){
                $data['data'.$key][] = $v;
            }
        }

        return $data;
    }
    /* 获取长视频标签 */
    public function getVideolabelnew($uid,$label){
        if(empty($label)){
            $video=DI()->notorm->video_label_long
                ->select("*")
                ->order("sort asc ")
                ->where("is_delete = 1 and type=1")
                ->fetchAll();
        }else{
            $video=DI()->notorm->video_label_long
                ->select("*")
                ->order("sort asc ")
                ->where("is_delete = 1  and type=1")
                ->where("label  ='".$label."'")
                ->fetchAll();
        }


        return $video;
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
            ->where("uid='{$uid}' and commentid='{$commentid}' and tenant_id ='{$game_tenant_id}' and video_type =2")
            ->fetchOne();

        if($like){
            DI()->notorm->users_video_comments_like
                ->where("uid='{$uid}' and commentid='{$commentid}' and tenant_id ='{$game_tenant_id}' and video_type =2")
                ->delete();

            DI()->notorm->video_comments
                ->where("id = '{$commentid}' and likes>0")
                ->update( array('likes' => new NotORM_Literal("likes - 1") ) );
            $rs['islike']='0';

        }else{
            DI()->notorm->users_video_comments_like
                ->insert(array("uid"=>$uid,"commentid"=>$commentid,"addtime"=>time(),"touid"=>$commentinfo['uid'],"videoid"=>$commentinfo['videoid']
                ,"tenant_id"=>$game_tenant_id,'video_type'=>2));

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


    /* 精选视频 */
    public function getVideojingxuan($uid,$p,$classify,$label,$iscoding,$watchmax,$likemax){
        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        $where = ' status = 2';
        $where .= " and   label = '精选'";


        if(!empty($iscoding)){
            $where .= " and   iscoding = '".$iscoding."'";
        }
        if($watchmax==1){
            $video=DI()->notorm->video_long
                ->select("*")
                ->order("watchtimes desc")
                ->limit($start,$nums)
                ->where($where)
                ->fetchAll();
        }elseif($likemax == 1){
            $video=DI()->notorm->video_long
                ->select("*")
                ->order("likes desc")
                ->limit($start,$nums)
                ->where($where)
                ->fetchAll();
        } else{
            $video=DI()->notorm->video_long
                ->select("*")
                ->order("sort asc ")
                ->limit($start,$nums)
                ->where($where)
                ->fetchAll();
        }

        $classifyinfo=DI()->notorm->video_long
            ->select("count(classify) as labelnums,classify")
            ->group("classify")
            ->where("label = '精选' ")
            ->fetchAll();
        $arr = array();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($video as $key=>$value){
            if ($value['origin'] != 3){
                $video[$key]['href'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$value[$paly_url['viode_table_field']];
                $video[$key]['thumb']= $paly_url['url'].$value['thumb'];
                $video[$key]['download_address'] = $download_url['url'].$value[$download_url['viode_table_field']];
            }

        }

        foreach ($classifyinfo as $k => $v){
          foreach ($video as $key=>$value){
             if($value['classify'] == $v['classify'] ){
                // var_dump($value);exit;
                 $arr[$k]['label'] =   $v['classify'];
                 $arr[$k]['data'][] = $value;
             }


          }
        }
       /* print_r('<pre>');
        print_r($arr);
        print_r('<pre/>');exit;*/
     /*   $arr['label'] = '精选每日推荐';
        $arr['data'] = $video;*/
        return $arr ;
    }

    /* 热门视频 */
    public function getVideoList($uid,$p,$classify,$label,$iscoding,$watchmax,$likemax,$release_time,$duration,$url,$timedesc){

        if($p<1){
            $p=1;
        }
        $order = 'sort asc,create_date desc';
        $nums=20;
        $start=($p-1)*$nums;
        $where = ' status = 2 ';
        if(!empty($label)){
            $where .= " and   label = '".$label."'";
        }
        if(!empty($classify)){
            $where .= " and   classify = '".$classify."'";
        }
        if(!empty($iscoding)){
            $where .= " and   iscoding = '".$iscoding."'";
        }
        if($watchmax==1){
            $order.= ",watchtimes desc ";

        }if($likemax == 1){
            $order .= ',likes desc';
        }
        if($timedesc == 1) {
            $order .= ', check_date desc';
        }
        if ($release_time){
            if ($release_time== 1){
                $time = date("Y-m-d H:i:s", strtotime("-1 day"));
                $where .= " and   create_date > '".$time."'";
            }elseif ($release_time== 2){
                $time = date("Y-m-d H:i:s", strtotime("-1 week"));
                $where .= " and   create_date > '".$time."'";
            }elseif ($release_time== 3){
                $time = date("Y-m-d H:i:s", strtotime("-2 week"));
                $where .= " and   create_date > '".$time."'";
            }elseif ($release_time== 4){
                $time = date("Y-m-d H:i:s", strtotime("-1 month"));
                $where .= " and   create_date > '".$time."'";
            }elseif ($release_time== 5){
                $time = date("Y-m-d H:i:s", strtotime("-1 year"));
                $where .= " and   create_date > '".$time."'";
            }

        }

        if ($duration){
            if ($duration== 1){
                $starttime = 0 * 60;
                $endtime  = 30 *60;
                $where .= " and   playTimeInt >= '{$starttime}' and playTimeInt < '{$endtime}' ";

            }elseif ($duration== 2){
                $starttime = 30 * 60;
                $endtime  = 60 *60;
                $where .= " and   playTimeInt >= '{$starttime}' and playTimeInt < '{$endtime}' ";

            }elseif ($duration== 3){

                $starttime  = 60 *60;
                $endtime = 90 * 60;
                $where .= " and   playTimeInt >= '{$starttime}' and playTimeInt < '{$endtime}' ";

            }elseif ($duration== 4){
                $starttime  = 90 *60;
                $where .= " and   playTimeInt >= '{$starttime}'";


            }

        }

        $video=DI()->notorm->video_long
            ->select("*")
            ->order($order)
            ->limit($start,$nums)
            ->where($where)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);

        $buylongVideoId = [];
        if ($video){
            $videoId  = array_column($video,'id');
            $videoIdString = implode(',',$videoId);
            $buylongVideo = DI()->notorm->users_video_buy
                ->select("*")
                ->where("uid = '{$uid}' and videoid in ($videoIdString) and  video_type = '2'")
                ->fetchAll();
            $buylongVideoId = array_column($buylongVideo,'videoid');
        }

        foreach ($video as $key=>$value){
            if ($value['origin'] != 3) {
                if ($url) {
                    $video[$key]['href'] = $url . $value[$paly_url['viode_table_field']];
                    if($paly_url['name'] == 'minio' && strrpos($value['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                        $video[$key]['thumb'] = $paly_url['url'] .'/liveprod-store-1039'. $value['thumb'];
                    }else{
                        $video[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    }
                    $video[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }else{
                    $video[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                    if($paly_url['name'] == 'minio' && strrpos($value['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                        $video[$key]['thumb'] = $paly_url['url'] .'/liveprod-store-1039'. $value['thumb'];
                    }else{
                        $video[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    }
                    $video[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }
            }
            if (in_array($value['id'],$buylongVideoId) || $value['price'] == 0){
                $video[$key]['is_buy'] = 1;
            }else{
                $video[$key]['is_buy'] = 0;
            }
        }

        return $video;
    }
    /* 热门视频 */
    public function getSearchContent($uid,$p,$searchcontent,$url){

        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        $where = ' status =2 ';
        if(!empty($searchcontent)) {
            $where .= " and   (label like '%" . $searchcontent . "%' or    title like '%" . $searchcontent . "%' )";
        }
        $tenant_id = getTenantId();
        $issearch =  $video=DI()->notorm->long_video_search
            ->select("id")
            ->where("title = '{$searchcontent}'")
            ->fetchOne();
         if(!$issearch){
             DI()->notorm->long_video_search
                 ->insert(array(
                     "title"=>$searchcontent,
                     "addtime"=>time(),
                     "tenant_id"=>$tenant_id,
                     'times'=>1,
                     'search_type'=>2
                 ));

         }else{
             DI()->notorm->long_video_search
                 ->where("title = '{$searchcontent}'")
                 ->update( array('times' => new NotORM_Literal("times + 1"),'title'=>$searchcontent ) );
         }
        $isusersearch =  DI()->notorm->long_video_usersearch
            ->select("id")
            ->where("title = '{$searchcontent}' and uid = ".$uid)
            ->fetchOne();
        if(!$isusersearch){
            DI()->notorm->long_video_usersearch
                ->insert(array(
                    "title"=>$searchcontent,
                    "addtime"=>time(),
                    "tenant_id"=>$tenant_id,
                    'uid'=>$uid,
                    'times'=>1,
                    'search_type'=>2
                ));

        }else{
            DI()->notorm->long_video_usersearch
                ->where("title = '{$searchcontent}'")  ->where("title = '{$searchcontent}' and uid = ".$uid)
                ->update( array('times' => new NotORM_Literal("times + 1") ) );
        }


        $video=DI()->notorm->video_long
            ->select("*")
            ->order("sort asc")
            ->limit($start,$nums)
            ->where($where)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($video as $key=>$value) {
            if ($value['origin'] != 3) {
                if ($url) {
                    $video[$key]['href'] = $url . $value[$paly_url['viode_table_field']];
                    $video[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    $video[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }else{

                    $video[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                    $video[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    $video[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }
            }
        }

        return $video;
    }


    /* 标签分类视频 */
    public function getVideobylabel($p,$label,$iscoding,$classify,$is_today_recommendation,$url){

        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;

        if(empty($label) && empty($iscoding) && empty($classify) && empty($is_today_recommendation)){
            $video=DI()->notorm->video_long
                ->select("*")
                ->order("sort asc")
                ->where('status =2 ')
                ->limit($start,$nums)
                ->fetchAll();
        }else{
            $where = 'status =2  ' ;
            if(!empty($classify)){
                $where .="label  = '".$label."'";  //上架且审核通过
            }
            $where ="label  = '".$label."'";  //上架且审核通过
            if(!empty($classify)){
                $where .= " and classify  = '".$classify."'";
            }
            if(!empty($iscoding)){
                $where .= " and iscoding  = '".$iscoding."'";
            }

            if ($is_today_recommendation){
                $todayTime = strtotime(date("Y-m-d"),time());
                $where .= " and addtime  >= '".$todayTime."'";
            }


            $video=DI()->notorm->video_long
                ->select("*")
                ->where($where)
                ->order("sort asc")
                ->limit($start,$nums)
                ->fetchAll();
        }

        foreach($video as $k=>$v){

            $userinfo=getUserInfo($v['uid']);
            if(!$userinfo){
                $userinfo['user_nicename']="已删除";
            }
            $video[$k]['userinfo']=$userinfo;
            $download_url =  play_or_download_url(2);
            $paly_url = play_or_download_url(1);
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

        $labelinfo=DI()->notorm->video_long
                    ->select("count(label) as labelnums,label")
                    ->group("label desc")
                    ->limit($start,$nums)
                    ->fetchAll();
        $video['count'] = $labelinfo;
        return $video;
    }
    /* 根据标签获取长视频（s站 新接口） */
    public function getVideobylabelnew($p,$label){

        //根据标签获取分类
        $where ="label  = '".$label."'";
        $classifyinfo =DI()->notorm->video_long_classify
            ->select("*")
            ->where($where)
            ->order("sort asc")
            ->fetchAll();
        $start = 0;
        foreach ($classifyinfo as $key=>$value){
            if(!empty($value['ad_url'])){
                $classifyinfo[$key]['ad_url'] = explode(',',$value['ad_url']);
            }else{
                $classifyinfo[$key]['ad_url'] = [];
            }
            if($value['model_type']==1){
                $nums=5;
            }
            if($value['model_type']==2){
                $nums=4;
            }
            if($value['model_type']==3){
                $nums=10;
            }
            if($value['model_type']==4 ||$value['model_type']==5 ){
                $nums=20;
                $start=($p-1)*$nums;
            }
            $wheres = 'status =2  ' ;

            if($value['label'] != '热门' && $value['label'] != '最新' ){
                $wheres .=  " and FIND_IN_SET('".$value['classify']."',classify)"."  and FIND_IN_SET('".$label."',label)" ;
            }
            $protocal_domain = get_protocal().'://' . $_SERVER['HTTP_HOST'];
            if($value['label']=='最新'){
                $video=DI()->notorm->video_long
                    ->select("*")
                    ->where($wheres)
                    ->order("create_date desc")
                    ->limit($start,$nums)
                    ->fetchAll();
            }elseif($value['label']=='热门') {
                $video=DI()->notorm->video_long
                    ->select("*")
                    ->where($wheres)
                    ->order("watchtimes desc")
                    ->limit($start,$nums)
                    ->fetchAll();
            }else{
                $video=DI()->notorm->video_long
                    ->select("*")
                    ->where($wheres)
                    ->order("sort asc")
                    ->limit($start,$nums)
                    ->fetchAll();
            }

            foreach($video as $k=>$v){

                $userinfo=getUserInfo($v['uid']);
                if(!$userinfo){
                    $userinfo['user_nicename']="已删除";
                }
                $video[$k]['userinfo']=$userinfo;
                $download_url =  play_or_download_url(2);
                $paly_url = play_or_download_url(1);
                if ($v['origin'] != 3) {
                  {
                      $video[$k]['href'] = $protocal_domain . $v[$paly_url['viode_table_field']];
                      $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
                      if($paly_url['name'] == 'minio' && strrpos($v['thumb'], '/liveprod-store-1101') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                          $video[$k]['thumb'] = $v['thumb'] ? $paly_url['url'].'/liveprod-store-1101'.$v['thumb'] : $v['thumb'];
                      } else{
                          $video[$k]['thumb'] = $v['thumb'] ? $paly_url['url'].$v['thumb'] : $v['thumb'];
                      }  }
                }

            }
            $classifyinfo[$key]['classifylist']=$video;

        }
        return $classifyinfo;
    }
    /* 获取所有主分类 和 子分类 （s站 新接口） */
    public function getAllclssifyandlabel(){

        //获取所有 主分类
        $labelyinfo =DI()->notorm->video_label_long
            ->select("*")
            ->where('is_delete=1 and type= 1 ')
            ->order("sort asc")
            ->fetchAll();

        $start = 0;
        foreach ($labelyinfo as $key=>$value){

            $wheres = 'is_delete =1  and '."label  = '".$value['label']."'" ;

            $classifyinfo=DI()->notorm->video_long_classify
                ->select("*")
                ->where($wheres)
                ->order("sort asc")
                ->fetchAll();


            $labelyinfo[$key]['classifylist']=$classifyinfo;

        }
        return $labelyinfo;
    }
    /* 根据分类获取长视频（s站 新接口） */
    public function getVideobyclassify($p,$classify){

        if($p<1){
            $p=1;
        }
        $nums=20;
        $start=($p-1)*$nums;
        $where = 'status =2  ' ;
        //$where .= " and classify  = '".$classify."'";
        $where .=  " and FIND_IN_SET('".$classify."',classify)" ;
        $video=DI()->notorm->video_long
                ->select("*")
                ->where($where)
                ->order("sort asc")
                ->limit($start,$nums)
                ->fetchAll();

        foreach($video as $k=>$v){
            $userinfo=getUserInfo($v['uid']);
            if(!$userinfo){
                $userinfo['user_nicename']="已删除";
            }
            $video[$k]['userinfo']=$userinfo;
            $download_url =  play_or_download_url(2);
            $paly_url = play_or_download_url(1);
            if ($v['origin'] != 3) {
                if($paly_url['name'] == 'minio' && strrpos($v['thumb'], '/liveprod-store-1101') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $video[$k]['thumb'] = $paly_url['url']  .'/liveprod-store-1101'. $v['thumb'];
                }elseif ($paly_url['name'] == 'aws_test' && strrpos($v['thumb'], '/liveprod-store-1039') === false){
                    $video[$k]['thumb'] = $paly_url['url']  .'/liveprod-store-1039'. $v['thumb'];
                }
                else{
                    $video[$k]['thumb'] = $paly_url['url'].$v['thumb'];
                }
               $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
               $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
            }

        }

       /* $labelinfo=DI()->notorm->video_long
            ->select("count(label) as labelnums,label")
            ->group("label desc")
            ->limit($start,$nums)
            ->fetchAll();
        $video['count'] = $labelinfo;*/
        return $video;
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
                $video[$k]['comments']=NumberFormat($v['comments']);
                $video[$k]['likes']=NumberFormat($v['likes']);
                $video[$k]['steps']=NumberFormat($v['steps']);
                $video[$k]['islike']=(string)ifLike($uid,$v['id']);
                $video[$k]['isstep']=(string)ifStep($uid,$v['id']);
                $video[$k]['isdialect']='0';
                //$video[$k]['musicinfo']=getMusicInfo($video[$k]['userinfo']['user_nicename'],$v['music_id']);

               // $video[$k]['thumb']=get_upload_path($v['thumb']);
              //  $video[$k]['thumb_s']=get_upload_path($v['thumb_s']);
               // $video[$k]['href']=get_upload_path($v['href']);
                if ($v['origin'] != 3) {
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                    $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                    $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
                }

            }
        }

        return $video;
    }

               /* 视频详情 */
    public function getVideo($uid,$videoid,$is_search){
        $video=DI()->notorm->video_long
            ->select("*")
            ->where("id = {$videoid}")
            ->fetchOne();
        if(!$video){
            return 1000;
        }

        if ($is_search == 1){
            $scorehot = DI()->redis->zScore('rank_longhotsearch_list',$video['id']);
            if(!$scorehot){
                $rankhot=DI()->redis -> zAdd('rank_longhotsearch_list',1,$video['id']);
            }else{
                $rankhot=DI()->redis -> zAdd('rank_longhotsearch_list',$scorehot+1,$video['id']);
            }
        }
        DI()->notorm->video_long
            ->where("id = {$videoid}")
            ->update( array('hot_searches' => new NotORM_Literal("hot_searches + 1") ) );
        $protocal_domain = get_protocal().'://' . $_SERVER['HTTP_HOST'];
        $video['is_buy'] = static::isbuy($uid,$video['id']);
        $video['is_collection'] = static::isCollection($uid,$video['id']);
        $video['is_like'] = static::isLike($uid,$video['id']);
        $video['is_download'] = static::isDownload($uid,$video['id']);
        $video['is_follow'] = static::isAttention($uid,$video['uid']);
        $video['userinfo']=getUserInfo($video['uid']);
        if ($video['origin'] != 3) {
            $download_url = play_or_download_url(2);
            $paly_url = play_or_download_url(1);
            $video['thumb'] = $paly_url['url']. $video['thumb'];
            $video['href'] =$protocal_domain . $video[$paly_url['viode_table_field']];
            $video['download_address'] = $download_url['url'] . $video[$download_url['viode_table_field']];
        }
        $video['users_attention'] =  DI()->notorm->users_attention->where("touid = '{$video['uid']}'")->count();
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
            ->where("videoid='{$videoid}' and parentid='0' and tenant_id = '{$game_tenant_id}' and video_type = 2 ")
            ->order("addtime desc")
            ->limit($start,$nums)
            ->fetchAll();
        foreach($comments as $k=>$v){
            $comments[$k]['userinfo']=getUserInfo($v['uid']);
            $comments[$k]['datetime']=datetime($v['addtime']);
            $comments[$k]['likes']=NumberFormat($v['likes']);
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
                ->count();
            $comments[$k]['replys']=$count;

            /* 回复 */
            $reply=DI()->notorm->video_comments
                ->select("*")
                ->where("commentid='{$v['id']}'")
                ->where("tenant_id={$game_tenant_id}")
                ->where("video_type=2")
                ->order("addtime desc")
                ->fetchAll();
            foreach($reply as $k1=>$v1){

                $v1['userinfo']=getUserInfo($v1['uid']);
                $v1['datetime']=datetime($v1['addtime']);
                $v1['likes']=NumberFormat($v1['likes']);
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
            ->where("video_type=2")
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

        $video=DI()->notorm->video_long
            ->select("*")
            ->where('uid=? ',$uid)
            ->order("sort asc ")
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

            $video[$k]['thumb']=get_upload_path($v['thumb']);
            $video[$k]['thumb_s']=get_upload_path($v['thumb_s']);
            //$video[$k]['href']=get_upload_path($v['href']);
            if ($v['origin'] != 3) {
                $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
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

          //  $video[$k]['thumb']=get_upload_path($v['thumb']);
            $video[$k]['thumb_s']=get_upload_path($v['thumb_s']);
           // $video[$k]['href']=get_upload_path($v['href']);
            if ($v['origin'] != 3) {
                $video[$k]['thumb'] = $paly_url['url']. $v['thumb'];
                $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
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
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($info as $k => $v) {
            $info[$k]['userinfo']=getUserInfo($v['uid']);
            $info[$k]['datetime']=datetime($v['addtime']);
          //  $info[$k]['thumb']=get_upload_path($v['thumb']);
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
           // $info[$k]['href']=get_upload_path($v['href']);
            if ($v['origin'] != 3) {
                $video[$k]['thumb'] = $paly_url['url']. $v['thumb'];
                $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
            }
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
        $usermodel = new Model_User();
        $rs = $usermodel->getBaseInfo($uid);
        $video=DI()->notorm->video_long
            ->select("uid,title,first_watch_time,watchtimes,title,classify,label")
            ->where("id='{$videoid}'")
            ->fetchOne();

        if(!$video){
            return ['code' =>1001 ];
        }
        $count =DI()->notorm->video_watch_record
            ->select("uid")
            ->where("uid='{$uid}'")
            ->where("video_type='2'")
            ->count();
        if ($rs['user_type'] == 4){
            $level_name  = 'vip0';
            $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = '{$level_name}'")->fetchOne();
            if ($level_name_jurisdiction['watch_number'] != 0){
                if ($count >= $level_name_jurisdiction['watch_number']){
                    return ['code' =>800 ];
                }
            }
        }
        $time = time();
        $userVip=DI()->notorm->users_vip
            ->where("uid = '{$uid}' and  endtime >'{$time}'")
            ->order('grade desc')
            ->fetchOne();
        if (empty($userVip)){
            $level_name  = 'vip1';
        }else{
            $vipInfo  =DI()->notorm->vip
                ->where("id = '{$userVip['vip_id']}'")
                ->fetchOne();
            $level_name  = $vipInfo['name'];

        }
        $level_name_jurisdiction = DI()->notorm->users_jurisdiction->where("grade = '{$level_name}'")->fetchOne();
        if ($level_name_jurisdiction['watch_number'] != 0){
            $watch_number = $level_name_jurisdiction['watch_number'] + $rs['watch_num'];
            if ($count >= $watch_number){
                return ['code' => 900,'msg' => $vipInfo['name'].'只能观看'.$level_name_jurisdiction['watch_number'].'条视频'];
            }
        }
        if (empty($is_record)){ // 前台返回操作，不记录此条数据
            if ($is_search){

                $scorehot = DI()->redis->zScore('rank_hotsearch_list',$video['title']);
                if(!$scorehot){
                    $rankhot=DI()->redis -> zAdd('rank_hotsearch_list',1,$video['title']);
                }else{
                    $rankhot=DI()->redis -> zAdd('rank_hotsearch_list',$scorehot+1,$video['title']);
                }
            }
            //更新视频看完次数
            if ($video['first_watch_time']){
                $res=DI()->notorm->video_long
                    ->where("id = '{$videoid}' ")
                    ->update(
                        array('watchtimes' => new NotORM_Literal("watchtimes + 1"),'last_watch_time'=>time() )
                    );
            }else{
                $res=DI()->notorm->video_long
                    ->where("id = '{$videoid}' ")
                    ->update(array(
                            'watchtimes' => new NotORM_Literal("watchtimes + 1"),
                            'first_watch_time'=>time(),
                            'last_watch_time'=>time(),
                        )
                    );
            }



            $records =DI()->notorm->video_watch_record
                ->select("uid")
                ->where("videoid='{$videoid}'")
                ->where("uid='{$uid}'")
                ->where("video_type='2'")
                ->fetchOne();
            // var_dump($record);exit;
            if(!$records){
                $record['uid']= $uid;
                $record['videoid'] = $videoid;
                $record['watchtime'] = date('Y-m-d H:i:s',time());
                $record['tenant_id'] = $game_tenant_id;
                $record['video_type'] = 2;
                $record['label'] = $video['label'];
                $record['addtime'] = time();
                $record['updatetime'] = time();
                $record['watch_count'] = 1;
                $insertrecord=DI()->notorm->video_watch_record
                    ->insert($record);
            }else{
                $insertrecord=DI()->notorm->video_watch_record
                    ->where("videoid='{$videoid}'")
                    ->where("uid='{$uid}'")
                    ->where("video_type='2'")
                    ->update( array('watch_count' => new NotORM_Literal("watch_count + 1"),'updatetime'=>time() ) );

            }

            $day = date('Ymd',time());
            $week = strftime('%U',time());
            $month = date('Ym',time());

            $redis =  connectionRedis();
            $scoreday = $redis->zScore('rank_day_list:'.$day,$video['title']);
            if(!$scoreday){
                $rankday=$redis -> zAdd('rank_day_list:'.$day,1,$video['title']);
            }else{
                $rankday=$redis-> zAdd('rank_day_list:'.$day,$scoreday+1,$video['title']);
            }
            //观影排行榜 数据更新
            $scoreweek = $redis->zScore('rank_week_list:'.$week,$video['title']);
            if(!$scoreweek){
                $rankweek=$redis -> zAdd('rank_week_list:'.$week,1,$video['title']);
            }else{
                $rankweek=$redis -> zAdd('rank_week_list:'.$week,$scoreweek+1,$video['title']);
            }


            $scoremonth = $redis->zScore('rank_month_list:'.$month,$video['title']);
            if(!$scoremonth){
                $rankmonth=$redis -> zAdd('rank_month_list:'.$month,1,$video['title']);
            }else{
                $rankmonth=$redis-> zAdd('rank_month_list:'.$month,$scoremonth+1,$video['title']);
            }

        }

        return $video;
    }
    /* 收藏*/
    public function addCollection($uid,$videoid,$game_tenant_id){
        $rs=array(
            'iscollection'=>'0',
            'collection'=>'0',
        );
        $video=DI()->notorm->video_long
            ->select("likes,uid,thumb")
            ->where("id = '{$videoid}'")
            ->fetchOne();

        if(!$video){
            return 1001;
        }
       /* if($video['uid']==$uid){
            return 1002;//不能给自己点赞
        }*/
        $like=DI()->notorm->users_video_collection
            ->select("id")
            ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id ='{$game_tenant_id}' and video_type =2")
            ->fetchOne();
        if($like){
            DI()->notorm->users_video_collection
                ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id='{$game_tenant_id}' and video_type=2 ")
                ->delete();

            DI()->notorm->video_long
                ->where("id = '{$videoid}' and collection>0")
                ->update( array('collection' => new NotORM_Literal("collection - 1") ) );
            $rs['iscollection']='0';
        }else{
            DI()->notorm->users_video_collection
                ->insert(array("uid"=>$uid,"videoid"=>$videoid,"addtime"=>time(),"tenant_id"=>$game_tenant_id,'video_type'=>2 ));

            DI()->notorm->video_long
                ->where("id = '{$videoid}'")
                ->update( array('collection' => new NotORM_Literal("collection + 1") ) );
            $rs['iscollection']='1';
            //yhTaskFinish($uid,getTaskConfig('task_4'));
        }

        $video=DI()->notorm->video_long
            ->select("collection,uid,thumb")
            ->where("id = '{$videoid}'")
            ->fetchOne();

        $rs['collection']=$video['collection'];

        return $rs;
    }

    private  static function isBuy($uid,$videoId){

        $buylongvip =DI()->notorm->users_buy_longvip
            ->select("*")
            ->where("uid = {$uid}")
            ->fetchOne();
        if($buylongvip){
            //会员有效期还在
            if($buylongvip['endtime']> time()){
                $video=DI()->notorm->video_long
                    ->select("vip_rate")
                    ->where("id = '{$videoId}'")
                    ->fetchOne();
                //会员免费视频，返回 已经购买
                if($video['vip_rate'] == 0){
                    return 1;
                }

            }
        }
        // 是否购买
        $is_buy  = DI()->notorm->users_video_buy
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 2 ")
            ->fetchOne();
        if ($is_buy){
            return 1;
        }else{
            return 0;
        }
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
            ->where("uid='{$uid}'  and tenant_id = '{$game_tenant_id}' and video_type = 2 ")
            ->order("addtime desc")
            ->limit($start,$nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach($comments as $k=>$v){
            // $comments[$k]['userinfo']=getUserInfo($v['uid']);

            $videoInfo =DI()->notorm->video_long
                ->select("*")
                ->where("id='{$v['videoid']}'")
                ->fetchOne();

            if ($videoInfo && $videoInfo['origin'] !=3){
                if($paly_url['name'] == 'minio' && strrpos($videoInfo['thumb'], '/liveprod-store-1101') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $videoInfo['thumb'] = $paly_url['url']  .'/liveprod-store-1101'. $videoInfo['thumb'];
                }elseif ($paly_url['name'] == 'aws_test' && strrpos($videoInfo['thumb'], '/liveprod-store-1039') === false){
                    $videoInfo['thumb'] = $paly_url['url']  .'/liveprod-store-1039'. $videoInfo['thumb'];
                }
                else{
                    $videoInfo['thumb'] = $paly_url['url'].$videoInfo['thumb'];
                }
                $videoInfo['href'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$videoInfo[$paly_url['viode_table_field']];
                $videoInfo['download_address'] = $download_url['url'].$videoInfo[$download_url['viode_table_field']];
            }

            if ($videoInfo){
                $videoInfo['is_collection'] = 1;
                $reply[] =  $videoInfo;
            }
        }

        return $reply;
    }
    /*  获取我的收藏*/
    public function getWatchrecord($uid,$game_tenant_id,$label,$p){
        if($p<1){
            $p=1;
        }
        $time = strtotime('-7 days');

        $nums=20;
        $start=($p-1)*$nums;
        $userInfo = getUserInfo($uid);
        if (empty($label)){
            $where = "uid='{$uid}'  and tenant_id = '{$game_tenant_id}' and user_type = {$userInfo['user_type']} and video_type = 2 and addtime >= '{$time}'";
        }else{
            $where = "uid='{$uid}'  and tenant_id = '{$game_tenant_id}' and user_type = {$userInfo['user_type']} and video_type = 2 and label = '{$label}' and  addtime >= '{$time}'";
        }

        $comments=DI()->notorm->video_watch_record  // 长视频为2 ，数据库备注有误
            ->select("*")
            ->where($where)
            ->order("watchtime desc")
            ->limit($start,$nums)
            ->fetchAll();
        $commentsCount = count($comments);

        if ($commentsCount < $nums){
            if (empty($label)){
                $where = "uid='{$uid}'  and tenant_id = '{$game_tenant_id}' and video_type = 2 and addtime < '{$time}' ";
            }else{
                $where = "uid='{$uid}'  and tenant_id = '{$game_tenant_id}' and video_type = 2 and label = '{$label}' and  addtime < '{$time}' ";
            }
            $newComments=DI()->notorm->video_watch_record
                ->select("*")
                ->where($where)
                ->order("watchtime desc")
                ->limit($start,$nums-$commentsCount)
                ->fetchAll();
            $download_url =  play_or_download_url(2);
            $paly_url = play_or_download_url(1);
            foreach($newComments as $k=>$v){
                 $video_long_list =DI()->notorm->video_long
                    ->select("*")
                    ->where("id='{$v['videoid']}'")
                    ->fetchAll();
                foreach ($video_long_list as $long_k => $long_v) {
                    if ($long_v['origin'] != 3){
                        $video_long_list[$long_k]['href'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$long_v[$paly_url['viode_table_field']];
                        $video_long_list[$long_k]['download_address'] = $download_url['url'].$long_v[$download_url['viode_table_field']];
                        //$video_long_list[$long_k]['thumb']= $paly_url['url'].$long_v['thumb'];
                        if($paly_url['name'] == 'minio' && strrpos($long_v['thumb'], '/liveprod-store-1101') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                            $video_long_list[$long_k]['thumb'] = $paly_url['url']  .'/liveprod-store-1101'. $long_v['thumb'];
                        }elseif($paly_url['name'] == 'aws_test' && strrpos($long_v['thumb'], '/liveprod-store-1039') === false){
                            $video_long_list[$long_k]['thumb'] = $paly_url['url']  .'/liveprod-store-1039'. $long_v['thumb'];
                        }
                        else{
                            $video_long_list[$long_k]['thumb'] = $paly_url['url'].$long_v['thumb'];
                        }
                    }
                }
                if ($video_long_list){
                    $reply[] = $video_long_list;
                }

            }
        }
        foreach($comments as $k=>$v){
            $video_list =DI()->notorm->video_long
                ->select("*")
                ->where("id='{$v['videoid']}'")
                ->fetchAll();
            foreach ($video_list as $video_key => $video_value) {
                if ($video_value['origin'] != 3){
                    $video_list[$video_key]['href'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$video_value[$paly_url['viode_table_field']];
                   // $video_list[$video_key]['thumb']= $paly_url['url'].$video_value['thumb'];
                    $video_list[$video_key]['download_address'] = $download_url['url'].$video_value[$download_url['viode_table_field']];
                    if($paly_url['name'] == 'minio' && strrpos($video_value['thumb'], '/liveprod-store-1101') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                        $video_list[$video_key]['thumb'] = $paly_url['url']  .'/liveprod-store-1101'. $video_value['thumb'];
                    }elseif($paly_url['name'] == 'aws_test' && strrpos($video_value['thumb'], '/liveprod-store-1039') === false){
                        $video_list[$video_key]['thumb'] = $paly_url['url']  .'/liveprod-store-1039'. $video_value['thumb'];
                    }
                    else{
                        $video_list[$video_key]['thumb'] = $paly_url['url'].$video_value['thumb'];
                    }
                }
            }
            if ($video_list){
                $newReply[] = $video_list;
            }

        }
        $rs=array(
            "counts"=>count( $newReply ) + count( $reply ),
            "earlier"=> $reply ,
            'one_week' => $newReply,

        );
        return $rs;
    }
    /*删除视频标签*/
    public function  deleteWatchrecord($uid,$id,$isdelete_all){
        if($isdelete_all){
            $rs =  DI()->notorm->video_watch_record
                ->where("uid='{$uid}'")
                ->delete();
        }else{
            $game_user = explode(',',$id );
            $where = "";
            foreach($game_user as $key => $value){
                if ($key == count($game_user)-1){
                    $where .= "'". $value."'";
                }else{
                    $where .= "'". $value."',";
                }
            }

            $where = " uid='{$uid}' and  id in ($where)";

            $rs =  DI()->notorm->video_watch_record
                ->where($where)
                ->delete();
        }

        return $rs;
    }

    public  function watchHistory($uid,$p,$game_tenant_id){

        if($p<1){
            $p=1;
        }
        $pnums=20;
        $start=($p-1)*$pnums;
        $res=DI()->notorm->video_watch_record
            ->where("uid = '{$uid}' and tenant_id = '{$game_tenant_id}'")
            ->limit($start,$pnums)
            ->fetchAll();
        $reply = array();
        foreach($res as $k=>$v){
            // $comments[$k]['userinfo']=getUserInfo($v['uid']);
            if ($v['video_type'] == 2){
                $reply[]=DI()->notorm->video_long
                    ->select("*")
                    ->where("id='{$v['videoid']}'")
                    ->fetchOne();;
            }else{
                $reply[]=DI()->notorm->video
                    ->select("*")
                    ->where("id='{$v['videoid']}'")
                    ->fetchOne();;
            }

        }
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($reply as $k => $v) {

            if ($v['origin'] != 3){
                $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$v[$paly_url['viode_table_field']];
                $video[$k]['thumb']= $paly_url['url'].$v['thumb'];
                $video[$k]['download_address'] = $download_url['url'].$v[$download_url['viode_table_field']];
            }

        }
        return $reply;
    }

    public function getVideoClassify($label){
     
        if($label){
            $classify=DI()->notorm->video_long_classify
                ->select("*")
                ->where("is_delete = 1 and label = '".$label."'")
                ->fetchAll();
        }else{
            $classify=DI()->notorm->video_long_classify
                ->select("*")
                ->where("is_delete = 1 ")
                ->fetchAll();
        }

        return $classify;
    }

    public  function getMyLongVideo($uid,$tenant_id,$p,$status){
        if ($p < 1) {
            $p = 1;
        }
        $nums = 20;
        $start = ($p - 1) * $nums;

        if ($status){
            $where = "uid='{$uid}'  and status = '{$status}'  ";
        }else{
            $where = "uid='{$uid}'   ";
        }
        $video = DI()->notorm->video_long
            ->select("*")
            ->where($where)
            ->order("id desc")
            ->limit($start,$nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($video as $k => $v) {
            $video[$k]['comments'] = NumberFormat($v['comments']);
            $video[$k]['likes'] = NumberFormat($v['likes']);
           // $video[$k]['thumb'] = get_upload_path($v['thumb']);
           // $video[$k]['href'] = get_upload_path($v['href']);
            if ($v['origin'] != 3) {
                $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
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
        $video=DI()->notorm->video_long
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
            ->where("uid='{$uid}' and videoid='{$videoid}' and tenant_id ='{$tenant_id}' and video_type =2")
            ->fetchOne();
        if(!$like){ // 重复下载不计算


            DI()->notorm->video_download
                ->insert(array(
                    "uid"=>$uid,
                    "videoid"=>$videoid,
                    "addtime"=>time(),
                    "tenant_id"=>$tenant_id,
                    'video_type'=>2
                ));

            DI()->notorm->video_long
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
            ->where("uid = '{$uid}' and video_type = 2")
            ->limit($start,$pnums)
            ->fetchAll();
        $reply = array();
        foreach($res as $k=>$v){
            $video  =DI()->notorm->video_long
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
                $reply[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                $reply[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
            }
        }
        return $reply;
    }

    /*删除视频标签*/
    public function  delMydownload($uid,$id,$isdelete_all){
        if($isdelete_all){
            $rs =  DI()->notorm->video_download
                ->where("uid='{$uid}'")
                ->delete();
        }else{
            $game_user = explode(',',$id );
            $id = '';
            foreach($game_user as $key => $value){
                if ($key == count($game_user)-1){
                    $id .= "'". $value."'";
                }else{
                    $id .= "'". $value."',";
                }
            }

            $where = " uid='{$uid}' and  id in ($id)";

            $rs =  DI()->notorm->video_download
                ->where($where)
                ->delete();
        }

        return $rs;
    }

    /**
     * 猜你喜欢
     */
    public  function guessLikeLongVide($uid,$p){

        if($p<1){
            $p=1;
        }
        $pnums=20;
        $start=($p-1)*$pnums;
        $res=DI()->notorm->video_long
            ->where('status =2 ')
            ->limit($start,$pnums)
            ->order('likes  desc')
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($res as $key=>$value){
            if ($value['origin'] != 3) {
                $res[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                $res[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                $res[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
            }
        }
        return  $res;
    }
    public  function getBanner($label){
        $where = 'status = 1';
        if ($label){
            $where.= " and label = '{$label}'";
        }
        $res=DI()->notorm->long_video_banner
            ->where($where)

            ->fetchAll();
        return  $res;
    }


    public  function getHotPerformer($uid,$tenant_id,$p){
        if ($p < 1) {
            $p = 1;
        }
        $nums = 20;
        $start = ($p - 1) * $nums;

        $video = DI()->notorm->video_long
            ->select("*")
            ->where('status = 2')
            ->where('is_performer  = 1 and status = 2 ')
            ->order("watchtimes desc")
            ->limit($start, $nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($video as $k => $v) {
            $video[$k]['comments'] = NumberFormat($v['comments']);
            $video[$k]['likes'] = NumberFormat($v['likes']);
            if ($v['origin'] != 3) {
                $res[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                $res[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                $res[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
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

        $video = DI()->notorm->video_long
            ->select("*")
            ->where('status = 2')
            ->order("watchtimes desc")
            ->limit($start, $nums)
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($video as $k => $v) {
            $video[$k]['comments'] = NumberFormat($v['comments']);
            $video[$k]['likes'] = NumberFormat($v['likes']);
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

    public  function uploadVideo($uid,$label,$title,$classify,$performer,$desc,$region,$years){
        $tenant_id = getTenantId();
        $videoinfo = getCutvideo($tenant_id);
        $user_info = getUserInfo($uid);
        $data = array(
            'uid' => $uid,
            'label' => $label,
            'title' => $title,
            'user_login' => $user_info['user_login'],
            'user_type' => intval($user_info['user_type']),
            'create_date' => date('Y-m-d H:i:s',time()),
            'origin' => 1,
            'is_performer' => 0,
            'status' => 1,
            'classify' => $classify,
            'tenant_id'=> $tenant_id,
        /*    'filestorekey' => $videoinfo['data']['fileStoreKey'],*/

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
        if ($performer){
            $data['is_performer'] =1;
            $data['performer'] = $performer;
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
        try {
            $result = DI()->notorm->video_long->insert($data);
        }catch (\Exception $e){
            logapi($data, '【长视频新增】失败：'.$e->getMessage());
            return array('code' => 502, 'msg' => codemsg('502'), 'info' => []);
        }
        return array('code' => 0, 'msg' => '', 'info' => [['video_id'=>intval($result['id']), 'title'=>$result['title'], 'upload_video_url'=>$upload_video_url]]);

    }

    public  function getperformer(){

        $performerList = DI()->notorm->performer
            ->select("*")

            ->fetchAll();

        return $performerList;
    }
    public  function getRandomVideo($url){

        $video_long = DI()->notorm->video_long

            ->where("status = 2")
            ->select("*")
            ->order('RAND()')
            ->fetchAll();
        $download_url =  play_or_download_url(2);
        $paly_url = play_or_download_url(1);
        foreach ($video_long as $key => $value){
            $self_watch_count = DI()->notorm->video_watch_record->where(" videoid = '{$value['id']}' and video_type = 1")->fetchone();
            $video_long[$key]['self_watch_count']= $self_watch_count['watch_count'];

            if ($value['origin'] != 3) {
                if ($url) {
                    $video_long[$key]['href'] = $url.$value[$paly_url['viode_table_field']];
                    $video_long[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    $video_long[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }else{

                    $video_long[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
                    $video_long[$key]['thumb'] = $paly_url['url'] . $value['thumb'];
                    $video_long[$key]['download_address'] = $download_url['url'] . $value[$download_url['viode_table_field']];
                }
            }

        }
       return $video_long;


    }

   /* private  function getRandomIndex($video_long,$data){
        $number =  count($video_long);
        $index = mt_rand(0,$number-1);

        if (array_key_exists($index,$data)){
            self::getRandomIndex($video_long,$data);
        }else{
            echo $index.'<be>';
            return $index;


        }
    }*/
    /* 长视频购买*/
    public function buyLongvideo($uid,$videoid){
        $code = codemsg();
        $video=DI()->notorm->video_long
            ->select("id,title,buy_numbers,uid,thumb,price,vip_rate")
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
            ->where("videoid = '{$videoid}' and uid = '{$uid}' and video_type = 2")
            ->fetchOne();
        if($videobuy){
            return 1004;//已经购买
        }
        $userModel = new Model_User();
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
        if($userInfo['user_type'] == 7){ // 测试账号，不能购买
            return array('code' => 402, 'msg' => $code['402'], 'info' => array('测试账号，不能购买'));
        }
        $video_price = $video['price'];
        //你是会员，且会员折扣率 为100% 无需购买

        $buylongvip =DI()->notorm->users_buy_longvip
            ->select("*")
            ->where("uid = {$uid}")
            ->fetchOne();
        if($buylongvip){
            //会员有效期还在
            if($buylongvip['endtime']> time()){
                if($video['vip_rate'] == 0){
                    return 1006;    //您是会员且没有到期，且该视频打折率100%  无需购买
                }
                 $video_price = $video['price']*$video['vip_rate']/100;
            }
        }

        if($userInfo['coin']<$video['price']){
            return 1005;
        }
        DI()->notorm->users
            ->where('id = ? and coin >=?', $uid,$video_price)
            ->update(array(
                'coin' => new NotORM_Literal("coin - {$video_price}")
            ) ); // 扣除当前用户收益
        delUserInfoCache($uid);
        $order_id = generater();
        $buy_log = DI()->notorm->users_video_buy
            ->insert(array(
                "uid"=>$uid,
                "videoid"=>$videoid,
                "addtime"=>time(),
                "tenant_id"=>$userInfo['tenant_id'],
                'video_type'=>2,
                'price'=>$video['price'],
                'user_login'=>$userInfo['user_login'],
                'ex_user_price'=>$video['price'],
                'ex_user_id'=>  $video['uid']
            ));
        DI()->notorm->video_long
            ->where("id = '{$videoid}'")
            ->update( array('buy_numbers' => new NotORM_Literal("buy_numbers + 1") ) );
        $rs['isbuy']='1';

        $insertanchor=array(
            "type"=>'expend',
            "action"=>'buy_video',
            "uid"=>$uid,
            'user_login' => $userInfo['user_login'],
            "user_type" => intval($userInfo['user_type']),
            "touid"=>$video['uid'] ,
            "giftid"=>$buy_log,
            "giftcount"=>1,
            "pre_balance" => floatval($userInfo['coin']),
            "totalcoin"=>$video_price,
            "after_balance" => floatval(bcadd($userInfo['coin'], -abs($video_price),4)),
            "showid"=>0,
            "mark"=>'11',
            "addtime"=>time(),
            'tenant_id' => $userInfo['tenant_id'],
            'remark' => '视频id: '.$video['id'].'<br>标题: '.$video['title'],
            'order_id' => $order_id,
        );

        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord($insertanchor);// 扣除用户 金额

        // 普通用户 购买不产生收入
        /*$vip_info = Model_Login::getInstance()->getUserjurisdiction($video['uid'], $authorUserInfo['user_type']);
        if($vip_info['new_level'] == '0'){
            return  $rs;
        }*/

     //   $coinrecordModel->addCoinrecord($coinrecordData); // 添加作者金额
        //$this->AgencyCommission($video['uid'],$video['price'],$buy_log,$type, $is_withdrawable, 'agent_buy_video',2, $video);
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
        $userInfo = Model_User::getInstance()->getUserInfoWithIdAndTid($uid);
        $tenantId=getTenantId();//租户ID
        //写入数据
        $coinData=[
            'type' => 'income',
            'uid' => $uid,
            'user_login' => $userInfo['user_login'],
            'user_type' => $userInfo['user_type'],
            'giftid' =>$giftid,
            'addtime' => time(),
            'tenant_id' => intval($tenantId),
            'action' => 'buy_shot_video',
            'totalcoin' => floatval($coin),//金额
            "giftcount"=>1,
        ];
        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord($coinData);
    }


    public function watchVodeo($uid,$videoid,$game_tenant_id){
        $userInfo = getUserInfo($uid);
        $watchCount = DI()->notorm->video_watch_record->where("tenant_id = {$game_tenant_id} and  video_type = 2 and uid = {$uid} and user_type = {$userInfo['user_type']}  ")->sum('watch_count');
        $config = getConfigPri();

        if ($userInfo['user_type'] == 4){
            if ($config['tourists_watch_time']  > 0 && $watchCount>= $config['tourists_watch_time'] ){
                return  ['code'=> 2124, 'msg' => codemsg(2124)];
            }
        }else{
            if ($config['vip_watch_time']  > 0 && $watchCount>= $config['vip_watch_time'] ){
                return  ['code'=> 2125, 'msg' => codemsg(2125)];
            }
        }

        $info= DI()->notorm->video_watch_record->where("tenant_id = {$game_tenant_id} and  videoid = '{$videoid}' and  video_type = 2 and uid = {$uid} and user_type = {$userInfo['user_type']}")->fetchone();
        if ($info){
            DI()->notorm->video_watch_record->where("id = {$info['id']}")
                ->update(array('watch_count' => new NotORM_Literal("watch_count + 1") ));
        }else{
            $record['uid']= $uid;
            $record['videoid'] = $videoid;
            $record['watchtime'] = date('Y-m-d H:i:s',time());
            $record['tenant_id'] = $game_tenant_id;
            $record['user_type'] = $userInfo['user_type'];
            $record['video_type'] = 2;
            $record['addtime'] = time();
            $record['updatetime'] = time();
            $record['watch_count'] = 1;
            DI()->notorm->video_watch_record
                ->insert($record);
        }
        DI()->notorm->video_long
                ->where("id = '{$videoid}'")
                ->update( array('watchtimes' => new NotORM_Literal("watchtimes + 1") ) );
        return ['code'=> 0];

    }
    public function getLongvideovip($game_tenant_id){

        $tenantId=getTenantId();//租户ID


        $vip_longgrade=DI()->notorm->vip_longgrade
            ->select("*")

            ->where('tenant_id = '.$tenantId)
            ->order('id asc')
            ->fetchAll();

        return $vip_longgrade;

    }
    public function getLongvideosearch($game_tenant_id){

        $tenantId=getTenantId();//租户ID


        $longvideo_search=DI()->notorm->long_video_search
            ->select("*")
            ->where('tenant_id = '.$tenantId.' and search_type = 2 ') //长视频搜索
            ->order('times desc')
            ->limit(0,8)
            ->fetchAll();

        return $longvideo_search;

    }
    public function getSearchbyuser($uid){

        $tenantId=getTenantId();//租户ID
        $longvideo_search=DI()->notorm->long_video_usersearch
            ->select("*")
            ->where('tenant_id = '.$tenantId.' and uid= '.$uid) //长视频搜索
            ->order('times desc,addtime desc')
            ->limit(0,5)
            ->fetchAll();

        return $longvideo_search;

    }

    public function delSearchbyuser($uid,$search_id){

        $tenantId=getTenantId();//租户ID
        $longvideo_search=DI()->notorm->long_video_usersearch
            ->select("*")
            ->where('tenant_id = '.$tenantId.' and id= '.$search_id) //长视频搜索
            ->delete();

        return $longvideo_search;

    }
    /* 长视频vip购买*/
    public function buyLongvideovip($uid,$vip_grade){

        $code = codemsg();
        $vip_info=DI()->notorm->vip_longgrade
            ->select("*")
            ->where("vip_grade = '{$vip_grade}'")
            ->fetchOne();

        if(!$vip_info){
            return 1001;
        }

        $usersbuy=DI()->notorm->users_buy_longvip
            ->select("*")
            ->where(" uid = {$uid}"." and status = 1")
            ->fetchOne();


        $userModel = new Model_User();
        $userInfo = $userModel->getUserInfoWithIdAndTid($uid);
        if($userInfo['user_type'] == 7){ // 测试账号，不能购买
            return array('code' => 402, 'msg' => $code['402'], 'info' => array('测试账号，不能购买'));
        }
        if($userInfo['coin']<$vip_info['price']){
            return 1005;
        }
        DI()->notorm->users
            ->where('id = ? and coin >=?', $uid,$vip_info['price'])
            ->update(array(
                'coin' => new NotORM_Literal("coin - {$vip_info['price']}")
            ) ); // 扣除当前用户收益
        delUserInfoCache($uid);

          if($usersbuy){

              DI()->notorm->users_buy_longvip
                  ->where('uid = ? and status =1', $uid)
                  ->update(array(
                      "endtime"=>$usersbuy['endtime']+$vip_info['effect_days']*86400,
                      'vip_level'=>$vip_info['vip_grade']
                  ) );
              $endtime = $usersbuy['endtime']+$vip_info['effect_days']*86400;
          }else{
              $buy_log = DI()->notorm->users_buy_longvip
                  ->insert(array(
                      'user_login'=>$userInfo['user_login'],
                      "uid"=>$uid,
                      'user_type'=>$userInfo['user_type'],
                      "addtime"=>time(),
                      'price'=>$vip_info['price'],
                      "tenant_id"=>$userInfo['tenant_id'],
                      'video_type'=>2,
                      "endtime"=>time()+$vip_info['effect_days']*86400,
                      'status'=>1,
                      'vip_level'=>$vip_info['vip_grade']
                  ));
              $endtime = time()+$vip_info['effect_days']*86400;
          }
        $order_id = generater();
        $rs['end_time']=date("Y-m-d H:i:s",$endtime);
        $rs['order_id']=$order_id;
        $insertanchor=array(
            "type"=>'expend',
            "action"=>'buy_longvideovip',
            "uid"=>$uid,
            'user_login' => $userInfo['user_login'],
            "user_type" => intval($userInfo['user_type']),
            "touid"=>'' ,
            "giftid"=>$buy_log,
            "giftcount"=>1,
            "showid"=>1,
            "pre_balance" => floatval($userInfo['coin']),
            "totalcoin"=>$vip_info['price'],
            "after_balance" => floatval(bcadd($userInfo['coin'], -abs($vip_info['price']),4)),
            "mark"=>'11',
            "addtime"=>time(),
            'tenant_id' => $userInfo['tenant_id'],
            'remark' => '长视频vip等级: '.$vip_info['vip_grade'].'<br>价格: '.$vip_info['price'],
            'order_id'=> $order_id,
        );
        DI()->notorm->users->where("id=?",$uid)->update(array("userlevel"=>$vip_info['vip_grade']));
        $coinrecordModel = new Model_Coinrecord();
        $coinrecordModel->addCoinrecord($insertanchor);// 扣除用户 金额


        return  $rs;
    }
    /* 长视频vip购买*/
    public function buyLongvideovipList($uid){

        $code = codemsg();
        $coinrecord=DI()->notorm->users_coinrecord
            ->select("*")
            ->where("uid = {$uid} and action = 'buy_longvideovip'")
            ->fetchAll();


        return  $coinrecord;
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
            DI()->notorm->video_long->where(['id'=>intval($video_id), 'uid'=>intval($uid), 'tenant_id'=>intval($tenant_id)])->update($data);

        }catch (\Exception $e){
            logapi($data, '【长视频信息更新】失败：'.$e->getMessage());
            return array('code' => 501, 'msg' => codemsg('501'), 'info' => []);
        }
        return array('code' => 0, 'msg' => '', 'info' => []);
    }





}
