<?php

/**
 *转账，转入转出
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TransferController extends AdminbaseController {

    private $status_list = array(
        '0' => array(
            'name' => '审核中',
            'color' => '#f00',
        ),
        '1' => array(
            'name' => '成功',
            'color' => '#090',
        ),
        '2' => array(
            'name' => '驳回',
            'color' => '#999',
        ),
    );

    private $type_list = array(
        '1' => array(
            'name' => '转出到余额',
        ),
        '2' => array(
            'name' => '转出到银行卡',
        ),
        '3' => array(
            'name' => '余额转入',
        ),
        '4' => array(
            'name' => '银行转入',
        ),
    );

    public function index(){
        $param = I('param.');

        $map = array();
        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']){
                $map['tenant_id'] = intval($param['tenant_id']);
            }
        }else{
            //租户id条件
            $map['tenant_id'] = intval(getTenantIds());
        }

        $param['tenant_id'] = isset($param['tenant_id']) ? $param['tenant_id'] : '';;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : '';
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = '';
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = '';
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $param['start_time'] = '';
            $param['end_time'] = '';
        }
        if(isset($param['status']) && $param['status'] !='-1'){
            $map['status']=$param['status'];
        }else{
            $param['status'] = '-1';
        }
        if(isset($param['uid']) && $param['uid']!=''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login']!=''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $model = M("user_transfer_yuebao");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);

        $list = $model
            ->where($map)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        // 如果是导出，则直接return源数据
        if(isset($_GET['action_type']) && $_GET['action_type'] == 'export'){
            return array('count'=>$count, 'list'=>$list);
        }

        $status_list = $this->status_list;
        $type_list = $this->type_list;
        foreach($list as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $list[$key]['userinfo'] = $userinfo;
            $list[$key]['status_name'] = '<span style="color: '.$status_list[$val['status']]['color'].';">'.$status_list[$val['status']]['name'].'</span>';
            $list[$key]['type_name'] = isset($type_list[$val['type']]) ? $type_list[$val['type']]['name'] : $val['type'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('list', $list);
        $this->assign('param', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('status_list',$status_list);
        $this->assign('user_type_list',user_type_list());
        $this->assign('type_list',$type_list);
        $this->display();
    }

    public function edit(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if($id){
            $cash=M("user_transfer_yuebao")->find($id);
            $this->assign('cash', $cash);
            $this->assign('type', $this->type);
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }

    public function edit_post(){
        if(IS_POST){
            $param = I('post.');

            if($param['status']=='0'){
                $this->error('未修改订单状态');
            }
            $id  = $param['id'];
            $transfer=M("user_transfer_yuebao");
            $transferInfo  = $transfer->where(['id'=>$id])->find();

            $userInfo=M("users")->field("coin,user_type,user_login")->where("id=".$transferInfo['uid'])->find();
            $mark= $param['mark'];

            if($transferInfo){

                if($param['status'] == 1){
                    if($transferInfo['type']==2){
                        $action="扣减余额宝金额 ：{$param['id']} ";
                        $insert=array(
                            "type"=>'expend',
                            "action"=>'yuebaoout_bank',
                            "uid"=>$transferInfo['uid'],
                            'user_login' => $userInfo['user_login'],
                            'user_type'=>$userInfo['user_type'],
                            "pre_balance" => floatval($userInfo['coin']),
                            "after_balance" => floatval($userInfo['coin']),
                            "giftid"=>0,
                            "totalcoin"=>$transferInfo['amount'],
                            "addtime"=>time(),
                            'tenant_id' =>getTenantId(),
                        );
                        $this->addCoinrecord($insert);
                    }
                    if($transferInfo['type']==4){
                        M("users")->where("id=%d ",$transferInfo['uid'])->setInc("yeb_balance", $transferInfo['amount']);
                        $action="增加余额宝金额 ：{$param['id']} ";
                        $insert=array(
                            "type"=>'expend',
                            "action"=>'yuebaoin_bank',
                            "uid"=>$transferInfo['uid'],
                            'user_login' => $userInfo['user_login'],
                            'user_type'=>$userInfo['user_type'],
                            "pre_balance" => floatval($userInfo['coin']),
                            "after_balance" => floatval($userInfo['coin']),
                            "giftid"=>0,
                            "totalcoin"=>$transferInfo['amount'],
                            "addtime"=>time(),
                            'tenant_id' =>getTenantId(),
                        );
                        $this->addCoinrecord($insert);
                    }
                }else{
                    if($transferInfo['type']==2){
                        M("users")->where("id=%d ",$transferInfo['uid'])->setInc("yeb_balance", $transferInfo['amount']);
                        $action="扣减余额宝金额 ：{$param['id']} ";
                    }
                    $action="驳回余额宝操作  ：{$param['id']} ";
                }
                M("user_transfer_yuebao")->where(['id'=>$id ])->save([
                    'status' => $param['status'],
                    'updatetime' => time(),
                    'mark' => $mark,
                    'operated_by' => get_current_admin_user_login(),
                ]);
                setAdminLog($action);

                delUserInfoCache($transferInfo['uid']);

                $this->success('修改成功',U('Transfer/index'));
            }else{

                $this->error('修改失败');
            }
        }
    }

    public function export()
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

    public function getCash(){
        $res=array("code"=>0,"msg"=>"获取成功","info"=>array());
        $role_id=$_SESSION['role_id'];
        $rule_name = '提现记录';
        $isauth = getAuth($role_id,$rule_name);
        if($isauth == 1){
            $charge=M("users_cashrecord");
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

    public function record(){
        $param = I('param.');

        $map = array();
        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']){
                $map['tenant_id'] = intval($param['tenant_id']);
            }
        }else{
            //租户id条件
            $map['tenant_id'] = intval(getTenantIds());
        }

        $param['tenant_id'] = isset($param['tenant_id']) ? $param['tenant_id'] : '';;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : '';
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = '';
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = '';
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $param['start_time'] = '';
            $param['end_time'] = '';
        }
        if(isset($param['uid']) && $param['uid']!=''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login']!=''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $model = M("yuebao_rate");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);

//        echo $model->getLastSql();

        $list = $model
            ->where($map)
            ->order("addtime DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach($list as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $list[$key]['userinfo'] = $userinfo;
            $list[$key]['rate_money'] = floatval($val['rate_money']);
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('list', $list);
        $this->assign('param', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }


}
