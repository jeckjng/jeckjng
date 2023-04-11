<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 20:38
 */
class Api_Usdt extends PhalApi_Api
{
    public function getRules()
    {
        return array(
            'bindingUsdtAddress' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'address' => array('name' => 'address', 'type' => 'string', 'require' => true, 'desc' => 'USDT地址'),
                'network_type' => array('name' => 'network_type', 'type' => 'string', 'require' => true, 'desc' => '网络类型：TRC20, ERC20'),
                'qrcode' => array('name' => 'qrcode', 'type' => 'string', 'require' => true, 'desc' => 'USDT二维码URL'),
            ),
            'myUsdtAddress' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),

            ),
        );

    }

    /**
     * 绑定USDT地址
     * @desc 绑定USDT地址
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     */
    public  function bindingUsdtAddress(){
        $rs = array('code' => 0, 'msg' => '添加成功', 'info' => array());

        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $address=checkNull($this->address);
        $network_type=checkNull($this->network_type);
        $qrcode=checkNull($this->qrcode);

        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }

        $result = Domain_Usdt::getInstance()->bindingUsdtAddress($uid, $address, $network_type, $qrcode);

        $rs['code'] = isset($result['code']) && $result['code'] ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) && $result['msg'] ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) && $result['info'] ? $result['info'] : $rs['info'];

        return $rs;
    }
    /**
     * 获取我的USDT地址列表
     * @desc 获取我的USDT地址列表
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     * @return int info.0.id ID编号
     * @return string info.0.address USDT地址
     * @return string info.0.network_type USDT网络类型
     * @return string info.0.qrcode USDT二维码
     */
    public  function myUsdtAddress(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);

        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }

        $result = Domain_Usdt::getInstance()->myUsdtAddress($uid);

        $rs['code'] = isset($result['code']) ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) ? $result['info'] : $rs['info'];
        return $rs;
    }
}