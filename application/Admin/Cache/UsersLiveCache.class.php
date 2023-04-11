<?php
namespace Admin\Cache;

use Admin\Model\VideoModel;
use Think\Controller;
use Common\Controller\CustRedis;

use Admin\Model\LiveClassModel;
use Admin\Model\UsersLiveModel;

class UsersLiveCache extends Controller {

    private $goGroup = '';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
     * 根据租户id和用户id获取直播列表
     * */
    function getUserLiveInfo($tenant_id, $liveuid){
        $cachekey = 'user_live_list_'.$tenant_id;
        $info = CustRedis::getInstance()->hGet($cachekey, $liveuid);
        if(!$info){
            $info = M(UsersLiveModel::getInstance()->table_name)->where([
                'uid' => intval($liveuid),
                'islive' => 1,
            ])->where('tenant_id = '.intval($tenant_id).' or isshare = 1')->find();
            if($info){
                CustRedis::getInstance()->hSet($cachekey, $liveuid, json_encode($info));
            }
        }else{
            $info = json_decode($info, true);
        }
        return $info;
    }


}
