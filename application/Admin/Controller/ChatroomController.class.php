<?php

/**
 * VIP管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ChatroomController extends AdminbaseController {

    /*
     * 聊天室管理
     * */
    function index(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = array();

        if(I('id')){
            $map['uc.id'] = I('id');
        }
        if(I('title')){
            $map['uc.title'] = I('title');
        }
        if(I('uid')){
            $map['uc.uid'] = I('uid');
        }
        if(I('user_login')){
            $u_info = M("users")->where(['user_login'=>I('user_login'),'user_type'=>2])->field('id,user_login')->find();
            $map['uc.uid'] = $u_info['id'];
        }
        if(I('status')!=''){
            $map['uc.status'] = I('status');
        }else{
            $map['uc.status'] = 1;
            $_GET['status'] = 1;
            $param['status'] = 1;
        }

        $users_chatroom=M("users_chatroom as uc");
        $count=$users_chatroom->where($map)->count();
        $page = $this->page($count, 20);

        $lists = $users_chatroom->join('cmf_users_chatroom_friends as ucf on ucf.room_id=uc.id and ucf.status!=2','left')
                ->field('uc.*,count(ucf.id) as friend_num')
                ->where($map)
                ->group('id')
                ->order("addtime desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach($lists as $key=>$val){
            $lists[$key]['userinfo'] = getUserInfo($val['uid']);
            $lists[$key]['act_userinfo'] = getUserInfo($val['act_uid']);
            $lists[$key]['title'] = rawurldecode($val['title']);
        }

        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
     * 查看会员
     * */
    public function view_friends(){
        $lists = M("users_chatroom_friends")->where(['room_id'=>intval(I('id')),'status'=>['neq',2]])->order("type desc,addtime desc")->select();
        foreach($lists as $key=>$val){
            $lists[$key]['userinfo'] = getUserInfo($val['sub_uid']);
            $lists[$key]['act_userinfo'] = getUserInfo($val['act_uid']);
            $lists[$key]['status_name'] = $val['status']=='1' ? '正常' : '禁言';
            $lists[$key]['type_name'] = $val['type']=='3' ? '房主' : ($val['type']=='2' ? '管理员' : '普通会员');

        }

        $this->assign('lists', $lists);
        $this->assign('title', I('title'));
        $this->display();
    }

    /*
     * 配置
     * */
    public function setting(){
        if(IS_POST){
            $param = I('post.');
            if($param['num']<1 || $param['num']>9999){
                $this->error('会员数量限制不规范，数量区间为：1 - 9999');
            }
            $data = array(
                'num' => intval($param['num']),
                'recordnum' => intval($param['recordnum']),
                'exptime' => intval($param['exptime']),
                'tenant_id' => intval(getTenantIds()),
                'act_uid' => intval($_SESSION["ADMIN_ID"]),
                'mtime' => time(),
            );

            if($param['id']){
                $res = M('users_chatroom_conf')->where(['id'=>intval($param['id'])])->save($data);
            }else{
                $res = M('users_chatroom_conf')->add($data);
            }

            if(!$res){
                $this->error('操作失败');
            }
            delChatRoomConfCache(); // 清除聊天室配置缓存
            $this->success('操作成功');
        }

        $info = M("users_chatroom_conf")->find();

        $this->assign('info', $info);
        $this->display();
    }

    /*
    * 群禁言
    * */
    public function room_mute(){
        $param = I('param.');
        if($param['id']){
            M('users_chatroom')->where(['id'=>intval($param['id'])])->save(['status'=>0,'act_uid'=>intval($_SESSION["ADMIN_ID"]),'mtime'=>time()]);
            $this->success('禁言成功');
        }
        $this->error('禁言失败');
    }

    /*
    * 取消群禁言
    * */
    public function room_cancel_mute(){
        $param = I('param.');
        if($param['id']){
            M('users_chatroom')->where(['id'=>intval($param['id'])])->save(['status'=>1,'act_uid'=>intval($_SESSION["ADMIN_ID"]),'mtime'=>time()]);
            $this->success('取消禁言成功');
        }
        $this->error('取消禁言失败');
    }

    /*
    * 会员禁言
    * */
    public function friend_mute(){
        $param = I('param.');
        if($param['id']){
            M('users_chatroom_friends')->where(['id'=>intval($param['id'])])->save(['status'=>0,'act_uid'=>intval($_SESSION["ADMIN_ID"]),'mtime'=>time()]);
            $this->success('禁言成功');
        }
        $this->error('禁言失败');
    }

    /*
    * 取消会员禁言
    * */
    public function friend_cancel_mute(){
        $param = I('param.');
        if($param['id']){
            M('users_chatroom_friends')->where(['id'=>intval($param['id'])])->save(['status'=>1,'act_uid'=>intval($_SESSION["ADMIN_ID"]),'mtime'=>time()]);
            $this->success('取消禁言成功');
        }
        $this->error('取消禁言失败');
    }

}
