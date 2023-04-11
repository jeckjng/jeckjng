<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <http://www.code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Think\Upload\Driver;

if (is_file(__DIR__ . '/AliyunOss/autoload.php')) {
    require_once __DIR__ . '/AliyunOss/autoload.php';
}

use OSS\OssClient;
use OSS\Core\OssException;


class Aliyunoss{
    /**
     * 上传文件根目录
     * @var string
     */
    private $rootPath;

    /**
     * 上传错误信息
     * @var string
     */
    private $error = '';

    private $config = array(
        'secretKey'      => '', //七牛服务器
        'accessKey'      => '', //七牛用户
        'domain'         => '', //七牛密码
        'bucket'         => '', //空间名称
        'timeout'        => 300, //超时时间
    );

    /**
     * 构造函数，用于设置上传根路径
     * @param array  $config FTP配置
     */
    public function __construct($config){
        $this->config = array_merge($this->config, $config);
        /* 设置根目录 */
//        $this->aliyunoss = new Aliyunoss($config);
    }

    /**
     * 检测上传根目录(七牛上传时支持自动创建目录，直接返回)
     * @param string $rootpath   根目录
     * @return boolean true-检测通过，false-检测失败
     */
    public function checkRootPath($rootpath){
        $this->rootPath = trim($rootpath, './') . '/';
        return true;
    }

    /**
     * 检测上传目录(七牛上传时支持自动创建目录，直接返回)
     * @param  string $savepath 上传目录
     * @return boolean          检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath){
        return true;
    }

    /**
     * 创建文件夹 (七牛上传时支持自动创建目录，直接返回)
     * @param  string $savepath 目录名称
     * @return boolean          true-创建成功，false-创建失败
     */
    public function mkdir($savepath){
        return true;
    }

    /**
     * 保存指定文件
     * @param  array   $file    保存的文件信息
     * @param  boolean $replace 同名文件是否覆盖
     * @return boolean          保存状态，true-成功，false-失败
     */
    public function save(&$file,$replace=true) {
        // 阿里云账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM用户进行API访问或日常运维，请登录RAM控制台创建RAM用户。
        $accessKeyId = $this->config['accessKey']; // "yourAccessKeyId";
        $accessKeySecret = $this->config['secretKey']; // "yourAccessKeySecret";
        // yourEndpoint填写Bucket所在地域对应的Endpoint。以华东1（杭州）为例，Endpoint填写为https://oss-cn-hangzhou.aliyuncs.com。
        $endpoint = $this->config['upHost']; // "yourEndpoint";
        // 填写Bucket名称，例如examplebucket。
        $bucket= $this->config['bucket']; // "examplebucket";
        // 填写Object完整路径，例如exampledir/exampleobject.txt。Object完整路径中不能包含Bucket名称。
        $object = $file['savepath'] . $file['savename']; // "exampledir/exampleobject.txt";
        // <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt。
        // 填写本地文件的完整路径，例如D:\\localpath\\examplefile.txt。如果未指定本地路径，则默认从示例程序所属项目对应本地路径中上传文件。
        $filePath = $file['tmp_name']; // "D:\\localpath\\examplefile.txt";

        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

            $result = $ossClient->uploadFile($bucket, $object, $filePath);
        } catch(OssException $e) {
            return $e->getMessage();
        }

        $domain = str_replace('https://','', $this->config['domain']);
        $domain = str_replace('http://','', $domain);
        $domain = trim($domain, '/');
        $file['url'] = 'http://'.$domain.'/'.$object; // 需要处理
        return false === $result ? false : true;

    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError(){
        return $this->aliyunoss->errorStr;
    }
}
