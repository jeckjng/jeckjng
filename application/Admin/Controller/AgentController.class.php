<?php

/**
 * 分销
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Admin\Model\UsersModel;
use Admin\Cache\UsersAgentCache;

class AgentController extends AdminbaseController {
    function index(){

		$map=array();
        $map['tenant_id']=getTenantIds();
 
		if($_REQUEST['uid']!=''){
			$map['uid']=$_REQUEST['uid']; 
			$_GET['uid']=$_REQUEST['uid'];
		}

		if($_REQUEST['one_uid']!=''){
			$map['one_uid']=$_REQUEST['one_uid']; 
			$_GET['one_uid']=$_REQUEST['one_uid'];
		}
			
	
			
    	$Agent=M("users_agent");
    	$Users=M("users");
    	$count=$Agent->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $Agent
			->where($map)
			->order("id DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			
		foreach($lists as $k=>$v){
			$userinfo=$Users->field("user_nicename")->where("id='{$v['uid']}'")->find();
			$lists[$k]['userinfo']=$userinfo;
			if($v['one_uid']){
				$oneuserinfo=$Users->field("user_nicename")->where("id='{$v['one_uid']}'")->find();
			}else{
				$oneuserinfo['user_nicename']='未设置';
			}
			$lists[$k]['oneuserinfo']=$oneuserinfo;
			
			if($v['two_uid']){
				$twouserinfo=$Users->field("user_nicename")->where("id='{$v['two_uid']}'")->find();
			}else{
				$twouserinfo['user_nicename']='未设置';
			}
			$lists[$k]['twouserinfo']=$twouserinfo;
			
			if($v['three_uid']){
				$threeuserinfo=$Users->field("user_nicename")->where("id='{$v['three_uid']}'")->find();
			}else{
				$threeuserinfo['user_nicename']='未设置';
			}
			$lists[$k]['threeuserinfo']=$threeuserinfo;
		}
			
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));

    	$this->display();
    }

    function index2(){

		$map=array();
        $map['tenant_id']=getTenantIds();
 
		if($_REQUEST['uid']!=''){
			$map['uid']=$_REQUEST['uid']; 
			$_GET['uid']=$_REQUEST['uid'];
		}

    	$live=M("users_agent_profit");
    	$count=$live->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = $live
			->where($map)
			->order("id DESC")
			->limit($page->firstRow . ',' . $page->listRows)
			->select();
			
		foreach($lists as $k=>$v){
			 $userinfo=M("users")->field("user_nicename")->where("id='{$v['uid']}'")->find();
			 $lists[$k]['userinfo']=$userinfo;
		}
			
    	$this->assign('lists', $lists);
    	$this->assign('formget', $_GET);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }
	
	
	function del()
	{
		$id=intval($_GET['id']);
        $tenantId=getTenantIds();
		if($id){
			$result=M("users_agent")->where("id=%d and tenant_id=%d",$id,$tenantId)->delete();
			if($result){
					$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}			
		}else{				
			$this->error('数据传入失败！');
		}								  	
	}

	/*
	 * 代理返佣配置
	 *  【会员管理-代理层级】新增 【代理返佣配置】
     *   根据配置的对应比例给最多上五级返佣，某一级配置为0，则该级不给佣金
     *   佣金计算方式：通过用户完成任务 的奖励金额为返佣金额基础，各级代理获得的返佣金额 = 该奖励金额*级别对应比例
     *   比如5级； 上1级 = 50% 上2级= 40% 上3级 = 30% 上4级 = 20% 上5级=10%
     *   举个例子：一个用户做完了任务 领取了  完成奖励1 = 100
     *   完成任务的用户 得 100
     *   上1级代理得 100*50% =50
     *   上2级代理得 100*40% =40
     *   上3级代理得 100*30% =30
     *   上4级代理得 100*20% =20
     *   上5级代理得 100*10% =10
     *   租户总共给了250
	 * */
    public function rebate_conf()
    {
        if (IS_POST) {
            $param = I("post.");
            
            $data = $param['level'];

            $totle = 0;
            $list  = [];
            $i= 0;
            foreach ($data as $key=>$val){

                if(!($val >= 0 && $val <= 100)){
                    $this->error('输入不合法，输入范围：0 - 100');
                }
                $totle += $val;
                $list[$i]['agent_level'] = $key;
                $list[$i]['rate'] = $val;
                $list[$i]['tenant_id'] = getTenantId();
                $i ++ ;
            }

            try {

                M("agent_proportion")->where(['tenant_id' => getTenantId()])->delete();
                M("agent_proportion")->addAll($list);
            }catch (\Exception $e) {
                $this->error('数据传入失败！');
            }
            delAgentRebateConf(getTenantId());

            setAdminLog('编辑代理返佣');
            $this->success('操作成功');
         /*   try{
                M("users_agent_profit")->where(array("id"=>$param['id']))->save($data);
            }catch (\Exception $e){
                setAdminLog('编辑代理返佣失败【'.$data['id'].'，'.$e->getMessage().'】');
                $this->error('数据传入失败！');
            }
            delAgentRebateConf(getTenantId());
            setAdminLog('编辑代理返佣成功【'.$data['id'].'】');
          ;*/
        }

     /*   $info = M("users_agent_profit")->where(['tenant_id'=>getTenantId()])->find();
        if(!$info){
            M("users_agent_profit")->add(['tenant_id'=>getTenantId(), 'act_uid'=>$_SESSION['ADMIN_ID'],'mtime'=>time()]);
            $info = M("users_agent_profit")->where(['tenant_id'=>getTenantId()])->find();
        }
        $info['one_profit'] = floatval($info['one_profit']);
        $info['two_profit'] = floatval($info['two_profit']);
        $info['three_profit'] = floatval($info['three_profit']);
        $info['four_profit'] = floatval($info['four_profit']);
        $info['five_profit'] = floatval($info['five_profit']);*/

        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->field('agent_sum')->find();
        $proportion =M("agent_proportion")->where('tenant_id="'.getTenantIds().'"')->select();
        $proportionByLevel = [];

        if ($proportion){
            $proportionByLevel = array_column($proportion,null,'agent_level');
        }
        $this->assign('proportion',$proportionByLevel);
        $this->assign('config',$config);
        $this->display();
    }

    public  function level_num(){
        $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->field('agent_sum')->find();
        $this->assign('config',$config);
        $this->display();
    }

    public  function rebate_list(){
        $param = I('param.');

        if($_REQUEST['start_time']!=''){
            $map['addtime']=array("gt",(strtotime($_REQUEST['start_time'].' 00:00:00')));
            $_GET['start_time']=$_REQUEST['start_time'];
        }
        $map['tenant_id']=getTenantIds();
        if($_REQUEST['end_time']!=''){

            $map['addtime']=array("lt",(strtotime($_REQUEST['end_time'].' 23:59:59')));
            $_GET['end_time']=$_REQUEST['end_time'];
        }
        if($_REQUEST['start_time']!='' && $_REQUEST['end_time']!='' ){
            $map['addtime']=array("between",array(strtotime($_REQUEST['start_time'].' 00:00:00'),strtotime($_REQUEST['end_time'].' 23:59:59')));
            $_GET['start_time']=$_REQUEST['start_time'];
            $_GET['end_time']=$_REQUEST['end_time'];
        }
        if($_REQUEST['type']!=''){
            $map['type']=array("eq",($_REQUEST['type']));
            $_GET['type']=$_REQUEST['type'];
        }
        if($_REQUEST['uid']){
            $map['uid']=array("eq",($_REQUEST['uid']));
            $_GET['uid']=$_REQUEST['uid'];
        }
        if($_REQUEST['pid']){
            $map['pid']=array("eq",($_REQUEST['pid']));
            $_GET['pid']=$_REQUEST['pid'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }

        if($_REQUEST['pid_username']){
            $_GET['pid_username'] = $_REQUEST['pid_username'];
            $userInfo =  M('users')->where(['user_login' => $_REQUEST['pid_username']])->field('id')->find();

            if (!$userInfo){
                $map['pid']  = 0;
            }else{
                $map['pid'] = $userInfo['id'];
            }
            $_GET['pid_username']=$_REQUEST['pid_username'];
        }
        if($_REQUEST['uid_username']){

            $userInfo =  M('users')->where(['user_login' => $_REQUEST['uid_username']])->field('id')->find();
            if (!$userInfo){
                $map['uid']  = 0;
            }else{
                $map['uid'] = $userInfo['id'];
            }
            $_GET['uid_username']=$_REQUEST['uid_username'];
        }
        $p=I("p");
        if(!$p){
            $p=1;
        }
        $model = M("agent_reward");
        $count = $model->where($map)->count();
        $page = $this->page($count, 20);
        $orderstr=' addtime DESC';
        $lists = $model
            ->where($map)
            ->order($orderstr)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $uidsArray = [];
        foreach($lists as $k=>$val){
            $uidsArray[] = $val['uid'];
            $uidsArray[] = $val['pid'];
            $userinfo = getUserInfo($val['pid']);
            if($val['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
        }
        if ($uidsArray) {
            $userList = M('users')->where(['id' => ['in', $uidsArray]])->field('id,user_login')->select();
            $userListById = array_column($userList, null, 'id');
            foreach ($lists as $k => $v) {
                $lists[$k]['username'] = $userListById[$v['uid']]['user_login'];
                $lists[$k]['pid_username'] = $userListById[$v['pid']]['user_login'];
            }
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign("p",$p);
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }


    public  function  team_statistics(){
        $param = I('param.');

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;

        $timeselect = get_timeselect(); // 获取时间格式
        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : 'today';
        if(!isset($param['start_time']) || $param['start_time'] == ''){
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
        }
        if(!isset($param['end_time']) || $param['end_time'] == ''){
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $param['start_time'] = explode(' ', $timeselect['today_start'])[0];
            $param['end_time'] = explode(' ', $timeselect['today_end'])[0];
        }
        $start_time = $param['start_time'];
        $end_time = $param['end_time'].' 23:59:59';

        $config  = getConfigPri($tenant_id);

        $data = [
            'son_count' => 0, // 下级用户数量
            'vip_margin' => 0,// 用户保证金
            'team_vip_margin' => 0, // 团队保证金
            'agent_video_count'=> 0, // 用户上传视频数量
            'agent_amount' => 0, //代理收益
            'commission'=> 0,
            'son_charge_amount'=> 0,
            'son_charge_count' => 0,
            'team_uplode_reward' => 0, // 团队上传视频佣金

            'team_like_amount' =>0,
            'son_time_count'=> 0, // 时间段用户
            'team_uplode_count' => 0,
            'son_coin' => 0,
            'team_like_count'=> 0,
            'cash_amount' => 0,
        ];

        $sonUid = [];
        $uid = null;
        $effect_user_types = UsersModel::getInstance()->effect_user_types;
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $effect_user_types = $param['user_type'];
            }
        }else{
            $effect_user_types = '2';
            $param['user_type'] = '2';
        }

        if(isset($param['user_login']) && $param['user_login']){
            $userWhere = ['user_type'=>['in', $effect_user_types]];
            $userWhere['tenant_id'] = $tenant_id;
            $userWhere['user_login'] = $param['user_login'];
            $userInfo = M('users')->where($userWhere)->field('id,pids,vip_margin')->find();

            if(!$userInfo){
                $this->error('代理用户不存在');
            }
            $uid = $userInfo['id'];
            $lowerUserWhere = ['user_type'=>['in', $effect_user_types]];
            $lowerUserWhere['tenant_id'] = $tenant_id;
//            $lowerUserWhere['pids'] =array('like',array('%,'.$userInfo['id'].',%','%,'.$userInfo['id']),'or');

            $allSubUids = UsersAgentCache::getInstance()->getUserAllSubUid($tenant_id, $uid);

            $data['UsersAgentCache_sql'] = M()->getLastSql();

            if(count($allSubUids) > 0){
                $lowerUserWhere['id'] = ['in', $allSubUids];
            }else{
                $lowerUserWhere['id'] = ['in', ''];
            }

            $lowerUserInfo = M('users')
                ->where($lowerUserWhere)->field('id,pids,vip_margin,create_time,coin')
                ->select();

            $data['allSubUids'] = json_encode($allSubUids);
            $data['users_sql'] = M()->getLastSql();

            foreach ($lowerUserInfo as $lowerUserInfoKey => $lowerUserInfoInfo){
//                $sonPid  = explode(',',$lowerUserInfoInfo['pids']);
//                array_shift($sonPid);
//               if (count($sonPid)> $config['agent_sum']){
//                   $key = array_search($userInfo['id'],$sonPid);
//                   if (count($sonPid) - $config['agent_sum']>= $key){ // 删除多余层级用户  只取配置多少 层级用户
//                       unset($lowerUserInfo[$lowerUserInfoKey]);
//                   }else{
//                       if (strtotime($lowerUserInfoInfo['create_time'])>= strtotime($start_time) && strtotime($lowerUserInfoInfo['create_time'])<=strtotime($end_time) ){
//                           $data['son_time_count']++;
//                       }
//                   }
//               }else{
                    if (strtotime($lowerUserInfoInfo['create_time'])>= strtotime($start_time) && strtotime($lowerUserInfoInfo['create_time'])<=strtotime($end_time) ){
                        $data['son_time_count']++;
                    }
//               }
            }
            $sonUid  =  array_column($lowerUserInfo,'id');

            $data['son_count'] = count($sonUid);
            $data['vip_margin'] = $userInfo['vip_margin'];
            if (!$sonUid){
                $sonUid[] = 0;
            }
            $sonVipMargin=array_column($lowerUserInfo, 'vip_margin');
            $data['team_vip_margin'] =  bcadd(array_sum($sonVipMargin),$userInfo['vip_margin'],2);

            $sonCoin=array_column($lowerUserInfo, 'coin');
            $data['son_coin'] =  array_sum($sonCoin);
        }else{
            $map = ['user_type'=>['in', $effect_user_types]];
            $map['tenant_id'] = $tenant_id;
            $user_count_sum_info = M('users')->where($map)->field('
                count(id) as son_count
                ,sum(coin) as sum_coin
                ,sum(vip_margin) as team_vip_margin
            ')->find();

            $time_map = ['user_type'=>['in', $effect_user_types]];
            $time_map['tenant_id'] = $tenant_id;
            $time_map['create_time'] = array("between",array($start_time, $end_time));
            $data['son_time_count'] = M('users')->where($time_map)->count();

            $data['son_count'] = $user_count_sum_info['son_count'] ? $user_count_sum_info['son_count'] : $data['son_time_count'];
            $data['vip_margin'] = 0;
            $data['team_vip_margin'] = $user_count_sum_info['team_vip_margin'] ? $user_count_sum_info['team_vip_margin'] : $data['team_vip_margin'];
            $data['son_coin'] = $user_count_sum_info['sum_coin'] ? $user_count_sum_info['sum_coin'] : $data['son_coin'];
        }

        /**
         * 代理上传视频数量
         */
        $AgentVideoWhere = ['user_type'=>['in', $effect_user_types]];
        $AgentVideoWhere['tenant_id'] = $tenant_id;
        $AgentVideoWhere['check_date'] =array("between",array(($start_time),($end_time)));
        $AgentVideoWhere['status'] = array('eq',2);
        if(!empty($sonUid)){
            $AgentVideoWhere['uid'] = array('in',$sonUid);
        }
        $data['agent_video_count']   = M('video')->where($AgentVideoWhere)->count();

        /**
         * 代理佣金
         */
        $agentRewardWhere = ['user_type'=>['in', $effect_user_types]];
        $agentRewardWhere['tenant_id'] = $tenant_id;
        $agentRewardWhere['addtime'] = array("between",array((strtotime( $start_time)),(strtotime($end_time))));
        if($uid){
            $agentRewardWhere['pid'] = array('eq', $uid);
        }
        $agent_amount =M('agent_reward')->where($agentRewardWhere)->sum('amount') ;
        $data['agent_amount']   = $agent_amount?$agent_amount:0;

        /**
         * 代理视频点赞收益
         */
        $agentLikeWhere = ['user_type'=>['in', $effect_user_types]];
        $agentLikeWhere['tenant_id'] = $tenant_id;
        $agentLikeWhere['create_time'] = array("between",array((strtotime( $start_time)),(strtotime($end_time))));
        if($uid){
            $agentLikeWhere['video_uid'] = array('eq', $uid);
        }
        $agentLikeAmount  =M('video_profit')->where($agentLikeWhere)->sum('video_profit') ;

        /**
         * 团队点赞收益
         */
        $teamLikeWhere = ['user_type'=>['in', $effect_user_types]];
        $teamLikeWhere['tenant_id'] = $tenant_id;
        $teamLikeWhere['create_time'] = array("between",array((strtotime( $start_time)),(strtotime($end_time))));
        if(!empty($sonUid)){
            $teamLikeWhere['uid'] = array('in',$sonUid);
        }
        $teamLikeAmount  =M('video_profit')->where($teamLikeWhere)->sum('video_profit') ;

        $data['team_like_amount'] = $teamLikeAmount?$teamLikeAmount:0;

        $teamLikeCountList  =M('video_profit')->where($teamLikeWhere)->field('video_uid')->group('video_uid')->select();
        $data['team_like_count'] = count($teamLikeCountList);
        $data['video_profit_count_sql'] = M()->getLastSql();

        /*
         * 代理上传视频奖励
         */
        $agentUplodeAmountWhere = ['user_type'=>['in', $effect_user_types]];
        $agentUplodeAmountWhere['tenant_id'] = $tenant_id;
        $agentUplodeAmountWhere['add_time'] = array("between",array((strtotime( $start_time)),(strtotime($end_time))));
        if($uid){
            $agentUplodeAmountWhere['uid'] = array('eq', $uid);
        }
        $agentUplodeAmount  =M('video_uplode_reward')->where($agentUplodeAmountWhere)->sum('price') ;

        /**
         * 个人佣金
         */
        $data['commission'] = bcadd($agentLikeAmount,$agentUplodeAmount,2);

        /**
         * 团队充值
         */
        //  $sonUid[] = $userInfo['id'];
        $usersChargeWhere = ['user_type'=>['in', $effect_user_types]];
        $usersChargeWhere['tenant_id'] = $tenant_id;
        $usersChargeWhere['addtime'] = array("between",array((strtotime( $start_time)),(strtotime($end_time))));;
        if(!empty($sonUid)){
            $usersChargeWhere['uid'] = array("in",$sonUid);
        }
        $usersChargeWhere['status'] = array('eq',2);
        $usersChargeWhere['type'] = array('in',[1,2,3]);
        $sonChargeAmount = M('users_charge')->where($usersChargeWhere)->sum('rnb_money');
        $data['son_charge_amount'] =  $sonChargeAmount? $sonChargeAmount:0;

        $sonChargeCountList = M('users_charge')->where($usersChargeWhere)->field('uid')->group('uid')->select();
        $data['son_charge_count']  = count($sonChargeCountList);
        $data['users_charge_count_sql'] = M()->getLastSql();

        /**
         * 团队上传视频奖励
         */
        $teamUplodeRewardWhere = ['user_type'=>['in', $effect_user_types]];
        $teamUplodeRewardWhere['tenant_id'] = $tenant_id;
        $teamUplodeRewardWhere['add_time'] =array("between",array((strtotime( $start_time)),(strtotime($end_time))));
        if(!empty($sonUid)){
            $teamUplodeRewardWhere['uid'] = array('in',$sonUid);
        }
        $teamUplodeReward  =M('video_uplode_reward')->where($teamUplodeRewardWhere)->sum('price') ;
        $data['team_uplode_reward'] =$teamUplodeReward?$teamUplodeReward:0;

        $teamUplodeCountList  =M('video_uplode_reward')->where($teamUplodeRewardWhere)->field('uid')->group('uid')->select();
        $data['team_uplode_count'] = count($teamUplodeCountList);
        $data['video_uplode_reward_sql'] = M()->getLastSql();

        $cashrecordWhere = ['user_type'=>['in', $effect_user_types]];
        $cashrecordWhere['tenant_id'] = $tenant_id;
        $cashrecordWhere['addtime'] = array("between",array((strtotime( $start_time)),(strtotime($end_time))));
        if(!empty($sonUid)){
            $cashrecordWhere['uid'] = array('in',$sonUid);
        }
        $cashrecordWhere['status'] = array('eq',1);

        $cashAmount=M("users_cashrecord")->where($cashrecordWhere)->sum('rnb_money');
        $data['cash_amount'] = $cashAmount;

        $data['son_count'] = floatval($data['son_count']);
        $data['vip_margin'] = floatval($data['vip_margin']);
        $data['team_vip_margin'] = floatval($data['team_vip_margin']);
        $data['agent_video_count'] = floatval($data['agent_video_count']);
        $data['agent_amount'] = floatval($data['agent_amount']);
        $data['commission'] = floatval($data['commission']);
        $data['son_charge_amount'] = floatval($data['son_charge_amount']);
        $data['son_charge_count'] = floatval($data['son_charge_count']);
        $data['team_uplode_reward'] = floatval($data['team_uplode_reward']);

        $data['team_like_amount'] = floatval($data['team_like_amount']);
        $data['son_time_count'] = floatval($data['son_time_count']);
        $data['team_uplode_count'] = floatval($data['team_uplode_count']);
        $data['son_coin'] = floatval($data['son_coin']);
        $data['team_like_count'] = floatval($data['team_like_count']);
        $data['cash_amount'] = floatval($data['cash_amount']);

        $this->assign('data',$data);
        $this->assign('param', $param);
        $this->assign('tenant_list', getTenantList());
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }

    /*
     * 代理商列表
     * */
    public function agent_list(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $param['tenant_id'] = $tenant_id;
        $param['num'] = !isset($param['num']) ? 100 : $param['num'];
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map = array();
        if(isset($param['uid']) && $param['uid'] !=''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login'] !=''){
            $map['user_login'] = $param['user_login'];
        }

        if(empty($map)){
            $map['one_uid'] = 0;
        }
        $map['tenant_id'] = intval($tenant_id);
        $map['user_type'] = ['in',[0,2,5,6,7]];

        $model = M("users_agent");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = $model
            ->where($map)
            ->order("addtime asc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        // 如果是导出，则直接return源数据
        if(isset($_GET['action_type']) && $_GET['action_type'] == 'export'){
            return array('count'=>$count, 'list'=>$list);
        }
        $uids = count($list) > 0 ? array_column($list, 'uid', null) : [];
        $user_list = count($uids) > 0 ? M('users')->field('id,user_type,user_status,create_time')->where(['id'=>['in',$uids]])->select() : [];
        $user_list = count($user_list) > 0 ? array_column($user_list, null, 'id') : [];
        $agent_code_list = count($uids) > 0 ? M('users_agent_code')->field('uid,code')->where(['uid'=>['in',$uids]])->select() : [];
        $agent_code_list = count($agent_code_list) > 0 ? array_column($agent_code_list, null, 'uid') : [];
        $chil_count_list = count($uids) > 0 ? $model->field('one_uid, count(one_uid) as child_count')->where(['one_uid'=>['in', $uids]])->group('one_uid')->select() : [];
        $chil_count_list =  count($chil_count_list) > 0 ? array_column($chil_count_list, null, 'one_uid') : [];
        $user_status_list = user_status_list();
        $user_type_list = user_type_list();
        foreach ($list as $key=>$val){
            $user_info = isset($user_list[$val['uid']]) ? $user_list[$val['uid']] : [];
            if($val['user_type'] == 0 && isset($user_info['user_type']) && $user_info['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$user_info['user_type']]);
            }
            if(isset($user_info['create_time']) && $user_info['create_time'] && $val['addtime'] != strtotime($user_info['create_time'])){
                $model->where(['id'=>$val['id']])->save(['addtime'=>strtotime($user_info['create_time'])]);
            }
            $user_info['user_status'] = isset($user_info['user_status']) ? $user_info['user_status'] : '';
            $user_info['user_type'] = isset($user_info['user_type']) ? $user_info['user_type'] : '';
            $list[$key]['status_name'] = isset($user_status_list[$user_info['user_status']]) ? $user_status_list[$user_info['user_status']]['name'] : $user_info['user_status'];
            $list[$key]['user_type_name'] = isset($user_type_list[$user_info['user_type']]) ? $user_type_list[$user_info['user_type']]['name'] : $user_info['user_type'];
            $list[$key]['invitation_code'] = isset($agent_code_list[$val['uid']]) ? $agent_code_list[$val['uid']]['code'] : '';
            $list[$key]['child_count'] = isset($chil_count_list[$val['uid']]) ? $chil_count_list[$val['uid']]['child_count'] : 0;
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
        $this->display();
    }

    /*
    * 查看上下级列表
    * */
    public function agent_parent_child(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $map['tenant_id'] = intval($tenant_id);
        $self_map['tenant_id'] = intval($tenant_id);

        $param['tenant_id'] = $tenant_id;
        $param['num'] = !isset($param['num']) ? 100 : $param['num'];
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        if($param['view_type'] == 'parent'){
            $self_map['uid'] = $param['puid'];
            $map['uid'] = $param['uid'];
        }
        if($param['view_type'] == 'child'){
            $self_map['uid'] = $param['uid'];
            $map['one_uid'] = $param['uid'];
        }
        $map['user_type'] = ['in',[0,2,5,6,7,8]];
        $self_map['user_type'] = ['in',[0,2,5,6,7,8]];

        $model = M("users_agent");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = $model
            ->where($map)
            ->order("addtime asc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach ($list as $key=>$val){
            $self_data['is_parent'] = 0;
        }

        if(isset($self_map['uid']) && $self_map['uid'] > 0){
            $self_data = $model->where($self_map)->find();
            if($self_data){
                $self_data['is_parent'] = 1;
                array_unshift($list, $self_data);
            }
        }

        // 如果是导出，则直接return源数据
        if(isset($_GET['action_type']) && $_GET['action_type'] == 'export'){
            return array('count'=>$count, 'list'=>$list);
        }

        $uids = count($list) > 0 ? array_column($list, 'uid', null) : [];
        $user_list = count($uids) > 0 ? M('users')->field('id,user_type,user_status,create_time')->where(['id'=>['in',$uids]])->select() : [];
        $user_list = count($user_list) > 0 ? array_column($user_list, null, 'id') : [];
        $agent_code_list = count($uids) > 0 ? M('users_agent_code')->field('uid,code')->where(['uid'=>['in',$uids]])->select() : [];
        $agent_code_list = count($agent_code_list) > 0 ? array_column($agent_code_list, null, 'uid') : [];
        $chil_count_list = count($uids) > 0 ? $model->field('one_uid, count(one_uid) as child_count')->where(['one_uid'=>['in', $uids]])->group('one_uid')->select() : [];
        $chil_count_list =  count($chil_count_list) > 0 ? array_column($chil_count_list, null, 'one_uid') : [];

        $user_status_list = user_status_list();
        $user_type_list = user_type_list();
        foreach ($list as $key=>$val){
            $user_info = isset($user_list[$val['uid']]) ? $user_list[$val['uid']] : [];
            if($val['user_type'] == 0 && isset($user_info['user_type']) && $user_info['user_type']){
                $model->where(['id'=>$val['id']])->save(['user_type'=>$user_info['user_type']]);
            }
            if(isset($user_info['create_time']) && $user_info['create_time'] && $val['addtime'] != strtotime($user_info['create_time'])){
                $model->where(['id'=>$val['id']])->save(['addtime'=>strtotime($user_info['create_time'])]);
            }
            $user_info['user_status'] = isset($user_info['user_status']) ? $user_info['user_status'] : '';
            $user_info['user_type'] = isset($user_info['user_type']) ? $user_info['user_type'] : '';
            $list[$key]['status_name'] = isset($user_status_list[$user_info['user_status']]) ? $user_status_list[$user_info['user_status']]['name'] : $user_info['user_status'];
            $list[$key]['user_type_name'] = isset($user_type_list[$user_info['user_type']]) ? $user_type_list[$user_info['user_type']]['name'] : $user_info['user_type'];
            $list[$key]['invitation_code'] = isset($agent_code_list[$val['uid']]) ? $agent_code_list[$val['uid']]['code'] : '';
            $list[$key]['child_count'] = isset($chil_count_list[$val['uid']]) ? $chil_count_list[$val['uid']]['child_count'] : 0;
        }
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $this->assign('list', $list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->assign('auth_access_json', json_encode(getAuthAccessList(getRoleId())));
        $this->assign('has_child',1);
        $this->display();
    }

    /**
     * 约会这边的代理
     */
    public function manage(){
        $param = I("post.");
        $amindUser = M("users") ->where(['id'=>$_SESSION['ADMIN_ID'] ])->find();
        if ($amindUser['user_type'] == 8) {
            $map = ['game_tenant_id' => 106, 'user_type' => ['in', [2, 3, 5, 6, 7]], 'pid' => $amindUser['id']];
        }else{
            $map = ['game_tenant_id' => 106, 'user_type' => 8];
        }

        $todayStartTime = strtotime(date('Y-m-d:00:00:00'));
        $todayEndTime = strtotime(date('Y-m-d 23:59:59'));
        $mapLow['create_time'] = array("between", array(date('Y-m-d:00:00:00'),date('Y-m-d 23:59:59')));
        if(isset($_REQUEST['start_time']) && $_REQUEST['start_time'] != ''){
            $mapLow['create_time'] = array("egt", $_REQUEST['start_time']);
            $_GET['start_time'] = $_REQUEST['start_time'];
            $todayStartTime = strtotime($_REQUEST['start_time']);
        }
        if(isset($_REQUEST['end_time']) && $_REQUEST['end_time']!=''){
            $mapLow['create_time'] = array("elt", $_REQUEST['end_time']);
            $_GET['end_time'] = $_REQUEST['end_time'];
            $todayEndTime = strtotime($_REQUEST['end_time']);
        }

        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $mapLow['create_time'] = array("between", array($param['start_time'], $param['end_time']));
        }

        if($_REQUEST['uid']){
            $_GET['uid'] = $_REQUEST['uid'];
            $map['id'] =  $_REQUEST['uid'];
        }
        if($_REQUEST['user_login']){
            $_GET['user_login'] = $_REQUEST['user_login'];
            $map['user_login'] =  $_REQUEST['user_login'];
        }

        $count=M("users")->where($map)->count();
        $page = $this->page($count, 20);
        $lists  = M("users")->where($map)->field('id,user_login,pid,coin,create_time,user_status')	->limit($page->firstRow . ',' . $page->listRows)->select(); // 代理下的用户

        if ($lists){
            $topUserId = array_column($lists,'id'); // 一级用户的id
          //  $topUserListById  = array_column($lists,null,'id');
            $userCode  =  M('users_agent_code')->where(['uid'=>['in',$topUserId]])->select();
            $userCodeById  = array_column($userCode,null,'uid'); //  一级用户邀请码
            $lowUser  = M("users")->where(['pid'=>['in',$topUserId]])->field('id,pid,coin,create_time')->where($mapLow)->select(); //下级用户
            $lowUserById  =  array_column($lowUser,null,'id');
            $pUserbyId = [];
            foreach ($lowUser as  $lowKey => $lowValue){  // 根据上级用户 分配对应下级用户数组的id
                $pUserbyId[$lowValue['pid']][] = $lowValue['id'];
            }

            foreach ($pUserbyId as $puserKey  => $puserValue){  // $puserKey 为 上级用户id   $puserValue 为 下级用户数组
                $pUserbyId[$puserKey]['register_count'] = count($puserValue);
                $pUserbyId[$puserKey]['turntable_count'] = count(M('users_lottery')->where(['uid'=> ['in',$puserValue],'addtime'=>['between',[$todayStartTime,$todayEndTime]]])->group('uid')->count());
                $pUserbyId[$puserKey]['access_count'] = count(M('access_log')->where(['uid'=> ['in',$puserValue],'addtime'=>['between',[$todayStartTime,$todayEndTime]]])->group('uid')->count());
                $pUserbyId[$puserKey]['today_register_count'] = 0;
               // $pUserbyId[$puserKey]['user_login'] = $topUserListById[$puserKey]['user_login'];
                foreach ($puserValue as $lowUserValue ){
                    if (strtotime($lowUserById[$lowUserValue]['create_time']) >= $todayStartTime && strtotime($lowUserById[$lowUserValue]['create_time']) <=$todayEndTime  ){
                        $pUserbyId[$puserKey]['today_register_count']++;
                    }
                }
            }
            foreach ($lists as $key => $value){
                $lists[$key]['register_count'] = isset($pUserbyId[$value['id']]['register_count']) ? $pUserbyId[$value['id']]['register_count'] : 0;
                $lists[$key]['turntable_count'] = isset( $pUserbyId[$value['id']]['turntable_count']) ? $pUserbyId[$value['id']]['turntable_count'] : 0;
                $lists[$key]['access_count'] = isset($pUserbyId[$value['id']]['access_count']) ? $pUserbyId[$value['id']]['access_count'] : 0;
                $lists[$key]['today_register_count'] = isset($pUserbyId[$value['id']]['today_register_count']) ? $pUserbyId[$value['id']]['today_register_count'] : 0;
                $lists[$key]['code']  = $userCodeById[$value['id']]['code'];
                $lists[$key]['level'] = 1;

            }
        }

        $this->assign('lists', $lists);
        $this->assign('param', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /**
     *  约会这边的下级
     */
    public function  lower_level(){
        $amindUser = M("users") ->where(['id'=>$_SESSION['ADMIN_ID'] ])->find();


        if($_REQUEST['id']){

            $map['pid'] =  $_REQUEST['id'];
        }
        $count=M("users")->where($map)->count();
        $page = $this->page($count, 20);
        $lists  = M("users")->where($map)->field('id,user_login,pid,coin,create_time,user_status')	->limit($page->firstRow . ',' . $page->listRows)->select(); // 代理下的用户
        if ($lists){
            $topUserId = array_column($lists,'id'); // 一级用户的id
            //  $topUserListById  = array_column($lists,null,'id');
            $userCode  =  M('users_agent_code')->where(['uid'=>['in',$topUserId]])->select();
            $userCodeById  = array_column($userCode,null,'uid'); //  一级用户邀请码
            $lowUser  = M("users")->where(['pid'=>['in',$topUserId]])->field('id,pid,coin,create_time')->select(); //下级用户
            $lowUserById  =  array_column($lowUser,null,'id');
            $pUserbyId = [];
            foreach ($lowUser as  $lowKey => $lowValue){  // 根据上级用户 分配对应下级用户数组的id
                $pUserbyId[$lowValue['pid']][] = $lowValue['id'];
            }
            $todayTime = strtotime(date('Y-m-d'));
            foreach ($pUserbyId as $puserKey  => $puserValue){  // $puserKey 为 上级用户id   $puserValue 为 下级用户数组
                $pUserbyId[$puserKey]['register_count'] = count($puserValue);
                $pUserbyId[$puserKey]['turntable_count'] = count(M('users_lottery')->where(['uid'=> ['in',$puserValue]])->group('uid')->count());
                $pUserbyId[$puserKey]['access_count'] = count(M('access_log')->where(['uid'=> ['in',$puserValue],'addtime'=>['egt',$todayTime]])->group('uid')->count());
                $pUserbyId[$puserKey]['today_register_count'] = 0;
                // $pUserbyId[$puserKey]['user_login'] = $topUserListById[$puserKey]['user_login'];
                foreach ($puserValue as $lowUserValue ){
                    if (strtotime($lowUserById[$lowUserValue]['create_time']) >$todayTime ){
                        $pUserbyId[$puserKey]['today_register_count']++;
                    }
                }
            }
            foreach ($lists as $key => $value){
                $lists[$key]['register_count'] = isset($pUserbyId[$value['id']]['register_count']) ? $pUserbyId[$value['id']]['register_count'] : 0;
                $lists[$key]['turntable_count'] = isset( $pUserbyId[$value['id']]['turntable_count']) ? $pUserbyId[$value['id']]['turntable_count'] : 0;
                $lists[$key]['access_count'] = isset($pUserbyId[$value['id']]['access_count']) ? $pUserbyId[$value['id']]['access_count'] : 0;
                $lists[$key]['today_register_count'] = isset($pUserbyId[$value['id']]['today_register_count']) ? $pUserbyId[$value['id']]['today_register_count'] : 0;
                $lists[$key]['code']  = $userCodeById[$value['id']]['code'];
                $lists[$key]['level'] = 1;
            }
        }

        $this->assign('lists', $lists);
        /*$this->assign('formget', $_GET);*/
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }


    public function agent_account(){
        $count=M('users')->where(array("user_type"=>8))->count();
        $page = $this->page($count, 20);
        $users = M('users')
            ->where(array("user_type"=> 8))
            ->order("create_time DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $role_user_list = M("role_user")->select();

        $roles_src=M('role')->select();
        $roles=array();
        foreach ($roles_src as $r){
            $roleid=$r['id'];
            $roles["$roleid"]=$r;
        }
        foreach ($users as $k=>$u){
            if(!empty($u['tenant_id'])){
                $tenantInfo=getTenantInfo($u['tenant_id']);
                if(!empty($tenantInfo)){
                    $users[$k]['tenant_name']=$tenantInfo['name'];
                }
            }
            $role_name = '';
            foreach ($role_user_list as $key=>$val){
                if($val['user_id'] == $u['id']){
                    $role_name = $roles[$val['role_id']]['name'];
                }
            }
            $users[$k]['role_name'] = $role_name;
            $users[$k]['code'] =  M('users_agent_code')->where(['uid'=>$u['id']])->find()['code'];
        }
        $this->assign("page", $page->show('Admin'));
        $this->assign("roles",$roles);
        $this->assign("users",$users);
        $this->display();
    }

    public function agent_account_add(){


        $roles=M('role')->where("status=1")->order("id desc")->select();
        $this->assign("roles",$roles);

        if(IS_POST){
            try{
                $param = I("post.");
                if(mb_strlen($param['user_nicename']) < 2 || mb_strlen($param['user_nicename']) > 32){
                    $this->error("昵称长度不合法，长度范围：2 - 32");
                }
                if(!$param['user_login']){
                    $this->error("请输入用户名");
                }
                if(!$param['user_pass']){
                    $this->error("请输入密码");
                }

                if(mb_strlen($param['user_pass']) < 6){
                    $this->error("密码长度不能小于6位");
                }

                $tenant = getTenantIds();
                $game_tenant_id = getGameTenantIds();
                $userinfo = M("users")->where(['user_login'=>$param['user_login']])->find();
                if ($userinfo){
                    $this->error("该账号已存在");
                }

                if(!empty($param['role_id']) && is_array($param['role_id'])){
                    $role_ids=$param['role_id'];
                    unset($param['role_id']);
                    if (M('users')->create()) {
                        M('users')->tenant_id=$tenant;
                        M('users')->user_type= 8;
                        M('users')->game_tenant_id=$game_tenant_id;
                        M('users')->act_uid = get_current_admin_id();
                        M('users')->create_time =  date('Y-m-d H:i:s');
                        $result=M('users')->add();
                        $uid = M('users')->getLastInsID();
                        if ($result!==false) {
                            $role_user_model=M("RoleUser");
                            $role_id = isset($role_ids[0])?$role_ids[0]:1;//是否选择角色，没有默认超级管理员
                            $role_user_model->add(array("role_id"=>$role_id,"user_id"=>$uid));

                            $code=createCode();
                            $code_info=array('uid'=>$uid,'code'=>$code,'tenant_id'=>$tenant['id']);
                            $isexist= M('users_agent_code')->where(['uid'=>$uid])-> find();
                            if($isexist){
                                M('users_agent_code')->where(['uid'=>$uid])->save($code_info);
                            }else{
                                M('users_agent_code')->add($code_info);
                            }
                            $action="添加代理账号：{$uid}";
                            setAdminLog($action);
                            $this->success("添加成功！", U("agent/agent_account"));
                        } else {
                            $this->error("添加失败！");
                        }
                    } else {
                        $this->error('请选择角色');
                    }
                }else{
                    $this->error("请为此用户指定角色！");
                }
            }catch (\Exception $e){
                $msg = $e->getMessage();
                setAdminLog('添加代理账号失败: '.$msg);
                $this->error("操作失败");
            }
        }

        $this->display();
    }

    public function agent_account_edit(){
        $id= intval(I("get.id"));
        $roles=M('role')->where("status=1")->order("id desc")->select();
        $role_user_model=M("RoleUser");
        $role_ids=$role_user_model->where(array("user_id"=>$id))->getField("role_id",true);
        $info = M('users')->where(array("id"=>$id))->find();
        if (IS_POST) {
           try{
                $param = I("post.");
                if(empty($param['role_id']) || !is_array($param['role_id'])){
                    $this->error('请选择角色');
                }
                if(!$param['id']){
                    $this->error('缺少参数');
                }
                $tenant = getTenantIds();
                $game_tenant_id = getGameTenantIds();
                if(mb_strlen($param['user_nicename']) < 2 || mb_strlen($param['user_nicename']) > 32){
                    $this->error("昵称长度不合法，长度范围：2 - 32");
                }


                $data = array(
                    'user_nicename' => $param['user_nicename'],
                    'tenant_id' => $tenant,
                    'game_tenant_id' => $game_tenant_id,
                    'act_uid' => get_current_admin_id(),
                    'mtime' => time(),
                );
                if($param['user_pass']){
                    if(mb_strlen($param['user_pass']) < 6){
                        $this->error("密码长度不能小于6位");
                    }
                    $data['user_pass'] = setPass($param['user_pass']);
                }
                $up_res = M('users')->where(['id'=>intval($param['id'])])->save($data);
                if ($up_res!==false) {
                    $role_ids=$param['role_id'];
                    $uid=intval($param['id']);
                    $role_user_model=M("RoleUser");
                    $role_user_model->where(array("user_id"=>$uid))->delete();
                    $role_id = isset($role_ids[0])?$role_ids[0]:1;//是否选择角色，没有默认超级管理员
                    $role_user_model->add(array("role_id"=>$role_id,"user_id"=>$uid));
                    $isexist= M('users_agent_code')->where(['uid'=>$uid])-> find();
                    if(!$isexist){
                        $code=createCode();
                        $code_info=array('uid'=>$uid,'code'=>$code,'tenant_id'=>$tenant['id']);
                        M('users_agent_code')->add($code_info);
                    }


                    $action="修改后台账号：{$uid}";
                    setAdminLog($action);
                    $this->success("操作成功",U('agent_account'));
                } else {
                    $this->error("操作失败");
                }
           }catch (\Exception $e){
                $msg = $e->getMessage();
                setAdminLog('修改后台账号失败: '.$msg);
                $this->error("操作失败");
            }
        }
        $this->assign("roles",$roles);
        $this->assign("role_ids",$role_ids);
        $this->assign('info',$info);
        $this->display();
    }
		
}
