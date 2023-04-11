<?php
//创建WebSocket Server对象，监听0.0.0.0:9502端口
$ws = new Swoole\WebSocket\Server('10.0.126.128', 9502);

//监听WebSocket连接打开事件
$ws->on('Open', function ($ws, $request) {
    echo "Message: 已连接\n";
    $ws->push($request->fd, "hello, LAWEN welcome\n");
    swoole_timer_tick(10000, function($timer_id){
       echo date('Y-m-d H:i:s')." 10s:time sendId:{$timer_id}\n";
    });
});

//监听WebSocket消息事件
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

//监听WebSocket连接关闭事件
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();