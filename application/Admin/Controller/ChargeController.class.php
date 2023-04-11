<?php

/**
 * 充值记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Admin\Model\UsersModel;

class ChargeController extends AdminbaseController {

    private $status_list = array(
        '1' => array(
            'name' => '未支付',
            'color' => '#f00',
        ),
        '2' => array(
            'name' => '已支付',
            'color' => '#090',
        ),
        '3' => array(
            'name' => '支付失败',
            'color' => '#999',
        ),
    );

    public function index(){
        $param = I('param.');

        $map = array('type'=>1);
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
        if(isset($param['currency_code']) && $param['currency_code'] !=''){
            $map['currency_code'] = $param['currency_code'];
        }
        if(isset($param['uid']) && $param['uid']!=''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login']!=''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['orderno']) && $param['orderno']!=''){
            $map['orderno'] = $param['orderno'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $model = M("users_charge");
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

        $moneysum = $model->where($map)->sum("rnb_money");

        $channel_account_list = M("channel_account")->select();
        $channel_account_list = count($channel_account_list) > 0 ? array_column($channel_account_list,null,'id') : [];

        $status_list = $this->status_list;
        foreach ($list as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $list[$key]['userinfo'] = $userinfo;
            $list[$key]['money'] = floatval($val['money']);
            $list[$key]['rnb_money'] = floatval($val['rnb_money']);
            $list[$key]['rate']= floatval($val['rate']);
            $list[$key]['coin_give'] = floatval($val['coin_give']);
            $list[$key]['channel_account_name'] = isset($channel_account_list[$val['account_channel_id']]) ? $channel_account_list[$val['account_channel_id']]['name'] : $val['account_channel_id'];
            $list[$key]['upstream_service_money'] = floatval($val['upstream_service_money']);
            $list[$key]['actual_money']= floatval($val['actual_money']);
            $list[$key]['status_name'] = '<span style="color: '.$status_list[$val['status']]['color'].';">'.$status_list[$val['status']]['name'].'</span>';
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('currency_list', currency_list());
    	$this->assign('moneysum', floatval($moneysum));
    	$this->assign('list', $list);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('status_list',$status_list);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
    	$this->display();
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

        $xlsData = $data['list'];

        foreach ($xlsData as $k => $v) {
            $userinfo=M("users")->field("user_nicename")->where("id='$v[uid]'")->find();
            $xlsData[$k]['user_nicename']= $userinfo['user_nicename']."(".$v['uid'].")";
            $xlsData[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']);
            $xlsData[$k]['rate']='1:'.$v['rate'];
            $xlsData[$k]['order_all']= $v['trade_no'] ? $v['orderno']."/".$v['trade_no'] : $v['orderno'];
            $xlsData[$k]['coin_all']= $v['coin']."/".$v['coin_give'];
            $channelAccountInfo = M("channel_account")->field("name")->where(array('id' =>$v['account_channel_id']))->find();
            $xlsData[$k]['channel_all']= $channelAccountInfo['name']."/".$v['upstream_service_money'];
            if($v['status']=='1'){
                $xlsData[$k]['status']="未支付";
            }else if ($v['status']=='2'){
                $xlsData[$k]['status']="已支付";
            }else{
                $xlsData[$k]['status']="支付失败";
            }
        }

        $xlsName  = "Excel";
        $action="导出充值记录：".M("users_charge")->getLastSql();
        setAdminLog($action);

        $rate_list = array_column(M('rate')->order('sort asc')->select(),'code','id');
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K');
        $config = getConfigPri();
        $xlsCell  = array(
            array('id','ID'),
            array('order_all',"订单号/商户订单号"),
            array('user_nicename','会员'),
            array('rnb_money','金额（CNY）'),
            array('rate','汇率'),
            array('money','充值金额（'.$rate_list[$_REQUEST['rate_id']].'）'),
            array('coin_all',$config['name_coin']."数/赠送"),
            array('channel_all','商户名称/上游手续费'),
            array('actual_money','实际到账金额'),
            array('status','订单状态'),
            array('addtime','提交时间')
        );
        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
    }

    public function offlinepay(){
        $param = I('param.');

        $no_virtual_currency = array();
        $currency_list = M('rate')->select();
        foreach ($currency_list as $key=>$val){
            if($val['is_virtual'] == 0){
                array_push($no_virtual_currency, $val['code']);
            }else{
                unset($currency_list[$key]);
            }
        }
        $map = array('type'=>2, 'currency_code'=>array('in', $no_virtual_currency));

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
        if(isset($param['currency_code']) && $param['currency_code'] !=''){
            $map['currency_code'] = $param['currency_code'];
        }
        if(isset($param['uid']) && $param['uid']!=''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login']!=''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['orderno']) && $param['orderno']!=''){
            $map['orderno'] = $param['orderno'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $model = M("users_charge");
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

        $moneysum = $model->where($map)->sum("rnb_money");

        $channel_list = M("channel")->select();
        $channel_list = count($channel_list) > 0 ? array_column($channel_list,null,'id') : [];
        $channel_account_list = M("channel_account")->select();
        $channel_account_list = count($channel_account_list) > 0 ? array_column($channel_account_list,null,'id') : [];
        $offlinepay_list = M("offlinepay")->select();
        $offlinepay_list = count($offlinepay_list) > 0 ? array_column($offlinepay_list,null,'id') : [];

        $status_list = $this->status_list;
        foreach ($list as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $list[$key]['userinfo'] = $userinfo;
            $list[$key]['money'] = floatval($val['money']);
            $list[$key]['rnb_money'] = floatval($val['rnb_money']);
            $list[$key]['rate'] = floatval($val['rate']);
            $list[$key]['coin_give'] = floatval($val['coin_give']);
            $list[$key]['channel_name'] = isset($channel_list[$val['channel_id']]) ? $channel_list[$val['channel_id']]['channel_name'] : $val['channel_id'];
            $list[$key]['channel_account_name'] = isset($channel_account_list[$val['account_channel_id']]) ? $channel_account_list[$val['account_channel_id']]['name'] : $val['account_channel_id'];
            $list[$key]['upstream_service_money'] = floatval($val['upstream_service_money']);
            $list[$key]['actual_money'] = floatval($val['actual_money']);
            $list[$key]['status_name'] = '<span style="color: '.$status_list[$val['status']]['color'].';">'.$status_list[$val['status']]['name'].'</span>';
            $list[$key]['offlinepay_info'] = isset($offlinepay_list[$val['account_channel_id']]) ? $offlinepay_list[$val['account_channel_id']] : [];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('currency_list', $currency_list);
        $this->assign('moneysum', floatval($moneysum));
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('status_list',$status_list);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }

    public  function offlinesupstatus(){
            $id = intval(I("get.id"));
            $status = intval(I("get.status"));
            $reids = connectRedis();
            $czid = $reids->get('cz_'.$id);
            if ($czid){
                return $this->error('订单状态有误');
            }else{
                $reids->set('cz_'.$id,time());
            }
            $chargeInfo = M("users_charge")->where(array('id' => $id))->find();
            if ($chargeInfo['status'] != '1'){
                $reids->del('cz_'.$id);
                $this->error('订单状态有误');
            }

            M("users_charge")->where(array('id' => $id))->save(array('status'=>$status,'updatetime'=>time(),'operated_by'=>get_current_admin_user_login()));
            if($status == 3){ // 取消充值订单
                M('offlinepay')->where(array('id'=>$chargeInfo['account_channel_id'] ))->setDec('already_charge_total_money', $chargeInfo['money']);
            }
            if ($status == 2){
                $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($chargeInfo['uid']);
                if(!$user_info){
                    $this->error('用户不存在');
                }
                try {
                    M()->startTrans();
                    $coin = $chargeInfo['coin']+ $chargeInfo['coin_give'];
                    if ($chargeInfo['is_buy_vip'] == 1){
                        $nowTime =  time();
                        $userVip = M('users_vip')->where(['id'=> $chargeInfo['buy_log_id']])->find();
                        $vipInfo =M('vip')->where(['id'=>$userVip['vip_id']])->find(); //购买的会员等级信息
                        $endtime = strtotime("+" . $vipInfo['length'] . " month", $nowTime);
                        if ($vipInfo['give_data']) {
                            $endtime = strtotime("+{$vipInfo['give_data']} day", $endtime);
                        }
                        M('users_vip')->where("id = '{$chargeInfo['buy_log_id']}'")->save(['endtime'=>$endtime ]);
                        $coin = $coin - $userVip['price'];
                    }
                    $data = array(
                        'coin'=>array('exp','coin+'.$coin),
                        'recharge_num'=>array('exp','recharge_num+1'),
                        'recharge_total' => array('exp',  'recharge_total+'.$chargeInfo['rnb_money']),
                        'actual_recharge_total' => array("exp" , 'actual_recharge_total+'.$chargeInfo['actual_money']),
                    );
                    if($user_info['recharge_num']==0){
                        $data['firstrecharge_coin'] = floatval($chargeInfo['coin']);
                    }
                    $order_id = generater();
                    M('users')->where(array('id' =>$chargeInfo['uid'] ))->save($data);
                    M('users_coinrecord')->add([
                        'type' => 'income',
                        'uid' => intval($chargeInfo['uid']),
                        "user_login" => $user_info['user_login'],
                        "user_type" => intval($user_info['user_type']),
                        'addtime' => time(),
                        'tenant_id' => intval($user_info['tenant_id']),
                        'action' => 'offline_charge',
                        "pre_balance" => floatval($user_info['coin']),
                        'totalcoin' => floatval($coin),
                        "after_balance" => floatval(bcadd($user_info['coin'], $coin,4)),
                        'order_id'=>$order_id,
                    ]);

                    M()->commit();
                }catch (\Exception $e){
                    M()->rollback();
                    setAdminLog('线下入款审核，更新用户余额失败【'.$id.'】'.$e->getMessage());
                    $this->error("操作失败！");
                }

                $action = '入账线下支付订单'.$chargeInfo['orderno'];
                setAdminLog($action);
                // 首充活动/分享活动 奖励赠送
                activity_reward($chargeInfo['uid'],$user_info['recharge_num'],$chargeInfo['coin'],$user_info['tenant_id']);
                delUserInfoCache($chargeInfo['uid']);
                $reids->del('cz_'.$id);
            }
            $this->success("操作成功！");
        }

    public  function usdtupstatus(){
        $id = intval(I("get.id"));
        $status = intval(I("get.status"));
        $reids = connectRedis();
        $czid = $reids->get('cz_'.$id);
        if ($czid){
            return $this->error('订单状态有误');
        }else{
            $reids->set('cz_'.$id,time());
        }
        $chargeInfo = M("users_charge")->where(array('id' => $id))->find();
        if ($chargeInfo['status'] != '1'){
            $reids->del('cz_'.$id);
            return $this->error('订单状态有误');
        }

        M("users_charge")->where(array('id' => $id))->save(array('status'=>$status, 'updatetime'=>time(), 'operated_by'=>get_current_admin_user_login()));
        if ($status == 2){
            $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($chargeInfo['uid']);
            if(!$user_info){
                $this->error('用户不存在');
            }
            try {
                M()->startTrans();
                $coin = $chargeInfo['coin']+ $chargeInfo['coin_give'];

                if ($chargeInfo['is_buy_vip'] == 1){
                    $nowTime =  time();
                    $userVip = M('users_vip')->where(['id'=> $chargeInfo['buy_log_id']])->find();
                    $vipInfo =M('vip')->where(['id'=>$userVip['vip_id']])->find(); //购买的会员等级信息
                    $endtime = strtotime("+" . $vipInfo['length'] . " month", $nowTime);
                    if ($vipInfo['give_data']) {
                        $endtime = strtotime("+{$vipInfo['give_data']} day", $endtime);
                    }
                    M('users_vip')->where("id = '{$chargeInfo['buy_log_id']}'")->save(['endtime'=>$endtime ]);
                    $coin = $coin - $userVip['price'];
                }
                $data = array(
                    'coin'=>array('exp','coin+'.$coin),
                    'recharge_num'=>array('exp','recharge_num+1'),
                    'recharge_total' => array('exp',  'recharge_total+'.$chargeInfo['rnb_money']),
                    'actual_recharge_total' => array("exp" , 'actual_recharge_total+'.$chargeInfo['actual_money']),
                );
                if($user_info['recharge_num']==0){
                    $data['firstrecharge_coin'] = floatval($chargeInfo['coin']);
                }
                M('users')->where(array('id' =>$chargeInfo['uid'] ))->save($data);
                M('users_coinrecord')->add([
                    'type' => 'income',
                    'uid' => intval($chargeInfo['uid']),
                    "user_login" => $user_info['user_login'],
                    "user_type" => intval($user_info['user_type']),
                    'addtime' => time(),
                    'tenant_id' => intval($user_info['tenant_id']),
                    'action' => 'offline_virtual_charge',
                    "pre_balance" => floatval($user_info['coin']),
                    'totalcoin' => floatval($coin),
                    "after_balance" => floatval(bcadd($user_info['coin'], $coin,4)),
                ]);
                M()->commit();
            }catch (\Exception $e){
                M()->rollback();
                setAdminLog('线下入款审核，更新用户余额失败【'.$id.'】'.$e->getMessage());
                $this->error("操作失败！");
            }
            $action = '入账线下支付订单'.$chargeInfo['orderno'];
            setAdminLog($action);

            // 首充活动/分享活动 奖励赠送
            activity_reward($chargeInfo['uid'],$user_info['recharge_num'],$chargeInfo['coin'],$user_info['tenant_id']);
            delUserInfoCache($chargeInfo['uid']);
            $reids->del('cz_'.$id);
        }
        $this->success("设置成功！");
    }

    public function offlinepayexport()
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

        $data = $this->offlinepay();

        if(count($data['list']) > 100000){ // 限制数量10万条导出
            echo '<script language="JavaScript">;alert("导出数据数量过大，数量不能大于10万条");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }

        $xlsData = $data['list'];

        $ambient=array(
            "1"=>array(
                '0'=>'App',
                '1'=>'PC',
            ),
            "2"=>array(
                '0'=>'App',
                '1'=>'公众号',
                '2'=>'PC',
            ),
            "3"=>array(
                '0'=>'沙盒',
                '1'=>'生产',
            )
        );
        foreach ($xlsData as $k => $v) {
            $userinfo=M("users")->field("user_nicename")->where("id='$v[uid]'")->find();
            $xlsData[$k]['user_nicename']= 'ID: '.$v['uid'].' | 昵称: '.$userinfo['user_nicename'].' | '."真实姓名: ".$v['user_real_name'];
            $xlsData[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']);
            $xlsData[$k]['rate']='1:'.$v['rate'];
            $xlsData[$k]['order_all']= $v['trade_no'] ? $v['orderno']."/".$v['trade_no'] : $v['orderno'];
            $xlsData[$k]['coin_all']= $v['coin']."/".$v['coin_give'];
            $channelAccountInfo = M("offlinepay")->field("name,bank_user_name,bank_branch,bank_name,bank_number")->where(array('id' =>$v['account_channel_id']))->find();
            $xlsData[$k]['channel_all']= $channelAccountInfo['name']."/".$v['upstream_service_money'];
            $xlsData[$k]['bank_info']= $channelAccountInfo ? $channelAccountInfo['bank_user_name']."/".$channelAccountInfo['bank_name']."/".$channelAccountInfo['bank_number'] : '/ / /';
            $xlsData[$k]['pay_info'] = $ambient[$v['type']][$v['ambient']]."/".$v['channel_name'];
            if($v['status']=='1'){
                $xlsData[$k]['status']="未支付";
            }else if ($v['status']=='2'){
                $xlsData[$k]['status']="已支付";
            }else{
                $xlsData[$k]['status']="支付失败";
            }
            $xlsData[$k]['img']= $v['img'] ? $v['img'] : "-";
        }

        $xlsName  = "Excel";
        $action="导出线下入款记录：".M("users_charge")->getLastSql();
        setAdminLog($action);

        $rate_list = array_column(M('rate')->order('sort asc')->select(),'code','id');
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','M','N');
        $config = getConfigPri();
        $xlsCell  = array(
            array('id','ID'),
            array('order_all',"订单号/商户订单号"),
            array('user_nicename','会员'),
            array('rnb_money','金额（CNY）'),
            array('rate','汇率'),
            array('money','充值金额（'.$rate_list[$_REQUEST['rate_id']].'）'),
            array('pay_info','支付环境/渠道名称'),
            array('bank_info','收款信息'),
            array('coin_all',$config['name_coin']."数/赠送"),
            array('channel_all','商户名称/上游手续费'),
            array('actual_money','实际到账金额'),
            array('status','订单状态'),
            array('addtime','提交时间')
        );
        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
    }

    public  function updateStatus(){
        $param = I('param.');
        $id = intval(I("get.id"));
        $status = intval(I("get.status"));
        $reids = connectRedis();
        $czid = $reids->get('cz_'.$id);
        if ($czid){
            return $this->error('订单状态有误');
        }else{
            $reids->set('cz_'.$id,time());
        }
        $chargeInfo = M("users_charge")->where(array('id' => $id))->find();
        if ($chargeInfo['status'] != '1'){
            $reids->del('cz_'.$id);
            $this->error('订单状态有误');
        }
      //  $chargeInfo = M("users_charge")->where(array('id' => $id))->find();
        M("users_charge")->where(array('id' => $id))->save(array('status'=>$status,'updatetime'=>time(),'operated_by'=>get_current_admin_user_login()));//
        if ($status == 2){
            $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($chargeInfo['uid']);
            if(!$user_info){
                $this->error('用户不存在');
            }
            try {
                M()->startTrans();
                $coin = $chargeInfo['coin']+ $chargeInfo['coin_give'];
                if ($chargeInfo['is_buy_vip'] == 1){
                    $nowTime =  time();
                    $userVip = M('users_vip')->where(['id'=> $chargeInfo['buy_log_id']])->find();
                    $vipInfo =M('vip')->where(['id'=>$userVip['vip_id']])->find(); //购买的会员等级信息
                    $endtime = strtotime("+" . $vipInfo['length'] . " month", $nowTime);
                    if ($vipInfo['give_data']) {
                        $endtime = strtotime("+{$vipInfo['give_data']} day", $endtime);
                    }
                    M('users_vip')->where("id = '{$chargeInfo['buy_log_id']}'")->save(['endtime'=>$endtime ]);
                    $coin = $coin - $userVip['price'];
                }
                $data = array(
                    'coin'=>array('exp','coin+'.$coin),
                    'recharge_num'=>array('exp','recharge_num+1'),
                    'recharge_total' => array('exp',  'recharge_total+'.$chargeInfo['rnb_money']),
                    'actual_recharge_total' => array("exp" , 'actual_recharge_total+'.$chargeInfo['actual_money']),
                );
                if($user_info['recharge_num']==0){
                    $data['firstrecharge_coin'] = floatval($chargeInfo['coin']);
                }
                M('users')->where(array('id' =>$chargeInfo['uid'] ))->save($data);
                M('users_coinrecord')->add([
                    'type' => 'income',
                    'uid' => intval($chargeInfo['uid']),
                    "user_login" => $user_info['user_login'],
                    "user_type" => intval($user_info['user_type']),
                    'addtime' => time(),
                    'tenant_id' => intval($user_info['tenant_id']),
                    'action' => 'online_charge',
                    "pre_balance" => floatval($user_info['coin']),
                    'totalcoin' => floatval($coin),
                    "after_balance" => floatval(bcadd($user_info['coin'], $coin,4)),
                ]);
                M()->commit();
            }catch (\Exception $e){
                M()->rollback();
                setAdminLog('线上入款审核，更新用户余额失败【'.json_encode($param).'】'.$e->getMessage());
                $this->error("操作失败！");
            }
            $action = '入账线上支付订单'.$chargeInfo['orderno'];
            setAdminLog($action);

            // 首充活动/分享活动 奖励赠送
            activity_reward($chargeInfo['uid'],$user_info['recharge_num'],$chargeInfo['coin'],$user_info['tenant_id']);
            delUserInfoCache($chargeInfo['uid']);
            $reids->del('cz_'.$id);
        }else{

            $action = '驳回线上支付订单'.$chargeInfo['orderno'];
            setAdminLog($action);
            $reids->del('cz_'.$id);
        }

        $this->success("设置成功！");
    }

    public function usdtpay(){
        $param = I('param.');

        $virtual_currency = array();
        $currency_list = currency_list();
        foreach ($currency_list as $key=>$val){
            if($val['is_virtual'] == 1){
                array_push($virtual_currency, $val['code']);
            }else{
                unset($currency_list[$key]);
            }
        }

        $map = array('type'=>2, 'currency_code'=>array('in', $virtual_currency));
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
        if(isset($param['currency_code']) && $param['currency_code'] !=''){
            $map['currency_code'] = $param['currency_code'];
        }
        if(isset($param['uid']) && $param['uid']!=''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login']!=''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['orderno']) && $param['orderno']!=''){
            $map['orderno'] = $param['orderno'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $model = M("users_charge");
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

        $moneysum = $model->where($map)->sum("rnb_money");

        $channel_list = M("channel")->select();
        $channel_list = count($channel_list) > 0 ? array_column($channel_list,null,'id') : [];
        $channel_account_list = M("channel_account")->select();
        $channel_account_list = count($channel_account_list) > 0 ? array_column($channel_account_list,null,'id') : [];
        $offlinepay_list = M("offlinepay")->select();
        $offlinepay_list = count($offlinepay_list) > 0 ? array_column($offlinepay_list,null,'id') : [];

        $status_list = $this->status_list;
        foreach ($list as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $list[$key]['userinfo'] = $userinfo;
            $list[$key]['money'] = floatval($val['money']);
            $list[$key]['rnb_money'] = floatval($val['rnb_money']);
            $list[$key]['rate'] = floatval($val['rate']);
            $list[$key]['coin'] = floatval($val['coin']);
            $list[$key]['coin_give'] = floatval($val['coin_give']);
            $list[$key]['channel_name'] = isset($channel_list[$val['channel_id']]) ? $channel_list[$val['channel_id']]['channel_name'] : $val['channel_id'];
            $list[$key]['channel_account_name'] = isset($channel_account_list[$val['account_channel_id']]) ? $channel_account_list[$val['account_channel_id']]['name'] : $val['account_channel_id'];
            $list[$key]['upstream_service_money'] = floatval($val['upstream_service_money']);
            $list[$key]['actual_money'] = floatval($val['actual_money']);
            $list[$key]['status_name'] = '<span style="color: '.$status_list[$val['status']]['color'].';">'.$status_list[$val['status']]['name'].'</span>';
            $list[$key]['offlinepay_info'] = isset($offlinepay_list[$val['account_channel_id']]) ? $offlinepay_list[$val['account_channel_id']] : [];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('currency_list', $currency_list);
        $this->assign('moneysum', floatval($moneysum));
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('status_list',$status_list);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }

    public function usdtpayexport()
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

        $data = $this->usdtpay();

        if(count($data['list']) > 100000){ // 限制数量10万条导出
            echo '<script language="JavaScript">;alert("导出数据数量过大，数量不能大于10万条");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }

        $xlsData = $data['list'];

        foreach($xlsData as $k=>$v){
            $userinfo=M("users")->field("user_nicename")->where("id='$v[uid]'")->find();
            $xlsData[$k]['user_nicename']= 'ID: '.$v['uid'].' | 昵称: '.$userinfo['user_nicename'].' | '."真实姓名: ".$v['user_real_name'];
            $xlsData[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']);
            $xlsData[$k]['rate']='1:'.$v['rate'];
            $xlsData[$k]['order_all']= $v['trade_no'] ? $v['orderno']."/".$v['trade_no'] : $v['orderno'];
            $xlsData[$k]['coin_all']= $v['coin']."/".$v['coin_give'];
            $channelAccountInfo = M("offlinepay")->field("name,usdt_type,usdt_address")->where(array('id' =>$v['account_channel_id']))->find();
            $xlsData[$k]['channel_all']= $channelAccountInfo['name']."/".$v['upstream_service_money'];
            $xlsData[$k]['bank_info']= $v['rate_name'] ."/" . $channelAccountInfo['usdt_type']."/" .$channelAccountInfo['usdt_address']."/".$v['channel_name'];
            if($v['status']=='1'){
                $xlsData[$k]['status']="未支付";
            }else if ($v['status']=='2'){
                $xlsData[$k]['status']="已支付";
            }else{
                $xlsData[$k]['status']="支付失败";
            }
            $xlsData[$k]['img']= $v['img'] ? $v['img'] : "-";
        }
        $xlsName  = "Excel";
        $action="导出虚拟币入款记录：".M("users_charge")->getLastSql();
        setAdminLog($action);

        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L');
        $config = getConfigPri();
        $xlsCell  = array(
            array('id','ID'),
            array('order_all',"订单号/商户订单号"),
            array('user_nicename','会员'),
            array('rnb_money','金额（CNY）'),
            array('rate','汇率'),
            array('money','充值金额'),
            array('bank_info','收款信息'),
            array('coin_all',$config['name_coin']."数/赠送"),
            array('channel_all','商户名称/上游手续费'),
            array('actual_money','实际到账金额'),
            array('status','订单状态'),
            array('addtime','提交时间')
        );
        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
    }

    public function getOffline(){
        $res=array("code"=>0,"msg"=>"获取成功","info"=>array());
        $role_id=$_SESSION['role_id'];
        $rule_name = '线下入款';
        $isauth = getAuth($role_id,$rule_name);
        if($isauth == 1){
            $map['a.status'] = 1;
            $map['a.type'] = 2;
            $map['c.is_virtual'] = 0;
            $charge=M("users_charge");
            $count=$charge
                ->alias('a')
                ->join('cmf_channel as b on a.channel_id=b.id','left')
                ->join('cmf_rate as c on b.coin_id=c.id','left')
                ->where($map)
                ->count();
            if($count>0){
                $res['code']=200;
                $res['counts']=$count;
            }
        }
        echo json_encode($res);
        exit;

    }

    public function getusdtpay(){
        $res=array("code"=>0,"msg"=>"获取成功","info"=>array());
        $role_id=$_SESSION['role_id'];
        $rule_name = '虚拟币入款';
        $isauth = getAuth($role_id,$rule_name);

        if($isauth == 1){
            $map['a.status'] = 1;
            $map['a.type'] = 2;
            $map['c.is_virtual'] = 1;
            $charge=M("users_charge");
            $count=$charge
                ->alias('a')
                ->join('cmf_channel as b on a.channel_id=b.id','left')
                ->join('cmf_rate as c on b.coin_id=c.id','left')
                ->where($map)
                ->count();
            if($count>0){
                $res['code']=200;
                $res['counts']=$count;
            }
        }
        echo json_encode($res);
        exit;

    }
}
