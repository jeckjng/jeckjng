<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;
use Think\Controller;
use Common\Controller\CustRedis;

class PlaybackAddressCache extends Controller {

    private $goGroup = '/playback_address';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 清除golang里的缓存
    * */
    public function delGoCache($tenant_id, $type){
        if(getSystemConf('open_playback_address_go_api') == 1){
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/del_playback_address_info_cache';
            $http_post_res = http_post($url, ['tenant_id'=>intval($tenant_id), 'type'=>intval($type)]);
            return $http_post_res;
        }
        return [];
    }

}
