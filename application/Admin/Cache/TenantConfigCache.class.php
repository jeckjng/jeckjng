<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;
use Think\Controller;
use Common\Controller\CustRedis;

class TenantConfigCache extends Controller {

    private $goGroup = '/tenant_config';

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
    public function delGoCache($tenant_id){
        if(getSystemConf('open_tenant_config_go_api') == 1){
            $url = goAdminUrl() . goAdminRouter() . $this->goGroup . '/del_tenant_config_cache';
            $http_post_res = http_post($url, ['tenant_id' => intval($tenant_id)]);
            return $http_post_res;
        }
        return [];
    }

}
