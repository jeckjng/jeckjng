<?php

/**
 * 租户管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TenantaloneController extends AdminbaseController {

    private $goGroup = '/tenant';
    private $thirdTenantName = 'alone';

    protected $users_model,$role_model;

    public function _initialize() {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->role_model = D("Common/Role");
    }

    public function index(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;
        $http_post_map = [
            'id' => 0,
            'page' => intval($p),
            'page_size' => intval($page_size),
            'enable' => -1,
            'third_tenant_name' => $this->thirdTenantName,
        ];
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map=array();

        if(isset($param['id']) && $param['id'] != ''){
            $map['id']=$param['id'];
            $http_post_map['id']=$param['id'];
        }
        if(isset($param['status']) && $param['status'] != '-1'){
            $map['status']=$param['status'];
            $http_post_map['enable'] = intval($param['status']);
        }

        if(enableGolangReplacePhp() === true){
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_tenant_list';
            $http_post_res = http_post($url, $http_post_map);
            $Data = $http_post_res['Data'];
            $page = $this->page($Data['count'], $page_size);
            $lists = $Data['list'];
        }else {
            $map['site_id'] = 2;
            $tenant = M("tenant");
            $count = $tenant->where($map)->count();
            $page = $this->page($count, 20);
            $lists = $tenant
                ->where($map)
                ->order("create_time DESC")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        }

        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    public function add(){
        $this->display();
    }
    public function add_post(){
        if(IS_POST){
            $param = I('param.');
            if(!isset($param['name']) || empty($param['name'])){
                $this->error('租户名称不能为空');
            }
            if(!isset($param['game_tenant_id']) || empty($param['game_tenant_id'])){
                $this->error('游戏系统租户ID不能为空');
            }

            $tenant_id = null;
            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'name' => $param['name'],
                    'domain' => $param['site'],
                    'enable' => $param['status']==1 ? true : false,
                    'enable_live' => $param['live_jurisdiction']==1 ? true : false,
                    'app_id' => "",
                    'app_key' => "",
                    'is_trying' => $param['is_trying']==1 ? true : false,
                    'trying_exp' => 0,
                    'third_tenant_name' => $this->thirdTenantName,
                    'third_tenant_id' => $param['game_tenant_id'],
                    'third_tenant_config' => json_encode([
                        'ThirdAppId' => $param['appid'] ? $param['appid'] : '',
                        'ThirdAppKey' => $param['appkey'] ? $param['appkey'] : '',
                        'BalanceQueryUrl' => $param['balance_query_url'] ? $param['balance_query_url'] : '',
                        'BalanceUpdateUrl' => $param['balance_update_url'] ? $param['balance_update_url'] : '',
                    ]),
                    'bank_name' => $param['account_bank'],
                    'bank_card_owner' => $param['account_name'],
                    'bank_card_no' => $param['bank_card'],
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/add_tenant';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
                //清除租户相关redis缓存
                delTenantCaches();
            }else{
                try {
                    M()->startTrans();

                    $isexist = M("tenant")->where(["name='{$param['name']}' or game_tenant_id='{$param['game_tenant_id']}'"])->find();
                    if ($isexist) {
                        $this->error('该租户名称/游戏租户id已存在');
                    }
                    $add_data = array(
                        'name' => trim($param['name']),
                        'site' => trim($param['site']),
                        'status' => intval($param['status']),
                        'type' => 1, // 租户类型 0-平台 1-租户
                        'appid' => md5(time().uniqid()),
                        'appkey' => trim($param['appkey']),
                        'game_tenant_id' => trim($param['game_tenant_id']),
                        'bank_card' => trim($param['bank_card']),
                        'account_name' => trim($param['account_name']),
                        'account_bank' => trim($param['account_bank']),
                        'balance_query_url' => isset($param['balance_query_url']) ? trim($param['balance_query_url']) : '',
                        'balance_update_url' => isset($param['balance_update_url']) ? trim($param['balance_update_url']) : '',
                        'site_id' => 2, // 租户类型：1 彩票租户 2 独立租户
                        'live_jurisdiction' => intval($param['live_jurisdiction']),
                        'balance_nft_url' => isset($param['balance_nft_url']) ? trim($param['balance_nft_url']) : '',
                        'operated_by' => get_current_admin_user_login(),
                    );

                    $tenant = M("tenant");
                    M("tenant")->add($add_data);
                    $tenant_id = (int)$tenant->getLastInsID();
                    M()->commit();
                }catch (\Exception $e){
                    M()->rollback();
                    setAdminLog('【新增租户-失败】'.$e->getMessage());
                    $this->error('操作失败');
                }
            }
            setAdminLog('【新增租户-成功】'.json_encode($param));
            // 新增站点相关的配置
            if($tenant_id){
                add_website_config($tenant_id);
            }

            $this->success("操作成功", U("index"));
        }
    }

    public function edit(){
        $param = I('param.');
        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_tenant_info';
            $http_post_res = http_post($url, ['id'=>intval($param['id'])]);
            $info = $http_post_res['Data'];
        }else {
            $id = intval($_GET['id']);
            if ($id) {
                $info = M("tenant")->where("id={$id}")->find();
            } else {
                $this->error('数据传入失败！');
            }
        }
        $this->assign('info', $info);
        $this->display();
    }

    public function edit_post(){
        $param = I('param.');
        if(IS_POST){
            $id=$_POST['id'];
            $name=$_POST['name'];
            $gameTenantId=$_POST['game_tenant_id'];

            if($param['name']==""){
                $this->error("租户名称不能为空！");
            }
            if($param['game_tenant_id']==""){
                $this->error("游戏系统租户ID不能为空！");
            }

            $redis = connectRedis();
            $redis->set('game_tenant_id', $param['game_tenant_id']);
            if(enableGolangReplacePhp() === true){
                // golang替换
                $info = http_post(goAdminUrl().goAdminRouter().$this->goGroup.'/get_tenant_info', ['id'=>intval($param['id'])])['Data'];
                $data = array(
                    'id' => intval($param['id']),
                    'name' => $param['name'],
                    'domain' => $param['site'],
                    'enable' => $param['status']==1 ? true : false,
                    'enable_live' => $param['live_jurisdiction']==1 ? true : false,
                    'app_id' => $param['app_id'],
                    'app_key' => $param['app_key'],
                    'is_trying' => $param['is_trying']==1 ? true : false,
                    'trying_exp' => intval($param['trying_exp']),
                    'third_tenant_name' => $this->thirdTenantName,
                    'third_tenant_id' => $param['game_tenant_id'],
                    'third_tenant_config' => json_encode([
                        'ThirdAppId' => $param['appid'] ? $param['appid'] : '',
                        'ThirdAppKey' => $param['appkey'] ? $param['appkey'] : '',
                        'BalanceQueryUrl' => $param['balance_query_url'] ? $param['balance_query_url'] : '',
                        'BalanceUpdateUrl' => $param['balance_update_url'] ? $param['balance_update_url'] : '',
                    ]),
                    'bank_name' => $param['account_bank'],
                    'bank_card_owner' => $param['account_name'],
                    'bank_card_no' => $param['bank_card'],
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/update_tenant';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
                //清除租户相关redis缓存
                delTenantCaches();
                delcache($id . '_' . 'getTenantInfo');
                if ($info['game_tenant_id'] != $param['game_tenant_id']) {
                    //更新租户的用户上的游戏租户id字段
                    M("users")->where("tenant_id='{$id}'")->setField("game_tenant_id", $gameTenantId);
                    //清除用户redis缓存
                    delUsersCaches();
                }
            }else {
                $rules = array(
                    array('name', 'require', '租户名称不能为空！'),
                    array('game_tenant_id', 'require', '游戏系统租户ID不能为空！'),
                );
                $tenant = M("tenant");
                //去除域名条件
                $isexist = $tenant->where(" ( name='{$name}' or game_tenant_id='{$gameTenantId}' ) and id!='{$id}'")->find();

                if ($isexist) {
                    $this->error('该租户名称/游戏租户id已存在');
                }
                $tenantinfo = $tenant->where("id='{$id}'")->find();
                $tenant = M("tenant");
                if (!$tenant->validate($rules)->create()) {
                    $this->error($tenant->getError());
                } else {

                }
                $result = $tenant->save();
                if ($result !== false) {
                    //清除租户相关redis缓存
                    delTenantCaches();
                    delcache($id . '_' . 'getTenantInfo');

                    if ($tenantinfo['game_tenant_id'] != $gameTenantId) {
                        //更新租户的用户上的游戏租户id字段
                        M("users")->where("tenant_id='{$id}'")->setField("game_tenant_id", $gameTenantId);
                        //清除用户redis缓存
                        delUsersCaches();
                    }
                } else {
                    $this->error('修改失败');
                }
            }
            $this->success('修改成功', U("index"));
        }
    }

}
