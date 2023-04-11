<?php
namespace Admin\Cache;

use Admin\Model\VideoModel;
use Think\Controller;
use Common\Controller\CustRedis;

class BetConfigCache extends Controller {

    private $goGroup = '/live_sync_cache';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 彩种配置新增、修改、删除，清除缓存
    * */
    public function DelBetConfigListCche($tenant_id){
        if(getSystemConf('open_atmosphere_go_api') == 1){
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/del_bet_config_list_cache';
            $http_post_res = http_post($url, ['TenantId'=>intval($tenant_id)]);
            return $http_post_res;
        }
        return [];
    }

}
