<?php
namespace Admin\Cache;

use Admin\Model\UsersAgentModel;
use Think\Controller;
use Common\Controller\CustRedis;
use Admin\Model\UsersModel;

class UsersCache extends Controller {

    private $fakeUsersGoGroup = '/fake_users';

    public $fields = 'id,user_login,user_nicename,avatar,avatar_thumb,sex,
                            signature,coin,nftwithdrawable_coin,nowithdrawable_coin,withdrawable_coding,votes,consumption,votestotal,
                            province,city,birthday,user_status,login_type,last_login_time,login_num,issuper,tenant_id,
                            isshare,issendred,isprivatemsg,game_tenant_id,game_user_id,integral,
                            mobile,watch_num,watch_time,addup_integral,follows,fans,user_type,beauty,familyids,isshutup,isforbidlive,
                            userlevel,isglr,is_allow_post,is_allow_comment,is_allow_seeking_slice,is_allow_push_slice,
                            version,client,pid,pids,vip_margin,recharge_total,is_certification,certification_name,yeb_balance,payment_password,turntable_times,
                            upload_video_profit_status,grab_red_packet_status,rebate_status,like_deposit';

    private static $instance;

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
            $info = UsersModel::getInstance()->getUserInfoWithIdAndTid($uid, null, $this->fields);
            if($info){
                $info['beauty'] = is_json($info['beauty']) ? json_decode($info['beauty'],true) : $info['beauty'];
                CustRedis::getInstance()->set($cachekey, json_encode($info), 60*60*24*7);
            }
        }else{
            $info = json_decode($info, true);
        }
        return $info;
    }

    /*
    * 清理所有用户信息缓存
    * */
    public function delAllUserInfoCache(){
        $cachekey = 'userinfo_'.'*';
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);
        return isset($res) ? $res : false;
    }

    /*
    * 清理用户播放视频缓存
    * */
    public function delUserPlayVideoCache($tenant_id){
        $cachekey = 'user_will_play_short_video_list_'.$tenant_id.'*';
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);
        return isset($res) ? $res : false;
    }

    /*
    * 清理用户已经观看的视频缓存
    * */
    public function delUserHasWatchVideoCache(){
        $cachekey = 'short_video_watch_' . date('d') . '*';
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);


        $cachekey = 'short_video_watch_' . date('d', strtotime('-1 day')) . '*'; // 昨天
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);

        $cachekey = 'short_video_watch_' . date('d', strtotime('-2 day')) . '*'; // 昨天
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);

        $cachekey = 'short_video_watch_' . date('d', strtotime('-3 day')) . '*'; // 昨天
        $keys = CustRedis::getInstance()->keys($cachekey);
        $res = CustRedis::getInstance()->del($keys);

        return isset($res) ? $res : false;
    }

    /*
     * 虚拟会员清除缓存golang
     * */
    public function DelVirtualUserListCche($tenant_id){
        if(getSystemConf('open_atmosphere_go_api') == 1){
            $url = goAdminUrl().goAdminRouter().$this->fakeUsersGoGroup.'/reload_users_info_to_cache';
            $http_post_res = http_post($url, ['TenantId'=>intval($tenant_id)]);
            return $http_post_res;
        }
        return [];
    }


}
