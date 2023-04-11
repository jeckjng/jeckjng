<?php
namespace Api\Controller;
use Think\Controller;
use Api\Controller\AdminApiBaseController;

class ShortMessageController extends AdminApiBaseController {

    public function __construct(){
        parent::__construct();
    }

    /*
    * 短信发送
    * */
    public function send_message(){
        $param = I('post.');
        $param = empty($param) ? I('get.') : $param;
        if(empty($param)){
            $param = getcaches('Api_ShortMessage_send_message');
        }
        foreach ($param as $key=>$val){
            $param[$key] = html_entity_decode($val);
        }
        $data = array();
        if(!IS_POST){
//            $this->out_put($data, 1,'请求方式错误');
        }

        $check_res = $this->check($param);
        if($check_res !== true){
            $this->out_put($data, 1,'不合法: '.$check_res.'  method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($_REQUEST));
        }
        if(!isset($param['url']) || $param['url'] == ''){
            $this->out_put($data, 1,'参数错误 method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($_REQUEST));
        }
        if(!isset($param['data']) || $param['data'] == ''){
            $this->out_put($data, 1,'参数错误 method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($_REQUEST));
        }

        $post_data = json_decode($param['data'], true);
        $result = http_post($param['url'], $post_data);

        $this->out_put($result, 20000,'success method: '.$_SERVER['REQUEST_METHOD'].' '.json_encode($_REQUEST));
    }


}
