<?php
namespace Admin\Cache;

use Admin\Model\VideoModel;
use Think\Controller;
use Common\Controller\CustRedis;

class RecommendCache extends Controller {

    private $liveSyncCacheGoGroup = '/live_sync_cache';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 推荐主播列表，清除缓存
    * */
    public function DelRecommendListCache($tenant_id){
        if(getSystemConf('open_atmosphere_go_api') == 1){
            $url = goAdminUrl().goAdminRouter().$this->liveSyncCacheGoGroup.'/del_recommend_list_cache';
            $http_post_res = http_post($url, ['TenantId'=>intval($tenant_id)]);
            return $http_post_res;
        }
        return [];
    }

}
