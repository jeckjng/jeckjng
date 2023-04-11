<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 20:38
 */
class Api_Vip extends PhalApi_Api
{
    public function getRules()
    {
        return array(

            'vipList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1,  'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户Token'),

            ),

            'buyVip' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'vip_id' =>array('name' => 'vip_id', 'type' => 'string', 'require' => true, 'desc' => 'vip id'),
                'coin' => array('name' => 'coin', 'type' => 'string', 'require' => true, 'desc' => '价格（平台币）'),
            ),
            'welfareList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),

            ),
            'exchangeWelfare' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'welfare_id' =>array('name' => 'welfare_id', 'type' => 'string', 'require' => true, 'desc' => '福利商品id'),
                'consignee' => array('name' => 'consignee', 'type' => 'string', 'require' => true, 'desc' => '收货人'),
                'phone' => array('name' => 'phone', 'type' => 'string', 'require' => true, 'desc' => '电话号码'),
                'address' => array('name' => 'address', 'type' => 'string', 'require' => true, 'desc' => '收货地址'),
            ),
            'exchangeWelfareLog' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'string', 'require' => true, 'desc' => '分页'),
            ),
            'welfareInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'welfare_id' => array('name' => 'welfare_id', 'type' => 'string', 'require' => true, 'desc' => '福利商品id'),
            ),

            'freeVip' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            ),
            'refundVip' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            ),

        );

    }
    /**
     * 会员等级
     * @desc 会员等级
     * @return string msg 提示信息
     * @return array info 列表数据
     *
     * @return string info[0].name 会员名称
     * @return string info[0].coin 所需金币
     * @return string info[0].length  会员时间（月）
     * @return string info[0].orderno 会员等级
     *  @return string info[0].give_data 赠送时间（天）
     */
    public  function vipList(){
        $rs = array('code' => 0, 'msg' => 'vip列表', 'info' => array());
        $domain = new Domain_Vip();
        $bankList = $domain->vipList();
        $rs['info'] = $bankList;
        return $rs;
    }

    /**
     * 购买会员
     * @desc 购买会员
     * @return string msg 提示信息
     * @return array info 列表数据
     */
    public  function buyVip(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $uid=checkNull($this->uid);
        $game_tenant_id = $this->game_tenant_id;
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $vip_id = $this->vip_id;
        $coin = $this->coin;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Vip();
        $info = $domain->buyVip($uid,$vip_id,$coin,$game_tenant_id);
        if ($info ===1001 ){
            $rs['code'] = 1001;
            $rs['msg'] = '支付金额有误';
            return $rs;
        }
        if ($info ===800 ){
            $rs['code'] = 800;
            $rs['msg'] = '当前为游客';
            return $rs;
        }
        if ($info ===1002 ){
            $rs['code'] = 1002;
            $rs['msg'] = '余额不足';
            return $rs;
        }
        if ($info === 1003 ){
            $rs['code'] = 1003;
            $rs['msg'] = '请缴纳更高级保证金';
            return $rs;
        }
        if ($info === 1004 ){
            $rs['code'] = 1004;
            $rs['msg'] = '已领取免费vip';
            return $rs;
        }
        if ($info === 1005 ){
            $rs['code'] = 1005;
            $rs['msg'] = '此保证金已交';
            return $rs;
        }
        if ($info === 1006 ){
            $rs['code'] = 1006;
            $rs['msg'] = '当前存在退款中的保证金，请联系客服先退款';
            return $rs;
        }
        if ($info === 1007 ){
            $rs['code'] = 1007;
            $rs['msg'] = '不能降低更少保证金';
            return $rs;
        }

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];

        return $rs;
    }

    /**
     * 福利列表
     * @desc 福利列表
     * @return string msg 提示信息
     * @return array info 列表数据
     */
    public function welfareList(){
        $rs = array('code' => 0, 'msg' => '福利列表', 'info' => array());
        $domain = new Domain_Vip();
        $bankList = $domain->welfareList();
        $rs['info'] = $bankList;
        return $rs;
    }

    /**
     * 福利兑换
     * @desc 福利兑换
     * @return string msg 提示信息
     * @return array info 列表数据
     */
    public function exchangeWelfare(){
        $rs = array('code' => 0, 'msg' => '兑换成功', 'info' => array());
        $uid=checkNull($this->uid);
        $game_tenant_id = $this->game_tenant_id;
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        $welfare_id = $this->welfare_id;
        $consignee = $this->consignee;
        $phone = $this->phone;
        $address = $this->address;
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Vip();
        $info = $domain->exchangeWelfare($uid,$welfare_id,$consignee,$phone,$address,$game_tenant_id);
        if ($info === 1001){
            $rs['code'] = 1001;
            $rs['msg'] = '积分不足！';
            return $rs;
        }
        return $rs;
    }
    /**
     * 福利兑换记录
     * @desc 福利兑换记录
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return array info.[0].welfare_name 兑换商品名称
     *@return array info.[0].status 1申请中：2兑换成功，3兑换失败
     */
    public  function exchangeWelfareLog(){
        $rs = array('code' => 0, 'msg' => '兑换记录', 'info' => array());
        $uid=checkNull($this->uid);
        $game_tenant_id = $this->game_tenant_id;
        $p = $this->p;
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Vip();
        $info = $domain->exchangeWelfareLog($uid,$p);
        $rs['info'] = $info;
        return $rs;

    }
    /**
     * 福利商品详情
     * @desc 福利商品详情
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return array info.[0].welfare_name 兑换商品名称

     */
    public  function welfareInfo(){
        $rs = array('code' => 0, 'msg' => '福利列表', 'info' => array());
        $welfare_id = $this->welfare_id;
        $domain = new Domain_Vip();
        $bankList = $domain->welfareInfo($welfare_id);
        $rs['info'] = $bankList;
        return $rs;
    }

    /**
     * 免费vip
     * @desc 免费vip
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return array

     */
    public function freeVip(){
        $rs = array('code' => 0, 'msg' => '领取免费vip', 'info' => array());
        $uid= $this->uid;
        $domain = new Domain_Vip();
        $freeVip = $domain->freeVip($uid);
        if (isset($freeVip['code']) && $freeVip['code'] == 1001){
            $rs['code'] = 1001;
            $rs['msg'] = $freeVip['msg'];
            return $rs;
        }
        if ($freeVip){
            $rs['info'] =$freeVip ;
        }
        return $rs;
    }

    /**
     * vip保证金退款申请
     * @desc vip保证金退款申请
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return array

     */
    public  function refundVip(){
        $rs = array('code' => 0, 'msg' => '您的退款申请已提交，请耐心等待', 'info' => array());
        $uid= $this->uid;
        $domain = new Domain_Vip();
        $token=checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if($checkToken===700){
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $info = $domain->refundVip($uid);
        if ( $info === 1001){
            $rs['code'] = 1001;
            $rs['msg'] = '无可退款vip保证金';
            return $rs;
        }

        if ($info === 1002){
            $rs['code'] = 1002;
            $rs['msg'] = '您的退款申请已提交，请耐心等待';
            return $rs;
        }

        return $rs;
    }
}