<?php
namespace Admin\Cache;

use Admin\Model\VideoModel;
use Think\Controller;
use Common\Controller\CustRedis;
use Admin\Model\LiveClassModel;

class LiveClassCache extends Controller {

    private $goGroup = '';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 清除直播分类列表缓存
    * */
    public function delLiveClassListCache($tenant_id){
        $cachekey = 'getLiveClass_'.$tenant_id;
        $res = CustRedis::getInstance()->del($cachekey);
        return isset($res) ? $res : false;
    }

    /*
    * 获取直播分类列表
    * */
    public function getLiveClassList($tenant_id){
        $cachekey = 'getLiveClass_'.$tenant_id;
        $list = CustRedis::getInstance()->get($cachekey);
        if(!$list) {
            $list = LiveClassModel::getInstance()->getListWithTenantId($tenant_id);
            if(!empty($list)){
                CustRedis::getInstance()->set($cachekey, json_encode($list), 60*60*24);
            }
        }else{
            $list = json_decode($list, true);
        }
        return $list;
    }


}
