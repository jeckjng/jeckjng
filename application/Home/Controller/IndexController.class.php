<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Home\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class IndexController extends HomebaseController {
	
    //首页
	public function index() {

	    $page = I('page');
        $agent_code = I('code');
        $game_tenant_id = I('game_tenant_id');
        $zone = I('zone');

		$prefix= C("DB_PREFIX");
		$this->assign("current",'index');	
		$uid=session("uid");
		$tenantId=getTenantId();
		$firstLive="";

		/*获取推荐播放列表(正在直播，推荐，按粉丝数排序)*/
        //TODO 加入允许租户条件
		$indexLive=M("users_live")->query("select l.* from __PREFIX__users_live l left join __PREFIX__users u on l.uid=u.id where l.islive='1' and u.isrecommend='1' and l.type='0'
        and (l.tenant_id='$tenantId' or l.isshare='1')  ");

		//var_dump($indexLive);
		
		foreach ($indexLive as $k => $v){
			if($v['thumb']==""){
				$indexLive[$k]['thumb']=get_upload_path($v['avatar']);
			}
			if($v['isvideo']==0){
                if($this->configpri['cdn_switch']!=5){
                    $indexLive[$k]['pull']=PrivateKeyA('rtmp',$v['stream'],0);
                    $indexLive[$k]['flvpull']=PrivateKeyA('http',$v['stream'].'.flv',0);
                }
            }
			$indexLive[$k]['fans_nums']=M("users_attention")->where("touid={$v['uid']}")->count();
		}

		$sort=array_column($indexLive,"fans_nums");
		array_multisort($sort, SORT_DESC, $indexLive);
		$indexLive1=array_slice($indexLive,0,4);
		$firstLive=$indexLive[0]['flvpull'];
		$firstUid=$indexLive[0]['uid'];
		$this->assign("indexLive",$indexLive1);
		$this->assign("firstUid",$firstUid);
		$this->assign("firstLive",$firstLive);
		//var_dump($indexLive1);
		/* 轮播 */
		$slide=M("slide")->where("slide_status='1' and tenant_id='$tenantId'")->order("listorder asc")->select();
		$this->assign("slide",$slide);	

		$redis =connectionRedis();

		/* 推荐（正在直播 在线人数） */
		$recommend=M("users_live l")
					->field("l.user_nicename,l.avatar,l.thumb,l.uid,l.stream,l.type,l.islive")
					->join("left join {$prefix}users u on u.id=l.uid")
					->where("l.islive='1' and (l.tenant_id='$tenantId' or l.isshare='1')")
					->limit(12)
					->select();
		foreach($recommend as $k=>$v){
	 		if($v['thumb']=="")
			{
				$recommend[$k]['thumb']=$v['avatar'];
			} 
			$nums=$redis->zCard('user_'.$v['stream']);
			$recommend[$k]['nums']=$nums;
		}

		$sort=array_column($recommend,"nums");
		$sort1=array_column($recommend,"uid");
		array_multisort($sort, SORT_DESC,$sort1,SORT_DESC, $recommend);

		
		$this->assign("recommend",$recommend);			 
			 
		/* 热门（在直播，推荐为热门） */
		$hot=M("users_live l")
					->field("l.user_nicename,l.avatar,l.uid,l.thumb,l.stream,l.title,l.city,l.islive,l.type,u.signature")
					->join("left join {$prefix}users u on u.id=l.uid")
					->where("l.islive='1' and u.ishot='1' and (l.tenant_id='$tenantId' or l.isshare='1')")
					->order("u.isrecommend desc,l.starttime desc")
					->limit(10)
					->select();
		 foreach($hot as $k=>$vi){
			$nums=$redis->zCard('user_'.$vi['stream']);

			$hot[$k]['nums']=(string)$nums;
			if($vi['thumb']=="")
			{
				$hot[$k]['thumb']=$vi['avatar'];
			}
		} 
		$this->assign("hot",$hot);

		/* 最新直播（在直播，按开播时间倒序） */ 
		$live=M("users_live")->field("uid,avatar,user_nicename,thumb,stream,title,city,islive,type")->where("islive='1' and (tenant_id='$tenantId' or isshare='1')")->order("starttime desc")->limit(10)->select();
		foreach($live as $k=>$vo){
			$nums=$redis->zCard('user_'.$vo['stream']);

			$live[$k]['nums']=(string)$nums;
			$live[$k]['signature']=M("users")->where("id={$vo['uid']}")->getField("signature");	
			if($vo['thumb']==""){
				$live[$k]['thumb']=get_upload_path($vo['avatar']);
			}
		} 

		$this->assign("live",$live);
        $this->assign("page",$page);
        $this->assign("agent_code",$agent_code);
        $this->assign("game_tenant_id",$game_tenant_id);
        $this->assign("zone",$zone);

		/* 主播排行榜 */
	  /*$anchorlist=M("users_liverecord")->field("uid,sum(nums) as light")->order("light desc")->group("uid")->limit(10)->select();
		foreach($anchorlist as $k=>$v){
			$anchorlist[$k]['userinfo']=getUserInfo($v['uid']);
			// 判断 当前用户是否关注
			if($uid>0){
				$isAttention=isAttention($uid,$v['uid']);
				$anchorlist[$k]['isAttention']=$isAttention;
			}else{
				$anchorlist[$k]['isAttention']=0;
			}
			
		}
		$this->assign("anchorlist",$anchorlist);*/

		$redis->close();
    	$this->display();
    }

    public   function decode_data($secretData,$signkey){   //数据解密函数
        return openssl_decrypt(base64_decode($secretData),'AES-128-CBC',$signkey,OPENSSL_RAW_DATA,'!WFNZFU_{H%M(S|a');
    }
	
	public function translate()
	{
		$prefix= C("DB_PREFIX");	
		
		if($_REQUEST['keyword']!='')
		{
			$where="user_type='2'";
			$keyword=$_REQUEST['keyword'];
			$where.=" and (id='{$keyword}' OR user_nicename like '%{$keyword}%')";
			$_GET['keyword']=$_REQUEST['keyword'];
		}
		else
		{
			$where="u.user_type='2' and l.islive='1' ";
		}
		$tenantId=getTenantId();
		$where=$where."and (l.tenant_id='$tenantId' or l.isshare='1')";

		$auth=M("users");
		$pagesize = 18; 
		if($_REQUEST['keyword']=="")
		{
			$count= M("users_live l")
					->field("l.user_nicename,l.avatar,l.uid,l.stream,l.title,l.city,l.islive")
					->join("left join {$prefix}users u on u.id=l.uid")
					->where($where)
					->order("l.starttime desc")
					->count();
			$Page= new \Page2($count,$pagesize);
			$show= $Page->show();
			$lists=M("users_live l")
					->field("l.user_nicename,l.avatar,l.uid,l.stream,l.title,l.city,l.islive")
					->join("left join {$prefix}users u on u.id=l.uid")
					->where($where)
					->order("l.starttime desc")
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
			$msg["info"]='抱歉,没有找到关于"';
			$msg["name"]='';
			$msg["result"]='"的搜索结果';
			$msg["type"]='0';
		}else{
			$count= $auth->where($where)->count();
			$Page= new \Page2($count,$pagesize);
			$show= $Page->show();
			$lists=$auth->where($where)->order("consumption desc")->limit($Page->firstRow.','.$Page->listRows)->select();
			$msg["info"]='共找到'.$count.'个关于"';
			$msg["name"]=$_REQUEST['keyword'];
			$msg["result"]='"的搜索结果';
			$msg["type"]='1';
		}
		$this->assign('lists',$lists);
		$this->assign('msg',$msg);
		$this->assign('page',$show);
		$this->assign('formget', $_GET);
		$this->display();
	}	
    
    /* 图片裁剪 */
    function cutImg(){
        $filepath=I('filepath');
        $new_width=I('width');
        $new_height=I('height');
        $source_info   = getimagesize($filepath);
        $source_width  = $source_info[0];
        $source_height = $source_info[1];
        $source_mime   = $source_info['mime'];
        $source_ratio  = $source_height / $source_width;
        $target_ratio  = $new_height / $new_width;
        // 源图过高
        if ($source_ratio > $target_ratio){

            $cropped_width  = $source_width;
            $cropped_height = $source_width * $target_ratio;
            $source_x = 0;
            $source_y = ($source_height - $cropped_height) / 2;
        }
        // 源图过宽
        elseif ($source_ratio < $target_ratio){
        	
            $cropped_width  = $source_height / $target_ratio;
            $cropped_height = $source_height;
            $source_x = ($source_width - $cropped_width) / 2;
            $source_y = 0;
        }
        // 源图适中
        else{

            $cropped_width  = $source_width;
            $cropped_height = $source_height;
            $source_x = 0;
            $source_y = 0;
        }

        switch ($source_mime){
            case 'image/gif':
                $source_image = imagecreatefromgif($filepath);
                break;
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($filepath);
                break;
            default:
                return false;
            break;
        }

        $target_image  = imagecreatetruecolor($new_width, $new_height);
        $cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);
        // 裁剪
        imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
        // 缩放
        imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $new_width, $new_height, $cropped_width, $cropped_height);
        header('Content-Type: image/jpeg');
        imagejpeg($target_image);
        imagedestroy($source_image);
        imagedestroy($target_image);
        imagedestroy($cropped_image);
    }
    function test(){
        $list=M("users_family")->field("id,addtime")->select();
        foreach($list as $k=>$v){
            
            M("users_family")->where("id={$v['id']}")->save(['uptime'=>$v['addtime']]);
        }
        
    }

    public function pchome3(){
        $page = I('page');
        $pagesize = 24;
        $class = I('class');
        $video_name = I('video_name');
        $label = M('video_label_long')->where(array('is_delete'=> 1))->order('sort')->select();
        $ads = M('ads')->where(array('sid'=> 9))->order('orderno')->select();
        $ads2 = M('ads')->where(array('sid'=> 11))->order('orderno')->select();
        $footads = M('ads')->where(array('sid'=> 10))->order('orderno')->select();
        $where = array('status'=> 2);
        if (!empty($class)){
            $where['label'] = $class;
        }
        if (!empty($video_name)){
            $where['title'] = ['like','%'.$video_name.'%'];
        }
        $count = M('video_long')->where($where)->count();
        $Page       = new \Page2($count,$pagesize);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show2();
        $video = M('video_long')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('video_name',$video_name);
        $this->assign('label',$label);
        $this->assign('class',$class);
        $this->assign('ads',$ads);
        $this->assign('ads2',$ads2);
        $this->assign('footads',$footads);
        $this->assign('video',$video);
        $this->assign('page',$show);// 赋值分页输出
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $iphone = (strpos($agent, 'iphone')) ? true : false;
        $ipad = (strpos($agent, 'ipad')) ? true : false;
        $android = (strpos($agent, 'android')) ? true : false;
        if($iphone || $ipad || $android){
            $this->display('h5home');
        } else {
            $this->display();
        };
    }

    public  function playvidoe3(){
        $id = I('id');
        $videoInfo = M('video_long')->where(array('id' => $id))->find();
        $page = I('page');
        $pagesize = 24;
        $class = I('class');
        $video_name = I('video_name');
        $label = M('video_label_long')->where(array('is_delete'=> 1))->order('sort')->select();
        $ads = M('ads')->where(array('sid'=> 9))->order('orderno')->select();
        $ads2 = M('ads')->where(array('sid'=> 11))->order('orderno')->select();
        $footads = M('ads')->where(array('sid'=> 10))->order('orderno')->select();
        $where = array('status'=> 2);
        if (!empty($class)){
            $where['label'] = $class;
        }
        if (!empty($video_name)){
            $where['title'] = ['like','%'.$video_name.'%'];
        }
        $count = M('video_long')->where($where)->count();
        $Page       = new \Page2($count,$pagesize);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show2();
        $video = M('video_long')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('video_name',$video_name);
        $this->assign('label',$label);
        $this->assign('class',$class);
        $this->assign('ads',$ads);
        $this->assign('ads2',$ads2);
        $this->assign('footads',$footads);
        $this->assign('video',$video);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('videoInfo',$videoInfo);
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $iphone = (strpos($agent, 'iphone')) ? true : false;
        $ipad = (strpos($agent, 'ipad')) ? true : false;
        $android = (strpos($agent, 'android')) ? true : false;
        if($iphone || $ipad || $android){
            $this->display('h5playvideo');
        } else {
            $this->display();
        };
    }

    public  function playvidoe(){
        $label_list = M('video_label_long')->where(array('is_delete'=> 1))->order('sort')->select();

        $id = I('id');
        $videoInfo = M('video_long')->where(array('id' => $id))->find();

        if ($videoInfo){
            if ($videoInfo['origin'] != 3){
                $href = M('playback_address')->where(['is_enable'=>1,'type'=>1])->getField('viode_table_field');
                $videoInfo['href'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'] . $videoInfo[$href];
            }
        }

        $class = I('class');

        $video_list = M('video_long')->where(array('status'=> 2))->limit('0,12')->select();

        $ads = M('ads')->order('orderno')->find(30);
        $this->assign('ads',$ads);
        $this->assign('video_list',$video_list);
        $this->assign('class',$class);
        $this->assign('label_list',$label_list);
        $this->assign('videoInfo',$videoInfo);
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $iphone = (strpos($agent, 'iphone')) ? true : false;
        $ipad = (strpos($agent, 'ipad')) ? true : false;
        $android = (strpos($agent, 'android')) ? true : false;
        if($iphone || $ipad || $android){
            $this->assign('if_pc',2);
        } else {
            $this->assign('if_pc',1);
        };
        $this->display();
    }

    public function pchome(){
        $label_list = M('video_label_long')->where(array('is_delete'=> 1))->order('sort')->select();

        $where = array('status'=> 2);
        //排行榜
        $video_list = M('video_long')->where($where)->limit('0,15')->select();

        //推荐
        $label = M('video_label_long')->where(array('is_delete'=> 1))->limit('0,4')->order('sort')->select();

        foreach ($label as $key=>$val){
            $where['label']=$val['label'];
            $label[$key]['video_list'] = M('video_long')->where($where)->limit('0,8')->select();
        }

        $ads = M('ads')->order('orderno')->find(30);
        $this->assign('ads',$ads);
        $this->assign('label_list',$label_list);
        $this->assign('label',$label);
        $this->assign('video_list',$video_list);
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $iphone = (strpos($agent, 'iphone')) ? true : false;
        $ipad = (strpos($agent, 'ipad')) ? true : false;
        $android = (strpos($agent, 'android')) ? true : false;
        if($iphone || $ipad || $android){
            $this->assign('if_pc',2);
        } else {
            $this->assign('if_pc',1);
        };
        $this->display();
    }

    public function pc_info(){
        $label_list = M('video_label_long')->where(array('is_delete'=> 1))->order('sort')->select();

        $class = I('class','1');
        $sort = I('sort',1);
        $video_name = I('video_name');
        if ($sort == 1){
            $sortSql = 'id desc';
        }elseif ($sort == 2){
            $sortSql = 'watchtimes desc';
        }elseif ($sort == 3){
            $sortSql = 'create_date desc';
        }
        $pagesize = 18;

        $where['status'] = 2;
        if ($class && $class != '1'){
            $where['label'] = $class;
        }
        if ($video_name){
            $where['title'] = ['like','%'.$video_name.'%'];
        }


        $count = M('video_long')->where($where)->count();
        $Page       = new \Page2($count,$pagesize);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();

        $video_list = M('video_long')->where($where)->order($sortSql)->limit($Page->firstRow.','.$Page->listRows)->select();

        $ads = M('ads')->order('orderno')->find(30);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('ads',$ads);
        $this->assign('video_name',$video_name);
        $this->assign('video_list',$video_list);
        $this->assign('class',$class);
        $this->assign('sort',$sort);
        $this->assign('label_list',$label_list);
        $this->display();
    }

    public  function register(){
        $this->display();
    }
}


