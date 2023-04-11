<?php

/**
 * 消费记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class CoinrecordController extends AdminbaseController {

    public function get_type(){
        return array("income"=>"收入","expend"=>"支出","inccoding"=>"增加打码量","redcoding"=>"减少打码量",'income_nowithdraw'=>'收入(不可提现金额)','move'=>'转化不可提现金额到可提现金额');
    }

    public function get_action(){
        return array(
            "sendgift"=>"赠送礼物",
            "getgift"=>"打赏收入",
            "getbet"=>"投注收入",
            "sendbarrage"=>"弹幕",
            "loginbonus"=>"登录奖励",
            "buyvip"=>"缴纳保证金",
            'vip_refund' =>  '退回保证金',
            "buycar"=>"购买坐骑",
            "buyliang"=>"购买靓号",
            'game_bet'=>'游戏下注',
            'game_return'=>'游戏退还',
            'game_win'=>'游戏获胜',
            'game_banker'=>'庄家收益',
            'set_deposit'=>'上庄扣除',
            'deposit_return'=>'下庄退还',
            'roomcharge'=>'房间扣费',
            'timecharge'=>'计时扣费',
            'sendred'=>'发送红包',
            'robred'=>'抢红包',
            'returnred'=>'红包退回',
            'buyguard'=>'开通守护',
            'reg_reward'=>'注册奖励',
            'bet'=>'彩票投注',
            'task'=>'任务奖励',
            'taskcommission'=>'任务佣金奖励',
            'returntaskcommission'=>'任务佣金奖励退回',
            'buytask'=>'领取任务',
            'taskprice'=>'任务价格扣费',
            'returntaskprice'=>'任务价格扣费退回',
            'buytaskclass'=>'任务分类解锁金额',
            'firstlogin_reword'=>'登录奖励-首次登录',
            'otherlogin_reword'=>'登录奖励-非首次登录',
            'firstrecharge'=>'首充奖励',
            'share_firstrecharge'=>'推荐首充奖励',
            'agent_rebate'=>'代理返佣',
            'buy_video'=>'视频付费',
            'like_video_rebate'=>'点赞收益',
            'buy_shot_video'=>'视频收益',
            'buyNoble' => '开通贵族',
            'renewalNoble' => '续费贵族',
            'buyNobleHandsel' => '开通贵族赠送',
            'renewalNobleHandsel' => '续费贵族赠送',
            'bar' => '求片',
            'bar_return' => '求片驳回',
            'charge_withdrawn' =>'不可提现转提现',
            'video_uplode_reward' => '上传视频收益',
            'agent_video_uplode_reward'=> '上传视频代理收益',
            'agent_buy_video'=> '购买视频代理收益',
            'agent_likes_video' => '点赞视频代理收益',
            'vip_upgrade_refund' => '升级保证金',
            'transfer_add' =>  '用户转入',
            'transfer_sub' =>  '用户转出',
            "redpackge" => "红包收入",
            "offline_charge" => "线下入款",
            "offline_virtual_charge" => "线下虚拟币入款",
            "online_charge" => "线上入款",
            "manual_charge" => "手动充值",
//            "withdrawn_success" => "提现成功",
            "withdrawn_apply" => "申请提现",
            "withdrawn_reject" => "拒绝提现",
            "yuebaoout_coin" => "米利宝转出到余额",
            "yuebaoout_bank" => "米利宝转出到银行卡",
            "yuebaoin_coin"  => "余额转出到米利宝",
            "yuebaoin_bank"  => "银行卡转出到米利宝",
            "yuebao_rate"  => "米利宝利息",
            "friend_consumption_award" => '好友消费奖励',
            'turntable_lottery' => '转盘抽奖奖励',
            'sign' => '签到',
            'nft_add'=>'NFT收入增加',
            'nft_reduce'=>'NFT收入减去',
            'shop_add'=>'退款收入增加',
            'shop_reduce'=>'商城收入减去',
            'shopuser_add'=>'确认收货增加余额',
            'shopuser_reduce'=>'店铺支付扣款',
            'shop_bondpay'=>'缴纳保证金',
            'trading_profit_rebate'=>'卖出商品利润返佣',
            'invite_award'=>'邀请好友',
            'turntable_consumption'=> '转盘抽奖消费',
            'lottery_add'=>'彩票收入增加',
            'lottery_reduce'=>'彩票收入减去',
            'manual_shop_margin'=>'商城保证金',
            'buy_longvideovip'=>'购买长视频等级',
            'charge'=>'充值',
            'agent_reward'=>'代理人奖励',
        );
    }

    public function index(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());

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

        //$param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $timeselect = get_timeselect(); // 获取时间格式
        $param['time_type'] = isset($param['time_type']) ? $param['time_type'] : '';
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }else{
            $param['start_time'] = explode(' ', $timeselect['tweek_start'])[0];
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }else{
            $param['end_time'] = explode(' ', $timeselect['tweek_end'])[0];
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if((!isset($param['start_time']) && !isset($param['start_time'])) || ($param['start_time'] == '' && $param['end_time'] == '')){
            $map['addtime'] = array("between", array(strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])+86399));
            $param['start_time'] = explode(' ', $timeselect['tweek_start'])[0];;
            $param['end_time'] = explode(' ', $timeselect['tweek_end'])[0];
        }

        if(isset($param['type']) && $param['type'] != '-1'){
            $map['type'] = $param['type'];
        }
        if(isset($param['action']) && $param['action'] != '-1'){
            $map['action'] = $param['action'];
        }
        if(isset($param['user_type'])){
            if($param['user_type'] != '-1'){
                $map['user_type'] = $param['user_type'];
            }
        }else{
            $map['user_type'] = 2;
            $param['user_type'] = 2;
        }
        if(isset($param['uid']) && $param['uid'] != ''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login'] != ''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['touid']) && $param['touid'] != ''){
            $map['touid'] = $param['touid'];
        }

    	$model = M("users_coinrecord");
		$Game=M("game");
		$Gift=M("gift");
		$Car=M("car");
		$Liang=M("liang");
		$Guard=M("guard");
		$game_action=array(
			'0'=>'',
			'1'=>'智勇三张',
			'2'=>'海盗船长',
			'3'=>'转盘',
			'4'=>'开心牛仔',
			'5'=>'二八贝',);
		
    	$count = $model->where($map)->count();
    	$page = $this->page($count, $page_size);
    	$lists = $model
            ->where($map)
            ->order("addtime DESC,id DESC")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        // 如果是导出，则直接return源数据
        if(isset($_GET['action_type']) && $_GET['action_type'] == 'export'){
            return array('count'=>$count, 'list'=>$lists);
        }

        $change_total_balance = $model->field('sum(totalcoin) as change_total_balance')->where($map)->find();
        $data['change_total_balance'] = $change_total_balance ? $change_total_balance['change_total_balance'] : 0;
        $data['current_p_change_balance'] = 0;

        $type_list = $this->get_type();
        $action_list = $this->get_action();
        foreach($lists as $k=>$v){
            $userinfo = getUserInfo($v['uid']);
            if($v['user_type'] == 0 && $userinfo['user_type']){
                $model->where(['id'=>$v['id']])->save(['user_type'=>$userinfo['user_type']]);
            }
            if($v['user_login'] == '' && $userinfo['user_login']){
                $model->where(['id'=>$v['id']])->save(['user_login'=>$userinfo['user_login']]);
            }
            $touserinfo = getUserInfo($v['touid']);
            if($v['to_user_login'] == '' && $touserinfo['user_login']){
                $model->where(['id'=>$v['id']])->save(['to_user_login'=>$touserinfo['user_login']]);
            }
            $action=$v['action'];
            if($action=='sendgift'){
                $giftinfo=$Gift->field("giftname")->where("id='$v[giftid]'")->find();
                $lists[$k]['giftinfo']= $giftinfo;
            }
            else if($action=='getgift'){
                $giftinfo=$Gift->field("giftname")->where("id='$v[giftid]'")->find();
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='loginbonus'){
                $giftinfo['giftname']='第'.$v['giftid'].'天';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='sendbarrage'){
                $giftinfo['giftname']='弹幕';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='game_bet' || $action=='game_return' || $action=='game_win' || $action=='game_brokerage' || $action=='game_banker'){
                $info=$Game->field('action')->where("id={$v['giftid']}")->find();
                $giftinfo['giftname']=$game_action[$info['action']];
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='set_deposit'){
                $giftinfo['giftname']='上庄扣除';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='deposit_return'){
                $giftinfo['giftname']='下庄退还';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='roomcharge'){
                $giftinfo['giftname']='房间扣费';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='timecharge'){
                $giftinfo['giftname']='计时扣费';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='buyvip'){
                $giftinfo['giftname']='购买vip';
             ;
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='vip_refund'){
                $giftinfo['giftname']='退回vip';
                ;
                $lists[$k]['giftinfo']= $giftinfo;
            }
            else if($action=='buycar'){
                $info=$Car->field("name")->where("id='".$v['giftid']."'")->find();
                $giftinfo['giftname']=$info['name'];
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='buyliang'){
                $info=$Liang->field("name")->where("id='".$v['giftid']."'")->find();
                $giftinfo['giftname']=$info['name'];
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='sendred'){
                $giftinfo['giftname']='发送红包';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='robred'){
                $giftinfo['giftname']='抢红包';
                $lists[$k]['giftinfo']= $giftinfo;
            } else if($action=='returnred'){
                $giftinfo['giftname']='红包退回';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='buyguard'){
                $info=$Guard->field("name")->where("id='".$v['giftid']."'")->find();
                $giftinfo['giftname']=$info['name'];
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='reg_reward'){
                $giftinfo['giftname']='注册奖励';
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='like_video_rebate'){
                $gradeInfo = '点赞收益';
                $giftinfo['giftname']=$gradeInfo;
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='buy_shot_video'){
                $gradeInfo = '购买视频';
                $giftinfo['giftname']=$gradeInfo;
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='video_uplode_reward'){
                $gradeInfo = '上传视频收益';
                $giftinfo['giftname']=$gradeInfo;
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='agent_video_uplode_reward'){
                $gradeInfo = '上传视频代理收益';
                $giftinfo['giftname']=$gradeInfo;
                $lists[$k]['giftinfo']= $giftinfo;
            } else if($action=='charge_withdrawn'){
                $gradeInfo = '不可提现转提现';
                $giftinfo['giftname']=$gradeInfo;
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='agent_video_uplode_reward'){
                $gradeInfo = '上传视频代理收益';
                $giftinfo['giftname']=$gradeInfo;
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='agent_buy_video'){
                $gradeInfo = '购买视频代理';
                $giftinfo['giftname']=$gradeInfo;
                $lists[$k]['giftinfo']= $giftinfo;
            }else if($action=='agent_likes_video'){
                $gradeInfo = '点赞视频代理收益';
                $giftinfo['giftname']=$gradeInfo;
                $lists[$k]['giftinfo']= $giftinfo;
            } else if($action=='bar'){//$giftinfo['giftname']='求片';
                $gradeInfo['giftname'] = '发布悬赏';
                $lists[$k]['giftinfo']= $gradeInfo;
                if ($v['type']=='income'){
                    $gradeInfo['giftname'] = '获得悬赏';
                    $lists[$k]['giftinfo']= $gradeInfo;
                }
            } else if($action=='bar_return'){//$giftinfo['giftname']='求片';
                $gradeInfo['giftname'] = '驳回悬赏';
                $lists[$k]['giftinfo']= $gradeInfo;
            }else if($action=='redpackge'){
                $giftinfo['giftname']='红包收入';
                $lists[$k]['giftinfo']= $giftinfo;
            }else{
                $giftinfo['giftname']='未知';
                $lists[$k]['giftinfo']= $giftinfo;
            }
            $tenantInfo=getTenantInfo($v['tenant_id']);
            if(!empty($tenantInfo)){
                $lists[$k]['tenant_name']=$tenantInfo['name'];
            }
            $lists[$k]['type_name'] = isset($type_list[$v['type']]) ? $type_list[$v['type']] : $v['type'];
            $lists[$k]['action_name'] = isset($action_list[$v['action']]) ? $action_list[$v['action']] : $v['action'];
            $cd_ratio_arr = explode(':',$v['cd_ratio']);
            $lists[$k]['totalcoin'] = $cd_ratio_arr[1] > 0 ? floatval(round($v['totalcoin']/$cd_ratio_arr[1],4)) : floatval($v['totalcoin']);
            $lists[$k]['tenant_total'] = floatval($v['tenant_total']);
            $lists[$k]['family_total'] = floatval($v['family_total']);
            $lists[$k]['anthor_total'] = floatval($v['anthor_total']);
            $lists[$k]['after_balance'] = floatval($v['after_balance']);
            $data['current_p_change_balance'] += $v['totalcoin'];
        }
        $data['change_total_balance'] = floatval($data['change_total_balance']);
        $data['current_p_change_balance'] = floatval($data['current_p_change_balance']);

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('data', $data);
        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('type_list', $type_list);
        $this->assign('action_list', $action_list);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('user_type_list',user_type_list());
        $this->display();
    }
		
		function del(){
			 	$id=intval($_GET['id']);
            $tenantId=getTenantIds();
					if($id){
						$result=M("users_coinrecord")->where("id=%d and tenant_id=%d",$id,$tenantId)->delete();
							if($result){
									$this->success('删除成功');
							 }else{
									$this->error('删除失败');
							 }			
					}else{				
						$this->error('数据传入失败！');
					}								  
					$this->display();				
		}

    function export()
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

        $type_list = $this->get_type();
        $action = $this->get_action();

        $data = $this->index();
        $list = $data['list'];

        if(count($list) > 100000){ // 限制数量10万条导出
            echo '<script language="JavaScript">;alert("导出数据数量过大，数量不能大于10万条");</script>;';
            echo '<script language="JavaScript">;history.back();</script>';
            exit;
        }

        $uids = array();
        $tenantids = array();
        $giftids = array();
        foreach ($list as $key => $value){
            if($value['uid']){
                array_push($uids,$value['uid']);
            }
            if($value['touid']){
                array_push($uids,$value['touid']);
            }
            if($value['tenant_id']){
                array_push($tenantids,$value['tenant_id']);
            }
            if($value['giftid']){
                array_push($giftids,$value['giftid']);
            }
        }

        $uids = array_unique($uids);
        $user_list = count($uids) > 0 ? M("users")->field('id,user_login')->where(['id'=>['in',$uids]])->select() : [];
        $user_list = count($user_list) > 0 ? array_column($user_list,null,'id') : [];

        $tenantids = array_unique($tenantids);
        $tenant_list = count($tenantids) > 0 ? M("tenant")->field('id,name')->where(['id'=>['in',$tenantids]])->select() : [];
        $tenant_list = count($tenant_list) > 0 ? array_column($tenant_list,null,'id') : [];

        $giftids = array_unique($giftids);
        $gift_list = count($giftids) > 0 ? M("gift")->field('id,giftname')->where(['id'=>['in',$giftids]])->select() : [];
        $gift_list = count($gift_list) > 0 ? array_column($gift_list,null,'id') : [];

        try {
            $export_data=[];
            foreach($list as $key => $value){
                $u_user_login = $value['uid'] && isset($user_list[$value['uid']]) ? $user_list[$value['uid']]['user_login'] : '';
                $tou_user_login = $value['touid'] && isset($user_list[$value['touid']]) ? $user_list[$value['touid']]['user_login'] : '';
                $tenant_name = $value['tenant_id'] && isset($tenant_list[$value['tenant_id']]) ? $tenant_list[$value['tenant_id']]['name'] : '';
                $giftname = $value['giftid'] && isset($gift_list[$value['giftid']]) ? $gift_list[$value['giftid']]['giftname'] : '';
                $cd_ratio_arr = explode(':',$value['cd_ratio']);

                $temp['id'] = $value['id'];
                $temp['type'] = $type_list[$value['type']].'color:#090';
                $temp['action'] = isset($action[$value['action']]) ? $action[$value['action']] : $value['action'];
                $temp['giftname'] = $giftname;
                $temp['uid'] = $u_user_login." (".$value['uid'].")".'color:#090';
                $temp['touid'] = $tou_user_login." (".$value['touid'].")";
                $temp['playname'] = $tenant_name;
                $temp['giftcount'] = $value['giftcount'];
                $temp['totalcoin'] = $cd_ratio_arr[1] > 0 ? floatval(round($value['totalcoin']/$cd_ratio_arr[1],2)) : floatval($value['totalcoin']);
                $temp['after_balance'] = floatval($value['after_balance']);
                $temp['cd_ratio'] = $value['cd_ratio'];
                $temp['showid'] = $value['showid'];
                $temp['addtime'] = date("Y-m-d H:i:s",$value['addtime']);
                $temp['tenant_name'] = $value['tenant_name'];
                $temp['receive_tenant_id'] = $value['receive_tenant_id'];
                $temp['tenantuser_total'] = $value['tenantuser_total'];
                $temp['tenant_total'] = $value['tenant_total'];
                $temp['family_total'] = $value['family_total'];
                $temp['anthor_total'] = $value['anthor_total'];
                $temp['familyhead_total'] = $value['familyhead_total'];

                array_push($export_data, $temp);
            }
            $header=array(
                'title' => array(
                    'id'=>'ID:15',
                    'type'=>'收支类型:15',
                    'action'=>'收支行为:15',
                    'giftname'=>'行为说明:15',
                    'uid'=>'会员 (ID):20',
                    'touid'=>'主播(ID):20',
                    'playname'=>'投注类型:20',
                    'giftcount'=>'数量:15',
                    'totalcoin'=>'变动金额:15',
                    'after_balance'=>'变动后余额:15',
                    'cd_ratio'=>'金币与账号余额的比例:15',
                    'showid'=>'直播id:15',
                    'addtime'=>'时间:20',
                    'tenant_name'=>'会员所属租户:15',
                    'receive_tenant_id'=>'直播所属租户:15',
                    'tenantuser_total'=>'消费租户分成:15',
                    'tenant_total'=>'直播租户分成:15',
                    'family_total'=>'家族分成:15',
                    'anthor_total'=>'主播分成:15',
                    'familyhead_total'=>'家族长分成:15',
                ),
                'dataType' => array(
                    'order_id'=>'str',
                ),
            );
            $filename="资金记录";
            $return_url = count($export_data) > 10000 ? true : false;
            $excel_filname = $return_url == true ? $filename.'-'.md5(json_encode($param)) : $filename." (".count($export_data)."条)-".date('Y-m-d H-i-s');
            include EXTEND_PATH ."util/UtilPhpexcel.php";
            $Phpexcel = new \UtilPhpexcel();
            $downurl = "/".$Phpexcel::export_excel_v2($export_data, $header,$excel_filname, $return_url);
        }catch (\Exception $e){
            $msg = $e->getMessage();
            $this->error($msg);
        }

        if($downurl){
            $output_filename = $filename." (".count($export_data)."条)-".date('Y-m-d H-i-s');
            header('pragma:public');
            header("Content-Disposition:attachment;filename=".$output_filename.".xls"); //下载文件，filename 为文件名
            echo file_get_contents($downurl);
            exit;
        }

        $this->success("导出成功",$downurl);

    }
}
