<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;
use Think\Controller;
use Common\Controller\CustRedis;

class UsersLikeCache extends Controller {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 清除用户点赞保证金数据
    * */
    public function delUsersLikeCache($uid){
        CustRedis::getInstance()->del('user_like_'.$uid);
    }

}
