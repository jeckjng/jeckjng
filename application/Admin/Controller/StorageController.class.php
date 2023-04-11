<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

use Admin\Model\CarModel;
use Admin\Model\GiftModel;
use Admin\Model\NobleModel;

class StorageController extends AdminbaseController{

    private $support_storages = array(
        "Local" => '系统本机',
        "Qiniu" => '七牛云存储',
        "Aliyunoss" => '阿里云OSS'
    );

	function _initialize() {
		parent::_initialize();
	}
	function index(){
        $param = I('param.');
        $info = M('options')->where(['option_name'=>'cmf_settings'])->find();
        if(!$info){
            $option_value = array(
                'banned_usernames' => '',
                'storage' => array(
                    'type' => 'Qiniu',
                    'Qiniu' => array(
                        'accessKey' => '',
                        'secretKey' => '',
                        'domain' => '',
                        'bucket' => '',
                        'upHost' => '',
                    ),
                ),
            );
            $data = array(
                'option_name' => 'cmf_settings',
                'option_value' => json_encode($option_value),
                'act_uid' => sp_get_current_admin_id(),
                'ctime' => time(),
            );
            M("options")->add($data);
            $info = M('options')->where(['option_name'=>'cmf_settings'])->find();
        }

	    $option_value = json_decode($info['option_value'],true);

        $this->assign('info', $info);
        $this->assign('option_value', $option_value);
        $this->assign('type_info', $option_value['storage'][$option_value['storage']['type']]);
        $this->assign('support_storages', $this->support_storages);
        $this->assign('param',$param);
        $this->display();
	}
	
	function setting_post(){
		if(IS_POST){
		    $param = I('post.');
            if(!isset($param['option_id']) || !$param['option_id']){
                $this->error('缺少id参数');
            }
		    if(!isset($param['type']) || !$param['type']){
		        $this->error('请选择存储类型');
            }
            if(!isset($param[$param['type']]) || !$param[$param['type']]){
                $this->error('缺少参数: '.$param['type']);
            }
            $tenant_id = getTenantIds();

            $option_value = array(
                'banned_usernames' => '',
                'storage' => array(
                    'type' => $param['type'],
                ),
            );

            foreach ($this->support_storages as $key=>$val){
                $option_value['storage'][$key] = array(
                    'accessKey' => $param[$key]['accessKey'],
                    'secretKey' => $param[$key]['secretKey'],
                    'domain' => $param[$key]['domain'],
                    'bucket' => $param[$key]['bucket'],
                    'upHost' => $param[$key]['upHost'],
                );
            }

            $data = array(
                'option_value' => json_encode($option_value),
                'act_uid' => sp_get_current_admin_id(),
                'mtime' => time(),
            );

            try {
                M("options")->where(["option_id"=>$param['option_id']])->save($data);
            }catch (\Exception $e){
                setAdminLog('编辑文件存储失败【'.$param['option_id'].' | '.$param['type'].'】'.$e->getMessage());
                $this->error("操作失败");
            }
            delStorage();
            getStorage();
            setAdminLog('编辑文件存储成功【'.$param['option_id'].' | '.$param['type'].'】');
            $config = getConfigPri($tenant_id);
            $url = $config['go_admin_url'].'/admin/v1/live_sync_cache/del_file_storage_config_cache';
            $res = http_post($url,['TenantId'=>$tenant_id]);

            $this->replace_file_url_domain();

            $this->success("操作成功");
		}
	}


	/*
	 * 文件域名替换
	 */
	public function replace_file_url_domain(){
        CarModel::getInstance()->updateFileUrlDomain();
        GiftModel::getInstance()->updateFileUrlDomain();
        NobleModel::getInstance()->updateFileUrlDomain();
    }
	
	
	
	
}