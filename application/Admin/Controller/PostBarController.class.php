<?php

/**
 * 直播记录
 */
namespace Admin\Controller;
use Admin\Model\UsersModel;
use Common\Controller\AdminbaseController;
class PostBarController extends AdminbaseController
{
    public  function index()
    {


        $tenantId=getTenantIds();
        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        } else{
            //租户id条件
            $map['tenant_id']=$tenantId;
        }
        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
        $this->assign('config',$config);
        try{
            if(IS_POST){
                $config=I("post.post");

                $config['login_type']=implode(",",$config['login_type']);
                $config['share_type']=implode(",",$config['share_type']);
                if (isset( $config['seeking_slice_effective_time'])){
                    $config['seeking_slice_effective_time']=$config['seeking_slice_effective_time'] * 86400;
                }
                if ($config['seeking_slice_bonus_min']>= 1000000){
                    $this->error("悬赏金额范围不能大于999999！");
                }
                if ($config['post[seeking_slice_bonus_max]']>= 1000000){
                    $this->error("悬赏金额范围不能大于999999！");
                }
                foreach($config as $k=>$v){
                    $config[$k]=html_entity_decode($v);
                }
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
        $this->display();
    }

    public  function ordinaryPostBar(){

        $menuList = M('menu')->where(['parentid'=>$_GET['menuid']])->order('listorder ASC')->select();
        foreach ($menuList as $key=>$value){
            $menuAction = $value['app'].'/'.$value['model'].'/'.$value['action'];
            if (!sp_auth_check($_SESSION['ADMIN_ID'],$menuAction)){
                unset($menuList[$key]);
            }
        }
        $menuList = array_column($menuList,'name','action');

        $where = ['type' => 1];
        if($_REQUEST['addtime']!='' && $_REQUEST['endtime']!='' ){
            $where['addtime']=array("between",array(strtotime($_REQUEST['addtime']),strtotime($_REQUEST['endtime'].' 23:59:59')));
            $_GET['addtime']=$_REQUEST['addtime'];
            $_GET['endtime']=$_REQUEST['endtime'];
        }

        if($_REQUEST['user_login']!=''){
            $where['user_login'] = $_REQUEST['user_login'];
            $_GET['user_login']=$_REQUEST['user_login'];
        }
        if($_REQUEST['uid']!=''){
            $where['uid'] = $_REQUEST['uid'];
            $_GET['uid']=$_REQUEST['uid'];
        }
        if($_REQUEST['status']){
            $where['status'] = $_REQUEST['status'];
            $_GET['status']=$_REQUEST['status'];
        }

        $model = M("bar");
        $count = $model->where($where)->count();
        $page = $this->page($count);
        $bar = $model->order('id desc ')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
        $paly_url = play_or_download_url(1);
        foreach ($bar as $key =>  $value){
            $userinfo = getUserInfo($value['uid']);
            if($value['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$value['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($value['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$value['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            if ($value[$paly_url['viode_table_field']]){
                $bar[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
            }
            if ($value['img']){
                $img =  explode(',',$value['img']);
                foreach ($img as $imgKey =>  $imgValue){
                    $img[$imgKey] =   $paly_url['url'].$imgValue;
                }
                $bar[$key]['img'] = $img;
            }
        }

        $this->assign('bar',$bar);
        $this->assign("page", $page->show('Admin'));
        $this->assign('formget', $_GET);
        $this->assign('menu_list',$menuList);
        $this->display();
    }

    public  function seekingSlice(){
        $menuList = M('menu')->where(['parentid'=>$_GET['menuid']])->order('listorder ASC')->select();
        foreach ($menuList as $key=>$value){
            $menuAction = $value['app'].'/'.$value['model'].'/'.$value['action'];
            if (!sp_auth_check($_SESSION['ADMIN_ID'],$menuAction)){
                unset($menuList[$key]);
            }
        }
        $menuList = array_column($menuList,'name','action');

        $where = ['type' => 2];
        if($_REQUEST['addtime']!='' && $_REQUEST['endtime']!='' ){
            $where['addtime']=array("between",array(strtotime($_REQUEST['addtime']),strtotime($_REQUEST['endtime'].' 23:59:59')));
            $_GET['addtime']=$_REQUEST['addtime'];
            $_GET['endtime']=$_REQUEST['endtime'];
        }
        if($_REQUEST['user_login']!=''){
            $where['user_login'] = $_REQUEST['user_login'];
            $_GET['user_login']=$_REQUEST['user_login'];
        }
        if($_REQUEST['uid']!=''){
            $where['uid'] = $_REQUEST['uid'];
            $_GET['uid']=$_REQUEST['uid'];
        }
        if($_REQUEST['status']){
            $where['status'] = $_REQUEST['status'];
            $_GET['status']=$_REQUEST['status'];
        }

        $model = M("bar");
        $count= $model->where($where)->count();
        $page = $this->page($count);
        $bar = $model->order('id desc ')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
        $paly_url = play_or_download_url(1);
        foreach ($bar as $key =>  $value){
            $userinfo = getUserInfo($value['uid']);
            if($value['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$value['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($value['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$value['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            if ($value[$paly_url['viode_table_field']]){
                $bar[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
            }

            if ($value['img']){
                $img =  explode(',',$value['img']);
                foreach ($img as $imgKey =>  $imgValue){
                    $img[$imgKey] =   $paly_url['url'].$imgValue;
                }
                $bar[$key]['img'] = $img;
            }
        }
        $this->assign('bar',$bar);
        $this->assign("page", $page->show('Admin'));
        $this->assign('formget', $_GET);
        $this->assign('menu_list',$menuList);
        $this->display();
    }

    public  function edit(){
        $id = $_REQUEST['id'] ;
        $status = $_REQUEST['status'] ;
        if ($status== 3 ){
            $bar =M("bar")->where(['id' => $id])->find();
            if ($bar['type'] == 2 ){
                $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($bar['uid']);
                if(!$user_info){
                    $this->error('用户不存在');
                }
                M('users') ->where(['id'=>$bar['uid']] ) ->setInc('coin',$bar['reward_money']);
                $insert=array(
                    "type"=>'income',
                    "action"=>'bar_return',
                    "uid"=>$bar['uid'],
                    'user_login' => $user_info['user_login'],
                    "user_type" => intval($user_info['user_type']),
                    "giftid"=>$id,
                    "pre_balance" => floatval($user_info['coin']),
                    "totalcoin"=>$bar['reward_money'],
                    "after_balance" => floatval(bcadd($user_info['coin'], $bar['reward_money'],4)),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $this->addCoinrecord($insert);
            }
        }
        M("bar")->where(['id'=>$id ])->save(['status' => $status,'operator_id'=> $_SESSION["ADMIN_ID"],'endtime'=> time()]);
        $this->success('操作成功');

    }

    public  function  commentList(){
        $where['is_delete'] = 0;
        if($_REQUEST['id']!=''){
            $where['bar_id'] = $_REQUEST['id'];
            $_GET['id']=$_REQUEST['id'];
        }
        if($_REQUEST['uid']!=''){
            $where['publish_uid'] = $_REQUEST['uid'];
            $_GET['uid']=$_REQUEST['uid'];
        }
        if($_REQUEST['user_login']!=''){
            $userId = M("users")->where(['user_login' =>$_REQUEST['user_login'] ])->getField('id');
            $where['publish_uid'] = $userId;
            $_GET['user_login']=$_REQUEST['user_login'];
        }
        $count= M("bar_comment")->where($where)->count();
        $page = $this->page($count);
        $bar =M("bar_comment")->order('addtime desc ')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();

        $this->assign("page", $page->show('Admin'));
        $this->assign('lists', $bar);
        $this->assign('title', I('title'));
        $this->assign('formget', $_GET);
        $this->display();
    }

    public  function commentDesc(){
        $where['is_delete'] = 0;
        if($_REQUEST['id']!=''){
            $where['bar_id'] = $_REQUEST['id'];
            $_GET['id']=$_REQUEST['id'];
        }
        if($_REQUEST['uid']!=''){
            $where['publish_uid'] = $_REQUEST['uid'];
            $_GET['uid']=$_REQUEST['uid'];
        }
        if($_REQUEST['user_login']!=''){
            $userId = M("users")->where(['user_login' =>$_REQUEST['user_login'] ])->getField('id');
            $where['publish_uid'] = $userId;
            $_GET['user_login']=$_REQUEST['user_login'];
        }
        $count= M("bar_comment")->where($where)->count();
        $page = $this->page($count);
        $bar =M("bar_comment")->order('status asc ')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();

        $this->assign("page", $page->show('Admin'));
        $this->assign('lists', $bar);
        $this->assign('title', I('title'));
        $this->assign('formget', $_GET);
        $this->display();
    }

    public  function delete(){
        if($_REQUEST['id']!=''){
            $where['id'] = $_REQUEST['id'];
        }
        $commentInfo =M("bar_comment")->where($where)->find();
        if ($commentInfo['parent_comment_id'] == 0){
            M("bar_comment")->where($where)->save(['is_delete' => 1]);
            M("bar_comment")->where(['comment_id'=>$_REQUEST['id'] ])->save(['is_delete' => 1]);
            $count =  M("bar_comment")->where(['comment_id'=>$_REQUEST['id'] ])->count();
            $count = $count + 1;
        }else{
            $commentId  =  $this->getComment($_REQUEST['id'][$_REQUEST['id']]);
            $count = count($commentId);
            M("bar_comment")->where(['comment_id'=>$commentId ])->save(['is_delete' => 1]);
        }
        M("bar")->where(['id' =>$commentInfo['bar_id'] ])->setDec('comments_number',$count);
        $this->success('操作成功');

    }

    public  function getComment($id,$array =[]){
        $commentId =M("bar_comment")->where(['parent_comment_id' => $id])->getField('id');
        if ($commentId){
            $array[] = $commentId;
            self::getComment($id,$array);
        }else{
            return $array;
        }

    }


}