<?php


// 加载文件
foreach (glob(__DIR__ . '/src/php/rpcx/*.php') as $start_file) {
    require_once $start_file;
}
// 加载文件
foreach (glob(__DIR__ . '/src/php/rpcx/codec/*.php') as $start_file) {
    require_once $start_file;
}
// 加载文件
foreach (glob(__DIR__ . '/src/php/rpcx/selector/*.php') as $start_file) {
    require_once $start_file;
}
// 加载文件
foreach (glob(__DIR__ . '/src/php/rpcx/transport/*.php') as $start_file) {
    require_once $start_file;
}
// 加载文件
foreach (glob(__DIR__ . '/src/php/rpcx/utils/*.php') as $start_file) {
    require_once $start_file;
}
