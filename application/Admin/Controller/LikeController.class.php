<?php
/*
   扩展配置
 */

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class LikeController extends AdminbaseController{

    protected $attribute;

    function _initialize() {
        parent::_initialize();
    }

    function index(){
        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
        $this->assign('config',$config);
        $this->display();
    }

    public function set_post(){
        try{
            if(IS_POST){
                $config=I("post.post");
                $tenantId=getTenantIds();
                if (M("tenant_config")->where('tenant_id="'.$tenantId.'"')->save($config)!==false) {
                    //$key=$tenantId.'_'.'getTenantConfig';
                    //setcaches($key,$config);
                    delcache($tenantId.'_'.'getTenantConfig');
                    delcache($tenantId.'_'."getPlatformConfig");

                    $action="修改租户设置";
                    setAdminLog($action);
                    $this->success("保存成功！");
                } else {
                    $this->error("保存失败！");
                }
            }
        }catch (\Exception $e){
            setAdminLog('修改租户设置失败：'.$e->getMessage());
            $this->error("保存失败！");
        }
    }

    public function like_list(){
        $param = $_REQUEST;
        $map = array();
        $tenantId=getTenantIds();
        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }
        else{
            //租户id条件
            $map['tenant_id']=$tenantId;
        }
        if($param['video_id']!=''){
            $map['video_id'] = $param['video_id'];
        }
        if($param['video_like_uid']!=''){
            $map['video_like_uid']=array("eq",$param['video_like_uid']);
        }
        if($param['video_uid']!=''){
            $map['video_uid']=array("eq",$param['video_uid']);
        }

        if($param['video_user_login']!=''){
            $map['video_user_login'] = $param['video_user_login'];
        }
        if($param['video_like_user_login']!=''){
            $map['video_like_user_login'] = $param['video_like_user_login'];
        }

        if($param['status']!=''){
            $map['status']= $param['status'];
        }

        if($param['start_time']!=''){
            $map['create_time']=array("gt",(strtotime($param['start_time'])));
        }

        if($param['end_time']!=''){
            $map['create_time']=array("lt",(strtotime($param['end_time'])+86399));
        }
        if($param['start_time']!='' && $param['end_time']!='' ){
            $map['create_time']=array("between",array((strtotime($param['start_time'])),(strtotime($param['end_time']))+86399));
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $where = '1';
        $p=I("p");
        if(!$p){
            $p=1;
        }

        $model = M("video_profit");
        $count = $model
            ->where($where)
            ->where($map)
            ->count();
        $page = $this->page($count);

        $lists = $model
            ->where($where)
            ->where($map)
            ->order('create_time desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $uids = [];
        foreach($lists as $key=>$val){
            $uids[] = $val['video_uid'];
            $uids[] = $val['video_like_uid'];
            if($val['video_type']==1){
                $lists[$key]['video_type']='短视频';
            }else{
                $lists[$key]['video_type']='长视频';
            }
            $userinfo = getUserInfo($val['video_uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['video_user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['video_user_login'=>$userinfo['user_login']]);
            }
            $video_like_userinfo = getUserInfo($val['video_like_uid']);
            if($val['video_like_user_login'] == '' && $video_like_userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['video_like_user_login'=>$video_like_userinfo['user_login']]);
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }

}