<?php

/**
 * 短视频
 */
namespace Admin\Controller;
use Admin\Model\UsersModel;
use Admin\Model\VideoModel;
use Common\Controller\AdminbaseController;
use Composer\Package\Loader\ValidatingArrayLoader;
use QCloud\Cos\Api;
//use QCloud\Cos\Auth;
use PHPExcel\PHPExcel\APC;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Driver\FFProbeDriver;

use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
use Think\Db;
use Admin\Cache\VideoCache;
use Common\Controller\CustRedis;
use Admin\Cache\VideoClassifyCache;

class ShotvideoController extends AdminbaseController {

    private $origin_list = array(
        '1' => '用户上传',
        '2' => '后台上传',
        '3' => '后台手动添加链接',
    );

    private $is_downloadable_list = array(
        '0' => array(
            'name' => '未完成',
            'color' => 'red',
        ),
        '1' => array(
            'name' => '已完成',
            'color' => 'green',
        ),
        '2' => array(
            'name' => '上传失败',
            'color' => 'red',
        ),
        '3' => array(
            'name' => '文件不存在',
            'color' => 'red',
        ),
        '4' => array(
            'name' => '解析失败',
            'color' => 'red',
        ),
    );

    private $status_list = array(
        '-1' => array(
            'name' => '上传中',
            'color' => '#f00',
        ),
        '1' => array(
            'name' => '待审核',
            'color' => '#F60',
        ),
        '2' => array(
            'name' => '审核通过',
            'color' => '#090',
        ),
        '3' => array(
            'name' => '审核未通过',
            'color' => '#999',
        ),
        '4' => array(
            'name' => '已删除',
            'color' => '#999',
        ),
    );

    protected $users_model,$role_model;

    function _initialize() {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->role_model = D("Common/VideoLabel");
    }

