<?php
/* 
   扩展配置
 */

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TenantConfigController extends AdminbaseController{
	
	protected $attribute;
	
	function _initialize() {
		parent::_initialize();
	}
	
	function index(){
		
		$config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
        $menuList = M('menu')->where(['parentid'=>$_GET['menuid']])->order('listorder ASC')->select();
        foreach ($menuList as $key=>$value){
            $menuAction = $value['app'].'/'.$value['model'].'/'.$value['action'];
            if (!sp_auth_check($_SESSION['ADMIN_ID'],$menuAction)){
                unset($menuList[$key]);
            }
        }
        $menuList = array_column($menuList,'name','action');
        $this->assign('menu_list',$menuList);
		$this->assign('config',$config);

		$this->display();
	}

	public function set_post1(){
	    $this->set_post();
    }
    public function set_post2(){
        $this->set_post();
    }
    public function set_post3(){
        $this->set_post();
    }
    public function set_post4(){
        $this->set_post();
    }
    public function set_post5(){
        $this->set_post();
    }
    public function set_post6(){
        $this->set_post();
    }
    public function set_post7(){
        $this->set_post();
    }
    public function set_post8(){
        $this->set_post();
    }

    public function set_post(){
	    try{
            if(IS_POST){
                $config=I("post.post");

                $config['login_type']=implode(",",$config['login_type']);
                $config['share_type']=implode(",",$config['share_type']);
                if (isset( $config['seeking_slice_effective_time'])){
                    $config['seeking_slice_effective_time']=$config['seeking_slice_effective_time'] * 86400;
                }

                if (isset( $config['order_statistics_code'])){

                }
                foreach($config as $k=>$v){
                    $config[$k]=html_entity_decode($v);
                }
                $tenantId=getTenantIds();
                if (M("tenant_config")->where('tenant_id="'.$tenantId.'"')->save($config)!==false) {
                    //$key=$tenantId.'_'.'getTenantConfig';
                    //setcaches($key,$config);
                    delcache($tenantId.'_'.'getTenantConfig');
                    delcache($tenantId.'_'."getPlatformConfig");

                    $action="修改租户设置";
                    setAdminLog($action);
                    $this->success("保存成功！");
                } else {
                    $this->error("保存失败！");
                }
            }
        }catch (\Exception $e){
	        setAdminLog('修改租户设置失败：'.$e->getMessage());
            $this->error("保存失败！");
        }
	}

}