<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;
use Think\Controller;
use Common\Controller\CustRedis;
use Admin\Model\VideoClassifyModel;

class VideoClassifyCache extends Controller {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 清理短视频分类缓存
    * */
    public function delShortVideoClassifyCache($tenant_id){
        $cachekey = 'video_classify_'.$tenant_id;
        $res = CustRedis::getInstance()->del($cachekey);
        return isset($res) ? $res : false;
    }

    /*
    * 根据租户id获取短视频分类列表
    * */
    public function getShortVideoClassifyList($tenant_id){
        $cachekey = "video_classify_".$tenant_id;
        $list = CustRedis::getInstance()->get($cachekey);
        if(!$list){
            $list = VideoClassifyModel::getInstance()->getShortVideoClassifyList($tenant_id);
            if(!empty($list)){
                CustRedis::getInstance()->set($cachekey, json_encode($list), 60*60*24*7);
            }
        }else{
            $list = json_decode($list, true);
        }
        return $list;
    }


}
