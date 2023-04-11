<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;
use Think\Controller;
use Common\Controller\CustRedis;

class LikeConfigCache extends Controller {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 清理缓存
    * */
    public function delCache($tenant_id){
        $cachekey = 'like_config_'.$tenant_id;
        $res = CustRedis::getInstance()->del($cachekey);
        return isset($res) ? $res : false;
    }


}
