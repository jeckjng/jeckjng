<?php

/**
 * 提现
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Api\Controller\ProfitShareController as ApiProfitShare;

class ProfitShareController extends AdminbaseController {
    var $type=array(
        '1'=>'支付宝',
        '2'=>'微信',
        '3'=>'银行卡',
    );
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

       if($_REQUEST['start_time']!=''){
           $map['addtime']=array("gt",strtotime($_REQUEST['start_time']));
       }
         
       if($_REQUEST['end_time']!=''){
           $map['addtime']=array("lt",strtotime($_REQUEST['end_time']));
       }
       if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
            $map['addtime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'])));
       }

        if($_REQUEST['tenant_name']!=''){
            $map['tenant_name']=$_REQUEST['tenant_name'];
        }

			
    	$model = M("profit_daysharing");
    	$count = $model->where($map)->count();
    	$page = $this->page($count, $page_size);
    	$lists = $model->where($map)->order("addtime DESC")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

    	$this->assign('lists', $lists);
    	$this->assign('type', $this->type);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->assign('tenant_list',getTenantList());
    	$this->display();
    }
    function exportindex()
    {
        $param = I('param.');
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

        if($_REQUEST['tenant_name']!=''){
            $map['tenant_name']=$_REQUEST['tenant_name'];
            $_GET['tenant_name']=$_REQUEST['tenant_name'];
        }

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $map['tenant_id'] = $tenant_id;

        $xlsName  = "Excel";
        $cashrecord=M("profit_daysharing");
        $xlsData=$cashrecord->where($map)->order("addtime DESC")->select();

        $action="导出分成报表：".M("profit_daysharing")->getLastSql();
        setAdminLog($action);
        $cellName = array('A','B','C','D','E','F','G','H','I');

        $xlsCell  = array(
            array('id','序号'),
            array('collet_day','时间'),
            array('tenant_name','租户'),
            array('cp_share','彩票分成'),
            array('zb_share','礼物分成'),
            array('cpjz_share','彩票分成（家族）'),
            array('zbjz_share','礼物分成（家族）'),
            array('cpzb_share','彩票分成（主播）'),
            array('zbzb_share','礼物分成（主播）'),
        );

        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
    }


    function auth(){
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
            $map['addtime']=array("gt",strtotime($_REQUEST['start_time']));
        }

        if($_REQUEST['end_time']!=''){
            $map['addtime']=array("lt",strtotime($_REQUEST['end_time']));
        }
        if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
            $map['addtime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'])));
        }

        if($_REQUEST['uid']!=''){
            $map['uid|user_login']=$_REQUEST['uid'];
        }
        if($_SESSION['admin_type'] == 1){
            $userinfo =M("users")->where("user_login='".$_SESSION['name']."'")->find();

            $author = array();
            $author_ids = array();
            if($userinfo['familyids']){
                $domain = strstr($userinfo['familyids'], ',');
                if(!$domain){
                    $users_family =M("users_family")->where("familyid=".$userinfo['familyids']."")->select();
                    foreach ($users_family as $key=>$value){
                        $author[] = $value['user_login'];
                        array_push($author_ids,$value['uid']);
                    }
                }else{
                    $familyid = explode(',',$userinfo['familyids']);
                    foreach ($familyid as $value){
                        $users_family =M("users_family")->where("familyid=".$value."")->select();
                        foreach ($users_family as $key=>$value){
                            $author[] = $value['user_login'];
                            array_push($author_ids,$value['uid']);
                        }
                    }
                }
            }else{
                array_push($author_ids,0);
            }
            if ($author_ids){
                $map['uid']=['in',$author_ids];
            }else{
                $map['uid']='-1';
            }

        }

        $model = M("users_basicsalary");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $lists = $model->where($map)->order("addtime DESC")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach($lists as $k=>$v){
            if($v['tenant_id'] == '0'){
                $userinfo = getUserInfo($v['uid']);
                $lists[$k]['tenant_id'] = isset($userinfo['tenant_id']) ? intval($userinfo['tenant_id']) : 0;
                $model->where(['id'=>$v['id']])->save(['tenant_id'=>intval($userinfo['tenant_id'])]);
            }
        }

        $totalsum = $model->where($map)->field('sum(tatal_money) as tatal_money, sum(time_to_sec(hour_total)) as hour_total, sum(bet_money) as bet_money, 
                        sum(gift_money) as gift_money, sum(money) as money, sum(hour_limit) as hour_limit, sum(gift_limit) as gift_limit')
                        ->find();
        $pagesum = array('tatal_money'=>0,'hour_total'=>0,'bet_money'=>0,'gift_money'=>0,'money'=>0,'hour_limit'=>0,'gift_limit'=>0);
        foreach ($lists as $key=>$value){
            if($_SESSION['admin_type'] == 1){
                if(!in_array($value['user_login'],$author)){
                    unset($lists[$key]);
                    continue;
                }
            }
            $pagesum['tatal_money'] += $value['tatal_money'];
            $pagesum['hour_total'] += str_to_second($value['hour_total']);
            $pagesum['bet_money'] += $value['bet_money'];
            $pagesum['gift_money'] += $value['gift_money'];
            $pagesum['money'] += $value['money'];
            $pagesum['hour_limit'] += $value['hour_limit'];
            $pagesum['gift_limit'] += $value['gift_limit'];
        }

        $pagesum['gift_limit'] = bcadd($pagesum['gift_limit'],0,2);
        $pagesum['hour_total'] = second_to_str($pagesum['hour_total']);
        $totalsum['hour_total'] = second_to_str($totalsum['hour_total']);

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign('pagesum', $pagesum);
        $this->assign('totalsum', $totalsum);
        $this->assign('type', $this->type);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->assign('tenant_list',getTenantList());
        $this->display();
    }

    function exportauth()
    {
        $param = I('param.');
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

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $map['tenant_id'] = $tenant_id;

        $xlsName  = "综合报表";
        $cashrecord=M("users_basicsalary");
        $xlsData=$cashrecord->where($map)->order("addtime DESC")->select();

        $action="导出综合报表：".M("users_basicsalary")->getLastSql();
        setAdminLog($action);
        $cellName = array('A','B','C','D','E','F','G','H','I','J');

        $xlsCell  = array(
            array('uid','ID'),
            array('user_login','用户名'),
            array('collet_day','时间'),
            array('tatal_money','总收益'),
            array('hour_total','当日时长'),
            array('bet_money','彩票分成'),
            array('gift_money','礼物分成'),
            array('money','底薪结算'),
            array('hour_limit','时长任务'),
            array('gift_limit','礼物任务'),
        );

        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
    }
    function sendgift(){
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
            $map['addtime']=array("gt",strtotime($_REQUEST['start_time']));
        }

        if($_REQUEST['end_time']!=''){
            $map['addtime']=array("lt",strtotime($_REQUEST['end_time']));
        }
        if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
            $map['addtime']=array("between",array(strtotime($_REQUEST['start_time']),strtotime($_REQUEST['end_time'])));
        }
        if($_REQUEST['uid']!=''){
            $map['uid|user_login']=$_REQUEST['uid'];
        }

        if($_REQUEST['tenant_name']!=''){
            $map['tenant_name']=$_REQUEST['tenant_name'];
        }


        $model = M("consumption_collect");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $lists = $model->where($map)->order("addtime DESC,id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach($lists as $k=>$v){
            if($v['tenant_id'] == '0'){
                $userinfo = getUserInfo($v['uid']);
                $lists[$k]['tenant_id'] = isset($userinfo['tenant_id']) ? intval($userinfo['tenant_id']) : 0;
                $model->where(['id'=>$v['id']])->save(['tenant_id'=>intval($userinfo['tenant_id'])]);
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign('type', $this->type);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->assign('tenant_list',getTenantList());
        $this->display();
    }
    function exportsendgift()
    {
        $param = I('param.');
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

        if($_REQUEST['tenant_name']!=''){
            $map['tenant_name']=$_REQUEST['tenant_name'];
            $_GET['tenant_name']=$_REQUEST['tenant_name'];
        }

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $map['tenant_id'] = $tenant_id;

        $xlsName  = "打赏报表";
        $cashrecord=M("consumption_collect");
        $xlsData=$cashrecord->where($map)->order("addtime DESC")->select();

        $action="导出打赏报表：".M("consumption_collect")->getLastSql();
        setAdminLog($action);
        $cellName = array('A','B','C','D','E','F','G');

        $xlsCell  = array(
            array('uid','ID'),
            array('user_login','会员'),
            array('action','行为'),
            array('totalcoin','总价'),
            array('collet_day','时间'),
            array('tenant_name','所属租户'),
            array('tenantuser_total','所属租户分成'),

        );

        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
    }

    /*
     * 综合汇总表
     * */
    public function complex_summary(){
        $redis = connectionRedis();
        $param = I('param.');
        if(!isset($param['ctime']) || !$param['ctime']){
            $param['ctime'] = date('Y-m-d');
        }
        if(!isset($param['type'])){
            $param['type'] = ['day'=>'today'];
        }
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $timeselect = get_timeselect(); // 获取时间格式

        $tenant_map = array();
        $live_now_count_map = ['islive'=>1];
        if(getRoleId() == 1){ // 超管
            if(isset($param['tenant_id']) && $param['tenant_id']){
                $tenant_map['id'] = $param['tenant_id'];
                $map['tenant_id'] = $param['tenant_id'];
                $cs_map['tenant_id'] = $param['tenant_id'];
                $live_now_count_map['tenant_id'] = $param['tenant_id'];
            }
        }else{
            $tenant_map['id'] = getTenantIds();
            $map['tenant_id'] = getTenantIds();
            $cs_map['tenant_id'] = $param['tenant_id'];
            $live_now_count_map['tenant_id'] = getTenantIds();
        }

        $tenant_list = getTenantList(); // M('tenant')->where($tenant_map)->field('id,name')->select(); // 租户列表

        $map['starttime'] = array('between',[strtotime($param['ctime'].' 00:00:00'),strtotime($param['ctime'].' 23:59:59')]);

        if(isset($param['type']['thisweek'])){
            $map['starttime'] = array('between',[strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])]);
        }
        if(isset($param['type']['lastweek'])){
            $map['starttime'] = array('between',[strtotime($timeselect['yweek_start']),strtotime($timeselect['yweek_end'])]);
        }
        if(isset($param['type']['thismonth'])){
            $map['starttime'] = array('between',[strtotime($timeselect['tmonth_start']),strtotime($timeselect['tmonth_end'])]);
        }
        if(isset($param['type']['lastmonth'])){
            $map['starttime'] = array('between',[strtotime($timeselect['ymonth_start']),strtotime($timeselect['ymonth_end'])]);
        }
        $date_between = date('Y-m-d',$map['starttime'][1][0]).' - '.date('Y-m-d',$map['starttime'][1][1]);

        $data['live_count'] = M('users_liverecord')->where($map)->count() + M('users_live')->where($map)->count(); // 开播直播间总数
        $data['live_now_count'] = M('users_live')->where($live_now_count_map)->count(); // 在线直播间总数
        $data['watching_num'] = 0; // 在线观看人数，初始化为 0

        if(isset($map['tenant_id'])){
            $data['watching_num'] += $redis->zCard('watching_num'.$map['tenant_id']);
        }else{
            foreach ($tenant_list as $key=>$val){
                $data['watching_num'] += $redis->zCard('watching_num'.$val['id']);
            }
        }

        $is_this_dwm = false; // 时间是否今天、本周、本月
        $ApiProfitShare = new ApiProfitShare();
        if(getRoleId() == 1){ // 超管
            // 日汇总
            if(isset($param['type']['day'])){
                if($param['ctime']==date('Y-m-d')){
                    $temp = array();
                    foreach ($tenant_list as $key=>$val){
                        $resdata = $ApiProfitShare->complex_summary_this_day($val['id']);
                        foreach ($resdata as $k=>$v){
                            $temp[$k] = isset($temp[$k]) ? ($temp[$k]+$v) : $v;
                        }
                    }
                    $data = array_merge($data,$temp);
                    $is_this_dwm = true;
                }else{
                    $cs_map['collet_day_time'] = strtotime($param['ctime']);
                }
            }
            // 本周
            if(isset($param['type']['thisweek'])){
                $temp = array();
                foreach ($tenant_list as $key=>$val){
                    $resdata = $ApiProfitShare->complex_summary_this_week($val['id']);
                    foreach ($resdata as $k=>$v){
                        $temp[$k] = isset($temp[$k]) ? ($temp[$k]+$v) : $v;
                    }
                }
                $data = array_merge($data,$temp);
                $is_this_dwm = true;
            }else if(isset($param['type']['lastweek'])){ // 上周
                $cs_map['collet_week_time'] = strtotime($timeselect['yweek_start']);
            }
            // 本月
            if(isset($param['type']['thismonth'])){
                $temp = array();
                foreach ($tenant_list as $key=>$val){
                    $resdata = $ApiProfitShare->complex_summary_this_month($val['id']);
                    foreach ($resdata as $k=>$v){
                        $temp[$k] = isset($temp[$k]) ? ($temp[$k]+$v) : $v;
                    }
                }
                $data = array_merge($data,$temp);
                $is_this_dwm = true;
            }else if(isset($param['type']['lastmonth'])){  // 上月
                $cs_map['collet_month_time'] = strtotime($timeselect['ymonth_start']);
            }

            // 不是今天、本周、本月的数据查询
            if($is_this_dwm === false){
                $cs_data = M('complex_summary')->where($cs_map)->select();
                $complex_summary_data = array();
                foreach ($cs_data as $key=>$val){
                    foreach ($val as $k=>$v){
                        $complex_summary_data[$k] = isset($complex_summary_data[$k]) ? ($complex_summary_data[$k]+$v) : $v;
                    }
                }
                $complex_summary_data = empty($complex_summary_data) > 0 ? $this->getEmptyData() : $complex_summary_data;
                $data = array_merge($data,$complex_summary_data);
            }

        }else{
            $tenant_id = getTenantIds();
            // 日汇总
            if(isset($param['type']['day'])){
                if($param['ctime']==date('Y-m-d')){
                    $resdata = $ApiProfitShare->complex_summary_this_day($tenant_id);
                    $data = array_merge($data,$resdata);
                    $is_this_dwm = true;
                }else{
                    $cs_map['collet_day_time'] = strtotime($param['ctime']);
                }
            }
            // 本周
            if(isset($param['type']['thisweek'])){
                $resdata = $ApiProfitShare->complex_summary_this_week($tenant_id);
                $data = array_merge($data,$resdata);
                $is_this_dwm = true;
            }else if(isset($param['type']['lastweek'])){ // 上周
                $cs_map['collet_week_time'] = strtotime($timeselect['yweek_start']);
            }
            // 本月
            if(isset($param['type']['thismonth'])){
                $resdata = $ApiProfitShare->complex_summary_this_month($tenant_id);
                $data = array_merge($data,$resdata);
                $is_this_dwm = true;
            }else if(isset($param['type']['lastmonth'])){ // 上月
                $cs_map['collet_month_time'] = strtotime($timeselect['ymonth_start']);
            }

            // 不是今天、本周、本月的数据查询
            if($is_this_dwm === false){
                $cs_map['tenant_id'] = intval($tenant_id);
                $cs_data = M('complex_summary')->where($cs_map)->select();
                $complex_summary_data = array();
                foreach ($cs_data as $key=>$val){
                    foreach ($val as $k=>$v){
                        $complex_summary_data[$k] = isset($complex_summary_data[$k]) ? ($complex_summary_data[$k]+$v) : $v;
                    }
                }
                $complex_summary_data = empty($complex_summary_data) > 0 ? $this->getEmptyData() :  $complex_summary_data;
                $data = array_merge($data,$complex_summary_data);
            }
        }

        $data['live_totalam'] = $data['timecharge_am'] + $data['roomcharge_am'] + $data['sendbarrage_am'] + $data['sendgift_am'] + $data['buycar_am'];
        $data['live_pa_totalam'] = $data['pa_roomcharge_am'] + $data['pa_sendbarrage_am'] + $data['pa_sendgift_am'] + $data['pa_bet_am'];
        $data['live_pf_totalam'] = $data['pf_roomcharge_am'] + $data['pf_sendbarrage_am'] + $data['pf_sendgift_am'] + $data['pf_bet_am'];

        $this->assign('data',$data);
        $this->assign('tenant_list', $tenant_list);
        $this->assign('param',$param);
        $this->assign('role_id',getRoleId());
        $this->assign('timeselect',$timeselect);
        $this->assign('date_between',$date_between);
        $this->display();
    }

    public function getEmptyData(){
        $data = array(
            'timecharge_am' => 0,
            'timecharge_num' => 0,
            'roomcharge_am' => 0,
            'roomcharge_num' => 0,
            'sendbarrage_am' => 0,
            'sendbarrage_num' => 0,
            'sendgift_am' => 0,
            'sendgift_num' => 0,
            'buycar_am' => 0,
            'buycar_num' => 0,

            'pa_roomcharge_am' => 0,
            'pa_roomcharge_num' => 0,
            'pa_sendbarrage_am' => 0,
            'pa_sendbarrage_num' => 0,
            'pa_sendgift_am' => 0,
            'pa_sendgift_num' => 0,
            'pa_bet_am' => 0,
            'pa_bet_num' => 0,

            'pf_roomcharge_am' => 0,
            'pf_roomcharge_num' => 0,
            'pf_sendbarrage_am' => 0,
            'pf_sendbarrage_num' => 0,
            'pf_sendgift_am' => 0,
            'pf_sendgift_num' => 0,
            'pf_bet_am' => 0,
            'pf_bet_num' => 0,
        );
        return $data;
    }

}
