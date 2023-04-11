<?php

/**
 * VIP管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use function FastRoute\TestFixtures\empty_options_cached;

class IntegralController extends AdminbaseController {

    function _initialize() {
        parent::_initialize();

    }

    /*
     * 积分明细
     * */
    function index(){
        $param = I('param.');
        $map=array();
        $start_time   = isset($param["start_time"]) && $param["start_time"]!='' ? $param["start_time"] . " 00:00:00" : "1997-01-01 00:00:00";
        $end_time     = isset($param["end_time"]) && $param["end_time"]!='' ? $param["end_time"] . " 23:59:59" : date( "Y-m-d" ) . " 23:59:59";

        if($param['start_time']!=''){
            $_GET['start_time'] = $param['start_time'];
        }
        if($param['end_time']!='' ){
            $_GET['end_time'] = $param['end_time'];
        }

        $map['ctime'] = array("between",array(strtotime($start_time),strtotime($end_time)?strtotime($end_time):time()));
        if(isset($param["keyword"]) && !empty($param["keyword"])){
            $user_info = M("users")->where(['id|user_login|user_nicename'=>['eq',$param["keyword"]],'user_type'=>2])
                            ->field('id,user_login,user_nicename')
                            ->find();
            $map['uid'] = isset($user_info['id']) ? $user_info['id'] : $param["keyword"];
            $_GET['keyword'] = $param['keyword'];
        }
        if(isset($param["change_type"]) && !empty($param["change_type"])){
            $map['change_type'] = $param["change_type"];
            $_GET['change_type'] = $param['change_type'];
        }
        if(isset($param["act_type"]) && !empty($param["act_type"])){
            $map['act_type'] = $param["act_type"];
            $_GET['act_type'] = $param['act_type'];
        }

        $tenantId=getTenantIds();
        $role_id=$_SESSION['role_id'];
        if($role_id==1) {
            $count = M("integral_log")->where($map)->count();
            $page = $this->page($count, 20);
            $lists = M("integral_log")->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        }else{
            $map['tenant_id'] = $tenantId;
            $count = M("integral_log")->where($map)->count();
            $page = $this->page($count, 20);
            $lists = M("integral_log")->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        }

        $integralLogActType = integralLogActType();
        foreach($lists as $key=>$val){
            $lists[$key]['userinfo'] = getUserInfo($val['uid']);
            $lists[$key]['act_userinfo'] = getUserInfo($val['act_uid']);
            $lists[$key]['act_type'] = isset($integralLogActType[$val['act_type']]) ? $integralLogActType[$val['act_type']] : $val['act_type'];
            $lists[$key]['status'] = $val['status']==1 ? '已完成' : '进行中';
        }

        $this->assign('integralLogActType', $integralLogActType);
        $this->assign('param', $param);
    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }

    /*
     * 注册积分设置
     * */
	function integral_config(){
        if(IS_POST){
            $level_1=I("level_1");
            $level_2=I("level_2");
            $level_3=I("level_3");
            $level_4=I("level_4");
            $level_5=I("level_5");

            if(!($level_1 >= 0 && $level_1 <= 9999)) {
                $this->error('1级赠送积分请输入如下范围：[0 - 9999]');
            }
            if(!($level_2 >= 0 && $level_2 <= 9999)) {
                $this->error('2级赠送积分请输入如下范围：[0-9999]');
            }
            if(!($level_3 >= 0 && $level_3 <= 9999)) {
                $this->error('3级赠送积分请输入如下范围：[0-9999]');
            }
            if(!($level_4 >= 0 && $level_4 <= 9999)) {
                $this->error('4级赠送积分请输入如下范围：[0-9999]');
            }
            if(!($level_5 >= 0 && $level_5 <= 9999)) {
                $this->error('5级赠送积分请输入如下范围：[0-9999]');
            }

            $data = [
                'level_1' => $level_1,
                'level_2' => $level_2,
                'level_3' => $level_3,
                'level_4' => $level_4,
                'level_5' => $level_5,
            ];
            $result = M("integral_config")->where(['type'=>1])->save($data);
            $action="注册积分设置：{$result}";
            setAdminLog($action);
            $this->success('操作成功');
        }

        $info = M("integral_config")->where(['type'=>1])->find();
        if(!$info){
            $IntegralConfig = M("integral_config");
            $IntegralConfig->level_1 = 0;
            $IntegralConfig->level_2 = 0;
            $IntegralConfig->level_3 = 0;
            $IntegralConfig->level_4 = 0;
            $IntegralConfig->level_5 = 0;
            $IntegralConfig->type = 1;
            $IntegralConfig->create($data);
            $IntegralConfig->add();
            $info = M("integral_config")->where(['type'=>1])->find();
        }
        $this->assign('info', $info);
        $this->display();
	}	


}
