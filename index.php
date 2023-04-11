<?php
/**
 * 入口文件
 * Some rights reserved：www.thinkcmf.com
 */
if (ini_get('magic_quotes_gpc')) {
	function stripslashesRecursive(array $array){
		foreach ($array as $k => $v) {
			if (is_string($v)){
				$array[$k] = stripslashes($v);
			} else if (is_array($v)){
				$array[$k] = stripslashesRecursive($v);
			}
		}
		return $array;
	}
	$_GET = stripslashesRecursive($_GET);
	$_POST = stripslashesRecursive($_POST);
}
//开启调试模式
define("APP_DEBUG", true);
//网站当前路径
define('SITE_PATH', dirname(__FILE__)."/");
//项目路径，不可更改
define('APP_PATH', SITE_PATH . 'application/');
//项目相对路径，不可更改
define('SPAPP_PATH',   SITE_PATH.'simplewind/');
//
define('SPAPP',   './application/');
//项目资源目录，不可更改
define('SPSTATIC',   SITE_PATH.'statics/');
//定义缓存存放路径
define("RUNTIME_PATH", SITE_PATH . "data/runtime/");
//静态缓存目录
define("HTML_PATH", SITE_PATH . "data/runtime/Html/");
//版本号
define("SIMPLEWIND_CMF_VERSION", 'X2.1.0');

define("THINKCMF_CORE_TAGLIBS", 'cx,Common\Lib\Taglib\TagLibSpadmin,Common\Lib\Taglib\TagLibHome');

//uc client root
define("UC_CLIENT_ROOT", './api/uc_client/');

define("EXTEND_PATH", SITE_PATH . "extend/");

if(file_exists(UC_CLIENT_ROOT."config.inc.php")){
	include UC_CLIENT_ROOT."config.inc.php";
}

// 加载所有extend/video目录下的扩展文件
foreach(glob(EXTEND_PATH.'/video/*.php') as $video_file)
{
    if(file_exists($video_file)){
        require_once $video_file;
    }
}

// 设置Session过期时间12个小时
ini_set('session.gc_maxlifetime', 60*60*12);

//载入框架核心文件
require SPAPP_PATH.'Core/ThinkPHP.php';

$gameTenantId=$_REQUEST['game_tenant_id'];
//从请求参数中取出游戏系统租户id与session中参数比对
//如参数不一致则重新获取租户信息
if( empty($_SESSION['gameTenantId']) ||  (!empty($gameTenantId) && $gameTenantId!=$_SESSION['gameTenantId'] ) ){
    $appdata= getTenantInfoFromGameTenantId($gameTenantId);
    if (empty($appdata['id'])) {
        //查找不到对应租户,使用平台租户,如不需要可注释
        $appdata=getPlatformTenantInfo();
    }

    if(empty($appdata['id'])){
        //平台租户和正常租户都不存在,返回错误信息
       // echo "Oops! Sorry, we are unable to find you! Please email us at abc123456@qq.com";
        exit();
    }
    $_SESSION['tenantId']=$appdata['id'];
    $_SESSION['gameTenantId']=$appdata['game_tenant_id'];
    $_REQUEST['tenant_id']=$_SESSION['tenantId'];
}


//if(empty($_SESSION['tenantId']))
//{
//    $appdata=getTenantInfoFromDomain($_SERVER['HTTP_HOST']);
//    if (empty($appdata['id'])) {
//
//        //开发暂时注释掉,id固定为1
//        $_SESSION['tenantId']=1;
////            echo "Oops! Sorry, we are unable to find you! Please email us at abc123456@qq.com";
////            exit();
//    }
//    else{
//        $_SESSION['tenantId']=$appdata['id'];
//    }
//}



