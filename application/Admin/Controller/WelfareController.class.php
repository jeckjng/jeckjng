<?php

/**
 * 福利管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class WelfareController extends AdminbaseController {
    function index(){
        $welfareModel = M("welfare");

        $tenantId=getTenantIds();
        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }
        else{
            //租户id条件
            $map['tenant_id']=$tenantId;
        }
        $count=$welfareModel->where($map)->count();
        $page = $this ->page($count, 20);
        $lists = $welfareModel
            ->where($map)
            ->limit($page->firstRow ,$page->listRows)
            ->select();
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }
    public  function add(){
        $this->display();
    }

    public  function add_post(){

        $name = I("post.name");
        $desc = I("post.desc");
        $status = I("post.status");
        $integral = I("post.integral");
        $welfare = M("welfare");
        if (!$_FILES['img']){
            $this->error('请上传图片');
        }
        if($_FILES) {
            $savepath = date('Ymd') . '/';
            //上传处理类
            $config = array(
                'rootPath' => './' . C("UPLOADPATH"),
                'savePath' => $savepath,
                'maxSize' => 11048576,
                'saveName' => array('uniqid', ''),
                'exts' => array('svga'),
                'autoSub' => false,
            );
            $upload = new \Think\Upload($config);//
            $info = $upload->upload();
            //开始上传
            if ($info) {
                //上传成功
                //写入附件数据库信息
                $first = array_shift($info);
                if (!empty($first['url'])) {
                    $url = $first['url'];
                } else {
                    $url = C("TMPL_PARSE_STRING.__UPLOAD__") . $savepath . $first['savename'];
                }
                $url = str_replace("http", "https", $url);

            } else {
                //上传失败，返回错误
                $this->error($upload->getError());
            }
        }
        $tenantId=getTenantIds();
        $welfare->create();
        $welfare->add(
            array(

                'name' => $name,
                'desc' => $desc ,
                'status' =>$status,
                'addtime' => time(),
                'integral' => $integral,
                'img' => $url,
                'tenant_id' => $tenantId,
            )
        );
        $action = '添加福利商品';
        setAdminLog($action);
        $this->success("添加成功！", U("Welfare/index"));
    }

    public  function del(){
        $id=intval($_GET['id']);
        if($id){
            $result=M("welfare")->delete($id);
            if($result){
                $action="删除福利：{$id}";
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

    public  function exchangelog(){

        $tenantId=getTenantIds();
        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }
        else{
            //租户id条件
            $map['tenant_id']=$tenantId;
        }
        $welfareModel = M("welfare_exchange_log");
        $count=$welfareModel->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $welfareModel->where($map)
            ->order('id desc')
            ->limit($page->firstRow ,$page->listRows)
            ->select();
        foreach ($lists as $key => $value){
            $lists[$key]['user_nicename']= M("users")->where(array(['id' => $value['uid']]))->getField('user_nicename');
            $welfareInfo = M("welfare")->where(array(['id' => $value['welfare_id']]))->find();
            $lists[$key]['welfare_name']  = $welfareInfo['name'];
            $lists[$key]['integral']  = $welfareInfo['integral'];
        }
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    public function  exchangelogedit(){
        $id=intval($_GET['id']);
        $welfareModel = M("welfare_exchange_log");
        $welfareInfoLog = $welfareModel
            ->where(array('id' => $id))
            ->find();
        $welfareInfo = M("welfare")->where(array(['id' => $welfareInfoLog['welfare_id']]))->find();
        $welfareInfoLog['welfare_name']  = $welfareInfo['name'];
        $welfareInfoLog['integral']  = $welfareInfo['integral'];
        $this->assign('info', $welfareInfoLog);
        $this->display();
    }

    public  function edit_post()
    {
        $id = intval($_POST['id']);
        $welfare_id = intval($_POST['welfare_id']);
        $user_id = intval($_POST['uid']);
        $status = intval($_POST['status']);
        $integral = $_POST['integral'];
        $express_order = $_POST['express_order'];
        $welfareModel = M("welfare_exchange_log")->where(array('id' => $id))->save(array('status' => $status, 'express_order' => $express_order));

        if ($status == 2) {
            M("welfare")->where("id = '{$welfare_id}'")->setInc('frequency', 1);
            $action = "通过福利申请：{$id}";
            setAdminLog($action);
        } else if($status == 3) {
            $u_info = getUserInfo($user_id);
            M("users")->where("id = '{$user_id}'")->setInc('integral', $integral);
            $integraldata = array(
                'uid' => $u_info['id'],
                'start_integral' => $u_info['integral'],
                'change_integral' => $integral,
                'end_integral' => ($u_info['integral'] + $integral),
                'act_type' => 2,
                'status' => 1,
                'remark' => $u_info['user_login'] . ' 驳回福利兑换申请',
                'ctime' => time(),
                'act_uid' => $_SESSION['ADMIN_ID'],
                'tenant_id' => $u_info['tenant_id'],
            );
            M("integral_log")->add($integraldata);
            delUserInfoCache($user_id);
            $action = "驳回福利申请：{$id}";
            setAdminLog($action);
        }else{
            $this->error('请选择审核状态');
        }

        $this->success('修改成功', U("Welfare/exchangelog"));
    }
}
