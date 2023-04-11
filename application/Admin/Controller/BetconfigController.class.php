<?php

/**
 * 彩种新增表
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Composer\Package\Loader\ValidatingArrayLoader;
use QCloud\Cos\Api;
use QCloud\Cos\Auth;
use PHPExcel\PHPExcel\APC;
use Admin\Cache\BetConfigCache;

class BetconfigController extends AdminbaseController {
    protected $users_model,$role_model;
    function _initialize() {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->tenamt_model = D("Common/Tenant");
    }
    /*直播氛围列表*/
    public function index(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $p=I("p");
        if(!$p){
            $p=1;
        }

        $map['tenant_id'] = $tenant_id;
        if(isset($param['name']) && $param['name'] != ''){
            $map['name'] = $param['name'];
        }

        $count = M("bet_config")->where($map)->count();
        $page = $this->page($count, 20);
        $orderstr="id DESC";


        $lists = M("bet_config")
            ->order($orderstr)
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach($lists as $k=>$v){
            $lists[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
        }

        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);

        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->display();
    }

    public function addbetconfig(){

        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('tenant_id',getTenantIds());
        $this->display();

    }

    public function add_betconfig(){
        if(IS_POST) {
            $param = I('post.');
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            $tenant_info = getTenantList($tenant_id);

            $name = $_POST['name'];
            $playname = $_POST['playname'];

            if ($name == "") {
                $this->error("彩种名称");
            }
            if ($playname == "") {
                $this->error("彩种配置");
            }
            $param['loss_rate'] = trim(trim(trim($param['loss_rate']),','));
            foreach (explode(',',$param['loss_rate']) as $key=>$val){
                if(!$val || !floatval($val)){
                    $this->error("彩种赔率输入错误");
                }
            }

            $data = array(
                'name' => trim($name),
                'playname' => html_entity_decode(trim($playname)),
                'loss_rate' => $param['loss_rate'],
                'tenant_id' => $tenant_info['id'],
                'tenant_name' => $tenant_info['name'],
                'game_tenant_id' => $tenant_info['game_tenant_id'],
                'act_uid' => get_current_admin_id(),
                'add_time'=>time(),
            );

            $betconfi = M("bet_config");
            $betconfi->create();
            $result = $betconfi->add($data);


            if ($result) {
                BetConfigCache::getInstance()->DelBetConfigListCche($tenant_id); //  彩种配置新增、修改、删除，清除缓存
                $this->success('添加成功', U('index',array('tenant_id'=>$tenant_id)));
            } else {
                $this->error('添加失败');
            }

        }

    }

    function edit(){
        if(IS_POST){
            $param = I("post.");
            $id = $param['id'];
            if(!$id){
                $this->error('id不能为空');
            }
            if ($param['name'] == "") {
                $this->error("彩种名称");
            }
            if ($param['playname'] == "") {
                $this->error("彩种配置");
            }
            $param['loss_rate'] = trim(trim(trim($param['loss_rate']),','));
            foreach (explode(',',$param['loss_rate']) as $key=>$val){
                if(!$val || !floatval($val)){
                    $this->error("彩种赔率输入错误");
                }
            }

            $info = M("bet_config")->where(["id"=>$param['id']])->find();
            $tenant_info = getTenantList($info['tenant_id']);
            $tenant_id = $info['tenant_id'];

            $data = array(
                'name' => trim($param['name']),
                'playname' => html_entity_decode(trim($param['playname'])),
                'loss_rate' => $param['loss_rate'],
                'tenant_name' => $tenant_info['name'],
                'game_tenant_id' => $tenant_info['game_tenant_id'],
                'act_uid' => get_current_admin_id(),
                'mtime' => time(),
            );

            try {
                $result = M("bet_config")->where(array('id' => $id))->save($data);
            }catch (\Exception $e){
                $this->error('操作失败');
            }
            $action="修改彩种配置记录ID ：{$id}";
            setAdminLog($action);
            BetConfigCache::getInstance()->DelBetConfigListCche($tenant_id); //  彩种配置新增、修改、删除，清除缓存
            $this->success('操作成功', U('index',array('tenant_id'=>$info['tenant_id'])));
        }

        $id=intval($_GET['id']);
        if(!$id){
            $this->error('数据传入失败！');
        }

        $info=M("bet_config")->where("id={$id}")->find();

        $this->assign('info', $info);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->display();
    }

    public function updatestatus(){
        $id=intval($_GET['id']);

        if($id){

            $data['status'] = $_GET['status'];
            if ($data['status'] == 1){
                $data['starttime'] = time();
            }
            if ($data['status'] == 2){
                $data['endtime'] = time();
            }
            //开启时候，数据写入redis，给进程判断是否走定时任务
            $result=  M("atmosphere_live")->where(array('id' => $id))->save($data);
            $liveinfo= M("atmosphere_live")->field("*")->where("id='{$id}'")->find();
            $redis = connectionRedis();
            $insetredis = $redis->hSet("atmosphere_".$id,$id,json_encode($liveinfo));


            if($result){
                $action="修改直播氛围记录ID ：{$id}";
                setAdminLog($action);
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error('数据传入失败！');
        }

    }

    function label(){
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("video_label_long");
        $count=$video_model->count();
        $page = $this->page($count, 20);
        $orderstr="id DESC";


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
    function classify(){
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("video_long_classify");
        $count=$video_model->count();
        $page = $this->page($count, 20);
        $orderstr="id DESC";


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
        $id = $_GET['id'];
        if(!$id){
            $res['code']=1001;
            $res['msg']='查询不到该彩种';
            echo json_encode($res);
            exit;
        }
        $info = M("bet_config")->where("id={$id}")->find();
        $tenant_id = $info['tenant_id'];
        $result = M("bet_config")->where("id={$id}")->delete();

        if($result!==false){
            BetConfigCache::getInstance()->DelBetConfigListCche($tenant_id); //  彩种配置新增、修改、删除，清除缓存
            $this->success('彩种删除成功');
        }else{
            $this->error('彩种删除失败');
        }
    }
    //排序
    public function listorders() {

        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("users_video")->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }


    function add(){
        $labels=$this->role_model->where("is_delete=1")->order("id desc")->select();
        // var_dump($roles);exit;
        $this->assign("labels",$labels);
        $this->display();
    }

    function add_post(){
        if(IS_POST){
            $video=M("video_long");
            $video->create();
            $title=$_POST['title'];
            $iscoding=$_POST['iscoding'];
            $classify=$_POST['classify'];

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


            $label= $_POST['labels'];

            //判断用户是否存在
            $user_info = M("users")->where("user_type in (2,5,6) and id={$uid}")->find();
            if (!$user_info) {
                $this->error("视频所有者不存在");
                return;
            }

            $videoinfo = getCutvideo($user_info['tenant_id']);
            //java端返回的m3u8文件内容
            $hrefcontent = base64_decode($videoinfo['data']['m3u8Str']);
            //文件名称
            $file_name = date('YmdHis',time()).random(5,10000000);
            //写入m3u8
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/test/".$file_name.".m3u8",$hrefcontent);
            $video_href = 'http://'.$_SERVER['SERVER_NAME'].'/test/'.$file_name.".m3u8";
            $arr['href'] = $video_href;
            $arr['create_date']= date('Y-m-d H:i:s',time());
            $arr['title']= $title;
            $arr['thumb']= $videoinfo['data']['coverImgUrl'];
            $arr['label']= $label;
            $arr['origin']= '2';
            $arr['classify']= $classify;
            $arr['iscoding']= $iscoding;
            $arr['uid']  = $uid;

            $videoid =  $video->add($arr);
            if ($_POST['banner_status'] == 1){
                $bannerData = array(
                    'img' => $arr['thumb'],
                    'title' => $arr['title'],
                    'video_id' => $videoid,
                    'status' => 1,
                    'addtime' => time(),
                    'label'  => $label,
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

    public function add_label(){

        if(IS_POST){
            $video=M("video_label_long");
            $video->create();

            $label=$_POST['label'];
            $owner_uid=$_POST['owner_uid'];
            if($label==""){
                $this->error("请填写标签名称");
                return;
            }
            if($owner_uid==""||!is_numeric($owner_uid)){
                $this->error("请填写用户id");
                return;
            }
            //判断用户是否存在
            $ownerInfo=M("users")->where("user_type=2 and id={$owner_uid}")->find();
            if(!$ownerInfo){
                $this->error("用户uid不存在");
                return;
            }
            $video->uid=$owner_uid;
            $labelInfo=M("video_label_long")->where("is_delete=1 and label='"."{$label}"."'")->find();
            if($labelInfo){
                $this->error("该标签已存在");
                return;
            }

            $arr['addtime']=time();
            $arr['uid']=$owner_uid;
            $arr['label']= $label;
            $arr['is_delete']= '1';
            $video->add($arr);
            $result = true;

            if($result){
                $this->success('添加成功','/Admin/Longvideo/label',3);
            }else{
                $this->error('添加失败');
            }

        }
    }
    public function getclassify(){
        $labels = $_POST['labels'];

        $ownerInfo=M("video_long_classify")->where("label='{$labels}'")->select();
        $res['code'] = 0;
        $res['msg'] = '获取成功';
        $res['info']= array_values($ownerInfo);
      //  var_dump(json_encode($res));
        echo json_encode($res);exit;
    }
    public function addclassify(){
        $labels=$this->role_model->where("is_delete=1")->order("id desc")->select();
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
            $owner_uid=$_POST['owner_uid'];
            if($label==""){
                $this->error("请填写标签名称");
                return;
            }
            if($owner_uid==""||!is_numeric($owner_uid)){
                $this->error("请填写用户id");
                return;
            }

            //判断用户是否存在
            $ownerInfo=M("users")->where("user_type=2 and id={$owner_uid}")->find();
            if(!$ownerInfo){
                $this->error("用户uid不存在");
                return;
            }
            $video->uid=$owner_uid;

            $labelInfo=M("video_long_classify")->where("is_delete=1 and label='"."{$label}"."' and  classify='"."{$classify}"."'")->find();
          //  $labelInfo=M("video_long_classify")->where("is_delete=1 and label='"."{$label}"."'")->find();
           // var_dump($labelInfo);exit;
            if($labelInfo){
                $this->error("该标签已存在");
                return;
            }

            $arr['addtime']=time();
            $arr['uid']=$owner_uid;
            $arr['label']= $label;
            $arr['is_delete']= '1';
            $arr['classify']= $classify;

            $video->add($arr);
            $result = true;

            if($result){
                $this->success('添加成功','/Admin/Longvideo/classify',3);
            }else{
                $this->error('添加失败');
            }

        }
    }
    public function pass(){
        $id=intval($_GET['id']);
        $pass= $_GET['pass'];
        if($id){
            $data['id'] = $id;
            $data['check_date'] = date('Y-m-d H:i:s',time());
            if($pass==1){
                $data['status'] = 2;
            }else{
                $data['status'] = 1;
            }
            $result=M("video_long")->save($data);

            if($result){
                $action="longvideo pass sucess：{$id}";
                setAdminLog($action);
                $this->success('审核成功');
            }else{
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


}
