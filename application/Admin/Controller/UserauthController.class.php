<?php

/**
 * 提现
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class UserauthController extends AdminbaseController {
    function index(){

					if($_REQUEST['status']!=''){
						  $map['status']=$_REQUEST['status'];
							$_GET['status']=$_REQUEST['status'];
					 }
				   if($_REQUEST['start_time']!=''){
						  $map['addtime']=array("gt",strtotime($_REQUEST['start_time']));
							$_GET['start_time']=$_REQUEST['start_time'];
					 }
					 
					 if($_REQUEST['end_time']!=''){
						 
						   $map['addtime']=array("lt",strtotime($_REQUEST['end_time']));
							 $_GET['end_time']=$_REQUEST['end_time'];
					 }
					 if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
						 
						 $map['addtime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'])));
						 $_GET['start_time']=$_REQUEST['start_time'];
						 $_GET['end_time']=$_REQUEST['end_time'];
					 }
 
					 if($_REQUEST['keyword']!=''){
						 $map['uid|real_name|mobile']=array("like","%".$_REQUEST['keyword']."%"); 
						 $_GET['keyword']=$_REQUEST['keyword'];
					 }


					if ($_SESSION['ADMIN_ID'] != 1){
                       // $map['tenant_id']=getGameTenantIds();
                        $map['tenant_id'] =  getTenantIds();
                    }
    	$auth=M("users_auth");
    	$count=$auth->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $auth
    	->where($map)
    	->order("addtime DESC")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();
        foreach($lists as $k=>$v){
            $userinfo=M("users")->field("user_nicename")->where("id='$v[uid]'")->find();
            $lists[$k]['userinfo']= $userinfo;

        }
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }
		
		function del(){
			 	$id=intval($_GET['id']);
			 	$tenantId=getGameTenantIds();
					if($id){
						$result=M("users_auth")->where("uid='{$id}' and tenant_id='{$tenantId}'")->delete();
							if($result){
                                    $action="删除会员认证信息：{$id}";
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


    function edit(){
        $id=intval($_GET['id']);


        $map['uid'] = $id;
        if ($_SESSION['ADMIN_ID'] != 1){  // 超管可任务操作
            $map['tenant_id']=getTenantIds();
        }
        if($id){
            $auth=M("users_auth")->where($map)->find();
            $auth['userinfo']=M("users")->field("user_nicename,isshare")->where("id='$auth[uid]'")->find();
            $this->assign('tenantId',$map['tenant_id']);
            $this->assign('auth', $auth);
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }
		
		function edit_post(){
				if(IS_POST){		
            /* if($_POST['status']=='0'){							
							  $this->error('未修改状态');			
						} */
                ;
                    $id=$_POST['uid'];

                    $map['uid'] = $id;
                    if ($_SESSION['ADMIN_ID'] != 1){
                        $map['tenant_id']=getGameTenantIds();
                    }
                    $authInfo=M("users_auth")->where($map)->find();
                    if(is_null($authInfo)){
                        $this->error('修改失败');
                    }
                    else{
                        $auth=M("users_auth");
                        $auth->create();
                        $auth->uptime=time();
                        $result=$auth->save();
                        if($result){
                            if($_POST['status']=='1'){
                                $action="修改会员认证信息：{$_POST['uid']} - 通过";
                            }else if($_POST['status']=='2'){
                                $action="修改会员认证信息：{$_POST['uid']} - 拒绝";
                            }else{
                                $action="修改会员认证信息：{$_POST['uid']} - 审核中";
                            }


                            setAdminLog($action);
                            $this->success('修改成功',U('Userauth/index'));
                        }else{
                            $this->error('修改失败');
                        }
                    }
				}			
		}
    public function getUserauth(){
        $res=array("code"=>0,"msg"=>"获取成功","info"=>array());
        $role_id=$_SESSION['role_id'];
        $rule_name = '身份认证';
        $isauth = getAuth($role_id,$rule_name);
        if($isauth == 1){
            $charge=M("users_auth");
            $count=$charge
                ->where('status=0')
                ->count();
            if($count>0){
                $res['code']=200;
                $res['counts']=$count;
            }
        }
        echo json_encode($res);
        exit;

    }


}
