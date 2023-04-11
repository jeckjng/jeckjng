<?php

class Api_WithdrawFeeConfig extends PhalApi_Api
{

    public function getRules()
    {
        return array(
            'getWithdrawFeeConfig' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
        );
    }

    /**
     * 获取提现手续费配置
     * @desc 用于获取提现手续费配置
     * @return int code 操作码，0表示成功
     * @return array info
     * @return float info[0].amount 金额
     * @return int info[0].type 类型：1.百分比，2.固定值
     * @return float info[0].fee 手续费
     * @return string msg 提示信息
     */
    public function getWithdrawFeeConfig(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=checkNull($this->uid);
        $token=checkNull($this->token);

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_WithdrawFeeConfig();
        $info = $domain->getWithdrawFeeConfig($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

}
