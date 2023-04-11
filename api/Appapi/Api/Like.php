<?php

class Api_Like extends PhalApi_Api
{

    public function getRules()
    {
        return array(
            'getLikeConfig' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'payLikeDeposit' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'id' => array('name' => 'id', 'type' => 'int', 'require' => true, 'desc' => '点赞奖励配置id'),
            ),
             'refundLikeDeposit' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
        );

    }

    /**
     * 获取点赞奖励配置
     * @desc 用于获取点赞奖励配置
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     * @return int info[0].id 点赞奖励配置id
     * @return float info[0].reward_amount 奖励金额
     * @return int info[0].reward_count 奖励次数
     * @return int info[0].reward_type 奖励模式：1.总次数, 2.每天
     * @return float info[0].deposit 保证金
     */
    public function getLikeConfig(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = intval($this->uid);
        $token = checkNull($this->token);

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $info = Domain_Like::getInstance()->getLikeConfig($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 支付点赞保证金
     * @desc 用于支付点赞保证金
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public function payLikeDeposit(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = intval($this->uid);
        $token = checkNull($this->token);
        $id = floatval($this->id);

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $info = Domain_Like::getInstance()->payLikeDeposit($uid, $id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 点赞保证金退款申请
     * @desc 用于点赞保证金退款申请
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public function refundLikeDeposit(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = intval($this->uid);
        $token = checkNull($this->token);

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $info = Domain_Like::getInstance()->refundLikeDeposit($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

}
