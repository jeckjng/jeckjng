<?php
namespace Admin\Cache;

use Admin\Model\VideoModel;
use Think\Controller;
use Common\Controller\CustRedis;

class CommonCache extends Controller {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

}
