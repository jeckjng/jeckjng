<?php

/**
 * 会员
 */
namespace User\Controller;
use Common\Controller\AdminbaseController;
use function PHPSTORM_META\elementType;

class AgentController extends AdminbaseController
{

    protected $users_model;

    function _initialize()
    {
        parent::_initialize();
        $this->users_model = D("Common/Users");
    }

    function rebate_conf()
    {
        $id = intval($_GET['id']);
        if ($id) {
            $rst = M("Users")->where(array("id" => $id, "user_type" => 2))->setField('ishot', '1');
            if ($rst !== false) {
                $action = "设置热门会员：{$id}";
                setAdminLog($action);
                $this->success("会员设置热门成功！");
            } else {
                $this->error('会员设置热门失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

}