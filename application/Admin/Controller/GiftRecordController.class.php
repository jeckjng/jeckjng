<?php

/**
 * 消费记录
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class GiftRecordController extends AdminbaseController
{
    function index()
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

        $map['type'] = 'expend';
        $map['action'] = 'sendgift';

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

        if ($_REQUEST['uid'] != '') {
            $map['uid'] = $_REQUEST['uid'];
            $_GET['uid'] = $_REQUEST['uid'];
        }

        $coin = M("users_coinrecord");
        $Users = M("users");
        $res = $coin
            ->where($map)
            ->field('
                uid,
                tenant_id,
                min(addtime) start_time,
                max(addtime) end_time,
                sum(totalcoin) as totalcoin_sum,
                sum(anthor_total) as anthor_total_sum,
                sum(tenant_total) as tenant_total_sum,
                sum(tenantuser_total) as tenantuser_total_sum
            ')
            ->group('uid,tenant_id')
            ->select();
        $page = $this->page(count($res));
        $lists = $coin->where($map)
            ->field('
                uid,
                tenant_id,
                min(addtime) start_time,
                max(addtime) end_time,
                sum(totalcoin) as totalcoin_sum,
                sum(anthor_total) as anthor_total_sum,
                sum(tenant_total) as tenant_total_sum,
                sum(tenantuser_total) as tenantuser_total_sum
            ')
            ->group('uid,tenant_id')
            ->order("tenant_id ASC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach ($lists as $k => $v) {
            $userinfo = $Users->field("user_nicename")->where("id='$v[uid]'")->find();
            $lists[$k]['userinfo'] = $userinfo;

            $tenantInfo = getTenantInfo($v['tenant_id']);
            if (!empty($tenantInfo)) {
                $lists[$k]['tenant_name'] = $tenantInfo['name'];
            }

        }

        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
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

        $map['type'] = 'expend';
        $map['action'] = 'sendgift';

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

        if ($_REQUEST['uid'] != '') {
            $map['uid'] = $_REQUEST['uid'];
            $_GET['uid'] = $_REQUEST['uid'];
        }

        $coin = M("users_coinrecord");
        $Users = M("users");
        $lists = $coin->where($map)
            ->field('
                uid,
                tenant_id,
                min(addtime) start_time,
                max(addtime) end_time,
                sum(totalcoin) as totalcoin_sum,
                sum(anthor_total) as anthor_total_sum,
                sum(tenant_total) as tenant_total_sum,
                sum(tenantuser_total) as tenantuser_total_sum
            ')
            ->group('uid,tenant_id')
            ->order("tenant_id ASC")
            ->select();

        foreach ($lists as $k => $v) {
            $userinfo = $Users->field("user_nicename")->where("id='$v[uid]'")->find();
            $lists[$k]['userinfo'] = $userinfo['user_nicename'];
            $lists[$k]['action'] = '礼物赠送';
            $lists[$k]['add_time'] = date('Y-m-d', $v['start_time']) . '-' . date('Y-m-d', $v['end_time']);
            $tenantInfo = getTenantInfo($v['tenant_id']);
            if (!empty($tenantInfo)) {
                $lists[$k]['tenant_name'] = $tenantInfo['name'];
            }

        }
        $xlsName = "Excel";
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        $xlsCell = array(
            array('uid', 'ID'),
            array('tenant_name', '用户名'),
            array('action', '行为'),
            array('totalcoin_sum', '总价'),
            array('add_time', '时间'),
            array('tenant_name', '会员所属游戏租户'),
            array('tenantuser_total_sum', '游戏租户分成'),
            array('tenant_total_sum', '直播租户分成'),
        );
        exportExcel($xlsName, $xlsCell, $lists, $cellName);
    }
}
