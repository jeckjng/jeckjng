<?php
namespace Admin\Cache;

use Admin\Model\VideoModel;
use Think\Controller;
use Common\Controller\CustRedis;
use Admin\Model\AutoLiveUserModel;

class AutoLiveUserCache extends Controller {

    private $goGroup = '';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 清除自动开播用户列表缓存
    * */
    public function delAutoLiveUserListCache($tenant_id){
        $cachekey = 'AutoLiveUserList_'.$tenant_id;
        $res = CustRedis::getInstance()->del($cachekey);
        return isset($res) ? $res : false;
    }

    /*
    * 获取自动开播用户列表
    * */
    public function getAutoLiveUserList($tenant_id){
        $cachekey = 'AutoLiveUserList_'.$tenant_id;
        $list = CustRedis::getInstance()->get($cachekey);
        if(!$list) {
            $list = AutoLiveUserModel::getInstance()->getListWithTenantId($tenant_id);
            if(!empty($list)){
                CustRedis::getInstance()->set($cachekey, json_encode($list), 60*60*24);
            }
        }else{
            $list = json_decode($list, true);
        }
        return $list;
    }


}
