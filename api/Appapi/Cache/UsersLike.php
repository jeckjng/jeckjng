<?php

use api\Common\CustRedis;

class Cache_UsersLike extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 获取用户点赞保证金数据
    * */
    public function getUsersLikeInfo($uid){
        $cachekey = 'user_like_'.$uid;
        $info = CustRedis::getInstance()->get($cachekey);
        if(!$info){
            $info = DI()->notorm->users_like->where('uid = ?', intval($uid))->fetchOne();
            if($info){
                CustRedis::getInstance()->set($cachekey, json_encode($info), 60*60*24*7);
            }
        }else{
            $info = json_decode($info, true);
        }
        return $info;
    }

    /*
     * 清除用户点赞保证金数据
     * */
    public function delUsersLikeCache($uid){
        CustRedis::getInstance()->del('user_like_'.$uid);
    }

}
