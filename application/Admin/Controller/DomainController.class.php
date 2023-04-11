<?php
/**
 * 域名配置模块
 */

 namespace Admin\Controller;
 use Common\Controller\AdminbaseController;
 class DomainController extends AdminbaseController {
    public function index(){
        $count = M("domain_config")->count();
        $page = $this->page($count, 20);
        $lists = M("domain_config")
            ->order("id desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign("page", $page->show('Admin'));
        $this->assign("lists",$lists);
        $this->display();
    }

    public function add()
    {
        if(IS_POST){
            $is_reachable = $_POST["is_reachable"];
            $title = $_POST['title'];
            $group = M("domain_config");
            $group->create();
            $group->title = $title;
            $group->is_view = 1;
            $group->is_reachable = 1;
            $group->created_at = $group->updated_at = date("Y-m-d H:i:s" ,time());
            $result = $group->add();
            if($result){
                $action="域名管理-添加域名{$result}";
                setAdminLog($action);
                $this->success('添加成功', U('index'));
            }else{
                 $this->error('添加失败');
            }
        }else{
            $this->display();
        }
        
    }

    public function edit(){
        if(IS_POST){
            $is_reachable = $_POST["is_reachable"];
            $title = $_POST['title'];
               
            $group = M("domain_config")->find($_POST['id']);
            $data = array();
            $data['title'] = $title;
            $data['is_view'] = 1;
            $data['is_reachable'] = $is_reachable;
            $data['updated_at'] = date("Y-m-d H:i:s" ,time());
            $result = M("domain_config")->where("id={$_POST['id']}")->save($data);
            if ($result !== false) {
                $action = "域名管理-修改域名：{$_POST['id']}";
                setAdminLog($action);
                $this->success('修改成功', U('index'));
            } else {
                $this->error('修改失败');
            }
        }else{
            $id=intval($_GET['id']);
            if($id){
                $group=M("domain_config")->find($id);
                $this->assign('group', $group);
            }else{
                $this->error('数据传入失败！');
            }
            $this->display();
        }
    }

    public function del()
    {
        $id=intval($_GET['id']);
        M("domain_config")->where("id={$id}")->delete();
        $action = "域名管理-删除域名:{$id}";
        setAdminLog($action);
        $this->success('删除成功', U('index'));
    }
 }
?>