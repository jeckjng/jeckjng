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
 * 消费相关
 */
class SpendController extends HomebaseController {
	/* 送礼物 */
	public function sendGift(){
		$User=M("users");
		$uid=session("uid");
		$tenantId=getTenantId();
		$touid=I('touid');
		$giftid=I('giftid');
		$giftcount=1;
		if($uid==$touid)
		{
			echo '{"errno":"1000","data":"","msg":"不允许给自己送礼物"}';
			exit;	
		}
		$userinfo= $User->field('coin,token,expiretime,user_nicename,avatar')->where("id='{$uid}'")->find();
		if($userinfo['token']!=session("token") || $userinfo['expiretime']<time()){
            session('uid',null);		
            session('token',null);
            session('user',null);
            cookie('uid',null);
            cookie('token',null);
			echo '{"errno":"700","data":"","msg":"您的登陆状态失效，请重新登陆！"}';
			exit;	
		}

        $anchorinfo=getTenantUserInfo($touid,$tenantId);
        if(!$anchorinfo){
            /**
             * 主播信息不存在或主播为其他租户的非公共主播,无法赠送礼物
             */
            echo '{"errno":"1001","data":"","msg":"暂时无法赠送"}';
            exit;
        }

		/* 礼物信息 */
		$giftinfo=M("gift")->field("giftname,gifticon,needcoin,type,mark,swftype,swf,swftime")->where("id='{$giftid}'")->find();		
		$total= $giftinfo['needcoin']*$giftcount;
		$addtime=time();

		$users_live=M("users_live")->where("uid='{$touid}' and islive=1")->	find();
		$showid=0;
		if($users_live){
            /**
             * 以主播开播时间作为直播标识
             */
			$showid=$users_live['starttime'];
		}
		/* 更新用户余额 消费 */
		$ifok=M()->execute("update __PREFIX__users set coin=coin-{$total},consumption=consumption+{$total} where id='{$uid}' and coin >={$total}");
		if(!$ifok){
            /* 余额不足 */
			echo '{"errno":"1001","data":"","msg":"余额不足"}';
			exit;	
        }
		/* 分销 */	
		setAgentProfit($uid,$total);
		/* 分销 */	
		$configpri=getConfigPri();
	
		/* 家族分成之后的金额 */
		$anthor_total=setFamilyDivide($touid,$total);
		
		/* 更新直播 映票 累计映票 */						 
		M()->execute("update __PREFIX__users set votes=votes+{$anthor_total},votestotal=votestotal+{$total} where id='{$touid}'");
        $insert_votes=[
            'type'=>'income',
            'action'=>'sendgift',
            'uid'=>$touid,
            'votes'=>$anthor_total,
            'addtime'=>time(),
        ];
        M('users_voterecord')->add($insert_votes);
		/* 更新直播 映票 累计映票 */
        $this->addCoinrecord(array("type"=>'expend',"action"=>'sendgift',"uid"=>$uid,"touid"=>$touid,"giftid"=>$giftid,"giftcount"=>$giftcount,"totalcoin"=>$total,"showid"=>$showid,"mark"=>$giftinfo['mark'],"addtime"=>$addtime ));
		$userinfo2=$User->where("id='{$uid}'")->find();
		$level=getLevel($userinfo2['tenant_id'],$userinfo2['consumption']);
        
        /* 更新主播热门 */
        if($giftinfo['mark']==1){
            M()->execute("update __PREFIX__users_live set hotvotes=hotvotes+{$total} where uid='{$touid}'");
        }
        
		/* 清除缓存 */
		delCache("userinfo_".$uid); 
		delCache("userinfo_".$touid); 
        
        $userinfo3=$User->field("votestotal")->where("id='{$touid}'")->find();
        
		$gifttoken=md5(md5('sendGift'.$uid.$touid.$giftid.$giftcount.$total.$showid.$addtime));
        
        $swf=$giftinfo['swf'] ? get_upload_path($giftinfo['swf']):'';
        
		$result=array("uid"=>(int)$uid,"giftid"=>(int)$giftid,"type"=>$giftinfo['type'],"giftcount"=>(int)$giftcount,"totalcoin"=>$total,"giftname"=>$giftinfo['giftname'],"gifticon"=>get_upload_path($giftinfo['gifticon']),"swftype"=>$giftinfo['swftype'],"swftime"=>$giftinfo['swftime'],"swf"=>$swf,"level"=>$level,"coin"=>$userinfo2['coin'],"votestotal"=>$userinfo3['votestotal']);
        
		$redis = connectionRedis();
		$redis  -> set($gifttoken,json_encode($result));
        if($users_live){
            $redis->zIncrBy('user_'.$users_live['stream'],$total,$uid);
        }
        
		$redis -> close();	

		echo '{"errno":"0","uid":"'.$uid.'","level":"'.$level.'","type":"'.$giftinfo['type'].'","coin":"'.$userinfo2['coin'].'","gifttoken":"'.$gifttoken.'","msg":"赠送成功"}';
		exit;	
			
	}		
	/* 弹幕 */
	public function sendHorn(){
		$rs = array('code' => 0, 'msg' => '', 'info' =>array());
		$users=M("users");
		$uid=session("uid");
		$tenantId=getTenantId();
		$liveuid=I("liveuid");
		$content=I("content");
		$stream=I("stream");

        $anchorinfo=getTenantUserInfo($liveuid,$tenantId);
        if(!$anchorinfo){
            /**
             * 主播信息不存在或主播为其他租户的非公共主播,无法赠送礼物
             */
            $rs['code']=1001;
            $rs['msg']='暂时无法赠送';
            echo  json_encode($rs);
            exit;
        }



		$configpri=getConfigPri();
		$giftid=0;
		$giftcount=1;
		$giftinfo=array(
			"giftname"=>'弹幕',
			"gifticon"=>'',
			"needcoin"=>$configpri['barrage_fee'],
		);		
		
		$total= $giftinfo['needcoin']*$giftcount;
		 
		$addtime=time();
		$type='expend';
		$action='sendbarrage';

		/* 更新用户余额 消费 */
		$ifok=M()->execute("update __PREFIX__users set coin=coin-{$total},consumption=consumption+{$total} where id='{$uid}' and coin >={$total}");
        if(!$ifok){
            $rs['code']=1001;
			$rs['msg']='余额不足';
			echo  json_encode($rs);
			exit;
        }
        
        setAgentProfit($uid,$total);
        
		/* 更新直播主播 映票 累计映票 */						 
		M()->execute("update __PREFIX__users set votes=votes+{$total},votestotal=votestotal+{$total} where id='{$liveuid}'");
        
        $insert_votes=[
            'type'=>'income',
            'action'=>$action,
            'uid'=>$liveuid,
            'votes'=>$total,
            'addtime'=>time(),
        ];
        M('users_voterecord')->add($insert_votes);
				
		$stream2=explode('_',$stream);
		$showid=$stream2[1];
        
        if(!$showid){
            $showid=0;
        }

		$insert=array("type"=>$type,"action"=>$action,"uid"=>$uid,"touid"=>$liveuid,"giftid"=>$giftid,"giftcount"=>$giftcount,"totalcoin"=>$total,"showid"=>$showid,"addtime"=>$addtime );
		$isup=$this->addCoinrecord($insert);
					 
		$userinfo2 =$users->where("id=".$uid)->find();
		/*获取当前用户的等级*/
		$level=getLevel($userinfo2['tenant_id'],$userinfo2['consumption']);
		
		/* 清除缓存 */
		delCache("userinfo_".$uid); 
		delCache("userinfo_".$liveuid); 
		/*获取主播影票*/
		$votestotal=$users->field('votestotal,coin')->where("id=".$liveuid)->find();
		
		$barragetoken=md5(md5($action.$uid.$liveuid.$giftid.$giftcount.$total.$showid.$addtime.rand(100,999)));
		 
		$result=array("uid"=>$uid,"content"=>$content,"giftid"=>$giftid,"giftcount"=>$giftcount,"totalcoin"=>$total,"giftname"=>$giftinfo['giftname'],"gifticon"=>$giftinfo['gifticon'],"level"=>$level,"coin"=>$userinfo2['coin'],"votestotal"=>$votestotal['votestotal'],"barragetoken"=>$barragetoken);
		$rs['info']=$result;
		/*写入 redis*/
		unset($result['barragetoken']);
		$redis =connectionRedis();
		$redis -> set($barragetoken,json_encode($result));
		$redis -> close();
		
		echo json_encode($rs);
	
	}
	/*设置 取消 管理员*/
	public function cancel()
	{
        /**
         * 房管不限租户
         */
		$rs = array('code' => 0, 'msg' => '', 'info' =>'操作成功');
		$uid=session("uid");
		$touid=I("touid");
		$showid=I("roomid");
		$users_livemanager=M("users_livemanager");
		if($uid!=$showid)
		{
			$rs['code']=1001;
			$rs['msg']='不是该房间主播';
			echo  json_encode($rs);
			exit;
		}
		if($uid==$touid)
		{
			$rs['code']=1002;
			$rs['msg']='自己无法管理自己';
			echo  json_encode($rs);
			exit;
		}
		$admininfo=$users_livemanager->where("uid='{$touid}' and liveuid='{$showid}'")->find();
		$rs=M("users")->field("id,avatar,avatar_thumb,user_nicename")->where("id=".$touid)->find();
		if($admininfo)
		{
			$users_livemanager->where("uid='{$touid}' and liveuid='{$showid}'")->delete();
			$rs['isadmin']=0;	
		}
		else
		{
			$count =$users_livemanager->where("liveuid='{$showid}'")->count();
			if($count>=5)
			{
				$rs['code']=1004;
				$rs['msg']='最多设置5个管理员';
				echo  json_encode($rs);
				exit;
			}
			$users_livemanager->add( array("uid"=>$touid,"liveuid"=>$showid));
			$rs['isadmin']=1;
		}
		$rs['msg']="设置成功";
		echo  json_encode($rs);
		exit;
	}
	/*禁言*/
	public function gag()
	{
		$rs = array('code' => 0, 'msg' => '禁言成功', 'info' => array());
		$uid=session("uid");
		$touid=I("touid");
		$showid=I("roomid");
		$uidtype = getIsAdmin($uid,$showid);
		if($uidtype==30 ){
			$rs["code"]=1001;
			$rs["msg"]='你不是主播或者管理员';
			echo  json_encode($rs);
			exit;
		}
		$touidtype = getIsAdmin($touid,$showid);
		if($touidtype==50)
		{
			$rs["code"]=1002;
			$rs["msg"]='对方是主播，不能禁言';
			echo  json_encode($rs);
			exit;
		}
		else if($touidtype==40 )
		{
			$rs["code"]=1002;
			$rs["msg"]='对方是管理员，不能禁言';
			echo  json_encode($rs);
			exit;
		}
		else if($touidtype==60 )
		{
			$rs["code"]=1002;
			$rs["msg"]='对方是超管，不能禁言';
			echo  json_encode($rs);
			exit;
		}
		
		$nowtime=time();
		$redis = connectionRedis();
		$result=$redis -> hGet($showid . 'shutup',$touid);
		if($result){
			if($nowtime<$result){
				$rs["code"]=1003;
				$rs["msg"]='对方已被禁言';
				echo  json_encode($rs);
				exit;
			}
		}
		$configpri=getConfigPri();
		$time=$nowtime + $configpri['shut_time'];
		$redis -> hSet($showid . 'shutup',$touid,$time);
		$redis -> close();
		$rs["info"]['shut_time']=$configpri['shut_time'].'秒';
		echo  json_encode($rs);
		exit;
	}
	public function isShutUp() {
		$uid=session("uid");
		$showid=I("showid");
		$rs = array('code' => 0, 'msg' => '', 'info' => '0');
		$nowtime=time();
		if($uid)
		{
			$admin = getIsAdmin($uid,$showid);
			$rs['admin']=$admin;
			$redis = connectionRedis();
			$result=$redis -> hGet($showid . 'shutup',$uid);
			if($result)
			{
				if($nowtime>$result)
				{
					$result=$redis -> hDel($showid . 'shutup',$uid);
					$rs['info']=0;
				}else{
						$rs['info']=1;
					}
			}
			else
			{
				$rs['info']=0;
			}
			$redis -> close();				
		}
		echo  json_encode($rs);
		exit;
  }
	/*踢人*/
	public function tiren()
	{
		$rs = array('code' => 0, 'msg' => '', 'info' =>'操作成功');
		$uid=session("uid");
		$touid=I("touid");
		$showid=I("roomid");
		$user_type = getIsAdmin($uid,$showid);
		if($user_type==30)
		{
			$rs['code']=1000;
			$rs['msg']='您不是管理员，无权操作';
			echo  json_encode($rs);
			exit;
		}
		$touser_type =getIsAdmin($touid,$showid);
		if($touser_type==50 )
		{
			$rs['code']=1001;
			$rs['msg']='对方是主播，不能被踢出';
			echo  json_encode($rs);
			exit;
		}
		else if($touser_type==40)
		{
			$rs['code']=1002;
			$rs['msg']='对方是管理员，不能被踢出';
			echo  json_encode($rs);
			exit;
		}
		else if($touser_type==60)
		{
			$rs["code"]=1002;
			$rs["info"]='对方是超管，不能被踢出';
			$rs["msg"]='对方是超管，不能被踢出';
			echo  json_encode($rs);
			exit;
		}
		$configpri=getConfigPri();
		$nowtime=time();
		$time=$nowtime+ $configpri['kick_time'];
		$redis = connectionRedis();
		$result=$redis  -> hset($showid.'kick',$touid,$time);
		$redis -> close();
		echo  json_encode($rs);
		exit;	
	}
	/*加入/取消 黑名单*/
	public function black()
	{
		$rs = array('code' => 0, 'msg' => '', 'info' =>'操作成功');
		$uid=session("uid");
		$touid=I("touid");
		if($uid==$touid)
		{
			$rs['code']=0;
			$rs['msg']='无法将自己拉黑';
			echo  json_encode($rs);
			exit;
		}
		$users_black=M(users_black);
		$isexist=$users_black->where("uid=".$uid." and touid=".$touid)->find();
		if($isexist)
		{
			$black=$users_black->where("uid=".$uid." and touid=".$touid)->delete();
			if($black)
			{
				$rs['code']=0;
				$rs['msg']='已将该用户移除黑名单';
				echo  json_encode($rs);
				exit;
			}
			else
			{
				$rs['code']=1000;
				$rs['msg']='移除黑名单失败';
				echo  json_encode($rs);
				exit;
			}
		}
		else
		{
			M('users_attention')->where("uid=".$uid." and touid=".$touid)->delete();
			$black=$users_black->add(array("uid"=>$uid,"touid"=>$touid));
			if($black)
			{
				$rs['code']=0;
				$rs['msg']='已将该用户添加到黑名单';
				echo  json_encode($rs);
				exit;
			}
			else
			{
				$rs['code']=1000;
				$rs['msg']='添加黑名单失败';
				echo  json_encode($rs);
				exit;
			}
			
		}			 
	}
	/*举报*/
	public function report()
	{
		$rs = array('code' => 0, 'msg' => '', 'info' =>'操作成功');
		$uid=session("uid");
		$tlleuid=I("tlleuid");
		$token=I("token");
		$liveuid=I("liveuid");
		$content=I("content");
		$users=M("users");
		if($uid!=$tlleuid)
		{
			$rs['code']=1000;
			$rs['msg']='未知信息错误';
			echo  json_encode($rs);
			exit;
		}
		$checkToken=checkToken($uid,$token);
		if($checkToken==700)
		{
            session('uid',null);		
            session('token',null);
            session('user',null);
            session('user_nicename',null);
            session('avatar',null);
            cookie('uid',null);
            cookie('token',null);
			$rs['code']=$checkToken;
			$rs['msg']='登录信息过期，请重新登录';
			echo  json_encode($rs);
			exit;
		}
		if($content=="")
		{
			$rs['code']=1001;
			$rs['msg']='举报内容不能为空';
			echo  json_encode($rs);
			exit;
		}
		$data=array(
			"uid"=>$uid,
			"touid"=>$liveuid,
			'content'=>$content,
			'addtime'=>time() 
		);
		$users_report=M("users_report")->add($data);	
		if($users_report)
		{
			$rs['code']=0;
			$rs['msg']='举报成功';
			echo  json_encode($rs);
			exit;
		}
		else
		{
			$rs['code']=1003;
			$rs['msg']='举报失败';
			echo  json_encode($rs);
			exit;
		}
		 
	}

