<?php

/**
 * 直播记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class LiveController extends AdminbaseController {
    public function index(){
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
            $map['starttime']=array("gt",strtotime($_REQUEST['start_time']));
         }

         if($_REQUEST['end_time']!=''){
            $map['starttime']=array("lt",strtotime($_REQUEST['end_time'].' 23:59:59'));
         }
         if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
             $map['starttime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'].' 23:59:59')));
         }

        if($_REQUEST['uid']!=''){
            $map['uid']=$_REQUEST['uid'];
        }
        if($_REQUEST['game_user_id'] != ''){
            $map['game_user_id'] = $_REQUEST['game_user_id'];
        }

    	$liveModel = M("users_liverecord");
    	$count = $liveModel->where($map)->count();
    	$page = $this->page($count, $page_size);
    	$lists = $liveModel->where($map)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach($lists as $k=>$v){
             $userinfo = getUserInfo($v['uid']);
             $lists[$k]['userinfo']=$userinfo;
             if(!empty($v['tenant_id'])){
                 $tenantInfo=getTenantInfo($v['tenant_id']);
                 if(!empty($tenantInfo)){
                     $lists[$k]['tenant_name']=$tenantInfo['name'];
                 }
             }
             if($v['game_user_id'] == '0' && $userinfo['game_user_id']){
                $lists[$k]['game_user_id']=$userinfo['game_user_id'];
                 $liveModel->where(['id'=>$v['id']])->save(['game_user_id'=>$userinfo['game_user_id']]);
             }
            $lists[$k]['effective_time']  = time2string($v['endtime']-$v['starttime']-$v['stop_time']);
            $lists[$k]['stop_time']  = time2string($v['stop_time']);
        }

        /*
         * 家族后台账号，只显示该账号包括的后台账号的家族里面的主播
         */
        if($_SESSION['admin_type'] == 1){
            $userinfo =M("users")->where("user_login='".$_SESSION['name']."'")->find();

            $author = array();
            if($userinfo['familyids']){
                $domain = strstr($userinfo['familyids'], ',');
                if(!$domain){
                    $users_family =M("users_family")->where("familyid=".$userinfo['familyids']."")->select();
                    foreach ($users_family as $key=>$value){
                        $author[] = $value['uid'];
                    }
                }else{
                    $familyid = explode(',',$userinfo['familyids']);
                    foreach ($familyid as $value){
                        $users_family =M("users_family")->where("familyid=".$value."")->select();
                        foreach ($users_family as $key=>$value){
                            $author[] = $value['uid'];
                        }
                    }
                }

            }

        }


        foreach ($lists as $key=>$value){
            if($_SESSION['admin_type'] == 1){
                if(!in_array($value['uid'],$author)){
                    unset($lists[$key]);
                }
            }

        }
        if($_SESSION['admin_type'] == 1){
            $page = $this->page(count($lists), $page_size);
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('tenant_list',getTenantList());
    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
    	$this->display();
    }
    public function del()
		{
			$id=intval($_GET['id']);
			$tenantId=getTenantIds();
			if($id){
				$result=M("users_liverecord")->where("id=%d and tenant_id=%d",$id,$tenantId)->delete();
				if($result){
						$this->success('删除成功');
				}else{
					$this->error('删除失败');
				}			
			}else{				
				$this->error('数据传入失败！');
			}								  
			$this->display();		
		}

    public function liverecord_summary(){
        $param = I('param.');
        $map=array();
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
        if(!isset($param['start_time']) && !isset($param['end_time'])){
            $param['start_time'] = date('Y-m-d');
            $param['end_time'] = date('Y-m-d');
        }
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        if(isset($param['start_time']) && $param['start_time']!=''){
            $map['starttime']=array("egt",strtotime($param['start_time']));
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['starttime']=array("elt",strtotime($param['end_time'].' 23:59:59'));
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time']!='' && $param['end_time']!='' ){
            $map['starttime']=array("between",array(strtotime($param['start_time']),strtotime($param['end_time'].' 23:59:59')));
        }

        if(isset($param['uid']) && $param['uid']!=''){
            $map['uid']=$_REQUEST['uid'];
        }
        if(isset($param['game_user_id']) && $param['game_user_id'] != ''){
            $map['game_user_id'] = $param['game_user_id'];
        }

        $model = M("users_liverecord");
        $count_list = $model->field('uid')->where($map)->group('uid')->select();
        $count = count($count_list);
        $page = $this->page($count, 20);
        $pagelistall = $model
            ->field('uid,sum(votes) as votes_sum ,sum(nums) as nums_sum')
            ->where($map)
            ->group('uid')
            ->order("votes_sum DESC, nums_sum DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        if(count($pagelistall) > 0){
            $map['uid'] = ['in',array_keys(array_column($pagelistall,null, 'uid'))];
            $listall = $model->where($map)->select();
        }else{
            $listall = [];
        }

        $temp_list = array();
        foreach($listall as $key=>$val) {
            if (!isset($temp_list[$val['uid']])) {
                $temp_list[$val['uid']] = $val;
                $temp_list[$val['uid']]['effective_time'] = 0;
            } else {
                $temp_list[$val['uid']]['votes'] += $val['votes'];
                $temp_list[$val['uid']]['stop_time'] += $val['stop_time'];
                $temp_list[$val['uid']]['nums'] += $val['nums'];
            }

            $effective_time = $val['endtime']-$val['starttime']-$val['stop_time'];
            if($effective_time<0){
                $effective_time = $val['endtime']-$val['starttime'];
                $v['stop_time'] = 0;
            }
            $temp_list[$val['uid']]['effective_time'] += $effective_time;
        }
        $list = array();
        foreach($pagelistall as $key=>$val){
            if(isset($temp_list[$val['uid']])){
                array_push($list,$temp_list[$val['uid']]);
            }
        }

        foreach($list as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            $list[$key]['userinfo']=$userinfo;
            if(!empty($val['tenant_id'])){
                $tenantInfo=getTenantInfo($val['tenant_id']);
                if(!empty($tenantInfo)){
                    $list[$key]['tenant_name'] = $tenantInfo['name'];
                }
            }
            if($val['game_user_id'] == '0' && $userinfo['game_user_id']){
                $lists[$key]['game_user_id'] = $userinfo['game_user_id'];
                M('users_liverecord')->where(['id'=>$val['id']])->save(['game_user_id'=>$userinfo['game_user_id']]);
            }
            if(!$list[$key]['tenant_name']){
                $list[$key]['tenant_name'] = '-';
            }
            $list[$key]['votes'] = floatval($val['votes']);
            $list[$key]['effective_time']  = time2string($val['effective_time']);
            $list[$key]['stop_time']  = time2string($val['stop_time']);
        }

        /*
         * 家族后台账号，只显示该账号包括的后台账号的家族里面的主播
         */
        if($_SESSION['admin_type'] == 1){
            $userinfo =M("users")->where("user_login='".$_SESSION['name']."'")->find();
            $author = array();
            if($userinfo['familyids']){
                $domain = strstr($userinfo['familyids'], ',');
                if(!$domain){
                    $users_family =M("users_family")->where("familyid=".$userinfo['familyids']."")->select();
                    foreach ($users_family as $key=>$value){
                        $author[] = $value['uid'];
                    }
                }else{
                    $familyid = explode(',',$userinfo['familyids']);
                    foreach ($familyid as $value){
                        $users_family =M("users_family")->where("familyid=".$value."")->select();
                        foreach ($users_family as $key=>$value){
                            $author[] = $value['uid'];
                        }
                    }
                }
            }
        }
        foreach ($list as $key=>$value){
            if($_SESSION['admin_type'] == 1){
                if(!in_array($value['uid'],$author)){
                    unset($list[$key]);
                }
            }
        }
        if($_SESSION['admin_type'] == 1){
            $page = $this->page(count($list), 20);
        }

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->assign("role_id", getRoleId());
        $this->assign('tenant_list',getTenantList());

        $this->display();
    }
		
}
