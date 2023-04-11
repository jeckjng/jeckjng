<?php
namespace Admin\Cache;

use Admin\Model\VideoModel;
use Think\Controller;
use Common\Controller\CustRedis;

class AtmosphereCache extends Controller {

    private $liveRobotGoGroup = '/live_robot';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 直播氛围，清除缓存
    * */
    public function DelAtmosphereCache($liveuid, $TaskConfig){
        if(getSystemConf('open_atmosphere_go_api') == 1){
            $url = goAdminUrl().goAdminRouter().$this->liveRobotGoGroup.'/event';
            $http_post_res = http_post($url, ['TaskId'=>intval($liveuid),'TaskConfig'=>json_encode($TaskConfig)]);
            return $http_post_res;
        }
        return [];
    }

}
