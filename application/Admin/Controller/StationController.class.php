<?php

/**
 * 推送管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class StationController extends AdminbaseController {

    function index(){
        $tenantId =getTenantIds();

        $map['tenant_id']=$tenantId;
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

        $Pushrecord=M("station_letter");
        $count=$Pushrecord->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $Pushrecord
            ->where($map)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $userAdmin  =M('users')->where(array("user_type"=>1,'tenant_id'=>$tenantId ))->field('id,user_login')->select();
        $userAdminById = array_column($userAdmin,null,'id');
        foreach ($lists as $key => $value ){
            $lists[$key]['admin'] = $userAdminById[$value['admin_id']]['user_login'];
        }


        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    function del(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if($id){
            $result=M("station_letter")->where("id=%d and tenant_id=%d",$id,$tenantId)->delete();
            $result=M("station_user")->where(['station_id'=>intval($id)])->delete();
            if($result){
                $action="删除推送信息：{$id}";
                setAdminLog($action);
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
    }

    function add(){
        if(IS_POST){
            $content=str_replace("\r","", $_POST['desc']);
            $content=str_replace("\n","", $content);
            if (empty($content)){
                $this->error('推送内容不能为空');
            }
            $title  = $_POST['title'];
            if (empty($title)){
                $this->error('请输入标题');
            }
            $data['title'] = $title;
            $data['desc'] = $content;
            $data['type'] = $_POST['type'];
            $stationModel =M("station_letter");
            $data['tenant_id']= getTenantIds();
            $data['addtime'] = time();
            $uid = $_POST['uid'];
            if ($uid){
                $uidArray = explode(',',$uid);
                $userinfo =  M("Users")->where(["id"=>['in', $uidArray],'tenant_id'=>getTenantIds()])->field('id')->select();
                $ids = array_column($userinfo,'id');
                $notUid = [];
                foreach ($uidArray as $value){
                    if (!in_array($value,$ids)){
                        $notUid[] = $value;
                    }
                    if ($notUid){
                        $this->error('用户id'.implode(',',$notUid).'不存在请核对');
                    }
                }
                $data['uid'] = $uid;

            }else{
                $data['uid'] =  0;
                $userList  =M('users')->where(array("user_type"=>['in',[2,3,4,5,6,7,8]],'tenant_id'=>getTenantIds() ))->select();
                $ids = array_column($userList,'id');

            }
            $data['admin_id'] = $_SESSION['ADMIN_ID'];
            $result = $stationModel->add($data);
            foreach ($ids as $value){
                $userStation[] = [
                    'uid' => $value,
                    'station_id' =>$result,
                    'addtime' => time(),
                    'type' =>$data['type'],
                ];
            }
            $result = M('station_user')->addAll($userStation);
            if($result!==false){
                $action="添加站内信ID：{$result}";
                setAdminLog($action);
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
        $this->display();
    }


}
