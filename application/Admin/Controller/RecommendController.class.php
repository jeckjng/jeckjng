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
use Admin\Cache\RecommendCache;

class RecommendController extends AdminbaseController {
    protected $users_model,$role_model;
    function _initialize() {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->tenamt_model = D("Common/Tenant");

    }
    /*推荐主播添加*/
    function index(){
        $param = I('param.');
        $map = array();
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = $tenant_id;
        if(isset($param['uid']) && $param['uid'] != ''){
            $map['liveuid'] = $param['uid'];
        }

        $model = M("recommend");
        $count = $model->count();
        $page = $this->page($count, $page_size);
        $lists = $model->where($map)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach($lists as $k=>$v){
            $lists[$k]['add_time']=date('Y-m-d H:i:s',$v['add_time']);
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->display();
    }

     function add_recommend(){

        if(IS_POST) {
            $tenamtinfo = explode('_',$_POST['tenamtinfo']);
            $liveuid = $_POST['liveuid'];
            if ($liveuid == "") {
                $this->error("推送的主播ID不能为空");
            }
            $recomenndinfo =M('recommend')->where(" liveuid=".$liveuid)->select();
            if($recomenndinfo){
                $this->error("该主播已经上了推荐，请勿重复添加！");
            }
            $liveinfo=M('users_live')->where("islive=1 and tenant_id=".$tenamtinfo[0]." and  uid=".$liveuid)->select();
            if(empty($liveinfo)){
                $this->error("主播和游戏租户不匹配，或者该主播未开始直播");
            }
            $userinfo = getUserInfo($liveuid);
            $tenant_id = $tenamtinfo[0];

            $data = array(
                'liveuid'=>$liveuid,
                'nickname'=>$userinfo['user_nicename'],
                'stream' => isset($liveinfo[0]['stream'])?$liveinfo[0]['stream']:'0',
                'tenant_id' => $tenamtinfo[0],
                'tenant_name' => $tenamtinfo[1],
                'game_tenant_id' => $tenamtinfo[2],
                'add_time'=>time(),
            );

            $betconfi = M("recommend");
            $betconfi->create();
            $result = $betconfi->add($data);


            if ($result) {
                RecommendCache::getInstance()->DelRecommendListCache($tenant_id); // 推荐主播列表，清除缓存
                $this->success('添加成功','/Admin/recommend/index',3);
            } else {
                $this->error('添加失败');
            }

        }

    }



    public function addrecommend(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $this->assign("tenant_id", $tenant_id);
        $this->assign("tenamtinfo", getTenantList());
        $this->display();

    }



    function edit(){
        $id=intval($_GET['id']);
        if($id){
            $atmosphere=M("atmosphere_live")->where("id={$id}")->find();
            $tenamtinfo=$this->tenamt_model->where("status=1")->order("id desc")->select();

            $this->assign('tenant', $atmosphere);
            $this->assign('tenamtinfo', $tenamtinfo);

        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }


    function del(){
        $res=array("code"=>0,"msg"=>"删除成功","info"=>array());
        $id = $_GET['id'];
        if(!$id){
            $res['code']=1001;
            $res['msg']='查询不到该主播';
            echo json_encode($res);
            exit;
        }
        $info = M("recommend")->where("id={$id}")->find();
        $tenant_id = $info['tenant_id'];
        $result = M("recommend")->where("id={$id}")->delete();
        if($result!==false){
            RecommendCache::getInstance()->DelRecommendListCache($tenant_id); // 推荐主播列表，清除缓存
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }


}
