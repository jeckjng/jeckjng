<?php

/**
 * 提现
 */
namespace Admin\Controller;
use Admin\Model\UsersModel;
use Common\Controller\AdminbaseController;

use Admin\Cache\UsersAgentCache;

class CashController extends AdminbaseController {
    private $status_list = array(
        '0' => array(
            'name' => '未处理',
            'color' => '#f00',
        ),
        '1' => array(
            'name' => '提现成功',
            'color' => '#090',
        ),
        '2' => array(
            'name' => '拒绝提现',
            'color' => '#999',
        ),
    );

    var $type=array(
        '1'=>'支付宝',
        '2'=>'微信',
        '3'=>'银行卡',
    );

    public function index(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());
        $currency_list = currency_list();
        $status_list = $this->status_list;

        $map = array();
        $map['tenant_id'] = $tenant_id;
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $search_time_field = isset($param['search_time_type']) && $param['search_time_type'] == 1 ?  'addtime' : 'uptime';

        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : '';
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map[$search_time_field] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = '';
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map[$search_time_field] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = '';
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map[$search_time_field] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
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
        if(isset($param['currency_code']) && $param['currency_code'] !=''){
            $map['currency_code'] = $param['currency_code'];
        }
        if(isset($param['uid']) && $param['uid']!=''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login']!=''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['superior_user_login']) && $param['superior_user_login']!=''){
            $agent_user_info = UsersModel::getInstance()->getUserInfoWithUserLoginAndTid($param['superior_user_login'], $tenant_id, 'id,tenant_id');
            $sub_uids = UsersAgentCache::getInstance()->getUserAllSubUid($agent_user_info['tenant_id'], $agent_user_info['id']);
            if(empty($sub_uids)){
                $page = $this->page(0, $page_size);
                $this->assign('rnb_money', 0);
                $this->assign('total_money_list', []);
                $this->assign('list', []);
                $this->assign('type', $this->type);
                $this->assign("page", $page->show('Admin'));
                $this->assign('role_id',getRoleId());
                $this->assign('tenant_list',getTenantList());
                $this->assign('param',$param);
                $this->assign('status_list',$status_list);
                $this->assign('user_type_list',user_type_list());
                $this->assign('currency_list', $currency_list);
                $this->display();
            }
            $map['uid'] = ['in', $sub_uids];
        }

