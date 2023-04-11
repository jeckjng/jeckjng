<?php
namespace Api\Controller;
use Admin\Model\UsersCoinrecordModel;
use Think\Controller;
use Api\Controller\AdminApiBaseController;
use Common\Controller\CustRedis;
use Admin\Model\UsersModel;
use Admin\Cache\VideoCache;

class ShortVideoController extends AdminApiBaseController {

    public function __construct(){
        parent::__construct();
    }

    /*
    * 自动审核短视频
    * */
    public function auto_check(){
        $param = I('post.');
        $data = array();
        if(!IS_POST){
            $this->out_put($data, 1,'请求方式错误');
        }

        $check_res = $this->check($param);
        if($check_res !== true){
            $this->out_put($data, 1,'不合法: '.$check_res.'  method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($param));
        }
        $tenant_list = getTenantList();
        foreach ($tenant_list as $tenant_key=>$tenant_val) {
            $tenant_id = $tenant_val['id'];
            $config = getConfigPub($tenant_id);
            if($config['auto_check_short_video'] != 1){
                continue;
            }
            for($i=0; $i<60; $i++){
                $video_id = CustRedis::getInstance()->lPop('auto_check_short_video_' . $tenant_id);
                $res = '';
                if ($video_id) {
                    $res = $this->check_short_vide($video_id);
                }
                array_push($data, [$video_id, $res]);
            }
        }
        $this->out_put($data, 20000,'success method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($param));
    }

    /*
     * 审核短视频（单个视频处理）
     * */
    public function check_short_vide($video_id){
        $operated_by = 'system';
        $shotvideo_check_action = CustRedis::getInstance()->get('shotvideo_check_action_'.$video_id);
        if ($shotvideo_check_action){
            return '有人在操作中';
        }else{
            CustRedis::getInstance()->set('shotvideo_check_action_'.$video_id, $operated_by, 60*5);
        }
        $data['id'] = $video_id;
        $data['check_date'] = date('Y-m-d H:i:s',time());
        $data['status'] =  2;
        $data['operated_by'] = $operated_by;
        $data['update_time'] = time();
        $status_name = isset($status_list[$data['status']]) ? $status_list[$data['status']]['name'] : $data['status'];

        $videoInfo =M("video")->where(['id'=>$video_id])->find();// 查找视频用户
        if (!$videoInfo) {
            CustRedis::getInstance()->del('shotvideo_check_action_' . $video_id);
            return '视频不存在';
        }

        try {
            if ($videoInfo['is_downloadable'] != 1){
                CustRedis::getInstance()->del('shotvideo_check_action_'.$video_id);
                return '上传非"已完成"';
            }
            if (!in_array($videoInfo['status'], [1,3])) {
                CustRedis::getInstance()->del('shotvideo_check_action_' . $video_id);
                return '状态不正确';
            }
            $userInfo = UsersModel::getInstance()->getUserInfoWithIdAndTid($videoInfo['uid']);

            $result = M("video")->save($data);
            if(!$result){
                setAdminLog('【短视频自动审核: '.$status_name.'】-失败'.$video_id, 4, $videoInfo['tenant_id']);
                CustRedis::getInstance()->del('shotvideo_check_action_' . $video_id);
                return '未更新';
            }
            if($userInfo['user_type'] != 7){ // 测试账号，审核成功后不做资金变动的处理
                $userVip = M("users_vip")->where(['uid'=>$videoInfo['uid'], 'status'=>1])->order('grade desc')->find();
                if($userVip) {
                    $vipInfo  = M("vip_grade")->where(['vip_grade'=> $userVip['grade']])->find();
                    if ($vipInfo['uplode_video_amount']>0) {
                        ShortVideoCheckReward($videoInfo, $userInfo, $vipInfo);
                    }
                }
            }
        }catch (\Exception $e){
            CustRedis::getInstance()->del('shotvideo_check_action_' . $video_id);
            setAdminLog('【短视频自动审核: '.$status_name.'】-出错-'.$video_id.'-'.$e->getMessage(), 4, $videoInfo['tenant_id']);
            return '【短视频自动审核: '.$status_name.'】-出错-'.$video_id.'-'.$e->getMessage();
        }
        setAdminLog('【短视频自动审核: '.$status_name.'】-成功-'.$video_id, 4, $videoInfo['tenant_id']);
        if($videoInfo['origin'] == 1){
            VideoCache::getInstance()->setPrivateListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
        }
        if(in_array($videoInfo['origin'], [2,3])) {
            VideoCache::getInstance()->setPublicListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
        }
        if($videoInfo['top'] == 1){
            VideoCache::getInstance()->setTopListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
        }
        if($videoInfo['is_advertise'] == 1){
            VideoCache::getInstance()->setAdvertiseListIdCache($videoInfo['tenant_id'], $videoInfo['id']);
        }
        CustRedis::getInstance()->del('shotvideo_check_action_' . $video_id);

        return true;
    }


}
