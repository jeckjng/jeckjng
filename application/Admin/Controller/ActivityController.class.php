<?php

/**
 * VIP管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ActivityController extends AdminbaseController {

    public function type_list(){
        return array('1'=>'首充活动','2'=>'分享活动');
    }

    /*
   * 首充活动
   * */
    function first_recharge(){
        if(IS_POST){
            $param = I('post.');

            $list = M('activity_config')->where(['type'=>1,'tenant_id'=>getTenantIds()])->field('id')->select();
            $ids_arr = array_column($list,null,'id');
            $max = 0;
            foreach ($param['data'] as $key=>$val){
                if($val['min']<=0){
                    $this->error('【首充奖励'.$val['sort_num'].'】最小值必须大于0');
                }
                if($val['min']>=$val['max']){
                    $this->error('【首充奖励'.$val['sort_num'].'】最大值必须大于最小值');
                }
                if($val['min']<=$max){
                    $this->error('【首充奖励'.$val['sort_num'].'】的最小值必须大于【首充奖励'.($val['sort_num']-1).'】的最小值');
                }

                if($val['reward']<0 || $val['reward']>999999){
                    $this->error('【首充奖励'.$val['sort_num'].'】奖励金额输入值错误，输入范围：0 - 999999');
                }
                if($val['watnum']<0 || $val['watnum']>999999){
                    $this->error('【首充奖励'.$val['sort_num'].'】赠送观影次数输入值错误，输入范围：0 - 999999');
                }
                if($val['wattime']<0 || $val['wattime']>999999){
                    $this->error('【首充奖励'.$val['sort_num'].'】赠送观影时长输入值错误，输入范围：0 - 999999');
                }

                if($val['reward']<=0 && $val['watnum']<=0 && $val['wattime']<=0){
                    $this->error('【首充奖励'.$val['sort_num'].'】奖励金额、赠送观影次数、赠送观影时长，必须有一个大于0');
                }

                $max = $val['max'];

                $val['sort_num'] = intval($val['sort_num']);
                $val['min'] = floatval($val['min']);
                $val['max'] = floatval($val['max']);
                $val['reward'] = floatval($val['reward']);
                $val['watnum'] = intval($val['watnum']);
                $val['wattime'] = intval($val['wattime']);
                $val['act_uid'] = intval($_SESSION["ADMIN_ID"]);
                $val['mtime'] = time();
                $val['tenant_id']= getTenantIds();
                if(isset($val['id'])){
                    M('activity_config')->where(['type'=>1,'id'=>intval($val['id'])])->save($val);
                    unset($ids_arr[$val['id']]);
                }else{
                    $val['type'] = 1;
                    M('activity_config')->add($val);
                }
            }

            $ids = array_keys($ids_arr);
            if(count($ids) > 0){
                M('activity_config')->where(['type'=>1,'id'=>['in',$ids],'tenant_id'=>getTenantIds()])->delete();
            }
            M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->save(['first_charge_award_amount_type'=>$param['first_charge_award_amount_type']]);
            delcache(getTenantIds().'_'.'getTenantConfig');
            delcache(getTenantIds().'_'."getPlatformConfig");
            $this->success('操作成功');
        }

        $list = M('activity_config')->where(['type'=>1,'tenant_id'=>getTenantIds() ])->order('sort_num asc')->select();
        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
        $this->assign('list', $list);
        $this->assign('config', $config);
        $this->display();
    }

    /*
   * 分享活动
   * */
    function share(){
        if(IS_POST){
            $param = I('post.');

            if($param['recom_frmin']<=0){
                $this->error('推荐奖励首充最低充值金额 必须大于0');
            }

            $per_num_arr = array_unique(array_keys(array_column($param['data'],null,'per_num')));
            if(count($per_num_arr)<7){
                $this->error('推荐首充人数不能重复');
            }

            foreach ($param['data'] as $key=>$val){
                if($val['per_num']<=0){
                    $this->error('【推荐首充'.$val['per_num'].'人】必须大于0');
                }
                if($val['reward']<0 || $val['reward']>999999){
                    $this->error('【推荐首充'.$val['per_num'].'人】奖励金额输入值错误，输入范围：0 - 999999');
                }
                if($val['watnum']<0 || $val['watnum']>999999){
                    $this->error('【推荐首充'.$val['per_num'].'人】赠送观影次数输入值错误，输入范围：0 - 999999');
                }
                if($val['wattime']<0 || $val['wattime']>999999){
                    $this->error('【推荐首充'.$val['per_num'].'人】赠送观影时长输入值错误，输入范围：0 - 999999');
                }

                $val['recom_frmin'] = floatval($param['recom_frmin']);
                $val['per_num'] = floatval($val['per_num']);
                $val['reward'] = floatval($val['reward']);
                $val['watnum'] = intval($val['watnum']);
                $val['wattime'] = intval($val['wattime']);
                $val['is_over'] = intval($val['is_over']);
                $val['act_uid'] = intval($_SESSION["ADMIN_ID"]);
                $val['mtime'] = time();

                if($val['id']){
                    M('activity_config')->where(['type'=>2,'id'=>intval($val['id'])])->save($val);
                }else{
                    $val['type'] = 2;
                    M('activity_config')->add($val);
                }
            }
            M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->save(['share_award_amount_type'=>$param['share_award_amount_type']]);
            delcache(getTenantIds().'_'.'getTenantConfig');
            delcache(getTenantIds().'_'."getPlatformConfig");

            $this->success('操作成功');
        }

        $list = M('activity_config')->where(['type'=>2,'tenant_id'=>getTenantIds()])->order('is_over asc,per_num asc')->select();

        if(count($list) < 7){
            $num = 7-count($list);
            for($i=1; $i <= $num; $i++){
                array_push($list,[]);
            }
        }
        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
        $this->assign('list', $list);
        $this->assign('config', $config);
        $this->display();
    }

    /*
     * 活动奖励明细
     * */
    function reward_log(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = array();

        if(I('uid')){
            $map['uid'] = I('uid');
        }
        if(I('user_login')){
            $map['user_login'] = $param['user_login'];
        }
        if (I('type')) {
            $map['type'] = I('type');
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        $tenantId=getTenantIds();
        $role_id=$_SESSION['role_id'];
        if($role_id==1){

        }else{
            $map['tenant_id'] = $tenantId;
        }

        $model = M("activity_reward_log");
        $count = $model->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $model->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        $type_list = $this->type_list();
        foreach($lists as $key=>$val){
            $userinfo = getUserInfo($val['uid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($val['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$val['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $lists[$key]['status'] = $val['status']==1 ? '生效' : '失效';
            $lists[$key]['type'] = $type_list[$val['type']];
        }

        $this->assign('user_type_list',user_type_list());
        $this->assign('type_list', $type_list);
        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    function recharge_gift(){

         $type_list = array(
            "0"=>"普通礼物",
            "1"=>"豪华礼物",
            "2"=>"守护礼物"
        );
         $mark_list = array(
            "0"=>"普通",
            "1"=>"热门",
            "2"=>"守护"
        );

        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $page_size = isset($param['num']) ? $param['num'] : 20;

        $map['tenant_id'] = $tenant_id;

        $Car=M("charge_gift");
        $count=$Car->where($map)->count();
        $page = $this->page($count, $page_size);
        $lists = $Car
            ->where($map)
            ->order("orderno asc, id desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $key = 'getGiftList_'.$tenant_id;
        $giftlist = getcache($key);
        $nobelist = getNobleList($tenant_id);
        $carlist = get_carlist($tenant_id);
        foreach ($lists as $key=>$val){
            foreach ($giftlist as $key1 => $val1){
                if($val['gift_id'] == $val1['id']){
                    $lists[$key]['giftname'] = $val1['giftname'];
                    $lists[$key]['mark'] = $mark_list[$val1['mark']];
                    $lists[$key]['gifttype'] = $type_list[$val1['type']] ;
                }
            }
            foreach ($nobelist as $key2 => $val2){
                if($val['nobel_id'] == $val2['id']){
                    $lists[$key]['nobel_name'] = $val2['name'];
                }
            }
            foreach ($carlist as $key3 => $val3){
                if($val['car_id'] == $val3['id']){
                    $lists[$key]['car_name'] = $val3['name'];
                }
            }
        }

        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);

        $this->display();
    }
    /*
     * 添加首充送豪礼
     */
    function add_recharge_gift(){

        $param = I('param.');
        $tenantId = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $key = 'getGiftList_'.$tenantId;
        $giftlist = getcache($key);

        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('giftlist',$giftlist);
        $this->assign('car_list', get_carlist($tenantId));
        $this->assign('getNobleList', getNobleList($tenantId));

        $this->assign('tenant_id',$tenantId);
        $this->display();
    }
    function add_recharge_post(){
        if(IS_POST){
            $param = I('post.');

            $price=$_POST['price'];
            if($price==""){
                $this->error("请填写价格范围");
            }
            $gift_num=$_POST['gift_num'];
            if($gift_num=="") {
                $this->error("请填写礼物赠送个数");
            }
            $car_num=$_POST['car_num'];
            if($car_num==""){
                $this->error("请填写坐骑体验天数");
            }

            $nobel_days=$_POST['nobel_days'];
            if($nobel_days==""){
                    $this->error("请填写贵族体验天数");
            }

            $is_open=$_POST['is_open'];
            if($is_open==""){
                $this->error("请选择是否开启");
            }

            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            if(M('charge_gift')->where(['tenant_id'=>$tenant_id,'price'=>$param['price']])->find()){
                $this->error('已存在该充值范围，请重新输入');
            }

            $Car=M("charge_gift");
            $Car->create();
            $Car->tenant_id = $tenant_id;
            $Car->act_uid = get_current_admin_id();
            $Car->addtime=time();
            $type=$_POST['type'];
            if($type==""){
                $Car->type=0;
            }
            $result=$Car->add();
            if($result!==false){
                $action="添加首充送豪礼：{$result}";
                setAdminLog($action);
                delCarlistCache($tenant_id);


                $this->success('添加成功', U('recharge_gift',array('tenant_id'=>$tenant_id)));
            }else{
                $this->error('添加失败');
            }
        }
    }
    function del(){
        $id=intval($_GET['id']);
        if($id){
            $result=M("charge_gift")->delete($id);
            if($result){
                $action="删除首充送豪礼：{$id}";
                setAdminLog($action);
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('数据传入失败！');
        }
        $this->display();
    }
    /*
       * 编辑首充送豪礼
       */
    function edit_recharge_gift(){
        $param = I('param.');
        $list = M('charge_gift')->where(['id'=>$param['id']])->find();
        $tenantId = isset($param['tenant_id']) ? $param['tenant_id'] : $list['tenant_id'];
        $key = 'getGiftList_'.$tenantId;
        $giftlist = getcache($key);
        $this->assign('list',$list);
        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('giftlist',$giftlist);
        $this->assign('car_list', get_carlist($tenantId));
        $this->assign('getNobleList', getNobleList($tenantId));
        $this->assign('tenant_id',$tenantId);
        $this->display();
    }
    function edit_recharge_post(){
        if(IS_POST){
            $param = I('post.');
            $id = $param['id'];
        /*    var_dump($id);
            var_dump($param);exit;*/
            $price=$_POST['price'];
            if($price==""){
                $this->error("请填写价格范围");
            }
            $gift_num=$_POST['gift_num'];
            if($gift_num=="") {
                $this->error("请填写礼物赠送个数");
            }
            $car_num=$_POST['car_num'];
            if($car_num==""){
                $this->error("请填写坐骑体验天数");
            }
            $nobel_days=$_POST['nobel_days'];
            if($nobel_days==""){
                $this->error("请填写贵族体验天数");
            }

            $is_open=$_POST['is_open'];
            if($is_open==""){
                $this->error("请选择是否开启");
            }
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            if(M('charge_gift')->where(['tenant_id'=>$tenant_id,'price'=>$param['price'],'id != '.$param['id']])->find()){
                $this->error('已存在该充值范围，请重新输入');
            }
            $Car=M("charge_gift");
            $Car->create();
            $Car->tenant_id = $tenant_id;
            $Car->act_uid = get_current_admin_id();
            $Car->addtime=time();
            $type=$_POST['type'];
            if($type==""){
                $Car->type=0;
            }
            $result=$Car->save();
            if($result!==false){
                $action="编辑首充送豪礼：{$result}";
                setAdminLog($action);
                delCarlistCache($tenant_id);
                $this->success('编辑成功', U('recharge_gift',array('tenant_id'=>$tenant_id)));
            }else{
                $this->error('编辑失败');
            }
        }
    }
    //排序
    public function listorders() {

        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("charge_gift")->where(array('id' => $key))->save($data);
        }
        $status = true;
        if ($status) {
            $action="更新首充礼物排序";
            setAdminLog($action);
            delPatternCacheKeys('carlist_*');
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    public function recommendFriend(){
        $list = M('activity_config')->where(['type'=>3,'tenant_id'=>getTenantIds()])->order('sort_num asc')->select();


        if(IS_POST){
            $param = I('post.');


            $ids_arr = array_column($list,null,'id');

            foreach ($param['data'] as $key=>$val){
                if($val['per_num']<=0){
                    $this->error('【邀请用户数'.$val['sort_num'].'】最小值必须大于0');
                }

                if($val['reward']<0 || $val['reward']>999999){
                    $this->error('【首充奖励'.$val['sort_num'].'】奖励金额输入值错误，输入范围：0 - 999999');
                }

                $val['sort_num'] = intval($val['sort_num']);
                $val['per_num'] = floatval($val['per_num']);
                $val['reward'] = floatval($val['reward']);
                $val['act_uid'] = intval($_SESSION["ADMIN_ID"]);
                $val['mtime'] = time();
                $val['tenant_id']= getTenantIds();
                if(isset($val['id'])){
                    M('activity_config')->where(['type'=>3,'id'=>intval($val['id'])])->save($val);
                    unset($ids_arr[$val['id']]);
                }else{
                    $val['type'] = 3;
                    M('activity_config')->add($val);
                }
            }

            delcache(getTenantIds().'_'.'getTenantConfig');
            delcache(getTenantIds().'_'."getPlatformConfig");
            $this->success('操作成功');
        }
        foreach ($list as $key => $value){
            $list[$key]['min'] = floatval($value['min']);
        }
        $this->assign('list', $list);
        $this->display();
    }

    public  function consumptionAward(){
        $list = M('activity_config')->where(['type'=>4,'tenant_id'=>getTenantIds()])->order('sort_num asc')->select();

        if(IS_POST){
            $param = I('post.');
            $ids_arr = array_column($list,null,'id');
            $max = 0;
            foreach ($param['data'] as $key=>$val){
                if($val['min']<=0){
                    $this->error('【首充奖励'.$val['sort_num'].'】最小值必须大于0');
                }
                if($val['min']>=$val['max']){
                    $this->error('【首充奖励'.$val['sort_num'].'】最大值必须大于最小值');
                }
                if($val['min']<=$max){
                    $this->error('【首充奖励'.$val['sort_num'].'】的最小值必须大于【首充奖励'.($val['sort_num']-1).'】的最小值');
                }

                if($val['reward']<0 || $val['reward']>999999){
                    $this->error('【首充奖励'.$val['sort_num'].'】奖励金额输入值错误，输入范围：0 - 999999');
                }


                $max = $val['max'];
                $val['sort_num'] = intval($val['sort_num']);
                $val['min'] = floatval($val['min']);
                $val['max'] = floatval($val['max']);
                $val['reward'] = floatval($val['reward']);
                $val['act_uid'] = intval($_SESSION["ADMIN_ID"]);
                $val['mtime'] = time();
                $val['tenant_id']= getTenantIds();
                if(isset($val['id'])){
                    M('activity_config')->where(['id'=>intval($val['id'])])->save($val);
                    unset($ids_arr[$val['id']]);
                }else{
                    $val['type'] = 4;
                    M('activity_config')->add($val);
                }
            }

            $ids = array_keys($ids_arr);
            if(count($ids) > 0){
                M('activity_config')->where(['type'=>4,'id'=>['in',$ids],'tenant_id'=>getTenantIds()])->delete();
            }
            $this->success('操作成功');
        }

        $this->assign('list', $list);
        $this->display();

    }

    public  function signSet(){
        $list = M('sign_set')->where('tenant_id="'.getTenantIds().'"')->find();
        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
        if(IS_POST){
            $setConfig = [];
            $param = I('post.');
            $setConfig['sing_award_type'] =$param['sing_award_type'];
            $setConfig['sing_is_show'] =$param['sing_is_show'];
            M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->save($setConfig);
            delcache(getTenantIds().'_'.'getTenantConfig');
            delcache(getTenantIds().'_'."getPlatformConfig");

            $data['first_times'] = $param['first_times'];
            $data['second_times'] = $param['second_times'];
            $data['third_times'] = $param['third_times'];
            $data['fourth_times'] = $param['fourth_times'];
            $data['fifth_times'] = $param['fifth_times'];
            $data['sixth_times'] = $param['sixth_times'];
            $data['seventh_times'] = $param['seventh_times'];
            $data['type'] = $param['sing_award_type'];
            $data['tenant_id'] = getTenantIds();
            if ($list){
                M('sign_set')->where(['id'=>$list['id'] ])->save($data);
            }else{
                M('sign_set')->add($data);
            }

            $action="修改签到奖励：".json_encode($param);
            setAdminLog($action);
            $this->success('操作成功');
        }
        $this->assign('info', $list);
        $this->assign('config',$config);
        $this->display();
    }
}
