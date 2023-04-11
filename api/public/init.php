<?php
/**
 * 统一初始化
 */
 
/** ---------------- 根目录定义，自动加载 ---------------- **/

// 入口指定时区，后期可以通过后台设定
date_default_timezone_set('Asia/Shanghai');

defined('API_ROOT') || define('API_ROOT', dirname(__FILE__) . '/..');

require_once API_ROOT . '/PhalApi/PhalApi.php';
require_once API_ROOT . '/vendor/autoload.php';
$loader = new PhalApi_Loader(API_ROOT, 'Library');
/** ---------------- 注册&初始化 基本服务组件 ---------------- **/

//自动加载
DI()->loader = $loader;

//配置
DI()->config = new PhalApi_Config_File(API_ROOT . '/Config');

//调试模式，$_GET['__debug__']可自行改名
DI()->debug = !empty($_GET['__debug__']) ? true : DI()->config->get('sys.debug');

//日记纪录
DI()->logger = new PhalApi_Logger_File(API_ROOT . '/Runtime', PhalApi_Logger::LOG_LEVEL_DEBUG | PhalApi_Logger::LOG_LEVEL_INFO | PhalApi_Logger::LOG_LEVEL_ERROR);

//数据操作 - 基于NotORM，$_GET['__sql__']可自行改名
DI()->notorm = new PhalApi_DB_NotORM(DI()->config->get('dbs'), !empty($_GET['__sql__']));

//翻译语言包设定
SL('zh_cn');

/** ---------------- 定制注册 可选服务组件 ---------------- **/
require_once API_ROOT . '/Common/CustRedis.php';
require_once API_ROOT . '/Common/functions.php';
require_once API_ROOT . '/Library/Upload/Aliyunoss.php';
if(!DI()->redis){
    DI()->redis=connectionRedis();
}

/**
//签名验证服务
DI()->filter = 'PhalApi_Filter_SimpleMD5';
 */

/**
//缓存 - Memcache/Memcached
DI()->cache = function () {
    return new PhalApi_Cache_Memcache(DI()->config->get('sys.mc'));
};
 */

/**
//支持JsonP的返回
if (!empty($_GET['callback'])) {
    DI()->response = new PhalApi_Response_JsonP($_GET['callback']);
}
 */
   /* 七牛上传 */
  DI()->qiniu = new Qiniu_Lite();
 
    /* 本地/云 上传 */
 DI()->ucloud = new UCloud_Lite();
if ($_GET['service'] != 'Charge.notify'){
    if(!session_id())
    {
        session_start();
        $_SESSION['session_id'] = session_id();
    }
    $_SESSION['session_id'] = session_id();

    $gameTenantId=$_REQUEST['game_tenant_id'];
//从请求参数中取出游戏系统租户id与session中参数比对
//如参数不一致则重新获取租户信息
    if( empty($_SESSION['gameTenantId']) || (!empty($gameTenantId) && $gameTenantId!=$_SESSION['gameTenantId'] )){
        $appdata= getTenantInfoFromGameTenantId($gameTenantId);
        if (empty($appdata['id'])) {
            //查找不到对应租户,使用平台租户,如不需要可注释
            $appdata=getPlatformTenantInfo();
        }

        if(empty($appdata['id'])){
            //平台租户和正常租户都不存在,返回错误信息
            echo "Oops! Sorry, we are unable to find you! Please email us at abc123456@qq.com";
            exit();
        }
        $_SESSION['tenantId']=$appdata['id'];
        $_SESSION['gameTenantId']=$appdata['game_tenant_id'];
        $_REQUEST['tenant_id']=$_SESSION['tenantId'];
    }
}

//


//$tenantId=$_REQUEST['tenant_id'];
//if(is_numeric($tenantId)){
//    $_SESSION['tenantId']=$tenantId;
//}
//
//if(empty($_SESSION['tenantId']))
//{
//    $gameTenantId=$_REQUEST['game_tenant_id'];
//    if(is_numeric($gameTenantId)){
//       $appdata= getTenantInfoFromGameTenantId($gameTenantId);
//
//    }
//    else{
//        $appdata=getTenantInfoFromDomain($_SERVER['HTTP_HOST']);
//    }
//
//    if (empty($appdata['id'])) {
//        //如果根据直播系统租户id/游戏系统租户id/直播系统租户域名均未获取到租户数据,默认取平台租户的信息
//        $appdata=getPlatformTenantInfo();
//
//        //开发暂时注释掉,id固定为1(平台租户)
//        $_SESSION['tenantId']=$appdata['id'];
////            echo "Oops! Sorry, we are unable to find you! Please email us at abc123456@qq.com";
////            exit();
//    }
//    else{
//        $_SESSION['tenantId']=$appdata['id'];
//        $_SESSION['gameTenantId']=$appdata['game_tenant_id'];
//    }
//}
//$_REQUEST['tenant_id']=$_SESSION['tenantId'];

