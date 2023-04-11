<?php

/**
 * 消费记录
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class ChargeWithdrawalRecordController extends AdminbaseController
{
    private $action_list = array('offline_charge'=>'线下入款', 'offline_virtual_charge'=>'线下虚拟币入款', 'online_charge'=>'线上入款', 'manual_charge'=>'手动充值', 'withdrawn_success'=>'提现成功');

    public function user_charge_withdrawal(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());
        $action_list = $this->action_list;

        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map[] = ['tenant_id'=>$tenant_id];
        $map[] = ['type'=>['in','income,expend']];
        $map[] = ['action'=>['in', implode(',', array_keys($action_list))]];

        $timeselect = get_timeselect(); // 获取时间格式
        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : '';
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $map['addtime'] = array("between", array(strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])+86399));
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];;
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $count = M('users_coinrecord')->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = M('users_coinrecord')->where($map)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

        // 如果是导出，则直接return源数据
        if(isset($_GET['action_type']) && $_GET['action_type'] == 'export'){
            return array('count'=>$count, 'list'=>$list);
        }

        $uids = count($list) > 0 ? array_column($list,'uid', null) : [];
        $user_list = count($uids) > 0 ? M('users')->where(['id'=>['in',$uids]])->select() : [];
        $user_list = count($user_list) > 0 ? array_column($user_list,null,'id') : [];

        foreach($list as $key=>$val){
            $user_info = isset($user_list[$val['uid']]) ? $user_list[$val['uid']] : [];
            if($val['user_type'] == 0 && isset($user_info['user_type']) && $user_info['user_type']){
                M("users_coinrecord")->where(['id'=>$val['id']])->save(['user_type'=>$user_info['user_type']]);
            }
            $list[$key]['user_login'] = isset($user_info['user_login']) ? $user_info['user_login'] : $val['uid'];
            $list[$key]['time'] = $val['addtime'] ? date('Y-m-d H:i:s', $val['addtime']) : $val['addtime'];
            $list[$key]['type_name'] = isset($action_list[$val['action']]) ? $action_list[$val['action']] : $val['action'];
            $list[$key]['money'] = $val['totalcoin'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign("list",$list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('tenant_id',$tenant_id);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }

    public function user_charge_withdrawal_export(){
        $param = I('param.');
        $timeselect = get_timeselect(); // 获取时间格式
        if(!isset($param['start_time']) || $param['start_time'] == ''){
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
        }
        if(!isset($param['end_time']) || $param['end_time'] == ''){
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];;
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if(strtotime(($param['end_time']))-(3600*24*31) > strtotime($param['start_time'])){
            $this->error("导出数据，间隔不能大于31天");
        }

        $_GET['action_type'] = 'export';
        $_GET['p'] = 1;
        $_GET['num'] = 200000;
        $data = $this->user_charge_withdrawal();

        if(count($data['list']) > 100000){ // 限制数量10万条导出
            echo '<script language="JavaScript">;alert("导出数据数量过大，数量不能大于10万条");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }

        $list = $data['list'];

        $uids = count($list) > 0 ? array_column($list,'uid', null) : [];
        $user_list = count($uids) > 0 ? M('users')->where(['id'=>['in',$uids]])->select() : [];
        $user_list = count($user_list) > 0 ? array_column($user_list,null,'id') : [];

        $action_list = $this->action_list;
        try {
            $export_data=[];
            foreach($list as $key=>$val){
                $user_login = isset($user_list[$val['uid']]) ? $user_list[$val['uid']]['user_login'] : $val['uid'];
                $temp['uid-user_login'] = $val['uid'].' （'.$user_login.'）';
                $temp['time'] = $val['addtime'] ? date('Y-m-d H:i:s', $val['addtime']) : $val['addtime'];
                $temp['type_name'] = isset($action_list[$val['action']]) ? $action_list[$val['action']] : $val['action'];
                $temp['money'] = $val['totalcoin'];
                array_push($export_data, $temp);
            }
            $header=array(
                'title' => array(
                    'uid-user_login'    =>  '会员（ID）:25',
                    'time'              =>  '时间:20',
                    'type_name'         =>  '入款方式:15',
                    'money'             =>  '金额:20',
                ),
                'dataType' => array(
                    'id'=>'str',
                ),
            );
            $filename="用户出入款报表";
            $return_url = count($export_data) > 10000 ? true : false;
            $excel_filname = $return_url == true ? $filename.'-'.md5(json_encode($param)) : $filename." (".count($export_data)."条)-".date('Y-m-d H-i-s');
            include EXTEND_PATH ."util/UtilPhpexcel.php";
            $Phpexcel = new \UtilPhpexcel();
            $downurl = "/".$Phpexcel::export_excel_v1($export_data, $header,$excel_filname, $return_url);
        }catch (\Exception $e){
            $msg = $e->getMessage();
            $this->error($msg);
        }

        if($downurl){
            $output_filename = $filename." (".count($export_data)."条)-".date('Y-m-d H-i-s');
            header('pragma:public');
            header("Content-Disposition:attachment;filename=".$output_filename.".xls"); //下载文件，filename 为文件名
            echo file_get_contents($downurl);
            exit;
        }

        $this->success("导出成功",$downurl);
    }

    // 线上入款
    public function index(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());
        $param['tenant_id'] = $tenant_id;

        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['a.tenant_id'] = $param['tenant_id'];

        $timeselect = get_timeselect(); // 获取时间格式
        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : '';
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['a.addtime'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['a.addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['a.addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $map['a.addtime'] = array("between", array(strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])+86399));
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];;
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['a.user_type'] = $param['user_type'];
            }
        }else{
            $map['a.user_type'] = 2;
            $param['user_type'] = 2;
        }

        //币种搜索, 默认一个币
        if (!isset($param['rate_id']) || !$param['rate_id']){
            $param['rate_id'] = M('rate')->where(['is_virtual'=>0,'tenant_id'=>$param['tenant_id']])->order('sort asc')->getField('id');
        }

        $channel = M('channel')->field('id')->where(['coin_id'=>$param['rate_id']])->select();
        $type_list = [1=>'线上入款',2=>'线下入款',3=>'手动充值'];
        if ($channel){
            $channelIds = array_column($channel,'id',NULL);
            $map['b.id'] = ['in',$channelIds];
            $charge=M("users_charge");
            $lists = $charge
                ->alias('a')
                ->field('
                    a.type,
                    a.tenant_id,
                    min(a.addtime) start_time,
                    max(a.addtime) end_time,
                    sum(a.rnb_money) as rnb_money_sum,
                    sum(a.money) as money_sum,
                    count(a.id) as order_sum
                ')
                ->where($map)
                ->join('cmf_channel as b on a.channel_id=b.id','left')
                ->group('a.type,a.tenant_id')
                ->select();
            foreach ($lists as $key=>$val){
                $lists[$key]['user_sum'] = count($charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->field('count(a.id) as user_sum')->where(['a.type'=>$val['type']])->group('a.uid')->select());
                $lists[$key]['success_rnb_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>2])->sum('a.rnb_money')?:0;
                $lists[$key]['success_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>2])->sum('a.money')?:0;
                $lists[$key]['error_rnb_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>['neq',2]])->sum('a.rnb_money')?:0;
                $lists[$key]['error_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>['neq',2]])->sum('a.money')?:0;
                $tenantInfo = getTenantInfo($val['tenant_id']);
                if (!empty($tenantInfo)) {
                    $lists[$key]['tenant_name'] = $tenantInfo['name'];
                }
            }
            if ($param['rate_id'] == 4){
                unset($map['b.id']);
                $map['type'] = 3;
                $lists2 =  $charge
                    ->alias('a')
                    ->field('
                        a.type,
                        a.tenant_id,
                        min(a.addtime) start_time,
                        max(a.addtime) end_time,
                        sum(a.rnb_money) as rnb_money_sum,
                        sum(a.money) as money_sum,
                        count(a.id) as order_sum
                    ')
                    ->where($map)
                    ->group('a.type,a.tenant_id')
                    ->select();
                foreach ($lists2 as $key=>$val){
                    $lists2[$key]['user_sum'] = count($charge->alias('a')->field('count(id) as user_sum')->where($map)->group('uid')->select());
                    $lists2[$key]['success_rnb_money'] = $charge->alias('a')->where($map)->where(['type'=>$val['type'],'status'=>2])->sum('rnb_money')?:0;
                    $lists2[$key]['success_money'] = $charge->alias('a')->where($map)->where(['type'=>$val['type'],'status'=>2])->sum('money')?:0;
                    $lists2[$key]['error_rnb_money'] = $charge->alias('a')->where($map)->where(['type'=>$val['type'],'status'=>['neq',2]])->sum('rnb_money')?:0;
                    $lists2[$key]['error_money'] = $charge->alias('a')->where($map)->where(['type'=>$val['type'],'status'=>['neq',2]])->sum('money')?:0;
                    $tenantInfo = getTenantInfo($val['tenant_id']);
                    if (!empty($tenantInfo)) {
                        $lists2[$key]['tenant_name'] = $tenantInfo['name'];
                    }
                }
                $lists = array_merge($lists,$lists2);
            }
        }else{
            $lists = [];
        }
        $last_names = array_column($lists,'tenant_id');
        array_multisort($last_names,$lists);

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign("tenant_list",getTenantList());
        $this->assign('lists', $lists);
        $this->assign('type', $type_list);
        $this->assign('rate_list', getRateList($param['tenant_id']));
        $this->assign('param', $param);
        $this->assign('role_id',getRoleId());
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }

    public function draw_money(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());
        $param['tenant_id'] = $tenant_id;

        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = $param['tenant_id'];

        $timeselect = get_timeselect(); // 获取时间格式
        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : '';
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $map['addtime'] = array("between", array(strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])+86399));
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];;
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }

        //币种搜索
        if (!isset($param['rate_code']) || !$param['rate_code']){
            //默认人民币
            $param['rate_code'] = 'CNY';
            $map['currency_code'] = 'CNY';
        }else{
            $map['currency_code'] = $param['rate_code'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $cashrecord=M("users_cashrecord");
        $count = $cashrecord
            ->field('
                tenant_id,
                min(addtime) start_time,
                max(addtime) end_time,
                sum(rnb_money) as rnb_money_sum,
                sum(money) as money_sum,
                count(id) as order_sum
            ')
            ->where($map)
            ->group('tenant_id')
            ->select();
        $page = $this->page(count($count));
        $lists = $cashrecord
            ->field('
                tenant_id,
                min(addtime) start_time,
                max(addtime) end_time,
                sum(rnb_money) as rnb_money_sum,
                sum(money) as money_sum,
                count(id) as order_sum
            ')
            ->where($map)
            ->group('tenant_id')
            ->order("tenant_id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        if(count($lists) > 100000){ // 限制数量10万条导出
            echo '<script language="JavaScript">;alert("导出数据数量过大，数量不能大于10万条");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }

        foreach ($lists as $key=>$val){
            $lists[$key]['user_sum'] = count($cashrecord->field('count(id) as user_sum')->where($map)->group('uid')->select());
            $lists[$key]['success_rnb_money'] = $cashrecord->where($map)->where(['status'=>1])->sum('rnb_money') ?: 0;
            $lists[$key]['error_rnb_money'] = $cashrecord->where($map)->where(['status'=>['neq',1]])->sum('rnb_money') ?: 0;
            $lists[$key]['success_money'] = $cashrecord->where($map)->where(['status'=>1])->sum('money') ?: 0;
            $lists[$key]['error_money'] = $cashrecord->where($map)->where(['status'=>['neq',1]])->sum('money') ?: 0;
            $tenantInfo = getTenantInfo($val['tenant_id']);
            if (!empty($tenantInfo)) {
                $lists[$key]['tenant_name'] = $tenantInfo['name'];
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign("tenant_list",getTenantList());
        $this->assign('lists', $lists);
        $this->assign('rate_list', getRateList($param['tenant_id']));
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }

    public function draw_export(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());
        $param['tenant_id'] = $tenant_id;

        $map['tenant_id'] = $param['tenant_id'];

        $timeselect = get_timeselect(); // 获取时间格式
        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : 'today';
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $map['addtime'] = array("between", array(strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])+86399));
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];;
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }

        //币种搜索
        if (!isset($_REQUEST['rate_code']) || !$_REQUEST['rate_code']){
            //默认人民币
            $_GET['rate_code'] = 'CNY';
            $map['currency_code'] = 'CNY';
        }else{
            $map['currency_code'] = $_REQUEST['rate_code'];
            $_GET['rate_code'] =  $_REQUEST['rate_code'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        // 租户
        $tenantList = M("tenant")->field('id,name')->select();
        $newTenantList = array();
        foreach ($tenantList as $tenantValue){
            $newTenantList[$tenantValue['id']] = $tenantValue['name'];
        }

        $cashrecord=M("users_cashrecord");
        $lists = $cashrecord
            ->field('
                tenant_id,
                min(addtime) start_time,
                max(addtime) end_time,
                sum(rnb_money) as rnb_money_sum,
                sum(money) as money_sum,
                count(id) as order_sum
            ')
            ->where($map)
            ->group('tenant_id')
            ->select();

        if(count($lists) > 100000){ // 限制数量10万条导出
            echo '<script language="JavaScript">;alert("导出数据数量过大，数量不能大于10万条");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }

        foreach ($lists as $key=>$val){
            $lists[$key]['user_sum'] = count($cashrecord->field('count(id) as user_sum')->where($map)->group('uid')->select());
            $lists[$key]['success_rnb_money'] = $cashrecord->where($map)->where(['status'=>1])->sum('rnb_money') ?: 0;
            $lists[$key]['error_rnb_money'] = $cashrecord->where($map)->where(['status'=>['neq',1]])->sum('rnb_money') ?: 0;
            $lists[$key]['success_money'] = $cashrecord->where($map)->where(['status'=>1])->sum('money') ?: 0;
            $lists[$key]['error_money'] = $cashrecord->where($map)->where(['status'=>['neq',1]])->sum('money') ?: 0;
            $lists[$key]['add_time'] = date('Y-m-d', $val['start_time']) . '-' . date('Y-m-d', $val['end_time']);
            $lists[$key]['number_count'] = $val['order_sum'].'/'.$lists[$key]['user_sum'];
            $tenantInfo = getTenantInfo($val['tenant_id']);
            if (!empty($tenantInfo)) {
                $lists[$key]['tenant_name'] = $tenantInfo['name'];
            }
        }

        $xlsName = "Excel";
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H','I','J');
        $xlsCell = array(
            array('tenant_name', '租户'),
            array('add_time', '时间'),
            array('pay_type', '提现方式'),
            array('rnb_money_sum', '总价（CNY）'),
            array('money_sum', '总价（'.$map['currency_code'].'）'),
            array('number_count', '单数/人数'),
            array('success_rnb_money', '成功金额（CNY）'),
            array('success_money', '成功金额（'.$map['currency_code'].'）'),
            array('error_rnb_money', '失败金额（CNY）'),
            array('error_money', '失败金额（'.$map['currency_code'].'）'),
        );
        exportExcel($xlsName, $xlsCell, $lists, $cellName);
    }

    public function export()
    {
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());
        $param['tenant_id'] = $tenant_id;

        $map['a.tenant_id'] = $param['tenant_id'];

        $timeselect = get_timeselect(); // 获取时间格式
        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : 'today';
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['a.addtime'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['a.addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['a.addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $map['a.addtime'] = array("between", array(strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])+86399));
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];;
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['a.user_type'] = $param['user_type'];
            }
        }else{
            $map['a.user_type'] = 2;
            $param['user_type'] = 2;
        }

        //币种搜索
        if (!isset($param['rate_id']) || !$param['rate_id']){
            //默认人民币
            $param['rate_id'] = M('rate')->where(['is_virtual'=>0,'tenant_id'=>$param['tenant_id']])->order('sort asc')->getField('id');
        }
        $rate_list = getRateList($param['tenant_id']);
        $rate_list = count($rate_list) > 0 ? array_column($rate_list, null, 'id') : [];
        $channel = M('channel')->field('id')->where(['coin_id'=>$param['rate_id']])->select();
        $type_list = [1=>'线上入款',2=>'线下入款',3=>'手动充值'];
        if ($channel){
            $channelIds = array_column($channel,'id',NULL);
            $map['b.id'] = ['in',$channelIds];
            $charge=M("users_charge");
            $lists = $charge
                ->alias('a')
                ->field('
                    a.type,
                    a.tenant_id,
                    min(a.addtime) start_time,
                    max(a.addtime) end_time,
                    sum(a.rnb_money) as rnb_money_sum,
                    sum(a.money) as money_sum,
                    count(a.id) as order_sum
                ')
                ->where($map)
                ->join('cmf_channel as b on a.channel_id=b.id','left')
                ->group('a.type,a.tenant_id')
                ->select();
            foreach ($lists as $key=>$val){
                $lists[$key]['user_sum'] = count($charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->field('count(a.id) as user_sum')->where(['a.type'=>$val['type']])->group('a.uid')->select());
                $lists[$key]['success_rnb_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>2])->sum('a.rnb_money')?:0;
                $lists[$key]['success_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>2])->sum('a.money')?:0;
                $lists[$key]['error_rnb_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>['neq',2]])->sum('a.rnb_money')?:0;
                $lists[$key]['error_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>['neq',2]])->sum('a.money')?:0;
                $tenantInfo = getTenantInfo($val['tenant_id']);
                if (!empty($tenantInfo)) {
                    $lists[$key]['tenant_name'] = $tenantInfo['name'];
                }
            }
            if ($param['rate_id'] == 4){
                unset($map['b.id']);
                $map['type'] = 3;
                $lists2 =  $charge
                    ->alias('a')
                    ->field('
                        a.type,
                        a.tenant_id,
                        min(a.addtime) start_time,
                        max(a.addtime) end_time,
                        sum(a.rnb_money) as rnb_money_sum,
                        sum(a.money) as money_sum,
                        count(a.id) as order_sum
                    ')
                    ->where($map)
                    ->group('a.type,a.tenant_id')
                    ->select();

                if(count($lists2) > 100000){ // 限制数量10万条导出
                    echo '<script language="JavaScript">;alert("导出数据数量过大，数量不能大于10万条");</script>;';
                    echo '<script language="JavaScript">;history.back();</script>';
                    exit;
                }

                foreach ($lists2 as $key=>$val){
                    $lists2[$key]['user_sum'] = count($charge->alias('a')->field('count(id) as user_sum')->where($map)->group('uid')->select());
                    $lists2[$key]['success_rnb_money'] = $charge->alias('a')->where($map)->where(['type'=>$val['type'],'status'=>2])->sum('rnb_money')?:0;
                    $lists2[$key]['success_money'] = $charge->alias('a')->where($map)->where(['type'=>$val['type'],'status'=>2])->sum('money')?:0;
                    $lists2[$key]['error_rnb_money'] = $charge->alias('a')->where($map)->where(['type'=>$val['type'],'status'=>['neq',2]])->sum('rnb_money')?:0;
                    $lists2[$key]['error_money'] = $charge->alias('a')->where($map)->where(['type'=>$val['type'],'status'=>['neq',2]])->sum('money')?:0;
                    $tenantInfo = getTenantInfo($val['tenant_id']);
                    if (!empty($tenantInfo)) {
                        $lists2[$key]['tenant_name'] = $tenantInfo['name'];
                    }
                }
                $lists = array_merge($lists,$lists2);
            }
        }else{
            $lists = [];
        }
        $last_names = array_column($lists,'tenant_id');
        array_multisort($last_names,$lists);

        $xlsName = "Excel";
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H','I','J');
        $xlsCell = array(
            array('tenant_name', '租户'),
            array('add_time', '时间'),
            array('pay_type', '入款方式'),
            array('rnb_money_sum', '总价（CNY）'),
            array('money_sum', '总价（'.$rate_list[$_GET['rate_id']]['code'].'）'),
            array('number_count', '单数/人数'),
            array('success_rnb_money', '成功金额（CNY）'),
            array('success_money', '成功金额（'.$rate_list[$_GET['rate_id']]['code'].'）'),
            array('error_rnb_money', '失败金额（CNY）'),
            array('error_money', '	失败金额（'.$rate_list[$_GET['rate_id']]['code'].'）'),
        );
        exportExcel($xlsName, $xlsCell, $lists, $cellName);
    }
}
