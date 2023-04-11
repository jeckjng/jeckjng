<?php

/**
 * 主播分成设置
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use PhpParser\Node\Expr\Variable;

class CommissionsetController extends AdminbaseController {

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

        if($_REQUEST['uid']!=''){
            $map['uid']=$_REQUEST['uid'];
        }
        if($_REQUEST['game_user_id']!=''){
            $map['game_user_id']=$_REQUEST['game_user_id'];
        }

    	$commissionSetModel = M("commission_set");
    	$count = $commissionSetModel->where($map)->count();
    	$page = $this->page($count, $page_size);
    	$lists = $commissionSetModel->where($map)->order("addtime DESC")->limit($page->firstRow . ',' . $page->listRows)->select();

        if($_SESSION['admin_type'] == 1){
            $userinfo = M("users")->where("user_login='".$_SESSION['name']."'")->find();

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
            if(!$value['game_user_id']){
                $userinfo = getUserInfo($value['uid']);
                if($userinfo['game_user_id']){
                    $lists[$key]['game_user_id'] = $userinfo['game_user_id'];
                    M("commission_set")->where(['id'=>$value['id']])->save(['game_user_id'=>$userinfo['game_user_id']]);
                }
            }
            if(!$value['tenant_id']){
                $userinfo = getUserInfo($value['uid']);
                if($userinfo['tenant_id']){
                    $lists[$key]['tenant_id'] = $userinfo['tenant_id'];
                    $value['tenant_id'] = $userinfo['tenant_id'];
                    M("commission_set")->where(['id'=>$value['id']])->save(['tenant_id'=>$userinfo['tenant_id']]);
                }
            }
            if(!empty($value['tenant_id'])){
                $tenantInfo=getTenantInfo($value['tenant_id']);
                if(!empty($tenantInfo)){
                    $lists[$key]['tenant_name']=$tenantInfo['name'];
                }
            }
            if($_SESSION['admin_type'] == 1){
                if(!in_array($value['user_name'],$author)){
                    unset($lists[$key]);
                }
            }
        }
        if($_SESSION['admin_type'] == 1){
            $page = $this->page(count($lists), 20);
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

    	$this->assign('lists', $lists);
    	$this->assign('type', $this->type);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('admin_type', $_SESSION['admin_type']);//标识 族长后台账号
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
    	$this->display();
    }
		
    function del(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
            if($id){
                $result=M("commission_set")->where("id=%d ",$id)->delete();
                    if($result){
                        $action="主播分成设置：{$id}";
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
    public  function add(){
        $config=getConfigPub();
        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        $this->assign('role_id', $role_id);
        $defautinfo = M('commission_config')->field('*')->find();
        $this->assign('defautinfo', $defautinfo); //家族彩票佣金

        $this->assign('admin_type', $_SESSION['admin_type']);//标识 族长后台账号
        $this->assign('anchor_profit_ratio', $config['anchor_profit_ratio']);//家族打赏佣金
        $this->assign('anchor_platform_ratio', $config['anchor_platform_ratio']); //家族彩票佣金
        $this->assign('defautinfo', $defautinfo); //家族彩票佣金
        $this->display();
    }
    public  function addpost(){
             if($_POST){
                 $anchor_commission = $_POST['anchor_commission'];
                 $anchor_betcommission = $_POST['anchor_betcommission'];
                 $config=getConfigPub();
                 if($anchor_commission>$config['anchor_profit_ratio']){
                     $this->error('主播打赏佣金，不能大于家族打赏佣金！');
                 }
                 if($anchor_betcommission>$config['anchor_platform_ratio']){
                     $this->error('主播彩票佣金，不能大于家族彩票佣金！');
                 }
                 $commissionconfig = M('commission_config')->field('anchor_limitcommission')->find();
                 if($anchor_commission<$commissionconfig['anchor_limitcommission']){
                     $this->error('主播打赏佣金，不能小于最低打赏佣金！');
                 }
                 if(empty($_POST['uid'])){
                     $this->error("请输入会员");
                 }
                 $whichTenant  = whichTenat();
                 if($whichTenant == 2 ){
                     $map['id'] = $_POST['uid'];
                 }else{
                     $map['game_user_id'] = $_POST['uid'];
                 }

                 if ($_SESSION['role_id'] != 1){
                     $map['tenant_id']=getTenantId();
                 }

                 $user =M('users')->where($map)->find();
                 if(!$user){
                     $this->error("该会员不存在");
                 }

                 $isuidadd =M('commission_set')->where("uid={$user['id']}")->find();
                 if($isuidadd){
                     $this->error("该会员已经添加了设置，请编辑");
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
                     if(!in_array($user['id'],$author)){
                         $this->error("该会员不在您账号的家族下！");
                     }

                 }


                 $defautinfo = M('commission_config')->field('*')->find();

                 $data['tenant_id'] = intval($user['tenant_id']);
                 $data['uid'] = $user['id'];
                 $data['addtime'] = time();
                 $data['anchor_commission']  = $anchor_commission;
                 $data['anchor_betcommission']  = $anchor_betcommission;
                 $data['anchor_profit_ratio']  = $config['anchor_profit_ratio'];
                 $data['anchor_platform_ratio']  = $config['anchor_platform_ratio'];
                 $data['user_name']  = $user['user_login'];
                 $data['operate_name'] = $_SESSION['name'];
                 $data['hour_money'] = isset($_POST['hour_money'])?$_POST['hour_money']:$defautinfo['hour_money'];
                 $data['hour_limit'] = isset($_POST['hour_limit'])?$_POST['hour_limit']:$defautinfo['hour_limit'];
                 $data['gift_limit'] = isset($_POST['gift_limit'])?$_POST['gift_limit']:$defautinfo['gift_limit'];
                 $data['hour_start'] = isset($_POST['hour_start'])?$_POST['hour_start']:0;
                 $data['hour_end'] = isset($_POST['hour_end'])?$_POST['hour_end']:0;

                 //成员管理，分成数据优化
                 $users_family =M("users_family")->where("game_user_id=".$_POST['uid'])->find();
                 if($users_family){
                     $usersfamily['gift_send'] = $config['anchor_profit_ratio'] - $anchor_commission;
                     $usersfamily['bet_send'] =  $config['anchor_platform_ratio'] - $anchor_betcommission;

                     M("users_family")->where(['id'=>$users_family['id']])->save($usersfamily);
                 }


                 $users= M("commission_set");
                 $users->create();
                 $result = $users->add($data);


             }

            $this->success('添加成功', U('index'));
        }
    public  function add_default(){
        $config=getConfigPub();
        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        $this->assign('role_id', $role_id);
        $defautinfo = M('commission_config')->field('*')->find();


        $this->assign('admin_type', $_SESSION['admin_type']);//标识 族长后台账号
        $this->assign('anchor_profit_ratio', $config['anchor_profit_ratio']);//家族打赏佣金
        $this->assign('anchor_platform_ratio', $config['anchor_platform_ratio']); //家族彩票佣金
        $this->assign('defautinfo', $defautinfo); //家族彩票佣金
        $this->display();
    }
    public function addpost_defult(){

        if($_POST) {
            $anchor_commission = isset($_POST['anchor_commission']) ? $_POST['anchor_commission'] : '';
            $anchor_limitcommission = isset($_POST['anchor_limitcommission']) ? $_POST['anchor_limitcommission'] : '';

            $anchor_betcommission = $_POST['anchor_betcommission'];
            $config = getConfigPub();
            if ($anchor_commission){
                if ($anchor_commission > $config['anchor_profit_ratio'] ||$anchor_commission < $anchor_limitcommission) {
                    $this->error('主播打赏佣金，只能在最小打赏佣金和家族佣金范围内！');
                }
            }
            if($anchor_limitcommission){
                if ($anchor_limitcommission > $config['anchor_profit_ratio']) {
                    $this->error('主播最小打赏佣金，不能大于家族打赏佣金！');
                }
            }

            if ($anchor_betcommission > $config['anchor_platform_ratio']) {
                $this->error('主播彩票佣金，不能大于家族彩票佣金！');
            }

            $data['addtime'] = time();
            $data['anchor_commission'] = $anchor_commission;
            $data['anchor_betcommission'] = $anchor_betcommission;
            $data['anchor_limitcommission'] = $anchor_limitcommission;
            $data['operate_name'] = $_SESSION['name'];
            $data['hour_money'] = $_POST['hour_money'];
            $data['hour_limit'] = $_POST['hour_limit'];
            $data['gift_limit'] = $_POST['gift_limit'];

            $isdefaut = M('commission_config')->field('id')->select();
            if ($isdefaut) {
                foreach ($isdefaut as $key => $value) {
                    M('commission_config')->where("id={$value['id']}")->delete();

                }
            }


            $users= M("commission_config");
            $users->create();

            $result = $users->add($data);
        }
        $this->success('添加成功','/Admin/Commissionset/add',3);


    }

    function edit(){
            $id=intval($_GET['id']);

            $tenantId=getTenantIds();
                if($id){
                    $defautinfo = M('commission_config')->field('*')->find();
                    $this->assign('defautinfo', $defautinfo);
                    $cash=M("commission_set")->find($id);
                    $this->assign('cash', $cash);
                    $config=getConfigPub();
                    $defautinfo = M('commission_config')->field('*')->find();
                    $whichTenant  = whichTenat();
                    $this->assign('defautinfo', $defautinfo); //最小打赏佣金
                    $this->assign('anchor_profit_ratio', $config['anchor_profit_ratio']);//家族打赏佣金
                    $this->assign('anchor_platform_ratio', $config['anchor_platform_ratio']); //家族彩票佣金
                    $this->assign('admin_type', $_SESSION['admin_type']);
                    $this->assign('whichTenant', $whichTenant);

                }else{
                    $this->error('数据传入失败！');
                }
                $this->display();
    }

    function editpost(){
            if(IS_POST){

                $anchor_commission = $_POST['anchor_commission'];
                $anchor_betcommission = $_POST['anchor_betcommission'];
                $config=getConfigPub();
                if($anchor_commission>$config['anchor_profit_ratio']){
                    $this->error('主播打赏佣金，不能大于家族打赏佣金！');
                }
                if($anchor_betcommission>$config['anchor_platform_ratio']){
                    $this->error('主播彩票佣金，不能大于家族彩票佣金！');
                }
                $commissionconfig = M('commission_config')->field('anchor_limitcommission')->find();
                if($anchor_commission<$commissionconfig['anchor_limitcommission']){
                    $this->error('主播打赏佣金，不能小于最低打赏佣金！');
                }

                $whichTenant  = whichTenat();
                if($whichTenant == 2 ){
                    $map['id'] = $_POST['uid'];
                }else{
                    $map['game_user_id'] = $_POST['uid'];
                }

                if ($_SESSION['role_id'] != 1){
                    $map['tenant_id']=getTenantId();
                }
                $user =M('users')->where($map)->find();
                if(!$user){
                    $this->error("该会员不存在");
                }

                $isuidadd =M('commission_set')->where("uid={$user['id']}")->select();
                foreach ($isuidadd as $key=>$val){
                    if($val['id'] != $_POST['id']){
                        $this->error("该会员已经添加了设置，请编辑");
                    }
                }

                $defautinfo = M('commission_config')->field('*')->find();
                $data['uid'] = $user['id'];
                $data['addtime'] = time();
                $data['anchor_commission']  = $anchor_commission;
                $data['anchor_betcommission']  = $anchor_betcommission;
                $data['anchor_profit_ratio']  = $config['anchor_profit_ratio'];
                $data['anchor_platform_ratio']  = $config['anchor_platform_ratio'];
                $data['user_name']  = $user['user_login'];
                $data['operate_name'] = $_SESSION['name'];


                if( $_SESSION['admin_type'] == 1){
                    $commissioninfo =M('commission_set')->where("uid={$user['id']}")->find();
                    $data['hour_money'] = $commissioninfo['hour_money'];
                    $data['hour_limit'] = $commissioninfo['hour_limit'];
                    $data['gift_limit'] = $commissioninfo['gift_limit'];

                }else{

                    $data['hour_money'] = isset($_POST['hour_money'])?$_POST['hour_money']:$defautinfo['hour_money'];
                    $data['hour_limit'] = isset($_POST['hour_limit'])?$_POST['hour_limit']:$defautinfo['hour_limit'];
                    $data['gift_limit'] = isset($_POST['gift_limit'])?$_POST['gift_limit']:$defautinfo['gift_limit'];
                }

                //成员管理，分成数据优化
                $users_family =M("users_family")->where("game_user_id=".$_POST['uid'])->find();
                if($users_family){
                    $usersfamily['gift_send'] = $config['anchor_profit_ratio'] - $anchor_commission;
                    $usersfamily['bet_send'] =  $config['anchor_platform_ratio'] - $anchor_betcommission;

                    M("users_family")->where(['id'=>$users_family['id']])->save($usersfamily);
                }

                $data['hour_start'] = isset($_POST['hour_start'])?$_POST['hour_start']:0;
                $data['hour_end'] = isset($_POST['hour_end'])?$_POST['hour_end']:0;

                $users= M("commission_set");
                $users->create();
                $result = $users->where("id={$_POST['id']}")->save($data);
                if($result){
                    $this->success("设置成功！",U('index'));
                }else{
                    $this->error("操作失败！");
                }

            }
    }
    function export()
		{
			if($_REQUEST['status']!=''){
                $map['status']=$_REQUEST['status'];
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
            if($_REQUEST['keyword']!=''){
                $map['uid|orderno|trade_no']=array("like","%".$_REQUEST['keyword']."%"); 
            }
            $tenantId=getTenantIds();
            //判断是否为超级管理员
            $role_id=$_SESSION['role_id'];
            $showTenant=false;
            if($role_id==1){
                $showTenant=true;
            }
            else{
                //租户id条件
                $map['tenant_id']=$tenantId;
            }
            $xlsName  = "Excel";
            $cashrecord=M("users_cashrecord");
            $xlsData=$cashrecord->where($map)->order("addtime DESC")->select();
            $tenantNameArray=array();
            foreach ($xlsData as $k => $v)
            {
                $userinfo=M("users")->field("user_nicename")->where("id='$v[uid]'")->find();
                $xlsData[$k]['user_nicename']= $userinfo['user_nicename']."(".$v['uid'].")";
                $xlsData[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']); 
                $xlsData[$k]['uptime']=date("Y-m-d H:i:s",$v['uptime']);

                $xlsDataTenantId=$xlsData[$k]['tenant_id'];
                if($showTenant && !empty($xlsDataTenantId)){
                    $tenantName=$tenantNameArray[$xlsDataTenantId];
                    if(empty($tenantName)){
                        $tenantInfo=getTenantInfo($xlsDataTenantId);
                        if(!empty($tenantInfo)){
                            $tenantName=$tenantInfo['name'];
                        }
                        else{
                            $tenantName='';
                        }
                        $tenantNameArray[$xlsDataTenantId]=$tenantName;
                    }
                    $xlsData[$k]['tenant_name']=$tenantName;
                }
                if($v['status']=='0'){ $xlsData[$k]['status']="处理中";}else if($v['status']=='2'){$xlsData[$k]['status']="提现失败";}else{ $xlsData[$k]['status']="提现完成";} 
            }
            $action="导出提现记录：".M("users_cashrecord")->getLastSql();
                setAdminLog($action);
            $cellName = array('A','B','C','D','E','F','G','H');

            $xlsCell  = array(
                array('id','序号'),
                array('user_nicename','会员'),
                array('money','提现金额'),
                array('votes','兑换点数'),
                array('trade_no','第三方支付订单号'),
                array('status','状态'),
                array('addtime','提交时间'),
                array('uptime','处理时间'),
            );
            if($showTenant){
                array_push($cellName,'I');
                array_push($xlsCell,array('tenant_name','所属租户'));
            }
            exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
		}
    
}
