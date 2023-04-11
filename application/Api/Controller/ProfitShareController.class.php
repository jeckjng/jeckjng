<?php
namespace Api\Controller;
use Think\Controller;


class ProfitShareController extends Controller {

    /*
    * 综合汇总表
    * 今天
    * */
    public function complex_summary_this_day($tenant_id){
        $redis = connectionRedis();
        $data = getcache('complex_summary_this_day'.$tenant_id);
        if(!$data){
            $timeselect = get_timeselect();
            $map_day_1 = array(
                'addtime' => ['between',[strtotime($timeselect['today_start']),strtotime($timeselect['today_end'])]],
                'type' => 'expend',
                'tenant_id' => $tenant_id,
            );
            $map_day_2 = array(
                'addtime' => ['between',[strtotime($timeselect['today_start']),strtotime($timeselect['today_end'])]],
                'type' => 'income',
                'tenant_id' => $tenant_id,
            );
            $time_arr = array('time_format'=>explode(' 00:00:00',$timeselect['today_start'])[0], 'time'=>strtotime($timeselect['today_start']));

            $LiveList = $this->getLiveList($map_day_1);
            $profitshare_anchor_list = $this->getLivePAList($map_day_2);
            $profitshare_family_list = $this->getLivePFList($map_day_2);
            $live_d = isset($LiveList[$tenant_id]) ? $LiveList[$tenant_id] : $this->getEmptyLiveData();
            $profitshare_anchor_d = isset($profitshare_anchor_list[$tenant_id]) ? $profitshare_anchor_list[$tenant_id] : $this->getEmptyPAData();
            $profitshare_family_d = isset($profitshare_family_list[$tenant_id]) ? $profitshare_family_list[$tenant_id] : $this->getEmptyPFData();
            $data = $this->getInsertData($tenant_id,$live_d,$profitshare_anchor_d,$profitshare_family_d,'day',$time_arr);

            $redis->set('complex_summary_this_day'.$tenant_id,json_encode($data),60); // redis 缓存，1分钟过期
        }
        return $data;
    }

    /*
    * 综合汇总表
    * 本周
    * */
    public function complex_summary_this_week($tenant_id){
        $redis = connectionRedis();
        $data = getcache('complex_summary_this_week'.$tenant_id);
        if(!$data){
            $timeselect = get_timeselect();
            $map_day_1 = array(
                'addtime' => ['between',[strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])]],
                'type' => 'expend',
                'tenant_id' => $tenant_id,
            );
            $map_day_2 = array(
                'addtime' => ['between',[strtotime($timeselect['tweek_start']),strtotime($timeselect['tweek_end'])]],
                'type' => 'income',
                'tenant_id' => $tenant_id,
            );
            $time_arr = array('time_format'=>explode(' 00:00:00',$timeselect['tweek_start'])[0], 'time'=>strtotime($timeselect['tweek_start']));

            $LiveList = $this->getLiveList($map_day_1);
            $profitshare_anchor_list = $this->getLivePAList($map_day_2);
            $profitshare_family_list = $this->getLivePFList($map_day_2);
            $live_d = isset($LiveList[$tenant_id]) ? $LiveList[$tenant_id] : $this->getEmptyLiveData();
            $profitshare_anchor_d = isset($profitshare_anchor_list[$tenant_id]) ? $profitshare_anchor_list[$tenant_id] : $this->getEmptyPAData();
            $profitshare_family_d = isset($profitshare_family_list[$tenant_id]) ? $profitshare_family_list[$tenant_id] : $this->getEmptyPFData();
            $data = $this->getInsertData($tenant_id,$live_d,$profitshare_anchor_d,$profitshare_family_d,'week',$time_arr);

