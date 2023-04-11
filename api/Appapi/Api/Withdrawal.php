<?php
class Api_Withdrawal extends PhalApi_Api
{

    public function getRules()
    {
        return array(
            'getCoin' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            ),
            'applyWithdrawal' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'cash_account_type' => array('name' => 'cash_account_type', 'type' => 'int', 'desc' => '提现账号类型：1.银行卡，2.USDT'),
                'bank_id' => array('name' => 'bank_id', 'type' => 'string', 'desc' => '绑定的银行卡id（银行卡提现必填）'),
                'cash_network_type' => array('name' => 'cash_network_type', 'type' => 'string', 'desc' => '网络类型：TRC20, ERC20（USDT提现必填）'),
                'virtual_coin_address' => array('name' => 'virtual_coin_address', 'type' => 'string', 'desc' => '虚拟币地址（USDT提现必填）'),
                'qr_code_url' => array('name' => 'qr_code_url', 'type' => 'string', 'desc' => '二维码地址（USDT提现必填）'),
                'coin' => array('name' => 'coin', 'type' => 'string', 'desc' => '币种id（银行卡提现必填）'),
                'amount' => array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '提现金额'),
                'type' => array('name' => 'type', 'type' => 'string', 'require' => true, 'desc' => '1:充值金额：2主播分成'),
            ),
            'withdrawalLog' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'status' => array('name' => 'status', 'type' => 'string',  'desc' => '0审核中，1审核通过，2审核拒绝'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),
        );
    }

    /**
     * 可提现币种
     * @desc 可提现币种
     * @return string msg 提示信息
     * @return array info 列表数据
     */
    public  function getCoin(){
        $rs = array('code' => 0, 'msg' => '提现币种', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Withdrawal();
        $bankList = $domain->getCoin($uid);
        $rs['info'] = $bankList;
        return $rs;
    }

    /**
     * 申请提现
     * @desc 申请提现
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return string info.cash_hour_star 提现时间（开始，单位：小时，code为2052时返回）
     * @return string info.cash_hour_end 提现时间（结束，单位：小时，code为2052时返回）
     * @return string info.cash_nosucc 提现失败订单数（code为2054时返回）
     */
    public  function applyWithdrawal(){
        $rs = array('code' => 0, 'msg' => '申请提现', 'info' => array());
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $checkToken = checkToken($uid,$token);
        $type = $this->type;
        $game_tenant_id = $this->game_tenant_id;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $bank_id = $this->bank_id;
        $coin = $this->coin;
        $amount = $this->amount;

        $cash_account_type = $this->cash_account_type;
        $cash_network_type = checkNull($this->cash_network_type);
        $virtual_coin_address = checkNull($this->virtual_coin_address);
        $qr_code_url = checkNull($this->qr_code_url);

        $domain = new Domain_Withdrawal();

        $info = $domain->applyWithdrawal($uid, $bank_id, $coin, $amount, $game_tenant_id, $type, $cash_account_type, $cash_network_type, $virtual_coin_address, $qr_code_url);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 提现记录
     * @desc 提现记录
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return info.[0]['pay_coin']  提现金额类型 1 :充值余额 2：主播分成
     * @returninfo.[0]['votes']  平台币总额
     * @returninfo.[0]['money']  提现币种金额总额
     * @returninfo.[0]['received_money']  到账金额
     * @returninfo.[0]['service_fee']  手续费
     * @return info.[0]['rnb_money']  转换成人民币金额
     *@return info.[0]['orderno']  订单号
     * @return info.[0]['status']  0提现中，1提现成功，2提现失败'
     * @return info.[0]['addtime']  提现发起时间
     * @return info.[0]['uptime']  提现审核时间
     */
    public function withdrawalLog(){
        $rs = array('code' => 0, 'msg' => '提现记录', 'info' => array());
        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $p=checkNull($this->p);
        $checkToken=checkToken($uid,$token);
        $status =$this->status;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Withdrawal();
        $info = $domain->withdrawalLog($uid,$status,$p);
        $rs['info'] = $info;
        return $rs;
    }
}
