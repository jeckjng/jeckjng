<?php
namespace Admin\Cache;

use Admin\Model\VideoModel;
use Think\Controller;
use Common\Controller\CustRedis;

class VideoCache extends Controller {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
     * 设置短视频详情缓存
     * */
    public function setShortVideoInfoCache($id, $info = array()){
        if(empty($info)){
            $info = DI()->notorm->video->where("id = ?", intval($id))->fetchOne();
            if(!$info){
                return false;
            }
        }
        if(!isset($info['tenant_id'])){
            return false;
        }
        $cachekey = 'short_video_info_'.$info['tenant_id'].$id;
        $res = CustRedis::getInstance()->set($cachekey, json_encode($info), 60*60*24*7);
        return $res;
    }

    /*
     * 清除短视频详情缓存
     * */
    public function delShortVideoInfoCache($tenant_id, $id){
        $cachekey = 'short_video_info_'.$tenant_id;
        $res = CustRedis::getInstance()->del($cachekey, $id);
        return $res;
    }

    /*
     * 设置短视频-私有列表-缓存
     * */
    public function setPrivateListIdCache($tenant_id, $id){
        $info = M(VideoModel::getInstance()->table_name)->where(['id'=>intval($id), 'tenant_id'=>intval($tenant_id), 'status'=>2, 'origin'=>1, 'top'=>0, 'is_advertise'=>0])->find();
        if($info){
            $classify_arr = explode(',', $info['classify']);
            foreach ($classify_arr as $key=>$val){
                if(!$val){
                    continue;
                }
                $cachekey = 'short_video_private_list_'.$info['tenant_id'].$val;
                $count = CustRedis::getInstance()->zCard($cachekey);
                $res = CustRedis::getInstance()->zAdd($cachekey, ($count+1), $id); // 将一个值插入到列表尾部
                $limit_len = videoCacheCount(); // 保留视频数量
                $end = $count > $limit_len ? ($count-$limit_len) : 0;
                CustRedis::getInstance()->zRevRange($cachekey, 0, $end); // 切除多余的数据
                CustRedis::getInstance()->expire($cachekey, 60*60*24*7);
            }
            // 设置短视频详情缓存
            $res = $this->setShortVideoInfoCache($id, $info);
        }else{
            $res = $this->delPrivateListIdCache($tenant_id, $id);
            // 清除短视频详情缓存
            $res = $this->setShortVideoInfoCache($tenant_id, $id);
        }
        return isset($res) ? $res : false;
    }

    /*
     * 移除短视频-私有列表-缓存
     * */
    public function delPrivateListCache($tenant_id){
        $keys_list = CustRedis::getInstance()->keys('short_video_private_list_'.$tenant_id.'*');
        foreach ($keys_list as $key=>$cachekey) {
            $res = CustRedis::getInstance()->del($cachekey);
        }
        return isset($res) ? $res : false;
    }

    /*
    * 移除短视频-私有列表ID-缓存
    * */
    public function delPrivateListIdCache($tenant_id, $id){
        $keys_list = CustRedis::getInstance()->keys('short_video_private_list_'.$tenant_id.'*');
        foreach ($keys_list as $key=>$cachekey) {
            $res = CustRedis::getInstance()->zRem($cachekey, $id);
        }
        return isset($res) ? $res : false;
    }

    /*
    * 设置短视频-公共列表-缓存
    * */
    public function setPublicListIdCache($tenant_id, $id){
        $info = M(VideoModel::getInstance()->table_name)->where(['id'=>intval($id), 'tenant_id'=>intval($tenant_id), 'status'=>2, 'origin'=>['in', [2,3]], 'top'=>0, 'is_advertise'=>0])->find();
        if($info){
            $classify_arr = explode(',', $info['classify']);
            foreach ($classify_arr as $key=>$val){
                if(!$val){
                    continue;
                }
                $cachekey = 'short_video_public_list_'.$info['tenant_id'].$val;
                $count = CustRedis::getInstance()->zCard($cachekey);
                $res = CustRedis::getInstance()->zAdd($cachekey, ($count+1), $id); // 将一个值插入到列表尾部
                $limit_len = videoCacheCount(); // 保留视频数量
                $end = $count > $limit_len ? ($count-$limit_len) : 0;
                CustRedis::getInstance()->zRevRange($cachekey, 0, $end); // 切除多余的数据
                CustRedis::getInstance()->expire($cachekey, 60*60*24*7);
            }
            // 设置短视频详情缓存
            $res = $this->setShortVideoInfoCache($id, $info);
        }else{
            $res = $this->delPublicListIdCache($tenant_id, $id);
            // 清除短视频详情缓存
            $res = $this->setShortVideoInfoCache($tenant_id, $id);
        }
        return isset($res) ? $res : false;
    }

    /*
     * 移除短视频-公共列表-缓存
     * */
    public function delPublicListCache($tenant_id){
        $keys_list = CustRedis::getInstance()->keys('short_video_public_list_'.$tenant_id.'*');
        foreach ($keys_list as $key=>$cachekey) {
            $res = CustRedis::getInstance()->del($cachekey);
        }
        return isset($res) ? $res : false;
    }

