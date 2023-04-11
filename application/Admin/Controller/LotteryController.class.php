<?php
/**
 * Created by PhpStorm.
 * User: jonem
 * Date: 2021/11/15
 * Time: 23:44
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Composer\Package\Loader\ValidatingArrayLoader;
use QCloud\Cos\Api;
use QCloud\Cos\Auth;
use PHPExcel\PHPExcel\APC;


class LotteryController extends AdminbaseController {


    /*待审核视频列表*/
    public function index(){
        $role_id=$_SESSION['role_id'];
        $tenantId = getTenantIds();
        if($role_id !=1){
            //租户id条件
            $map['tenant_id'] = $tenantId;
        }
        if($_REQUEST['tenant_id']!=''){
            $map['tenant_id']=$_REQUEST['tenant_id'];
            $_GET['tenant_id']=$_REQUEST['tenant_id'];
        }
        if($_REQUEST['status']!=''){
            $map['status']=array("tenant_id"=>$_REQUEST['status']);
            $_GET['status']=$_REQUEST['status'];
        }
        if ($map){
            $list = M('lottery_config')->where($map)->select();
        }else{
            $list = M('lottery_config')->select();
        }
        $tenant =M("tenant")->where(array('site_id' => 1))->order("create_time DESC")->select();
        $this->assign('role_id', $role_id);
        $this->assign('list', $list);
        $tenant_list = array();
        foreach ($tenant as $value){
            $tenant_list[$value['id']] = $value;
        }
        $this->assign('tenant', $tenant_list);
        $this->assign('formget', $_GET);
        $this->display();
    }

    public  function  add(){
        $role_id=$_SESSION['role_id'];
        $tenant =M("tenant")->where(array('site_id' => 1))->order("create_time DESC")->select();
        $this->assign('role_id', $role_id);
        $this->assign('tenant', $tenant);
        $this->display();
    }

    public  function  add_post(){
        $tenant_id= $_POST['tenant_id'];
        if ($tenant_id){
            $data['tenant_id'] =$tenant_id;
        }else{
            $data['tenant_id'] = getTenantIds();
        }
        $play_cname = $_POST['play_cname'];
        if (empty($play_cname)){
            $this->error('请填写彩种名称');
        }
        $play_name = $_POST['play_name'];
        if (empty($play_name)){
            $this->error('请填写彩种简称名称');
        }
        $upper_id = $_POST['upper_id'];
        if ($upper_id === ''){
            $this->error('请填写upper_id');
        }
        $play_id = $_POST['play_id'];
        if (empty($play_id)){
            $this->error('请填写play_id名称');
        }
        $status = $_POST['status'];
        $data['play_name'] = $play_name;
        $data['upper_id'] = $upper_id;
        $data['play_id'] = $play_id;
        $data['status'] = $status;
        $data['play_cname'] = $play_cname;
        $success = M('lottery_config')->add($data);
        $id= M('lottery_config')->getLastInsID();
        if ($success){
            $action="添加彩种配置,修改id为".$id;
            $this->success('添加成功');
        }else{
            $this->success('添加失败');
        }
    }

    public function edit(){
        $role_id=$_SESSION['role_id'];
        $tenant =M("tenant")->where(array('site_id' => 1))->order("create_time DESC")->select();
        $id = $_GET['id'];
        $info = M('lottery_config')->where(array('id' => $id))->find();
        $this->assign('role_id', $role_id);
        $this->assign('tenant', $tenant);
        $this->assign('info', $info);

        $this->display();
    }

    public  function edit_post(){
        $id = $_POST['id'];
        $tenant_id= $_POST['tenant_id'];
        if ($tenant_id){
            $data['tenant_id'] =$tenant_id;
        }else{
            $data['tenant_id'] = getTenantIds();
        }
        $play_cname = $_POST['play_cname'];
        if (empty($play_cname)){
            $this->error('请填写彩种名称');
        }
        $play_name = $_POST['play_name'];
        if (empty($play_name)){
            $this->error('请填写彩种简称名称');
        }
        $upper_id = $_POST['upper_id'];
        if ($upper_id === ''){
            $this->error('请填写upper_id');
        }
        $play_id = $_POST['play_id'];
        if (empty($play_id)){
            $this->error('请填写play_id名称');
        }
        $status = $_POST['status'];
        $data['play_name'] = $play_name;
        $data['upper_id'] = $upper_id;
        $data['play_id'] = $play_id;
        $data['status'] = $status;
        $data['play_cname'] = $play_cname;
        $success = M('lottery_config')->where(array('id'=>$id))->save($data);
        if ($success){
            $action="修改彩种配置,修改id为".$id;
            setAdminLog($action);
            $this->success('修改成功');
        }else{
            $this->success('修改失败');
        }
    }
}

