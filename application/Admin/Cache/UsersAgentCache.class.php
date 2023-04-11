<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;

use Think\Controller;
use Common\Controller\CustRedis;

class UsersAgentCache extends Controller {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
     * 移除代理-所有上级uid-缓存
     * */
    public function delUserAllSuperiorCache($tenant_id){
        $cachekey = 'user_all_superior_'.$tenant_id.'*';
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);
        return isset($res) ? $res : false;
    }

    /*
     * 移除代理-所有下级uid-缓存
     * */
    public function delUserAllSubCache($tenant_id){
        $cachekey = 'user_all_sub_'.$tenant_id.'*';
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);
        return isset($res) ? $res : false;
    }

    /*
   * 根据租户id和用户id获取所有上级uid
   * */
    public function getUserAllSuperiorUid($tenant_id, $uid){
        $cachekey = "user_all_superior_".$tenant_id.'_'.$uid;
        $uids = CustRedis::getInstance()->get($cachekey);
        if(!$uids){
            $uids = UsersAgentModel::getInstance()->getAllSuperiorUid($tenant_id, $uid);
            if(!empty($uids)){
                CustRedis::getInstance()->set($cachekey, json_encode($uids), 60*60*24*3);
            }
        }else{
            $uids = json_decode($uids, true);
        }
        $uids = array_unique($uids);
        return $uids;
    }

    /*
    * 根据用户id获取所有下级uid
    * */
    public function getUserAllSubUid($tenant_id, $uid){
        $cachekey = "user_all_sub_".$tenant_id.'_'.$uid;
        $uids = CustRedis::getInstance()->get($cachekey);
        if(!$uids){
            $uids = UsersAgentModel::getInstance()->getAllSubUid($tenant_id, $uid);
            if(!empty($uids)){
                CustRedis::getInstance()->set($cachekey, json_encode($uids), 60*60*24*3);
            }
        }else{
            $uids = json_decode($uids, true);
        }
        $uids = array_unique($uids);
        return $uids;
    }


}
