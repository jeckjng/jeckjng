<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 2020/10/18
 * Time: 21:02
 */

namespace App\Extend;

use EasySwoole\Http\AbstractInterface\Controller;

class HttpNexwork extends Controller
{

    static protected function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }


    public static function startHttpPost($url,$postData=[],$header=[],$timeOut = 3){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        // 执行后不直接打印出来
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        // 设置请求方式为post
        curl_setopt($ch,CURLOPT_POST,true);
        // post的变量
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
        // 请求头，可以传数组
        if(!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        }
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_TIMEOUT,$timeOut);
        // 跳过证书检查
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        // 不从证书中检查SSL加密算法是否存在
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        $output=curl_exec($ch);
        curl_close($ch);

        return $output;
    }



}