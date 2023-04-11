<?php
namespace Rpc\Controller;
use Think\Controller;


class TestController extends Controller {
    public function index(){
        include dirname(__FILE__).'/../examples/http_client.php';

//        include dirname(__FILE__).'/../examples/tcp_client.php';
    }
}