            $redis->set('complex_summary_this_week'.$tenant_id,json_encode($data),60*20); // redis 缓存，20分钟过期
        }
        return $data;
    }

    /*
    * 综合汇总表
    * 本月
    * */
    public function complex_summary_this_month($tenant_id){
        $redis = connectionRedis();
        $data = getcache('complex_summary_this_month'.$tenant_id);
        if(!$data){
            $timeselect = get_timeselect();
            $map_day_1 = array(
                'addtime' => ['between',[strtotime($timeselect['tmonth_start']),strtotime($timeselect['tmonth_end'])]],
                'type' => 'expend',
                'tenant_id' => $tenant_id,
            );
            $map_day_2 = array(
                'addtime' => ['between',[strtotime($timeselect['tmonth_start']),strtotime($timeselect['tmonth_end'])]],
                'type' => 'income',
                'tenant_id' => $tenant_id,
            );
            $time_arr = array('time_format'=>explode(' 00:00:00',$timeselect['tmonth_start'])[0], 'time'=>strtotime($timeselect['tmonth_start']));

            $LiveList = $this->getLiveList($map_day_1);
            $profitshare_anchor_list = $this->getLivePAList($map_day_2);
            $profitshare_family_list = $this->getLivePFList($map_day_2);
            $live_d = isset($LiveList[$tenant_id]) ? $LiveList[$tenant_id] : $this->getEmptyLiveData();
            $profitshare_anchor_d = isset($profitshare_anchor_list[$tenant_id]) ? $profitshare_anchor_list[$tenant_id] : $this->getEmptyPAData();
            $profitshare_family_d = isset($profitshare_family_list[$tenant_id]) ? $profitshare_family_list[$tenant_id] : $this->getEmptyPFData();
            $data = $this->getInsertData($tenant_id,$live_d,$profitshare_anchor_d,$profitshare_family_d,'month',$time_arr);

            $redis->set('complex_summary_this_month'.$tenant_id,json_encode($data),60*60); // redis 缓存，1小时过期
        }
        return $data;
    }


    /*
     * 综合汇总表
     * 定时生成数据写入数据库
     * 昨天、上周、上月
     * */
    public function complex_summary_last(){
        try{
            $param = I('post.');
            $redis = connectionRedis();
            $data = array();

            $timeselect = get_timeselect();

            $tenant_list= getTenantList();
            foreach ($tenant_list as $key=>$val){
                if($val['status'] != 1){
                    unset($tenant_list[$key]);
                }
            }

            $insertdataList = array();

            // 如果有传天的参数，则使用参数来处理
            $timeday = isset($param['timeday']) && $param['timeday'] ? strtotime($param['timeday']) : 0;
            $day_start = $timeday ? date('Y-m-d 00:00:00',$timeday) : $timeselect['ytoday_start'];
            $day_end = $timeday ? date('Y-m-d 23:59:59',$timeday) : $timeselect['ytoday_end'];

            // 天记录
            $exist_day = M('complex_summary')->where(['type'=>0, 'collet_day_time'=>strtotime($day_start)])->find();
            if(!$exist_day){
                $map_day_1 = array(
                    'addtime' => ['between',[strtotime($day_start),strtotime($day_end)]],
                    'type' => 'expend',
                );
                $map_day_2 = array(
                    'addtime' => ['between',[strtotime($day_start),strtotime($day_end)]],
                    'type' => 'income',
                );
                $time_arr = array('time_format'=>explode(' 00:00:00',$day_start)[0], 'time'=>strtotime($day_start));
                $LiveList = $this->getLiveList($map_day_1);
                $profitshare_anchor_list = $this->getLivePAList($map_day_2);
                $profitshare_family_list = $this->getLivePFList($map_day_2);
                foreach ($tenant_list as $key=>$val){
                    $live_d = isset($LiveList[$val['id']]) ? $LiveList[$val['id']] : $this->getEmptyLiveData();
                    $profitshare_anchor_d = isset($profitshare_anchor_list[$val['id']]) ? $profitshare_anchor_list[$val['id']] : $this->getEmptyPAData();
                    $profitshare_family_d = isset($profitshare_family_list[$val['id']]) ? $profitshare_family_list[$val['id']] : $this->getEmptyPFData();
                    $resdata = $this->getInsertData($val['id'],$live_d,$profitshare_anchor_d,$profitshare_family_d,'day',$time_arr);
                    array_push($insertdataList,$resdata);
                }
            }

            // 周记录
            $exist_week = M('complex_summary')->where(['type'=>0,'collet_week_time'=>strtotime($timeselect['yweek_start'])])->find();
            if(!$exist_week){
                $map_day_1 = array(
                    'addtime' => ['between',[strtotime($timeselect['yweek_start']),strtotime($timeselect['yweek_end'])]],
                    'type' => 'expend',
                );
                $map_day_2 = array(
                    'addtime' => ['between',[strtotime($timeselect['yweek_start']),strtotime($timeselect['yweek_end'])]],
                    'type' => 'income',
                );
                $time_arr = array('time_format'=>explode(' 00:00:00',$timeselect['yweek_start'])[0], 'time'=>strtotime($timeselect['yweek_start']));
                $LiveList = $this->getLiveList($map_day_1);
                $profitshare_anchor_list = $this->getLivePAList($map_day_2);
                $profitshare_family_list = $this->getLivePFList($map_day_2);
                foreach ($tenant_list as $key=>$val){
                    $live_d = isset($LiveList[$val['id']]) ? $LiveList[$val['id']] : $this->getEmptyLiveData();
                    $profitshare_anchor_d = isset($profitshare_anchor_list[$val['id']]) ? $profitshare_anchor_list[$val['id']] : $this->getEmptyPAData();
                    $profitshare_family_d = isset($profitshare_family_list[$val['id']]) ? $profitshare_family_list[$val['id']] : $this->getEmptyPFData();
                    $resdata = $this->getInsertData($val['id'],$live_d,$profitshare_anchor_d,$profitshare_family_d,'week',$time_arr);
                    array_push($insertdataList,$resdata);
                }
            }

            // 月记录
            $exist_month = M('complex_summary')->where(['type'=>0,'collet_month_time'=>strtotime($timeselect['ymonth_start'])])->find();
            if(!$exist_month){
                $map_day_1 = array(
                    'addtime' => ['between',[strtotime($timeselect['ymonth_start']),strtotime($timeselect['ymonth_end'])]],
                    'type' => 'expend',
                );
                $map_day_2 = array(
                    'addtime' => ['between',[strtotime($timeselect['ymonth_start']),strtotime($timeselect['ymonth_end'])]],
                    'type' => 'income',
                );
                $time_arr = array('time_format'=>explode(' 00:00:00',$timeselect['ymonth_start'])[0], 'time'=>strtotime($timeselect['ymonth_start']));
                $LiveList = $this->getLiveList($map_day_1);
                $profitshare_anchor_list = $this->getLivePAList($map_day_2);
                $profitshare_family_list = $this->getLivePFList($map_day_2);
                foreach ($tenant_list as $key=>$val){
                    $live_d = isset($LiveList[$val['id']]) ? $LiveList[$val['id']] : $this->getEmptyLiveData();
                    $profitshare_anchor_d = isset($profitshare_anchor_list[$val['id']]) ? $profitshare_anchor_list[$val['id']] : $this->getEmptyPAData();
                    $profitshare_family_d = isset($profitshare_family_list[$val['id']]) ? $profitshare_family_list[$val['id']] : $this->getEmptyPFData();
                    $resdata = $this->getInsertData($val['id'],$live_d,$profitshare_anchor_d,$profitshare_family_d,'month',$time_arr);
                    array_push($insertdataList,$resdata);
                }
            }

            if(count($insertdataList) > 0){
                try{
                    $data['insert_res'] = M('complex_summary')->addAll($insertdataList);
                }catch (\Exception $e){
                    $this->responsedata('综合汇总表，插入数据失败：'.$e->getMessage());
                }
            }

            $this->responsedata($data);
        }catch (\Exception $e){
            $this->responsedata(['msg'=>$e->getMessage()]);
        }

    }

    public function responsedata($data = ''){
        echo in_array(gettype($data),['array','object']) ? json_encode($data) : $data;
        exit;
    }

    public function getLiveList($map){
        $list = M('users_coinrecord')->field("
                    tenant_id,
                    sum(if((`action` = 'timecharge'),`totalcoin`,0.00)) as timecharge_am,
                    count(distinct if(`action` = 'timecharge',`uid`,null)) as timecharge_num,

                    sum(if((`action` = 'roomcharge'),`totalcoin`,0.00)) as roomcharge_am,
                    count(distinct if(`action` = 'roomcharge',`uid`,null)) as roomcharge_num,

                    sum(if((`action` = 'sendbarrage'),`totalcoin`,0.00)) as sendbarrage_am,
                    count(distinct if(`action` = 'sendbarrage',`uid`,null)) as sendbarrage_num,

                    sum(if((`action` = 'sendgift'),`totalcoin`,0.00)) as sendgift_am,
                    count(distinct if(`action` = 'sendgift',`uid`,null)) as sendgift_num,

                    sum(if((`action` = 'buycar'),`totalcoin`,0.00)) as buycar_am,
                    count(distinct if(`action` = 'buycar',`uid`,null)) as buycar_num
                ")->where($map)->group('tenant_id')->select();
        $list = count($list) > 0 ? array_column($list,null,'tenant_id') : array();
        return $list;
    }

    public function getLivePAList($map){
        $list = M('users_coinrecord')->field("
                    tenant_id,
                    sum(if((`action` = 'timecharge' or `action` = 'roomcharge'),`totalcoin`,0.00)) as pa_roomcharge_am,
                    count(distinct if(`action` = 'timecharge' or`action` = 'roomcharge',`uid`,null)) as pa_roomcharge_num,
                    
                    sum(if((`action` = 'sendbarrage'),`totalcoin`,0.00)) as pa_sendbarrage_am,
                    count(distinct if(`action` = 'sendbarrage',`uid`,null)) as pa_sendbarrage_num,
                    
                    sum(if((`action` = 'sendgift'),`totalcoin`,0.00)) as pa_sendgift_am,
                    count(distinct if(`action` = 'sendgift',`uid`,null)) as pa_sendgift_num,
                    
                    sum(if((`action` = 'bet'),`totalcoin`,0.00)) as pa_bet_am,
                    count(distinct if(`action` = 'bet',`uid`,null)) as pa_bet_num
                ")->where($map)->where("`uid` = `touid`")->group('tenant_id')->select();
        $list = count($list) > 0 ? array_column($list,null,'tenant_id') : array();
        return $list;
    }

    public function getLivePFList($map){
        $list = M('users_coinrecord')->field("
                            tenant_id,
                            sum(if((`action` = 'timecharge' or `action` = 'roomcharge'),`totalcoin`,0.00)) as pf_roomcharge_am,
                            count(distinct if(`action` = 'timecharge' or`action` = 'roomcharge',`uid`,null)) as pf_roomcharge_num,

                            sum(if((`action` = 'sendbarrage'),`totalcoin`,0.00)) as pf_sendbarrage_am,
                            count(distinct if(`action` = 'sendbarrage',`uid`,null)) as pf_sendbarrage_num,

                            sum(if((`action` = 'sendgift'),`totalcoin`,0.00)) as pf_sendgift_am,
                            count(distinct if(`action` = 'sendgift',`uid`,null)) as pf_sendgift_num,

                            sum(if((`action` = 'bet'),`totalcoin`,0.00)) as pf_bet_am,
                            count(distinct if(`action` = 'bet',`uid`,null)) as pf_bet_num
                        ")->where($map)->where("`uid` != `touid`")->group('tenant_id')->select();
        $list = count($list) > 0 ? array_column($list,null,'tenant_id') : array();
        return $list;
    }

    public function getEmptyLiveData(){
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
        );
        return $data;
    }

    public function getEmptyPAData(){
        $data = array(
            'pa_roomcharge_am' => 0,
            'pa_roomcharge_num' => 0,
            'pa_sendbarrage_am' => 0,
            'pa_sendbarrage_num' => 0,
            'pa_sendgift_am' => 0,
            'pa_sendgift_num' => 0,
            'pa_bet_am' => 0,
            'pa_bet_num' => 0,
        );
        return $data;
    }

    public function getEmptyPFData(){
        $data = array(
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

    public function getInsertData($tenant_id,$live_d,$profitshare_anchor_d,$profitshare_family_d,$type,$time_arr){
        $timeselect = get_timeselect();
        $data = array(
            'timecharge_am' => $live_d['timecharge_am'],
            'timecharge_num' => $live_d['timecharge_num'],
            'roomcharge_am' => $live_d['roomcharge_am'],
            'roomcharge_num' => $live_d['roomcharge_num'],
            'sendbarrage_am' => $live_d['sendbarrage_am'],
            'sendbarrage_num' => $live_d['sendbarrage_num'],
            'sendgift_am' => $live_d['sendgift_am'],
            'sendgift_num' => $live_d['sendgift_num'],
            'buycar_am' => $live_d['buycar_am'],
            'buycar_num' => $live_d['buycar_num'],

            'pa_roomcharge_am' => $profitshare_anchor_d['pa_roomcharge_am'],
            'pa_roomcharge_num' => $profitshare_anchor_d['pa_roomcharge_num'],
            'pa_sendbarrage_am' => $profitshare_anchor_d['pa_sendbarrage_am'],
            'pa_sendbarrage_num' => $profitshare_anchor_d['pa_sendbarrage_num'],
            'pa_sendgift_am' => $profitshare_anchor_d['pa_sendgift_am'],
            'pa_sendgift_num' => $profitshare_anchor_d['pa_sendgift_num'],
            'pa_bet_am' => $profitshare_anchor_d['pa_bet_am'],
            'pa_bet_num' => $profitshare_anchor_d['pa_bet_num'],

            'pf_roomcharge_am' => $profitshare_family_d['pf_roomcharge_am'],
            'pf_roomcharge_num' => $profitshare_family_d['pf_roomcharge_num'],
            'pf_sendbarrage_am' => $profitshare_family_d['pf_sendbarrage_am'],
            'pf_sendbarrage_num' => $profitshare_family_d['pf_sendbarrage_num'],
            'pf_sendgift_am' => $profitshare_family_d['pf_sendgift_am'],
            'pf_sendgift_num' => $profitshare_family_d['pf_sendgift_num'],
            'pf_bet_am' => $profitshare_family_d['pf_bet_am'],
            'pf_bet_num' => $profitshare_family_d['pf_bet_num'],

            'tenant_id' => intval($tenant_id),
            'ctime' => time(),
        );
        switch ($type){
            case 'day':
                $data['collet_day'] = $time_arr['time_format'];
                $data['collet_day_time'] = $time_arr['time'];
                $data['collet_week'] = '';
                $data['collet_week_time'] = 0;
                $data['collet_month'] = '';
                $data['collet_month_time'] = 0;
                break;
            case 'week':
                $data['collet_day'] = '';
                $data['collet_day_time'] = 0;
                $data['collet_week'] = $time_arr['time_format'];
                $data['collet_week_time'] = $time_arr['time'];
                $data['collet_month'] = '';
                $data['collet_month_time'] = 0;
                break;
            case 'month':
                $data['collet_day'] = '';
                $data['collet_day_time'] = 0;
                $data['collet_week'] = '';
                $data['collet_week_time'] = 0;
                $data['collet_month'] = $time_arr['time_format'];
                $data['collet_month_time'] = $time_arr['time'];
                break;
        }

        return $data;
    }

}
