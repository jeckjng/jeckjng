<?php

/**
 * 关键字
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class KeywordsetController extends AdminbaseController {

    /*
     * 记录管理
     * */
    function index(){
        $param = I('param.');
        $map = array();

        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $keywordset_map = array();
        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']!=''){
                $map['tenant_id'] = $param['tenant_id'];
                $keywordset_map = $param['tenant_id'];
            }else{
                $param['tenant_id'] = '';
            }
        }else{
            $tenant_id = getTenantIds();
            $param['tenant_id'] = $tenant_id;
            $map['tenant_id'] = $tenant_id;
            $keywordset_map = $param['tenant_id'];
        }

        if(I('uid')){
            $map['uid'] = I('uid');
        }
        if(I('content')){
            $map['content'] = I('content');
        }


        $model = M("user_keyword");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $lists = $model->field('*')->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        $keylists = M("user_keywordset")->where($keywordset_map)->select();
        $keylists_index = array();
        foreach ($keylists as $key=>$value){
            $keylists_index[$value['tenant_id'].'_'.$value['content']] = $value;
        }

        foreach ($lists as $key=>$value){
            if($value['tenant_id'] == '0'){
                $userinfo = getUserInfo($value['uid']);
                $lists[$key]['tenant_id'] = isset($userinfo['tenant_id']) ? intval($userinfo['tenant_id']) : 0;
                $value['tenant_id'] = $lists[$key]['tenant_id'];
                $model->where(['id'=>$value['id']])->save(['tenant_id'=>intval($userinfo['tenant_id'])]);
            }
            $tenantInfo = getTenantInfo($value['tenant_id']);
            $lists[$key]['tenant_name'] = $tenantInfo ? $tenantInfo['name'] : $value['tenant_id'];
            $lists[$key]['addtime'] =  date("Y-m-d H:i:s",$value['addtime']);
            $index = $value['tenant_id'].'_'.$value['content'];
            $lists[$key]['set_shut_times'] = isset($keylists_index[$index]) ? $keylists_index[$index]['shut_times'] : 0;
            $lists[$key]['set_outroom_times'] = isset($keylists_index[$index]) ? $keylists_index[$index]['outroom_times'] : 0;
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list', getTenantList());
        $this->assign('param', $param);
        $this->display();
    }

    /*
        * 设置禁言列表
        * */
    function set(){
        $param = I('param.');
        $map = array();
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = $tenant_id;

        if(I('uid')){
            $map['uid'] = I('uid');
        }
        if(I('content')){
            $map['content'] = I('content');
        }

        $model = M("user_keywordset");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $lists = $model->field('*')->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach ($lists as $key=>$value){
            if($value['tenant_id'] == '0'){
                $userinfo = getUserInfo($value['uid']);
                $lists[$key]['tenant_id'] = isset($userinfo['tenant_id']) ? intval($userinfo['tenant_id']) : 0;
                $model->where(['id'=>$value['id']])->save(['tenant_id'=>intval($userinfo['tenant_id'])]);
            }
            $tenantInfo = getTenantInfo($value['tenant_id']);
            $lists[$key]['tenant_name'] = $tenantInfo ? $tenantInfo['name'] : $value['tenant_id'];
            $lists[$key]['addtime'] =  date("Y-m-d H:i:s",$value['addtime']);
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list', getTenantList());
        $this->assign('param', $param);
        $this->display();
    }


    /*
     * 配置
     * */
    public function setting(){
        if(IS_POST){
            $param = I('param.');
            $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : getTenantIds();
            $content = trim(urldecode($param['content']));

            $keywordsetinfo = M('user_keywordset')->where(['tenant_id'=>$tenant_id, 'content'=>$content])->select();
            if($keywordsetinfo){
                $this->error("关键词已经设置了，请编辑 ");
            }

            $data['tenant_id'] = $tenant_id;
            $data['shut_times'] = $_POST['shut_times'];
            $data['outroom_times'] = $_POST['outroom_times'];
            $data['content'] = $_POST['content'];
            $data['addtime'] = time();

            $users_keyword = M("user_keywordset");
            $users_keyword->create();
            $result = $users_keyword->add($data);
            $this->success("设置成功！", U('set', array('tenant_id'=>$tenant_id)));
        }

        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $info = '';

        $this->assign('info', $info);
        $this->assign('tenant_list',getTenantList());
        $this->assign('tenant_id',$tenant_id);
        $this->display();
    }
    /*
        * 编辑
        * */
    public function edit(){
        if(IS_POST){
            $param = I('param.');
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            $content = trim(urldecode($param['content']));

            $keywordsetinfo = M('user_keywordset')->where(['tenant_id'=>$tenant_id, 'content'=>$content,'id'=>['neq', intval($param['id'])]])->select();
            if($keywordsetinfo){
                $this->error("关键词已经设置了，请编辑 ");
            }

            $data['uid'] = $_POST['uid'];
            $data['shut_times'] = $_POST['shut_times'];
            $data['outroom_times'] = $_POST['outroom_times'];
            $data['content'] = $_POST['content'];
            $data['addtime'] = time();

            $users_keyword = M("user_keywordset");
            $users_keyword->create();
            $result = $users_keyword->where("id={$_POST['id']}")->save($data);
            $this->success("设置成功！", U('set', array('tenant_id'=>$tenant_id)));
        }


        $id=intval($_GET['id']);
        if($id) {
            $info = M("user_keywordset")->where("id={$id}")->find();
            $this->assign('info', $info);
            $this->display();
        }


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

    /*
    * 取消会员触发关键字禁言
    * */
    public function cancelshut(){
        $param = I('param.');
        if($param['uid']){
            $redis = connectionRedis();
            $redis->hDel( 'shutup_keyword', $param['uid']);
            $redis->hDel( 'outroom_keyword', $param['uid']);

            M('user_keyword')->where(['uid'=>intval($param['uid'])])->save(['shut_times'=>0,'outroom_times'=>0]);

            setAdminLog("解除会员禁言：{$param['uid']}");

            $this->success('解除成功');
        }

    }

}
