<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;
use Think\Controller;
use Common\Controller\CustRedis;

class VipGradeCache extends Controller {

    private $goGroup = '/vip_grade';

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
    public function delGoCache($tenant_id, $vip_grade){
        if(getSystemConf('open_vip_go_api') == 1){
            $url = goAdminUrl() . goAdminRouter() . $this->goGroup . '/del_vip_grade_info_cache';
            $http_post_res = http_post($url, ['tenant_id' => intval($tenant_id), 'vip_grade'=>intval($vip_grade)]);
            return $http_post_res;
        }
        return [];
    }

}
