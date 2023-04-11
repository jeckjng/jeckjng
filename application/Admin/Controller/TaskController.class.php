<?php

/**
 * VIP管理
 */
namespace Admin\Controller;
use Admin\Model\UsersModel;
use Common\Controller\AdminbaseController;
class TaskController extends AdminbaseController {

    public function type_list(){
        return array('1'=>'初级','2'=>'中级','3'=>'高级');
    }

    public function client_list(){
        return array('0'=>'全部','1'=>'PC','2'=>'H5','3'=>'Android','4'=>'iOS');
    }

    public function classification_list(){
        $classification_list = M("task_classification")->field('id,name,type')->order('id asc')->select();
        $classification_list = array_column($classification_list,null,'id');
        return $classification_list;
    }

    public function user_task_status_list(){
        return array('0'=>'已取消','1'=>'进行中','2'=>'审核中','3'=>'审核通过','4'=>'审核拒绝');
    }

    /*
     * 用户行为查询
     * */
    function index(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = array();

        if(I('name')){
            $map['name'] = I('name');
        }
        if(I('start_time')){
            $map['start_time'] = ['egt',strtotime(I('start_time'))];
        }
        if(I('end_time')){
            $map['end_time'] = ['elt',strtotime(I('end_time'). " 23:59:59")];
        }
        if (I('type')) {
            $map['type'] = I('type');
        }
        if (I('classification')) {
            $map['classification'] = I('classification');
        }
        if(I('status')!=''){
            $map['status'] = I('status');
        }else{
            $map['status'] = 1;
            $_GET['status'] = 1;
            $param['status'] = 1;
        }

        $task=M("task");
        $count=$task->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $task->where($map)->order("sort asc, id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        $type_list = $this->type_list();
        $client_list = $this->client_list();
        $classification_list = $this->classification_list();

        foreach($lists as $key=>$val){
            $lists[$key]['act_userinfo']=getUserInfo($val['act_uid']);
            $lists[$key]['status'] = $val['status']==1 ? '生效' : '失效';
            $client = explode(',',$val['client']);
            $lists[$key]['client'] = '';
            foreach($client as $k=>$v){
                if(isset($client_list[$v])){
                    $lists[$key]['client'] .= $client_list[$v].' | ';
                }
            }
            $lists[$key]['client'] = trim($lists[$key]['client'],' | ');
            $lists[$key]['reward2_upgrade_vip'] = $val['reward2_upgrade_vip']==1 ? '是' : '否';
            $lists[$key]['is_manual_check'] = $val['is_manual_check']==1 ? '是' : '否';
            $lists[$key]['is_upleveltask'] = $val['is_upleveltask']==1 ? '是' : '否';
            if(isset($classification_list[$val['classification']])){
                $lists[$key]['classification'] = $classification_list[$val['classification']]['name'];
                $lists[$key]['type'] = $classification_list[$val['classification']]['type'];
                $lists[$key]['type'] = $type_list[$val['type']];
            }
        }

        $this->assign('type_list', $type_list);
        $this->assign('lists', $lists);
        $this->assign('classification_list', $classification_list);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
    * 新增任务
    * */
    function add_task(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['client'])){
                $this->error('请选择客户端');
            }
            foreach ($param as $key=>$val){
                if($key == 'client'){
                    $param['client'] = implode(',',$param['client']);
                    $val = $param['client'];
                }
                if($val == null || trim($val) ==''){
                    $this->error('缺少参数');
                }
            }
            if(strtotime($param['start_time']) >= strtotime($param['end_time'])){
                $this->error('失效时间必须大于生效时间');
            }
            if(!isset($param['reward2_upgrade_vip']) || $param['reward2_upgrade_vip']==''){
                $this->error('请选择完成奖励2 是否升级VIP等级');
            }
            if(!isset($param['is_manual_check']) || $param['is_manual_check']==''){
                $this->error('请选择人工审核');
            }
            if(!isset($param['is_upleveltask']) || $param['is_upleveltask']==''){
                $this->error('请选择是否需要上一级任务完成');
            }
            if($param['reward1']<0){
                $this->error('完成奖励1 金额不规范');
            }

            $classification_info = M("task_classification")->where(['id'=>intval($param['classification'])])->find();
            if(!($param['price']>=$classification_info['min_amount'] && $param['price']<=$classification_info['max_amount'])){
                $this->error('任务价格金额不规范，价格区间为：'.$classification_info['min_amount'].' - '.$classification_info['max_amount']);
            }
            if($classification_info['type']==1 && $param['is_upleveltask']==1){
                $this->error('初级任务，不需要上一级任务完成，请选择否');
            }

            $data = array(
                'name' => $param['name'],
                'description' => $param['description'],
                'price' => floatval($param['price']),
                'start_time' => strtotime($param['start_time']),
                'end_time' => strtotime($param['end_time']),
                'client' => $param['client'],
                'type' => intval($classification_info['type']),
                'classification' => intval($param['classification']),
                'sort' => intval($param['sort']),
                'img'  => $param['img'],
                'file' => $param['file'],
                'reward1' => floatval($param['reward1']),
                'reward2_upgrade_vip' => intval($param['reward2_upgrade_vip']),
                'is_manual_check' => intval($param['is_manual_check']),
                'is_upleveltask' => intval($param['is_upleveltask']),
                'task_details_type' => intval($param['task_details_type']),
                'task_details' => $param['task_details'],
                'status' => intval($param['status']),
                'act_uid' => intval($_SESSION["ADMIN_ID"]),
                'mtime' => time(),
                'only_one' => intval($param['only_one']),
            );
           

            M('task')->add($data);
            $this->success('操作成功',U('index'));
        }

