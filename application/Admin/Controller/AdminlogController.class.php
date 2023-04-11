<?php

/**
 * 管理员日志
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

use Admin\Model\CommonModel;
use Admin\Model\LogComplexModel;

use Common\Controller\CustRedis;

use Admin\Cache\UsersLiveCache;

class AdminlogController extends AdminbaseController {

    private $admin_type_list = array(
        '1' => '默认',
        '2' => '弹窗警告	',
        '3' => '用户信息',
        '4' => '短视频',
        '5' => '长视频',
        '6' => '余额调整',
        '7' => '提现',
        '8' => '充值',
        '9' => 'vip等级',
        '10' => '广告',
        '11' => '前台菜单',
        '12' => '后台菜单',
        '13' => '登录',
        '14' => '网站设置',
        '15' => '平台设置',
        '16' => '角色',
        '17' => '推拉流线路',
        '18' => '文件存储',
        '19' => '播放下载线路',
        '20' => '系统配置',
        '21' => '层级配置',
        '22' => '后台账号',
        '23' => '汇率',
        '24' => '红包',
        '200' => '直播房间',
        '300' => '支付',
        '400' => '点赞',
    );

    private $socket_type_list = array(
        '1' => '连接失败',
        '2' => '重连失败',
        '3' => '发消息失败',
    );

    private $complex_type_list = array(
        '1' => '默认',
        '2' => '红包',
        '3' => '短视频',
        '400' => '点赞',
    );

    function index(){
        $param = I('param.');
        $map=array();
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
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime']=array("egt",strtotime($param['start_time']));
        }
         if(isset($param['end_time']) && $param['end_time'] != ''){
            $map['addtime']=array("elt",strtotime($param['end_time'].' 23:59:59'));
         }
         if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != '' ){
            $map['addtime']=array("between",array(strtotime($param['start_time']),strtotime($param['end_time'].' 23:59:59')));
         }
        if(isset($param['adminid']) && $param['adminid'] != ''){
            $map['adminid'] = $param['adminid'];
        }
        if(isset($param['admin']) && $param['admin'] != ''){
            $map['admin'] = $param['admin'];
        }
        if(isset($param['type']) && $param['type'] != ''){
            $map['type'] = $param['type'];
        }else{
            $param['type'] = '';
        }

    	$AdminLog=M("admin_log");
    	$count = $AdminLog->where($map)->count();
    	$page = $this->page($count, 20);
        $list = $AdminLog
                ->where($map)
                ->order("addtime DESC")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();

    	$type_list = $this->admin_type_list;
    	foreach ($list as $key=>$val){
            $list[$key]['type_name'] = isset($type_list[$val['type']]) ? $type_list[$val['type']] : $val['type'];
            $list[$key]['cutaction'] = strlen($val['action']) > 480 ? htmlspecialchars(substr($val['action'],0,480)).'...' : htmlspecialchars($val['action']);
            $list[$key]['action'] = htmlspecialchars($list[$key]['action']);
    	}

    	$this->assign('list', $list);
    	$this->assign('param', $param);
    	$this->assign("page", $page->show('Admin'));
    	$this->assign('type_list',$this->admin_type_list);
        $this->assign("role_id", getRoleId());
        $this->assign('tenant_list',getTenantList());
    	$this->display();
    }

    /*
     * 清除3个月以前的记录
     * */
    public function clear_three_month_before(){
        $result = M("admin_log")->where(['addtime'=>['lt', strtotime('-3 month', time())]])->delete();
        if($result >= 0){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }		
    
    function export()
    {
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
            $map['adminid']=array("like","%".$_REQUEST['keyword']."%"); 
        }

        $map['tenant_id']=getTenantIds();

        $xlsName  = "Excel";
        $AdminLog=M("admin_log");
        $xlsData=$AdminLog->where($map)->order("addtime DESC")->select();
        foreach ($xlsData as $k => $v)
        {
            $xlsData[$k]['msg_type']=$this->msg_type[$v['type']];
            $xlsData[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']);
            $xlsData[$k]['ip'] = long2ip ( $v['ip']);
        }
                $cellName = array('A','B','C','D','E','F');
                $xlsCell  = array(
            array('id','序号'),
            array('adminid','管理员id'),
            array('admin','管理员'),
            array('ip','IP地址'),
            array('action','操作内容'),
            array('addtime','提交时间'),
        );
        exportExcel($xlsName,$xlsCell,$xlsData,$cellName);
    }

    /*
     * 接口日志
     * */
    public function api(){
        $param = I('param.');
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
        if(!isset($param['start_time']) && !isset($param['end_time'])){
            $param['start_time'] = date('Y-m-d');
            $param['end_time'] = date('Y-m-d');
        }
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        if($param['start_time']!=''){
            $map['ctime']=array("egt",strtotime($param['start_time'].' 00:00:00'));
        }
        if($param['end_time']!=''){
            $map['ctime']=array("elt",strtotime($param['end_time'].' 23:59:59'));
        }
        if($param['start_time']!='' && $param['end_time']!='' ){
            $map['ctime']=array("between",array(strtotime($param['start_time'].' 00:00:00'),strtotime($param['end_time'].' 23:59:59')));
        }

        if(isset($param['service']) && $param['service'] != ''){
            $map['service']=$param['service'];
        }
        if(isset($param['ip']) && $param['ip'] != ''){
            $map['ip']=$param['ip'];
        }

        $count=M("log_api")->where($map)->count();
        $page = $this->page($count, 20);
        $lists = M("log_api")
            ->where($map)
            ->order("id desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach($lists as $k=>$v){
            $lists[$k]['remark'] = htmlspecialchars($v['remark']);
            $lists[$k]['cutct'] = strlen($v['ct']) > 100 ? htmlspecialchars(substr($v['ct'],0,100)).'...' : htmlspecialchars($v['ct']);
            $lists[$k]['ct'] = htmlspecialchars($v['ct']);
            $lists[$k]['cuturi'] = strlen($v['uri']) > 100 ? htmlspecialchars(substr($v['uri'],0,100)).'...' : htmlspecialchars($v['uri']);
        }

        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->assign('logapi_reqeuest_status', $this->redis->get('logapi_reqeuest_status'));
        $this->assign("role_id", getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->display();
    }

    // 开启或关闭 接口请求日志
    public function logapi_reqeuest_status(){
        if(IS_AJAX){
            $param = I('param.');
            if(isset($param['status']) && $param['status'] == 1){
                $this->redis->set('logapi_reqeuest_status',1,60*5);
            }else{
                $this->redis->del('logapi_reqeuest_status');
            }
//            CommonModel::getInstance()->autoUpdateUserType();

            $this->success('操作成功');
        }
        $this->error('请求方式错误');
    }

    /*
    * socket日志
    * */
    public function socket(){
        $param = I('param.');
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
        if(!isset($param['start_time']) && !isset($param['end_time'])){
            $param['start_time'] = date('Y-m-d');
            $param['end_time'] = date('Y-m-d');
        }
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        if($param['start_time']!=''){
            $map['ctime']=array("egt",strtotime($param['start_time'].' 00:00:00'));
        }
        if($param['end_time']!=''){
            $map['ctime']=array("elt",strtotime($param['end_time'].' 23:59:59'));
        }
        if($param['start_time']!='' && $param['end_time']!='' ){
            $map['ctime']=array("between",array(strtotime($param['start_time'].' 00:00:00'),strtotime($param['end_time'].' 23:59:59')));
        }

        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid']=$param['uid'];
        }

        if(isset($param['type']) && $param['type'] != ''){
            $map['type'] = $param['type'];
        }else{
            $param['type'] = '';
        }

        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid']=$param['uid'];
        }

        if(isset($param['event']) && $param['event'] != ''){
            $map['event']=$param['event'];
        }

        $count=M("log_socket")->where($map)->count();
        $page = $this->page($count, 20);
        $lists = M("log_socket")
            ->where($map)
            ->order("id desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $type_list = $this->socket_type_list;
        foreach($lists as $key=>$val){
            $lists[$key]['user_info'] = getUserInfo($val['uid']);
            $lists[$key]['type_name'] = isset($type_list[$val['type']]) ? $type_list[$val['type']] : $val['type'];
            $lists[$key]['ct_send_ct'] = strlen($val['send_ct']) > 100 ? htmlspecialchars(substr($val['send_ct'],0,100)).'...' : htmlspecialchars($val['send_ct']);
            $lists[$key]['send_ct'] = htmlspecialchars($val['ct']);
            $lists[$key]['cut_description'] = strlen($val['description']) > 100 ? htmlspecialchars(substr($val['description'],0,100)).'...' : htmlspecialchars($val['description']);
            $lists[$key]['description'] = htmlspecialchars($val['description']);
        }

        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->assign("role_id", getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('type_list',$type_list);
        $this->display();
    }

    /*
      * 综合日志
      * */
    public function complex(){
        $param = I('param.');
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
        if(!isset($param['start_time']) && !isset($param['end_time'])){
            $param['start_time'] = date('Y-m-d');
            $param['end_time'] = date('Y-m-d');
        }
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        if($param['start_time']!=''){
            $map['ctime']=array("egt",strtotime($param['start_time'].' 00:00:00'));
        }
        if($param['end_time']!=''){
            $map['ctime']=array("elt",strtotime($param['end_time'].' 23:59:59'));
        }
        if($param['start_time']!='' && $param['end_time']!='' ){
            $map['ctime']=array("between",array(strtotime($param['start_time'].' 00:00:00'),strtotime($param['end_time'].' 23:59:59')));
        }

        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_acount']) && $param['user_acount'] != ''){
            $map['user_acount'] = $param['user_acount'];
        }
        if(isset($param['admin_id']) && $param['admin_id'] != ''){
            $map['admin_id'] = $param['admin_id'];
        }
        if(isset($param['admin']) && $param['admin'] != ''){
            $map['admin_acount'] = $param['admin_acount'];
        }
        if(isset($param['type']) && $param['type'] != ''){
            $map['type'] = $param['type'];
        }else{
            $param['type'] = '';
        }
        if(isset($param['remark']) && $param['remark'] != ''){
            $map['remark'] = $param['remark'];
        }
        if(isset($param['ip']) && $param['ip'] != ''){
            $map['ip']=$param['ip'];
        }

        $count=M("log_complex")->where($map)->count();
        $page = $this->page($count, 20);
        $list = M("log_complex")
            ->where($map)
            ->order("id desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $type_list = $this->complex_type_list;
        $tenant_list = getTenantList();
        $tenant_list = array_column($tenant_list,null,'id');
        foreach($list as $key=>$val){
            $list[$key]['type_name'] = isset($type_list[$val['type']]) ? $type_list[$val['type']] : $val['type'];
            $list[$key]['remark'] = htmlspecialchars($val['remark']);
            $list[$key]['cutct'] = strlen($val['ct']) > 100 ? htmlspecialchars(substr($val['ct'],0,100)).'...' : htmlspecialchars($val['ct']);
            $list[$key]['ct'] = htmlspecialchars($val['ct']);
            $list[$key]['tenant_name'] = isset($tenant_list[$val['tenant_id']]) ? $tenant_list[$val['tenant_id']]['name'] : $val['tenant_id'];
        }

        $this->assign('list', $list);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->assign("role_id", getRoleId());
        $this->assign('tenant_list', $tenant_list);
        $this->assign('type_list', $type_list);
        $this->display();
    }

}
