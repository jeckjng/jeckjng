<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/6/2
 * Time: 20:29
 */

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class PayController extends AdminbaseController
{

    private $status_list = array(
        '0' => '关闭',
        '1' => '开启',
    );

    protected $nav_model;
    protected $navcat_model;


    function _initialize()
    {
        parent::_initialize();
        $this->channel_model = D("Common/channel");
        $this->offlinepay_model = D("Common/offlinepay");
    }

    /**
     * 渠道列表
     */
    public function channellist()
    {
        $param = I('param.');
        $map = array();

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $map['tenant_id'] = $tenant_id;
        $param['tenant_id'] = $tenant_id;

        if(isset($param['status'])){
            if($param['status'] != '-1'){
                $map['status'] = $param['status'];
            }
        }else{
            $map['status'] = '1';
            $param['status'] = '1';
        }

        $rateModel = M("rate");
        $count = $this->channel_model->where($map)->count();
        $page = $this->page($count);
        $list = $this->channel_model->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach ($list as $key => $value) {
            $rateInfo = $rateModel->where(array('id' => $value['coin_id']))->find();
            $list[$key]['currency_name'] = $rateInfo['name'];
            $list[$key]['currency_code'] = $rateInfo['code'];
            $list[$key]['rate'] = $rateInfo['rate'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign("list", $list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list',getTenantList());
        $this->assign('status_list',$this->status_list);
        $this->assign('param', $param);
        $this->display();
    }

    /**
     * 修改渠道状态
     */
    public function upstatus()
    {
        $id = intval(I("get.id"));
        $status = intval(I("get.status"));
        if ($this->channel_model->where("id=$id")->save(array('status' => $status)) !== false) {

            $this->success("设置成功！");
        } else {
            $this->error("设置失败！");
        }

    }

    /*
     * 添加渠道
     */
    public function channeladd()
    {
        $rateModel = M("rate");
        $tenantId=getTenantIds();
        $rateInfo = $rateModel->where(['tenant_id'=>$tenantId])->select();
        if (empty($rateInfo)) {
            $this->error("请先添加币种！", '/Admin/Rate/index');
        }
        $gameid = getGameTenantIds();
        $RegurlModel  =  M('users_reg_url');
        $regurlList =  $RegurlModel->where(['tenant_id'=> getTenantIds()])->select();

        $Vip=M("vip");
        $vipList =$Vip->where('tenant_id="'.getTenantIds().'"')->select();
        $this->assign("viplist", $vipList);
        $this->assign("regurlList", $regurlList);
        $this->assign("gameid", $gameid);


        $this->assign("rateInfo", $rateInfo);
        $this->display();
    }

    public function channeledit(){
        $id = intval(I("get.id"));
        $rateModel = M("rate");
        $Vip=M("vip");
        $vipList =$Vip->where('tenant_id="'.getTenantIds().'"')->select();
        $this->assign("viplist", $vipList);
        $tenantId=getTenantIds();
        $rateInfo = $rateModel->where(['tenant_id'=>$tenantId])->select();
        $channel = $this->channel_model->where("id=$id")->find();
        if (IS_POST){
            $id = intval(I("post.id"));
            $type = I("post.type");
            $chanelName = I("post.chanel_name");
            $code = I("post.code");
            $status = I("post.status");
            $coin_id = I("post.coin_id");
            $icon =   I("post.icon");
            $channel = M("channel");
            $key = I("post.key_id");
            if ($key){
                $keyString = implode(',',$key);
            }else{
                $keyString = '';
            }
            $vip_id = $_POST['vip_id'];
            if ($vip_id){
                $vipString = implode(',',$vip_id);
            }else{
                $vipString = '';
            }

            $tenantId=getTenantIds();

            $channel->where(['id'=> $id])->save(
                array(
                    'type' => $type,
                    'channel_name' => $chanelName,
                    'code' => $code,
                    'status' => $status,
                    'addtime' => time(),
                    'coin_id' => $coin_id,
                    'icon' => $icon,
                    'tenant_id'=>$tenantId,
                    'reg_url_id' => $keyString,
                    'vip_id' => $vipString,
                )
            );
            $action = '修改成功';
            setAdminLog($action);
            $this->success("修改成功！", U("Pay/channellist"));
        }
        $gameid = getGameTenantIds();
        $urlid = explode(',',$channel['reg_url_id']);
        $this->assign("url_id", $urlid);
        $vipid = explode(',',$channel['vip_id']);
        $this->assign("vip_id", $vipid);
        $RegurlModel  =  M('users_reg_url');
        $regurlList =  $RegurlModel->where(['tenant_id'=> getTenantIds()])->select();
        $this->assign("regurlList", $regurlList);
        $this->assign("gameid", $gameid);
        $this->assign("rateInfo", $rateInfo);
        $this->assign("channel", $channel);
        $this->display();
    }

    /**
     * 添加渠道
     */
    public function channel_add_post()
    {
        $type = I("post.type");
        $chanelName = I("post.chanel_name");
        $code = I("post.code");
        $status = I("post.status");
        $coin_id = I("post.coin_id");
        $channel = M("channel");
        $key = I("post.key_id");
        $vip_id = $_POST['vip_id'];
        if ($vip_id){
            $vipString = implode(',',$vip_id);
        }else{
            $vipString = '';
        }
        $key = I("post.key_id");

        if ($key){
            $keyString = implode(',',$key);
        }else{
            $keyString = '';
        }

        if (!$_FILES['coin']) {
            $this->error('请上传图标');
        }
        if ($_FILES) {
            $savepath = date('Ymd') . '/';
            //上传处理类
            $config = array(
                'rootPath' => './' . C("UPLOADPATH"),
                'savePath' => $savepath,
                'maxSize' => 11048576,
                'saveName' => array('uniqid', ''),
                'exts' => array('svga'),
                'autoSub' => false,
            );
            $upload = new \Think\Upload($config);//
            $info = $upload->upload();
            //开始上传
            if ($info) {
                //上传成功
                //写入附件数据库信息
                $first = array_shift($info);
                if (!empty($first['url'])) {
                    $url = $first['url'];
                } else {
                    $url = C("TMPL_PARSE_STRING.__UPLOAD__") . $savepath . $first['savename'];
                }
                $url = str_replace("http", "https", $url);

            } else {
                //上传失败，返回错误
                $this->error($upload->getError());
            }
        }
        $tenantId=getTenantIds();
        $channel->create();
        $channel->add(
            array(
                'type' => $type,
                'channel_name' => $chanelName,
                'code' => $code,
                'status' => $status,
                'addtime' => time(),
                'coin_id' => $coin_id,
                'icon' => $url,
                'tenant_id'=>$tenantId,
                'reg_url_id' => $keyString,
                'vip_id' => $vipString,
            )
        );
        $action = '添加渠道';
        setAdminLog($action);
        $this->success("添加成功！", U("Pay/channellist"));
    }


    /**
     * 子渠道列表
     */
    public function accountchannellist()
    {
        $param = I('param.');
        $map = array();
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $map['tenant_id'] = $tenant_id;
        $param['tenant_id'] = $tenant_id;

        if (isset($param['channel_id']) && $param['channel_id'] != '0') {
            $map['channel_id'] = $param['channel_id'];
        }

        if(isset($param['status'])){
            if($param['status'] != ''){
                $map['status'] = $param['status'];
            }
        }else{
            $map['status'] = 1;
            $param['status'] = 1;
        }

        $accountChannel = M("ChannelAccount");
        // 租户
        $tenant_ist = getTenantList();
        $tenant_ist = array_column($tenant_ist, null, 'id');

        // 渠道
        $channel_list = $this->channel_model->field('id,channel_name,type,coin_id,reg_url_id,vip_id')->where([ 'tenant_id'=>getTenantIds(),'type'=>1])->select();
        $channel_list = array_column($channel_list, null, 'id');

        $count = $accountChannel->where($map)->count();
        $page = $this->page($count, 20);
        $list = $accountChannel
            ->where($map)
            ->order('sort desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $rateModel = M("rate");

        foreach ($list as $key => $val) {
            $list[$key]['tenant_name'] = isset($tenant_ist[$val['tenant_id']]) ? $tenant_ist[$val['tenant_id']]['name'] : $val['tenant_id'];
            $list[$key]['channel_name'] = isset($channel_list[$val['channel_id']]) ? $channel_list[$val['channel_id']]['channel_name'] : $val['channel_id'];
            $list[$key]['type'] = isset($channel_list[$val['channel_id']]) ? $channel_list[$val['channel_id']]['type'] : $val['channel_id'];
            $list[$key]['rate'] = $rateModel->where(array('id' => $channel_list[$val['channel_id']]['coin_id']))->getField('rate');
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('param', $param);
        $this->assign("channel_list", $channel_list);
        $this->assign("tenant_list", $tenant_ist);
        $this->assign("list", $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /**
     * 修改渠道状态
     */
    public function accountupstatus()
    {
        $id = intval(I("get.id"));
        $status = intval(I("get.status"));
        if (M("ChannelAccount")->where(array('id' => $id))->save(array('status' => $status)) !== false) {

            $this->success("设置成功！");
        } else {
            $this->error("设置失败！");
        }
    }

    /*
     * 添加渠道
     */
    public function accountchanneladd()
    {
        // 租户
        $role_id = $_SESSION['role_id'];
        $tenantId=getTenantIds();
        if($role_id==1){
            $tenantList =  getTenantList();
        } else{
            //租户id条件
            $tenantListInfo =  getTenantList();
            $tenantListById =  array_column($tenantListInfo,null,'id');

            $tenantList[] = $tenantListById[$tenantId];

        }
        // 渠道
        $channelList = $this->channel_model->field('id,channel_name,type,reg_url_id,vip_id')
            ->where(['type'=> 1,'tenant_id'=>$tenantId,"status"=>1])
            ->select();
        $gameid = getGameTenantIds();
        $reg_url_id  =  explode(',',$channelList[0]['reg_url_id']);
        $RegurlModel  =  M('users_reg_url');
        $regurlList =  $RegurlModel->where(['tenant_id'=> getTenantIds(),'id'=> ['in',$reg_url_id ]])->select();
        $vip_id  =  explode(',',$channelList[0]['vip_id']);
        $Vip=M("vip");
        $vipList =$Vip->where('tenant_id="'.getTenantIds().'"')->where(['id'=> ['in',$vip_id]])->select();
        $this->assign("viplist", $vipList);

        $this->assign("regurlList", $regurlList);
        $this->assign("gameid", $gameid);
        $this->assign("channel_list", $channelList);

        $this->assign("tenant_list", $tenantList);
        $this->display();
    }

    public function accountchanneledit()
    {
        $id = $_REQUEST['id'];
        // 租户
        $role_id = $_SESSION['role_id'];
        $tenantId=getTenantIds();
        if($role_id==1){
            $tenantList =  getTenantList();
        } else{
            //租户id条件
            $tenantListInfo =  getTenantList();
            $tenantListById =  array_column($tenantListInfo,null,'id');

            $tenantList[] = $tenantListById[$tenantId];

        }
        $gameid = getGameTenantIds();
        $channelList = $this->channel_model->field('id,channel_name,type,reg_url_id,vip_id')
            ->where(['type'=> 1,'tenant_id'=>$tenantId,"status"=>1])->select();

        $this->assign("gameid", $gameid);
        $result   = M('channel_account')->find($id);
        $channelListById = array_column($channelList,null,'id');

        $RegurlModel  =  M('users_reg_url');
        $regurlId  = explode(',',$channelListById[$result['channel_id']]['reg_url_id']);
        $vip_id  = explode(',',$channelListById[$result['channel_id']]['vip_id']);
        $regurlList =  $RegurlModel->where(['tenant_id'=> getTenantIds(),'id'=> ['in',$regurlId]])->select();
        $Vip=M("vip");
        $vipList =$Vip->where('tenant_id="'.getTenantIds().'"')->where(['id'=> ['in',$vip_id]])->select();
        $this->assign("regurlList", $regurlList);
        $this->assign("vipList", $vipList);
        $urlid = explode(',',$result['reg_url_id']);
        $vipIdArray = explode(',',$result['vip_id']);
        $this->assign("url_id", $urlid);
        $this->assign("vipIdArray", $vipIdArray);
        // 渠道
        $this->assign("channel_list", $channelList);
        $this->assign("tenant_list", $tenantList);

        $this->assign("result",$result);
        $this->display();
    }


    /**
     * 添加渠道
     */
    public function account_channel_add_post()
    {
        $rules = array(
            array('name', 'require', '请输入商户名称！'),
            array('reception_name', 'require', '请输入前台显示名称！'),
            array('mer_id', 'require', '请输入商户号！'),
            array('secret_key', 'require', '请输入商户密钥！'),
            array('secret_key', 'require', '请输入商户密钥！'),
            array('url', 'require', '请输入请求地址！'),
            array('account_code', 'require', '请输入商户渠道编号！'),
            array('service_charge', 'require', '请输入上游手续费！'),
            array('orderno', 'require', '请输入排序！'),
        );
        $tenant_id = $_POST['tenant_id'];
        $channel_id = $_POST['channel_id'];
        $name = $_POST['name'];
        $mer_id = $_POST['mer_id'];
        $url = $_POST['url'];
        $account_code = $_POST['account_code'];
        $service_charge = $_POST['service_charge'];
        $orderno = $_POST['orderno'];
        $status = $_POST['status'];
        $secret_key = $_POST['secret_key'];
        $white_ip = $_POST['white_ip'];
        $notify_ip = $_POST['notify_ip'];
        $float_amount = $_POST['float_amount'];
        $select_amount = $_POST['select_amount'];
        $reception_name = $_POST['reception_name'];
        $explain= $_POST['explain'];
        $key = I("post.key_id");
        if ($key){
            $keyString = implode(',',$key);
        }else{
            $keyString = '';
        }

        $vip_id = $_POST['vip_id'];
        if ($vip_id){
            $vipString = implode(',',$vip_id);
        }else{
            $vipString = '';
        }
        $channelAccount = M("ChannelAccount");
        if (!$channelAccount->validate($rules)->create()) {
            $this->error($channelAccount->getError());
        }
        if (empty($service_charge)) {
            $service_charge = 0;
        }
        if (empty($float_amount)) {
            $float_amount = 0;
        }


        $channelInfo = M('channel')->where(array('id' => $channel_id))->find();
        $data = array(
            'tenant_id' => $tenant_id,
            'channel_id' => $channel_id,
            'name' => $name,
            'url' => $url,
            'account_code' => $account_code,
            'service_charge' => $service_charge,
            'mer_id' => $mer_id,
            'orderno' => $orderno,
            'status' => $status,
            'currency' => $channelInfo['currency_code'],
            'secret_key' => $secret_key,
            'white_ip' => $white_ip,
            'notify_ip' => $notify_ip,
            'float_amount' => $float_amount,
            'reception_name' => $reception_name,
            'select_amount' => $select_amount,
            'add_time' => time(),
            'explain'=>$explain,
            'reg_url_id' => $keyString,
            'vip_id' => $vipString,
        );

        $channelAccount->add($data);
        $action = '添加线上渠道';
        setAdminLog($action);
        $this->success("添加成功！", U("Pay/accountchannellist"));
    }

    public function account_channel_edit_post()
    {
        $id = $_REQUEST['id'];
        if (!$id) {
            $this->error('系统错误！');
        }
        $rules = array(
            array('name', 'require', '请输入商户名称！'),
            array('reception_name', 'require', '请输入前台显示名称！'),
            array('mer_id', 'require', '请输入商户号！'),
            array('secret_key', 'require', '请输入商户密钥！'),
            array('secret_key', 'require', '请输入商户密钥！'),
            array('url', 'require', '请输入请求地址！'),
            array('account_code', 'require', '请输入商户渠道编号！'),
            array('service_charge', 'require', '请输入上游手续费！'),
            array('orderno', 'require', '请输入排序！'),
        );
        $tenant_id = $_POST['tenant_id'];
        $channel_id = $_POST['channel_id'];
        $name = $_POST['name'];
        $mer_id = $_POST['mer_id'];
        $url = $_POST['url'];
        $account_code = $_POST['account_code'];
        $service_charge = $_POST['service_charge'];
        $orderno = $_POST['orderno'];
        $status = $_POST['status'];
        $secret_key = $_POST['secret_key'];
        $white_ip = $_POST['white_ip'];
        $notify_ip = $_POST['notify_ip'];
        $float_amount = $_POST['float_amount'];
        $select_amount = $_POST['select_amount'];
        $reception_name = $_POST['reception_name'];
        $explain = $_POST['explain'];
        $channelAccount = M("ChannelAccount");
        $callbackurl = $_POST['callbackurl'];
        if (!$channelAccount->validate($rules)->create()) {
            $this->error($channelAccount->getError());
        }
        if (empty($service_charge)) {
            $service_charge = 0;
        }
        if (empty($float_amount)) {
            $float_amount = 0;
        }
        $key = $_POST['key_id'];
        if ($key){
            $keyString = implode(',',$key);
        }else{
            $keyString = '';
        }
        $channelInfo = M('channel')->where(array('id' => $channel_id))->find();

        $data = array(
            'tenant_id' => $tenant_id,
            'channel_id' => $channel_id,
            'name' => $name,
            'url' => $url,
            'account_code' => $account_code,
            'service_charge' => $service_charge,
            'mer_id' => $mer_id,
            'orderno' => $orderno,
            'status' => $status,
            'currency' => $channelInfo['currency_code'],
            'secret_key' => $secret_key,
            'white_ip' => $white_ip,
            'notify_ip' => $notify_ip,
            'float_amount' => $float_amount,
            'reception_name' => $reception_name,
            'select_amount' => $select_amount,
            'add_time' => time(),
            'explain' => $explain,
            'reg_url_id' => $keyString,
            'vip_id' => '',
            'callbackurl'=>$callbackurl,
        );

        $channelAccount->where(['id' => $id])->save($data);
        $action = '添加线上渠道';
        setAdminLog($action);
        $this->success("添加成功！", U("Pay/accountchannellist"));
    }

    public function offlinepay()
    {
        $param = I('param.');
        $map = array();

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $map['a.tenant_id'] = $tenant_id;
        $param['tenant_id'] = $tenant_id;

        if(isset($param['status'])){
            if($param['status'] != ''){
                $map['a.status'] = $param['status'];
            }
        }else{
            $map['a.status'] = '1';
            $param['status'] = '1';
        }

        $channel_id = $param['channel_id'];
        if (isset($param['channel_id']) && $param['channel_id'] != '0') {
            $map['a.channel_id'] = $channel_id;
        }

        $map['c.is_virtual'] = 0;
        $map['b.type'] = 2;

        $offlinepay_model = M("offlinepay");
        $count = $offlinepay_model
            ->alias('a')
            ->join('cmf_channel as b on a.channel_id=b.id')
            ->join('cmf_rate as c on b.coin_id=c.id')
            ->where($map)
            ->count();
        $page = $this->page($count, 20);
        $list = $offlinepay_model
            ->alias('a')
            ->field('a.*')
            ->join('cmf_channel as b on a.channel_id=b.id')
            ->join('cmf_rate as c on b.coin_id=c.id')
            ->where($map)
            ->order("a.orderno asc,a.addtime desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $tenant_list = getTenantList();
        $tenant_list = array_column($tenant_list, null, 'id');
        $channel_list = $this->channel_model->field('id,channel_name,type')->where(['type' =>2,'tenant_id'=>$tenant_id])->select();
        $channel_list = array_column($channel_list, null, 'id');
        foreach ($list as $key => $val) {
            $list[$key]['tenant_name'] = isset($tenant_list[$val['tenant_id']]) ? $tenant_list[$val['tenant_id']]['name'] : $val['tenant_id'];
            $list[$key]['channel_name'] = isset($channel_list[$val['channel_id']]) ? $channel_list[$val['channel_id']]['channel_name'] : $val['channel_id'];
            $list[$key]['limit_charge_total_money'] = floatval($val['limit_charge_total_money']);
            $list[$key]['already_charge_total_money'] = floatval($val['already_charge_total_money']);
            $list[$key]['min_amount'] = floatval($val['min_amount']);
            $list[$key]['max_amount'] = floatval($val['max_amount']);
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign("channel_list", $channel_list);
        $this->assign("tenant_list", $tenant_list);
        $this->assign("list", $list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param', $param);
        $this->display();
    }

    public function offlinepayadd()
    {
        $tenantId=getTenantIds();
        //判断是否为超级管理员
        $role_id=$_SESSION['role_id'];
        if($role_id==1){
            $tenantList =  getTenantList();
        } else{
            //租户id条件
            $tenantListInfo =  getTenantList();
            $tenantListById =  array_column($tenantListInfo,null,'id');

            $tenantList[] = $tenantListById[$tenantId];

        }


        // 渠道
        $channelList = $this->channel_model
            ->alias('a')
            ->join('cmf_rate as c on a.coin_id=c.id')
            ->field('a.id,a.channel_name,a.type,a.reg_url_id,a.vip_id')
            ->where(['a.type' => 2, 'c.is_virtual' => 0,'a.tenant_id'=>$tenantId,'a.status'=>1])
            ->select();
        $gameid = getGameTenantIds();

        $reg_url_id  =  explode(',',$channelList[0]['reg_url_id']);
        $RegurlModel  =  M('users_reg_url');
        $regurlList =  $RegurlModel->where(['tenant_id'=> getTenantIds(),'id'=> ['in',$reg_url_id ]])->select();
        $vip_id  =  explode(',',$channelList[0]['vip_id']);
        $Vip=M("vip");
        $vipList =$Vip->where('tenant_id="'.getTenantIds().'"')->where(['id'=> ['in',$vip_id]])->select();
        $this->assign("viplist", $vipList);
        $this->assign("regurlList", $regurlList);
        $this->assign("gameid", $gameid);
        $this->assign("channel_list", $channelList);
        $this->assign("tenant_list", $tenantList);
        $this->display();
    }

    public function offlinepayedit()
    {
        $id = $_REQUEST['id'];
        $tenantId=getTenantIds();
        $role_id = $_SESSION['role_id'];
        if($role_id==1){
            $tenantList =  getTenantList();
        } else{
            //租户id条件
            $tenantListInfo =  getTenantList();
            $tenantListById =  array_column($tenantListInfo,null,'id');

            $tenantList[] = $tenantListById[$tenantId];

        }
        // 渠道
        $channelList = $this->channel_model
            ->alias('a')
            ->join('cmf_rate as c on a.coin_id=c.id')
            ->field('a.id,a.channel_name,a.type,a.reg_url_id,a.vip_id')
            ->where(['a.type' => 2, 'c.is_virtual' => 0])
            ->select();
        $gameid = getGameTenantIds();

        $channelListById = array_column($channelList,null,'id');
        $result   = M('offlinepay')->find($id);
        $RegurlModel  =  M('users_reg_url');
        $regurlId  = explode(',',$channelListById[$result['channel_id']]['reg_url_id']);
        $vip_id  = explode(',',$channelListById[$result['channel_id']]['vip_id']);
        $regurlList =  $RegurlModel->where(['tenant_id'=> getTenantIds(),'id'=> ['in',$regurlId]])->select();
        $Vip=M("vip");
        $vipList =$Vip->where('tenant_id="'.getTenantIds().'"')->where(['id'=> ['in',$vip_id]])->select();
        $this->assign("regurlList", $regurlList);
        $this->assign("vipList", $vipList);
        $urlid = explode(',',$result['reg_url_id']);
        $vipIdArray = explode(',',$result['vip_id']);

        $result['service_charge'] = floatval($result['service_charge']);
        $result['limit_charge_total_money'] = floatval($result['limit_charge_total_money']);
        $result['min_amount'] = floatval($result['min_amount']);
        $result['max_amount'] = floatval($result['max_amount']);

        $this->assign("url_id", $urlid);
        $this->assign("vipIdArray", $vipIdArray);
        $this->assign("channel_list", $channelList);
        $this->assign("tenant_list", $tenantList);
        $this->assign("result",$result );
        $this->assign("gameid", $gameid);
        $this->display();
    }

    public function offline_add_post()
    {
        $param = $_REQUEST;
        $rules = array(
            array('name', 'require', '请输入商户名称！'),
            array('reception_name', 'require', '请输入前台显示名称！'),
            array('bank_user_name', 'require', '请输入户主名称！'),
            array('bank_name', 'require', '请输入银行名称！'),
            array('bank_number', 'require', '请输入银卡卡号！'),
            array('bank_branch', 'require', '请输入开户支行！'),
            array('service_charge', 'require', '请输入上游手续费！'),
            array('min_amount', 'require', '请输入支付限额！'),
            array('max_amount', 'require', '请输入支付限额！'),
            array('orderno', 'require', '请输入排序！'),
        );
        $tenant_id = $_POST['tenant_id'];
        $channel_id = $_POST['channel_id'];
        $name = $_POST['name'];
        $reception_name = $_POST['reception_name'];
        $bank_name = $_POST['bank_name'];
        $bank_number = $_POST['bank_number'];
        $service_charge = $_POST['service_charge'];
        $bank_branch = $_POST['bank_branch'];
        $status = $_POST['status'];
        $min_amount = $_POST['min_amount'];
        $max_amount = $_POST['max_amount'];
        $qr_code = $_POST['qr_code'];
        $bank_user_name = $_POST['bank_user_name'];
        $explain = $_POST['explain'];
        $key = I("post.key_id");
        $vip_id = $_POST['vip_id'];
        if ($vip_id){
            $vipString = implode(',',$vip_id);
        }else{
            $vipString = '';
        }
        if ($key){
            $keyString = implode(',',$key);
        }else{
            $keyString = '';
        }

        if (empty($min_amount)) {
            $min_amount = 0;
        }
        if (empty($max_amount)) {
            $max_amount = 0;
        }

        $offlinepay = M("offlinepay");
        if (!$offlinepay->validate($rules)->create()) {
            $this->error($offlinepay->getError());
        }
        if (empty($service_charge)) {
            $service_charge = 0;
        }
        if (empty($float_amount)) {
            $float_amount = 0;
        }

        $channelInfo = M('channel')->where(array('id' => $channel_id))->find();
        $data = array(
            'tenant_id' => $tenant_id,
            'channel_id' => $channel_id,
            'name' => $name,
            'service_charge' => $service_charge,
            'status' => $status,
            'float_amount' => $float_amount,
            'reception_name' => $reception_name,
            'addtime' => time(),
            'bank_name' => $bank_name,
            'bank_number' => $bank_number,
            'bank_branch' => $bank_branch,
            'limit_charge_total_money' => floatval($param['limit_charge_total_money']),
            'min_amount' => $min_amount,
            'max_amount' => $max_amount,
            'qr_code' => $qr_code,
            'bank_user_name' => $bank_user_name,
            'explain' => $explain,
            'reg_url_id' => $keyString,
            'vip_id' => $vipString,

        );

        $offlinepay->add($data);
        $action = '添加线下支付渠道';
        setAdminLog($action);
        $this->success("添加成功！", U("Pay/offlinepay"));
    }

    public function offline_edit_post()
    {
        $param = $_REQUEST;
        $id = $_REQUEST['id'];
        if (!$id) {
            $this->error('系统错误！');
        }
        $rules = array(
            array('name', 'require', '请输入商户名称！'),
            array('reception_name', 'require', '请输入前台显示名称！'),
            array('bank_user_name', 'require', '请输入户主名称！'),
            array('bank_name', 'require', '请输入银行名称！'),
            array('bank_number', 'require', '请输入银卡卡号！'),
            array('bank_branch', 'require', '请输入开户支行！'),
            array('service_charge', 'require', '请输入上游手续费！'),
            array('min_amount', 'require', '请输入支付限额！'),
            array('max_amount', 'require', '请输入支付限额！'),
            array('orderno', 'require', '请输入排序！'),
        );
        $tenant_id = $_POST['tenant_id'];
        $channel_id = $_POST['channel_id'];
        $name = $_POST['name'];
        $reception_name = $_POST['reception_name'];
        $bank_name = $_POST['bank_name'];
        $bank_number = $_POST['bank_number'];
        $service_charge = $_POST['service_charge'];
        $bank_branch = $_POST['bank_branch'];
        $status = $_POST['status'];
        $min_amount = $_POST['min_amount'];
        $max_amount = $_POST['max_amount'];
        $qr_code = $_POST['qr_code'];
        $bank_user_name = $_POST['bank_user_name'];
        $explain = $_POST['explain'];
        $key = I("post.key_id");
        if ($key){
            $keyString = implode(',',$key);
        }else{
            $keyString = '';
        }
        $vip_id = $_POST['vip_id'];
        if ($vip_id){
            $vipString = implode(',',$vip_id);
        }else{
            $vipString = '';
        }

        if (empty($min_amount)) {
            $min_amount = 0;
        }
        if (empty($max_amount)) {
            $max_amount = 0;
        }

        $info = M("offlinepay")->where(['id'=>intval($param['id'])])->find();

        $offlinepay = M("offlinepay");
        if (!$offlinepay->validate($rules)->create()) {
            $this->error($offlinepay->getError());
        }
        if (empty($service_charge)) {
            $service_charge = 0;
        }
        if (empty($float_amount)) {
            $float_amount = 0;
        }
        $data = array(
            'tenant_id' => $tenant_id,
            'channel_id' => $channel_id,
            'name' => $name,
            'service_charge' => $service_charge,
            'status' => $status,
            'float_amount' => $float_amount,
            'reception_name' => $reception_name,
            'bank_name' => $bank_name,
            'bank_number' => $bank_number,
            'bank_branch' => $bank_branch,
            'limit_charge_total_money' => floatval($param['limit_charge_total_money']),
            'min_amount' => $min_amount,
            'max_amount' => $max_amount,
            'qr_code' => $qr_code,
            'bank_user_name' => $bank_user_name,
            'explain' => $explain,
            'reg_url_id'=> $keyString,
            'vip_id' => $vipString,
        );

        if($info['bank_number'] != $bank_number){
            $data['already_charge_total_money'] = 0;
        }

        $offlinepay->where(['id' => $id])->save($data);
        $action = '编辑线下支付渠道';
        setAdminLog($action);
        $this->success("编辑成功！", U("Pay/offlinepay"));
    }

    public function usdtpay()
    {
        $param = I('param.');
        $map = array();

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $map['a.tenant_id'] = $tenant_id;
        $param['tenant_id'] = $tenant_id;

        if(isset($param['status'])){
            if($param['a.status'] != ''){
                $map['status'] = $param['status'];
            }
        }else{
            $map['a.status'] = '1';
            $param['status'] = '1';
        }

        if (isset($param['channel_id']) && $param['channel_id'] != '0') {
            $map['a.channel_id'] = $param['channel_id'];
        }

        $map['c.is_virtual'] = 1;
        $map['b.type'] = 2;

        $tenant_list = getTenantList();
        $tenant_list = array_column($tenant_list, null, 'id');
        $channel_list = $this->channel_model->field('id,channel_name,type')->where(['type' =>2,'tenant_id'=>$tenant_id])->select();
        $channel_list = array_column($channel_list, null, 'id');

        $offlinepay = M("offlinepay");
        $count = $offlinepay
            ->alias('a')
            ->join('cmf_channel as b on a.channel_id=b.id')
            ->join('cmf_rate as c on b.coin_id=c.id')
            ->where($map)
            ->count();

        $page = $this->page($count, 20);
        $list = $offlinepay
            ->alias('a')
            ->field('a.*,c.name as rate_name')
            ->join('cmf_channel as b on a.channel_id=b.id')
            ->join('cmf_rate as c on b.coin_id=c.id')
            ->where($map)
            ->order("a.orderno asc,a.addtime desc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        foreach ($list as $key => $value) {
            $list[$key]['tenant_name'] = isset($tenant_list[$value['tenant_id']]) ? $tenant_list[$value['tenant_id']]['name'] : $value['tenant_id'];
            $list[$key]['channel_name'] = isset($channel_list[$value['channel_id']]) ? $channel_list[$value['channel_id']]['channel_name'] : $value['channel_id'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('param', $param);;
        $this->assign("channel_list", $channel_list);
        $this->assign("tenant_list", $tenant_list);
        $this->assign("list", $list);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public function usdtadd()
    {
        $tenantId=getTenantIds();
        $role_id = $_SESSION['role_id'];
        if($role_id==1){
            $tenantList =  getTenantList();
        } else{
            //租户id条件
            $tenantListInfo =  getTenantList();
            $tenantListById =  array_column($tenantListInfo,null,'id');
            $tenantList[] = $tenantListById[$tenantId];

        }
        // 渠道
        $channelList = $this->channel_model
            ->alias('a')
            ->join('cmf_rate as c on a.coin_id=c.id')
            ->field('a.id,a.channel_name,a.type,a.reg_url_id,a.vip_id')
            ->where(['a.type' => 2, 'c.is_virtual' => 1,'a.tenant_id'=>$tenantId,'a.status'=>1])
            ->select();
        $gameid = getGameTenantIds();


        $reg_url_id  =  explode(',',$channelList[0]['reg_url_id']);
        $RegurlModel  =  M('users_reg_url');
        $regurlList =  $RegurlModel->where(['tenant_id'=> getTenantIds(),'id'=> ['in',$reg_url_id ]])->select();
        $vip_id  =  explode(',',$channelList[0]['vip_id']);
        $Vip=M("vip");
        $vipList =$Vip->where('tenant_id="'.getTenantIds().'"')->where(['id'=> ['in',$vip_id]])->select();
        $this->assign("viplist", $vipList);
        $this->assign("regurlList", $regurlList);

        $this->assign("gameid", $gameid);

        $this->assign("channel_list", $channelList);
        $this->assign("tenant_list", $tenantList);
        $this->display();
    }

    public function usdtedit()
    {
        $id = $_REQUEST['id'];
        $tenantId=getTenantIds();
        $role_id = $_SESSION['role_id'];
        if($role_id==1){
            $tenantList =  getTenantList();
        } else{
            //租户id条件
            $tenantListInfo =  getTenantList();
            $tenantListById =  array_column($tenantListInfo,null,'id');

            $tenantList[] = $tenantListById[$tenantId];

        }
        // 渠道
        $channelList = $this->channel_model
            ->alias('a')
            ->join('cmf_rate as c on a.coin_id=c.id')
            ->field('a.id,a.channel_name,a.type')
            ->where(['a.type' => 2, 'c.is_virtual' => 1])
            ->select();

        $gameid = getGameTenantIds();


        $result = M('offlinepay')->find($id);
        $channelListById = array_column($channelList,null,'id');

        $RegurlModel  =  M('users_reg_url');
        $regurlId  = explode(',',$channelListById[$result['channel_id']]['reg_url_id']);
        $vip_id  = explode(',',$channelListById[$result['channel_id']]['vip_id']);
        $regurlList =  $RegurlModel->where(['tenant_id'=> getTenantIds(),'id'=> ['in',$regurlId]])->select();
        $Vip=M("vip");
        $vipList =$Vip->where('tenant_id="'.getTenantIds().'"')->where(['id'=> ['in',$vip_id]])->select();
        $this->assign("regurlList", $regurlList);
        $this->assign("vipList", $vipList);
        $urlid = explode(',',$result['reg_url_id']);
        $vipIdArray = explode(',',$result['vip_id']);
        $this->assign("url_id", $urlid);
        $this->assign("gameid", $gameid);
        $this->assign("vipIdArray", $vipIdArray);
        $this->assign("channel_list", $channelList);
        $this->assign("tenant_list", $tenantList);
        $this->assign("result", $result);
        $this->display();
    }

    public function usdt_add_post()
    {
        $rules = array(
            array('name', 'require', '请输入商户名称！'),
            array('reception_name', 'require', '请输入前台显示名称！'),
            array('usdt_type', 'require', '请输入链类型！'),
            array('usdt_address', 'require', '请输入链地址！'),
            array('service_charge', 'require', '请输入上游手续费！'),
            array('min_amount', 'require', '请输入支付限额！'),
            array('max_amount', 'require', '请输入支付限额！'),
            array('orderno', 'require', '请输入排序！'),
        );
        $tenant_id = $_POST['tenant_id'];
        $channel_id = $_POST['channel_id'];
        $name = $_POST['name'];
        $reception_name = $_POST['reception_name'];
        $usdt_type = $_POST['usdt_type'];
        $service_charge = $_POST['service_charge'];
        $status = $_POST['status'];
        $min_amount = $_POST['min_amount'];
        $max_amount = $_POST['max_amount'];
        $qr_code = $_POST['qr_code'];
        $usdt_address = $_POST['usdt_address'];
        $explain = $_POST['explain'];
        $key = I("post.key_id");
        if ($key){
            $keyString = implode(',',$key);
        }else{
            $keyString = '';
        }
        if(!isset($channel_id) || empty($channel_id)){
            $this->error('请选择渠道');
        }
        $vip_id = $_POST['vip_id'];
        if ($vip_id){
            $vipString = implode(',',$vip_id);
        }else{
            $vipString = '';
        }
        if (empty($min_amount)) {
            $min_amount = 0;
        }
        if (empty($max_amount)) {
            $max_amount = 0;
        }

        $offlinepay = M("offlinepay");
        if (!$offlinepay->validate($rules)->create()) {
            $this->error($offlinepay->getError());
        }
        if (empty($service_charge)) {
            $service_charge = 0;
        }
        if (empty($float_amount)) {
            $float_amount = 0;
        }
        $data = array(
            'tenant_id' => $tenant_id,
            'channel_id' => $channel_id,
            'name' => $name,
            'service_charge' => $service_charge,
            'status' => $status,
            'float_amount' => $float_amount,
            'reception_name' => $reception_name,
            'usdt_type' => $usdt_type,
            'usdt_address' => $usdt_address,
            'min_amount' => $min_amount,
            'max_amount' => $max_amount,
            'qr_code' => $qr_code,
            'explain'=>$explain,
            'reg_url_id' => $keyString,
            'vip_id' => $vipString,
        );

        $offlinepay->add($data);
        $action = '添加线下支付渠道';
        setAdminLog($action);
        $this->success("添加成功！", U("Pay/usdtpay"));
    }

    public function usdt_edit_post()
    {
        $id = $_REQUEST['id'];
        if (!$id) {
            $this->error('系统错误！');
        }
        $rules = array(
            array('name', 'require', '请输入商户名称！'),
            array('reception_name', 'require', '请输入前台显示名称！'),
            array('usdt_type', 'require', '请输入链类型！'),
            array('usdt_address', 'require', '请输入链地址！'),
            array('service_charge', 'require', '请输入上游手续费！'),
            array('min_amount', 'require', '请输入支付限额！'),
            array('max_amount', 'require', '请输入支付限额！'),
            array('orderno', 'require', '请输入排序！'),
        );
        $tenant_id = $_POST['tenant_id'];
        $channel_id = $_POST['channel_id'];
        $name = $_POST['name'];
        $reception_name = $_POST['reception_name'];
        $usdt_type = $_POST['usdt_type'];
        $service_charge = $_POST['service_charge'];
        $status = $_POST['status'];
        $min_amount = $_POST['min_amount'];
        $max_amount = $_POST['max_amount'];
        $qr_code = $_POST['qr_code'];
        $usdt_address = $_POST['usdt_address'];
        $explain = $_POST['explain'];
        $key = I("post.key_id");
        if ($key){
            $keyString = implode(',',$key);
        }else{
            $keyString = '';
        }
        $vip_id = $_POST['vip_id'];
        if ($vip_id){
            $vipString = implode(',',$vip_id);
        }else{
            $vipString = '';
        }
        if (empty($min_amount)) {
            $min_amount = 0;
        }
        if (empty($max_amount)) {
            $max_amount = 0;
        }

        $offlinepay = M("offlinepay");
        if (!$offlinepay->validate($rules)->create()) {
            $this->error($offlinepay->getError());
        }
        if (empty($service_charge)) {
            $service_charge = 0;
        }
        if (empty($float_amount)) {
            $float_amount = 0;
        }
        $data = array(
            'tenant_id' => $tenant_id,
            'channel_id' => $channel_id,
            'name' => $name,
            'service_charge' => $service_charge,
            'status' => $status,
            'float_amount' => $float_amount,
            'reception_name' => $reception_name,
            'usdt_type' => $usdt_type,
            'usdt_address' => $usdt_address,
            'min_amount' => $min_amount,
            'max_amount' => $max_amount,
            'qr_code' => $qr_code,
            'explain' => $explain,
            'reg_url_id' => $keyString,
            'vip_id' => $vipString,
        );

        $offlinepay->where(['id' => $id])->save($data);
        $action = '编辑线下支付渠道';
        setAdminLog($action);
        $this->success("编辑成功！", U("Pay/usdtpay"));
    }

    public function offlinesupstatus()
    {
        $id = intval(I("get.id"));
        $status = intval(I("get.status"));
        if ($this->offlinepay_model->where("id=$id")->save(array('status' => $status)) !== false) {

            $this->success("设置成功！");
        } else {
            $this->error("设置失败！");
        }
    }

    public function usdtupstatus()
    {
        $id = intval(I("get.id"));
        $status = intval(I("get.status"));
        if ($this->offlinepay_model->where("id=$id")->save(array('status' => $status)) !== false) {

            $this->success("设置成功！");
        } else {
            $this->error("设置失败！");
        }
    }

    public function listorders()
    {
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['sort'] = $r;
            $model = M("ChannelAccount");
            $model->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $action = "修改线上支付排序";
            setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    public function offlineorders()
    {
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            $model = M("offlinepay");
            $model->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $action = "修改线下支付排序";
            setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    public function usdtorders()
    {
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            $model = M("offlinepay");
            $model->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $action = "修改虚拟币支付排序";
            setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    public function getUrlAndVip(){
        if(IS_AJAX ){

            $gameid = getGameTenantIds();
            if ($gameid == 104){
                $channle_id = I('channle_id');

                $channelList = $this->channel_model->field('id,channel_name,type,coin_id,reg_url_id,vip_id')
                    ->where([ 'tenant_id'=>getTenantIds(),'id'=>$channle_id ])->select();
                $reg_url_id  =  explode(',',$channelList[0]['reg_url_id']);
                $RegurlModel  =  M('users_reg_url');
                $regurlList =  $RegurlModel->where(['tenant_id'=> getTenantIds(),'id'=> ['in',$reg_url_id ]])->select();
                $vip_id  =  explode(',',$channelList[0]['vip_id']);
                $Vip=M("vip");
                $vipList =$Vip->where('tenant_id="'.getTenantIds().'"')->where(['id'=> ['in',$vip_id]])->select();
                $data['data'] = ['vip'=>$vipList,'urlList'=> $regurlList ];
                $data['code'] =  0;

                exit( json_encode($data));


            }else{
                $data['data'] = [];
                $data['code'] = 0;
            }

        }
    }
}