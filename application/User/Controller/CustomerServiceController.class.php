<?php

namespace User\Controller;

use Common\Controller\AdminbaseController;
use Common\Controller\HomebaseController;

class CustomerServiceController extends AdminbaseController
{
    //列表页
    public function index()
    {
        if ($_REQUEST['start_time'] != '') {
            $map['addtime'] = array("gt", strtotime($_REQUEST['start_time']));
            $_GET['start_time'] = $_REQUEST['start_time'];
        }

        if ($_REQUEST['end_time'] != '') {

            $map['addtime'] = array("lt", strtotime($_REQUEST['end_time']));
            $_GET['end_time'] = $_REQUEST['end_time'];
        }
        if ($_REQUEST['start_time'] != '' && $_REQUEST['end_time'] != '') {

            $map['addtime'] = array("between", array(strtotime($_REQUEST['start_time']), strtotime($_REQUEST['end_time'])));
            $_GET['start_time'] = $_REQUEST['start_time'];
            $_GET['end_time'] = $_REQUEST['end_time'];
        }
        if ($_REQUEST['puid'] != '') {
            $map['puid'] = $_REQUEST['puid'];
            $_GET['puid'] = $_REQUEST['puid'];
        }
        if ($_REQUEST['username'] != '') {
            $map['username'] = $_REQUEST['username'];
            $_GET['username'] = $_REQUEST['username'];
        }
        $customerModel = M('users_customer');
        $count = $customerModel->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $customerModel
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    //添加页面
    public function add()
    {
        $this->display();
    }

    //修改页面
    public function edit()
    {
        $id = intval($_GET['id']);
        $performer = M('users_customer')->field('id,uid,puid,title,avatar')->where(['id' => $id])->find();
        $this->assign('performer', $performer);
        $this->display();
    }

    //删除动作
    public function del()
    {
        $id = intval($_GET['id']);
        $menb = M('users_customer')->field('id,uid')->where(['id' => $id])->find();
        if ($menb) {
            if (M('users_customer')->where(['id' => $id])->delete()) {
                $rooms=M('users_chatroom_friends')->field('room_id')->where(['sub_uid'=>$menb['uid'],'roomtype'=>1])->select();
                $roomIds=array_column($rooms,'room_id');
                M('users_chatroom_friends')->where(['room_id'=>['in',$roomIds]])->delete();
                M('users_chatroom')->where(['id'=>['in',$roomIds]])->delete();
                $this->success('删除成功');
            }
            $this->error('删除失败');
        }
        $this->error('缺少参数!');
    }
    //修改数据
    public function  edit_post()
    {
        if(IS_POST){
            $id=I('id');
            if (empty($id)) {
                $this->error('ID不能为空!');
            }
            $avatar=I('post.avatar');
            $title= I('post.title');
            if(M('users_customer')->where(['id' => $id])->save([
                'title'=>$title,
                'avatar'=>$avatar
            ])){
                $this->success('修改成功！');
            }
            $this->error('修改失败！');
        }
    }
    //写入数据
    public function add_post()
    {
        if (IS_POST) {
            $uid = I("post.uid");
            $puid= I('post.puid');
            $avatar= I('post.avatar');
            $title =I('post.title');
            if (empty($uid)) {
                $this->error('UID不能为空!');
            }
            if(empty($puid)){
                $this->error('UID不能为空!');
            }
            $_uid=M('users_customer')->field('uid')->where(['uid'=>$uid])->find();
            if($_uid['uid']){
                $this->error('该用户为客服!');
            }
            //查询
            $users_model=M("Users");
            $userNameData=$users_model->field('user_nicename')->where(['id'=>$uid])->find();
            $userPuidName=$users_model->field('user_nicename')->where(['id'=>$puid])->find();
            //写入数据库
            if (M('users_customer')->add([
                    'uid' => $uid,
                    'puid' => $puid,
                    'adminname' => $_SESSION['name'],
                    'addtime' => time(),
                    'username' => $userNameData['user_nicename'],
                    'pusername'=>$userPuidName['user_nicename'],
                    'avatar'=>$avatar,
                    'title'=>$title,
                ]) == true) {

                $this->success('写入成功!');
            } else {
                $this->error('写入失败!');
            }
        }
    }
}








