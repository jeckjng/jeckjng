<?php

/**
 * 红包
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Common\Controller\CustRedis;

class RedController extends AdminbaseController {

    private $red_time_list = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','00');

    private $red_type_list = array(
        '1' => '普通红包',
        '2' => '购物券',
    );

    private $shopping_voucher_status_list = array(
        '1' => array(
            'name' => '未使用',
            'color' => 'red',
        ),
        '2' => array(
            'name' => '已使用',
            'color' => 'green',
        ),
    );



    var $type=array(
        '0'=>'平均',
        '1'=>'手气',
    );
    var $type_grant=array(
        '0'=>'立即',
        '1'=>'延迟',
    );

    public function index(){

		$map=array();
		$tenantId=getTenantIds();
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }
        else{
            //租户id条件
            $map['tenant_id']=$tenantId;
        }
		if($_REQUEST['uid']!=''){
			$map['uid']=$_REQUEST['uid']; 
			$_GET['uid']=$_REQUEST['uid'];
		}

			
    	$Red=M("red");

    	$count=$Red->where($map)->count();
    	$page = $this->page($count);
    	$lists = $Red
			->where($map)
			->order("id DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			
		foreach($lists as $k=>$v){
            $v['userinfo']=getUserInfo($v['uid']);
            $v['anchorinfo']=getUserInfo($v['liveuid']);
            $lists[$k]=$v;
		}
			
    	$this->assign('lists', $lists);
    	$this->assign('type', $this->type);
    	$this->assign('type_grant', $this->type_grant);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }

    public function index2(){
        $redid=I("redid");
		$map=array();
        
        $map['redid']=$redid;
        $tenantId=getTenantIds();
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }
        else{
            //租户id条件
            $map['tenant_id']=$tenantId;
        }
 
		if($_REQUEST['uid']!=''){
			$map['uid']=$_REQUEST['uid']; 
			$_GET['uid']=$_REQUEST['uid'];
		}

    	$Redrecord=M("red_record");
    	$count=$Redrecord->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $Redrecord
			->where($map)
			->order("addtime DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			
		foreach($lists as $k=>$v){
			$userinfo=getUserInfo($v['uid']);
			$lists[$k]['userinfo']=$userinfo;
		}
			
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }

    /*
     * 红包列表
     * */
    public function red_list(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $map['tenant_id'] = intval($tenant_id);

        $param['tenant_id'] = $tenant_id;
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
        if(isset($param['name']) && $param['name'] !=''){
            $map['name'] = $param['name'];
        }
        if(isset($param['type']) && $param['type'] !=''){
            $map['type'] = $param['type'];
        }

        $model = M("red_setting");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = $model
            ->where($map)
            ->order("effect_time_end desc, addtime desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        // 如果是导出，则直接return源数据
        if(isset($_GET['action_type']) && $_GET['action_type'] == 'export'){
            return array('count'=>$count, 'list'=>$list);
        }

        $vip_grade_list = getVipGradeList($tenant_id);
        $red_type_list = $this->red_type_list;
        foreach ($list as $key=>$val){
            $list[$key]['effect_time_start'] = $val['effect_time_start'] ? date('Y-m-d H:i:s', $val['effect_time_start']) : '';
            $list[$key]['effect_time_end'] = $val['effect_time_end'] ? date('Y-m-d H:i:s', $val['effect_time_end']) : '';
            $list[$key]['vip_conf'] = json_decode($val['vip_conf'], true);
            foreach ($list[$key]['vip_conf'] as $k=>$v){
                $vip_grade = trim($k,'vip_grade_');
                $vip_grade_info = $vip_grade_list[$vip_grade];
                $list[$key]['vip_conf'][$k]['vip_grade_name'] = $vip_grade_info['name'];
            }
            $list[$key]['type_name'] = isset($red_type_list[$val['type']]) ? $red_type_list[$val['type']] : $val['type'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('auth_access_json', json_encode(getAuthAccessList(getRoleId())));
        $this->assign('vip_grade_list',$vip_grade_list);
        $this->assign('type_list',$red_type_list);
        $this->display();
    }

    /*
     * 红包-新增
     * */
    public function red_add(){
        $param = I('param.');
        if(IS_POST){
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            if(!$tenant_id){
                $this->error('请选择租户');
            }
            if(!$param['name']){
                $this->error('红包名称 不能为空');
            }
            if(empty($param['red_time'])){
                $this->error('请选择红包推送时间点');
            }
            if(!($param['second_time'] >= 0)){
                $this->error('开抢分钟设置 必须大于等于0');
            }
            if(!($param['effect_time'] > 0)){
                $this->error('红包有效时长 必须大于0');
            }
            if(!($param['win_time'] > 0)){
                $this->error('可抢到红包时长 必须大于0');
            }
            if($param['win_time'] > $param['effect_time']){
                $this->error("可抢到红包时长不能大于红包有效时长");
            }
            if($param['effect_time'] + $param['second_time'] > 60){
                $this->error("分钟数加上效果时间不能大于60分钟");
            }

            if(!($param['red_total'] >= 0)){
                $this->error('普通用户，红包总金额 必须大于等于0');
            }
            if(!($param['red_num'] >= 0)){
                $this->error('普通用户，红包个数 必须大于等于0');
            }
            if(!($param['money_min'] >= 0)){
                $this->error('普通用户，单个红包最小金额 必须大于等于0');
            }
            if(!($param['money_max'] >= 0)){
                $this->error('普通用户，单个红包最大金额 必须大于等于0');
            }

            if($param['money_min'] * $param['red_num'] > $param['red_total']){
                $this->error("普通用户，单个红包最小金额乘以个数，不能大于红包总额");
            }
            if($param['money_min'] > $param['money_max']){
                $this->error("普通用户，单个红包最小金最小金额不能大于最大金额");
            }
            if($param['money_max'] > $param['red_total']){
                $this->error("普通用户，单个红包最大金额，不能大于于红包总额");
            }
            if($param['money_max'] * $param['red_num'] < $param['red_total']){
                $this->error("普通用户，单个红包最大金额乘以个数，不能小于红包总额");
            }
            if($param['multiple'] <= 0 || $param['multiple'] > 9999){
                $this->error("倍数错误，请输入正确范围：1 - 9999");
            }

            $effect_time_start = strtotime($param['effect_time_start']);
            $effect_time_end = strtotime($param['effect_time_end']);
            if($effect_time_start > $effect_time_end){
                $this->error('生效时间 不能大于 结束时间');
            }

            $tenantInfo = getTenantInfo($tenant_id);

            if(M('red_setting')->where(['tenant_id'=>intval($tenant_id),'name'=>$param['name']])->find()){
                $this->error('已存在该名称，请重新输入');
            }

            $vip_grade_list = getVipGradeList($tenant_id);
            foreach ($param['vip_conf'] as $key=>$val){
                $vip_grade = trim($key,'vip_grade_');
                $vip_grade_info = $vip_grade_list[$vip_grade];
                if(!($val['red_num'] >= 0)){
                    $this->error($vip_grade_info['name'].'，红包个数 必须大于等于0');
                }
                if(!($val['money_min'] >= 0)){
                    $this->error($vip_grade_info['name'].'，单个红包最小金额 必须大于等于0');
                }
                if(!($val['money_max'] >= 0)){
                    $this->error($vip_grade_info['name'].'，单个红包最大金额 必须大于等于0');
                }
                if($val['money_min'] * $val['red_num'] > $val['red_total']){
                    $this->error($vip_grade_info['name'].'，单个红包最小金额乘以个数，不能大于红包总额');
                }
                if($val['money_min'] > $val['money_max']){
                    $this->error($vip_grade_info['name'].'，单个红包最小金最小金额不能大于最大金额');
                }
                if($val['money_max'] > $val['red_total']){
                    $this->error($vip_grade_info['name'].'，单个红包最大金额，不能大于于红包总额');
                }
                if($val['money_max'] * $val['red_num'] < $val['red_total']){
                    $this->error($vip_grade_info['name'].'，单个红包最大金额乘以个数，不能小于红包总额');
                }
            }

            try{
                $uids = array();
                foreach (explode(',', $param['uids']) as $key=>$val){
                    $uid = intval(trim($val));
                    if($uid){
                        array_push($uids, $uid);
                    }
                }
                $user_list = count($uids)>0 ? M('users')->field('id')->where(['id'=>['in', $uids]])->select() : [];
                $user_ids = count($user_list)>0 ? array_column($user_list, 'id', null) : [];

                $data = array(
                    'name' => trim($param['name']),
                    'red_time' => implode(',', $param['red_time']),
                    'last_time' => 0,
                    'money_min' => intval($param['money_min']),
                    'money_max' => intval($param['money_max']),
                    'red_total' => intval($param['red_total']),
                    'red_num' => intval($param['red_num']),
                    'addtime' => time(),
                    'tenant_id' => intval($tenant_id),
                    'game_tenant_id' => $tenantInfo['game_tenant_id'],
                    'second_time' => intval($param['second_time']),
                    'effect_time' => intval($param['effect_time']),
                    'win_time' => intval($param['win_time']),
                    'operated_by' => get_current_admin_user_login(),
                    'uids' => implode(',', $user_ids),
                    'multiple' => intval($param['multiple']),
                    'vip_conf' => json_encode($param['vip_conf']),
                    'effect_time_start' => $effect_time_start,
                    'effect_time_end' => $effect_time_end,
                    'type' => intval($param['type']),
                );

                M('red_setting')->add($data);
            }catch (\Exception $e){
                setAdminLog('【新增红包】失败：'.$e->getMessage());
                $this->error('操作失败');
            }
            sleep(1); // 延迟1秒，防止由于从库还没来得及更新数据
            $list = M('red_setting')->where(['tenant_id'=>$tenant_id, 'effect_time_end'=>['gt',time()]])->select();
            $this->resetcaches($list, $tenant_id);
            setAdminLog('【新增红包】成功-'.json_encode($param, JSON_UNESCAPED_UNICODE));
            $this->success('操作成功', U('red_list',array('tenant_id'=>$tenant_id)));
        }

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('red_time_list', $this->red_time_list);
        $this->assign('tenant_id',$tenant_id);
        $this->assign('vip_grade_list', getVipGradeList($tenant_id));
        $this->assign('type_list',$this->red_type_list);
        $this->display();
    }

    /*
     * 红包-编辑
     * */
    public function red_edit(){
        $param = I('param.');

        if(IS_POST){
            if(!isset($param['id']) || !$param['id']){
                $this->error('参数错误');
            }
            if(!$param['name']){
                $this->error('红包名称 不能为空');
            }
            if(empty($param['red_time'])){
                $this->error('请选择红包推送时间点');
            }
            if(!($param['second_time'] >= 0)){
                $this->error('开抢分钟设置 必须大于等于0');
            }
            if(!($param['effect_time'] > 0)){
                $this->error('红包有效时长 必须大于0');
            }
            if(!($param['win_time'] > 0)){
                $this->error('可抢到红包时长 必须大于0');
            }
            if($param['win_time'] > $param['effect_time']){
                $this->error("可抢到红包时长不能大于红包有效时长");
            }
            if($param['effect_time'] + $param['second_time'] > 60){
                $this->error("分钟数加上效果时间不能大于60分钟");
            }

            if(!($param['red_total'] >= 0)){
                $this->error('普通用户，红包总金额 必须大于等于0');
            }
            if(!($param['red_num'] >= 0)){
                $this->error('普通用户，红包个数 必须大于等于0');
            }
            if(!($param['money_min'] >= 0)){
                $this->error('普通用户，单个红包最小金额 必须大于等于0');
            }
            if(!($param['money_max'] >= 0)){
                $this->error('普通用户，单个红包最大金额 必须大于等于0');
            }

            if($param['money_min'] * $param['red_num'] > $param['red_total']){
                $this->error("普通用户，单个红包最小金额乘以个数，不能大于红包总额");
            }
            if($param['money_min'] > $param['money_max']){
                $this->error("普通用户，单个红包最小金最小金额不能大于最大金额");
            }
            if($param['money_max'] > $param['red_total']){
                $this->error("普通用户，单个红包最大金额，不能大于于红包总额");
            }
            if($param['money_max'] * $param['red_num'] < $param['red_total']){
                $this->error("普通用户，单个红包最大金额乘以个数，不能小于红包总额");
            }
            if($param['multiple'] <= 0 || $param['multiple'] > 9999){
                $this->error("倍数错误，请输入正确范围：1 - 9999");
            }

            $effect_time_start = strtotime($param['effect_time_start']);
            $effect_time_end = strtotime($param['effect_time_end']);
            if($effect_time_start > $effect_time_end){
                $this->error('生效时间 不能大于 结束时间');
            }

            $info = M('red_setting')->where(['id'=>intval($param['id'])])->find();

            if(M('red_setting')->where(['tenant_id'=>$info['tenant_id'],'id'=>['neq',intval($param['id'])],'name'=>$param['name']])->find()){
                $this->error('已存在该名称，请重新输入');
            }

            $vip_grade_list = getVipGradeList($info['tenant_id']);
            foreach ($param['vip_conf'] as $key=>$val){
                $vip_grade = trim($key,'vip_grade_');
                $vip_grade_info = $vip_grade_list[$vip_grade];
                if(!($val['red_num'] >= 0)){
                    $this->error($vip_grade_info['name'].'，红包个数 必须大于等于0');
                }
                if(!($val['money_min'] >= 0)){
                    $this->error($vip_grade_info['name'].'，单个红包最小金额 必须大于等于0');
                }
                if(!($val['money_max'] >= 0)){
                    $this->error($vip_grade_info['name'].'，单个红包最大金额 必须大于等于0');
                }
                if($val['money_min'] * $val['red_num'] > $val['red_total']){
                    $this->error($vip_grade_info['name'].'，单个红包最小金额乘以个数，不能大于红包总额');
                }
                if($val['money_min'] > $val['money_max']){
                    $this->error($vip_grade_info['name'].'，单个红包最小金最小金额不能大于最大金额');
                }
                if($val['money_max'] > $val['red_total']){
                    $this->error($vip_grade_info['name'].'，单个红包最大金额，不能大于于红包总额');
                }
                if($val['money_max'] * $val['red_num'] < $val['red_total']){
                    $this->error($vip_grade_info['name'].'，单个红包最大金额乘以个数，不能小于红包总额');
                }
            }

            try{
                $uids = array();
                foreach (explode(',', $param['uids']) as $key=>$val){
                    $uid = intval(trim($val));
                    if($uid){
                        array_push($uids, $uid);
                    }
                }
                $user_list = count($uids)>0 ? M('users')->field('id')->where(['id'=>['in', $uids]])->select() : [];
                $user_ids = count($user_list)>0 ? array_column($user_list, 'id', null) : [];

                $data = array(
                    'name' => trim($param['name']),
                    'red_time' => implode(',', $param['red_time']),
                    'money_min' => intval($param['money_min']),
                    'money_max' => intval($param['money_max']),
                    'red_total' => intval($param['red_total']),
                    'red_num' => intval($param['red_num']),
                    'update_time' => time(),
                    'second_time' => intval($param['second_time']),
                    'effect_time' => intval($param['effect_time']),
                    'win_time' => intval($param['win_time']),
                    'operated_by' => get_current_admin_user_login(),
                    'uids' => implode(',', $user_ids),
                    'multiple' => intval($param['multiple']),
                    'vip_conf' => json_encode($param['vip_conf']),
                    'effect_time_start' => $effect_time_start,
                    'effect_time_end' => $effect_time_end,
                    'type' => intval($param['type']),
                );

                M('red_setting')->where(['id'=>intval($param['id'])])->save($data);
            }catch (\Exception $e){
                setAdminLog('【编辑红包】失败：'.$e->getMessage());
                $this->error('操作失败');
            }
            sleep(1); // 延迟1秒，防止由于从库还没来得及更新数据
            $list = M('red_setting')->where(['tenant_id'=>$info['tenant_id'], 'effect_time_end'=>['gt',time()]])->select();
            $this->resetcaches($list, $info['tenant_id']);
            setAdminLog('【编辑包】成功-'.json_encode($param, JSON_UNESCAPED_UNICODE));
            $this->success('操作成功', U('red_list',array('tenant_id'=>$info['tenant_id'])));
        }

        $id = I('id');
        if(!$id){
            $this->error('参数错误');
        }
        $info = M('red_setting')->where(['id'=>intval($id)])->find();
        $info['red_time_checked'] = explode(',',$info['red_time']);
        $info['vip_grade_list'] = explode(',', $info['vip_grade']);
        $info['vip_conf'] = json_decode($info['vip_conf'], true);
        $vip_grade_list = getVipGradeList($info['tenant_id']);
        $info['effect_time_start'] = $info['effect_time_start'] ? date('Y-m-d H:i:s', $info['effect_time_start']) : '';
        $info['effect_time_end'] = $info['effect_time_end'] ? date('Y-m-d H:i:s', $info['effect_time_end']) : '';

        foreach ($vip_grade_list as $key=>$val){
            if(isset($info['vip_conf']['vip_grade_'.$val['vip_grade']])){
                $vip_grade_list[$key]['vip_conf'] = $info['vip_conf']['vip_grade_'.$val['vip_grade']];
            }else{
                $vip_grade_list[$key]['vip_conf'] = array(
                    'red_total' => 0,
                    'red_num' => 0,
                    'money_min' => 0,
                    'money_max' => 0,
                    'multiple' => 1,
                );
            }
        }

        $this->assign('info',$info);
        $this->assign('red_time_list', $this->red_time_list);
        $this->assign('vip_grade_list', $vip_grade_list);
        $this->assign('type_list',$this->red_type_list);
        $this->display();
    }

    public function red_del(){
        $param = I('param.');
        $id=intval($_GET['id']);
        if($id){
            $info = M('red_setting')->where(['id'=>intval($id)])->find();
            $info['red_time_checked'] = explode(',',$info['red_time']);
            if($info['effect_time_start'] >= time() && time() <= $info['effect_time_start'] && in_array(date('H'), $info['red_time_checked'])){
                $this->error('红包正在运行，不能删除');
            }

            try{
                M('red_setting')->where(['id'=>intval($id)])->delete();
            }catch (\Exception $e){
                setAdminLog('【删除红包】失败：'.$e->getMessage());
                $this->error('操作失败');
            }
            setAdminLog('【删除红包】成功'.json_encode($param));
            sleep(1); // 延迟1秒，防止由于从库还没来得及更新数据
            $list = M('red_setting')->where(['tenant_id'=>$info['tenant_id'], 'effect_time_end'=>['gt',time()]])->select();
            $this->resetcaches($list, $info['tenant_id']);
            $this->success('操作成功',U('red_list',array('tenant_id'=>$info['tenant_id'])));
        }else{
            $this->error('参数错误');
        }
    }

    public function resetcaches($list,$tenant_id){
        if(empty($list)){
            delcache('red_setting_list_'.$tenant_id);
            return;
        }
        setcaches('red_setting_list_'.$tenant_id,$list);
        getRedinfo($list);
    }

    public function red_recordalone(){
        $param = I('param.');
        $map=array();
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $map['tenant_id'] = intval($tenant_id);

        $param['tenant_id'] = $tenant_id;
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid']=$param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login'] != ''){
            $map['user_login']=$param['user_login'];
        }

        $model = M("red_record_detail");
        $count = $model->where($map)->count();
        $page = $this->page($count);
        $lists = $model
            ->where($map)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach($lists as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $lists[$key]['userinfo'] = $userinfo;
            $lists[$key]['after_balance'] = floatval($val['after_balance']);
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign('type', $this->type);
        $this->assign('type_grant', $this->type_grant);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->assign('tenant_list',getTenantList());
        $this->display();
    }

    public function shopping_voucher_list(){
        $param = I('param.');
        $map=array();
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $map['tenant_id'] = intval($tenant_id);

        $param['tenant_id'] = $tenant_id;
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        if(isset($param['status']) && $param['status'] != '-1'){
            $map['status'] = $param['status'];
        }

        if(isset($param['id']) && $param['id'] != ''){
            $map['id']=$param['id'];
        }
        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid']=$param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login'] != ''){
            $map['user_login']=$param['user_login'];
        }

        $model = M("shopping_voucher");
        $count = $model->where($map)->count();
        $page = $this->page($count);
        $list = $model
            ->where($map)
            ->order("id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $shopping_voucher_status_list = $this->shopping_voucher_status_list;
        foreach($list as $key=>$val){
            $list[$key]['status_name'] = '<span style="color: '.$shopping_voucher_status_list[$val['status']]['color'].';">'.$shopping_voucher_status_list[$val['status']]['name'].'</span>';
            $list[$key]['update_time_date'] = $val['status'] == 2 && $val['update_time'] ? date('Y-m-d H:i:s', $val['update_time']) : '-';
            $list[$key]['amount'] = floatval($val['amount']);
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('list', $list);
        $this->assign('type', $this->type);
        $this->assign('type_grant', $this->type_grant);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param', $param);
        $this->assign('user_type_list', user_type_list());
        $this->assign('status_list', $shopping_voucher_status_list);
        $this->assign('tenant_list', getTenantList());
        $this->display();
    }

}
