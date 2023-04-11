<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;
use Think\Controller;
use Common\Controller\CustRedis;
use Admin\Model\VideoClassifyModel;

class TenantCache extends Controller {

    private $goGroup = '/tenant';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 更新golang租户列表缓存数据
    * */
    public function UpdateGoTenantCacheOriginStruct(){
        if(getSystemConf('open_tenant_go_api') == 1){
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/update_tenant_cache_origin_struct';
            $http_post_res = http_post($url, []);
            return $http_post_res;
        }
        return [];
    }


}
