<?php
namespace Common\Controller;
use Common\Controller\HomebaseController;
class MemberbaseController extends HomebaseController{
	
	function _initialize() {
		parent::_initialize();

        // 设置时区
        date_default_timezone_set('Asia/Shanghai');

		$this->check_login();
		$this->check_user();
	}
	
}