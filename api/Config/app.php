<?php
/**
 * 请在下面放置任何您需要的应用配置
 * 1
 */

$path = dirname(__FILE__) .'/../../data/conf/db.php';
$dbconfig = include $path;

return array(

    /**
     * 应用接口层的统一参数
     */
    'apiCommonRules' => array(
        'game_tenant_id' => array('name' => 'game_tenant_id', 'type' => 'string', 'min' => 1, 'require' => true, 'max'=>'30', 'desc' => '游戏系统租户id'),
        'language_id' => array('name' => 'language_id', 'type' => 'string', 'desc' => '语言id'),
    ),
    'REDIS_HOST' => $dbconfig['REDIS_HOST'], // "127.0.0.1",
    'REDIS_AUTH' => $dbconfig['REDIS_AUTH'], // "123456",
    'REDIS_PORT' => $dbconfig['REDIS_PORT'], // "6379",
    'REDIS_DBINDEX' => $dbconfig['REDIS_DBINDEX'], // "1",
    
    'sign_key' => '76576076c1f5f657b634e966c8836a06',
		
	'uptype'=>2,//上传方式：1表示 七牛，2表示 本地
		/**
     * 七牛相关配置
     */
    'Qiniu' =>  array(
        //统一的key
        'accessKey' => '',
        'secretKey' => '',
        //自定义配置的空间
        'space_bucket' => '',
        'space_host' => '',
    ),
		
		 /**
     * 本地上传
     */
    'UCloudEngine' => 'local',

    /**
     * 本地存储相关配置（UCloudEngine为local时的配置）
     */
    'UCloud' => array(
        //对应的文件路径
        'host' => $_SERVER["HTTP_HOST"].'/api/upload'
    ),
		
		/**
     * 云上传引擎,支持local,oss,upyun
     */
    //'UCloudEngine' => 'oss',

    /**
     * 云上传对应引擎相关配置
     * 如果UCloudEngine不为local,则需要按以下配置
     */

   /*  'UCloud' => array(
        //上传的API地址,不带http://,以下api为阿里云OSS杭州节点
        'api' => 'oss-cn-hangzhou.aliyuncs.com',

        //统一的key
        'accessKey' => '',
        'secretKey' => '',

        //自定义配置的空间
        'bucket' => '',
        'host' => 'http://image.xxx.com', //必带http:// 末尾不带/

        'timeout' => 90
    ), */

);
