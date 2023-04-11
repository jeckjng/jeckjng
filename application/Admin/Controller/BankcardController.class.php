<?php

/**
 *
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Composer\Package\Loader\ValidatingArrayLoader;
use QCloud\Cos\Api;
use QCloud\Cos\Auth;
use PHPExcel\PHPExcel\APC;




class BankcardController extends AdminbaseController {


    function index(){
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $video_model=M("bankcard_share");
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

        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->display();
    }




    public function addbank(){
        $this->display();

    }
    public function add_bank(){

        if(IS_POST){
            $video=M("bankcard_share");
            $video->create();

            $bankname   = $_POST['bankname'];
            $banknumber = $_POST['banknumber'];
            $beneficiary = $_POST['beneficiary'];
            if($beneficiary==""){
                $this->error("请填写收款方");
                return;
            }
            if($bankname==""){
                $this->error("请填写标签名称");
                return;
            }
            if($banknumber==""||!is_numeric($banknumber)){
                $this->error("请填写正确的银行卡号，");
                return;
            }
            //判断用户是否存在
        /*    $ownerInfo=M("users")->where("user_type=2 and id={$owner_uid}")->find();
            if(!$ownerInfo){
                $this->error("用户uid不存在");
                return;
            }*/
      /*      $video->uid=$owner_uid;
            $labelInfo=M("video_label_long")->where("is_delete=1 and label='"."{$label}"."'")->find();
            if($labelInfo){
                $this->error("该标签已存在");
                return;
            }*/

            $arr['addtime']=time();
            $arr['banknumber']= $banknumber;
            $arr['bankname']= $bankname;
            $arr['beneficiary']= $beneficiary;
            $arr['is_delete']= '1';
            $video->add($arr);
            $result = true;
            $action="添加银行卡：{$id}";
            setAdminLog($action);
            if($result){
                $this->success('添加成功','/Admin/Bankcard/index',3);
            }else{
                $this->error('添加失败');
            }

        }
    }
    public function deletelabel(){
        $id=intval($_GET['id']);

        if($id){
            $result=M("bankcard_share")->delete($id);

            if($result){
                $action="删除银行卡成功 ：{$id}";
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
