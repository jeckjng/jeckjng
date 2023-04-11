<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

use Admin\Cache\VideoCache;
use Admin\Cache\UsersAgentCache;
use Admin\Cache\UsersCache;
use Admin\Cache\WithdrawFeeConfigCache;
use Admin\Cache\VideoClassifyCache;


class SettingController extends AdminbaseController{
	
	protected $options_model;

    public function _initialize() {
		parent::_initialize();
		$this->options_model = D("Common/Options");
	}

    public function site(){
	    C(S('sp_dynamic_config'));//加载动态配置
		$option=$this->options_model->where("option_name='site_options'")->find();
		$cmf_settings=$this->options_model->where("option_name='cmf_settings'")->getField("option_value");
		$tpls=sp_scan_dir(C("SP_TMPL_PATH")."*",GLOB_ONLYDIR);
		$noneed=array(".","..",".svn");
		$tpls=array_diff($tpls, $noneed);
		$this->assign("templates",$tpls);
		
		$adminstyles=sp_scan_dir("public/simpleboot/themes/*",GLOB_ONLYDIR);
		$adminstyles=array_diff($adminstyles, $noneed);
		$this->assign("adminstyles",$adminstyles);
		if($option){
			$this->assign((array)json_decode($option['option_value']));
			$this->assign("option_id",$option['option_id']);
		}
		
		$this->assign("cmf_settings",json_decode($cmf_settings,true));
		
		
		$this->display();
	}

    public function site_post(){
		if (IS_POST) {
			if(isset($_POST['option_id'])){
				$data['option_id']=intval($_POST['option_id']);
			}
			
			$configs["SP_SITE_ADMIN_URL_PASSWORD"]=empty($_POST['options']['site_admin_url_password'])?"":md5(md5(C("AUTHCODE").$_POST['options']['site_admin_url_password']));
			$configs["SP_DEFAULT_THEME"]=$_POST['options']['site_tpl'];
			$configs["DEFAULT_THEME"]=$_POST['options']['site_tpl'];
			$configs["SP_ADMIN_STYLE"]=$_POST['options']['site_adminstyle'];
			$configs["URL_MODEL"]=$_POST['options']['urlmode'];
			$configs["URL_HTML_SUFFIX"]=$_POST['options']['html_suffix'];
			$configs["UCENTER_ENABLED"]=empty($_POST['options']['ucenter_enabled'])?0:1;
			$configs["COMMENT_NEED_CHECK"]=empty($_POST['options']['comment_need_check'])?0:1;
			$comment_time_interval=intval($_POST['options']['comment_time_interval']);
			$configs["COMMENT_TIME_INTERVAL"]=$comment_time_interval;
			$_POST['options']['comment_time_interval']=$comment_time_interval;
			$configs["MOBILE_TPL_ENABLED"]=empty($_POST['options']['mobile_tpl_enabled'])?0:1;
			$configs["HTML_CACHE_ON"]=empty($_POST['options']['html_cache_on'])?false:true;
				
			sp_set_dynamic_config($configs);//sae use same function
				
			$data['option_name']="site_options";
			$data['option_value']=json_encode($_POST['options']);
			if($this->options_model->where("option_name='site_options'")->find()){
				$r=$this->options_model->where("option_name='site_options'")->save($data);
			}else{
				$r=$this->options_model->add($data);
			}
			
			$banned_usernames=preg_replace("/[^0-9A-Za-z_\x{4e00}-\x{9fa5}-]/u", ",", $_POST['cmf_settings']['banned_usernames']);
			$_POST['cmf_settings']['banned_usernames']=$banned_usernames;

			sp_set_cmf_setting($_POST['cmf_settings']);
			
			if ($r!==false) {
				$this->success("保存成功！");
			} else {
				$this->error("保存失败！");
			}
			
		}
	}

    public function password(){
		$this->display();
	}

    public function password_post(){
		if (IS_POST) {
			if(empty($_POST['old_password'])){
				$this->error("原始密码不能为空！");
			}
			if(empty($_POST['password'])){
				$this->error("新密码不能为空！");
			}
			$user_obj = D("Common/Users");
			$uid=get_current_admin_id();
			$admin=$user_obj->where(array("id"=>$uid))->find();
			$old_password=$_POST['old_password'];
			$password=$_POST['password'];
			if(sp_compare_password($old_password,$admin['user_pass'])){
				if($_POST['password']==$_POST['repassword']){
					if(sp_compare_password($password,$admin['user_pass'])){
						$this->error("新密码不能和原始密码相同！");
					}else{
						$data['user_pass']=sp_password($password);
						$data['id']=$uid;
						$r=$user_obj->save($data);
						if ($r!==false) {
                            $action="管理员修改密码";
                    setAdminLog($action);
							$this->success("修改成功！");
						} else {
							$this->error("修改失败！");
						}
					}
				}else{
					$this->error("密码输入不一致！");
				}
	
			}else{
				$this->error("原始密码不正确！");
			}
		}
	}
	
	//清除缓存
	public function clearcache(){
        $param = I('param.');
        $param = empty($param) ? array_merge($_GET, $_POST) : $param;
        $action = isset($param['action']) ? $param['action'] : '';
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());
        try {
            switch ($action){
                case 'video':
                    $result = VideoCache::getInstance()->delPrivateListCache($tenant_id);
                    $result = VideoCache::getInstance()->delPublicListCache($tenant_id);
                    $result = VideoCache::getInstance()->delTopListCache($tenant_id);
                    $result = VideoCache::getInstance()->delAdvertiseListCache($tenant_id);
                    break;
                case 'agent':
                    $result = UsersAgentCache::getInstance()->delUserAllSuperiorCache($tenant_id);
                    $result = UsersAgentCache::getInstance()->delUserAllSubCache($tenant_id);
                    break;
                 case 'user_play_video':
                     $result = UsersCache::getInstance()->delUserPlayVideoCache($tenant_id);
                     $result = UsersCache::getInstance()->delUserHasWatchVideoCache();
                     break;
                case 'user_info':
                    $result = UsersCache::getInstance()->delAllUserInfoCache();
                    break;
                case 'common':
                    delcache('stopRoom_8920049_1667742728');
                    sp_clear_cache();
                    WithdrawFeeConfigCache::getInstance()->delCache($tenant_id);
                    VideoClassifyCache::getInstance()->delShortVideoClassifyCache($tenant_id);
                    break;
            }
        }catch (\Exception $e){
            setAdminLog('【清除缓存】失败：'.$e->getMessage());
            $this->error('操作失败');
        }
		$this->success('操作成功');
	}

	// 获取时间信息
	public function get_time_date_info(){
        $param = I('param.');

        $data['time_zone'] = date_default_timezone_get();
        $data['time_now'] = date('Y-m-d H:i:s');

        $this->success($data);
    }
	
	
}