<?php

use api\Common\CustRedis;

class Cache_Video extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
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
     * 获取短视频详情
     * */
    public function getShortVideoInfo($tenant_id, $id)
    {
        $cachekey = 'short_video_info_' . $tenant_id . $id;
        $info = CustRedis::getInstance()->get($cachekey);
        if (!$info) {
            $info = DI()->notorm->video->where("id = ?", intval($id))->fetchOne();
            if ($info) {
                CustRedis::getInstance()->set($cachekey, json_encode($info), 60 * 60 * 24 * 7);
            }
        } else {
            $info = json_decode($info, true);
        }
        return $info;
    }
    /*
     * 获取长视频视频详情
     * */
    public function getLongVideoInfo($tenant_id, $id)
    {
        $cachekey = 'long_video_info_' . $tenant_id . $id;
        $info = CustRedis::getInstance()->get($cachekey);

        if (!$info) {
            $info = DI()->notorm->video_long->where("id = ?", intval($id))->fetchOne();
            if ($info) {
                CustRedis::getInstance()->set($cachekey, json_encode($info), 60 * 60 * 24 * 7);
            }
        } else {
            $info = json_decode($info, true);
        }
        return $info;
    }
    /*
     * 获取长视频列表id
     * */
    public function getLongVideoidbatch($tenant_id)
    {
        $cachekey = 'long_video_list_'.$tenant_id;
        $count = CustRedis::getInstance()->lLen($cachekey);
        if($count == 0){
            $list = DI()->notorm->video_long->where("tenant_id = ? and status = 2 and shot_status=1  ", intval($tenant_id))
                ->order('id desc')
                ->limit(100)
                ->fetchAll();

            foreach ($list as $key=>$info){
                $num ++;
                if($info['id']){
                    CustRedis::getInstance()->rPush($cachekey, $info['id']);
                }
            }
            CustRedis::getInstance()->expire($cachekey, 60*60*24*7);
            return  $num;
        }
        return $count;
    }

    /*
     * 用户已经观看该视频, 则记录保存
     * */
    public function setShortVideoWatchCache($uid, $video_id)
    {
        $cachekey = 'short_video_watch_' . date('d') . $uid;
        $res = CustRedis::getInstance()->hSet($cachekey, $video_id, $video_id);
        CustRedis::getInstance()->expire($cachekey, 60*60*24*7);
        return $res;
    }

    /*
    * 判断用户，该视频是否已经观看
    * */
    public function existShortVideoWatch($uid, $video_id)
    {
        $cachekey = 'short_video_watch_' . date('d') . $uid; // 今天
        $res = CustRedis::getInstance()->hExists($cachekey, $video_id);
        if($res == true){
            return $res;
        }
        $cachekey = 'short_video_watch_' . date('d', strtotime('-1 day')) . $uid; // 昨天
        $res = CustRedis::getInstance()->hExists($cachekey, $video_id);
        if($res == true){
            return $res;
        }
        $cachekey = 'short_video_watch_' . date('d', strtotime('-2 day')) . $uid; // 昨昨天
        $res = CustRedis::getInstance()->hExists($cachekey, $video_id);
        if($res == true){
            return $res;
        }
        $cachekey = 'short_video_watch_' . date('d', strtotime('-3 day')) . $uid; // 昨昨昨天
        $res = CustRedis::getInstance()->hExists($cachekey, $video_id);
        if($res == true){
            return $res;
        }
    }

    /*
     * 设置短视频列表缓存
     * */
    public function setVideoList($video_list, $cachekey){
        $video_list = array_reverse($video_list);
        $count = CustRedis::getInstance()->zCard($cachekey);
        foreach ($video_list as $key=>$info){
            if($info['id']){
                $count ++;
                $res = CustRedis::getInstance()->zAdd($cachekey, ($count+1), $info['id']); // 将一个值插入到列表尾部
            }
        }
        $count = CustRedis::getInstance()->zCard($cachekey);
        $limit_len = videoCacheCount(); // 保留视频数量
        $end = $count > $limit_len ? ($count-$limit_len) : 0;
        CustRedis::getInstance()->zRevRange($cachekey, 0, $end); // 切除多余的数据
        CustRedis::getInstance()->expire($cachekey, 60*60*24*7);

        return isset($res) ? $res : false;
    }
    /*
       *  精选特殊处理
       * */
    public function getJIngxuanId($tenant_id, $uid, $classify){
        $cachekey = 'short_video_jingxuan_list_'.$tenant_id.$classify;
        $count = CustRedis::getInstance()->zCard($cachekey);
        if($count == 0){
            $video_list = DI()->notorm->video->where("tenant_id = ? and status = 2  and likes > 0 ", intval($tenant_id))
                ->order('likes asc')
                ->limit(videoCacheCount())
                ->fetchAll();

            foreach ($video_list as $key=>$info){
                if($info['id']){
                    $res = CustRedis::getInstance()->zAdd($cachekey, $info['likes'], $info['id']);
                }
            }
            $count = CustRedis::getInstance()->zCard($cachekey);
            $limit_len = videoCacheCount(); // 保留视频数量
            $end = $count > $limit_len ? ($count-$limit_len) : 0;
            CustRedis::getInstance()->zRevRange($cachekey, 0, $end); // 切除多余的数据
            CustRedis::getInstance()->expire($cachekey, 60);   //十分钟缓存
        }
        return $count;
    }
    /*
   *  关注特殊处理
   * */
    public function getGuanzhuId($tenant_id, $uid, $classify){
        $cachekey = 'short_video_guanzhu_list_' . $tenant_id  . $uid;
        $count = CustRedis::getInstance()->zCard($cachekey);
        if($count == 0){
            $atttion_list = DI()->notorm->users_attention
                ->select('touid')
                ->where("uid =".$uid)
                ->limit(10)
                ->fetchAll();
            foreach ($atttion_list as $key=>$info) {
                $video_list = DI()->notorm->video
                    ->select('id')
                    ->where("tenant_id = ? and status = 2 and uid=  " . $info['touid'], intval($tenant_id))
                    ->limit(videoCacheCount())
                    ->fetchAll();
                if (!empty($video_list)) {
                    foreach ($video_list as $keys => $values) {
                        $res = CustRedis::getInstance()->zAdd($cachekey, rand(1, 1000), $values['id']);
                    }
                }
            }
            CustRedis::getInstance()->zRevRange($cachekey, 0, 1000); // 切除多余的数据
            CustRedis::getInstance()->expire($cachekey, 120);   //两分钟分钟缓存
        }
        return $count;
    }

    /*
    * 根据租户id、用户id、分类、是否代理线可见、上下级ids，获取短视频-私有Id
    * */
    public function getPrivateId($id_list = array(), $tenant_id, $uid, $classify = '', $agent_line_visible = 0, $agent_all_uids = array(), $index = null){
        $cachekey = 'short_video_private_list_'.$tenant_id.$classify;
        $count = CustRedis::getInstance()->zCard($cachekey);
        $data = ['id'=>0, 'index'=>0, 'video_data_count'=>videoCacheCount()];
        if($count == 0){
            if($classify == '推荐'){
                $and_classify_where   = " ";
            }else{
                $and_classify_where = $classify ? " and FIND_IN_SET('".$classify."',classify)" : '';
            }

            $list = DI()->notorm->video->where("tenant_id = ? and status = 2 and origin = 1 and top = 0 and is_advertise = 0 ".$and_classify_where, intval($tenant_id))
                ->order('id desc')
                ->limit(videoCacheCount())
                ->fetchAll();
            $this->setVideoList($list, $cachekey);
            $data['video_data_count'] = count($list);
        }

        $count = CustRedis::getInstance()->zCard($cachekey);
        $i_start = $index !== null && $index >= 0 ? ($count - $index + 1) : 1; // 如果有下标，则从下标后一位开始取数据，如下标为9，则从8开始取

        for($i=$i_start; $i<=$count; $i++){

            $start = $count - $i;
            $end = $start;
            $id_arr = CustRedis::getInstance()->zRange($cachekey, $start, $end);
            $id = $id_arr[0];

            if(in_array($id, $id_list)){
                continue;
            }

            if($this->existShortVideoWatch($uid, $id) === true){
                continue;
            }

            if($agent_line_visible == 1){
                $info = $this->getShortVideoInfo($tenant_id, $id);
                if($info && in_array($info['uid'], $agent_all_uids)){
                    $data['id'] = $id;
                    $data['index'] = $start;
                    return $data;
                }
            }else{

                $data['id'] = $id;
                $data['index'] = $start;
                return $data;
            }
        }
        return $data;
    }

    /*
    * 根据租户id获取短视频-私有列表数量
    * */
    public function getPrivateListCount($tenant_id, $classify){
        $cachekey = 'short_video_private_list_'.$tenant_id.$classify;
        $count = CustRedis::getInstance()->zCard($cachekey);
        return $count;
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
    * 根据租户id、用户id、分类, 获取短视频-公共Id
    * */
    public function getPublicId($id_list = array(), $tenant_id, $uid, $classify, $index = null){
        $cachekey = 'short_video_public_list_'.$tenant_id.$classify;
        $count = CustRedis::getInstance()->zCard($cachekey);
        $data = ['id'=>0, 'index'=>0, 'video_data_count'=>videoCacheCount()];
        if($count == 0){
            if($classify == '推荐'){
                $and_classify_where   = " ";
            }else{
                $and_classify_where = $classify ? " and FIND_IN_SET('".$classify."',classify)" : '';
            }

            $list = DI()->notorm->video->where("tenant_id = ? and status = 2 and origin in(2,3) and top = 0 and is_advertise = 0 ".$and_classify_where, intval($tenant_id))
                ->order('id desc')
                ->limit(videoCacheCount())
                ->fetchAll();
            $this->setVideoList($list, $cachekey);
            $data['video_data_count'] = count($list);
        }
        $count = CustRedis::getInstance()->zCard($cachekey);
        $i_start = $index !== null && $index >= 0 ? ($count - $index + 1) : 1; // 如果有下标，则从下标后一位开始取数据，如下标为9，则从8开始取
        for($i=$i_start; $i<=$count; $i++){
            $start = $count - $i;
            $end = $start;
            $id_arr = CustRedis::getInstance()->zRange($cachekey, $start, $end);
            $id = $id_arr[0];
            if(in_array($id, $id_list)){
                continue;
            }
            if($this->existShortVideoWatch($uid, $id) === true){
                continue;
            }
            $data['id'] = $id;
            $data['index'] = $start;
            return $data;
        }
        return $data;
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
    * 根据租户id、分类, 获取短视频-置顶Id
    * */
    public function getTopId($tenant_id, $classify){
        $cachekey = 'short_video_top_list_'.$tenant_id.$classify;
        $len = CustRedis::getInstance()->lLen($cachekey);
        $data = '';
        if($len == 0){
            $and_classify_where = $classify ? " and FIND_IN_SET('".$classify."',classify)" : '';
            $list = DI()->notorm->video->where("tenant_id = ? and status = 2 and top = 1 ".$and_classify_where, intval($tenant_id))
                ->order('id desc')
                ->limit(videoCacheCount())
                ->fetchAll();
            foreach ($list as $key=>$info){
                $res = CustRedis::getInstance()->lPush($cachekey, $info['id']); // 将一个值插入到列表尾部
            }
            CustRedis::getInstance()->expire($cachekey, 60*60*24*7);
        }
        $len = CustRedis::getInstance()->lLen($cachekey);
        $data = $len > 0 ? CustRedis::getInstance()->bRPopLPush($cachekey, $cachekey, 60*60*24*7) : $data;
        return $data;
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
     * 根据租户id，获取短视频-广告Id
     * */
    public function getAdvertiseId($tenant_id){
        $cachekey = 'short_video_advertise_list_'.$tenant_id;
        $len = CustRedis::getInstance()->lLen($cachekey);
        $data = '';
        if($len == 0){
            $list = DI()->notorm->video->where("tenant_id = ? and status = 2 and is_advertise = 1 ", intval($tenant_id))
                ->order('id desc')
                ->limit(videoCacheCount())
                ->fetchAll();
            foreach ($list as $key=>$info){
                CustRedis::getInstance()->lPush($cachekey, $info['id']); // 将一个值插入到列表尾部
            }
            CustRedis::getInstance()->expire($cachekey, 60*60*24*7);
        }
        $len = CustRedis::getInstance()->lLen($cachekey);
        $data = $len > 0 ? CustRedis::getInstance()->bRPopLPush($cachekey, $cachekey, 60*60*24*7) : $data;
        return $data;
    }

    /*
     * 移除短视频-广告列表ID-缓存
     * */
    public function delAdvertiseListIdCache($tenant_id, $id){
        $cachekey = 'short_video_advertise_list_'.$tenant_id;
        $res = CustRedis::getInstance()->lRem($cachekey, $id,0);
        return isset($res) ? $res : false;
    }

    /*
    * 根据租户id、用户id、分类、是否代理线可见、上下级ids，获取短视频-私有或公共Id
    * $repeat bool  // true 允许重复，false 不允许重复
    * */
    public function getPrivateOrPublicId($id_list = array(), $repeat = false, $tenant_id, $classify = '', $agent_line_visible = 0, $agent_all_uids = array()){
        $private_cachekey = 'short_video_private_list_'.$tenant_id.$classify;
        $public_cachekey = 'short_video_public_list_'.$tenant_id.$classify;
        $cachekey_arr = array();
        array_push($cachekey_arr, $private_cachekey);
        array_push($cachekey_arr, $public_cachekey);
        $cachekey_arr_count = count($cachekey_arr);

        $cachekey = $cachekey_arr[mt_rand(0, ($cachekey_arr_count-1))];
        $data = '';
        $count = CustRedis::getInstance()->zCard($cachekey);
        $count = $count <= 50 ? $count : 50; // 限制内循环20次，防止 外循环次数 乘以 内循环次数 后总次数太大消耗太多计算力
        for($i=1; $i<=$count; $i++){
            $start = mt_rand(0, $count-1);
            $end = $start;
            $id_arr = CustRedis::getInstance()->zRange($cachekey, $start, $end);
            $id = $id_arr[0];
            if($repeat === false && in_array($id, $id_list)){
                continue;
            }
            if($agent_line_visible == 1 && $cachekey == $private_cachekey){ // 开启代理线并且是私有视频，才判断是否在代理线里面
                $info = $this->getShortVideoInfo($tenant_id, $id);
                if($info && in_array($info['uid'], $agent_all_uids)){
                    $data = $id;
                    return $data;
                }
            }else{
                $data = $id;
                return $data;
            }
        }
        return $data;
    }


}