    /*
    * 移除短视频-公共列表ID-缓存
    * */
    public function delPublicListIdCache($tenant_id, $id){
        $keys_list = CustRedis::getInstance()->keys('short_video_public_list_'.$tenant_id.'*');
        foreach ($keys_list as $key=>$cachekey) {
            $res = CustRedis::getInstance()->zRem($cachekey, $id);
        }
        return isset($res) ? $res : false;
    }

    /*
    * 设置短视频-置顶列表-缓存
    * */
    public function setTopListIdCache($tenant_id, $id){
        $info = M(VideoModel::getInstance()->table_name)->where(['id'=>intval($id), 'tenant_id'=>intval($tenant_id), 'status'=>2, 'top'=>1])->find();
        if($info){
            $classify_arr = explode(',', $info['classify']);
            foreach ($classify_arr as $key=>$val){
                if(!$val){
                    continue;
                }
                $cachekey = 'short_video_top_list_'.$info['tenant_id'].$val;
                CustRedis::getInstance()->lRem($cachekey, $id,0);
                $res = CustRedis::getInstance()->LPush($cachekey, $id); // 将一个值插入到列表尾部
                $len = CustRedis::getInstance()->lLen($cachekey);
                $limit_len = videoCacheCount(); // 保留视频数量
                $star = $len > $limit_len ? ($len-$limit_len) : 0;
                $end = $len;
                CustRedis::getInstance()->lTrim($cachekey, $star, $end); // 切除多余的数据
                CustRedis::getInstance()->expire($cachekey, 60*60*24*7);
            }
            // 设置短视频详情缓存
            $res = $this->setShortVideoInfoCache($id, $info);
        }else{
            $res = $this->delTopListIdCache($tenant_id, $id);
            // 清除短视频详情缓存
            $res = $this->setShortVideoInfoCache($tenant_id, $id);
        }
        return isset($res) ? $res : false;
    }

    /*
     * 移除短视频-置顶列表-缓存
     * */
    public function delTopListCache($tenant_id){
        $keys_list = CustRedis::getInstance()->keys('short_video_top_list_'.$tenant_id.'*');
        foreach ($keys_list as $key=>$cachekey) {
            $res = CustRedis::getInstance()->del($cachekey);
        }
        return isset($res) ? $res : false;
    }

    /*
    * 移除短视频-置顶列表ID-缓存
    * */
    public function delTopListIdCache($tenant_id, $id){
        $keys_list = CustRedis::getInstance()->keys('short_video_top_list_'.$tenant_id.'*');
        foreach ($keys_list as $key=>$cachekey) {
            $res = CustRedis::getInstance()->lRem($cachekey, $id,0);
        }
        return isset($res) ? $res : false;
    }

    /*
    * 设置短视频-广告列表-缓存
    * */
    public function setAdvertiseListIdCache($tenant_id, $id){
        $info = M(VideoModel::getInstance()->table_name)->where(['id'=>intval($id), 'tenant_id'=>intval($tenant_id), 'status'=>2, 'is_advertise'=>1])->find();
        if($info){
            $classify_arr = explode(',', $info['classify']);
            foreach ($classify_arr as $key=>$val){
                if(!$val){
                    continue;
                }
                $cachekey = 'short_video_advertise_list_'.$info['tenant_id'].$val;
                CustRedis::getInstance()->lRem($cachekey, $id,0);
                $res = CustRedis::getInstance()->LPush($cachekey, $id); // 将一个值插入到列表尾部
                $len = CustRedis::getInstance()->lLen($cachekey);
                $limit_len = videoCacheCount(); // 保留视频数量
                $star = $len > $limit_len ? ($len-$limit_len) : 0;
                $end = $len;
                CustRedis::getInstance()->lTrim($cachekey, $star, $end); // 切除多余的数据
                CustRedis::getInstance()->expire($cachekey, 60*60*24*7);
            }
            // 设置短视频详情缓存
            $res = $this->setShortVideoInfoCache($id, $info);
        }else{
            $res = $this->delAdvertiseListIdCache($tenant_id, $id);
            // 清除短视频详情缓存
            $res = $this->setShortVideoInfoCache($tenant_id, $id);
        }
        return isset($res) ? $res : false;
    }

    /*
     * 移除短视频-广告列表-缓存
     * */
    public function delAdvertiseListCache($tenant_id){
        $cachekey = 'short_video_advertise_list_'.$tenant_id;
        $res = CustRedis::getInstance()->del($cachekey);
        return isset($res) ? $res : false;
    }

    /*
    * 移除短视频-广告列表ID-缓存
    * */
    public function delAdvertiseListIdCache($tenant_id, $id){
        $cachekey = 'short_video_advertise_list_'.$tenant_id;
        $res = CustRedis::getInstance()->lRem($cachekey, $id,0);
        return isset($res) ? $res : false;
    }

}
