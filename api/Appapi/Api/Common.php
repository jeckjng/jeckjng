<?php

class Api_Common extends PhalApi_Api {

	public function getRules() {
		return array(
            'getLevelList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getAwardLogList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'p' => array('name' => 'p', 'type' => 'int', 'default'=>'1' ,'desc' => '页数'),
            ),
		);
	}

    /**
     * 获取用户等级列表
     * @desc 用于获取用户等级列表
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表信息
     */
    public function getLevelList(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=$this->uid;
        $language_id = $this->language_id;
        $token = checkNull($this->token);

        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Common();
        $info = $domain->getLevelList();

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取奖励记录列表
     * @desc 用于获取用户等级列表
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表信息
     * @return int info[0].id 奖励记录ID
     * @return int info[0].uid 用户ID
     * @return int info[0].type 奖励类型（1任务奖励、2签到奖励、3邀请好友注册奖励、4好友消费奖励、5转盘奖励）
     * @return int info[0].data_type （1金额、2碎片、3转盘次数）
     * @return float info[0].amount data_type 1=金额 | data_type 2=碎片值 | data_type 3=转盘次数
     * @return float info[0].completion_value 碎片已完成数量
     * @return string info[0].award_name 奖励名称
     * @return string info[0].user_login type =3 或者 =4 展示来源用户名
     * @return int info[0].status 状态(1已完成 2未完成 3已操作)
     */
    public function getAwardLogList(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=$this->uid;
        $language_id = $this->language_id;
        $token = checkNull($this->token);

        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Common();
        $rs['info'] = $domain->getAwardLogList($uid,$this->p);
        return $rs;
    }

}
