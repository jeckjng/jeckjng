<?php

/**
 * 消费记录
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class ChargeRecordController extends AdminbaseController
{
    function index(){
        //默认显示前一天数据
        $sdefaultDate = date("Y-m-d");
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -1 days"));
        $week_end = date('Y-m-d', strtotime("$sdefaultDate  -1 days"));

        //判断是否为超级管理员
        $role_id = $_SESSION['role_id'];
        //租户id条件
        if ($role_id == 1 && empty($_REQUEST['tenant_id'])) {

        } elseif (!empty($_REQUEST['tenant_id'])) {
            $map['a.tenant_id'] = $_REQUEST['tenant_id'];
            $_GET['tenant_id'] = $_REQUEST['tenant_id'];
        } else {
            $tenantId = getTenantIds();
            $map['a.tenant_id'] = $tenantId;
        }

        if ($_REQUEST['start_time'] || $_REQUEST['end_time']) {
            $today_time = strtotime($sdefaultDate);
            $start_time = strtotime($_REQUEST['start_time']);
            $end_time = strtotime($_REQUEST['end_time']);
            if ($start_time >= $today_time || $end_time >= $today_time) {
                $this->error('不可以搜索当天数据！');
            }
        }

        if ($_REQUEST['start_time'] != '') {
            $map['a.addtime'] = array("gt", strtotime($_REQUEST['start_time']));
            $_GET['start_time'] = $_REQUEST['start_time'];
        } else {
            $_GET['start_time'] = $week_start;
        }
        if ($_REQUEST['end_time'] != '') {
            $map['a.addtime'] = array("lt", strtotime($_REQUEST['end_time']) + 86399);
            $_GET['end_time'] = $_REQUEST['end_time'];
        } else {
            $_GET['end_time'] = $week_end;
        }
        if ($_REQUEST['start_time'] != '' && $_REQUEST['end_time'] != '') {
            $map['a.addtime'] = array("between", array(strtotime($_REQUEST['start_time']), strtotime($_REQUEST['end_time']) + 86399));
            $_GET['start_time'] = $_REQUEST['start_time'];
            $_GET['end_time'] = $_REQUEST['end_time'];
        }
        if ($_REQUEST['start_time'] == '' && $_REQUEST['end_time'] == '') {
            $map['a.addtime'] = array("between", array(strtotime($week_start), strtotime($week_end) + 86399));
        }
        //币种搜索
        if (!isset($_REQUEST['rate_id']) || !$_REQUEST['rate_id']){
            //默认人民币
            $_REQUEST['rate_id'] = 4;
        }

        // 租户
        $tenantList = M("tenant")->field('id,name')->select();
        $newTenantList = array();
        foreach ($tenantList as $tenantValue){
            $newTenantList[$tenantValue['id']] = $tenantValue['name'];
        }

        $channel = M('channel')->field('id')->where(['coin_id'=>$_REQUEST['rate_id']])->select();
        $_GET['rate_id'] = $_REQUEST['rate_id'];
        $type = [1=>'线上入款',2=>'线下入款',3=>'手动充值'];
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
                /*if (!$tenantInfo){
                    $tenantInfo = getTenantInfoFromGameTenantId($val['tenant_id']);
                }*/
                if (!empty($tenantInfo)) {
                    $lists[$key]['tenant_name'] = $tenantInfo['name'];
                }
            }
            if ($_REQUEST['rate_id'] == 4){
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
        $this->assign("tenant_list",$tenantList);
        $this->assign('lists', $lists);
        $this->assign('type', $type);
        $this->assign('rate_list', array_column(M('rate')->order('sort asc')->select(),NULL,'id'));
        $this->assign('formget', $_GET);
        $this->display();
    }

    function draw_money(){
        //默认显示前一天数据
        $sdefaultDate = date("Y-m-d");
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -1 days"));
        $week_end = date('Y-m-d', strtotime("$sdefaultDate  -1 days"));

        //判断是否为超级管理员
        $role_id = $_SESSION['role_id'];
        //租户id条件
        if ($role_id == 1 && empty($_REQUEST['tenant_id'])) {

        } elseif (!empty($_REQUEST['tenant_id'])) {
            $map['tenant_id'] = $_REQUEST['tenant_id'];
            $_GET['tenant_id'] = $_REQUEST['tenant_id'];
        } else {
            $tenantId = getTenantIds();
            $map['tenant_id'] = $tenantId;
        }

        if ($_REQUEST['start_time'] || $_REQUEST['end_time']) {
            $today_time = strtotime($sdefaultDate);
            $start_time = strtotime($_REQUEST['start_time']);
            $end_time = strtotime($_REQUEST['end_time']);
            if ($start_time >= $today_time || $end_time >= $today_time) {
                $this->error('不可以搜索当天数据！');
            }
        }

        if ($_REQUEST['start_time'] != '') {
            $map['addtime'] = array("gt", strtotime($_REQUEST['start_time']));
            $_GET['start_time'] = $_REQUEST['start_time'];
        } else {
            $_GET['start_time'] = $week_start;
        }
        if ($_REQUEST['end_time'] != '') {
            $map['addtime'] = array("lt", strtotime($_REQUEST['end_time']) + 86399);
            $_GET['end_time'] = $_REQUEST['end_time'];
        } else {
            $_GET['end_time'] = $week_end;
        }
        if ($_REQUEST['start_time'] != '' && $_REQUEST['end_time'] != '') {
            $map['addtime'] = array("between", array(strtotime($_REQUEST['start_time']), strtotime($_REQUEST['end_time']) + 86399));
            $_GET['start_time'] = $_REQUEST['start_time'];
            $_GET['end_time'] = $_REQUEST['end_time'];
        }
        if ($_REQUEST['start_time'] == '' && $_REQUEST['end_time'] == '') {
            $map['addtime'] = array("between", array(strtotime($week_start), strtotime($week_end) + 86399));
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

        // 租户
        $tenantList = M("tenant")->field('id,name')->select();
        $newTenantList = array();
        foreach ($tenantList as $tenantValue){
            $newTenantList[$tenantValue['id']] = $tenantValue['name'];
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
        $this->assign("tenant_list",$tenantList);
        $this->assign('lists', $lists);
        $this->assign('rate_list', array_column(M('rate')->order('sort asc')->select(),NULL,'id'));
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    function draw_export(){
        //默认显示前一天数据
        $sdefaultDate = date("Y-m-d");
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -1 days"));
        $week_end = date('Y-m-d', strtotime("$sdefaultDate  -1 days"));

        //判断是否为超级管理员
        $role_id = $_SESSION['role_id'];
        //租户id条件
        if ($role_id == 1 && empty($_REQUEST['tenant_id'])) {

        } elseif (!empty($_REQUEST['tenant_id'])) {
            $map['tenant_id'] = $_REQUEST['tenant_id'];
            $_GET['tenant_id'] = $_REQUEST['tenant_id'];
        } else {
            $tenantId = getTenantIds();
            $map['tenant_id'] = $tenantId;
        }

        if ($_REQUEST['start_time'] || $_REQUEST['end_time']) {
            $today_time = strtotime($sdefaultDate);
            $start_time = strtotime($_REQUEST['start_time']);
            $end_time = strtotime($_REQUEST['end_time']);
            if ($start_time >= $today_time || $end_time >= $today_time) {
                $this->error('不可以搜索当天数据！');
            }
        }

        if ($_REQUEST['start_time'] != '') {
            $map['addtime'] = array("gt", strtotime($_REQUEST['start_time']));
            $_GET['start_time'] = $_REQUEST['start_time'];
        } else {
            $_GET['start_time'] = $week_start;
        }
        if ($_REQUEST['end_time'] != '') {
            $map['addtime'] = array("lt", strtotime($_REQUEST['end_time']) + 86399);
            $_GET['end_time'] = $_REQUEST['end_time'];
        } else {
            $_GET['end_time'] = $week_end;
        }
        if ($_REQUEST['start_time'] != '' && $_REQUEST['end_time'] != '') {
            $map['addtime'] = array("between", array(strtotime($_REQUEST['start_time']), strtotime($_REQUEST['end_time']) + 86399));
            $_GET['start_time'] = $_REQUEST['start_time'];
            $_GET['end_time'] = $_REQUEST['end_time'];
        }
        if ($_REQUEST['start_time'] == '' && $_REQUEST['end_time'] == '') {
            $map['addtime'] = array("between", array(strtotime($week_start), strtotime($week_end) + 86399));
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

    function export()
    {
        //默认显示前一天数据
        $sdefaultDate = date("Y-m-d");
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -1 days"));
        $week_end = date('Y-m-d', strtotime("$sdefaultDate  -1 days"));

        //判断是否为超级管理员
        $role_id = $_SESSION['role_id'];
        //租户id条件
        if ($role_id == 1 && empty($_REQUEST['tenant_id'])) {

        } elseif (!empty($_REQUEST['tenant_id'])) {
            $map['tenant_id'] = $_REQUEST['tenant_id'];
            $_GET['tenant_id'] = $_REQUEST['tenant_id'];
        } else {
            $tenantId = getTenantIds();
            $map['tenant_id'] = $tenantId;
        }

        if ($_REQUEST['start_time'] || $_REQUEST['end_time']) {
            $today_time = strtotime($sdefaultDate);
            $start_time = strtotime($_REQUEST['start_time']);
            $end_time = strtotime($_REQUEST['end_time']);
            if ($start_time >= $today_time || $end_time >= $today_time) {
                $this->error('不可以搜索当天数据！');
            }
        }

        if ($_REQUEST['start_time'] != '') {
            $map['a.addtime'] = array("gt", strtotime($_REQUEST['start_time']));
            $_GET['start_time'] = $_REQUEST['start_time'];
        } else {
            $_GET['start_time'] = $week_start;
        }
        if ($_REQUEST['end_time'] != '') {
            $map['a.addtime'] = array("lt", strtotime($_REQUEST['end_time']) + 86399);
            $_GET['end_time'] = $_REQUEST['end_time'];
        } else {
            $_GET['end_time'] = $week_end;
        }
        if ($_REQUEST['start_time'] != '' && $_REQUEST['end_time'] != '') {
            $map['a.addtime'] = array("between", array(strtotime($_REQUEST['start_time']), strtotime($_REQUEST['end_time']) + 86399));
            $_GET['start_time'] = $_REQUEST['start_time'];
            $_GET['end_time'] = $_REQUEST['end_time'];
        }
        if ($_REQUEST['start_time'] == '' && $_REQUEST['end_time'] == '') {
            $map['a.addtime'] = array("between", array(strtotime($week_start), strtotime($week_end) + 86399));
        }


        //币种搜索
        if (!isset($_REQUEST['rate_id']) || !$_REQUEST['rate_id']){
            //默认人民币
            $_REQUEST['rate_id'] = 4;
        }
        $rate_list = array_column(M('rate')->order('sort asc')->select(),NULL,'id');
        $channel = M('channel')->field('id')->where(['coin_id'=>$_REQUEST['rate_id']])->select();
        $_GET['rate_id'] = $_REQUEST['rate_id'];
        $type = [1=>'线上入款',2=>'线下入款',3=>'手动充值'];
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
                    count(id) as order_sum
                ')
                ->where($map)
                ->join('cmf_channel as b on a.channel_id=b.id','left')
                ->group('a.type,a.tenant_id')
                ->select();
            foreach ($lists as $key=>$val){
                $lists[$key]['user_sum'] = count($charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->field('count(id) as user_sum')->where(['a.type'=>$val['type']])->group('a.uid')->select());
                $lists[$key]['success_rnb_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>2])->sum('a.rnb_money') ?: 0;
                $lists[$key]['success_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>2])->sum('a.money') ?: 0;
                $lists[$key]['error_rnb_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>['neq',2]])->sum('a.rnb_money') ?: 0;
                $lists[$key]['error_money'] = $charge->alias('a')->join('cmf_channel as b on a.channel_id=b.id','left')->where($map)->where(['a.type'=>$val['type'],'a.status'=>['neq',2]])->sum('a.money') ?: 0;
                $lists[$key]['add_time'] = date('Y-m-d', $val['start_time']) . '-' . date('Y-m-d', $val['end_time']);
                $lists[$key]['pay_type'] = $type[$val['type']];
                $lists[$key]['number_count'] = $val['order_sum'].'/'.$lists[$key]['user_sum'];
                $tenantInfo = getTenantInfo($val['tenant_id']);
                if (!empty($tenantInfo)) {
                    $lists[$key]['tenant_name'] = $tenantInfo['name'];
                }
            }
            if ($_REQUEST['rate_id'] == 4){
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
                        count(id) as order_sum
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
                    $lists2[$key]['add_time'] = date('Y-m-d', $val['start_time']) . '-' . date('Y-m-d', $val['end_time']);
                    $lists2[$key]['pay_type'] = $type[$val['type']];
                    $lists2[$key]['number_count'] = $val['order_sum'].'/'.$lists2[$key]['user_sum'];
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
