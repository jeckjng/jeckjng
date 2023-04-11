<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RechargeLevelController extends AdminbaseController
{


    function _initialize()
    {
        parent::_initialize();

    }

    public  function index(){

        $tenantId = getTenantIds();
        $map['tenant_id'] = $tenantId;
        $rechargeLevel = M("rechargeLevel");
        $count = $rechargeLevel->where($map)->count();
        $page = $this->page($count, 20);
        $rechargeLevelList = $rechargeLevel
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        if ($rechargeLevelList){
            $channelAllArray= [];
            $channelAccountAllArray =[];
            $offlinepayAllArray =[] ;
            foreach ($rechargeLevelList as $key => $value){
                if ($value['channel_id']){
                    $channel = explode(',',$value['channel_id']);
                    $channelAllArray = array_merge($channelAllArray,$channel);
                    $rechargeLevelList[$key]['channel_list'] = $channel;
                }
                if ($value['channel_account_id']){
                    $channelAccount = explode(',',$value['channel_account_id']);
                    $channelAccountAllArray = array_merge($channelAccountAllArray,$channelAccount);
                    $rechargeLevelList[$key]['channel_account_list'] = $channelAccount;
                }
                if ($value['offlinepay_id']){
                    $offlinepay = explode(',',$value['offlinepay_id']);
                    $offlinepayAllArray = array_merge($offlinepayAllArray,$offlinepay);
                    $rechargeLevelList[$key]['offlinepay_list'] = $offlinepay;
                }
            }


            if ($channelAllArray){
                $channelList  = M('channel')->where(['id'=> ['in',$channelAllArray]])->field('id,channel_name')->select(); // 查找 渠道
                $channelListById =  array_column($channelList,null,'id');

            }

            if ($channelAccountAllArray){
                $channelAccountList  = M('channel_account')->where(['id'=> ['in',$channelAccountAllArray]])->field('id,name,channel_id')->select();// 查询 子渠道
                $channelAccountListById  = array_column($channelAccountList,null,'id');

            }

            if ($offlinepayAllArray){
                $offlinepayList  = M('offlinepay')->where(['id'=> ['in',$offlinepayAllArray]])->field('id,name,channel_id')->select();// 查询 子渠道

            }

            $offlinepayListById  = array_column($offlinepayList,null,'id');

            foreach ($rechargeLevelList as $key => $value){
                $channelAccount  = [];
                $channelData = [];
                $offlinePayData = [];
                foreach ($value['channel_list'] as $channelListValue){  // 塞入上级 渠道
                    $channelData[$channelListValue]['id'] = $channelListValue;
                    $channelData[$channelListValue]['name'] = $channelListById[$channelListValue]['channel_name'];
                }
                foreach ($value['channel_account_list'] as $channelAccountKey=>  $channelListAccountValue){ // 把下级渠道 塞到对应的上级 渠道
                    $channelAccount['id'] = $channelListAccountValue;
                    $channelAccount['name'] = $channelAccountListById[$channelListAccountValue]['name'];
                    $channelData[$channelAccountListById[$channelListAccountValue]['channel_id']]['account_list'][] = $channelAccount;
                }
                foreach ($value['offlinepay_list'] as $offlinepayKey=>  $offlinepayValue){ // 把下级渠道 塞到对应的上级 渠道
                    $offlinePayData['id'] = $offlinepayValue;
                    $offlinePayData['name'] = $offlinepayListById[$offlinepayValue]['name'];
                    $channelData[$offlinepayListById[$offlinepayValue]['channel_id']]['account_list'][] = $offlinePayData;
                }

                unset($rechargeLevelList[$key]['channel_account_list']);
                unset($rechargeLevelList[$key]['channel_list']);
                unset($rechargeLevelList[$key]['offlinePay']);
                $rechargeLevelList[$key]['channel_data'] =$channelData;
            }
        }

       /* echo '<pre>';
        var_dump($rechargeLevelList);exit;*/
        $this->assign("list", $rechargeLevelList);

        $this->display();
    }

    public  function add(){

        $accountChannelList  = M('channel_account')->where(['tenant_id' => getTenantIds()])->select();
        $offlinePay  = M('offlinepay')->where(['tenant_id' => getTenantIds()])->select();
        $offlinePayById  = array_column($offlinePay,'channel_id');
        $channelIdArray =array_merge( array_column($accountChannelList,'channel_id'),$offlinePayById);
        if ($channelIdArray){
            $channelList  = M('channel')->where(['id'=> ['in',$channelIdArray]])->select();

        }else{
            $channelList =[];
        }

        $this->assign("channelList", $channelList);
        $this->assign("offlinePay", $offlinePay);
        $this->assign("accountChannelList", $accountChannelList);
        if (IS_POST) {
            $param = I("post.");
            $name = $param['name'];
            if (!$name){
                $this->error('请输入层级名称');
            }
            $max_amount = $param['max_amount'];
            if ($max_amount === ''){
                $this->error('请输入充值范围最大值');
            }
            $min_amount = $param['min_amount'];
            if ($min_amount === ''){
                $this->error('请输入充值范围最小值');
            }
            $data= [
                'name' => $name,
                'max_amount' => $max_amount,
                'min_amount' => $min_amount,
                'every_day_count' => $param['every_day_count'],
                'every_day_amount' => $param['every_day_amount'],
                'des' => $param['des'],
                'status' => $param['status'],
                'add_time' => time(),
                'tenant_id'=> getTenantIds()
            ];
            $channelById =  array_column($accountChannelList,null,'id');
            $accountId = $param['account_id'];
            $channelId =[];
            $data['channel_id'] =[];
            if ($accountId){ //  线上id
                foreach ($accountId as $accountIdValue){
                    if (!in_array($channelById[$accountIdValue]['channel_id'],$channelId)){
                        $channelId[]  = $channelById[$accountIdValue]['channel_id'];
                    }
                }
                $data['channel_account_id'] =implode(',',$accountId);
            }else{
                $data['channel_account_id'] =0;
            }
            $offlinePayId  = $param['offlinepay_id'];
            $offlinePayById =  array_column($offlinePay,null,'id');
            if ($offlinePayId){  // 线下id
                foreach ($offlinePayId as $offlinePayIdValue){
                    if (!in_array($offlinePayById[$offlinePayIdValue]['channel_id'],$channelId)){
                        $channelId[]  = $offlinePayById[$offlinePayIdValue]['channel_id'];
                    }
                }
                $data['offlinepay_id'] =implode(',',$offlinePayId);

            }else{
                $data['offlinepay_id'] =0;
            }
            if ($channelId){
                $data['channel_id'] =implode(',',$channelId);
            }else{
                $data['channel_id'] = 0;
            }

            $result = M("rechargeLevel")->add($data);

            if ($result !== false){
                $action="添加层级id:{$result}";
                setAdminLog($action);
                return $this->success('添加成功');
            }else{
                return $this->error('添加失败');
            }
        }
        $this->display();
    }


    public  function edit(){
        $accountChannelList  = M('channel_account')->where(['tenant_id' => getTenantIds()])->select();
        $offlinePay  = M('offlinepay')->where(['tenant_id' => getTenantIds()])->select();
        $offlinePayArray  = array_column($offlinePay,'channel_id');
        $channelIdArray = array_column($accountChannelList,'channel_id');
        $channelIdArray = array_merge($offlinePayArray,$channelIdArray);

        if ($channelIdArray){
            $channelList  = M('channel')->where(['id'=> ['in',$channelIdArray]])->select();

        }else{
            $channelList =[];
        }
        $this->assign("channelList", $channelList);
        $this->assign("offlinePay", $offlinePay);
        $this->assign("accountChannelList", $accountChannelList);
        $id = $_REQUEST['id'] ;
        $info  =  M("rechargeLevel")->where(['id'=> $id])->find();
        if (IS_POST) {

            $param = I("post.");
            $name = $param['name'];

            if (!$name){
                $this->error('请输入层级名称');
            }
            $max_amount = $param['max_amount'];

            if ($max_amount === ''){
                $this->error('请输入充值范围最大值');
            }
            $min_amount = $param['min_amount'];
            if ($min_amount === ''){
                $this->error('请输入充值范围最小值');
            }
            $data= [
                'name' => $name,
                'max_amount' => $max_amount,
                'min_amount' => $min_amount,
                'every_day_count' => $param['every_day_count'],
                'every_day_amount' => $param['every_day_amount'],
                'des' => $param['des'],
                'status' => $param['status'],
                'add_time' => time(),
                'tenant_id'=> getTenantIds()
            ];
            $channelById =  array_column($accountChannelList,null,'id');
            $accountId = $param['account_id'];
            $channelId =[];
            if ($accountId){ //  线上id
                foreach ($accountId as $accountIdValue){
                    if (!in_array($channelById[$accountIdValue]['channel_id'],$channelId)){
                        $channelId[]  = $channelById[$accountIdValue]['channel_id'];
                    }
                }
                $data['channel_account_id'] =implode(',',$accountId);
            }else{
                $data['channel_account_id'] =0;
            }

            $offlinePayById =  array_column($offlinePay,null,'id');
            $offlinePayId  = $param['offlinepay_id'];
            if ($offlinePayId){  // 线下id
                foreach ($offlinePayId as $offlinePayIdValue){
                    if (!in_array($offlinePayById[$offlinePayIdValue]['channel_id'],$channelId)){
                        $channelId[]  = $offlinePayById[$offlinePayIdValue]['channel_id'];
                    }
                }
                $data['offlinepay_id'] =implode(',',$offlinePayId);

            }else{
                $data['offlinepay_id'] =0;
            }
            if ($channelId){
                $data['channel_id'] =implode(',',$channelId);
            }else{
                $data['channel_id'] = 0;
            }

            $result = M("rechargeLevel")->where(['id'=> $id])->save($data);


            if ($result !== false){
                $action="修改层级id:{$result}";
                setAdminLog($action);
                return $this->success('修改成功');
            }else{
                return $this->error('修改失败');
            }
        }

        $this->assign("channelList", $channelList);
        $this->assign("accountChannelList", $accountChannelList);
        $info['channel_account_id']  = explode(',',$info['channel_account_id']);
        $info['offlinepay_id']  = explode(',',$info['offlinepay_id']);
        $this->assign("info", $info);
        $this->assign("id", $id);
        $this->display();
    }

    public function updateStatus()
    {

            $param = $_REQUEST;

            $result = M("rechargeLevel")->where(['id'=> $param['id']])->save(['status'=>$param['status']]);
            if ($result !== false){
                return $this->success('操作成功');
            }else{
                return  $this->error('操作失败');
            }


    }

}