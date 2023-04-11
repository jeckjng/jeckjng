<?php
include_once './api/Common/function.php';
$verifyData = file_get_contents("php://input");
//$verifyData = "{\"action\":\"on_play\",\"client_id\":105,\"ip\":\"139.71.22.215\",\"vhost\":\"__defaultVhost__\",\"app\":\"live\",\"tcUrl\":\"rtmp://ip:1935/live?user=player&pwd=123\",\"pageUrl\":\"\"}";
$obj=json_decode($verifyData);
// echo 0;
// exit;
if ( $obj->action == "on_connect"){
    echo 0;
    exit;
}
else if ( $obj->action == "on_close"){
    echo 0;
    exit;
}
else if ( $obj->action == "on_publish"){
    $arr = parse_url($obj->tcUrl);
    $arr_query = convertUrlQuery($arr['query']);
    $stream = $obj->stream;
    //TODO 签名验证
    if($stream == "livestream"){
        //测试地址直接返回
        echo 0;
        exit;
    }

    if (1==1) {
        echo 0;
        exit;
    }
    else {
         echo 1;
         exit;
    }
}
else if ( $obj->action == "on_unpublish"){
    echo 0;
    exit;
}
else if ( $obj->action == "on_play"){
    //TODO 播流验证
    echo 0;
    exit;
}
else if ( $obj->action == "on_stop"){
    echo 0;
    exit;
}
else if ( $obj->action == "on_dvr"){
    echo 0;
    exit;
}
else{
    echo 1;
    exit;
}

function convertUrlQuery($query)
{
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}
 
function getUrlQuery($array_query)
{
    $tmp = array();
    foreach($array_query as $k=>$param)
    {
        $tmp[] = $k.'='.$param;
    }
    $params = implode('&',$tmp);
    return $params;
}
?>