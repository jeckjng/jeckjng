<?php

/**
 * 举报
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ReportController extends AdminbaseController {
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
             $map['uid']=array("like","%".$_REQUEST['keyword']."%"); 
             $_GET['keyword']=$_REQUEST['keyword'];
         }

         $map['tenant_id']=getTenantIds();
			
    	$Report=M("users_report");
    	$count=$Report->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $Report
    	->where($map)
    	->order("addtime DESC")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();
			
			foreach($lists as $k=>$v){
                $lists[$k]['userinfo']= M("users")->field("user_nicename")->where("id='{$v[uid]}'")->find();
                $lists[$k]['touserinfo']= M("users")->field("user_nicename,user_status")->where("id='{$v[touid]}'")->find();
			}			
			
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }
		
		function setstatus(){
			 	$id=intval($_GET['id']);
			 	$tenantId=getTenantIds();
					if($id){
						 $data['status']=1;
						 $data['uptime']=time();
						$result=M("users_report")->where("id='{$id}' and tenant_id='{$tenantId}'")->save($data);
							if($result){
                                $action="用户举报标记处理：{$id}";
                    setAdminLog($action);
									$this->success('标记成功');
							 }else{
									$this->error('标记失败');
							 }			
					}else{				
						$this->error('数据传入失败！');
					}								  		
		}		
		
		function del(){
			 	$id=intval($_GET['id']);
			 	$tenantId=getTenantIds();
					if($id){
						$result=M("users_report")->where("id=%d and tenant_id=%d",$id,$tenantId)->delete();
							if($result){
                                $action="删除用户举报：{$id}";
                    setAdminLog($action);
									$this->success('删除成功');
							 }else{
									$this->error('删除失败');
							 }			
					}else{				
						$this->error('数据传入失败！');
					}								  
		}		

		
		function edit(){
			 	$id=intval($_GET['id']);
			 	$tenantId=getTenantIds();
					if($id){
						$Report=M("users_report")->find($id);
						$Report['userinfo']=M("users")->field("user_nicename")->where("id=%d and tenant_id=%d",$Report['uid'],$tenantId)->find();
						$this->assign('Report', $Report);						
					}else{				
						$this->error('数据传入失败！');
					}								  
					$this->display();				
		}
		
		function edit_post(){
				if(IS_POST){		
                    if($_POST['status']=='0'){							
                        $this->error('未修改状态');			
                    }
				     $tenantId=getTenantIds();
					 $Report=M("users_report");
                     $Report->where("id=%d and tenant_id=%d",$Report['uid'],$tenantId);
					 $Report->create();
					 $Report->uptime=time();
					 $result=$Report->save(); 
					 if($result){
						  $this->success('修改成功',U('Report/index'));
					 }else{
						  $this->error('修改失败');
					 }
				}			
		}		
    
}
