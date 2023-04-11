<?php
/**
 * $APP_NAME 统一入口
 */

require_once dirname(__FILE__) .'/init.php';
require_once dirname(__FILE__) .'/qiniucdn/Pili_v2.php';
//装载你的接口
DI()->loader->addDirs('Appapi');

// 入口指定时区，后期可以通过后台设定
date_default_timezone_set('Asia/Shanghai');

/** ------------- 响应接口请求 ---------------- **/

$api = new PhalApi();
$rs = $api->response();
$rs->output();

