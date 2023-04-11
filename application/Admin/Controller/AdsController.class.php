<?php

/**
 * 广告图片
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class AdsController extends AdminbaseController {
    function index(){

        $ads_sort=M("ads_sort")->order('id desc')->getField("id,sortname");
        //$ads_sort[0]="默认分类";
        $this->assign('ads_sort', $ads_sort);
        $video_long_classify =M("video_long_classify")->order('id desc')->getField("id,classify");
        //$ads_sort[0]="默认分类";
        $this->assign('video_long_classify', $video_long_classify);
        $map = [];
        $tenantId=getGameTenantIds();
        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }else{
            //租户id条件
            $map['tenant_id']=$tenantId;
        }
        if($_REQUEST['type']){
            $map['sid']=array("eq",$_REQUEST['type']);
            $_GET['type']=$_REQUEST['type'];
        }
        if($_REQUEST['name']!=''){
            $map['name']=array("eq",$_REQUEST['name']);
            $_GET['name']=$_REQUEST['name'];
        }

    	$ads_model=M("ads");
    	$count=$ads_model->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $ads_model
            ->where($map)
    	->order("id desc")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();
    	foreach ($lists as $key=>$val){
            $lists[$key]['des'] = htmlspecialchars_decode($val['des']);
        }

    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('formget', $_GET);
    	$this->display();
    }
		
    function del(){
        $id=intval($_GET['id']);
            if($id){
                $result=M("ads")->delete($id);
                    if($result){
                        $action="删除广告图片：{$id}";
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
    //排序
    public function listorders() { 
		
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("ads")->where(array('id' => $key))->save($data);
        }
				
        $status = true;
        if ($status) {
            $action="更新广告图片排序";
                    setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
    public  function add(){
        $ads_sort=M("ads_sort")->getField("id,sortname");
        $this->assign('ads_sort', $ads_sort);

        $video_long_classify =M("video_long_classify")->order('id desc')->getField("id,classify");
        //$ads_sort[0]="默认分类";
        $this->assign('video_long_classify', $video_long_classify);
        $this->display();
    }
    public function add_post(){
        if(IS_POST){
             $ads=M("ads");
             $ads->create();
             $ads->tenant_id = getGameTenantIds();
             $ads->addtime=time();
             if ($ads->sid == 15){
                 if ($ads->grade<=0 ||  $ads->grade >5){
                     $this->error('工具难度为1到5');
                 }
             }
             $result=$ads->add();
             if($result){
                 $action="添加广告图片：{$result}";
                 setAdminLog($action);
                 $this->success('添加成功', U('index'));
             }else{
                  $this->error('添加失败');
             }
        }
    }
    public function edit(){
        $id=intval($_GET['id']);
            if($id){
                $ads_sort=M("ads_sort")->getField("id,sortname");
                $this->assign('ads_sort', $ads_sort);
                $video_long_classify =M("video_long_classify")->order('id desc')->getField("id,classify");
                //$ads_sort[0]="默认分类";
                ;
                $this->assign('video_long_classify', $video_long_classify);
                $ads=M("ads")->find($id);
                $ads['des_html'] = addslashes(htmlspecialchars_decode($ads['des']));
                $this->assign('ads', $ads);
            }else{
                $this->error('数据传入失败！');
            }
        $this->display();
    }
		
    public function edit_post()
    {
        if (IS_POST) {
            $user = M("ads");
            $user->create();
            if ($user->sid == 15){
                if ($user->grade<=0 ||  $user->grade >5){
                    $this->error('工具难度为1到5');
                }
            }
            $result = $user->save();
            if ($result !== false) {
                $action = "修改广告图片：{$_POST['id']}";
                setAdminLog($action);
                $this->success('修改成功', U('index'));
            } else {
                $this->error('修改失败');
            }
        }
    }
    public  function sort_index()
    {

        $ads_sort = M("ads_sort");
        $count = $ads_sort->count();
        $page = $this->page($count, 20);
        $lists = $ads_sort
            ->order("id desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    public  function sort_del()
    {
        $id = intval($_GET['id']);
        if ($id) {
            $result = M("ads_sort")->delete($id);
            if ($result) {
                $action = "删除广告图片分类：{$id}";
                setAdminLog($action);
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    //排序
    public function sort_listorders() { 
		
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("ads_sort")->where(array('id' => $key))->save($data);
        }
				
        $status = true;
        if ($status) {
            $action="更新广告图片分类排序";
                    setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }				
    function sort_add(){
		    	
    	$this->display();
    }		
    function do_sort_add(){

        if(IS_POST){
            if($_POST['sortname']==''){
                $this->error('分类名称不能为空');
            }
            $ads_sort=M("ads_sort");
            $ads_sort->create();
            $ads_sort->addtime=time();
					 
            $result=$ads_sort->add();
            if($result){
                $action="添加广告图片分类：{$result}";
                setAdminLog($action);
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
    }		
    function sort_edit(){

			 	$id=intval($_GET['id']);
					if($id){
						$sort	=M("ads_sort")->find($id);
						$this->assign('sort', $sort);						
					}else{				
						$this->error('数据传入失败！');
					}								      	
    	$this->display();
    }			
    function do_sort_edit(){
				if(IS_POST){			
					 $ads_sort=M("ads_sort");
					 $ads_sort->create();
					 $result=$ads_sort->save(); 
					 if($result){
                         $action="编辑广告图片分类：{$_POST['id']}";
                    setAdminLog($action);
						  $this->success('修改成功');
					 }else{
						  $this->error('修改失败');
					 }
				}	
    }				
}
