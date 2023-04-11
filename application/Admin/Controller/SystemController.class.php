<?php

/**
 * 系统消息
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SystemController extends AdminbaseController {
    protected $users_model,$role_model;
    function _initialize() {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->tenamt_model = D("Common/Tenant");
    }
    function index(){
        $param = I("param.");
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $list = getTenantList();
        foreach($list as $key=>$val){
            $config = getConfigPri($val['tenant_id']);
            $list[$key]['socket_type'] = $config['socket_type'];
            if($config['socket_type'] == 3){
                $list[$key]['chatserver'] = $config['go_socket_url'];
            }else{
                $list[$key]['chatserver'] = $config['chatserver'];
            }
        }

        $this->assign("list",$list);
        $this->assign("tenant_id",$tenant_id);
    	$this->display("edit");
    }

    public function system_message(){
        $param = I('param.');
        if(!isset($param['tenant_id']) || !$param['tenant_id']){
            $this->error('请选择租户');
        }
        if(!isset($param['content']) || !$param['content']){
            $this->error('请输入消息内容');
        }

        $tenant_id = $param['tenant_id'];
        $tenant_info = getTenantInfo($param['tenant_id']);
        $game_tenant_id = $tenant_info['game_tenant_id'];
        $config = getConfigPri($tenant_id);
        if($tenant_id && $game_tenant_id && $config['go_admin_url']){
            $url = $config['go_admin_url'].'/admin/v1/live_room/broadcast_system_event';
            $res = http_post($url,['EventType'=>'SystemNot', 'Message'=>json_encode(['TenantId'=>$tenant_id,'GameTenantId'=>$game_tenant_id,'Content'=>html_entity_decode($param['content'])]) ]);
        }
        $this->success('操作成功');
    }
		
	function send(){
		$content=I("content");

        $tenantId=getTenantIds();
		if(!$content){
			$data=array(
				"error"=>10001,
				"data"=>'',
				"msg"=>'内容不能为空'
			);
		}
        $tenantinfo = explode('_',$_POST['tenant_id']);
        $result = array(
            'msgtype'=>101,
            'content'=>$_POST['content'],
            'tenantId'=>$tenantinfo[0],
            'game_tenant_id'=>$tenantinfo[1],
            "token"=>"1234567",
        );

		$gonggao = array(
            '_method_' => 'GameNews',
            'action' => 'GameNews',
            'gamedata' => $result,
            'token' =>   '1234567',
            'tenantId'=>$tenantinfo[0],
            'game_tenant_id'=>$tenantinfo[1],
        );
        $redis = connectionRedis();
        $room_announcement = $redis->lPush('room_announcement',json_encode($gonggao));
        $announcement_bak = $redis->lPush('announcement_bak',json_encode($gonggao));


       /* $action="发送系统消息：{$content}";
                    setAdminLog($action);*/

		$data=array(
			"error"=>0,
			"data"=>$tenantId,
			"msg"=>''
		);
				
		echo json_encode($data);
		
	}		
		
}
