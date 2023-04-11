<?php

use api\Common\CustRedis;

class Cache_VideoClassify extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 根据租户id获取短视频分类列表
    * */
    public function getShortVideoClassifyList($tenant_id){
        $cachekey = "video_classify_".$tenant_id;
        $list = CustRedis::getInstance()->get($cachekey);
        if(!$list){
            $list = Model_VdeoClassify::getInstance()->getShortVideoClassifyList($tenant_id);
            if(!empty($list)){
                CustRedis::getInstance()->set($cachekey, json_encode($list), 60);
            }
        }else{
            $list = json_decode($list, true);
        }
        return $list;
    }

    /*
    * 根据 租户id 和 分类名称 获取短视频分类详情
    * */
    public function getShortVideoClassifyInfo($tenant_id, $classify){
        $info = array();
        $list = $this->getShortVideoClassifyList($tenant_id);
        foreach ($list as $key=>$val){
            if($val['classify'] == $classify){
                $info = $val;
            }
        }
        return $info;
    }

}