    /*待审核视频列表*/
    public function index(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());

        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map[] = ['tenant_id'=>$tenant_id];
        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : '';
        if($_REQUEST['start_time']!=''){
            $map['create_date']=array("gt",$_REQUEST['start_time']);
        }
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['create_time'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = '';
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['create_time'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = '';
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['create_time'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $param['start_time'] = '';
            $param['end_time'] = '';
        }
        if(isset($param['status']) && $param['status'] !=''){
            $map['status']=$param['status'];
        }
        if(isset($param['uid']) && $param['uid']!=''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login']!=''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['orderno']) && $param['orderno']!=''){
            $map['orderno'] = $param['orderno'];
        }
        if(isset($param['trade_no']) && $param['trade_no']!=''){
            $map['trade_no'] = $param['trade_no'];
        }
        if(isset($param['origin']) && $param['origin']!=''){
            $map['origin']=array("eq",$param['origin']);
        }
        if(isset($param['is_downloadable']) && $_REQUEST['is_downloadable'] !='-1'){
            $map['is_downloadable'] = $param['is_downloadable'];
        }
        if(isset($param['status']) && $_REQUEST['status'] !=''){
            $map['status'] = $param['status'];
        }else{
            $map['status'] = array('in',[1,2,3]);
        }
        if(isset($param['id']) && $param['id']!='') {
            $map['id'] = $param['id'];
        }
        if(isset($param['user_id']) && $param['user_id']!=''){
            $map['uid']= $param['user_id'];
        }
        if(isset($param['top']) && $_REQUEST['top'] !='-1'){
            $map['top'] = $param['top'];
        }
        if(isset($param['is_advertise']) && $_REQUEST['is_advertise'] !='-1'){
            $map['is_advertise'] = $param['is_advertise'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
         /*   $map['user_type'] = 2;
            $param['user_type'] = 2;*/
            $map['user_type'] = array('in',[2,3]);
            $param['user_type'] = -1;
        }

        $where = '1';
        if(isset($param['classify']) && $param['classify']!=''){
            $search_classify = $param['classify'];
            $_GET['classify']=$param['classify'];
            $where="FIND_IN_SET('".$search_classify."',classify)";
        }

        $playback_address_info = M('playback_address')
            ->where( array('is_enable'=> 1,'type'=>1,'tenant_id'=> getTenantIds()))
            ->find();
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $model = M("video");
        $count = $model->where($where)->where($map)->count();
        $page = $this->page($count);
        $orderstr="id DESC";



        
        $lists = $model
            ->where($where)
            ->where($map)
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
           // var_dump($map);
        //var_dump($model->getLastSql()); exit;
        $origin_list = $this->origin_list;
        $is_downloadable_list = $this->is_downloadable_list;
        $status_list = $this->status_list;
        foreach($lists as $k=>$v){
            $userinfo = getUserInfo($v['uid']);
            if($v['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$v['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $lists[$k]['origin_name'] = $origin_list[$v['origin']];
            $lists[$k]['is_downloadable_name'] = '<span style="color: '.$is_downloadable_list[$v['is_downloadable']]['color'].';">'.$is_downloadable_list[$v['is_downloadable']]['name'].'</span>';
            $lists[$k]['status_name'] = '<span style="color: '.$status_list[$v['status']]['color'].';">'.$status_list[$v['status']]['name'].'</span>';
            if ($v['origin']!=3){
                if($playback_address_info['name'] == 'minio' && strrpos($v['thumb'], '/liveprod-store-1101') === false){ // 是 minio, 同时不存在 /liveprod-store-1101
                    $lists[$k]['thumb'] = $v['thumb'] ? $playback_address_info['url'].'/liveprod-store-1101'.$v['thumb'] : $v['thumb'];
                }else{
                    $lists[$k]['thumb'] = $v['thumb'] ? $playback_address_info['url'].$v['thumb'] : $v['thumb'];
                }
            }
            if ($v[$playback_address_info['viode_table_field']]){
                $lists[$k]['href'] = geturlType() . $_SERVER['HTTP_HOST'] . $v[$playback_address_info['viode_table_field']];
            }
            $lists[$k]['userinfo']=$userinfo;
            $lists[$k]['user_nicename']=$userinfo['user_nicename'];
            $hasurgemoney=($v['big_urgenums']-$v['urge_nums'])*$v['urge_money'];
            $lists[$k]['hasurgemoney']=$hasurgemoney;
            $lists[$k]['create_time_date'] = $v['create_time'] ? date('Y-m-d H:i:s', $v['create_time']) : $v['create_date'];
            $lists[$k]['update_time_date'] = $v['update_time'] ? date('Y-m-d H:i:s', $v['update_time']) : '-';
        }
        $classify = VideoClassifyCache::getInstance()->getShortVideoClassifyList($tenant_id);

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('classify', $classify);
        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign('status_list', $status_list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('is_downloadable_list',$is_downloadable_list);
        $this->assign('user_type_list',user_type_list());
        $this->assign('param',$param);
        $this->assign('tenant_id',$tenant_id);
        $this->assign("p",$p);
        $this->display();
    }

    /*
     * 置顶更新
     * */
    public function update_top(){
        if(!IS_AJAX){
            $this->error('请求方式错误');
        }
        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            $this->error('参数错误'.json_encode($param));
        }
        if(!isset($param['top'])){
            $this->error('参数错误'.json_encode($param));
        }
        $info = M('video')->where(['id'=>intval($param['id'])])->find();
        try {
            $data = array(
                'top' => intval($param['top']),
                'update_time' => time(),
                'operated_by' => get_current_admin_user_login(),
            );
            M('video')->where(['id'=>intval($param['id'])])->save($data);
        }catch(\Exception $e){
            setAdminLog('【置顶更新-失败】'.$e->getMessage());
            $this->error('操作失败');
        }
        setAdminLog('【置顶更新-成功】'.json_encode($param));

        if($info['origin'] == 1){
            VideoCache::getInstance()->setPrivateListIdCache($info['tenant_id'], $info['id']);
        }
        if(in_array($info['origin'], [2,3])) {
            VideoCache::getInstance()->setPublicListIdCache($info['tenant_id'], $info['id']);
        }
        VideoCache::getInstance()->setTopListIdCache($info['tenant_id'], $info['id']);
        if($info['is_advertise'] == 1){
            VideoCache::getInstance()->setAdvertiseListIdCache($info['tenant_id'], $info['id']);
        }
        //VideoCache::getInstance()->NotifyVGoideoWasApproved($info['id']); // 视频审核通过、修改、删除，通知一下golang
        $this->success('操作成功');
    }

    /*
     * 设置广告更新
     * */
    public function update_is_advertise(){
        if(!IS_AJAX){
            $this->error('请求方式错误');
        }
        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            $this->error('参数错误'.json_encode($param));
        }
        if(!isset($param['is_advertise'])){
            $this->error('参数错误'.json_encode($param));
        }
        $action_name = $param['is_advertise']==1 ? '设为广告' : '取消广告';
        $info = M('video')->where(['id'=>intval($param['id'])])->find();
        try {
            $data = array(
                'is_advertise' => intval($param['is_advertise']),
                'update_time' => time(),
                'operated_by' => get_current_admin_user_login(),
            );
            M('video')->where(['id'=>intval($param['id'])])->save($data);
        }catch(\Exception $e){
            setAdminLog('【'.$action_name.'】-失败：'.$e->getMessage());
            $this->error('操作失败');
        }
        setAdminLog('【'.$action_name.'】-成功：'.json_encode($param));
        if($info['origin'] == 1){
            VideoCache::getInstance()->setPrivateListIdCache($info['tenant_id'], $info['id']);
        }
        if(in_array($info['origin'], [2,3])) {
            VideoCache::getInstance()->setPublicListIdCache($info['tenant_id'], $info['id']);
        }
        if($info['top'] == 1){
            VideoCache::getInstance()->setTopListIdCache($info['tenant_id'], $info['id']);
        }
        VideoCache::getInstance()->setAdvertiseListIdCache($info['tenant_id'], $info['id']);
       // VideoCache::getInstance()->NotifyVGoideoWasApproved($info['id']); // 视频审核通过、修改、删除，通知一下golang
        $this->success('操作成功');
    }

    public function label(){
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("video_label");
        $count=$video_model->count();
        $page = $this->page($count, 20);
        $orderstr="sort asc ,id DESC";


        $lists = $video_model
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        //var_dump($lists);exit;
        foreach($lists as $k=>$v){
            $lists[$k]['addtime']=date('Y-m-d H:i:s',$v['addtime']);

        }
        // var_dump($lists);exit;
        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->display();
    }

    public function getinfo(){
        $res=array("code"=>0,"msg"=>"获取成功","info"=>array());
        $id=empty(I("id"))?'0':I("id");
        $user_login=empty(I("user_login"))?'1':I("user_login");/*
        var_dump($id);var_dump($user_login);
        var_dump(!$id && !$user_login);*/
        if(!$id && !$user_login){
            $res['code']=1001;
            $res['msg']='请输入查询条件';
            echo json_encode($res);
            exit;
        }
        //获取视频信息
        $userinfo=M("users")->where("id={$id} or user_login = {$user_login}")->find();

        if($userinfo){
            $res['info'] =  $userinfo;
        }else{
            $res['code']=1002;
            $res['msg']='不存在该会员';
            echo json_encode($res);
            exit;
        }
        echo json_encode($res);
        exit;
    }

    public function  addactive(){
        $id = intval($_GET['id']);
        $active_menber=M("video_active_member")->where("uid={$id}")->find();
        if($active_menber){
            $this->error('该会员已经是活跃会员');
        }
        $userinfo=M("users")->where("id={$id}")->find();
        $data['uid'] = $id;
        $data['create_time'] = $userinfo['create_time'];
        $data['user_nicename'] = $userinfo['user_nicename'];
        $data['user_login'] = $userinfo['user_login'];

        $video = M("video_active_member");
        $video->create();
        $result = $video->add($data);
        $this->success('添加会员成功');
        if($result){
            $this->display('/Shotvideo/active');
        }
        //var_dump($result);exit;

    }

    /*活跃用户列表*/
    public function active(){

        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("video_active_member");
        $count=$video_model->count();
        $page = $this->page($count, 20);
        $orderstr="id DESC";


        $lists = $video_model

            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->display();
    }

    public function Sec2Time($time){
        if(is_numeric($time)){
            $value = array(
                "years" => 0, "days" => 0, "hours" => 0,
                "minutes" => 0, "seconds" => 0,
            );
            if($time >= 31556926){
                $value["years"] = floor($time/31556926);
                $time = ($time%31556926);
            }
            if($time >= 86400){
                $value["days"] = floor($time/86400);
                $time = ($time%86400);
            }
            if($time >= 3600){
                $value["hours"] = floor($time/3600);
                $time = ($time%3600);
            }
            if($time >= 60){
                $value["minutes"] = floor($time/60);
                $time = ($time%60);
            }
            $value["seconds"] = floor($time);
            //return (array) $value;
            $t= $value["hours"] ."小时". $value["minutes"] ."分".$value["seconds"]."秒";
            Return $t;

        }else{
            return (bool) FALSE;
        }
    }

    /*未通过视频列表*/
    public function nopassindex(){

        if($_REQUEST['ordertype']!=''){
            $ordertype=$_REQUEST['ordertype'];
            $_GET['ordertype']=$_REQUEST['ordertype'];
        }
        $map['isdel']=0;
        $map['status']=2;
        $map['is_ad']=0;

        if($_REQUEST['keyword']!=''){
            $map['uid|id']=array("eq",$_REQUEST['keyword']);
            $_GET['keyword']=$_REQUEST['keyword'];
        }
        if($_REQUEST['keyword1']!=''){
            $map['title']=array("like","%".$_REQUEST['keyword1']."%");
            $_GET['keyword1']=$_REQUEST['keyword1'];
        }
        //用户名称
        if($_REQUEST['keyword2']!=''){
            /* $map['title']=array("like","%".$_REQUEST['keyword2']."%");   */
            $_GET['keyword2']=$_REQUEST['keyword2'];
            $username=$_REQUEST['keyword2'];
            $userlist =M("users")->field("id")->where("user_nicename like '%".$username."%'")->select();
            $strids="";
            foreach($userlist as $ku=>$vu){
                if($strids==""){
                    $strids=$vu['id'];
                }else{
                    $strids.=",".$vu['id'];
                }
            }
            $map['uid']=array("in",$strids);
        }

        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("users_video");
        $count=$video_model->where($map)->count();
        $page = $this->page($count, 20);
        $orderstr="";
        if($ordertype==1){//评论数排序
            $orderstr="comments DESC";
        }else if($ordertype==2){//票房数量排序（点赞）
            $orderstr="likes DESC";
        }else if($ordertype==3){//分享数量排序
            $orderstr="shares DESC";
        }else{
            $orderstr="addtime DESC";
        }

        $lists = $video_model
            ->where($map)
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach($lists as $k=>$v){
            if($v['uid']==0){
                $userinfo=array(
                    'user_nicename'=>'系统管理员'
                );
            }else{
                $userinfo=getUserInfo($v['uid']);
                if(!$userinfo){
                    $userinfo=array(
                        'user_nicename'=>'已删除'
                    );
                }

            }


            $lists[$k]['userinfo']=$userinfo;

            $hasurgemoney=($v['big_urgenums']-$v['urge_nums'])*$v['urge_money'];
            $lists[$k]['hasurgemoney']=$hasurgemoney;
        }
        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->display();
    }

    /*审核通过视频列表*/
    public function passindex(){
        if($_REQUEST['ordertype']!=''){
            $ordertype=$_REQUEST['ordertype'];
            $_GET['ordertype']=$_REQUEST['ordertype'];
        }
        $map['isdel']=0;
        $map['status']=1;
        $map['is_ad']=0;

        if($_REQUEST['keyword']!=''){
            $map['uid|id']=array("eq",$_REQUEST['keyword']);
            $_GET['keyword']=$_REQUEST['keyword'];
        }
        if($_REQUEST['keyword1']!=''){
            $map['title']=array("like","%".$_REQUEST['keyword1']."%");
            $_GET['keyword1']=$_REQUEST['keyword1'];
        }
        //用户名称
        if($_REQUEST['keyword2']!=''){
            /* $map['title']=array("like","%".$_REQUEST['keyword2']."%");   */
            $_GET['keyword2']=$_REQUEST['keyword2'];
            $username=$_REQUEST['keyword2'];
            $userlist =M("users")->field("id")->where("user_nicename like '%".$username."%'")->select();
            $strids="";
            foreach($userlist as $ku=>$vu){
                if($strids==""){
                    $strids=$vu['id'];
                }else{
                    $strids.=",".$vu['id'];
                }
            }
            $map['uid']=array("in",$strids);
        }

        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("users_video");
        $count=$video_model->where($map)->count();
        $page = $this->page($count, 20);
        $orderstr="";
        if($ordertype==1){//评论数排序
            $orderstr="comments DESC";
        }else if($ordertype==2){//票房数量排序（点赞）
            $orderstr="likes DESC";
        }else if($ordertype==3){//分享数量排序
            $orderstr="shares DESC";
        }else{
            $orderstr="addtime DESC";
        }

        $lists = $video_model
            ->where($map)
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach($lists as $k=>$v){
            if($v['uid']==0){
                $userinfo=array(
                    'user_nicename'=>'系统管理员'
                );
            }else{
                $userinfo=getUserInfo($v['uid']);
                if(!$userinfo){
                    $userinfo=array(
                        'user_nicename'=>'已删除'
                    );
                }

            }


            $lists[$k]['userinfo']=$userinfo;

            $hasurgemoney=($v['big_urgenums']-$v['urge_nums'])*$v['urge_money'];
            $lists[$k]['hasurgemoney']=$hasurgemoney;
        }
        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->display();
    }

    public function del(){
        $res=array("code"=>0,"msg"=>"删除成功","info"=>array());
        $id=I("id");
        $reason=I("reason");
        if(!$id){

            $res['code']=1001;
            $res['msg']='视频信息加载失败';
            echo json_encode($res);
            exit;
        }

        //获取视频信息
        $videoInfo=M("users_video")->where("id={$id}")->find();

        $result=M("users_video")->where("id={$id}")->save();

        //$result=M("users_video")->where("id={$id}")->setField("isdel","1");

        if($result!==false){

            M("users_video_black")->where("videoid={$id}")->delete();	 //删除视频拉黑
            M("users_video_comments")->where("videoid={$id}")->delete();	 //删除视频评论
            M("users_video_like")->where("videoid={$id}")->delete();	 //删除视频喜欢
            M("users_video_report")->where("videoid={$id}")->delete();	 //删除视频举报

            /*//获取该视频的评论id
            $commentlists=M("users_video_comments")->field("id")->where("videoid={$id}")->select();
            $commentids="";
            foreach($commentlists as $k=>$v){
                if($commentids==""){
                    $commentids=$v['id'];
                }else{
                    $commentids.=",".$v['id'];
                }
            }

            //删除视频评论喜欢
            $map['commentid']=array("in",$commentids);*/


            M("users_video_comments_like")->where("videoid={$id}")->delete(); //删除视频评论喜欢



            if($videoInfo['isdel']==0){ //视频上架情况下被删除发送通知
                //极光IM
                $id=$_SESSION['ADMIN_ID'];
                $user=M("Users")->where("id='{$id}'")->find();

                //向系统通知表中写入数据
                /* $sysInfo=array(
                    'title'=>'视频删除提醒',
                    'addtime'=>time(),
                    'admin'=>$user['user_login'],
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                    'uid'=>$videoInfo['uid']

                );

                if($videoInfo['title']!=''){
                    $videoTitle='上传的《'.$videoInfo['title'].'》';
                }else{
                    $videoTitle='上传的';
                }

                $baseMsg='您于'.date("Y-m-d H:i:s",$videoInfo['addtime']).$videoTitle.'视频被管理员于'.date("Y-m-d H:i:s",time()).'删除';

                if(!$reason){
                    $sysInfo['content']=$baseMsg;
                }else{
                    $sysInfo['content']=$baseMsg.',删除原因为：'.$reason;
                }

                $result1=M("system_push")->add($sysInfo);

                if($result1!==false){

                    $text="视频删除提醒";
                    $uid=$videoInfo['uid'];
                    jMessageIM($text,$uid);

                } */
            }




            $res['msg']='视频删除成功';
            echo json_encode($res);
            exit;
        }else{
            $res['code']=1002;
            $res['msg']='视频删除失败';
            echo json_encode($res);
            exit;
        }

    }

    //排序
    public function listorders() {
        $ids = $_POST['sort'];
        foreach ($ids as $key => $r) {
            $data['sort'] = $r;
            M("video")->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    public function add(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $labels=$this->role_model->where("is_delete=1")->order("id desc")->select();
        $performer=M('performer')->field('id,name')->select();;
        $classifylist = VideoClassifyCache::getInstance()->getShortVideoClassifyList($tenant_id);;

        $config = getConfigPub($tenant_id);
        $cut_video_url_array = explode("\n",$config['url_of_push_to_java_cut_video']);
        $cut_video_url = $cut_video_url_array[0];
        $url_info = parse_url($cut_video_url);
        $url_is_ip = filter_var($url_info['host'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false ? 0 : 1;

        $this->assign("url_of_push_to_java_cut_video", $cut_video_url);
        $this->assign("url_is_ip", $url_is_ip);
        $this->assign("labels", $labels);
        $this->assign("performer" ,$performer);
        $this->assign("classifylist", $classifylist);
        $this->display();
    }

    public function add_post(){
        set_time_limit(0);
        require 'vendor/autoload.php';
        require 'qiniusdk/autoload.php';
        $ffmpeg = FFMpeg::create(array(
            'ffmpeg.binaries' => '/usr/local/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/local/bin/ffprobe',
            //'ffmpeg.binaries' => 'C:\phpstudy_pro\Extensions\php\php7.3.4nts\bin\ffmpeg.exe',
            //'ffprobe.binaries' => 'C:\phpstudy_pro\Extensions\php\php7.3.4nts\bin\ffprobe.exe',
            'timeout' => 0,
            'ffmpeg.threads' => 12
        ));

        if(IS_POST){


            $video=M("video");
            $video->create();
            $title=$_POST['title'];
            if($title==""){
                $this->error("请填写视频标题");
            }
            //获取后台上传配置
            $configpri=getConfigPri();


            $res = array();
            if($configpri['cloudtype']==1){  //七牛云存储
                //获取上传视频的核心功能（视频长度，以及视频对象，等待切片）
                $ffmpegvideo = $ffmpeg->open($_FILES['file']['tmp_name']);
                $videoinfo =  $ffmpegvideo->getStreams()->videos()->first()->all();
                $every_time = 60;
                if ($videoinfo['duration']< 60){
                    $every_time =  $videoinfo['duration'];
                }
                $duration = round($videoinfo['duration']/$every_time);

                //视频切成图片，只要一张
                $frame = $ffmpegvideo->frame(TimeCode::fromSeconds(3));//提取第几秒的图像
                $img_url = date('YmdHis',time()).random(5,10000000);
                $frame->save('data/upload/'.$img_url.'.jpg');
                $img_file = $_FILES;

                $img_file['file']['name'] =  $img_url . '.jpg';
                $img_file['file']['type'] = 'image/jpeg';
                //切割文件的本地存放路径
                $img_file['file']['tmp_name'] = $_SERVER['DOCUMENT_ROOT'] . '/data/upload/'.$img_url.'.jpg';



                // 控制台获取密钥：https://portal.qiniu.com/user/key
                $accessKey = 'TvsFdVv-pESRgB0YKJZ5j_wl5fYB79wSlxojkV-4';
                $secretKey = 'TRkgHvGDoVI_g8xS9fqcS0L6v5nq6CNCuprq1PRY';
                $bucket = 'liveuatmb';
                $domain = 'liveuatstore.51mmtuan.com';

                // 构建鉴权对象
                $auth = new Auth($accessKey, $secretKey);
                // 生成上传 Token
                $token = $auth->uploadToken($bucket);
                // 要上传文件的本地路径
                $filePath = $img_file['file']['tmp_name'];
                // 上传到存储后保存的文件名
                $key = $img_url.'.jpg';
                // 初始化 UploadManager 对象并进行文件的上传。
                $uploadMgr = new UploadManager();
                // 调用 UploadManager 的 putFile 方法进行文件的上传。
                list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);

                if ($err !== null) {
                    $this->error('添加失败');
                } else {
                    $thumb = $domain.'/'.$key;
                }


                $video_href ='';
                //视频切片，根据视频长度
                for($i=0;$i<$duration;$i++) {
                    $new_auth = new Auth($accessKey, $secretKey);
                    // 生成上传 Token
                    $new_token = $new_auth->uploadToken($bucket);
                    $ts = $img_url.$i.'.ts';
                    $video_url = date('YmdHis',time()).random(3,10000000);
                    $clip = $ffmpegvideo->clip(TimeCode::fromSeconds($i * 60),TimeCode::fromSeconds(60));
                    $new_file= $clip->save(new X264('aac'), '/data/wwwroot/livedev/data/upload/succ' .$ts);
                    // $new_file['file']['tmp_name'][] = $_SERVER['DOCUMENT_ROOT'] . '/data/upload/succ' . $i . '.ts';
                    list($ret, $err) = $uploadMgr->putFile($new_token, $ts ,   '/data/wwwroot/livedev/data/upload/succ' . $ts);

                    if ($err !== null) {
                        $this->error('添加失败');
                    } else {
                        $video_href .= $domain.'/'.$ts;

                    }
                }
                $file_name = date('YmdHis',time()).random(5,10000000);
                //写入m3u8
                file_put_contents($_SERVER['DOCUMENT_ROOT']."/test/".$file_name.".m3u8",$video_href);
                $video_href = 'https://'.$_SERVER['SERVER_NAME'].'/test/'.$file_name.".m3u8";

            }

            if(1){
                $this->success('添加成功','Admin/Shotvideo/passindex',3);
            }else{
                $this->error('添加失败');
            }

        }
    }

    public function add_postbyj(){
        if(IS_POST){
            $video=M("video");
            $video->create();
            $title=$_POST['title'];
            if($title==""){
                $this->error("请填写视频标题");
            }
            $owner_uid=$_POST['owner_uid'];

            if($owner_uid==""||!is_numeric($owner_uid)){
                $this->error("请填写视频所有者id");
                return;
            }

            //判断用户是否存在
            $ownerInfo=M("users")->where("user_type in(2,5,6) and id={$owner_uid}")->find();
            if(!$ownerInfo){
                $this->error("视频所有者不存在");
                return;
            }
            $video->uid=$owner_uid;


            $label= $_POST['label'];
            $performer = $_POST['performer'];
            $abelinfo = '';
            if(!empty($label)){
                foreach ($label as $key=>$value){
                    if($key==0){
                        $abelinfo = $value;
                    }else{
                        $abelinfo .= ",".$value;
                    }
                }
            }
            if($_FILES['file']['type'] != 'video/mp4'){
                $this->error("上传文件只能为mp4文件");
                return;
            }
            $videoinfo = getCutvideo($ownerInfo['tenant_id']);
            //java端返回的m3u8文件内容
            $hrefcontent = base64_decode($videoinfo['data']['m3u8Str']);
            //文件名称
            $file_name = date('YmdHis',time()).random(5,10000000);
            //写入m3u8
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/test/".$file_name.".m3u8",$hrefcontent);
            $video_href = 'https://'.$_SERVER['SERVER_NAME'].'/test/'.$file_name.".m3u8";
            $arr['href'] = $video_href;
            $arr['create_date']= date('Y-m-d H:i:s',time());
            $arr['uid']=$owner_uid;
            $arr['title']= $title;
            $arr['thumb']= $videoinfo['data']['coverImgUrl'];
            $arr['label']= $abelinfo;
            $arr['origin']= '3';
            $arr['duration'] = $videoinfo['data']['playTime'];
            $arr['performer']= $performer;
            $arr['is_downloadable'] = 0;
            $arr['filestorekey'] = $videoinfo['data']['fileStoreKey'];
            $is_performer = 0;
            if ($performer){
                $is_performer = 1;
            }
            $arr['is_performer'] =$is_performer;
            $videoId = $video->add($arr);
            $result = true;

            if($result){
                $this->success('添加成功','Admin/Shotvideo/passindex',3);
            }else{
                $this->error('添加失败');
            }

        }
    }

    public function updatefile(){
        if (IS_POST) {
            $param = I('post.');
            $video = M("video");
            $video->create();
            $title = $_POST['title'];
            if ($title == "") {
                $this->error("请填写视频标题");
            }
            if(!$param['fileStoreKey']){
                $this->error("请上传视频");
            }
            $owner_uid = $_POST['owner_uid'];

            if ($owner_uid == "" || !is_numeric($owner_uid)) {
                $this->error("请填写视频所有者id");
                return;
            }
            if(mb_strlen($param['remark']) > 255){
                $this->error("备注长度不能大于255");
            }

            //判断用户是否存在
            $ownerInfo = M("users")->where("user_type in (2,3,4,5,6,7) and id={$owner_uid}")->find();
            if (!$ownerInfo) {
                $this->error("视频所有者不存在");
                return;
            }
            if (!$ownerInfo['user_type'] == 3) {
                $this->error("不能使用虚拟会员上传");
                return;
            }
            if (!$ownerInfo['user_type'] == 4) {
                $this->error("不能使用游客上传");
                return;
            }
            $video->uid = $owner_uid;

            $label = $_POST['label'];
            $performer = $_POST['performer'];
            $abelinfo = '';
            if (!empty($label)) {
                foreach ($label as $key => $value) {
                    if ($key == 0) {
                        $abelinfo = $value;
                    } else {
                        $abelinfo .= "," . $value;
                    }
                }
            }
            $classify  = $_POST['classify'];
            if (!empty($classify)) {
                $classify = implode(',',$classify);

            }

            $price = trim($_POST['price']);
            $try_watch_time = trim($_POST['try_watch_time']);
            $years = trim($_POST['years']);
            $region = trim($_POST['region']);
            $desc = trim($_POST['desc']);
            $watchtimes   =$_POST['watchtimes'];
            if ($_POST['fileStoreKey']){
                $arr['filestorekey'] = $_POST['fileStoreKey'];
                $arr['status'] = '1';//等待上传
                if (!empty($_POST['create_date'])){
                    $arr['create_date'] = $_POST['create_date'].':'.date('s');
                }else{
                    $arr['create_date']= date('Y-m-d H:i:s',time());
                }
                if ($_POST['likes']){
                    $arr['likes'] = $_POST['likes'];
                    $action = '默认设置点赞数量为'.$_POST['likes'];
                    setAdminLog($action);
                }
                if ($_POST['collection']){
                    $arr['collection'] = $_POST['collection'];
                    $action = '默认设置收藏数量为'.$_POST['collection'];
                    setAdminLog($action);
                }
                $arr['uid'] = $owner_uid;
                $arr['user_login'] = $ownerInfo['user_login'];
                $arr['user_type'] = intval($ownerInfo['user_type']);
                $arr['tenant_id'] = intval($ownerInfo['tenant_id']);
                $arr['create_time'] = time();
                $arr['operated_by'] = get_current_admin_user_login();
                $arr['title'] = $title;
                $arr['years'] = $years;
                $arr['region'] = $region;
                $arr['desc'] = $desc;
                $arr['label'] = $abelinfo;
                $arr['origin'] = '2';
                $arr['classify'] = $classify;
                $arr['price'] = $price;
                $arr['try_watch_time'] = $try_watch_time;
                $arr['remark'] = trim($param['remark']);
                $arr['shoptype'] = trim($param['shoptype']);
                $arr['shop_value'] = trim($param['shop_value']);
                $arr['shop_url'] = trim($param['shop_url']);
                $arr['watchtimes'] = $watchtimes;

                $arr['performer'] = $performer;
                $is_performer = 0;
                if ($performer) {
                    $is_performer = 1;
                }
                $arr['is_performer'] = $is_performer;
                $videoId = $video->add($arr);

                $video_id = $video->getLastInsID();
                if( $arr['shoptype'] == 1){
                    $tenantInfo=getTenantInfo($ownerInfo['tenant_id']);
                    $shopparms = array(
                        'id' => $arr['shop_value'],
                    );
                    $url = $tenantInfo['shop_url'].'/api.php?s=Goods/Detail';
                    $shopinfo = http_post($url,$shopparms);
                    if(isset($shopinfo['code']) && $shopinfo['code'] == 0){
                        $videobindshop = array(
                            'title'=>$shopinfo['data']['goods']['title'],
                            'images'=>$shopinfo['data']['goods']['images'],
                            'price'=>$shopinfo['data']['goods']['price'],
                            'original_price'=>$shopinfo['data']['goods']['original_price'],
                            'shop_url'=>$arr['shop_url'],
                        );
                        $redis = connectionRedis();
                        $redis->hSet("videobindshop",$video_id,json_encode($videobindshop));
                    }else{
                        $this->error('绑定商品ID失败');
                    }
                }
                if( $arr['shoptype'] == 2){
                    $tenantInfo=getTenantInfo($ownerInfo['tenant_id']);
                    $shopparms = array(
                        'id' => $arr['shop_value'],
                    );
                    $url = $tenantInfo['shop_url'].'/api.php?s=plugins/index&pluginsname=shop&pluginscontrol=index&pluginsaction=detail';
                    $shopinfo = http_post($url,$shopparms);
                    $shopgoodsparms = array(
                        'shop_id' => $arr['shop_value'],
                    );
                    $url = $tenantInfo['shop_url'].'/api.php?s=plugins/index&pluginsname=shop&pluginscontrol=search&pluginsaction=DataList';
                    $shopgoodsinfo = http_post($url,$shopgoodsparms);

                    if(isset($shopinfo['code']) && $shopinfo['code'] == 0){
                        $videobindshop = array(
                            'title'=>$shopinfo['data']['shop']['name'],
                            'images'=>$shopinfo['data']['shop']['logo'],
                            'shop_url'=>$arr['shop_url'],
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


                        $redis = connectionRedis();
                        $redis->hSet("videobindshop",$video_id,json_encode($videobindshop));
                    }else{
                        $this->error('绑定店铺ID失败');
                    }
                }
                if($arr['origin'] == 1){
                    VideoCache::getInstance()->setPrivateListIdCache($arr['tenant_id'], $video_id);
                }
                if(in_array($arr['origin'], [2,3])) {
                    VideoCache::getInstance()->setPublicListIdCache($arr['tenant_id'], $video_id);
                }
                $this->success('添加成功');
            }else{
                setAdminLog('【短视频上传】-失败-'.$ownerInfo['tenant_id'].'-'.json_encode($param));
                $this->error('上传失败');
            }
        }
    }

    public function edit(){
        $id=intval($_GET['id']);
        $from=I("from");
        if($id){
            $video=M("users_video")->where("id={$id}")->find();
            if($video['uid']==0){
                $userinfo=array(
                    'user_nicename'=>'系统管理员'
                );
            }else{
                $userinfo=getUserInfo($video['uid']);
                if(!$userinfo){
                    $userinfo=array(
                        'user_nicename'=>'已删除'
                    );
                }
            }

            $video['userinfo']=$userinfo;
            $this->assign('video', $video);
        }else{
            $this->error('数据传入失败！');
        }
        $this->assign("from",$from);
        $this->display();
    }

    public function edit_post(){
        if(IS_POST){

            $video=M("users_video");
            $video->create();

            $id=$_POST['id'];
            $title=$_POST['title'];
            $thumb=$_POST['thumb'];
            $type=$_POST['video_upload_type'];
            $url=$_POST['href'];
            $status=$_POST['status'];
            $isdel=$_POST['isdel'];
            $nopasstime=$_POST['nopasstime'];



            /*if($title==""){
                $this->error("请填写视频标题");
            }*/

            if($thumb==""){
                $this->error("请上传视频封面");
            }

            $video->thumb_s=$_POST['thumb'];

            if($type!=''){

                if($type==0){ //视频链接型式
                    if($url==''){
                        $this->error("请填写视频链接地址");
                    }

                    //判断链接地址的正确性
                    if(strpos($url,'http')!==false||strpos($url,'https')!==false){

                        $video_type=substr(strrchr($url, '.'), 1);

                        if(strtolower($video_type)!='mp4'){
                            $this->error("文件名后缀必须为mp4格式");
                        }

                        $video->href=$url;

                    }else{

                        $this->error("请填写正确的视频地址");

                    }


                }else if($type==1){ //文件上传型式

                    $savepath=date('Ymd').'/';

                    //获取后台上传配置
                    $configpri=getConfigPri();

                    //var_dump($configpri);
                    if($configpri['cloudtype']==1){  //七牛云存储


                        //上传处理类
                        $config=array(
                            'rootPath' => './'.C("UPLOADPATH"),
                            'savePath' => $savepath,
                            'maxSize' => 100*1048576, //100M
                            'saveName'   =>    array('uniqid',''),
                            'exts'       =>    array('mp4'),
                            'autoSub'    =>    false,
                        );

                        $config_qiniu = array(

                            'accessKey' => $configpri['qiniu_accesskey'], //这里填七牛AK
                            'secretKey' => $configpri['qiniu_secretkey'], //这里填七牛SK
                            'domain' => $configpri['qiniu_domain'],//这里是域名
                            'bucket' => $configpri['qiniu_bucket']//这里是七牛中的“空间”
                        );


                        $upload = new \Think\Upload($config,'Qiniu',$config_qiniu);


                        $info = $upload->upload();

                        if ($info) {
                            //上传成功
                            //写入附件数据库信息
                            $first=array_shift($info);
                            if(!empty($first['url'])){
                                $url=$first['url'];

                            }else{
                                $url=C("TMPL_PARSE_STRING.__UPLOAD__").$savepath.$first['savename'];

                            }

                            /*echo "1," . $url.",".'1,'.$first['name'];
                            exit;*/


                        } else {
                            //上传失败，返回错误
                            //exit("0," . $upload->getError());
                            $this->error('视频文件上传失败');
                        }



                    }else if($configpri['cloudtype']==2){ //腾讯云存储

                        /* 腾讯云 */
                        require(SITE_PATH.'api/public/txcloud/include.php');
                        //bucketname
                        $bucket = $configpri['txcloud_bucket'];

                        $src = $_FILES["file"]["tmp_name"];

                        //var_dump("src".$src);

                        //cosfolderpath
                        $folder = '/'.$configpri['txvideofolder'];
                        //cospath
                        $dst = $folder.'/'.$_FILES["file"]["name"];
                        //config your information
                        $config = array(
                            'app_id' => $configpri['txcloud_appid'],
                            'secret_id' => $configpri['txcloud_secret_id'],
                            'secret_key' => $configpri['txcloud_secret_key'],
                            'region' => $configpri['txcloud_region'],   // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
                            'timeout' => 60
                        );

                        $cosApi = new 	Api($config);

                        $ret = $cosApi->upload($bucket, $src, $dst);



                        if($ret['code']!=0){
                            //上传失败，返回错误
                            //exit("0," . $ret['message']);
                            $this->error('视频文件上传失败');
                        }

                        $url = $ret['data']['source_url'];


                    }


                }


                $video->href=$url;
            }else{

                //获取该视频的href
                $url=$video->where("id={$id}")->getField("href");

                $video->href=$url;
            }

            if($status==2){
                $video->nopass_time=time();
            }

            //审核通过给该视频添加曝光值（改为接口添加视频时直接添加曝光值）
            // if($status==1){
            // 	$video->show_val=100;
            // }

            $result=$video->save();

            if($result!==false){

                if($status==2||$isdel==1){  //如果该视频下架或视频状态改为不通过，需要将视频喜欢列表的状态更改
                    M("users_video_like")->where("videoid={$id}")->setField('status',0);
                }

                if($status==2&&$nopasstime==0){  //视频状态为审核不通过且为第一次审核为不通过，发送极光IM

                    $videoInfo=M("users_video")->where("id={$id}")->find();

                    $id=$_SESSION['ADMIN_ID'];
                    $user=M("Users")->where("id='{$id}'")->find();

                    //向系统通知表中写入数据
                    /* $sysInfo=array(
                        'title'=>'视频未审核通过提醒',
                        'addtime'=>time(),
                        'admin'=>$user['user_login'],
                        'ip'=>$_SERVER['REMOTE_ADDR'],
                        'uid'=>$videoInfo['uid']

                    );

                    $baseMsg='您于'.date("Y-m-d H:i:s",$videoInfo['addtime']).'上传的《'.$videoInfo['title'].'》视频被管理员于'.date("Y-m-d H:i:s",time()).'审核为不通过';;


                    $sysInfo['content']=$baseMsg;


                    $result1=M("system_push")->add($sysInfo);

                    if($result1!==false){

                        $text="视频未审核通过提醒";
                        $uid=$videoInfo['uid'];
                        jMessageIM($text,$uid);

                    } */

                }

                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
    }

    public function reportlist(){

        if($_REQUEST['status']!=''){
            $map['status']=$_REQUEST['status'];
            $_GET['status']=$_REQUEST['status'];
        }
        if($_REQUEST['start_time']!=''){
            $map['addtime']=array("gt",strtotime($_REQUEST['start_time']));
            $_GET['start_time']=$_REQUEST['start_time'];
        }

        if($_REQUEST['end_time']!=''){

            $map['addtime']=array("lt",strtotime($_REQUEST['end_time']));
            $_GET['end_time']=$_REQUEST['end_time'];
        }
        if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){

            $map['addtime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'])));
            $_GET['start_time']=$_REQUEST['start_time'];
            $_GET['end_time']=$_REQUEST['end_time'];
        }

        if($_REQUEST['keyword']!=''){
            $map['uid']=array("like","%".$_REQUEST['keyword']."%");
            $_GET['keyword']=$_REQUEST['keyword'];
        }

        $p=I("p");
        if(!$p){
            $p=1;
        }

        $Report=M("users_video_report");
        $Users=M("users");
        $count=$Report->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $Report
            ->where($map)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach($lists as $k=>$v){
            $userinfo=$Users->field("user_nicename")->where("id='".$v['uid']."'")->find();
            if(!$userinfo){
                $userinfo=array(
                    'user_nicename'=>'已删除'
                );
            }
            $lists[$k]['userinfo']= $userinfo;
            $touserinfo=$Users->field("user_nicename")->where("id='".$v['touid']."'")->find();
            if(!$touserinfo){
                $touserinfo=array(
                    'user_nicename'=>'已删除'
                );
            }
            $lists[$k]['touserinfo']= $touserinfo;
        }

        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->display();
    }

    public function setstatus(){
        $id=intval($_GET['id']);
        if($id){
            $data['status']=1;
            $data['uptime']=time();
            $result=M("users_video_report")->where("id='{$id}'")->save($data);
            if($result!==false){
                $this->success('标记成功');
            }else{
                $this->error('标记失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
    }

    //删除用户举报列表
    public function report_del(){
        $id=intval($_GET['id']);
        if($id){
            $result=M("users_video_report")->delete($id);
            if($result){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
    }

    //举报内容设置**************start******************

    //举报类型列表
    public function reportset(){
        $report=M("users_video_report_classify");
        $lists = $report
            ->order("orderno ASC")
            ->select();

        $this->assign('lists', $lists);
        $this->display();
    }

    //添加举报理由
    public function add_report(){

        $this->display();
    }

    public function add_reportpost(){

        if(IS_POST){
            $report=M("users_video_report_classify");

            $name=I("name");//举报类型名称
            if(!trim($name)){
                $this->error('举报类型名称不能为空');
            }
            $isexit=M("users_video_report_classify")->where("name='{$name}'")->find();
            if($isexit){
                $this->error('该举报类型名称已存在');
            }

            $report->create();
            $report->addtime=time();
            $result=$report->add();
            if($result){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
    }

    //编辑举报类型名称
    public function edit_report(){
        $id=intval($_GET['id']);
        if($id){
            $reportinfo=M("users_video_report_classify")->where("id={$id}")->find();

            $this->assign('reportinfo', $reportinfo);
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    public function edit_reportpost(){
        if(IS_POST){
            $report=M("users_video_report_classify");

            $id=I("id");
            $name=I("name");//举报类型名称
            if(!trim($name)){
                $this->error('举报类型名称不能为空');
            }

            $isexit=M("users_video_report_classify")->where("id!={$id} and name='{$name}'")->find();
            if($isexit){
                $this->error('该举报类型名称已存在');
            }

            $report->create();
            $result=$report->save();
            if($result!==false){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
    }

    //删除举报类型名称
    public function del_report(){
        $id=intval($_GET['id']);
        if($id){
            $result=M("users_video_report_classify")->where("id={$id}")->delete();
            if($result!==false){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    //举报内容排序
    public function listordersset() {

        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("users_video_report_classify")->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
//举报内容设置**************end******************
//
    //设置下架
    public function setXiajia(){
        $res=array("code"=>0,"msg"=>"下架成功","info"=>array());
        $id=I("id");
        $reason=I("reason");
        if(!$id){
            $res['code']=1001;
            $res['msg']="请确认视频信息";
            echo json_encode($res);
            exit;
        }

        //判断此视频是否存在
        $videoInfo=M("users_video")->where("id={$id}")->find();
        if(!$videoInfo){
            $res['code']=1001;
            $res['msg']="请确认视频信息";
            echo json_encode($res);
            exit;
        }

        //更新视频状态
        $data=array("isdel"=>1,"xiajia_reason"=>$reason);

        $result=M("users_video")->where("id={$id}")->save($data);

        if($result!==false){

            //将视频喜欢列表的状态更改
            M("users_video_like")->where("videoid={$id}")->setField('status',0);

            //更新此视频的举报信息
            $data1=array(
                'status'=>1,
                'uptime'=>time()
            );

            M("users_video_report")->where("videoid={$id}")->save($data1);

            $id=$_SESSION['ADMIN_ID'];
            $user=M("Users")->where("id='{$id}'")->find();

            //向系统通知表中写入数据
            /* $sysInfo=array(
                'title'=>'视频下架提醒',
                'addtime'=>time(),
                'admin'=>$user['user_login'],
                'ip'=>$_SERVER['REMOTE_ADDR'],
                'uid'=>$videoInfo['uid']

            );

            $baseMsg='您于'.date("Y-m-d H:i:s",$videoInfo['addtime']).'上传的《'.$videoInfo['title'].'》视频被管理员于'.date("Y-m-d H:i:s",time()).'下架';;

            if(!$reason){
                $sysInfo['content']=$baseMsg;
            }else{
                $sysInfo['content']=$baseMsg.',下架原因为：'.$reason;
            }

            $result1=M("system_push")->add($sysInfo);


            if($result1!==false){

                $text="视频下架提醒";
                $uid=$videoInfo['uid'];
                jMessageIM($text,$uid);

            } */




            echo json_encode($res);
            exit;
        }else{
            $res['code']=1002;
            $res['msg']="下架失败";
            echo json_encode($res);
            exit;
        }

    }

    /*下架视频列表*/
    public  function lowervideo(){

        if($_REQUEST['ordertype']!=''){
            $ordertype=$_REQUEST['ordertype'];
            $_GET['ordertype']=$_REQUEST['ordertype'];
        }
        $map['isdel']=1;
        $map['is_ad']=0;

        if($_REQUEST['keyword']!=''){
            $map['uid|id']=array("eq",$_REQUEST['keyword']);
            $_GET['keyword']=$_REQUEST['keyword'];
        }
        if($_REQUEST['keyword1']!=''){
            $map['title']=array("like","%".$_REQUEST['keyword1']."%");
            $_GET['keyword1']=$_REQUEST['keyword1'];
        }
        //用户名称
        if($_REQUEST['keyword2']!=''){
            /* $map['title']=array("like","%".$_REQUEST['keyword2']."%");   */
            $_GET['keyword2']=$_REQUEST['keyword2'];
            $username=$_REQUEST['keyword2'];
            $userlist =M("users")->field("id")->where("user_nicename like '%".$username."%'")->select();
            $strids="";
            foreach($userlist as $ku=>$vu){
                if($strids==""){
                    $strids=$vu['id'];
                }else{
                    $strids.=",".$vu['id'];
                }
            }
            $map['uid']=array("in",$strids);
        }

        $p=I("p");
        if(!$p){
            $p=1;
        }


        $video_model=M("users_video");
        $count=$video_model->where($map)->count();
        $page = $this->page($count, 20);
        $orderstr="";
        if($ordertype==1){//评论数排序
            $orderstr="comments DESC";
        }else if($ordertype==2){//点赞数量排序
            $orderstr="likes DESC";
        }else if($ordertype==3){//分享数量排序
            $orderstr="shares DESC";
        }else{
            $orderstr="addtime DESC";
        }

        $lists = $video_model
            ->where($map)
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach($lists as $k=>$v){
            if($v['uid']==0){
                $userinfo=array(
                    'user_nicename'=>'系统管理员'
                );
            }else{
                $userinfo=getUserInfo($v['uid']);
                if(!$userinfo){
                    $userinfo=array(
                        'user_nicename'=>'已删除'
                    );
                }

            }


            $lists[$k]['userinfo']=$userinfo;

            $hasurgemoney=($v['big_urgenums']-$v['urge_nums'])*$v['urge_money'];
            $lists[$k]['hasurgemoney']=$hasurgemoney;
        }
        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->display();
    }


    public function  video_listen(){
        $id=I("id");
        if(!$id||$id==""||!is_numeric($id)){
            $this->error("加载失败");
        }else{
            //获取音乐信息
            $info=M("users_video")->where("id={$id}")->find();
            $this->assign("info",$info);
        }

        $this->display();
    }


    /*视频上架*/
    public function set_shangjia(){
        $id=I("id");
        if(!$id){
            $this->error("视频信息加载失败");
        }

        //获取视频信息
        $info=M("users_video")->where("id={$id}")->find();
        if(!$info){
            $this->error("视频信息加载失败");
        }

        $data=array(
            'xiajia_reason'=>'',
            'isdel'=>0
        );
        $result=M("users_video")->where("id={$id}")->save($data);
        if($result!==false){

            //将视频喜欢列表的状态更改
            M("users_video_like")->where("videoid={$id}")->setField('status',1);

            $this->success("上架成功");
        }
        $this->display();
    }

    public function commentlists(){

        $videoid=I("videoid");
        $video_comment=M("users_video_comments");
        $map=array();
        //$map['parentid']=0;
        $map['videoid']=$videoid;
        $count=$video_comment->where($map)->count();
        $page = $this->page($count, 20);
        //获取一级评论列表
        $lists=$video_comment->where($map)->order("addtime desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        //var_dump($video_comment->getLastSql());
        foreach ($lists as $k => $v) {
            $lists[$k]['user_nicename']=M("users")->where("id={$v['uid']}")->getField("user_nicename");
            /*$secondComments=$video_comment->where("videoid={$videoid} and commentid={$v['id']}")->select();
            foreach ($secondComments as $k1 => $v1) {
                $secondComments[$k1]['user_nicename']=M("users")->where("id={$v1['uid']}")->getField("user_nicename");
                $lists[$k]['secondComments']=$secondComments;
            }*/
        }

        //var_dump($lists);

        $this->assign("lists",$lists);
        $this->assign("page", $page->show('Admin'));
        $this->display();

    }

    public function addlabel(){
        $id=intval($_GET['id']);
        $labelInfo= array();
        if ($id){
            $labelInfo=M("video_label")->where(array('id'=>$id))->find();
        }
        $this->assign("info",$labelInfo);
        $this->display();

    }

    public function add_label(){

        if(IS_POST){
            $video=M("video_label");
            $video->create();

            $label=$_POST['label'];
            $owner_uid=$_POST['owner_uid'];
            $sort=$_POST['sort'];
            if($label==""){
                $this->error("请填写标签名称");
                return;
            }
            /* if($owner_uid==""||!is_numeric($owner_uid)){
                 $this->error("请填写用户id");
                 return;
             }
             //判断用户是否存在
             $ownerInfo=M("users")->where("user_type=2 and id={$owner_uid}")->find();
             if(!$ownerInfo){
                 $this->error("用户uid不存在");
                 return;
             }
             $video->uid=$owner_uid;*/
            $arr['addtime']=time();
            $arr['uid']=$owner_uid;
            $arr['label']= $label;
            $arr['is_delete']= '1';
            $arr['sort']= $sort;
            if (isset($_POST['id']) && !empty($_POST['id'])){
                $labelInfo=M("video_label")->where("is_delete=1 and label='"."{$label}"."' and id != '{$_POST['id']}'")->find();
                if($labelInfo){
                    $this->error("该标签已存在");
                    return;
                }
                $video->where(array('id' =>$_POST['id'] ))->save($arr);
                $result = true;
                if($result){
                    $this->success('修改成功','/Admin/Shotvideo/label',3);
                }else{
                    $this->error('修改失败');
                }
            }else{
                $labelInfo=M("video_label")->where("is_delete=1 and label='"."{$label}"."'")->find();
                if($labelInfo){
                    $this->error("该标签已存在");
                    return;
                }

                $video->add($arr);
                $result = true;

                if($result){
                    $this->success('添加成功','/Admin/Shotvideo/label',3);
                }else{
                    $this->error('添加失败');
                }

            }

        }
    }

    public function pass(){
        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            $this->error('缺少参数');
        }
        if(!isset($param['pass']) || !$param['id']){
            $this->error('缺少参数');
        }

        if(IS_POST) {
            $id = intval($_GET['id']);
            $pass = $_GET['pass'];
            $status_list = $this->status_list;
            $status_name = isset($status_list[$pass]) ? $status_list[$pass]['name'] : $pass;
            if(!$id){
                $this->error('数据传入失败！');
            }
            $redis = connectRedis();
            $shotvideo_check_action = $redis->get('shotvideo_check_action_' . $id);
            if ($shotvideo_check_action) {
                $this->error('已经有人在操作，请等待他人操作完成或者5分钟后再来操作该视频');
            } else {
                $redis->set('shotvideo_check_action_' . $id, get_current_admin_id(), 60*5); // 1个小时后该操作没有执行完，则可以让别人操作
            }
            $data['id'] = $id;
            $data['check_date'] = date('Y-m-d H:i:s', time());
            $data['status'] = $pass;
            $data['remark'] = trim($param['remark']);
            $data['operated_by'] = get_current_admin_user_login();
            $data['update_time'] = time();

            $videoInfo = M("video")->where(['id' => $id])->find();// 查找视频用户
            if (!$videoInfo) {
                $redis->del('shotvideo_check_action_' . $id);
                $this->error('没有该视频');
            }

            try {
                if ($data['status'] == 2) {
                    if ($videoInfo['is_downloadable'] != 1) {
                        $redis->del('shotvideo_check_action_' . $id);
                        $this->error('视频未上传完成');
                    }
                    if (!in_array($videoInfo['status'], [1,3])) {
                        $redis->del('shotvideo_check_action_' . $id);
                        $this->error('视频状态有误');
                    }
                }

                $userInfo = UsersModel::getInstance()->getUserInfoWithIdAndTid($videoInfo['uid']);
                if (!$userInfo) {
                    $redis->del('shotvideo_check_action_' . $id);
                    $this->error('用户不存在');
                }

                $result = M("video")->save($data);
                if(!$result){
                    setAdminLog('【短视频审核: '.$status_name.'】-失败-'.json_encode($param));
                    $redis->del('shotvideo_check_action_' . $id);
                    $this->error('审核失败');
                }

                if($result){
                    if ($pass == 2 && $videoInfo['origin']==1) {

                        $config = getConfigPub($userInfo['tenant_id']);
                        $map['endtime'] = array("egt", (time()));
                        $map['uid'] = array("eq", ($videoInfo['uid']));
                        $pre_balance = $userInfo['coin'];
                        $after_balance = bcadd($userInfo['coin'], 1, 4);
                        M("users")->where(array('id' => $videoInfo['uid']))->save(['coin' => $after_balance]);
    
                        $rewardData = [
                            'video_id' => $id,
                            'video_type' => 2,
                            'uid' => $videoInfo['uid'],
                            'user_type' => $userInfo['user_type'],
                            'add_time' => time(),
                            'price' => 1,
                        ];
                        $reward_id = M('video_uplode_reward')->add($rewardData);
                        $coinrecordData = [
                            'type' => 'income',
                            'action' => 'video_uplode_reward',
                            'uid' => $videoInfo['uid'],
                            "user_login" => $userInfo['user_login'],
                            'user_type' => $userInfo['user_type'],
                            'giftid' => $reward_id,
                            'addtime' => time(),
                            'tenant_id' => $userInfo['tenant_id'],
                            "pre_balance" => floatval($pre_balance),
                            'totalcoin' => 1,//金额
                            "after_balance" => floatval($after_balance),
                            "giftcount" => 1,
                            'is_withdrawable' => 1,
                            "order_id" => generater(),
                        ];
                        $this->addCoinrecord($coinrecordData);
                        delUserInfoCache($videoInfo['uid']);
                    }
                }
               /* if($userInfo['user_type'] != 7){ // 测试账号，审核成功后不做资金变动的处理
                    $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
                    if ($config['upload_video_reward_model'] ==1){
                        $userVip = M("users_vip")->where(['uid'=>$videoInfo['uid'], 'status'=>1])->order('grade desc')->find();
                        if($userVip){
                            $vipInfo = M("vip_grade")->where(['vip_grade' => $userVip['grade']])->find();
                            if ($data['status'] == 2 && $vipInfo['uplode_video_amount'] > 0) { // 审核通过，并且 奖励大于0， 则调用奖励机制
                                ShortVideoCheckReward($videoInfo, $userInfo, $vipInfo);
                            }
                        }
                    }else{
                        ShortVideoCheckRewardBytime($videoInfo, $userInfo);
                    }

                }*/

             }catch (\Exception $e){
                $redis->del('shotvideo_check_action_' . $id);
                setAdminLog('【短视频审核: '.$status_name.'】-出错-'.json_encode($param).'-'.$e->getMessage());
                $this->error('审核出错');
            }
            setAdminLog('【短视频审核: '.$status_name.'】-成功-'.json_encode($param));
            $redis->del('shotvideo_check_action_' . $id);
            if($videoInfo['origin'] == 1){
                VideoCache::getInstance()->setPrivateListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            if(in_array($videoInfo['origin'], [2,3])) {
                VideoCache::getInstance()->setPublicListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            if($videoInfo['top'] == 1){
                VideoCache::getInstance()->setTopListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            if($videoInfo['is_advertise'] == 1){
                VideoCache::getInstance()->setAdvertiseListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            //VideoCache::getInstance()->NotifyVGoideoWasApproved($id); // 视频审核通过、修改、删除，通知一下golang
            $this->success('审核成功', 'preload');
        }

        $video_info = M("video")->where(['id' => intval($param['id'])])->find();
        $origin_list = $this->origin_list;
        $is_downloadable_list = $this->is_downloadable_list;
        $video_info['origin_name'] = isset($origin_list[$video_info['origin']]) ? $origin_list[$video_info['origin']] : $video_info['origin'];
        $video_info['is_downloadable_name'] = '<span style="color: '.$is_downloadable_list[$video_info['is_downloadable']]['color'].';">'.$is_downloadable_list[$video_info['is_downloadable']]['name'].'</span>';

        $user_info = getUserInfo($video_info['uid']);

        $this->assign('video_info', $video_info);
        $this->assign('user_info', $user_info);
        $this->assign('param', $param);
        $this->display('pass');
    }

    public function deletelabel(){
        $id=intval($_GET['id']);

        if($id){
            $result=M("video_label")->delete($id);

            if($result){
                $action="shotvideo pass sucess：{$id}";
                setAdminLog($action);
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    public function deleteuser(){
        $id=intval($_GET['id']);

        if($id){
            $type = M("video_label")->where(array('id' => $id))->getField('type');

            if ($type){
                $this->error('请将标签设为普通标签再删除');
            }
            $result=M("video_active_member")->delete($id);

            if($result){
                $action="delete active member：{$id}";
                setAdminLog($action);
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    public  function updatetype(){
        $id=intval($_GET['id']);
        $type = $_GET['type'];
        if($id) {
            $result = M("video_label")->where(array('id' => $id))->save(array('type' => $type));
            if ($result) {
                $this->success('设置成功');
            } else {
                $this->error('设置失败');
            }
        }
    }

    public  function labelsort(){
        $ids = $_POST['sort'];

        foreach ($ids as $key => $r) {
            $data['sort'] = $r;
            M("video_label")->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $action="更新短视频视频标签排序";
            setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    public  function add_address(){
        $param = I('param.');
        $videoInfo  = array();
        $id = '';
        $videoLabel  = [];
        if (isset($param['id'])){
            $id  = $param['id'];
            $video = M("video");
            $videoInfo = $video->where(array('id'=>$id))->find();
            $tenant_id = $videoInfo['tenant_id'];

            $videoLabel = explode(',',$videoInfo['label']);
            $videoclassify = explode(',',$videoInfo['classify']);
        }else{
            $tenant_id = getTenantId();
        }

        $labels=$this->role_model->where("is_delete=1")->order("id desc")->select();
        $classify = VideoClassifyCache::getInstance()->getShortVideoClassifyList($tenant_id);

        // var_dump($roles);exit;
        if ($videoInfo['origin'] != 3){
            $playback_address= M('playback_address')->where(array('tenant_id'=>intval($videoInfo['tenant_id']), 'is_enable'=>'1','type'=>1))->find();
            $videoInfo['href'] =  $videoInfo[$playback_address['viode_table_field']];

            $download_address= M('playback_address')->where(array('tenant_id'=>intval($videoInfo['tenant_id']), 'is_enable'=>'1','type'=>2))->find();
            $videoInfo['download_address'] =  $videoInfo[$download_address['viode_table_field']];
        }
        $this->assign("videoInfo",$videoInfo);
        $performer=M('performer')->field('id,name')->select();;
        $this->assign("performer",$performer);
        $this->assign("id",$id);
        $this->assign("labels",$labels);
        $this->assign("videoLabel",$videoLabel);
        $this->assign("classify",$classify);
        $this->assign("videoclassify",$videoclassify);
        $this->display();
    }

    public  function add_address_post(){
        if (IS_POST) {
            $param = I('post.');
            $video = M("video");
            $video->create();
            $title = trim($_POST['title']);
            $thumb = trim($_POST['thumb']);
            $href = trim($_POST['href']);
            $years = trim($_POST['years']);
            $region = trim($_POST['region']);
            $desc = trim($_POST['desc']);
            $download_address = trim($_POST['download_address']);
            $duration = $_POST['duration'];
            $playTimeIntArray = explode(':',$duration);
            $playTimeInt = $playTimeIntArray[0] * 24*60*60;
            $playTimeInt+= $playTimeIntArray[1] * 24*60*60;
            $newplayTimeInt = explode('.',$playTimeIntArray[2]);
            $playTimeInt+= $newplayTimeInt[0]*60;
            $playTimeInt+= $newplayTimeInt[1];
            $watchtimes   =$_POST['watchtimes'];
            if ($title == "") {
                $this->error("请填写视频标题");
            }
            if ($thumb == "") {
                $this->error("请填写视频封面图");
            }
            if ($href == "") {
                $this->error("请填写视频播放地址");
            }
            if ($download_address == "") {
                $this->error("请填写视频下载地址");
            }
            if (mb_strlen($thumb) > 500) {
                $this->error("视频封面图链接长度不能大于500");
            }
            if (mb_strlen($href) > 500) {
                $this->error("频播放地址长度不能大于500");
            }
            if (mb_strlen($download_address) > 1000) {
                $this->error("视频下载地址长度不能大于500");
            }
            if(mb_strlen($param['remark']) > 255){
                $this->error("备注长度不能大于255");
            }
            $download_address_array = explode('.',$download_address);

            if ($duration == "") {
                $this->error("请填写视频播放时长");
            }
            $owner_uid = $_POST['owner_uid'];

            if ($owner_uid == "" || !is_numeric($owner_uid)) {
                $this->error("请填写视频所有者id");
                return;
            }

            //判断用户是否存在
            $ownerInfo = M("users")->where("user_type in(2,3,4,5,6,7) and id={$owner_uid}")->find();
            if (!$ownerInfo) {
                $this->error("视频所有者不存在");
                return;
            }
          /*  if ($ownerInfo['user_type'] == 3) {
                $this->error("视频所有者不能是虚拟会员");
                return;
            }*/
            if ($ownerInfo['user_type'] == 4) {
                $this->error("视频所有者不能是游客");
                return;
            }

            $video->uid = $owner_uid;
            $price = trim($_POST['price']);
            $try_watch_time = trim($_POST['try_watch_time']);
            $label = $_POST['label'];
            $performer = $_POST['performer'];
            $abelinfo = '';
            if (!empty($label)) {
                foreach ($label as $key => $value) {
                    if ($key == 0) {
                        $abelinfo = $value;
                    } else {
                        $abelinfo .= "," . $value;
                    }
                }
            }
            $classify  = $_POST['classify'];
            if (!empty($classify)) {
                $classify = implode(',',$classify);

            }
            $arr['href'] = $href;
            $arr['watchtimes'] = $watchtimes;
            $arr['playTimeInt'] = $playTimeInt;
            $arr['years'] = $years;
            $arr['region'] = $region;
            $arr['desc'] = $desc;
            $arr['uid'] = $owner_uid;
            $arr['user_login'] = $ownerInfo['user_login'];
            $arr['tenant_id'] = intval($ownerInfo['tenant_id']);
            $arr['thumb'] = $thumb;
            $arr['title'] = $title;
            $arr['duration'] = $duration;
            $arr['label'] = $abelinfo;
            $arr['price'] = $price;
            $arr['try_watch_time'] = $try_watch_time;
            $arr['performer'] = $performer;
            $arr['classify'] = $classify;
            $arr['remark'] = trim($param['remark']);
            $arr['shoptype'] = intval($param['shoptype']);
            $arr['shop_value'] = trim($param['shop_value']);
            $arr['shop_url'] = trim($param['shop_url']);
            if (!empty($_POST['create_date'])){
                $arr['create_date'] = date('Y-m-d H:i',strtotime($_POST['create_date'])).':'.date('s');
            }else{
                $arr['create_date'] = date('Y-m-d H:i',strtotime(time())).':'.date('s');
            }
            if ($_POST['likes']){
                $arr['likes'] = $_POST['likes'];
                $action = '修改点赞数量为'.$_POST['likes'];
                setAdminLog($action);
            }
            if ($_POST['collection']){
                $arr['collection'] = $_POST['collection'];
                $action = '修改收藏数量为'.$_POST['collection'];
                setAdminLog($action);
            }
            $is_performer = 0;
            if ($performer) {
                $is_performer = 1;
            }
            $arr['is_performer'] = $is_performer;
            $arr['operated_by'] = get_current_admin_user_login();

            try{
                $video_info = VideoModel::getInstance()->getInfoWithid($_POST['id']);
                if ($_POST['id']){
                    $arr['update_time'] = time();
                    if (isset($_POST['origin']) && $_POST['origin'] == 3 ){
                        if (strtolower(end($download_address_array)) != 'mp4' ){
                            $this->error("下载地址为mp4的地址");
                        }
                    }
                    if (isset($_POST['origin']) && $_POST['origin'] != 3){
                        $playback_address= M('playback_address')->where(array('is_enable'=>'1','type'=>1))->find();
                        $arr[$playback_address['viode_table_field']] =  $href;
                        $download_address_line= M('playback_address')->where(array('is_enable'=>'1','type'=>2))->find();
                        $videoInfo[$download_address_line['viode_table_field']] =  $download_address;
                    }else{
                        $arr['href'] = $href;
                        $arr['download_address'] = $download_address;
                    }
                    $result = $video->where(array('id'=> $_POST['id']))->save($arr);
                    if($result > 0){
                        if($video_info['origin'] == 1){
                            VideoCache::getInstance()->setPrivateListIdCache($arr['tenant_id'], $_POST['id']);
                        }
                        if(in_array($video_info['origin'], [2,3])) {
                            VideoCache::getInstance()->setPublicListIdCache($arr['tenant_id'], $_POST['id']);
                        }
                        if($video_info['top'] == 1){
                            VideoCache::getInstance()->setTopListIdCache($arr['tenant_id'], $_POST['id']);
                        }
                        if($video_info['is_advertise'] == 1){
                            VideoCache::getInstance()->setAdvertiseListIdCache($arr['tenant_id'], $_POST['id']);
                        }
                      //  VideoCache::getInstance()->NotifyVGoideoWasApproved($_POST['id']); // 视频审核通过、修改、删除，通知一下golang
                    }

                    /*
                     * 编辑作品绑定商品id或者店铺id
                     */
                    $tenant_id = getTenantIds();
                    if( $arr['shoptype'] == 1){
                        $tenantInfo=getTenantInfo($ownerInfo['tenant_id']);
                        $shopparms = array(
                            'id' => $arr['shop_value'],
                        );
                        $url = $tenantInfo['shop_url'].'/api.php?s=Goods/Detail';
                        $shopinfo = http_post($url,$shopparms);
                        if(isset($shopinfo['code']) && $shopinfo['code'] == 0){
                            $videobindshop = array(
                                'title'=>$shopinfo['data']['goods']['title'],
                                'images'=>$shopinfo['data']['goods']['images'],
                                'price'=>$shopinfo['data']['goods']['price'],
                                'original_price'=>$shopinfo['data']['goods']['original_price'],
                                'shop_url'=>$arr['shop_url'],
                            );
                            $redis = connectionRedis();
                            $redis->hSet("videobindshop",$_POST['id'],json_encode($videobindshop));
                            $redis->Del( 'short_video_info_' . $tenant_id . $_POST['id']);
                        }else{
                            $this->error('绑定商品ID失败');
                        }
                    }
                    if( $arr['shoptype'] == 2){
                        $tenantInfo=getTenantInfo($ownerInfo['tenant_id']);
                        $shopparms = array(
                            'id' => $arr['shop_value'],
                        );
                        $url = $tenantInfo['shop_url'].'/api.php?s=plugins/index&pluginsname=shop&pluginscontrol=index&pluginsaction=detail';
                        $shopinfo = http_post($url,$shopparms);
                        $shopgoodsparms = array(
                            'shop_id' => $arr['shop_value'],
                        );
                        $url = $tenantInfo['shop_url'].'/api.php?s=plugins/index&pluginsname=shop&pluginscontrol=search&pluginsaction=DataList';
                        $shopgoodsinfo = http_post($url,$shopgoodsparms);

                        if(isset($shopinfo['code']) && $shopinfo['code'] == 0){
                            $videobindshop = array(
                                'title'=>$shopinfo['data']['shop']['name'],
                                'images'=>$shopinfo['data']['shop']['logo'],
                                'shop_url'=>$arr['shop_url'],
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


                            $redis = connectionRedis();
                            $redis->hSet("videobindshop",$_POST['id'],json_encode($videobindshop));
                            $redis->Del( 'short_video_info_' . $tenant_id . $_POST['id']);
                        }else{
                            $this->error('绑定店铺ID失败');
                        }
                    }
                    if ($result >= 0){
                        $this->success('修改成功');
                    }else{
                        $this->error('修改失败');
                    }
                }else{
                    if (strtolower(end($download_address_array)) != 'mp4' ){
                        $this->error("下载地址为mp4的地址");
                    }
                    $arr['user_type'] = intval($ownerInfo['user_type']);
                    $arr['href'] = $href;
                    $arr['download_address'] = $download_address;
                    $arr['origin'] = '3'; //后台手动上传
                    $arr['is_downloadable'] = 1;
                    $arr['status'] = '1';
                    $arr['create_date'] = date('Y-m-d H:i:s', time());
                    $arr['create_time'] = time();

                    $result = $video->add($arr);
                    if ($result) {
                        $video_id = $video->getLastInsID();
                        if($arr['origin'] == 1){
                            VideoCache::getInstance()->setPrivateListIdCache($arr['tenant_id'], $video_id);
                        }
                        if(in_array($arr['origin'], [2,3])) {
                            VideoCache::getInstance()->setPublicListIdCache($arr['tenant_id'], $video_id);
                        }
                        $this->success('添加成功');
                    } else {
                        $this->error('添加失败');
                    }
                }
            }catch (\Exception $e){
                $action="短视频操作失败: ".$e->getMessage();
                setAdminLog($action);
                $this->error('视频操作失败');
            }
        }
    }

    public  function del_viode(){
        $id=I("id");
        if(!$id){
            $res['code']=1001;
            $res['msg']='视频信息加载失败';
            echo json_encode($res);
            exit;
        }
        //获取视频信息
        $videoInfo=M("video")->where("id={$id}")->find();

        $result=M("video")->where("id={$id}")->save(['status'=> 4,  'update_time' => time(), 'operated_by' => get_current_admin_user_login()]);

        //$result=M("users_video")->where("id={$id}")->setField("isdel","1");

        if($result!==false){
            M("users_video_comments")->where(array('videoid'=> $id,'type' =>1))->delete();	 //删除视频评论
            M("users_video_like")->where(array('videoid'=> $id,'type' =>1))->delete();	 //删除视频喜欢
            M("users_video_comments_like")->where(array('videoid'=> $id,'type' =>1))->delete(); //删除视频评论喜欢
            M("users_video_comments_like")->where(array('videoid'=> $id,'type' =>1))->delete(); //删除视频评论喜欢
            M("video_watch_record")->where(array('videoid'=> $id,'type' =>1))->delete(); //观看次数
            M("video_download")->where(array('videoid'=> $id,'type' =>1))->delete(); //下载
            for ($i= 0 ;$i>10;$i++){
                if ($i ==0){
                    $file =$_SERVER['DOCUMENT_ROOT'] . $videoInfo['href'] ;
                }else{
                    $file =$_SERVER['DOCUMENT_ROOT'] . $videoInfo['href_'.$i] ;
                }
                if(file_exists($file)) {
                    unlink($file);
                }
            }
            if($videoInfo['origin'] == 1){
                VideoCache::getInstance()->delPrivateListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            if(in_array($videoInfo['origin'], [2,3])) {
                VideoCache::getInstance()->delPublicListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            if($videoInfo['top'] == 1){
                VideoCache::getInstance()->delTopListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            if($videoInfo['is_advertise'] == 1){
                VideoCache::getInstance()->delAdvertiseListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            //VideoCache::getInstance()->NotifyVGoideoWasApproved($id); // 视频审核通过、修改、删除，通知一下golang
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }

    }

    public  function file_encrypt($source, $dest, $key){

        if(file_exists($source)){
            $content = ''; // 处理后的字符串
            $keylen = strlen($key); // 密钥长度
            $index = 0;
            $fp = fopen($source, 'rb+');
            while(!feof($fp)){
                $tmp = fread($fp, 8);

                $content .= $tmp ^ substr($key,$index%$keylen,1);
                $index++;
            }
            fclose($fp);
            return file_put_contents($dest, $content, true);

        }else{
            return false;
        }
    }

    public function addclassify(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $id=intval($_GET['id']);
        $clssifyInfo= array();
        if ($id){
            $clssifyInfo=M("video_classify")->where(array('id'=>$id))->find();
        }

        $this->assign("info", $clssifyInfo);
        $this->assign('tenant_id', $tenant_id);
        $this->assign('tenant_list',getTenantList());
        $this->display();

    }

    public function classify(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());

        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map[] = ['tenant_id'=>$tenant_id];

        if(isset($param['classify']) && $param['classify']!=''){
            $map['classify']= $param['classify'];
        }


        $video_model = M("video_classify");
        $count = $video_model->where($map)->count();
        $page = $this->page($count, $page_size);

        $lists = $video_model
            ->where($map)
            ->order("sort asc ,id asc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach($lists as $k=>$v){
            $lists[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
            $lists[$k]['update_time_date'] = $v['update_time'] ? date('Y-m-d H:i:s',$v['update_time']) : '-';
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list',getTenantList());
        $this->assign('param', $param);
        $this->assign("p",$p);
        $this->display();
    }

    public function addclassify_post(){

        if(IS_POST){
            $param = I('param.');
            $video = M("video_classify");
            $video->create();

            $classify=$_POST['classify'];

            if($classify==""){
                $this->error("请填写分类名称");
                return;
            }

            $arr['classify']= trim($classify);
            $arr['is_lowerlevel']= intval($param['is_lowerlevel']);
            $arr['agent_line_visible']= intval($param['agent_line_visible']);
            $arr['is_list']= intval($param['is_list']);
            $arr['addtime']=time();
            $arr['sort'] = $param['sort'] != '' ? intval($param['sort']) : 1;
            $arr['operatename']  = get_current_admin_user_login();
            $arr['type'] = intval($param['type']) == 0 ? 1: 0;

            if (isset($_POST['id']) && !empty($_POST['id'])){
                $exist = M("video_classify")->where("classify='"."{$classify}"."' and id != '{$param['id']}'")->find();
                if($exist){
                    $this->error("该分类已存在");
                }
                $info = M("video_classify")->where(['id'=>intval($param['id'])])->find();

                $tenant_id = intval($info['tenant_id']);
                $arr['update_time']  = time();
                $result = $video->where(array('id' =>$_POST['id'] ))->save($arr);
                if($result >= 0){
                    setAdminLog('【修改短视频分类】成功：'.json_encode($param, JSON_UNESCAPED_UNICODE));
                    VideoClassifyCache::getInstance()->delShortVideoClassifyCache($tenant_id); // 清理短视频分类缓存
                   // VideoClassifyCache::getInstance()->updateGoShortVideoClassifyCache($tenant_id); // 更新golang短视频分类列表数据
                    $this->success('修改成功', U('classify',array('tenant_id'=>$tenant_id)));
                }else{
                    $this->error('修改失败');
                }
            }else{
                $exist = M("video_classify")->where("classify='"."{$classify}"."'")->find();
                if($exist){
                    $this->error("该分类已存在");
                }
                $tenant_id = intval($param['tenant_id']);
                $arr['tenant_id'] = $tenant_id;
                $result = $video->add($arr);
                if($result >= 0){
                    setAdminLog('【新增短视频分类】成功：'.json_encode($param, JSON_UNESCAPED_UNICODE));
                    VideoClassifyCache::getInstance()->delShortVideoClassifyCache($tenant_id); // 清理短视频分类缓存
                  //  VideoClassifyCache::getInstance()->updateGoShortVideoClassifyCache($tenant_id); // 更新golang短视频分类列表数据
                    $this->success('添加成功', U('classify',array('tenant_id'=>$tenant_id)));
                }else{
                    $this->error('添加失败');
                }

            }

        }
    }

    public function deleteclassify(){
        $param = I('param.');
        $id=intval($_GET['id']);
        if($id){
            $info = M("video_classify")->where(['id'=>intval($param['id'])])->find();
            $result=M("video_classify")->delete($id);
            if($result){
                setAdminLog('【删除短视频分类】成功：'.json_encode($info, JSON_UNESCAPED_UNICODE));
                VideoClassifyCache::getInstance()->delShortVideoClassifyCache($info['tenant_id']); // 清理短视频分类缓存
                VideoClassifyCache::getInstance()->updateGoShortVideoClassifyCache($info['tenant_id']); // 更新golang短视频分类列表数据
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    /*购买记录*/
    public function buyindex(){
        $param = $_REQUEST;
        $map =['tenant_id'=>getTenantIds()];


        if($param['start_time']!=''){
            $map['addtime']=array("gt",($param['start_time']));
        }

        if($param['end_time']!=''){
            $map['addtime']=array("lt",($param['end_time']));
        }
        if($param['start_time']!='' && $param['end_time']!='' ){
            $map['addtime']=array("between",array(($param['start_time']),($param['end_time'])));
        }
        if($param['user_login']!='') {
            $map['user_login'] = $param['user_login'];
        }
        if($param['uid']!='') {
            $map['uid'] = $param['uid'];
        }
        if($param['video_id']!='') {
            $map['videoid'] = $param['video_id'];
        }
        if($param['video_type']!='') {
            $map['video_type'] = $param['video_type'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $where = '1';

        $p=I("p");
        if(!$p){
            $p=1;
        }

        $model = M("users_video_buy");
        $count = $model->where($where)->where($map)->count();
        $page = $this->page($count);
        $orderstr="addtime DESC";

        $lists = $model
            ->where($where)
            ->where($map)
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $configpri=getConfigPri();
        $money = 0;
        foreach($lists as $key=>$val){
            $uids[] = $val['ex_user_id'];
            if($val['video_type']==1){
                $lists[$key]['video_type']='短视频';
            }else{
                $lists[$key]['video_type']='长视频';
            }
            $money += $val['price']*$configpri['money_rate'];
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
        }
        if ($uids){
            $userList =  M('users')->where(['id' =>[ 'in',$uids]])->field('id,user_login')->select();
            $userListById =  array_column($userList,null,'id');
            foreach($lists as $k=>$v){

                $lists[$k]['ex_users'] = $userListById[$v['ex_user_id']]['user_login'];
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('money', $money);
        $this->assign('count', $count);
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }

    //作者收益设置
    public  function rate()
    {
        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
        $this->assign('config',$config);
        $this->display();
    }

    public function rate_add()
    {

        try{
            if(IS_POST){
                $config=I("post.post");
                $tenantId=getTenantIds();
                if (M("tenant_config")->where('tenant_id="'.$tenantId.'"')->save($config)!==false) {
                    //$key=$tenantId.'_'.'getTenantConfig';
                    //setcaches($key,$config);
                    delcache($tenantId.'_'.'getTenantConfig');
                    delcache($tenantId.'_'."getPlatformConfig");

                    $action="修改租户设置";
                    setAdminLog($action);
                    $this->success("保存成功！");
                } else {
                    $this->error("保存失败！");
                }
            }
        }catch (\Exception $e){
            setAdminLog('修改租户设置失败：'.$e->getMessage());
            $this->error("保存失败！");
        }
    }

    public function rate_edit_post()
    {
        if(IS_POST){
            $uid=I('post.uid');
            if(empty($uid)){
                $this->error('UID不能为空');
            }
            $rate=I('post.rate');
            /*$onerate=I('post.one_uid_rate');
            $tworate=I('post.two_uid_rate');
            $threerate=I('post.three_uid_rate');*/
            if(empty($rate) || !is_numeric($rate)){
                $this->error('作者收益率请填写数字，不能为空');
            }
            if(empty($onerate) || !is_numeric($onerate)){
                $this->error('作者上一级收益请填写数字，不能为空');
            }
            if(empty($tworate) || !is_numeric( $tworate)){
                $this->error('作者上二级收益请填写数字，不能为空');
            }
            if(empty($threerate) || !is_numeric($threerate)){
                $this->error('作者上三级收益请填写数字，不能为空');
            }
            $rateDat=M('users_video_rate')->field('id')->where(['uid'=>$uid])->find();
            if($rateDat['id']){
                if(M('users_video_rate')->where(['id'=>$rateDat['id']])->save(
                    [
                        'rate'=>$rate,
                        'one_uid_rate'=>$onerate,
                        'two_uid_rate'=>$tworate,
                        'three_uid_rate'=>$threerate,
                        'add_time'=>time()
                    ]
                )){
                    $this->success('添加成功');
                }
            }else{
                if(M('users_video_rate')->add(
                    [
                        'uid'=>$uid,
                        'rate'=>$rate,
                        'one_uid_rate'=>$onerate,
                        'two_uid_rate'=>$tworate,
                        'three_uid_rate'=>$threerate,
                        'add_time'=>time()
                    ]
                )){
                    $this->success('添加成功');
                }
            }

            $this->error('添加失败');
        }
    }

    public function rate_edit()
    {
        $id=intval($_GET['id']);
        if(!$id){
            $this->error('缺少关键ID');
        }
        $uids=M('video')->field('uid')->where(['id'=>$id])->find();
        if(!$uids){
            $this->error('视频下架');
        }
        $this->assign('uid',$uids['uid']);
        $res=M('users_video_rate')->field('rate,one_uid_rate,two_uid_rate,three_uid_rate')->where(['uid'=>$uids['uid']])->find();
        $this->assign('res', $res);
        $this->display();
    }

    public  function uplode_reward(){
        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
        $this->assign('config',$config);
        $this->display();
    }



    public  function uplode_reward_set(){
        try{
            if(IS_POST){
                $config=I("post.post");
                $tenantId=getTenantIds();
                if (M("tenant_config")->where('tenant_id="'.$tenantId.'"')->save($config)!==false) {
                    //$key=$tenantId.'_'.'getTenantConfig';
                    //setcaches($key,$config);
                    delcache($tenantId.'_'.'getTenantConfig');
                    delcache($tenantId.'_'."getPlatformConfig");

                    $action="修改租户设置";
                    setAdminLog($action);
                    $this->success("保存成功！");
                } else {
                    $this->error("保存失败！");
                }
            }
        }catch (\Exception $e){
            setAdminLog('修改租户设置失败：'.$e->getMessage());
            $this->error("保存失败！");
        }
    }

    public  function uplode_reward_log(){
        $param = I('param.');
        $map = [];

        if($_REQUEST['start_time']!=''){
            $map['addtime']=array("gt",($_REQUEST['start_time']));
            $_GET['start_time']=$_REQUEST['start_time'];
        }

        if($_REQUEST['end_time']!=''){
            $map['addtime']=array("lt",($_REQUEST['end_time']));
            $_GET['end_time']=$_REQUEST['end_time'];
        }
        if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
            $map['addtime']=array("between",array(($_REQUEST['start_time']),($_REQUEST['end_time'])));
            $_GET['start_time']=$_REQUEST['start_time'];
            $_GET['end_time']=$_REQUEST['end_time'];
        }
        if($_REQUEST['username']!='') {
            $_GET['username'] = $_REQUEST['username'];
            $userInfo =  M('users')->where(['user_login' => $_REQUEST['username']])->field('id')->find();

           if ($userInfo){
               $map['uid'] = $userInfo['id'];
           }else{
               $map['uid']  = 0;

           }

        }
        if($_REQUEST['uid']!='') {
            $map['uid'] = $_REQUEST['uid'];
            $_GET['uid'] = $_REQUEST['uid'];
        }
        if($_REQUEST['video_id']!='') {
            $map['video_id'] = $_REQUEST['video_id'];
            $_GET['video_id'] = $_REQUEST['video_id'];
        }
        if($_REQUEST['video_type']!='') {
            $map['video_type'] = $_REQUEST['video_type'];
            $_GET['video_type'] = $_REQUEST['video_type'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $p=I("p");
        if(!$p){
            $p=1;
        }

        $model = M("video_uplode_reward");
        $count = $model->where($map)->count();
        $page = $this->page($count);
        $orderstr="add_time DESC";

        $lists = $model
            ->where($map)
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $configpri=getConfigPri();
        $money = 0;
        $uids = [];
        foreach($lists as $k=>$v){
            $uids[] = $v['uid'];
            if($v['video_type']==1){
                $lists[$k]['video_type']='短视频';
            }else{
                $lists[$k]['video_type']='长视频';
            }
            $userinfo = getUserInfo($v['uid']);
            if($v['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$v['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
        }
        if ($uids) {
            $userList = M('users')->where(['id' => ['in', $uids]])->field('id,user_login')->select();
            $userListById = array_column($userList, null, 'id');
            foreach ($lists as $k => $v) {
                $lists[$k]['username'] = $userListById[$v['uid']]['user_login'];
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('money', $money);
        $this->assign('count', $count);
        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }

    public  function batchPass(){
        $ids = $_POST['ids'];
        $redis = connectRedis();

        foreach ($ids as $key => $id) {
            if(!$id) {
                continue;
            }
            $shotvideo_check_action = $redis->get('shotvideo_check_action_'.$id);
            if ($shotvideo_check_action){
                continue;
            }else{
                $redis->set('shotvideo_check_action_'.$id, get_current_admin_user_login(), 60*5);
            }
            $data['id'] = $id;
            $data['check_date'] = date('Y-m-d H:i:s',time());
            $data['status'] =  2;
            $data['operated_by'] = get_current_admin_user_login();
            $data['update_time'] = time();
            $status_name = isset($status_list[$data['status']]) ? $status_list[$data['status']]['name'] : $data['status'];

            $videoInfo =M("video")->where(['id'=>$id])->find();// 查找视频用户
            if (!$videoInfo) {
                $redis->del('shotvideo_check_action_' . $id);
                continue;
            }

            try {
                if ($videoInfo['is_downloadable'] != 1){
                    $redis->del('shotvideo_check_action_'.$id);
                    continue;
                }
                if (!in_array($videoInfo['status'], [1,3])) {
                    $redis->del('shotvideo_check_action_' . $id);
                    continue;
                }
                $userInfo = UsersModel::getInstance()->getUserInfoWithIdAndTid($videoInfo['uid']);

                $result=M("video")->save($data);
                if(!$result){
                    setAdminLog('【短视频批量审核: '.$status_name.'】-失败'.$id);
                    $redis->del('shotvideo_check_action_' . $id);
                    continue;
                }
                if($userInfo['user_type'] == 7){ // 测试账号，审核成功后不做资金变动的处理，直接返回
                    $redis->del('shotvideo_check_action_' . $id);
                    continue;
                }
                $userVip = M("users_vip")->where(['uid'=>$videoInfo['uid'], 'status'=>1])->order('grade desc')->find();
                if(!$userVip){
                    $redis->del('shotvideo_check_action_' . $id);
                    continue;
                }
                $vipInfo  = M("vip_grade")->where(['vip_grade'=> $userVip['grade']])->find();
                if ($vipInfo['uplode_video_amount']>0){
                    ShortVideoCheckReward($videoInfo, $userInfo, $vipInfo);
                }
            }catch (\Exception $e){
                $redis->del('shotvideo_check_action_' . $id);
                setAdminLog('【短视频批量审核: '.$status_name.'】-出错-'.$id.'-'.$e->getMessage());
                continue;
            }
            setAdminLog('【短视频批量审核: '.$status_name.'】-成功-'.$id);
            if($videoInfo['origin'] == 1){
                VideoCache::getInstance()->setPrivateListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            if(in_array($videoInfo['origin'], [2,3])) {
                VideoCache::getInstance()->setPublicListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            if($videoInfo['top'] == 1){
                VideoCache::getInstance()->setTopListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            if($videoInfo['is_advertise'] == 1){
                VideoCache::getInstance()->setAdvertiseListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
            }
            //VideoCache::getInstance()->NotifyVGoideoWasApproved($id); // 视频审核通过、修改、删除，通知一下golang
            $redis->del('shotvideo_check_action_' . $id);
        }
        $this->success('审核成功', U('index',$_REQUEST));
    }

    public  function batchFail(){
        $ids = $_POST['ids'];
        if (empty($ids)){
            return $this->error('请勾选数据');

        }
        $result=M("video")->where(['id'=>['in',$ids], 'operated_by' => get_current_admin_user_login()])->save(['status'=>3]);
        if ($result !== false){
            $this->success('操作成功');

        }else{
            return $this->success('操作失败');
        }
    }

    public  function batchDel(){
        $ids = $_POST['ids'];
        if (empty($ids)) {
            return $this->error('请勾选数据');
        }
        $result=M("video")->where(['id'=>['in',$ids]])->save(['status'=>4]);
        if ($result !== false){
            $this->success('操作成功');

        }else{
            return $this->success('操作失败');
        }
    }

    public  function export(){
     /*   $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X');
        $xlsCell  = array(
            array('uid','用户id(视频归属)'),
            array('title','视频标题'),
            array('label','标签'),
            array('thumb','封面地址'),
            array('create_date','上传时间2022-04-13 21:06:41'),
            array('check_date','审核时间 2022-04-13 21:06:41'),
            array('status','状态1 待审核 2 审核通过 '),
            array('href','视频播放链接'),
            array('comments','评论数'),
            array('watchtimes','观看数'),
            array('likes','喜欢数'),
            array('duration','时长00:00:03'),
            array('collection','收藏数'),
            array('download_times','下载次数'),
            array('download_address','下载地址'),
            array('desc','剧情简介'),
            array('years','年代'),
            array('region','地区'),
            array('classify','分类'),
            array('price','价格'),
            array('try_watch_time','试看时间'),
            array('buy_numbers','购买次数'),
            array('type','1 长视频 2 短视频'),


        );
        $xlsName = 'video';
        $xlsData = [];
        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);*/

        $export_data[]=[
            'uid'=>'',
            'title'=>'',
            'label'=>'',
            'thumb'=>'',
            'create_date'=>'',
            'check_date'=>'',
            'status'=>'',
            'href'=>'',
            'comments'=>'',
            'watchtimes'=>'',
            'likes'=>'',
            'duration'=>'',
            'collection' =>'',
            'download_times'=>'',
            'download_address'=>'',
            'desc'=>'',
            'years'=>'',
            'region'=>'',
            'classify'=>'',
            'price'=>'',
            'try_watch_time'=>'',
            'buy_numbers'=>'',
            'type'=>'1',
        ];


        $header=array(
            'title' => array(
                'uid'=>'用户id(视频归属)',
                'title'=>'视频标题',
                'label'=>'标签',
                'thumb'=>'封面地址',
                'create_date'=>'上传时间',
                'check_date'=>'审核时间',
                'status'=>'状态1 待审核 2 审核通过 ',
                'href'=>'视频连接',
                'comments'=>'评论数',
                'watchtimes'=>'观看数',
                'likes'=>'喜欢数',
                'duration'=>'时长',
                'collection'=>'收藏数',
                'download_times'=>'下载次数',
                'download_address'=>'下载地址',
                'desc'=>'剧情简介',
                'years'=>'年代',
                'region'=>'地区',
                'classify'=>'分类',
                'price'=>'价格',
                'try_watch_time'=>'试看时间',
                'buy_numbers'=>'购买次数',
                'type'=>'1 长视频 2 短视频:20',
            ),

        );

            $filename="video";
            $return_url = count($export_data) > 10000 ? true : false;
            $excel_filname = $return_url == true ? $filename : $filename;
            include EXTEND_PATH ."util/UtilPhpexcel.php";
            $Phpexcel = new \UtilPhpexcel();
            $downurl = "/".$Phpexcel::export_excel_v1($export_data, $header,$excel_filname, $return_url);

        if($downurl){
            $output_filename = $filename;
            header('pragma:public');
            header("Content-Disposition:attachment;filename=".$output_filename.".xls"); //下载文件，filename 为文件名
            echo file_get_contents($downurl);
            exit;
        }

        $this->success("导出成功",$downurl);
    }

    public function import(){
        if(IS_POST){
            $tmp_file = $_FILES ['file'] ['tmp_name'];
            $type = strstr( $_FILES ['file']['name'],'.');
            if ($type != '.xls' && $type != '.xlsx') {
                $this->error('请上传excel文件');
            }
            if (is_uploaded_file($tmp_file)) {

                /*设置上传路径*/
                $savePath = './data/upload/';
                /*以时间来命名上传的文件*/
                $str = date('Ymdhis');
                $file_name = $str . "." . $type;
                /*是否上传成功*/
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $savePath . $file_name)) {
                    $this->error('上传失败');
                }
                vendor("PHPExcel.PHPExcel.IOFactory");
                $iofactory = new \PHPExcel_IOFactory();
                $objReader = $iofactory::createReaderForFile($savePath . $file_name);
                $objPHPExcel = $objReader->load($savePath . $file_name);
                $objPHPExcel->setActiveSheetIndex(0);
                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
                $data = array();

                if ($highestRow> 10000){
                    $this->error('单次操作不用操过1千');
                }
                M()->startTrans();
                for ($row = 2; $row <= $highestRow; $row++) {

                    $data['uid']  = $sheet->getCellByColumnAndRow(0, $row)->getValue();
                    if ( !M("users")->where(array('id'=> $data['uid'] ,'tenant_id' =>getTenantIds() ))->find()){
                        M()->rollback();
                        $this->error('第'.$row.'行，用户名'. '用户id'. $data['uid'].'不存在,请编辑后在导入');
                    }
                    $data['title']   = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                    if(is_object(  $data['title']))    $data['title']=   $data['title']->__toString();
                    $data['label']  = $sheet->getCellByColumnAndRow(2, $row)->getValue();
                    $data['thumb']  = empty($sheet->getCellByColumnAndRow(3, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(3, $row)->getValue();
                    $data['create_date']  = empty($sheet->getCellByColumnAndRow(4, $row)->getValue()) ?date('Y-m-d H:i:s'):
                        date('Y-m-d H:i:s',strtotime($sheet->getCellByColumnAndRow(4, $row)->getValue()));
                    $data['check_date']  = empty($sheet->getCellByColumnAndRow(5, $row)->getValue()) ?date('Y-m-d H:i:s'):     date('Y-m-d H:i:s',strtotime($sheet->getCellByColumnAndRow(5, $row)->getValue()));
                    $data['status']  = empty($sheet->getCellByColumnAndRow(6, $row)->getValue()) ?'1':$sheet->getCellByColumnAndRow(6, $row)->getValue();
                    $data['href']  = empty($sheet->getCellByColumnAndRow(7, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(7, $row)->getValue();
                    $data['comments']  =  empty($sheet->getCellByColumnAndRow(8, $row)->getValue()) ?0:$sheet->getCellByColumnAndRow(8, $row)->getValue();

                    $data['watchtimes']  = empty($sheet->getCellByColumnAndRow(9, $row)->getValue()) ?0:$sheet->getCellByColumnAndRow(9, $row)->getValue();
                    $data['likes']  = empty($sheet->getCellByColumnAndRow(10, $row)->getValue()) ?0:$sheet->getCellByColumnAndRow(10, $row)->getValue();
                    $data['duration']  = empty($sheet->getCellByColumnAndRow(11, $row)->getValue()) ?'00:00:00':$sheet->getCellByColumnAndRow(11, $row)->getValue();
                    $data['collection']  = empty($sheet->getCellByColumnAndRow(12, $row)->getValue()) ?'0':$sheet->getCellByColumnAndRow(12, $row)->getValue();
                    $data['download_times']  = empty($sheet->getCellByColumnAndRow(13, $row)->getValue()) ?'0':$sheet->getCellByColumnAndRow(13, $row)->getValue();
                    $data['download_address'] =empty($sheet->getCellByColumnAndRow(14, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(14, $row)->getValue();
                    $data['desc'] =empty($sheet->getCellByColumnAndRow(15, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(15, $row)->getValue();
                    $data['years']  = empty($sheet->getCellByColumnAndRow(16, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(16, $row)->getValue();
                    $data['region']  = empty($sheet->getCellByColumnAndRow(17, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(17, $row)->getValue();
                    $data['classify']  = empty($sheet->getCellByColumnAndRow(18, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(18, $row)->getValue();
                    $data['price']  = empty($sheet->getCellByColumnAndRow(19, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(19, $row)->getValue();

                    $data['try_watch_time']  = empty($sheet->getCellByColumnAndRow(20, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(20, $row)->getValue();
                    $data['buy_numbers']  = empty($sheet->getCellByColumnAndRow(21, $row)->getValue()) ?'':$sheet->getCellByColumnAndRow(21, $row)->getValue();
                    $data['is_downloadable'] = 1;
                    $data['tenant_id'] = getTenantIds();
                    $data['origin'] = 3;
                    $type  = empty($sheet->getCellByColumnAndRow(22, $row)->getValue()) ?1:$sheet->getCellByColumnAndRow(22, $row)->getValue();
                    if ($type == 2){

                        M("video")->add($data);

                    }else{

                        M("video_long")->add($data);
                    }


                }

               M()->commit();
                $this->success('导入成功');
            }
        }
        $this->display();
    }


    public function reward_rule(){
        $lists =M("uplode_video_rules")->where('tenant_id="'.getTenantIds().'"')->select();
        $this->assign('lists',$lists);
        $this->display();
    }
    public  function reward_rule_add(){
        try{
            if(IS_POST){
                $tenantId=getTenantIds();
                $param=I("post.");
                $data = [
                    'amount' =>$param['amount'] ,
                    'min_time' =>$param['min_time'] ,
                    'max_time' =>$param['max_time'] ,
                    'add_time' => time(),
                    'tenant_id' => $tenantId
                ];

                if (M("uplode_video_rules")->add($data)!==false) {
                    $action="添加成功";
                    setAdminLog($action);
                    $this->success("保存成功！");
                } else {
                    $this->error("保存失败！");
                }
            }else{
                $this->display();
            }
        }catch (\Exception $e){
            setAdminLog('修改租户设置失败：'.$e->getMessage());
            $this->error("保存失败！");
        }
    }
    public  function reward_rule_edit(){
        try{
            if(IS_POST){
                $tenantId=getTenantIds();
                $param=I("post.");
                $data = [
                    'amount' =>$param['amount'] ,
                    'min_time' =>$param['min_time'] ,
                    'max_time' =>$param['max_time'] ,
                    'add_time' => time(),
                    'tenant_id' => $tenantId,
                ];
                if (M("uplode_video_rules")->where(['id'=> $param['id']])->save($data)!==false) {
                    $action="添加成功";
                    setAdminLog($action);
                    $this->success("保存成功！");
                } else {
                    $this->error("保存失败！");
                }
            }else{
                $id=intval($_GET['id']);
                if(!$id){
                    $this->error('缺少关键ID');
                }
               $rule =   M("uplode_video_rules")->where(['id'=> $id])->find();
                $this->assign('rule', $rule);
                $this->assign('id',$id);
                $this->display();
            }
        }catch (\Exception $e){
            setAdminLog('修改租户设置失败：'.$e->getMessage());
            $this->error("保存失败！");
        }
    }
    public  function reward_rule_del(){
        $id=intval($_GET['id']);
        if($id){
            $result=M("uplode_video_rules")->delete($id);
            if($result){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
    }
}
