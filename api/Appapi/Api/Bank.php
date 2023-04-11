<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 20:38
 */
class Api_Bank extends PhalApi_Api
{
    public function getRules()
    {
        return array(
            'bankList' => array(),
            'bindingBank' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'bank_name' => array('name' => 'bank_name', 'type' => 'string', 'require' => true, 'desc' => '银行名'),
                'bank_id' => array('name' => 'bank_id', 'type' => 'string', 'require' => true, 'desc' => '银行id'),
                'real_name' => array('name' => 'real_name', 'type' => 'string', 'require' => true, 'desc' => '真实姓名'),
                'bank_number' => array('name' => 'bank_number', 'type' => 'string', 'require' => true, 'desc' => '银行卡号'),
                'phone' => array('name' => 'phone', 'type' => 'string', 'require' => true, 'desc' => '手机号'),
            ),
            'myBlanCard' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),

            ),
        );

    }
    /**
     * 银行列表
     * @desc 银行列表
     * @return string msg 提示信息
     * @return array info
     */
    public  function bankList(){
        $rs = array('code' => 0, 'msg' => '银行列表', 'info' => array());
        $domain = new Domain_Bank();
        $bankList = $domain->bankList();
        $rs['info'] = $bankList;
        return $rs;
    }
    /**
     * 绑定银行卡
     * @desc 绑定银行卡
     * @return string msg 提示信息
     */
    public  function bindingBank(){
        $rs = array('code' => 0, 'msg' => '添加成功', 'info' => array());
        $uid=checkNull($this->uid);
        $bank_name=checkNull($this->bank_name);
        $bank_id=checkNull($this->bank_id);
        $real_name=checkNull($this->real_name);
        $bank_number=checkNull($this->bank_number);
        $phone = checkNull($this->phone);
        $game_tenant_id = $this->game_tenant_id;
        /*$code = verifyBankCard($bank_number);*/
        if (!is_numeric($bank_number) || empty($bank_number)){
            $rs['code'] = 1002;
            $rs['msg'] = '银行卡账号有误，请查看';
            return $rs;

        }

        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);

        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $data = array(
            'uid' => $uid,
            'bank_name' => $bank_name,
            'real_name' => $real_name,
            'bank_number' => $bank_number,
            'bank_id' => $bank_id,
            'tenant_id' => getTenantId(),
            'phone'  =>  $phone
        );

        $domain = new Domain_Bank();
        $result = $domain->bindingBank($data, $uid, $real_name, $bank_number);
        if ($result == 1000){
            $rs['code'] = 1000;
            $rs['msg'] = '添加失败';
            return $rs;
        }elseif ($result == 1001){
            $rs['code'] = 1001;
            $rs['msg'] = '此银行卡号已绑定';
            return $rs;
        }elseif ($result == 1003){
            $rs['code'] = 1003;
            $rs['msg'] = '银行真实姓名与以前绑定的不一致';
        }

        $rs['code'] = isset($result['code']) ? $result['code'] : $rs['code'];
        $rs['msg'] = isset($result['msg']) ? $result['msg'] : $rs['msg'];
        $rs['info'] = isset($result['info']) ? $result['info'] : $rs['info'];

        return $rs;
    }
    /**
     * 我的银行卡
     * @desc 我的银行卡
     * @return string msg 提示信息
     * @return array info 列表数据
     */
    public  function myBlanCard(){
        $rs = array('code' => 0, 'msg' => '银行列表', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $game_tenant_id = $this->game_tenant_id;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Bank();
        $bankList = $domain->myBlanCard($uid,$game_tenant_id);
        $rs['info'] = $bankList;
        return $rs;
    }
}