        $classification_list = $this->classification_list();

        $this->assign('classification_list', $classification_list);
        $this->display();
    }

    /*
    * 修改任务
    * */
    function edit_task(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['id']) || !$param['id']){
                $this->error('缺少参数');
            }
            if(!isset($param['client'])){
                $this->error('请选择客户端');
            }
            foreach ($param as $key=>$val){
                if($key == 'client'){
                    $param['client'] = implode(',',$param['client']);
                    $val = $param['client'];
                }
                if($val == null || trim($val) ==''){
                    $this->error('缺少参数');
                }
            }
            if(strtotime($param['start_time']) >= strtotime($param['end_time'])){
                $this->error('失效时间必须大于生效时间');
            }
            if(!isset($param['reward2_upgrade_vip']) || $param['reward2_upgrade_vip']==''){
                $this->error('请选择完成奖励2 是否升级VIP等级');
            }
            if(!isset($param['reward2_upgrade_vip']) || $param['reward2_upgrade_vip']==''){
                $this->error('请选择人工审核');
            }
            if(!isset($param['is_upleveltask']) || $param['is_upleveltask']==''){
                $this->error('请选择是否需要上一级任务完成');
            }
            if($param['reward1']<0){
                $this->error('完成奖励1 金额不规范');
            }

            $classification_info = M("task_classification")->where(['id'=>intval($param['classification'])])->find();
            if(!($param['price']>=$classification_info['min_amount'] && $param['price']<=$classification_info['max_amount'])){
                $this->error('任务价格金额不规范，价格区间为：'.$classification_info['min_amount'].' - '.$classification_info['max_amount']);
            }
            if($classification_info['type']==1 && $param['is_upleveltask']==1){
                $this->error('初级任务，不需要上一级任务完成，请选择否');
            }

            $data = array(
                'name' => $param['name'],
                'description' => $param['description'],
                'price' => floatval($param['price']),
                'start_time' => strtotime($param['start_time']),
                'end_time' => strtotime($param['end_time']),
                'client' => $param['client'],
                'type' => intval($classification_info['type']),
                'classification' => intval($param['classification']),
                'sort' => intval($param['sort']),
                'img'  => $param['img'],
                'file' => $param['file'],
                'reward1' => floatval($param['reward1']),
                'reward2_upgrade_vip' => intval($param['reward2_upgrade_vip']),
                'is_manual_check' => intval($param['is_manual_check']),
                'is_upleveltask' => intval($param['is_upleveltask']),
                'task_details_type' => intval($param['task_details_type']),
                'task_details' => $param['task_details'],
                'status' => intval($param['status']),
                'act_uid' => intval($_SESSION["ADMIN_ID"]),
                'mtime' => time(),
                'only_one' => intval($param['only_one']),
            );

            M('task')->where(['id'=>$param['id']])->save($data);
            $this->success('操作成功',U('index'));
        }

        $info = M('task')->where(['id'=>I('id')])->find();
        $info['task_details_htmls'] = htmlspecialchars_decode($info['task_details']);
        $info['client'] = explode(',',$info['client']);
        if(in_array('1',$info['client']) && in_array('2',$info['client']) && in_array('3',$info['client']) && in_array('4',$info['client'])){
            array_push($info['client'],'0');
        }
        $classification_list = $this->classification_list();

        $this->assign('info', $info);
        $this->assign('classification_list', $classification_list);
        $this->display();
    }

    /*
    * 删除任务
    * */
    function del_task(){
        $param = I('param.');
        if($param['id']){
            $is_exist = M('user_task')->where(['task_id'=>intval($param['id']),'status'=>['in','1,2,3']])->select();
            if(is_array($is_exist) && count($is_exist)>0){
                $this->error('该任务有用户已领取，并且状态在（进行中、审核中、审核通过）中，不能删除');
            }

            M('task')->where(['id'=>$param['id']])->delete();
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }

    /*
    * 任务分类
    * */
    function classification(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = array();
        if(I('name')){
            $map['name'] = I('name');
        }
        if (I('type')) {
            $map['type'] = I('type');
        }

        $task_classification=M("task_classification");
        $count=$task_classification->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $task_classification->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        $type_list = $this->type_list();
        foreach($lists as $key=>$val){
            $lists[$key]['act_userinfo'] = getUserInfo($val['act_uid']);
            $lists[$key]['type'] = $type_list[$val['type']];
            $lists[$key]['commission_rate'] = ($val['commission_rate']*100).' %';
            $lists[$key]['status'] = $val['status'] == '1' ? '开启' : '关闭';
            $lists[$key]['experience_shop'] = $val['experience_shop'] == '1' ? '是' : '否';
        }

        $this->assign('type_list', $type_list);
        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
    * 任务分类
    * */
    function get_classification_with_type(){
        if(!I('type')){
            $this->error('type 不能为空');
        }

        $lists = M("task_classification")->field('id,name')->where(['type'=>intval(I('type'))])->order("id desc")->select();

        $this->success($lists);
    }

    /*
     * 删除任务分类
     * */
    function del_classification(){
        $param = I('param.');
        if($param['id']){
            $is_exist = M('user_task')->where(['classification'=>intval($param['id']),'status'=>['in','1,2,3']])->select();
            if(is_array($is_exist) && count($is_exist)>0){
                $this->error('该任务分类有用户已领取，并且状态在（进行中、审核中、审核通过）中，不能删除');
            }

            M('task_classification')->where(['id'=>$param['id']])->delete();
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }

    /*
     * 新增/修改 任务分类
     * */
    function addedit_classification(){
        if(IS_POST){
            $param = I('post.');
            foreach ($param as $key=>$val){
                if($key!='id' && $key!='description' && ($val == null || trim($val) =='')){
                    $this->error('缺少参数');
                }
            }
            if(!isset($param['type']) || $param['type']==''){
                $this->error('请选择分类类别');
            }
            if($param['commission_rate']>1 || $param['commission_rate']<0){
                $this->error('佣金比例不规范，比例范围为：0 - 1');
            }
            if(!($param['unlock_amount']>=0 && $param['unlock_amount']<=999999)){
                $this->error('解锁金额不规范，金额区间为：0 - 999999');
            }
            if(!($param['max_amount']>=0 && $param['max_amount']<=999999)){
                $this->error('订单最高金额不规范，金额区间为：0 - 999999');
            }
            if(!($param['min_amount']>=0 && $param['min_amount']<=999999)){
                $this->error('订单最低金额不规范，金额区间为：0 - 999999');
            }
            if(!($param['limit_max_balance']>=0 && $param['limit_max_balance']<=99999999999999999999)){
                $this->error('订单最低金额不规范，金额区间为：0 - 99999999999999999999');
            }

            $data = array(
                'name' => $param['name'],
                'type' => intval($param['type']),
                'description' => $param['description'],
                'logo' => $param['logo'],
                'bgimg' => $param['bgimg'],
                'commission_rate' => floatval($param['commission_rate']),
                'daily_task' => intval($param['daily_task']),
                'unlock_amount' => floatval($param['unlock_amount']),
                'direct_invitation' => intval($param['direct_invitation']),
                'max_amount'  => floatval($param['max_amount']),
                'min_amount' => floatval($param['min_amount']),
                'limit_max_balance' => floatval($param['limit_max_balance']),
                'space_time' => intval($param['space_time']),
                'status' => intval($param['status']),
                'experience_shop' => intval($param['experience_shop']),
                'act_uid' => intval($_SESSION["ADMIN_ID"]),
                'mtime' => time(),
            );

            if(isset($param['id']) && $param['id']>0){
                M('task_classification')->where(['id'=>$param['id']])->save($data);
            }else{
                M('task_classification')->add($data);
            }

            $this->success('操作成功');
        }

        $info = [];
        if(I('id')){
            $info = M('task_classification')->where(['id'=>I('id')])->find();
        }

        $title = I('id') ? '修改任务分类' : '新增任务分类';
        $this->assign('info', $info);
        $this->assign('title', $title);
        $this->assign('type_list', $this->type_list());
        $this->display();
    }

    /*
    * 任务领取记录
    * */
    function user_task(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = array();
        $start_time   = isset($param["start_time"]) && $param["start_time"]!='' ? $param["start_time"] . " 00:00:00" : "1997-01-01 00:00:00";
        $end_time     = isset($param["end_time"]) && $param["end_time"]!='' ? $param["end_time"] . " 23:59:59" : date( "Y-m-d" ) . " 23:59:59";
        if(I('uid')){
            $map['cmf_user_task.uid'] = I('uid');
        }
        if(I('user_login')){
            $u_info = M("users")->where(['user_login'=>I('user_login'),'user_type'=>['in',[2,5,6]]])->field('id,user_login')->find();
            $map['cmf_user_task.uid'] = $u_info['id'];
        }
        $map['cmf_user_task.ctime'] = array("between",array(strtotime($start_time),strtotime($end_time)?strtotime($end_time):time()));
        if (I('classification')) {
            $map['cmf_user_task.classification'] = I('classification');
        }
        if(I('status')!=''){
            $map['cmf_user_task.status'] = I('status');
        }

        $user_task=M("user_task");
        $tenantId=getTenantIds();
        $role_id=$_SESSION['role_id'];
        if($role_id==1){
            $count=$user_task->where($map)->count();
            $page = $this->page($count, 20);
            $lists = $user_task->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        }else{
            $map['tenant_id'] = $tenantId;
            $count=$user_task->where($map)->count();
            $page = $this->page($count, 20);
            $lists = $user_task->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        }

        $status_list = $this->user_task_status_list();  // 待审核 审核通过 审核拒绝   任务初始状态-接受任务后-》进行中状态—点确认完成后-》审核中状态-后台审核完成后-》完成状态
        $classification_list = $this->classification_list();

        $uids = array();
        foreach($lists as $key=>$val){
            array_push($uids,$val['uid']);
        }
        $uvlist = count($uids)>0 ? M("users_vip")->where(['uid'=>['in',$uids],'endtime'=>['gt',time()]])->field('uid,vip_id')->order('grade asc')->select() : [];
        $uvlist = count($uids)>0 ? array_column($uvlist,null,'uid') : [];

        foreach($lists as $key=>$val){
            $lists[$key]['userinfo'] = getUserInfo($val['uid']);
            $lists[$key]['act_userinfo'] = getUserInfo($val['act_uid']);
            $vip_id = isset($uvlist[$val['uid']]) ? $uvlist[$val['uid']]['vip_id'] : 0;
            $lists[$key]['vip_name'] = $vip_id!=0 ? M("vip")->where(['id'=>intval($vip_id)])->getField('name') : M("vip")->where(array('orderno'=>1))->order('length asc')->limit(1)->find()['name'];

            $lists[$key]['task_name'] = M("task")->where(['id'=>intval($val['task_id'])])->getField('name');
            $lists[$key]['classification'] = isset($classification_list[$val['classification']]) ? $classification_list[$val['classification']]['name'] : $val['classification'];
            $lists[$key]['reward2_upgrade_vip'] = $val['reward2_upgrade_vip']==1 ? 'VIP等级晋级' : '-';
            $lists[$key]['status_name'] = $status_list[$val['status']];
            $lists[$key]['submit_time'] = $val['submit_time'] == '0' ? '-' : date('Y-m-d H:i:s',$val['submit_time']);
        }

        $this->assign('lists', $lists);
        $this->assign('classification_list', $classification_list);
        $this->assign('status_list', $status_list);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
   * 查看 对应会员的用户行为明细
   * */
    function view_rewardlog(){
        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            return $this->error('缺少参数');
        }
        $map['user_task_id'] = $param['id'];

        $tenantId=getTenantIds();
        $role_id=$_SESSION['role_id'];
        if($role_id==1){
            $lists = M("task_rewardlog")->where($map)->order("id desc")->select();
        }else{
            $map['tenant_id'] = $tenantId;
            $lists = M("task_rewardlog")->where($map)->order("id desc")->select();
        }

        $type_list = $this->type_list();
        $client_list = $this->client_list();
        foreach($lists as $key=>$val){
            $usertask_info = M("user_task")->where(['id'=>$val['user_task_id']])->find();
            $classifi_info = M("task_classification")->where(['id'=>$usertask_info['classification']])->find();
            $lists[$key]['userinfo'] = getUserInfo($val['uid']);
            if($val['vip_id']==0){
                $vip_info = M("vip")->where(array('orderno'=>1))->order('length asc')->limit(1)->find();
                $lists[$key]['vip_id'] = $vip_info['name'].'（0个月）';
            }else{
                $vip_info = M("vip")->where(array('id' =>$val['vip_id']))->find();
                $lists[$key]['vip_id'] = $vip_info['name'].'（'.$vip_info['length'].'个月）';
            }
            $lists[$key]['type'] = $type_list[$val['type']];
            $lists[$key]['client'] = $usertask_info['client'] ? $client_list[$usertask_info['client']] : '';
            $lists[$key]['unlock_amount'] = $classifi_info['unlock_amount'];
            $lists[$key]['giveout_type'] = $val['giveout_type']==1 ? '人工审核发放' : '系统自动发放';
            if($val['reward_end_vip']==0){
                $vip_info = M("vip")->where(array('orderno'=>1))->order('length asc')->limit(1)->find();
                $lists[$key]['reward_end_vip'] = $vip_info['name'].'（0个月）';
            }else{
                $vip_info = M("vip")->where(array('id' =>$val['reward_end_vip']))->find();
                $lists[$key]['reward_end_vip'] = $vip_info['name'].'（'.$vip_info['length'].'个月）';
            }
        }

        $this->assign('lists', $lists);
        $this->display();
    }

    /*
     * 取消任务
     * */
    function cancel_task(){
        $param = I('param.');
        if($param['id']){
            M('user_task')->where(['id'=>$param['id'],'status'=>1])->save(['status'=>0]);
            $this->success('取消任务成功');
        }
        $this->error('取消任务失败');
    }

    /*
    * 立即审核
    * */
    function immediately_check(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['id']) || !$param['id']){
                $this->error('缺少参数');
            }
            if(!isset($param['status']) || $param['status']==''){
                $this->error('请选择 审核状态');
            }
            $info = M('user_task')->where(['id'=>intval(I('id'))])->find();
            if ($info['only_one']==0) {
                if ($info['status'] != 2) {
                    $this->error('该任务状态不是审核中，不能审核');
                }
            }

            if($param['status'] == 4){
                try{
                    M()->startTrans();

                    $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($info['uid']);
                    if(!$user_info){
                        $this->error('用户不存在');
                    }
                    if ($info['only_one']==0) {
                        $data = array('status' => 4,'remark' => $param['remark'],'act_uid' => intval($_SESSION["ADMIN_ID"]),'mtime' => time());
                    }else{
                        $data = array('status' => 1,'remark' => $param['remark'],'act_uid' => intval($_SESSION["ADMIN_ID"]),'mtime' => time(),'today_ischeck'=>1);

                    }
                    M('user_task')->where(['id'=>$param['id']])->save($data);

                    if($info['price'] > 0){
                        M('users')->where(['id'=>$info['uid']])->setInc('coin',$info['price']);
                        $users_coinrecord0 = array(
                            "type"=>'income',
                            "action"=>'returntaskprice',
                            "uid"=>intval($info['uid']),
                            'user_login' => $user_info['user_login'],
                            "user_type" => intval($user_info['user_type']),
                            "pre_balance" => floatval($user_info['coin']),
                            "totalcoin"=>floatval($info['price']),
                            "after_balance" => floatval(bcadd($user_info['coin'], $info['price'],4)),
                            "addtime"=>time(),
                            'tenant_id' => $user_info['tenant_id'],
                        );
                        $this->addCoinrecord($users_coinrecord0);
                    }
                    M()->commit();
                }catch (\Exception $e){
                    M()->rollback();
                    $this->success('操作失败：'.$e->getMessage());
                }
                delUserInfoCache($info['uid']);
                $this->success('操作成功');
            }

            $status = $param['status'] == 3 ? 3 : 4; // 3审核通过（已完成），4审核拒绝

            if($info['experience_shop'] == '1'){ // 是否体验商城: 0否，1是
                $data = array('status' => $status,'remark' => $param['remark'],'act_uid' => intval($_SESSION["ADMIN_ID"]),'mtime' => time());
                M('user_task')->where(['id'=>$param['id']])->save($data);
            }else{
                try{
                    M()->startTrans();

                    $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($info['uid']);
                    if(!$user_info){
                        $this->error('用户不存在');
                    }

                    if($user_info['nowithdrawable_coin']*100 < $info['reward1']*100){ // 不可提现金额不足
                        $this->error('不可提现金额不足');
                    }
                    if ($info['only_one']==0) {
                        $data = array('status' => $status,'remark' => $param['remark'],'act_uid' => intval($_SESSION["ADMIN_ID"]),'mtime' => time());
                    }else{
                        $data = array('status' => 1,'remark' => $param['remark'],'act_uid' => intval($_SESSION["ADMIN_ID"]),'mtime' => time(),'today_ischeck'=>1);

                    }
                    M('user_task')->where(['id'=>$param['id']])->save($data);

                    // 额外奖励（分类 佣金比例）
                    $commission = bcmul($info['price'],$info['commission_rate'],2);
                    if($commission > 0){
                        M('users')->where(['id'=>$info['uid']])->setInc('coin',$commission);
                        $users_coinrecord2 = array(
                            'type' => 'income',
                            'uid' => intval($info['uid']),
                            'user_login' => $user_info['user_login'],
                            "user_type" => intval($user_info['user_type']),
                            'addtime' => time(),
                            'tenant_id' => intval($user_info['tenant_id']),
                            'action' => 'taskcommission',
                            "pre_balance" => floatval($user_info['coin']),
                            'totalcoin' => floatval($commission),
                            "after_balance" => floatval(bcadd($user_info['coin'], $commission,4)),
                        );
                        $this->addCoinrecord($users_coinrecord2); // 可提现金币变动记录
                    }

                    $reward1 = $info['reward1'];

                    $user_vip = M("users_vip")->where(['uid'=>intval($info['uid']),'endtime'=>['gt',time()]])->order('grade desc')->find();
                    $vip_id = isset($user_vip['vip_id']) ? $user_vip['vip_id'] : 0;

                    if($info['reward1']>0){  // 完成奖励1 大于 0 才执行
                        M('users')->execute('UPDATE '.C('DB_PREFIX').'users SET coin=coin+'.floatval($reward1).', nowithdrawable_coin=nowithdrawable_coin-'.floatval($reward1).' WHERE id = '.intval($info['uid']));
                        delUserInfoCache($info['uid']);
                        $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($info['uid']);
                        // 任务奖励
                        $users_coinrecord1 = array(
                            'type' => 'move',
                            'uid' => intval($info['uid']),
                            'user_login' => $user_info['user_login'],
                            "user_type" => intval($user_info['user_type']),
                            'addtime' => time(),
                            'tenant_id' => intval($user_info['tenant_id']),
                            'action' => 'task',
                            "pre_balance" => floatval($user_info['coin']),
                            'totalcoin' => floatval($reward1),
                            "after_balance" => floatval(bcadd($user_info['coin'], $reward1,4)),
                        );
                        $this->addCoinrecord($users_coinrecord1); // 可提现钻石变动记录

                        $task_rewardlog_data = array(
                            'uid' => intval($info['uid']),
                            'vip_id' => intval($vip_id),
                            'type' => intval($info['task_type']),
                            'task_id' => intval($info['task_id']),
                            'task_name' => $info['task_name'],
                            'user_task_id' => intval($info['id']),
                            'reward_type' => 1,
                            'reward1' => floatval($info['reward1']),
                            'reward2_upgrade_vip' => intval($info['reward2_upgrade_vip']),
                            'start_time' => intval($info['start_time']),
                            'end_time' => intval($info['end_time']),
                            'reward_start_amount' => floatval($user_info['coin']),
                            'reward_result' => '奖励金额：'.$reward1,
                            'reward_end_amount' => bcadd($user_info['coin'],$reward1,2),
                            'reward_end_vip' => intval($vip_id),
                            'giveout_type' => 1,
                            'status' => 1,
                            'tenant_id' => intval($user_info['tenant_id']),
                            'mtime' => time(),
                        );
                        M('task_rewardlog')->add($task_rewardlog_data);
                    }

                    if($info['reward2_upgrade_vip']=='1'){ // 升级VIP等级
                        $next_vip_info = $this->get_next_vip($vip_id);
                        $exist_users_vip = M('users_vip')->where(['uid'=>intval($info['uid']),'grade'=>intval($next_vip_info['orderno'])])->find();

                        // 如果购买同等级的就延长时间，购买搞等级的，就把低等级到期的时间延长
                        if($vip_id != 0 && $user_vip['grade'] == $next_vip_info['orderno']){
                            $endtime = strtotime ("+".$next_vip_info['length']." month", $user_vip['endtime']);
                        }else{
                            $endtime = strtotime ("+".$next_vip_info['length']." month", time());
                        }

                        if ($next_vip_info['give_data']){
                            $endtime = strtotime ("+{$next_vip_info['give_data']} day", $endtime);
                        }

                        if($vip_id == 0 || ($vip_id != 0 && $user_vip['grade'] != $next_vip_info['orderno'])){
                            if(isset($exist_users_vip['id'])){
                                M('users_vip')->where(['id'=>intval($exist_users_vip['id'])])->save([
                                    'addtime' => time(),
                                    'endtime' => intval($endtime),
                                    'vip_id'=>intval($next_vip_info['id']),
                                ]);
                            }else{
                                $users_vip_data = array(
                                    'uid' => intval($info['uid']),
                                    'addtime' => time(),
                                    'endtime' => intval($endtime),
                                    'tenant_id' => intval($user_info['tenant_id']),
                                    'vip_id'=>intval($next_vip_info['id']),
                                    'grade'=>intval($next_vip_info['orderno']),
                                );
                                M('users_vip')->add($users_vip_data);
                            }
                        }
                        if($vip_id != 0 && $user_vip['grade'] == $next_vip_info['orderno']){
                            M('users_vip')->where(['id'=>intval($user_vip['id'])])->save([
                                'addtime' => time(),
                                'endtime' => intval($endtime),
                                'vip_id'=>intval($next_vip_info['id']),
                            ]);
                        }

                        // 小于升级的等级，延长时间
                        $historyVip =  M('users_vip')->where(['uid'=>intval($info['uid']), 'endtime'=>['gt',time()]])->order('grade desc')->select(); // 获取用户全部没过期的vip历史
                        foreach ($historyVip as $key =>  $val){
                            if ($key > 0){  // 刚升级的用户等级不参与计算
                                $val['endtime'] = strtotime ("+".$next_vip_info['length']." month", $val['endtime']);
                                if ($next_vip_info['give_data']){
                                    $val['endtime'] = strtotime ("+{$next_vip_info['give_data']} day",  $val['endtime']);
                                }
                                M('users_vip')->where(['id'=>intval($val['id'])])->save(array('endtime'=>$val['endtime']));
                            }
                        }

                        $task_rewardlog_data1 = array(
                            'uid' => intval($info['uid']),
                            'vip_id' => intval($vip_id),
                            'type' => intval($info['task_type']),
                            'task_id' => intval($info['task_id']),
                            'task_name' => $info['task_name'],
                            'user_task_id' => intval($info['id']),
                            'reward_type' => 2,
                            'reward1' => floatval($info['reward1']),
                            'reward2_upgrade_vip' => intval($info['reward2_upgrade_vip']),
                            'start_time' => intval($info['start_time']),
                            'end_time' => intval($info['end_time']),
                            'reward_start_amount' => bcadd($user_info['coin'],$reward1,2),
                            'reward_result' => 'VIP等级升级',
                            'reward_end_amount' => bcadd($user_info['coin'],$reward1,2),
                            'reward_end_vip' => intval($next_vip_info['id']),
                            'giveout_type' => 1,
                            'status' => 1,
                            'tenant_id' => intval($user_info['tenant_id']),
                            'mtime' => time(),
                        );
                        M('task_rewardlog')->add($task_rewardlog_data1);
                    }
                    $this->agent_rabate($user_info,$info['price']); // 代理返佣
                    M()->commit();
                }catch (\Exception $e){
                    M()->rollback();
                    $this->success('操作失败：'.$e->getMessage());
                }
            }
            delUserInfoCache($info['uid']);
            $this->success('操作成功');
        }

        $info = M('user_task')->where(['id'=>intval(I('id'))])->find();
        $info['userinfo'] = getUserInfo($info['uid']);
        $user_vip = M("users_vip")->where(['uid'=>intval($info['uid']),'endtime'=>['gt',time()]])->order('grade desc')->find();
        $vip_id = isset($user_vip['vip_id']) ? $user_vip['vip_id'] : 0;
        if($vip_id==0){
            $vip_info = M("vip")->where(array('orderno'=>1))->order('length asc')->limit(1)->find();
            $info['vip_name'] = $vip_info['name'].'（0个月）';
        }else{
            $vip_info = M("vip")->where(array('id' =>$vip_id))->find();
            $info['vip_name'] = $vip_info['name'].'（'.$vip_info['length'].'个月）';
        }
        $info['task_name'] = M("task")->where(['id'=>intval($info['task_id'])])->getField('name');
        $info['classification'] = M("task_classification")->where(array('id'=>$info['classification']))->getField('name');
        $info['submit_time'] = $info['submit_time'] == '0' ? '-' : date('Y-m-d H:i:s',$info['submit_time']);
        $info['task_type'] = $this->type_list()[$info['task_type']].'任务';

        if($info['reward2_upgrade_vip'] == '1'){
            $next_vip_info = $this->get_next_vip($vip_id);
            $info['next_vip_name'] = isset($next_vip_info['name']) ? $next_vip_info['name'].'（'.$next_vip_info['length'].'个月）' : $info['vip_id'];
        }

        $this->assign('info', $info);
        $this->display();
    }

    public function get_next_vip($vip_id){
        if($vip_id==0){
            return M("vip")->where(array('orderno'=>1))->order('length asc')->find();
        }
        $curr_vip_info = M("vip")->where(['id'=>intval($vip_id)])->find();
        if(!$curr_vip_info){
            return M("vip")->where(array('orderno'=>1))->order('length asc')->find();
        }
        $vip_list = M("vip")->order('length asc')->select();
        $vip = array();
        foreach ($vip_list as $key=>$val){
            if(empty($vip) && $curr_vip_info['orderno']==$val['orderno'] && $curr_vip_info['length']<$val['length']){
                $vip = $val;
            }
        }
        if(empty($vip)){
            switch ($curr_vip_info['orderno']){
                case '1':
                    $next_vip_orderno = '2';
                    break;
                case '2':
                    $next_vip_orderno = '3';
                    break;
                case '3':
                    $next_vip_orderno = '4';
                    break;
                case '4':
                    $next_vip_orderno = '6';
                    break;
                case '6':
                    $next_vip_orderno = '6';
                    break;
                default:
                    $next_vip_orderno = '';
            }
            if($next_vip_orderno==''){
                $vip = $curr_vip_info;
            }else{
                $vip = M("vip")->where(array('orderno'=>$next_vip_orderno))->order('length asc')->find();
                $vip = $vip ? $vip : $curr_vip_info;
            }
        }

        return $vip;
    }

    /*
     * 代理返佣
     * */
    public function agent_rabate($user_info,$price=0){
        $agentinfo = getAgentInfo($user_info['id']);
        $RebateConf = getAgentRebateConf($user_info['tenant_id']);
        if(!$RebateConf){
            return ;
        }
        $arr = array(   array('uid'=>$agentinfo['one_uid'],'profit'=>$RebateConf['one_profit']),
                        array('uid'=>$agentinfo['two_uid'],'profit'=>$RebateConf['two_profit']),
                        array('uid'=>$agentinfo['three_uid'],'profit'=>$RebateConf['three_profit']),
                        array('uid'=>$agentinfo['four_uid'],'profit'=>$RebateConf['four_profit']),
                        array('uid'=>$agentinfo['five_uid'],'profit'=>$RebateConf['five_profit']) );
        foreach ($arr as $key=>$val){
            $rebate = bcmul($price,$val['profit']/100,2);
            if($val['uid'] && $val['profit'] > 0 && $rebate > 0){
                $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($val['uid']);
                M('users')->where(['id'=>$user_info['id']])->save(['coin'=>['exp','coin+'.$rebate]]);
                delUserInfoCache($val['uid']);
                $coinrecord = array(
                    'type' => 'move',
                    'uid' => intval($user_info['id']),
                    'user_login' => $user_info['user_login'],
                    "user_type" => intval($user_info['user_type']),
                    'addtime' => time(),
                    'tenant_id' => intval($user_info['tenant_id']),
                    'action' => 'agent_rebate',
                    "pre_balance" => floatval($user_info['coin']),
                    'totalcoin' => floatval($rebate),
                    "after_balance" => floatval(bcadd($user_info['coin'], $rebate,4)),
                );
                $this->addCoinrecord($coinrecord); // 可提现钻石变动记录
            }
        }
        return ;
    }

    /*
    * 奖励明细
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
            $u_info = M("users")->where(['user_login'=>I('user_login'),'user_type'=>['in',[2,5,6]]])->field('id,user_login')->find();
            $map['uid'] = $u_info['id'];
        }
        if(I('start_time')){
            $map['start_time'] = ['egt',strtotime(I('start_time'))];
        }
        if(I('end_time')){
            $map['end_time'] = ['elt',strtotime(I('end_time'). " 23:59:59")];
        }
        if (I('reward_type')) {
            $map['reward_type'] = I('reward_type');
        }

        $task_rewardlog=M("task_rewardlog");
        $tenantId=getTenantIds();
        $role_id=$_SESSION['role_id'];
        if($role_id==1){
            $count=$task_rewardlog->where($map)->count();
            $page = $this->page($count, 20);
            $lists = $task_rewardlog->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        }else{
            $map['tenant_id'] = $tenantId;
            $count=$task_rewardlog->where($map)->count();
            $page = $this->page($count, 20);
            $lists = $task_rewardlog->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        }

        $type_list = $this->type_list();
        $reward_type_list = ['1'=>'奖励1','2'=>'奖励2'];
        foreach($lists as $key=>$val){
            $lists[$key]['userinfo'] = getUserInfo($val['uid']);
            if($val['vip_id']==0){
                $vip_info = M("vip")->where(array('orderno'=>1))->order('length asc')->limit(1)->find();
                $lists[$key]['vip_id'] = $vip_info['name'].'（0个月）';
            }else{
                $vip_info = M("vip")->where(array('id' =>$val['vip_id']))->find();
                $lists[$key]['vip_id'] = $vip_info['name'].'（'.$vip_info['length'].'个月）';
            }
            $lists[$key]['type'] = $type_list[$val['type']];
            $lists[$key]['task_name'] = M("task")->where(array('id' =>$val['task_id'] ))->getField('name');
            $lists[$key]['reward_type'] = $reward_type_list[$val['reward_type']];
            $lists[$key]['giveout_type'] = $val['giveout_type']==1 ? '人工审核发放' : '系统自动发放';
            if($val['reward_end_vip']==0){
                $vip_info = M("vip")->where(array('orderno'=>1))->order('length asc')->limit(1)->find();
                $lists[$key]['reward_end_vip'] = $vip_info['name'].'（0个月）';
            }else{
                $vip_info = M("vip")->where(array('id' =>$val['reward_end_vip']))->find();
                $lists[$key]['reward_end_vip'] = $vip_info['name'].'（'.$vip_info['length'].'个月）';
            }
            $lists[$key]['status'] = $val['status']==1 ? '已发放' : '未发放';
        }

        $this->assign('lists', $lists);
        $this->assign('type_list', $type_list);
        $this->assign('reward_type_list', $reward_type_list);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
    * 登录赠送
    * */
    function login_reward(){
        if(IS_POST){
            $param = I('post.');
            $data = array();
            foreach ($param as $key=>$val){
                if(!($val >= 0 && $val <= 9999)){
                    $this->error('操作失败，输入的值范围：0 - 9999');
                }
                $data[$key] = $val;
            }

            unset($data['id']);
            $data['act_uid'] = intval($_SESSION["ADMIN_ID"]);
            $data['mtime'] = time();

            if(!M('task_loginreward')->where(['type'=>1])->find()){
                $data['type'] = 1;
                M('task_loginreward')->add($data);
            }else{
                M('task_loginreward')->where(['id'=>$param['id']])->save($data);
            }
            $this->success('操作成功');
        }

        $info = M('task_loginreward')->where(['type'=>1])->find();

        $this->assign('info', $info);
        $this->display();
    }

    /*
    * 打针计划、派单功能
    * */
    function plan(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = array();
        if(I('uid')){
            $map['cmf_task_plan.uid'] = I('uid');
        }
        if(I('user_login')){
            $u_info = M("users")->where(['user_login'=>I('user_login'),'user_type'=>['in',[2,5,6]]])->field('id,user_login')->find();
            $map['cmf_task_plan.uid'] = $u_info['id'];
        }
        if(I('num')){
            $map['num'] = intval(I('num'));
        }
        if (I('type')) {
            $map['type'] = intval(I('type'));
        }

        $task_plan=M("task_plan");
        $count=$task_plan->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $task_plan->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach($lists as $key=>$val){
            $lists[$key]['userinfo'] = getUserInfo($val['uid']);
            $lists[$key]['act_userinfo'] = getUserInfo($val['act_uid']);
            $lists[$key]['status'] = $val['status'] == '1' ? '开启' : '关闭';
            if($val['type'] == '1'){
                $lists[$key]['type'] = '打针计划';
                $lists[$key]['amount'] = '-';
            }else{
                $lists[$key]['type'] = '派单功能';
                $lists[$key]['percent'] = '-';
            }
        }

        $this->assign('type_list', ['1'=>'打针计划','2'=>'派单功能']);
        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
     * 新增/修改 计划
     * */
    function addedit_plan(){
        if(IS_POST){
            $param = I('post.');
            if(!$param['num'] || $param['num']<=0){
                $this->error('打针计划第几单输入错误');
            }
            if(!($param['inject_percent']>=0 && $param['inject_percent']<=1)){
                $this->error('抢到本金百分比输入错误，百分比区间为：0 - 1');
            }
            if(!($param['inject_percent']>=0 && $param['inject_percent']<=999999)){
                $this->error('抢到多少金额输入错误，金额区间为：0 - 999999');
            }
            $uinfo = M('users')->where(['id'=>intval($param['uid']),'user_type'=>['in',[2,5,6]]])->find();
            if(!$uinfo){
                $this->error('会员不存在');
            }
            $is_exist_where = array('uid'=>intval($param['uid']),'num'=>intval($param['num']));
            if(isset($param['id']) && $param['id']>0){
                $is_exist_where['id'] = array('neq',$param['id']);
            }
            $is_exist = M('task_plan')->where($is_exist_where)->find();
            if($is_exist){
                $this->error('第'.$param['num'].'单已存在');
            }

            $data = array(
                'uid' => intval($param['uid']),
                'num' => intval($param['num']),
                'type' => intval($param['type']),
                'percent' => ($param['type']==1 ? floatval($param['percent']) : 0),
                'amount' => ($param['type']==2 ? floatval($param['amount']) : 0),
                'status' => intval($param["status"]),
                'act_uid' => intval($_SESSION["ADMIN_ID"]),
                'mtime' => time(),
            );
            if(isset($param['id']) && $param['id']>0){
                M('task_plan')->where(['id'=>$param['id']])->save($data);
            }else{
                M('task_plan')->add($data);
            }

            $this->success('操作成功');
        }

        $info = ['type'=>1];
        if(I('id')){
            $info = M('task_plan')->where(['id'=>I('id')])->find();
        }

        $title = I('id') ? '修改' : '新增';
        $this->assign('info', $info);
        $this->assign('title', $title);
        $this->display();
    }

    /*
     * 删除计划
     * */
    function del_plan(){
        $param = I('param.');
        if($param['id']){
            M('task_plan')->where(['id'=>$param['id']])->delete();
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }
    public function getUsertask(){
        $res=array("code"=>0,"msg"=>"获取成功","info"=>array());
        $role_id=$_SESSION['role_id'];
        $rule_name = '任务领取记录';
        $isauth = getAuth($role_id,$rule_name);
        if($isauth == 1){
            $charge=M("user_task");
            $count=$charge
                ->where('status=2')
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
