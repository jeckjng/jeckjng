<?php
/**
 * 加群开车模块
 */

 namespace Admin\Controller;
 use Common\Controller\AdminbaseController;
 class GroupsController extends AdminbaseController {
    public function index(){
        $count = M("groups_config")->where("is_deleted = 0")->count();
        $page = $this->page($count, 20);
        $lists = M("groups_config")->where("is_deleted = 0")
            ->order("id desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign("page", $page->show('Admin'));
        $this->assign("lists",$lists);
        $this->display();
    }

    public function add_group()
    {
        if(IS_POST){
            $url = $_POST["url"];
            $description = $_POST['description'];
            $is_visible = $_POST['is_visible'];
            $img = $_POST['img'];
               
            $group = M("groups_config");
            $group->create();
            $group->url = $url;
            $group->desc = $description;
            $group->status = $is_visible;
            $group->icon = $img;
            $group->created_at = $group->updated_at = date("Y-m-d H:i:s" ,time());
            $result = $group->add();
            if($result){
                $action="添加加群开车{$result}";
                setAdminLog($action);
                $this->success('添加成功', U('index'));
            }else{
                 $this->error('添加失败');
            }
        }else{
            $this->display();
        }
        
    }

    public function edit_group(){
        if(IS_POST){
            $url = $_POST["url"];
            $description = $_POST['description'];
            $is_visible = $_POST['is_visible'];
            $img = $_POST['img'];
            $group = M("groups_config");
            $data = array();
            $data['url'] = $url;
            $data['desc'] = $description;
            $data['status'] = $is_visible;
            $data['icon'] = $img;
            $result = $group->where("id={$_POST['id']}")->save($data);
            if ($result !== false) {
                $action = "修改加群开车：{$_POST['id']}";
                setAdminLog($action);
                $this->success('修改成功', U('index'));
            } else {
                $this->error('修改失败');
            }
        }else{
            $id=intval($_GET['id']);
            if($id){
                $group=M("groups_config")->find($id);
                $this->assign('group', $group);
            }else{
                $this->error('数据传入失败！');
            }
            $this->display();
        }
    }

    public function del_group()
    {
        $id=intval($_GET['id']);
        M("groups_config")->where("id={$id}")->save(array('is_deleted'=>1));
        $action = "修改加群开车：{$id}";
        setAdminLog($action);
        $this->success('删除成功', U('index'));
    }
 }
?>