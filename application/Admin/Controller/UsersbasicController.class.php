<?php

/**
 *  底薪结算
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Admin\Controller\CoinrecordController;

class UsersbasicController extends AdminbaseController {

    protected $users_model,$role_model;
    protected $status = array(
                            '0'=>'处理中',
                            '1'=>'转账成功',
                        );


    function _initialize() {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->role_model = D("Common/Users_share");
    }

    function index(){
        $param = I('param.');
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map = array();

        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']!=''){
                $map['tenant_id'] = $param['tenant_id'];
            }else{
                $param['tenant_id'] = '';
            }
        }else{
            $tenant_id = getTenantIds();
            $param['tenant_id'] = $tenant_id;
            $map['tenant_id'] = $tenant_id;
        }

        if($_REQUEST['start_time']!=''){
            $map['addtime']=array("gt",strtotime($_REQUEST['start_time']));
        }

        if($_REQUEST['end_time']!=''){
            $map['addtime']=array("lt",strtotime($_REQUEST['end_time']));
        }
        if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
            $map['addtime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'])));
        }

        if($_REQUEST['uid']!=''){
            $map['uid|user_login']=$_REQUEST['uid'];
        }
        $map['status'] = array('neq',3);
        if($_REQUEST['status']!=''){
            $map['status']=$_REQUEST['status'];
        }

        if($_SESSION['admin_type'] == 1){
            $userinfo =M("users")->where("user_login='".$_SESSION['name']."'")->find();

            $author = array();
            if($userinfo['familyids']){
                $domain = strstr($userinfo['familyids'], ',');
                if(!$domain){
                    $users_family =M("users_family")->where("familyid=".$userinfo['familyids']."")->select();
                    foreach ($users_family as $key=>$value){
                        $author[] = $value['user_login'];
                    }
                }else{
                    $familyid = explode(',',$userinfo['familyids']);
                    foreach ($familyid as $value){
                        $users_family =M("users_family")->where("familyid=".$value."")->select();
                        foreach ($users_family as $key=>$value){
                            $author[] = $value['user_login'];
                        }
                    }
                }

            }
        }

        $model = M("users_basicsalary");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $lists = $model->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach ($lists as $key=>$value){
            if($_SESSION['admin_type'] == 1){
                if(!in_array($value['user_login'],$author)){
                    unset($lists[$key]);
                }
            }
            if($value['tenant_id'] == '0'){
                $userinfo = getUserInfo($value['uid']);
                $lists[$key]['tenant_id'] = isset($userinfo['tenant_id']) ? intval($userinfo['tenant_id']) : 0;
                $model->where(['id'=>$value['id']])->save(['tenant_id'=>intval($userinfo['tenant_id'])]);
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->assign('tenant_list',getTenantList());
    	$this->display();
    }


    function edit(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if($id){
            $cash=M("users_basicsalary")->find($id);

            $bankaccount=M("bankcard_share")->where("is_delete=1")->order("id desc")->select();
            $this->assign('cash', $cash);
            $this->assign('bankaccount', $bankaccount);
            $this->assign('type', $this->type);
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    function edit_post(){
        if(IS_POST){

            if($_POST['status']==''){
                $this->error('请选择状态！');
            }
            if($_POST['bankaccount']==''){
                $this->error('银行名称不能为空');
            }
            if($_POST['banknumber']==''){
                $this->error('银行账号不能为空');
            }

            $usershareinfo=M("users_basicsalary")->where("id=".$_POST['id'])->find();


            $data['operatetime']=time();;
            $data['bankaccount']=$_POST['bankaccount'];
            $data['banknumber']=$_POST['banknumber'];
            $data['status']=1;
            $data['mark']=$_POST['mark'];
            $data['operatename']=$_SESSION['name'];


            $result=M("users_basicsalary")->where("id='{$_POST['id']}'")->save($data);
            if($result){
                $action="发放底薪：{$usershareinfo['money']} 给 :{$usershareinfo['user_login']}";
                setAdminLog($action);
                $this->success('修改成功',U('Usersbasic/index'));
            }else{
                $this->error('修改失败');
            }
        }
    }



    	
}
