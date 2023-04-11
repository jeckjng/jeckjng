<?php

/**
 * 消费记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Admin\Controller\CoinrecordController;

class UsershareController extends AdminbaseController {

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
        if($_REQUEST['beneficiary']!=''){
            $map['beneficiary']=$_REQUEST['beneficiary'];
            $_GET['beneficiary']=$_REQUEST['beneficiary'];
        }
        if($_REQUEST['consumption_name']!=''){
            $map['consumption_name']=$_REQUEST['consumption_name'];
            $_GET['consumption_name']=$_REQUEST['consumption_name'];
        }


    	$usershare=M("users_share");

        $role_id=$_SESSION['role_id'];
        if($role_id==1){

            $count=$usershare->where($map)->where('status=0')->count();
            $page = $this->page($count, 20);
            $lists = $usershare
                ->where($map)
                ->where('status=0')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('id desc')
                ->select();
        }else{
            $map['tenant_id'] = getTenantIds();
            $count=$usershare->where($map)->where('status=0')->count();
            $page = $this->page($count, 20);
            $lists = $usershare
                ->where($map)
                ->where('status=0')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('id desc')
                ->select();
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
        foreach ($lists as $key=>$value){
            if($_SESSION['admin_type'] == 1){
                if(!in_array($value['beneficiary'],$author)){
                    unset($lists[$key]);
                }
            }

        }

    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }

    function record(){
        if($_REQUEST['beneficiary']!=''){
            $map['beneficiary']=$_REQUEST['beneficiary'];
            $_GET['beneficiary']=$_REQUEST['beneficiary'];
        }
        if($_REQUEST['consumption_name']!=''){
            $map['consumption_name']=$_REQUEST['consumption_name'];
            $_GET['consumption_name']=$_REQUEST['consumption_name'];
        }


        $usershare=M("users_share");

        $map['status'] = 1;
        $role_id=$_SESSION['role_id'];
        if($role_id==1){
            $count=$usershare->where($map)->count();
            $page = $this->page($count, 20);
            $lists = $usershare
                ->where($map)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('id desc')
                ->select();
        }else{
            $map['tenant_id'] = getTenantIds();
            $count=$usershare->where($map)->count();
            $page = $this->page($count, 20);
            $lists = $usershare
                ->where($map)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('id desc')
                ->select();
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
        foreach ($lists as $key=>$value){
            if($_SESSION['admin_type'] == 1){
                if(!in_array($value['beneficiary'],$author)){
                    unset($lists[$key]);
                }
            }

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
						$result=M("users_coinrecord")->where("id=%d and tenant_id=%d",$id,$tenantId)->delete();
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

    function edit(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();

        $transfer_money=M("users_share_log")->where("share_id= ".$id)->order("id desc")->sum('transfer_money');
        if(!$transfer_money){
            $transfer_money = '0.00';
        }



        if($id){
            $lists=M("users_share")->find($id);
            $bankaccount=M("bankcard_share")->where("is_delete=1 and beneficiary = '".$lists['beneficiary']."'")->order("id desc")->select();

            $total_money = number_format($transfer_money+$lists['money'],2);
            $this->assign('cash', $lists);
            $this->assign('bankaccount', $bankaccount);
            $this->assign('money', $lists['money']);
            $this->assign('transfer_money', $transfer_money);
            $this->assign('total_money', $total_money);
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
            $status  = $_POST['status'];
            $usershareinfo=M("users_share")->where("id=".$_POST['id'])->find();
            if($usershareinfo){
                if($_POST['money']>$usershareinfo['money']){
                    $this->error('转账金额不能大于余额');
                }elseif($_POST['money']<$usershareinfo['money']){
                    $money =floatval($usershareinfo['money']-$_POST['money']);
                    $status  = '0';
                }else{
                    $total_money = M("users_share_log")->where("share_id='{$_POST['id']}'")->sum('transfer_money');
                    $money=$total_money+$_POST['money'];
                  //  var_dump($money);

                }
            }

            $data['updatetime']=time();;
            $data['bankaccount']=$_POST['bankaccount'];
            $data['banknumber']=$_POST['banknumber'];
            $data['status']=$status;
            $data['mark']=$_POST['mark'];
            $data['money'] = $money;

            $result=M("users_share")->where("id='{$_POST['id']}'")->save($data);
            if($result){

                    $action="[ {$usershareinfo['beneficiary']} ] 提现操作 ,金额为：".$_POST['money'];

                $insetdata = array(
                    'share_id' => $usershareinfo['id'],
                    'transfer_money' => $_POST['money'],
                    'beneficiary' => $usershareinfo['beneficiary'], // 收款方
                    'consumption_name' => $usershareinfo['consumption_name'], // 收款方
                    'action' => 1, // 行为: 1.彩票分润结算
                    'admin_id' => $_SESSION['ADMIN_ID'],
                    'tenant_id'=>$usershareinfo['anchor_id'],
                    'ctime' => time(),
                );

                M("users_share_log")->add($insetdata);
                setAdminLog($action);
                $this->success('修改成功',U('Usershare/index'));
            }else{
                $this->error('修改失败');
            }
        }
    }

    function info(){

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

        if($_REQUEST['uid']!=''){
            $map['uid|user_login']=$_REQUEST['uid'];
            $_GET['uid']=$_REQUEST['uid'];
        }

        $usershare=M("users_sharedetail");
        $map['share_id']=$_REQUEST['id'];
        $count=$usershare->where($map)->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $usershare
                ->where($map)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('id desc')
                ->select();
        $types = array(
            1=>'发送礼物',
            2=>'发送弹幕',
            3=>'收费房间',
            4=>'彩票投注',
        );
        foreach ($lists as $key =>$value){
            $lists[$key]['rent_percent'] = $value['rent_percent']*100;
            $lists[$key]['type']  =  $types[$value['type']];
        }

        $rent_money=$usershare->where($map)->sum('rent');
        $usershareinfo = M("users_share")->where('id='.$_REQUEST['id'])->find();

        $this->assign('lists', $lists);
        $this->assign('id', $_REQUEST['id']);
        $this->assign('rent_money', $rent_money);
        $this->assign('beneficiary', $usershareinfo['beneficiary']);

        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    function actionlog(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = array();
        if(isset($param['act_user']) && $param['act_user']){
            $act_userinfo = M("Users")->where(['id|user_login'=>array("eq",$param['act_user'])])->field(['id'])->select();
            if(count($act_userinfo) > 0){
                $map['admin_id'] = array('in',array_keys(array_column($act_userinfo,null,'id')));
            }else{
                $map['admin_id'] = $param['act_user'];
            }
        }

        if(isset($param['beneficiary']) && $param['beneficiary']){
              $map['beneficiary|tenant_id']=array("like","%".$_REQUEST['beneficiary']."%");
              $_GET['beneficiary']=$_REQUEST['beneficiary'];
        }
        if($_REQUEST['consumption_name']!=''){
            $map['consumption_name']=$_REQUEST['consumption_name'];
            $_GET['consumption_name']=$_REQUEST['consumption_name'];
        }

        if(I('start_time')){
            $map['ctime'] = ['egt',strtotime(I('start_time'))];
        }
        if(I('end_time')){
            $map['ctime'] = ['elt',strtotime(I('end_time'). " 23:59:59")];
        }
        if(I('start_time') && I('end_time')){
            $map['ctime'] = array("between",array(strtotime(I('start_time')),strtotime(I('end_time'). " 23:59:59")));
        }

        $role_id=$_SESSION['role_id'];
        if($role_id==1){
            $count=M("users_share_log")->where($map)->count();
            $page = $this->page($count, 20);
            $lists = M("users_share_log")
                ->where($map)
                ->order('id desc')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();

            $total_money = M("users_share_log")->where($map)->getField('sum(transfer_money)');
        }else{
            $map['tenant_id'] = getTenantIds();
            $count=M("users_share_log")->where($map)->count();
            $page = $this->page($count, 20);
            $lists = M("users_share_log")
                ->where($map)
                ->order('id desc')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();

            $total_money = M("users_share_log")->where($map)->getField('sum(transfer_money)');
        }

        if(count($lists) > 0){
            $act_userids = array_keys(array_column($lists,null,'admin_id'));
            $act_userlist = M("users")->where(['id'=>['in',$act_userids]])->field('id,user_login')->select();
            $act_userlist = array_column($act_userlist,null,'id');
        }
        foreach ($lists as $key=>$val){
            $lists[$key]['action'] = $val['action']==1 ? '彩票分润结算	' : '-';
            $tenant_info = getTenantInfo($val['tenant_id']);
            $lists[$key]['tenant_name'] = isset($tenant_info['name']) ? $tenant_info['name'] : '-';
            $lists[$key]['act_user'] = isset($act_userlist[$val['admin_id']]) ? $act_userlist[$val['admin_id']]['user_login'] : '-';
            if($val['type']==3){ // 主播
                $b_userinfo = getUserInfo($val['beneficiary_id']);
                $lists[$key]['beneficiary_id'] = isset($b_userinfo['user_login']) ? $b_userinfo['user_login'].'（'.$val['beneficiary_id'].'）' : $val['beneficiary'];
            }else{ // 租户
                $b_tenant_info = getTenantInfo($val['beneficiary_id']);
                $lists[$key]['beneficiary_id'] = isset($b_tenant_info['name']) ? $b_tenant_info['name'].'（'.$val['beneficiary_id'].'）' : $val['beneficiary'];
            }
        }

        $this->assign('lists', $lists);
        $this->assign('total_money', $total_money);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function getclassify(){

        $bankaccount = $_POST['bankaccount'];

        $ownerInfo=M("bankcard_share")->where("bankname='{$bankaccount}'")->select();
        $res['code'] = 0;
        $res['msg'] = '获取成功';
        $res['info']= array_values($ownerInfo);
        echo json_encode($res);exit;
    }
    public function updatebatch(){

        $ids = $_POST['ids'];

        if(empty($ids)){
            $res['code'] = 1001;
            $res['msg'] = '请选择操作项';
            $res['info']= '';
            echo json_encode($res);exit;
        }
        $game_user = explode(',',$ids );

        foreach($game_user as $key => $value){
            $shareinfo=M("users_share")->where('id='.$value)->select();
            $action="批量修改分润金额，收款方 ：[ ".$shareinfo[0]['beneficiary']." ]"." 修改金额为：".$shareinfo[0]['money'] ;
            setAdminLog($action);
            if ($key == count($game_user)-1){
                $where .= "'". $value."'";
            }else{
                $where .= "'". $value."',";
            }
        }
        $where = " id in ($where)";

        $arr['money'] = 0;
        $arr['status'] = 1;
        $arr['updatetime'] = time();
        $ownerInfo=M("users_share")->where($where)->save($arr);
        $res['code'] = 0;
        $res['msg'] = '批量审核成功';
        $res['info']= array_values($ownerInfo);


        echo json_encode($res);exit;
    }


    	
}
