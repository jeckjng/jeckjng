<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;
use Think\Controller;
use Common\Controller\CustRedis;

class LiveingSet extends Controller {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 清理直播间设置列表信息缓存
    * */
    public function delLiveingSetListCache($tenant_id){
        $cachekey = "liveing_set_list_".$tenant_id;
        $res = CustRedis::getInstance()->del($cachekey);
        return isset($res) ? $res : false;
    }

}
