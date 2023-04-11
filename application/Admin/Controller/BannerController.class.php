<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/6/16
 * Time: 13:24
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

class BannerController extends AdminbaseController{

    public  function index(){
        $count= M('long_video_banner')->count();
        $page = $this->page($count);
        $banner = M('long_video_banner')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign("banner",$banner);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public  function upstatus(){
        $id = intval(I("get.id"));
        $status = intval(I("get.status"));
        if (M('long_video_banner')->where("id=$id")->save(array('status'=>$status))!==false) {

            $this->success("设置成功！");
        } else {
            $this->error("设置失败！");
        }
    }
    public  function delete(){
        $id=intval($_GET['id']);
        if($id){
            $result=M("long_video_banner")->where("id={$id}")->delete();
            if($result!==false){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
    }

}