<?php

class Api_ShoppingVoucher extends PhalApi_Api
{

    public function getRules()
    {
        return array(
            'getShoppingVoucherList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
        );
    }

    /**
     * 获取购物券列表
     * @desc 用于获取购物券列表
     * @return int code 操作码，0表示成功
     * @return array info
     * @return int info[0].id 购物券id
     * @return float info[0].amount 购物券金额
     * @return string msg 提示信息
     */
    public function getShoppingVoucherList(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=checkNull($this->uid);
        $token=checkNull($this->token);

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_ShoppingVoucher();
        $info = $domain->getShoppingVoucherList($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

}
