<?php
namespace Api\Controller;
use Think\Controller;
use Api\Controller\ProfitShareController as ApiProfitShare;
use function MongoDB\BSON\toJSON;


class OpenApiController extends Controller {

    public function return_json($code,$msg='',$info=''){
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode(['code'=>$code,'msg'=>$msg,'info'=>$info]);
    }

    public function getComplexSummary(){
        $redis = connectionRedis();
        $tenant_id = I('param.tenant_id');
        $type = I('param.type');
        $ctime = I('param.ctime');
        if(!$tenant_id){
            return $this->return_json(400,'缺少参数tenant_id');
        }
        if(!$type){
            return $this->return_json(400,'缺少参数type');
        }
        if(!$ctime && $type == 1){
            return $this->return_json(400,'缺少参数ctime');
        }

        $tenantInfo=M("tenant")->where("status='1' and game_tenant_id='$tenant_id' ")->find();
        if(!$tenantInfo){
            return $this->return_json(400,'租户不存在');
        }

        $tenant_id = $tenantInfo['id'];

        $timeselect = get_timeselect(); // 获取时间格式

        $live_now_count_map = ['islive'=>1];

        $map['tenant_id'] = $tenant_id;
        $live_now_count_map['tenant_id'] = $tenant_id;
        $cs_map['tenant_id'] = $tenant_id;

        $map['starttime'] = array('between',[strtotime($ctime.' 00:00:00'),strtotime($ctime.' 23:59:59')]);

        if($type == 2){
            $map['starttime'] = array('between',[strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])]);
        }
        if($type == 3){
            $map['starttime'] = array('between',[strtotime($timeselect['yweek_start']),strtotime($timeselect['yweek_end'])]);
        }
        if($type == 4){
            $map['starttime'] = array('between',[strtotime($timeselect['tmonth_start']),strtotime($timeselect['tmonth_end'])]);
        }
        if($type == 5){
            $map['starttime'] = array('between',[strtotime($timeselect['ymonth_start']),strtotime($timeselect['ymonth_end'])]);
        }

        $data['live_count'] = M('users_liverecord')->where($map)->count() + M('users_live')->where($map)->count(); // 开播直播间总数
        $data['live_now_count'] = M('users_live')->where($live_now_count_map)->count(); // 在线直播间总数
        $data['watching_num'] = 0; // 在线观看人数，初始化为 0

        if(isset($map['tenant_id'])){
            $data['watching_num'] += $redis->zCard('watching_num'.$map['tenant_id']);
        }

        $is_this_dwm = false; // 时间是否今天、本周、本月
        $ApiProfitShare = new ApiProfitShare();

        // 日汇总
        if($type == 1){
            if($ctime==date('Y-m-d')){
                $resdata = $ApiProfitShare->complex_summary_this_day($tenant_id);
                $data = array_merge($data,$resdata);
                $is_this_dwm = true;
            }else{
                $cs_map['collet_day_time'] = strtotime($ctime);
            }
        }
        // 本周
        if($type == 2){
            $resdata = $ApiProfitShare->complex_summary_this_week($tenant_id);
            $data = array_merge($data,$resdata);
            $is_this_dwm = true;
        }else if($type == 3){ // 上周
            $cs_map['collet_week_time'] = strtotime($timeselect['yweek_start']);
        }
        // 本月
        if($type == 4){
            $resdata = $ApiProfitShare->complex_summary_this_month($tenant_id);
            $data = array_merge($data,$resdata);
            $is_this_dwm = true;
        }else if($type == 5){ // 上月
            $cs_map['collet_month_time'] = strtotime($timeselect['ymonth_start']);
        }

        // 不是今天、上周、上月的数据查询
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


        $data['live_totalam'] = $data['timecharge_am'] + $data['roomcharge_am'] + $data['sendbarrage_am'] + $data['sendgift_am'] + $data['buycar_am'];
        $data['live_pa_totalam'] = $data['pa_roomcharge_am'] + $data['pa_sendbarrage_am'] + $data['pa_sendgift_am'] + $data['pa_bet_am'];
        $data['live_pf_totalam'] = $data['pf_roomcharge_am'] + $data['pf_sendbarrage_am'] + $data['pf_sendgift_am'] + $data['pf_bet_am'];

        return $this->return_json(200,'success',$data);
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
