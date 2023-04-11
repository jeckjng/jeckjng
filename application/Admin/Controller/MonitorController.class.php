<?php

/**
 * 直播记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Admin\Controller\LivepushpullController;

class MonitorController extends AdminbaseController {
    function index(){
        $param = I('param.');
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 10;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map=array();

        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']!=''){
                $map['tenant_id'] = $param['tenant_id'];
            }else{
                $param['tenant_id'] = '';
            }
        }else{
            $tenant_id = getTenantIds();
            $param['tenant_id'] = $tenant_id;
            $map['tenant_id'] = $tenant_id;
        }

        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid'] = $param['uid'];
        }

        $map['islive'] = 1;
        $map['isvideo'] = 0;
        if(getRoleId() != 1){
            $map['tenant_id'] = getTenantIds();
        }

        $live=M("users_live");
    	$count=$live->where($map)->count();
    	$page = $this->page($count, $page_size);
    	$lists = $live
        ->where($map)
    	->order("starttime DESC")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();


        foreach($lists as $k=>$v){
            $userinfo=getUserInfo($v['uid']);
            $lists[$k]['userinfo']=$userinfo;
            if(!$v['thumb']){
                $lists[$k]['thumb']=get_upload_path($userinfo['avatar']);
            }

            $times = time()-$v['starttime'];
            $hour = floor($times/3600);
            $minute = floor(($times-3600 * $hour)/60);
            $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
            $result = $hour.':'.$minute.':'.$second;
            $lists[$k]['live_time']=$result;
            $config = getConfigPri($v['tenant_id']);
            $lists[$k]['chatserver'] = $config['chatserver'];
            $lists[$k]['socket_type'] = $config['socket_type'];
            $lists[$k]['stop_time']  = time2string($v['stop_time']);

        }

    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->assign('tenant_list',getTenantList());
    	$this->display();
    }
	public function full()
	{
		$uid=$_GET['uid'];
		$live=M("users_live")->where("uid='{$uid}' and islive='1'")->find();
        $config=getConfigPri();
		if($live['title']=="")
		{
			$live['title']="直播监控后台";
		}
        if(explode('://',$live['flvpull'])[0] != get_protocal()){
            $live['flvpull'] = get_protocal().'://'.explode('://',$live['flvpull'])[1];
        }
		$this->assign('config', $config);
		$this->assign('live', $live);
		$this->display();
	}

	public function livevideo(){
        $param = I('param.');
        $info=M("users_live")->where(["uid"=>$param['uid']])->find();

        $Livepushpull = new LivepushpullController();
        $PushpullInfo = $Livepushpull->getPushpullInfoWithId($info['pushpull_id']);
        $info['flvpull'] = $Livepushpull->PrivateKeyA(get_protocal(),$info['stream'].'.flv',0,$PushpullInfo);

        $this->assign('info', $info);
        $this->assign('key', $param['key']);
        $this->display();
    }

	/*
	 * 关闭房间
	 * */
    public function stopRoom(){
        $param = I('post.');
        if(IS_POST && isset($param['golang_event'])) {
            $param = I('post.');
            $live_info = getUserLiveInfo($param['uid']);
            $config = getConfigPri($live_info['tenant_id']);
            if ($config['go_admin_url']) {
                $url = $config['go_admin_url'] . '/admin/v1/live_room/broadcast_system_event';
                $res = http_post($url, ['EventType' => 'Closelive', 'Message' => json_encode(['Uid' => $param['uid']])]);
                $del_room_url = $config['go_admin_url'].'/admin/v1/live_room/delete_room';
                $del_room_res = http_post($del_room_url,['Uid'=>$param['uid'],'TenantId'=>$live_info['tenant_id']]);
            }
            $this->success($res);
        }

        $uid=I('post.uid');
        if ($uid) {
            $stream = M('users_live')->where(['uid'=>$uid])->getField('stream');
            if(!$stream){
                $this->success(['msg'=>'操作成功！','stopres'=>'前端已经处理关播事件']);
            }
            $u_info= M("users")->field("game_tenant_id,token")->where(['id'=>$uid])->find();
            $stopRoomUrl = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/api/public/?service=Live.StopRoom&game_tenant_id='.$u_info['game_tenant_id'].'&uid='.$uid.'&token='.$u_info['token'].'&stream='.$stream.'&acttype=amdin_stop';
            $stopres = file_get_contents($stopRoomUrl);
            $stopres = is_json($stopres) ? json_decode($stopres,true) : $stopres;
            if(!$stopres || ($stopres['data']['code'] != '0' && $stopres['data']['code'] != 700)){
                $this->error(['msg'=>'操作失败！','stopres1'=>$stopres]);
            }
            if($stopres['data']['code'] == 700){
                if($u_info['expiretime']<time()){
                    M("users")->where(['id'=>$uid])->save(['expiretime'=>(time()+60*60*24*300)]);
                }
                delcache("token_".$uid);
                $stopres = file_get_contents($stopRoomUrl);
                $stopres = is_json($stopres) ? json_decode($stopres,true) : $stopres;
            }
            if(!$stopres || $stopres['data']['code'] != '0'){
                $this->error(['msg'=>'操作失败！','stopres'=>$stopres, 'stopRoomUrl'=>$stopRoomUrl]);
            }
            setAdminLog('关闭房间成功【'.$uid.'】');
            $this->success(['msg'=>'操作成功！','stopres'=>$stopres]);
        } else {
            $this->error(['msg'=>'数据传入失败！']);
        }
    }

    /*
     * 弹窗警告
     * */
    public function liveuid_notice(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['uid']) || !$param['uid']){
                $this->error('缺少主播ID参数');
            }
            if(!isset($param['content']) || !$param['content']){
                $this->error('请输入警告内容');
            }
            if(!isset($param['type']) || !$param['type']){
                $this->error('缺少参数');
            }
            if($param['type'] == 'log'){
                setAdminLog('uid: '.$param['uid'].' , 内容：【'.$param['content'].'】',2);
                $this->success('操作成功');
            }
            if($param['type'] == 'send'){
                $live_info = getUserLiveInfo($param['uid']);
                $config = getConfigPri($live_info['tenant_id']);
                $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
                $res = http_post($url,['EventType'=>'PopWarnMessage', 'Message'=>json_encode(['Uid'=>$param['uid'], 'Content'=>html_entity_decode($param['content'])]) ]);
                setAdminLog('uid: '.$param['uid'].' , 内容:【'.$param['content'].'】',2);
                $this->success('操作成功');
            }
        }
        $info = M("users_live")->where(["uid"=>intval(I('uid'))])->find();
        $config = getConfigPri($info['tenant_id']);

        $this->assign('uid',I('uid'));
        $this->assign('config', $config);
        $this->display();
    }

}
