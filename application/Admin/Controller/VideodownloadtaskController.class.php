<?php
namespace Admin\Controller;
use Common\Controller\CustRedis;
use Think\Controller;

use Admin\Model\LogApiModel;
use Admin\Model\LogComplexModel;

class  VideodownloadtaskController extends Controller {

    public function index(){

        $forbiden_dmain = getKvconfigVal(1,'forbiden_dmain');
        $forbiden_dmain = explode("\n", $forbiden_dmain);
        if(in_array($_SERVER['HTTP_HOST'], $forbiden_dmain)){
            LogComplexModel::getInstance()->add([$_SERVER['HTTP_HOST']], '【禁用的域名不允许访问网站】', 3);
            return false;
        }

        $rand_num = rand(0,9);
        $sort_arr = ['id desc','id desc','id desc','id desc','id desc','id desc','id desc','id desc','id desc','id asc','rand()'];
        $sort_order = $sort_arr[$rand_num];
        $short_list = M("video")->where(array('is_downloadable'=>0,'status'=>array('neq',4)))->order($sort_order)->limit(60)->select();
        foreach ($short_list as $key=>$lists){
            $video_model=M("video");
//            $lists = $video_model->where(array('is_downloadable'=>0))->order('id desc')->find();
            LogApiModel::getInstance()->add($lists,'【短视频信息获取Test】');
            if ($lists['filestorekey']){
                $video_model->where(array('id'=>$lists['id']))->save(array('is_downloadable' =>4, 'update_time'=>time() ));// 定时任务下次请求不会出现重复
                $data = $this->curlPost($lists['filestorekey'], $lists['tenant_id']);
                $downloadtask_res = file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/test/downloadtask.txt',$lists['id'].'-----'.json_encode($data));
                if($downloadtask_res == false){
                    LogComplexModel::getInstance()->add([$_SERVER['DOCUMENT_ROOT'] . '/test/downloadtask.txt', $data], '【短视频信息写入downloadtask失败】'.$lists['id'], 3, $lists['tenant_id'], $lists['uid'], $lists['user_login']);
                }
                if ($data['code'] == '200'){
                    if ($data['data']['status'] == 'success'){
                        $video_data = array('is_downloadable' =>1 );
                        foreach ($data['data']['stores'] as $amazonKey => $amazonvalue) {
                            $patharr = parse_url($amazonvalue);
                            if ($amazonKey == 0){
                                $video_data['download_address'] = $patharr['path'];
                            }else{
                                $video_data['download_address_'.$amazonKey] = $patharr['path'];
                            }
                        }
                        foreach ($data['data']['m3u8'] as $m3u8Key => $m3u8value) {
                            $file_name = $lists['id'].date('YmdHis', time()). explode('.', microtime(true))[1] . uniqid() . mt_rand(10000,99999);
                            // 检测目录
                            $checkDirRes = checkSaveDir($_SERVER['DOCUMENT_ROOT'] . "/test/");
                            if($checkDirRes !== true){
                                echo '保存目录不存在: '.$checkDirRes;
                                return ;
                            }
                            //写入m3u8
                            $m3u8_res = file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $m3u8value['m3u8Content']);
                            if($m3u8_res == false){
                                LogComplexModel::getInstance()->add([$_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $m3u8value['m3u8Content']], '【短视频信息写入m3u8失败】'.$lists['id'], 3, $lists['tenant_id'], $lists['uid'], $lists['user_login']);
                            }
                            $video_href = '/test/' . $file_name . ".m3u8";
                            $playback_address= M('playback_address')->where(array('java_field'=>$m3u8value['name']))->find();
                            $video_data[$playback_address['viode_table_field']] = $video_href;
                            $coverFileName_arr = parse_url($m3u8value['coverFileName']);
                            $video_data['thumb']= $coverFileName_arr['path'];
                            $video_data['playTimeInt']= $m3u8value['playTimeInt'];
                            $video_data['duration'] = $m3u8value['playTimeStr'];
                            $video_data['update_time'] = time();

                            // 防止出现异常的情况：两次$file_name的值一样
                            unset($file_name);
                        }
                        $update_res = $video_model->where(array('id' => $lists['id']))->save($video_data);
                        if($update_res){
                            CustRedis::getInstance()->rPush('auto_check_short_video_' . $lists['tenant_id'], $lists['id']);
                        }
                    }elseif ($data['data']['status'] == 'fail'){
                        M("video")->where(array('id' => $lists['id'], 'update_time'=>time() ))->save(array('is_downloadable'=>2, 'update_time'=>time() ));
                        LogApiModel::getInstance()->add($data,'【短视频信息获取】fail', $lists['tenant_id']);
                    }elseif ($data['data']['status'] == 'notfound'){
                        M("video")->where(array('id' => $lists['id']))->save(array('is_downloadable'=>3, 'update_time'=>time() ));
                        LogApiModel::getInstance()->add($data,'【短视频信息获取】notfound', $lists['tenant_id']);
                    }else{
                        $video_model->where(array('id' => $lists['id']))->save(array('is_downloadable'=>0, 'update_time'=>time() ));
                    }
                }else{
                    $video_model->where(array('id'=>$lists['id']))->save(array('is_downloadable' =>0, 'update_time'=>time() ));// 还没成功数据还原
                }
            }
        }