        if(isset($param['orderno']) && $param['orderno']!=''){
            $map['orderno'] = $param['orderno'];
        }
        if(isset($param['trade_no']) && $param['trade_no']!=''){
            $map['trade_no'] = $param['trade_no'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $model = M("users_cashrecord");
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

        $user_type_list = user_type_list();
        foreach($list as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $list[$key]['userinfo'] = $userinfo;
            $list[$key]['money'] = floatval($val['money']);
            $list[$key]['received_money'] = floatval($val['received_money']);
            $list[$key]['service_fee'] = floatval($val['service_fee']);
            $list[$key]['rnb_money']= floatval($val['rnb_money']);
            $list[$key]['rate']= floatval($val['rate']);
            $list[$key]['tenant_name']= getTenantInfo($val['tenant_id'])['name'];
            $list[$key]['status_name'] = '<span style="color: '.$status_list[$val['status']]['color'].';">'.$status_list[$val['status']]['name'].'</span>';
            $list[$key]['user_type_name'] = '<span style="color: '.$user_type_list[$val['user_type']]['color'].';">'.$user_type_list[$val['user_type']]['name'].'</span>';
        }

        $rnb_money = $model->where($map)->sum("rnb_money");
        $field = '';
        foreach ($currency_list as $key=>$val){
            $currency_code = $key;
            $field .= "sum(if((`currency_code` = '".$currency_code."'),`money`,0)) as ".str_replace('-','_', $currency_code).',';
        }
        $field = trim($field,',');
        $total_money_where = $map;
        if(isset($total_money_where['currency_code'])){
            unset($total_money_where['currency_code']);
        }
        $total_money = $model->where($total_money_where)->field($field)->find();
        $total_money_list = array();
        foreach ($total_money as $key=>$val){
            $currency_code = str_replace('_','-', strtoupper($key));
            if(!isset($map['currency_code']) || $map['currency_code'] == $currency_code){
                $total_money_list[$currency_code] = floatval($val);
            }else{
                $total_money_list[$currency_code] = 0;
            }
        }
        foreach ($total_money_list as $key=>$val){
            if(isset($currency_list[$key])){
                $total_money_list[$currency_list[$key]['name']] = $val;
                unset($total_money_list[$key]);
            }else if($key == 'E-CNY'){
                $total_money_list[$currency_list['e-CNY']['name']] = $val;
                unset($total_money_list[$key]);
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('rnb_money', floatval($rnb_money));
        $this->assign('total_money_list', $total_money_list);
    	$this->assign('list', $list);
    	$this->assign('type', $this->type);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('status_list',$status_list);
        $this->assign('user_type_list',user_type_list());
        $this->assign('currency_list', $currency_list);
        $this->assign('tenant_id', $tenant_id);
    	$this->display();
    }

    public function edit(){
        $id=intval($_GET['id']);
        $tenantId=getTenantIds();
        if($id){
            $cash=M("users_cashrecord")->find($id);
            $cash['userinfo']=M("users")->field("user_nicename")->where("id='$cash[uid]' and tenant_id=$tenantId ")->find();
            $cash['auth']=M("users_auth")->field("*")->where("uid='$cash[uid]' and tenant_id=$tenantId ")->find();

            $cash['money']= floatval($cash['money']);
            $cash['received_money']= floatval($cash['received_money']);
            $cash['service_fee']= floatval($cash['service_fee']);

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
            $status = $param['status'];
            $des = $param['des'];
            $redis =  connectRedis();
            $withdrawal_action = $redis->get('withdrawal_action_'.$id);
            if ($withdrawal_action){
                $this->error('已经有人在操作，请等待他人操作完成或者1小时后再来操作');
            }else{
                $redis->set('withdrawal_action_'.$id, get_current_admin_id(), 60*60);
            }
            $cash=M("users_cashrecord");
            $cashInfo  = $cash->where(['id'=>$id])->find();
            if ($cashInfo['status'] != 0){
                $redis->del('withdrawal_action_'.$id);
                $this->error('订单状态有误');
            }
            $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($param['uid']);
            if(!$user_info){
                $this->error('用户不存在');
            }
            if($user_info['user_type'] == '7' && $status == 1){ // 测试账号，不能审核成功
                $redis->del('withdrawal_action_'.$id);
                $this->error('测试账号，不能审核成功');
            }
            if(bcadd($param['received_money'], $param['service_fee'],4) != $cashInfo['money']){
                $redis->del('withdrawal_action_'.$id);
                $this->error(bcadd($param['received_money'], $param['service_fee'],4).' 到账金额 + 手续费 必须等于 提现金额 '.$cashInfo['money']);
            }
            $update_data = [
                'status'=>$status,
                'uptime'=>time(),
                'des'=>$des,
                'operated_by'=>get_current_admin_user_login()
            ];
            $action_remark = '';
            if($cashInfo['received_money'] != $param['received_money'] || $cashInfo['service_fee'] != $param['service_fee']){
                $update_data['received_money'] = $param['received_money'];
                $update_data['service_fee'] = $param['service_fee'];
                $action_remark = ' | 变动数据： 到账金额: '.$param['received_money'].', 手续费：'.$param['service_fee'];
            }

            $result =  $cash->where(['id'=>$id ])->save($update_data);

            $pay_coin = $param['pay_coin'];
            if ($pay_coin == 1){
                M("users")->where("id=%d ",$param['uid'])->setDec("frozen_coin", $cashInfo['coin_number']);
            }else{
                M("users")->where("id=%d ",$param['uid'])->setDec("frozen_votes", $cashInfo['coin_number']);
            }

            if($result){
                if($param['status']=='2'){
                    M("users")->where("id=%d ",$param['uid'])->setInc("withdrawable_money", $cashInfo['coin_number']);
                    if ($pay_coin == 1){
                        M("users")->where("id=%d ",$param['uid'])->setInc("coin", $cashInfo['coin_number']);
                        $pre_balance = $user_info['coin'];
                        $after_balance = bcadd($user_info['coin'], $cashInfo['coin_number'],4);
                    }else{
                        M("users")->where("id=%d ",$param['uid'])->setInc("votes", $cashInfo['coin_number']);
                        $pre_balance = $user_info['votes'];
                        $after_balance = bcadd($user_info['votes'], $cashInfo['coin_number'],4);
                    }
                    $this->addCoinrecord([
                        'type' => 'income',
                        'uid' => intval($param['uid']),
                        "user_login" => $user_info['user_login'],
                        "user_type" => intval($user_info['user_type']),
                        'addtime' => time(),
                        'tenant_id' => intval($user_info['tenant_id']),
                        'action' => 'withdrawn_reject',
                        "pre_balance" => floatval($pre_balance),
                        'totalcoin' => floatval($cashInfo['coin_number']),
                        "after_balance" => floatval($after_balance),
                    ]);
                    $action="修改提现记录：{$param['id']} - 拒绝";
                }else if($param['status']=='1'){
                    $action="修改提现记录：{$param['id']} - 同意";
                }else if($param['status']=='0'){
                    $action="修改提现记录：{$param['id']} - 审核中";
                }
                setAdminLog($action.$action_remark);
                delUserInfoCache($param['uid']);
                $redis->del('withdrawal_action_'.$id);
                $this->success('修改成功',U('Cash/index'));
            }else{
                $redis->del('withdrawal_action_'.$id);
                $this->error('修改失败');
            }
        }
    }

    public function export()
    {
        set_time_limit(60*60*5);
        ini_set('memory_limit', '4096M');

        $param = I('param.');
        $_GET['action_type'] = 'export';
        $_GET['p'] = 1;
        $_GET['num'] = 100000;

        if(!$param['start_time'] || !$param['end_time']){
            echo '<script language="JavaScript">;alert("请选择时间");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }
        $sep_time = strtotime($param['end_time'].' 23:59:59') -  strtotime($param['start_time'].' 00:00:00');
        if($sep_time < 0){
            echo '<script language="JavaScript">;alert("开始时间不能大于结束时间");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }
        if($sep_time > 60*60*24*31){
            echo '<script language="JavaScript">;alert("时间间隔不能大于31天");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }

        $data = $this->index();

        if(count($data['list']) > 100000){ // 限制数量10万条导出
            echo '<script language="JavaScript">;alert("导出数据数量过大，数量不能大于10万条");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }

        $xlsName  = "Excel";
        $xlsData = $data['list'];
        $tenantNameArray=array();

        $status_list = $this->status_list;
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['addtime'] = date("Y-m-d H:i:s",$v['addtime']);
            $xlsData[$k]['uptime'] = $v['uptime'] ? date("Y-m-d H:i:s",$v['uptime']) : '未处理';

            $xlsDataTenantId=$xlsData[$k]['tenant_id'];
            if(getRoleId() == 1 && !empty($xlsDataTenantId)){
                $tenantName=$tenantNameArray[$xlsDataTenantId];
                if(empty($tenantName)){
                    $tenantInfo=getTenantInfo($xlsDataTenantId);
                    if(!empty($tenantInfo)){
                        $tenantName=$tenantInfo['name'];
                    }else{
                        $tenantName='';
                    }
                    $tenantNameArray[$xlsDataTenantId]=$tenantName;
                }
                $xlsData[$k]['tenant_name']=$tenantName;
            }else{
                $xlsData[$k]['tenant_name'] = '-';
            }
            $xlsData[$k]['status'] = $status_list[$v['status']]['name'];
            $xlsData[$k]['money'] = floatval($v['money']);
            $xlsData[$k]['received_money'] = floatval($v['received_money']);
            $xlsData[$k]['service_fee'] = floatval($v['service_fee']);
            $xlsData[$k]['rnb_money']= floatval($v['rnb_money']);
            $xlsData[$k]['name'] = $v['name'] ? $v['name'] : '-';
            $xlsData[$k]['account_bank'] = $v['account_bank'] ? $v['account_bank'] : '-';
            $xlsData[$k]['account'] = $v['account'] ? $v['account'] : '-';
            $xlsData[$k]['cash_network_type'] = $v['cash_network_type'] ? $v['cash_network_type'] : '-';
            $xlsData[$k]['virtual_coin_address'] = $v['virtual_coin_address'] ? $v['virtual_coin_address'] : '-';
            $xlsData[$k]['trade_no'] = $v['trade_no'] ? $v['trade_no'] : '-';
        }
        $action="导出提现记录：".M("users_cashrecord")->getLastSql();
            setAdminLog($action);
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M',"N",'O','P','Q');

        $xlsCell  = array(
            array('id','序号'),
            array('uid','会员ID'),
            array('user_login','会员账号'),
            array('money','提现金额'),
            array('received_money','到账金额'),
            array('service_fee','手续费'),
            array('currency_code','提现币种'),
            array('votes','兑换点数'),
            array('name','名字'),
            array('account_bank','银行'),
            array('account','卡号'),
            array('cash_network_type','虚拟币网络类型'),
            array('virtual_coin_address','虚拟币地址'),
            array('trade_no','第三方支付订单号'),
            array('status','状态'),
            array('addtime','提交时间'),
            array('uptime','处理时间'),
        );
        if(getRoleId() == 1){
            array_push($cellName,'R');
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

    /*
     * 提现批量审核处理
     * */
    public  function batch_check(){
        if(!IS_POST){
            $this->error('请求方式错误');
        }
        $param = I('post.');
        $status = I('get.status');
        if(!$status || !in_array($status, [1,2])){
            $this->error('参数错误');
        }
        $ids = isset($param['ids']) ? $param['ids'] : [];
        if(empty($ids)){
            $this->error('请选择');
        }
        $redis = connectRedis();
        foreach ($ids as $key => $id) {
            if(!$id){
                continue;
            }
            $withdrawal_action = $redis->get('withdrawal_action_'.$id);
            if ($withdrawal_action){
                continue;
            }else{
                $redis->set('withdrawal_action_'.$id, get_current_admin_id(), 60*60);
            }
            $cashInfo = M("users_cashrecord")->where(['id'=>$id])->find();
            if ($cashInfo['status'] != 0){
                $redis->del('withdrawal_action_'.$id);
                continue;
            }
            $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($cashInfo['uid']);
            if(!$user_info){
                $redis->del('withdrawal_action_'.$id);
                continue;
            }
            if($user_info['user_type'] == '7' && $status == 1){ // 测试账号，不能审核成功
                $redis->del('withdrawal_action_'.$id);
                continue;
            }

            try {
                M()->startTrans();
                $des = '批量处理';
                $result =  M("users_cashrecord")->where(['tenant'=>intval($cashInfo['tenant_id']), 'id'=>intval($id)])->save(['status'=>$status,'uptime'=>time() ,'des'=>$des ]);
                if ($cashInfo['pay_coin'] == 1){
                    M("users")->where("id=%d ",$cashInfo['uid'])->setDec("frozen_coin", $cashInfo['coin_number']);
                }else{
                    M("users")->where("id=%d ",$cashInfo['uid'])->setDec("frozen_votes", $cashInfo['coin_number']);
                }
                if($result){
                    if($status=='2'){
                        M("users")->where("id=%d ",$cashInfo['uid'])->setInc("withdrawable_money", $cashInfo['coin_number']);
                        if ($cashInfo['pay_coin'] == 1){
                            M("users")->where("id=%d ",$cashInfo['uid'])->setInc("coin", $cashInfo['coin_number']);
                            $pre_balance = $user_info['coin'];
                            $after_balance = bcadd($user_info['coin'], $cashInfo['coin_number'],4);
                        }else{
                            M("users")->where("id=%d ",$cashInfo['uid'])->setInc("votes", $cashInfo['coin_number']);
                            $pre_balance = $user_info['votes'];
                            $after_balance = bcadd($user_info['votes'], $cashInfo['coin_number'],4);
                        }
                        $this->addCoinrecord([
                            'type' => 'income',
                            'uid' => intval($cashInfo['uid']),
                           "user_login" => $user_info['user_login'],
                            "user_type" => intval($user_info['user_type']),
                            'addtime' => time(),
                            'tenant_id' => intval($user_info['tenant_id']),
                            'action' => 'withdrawn_reject',
                            "pre_balance" => floatval($pre_balance),
                            'totalcoin' => floatval($cashInfo['coin_number']),
                            "after_balance" => floatval($after_balance),
                        ]);
                    }else if($status=='1'){
                    }
                }else{
                    $redis->del('withdrawal_action_'.$id);
                    M()->rollback();
                    setAdminLog('【提现审核批量处理】失败'.json_encode(['status'=>$status, 'id'=>$id]));
                    continue;
                }
                M()->commit();
            }catch (\Exception $e){
                M()->rollback();
                $redis->del('withdrawal_action_'.$id);
                setAdminLog('【提现审核批量处理】失败'.json_encode(['status'=>$status, 'id'=>$id]).' | '.$e->getMessage());
                continue;
            }
            $redis->del('withdrawal_action_'.$id);
            setAdminLog('【提现审核批量处理】成功'.json_encode(['status'=>$status, 'id'=>$id]));
            delUserInfoCache($cashInfo['uid']);
        }
        setAdminLog('【提现审核批量处理】'.json_encode($param));
        $this->success('操作成功', U('index',array('tenant_id'=>$param['tenant_id'])));
    }
    
}
