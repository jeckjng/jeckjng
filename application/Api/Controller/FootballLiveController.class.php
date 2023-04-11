<?php
namespace Api\Controller;

use Admin\Cache\AutoLiveUserCache;
use Common\Controller\CustRedis;
use Think\Controller;
use Api\Controller\AdminApiBaseController;
use Admin\Model\UsersLiveModel;
use Admin\Model\LogComplexModel;
use Admin\Model\UsersModel;
use Admin\Model\LiveingLogModel;

use Admin\Cache\FootballLiveCache;
use Admin\Cache\UsersCache;
use Admin\Cache\LiveClassCache;
use Admin\Cache\UsersLiveCache;


class FootballLiveController extends AdminApiBaseController {

    protected $runing_liveuids = array(); // 正在开播中的主播id列表
    protected $runing_match_ids = array(); // 正在开播中的比赛ID列表

    public function __construct(){
        parent::__construct();
    }

    /*
    * 获取足球视频直播列表
    * */
    public function getFootballLiveList(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $param[$key] = html_entity_decode($val);
        }
        $data = array();
        if(!isset($param['tenant_id']) || empty($param['tenant_id'])){
            $this->out_put($data, 1,'tenant_id 不能为空');
        }

        $tenant_id = intval($param['tenant_id']);
        $config = getConfigPub($tenant_id);

        $data = getFootballLiveList($config['football_live_base_url'], $config['football_live_token']);
        if(is_array($data)){
            foreach ($data as $key=>$val){
                // 更据租户配置和比赛id（match_id）获取标题等数据
                $footballInfo = $this->getFootballInfo($config, $val['match_id']);
                $data[$key]['match_info'] = $footballInfo['match_info'];
                $data[$key]['league_info'] = $footballInfo['league_info'];
                $data[$key]['league_name'] = $footballInfo['league_name'];
                $data[$key]['league_pic'] = $footballInfo['league_pic'];
                $data[$key]['vs_name'] = $footballInfo['vs_name'];

                // 更据type的值做排序处理，优先获取高清中文播流地址
                $playUrlInfo = $this->getFootballPlayUrl($val['live_streams']);
                $data[$key]['pull'] = $playUrlInfo['pull'];
                $data[$key]['flvpull'] = $playUrlInfo['flvpull'];
                $data[$key]['m3u8pull'] = $playUrlInfo['m3u8pull'];
                $data[$key]['type'] = $playUrlInfo['type'];
            }
            $data = array_values($data);
        }

