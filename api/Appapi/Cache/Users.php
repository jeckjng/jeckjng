<?php

use api\Common\CustRedis;

class Cache_Users extends PhalApi_Model_NotORM {

    private static $instance;

    public $fields = 'id,user_login,user_nicename,avatar,avatar_thumb,sex,
                            signature,coin,nftwithdrawable_coin,nowithdrawable_coin,withdrawable_coding,votes,consumption,votestotal,
                            province,city,birthday,user_status,login_type,last_login_time,login_num,issuper,tenant_id,
                            isshare,issendred,isprivatemsg,game_tenant_id,game_user_id,integral,
                            mobile,watch_num,watch_time,addup_integral,follows,fans,user_type,beauty,familyids,isshutup,isforbidlive,
                            userlevel,isglr,is_allow_post,is_allow_comment,is_allow_seeking_slice,is_allow_push_slice,
                            version,client,pid,pids,vip_margin,recharge_total,is_certification,certification_name,yeb_balance,payment_password,turntable_times,
                            upload_video_profit_status,grab_red_packet_status,rebate_status,like_deposit';

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
    * 根据用户id获取信息
    * */
    public function getUserInfoCache($uid){
        $cachekey = "userinfo_".$uid;
        $info = CustRedis::getInstance()->get($cachekey);
        if(!$info){
            $info = DI()->notorm->users->select($this->fields)->where('id=? and user_type !="1"', $uid)->fetchOne();
            if($info){
                $info['beauty'] = is_json($info['beauty']) ? json_decode($info['beauty'],true) : $info['beauty'];
                CustRedis::getInstance()->set($cachekey, json_encode($info), 60);
            }
        }else{
            $info = json_decode($info, true);
        }
        CustRedis::getInstance()->set($cachekey, json_encode($info), 60);
        return $info;
    }

    /*
     * 清理用户已经观看的视频缓存
     * */
    public function delUserHasWatchVideoCache($uid){
        $cachekey = 'short_video_watch_' . date('d') . $uid;
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);


        $cachekey = 'short_video_watch_' . date('d', strtotime('-1 day')) . $uid; // 昨天
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);

        $cachekey = 'short_video_watch_' . date('d', strtotime('-2 day')) . $uid; // 昨天
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);

        $cachekey = 'short_video_watch_' . date('d', strtotime('-3 day')) . $uid; // 昨天
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);

        return isset($res) ? $res : false;
    }

}
