<?php

/**
 * 短视频
 */
namespace Admin\Controller;
use Admin\Model\UsersModel;
use Common\Controller\AdminbaseController;
use Composer\Package\Loader\ValidatingArrayLoader;
use QCloud\Cos\Api;
use QCloud\Cos\Auth;
use PHPExcel\PHPExcel\APC;


class LongvideoController extends AdminbaseController {
    protected $users_model,$role_model;
  function _initialize() {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->role_model = D("Common/VideoLabelLong");
    }
    /*待审核视频列表*/
    function index(){
        $param = I('param.');


     /*   if($_REQUEST['keyword']!=''){
            $map['title']=array("like","%".$_REQUEST['keyword']."%");
            $_GET['keyword']=$_REQUEST['keyword'];
        }*/
        if($_REQUEST['id']!=''){
            $map['id']= $_REQUEST['id'];
            $_GET['id']=$_REQUEST['id'];
        }

        if($_REQUEST['user_id']!=''){
            $map['uid']= $_REQUEST['user_id'];
            $_GET['user_id']=$_REQUEST['user_id'];
        }
        if($_REQUEST['user_login']!=''){
            $map['user_login']= $_REQUEST['user_login'];
            $_GET['user_login']=$_REQUEST['user_login'];
        }
        if($_REQUEST['start_time']!=''){
            $map['create_date']=array("gt",($_REQUEST['start_time']));
            $_GET['start_time']=$_REQUEST['start_time'];
        }

        if($_REQUEST['end_time']!=''){

            $map['create_date']=array("lt",($_REQUEST['end_time']));
            $_GET['end_time']=$_REQUEST['end_time'];
        }
        if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){

            $map['create_date']=array("between",array(($_REQUEST['start_time']),($_REQUEST['end_time'])));
            $_GET['start_time']=$_REQUEST['start_time'];
            $_GET['end_time']=$_REQUEST['end_time'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = array('in',[2,3]);
            $param['user_type'] = -1;
        }

        $labels=$this->role_model->where("is_delete=1 and type=1")->order("id desc")->select();
        if($_REQUEST['labels']!=''){
            $map['label']=array("eq",($_REQUEST['labels']));
            $_GET['labels']=$_REQUEST['labels'];
        }
        if($_REQUEST['status']!=''){
            if ($_REQUEST['status'] == -1) {
                $map['is_downloadable'] = array("eq", 0);
            }elseif ($_REQUEST['status'] == 1){
                $map['status']=array("eq",($_REQUEST['status']));
                $map['is_downloadable'] = array("eq", 1);
            }else{
                $map['status']=array("eq",($_REQUEST['status']));

            }
            $_GET['status']=$_REQUEST['status'];
        }
        $p=I("p");
        if(!$p){
            $p=1;
        }
        $model = M("video_long");
        $count = $model->where($map)->count();
        $page = $this->page($count, 20);
        $orderstr="sort asc ,create_date DESC";

        $playback_address_info = M('playback_address')
            ->where( array('is_enable'=> 1,'type'=>1,'tenant_id'=> getTenantIds()))
            ->find();
        $lists = $model
            ->where($map)
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        //var_dump($lists);exit;
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
                if($v['user_type'] == 0 && $userinfo['user_type']){
                    $model->where(['id'=>$v['id']])->save(['user_type'=>$userinfo['user_type']]);
                }
                if($v['user_login'] == '' && $userinfo['user_login']){
                    $model->where(['id'=>$v['id']])->save(['user_login'=>$userinfo['user_login']]);
                }
            }
            if ($v['origin']!=3){
                if($playback_address_info['name'] == 'minio' && strrpos($v['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $lists[$k]['thumb'] = $v['thumb'] ? $playback_address_info['url'].'/liveprod-store-1039'.$v['thumb'] : $v['thumb'];
                }else{
                    $lists[$k]['thumb'] = $v['thumb'] ? $playback_address_info['url'].$v['thumb'] : $v['thumb'];
                }
            }
            if ($v[$playback_address_info['viode_table_field']]){
                $lists[$k]['href'] = geturlType() . $_SERVER['HTTP_HOST'] . $v[$playback_address_info['viode_table_field']];
            }
            if($v['origin']==1){
                $lists[$k]['origin_name']='用户上传';
            }else if ($v['origin']==2){
                $lists[$k]['origin_name']='后台上传';
            }else{
                $lists[$k]['origin_name']='后台手动添加上传';
            }
            $lists[$k]['userinfo']=$userinfo;
            $lists[$k]['user_nicename']=$userinfo['user_nicename'];
            $hasurgemoney=($v['big_urgenums']-$v['urge_nums'])*$v['urge_money'];
            $lists[$k]['hasurgemoney']=$hasurgemoney;
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('labels', $labels);
        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }
    function label(){
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("video_label_long");
        $count=$video_model->where("is_delete=1 and type=1")->count();
        $page = $this->page($count, 20);
        $orderstr="sort asc ,id DESC";


        $lists = $video_model
            ->where("is_delete=1 and type=1")
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

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

    function tag(){
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("video_label_long");
        $count=$video_model->where("is_delete=1 and type=2")->count();
        $page = $this->page($count, 20);
        $orderstr="sort asc ,id DESC";


        $lists = $video_model
            ->where("is_delete=1 and type=2")
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

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

    function classify(){
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("video_long_classify");
        $count=$video_model->count();
        $page = $this->page($count, 20);
        $orderstr="sort asc ,id DESC";


        $lists = $video_model
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

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
    function getinfo(){
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
    public  function  addactive(){
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
    function active(){

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
    function Sec2Time($time){
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

    function nopassindex(){

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
    function passindex(){

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
    function del(){

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

        $result=M("users_video")->where("id={$id}")->delete();

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
            M("video_long")->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $this->success('审核成功', U('index',$_REQUEST));
        } else {
            $this->error("排序更新失败！");
        }
    }


    function add(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $labels=M('video_label_long')->where("is_delete=1 and type=1")->order("id desc")->select();
        $performer=M('performer')->field('id,name')->select();;
        $classifyinfo=M('video_long_classify')->field('id,classify')->select();
        $tags =M('video_label_long')->where("is_delete=1 and type=2")->order("id desc")->select();
        $config = getConfigPub($tenant_id);
        $cut_video_url_array = explode("\n",$config['url_of_push_to_java_cut_video']);
        $cut_video_url = $cut_video_url_array[0];
        $url_info = parse_url($cut_video_url);
        $url_is_ip = filter_var($url_info['host'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false ? 0 : 1;
        $this->assign("url_of_push_to_java_cut_video", $cut_video_url);
        $this->assign("url_is_ip", $url_is_ip);
        $this->assign("labels",$labels);
        $this->assign("tags",$tags);
        $this->assign("performer",$performer);
        $this->assign("classifyinfo", $classifyinfo);
        $this->display();
    }

    function add_post(){
        if(IS_POST){
            $video=M("video_long");
            $video->create();
            $title=$_POST['title'];
            $iscoding=$_POST['iscoding'];
            $classify=$_POST['classify'];


            if (!empty($_POST['tourist_time'])){
                $tourist_time =  $_POST['tourist_time'];
            }else{
                $tourist_time =  0;
            }
            $uid =$_POST['owner_uid'];
            if ($iscoding == ""){
                $this->error("请选择是否打码");
            }

            if ($classify == ""){
                $this->error("请选择标签下面分类");
            }

            if($title==""){
                $this->error("请填写视频标题");
            }

            //判断用户是否存在
            $user_info = M("users")->where("user_type in (2,5,6) and id={$uid}")->find();
            if (!$user_info) {
                $this->error("视频所有者不存在");
                return;
            }

            $label= $_POST['labels'];
            $tags = $_POST['tag'];
            if(count($tags)>5){
                $this->error("三级标签最多只能选择5个");
            }
            var_dump($tags);exit;
            $performer = $_POST['performer'];
            $videoinfo = getCutvideo($user_info['tenant_id']);
            //java端返回的m3u8文件内容
            $hrefcontent = base64_decode($videoinfo['data']['m3u8Str']);
            //文件名称
            $file_name = date('YmdHis',time()).random(5,10000000);
            //写入m3u8
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/test/".$file_name.".m3u8",$hrefcontent);
            $video_href = 'http://'.$_SERVER['SERVER_NAME'].'/test/'.$file_name.".m3u8";
            $arr['href'] = $video_href;
            if ($_POST['create_date']){
                $arr['create_date'] = $_POST['create_date'];
            }else{
                $arr['create_date']= date('Y-m-d H:i:s',time());
            }

            $arr['title']= $title;
            $arr['tags'] = implode(',',$tags);
            $arr['thumb']= $videoinfo['data']['coverImgUrl'];
            $arr['label']= $label;
            $arr['origin']= '2';
            $arr['classify']= $classify;
            $arr['iscoding']= $iscoding;
            $arr['uid']  = $uid;
            $arr['performer']= $performer;
            $arr['tourist_time']= $tourist_time;

            if (isset($videoinfo['data']['playTime'])){
                $arr['duration'] = $videoinfo['data']['playTime'];
            }

            $is_performer = 0;
            if ($performer){
                $is_performer = 1;
            }
            $arr['is_performer'] =$is_performer;
            $arr['is_downloadable'] = 0;
            $arr['filestorekey'] = $videoinfo['data']['fileStoreKey'];
            $videoid =  $video->add($arr);
            if ($_POST['banner_status'] == 1){
                $bannerData = array(
                    'img' => $arr['thumb'],
                    'title' => $arr['title'],
                    'video_id' => $videoid,
                    'status' => 1,
                    'addtime' => time(),
                    'label'  => $label,
                    'tags' => $tags,
                );
                if ($arr['thumb']){
                    $banner =M("long_video_banner");
                    $banner->create();
                    $banner->add($bannerData);
                }

            }
            $result = true;

            if($result){
                $this->success('添加成功','Admin/Longvideo/add',3);
            }else{
                $this->error('添加失败');
            }

        }
    }

    public function updatefile()
    {
        if(IS_POST){
            $param = I('post.');
            $video = M("video_long");
            $video->create();
            $title = $_POST['title'];
            if ($title == "") {
                $this->error("请填写视频标题");
            }
            if(!$param['fileStoreKey']){
                $this->error("请上传视频");
            }
            $owner_uid = $_POST['owner_uid'];
            $iscoding=$_POST['iscoding'];
            $classify=$_POST['classify'];
            $tags = $_POST['tag'];
            if ($owner_uid == "" || !is_numeric($owner_uid)) {
                $this->error("请填写视频所有者id");
                return;
            }
            if ($iscoding == ""){
                $this->error("请选择是否打码");
            }

            if ($classify == ""){
                $this->error("请选择标签下面分类");
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
            if(count($tags)>5){
                $this->error("三级标签最多只能选择5个");
            }
            $video->uid = $owner_uid;

            $label = $_POST['labels'];
            
            $performer = $_POST['performer'];
            $price = trim($_POST['price']);
            $try_watch_time = trim($_POST['try_watch_time']);
            $years = trim($_POST['years']);
            $region = trim($_POST['region']);
            $desc = trim($_POST['desc']);
            $watchtimes   =$_POST['watchtimes'];
            $arr['vip_rate'] = $_POST['vip_rate'];
            $arr['shot_status'] = $_POST['shot_status'];
            if ($_POST['fileStoreKey']){
                $arr['filestorekey'] = $_POST['fileStoreKey'];
                $arr['status'] = '1';//等待上传
                if ($_POST['create_date']){
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
                $arr['tags'] = implode(',',$tags);
                $arr['user_login'] = $ownerInfo['user_login'];
                $arr['user_type'] = intval($ownerInfo['user_type']);
                $arr['tenant_id'] = intval($ownerInfo['tenant_id']);
                $arr['title'] = $title;
                $arr['years'] = $years;
                $arr['region'] = $region;
                $arr['desc'] = $desc;
                $arr['label'] = $label;
                $arr['origin'] = '2';
                $arr['iscoding'] = $iscoding;
                $arr['classify'] = $classify;
                $arr['price'] = $price;
                $arr['try_watch_time'] = $try_watch_time;
                $arr['watchtimes'] = $watchtimes;

                $arr['performer'] = $performer;
                $is_performer = 0;
                if ($performer) {
                    $is_performer = 1;
                }
                $arr['is_performer'] = $is_performer;
                $videoId = $video->add($arr);

                $this->success('添加成功');
            }else{
                $this->error('上传失败');
            }

        }
    }

    function edit(){
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

    function edit_post(){
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

    function reportlist(){

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
            $userinfo=$Users->field("user_nicename")->where("id='{$v[uid]}'")->find();
            if(!$userinfo){
                $userinfo=array(
                    'user_nicename'=>'已删除'
                );
            }
            $lists[$k]['userinfo']= $userinfo;
            $touserinfo=$Users->field("user_nicename")->where("id='{$v[touid]}'")->find();
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

    function setstatus(){
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
    function report_del(){
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

    function reportset(){
        $report=M("users_video_report_classify");
        $lists = $report
            ->order("orderno ASC")
            ->select();

        $this->assign('lists', $lists);
        $this->display();
    }
    //添加举报理由
    function add_report(){

        $this->display();
    }
    function add_reportpost(){

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
    function edit_report(){
        $id=intval($_GET['id']);
        if($id){
            $reportinfo=M("users_video_report_classify")->where("id={$id}")->find();

            $this->assign('reportinfo', $reportinfo);
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function edit_reportpost(){
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
    function del_report(){
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
            $labelInfo=M("video_label_long")->where(array('id'=>$id,"type"=>1))->find();
        }
        $this->assign("info",$labelInfo);
        $this->display();

    }

    public function addtag(){
        $id=intval($_GET['id']);
        $labelInfo= array();
        if ($id){
            $labelInfo=M("video_label_long")->where(array('id'=>$id,"type"=>2))->find();
        }
        $this->assign("info",$labelInfo);
        $this->display();
    }
    


 

    public function add_label(){

        if(IS_POST){
            $video_classify=M("video_long_classify");
            $video_classify->create();
            $video=M("video_label_long");
            $video->create();
            $sort=$_POST['sort'];
            $label=$_POST['label'];
            if($_POST['type'] == 2){
                $sort=0;
                $arr['type']=2;
            }
            
            $owner_uid=$_POST['owner_uid'];
            if($label==""){
                $this->error("请填写标签名称");
                return;
            }
            /*  if($owner_uid==""||!is_numeric($owner_uid)){
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
            $arr['sort']= $sort;
            $arr['is_delete']= '1';
            if (isset($_POST['id']) && !empty($_POST['id'])){
                $labelInfopre=M("video_label_long")->where("is_delete=1 and id='"."{$_POST['id']}"."'")->find();

                $labelInfo=M("video_label_long")->where("is_delete=1 and label='"."{$label}"."' and id != '{$_POST['id']}'")->find();
                if($labelInfo){
                    $this->error("该标签已存在");
                    return;
                }
                $video->where(array('id' =>$_POST['id'] ))->save($arr);


                $classinfo  = array("label"=>$label);
                $video_classify->where(array('label' =>$labelInfopre['label'] ))->save($classinfo);
                $result = true;
                if($result){
                    if($_POST['type']==2){
                        $this->success('添加成功','/Admin/Longvideo/tag',3);
                    }else{
                        $this->success('添加成功','/Admin/Longvideo/label',3);
                    }
                    
                }else{
                    $this->error('修改失败');
                }
            }else{
                $labelInfo=M("video_label_long")->where("is_delete=1 and label='"."{$label}"."'")->find();
                if($labelInfo){
                    $this->error("该标签已存在");
                    return;
                }


                $video->add($arr);
                $result = true;
                if($result){
                    if($_POST['type']==2){
                        $this->success('添加成功','/Admin/Longvideo/tag',3);
                    }else{
                        $this->success('添加成功','/Admin/Longvideo/label',3);
                    }
                    
                }else{
                    $this->error('添加失败');
                }
            }



        }
    }
    public function getclassify(){
        $labels = $_POST['labels'];

        $ownerInfo=M("video_long_classify")->where("label='{$labels}'")->select();
        $res['code'] = 0;
        $res['msg'] = '获取成功';
        $res['info']= array_values($ownerInfo);
        echo json_encode($res);exit;
    }
    public function addclassify(){
        $labels=$this->role_model->where("is_delete=1")->order("id desc")->select();
        $id=intval($_GET['id']);
        $classifyinfo = array();
        if ($id){
            $classifyinfo=M("video_long_classify")->where(array('id'=>$id))->find();
        }
        $this->assign("info",$classifyinfo);
        $this->assign("labels",$labels);
        $this->display();

    }
    public function add_classify(){

        if(IS_POST){
            // var_dump($_POST);exit;
            $video=M("video_long_classify");
            $video->create();

            $label=$_POST['labels'];
            $classify=$_POST['classify'];
            /*  $owner_uid=$_POST['owner_uid'];*/
            $sort=$_POST['sort'];
            if($label==""){
                $this->error("请填写标签名称");
                return;
            }
            /*  if($owner_uid==""||!is_numeric($owner_uid)){
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
            //$arr['uid']=$owner_uid;
            $arr['label']= $label;
            $arr['is_delete']= '1';
            $arr['classify']= $classify;
            $arr['sort']  = $sort;
            $arr['model_type']  = $_POST['model_type'];
            $arr['ad_isopen']  = $_POST['ad_isopen'];
            $arr['thumb_first']= $_POST['thumb_first'];
            $arr['thumb_second']= $_POST['thumb_second'];
            $arr['thumb_third']  = $_POST['thumb_third'];
            $arr['ad_url']  = $_POST['ad_url'];

            if (isset($_POST['id']) && !empty($_POST['id'])){
                $labelInfo=M("video_long_classify")->where("is_delete=1 and label='"."{$label}"."' and  classify='"."{$classify}"."' and id != '{$_POST['id']}'")->find();
                if($labelInfo){
                    $this->error("该标签已存在");
                    return;
                }
                $video->where(array('id' =>$_POST['id'] ))->save($arr);
                $result = true;
                if($result){
                    $this->success('修改成功','/Admin/Longvideo/classify',3);
                }else{
                    $this->error('修改失败');
                }
            }else{
                $labelInfo=M("video_long_classify")->where("is_delete=1 and label='"."{$label}"."' and  classify='"."{$classify}"."'")->find();
                if($labelInfo){
                    $this->error("该标签已存在");
                    return;
                }

                $video->add($arr);
                $result = true;

                if($result){
                    $this->success('添加成功','/Admin/Longvideo/classify',3);
                }else{
                    $this->error('添加失败');
                }
            }


        }
    }
    public function pass(){
        $id=intval($_GET['id']);
        $pass= $_GET['pass'];

        if($id){
            $redis = connectRedis();
            $vidoeId = $redis->get('longvideo_'.$id);
            if ($vidoeId){
                $this->error('视频状态有误');
            }else{
                $redis->set('longvideo_'.$id, time(), 60*60);
            }
            $data['id'] = $id;
            $data['check_date'] = date('Y-m-d H:i:s',time());
            $data['status'] = $pass;
            $data['operated_by'] = get_current_admin_user_login();
            $videoInfo = M("video_long")->where(['id' => $id])->field('uid,is_downloadable')->find();// 查找视频用户

            if ($pass == 2){
                if ($videoInfo['is_downloadable'] != 1){
                    $redis->del('longvideo_'.$id);
                    $this->error('视频未上传完成');
                }
            }

            $userInfo = UsersModel::getInstance()->getUserInfoWithIdAndTid($videoInfo['uid']);

            $result=M("video_long")->save($data);

            if($result) {
                if ($userInfo['user_type'] == 7) { // 测试账号，审核后不做资金变动的处理
                    $redis->del('longvideo_' . $id);
                    setAdminLog("【长视频审核-成功】{$id}");
                    $this->success('审核成功');
                }
                if ($pass == 2) {

                    $config = getConfigPub($userInfo['tenant_id']);
                    $map['endtime'] = array("egt", (time()));
                    $map['uid'] = array("eq", ($videoInfo['uid']));
                    $pre_balance = $userInfo['coin'];
                    $after_balance = bcadd($userInfo['coin'], 2, 4);
                    M("users")->where(array('id' => $videoInfo['uid']))->save(['coin' => $after_balance]);

                    $rewardData = [
                        'video_id' => $id,
                        'video_type' => 2,
                        'uid' => $videoInfo['uid'],
                        'user_type' => $userInfo['user_type'],
                        'add_time' => time(),
                        'price' => 2,
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
                        'totalcoin' => 2,//金额
                        "after_balance" => floatval($after_balance),
                        "giftcount" => 1,
                        'is_withdrawable' => 1,
                    ];
                    $this->addCoinrecord($coinrecordData);
                    delUserInfoCache($videoInfo['uid']);
                }

                setAdminLog("【长视频审核-成功】{$id}");
                $redis->del('longvideo_' . $id);
                $this->success('审核成功');
            } else {
                    setAdminLog("【长视频审核-失败】{$id}");
                    $redis->del('longvideo_' . $id);
                    $this->error('审核失败');
            }

        }else{

            $this->error('数据传入失败！');
        }
        $this->display();
    }
    public function deletelabel(){
        $id=intval($_GET['id']);

        if($id){
            $result=M("video_label_long")->delete($id);

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
    public function deleteclassify(){
        $id=intval($_GET['id']);

        if($id){
            $result=M("video_long_classify")->delete($id);

            if($result){
                $action="longvideo classify delete success：{$id}";
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

    public  function labelsort(){
        $ids = $_POST['sort'];

        foreach ($ids as $key => $r) {
            $data['sort'] = $r;
            M("video_label_long")->where(array('id' => $key,'type'=>1))->save($data);
        }

        $status = true;
        if ($status) {
            $action="更新长视频标签排序";
            setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
    public  function classifysort(){
        $ids = $_POST['sort'];

        foreach ($ids as $key => $r) {
            $data['sort'] = $r;
            M("video_long_classify")->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $action="更新长视频标签排序";
            setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
    public  function add_address(){
        $videoInfo  = array();
        $id = '';
        $classify = array();
        if ($_GET['id']){
            $id  = $_GET['id'];
            $video = M("video_long");
            $videoInfo = $video->where(array('id'=>$id))->find();
            $classify=M("video_long_classify")->where("label='{$videoInfo['label']}'")->select();
        }
        $labels=$this->role_model->where("is_delete=1 and type=1")->order("id desc")->select();
        if ($videoInfo['origin'] != 3){
            $playback_address= M('playback_address')->where(array('is_enable'=>'1','type'=>1))->find();
            $videoInfo['href'] =  $videoInfo[$playback_address['viode_table_field']];

            $download_address_line= M('playback_address')->where(array('is_enable'=>'1','type'=>2))->find();
            $videoInfo['download_address'] =  $videoInfo[$download_address_line['viode_table_field']];
        }

        if(!empty($videoInfo['tags'])){
            $videoInfo['tags'] = explode(",",$videoInfo['tags']);
        }

        $tags=$this->role_model->where("is_delete=1 and type=2")->order("id desc")->select();
        $this->assign('tags',$tags);
        $this->assign("labels",$labels);
        $performer=M('performer')->field('id,name')->select();;
        $this->assign("performer",$performer);
        $this->assign("videoInfo",$videoInfo);
        $this->assign("classify",$classify);

        $this->assign("id",$id);
        $this->display();
    }
    public  function add_address_post(){
        if (IS_POST) {
            $video=M("video_long");
            $video->create();
            $title = trim($_POST['title']);
            $thumb = trim($_POST['thumb']);
            $href = trim($_POST['href']);
            $years = trim($_POST['years']);
            $region = trim($_POST['region']);
            $desc = trim($_POST['desc']);
            $download_address = trim($_POST['download_address']);
            $watchtimes = trim($_POST['watchtimes']);
            $duration = $_POST['duration'];
            $playTimeIntArray = explode(':',$duration);
            $playTimeInt = $playTimeIntArray[0] * 24*60*60;
            $playTimeInt+= $playTimeIntArray[1] * 24*60*60;
            $newplayTimeInt = explode('.',$playTimeIntArray[2]);
            $playTimeInt+= $newplayTimeInt[0]*60;
            $playTimeInt+= $newplayTimeInt[1];
            $iscoding=$_POST['iscoding'];
            $classify=$_POST['classify'];
            $price = trim($_POST['price']);
            $try_watch_time = trim($_POST['try_watch_time']);
            $vip_rate = trim($_POST['vip_rate']);
            $shot_status = trim($_POST['shot_status']);
            $tags = $_POST['tag'];

            if ($iscoding == ""){
                $this->error("请选择是否打码");
            }

            if(count($tags)>5){
                $this->error("三级标签最多只能选择5个");
            }

            if ($classify == ""){
                $this->error("请选择标签下面分类");
            }

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
            $ownerInfo = M("users")->where("user_type in (2,3,4,5,6,7) and id={$owner_uid}")->find();
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

            $label = $_POST['labels'];
            $performer = $_POST['performer'];
            $arr['playTimeInt'] = $playTimeInt;
            $arr['uid'] = $owner_uid;
            $arr['user_login'] = $ownerInfo['user_login'];
            $arr['tenant_id'] = intval($ownerInfo['tenant_id']);
            $arr['thumb'] = $thumb;
            $arr['title'] = $title;
            $arr['watchtimes'] = $watchtimes;
            $arr['duration'] = $duration;
            $arr['tags'] = implode(',',$tags);
            if ($_POST['create_date']){
                $arr['create_date'] = date('Y-m-d H:i',strtotime($_POST['create_date'])).':'.date('s');
            }
            $arr['label']= $label;
            $arr['years'] = $years;
            $arr['region'] = $region;
            $arr['desc'] = $desc;
            $arr['classify']= $classify;
            $arr['iscoding']= $iscoding;
            $arr['performer'] = $performer;
            $arr['price'] = $price;
            $arr['try_watch_time'] = $try_watch_time;
            $arr['vip_rate'] = $vip_rate;
            $arr['shot_status'] = $shot_status;
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

            try{
                if ($_POST['id']){
                    $arr['operated_by'] = get_current_admin_user_login();
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
                    $videoId = $video->where(array('id'=>$_POST['id'] ))->save($arr);
                    $result = true;

                    if ($result) {
                        $this->success('修改成功');
                    } else {
                        $this->error('修改失败');
                    }
                }else{
                    if (strtolower(end($download_address_array)) != 'mp4' ){
                        $this->error("下载地址为mp4的地址");
                    }
                    $arr['user_type'] = intval($ownerInfo['user_type']);
                    $arr['href'] = $href;
                    $arr['download_address'] = $download_address;
                    $arr['is_downloadable'] = 1;
                    $arr['status'] = '1';
                    $arr['origin']= '3';//后台手动上传
                    $arr['create_date'] = date('Y-m-d H:i:s', time());
                    $videoId = $video->add($arr);
                    $result = true;

                    if ($result) {
                        $this->success('添加成功');
                    } else {
                        $this->error('添加失败');
                    }
                }
            }catch (\Exception $e){
                $action="长视频操作失败: ".$e->getMessage();
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
        $videoInfo=M("video_long")->where("id={$id}")->find();

        $result=M("video_long")->where("id={$id}")->delete();

        //$result=M("users_video")->where("id={$id}")->setField("isdel","1");

        if($result!==false){
            M("users_video_comments")->where(array('videoid'=> $id,'type' =>2))->delete();	 //删除视频评论
            M("users_video_like")->where(array('videoid'=> $id,'type' =>2))->delete();	 //删除视频喜欢
            M("users_video_comments_like")->where(array('videoid'=> $id,'type' =>2))->delete(); //删除视频评论喜欢
            M("users_video_comments_like")->where(array('videoid'=> $id,'type' =>2))->delete(); //删除视频评论喜欢
            M("video_watch_record")->where(array('videoid'=> $id,'type' =>2))->delete(); //观看次数
            M("video_download")->where(array('videoid'=> $id,'type' =>2))->delete(); //下载
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
            $this->success('删除成功');

        }else{
            $this->error('删除失败');
        }

    }
    /**
     * @param $uid 用户id
     * @param $price  金额
     * @param $giftid  操作id
     * @param $type  //  income 可提现 ， income_nowithdraw不可提余额
     * @param $is_withdrawable  1可提现  2  不可提现
     * @param $action   agent_buy_video  购买视频代理收益
     * @param $agentType    1  任务  ，2 购买视频  ， 3 点赞视频，4 上传视频'
     * @return bool
     */
    public  function AgencyCommission($uid,$price,$giftid, $type,$is_withdrawable,$action,$agentType ){
        $RebateConf = getAgentRebateConf(getTenantIds());
        if(!$RebateConf){
            return  true;
        }
        $RebateConfByLevel =  array_column($RebateConf,null,'level');
        $config  =getConfigPub();
        if (!$config['agent_sum']){
            return  true;
        }
        $userinfo=getUserInfo($uid);
        $uids = explode(',',$userinfo['pids']);
        unset($uids[0]);
        if (empty($uids)){
            return  true;
        }
        if ($config['agent_sum']< count($uids)){
            $uids = array_slice($uids,-$config['agent_sum']);
        }
        $uids  =  array_reverse($uids);

        $redis = connectRedis();
        $i = 0;
        foreach ($uids as $key =>$value){
            $agentInfo = UsersModel::getInstance()->getUserInfoWithIdAndTid($value);
            if($agentInfo['user_type'] == 7){ // 测试账号，不做逻辑处理，直接返回
                continue;
            }
            $rebate = bcmul($price,$RebateConfByLevel[$key+1]['rate']/100,2);
            if ($rebate> 0) {
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
                delUserInfoCache($value);
                if ($is_withdrawable == 1) {
                    M('users')->where(['id' => $value])->save(['agent_total_income' => ['exp', 'agent_total_income+' . $rebate], 'coin' => ['exp', 'coin+' . $rebate]]);
                } else {
                    M('users')->where(['id' => $value])->save(['agent_total_income' => ['exp', 'agent_total_income+' . $rebate], 'nowithdrawable_coin' => ['exp', 'nowithdrawable_coin+' . $rebate]]);
                    $keytime = time();
                    $redis->lPush($value . '_reward_time', $keytime);// 存用户 时间数据key
                    $amount = $redis->get($value . '_' . $keytime);
                    $totalAmount = bcadd($rebate, $amount, 2);
                    $redis->set($value . '_' . $keytime, $totalAmount);// 存佣金
                    $expireTime = time() + $config['withdrawal_time'] * 86400;
                    $redis->expireAt($value . '_' . $keytime, $expireTime);// 设置过去时间
                }
                $agentRewardId = M('agent_reward')->add($agentData);// 代理记录
                $coinrecordData = [
                    'type' => $type,
                    'uid' => $value,
                    "user_login" => $agentInfo['user_login'],
                    'user_type' => $agentInfo['user_type'],
                    'giftid' => $agentRewardId,
                    'addtime' => time(),
                    'tenant_id' => $agentInfo['tenant_id'],
                    'action' => $action,
                    "pre_balance" => floatval($agentInfo['coin']),
                    'totalcoin' => $rebate,//金额
                    "after_balance" => floatval(bcadd($agentInfo['coin'], $rebate,4)),
                    "giftcount" => 1,
                    'is_withdrawable' => $is_withdrawable,
                ];
                $i++;
                $this->addCoinrecord($coinrecordData); //  账变记录
                delUserInfoCache($value);
            }
        }
        return true;
    }
    public  function batchPass(){
        $ids = $_POST['ids'];
        foreach ($ids as $key => $id) {
            if($id){
                $redis = connectRedis();
                $vidoeId = $redis->get('longvideo_'.$id);
                if ($vidoeId){
                    $this->error('视频状态有误');
                }else{
                    $redis->set('longvideo_'.$id,time(), 60*60);
                }
                $videoInfo = M("video_long")->where(['id'=>$id])->field('uid,is_downloadable')->find();// 查找视频用户
                if ($videoInfo['is_downloadable'] != 1){
                    $redis->del('longvideo_'.$id);
                    continue;
                }
                $userInfo = UsersModel::getInstance()->getUserInfoWithIdAndTid($videoInfo['uid']);

                $data['id'] = $id;
                $data['check_date'] = date('Y-m-d H:i:s',time());
                $data['status'] =  2;
                $result=M("video_long")->save($data);
                if($result ){
                    if($userInfo['user_type'] == 7){ // 测试账号，审核后不做资金变动的处理
                        $redis->del('longvideo_'.$id);
                        setAdminLog("【长视频审核-成功】{$id}");
                        continue;
                    }
                    $config=getConfigPub($userInfo['tenant_id']);
                    $map['endtime']=array("egt",(time()));
                    $map['uid']=array("eq",($videoInfo['uid']));
                    $userVip = M("users_vip")
                    ->where($map)
                    ->order('grade desc')
                    ->find();

                    $vipInfo  = M("vip_grade")
                    ->where(['vip_grade'=> $userVip['grade']])
                    ->find();
                    if ($videoInfo['uid']&& $vipInfo['uplode_video_amount']>0){
                        $VideoAwardCount  =M("video_uplode_reward")->where(['uid'=>$videoInfo['uid']])->count();
                        $rewardInfo  = M('video_uplode_reward')->where(['video_type'=>1,'video_id'=>$id ])->find();
                        if (empty($rewardInfo) && $VideoAwardCount < $vipInfo['uplode_video_num']){ //没有获取过奖励
                            if ($vipInfo['uplode_video_amount']>0){
                                if ($config['video_uplode_amount_type'] ==1){ // 可提现
                                    M("users")->where(['id'=> $videoInfo['uid']])->setInc('coin',$vipInfo['uplode_video_amount']);
                                    $pre_balance = $userInfo['coin'];
                                    $after_balance = bcadd($userInfo['coin'], $vipInfo['uplode_video_amount'],4);
                                    $is_withdrawable =1;
                                    $type = 'income';
                                } else{
                                    M("users")->where(['id'=> $videoInfo['uid']])->setInc('nowithdrawable_coin',$vipInfo['uplode_video_amount']);
                                    $pre_balance = $userInfo['nowithdrawable_coin'];
                                    $after_balance = bcadd($userInfo['nowithdrawable_coin'], $vipInfo['uplode_video_amount'],4);
                                    $is_withdrawable =2;
                                    $type = 'income_nowithdraw';
                                    $redis = connectRedis();
                                    $keytime = time();
                                    $redis->lPush( $videoInfo['uid'].'_reward_time',$keytime);// 存用户 时间数据key
                                    $amount = $redis->get($videoInfo['uid'].'_'.$keytime);
                                    $totalAmount  = bcadd($vipInfo['uplode_video_amount'],$amount,2);
                                    $redis->set($videoInfo['uid'].'_'.$keytime,$totalAmount);// 存佣金
                                    $expireTime= time()+ $config['withdrawal_time']  * 86400;
                                    $redis->expireAt($videoInfo['uid'].'_'.$keytime, $expireTime);// 设置过去时间
                                }
                                delUserInfoCache($videoInfo['uid']);
                                $rewardData = [
                                    'video_id' => $id,
                                    'video_type' => 2,
                                    'uid' => $videoInfo['uid'],
                                    'user_type' => $userInfo['user_type'],
                                    'add_time' => time(),
                                    'price' => $vipInfo['uplode_video_amount'],
                                ];
                                $reward_id =   M('video_uplode_reward')->add($rewardData);
                                $coinrecordData = [
                                    'type' => $type,
                                    'uid' => $videoInfo['uid'],
                                    "user_login" => $userInfo['user_login'],
                                    'user_type' => $userInfo['user_type'],
                                    'giftid' =>$reward_id ,
                                    'addtime' => time(),
                                    'tenant_id' => $userInfo['tenant_id'],
                                    'action' => 'video_uplode_reward',
                                    "pre_balance" => floatval($pre_balance),
                                    'totalcoin' => $vipInfo['uplode_video_amount'],//金额
                                    "after_balance" => floatval($after_balance),
                                    "giftcount" => 1,
                                    'is_withdrawable' => $is_withdrawable,
                                    ];
                                $this->addCoinrecord($coinrecordData);
                                $this->AgencyCommission($videoInfo['uid'],$vipInfo['uplode_video_amount'],$reward_id,$type, $is_withdrawable, 'agent_video_uplode_reward',4 );
                            }
                        }
                    }
                    setAdminLog("【长视频审核-成功】{$id}");
                    $redis->del('longvideo_'.$id);
              }else{
                    setAdminLog("【长视频审核-失败】{$id}");
                    $redis->del('longvideo_'.$id);
              }
          }

        }
        $this->success('审核成功', U('index',$_REQUEST));


    }
    public  function batchFail(){
        $ids = $_POST['ids'];
        if (empty($ids)){
            return $this->error('请勾选数据');

        }
        $result=M("video_long")->where(['id'=>['in',$ids], 'operated_by' => get_current_admin_user_login()])->save(['status'=>3]);
        if ($result !== false){
            return    $this->success('操作成功', U('index',$_REQUEST));
        }else{
            return $this->success('操作失败');
        }
    }

    public  function batchDel(){
        $ids = $_POST['ids'];
        if (empty($ids)) {
            return $this->error('请勾选数据');
        }
        $result=M("video_long")->where(['id'=>['in',$ids]])->delete();
        if ($result !== false){
            return  $this->success('操作成功', U('index',$_REQUEST));
        }else{
            return $this->success('操作失败');
        }
    }
    public  function showfirst(){
        $type = $_GET['type'];
        $url = '/public/images/type'.$type.".png";
       
        $this->assign('url', $url);

        $this->display('showfirst');
    }

}
