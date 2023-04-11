<?php

/**
 * 直播氛围列表
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Composer\Package\Loader\ValidatingArrayLoader;
use QCloud\Cos\Api;
use QCloud\Cos\Auth;
use PHPExcel\PHPExcel\APC;
use Common\Controller\CustRedis;
use Admin\Cache\AtmosphereCache;

class AtmosphereController extends AdminbaseController {
    protected $users_model,$role_model;
    function _initialize() {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->tenamt_model = D("Common/Tenant");
    }

    /*直播氛围列表*/
    public function index(){
        $param = I('param.');
        $map = array();
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = $tenant_id;

        if(isset($param['status']) && $param['status'] != ''){
            $map['status'] = $param['status'];
        }

        $redis = connectionRedis();

        $model = M("atmosphere_live");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $lists = $model->where($map)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach($lists as $k=>$v){
            array_push($ids,$v['id']);
            $lists[$k]['addtime']=date('Y-m-d H:i:s',$v['addtime']);
            $tenantinfo = getTenantInfo($v['tenant_id']);
            $lists[$k]['site'] =  $tenantinfo['site'];

            $config = getConfigPri($v['tenant_id']);
            $lists[$k]['propellingserver'] = $config['propellingserver'];
            $lists[$k]['socket_type'] = $config['socket_type'];
            $lists[$k]['type_name'] = $v['type'] == 1 ? "单个直播间" : "租户所有直播间";
            $lists[$k]['uid'] = $v['uid'] ? $v['uid'] : "";
        }

        $all_id_list = $model->where(1)->field('id')->select();
        $ids = array_column($all_id_list,'id');
        $keys = $redis->hKeys("autosend_atmosphere");
        if(count($keys) > 0){
            foreach($keys as $key=>$val){
                if(!in_array($val,$ids)){
                    $redis->hDel("autosend_atmosphere",$val);
                }
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->display();
    }

    public function updatestatus(){
        $id=intval($_GET['id']);

        if($id){
            $redis = connectionRedis();
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

            $config = getConfigPri($liveinfo['tenant_id']);
            $tenantinfo = getTenantInfo($liveinfo['tenant_id']);

            $uids = [];
            if($liveinfo['type'] == 1 && $liveinfo['uid']){
                array_push($uids,$liveinfo['uid']);
                $TaskConfig = $liveinfo;
                AtmosphereCache::getInstance()->DelAtmosphereCache($liveinfo['uid'], $TaskConfig); // 直播氛围，清除缓存
            }
            if($liveinfo['type'] == 2){
                $user_live_list = getUserLiveList($liveinfo['tenant_id'],1);
                $self_robot_list = M("atmosphere_live")->field("id,uid")->where(['type'=>1])->select();
                $self_robot_list = count($self_robot_list) > 0 ? array_column($self_robot_list,null,'uid') : [];
                foreach ($user_live_list as $key=>$val){
                    if(isset($self_robot_list[$val['uid']])){
                        continue;
                    }
                    array_push($uids,$val['uid']);
                    $TaskConfig = $liveinfo;
                    $TaskConfig['uid'] = $val['uid'];
                    AtmosphereCache::getInstance()->DelAtmosphereCache($val['uid'], $TaskConfig); // 直播氛围，清除缓存
                }
            }

            $r_data['id'] = $id;
            $r_data['httporigin'] = $tenantinfo['site'];
            $r_data['status'] = $data['status'];
            $r_data['socket_type'] = $config['socket_type'];
            $liveinfo['r_data'] = $r_data;
            if($data['status'] == 3){
                $redis->hDel("autosend_atmosphere", $id);
                M("atmosphere_live")->where(array('status' => 3))->delete();
            }else{
                $redis->hSet("autosend_atmosphere", $id ,json_encode($liveinfo));
            }

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

    public function add_atmosphere(){
        if(IS_POST) {
            $param = I("post.");

            $tenamtinfo = explode('_',$param['tenamtinfo']);
            if ($param['rulename'] == "") {
                $this->error("请选择规则名称");
            }
            if ($param['tenamtinfo'] == "") {
                $this->error("请选择租户");
            }

            $data = array(
                'num' => intval($param['num']),
                'enterroom' => intval($param['enterroom']),
                'sendgift' => intval($param['sendgift']),
                'sendbarrage' => intval($param['sendbarrage']),
                'logout' => intval($param['logout']),
                'timebet' => intval($param['timebet']),
                'recommend' => intval($param['recommend']),

                'type' => intval($param['type']),
                'uid' => intval($param['uid']),

                'rulename' => $param['rulename'],
                'tenant_id' => $tenamtinfo[0],
                'game_tenant_id' => $tenamtinfo[1],
                'tenant_name' => $tenamtinfo[2],
                'enterroomparallelnum' => intval($param['enterroomparallelnum']),
                'enterroomrate' => intval($param['enterroomrate']),
                'enterroommininterval' => intval($param['enterroommininterval']),
                'enterroommaxinterval' => intval($param['enterroommaxinterval']),
                'givegiftparallelnum' => intval($param['givegiftparallelnum']),
                'givegiftrate' => intval($param['givegiftrate']),
                'givegiftmininterval' => intval($param['givegiftmininterval']),
                'givegiftmaxinterval' => intval($param['givegiftmaxinterval']),

                'autotalkingparallelnum' => intval($param['autotalkingparallelnum']),
                'autotalkingrate' => intval($param['autotalkingrate']),
                'autotalkingmininterval' => intval($param['autotalkingmininterval']),
                'autotalkingmaxinterval' => intval($param['autotalkingmaxinterval']),
                'leaveroomparallelnum' => intval($param['leaveroomparallelnum']),
                'leaveroomrate' => intval($param['leaveroomrate']),
                'leaveroommininterval' => intval($param['leaveroommininterval']),
                'leaveroommaxinterval' => intval($param['leaveroommaxinterval']),

                'timebetparallelnum' => intval($param['timebetparallelnum']),
                'timebetrate' => intval($param['timebetrate']),
                'timebetmininterval' => intval($param['timebetmininterval']),
                'timebetmaxinterval' => intval($param['timebetmaxinterval']),
                'recomparallelnum' => intval($param['recomparallelnum']),
                'recomrate' => intval($param['recomrate']),
                'recommininterval' => intval($param['recommininterval']),
                'recommaxinterval' => intval($param['recommaxinterval']),

                'status' => 0,
                'addtime'=> time(),
            );


            $atmosphere = M("atmosphere_live");
            $atmosphere->create();
            $result = $atmosphere->add($data);
            //开启时候，数据写入redis，给进程判断是否走定时任务
            $liveinfo= M("atmosphere_live")->field("*")->where("id='{$result}'")->find();
            $redis = connectionRedis();
            $insetredis = $redis->hSet("atmosphere_".$result,$result,json_encode($liveinfo));

            if ($result) {
                $this->success('添加成功','/Admin/Atmosphere/index',3);

            } else {
                $this->error('添加失败');
            }

        }

    }

    public function addatmosphere(){

        $this->assign("tenant_list",getTenantList());
        $this->display();
    }

    public function addreward(){
        $this->display();
    }

    public function rewardedit_post(){
            $param = I("post.");
            $id= $param['id'];
            if ($param['uid'] == "") {
                $this->error("请选择用户ID");
            }

          /*  if ($param['time_start'] == "" || $param['time_end'] == ""   ) {
                $this->error("打赏调整时间必须选择");
            }*/
            if ($param['coin_start'] == "" || $param['coin_end'] == ""  ) {
                $this->error("打赏调整幅度必须选择");
            }
        /*    if ($param['time_start'] < 10   ) {
                $this->error("打赏调整时间必须大于等于10");
            }
            if ( $param['time_end'] >  99999   ) {
                $this->error("打赏调整时间不能大于等于99999");
            }
            if ($param['time_start'] > $param['time_end']   ) {
                $this->error("开始时间不能大于结束时间");
            }*/
            if ($param['coin_start'] < 1   ) {
                $this->error("打赏调整金额必须大于等于1");
            }
            if ( $param['coin_end'] >  999   ) {
                $this->error("打赏金额不能大于等于999");
            }
            if ($param['coin_start'] > $param['coin_end']   ) {
                $this->error("打赏幅度后面金额必须大于前面");
            }
            $liveinfo= M("users")->field("user_nicename")->where("id='{$param['uid']}'")->find();
            if(empty($liveinfo)){
                $this->error("查询不到该会员ID");
            }
            $isexit= M("atmosphere_reward")->field("id,uid")->where("uid='{$param['uid']}'")->select();


            if($isexit){
                if($param['old_uid'] != $param['uid']){
                    $this->error("不能重复添加会员ID");
                }

            }

            $data['uid'] = $param['uid'];
            $data['time_start'] = 0;
            $data['time_end'] = 1;
            $data['coin_start'] = $param['coin_start'];
            $data['coin_end'] = $param['coin_end'];
            $data['user_nicenames'] = $liveinfo['user_nicename'];
            $data['status'] = 1;
            $data['addtime'] = time();

            try {
                $result = M("atmosphere_reward")->where(array('id' => $id))->save($data);
                if($result) {
                    $this->success('编辑成功','/Admin/Atmosphere/rewardlist',3);

                } else {
                    $this->error('修改失败');
                }
            }catch (\Exception $e){
                $this->error('操作失败');
            }
    }

    public function addreward_post(){
      //  var_dump('succ');exit;
        $param = I("post.");
        if ($param['uid'] == "") {
            $this->error("请选择用户ID");
        }

     /*   if ($param['time_start'] == "" || $param['time_end'] == ""   ) {
            $this->error("打赏调整时间必须选择");
        }*/
        if ($param['coin_start'] == "" || $param['coin_end'] == ""  ) {
            $this->error("打赏调整幅度必须选择");
        }
    /*    if ($param['time_start'] < 10   ) {
            $this->error("打赏调整时间必须大于等于10");
        }
        if ( $param['time_end'] >  99999   ) {
            $this->error("打赏调整时间不能大于等于99999");
        }
        if ($param['time_start'] > $param['time_end']   ) {
            $this->error("开始时间不能大于结束时间");
        }*/
        if ($param['coin_start'] < 1   ) {
            $this->error("打赏调整金额必须大于等于1");
        }
        if ( $param['coin_end'] >  999   ) {
            $this->error("打赏金额不能大于等于999");
        }
        if ($param['coin_start'] > $param['coin_end']   ) {
            $this->error("打赏幅度后面金额必须大于前面");
        }

        $liveinfo= M("users")->field("user_nicename")->where("id='{$param['uid']}'")->find();
        if(empty($liveinfo)){
            $this->error("查询不到该会员ID");
        }
        $isexit= M("atmosphere_reward")->field("id")->where("uid='{$param['uid']}'")->select();

        if($isexit){
            $this->error("不能重复添加会员ID");
        }
        $data['uid'] = $param['uid'];
        $data['time_start'] = 0;
        $data['time_end'] = 1;
        $data['coin_start'] = $param['coin_start'];
        $data['coin_end'] = $param['coin_end'];
        $data['user_nicename'] = $liveinfo['user_nicename'];
        $data['status'] = 1;
        $data['addtime'] = time();


        $atmosphere = M("atmosphere_reward");
        $atmosphere->create();
        $result = $atmosphere->add($data);
        if ($result) {
            $this->success('添加成功','/Admin/Atmosphere/rewardlist',3);

        } else {
            $this->error('添加失败');
        }

    }

    /*打赏氛围列表*/
    public function rewardlist(){
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("atmosphere_reward");
        $count=$video_model   ->where('status != 3 ')->count();
        $page = $this->page($count, 20);
        $orderstr="id DESC";


        $lists = $video_model
            ->order($orderstr)
            ->where('status != 3 ')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach($lists as $k=>$v){
            $lists[$k]['addtime']=date('Y-m-d H:i:s',$v['addtime']);

        }



        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->display();
    }

    public function reward_delete(){
        $res=array("code"=>0,"msg"=>"删除成功","info"=>array());
        $id=I("id");
        $result=M("atmosphere_reward")->where("id={$id}")->delete();
        if($result){

            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    public function reward_edit(){
        $res=array("code"=>0,"msg"=>"删除成功","info"=>array());
        $id=I("id");
        $list=M("atmosphere_reward")->where("id={$id}")->find();
        $this->assign('list', $list);



        $this->display();
    }

    /*
     * 编辑
     * */
    public function edit(){
        if(IS_POST){
            $param = I("post.");
            $id = $param['id'];
            if(!$id){
                $this->error('id不能为空');
            }
            if ($param['rulename'] == "") {
                $this->error("请选择规则名称");
            }

            $data = array(
                'num' => intval($param['num']),
                'enterroom' => intval($param['enterroom']),
                'sendgift' => intval($param['sendgift']),
                'sendbarrage' => intval($param['sendbarrage']),
                'logout' => intval($param['logout']),
                'timebet' => intval($param['timebet']),
                'recommend' => intval($param['recommend']),

                'type' => intval($param['type']),
                'uid' => intval($param['uid']),

                'rulename' => $param['rulename'],
                'enterroomparallelnum' => intval($param['enterroomparallelnum']),
                'enterroomrate' => intval($param['enterroomrate']),
                'enterroommininterval' => intval($param['enterroommininterval']),
                'enterroommaxinterval' => intval($param['enterroommaxinterval']),
                'givegiftparallelnum' => intval($param['givegiftparallelnum']),
                'givegiftrate' => intval($param['givegiftrate']),
                'givegiftmininterval' => intval($param['givegiftmininterval']),
                'givegiftmaxinterval' => intval($param['givegiftmaxinterval']),

                'autotalkingparallelnum' => intval($param['autotalkingparallelnum']),
                'autotalkingrate' => intval($param['autotalkingrate']),
                'autotalkingmininterval' => intval($param['autotalkingmininterval']),
                'autotalkingmaxinterval' => intval($param['autotalkingmaxinterval']),
                'leaveroomparallelnum' => intval($param['leaveroomparallelnum']),
                'leaveroomrate' => intval($param['leaveroomrate']),
                'leaveroommininterval' => intval($param['leaveroommininterval']),
                'leaveroommaxinterval' => intval($param['leaveroommaxinterval']),

                'timebetparallelnum' => intval($param['timebetparallelnum']),
                'timebetrate' => intval($param['timebetrate']),
                'timebetmininterval' => intval($param['timebetmininterval']),
                'timebetmaxinterval' => intval($param['timebetmaxinterval']),
                'recomparallelnum' => intval($param['recomparallelnum']),
                'recomrate' => intval($param['recomrate']),
                'recommininterval' => intval($param['recommininterval']),
                'recommaxinterval' => intval($param['recommaxinterval']),
            );

            //开启时候，数据写入redis，给进程判断是否走定时任务
            try {
                $result = M("atmosphere_live")->where(array('id' => $id))->save($data);
            }catch (\Exception $e){
                $this->error('操作失败');
            }
            $liveinfo = M("atmosphere_live")->field("*")->where("id='{$id}'")->find();
            CustRedis::getInstance()->hSet("atmosphere_".$id, $id, json_encode($liveinfo));

            $action="修改直播氛围记录ID ：{$id}";
            setAdminLog($action);
            $this->success('操作成功',U('index', array('tenant_id'=>$liveinfo['tenant_id'])),1);
        }

        $id=intval($_GET['id']);
        if($id){
            $info = M("atmosphere_live")->where("id={$id}")->find();
            $this->assign('info', $info);
            $this->assign('tenant_list', getTenantList());
        }else{
            $this->error('数据传入失败！');
        }

        $this->display();
    }

    public function label(){
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

    public function classify(){
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

    public function add(){
        $labels=$this->role_model->where("is_delete=1")->order("id desc")->select();
        // var_dump($roles);exit;
        $this->assign("labels",$labels);
        $this->display();
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