        $long_list = M("video_long")->where(array('is_downloadable'=>0,'status'=>array('neq',4)))->order($sort_order)->limit(60)->select();
        foreach ($long_list as $key=>$long_lists){
            $video_long_model=M("video_long");
//            $long_lists = $video_long_model->where(array('is_downloadable'=>0))->find();

            if ($long_lists['filestorekey']){
                $video_long_model->where(array('id'=>$long_lists['id']))->save(array('is_downloadable' =>4));// 定时任务下次请求不会出现重复
                $data = $this->curlPost($long_lists['filestorekey'], $long_lists['tenant_id']);
                file_put_contents('./data/test/downloadtask.txt',$long_lists['id'].'-----'.json_encode($data));
                if ($data['code'] == '200'){
                    if ($data['data']['status'] == 'success'){
                        $long_video_data = array('is_downloadable' =>1 );
                        foreach ($data['data']['stores'] as $amazonKey => $amazonvalue) {
                            $patharr = parse_url($amazonvalue);
                            if ($amazonKey == 0){
                                $long_video_data['download_address'] = $patharr['path'];
                            }else{
                                $long_video_data['download_address_'.$amazonKey] = $patharr['path'];
                            }
                        }
                        foreach ($data['data']['m3u8'] as $m3u8Key => $m3u8value) {
                            $file_name = $long_lists['id'].date('YmdHis', time()). explode('.', microtime(true))[1] . uniqid() . mt_rand(10000,99999);
                            // 检测目录
                            $checkDirRes = checkSaveDir($_SERVER['DOCUMENT_ROOT'] . "/test/");
                            if($checkDirRes !== true){
                                echo '保存目录不存在: '.$checkDirRes;
                                return ;
                            }
                            //写入m3u8
                            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $m3u8value['m3u8Content']);
                            $video_href = '/test/' . $file_name . ".m3u8";
                            $playback_address= M('playback_address')->where(array('java_field'=>$m3u8value['name']))->find();
                            $long_video_data[$playback_address['viode_table_field']] = $video_href;
                            $coverFileName_arr = parse_url($m3u8value['coverFileName']);
                            $long_video_data['thumb']= $coverFileName_arr['path'];
                            $long_video_data['duration'] = $m3u8value['playTimeStr'];
                            $long_video_data['playTimeInt']= $m3u8value['playTimeInt'];

                            // 防止出现异常的情况：两次$file_name的值一样
                            unset($file_name);
                        }
                        $video_long_model->where(array('id' => $long_lists['id']))
                            ->save($long_video_data);
                    }elseif ($data['data']['status'] == 'fail'){
                        $video_long_model->where(array('id' => $long_lists['id']))
                            ->save(array('is_downloadable'=>2));
                        LogApiModel::getInstance()->add($data,'【长视频信息获取】fail', $long_lists['tenant_id']);
                    }elseif ($data['data']['status'] == 'notfound'){
                        $video_long_model->where(array('id' => $long_lists['id']))
                            ->save(array('is_downloadable'=>3));
                        LogApiModel::getInstance()->add($data,'【长视频信息获取】notfound', $long_lists['tenant_id']);
                    }else{
                        /* if ($data['data']['status'] == "encrypting" or $data['data']['status'] == 'uploading' ||$data['data']['status'] == "preparing"  || $data['data']['status'] == " rivideo uploading" || $data['data']['status'] == "m3u8 uploading"){
                           */
                        $video_long_model->where(array('id' => $long_lists['id']))
                            ->save(array('is_downloadable'=>0));
                    }
                }else{
                    $video_long_model->where(array('id'=>$lists['id']))->save(array('is_downloadable' =>0));// 还没成功数据还原

                }
            }
        }

        $bar_list = M("bar")->where(array('video_status'=>0))->order($sort_order)->limit(60)->select();
        $bar_id  = array_column($bar_list, 'id');// 贴吧id
        $bar_model=M("bar");
        $bar_id =implode(",",$bar_id);
      //  $bar_model->where(array('id'=> array('in',$bar_id)))->save(array('video_status' =>4));// 定时任务下次请求不会出现重复

        foreach ($bar_list as   $key=>$bar_lists){
            if ($bar_lists['filestorekey']){
                $data = $this->curlPost($bar_lists['filestorekey'], $bar_lists['tenant_id']);
                file_put_contents('./data/test/downloadtask.txt','贴吧id'.$bar_lists['id'].'-----'.json_encode($data));
                if ($data['code'] == '200'){
                    if ($data['data']['status'] == 'success'){
                        $bar_data = array('video_status' =>1 );
                        foreach ($data['data']['m3u8'] as $m3u8Key => $m3u8value) {
                            $file_name = $bar_lists['id'].date('YmdHis', time()). explode('.', microtime(true))[1] . uniqid() . mt_rand(10000,99999);

                            // 检测目录
                            $checkDirRes = checkSaveDir($_SERVER['DOCUMENT_ROOT'] . "/test/");
                            if($checkDirRes !== true){
                                echo '保存目录不存在: '.$checkDirRes;
                                return ;
                            }
                            //写入m3u8
                            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $m3u8value['m3u8Content']);
                            $video_href = '/test/' . $file_name . ".m3u8";
                            $playback_address= M('playback_address')->where(array('java_field'=>$m3u8value['name']))->find();
                            if ($playback_address){
                                $bar_data[$playback_address['viode_table_field']] = $video_href;
                                $coverFileName_arr = parse_url($m3u8value['coverFileName']);
                                $bar_data['video_img']= $coverFileName_arr['path'];
                            }

                            // 防止出现异常的情况：两次$file_name的值一样
                            unset($file_name);
                        }
                        $bar_model->where(array('id' => $bar_lists['id']))
                            ->save($bar_data);
                    }elseif ($data['data']['status'] == 'fail'){
                        $bar_model->where(array('id' => $bar_lists['id']))
                            ->save(array('video_status'=>2));
                        LogApiModel::getInstance()->add($data,'【贴吧视频信息获取】fail', $bar_list['tenant_id']);
                    }elseif ($data['data']['status'] == 'notfound'){
                        $bar_model->where(array('id' => $bar_lists['id']))
                            ->save(array('video_status'=>3));
                        LogApiModel::getInstance()->add($data,'【贴吧频信息获取】notfound', $bar_list['tenant_id']);
                    }else{

                        /* if ($data['data']['status'] == "encrypting" or $data['data']['status'] == 'uploading' ||$data['data']['status'] == "preparing"  || $data['data']['status'] == " rivideo uploading" || $data['data']['status'] == "m3u8 uploading"){
                           */
                        $bar_model->where(array('id' => $bar_lists['id']))
                            ->save(array('video_status'=>0));
                    }

                }else{
                    $bar_model->where(array('id'=>$bar_lists['id']))->save(array('video_status' =>0));// 还没成功数据还原

                }

            }
        }

    }

    public  function curlPost($string, $tenant_id){
        $config = getConfigPub($tenant_id);
        $url = $config['url_of_get_video_info_from_java'] ? trim($config['url_of_get_video_info_from_java'], '/').'?key='.$string : 'http://16.162.206.41:9999/api/store/getStoreStatus?key='.$string;
        $data['key'] = $string;
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            //设置cURL允许执行的最长毫秒数,从 PHP 5.2.3 起可使用
            curl_setopt($ch,  CURLOPT_TIMEOUT_MS, 10000);
            // 执行后不直接打印出来
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // 设置请求方式为post
            /* curl_setopt($ch,CURLOPT_POST,true);*/
            // post的变量
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            /*   curl_setopt($ch,CURLOPT_POSTFIELDS,$data);*/
            // 请求头，可以传数组
            if (!empty($header)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // 不从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $return_data = curl_exec($ch);
            curl_close($ch);
        }
        catch(Exception $e){
                throw new Exception("getStoreStatus Invalid URL",0,$e);
        }

        return json_decode($return_data,true);

    }

   /* public  function uplodevideo(){
        $video_model=M("video");
        $lists = $video_model
            ->where(array('status'=>-1))
            ->find();
        $video_model->where(array('id'=>$lists['id'] ))->save(array('status' =>4)); // 改成一个临时状态，让定时任务下次请求不会在查询到此数据
        $url = 'http://16.162.206.41:9999/api/video/cut';

        $uplodainfo = postUploadFile($url,$lists['video_name'],$lists['video_address'],$type = 'text/plain');
        $uplodainfo = json_decode($uplodainfo,true);

        var_dump($uplodainfo);exit;
        if ($uplodainfo['code'] == 200){
            $hrefcontent = base64_decode($uplodainfo['data']['m3u8Str']);
            //文件名称
            $file_name = date('YmdHis', time()) . random(5, 10000000);
            //写入m3u8
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $hrefcontent);
            $video_href = 'https://' . $_SERVER['SERVER_NAME'] . '/test/' . $file_name . ".m3u8";

            $arr['href'] = $video_href;
            $arr['thumb']= $uplodainfo['data']['coverImgUrl'];
            $arr['duration'] = $uplodainfo['data']['playTime'];
            $arr['status'] = 1;
            $arr['is_downloadable'] = 0;
            $arr['filestorekey'] = $uplodainfo['data']['fileStoreKey'];
            $video_model->where(array('id' =>$lists['id'] ))->save($arr);
            unlink($lists['video_address']) ;
        }else{ // 失败的话 改回原数据
            $video_model->where(array('id'=>$lists['id'] ))->save(array('status' =>-1));
        }
        // 长视频
        $video_long_model=M("video_long");
        $long_lists = $video_long_model ->where(array('status'=>-1))->find();
        $video_long_model->where(array('id'=>$long_lists['id'] ))->save(array('status' =>4)); // 改成一个临时状态，让定时任务下次请求不会在查询到此数据
        $uplodainfo = postUploadFile($url,$long_lists['video_name'],$long_lists['video_address'],$type = 'text/plain');
        $uplodainfo = json_decode($uplodainfo,true);
        if ($uplodainfo['code'] == 200){
            $hrefcontent = base64_decode($uplodainfo['data']['m3u8Str']);
            //文件名称
            $file_name = date('YmdHis', time()) . random(5, 10000000);
            //写入m3u8
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $hrefcontent);
            $long_video_href = 'https://' . $_SERVER['SERVER_NAME'] . '/test/' . $file_name . ".m3u8";

            $long_arr['href'] = $long_video_href;
            $long_arr['thumb']= $uplodainfo['data']['coverImgUrl'];
            $long_arr['duration'] = $uplodainfo['data']['playTime'];
            $long_arr['status'] = 1;
            $long_arr['is_downloadable'] = 0;
            $long_arr['filestorekey'] = $uplodainfo['data']['fileStoreKey'];
            $video_long_model->where(array('id' =>$long_lists['id'] ))->save($long_arr);
            unlink($long_lists['video_address']) ;
        }
    }*/



   public  function  addhref(){
       $short_list = M("video")->where(array('origin'=>array('neq',3),'status'=>array('neq',4)))->field('id,href')->select();
       $playback_address= M('playback_address')->where(['java_field'=> 'aws01'])->find();
       $playback_addressOld= M('playback_address')->where(['java_field'=> 'slave-1'])->find();
       foreach ($short_list as $short_value ){
           if(file_exists($_SERVER['DOCUMENT_ROOT'].$short_value['href'])){
               $fil_contents = file_get_contents($_SERVER['DOCUMENT_ROOT'].$short_value['href']);
               $contents = str_replace($playback_addressOld['url'],$playback_address['url'],$fil_contents);

               $file_name = date('YmdHis', time()) . uniqid() . random(5);
               // 检测目录
               $checkDirRes = checkSaveDir($_SERVER['DOCUMENT_ROOT'] . "/test/");
               if($checkDirRes !== true){
                   echo '保存目录不存在: '.$checkDirRes;
                   return ;
               }
               //写入m3u8
               file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $contents);
               $video_href = '/test/' . $file_name . ".m3u8";
               M("video")->where(array('id' => $short_value['id']))->save(['href_3'=> $video_href] );

           }
       }
       $video_long_list = M("video_long")->where(array('origin'=>array('neq',3),'status'=>array('neq',4)))->field('id,href')->select();
       foreach ($video_long_list as $video_long_value ){
           if(file_exists($_SERVER['DOCUMENT_ROOT'].$video_long_value['href'])){
               $fil_contents = file_get_contents($_SERVER['DOCUMENT_ROOT'].$video_long_value['href']);
               $contents = str_replace($playback_addressOld['url'],$playback_address['url'],$fil_contents);

               $file_name = date('YmdHis', time()) . uniqid() . random(5);
               // 检测目录
               $checkDirRes = checkSaveDir($_SERVER['DOCUMENT_ROOT'] . "/test/");
               if($checkDirRes !== true){
                   echo '保存目录不存在: '.$checkDirRes;
                   return ;
               }
               //写入m3u8
               file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $contents);
               $video_href = '/test/' . $file_name . ".m3u8";
               M("video")->where(array('id' => $video_long_value['id']))->save(['href_3'=> $video_href] );

           }
       }

       $bar_list = M("bar")->where(array('video_status'=>0))->limit(20)->select();
       foreach ($bar_list as $bar_value ){
           if(file_exists($_SERVER['DOCUMENT_ROOT'].$bar_value['href'])){
               $fil_contents = file_get_contents($_SERVER['DOCUMENT_ROOT'].$bar_value['href']);
               $contents = str_replace($playback_addressOld['url'],$playback_address['url'],$fil_contents);

               $file_name = date('YmdHis', time()) . uniqid() . random(5);
               // 检测目录
               $checkDirRes = checkSaveDir($_SERVER['DOCUMENT_ROOT'] . "/test/");
               if($checkDirRes !== true){
                   echo '保存目录不存在: '.$checkDirRes;
                   return ;
               }
               //写入m3u8
               file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $contents);
               $video_href = '/test/' . $file_name . ".m3u8";
              M("bar")->where(array('id' => $bar_value['id']))->save(['href_3'=> $video_href] );

           }
       }
      ;
   }


}
