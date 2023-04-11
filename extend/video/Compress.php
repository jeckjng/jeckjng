<?php

namespace extend\video;

class Compress{

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function compressMp4($video_dir, $filename)
    {
        $out_path = SITE_PATH . 'data/runtime/video/';
        if(!is_dir($out_path)){
            mkdir($out_path,0777);
        }
        if(!is_dir($out_path)){
            return SITE_PATH . 'date/runtime/ 目录没有权限';
        }

        $out_dir = $out_path . mt_rand(10000, 99999) . date('Y-m-d-H:i:s') . '_smallVideo' . ".mp4"; //压缩后的视频

//        $out_dir = round(filesize($video_dir)/1024).' KB';
//        return $out_dir;


        $reduce_result = $this->reduceVideo($video_dir, $out_dir);
        return $reduce_result;
    }

    //视频压缩
    public function reduceVideo($video_dir,$out_dir){
//        return  false;
        if(!file_exists($video_dir)){
            return  false;
        }
        $toSize = "800M"; //最大不超过8M
        //判断文件大小
        $file_size = filesize($video_dir);
        $file_size = intval($file_size / (1024*1024));
        if($file_size < 1){
//            $str =  base64_encode(file_get_contents($video_dir));
            return $video_dir; // 小于9m直接返回保存地址
        }
        // -y 自动确认覆盖  -s 压缩后分辨率 -b:v 视频码率  -fs $toSize 最大视频大小,超过后裁剪
        $shell = "ffmpeg -i " . $video_dir . " -y  -s 360x640 -b:v 862k  -fs " . $toSize . "  ". $out_dir . " 2>&1";
        return $shell;
        exec($shell, $output, $ret);
        if($ret == 0){
            //ret 压缩执行成功返回0 执行失败返回1
//            $str =  base64_encode(file_get_contents($out_dir));
            return $out_dir;
        }else{
            if(file_exists($out_dir)){
//                @unlink($out_dir); // 删除本地失败的
            }
            return  false;
        }
    }

    public function reduceImage($image_path,$ossClient){
        $file_info = $ossClient->getObject(
            config('filesystems.disks.oss-private.bucket'),
            $image_path,
            [OssClient::OSS_PROCESS=>'image/info']
        );
        $file_info = json_decode($file_info,true);
        $file_size = $file_info['FileSize']['value'] / 1024;
        $rate = 100;
        if ($file_size > 1024){
            $rate = floor(1024 / $file_size * (100 - (350 / $file_size * 100)));
        }
        $options = [
            OssClient::OSS_PROCESS => "image/resize,p_{$rate}"
        ];
        $image_path = $ossClient->signUrl(
            config('filesystems.disks.oss-private.bucket'),
            $image_path,
            600,
            OssClient::OSS_HTTP_GET,
            $options
        );
        return $image_path;
    }

}

