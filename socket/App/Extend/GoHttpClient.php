<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 2020/10/29
 * Time: 0:33
 */

namespace App\Extend;

use EasySwoole\HttpClient\HttpClient;
class GoHttpClient
{

    public $client;
    public $msg;
    public $url;

    public function __construct($url, $ssl=false, $timeout=5, $connectTimeout=10)
    {
        $this->url = $url;
    }

    // POST请求
    public function HttpPost($postData,$header=[],$timeout=3)
    {
        $client = new HttpClient($this->url);// 实列化
        $client->setTimeout(3);//设置等待超时时间
        $client->setConnectTimeout($timeout); //设置连接超时时间

        $response = $client->post($postData,$header);
        if ($response->getErrCode()) {
            return $response->getErrMsg(); // . $this->msg;
        }
        return $response->getBody();
    }

    //postJson请求
    public function HttpPostJson($post)
    {
        $this->client->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->client->setHeader('Content-Length', strlen(json_encode($post)));
        $response = $this->client->postJson(json_encode($post));
        if ($response->getErrCode()) {
            return $response->getErrMsg() . $this->msg;
        }
        return $response->getBody();
    }

    // put请求
    public function HttpPut($postData,$header=[])
    {
        if(empty($header)){
            switch (gettype($postData)) {
                case "array" :
                    $header = ["Content-Type"=>"multipart/form-data; charset=utf-8"];
                    break;
                case "string" :
                    $header = ["Content-Type"=>"application/x-www-form-urlencoded; charset=utf-8"];
                    break;
                default :
                    $header = ["Content-Type"=>"application/json; charset=utf-8"];
            }
        }

        $client = new HttpClient($this->url);// 实列化

        $response = $client->put($postData,$header);
        if ($response->getErrCode()) {
            return $response->getErrMsg(); // . $this->msg;
        }
        return $response->getBody();
    }
    // POST请求
    public function HttpPosturl($postData,$url,$header=[],$timeout=3)
    {
        $client = new HttpClient($url);// 实列化
        $client->setTimeout(3);//设置等待超时时间
        $client->setConnectTimeout($timeout); //设置连接超时时间

        $response = $client->post($postData,$header);
        if ($response->getErrCode()) {
            return $response->getErrMsg(); // . $this->msg;
        }
        return $response->getBody();
    }


}