        $this->out_put($data, 20000,'success');
    }

    /*
    * 定时-自动更新足球视频直播比赛数据
    * */
    public function updateFootballUsersLiveInfoOld(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $param[$key] = html_entity_decode($val);
        }
        $data = array();

        $football_users_live_list = UsersLiveModel::getInstance()->getFootballUsersLiveList();

        $tenant_ids = array();
        foreach ($football_users_live_list as $key=>$val){
            if($val['tenant_id'] && !in_array($val['tenant_id'], $tenant_ids)){
                array_push($tenant_ids, $val['tenant_id']);
            }
        }

        $tid_football_live_list = [];
        foreach ($tenant_ids as $key=>$val){
            if($val){
                $config = getConfigPub($val);
                $temp_list = getFootballLiveList($config['football_live_base_url'], $config['football_live_token']);
                $temp_list = count($temp_list) > 0 ? array_column($temp_list, null,'match_id') : [];
                if(!empty($temp_list)){
                    $tid_football_live_list[$val] = $temp_list;
                }
            }
        }

        foreach ($football_users_live_list as $key1=>$val1){
            if(!isset($tid_football_live_list[$val1['tenant_id']])){
                CustRedis::getInstance()->zAdd('disconnect_'.$val1['tenant_id'],0, $val1['uid']); // 没有足球直播了，则不显示到前端
                array_push($data, 'continue '.$val1['tenant_id'].' | '.$val1['uid']);
                continue;
            }
            $football_live_list = $tid_football_live_list[$val1['tenant_id']];
            $footballLiveInfo = isset($football_live_list[$val1['football_live_match_id']]) ? $football_live_list[$val1['football_live_match_id']] : [];
            if(empty($footballLiveInfo)){
                $football_live_list = array_values($football_live_list);
                $footballLiveInfo = count($football_live_list) > 0 ? $football_live_list[rand(0, count($football_live_list)-1)] : [];
            }

            if(isset($footballLiveInfo['live_streams'])){
                $update_data = ['football_live_match_id'=>$footballLiveInfo['match_id']];

                // 优先获取中文高清的
                foreach ($footballLiveInfo['live_streams'] as $key2=>$val2){
                    if($val2['format'] == 4 && $val2['type'] == 4){
                        $update_data['pull'] = $val2['url'];
                    }
                    if($val2['format'] == 3 && $val2['type'] == 4){
                        $update_data['flvpull'] = $val2['url'];
                    }
                    if($val2['format'] == 2 && $val2['type'] == 4){
                        $update_data['m3u8pull'] = $val2['url'];
                    }
                }
                // 优先获取中文标清的
                foreach ($footballLiveInfo['live_streams'] as $key2=>$val2){
                    if(!isset($update_data['pull'])){
                        if($val2['format'] == 4 && $val2['type'] == 2){
                            $update_data['pull'] = $val2['url'];
                        }
                    }
                    if(!isset($update_data['flvpull'])){
                        if($val2['format'] == 3 && $val2['type'] == 2){
                            $update_data['flvpull'] = $val2['url'];
                        }
                    }
                    if(!isset($update_data['m3u8pull'])){
                        if($val2['format'] == 2 && $val2['type'] == 2){
                            $update_data['m3u8pull'] = $val2['url'];
                        }
                    }
                }
                // 优先获取中英文混合标清的
                foreach ($footballLiveInfo['live_streams'] as $key2=>$val2){
                    if(!isset($update_data['pull'])){
                        if($val2['format'] == 4 && $val2['type'] == 1){
                            $update_data['pull'] = $val2['url'];
                        }
                    }
                    if(!isset($update_data['flvpull'])){
                        if($val2['format'] == 3 && $val2['type'] == 1){
                            $update_data['flvpull'] = $val2['url'];
                        }
                    }
                    if(!isset($update_data['m3u8pull'])){
                        if($val2['format'] == 2 && $val2['type'] == 1){
                            $update_data['m3u8pull'] = $val2['url'];
                        }
                    }
                }

                // 如果 type = 1 的没有则不限制type (type = 1 为中文标清)
                foreach ($footballLiveInfo['live_streams'] as $key2=>$val2){
                    if(!isset($update_data['pull'])){
                        if($val2['format'] == 4){
                            $update_data['pull'] = $val2['url'];
                        }
                    }
                    if(!isset($update_data['flvpull'])){
                        if($val2['format'] == 3){
                            $update_data['flvpull'] = $val2['url'];
                        }
                    }
                    if(!isset($update_data['m3u8pull'])){
                        if($val2['format'] == 2){
                            $update_data['m3u8pull'] = $val2['url'];
                        }
                    }
                }

                UsersLiveModel::getInstance()->updateFootBallLiveInfo($val1['uid'], $val1['stream'], $update_data);
                CustRedis::getInstance()->zRem('disconnect_'.$val1['tenant_id'], $val1['uid']); // 有足球直播，则显示到前端
                array_push($data, $val1['uid'].' | '.$val1['stream'].' | '.$footballLiveInfo['match_id']);
            }else{
                CustRedis::getInstance()->zAdd('disconnect_'.$val1['tenant_id'],0, $val1['uid']); // 没有足球直播了，则不显示到前端
                array_push($data, 'disconnect_'.$val1['tenant_id'].' | '.$val1['uid']);
            }
        }

        $this->out_put($data, 20000,'success');
    }

    /*
    * 定时-自动更新足球视频直播比赛数据, 并自动创建直播
    * */
    public function updateFootballUsersLiveInfo(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $param[$key] = html_entity_decode($val);
        }
        $data = array();

        $tenant_list = getTenantList();
        foreach ($tenant_list as $key=>$val){
            $config = getConfigPub($val['id']);
            echo $val['id'].' || '.$config['football_live_base_url'].' || '.$config['football_live_token']."\n";
            if(!empty($config['football_live_base_url']) && !empty($config['football_live_token'])){
                $footballLiveList = getFootballLiveList($config['football_live_base_url'], $config['football_live_token']);
                $usersLiveFootballList = UsersLiveModel::getInstance()->getAutoCreateFootballUsersLiveList();
                echo '$footballLiveList: '.count($footballLiveList)."\n\n";
                echo '$usersLiveFootballList: '.count($usersLiveFootballList)."\n\n";

                // 检测并更新用户直播数据，如果比赛id不存在则关播
                $resultCheckAndUpdateUsersLive = $this->checkAndUpdateUsersLive($footballLiveList, $usersLiveFootballList, $config);
                echo "检测并更新用户直播数据，如果比赛id不存在则关播: ".json_encode($resultCheckAndUpdateUsersLive)."\n";

                // 创建直播
                $resultCreateUsersLive = $this->createUsersLive($footballLiveList, $config, $val['id']);
                echo "创建直播: ".json_encode($resultCreateUsersLive)."\n";
            }
        }

        $this->out_put($data, 20000,'success');
    }

    // 检测并更新用户直播数据，如果比赛id不存在则关播
    protected function checkAndUpdateUsersLive($footballLiveList, $usersLiveFootballList, $config){
        $footballLiveList = count($footballLiveList) > 0 ? array_column($footballLiveList, null, 'match_id') : [];
        $runing_liveuids = array(); // 正在开播中的主播id列表
        $runing_match_ids = array(); // 正在开播中的比赛ID列表
        foreach ($usersLiveFootballList as $key=>$val){
            // 赛事不存在，则进行关播处理
            if(!isset($footballLiveList[$val['football_live_match_id']])){
                $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($val['uid'], $val['tenant_id'],'game_tenant_id,token');
                $stopRoomUrl = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/api/public/?service=Live.StopRoom&game_tenant_id='.$user_info['game_tenant_id'].'&uid='.$val['uid'].'&token='.$user_info['token'].'&stream='.$val['stream'].'&acttype=amdin_stop';

                $stopres = file_get_contents($stopRoomUrl);
                $stopres = is_json($stopres) ? json_decode($stopres,true) : $stopres;

                if($stopres['data']['code'] == 700){
                    if($user_info['expiretime']<time()){
                        M("users")->where(['id'=>$val['uid']])->save(['expiretime'=>(time()+60*60*24*300)]);
                    }
                    delcache("token_".$val['uid']);
                    $stopres = file_get_contents($stopRoomUrl);
                    if($stopres['data']['code'] == 700){
                        array_push($runing_liveuids, $val['uid']); // 关播失败，加入正在开播中的主播id列表
                        array_push($runing_match_ids, $val['football_live_match_id']); // 关播失败，加入正在开播中的比赛ID列表
                    }
                }
            }else{
                // 赛事存在，则更新处理
                $footballLiveInfo = $footballLiveList[$val['football_live_match_id']];
                $match_info = FootballLiveCache::getInstance()->getFootballLiveMatchInfo($config['football_live_base_url'], $config['football_live_token'], $footballLiveInfo['match_id']);
                if(!empty($match_info)){
                    $footballLiveInfo['match_info'] = $match_info;
                    $footballLiveInfo['league_info'] = FootballLiveCache::getInstance()->getFootballLiveLeagueInfo($config['football_live_base_url'], $config['football_live_token'], $match_info['league_id']);
                    $footballLiveInfo['league_name'] = !empty($footballLiveInfo['league_info']) ? $footballLiveInfo['league_info']['name_zh_full'] : '';
                    $footballLiveInfo['league_pic'] = !empty($footballLiveInfo['league_info']) ? $footballLiveInfo['league_info']['pic'] : '';
                    $footballLiveInfo['vs_name'] = '';
                    foreach ($footballLiveInfo['match_info']['team'] as $k=>$v){
                        $footballLiveInfo['team_'.$v['team_id']] = FootballLiveCache::getInstance()->getFootballLiveTeamInfo($config['football_live_base_url'], $config['football_live_token'], $v['team_id']);
                        $footballLiveInfo['vs_name'] .= !empty($footballLiveInfo['team_'.$v['team_id']]) ? ' '.$footballLiveInfo['team_'.$v['team_id']]['name_zh'].' VS' : '';
                    }
                    $footballLiveInfo['vs_name'] = trim($footballLiveInfo['vs_name'], ' VS');
                }else{
                    $footballLiveInfo['match_info'] = [];
                    $footballLiveInfo['league_info'] = [];
                    $footballLiveInfo['league_name'] = '';
                    $footballLiveInfo['league_pic'] = '';
                    $footballLiveInfo['vs_name'] = '';
                }

                // 更据租户配置和比赛id（match_id）获取标题等数据
                $footballInfo = $this->getFootballInfo($config, $val['match_id']);
                $footballLiveInfo['match_info'] = $footballInfo['match_info'];
                $footballLiveInfo['league_info'] = $footballInfo['league_info'];
                $footballLiveInfo['league_name'] = $footballInfo['league_name'];
                $footballLiveInfo['league_pic'] = $footballInfo['league_pic'];
                $footballLiveInfo['vs_name'] = $footballInfo['vs_name'];

                // 更据type的值做排序处理，优先获取高清中文播流地址
                $playUrlInfo = $this->getFootballPlayUrl($footballLiveInfo['live_streams']);
                $update_data = [];
                if($playUrlInfo['pull'] != $val['pull']){
                    $update_data['pull'] = $playUrlInfo['pull'];
                }
                if($playUrlInfo['flvpull'] != $val['flvpull']){
                    $update_data['flvpull'] = $playUrlInfo['flvpull'];
                }
                if($playUrlInfo['m3u8pull'] != $val['m3u8pull']){
                    $update_data['m3u8pull'] = $playUrlInfo['m3u8pull'];
                }
                if($footballLiveInfo['vs_name'] != $val['m3u8pull']){
                    $update_data['title'] = $footballLiveInfo['vs_name'];
                }
                if(!empty($update_data)){
                    UsersLiveModel::getInstance()->updateFootBallLiveInfo($val['uid'], $val['stream'], $update_data);
                    CustRedis::getInstance()->zRem('disconnect_'.$val['tenant_id'], $val['uid']); // 有足球直播，则显示到前端
                }
                array_push($runing_liveuids, $val['uid']); // 加入正在开播中的主播id列表
                array_push($runing_match_ids, $val['football_live_match_id']); // 关播失败，加入正在开播中的比赛ID列表
            }
        }

        // 保存到属性中
        $this->runing_liveuids = $runing_liveuids;
        $this->runing_match_ids = $runing_match_ids;
        return true;
    }

    // 更据type的值做排序处理，优先获取高清中文播流地址
    protected function getFootballPlayUrl($live_streams_list){
        $info = array(
            'pull' => '',
            'flvpull' => '',
            'm3u8pull' => '',
            'type' => '',
        );
        $live_streams_list = is_array($live_streams_list) ? $live_streams_list : array();
        // 优先获取中文高清的
        foreach ($live_streams_list as $key=>$val){
            if($val['format'] == 4 && $val['type'] == 4){
                $info['pull'] = $val['url'];
                $info['type'] = $val['type'];
            }
            if($val['format'] == 3 && $val['type'] == 4){
                $info['flvpull'] = $val['url'];
            }
            if($val['format'] == 2 && $val['type'] == 4){
                $info['m3u8pull'] = $val['url'];
            }
        }
        // 优先获取中文标清的
        foreach ($live_streams_list as $key=>$val){
            if(empty($info['pull']) && $val['format'] == 4 && $val['type'] == 2){
                $info['pull'] = $val['url'];
                $info['type'] = $val['type'];
            }
            if(empty($info['flvpull']) && $val['format'] == 3 && $val['type'] == 2){
                $info['flvpull'] = $val['url'];
            }
            if(empty($info['m3u8pull']) && $val['format'] == 2 && $val['type'] == 2){
                $info['m3u8pull'] = $val['url'];
            }
        }
        // 优先获取中英文混合标清的
        foreach ($live_streams_list as $key=>$val){
            if(empty($info['pull']) && $val['format'] == 4 && $val['type'] == 1){
                $info['pull'] = $val['url'];
                $info['type'] = $val['type'];
            }
            if(empty($info['flvpull']) && $val['format'] == 3 && $val['type'] == 1){
                $info['flvpull'] = $val['url'];
            }
            if(empty($info['m3u8pull']) && $val['format'] == 2 && $val['type'] == 1){
                $info['m3u8pull'] = $val['url'];
            }
        }
        // 没有优先获取的，则不做直播类型限制，直接获取播流地址url
        foreach ($live_streams_list as $key=>$val){
            if(empty($info['pull']) && $val['format'] == 4){
                $info['pull'] = $val['url'];
                $info['type'] = $val['type'];
            }
            if(empty($info['flvpull']) && $val['format'] == 3){
                $info['flvpull'] = $val['url'];
            }
            if(empty($info['m3u8pull']) && $val['format'] == 2){
                $info['m3u8pull'] = $val['url'];
            }
        }
        return $info;
    }

    // 更据租户配置和比赛id（match_id）获取标题等数据
    protected function getFootballInfo($config, $match_id){
        $info = array(
            'match_info' => [],
            'league_info' => [],
            'league_name' => '',
            'league_pic' => '',
            'vs_name' => '',
        );
        if(empty($config) || empty($match_id)){
            return $info;
        }
        $match_info = FootballLiveCache::getInstance()->getFootballLiveMatchInfo($config['football_live_base_url'], $config['football_live_token'], $match_id);
        if(!empty($match_info)){
            $info['match_info'] = $match_info;
            $info['league_info'] = FootballLiveCache::getInstance()->getFootballLiveLeagueInfo($config['football_live_base_url'], $config['football_live_token'], $match_info['league_id']);
            $info['league_name'] = !empty($info['league_info']) ? $info['league_info']['name_zh_full'] : '';
            $info['league_pic'] = !empty($info['league_info']) ? $info['league_info']['pic'] : '';
            $info['vs_name'] = '';
            foreach ($match_info['team'] as $key=>$val){
                $info['team_'.$val['team_id']] = FootballLiveCache::getInstance()->getFootballLiveTeamInfo($config['football_live_base_url'], $config['football_live_token'], $val['team_id']);
                $info['vs_name'] .= !empty($info['team_'.$val['team_id']]) ? ' '.$info['team_'.$val['team_id']]['name_zh'].' VS' : '';
            }
            $info['vs_name'] = trim($info['vs_name'], ' VS');
        }else{
            $info['match_info'] = [];
            $info['league_info'] = [];
            $info['league_name'] = '';
            $info['league_pic'] = '';
            $info['vs_name'] = '';
        }
        return $info;
    }


    // 创建直播
    protected function createUsersLive($footballLiveList, $config, $tenant_id){
        $footballLiveList = count($footballLiveList) > 0 ? array_column($footballLiveList, null, 'match_id') : [];
        $runing_liveuids = $this->runing_liveuids; // 正在开播中的主播id列表
        echo '$footballLiveList: '.json_encode(array_keys($footballLiveList))."\n";
        echo '$runing_liveuids: '.json_encode($runing_liveuids)."\n";

        $runing_match_ids = $this->runing_match_ids; // 正在开播中的比赛ID列表
        $auto_live_user_list = AutoLiveUserCache::getInstance()->getAutoLiveUserList($tenant_id);
        foreach ($auto_live_user_list as $key=>$val){
            if(in_array($val['uid'], $runing_liveuids)){
                unset($auto_live_user_list[$key]);
            }
        }
        if(count($auto_live_user_list) <= 0){
            return true;
        }
        $num = 0;
        $auto_live_user_list = array_values($auto_live_user_list);
        foreach ($footballLiveList as $key=>$val){
            if(in_array($val['match_id'], $runing_match_ids)){
                continue;
            }
            if(!isset($auto_live_user_list[$num])){
                continue;
            }

            $auto_live_user_info = [];
            $uid = 0;
            foreach ($auto_live_user_list as $k=>$v) {
                unset($auto_live_user_list[$k]);
                $users_live_info = UsersLiveCache::getInstance()->getUserLiveInfo($tenant_id, $v['uid']);
                if(!empty($users_live_info)){
                    continue;
                }
                $auto_live_user_info = $v;
                $uid = $v['uid'];
                break;
            }
            if(empty($uid)){
                continue;
            }

            $user_info = UsersCache::getInstance()->getUserInfoCache($uid);

            // 更据租户配置和比赛id（match_id）获取标题等数据
            $footballInfo = $this->getFootballInfo($config, $val['match_id']);
            $footballLiveInfo['match_info'] = $footballInfo['match_info'];
            $footballLiveInfo['league_info'] = $footballInfo['league_info'];
            $footballLiveInfo['league_name'] = $footballInfo['league_name'];
            $thumb = !empty($auto_live_user_info['thumb']) ? $auto_live_user_info['thumb'] : $footballInfo['league_pic'];
            $title = $footballInfo['vs_name'];

            // 更据type的值做排序处理，优先获取高清中文播流地址
            $playUrlInfo = $this->getFootballPlayUrl($val['live_streams']);
            $pull = $playUrlInfo['pull'];
            $flvpull = $playUrlInfo['flvpull'];
            $m3u8pull = $playUrlInfo['m3u8pull'];
            $data[$key]['type'] = $playUrlInfo['type'];

            $nowtime = time();
            $stream = $uid.'_'.$nowtime;
            $pushpull_type = 1; // 直播线路类型：1.默认，2.黄播,3.绿播,4.赌播

            $liveclassid = 0; // 直播分类 （默认分类 0）
            $live_class_list = LiveClassCache::getInstance()->getLiveClassList($user_info['tenant_id']);
            foreach ($live_class_list as $k=>$v){
                if($v['name'] == '世界杯'){
                    $liveclassid = $v['id'];
                }
            }

            $tryWatchTime = $config['trywatchtime'];

            $insert_data = array(
                "uid" => intval($uid),
                "user_nicename" => $user_info['user_nicename'],
                "avatar" => get_upload_path($user_info['avatar']),
                "avatar_thumb"=>get_upload_path($user_info['avatar_thumb']),
                "showid" => $nowtime,
                "starttime" => $nowtime,
                "title" => $title,
                "province" => '',
                "city" => '广州市',
                "stream" => $stream,
                "thumb" => trim($thumb),
                "pushpull_type" => intval($pushpull_type),
                "is_football" => 1,
                "football_live_match_id" => trim($val['match_id']),
                "football_live_time_stamp" => time(),
                "pull" => trim($pull),
                "flvpull" => trim($flvpull),
                "m3u8pull" => trim($m3u8pull),
                "lng" => '',
                "lat" => '',
                "type" => 0, // 普通房间
                "type_val" => '', // 密码/价格 空
                "isvideo" => 1,
                "islive" => 1,
                "anyway" => 1, // 视频类型 横屏
                "liveclassid" => $liveclassid, // 默认分类
                "tenant_id" => $user_info['tenant_id'],
                "game_user_id" => $user_info['game_user_id'] ? $user_info['game_user_id'] : 0,
                "isshare" => $user_info['isshare'],
                "tryWatchTime" => $tryWatchTime,
                'isrecommend' => 1, // 主播开播以后自动上推荐
                "act_uid" => 0,
            );

            try{
                M()->startTrans();
                UsersLiveModel::getInstance()->add($insert_data);
                LiveingLogModel::getInstance()->add([
                    "uid" => $uid,
                    "starttime" => $nowtime,
                    "stream" => $stream,
                    "status" => 0,
                ]);
                M()->commit();
            }catch (\Exception $e){
                M()->rollback();
                LogComplexModel::getInstance()->add(['insert_data'=>$insert_data, 'error_msg'=>$e->getMessage()], '【自动创建足球直播 失败】', 200, $tenant_id, $uid, $user_info['user_login'], 0, 'system');
                return '自动创建足球直播 失败: '.$e->getMessage();
            }
            setUserLiveListCache($uid, 'create'); // 设置直播列表缓存
        }
        return true;
    }



}
