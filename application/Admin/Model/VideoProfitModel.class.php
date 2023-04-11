<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class VideoProfitModel extends AdminModelBaseModel {

    public $table_name = 'video_profit';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function updateUserTypeWithUid($uid, $UserType){
        $res = M($this->table_name)->where(['video_uid'=>intval($uid)])->save(['user_type'=>intval($UserType)]);
        return $res;
    }

    public function autoUpdateUserType(){
        $model = M($this->table_name);
        $redis = connectionRedis();
        $list = $model->field('id,video_uid,user_type')->where(['user_type'=>0])->limit(1000)->order('id desc')->select();
        $count = 0;
        foreach ($list as $key=>$val){
            if($val['user_type'] == '0' && !$redis->hGet('user_type_is_empty',$val['uid'])){
                $user_info = getUserInfo($val['video_uid']);
                if(isset($user_info['user_type']) && $user_info['user_type'] != '0'){
                    $model->where(['id'=>$val['id']])->save(['user_type'=>$user_info['user_type']]);
                    $count++;
                }else{
                    $redis->hSet('user_type_is_empty', $val['uid'], 1);
                    $redis->expire('user_type_is_empty',60*60*7);
                }
            }
        }
        return $count;
    }

}
