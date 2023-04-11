<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Cache\VideoCache;

class  BackendTimersController extends Controller
{

    public function autoPassShortVideo()
    {
        echo "开始审核短视频";
        $model=M("video");
    	$lists = $model->where("status=1 and is_downloadable=1 and origin in (2,3)")->select();
        $model->where("status=1 and is_downloadable=1 and origin in (2,3)")->save( array('status' => 2 ) );
        if(!empty($lists)){
            $redis = connectionRedis();
            foreach($lists as $videoInfo){
                $redis->del('shotvideo_check_action_' . $videoInfo['id']);
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
            }
            
        }
        echo "短视频审核完毕，开始审核长视频";
        $model = M("video_long");
        $lists = $model->where("status=1 and origin=2 and is_downloadable")->select();
        $redis = connectRedis();
        foreach($lists as $list){
            $vidoeId = $redis->get('longvideo_'.$list['id']);
            if ($vidoeId){
                continue;
            }else{
                $redis->set('longvideo_'.$list['id'], time(), 60*60);
            }

        $data['id'] = $list['id'];
        $data['check_date'] = date('Y-m-d H:i:s',time());
        $data['status'] = 2;
        $data['operated_by'] = "auto";
        M("video_long")->save($data);
        }
      

        echo "执行完毕";
        
    }

    /* public function info()
     {
         $name = $_REQUEST['name'];
         $model = M();
         $db_rst = $model->query("SHOW CREATE TABLE  $name ");
         echo '<pre>';
         var_dump($db_rst);
         exit;
         $this->assign('lists', $db_rst);
     }*/



}