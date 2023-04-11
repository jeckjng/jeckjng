<?php

/**
 * VIP管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class UploadController extends AdminbaseController {

    /*
     * 上传图片
     * */
    public function img_upload(){
        ini_set('max_execution_time',0);
        ini_set('memory_limit','4096M');
        ini_set('post_max_size','1024M');
        ini_set('upload_max_filesize','1024M');

        if ($_FILES['image']) {
            $savepath = date('Ymd') . '/';
            //上传处理类
            $config = array(
                'rootPath' => './' . C("UPLOADPATH"),
                'savePath' => $savepath,
                'maxSize' => 1024*1024*30,
                'saveName' => array('uniqid', ''),
                'exts' => array('svga'),
                'autoSub' => false,
            );
            $upload = new \Think\Upload($config);
            $info = $upload->upload();
            //开始上传
            if ($info) {
                //上传成功
                //写入附件数据库信息
                $first = array_shift($info);
                if (!empty($first['url'])) {
                    $url = $first['url'];
                } else {
                    $url = C("TMPL_PARSE_STRING.__UPLOAD__") . $savepath . $first['savename'];
                }
                $url = str_replace("http", "https", $url);
                $this->success($url);
            } else {
                //上传失败，返回错误
                $this->error($upload->getError());
            }
        }else if($_FILES['file']){
            $this->file_upload();
        } else {
            echo '缺少参数';
           $this->error('缺少参数');
       }
    }

    /*
    * wangEditor 富文本上传图片
    * */
    public function wang_editor_img_upload(){
        if (count($_FILES) > 0) {
            $savepath = date('Ymd') . '/';
            //上传处理类
            $config = array(
                'rootPath' => './' . C("UPLOADPATH"),
                'savePath' => $savepath,
                'maxSize' => 1024*1024*30,
                'saveName' => array('uniqid', ''),
                'exts' => array('svga'),
                'autoSub' => false,
            );
            $upload = new \Think\Upload($config);
            $info = $upload->upload();
            //开始上传
            if ($info) {
                //上传成功
                //写入附件数据库信息
                $first = array_shift($info);
                if (!empty($first['url'])) {
                    $url = $first['url'];
                } else {
                    $url = C("TMPL_PARSE_STRING.__UPLOAD__") . $savepath . $first['savename'];
                }
                $url = str_replace("http", "https", $url);
                $backdata = array(
                    "errno" => 0, // 即错误代码，0 表示没有错误。
                    "data" => array($url),
                );
                $this->success($backdata,'',true);
            } else {
                //上传失败，返回错误
                $this->error($upload->getError());
            }
        } else {
            $this->error('缺少参数');
        }
    }


    public function file_upload()
    {
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $videoinfo = getCutvideo($tenant_id);
        $fileStoreKey = $videoinfo['data']['fileStoreKey'];
        sleep(3);
        $this->getVideoInfo($fileStoreKey, $tenant_id);
    }

    public function getVideoInfo($filestorekey='', $tenant_id, $num=0){
        if ($filestorekey) {
            $num++;
            $config = getConfigPub($tenant_id);
            $url = $config['url_of_get_video_info_from_java'] ? trim($config['url_of_get_video_info_from_java'], '/').'?key='.$filestorekey : 'http://16.162.206.41:9999/api/store/getStoreStatus?key='.$filestorekey;
            $data = file_get_contents($url);
            if($num>60){
                $this->success([$num,$url,$data],'',true);
            }
            if ($data['code'] == '200' && $data['data']['status'] == 'success') {
                foreach ($data['data']['m3u8'] as $m3u8Key => $m3u8value) {
                    $file_name = date('YmdHis', time()) . random(5, 10000000);
                    //写入m3u8
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test/" . $file_name . ".m3u8", $m3u8value['m3u8Content']);
                    $data['video_href'] = '/test/' . $file_name . ".m3u8";
                    $playback_address = M('playback_address')->where(array('java_field' => $m3u8value['name']))->find();
                    $data['url'] = $playback_address['url'];
                }
                return $this->getVideoInfo($data,$num);
            }else{
                sleep(1);
                return $this->getVideoInfo($filestorekey,$num);
            }
        }else{
            $this->success(['$filestorekey'=>$filestorekey,$num],'',true);
        }
    }

    /*
     * 下载文件
     * */
    public function downFile(){
        $file_url = $_GET['file_url'];
        $fileinfo = pathinfo($file_url);
        header('Content-type: application/x-' . $fileinfo['extension']);
        header('Content-Disposition: attachment; filename=' . $fileinfo['basename']);
        readfile($file_url);
        exit();
    }

    /*
     * 上传视频中转到java
     * */
    public function video_upload_to_java()
    {
        $param = I('param.');
        if(empty($_FILES)){
            setAdminLog('【视频上传】-失败-环境配置问题-'.json_encode($_FILES));
            $this->out_put(['FILES'=>$_FILES], 0, '视频上传失败，请联系客服');
        }

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $file = $_FILES['file'];        //文件信息
        $filename = $file['name'];      //本地文件名
        $tmpFile = $file['tmp_name'];   //临时文件名
        $fileType = $file['type'];      //文件类型

        $config = getConfigPub($tenant_id);
        if(!$config['url_of_push_to_java_cut_video']){
            $this->out_put(['FILES'=>$_FILES], 0, '视频上传失败，未设置视频上传url');
        }
        $cut_video_url_array = explode("\n",$config['url_of_push_to_java_cut_video']);
        foreach ($cut_video_url_array as $key=>$val){
            if(!$val || !trim($val)){
                continue;
            }
            $url = trim($val, '/');
            $result = postUploadFile($url, $filename, $tmpFile, 'text/plain');
            $result = json_decode($result,true);
            if (!isset($result['code']) || $result['code'] != 200){
                setAdminLog('【视频上传】-失败-'.$url.'-'.json_encode($result));
                continue;
            }
            if(isset($result['data']) && isset($result['data']['fileStoreKey']) && $result['data']['fileStoreKey']){
                $this->out_put(['FILES'=>$_FILES, 'url'=>$url, 'result'=>$result, 'fileStoreKey'=>$result['data']['fileStoreKey']], 200, 'success');
            }else{
                setAdminLog('【视频上传】-失败-'.$url.'-'.json_encode($result));
                continue;
            }
        }
        $this->out_put(['FILES'=>$_FILES, 'url'=>$url, $result], 0, '视频上传失败，请联系客服');
    }

}
