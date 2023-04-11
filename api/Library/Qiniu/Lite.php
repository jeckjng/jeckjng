<?php
/**
 * 七牛接口调用
 * 1、图片文件上传
 *
 * 参考：http://developer.qiniu.com/docs/v6/sdk/php-sdk.html
 *
 * @author: dogstar 2015-03-17
 */

require_once dirname(__FILE__) . '/qiniu/io.php';
require_once dirname(__FILE__) . '/qiniu/rs.php';

class Qiniu_Lite {

    protected $config;

    /**
     * @param string $config['accessKey']  统一的key
     * @param string $config['secretKey']
     * @param string $config['space_bucket']  自定义配置的空间
     * @param string $config['space_host']  
     */
    public function __construct($config = NULL) {
        $this->config = $config;

        if ($this->config === NULL) {
            $this->config = DI()->config->get('app.Qiniu');
            $info = getStorage();
            $type = 'Qiniu';
            if($info && isset($info['storage']) && isset($info['storage'][$type])){
                $this->config = array(
                    'accessKey' => $info['storage'][$type]['accessKey'],
                    'secretKey' => $info['storage'][$type]['secretKey'],
                    'domain' => $info['storage'][$type]['domain'],
                    'space_host' => $info['storage'][$type]['upHost'],
                    'space_bucket' => $info['storage'][$type]['bucket'],
                );
            }
        }
    }

    /**
     * 文件上传
     * @param string $filePath 待上传文件的绝对路径
     * @return string 上传成功后的URL，失败时返回空
     */
    public function uploadFile($filePath)
    {
        $fileUrl = '';

        if (!file_exists($filePath)) {
            return $fileUrl;
        }
        $config = $this->config;

        /* 获取上传文件后缀 */
        $file_ext = isset($_FILES['file']) && isset($_FILES['file']['name']) ? pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION) : '';

        $fileName = date('YmdHis_', $_SERVER['REQUEST_TIME']) . md5(PhalApi_Tool::createRandStr(8) . microtime(true));
        $fileName = $file_ext ? $fileName.'.'.$file_ext : $fileName;

        Qiniu_SetKeys($config['accessKey'], $config['secretKey']);
        $putPolicy = new Qiniu_RS_PutPolicy($config['space_bucket']);
        $upToken = $putPolicy->Token(null);
        $putExtra = new Qiniu_PutExtra();
        $putExtra->Crc32 = 1;

        global $QINIU_UP_HOST;
        $QINIU_UP_HOST = $this->config['space_host'];

        list($ret, $err) = Qiniu_PutFile($upToken, $fileName, $filePath, $putExtra);

        if ($err !== null) {
            DI()->logger->debug('failed to upload file to qiniu', 
                array('Err' => $err->Err, 'Reqid' => $err->Reqid, 'Details' => $err->Details, 'Code' => $err->Code));
        } else {
            $fileUrl = 'https://'.$config['domain'] . '/' . $fileName;
            DI()->logger->debug('succeed to upload file to qiniu', $ret);
        }

        return $fileUrl;
    }
    
	/**
     * 获取七牛Token
     * @return string 七牛Token
     */
    public function getQiniuToken()
    { 
        $config = $this->config;
        Qiniu_SetKeys($config['accessKey'], $config['secretKey']);
        $putPolicy = new Qiniu_RS_PutPolicy($config['space_bucket']);
        $upToken = $putPolicy->Token(null);
        return $upToken;	
    }
    
    /**
     * 获取七牛Token(方法重写)
     * @return string 七牛Token
     */
    public function getQiniuToken2($accesskey,$secretkey,$bucket){

        Qiniu_SetKeys($accesskey, $secretkey);
        $putPolicy = new Qiniu_RS_PutPolicy($bucket);
        $upToken = $putPolicy->Token(null);
        return $upToken;    
    }
}
