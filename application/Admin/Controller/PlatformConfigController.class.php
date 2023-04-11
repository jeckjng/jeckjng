<?php
/* 
   扩展配置
 */

namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PlatformConfigController extends AdminbaseController{
	
	protected $attribute;
	
	function _initialize() {
		parent::_initialize();
	}
	
	function index(){
	    $param = I('param.');
        $param['li_key'] = isset($param['li_key']) ? $param['li_key'] : 'base_set';
        $tenantId = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
		$config=M("platform_config")->where(['tenant_id'=>$tenantId])->find();
		if(!$config){
            M("platform_config")->add(['tenant_id'=>$tenantId]);
            $config=M("platform_config")->where(['tenant_id'=>$tenantId])->find();
        }
        $menuList = M('menu')->where(['parentid'=>$_GET['menuid']])->order('listorder ASC')->select();
        foreach ($menuList as $key=>$value){
            $menuAction = $value['app'].'/'.$value['model'].'/'.$value['action'];
            if (!sp_auth_check($_SESSION['ADMIN_ID'],$menuAction)){
                unset($menuList[$key]);
            }
        }
        $menuList = array_column($menuList,'name','action');

        $hours = array();
        for($i=0;$i<=23;$i++){
            array_push($hours,$i);
        }

        $tenant_list = getTenantList(); // M('tenant')->where(['id'=>['gt',0]])->field('id,name')->select(); // 彩票租户列表

        $config['cash_account_type'] = explode(',', $config['cash_account_type']);
        $config['url_of_push_to_java_cut_video_rows'] = count(explode("\n",$config['url_of_push_to_java_cut_video'])) + 1;

        $this->assign('menu_list',$menuList);
		$this->assign('config',$config);
        $this->assign('hours',$hours);
        $this->assign('tenant_list',$tenant_list);
        $this->assign('tenantId',$tenantId);
        $this->assign('role_id',getRoleId());
        $this->assign('cash_account_type_list',cash_account_type_list());
        $this->assign('cash_network_type_list',cash_network_type_list());
        $this->assign('param',$param);
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
            if($_FILES){
                if ($_FILES["file"]["error"] > 0) {
                    $rs['code'] = 1003;
                    $rs['msg'] = T('failed to upload file with error: {error}', array('error' => $_FILES['file']['error']));
                    DI()->logger->debug('failed to upload file with error: ' . $_FILES['file']['error']);
                    return $rs;
                }
                $_FILES["file"]["name"]=date('YmdHis').'.jpg';
                if (file_exists("api/upload/" . $_FILES["file"]["name"])){
                    echo $_FILES["file"]["name"] . " 文件已经存在。 ";
                }else{
                    // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
                    move_uploaded_file($_FILES["file"]["tmp_name"], "api/upload/" . $_FILES["file"]["name"]);
                }
                $url =  'http://'.$_SERVER['SERVER_NAME'].'/api/upload/'.$_FILES["file"]["name"];
                @unlink($_FILES['file']['tmp_name']);
            }

            if(IS_POST){

                $config = I("post.post"); // var_dump($config);exit;
                $param = $config;
                if(!isset($config['id']) || !$config['id']){
                    $this->error('缺少参数');
                }
                if($_FILES){
                    $config['votes_icon'] =  $url;
                }
                $config['game_switch'] = implode(",",$config['game_switch']);
                $config['live_type'] = implode(",",$config['live_type']);
                $config['cash_account_type'] = implode(",", $config['cash_account_type']);
                if(isset($config['url_of_push_to_java_cut_video']) && $config['url_of_push_to_java_cut_video']){
                    $arr_cut_video_url = explode("\n", $config['url_of_push_to_java_cut_video']);
                    $temp_pull = array();
                    foreach ($arr_cut_video_url as $key=>$val){
                        $val = trim($val);
                        if($val){
                            array_push($temp_pull,$val);
                        }
                    }
                    $config['url_of_push_to_java_cut_video'] = implode("\n",$temp_pull);
                }

                if(!isset($config['user_agreement'])){
                    $config['user_agreement'] = '';
                }
                if(empty( $config['explain'])){
                    unset($config['explain']);
                }
                if(empty( $config['yuebao_explain'])){
                    unset($config['yuebao_explain']);
                }
                if(empty( $config['shop_explain'])){
                    unset($config['shop_explain']);
                }
                unset($config['tenant_id']);
                unset($config['menuid']);
                unset($config['li_key']);
                foreach($config as $k=>$v){
                    $config[$k]=trim(html_entity_decode($v));
                }


                $info = M("platform_config")->where(["id"=>$config['id']])->find();

                if (M("platform_config")->where(["id"=>$config['id']])->save($config)!==false) {
                    $action="修改平台设置";
                    setAdminLog($action);

//                    delPatternCacheKeys('*_'.'getTenantConfig');
//                    delPatternCacheKeys('*_'."getPlatformConfig");

                    delcache($info['tenant_id'].'_'.'getTenantConfig');
                    delcache($info['tenant_id'].'_'.'getPlatformConfig');

                    if($config['live_nums_min'] != $info['live_nums_min'] || $config['live_nums_max'] != $info['live_nums_max']){
                        delPatternCacheKeys("LiveNumsDefault_"."*");  // 清除直播初始人数始默认值
                    }
                    if($config['live_fans_min'] != $info['live_fans_min'] || $config['live_fans_max'] != $info['live_fans_max']){
                        delPatternCacheKeys("LiveFansDefault_"."*"); // 清除直播初始粉丝人数始默认值
                    }
                    if($config['live_votestotal_min'] != $info['live_votestotal_min'] || $config['live_votestotal_max'] != $info['live_votestotal_max']){
                        delPatternCacheKeys("LiveVotestotalDefault_"."*"); // 清除	直播初始收入打赏始默认值
                    }

                    $this->success("保存成功！",U('index',array('tenant_id'=>$param['tenant_id'],'menuid'=>$param['menuid'],'li_key'=>$param['li_key'])));
                } else {
                    $this->error("保存失败！");
                }
            }
            $this->error('操作失败，请求方式错误');
        }catch (\Exception $e){
            $msg = $e->getMessage();
            setAdminLog('编辑平台设置失败: '.$msg);
            $this->error('操作失败');
        }
	}
}