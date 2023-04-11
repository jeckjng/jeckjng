<?php
namespace Api\Controller;
use Think\Controller;
use Api\Controller\AdminApiBaseController;

class MenuController extends AdminApiBaseController {

    public function __construct(){
        parent::__construct();
    }

    /*
    * 获取后台菜单执行sql
    * */
    public function get_admin_menu_rule_action(){
        $param = I('post.');
        $data = array();
        if(!IS_POST){
            $this->out_put($data, 1,'请求方式错误');
        }

        $check_res = $this->check($param);
        if($check_res !== true){
            $this->out_put($data, 1,'不合法: '.$check_res);
        }
        if(!isset($param['id']) || $param['id'] == ''){
            $this->out_put($data, 1,'参数错误');
        }
        $list = M('menu_auth_rule_action')->where(['id'=>['gt',$param['id']]])->order('id asc')->select();
        $data = $list;

        $this->out_put($data, 20000,'success');
    }


}
