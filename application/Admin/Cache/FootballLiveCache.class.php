<?php
namespace Admin\Cache;

use Admin\Model\VideoModel;
use Think\Controller;
use Common\Controller\CustRedis;

class FootballLiveCache extends Controller {

    private $goGroup = '';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
     * 5.4 比赛赛事信息
     * */
    public function getFootballLiveMatchInfo($football_live_base_url, $football_live_token, $match_id){
        $cachekey = "getFootballLiveMatchInfo_".$match_id;
        $info = CustRedis::getInstance()->get($cachekey);
        if(!$info || $info == 'null'){
            if(!$football_live_base_url || !$football_live_token || !$match_id){
                return [];
            }
            $url = trim($football_live_base_url, '/').'/soccer/api/match';
            $url .= '?is_streaming=1&time_stamp='.time().'&begin_id='.trim($match_id).'&limit=1';
            $http_get_res = http_get($url, ['token:'.trim($football_live_token)]);
            if(isset($http_get_res['code']) && $http_get_res['code'] == 0 && isset($http_get_res['result']) && count($http_get_res['result']) > 0){
                $info = $http_get_res['result'][0];
            }
            if(!empty($info)){
                CustRedis::getInstance()->set($cachekey, json_encode($info), 60*60*24);
            }
        }else{
            $info = json_decode($info, true);
        }
        return $info;
    }

    /*
     * 5.1 赛事信息
     * */
    public function getFootballLiveLeagueInfo($football_live_base_url, $football_live_token, $league_id){
        $cachekey = "getFootballLiveLeagueInfo_".$league_id;
        $info = CustRedis::getInstance()->get($cachekey);
        if(!$info || $info == 'null') {
            if (!$football_live_base_url || !$football_live_token || !$league_id) {
                return [];
            }
            $url = trim($football_live_base_url, '/') . '/soccer/api/league';
            $url .= '?is_streaming=1&time_stamp=' . time() . '&league_id=' . trim($league_id) . '&limit=1';
            $http_get_res = http_get($url, ['token:' . trim($football_live_token)]);
            if (isset($http_get_res['code']) && $http_get_res['code'] == 0 && isset($http_get_res['result']) && count($http_get_res['result']) > 0) {
                $info = $http_get_res['result'][0];
            }
            if(!empty($info)){
                CustRedis::getInstance()->set($cachekey, json_encode($info), 60*60*24);
            }
        }else{
            $info = json_decode($info, true);
        }
        return $info;
    }

    /*
    * 4.1 球队信息 详情
    * */
    public function getFootballLiveTeamInfo($football_live_base_url, $football_live_token, $team_id){
        $cachekey = "getFootballLiveTeamInfo_".$team_id;
        $info = CustRedis::getInstance()->get($cachekey);
        if(!$info || $info == 'null') {
            if (!$football_live_base_url || !$football_live_token || !$team_id) {
                return [];
            }
            $url = trim($football_live_base_url, '/') . '/soccer/api/team';
            $url .= '?is_streaming=1&time_stamp=' . time() . '&begin_id=' . trim($team_id) . '&limit=1';
            $http_get_res = http_get($url, ['token:' . trim($football_live_token)]);
            if (isset($http_get_res['code']) && $http_get_res['code'] == 0 && isset($http_get_res['result']) && count($http_get_res['result']) > 0) {
                $info = $http_get_res['result'][0];
            }
            if(!empty($info)){
                CustRedis::getInstance()->set($cachekey, json_encode($info), 60*60*24);
            }
        }else{
            $info = json_decode($info, true);
        }
        return $info;
    }

}
