<?php

/**
 * VIP管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class UseractionController extends AdminbaseController {

	public $long=array(
		'1'=>'1个月',
		'3'=>'3个月',
		'6'=>'6个月',
		'12'=>'12个月',
	);

    /*
     * 用户行为查询
     * */
    function index(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $map = array();
        if($param['uid']){
            $map['uid'] = $param['uid'];
        }
        if($param['user_login']){
            $map['user_login'] = $param['user_login'];
        }
        if($param['vip']){
            $map['vip'] = $param['vip'];
        }
        if ($param['action_type']) {
            $map['action_type'] = [ 'in', explode(',',I('action_type'))];
            $param['action_type_json'] = json_encode(explode(',',I('action_type')));
        }else if(I('action_type')=='0'){
            $param['action_type_json'] = json_encode(explode(',',I('action_type')));
        }

        $tenantId=getTenantIds();
        if(getRoleId() == 1){

        }else{
            $map['tenant_id'] = $tenantId;
        }

        $model = M("users_action");
        $count = $model->where($map)->count();
        $page = $this->page($count, 20);
        $list = $model->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        $actionType = actionType();
        foreach($list as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            $vip_grade = M("vip_grade")->where(['vip_grade' =>$val['vip']])->find();
            $list[$key]['name'] = isset($vip_grade['name']) ? $vip_grade['name'] : 'vip1';
            $list[$key]['action_type']=$actionType[$val['action_type']];
            if(in_array($val['action_type'],[42,44,50,52,54,56,66])){
                $list[$key]['action_time'] = sec2Time($val['action_time']);
            }else{
                $list[$key]['action_time'] = $val['action_time'];
            }
            $list[$key]['status'] = $val['status']==1 ? '已发放' : '未发放';
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
        }

        $vip_list = M('vip_grade')->order('vip_grade')->select();

        $this->assign([
            'bootstrap_select_css' => bootstrap_select_css(),
            'bootstrap_select_js' => bootstrap_select_js(),
        ]);
        $this->assign('actionType', $actionType);
        $this->assign('list', $list);
        $this->assign('vip_list', $vip_list);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
     * 积分配置
     * */
    function integral_config(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['vip'])){
                $this->error('请添加VIP等级');
            }
            foreach ($param['vip'] as $key=>$val){
                $data = array(
                    'type' => 2,
                    'vip' => $key,
                    'val' => json_encode($val),
                );
                if(M('integral_config')->where(['type'=>2,'vip'=>$key])->find()){
                    M("integral_config")->where(['type'=>2,'vip'=>$key])->save(['val'=>$data['val']]);
                }else{
                    M('integral_config')->add($data);
                }
            }

            $this->success('操作成功');
        }

        $lists = M('integral_config')->where(['type'=>2])->field('vip,val')->order("vip asc")->select();

        $action_type = actionType();

        foreach($lists as $key=>$val){
            $lists[$key]['vip_name'] = M("vip_grade")->where(array('vip_grade' =>$val['vip'] ))->getField('name');
            $ic_val = json_decode($val['val'],true);
            $ic_val = array_column($ic_val,null,'k');
            $temp = array();
            foreach ($action_type as $k=>$v){
                $d = [];
                $d['k'] = $k;
                $d['name'] = $v;
                $d['val'] = isset($ic_val[$k]) ? $ic_val[$k]['val'] : 0;
                array_push($temp,$d);
            }
            $lists[$key]['val'] = $temp;
        }

        $this->assign('lists', $lists);
        $this->display();
    }

    /*
     * 添加vip等级
     * */
    function vip_level(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['vip'])){
                $this->error('缺少参数');
            }

            $action_type = actionType();

            $val = [];
            foreach ($action_type as $k=>$v){
                $tem = [
                    'k'=>$k,
                    'val'=>0,
                ];
                array_push($val,$tem);
            }

            $lists = M('integral_config')->where(['type'=>2])->field('vip')->select();
            $lists = array_column($lists,null,'vip');

            foreach ($param['vip'] as $k=>$v){
                $data = array(
                    'type' => 2,
                    'vip' => $v,
                    'val' => json_encode($val),
                );
                if(!M('integral_config')->where(['type'=>2,'vip'=>$v])->find()){
                    M('integral_config')->add($data);
                }
                if(isset($lists[$v])){
                    unset($lists[$v]);
                }
            }
            foreach ($lists as $k=>$v){
                M('integral_config')->where(['type'=>2,'vip'=>$k])->delete();
            }
            $this->success('操作成功');
        }

        $lists = M('integral_config')->where(['type'=>2])->field('vip')->select();
        $lists = array_column($lists,null,'vip');

        $vip_list = M('vip_grade')->order('vip_grade')->select();

        $this->assign('lists', $lists);
        $this->assign('vip_list', $vip_list);
        $this->display();
    }

    /*
     * 批量任务设置
     * */
    function multi_set(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['vip'])){
                $this->error('缺少参数');
            }

            $action_type = actionType();

            foreach ($param['vip'] as $key=>$val){
                if($val==''){
                    continue;
                }
                if(!($val >= 0 && $val <= 9999)) {
                    $this->error('积分请输入如下范围：[0-9999]');
                }
                $config_val = [];
                foreach ($action_type as $k=>$v){
                    $tem = [
                        'k'=>$k,
                        'val'=>$val['val'],
                    ];
                    array_push($config_val,$tem);
                }
                $data = array(
                    'type' => 2,
                    'vip' => $key,
                    'val' => json_encode($config_val),
                );

                if(M('integral_config')->where(['type'=>2,'vip'=>$key])->find()){
                    M('integral_config')->where(['type'=>2,'vip'=>$key])->save(['val'=>$data['val']]);
                }else{
                    M('integral_config')->add($data);
                }
            }
            $this->success('操作成功');
        }

        $lists = M('integral_config')
            ->join('cmf_vip_grade on cmf_vip_grade.vip_grade=cmf_integral_config.vip','left')
            ->field('cmf_integral_config.vip,cmf_vip_grade.name')
            ->where(['cmf_integral_config.type'=>2])->select();

        $this->assign('list', $lists);
        $this->display();
    }


}
