<?php

/**
 * 管理员手动充值记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Admin\Model\UsersModel;

class ManualController extends AdminbaseController {

    private $status_list = array(
        '1' => array(
            'name' => '已调整',
            'type' => '1',
        ),
    );

    public function index(){
        $param = I('param.');
        $currency_list = currency_list();

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
        if(isset($param['currency_code']) && $param['currency_code'] !=''){
            $map['currency_code'] = $param['currency_code'];
        }
        if(isset($param['uid']) && $param['uid']!=''){
            $map['touid'] = $param['uid'];
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

        $model = M("users_charge_admin");
    	$count = $model->where($map)->count();
    	$page = $this->page($count, $page_size);
        $list = $model
            ->where($map)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        // 如果是导出，则直接return源数据
        if(isset($_GET['action_type']) && $_GET['action_type'] == 'export'){
            return array('count'=>$count, 'list'=>$list);
        }

        $user_type_list = user_type_list();
        $balance_type_list = balance_type_list();
        $business_type_list = business_type_list();
        $status_list = $this->status_list;
        foreach($list as $key=>$val){
            $userinfo = getUserInfo($val['touid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $list[$key]['user_type_name'] = '<span style="color: '.$user_type_list[$val['user_type']]['color'].';">'.$user_type_list[$val['user_type']]['name'].'</span>';
            $list[$key]['currency_name']=isset($currency_list[$val['currency_code']]) ? $currency_list[$val['currency_code']]['name'] : $val['currency_code'];
            $list[$key]['money'] = floatval($val['money']);
            $list[$key]['rnb_money']= floatval($val['rnb_money']);
            $list[$key]['rate']= floatval($val['rate']);
            $list[$key]['coin']= floatval($val['coin']);
            $list[$key]['after_balance']= floatval($val['after_balance']);
            $list[$key]['balance_type_name'] = $balance_type_list[$val['balance_type']]['name'];
            $list[$key]['business_type_name'] = $business_type_list[$val['business_type']]['name'];
            $list[$key]['status_name'] = $status_list[$val['status']]['name'];
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

    	$this->assign('list', $list);
    	$this->assign('rnb_money', floatval($rnb_money));
        $this->assign('total_money_list', $total_money_list);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->assign('currency_list', $currency_list);
    	$this->display();
    }

    public function add(){
        $tenantId = getTenantIds();
        $currency_list = M('rate')->select();
        foreach ($currency_list as $key=>$val){
            if($val['is_virtual'] == 0){
                array_push($no_virtual_currency, $val['code']);
            }else{
                unset($currency_list[$key]);
            }
        }

        $this->assign('ratelsit', $currency_list);
        $this->assign('business_type_list', business_type_list());
        $this->assign('tenant_list', getTenantList());
        $this->assign('tenant_id', getTenantIds());
        $this->display();
    }
    
    public function add_post() {
        $param = I('param.');
        if(!$param['uid'] && !$param['user_login']){
            $this->error("请输入会员ID或者会员账号");
        }
        if(!$param['currency_code']){
            $this->error("请选择充值币种");
        }
        if(!$param['money']){
            $this->error("请输入充值金额");
        }
        if(!$param['business_type']){
            $this->error("请选择业务类型");
        }
        if(mb_strlen($param['business_type']) > 500){
            $this->error("备注过长");
        }
        $currency_code = $param['currency_code'];
        $money = $param['money'];

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $user_info = UsersModel::getInstance()->getUserInfoWithIdOrAccountAndTid($param['uid'], $param['user_login'], $tenant_id);

        if(!isset($user_info['id']) || !$user_info['id']){
            $this->error("会员不存在，请更正");
        }

        $uid = $user_info['id'];

        $config = getConfigPub($user_info['tenant_id']);
        if(date('H',time())<$config['charge_hour_star'] || date('H',time())>$config['charge_hour_end']){
            $this->error("不在充值时间段内【".$config['charge_hour_star']."点 - ".$config['charge_hour_end']."点】，无法充值");
        }
      /*  if ($config['money_rate'] <= 0){
            $this->error("请设置余额与钱的比例");
        }*/

        $recharge_num = $user_info['recharge_num'];
        $rateInfo = getRateList($tenant_id)[$currency_code];
        $rnb_money =  bcdiv($money,$rateInfo['rate'],4);// 人民币金额
        $coin =$rnb_money;// 钻石金额
        if ($coin<0){
            if ($user_info['coin']< abs($coin) ){
                $this->error("余额不足");
            }
        }

        if(!$coin){
            $this->error("变动金额错误：".$coin);
        }

        $result=M("users_charge_admin")->add(array(
            "orderno" => getOrderid($uid),
            "touid" => $uid,
            'user_login' => $user_info['user_login'],
            "user_type" => intval($user_info['user_type']),
            'balance_type' => 1, // 余额类型：1.可提现余额，2.不可提现余额
            "pre_balance" => floatval($user_info['coin']),
            "coin" => floatval($coin),
            "after_balance" => floatval(bcadd($user_info['coin'], $coin,4)),
            'money' => floatval($money),
            'rate' => floatval($rateInfo['rate']),
            'rnb_money' => floatval($rnb_money),
            'currency_code' => $currency_code,
            'status' => 1,
            'business_type' => intval($param['business_type']), // 业务类型：1.充值优惠-手工，2.手工充值，3.代理返点-手工，4.返水优惠-手工，5.其他优惠，6.异常加减分
            "addtime" => time(),
            "act_uid" => intval($_SESSION['ADMIN_ID']),
            "operated_by" => get_current_admin_user_login(),
            "ip" => get_client_ip(),
            'tenant_id' => intval($tenant_id),
            'remark' => trim($param['remark']),
        ));

        if($param['business_type'] == 2){
            M("users_charge")->add(array(
                'uid' => intval($uid),
                'user_login' => $user_info['user_login'],
                "user_type" => intval($user_info['user_type']),
                'money' => floatval($money),
                'currency_code' => $currency_code,
                'rnb_money' => floatval($rnb_money),
                'coin' => floatval($coin),
                'orderno' => 'sd_'.$uid.'_'.date('YmdHis').rand(100,999),
                'status' => 2,
                'addtime' => time(),
                'type' =>3,
                'tenant_id'=> intval($tenant_id),
                "operated_by" => get_current_admin_user_login(),
                'actual_money' => floatval($money),
                'rate' => floatval($rateInfo['rate']),
            ));
        }

        if ($result) {
            $action = 'manual_charge';
            switch ($param['business_type']){
                case 7:
                    $action = 'manual_shop_margin';
                    break;
                case 8:
                    $action = 'agent_reward';
                    break;
            }

            try {
                $data = array(
                    'coin' => array('exp', 'coin+' . $coin),
                    'recharge_num' => array('exp', 'recharge_num+1'),
                );
                if ($recharge_num == 0) {
                    $data['firstrecharge_coin'] = floatval($coin);
                }
                M("users")->where("id='$uid'")->save($data);
                $this->addCoinrecord([
                    'type' => 'income',
                    'uid' => intval($uid),
                    "user_login" => $user_info['user_login'],
                    "user_type" => intval($user_info['user_type']),
                    'addtime' => time(),
                    'tenant_id' => intval($tenant_id),
                    'action' => $action,
                    "pre_balance" => floatval($user_info['coin']),
                    'totalcoin' => floatval($coin),
                    "after_balance" => floatval(bcadd($user_info['coin'], $coin,4)),
                ]);
            }catch (\Exception $e){
                setAdminLog('【手动调整余额】失败'.json_encode($param).' | '.$e->getMessage());
                $this->error("操作失败！");
            }
            setAdminLog('【手动调整余额】成功'.json_encode($param));
            // 首充活动/分享活动 奖励赠送
            activity_reward($uid,$recharge_num,$coin,$tenant_id);

            // $coinrecordData = [
            //     'type' => 'income',
            //     'action' => 'Munal change by admin',
            //     'touid' => intval($uid),
            //     "user_login" => $user_info['user_login'],
            //     'user_type' => intval($user_info['user_type']),
            //     'giftid' => 0,
            //     'addtime' => time(),
            //     'tenant_id' => intval($tenant_id),

            //     "pre_balance" => floatval($user_info['coin']),
            //     'totalcoin' => 2,//金额
            //     "after_balance" => floatval(bcadd($user_info['coin'], $coin,4)),
            //     "giftcount" => 1,
            //     'is_withdrawable' => 1,
            // ];
            // $this->addCoinrecord($coinrecordData);
            
            delUserInfoCache($uid);
            $this->success("操作成功！");
        } else {
            setAdminLog('【手动调整余额】失败'.json_encode($param));
            $this->error("操作失败！");
        }
        
    }

    public function export()
    {
        $param = I('param.');
        $_GET['action_type'] = 'export';
        $_GET['p'] = 1;
        $_GET['num'] = 200000;

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

        $xlsName  = "Excel";
        $xlsData = $data['list'];
        foreach ($xlsData as $k => $v)
        {
            $userinfo=M("users")->field("user_login,user_nicename")->where("id='$v[touid]'")->find();
            $xlsData[$k]['user_nicename']= $userinfo['user_nicename']."(".$userinfo['user_login'].")"."(".$v['touid'].")";
            $xlsData[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']); 
        }
        $action="导出手动充值记录：".M("users_charge_admin")->getLastSql();
        setAdminLog($action);
        $cellName = array('A','B','C','D','E','F');
        $xlsCell  = array(
            array('id','序号'),
            array('admin','管理员'),
            array('user_nicename','会员 (账号)(ID)'),
            array('coin','充值点数'),
            array('ip','IP'),
            array('addtime','时间'),
        );
        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
    }
    

}