	/*收费房间扣费*/
	public function roomCharge()
	{
		$rs = array('code' => 0, 'msg' => '', 'info' => array());
		$liveuid=I("liveuid");
		$stream=I("stream");
		$uid=session("uid");
		$tenantId=getTenantId();
		$token=session("token");
        /**
         * 多租户处理
         */
		$islive=M("users_live")->field("islive,type,type_val,starttime")->where("uid='{$liveuid}' and stream='{$stream}' and (tenant_id='{$tenantId}' or isshare='1' )")->find();
		if(!$islive || $islive['islive']==0){
			$rs['code'] = 1005;
			$rs['msg'] = '直播已结束';
			echo json_encode($rs);
			exit;
		}
		if($islive['type']==0 || $islive['type']==1 ){
			$rs['code'] = 1006;
			$rs['msg'] = '该房间非扣费房间';
			echo json_encode($rs);
			exit;
		}
		$userinfo=M("users")->field("token,expiretime,coin")->where("id='{$uid}'")->find();
		if($userinfo['token']!=$token || $userinfo['expiretime']<time()){
            session('uid',null);		
            session('token',null);
            session('user',null);
            session('user_nicename',null);
            session('avatar',null);
            cookie('uid',null);
            cookie('token',null);
			$rs['code'] = 700;
			$rs['msg'] = '您的登陆状态失效，请重新登陆！';
			echo json_encode($rs);
			exit;			
		}
		$total=$islive['type_val'];
		if($total<=0){
			$rs['code'] = 1007;
			$rs['msg'] = '房间费用有误';
			echo json_encode($rs);
			exit;
		}
		$action='roomcharge';
		if($islive['type']==3){
			$action='timecharge';
		}
		$giftid=0;
		$giftcount=0;
		$showid=$islive['starttime'];
		$addtime=time();
		/* 更新用户余额 消费 */
		$ifok=M()->execute("update __PREFIX__users set coin=coin-{$total},consumption=consumption+{$total} where id='{$uid}' and coin>={$total}");
		if(!$ifok){
            $rs['code'] = 1008;
			$rs['msg'] = '余额不足';
			echo json_encode($rs);
			exit;
        }
		/* 分销 */	
		setAgentProfit($uid,$total);
		/* 分销 */		
		$configpri=getConfigPri();
	
		/* 家族分成之后的金额 */
		$anthor_total=setFamilyDivide($touid,$total);
		
		/* 更新直播 映票 累计映票 */
		M()->execute("update __PREFIX__users set votes=votes+{$anthor_total},votestotal=votestotal+{$total} where id='{$liveuid}'");
        $insert_votes=[
            'type'=>'income',
            'action'=>$action,
            'uid'=>$liveuid,
            'votes'=>$anthor_total,
            'addtime'=>time(),
        ];
        M('users_voterecord')->add($insert_votes);
		/* 更新直播 映票 累计映票 */
        $this->addCoinrecord(array("type"=>'expend',"action"=>$action,"uid"=>$uid,"touid"=>$liveuid,"giftid"=>$giftid,"giftcount"=>$giftcount,"totalcoin"=>$total,"showid"=>$showid,"addtime"=>$addtime ));
		$userinfo2=M("users")->field('coin')->where("id='{$uid}'")->find();	
		$rs['coin']=$userinfo2['coin'];
		echo json_encode($rs);
	}


}


