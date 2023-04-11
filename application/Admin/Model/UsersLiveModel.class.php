<?php
namespace Admin\Model;

use Admin\Model\AdminModelBaseModel;

class UsersLiveModel extends AdminModelBaseModel {

    public $table_name = 'users_live';

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getLastSql(){
        $res = M($this->table_name)->getLastSql();
        return $res;
    }

    public function add($insert_data){
        $res = M($this->table_name)->add($insert_data);
        return $res;
    }

    public function getFootballUsersLiveList($limit = 10000, $field = '*'){
        $list = M($this->table_name)->field($field)->where([
            'is_football' => 1,
            'football_live_time_stamp' => ['lt', time()-30],
            'football_live_match_id' => ['neq', ''],
        ])->limit($limit)->select();
        return $list;
    }

    public function getAutoCreateFootballUsersLiveList($limit = 10000, $field = '*'){
        $list = M($this->table_name)->field($field)->where([
            'is_football' => 1,
            'isvideo' => 1,
        ])->limit($limit)->select();
        return $list;
    }

    /*
    * 更新足球视频直播比赛数据
    * */
    public function updateFootBallLiveInfo($liveuid, $stream, $data = array()){
        $update_data = array();
        if(isset($data['football_live_match_id'])){
            $update_data['football_live_match_id'] = $data['football_live_match_id'];
        }
        if(isset($data['pull'])){
            $update_data['pull'] = $data['pull'];
        }
        if(isset($data['flvpull'])){
            $update_data['flvpull'] = $data['flvpull'];
        }
        if(isset($data['m3u8pull'])){
            $update_data['m3u8pull'] = $data['m3u8pull'];
        }
        if(empty($update_data)){
            return true;
        }
        $update_data['football_live_time_stamp'] = time();

        try{
            M($this->table_name)->where(['uid'=>intval($liveuid), 'stream'=>$stream])->save($update_data);
        }catch (\Exception $e){
            LogComplexModel::getInstance()->add([$liveuid, $stream, $update_data, $e->getMessage()], '【新足球视频直播比赛数据 失败】');
            return false;
        }
        setUserLiveListCache($liveuid); // 设置直播列表缓存
        return true;
    }



}
