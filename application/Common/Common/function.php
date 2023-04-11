<?php

use Admin\Model\UsersCoinrecordModel;
use Admin\Model\UsersModel;
use Common\Controller\CustRedis;
use Admin\Model\LogComplexModel;

use Admin\Cache\UsersCache;

/**
 * 清除租户相关缓存
 */
function delTenantCaches(){
    delcache('tenant_list_all');
    delPatternCacheKeys('tenant*');
}

function delUsersCaches(){
    $keys='userinfo*';
    delPatternCacheKeys($keys);
}

/*
 * 新增站点相关的配置
 * */
function add_website_config($tenant_id){
    // 添加播放下载线路
    add_playback_address($tenant_id);
    // 添加网站设置
    add_tenant_config($tenant_id);
}

/*
 * 添加播放下载线路
 * */
function add_playback_address($tenant_id){
    if(!M("playback_address")->where(['tenant_id'=>intval($tenant_id)])->find()){
        $playback_address = [];
        for ($i= 0; $i< 10;$i++){
            if ($i == 0){
                $playback_address[] = ['url' => '', 'is_enable'=>0, 'type' => 1,'viode_table_field'=> 'href','tenant_id'=>intval($tenant_id)];
            }else{
                $playback_address[] = ['url' => '', 'is_enable'=>0, 'type' => 1,'viode_table_field'=> 'href_'.$i,'tenant_id'=>intval($tenant_id)];
            }
        }
        for ($i= 0; $i< 3;$i++){
            if ($i == 0){
                $playback_address[] = ['url' => '', 'is_enable'=>0, 'type' => 2,'viode_table_field'=> 'download_address','tenant_id'=>intval($tenant_id)];
            }else{
                $playback_address[] = ['url' => '', 'is_enable'=>0, 'type' => 2,'viode_table_field'=> 'download_address_'.$i,'tenant_id'=>intval($tenant_id)];
            }
        }
        M("playback_address")->addAll($playback_address); // 用于视频播放线路
        return true;
    }
    return false;
}

/*
 * 添加网站设置
 * */
function add_tenant_config($tenant_id){
    if(!M("tenant_config")->where(['tenant_id'=>intval($tenant_id)])->find()){
        $tenantConfigData = array(
            'tenant_id' => intval($tenant_id),
            'site' => '',
            'sitename' => ''
        );
        M("tenant_config")->add($tenantConfigData);
        return true;
    }
    return false;
}

function getTenantInfoFromGameTenantId($gameTenantId){
/*    $key='tenant_gameTenantId_'.$gameTenantId;
    $tenantInfo= getcaches($key);
    if(!$tenantInfo){
        $tenantInfo=M("tenant")->where("status='1' and game_tenant_id='$gameTenantId' ")->find();
        setcaches($key,$tenantInfo);
    }*/

    $tenantInfo = array();
    $tenant_list = getTenantList();
    foreach ($tenant_list as $key=>$val){
        if($val['status'] == 1 && $val['game_tenant_id'] == $gameTenantId){
            $tenantInfo = $val;
        }
    }
    return $tenantInfo;
}
function getPlatformTenantInfo(){
/*    $platformTenantId=1;
    $key='tenant_'.$platformTenantId;
    $tenantInfo= getcaches($key);
    if(!$tenantInfo){
        $tenantInfo=M("tenant")->where("status='1' and id='$platformTenantId' ")->find();
        setcaches($key,$tenantInfo);
    }*/

    $tenantInfo = array();
    $tenant_list = getTenantList();
    foreach ($tenant_list as $key=>$val){
        if($val['status'] == 1 && $val['id'] == 1){
            $tenantInfo = $val;
        }
    }
    return $tenantInfo;
}
/* 根据租户id获取租户信息 */
function getTenantInfo($tenantId) {
  /*  if(empty($tenantId)){
        return null;
    }
    $key=$tenantId.'_'.'getTenantInfo';
    $tenantInfo=getcaches($key);
    if(!$tenantInfo){
        $tenantInfo=M("tenant")->where("id='$tenantId'")->find();
        setcaches($key,$tenantInfo);
    }*/

    $tenantInfo = getTenantList($tenantId);
    return $tenantInfo;
}

/* 获取租户列表 */
function getTenantList($tenant_id = null) {
    $key = 'tenant_list_all';
    $list = getcaches($key);
    if (!$list || empty($list)) {
        if(enableGolangReplacePhp() === true){
            $url = goAdminUrl().goAdminRouter().'/tenant/get_tenant_list_all';
            $http_post_map = [
                'third_tenant_name' => '',
            ];
            $http_post_res = http_post($url, $http_post_map);
            $list = $http_post_res['Data'];
        }else {
            $list = M("tenant")->where(['id' => ['gt', 0]])->select();
        }
        setcaches($key, $list);
    }
    if($tenant_id){
        $list = array_column($list,null,'id');
        return isset($list[$tenant_id]) ? $list[$tenant_id] : [];
    }
    return 	$list;
}

function getTenantId(){
    return $_SESSION['tenantId'];
}
function getGameTenantId(){
    return $_SESSION['gameTenantId'];
}

//区分前端接口的tenantId
function getTenantIds(){
    return $_SESSION['tenantIds'];
}
function getGameTenantIds(){
    return $_SESSION['gameTenantIds'];
}

/**
 * 获取当前登录的管事员id
 * @return int
 */
function get_current_admin_id(){
	return $_SESSION['ADMIN_ID'];
}

/**
 * 获取当前登录的管事员账号
 * @return int
 */
function get_current_admin_user_login(){
    return $_SESSION['name'];
}



/**
 * 获取当前登录的管事员id
 * @return int
 */
function sp_get_current_admin_id(){
	return get_current_admin_id();
}

/**
 * 判断前台用户是否登录
 * @return boolean
 */
function sp_is_user_login(){
	return  !empty($_SESSION['user']);
}

/**
 * 获取当前登录的前台用户的信息，未登录时，返回false
 * @return array|boolean
 */
function sp_get_current_user(){
	if(isset($_SESSION['user'])){
		return $_SESSION['user'];
	}else{
		return false;
	}
}

/**
 * 更新当前登录前台用户的信息
 * @param array $user 前台用户的信息
 */
function sp_update_current_user($user){
	$_SESSION['user']=$user;
}

/**
 * 获取当前登录前台用户id,推荐使用sp_get_current_userid
 * @return int
 */
function get_current_userid(){
	
	if(isset($_SESSION['user'])){
		return $_SESSION['user']['id'];
	}else{
		return 0;
	}
}

/*
 * 获取当前登录账号的角色id
 * */
function getRoleId(){
    return $_SESSION['role_id'];
}


/**
 * 获取当前登录前台用户id
 * @return int
 */
function sp_get_current_userid(){
	return get_current_userid();
}

/**
 * 返回带协议的域名
 */
function sp_get_host(){
	$host=$_SERVER["HTTP_HOST"];
	$protocol=is_ssl()?"https://":"http://";
	return $protocol.$host;
}

/**
 * 获取前台模板根目录
 */
function sp_get_theme_path(){
	// 获取当前主题名称
	$tmpl_path=C("SP_TMPL_PATH");
	$theme      =    C('SP_DEFAULT_THEME');
	if(C('TMPL_DETECT_THEME')) {// 自动侦测模板主题
		$t = C('VAR_TEMPLATE');
		if (isset($_GET[$t])){
			$theme = $_GET[$t];
		}elseif(cookie('think_template')){
			$theme = cookie('think_template');
		}
		if(!file_exists($tmpl_path."/".$theme)){
			$theme  =   C('SP_DEFAULT_THEME');
		}
		cookie('think_template',$theme,864000);
	}

	return __ROOT__.'/'.$tmpl_path.$theme."/";
}


/**
 * 获取用户头像相对网站根目录的地址
 */
function sp_get_user_avatar_url($avatar){
	
	if($avatar){
		if(strpos($avatar, "http")===0){
			return $avatar;
		}else {
			return sp_get_asset_upload_path("avatar/".$avatar);
		}
		
	}else{
		return $avatar;
	}
	
}

/**
 * CMF密码加密方法
 * @param string $pw 要加密的字符串
 * @return string
 */
function sp_password($pw,$authcode=''){
    if(empty($authcode)){
        $authcode=C("AUTHCODE");
    }
	$result="###".md5(md5($authcode.$pw));
	return $result;
}

/**
 * CMF密码加密方法 (X2.0.0以前的方法)
 * @param string $pw 要加密的字符串
 * @return string
 */
function sp_password_old($pw){
    $decor=md5(C('DB_PREFIX'));
    $mi=md5($pw);
    return substr($decor,0,12).$mi.substr($decor,-4,4);
}

/**
 * CMF密码比较方法,所有涉及密码比较的地方都用这个方法
 * @param string $password 要比较的密码
 * @param string $password_in_db 数据库保存的已经加密过的密码
 * @return boolean 密码相同，返回true
 */
function sp_compare_password($password,$password_in_db){
    if(strpos($password_in_db, "###")===0){
        return sp_password($password)==$password_in_db;
    }else{
        return sp_password_old($password)==$password_in_db;
    }
}


function sp_log($content,$file="log.txt"){
	file_put_contents($file, $content,FILE_APPEND);
}

/**
 * 随机字符串生成
 * @param int $len 生成的字符串长度
 * @return string
 */
function sp_random_string($len = 6) {
	$chars = array(
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
			"l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
			"w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
			"H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
			"S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
			"3", "4", "5", "6", "7", "8", "9"
	);
	$charsLen = count($chars) - 1;
	shuffle($chars);    // 将数组打乱
	$output = "";
	for ($i = 0; $i < $len; $i++) {
		$output .= $chars[mt_rand(0, $charsLen)];
	}
	return $output;
}

/**
 * 清空系统/redis缓存，兼容sae
 */
function sp_clear_cache(){
    import ( "ORG.Util.Dir" );
    $dirs = array ();
    // runtime/
    $rootdirs = sp_scan_dir( RUNTIME_PATH."*" );
    //$noneed_clear=array(".","..","Data");
    $noneed_clear=array(".","..");
    $rootdirs=array_diff($rootdirs, $noneed_clear);
    foreach ( $rootdirs as $dir ) {

        if ($dir != "." && $dir != "..") {
            $dir = RUNTIME_PATH . $dir;
            if (is_dir ( $dir )) {
                //array_push ( $dirs, $dir );
                $tmprootdirs = sp_scan_dir ( $dir."/*" );
                foreach ( $tmprootdirs as $tdir ) {
                    if ($tdir != "." && $tdir != "..") {
                        $tdir = $dir . '/' . $tdir;
                        if (is_dir ( $tdir )) {
                            array_push ( $dirs, $tdir );
                        }else{
                            @unlink($tdir);
                        }
                    }
                }
            }else{
                @unlink($dir);
            }
        }
    }
    $dirtool=new \Dir("");
    foreach ( $dirs as $dir ) {
        $dirtool->delDir ( $dir );
    }

    if(sp_is_sae()){
        $global_mc=@memcache_init();
        if($global_mc){
            $global_mc->flush();
        }

        $no_need_delete=array("THINKCMF_DYNAMIC_CONFIG");
        $kv = new SaeKV();
        // 初始化KVClient对象
        $ret = $kv->init();
        // 循环获取所有key-values
        $ret = $kv->pkrget('', 100);
        while (true) {
            foreach($ret as $key =>$value){
                if(!in_array($key, $no_need_delete)){
                    $kv->delete($key);
                }
            }
            end($ret);
            $start_key = key($ret);
            $i = count($ret);
            if ($i < 100) break;
            $ret = $kv->pkrget('', 100, $start_key);
        }

    }
    $tenant_list = getTenantList();

    // 删除菜单缓存
    F("Menu", null);

    delcache($_SESSION['ADMIN_ID']);
    // 清除key-val配置redis 缓存
    delcache('kvconfig');
    // 清除租户列表缓存
    delcache('tenant_list_all');
    // 清除国家列表缓存
    delcache('country_list');
    // 清除auth_access列表缓存
    delcache('auth_access_list');

    if(count($tenant_list) > 0){
        foreach ($tenant_list as $key=>$val){
            $tenant_id = $val['id'];
            // 清除租户和平台配置
            delcache($tenant_id.'_'.'getTenantConfig');
            delcache($tenant_id.'_'."getPlatformConfig");
            // 清除综合汇总表缓存（今日）
            delcache("complex_summary_this_day".$tenant_id);
            // 清除综合汇总表缓存（本周）
            delcache("complex_summary_this_week".$tenant_id);
            // 清除综合汇总表缓存（本月）
            delcache("complex_summary_this_month".$tenant_id);
            // 清除直播间列表缓存
            delcache("user_live_list_".$tenant_id);
            // 清除礼物列表缓存
            delcache("getGiftList_".$tenant_id);
            delcache("getGiftall_".$tenant_id);

            // 清除汇率列表缓存
            delcache('ratelist_'.$tenant_id);
            // 清除用户vip信息缓存
            delcache('user_vip_info_'.$tenant_id);
            delcache('user_vip_checking_info_'.$tenant_id);
            // 清除vip等级列表缓存
            delcache('vip_grade_list_'.$tenant_id);
            // 清除置顶的视频列表缓存
            delcache('top_video_list_'.$tenant_id);
            // 清理直播分类列表
            delcache('getLiveClass_'.$tenant_id);
        }
    }else{
        // 清除租户和平台配置
        delPatternCacheKeys('*_'.'getTenantConfig');
        delPatternCacheKeys('*_'."getPlatformConfig");
        // 清除综合汇总表缓存（今日）
        delPatternCacheKeys("complex_summary_this_day"."*");
        // 清除综合汇总表缓存（本周）
        delPatternCacheKeys("complex_summary_this_week"."*");
        // 清除综合汇总表缓存（本月）
        delPatternCacheKeys("complex_summary_this_month"."*");
        // 清除直播间列表缓存
        delPatternCacheKeys("user_live_list_"."*");
        // 清理直播分类列表
        delPatternCacheKeys('getLiveClass_'."*");
    }
}

/**
 * 保存数组变量到php文件
 */
function sp_save_var($path,$value){
	$ret = file_put_contents($path, "<?php\treturn " . var_export($value, true) . ";?>");
	return $ret;
}

/**
 * 更新系统配置文件
 * @param array $data <br>如：array("URL_MODEL"=>0);
 * @return boolean
 */
function sp_set_dynamic_config($data){
	
	if(!is_array($data)){
		return false;
	}
	
	if(sp_is_sae()){
		$kv = new SaeKV();
		$ret = $kv->init();
		$configs=$kv->get("THINKCMF_DYNAMIC_CONFIG");
		$configs=empty($configs)?array():unserialize($configs);
		$configs=array_merge($configs,$data);
		$result = $kv->set('THINKCMF_DYNAMIC_CONFIG', serialize($configs));
	}elseif(defined('IS_BAE') && IS_BAE){
		$bae_mc=new BaeMemcache();
		$configs=$bae_mc->get("THINKCMF_DYNAMIC_CONFIG");
		$configs=empty($configs)?array():unserialize($configs);
		$configs=array_merge($configs,$data);
		$result = $bae_mc->set("THINKCMF_DYNAMIC_CONFIG",serialize($configs),MEMCACHE_COMPRESSED,0);
	}else{
		$config_file="./data/conf/config.php";
		if(file_exists($config_file)){
			$configs=include $config_file;
		}else {
			$configs=array();
		}
		$configs=array_merge($configs,$data);
		$result = file_put_contents($config_file, "<?php\treturn " . var_export($configs, true) . ";");
	}
	sp_clear_cache();
	S("sp_dynamic_config",$configs);
	return $result;
}


/**
 * 生成参数列表,以数组形式返回
 */
function sp_param_lable($tag = ''){
	$param = array();
	$array = explode(';',$tag);
	foreach ($array as $v){
		$v=trim($v);
		if(!empty($v)){
			list($key,$val) = explode(':',$v);
			$param[trim($key)] = trim($val);
		}
	}
	return $param;
}


/**
 * 获取后台管理设置的网站信息，此类信息一般用于前台，推荐使用sp_get_site_options
 */
function get_site_options(){
	$site_options = F("site_options");
	if(empty($site_options)){
		$options_obj = M("Options");
		$option = $options_obj->where("option_name='site_options'")->find();
		if($option){
			$site_options = json_decode($option['option_value'],true);
		}else{
			$site_options = array();
		}
		F("site_options", $site_options);
	}
	$site_options['site_tongji']=htmlspecialchars_decode($site_options['site_tongji']);
	return $site_options;	
}

/**
 * 获取后台管理设置的网站信息，此类信息一般用于前台
 */
function sp_get_site_options(){
	get_site_options();
}

/**
 * 获取CMF系统的设置，此类设置用于全局
 * @param string $key 设置key，为空时返回所有配置信息
 * @return mixed
 */
function sp_get_cmf_settings($key=""){
	$cmf_settings = F("cmf_settings");
	if(empty($cmf_settings)){
		$options_obj = M("Options");
		$option = $options_obj->where("option_name='cmf_settings'")->find();
		if($option){
			$cmf_settings = json_decode($option['option_value'],true);
		}else{
			$cmf_settings = array();
		}
		F("cmf_settings", $cmf_settings);
	}
	if(!empty($key)){
		return $cmf_settings[$key];
	}
	return $cmf_settings;
}

/**
 * 更新CMF系统的设置，此类设置用于全局
 * @param array $data 
 * @return boolean
 */
function sp_set_cmf_setting($data){
	if(!is_array($data) || empty($data) ){
		return false;
	}
	$cmf_settings['option_name']="cmf_settings";
	$options_model=M("Options");
	$find_setting=$options_model->where("option_name='cmf_settings'")->find();
	F("cmf_settings",null);
	if($find_setting){
		$setting=json_decode($find_setting['option_value'],true);
		if($setting){
			$setting=array_merge($setting,$data);
		}else {
			$setting=$data;
		}
		
		$cmf_settings['option_value']=json_encode($setting);
		return $options_model->where("option_name='cmf_settings'")->save($cmf_settings);
	}else{
		$cmf_settings['option_value']=json_encode($data);
		return $options_model->add($cmf_settings);
	}
}




/**
 * 全局获取验证码图片
 * 生成的是个HTML的img标签
 * @param string $imgparam <br>
 * 生成图片样式，可以设置<br>
 * length=4&font_size=20&width=238&height=50&use_curve=1&use_noise=1<br>
 * length:字符长度<br>
 * font_size:字体大小<br>
 * width:生成图片宽度<br>
 * heigh:生成图片高度<br>
 * use_curve:是否画混淆曲线  1:画，0:不画<br>
 * use_noise:是否添加杂点 1:添加，0:不添加<br>
 * @param string $imgattrs<br>
 * img标签原生属性，除src,onclick之外都可以设置<br>
 * 默认值：style="cursor: pointer;" title="点击获取"<br>
 * @return string<br>
 * 原生html的img标签<br>
 * 注，此函数仅生成img标签，应该配合在表单加入name=verify的input标签<br>
 * 如：&lt;input type="text" name="verify"/&gt;<br>
 */
function sp_verifycode_img($imgparam='length=4&font_size=20&width=238&height=50&use_curve=1&use_noise=1',$imgattrs='style="cursor: pointer;" title="点击获取"'){
	$src=__ROOT__."/index.php?g=api&m=checkcode&a=index&".$imgparam;
	$img=<<<hello
<img class="verify_img" src="$src" onclick="this.src='$src&time='+Math.random();" $imgattrs/>
hello;
	return $img;
}


/**
 * 返回指定id的菜单
 * 同上一类方法，jquery treeview 风格，可伸缩样式
 * @param $myid 表示获得这个ID下的所有子级
 * @param $effected_id 需要生成treeview目录数的id
 * @param $str 末级样式
 * @param $str2 目录级别样式
 * @param $showlevel 直接显示层级数，其余为异步显示，0为全部限制
 * @param $ul_class 内部ul样式 默认空  可增加其他样式如'sub-menu'
 * @param $li_class 内部li样式 默认空  可增加其他样式如'menu-item'
 * @param $style 目录样式 默认 filetree 可增加其他样式如'filetree treeview-famfamfam'
 * @param $dropdown 有子元素时li的class
 * $id="main";
 $effected_id="mainmenu";
 $filetpl="<a href='\$href'><span class='file'>\$label</span></a>";
 $foldertpl="<span class='folder'>\$label</span>";
 $ul_class="" ;
 $li_class="" ;
 $style="filetree";
 $showlevel=6;
 sp_get_menu($id,$effected_id,$filetpl,$foldertpl,$ul_class,$li_class,$style,$showlevel);
 * such as
 * <ul id="example" class="filetree ">
 <li class="hasChildren" id='1'>
 <span class='folder'>test</span>
 <ul>
 <li class="hasChildren" id='4'>
 <span class='folder'>caidan2</span>
 <ul>
 <li class="hasChildren" id='5'>
 <span class='folder'>sss</span>
 <ul>
 <li id='3'><span class='folder'>test2</span></li>
 </ul>
 </li>
 </ul>
 </li>
 </ul>
 </li>
 <li class="hasChildren" id='6'><span class='file'>ss</span></li>
 </ul>
 */

function sp_get_menu($id="main",$effected_id="mainmenu",$filetpl="<span class='file'>\$label</span>",$foldertpl="<span class='folder'>\$label</span>",$ul_class="" ,$li_class="" ,$style="filetree",$showlevel=6,$dropdown='hasChild'){
	$navs=F("site_nav_".$id);
	if(empty($navs)){
		$navs=_sp_get_menu_datas($id);
	}
	
	import("Tree");
	$tree = new \Tree();
	$tree->init($navs);
	return $tree->get_treeview_menu(0,$effected_id, $filetpl, $foldertpl,  $showlevel,$ul_class,$li_class,  $style,  1, FALSE, $dropdown);
}


function _sp_get_menu_datas($id){
	$nav_obj= M("Nav");
	$oldid=$id;
	$id= intval($id);
	$id = empty($id)?"main":$id;
	if($id=="main"){
		$navcat_obj= M("NavCat");
		$main=$navcat_obj->where("active=1")->find();
		$id=$main['navcid'];
	}
	
	if(empty($id)){
		return array();
	}
	
	$navs= $nav_obj->where(array('cid'=>$id,'status'=>1))->order(array("listorder" => "ASC"))->select();
	foreach ($navs as $key=>$nav){
		$href=htmlspecialchars_decode($nav['href']);
		$hrefold=$href;
		
		if(strpos($hrefold,"{")){//序列 化的数据
			$href=unserialize(stripslashes($nav['href']));
			$default_app=strtolower(C("DEFAULT_MODULE"));
			$href=strtolower(leuu($href['action'],$href['param']));
			$g=C("VAR_MODULE");
			$href=preg_replace("/\/$default_app\//", "/",$href);
			$href=preg_replace("/$g=$default_app&/", "",$href);
		}else{
			if($hrefold=="home"){
				$href=__ROOT__."/";
			}else{
				$href=$hrefold;
			}
		}
		$nav['href']=$href;
		$navs[$key]=$nav;
	}
	F("site_nav_".$oldid,$navs);
	return $navs;
}


function sp_get_menu_tree($id="main"){
	$navs=F("site_nav_".$id);
	if(empty($navs)){
		$navs=_sp_get_menu_datas($id);
	}

	import("Tree");
	$tree = new \Tree();
	$tree->init($navs);
	return $tree->get_tree_array(0, "");
}



/**
 *获取html文本里的img
 * @param string $content
 * @return array
 */
function sp_getcontent_imgs($content){
	import("phpQuery");
	\phpQuery::newDocumentHTML($content);
	$pq=pq();
	$imgs=$pq->find("img");
	$imgs_data=array();
	if($imgs->length()){
		foreach ($imgs as $img){
			$img=pq($img);
			$im['src']=$img->attr("src");
			$im['title']=$img->attr("title");
			$im['alt']=$img->attr("alt");
			$imgs_data[]=$im;
		}
	}
	\phpQuery::$documents=null;
	return $imgs_data;
}


/**
 * 
 * @param unknown_type $navcatname
 * @param unknown_type $datas
 * @param unknown_type $navrule
 * @return string
 */
function sp_get_nav4admin($navcatname,$datas,$navrule){
	$nav['name']=$navcatname;
	$nav['urlrule']=$navrule;
	foreach($datas as $t){
		$urlrule=array();
		$group=strtolower(MODULE_NAME)==strtolower(C("DEFAULT_MODULE"))?"":MODULE_NAME."/";
		$action=$group.$navrule['action'];
		$urlrule['action']=MODULE_NAME."/".$navrule['action'];
		$urlrule['param']=array();
		if(isset($navrule['param'])){
			foreach ($navrule['param'] as $key=>$val){
				$urlrule['param'][$key]=$t[$val];
			}
		}
		
		$nav['items'][]=array(
				"label"=>$t[$navrule['label']],
				"url"=>U($action,$urlrule['param']),
				"rule"=>serialize($urlrule)
		);
	}
	return json_encode($nav);
}

function sp_get_apphome_tpl($tplname,$default_tplname,$default_theme=""){
	$theme      =    C('SP_DEFAULT_THEME');
	if(C('TMPL_DETECT_THEME')){// 自动侦测模板主题
		$t = C('VAR_TEMPLATE');
		if (isset($_GET[$t])){
			$theme = $_GET[$t];
		}elseif(cookie('think_template')){
			$theme = cookie('think_template');
		}
	}
	$theme=empty($default_theme)?$theme:$default_theme;
	$themepath=C("SP_TMPL_PATH").$theme."/".MODULE_NAME."/";
	$tplpath = sp_add_template_file_suffix($themepath.$tplname);
	$defaultpl = sp_add_template_file_suffix($themepath.$default_tplname);
	if(file_exists_case($tplpath)){
	}else if(file_exists_case($defaultpl)){
		$tplname=$default_tplname;
	}else{
		$tplname="404";
	}
	return $tplname;
}


/**
 * 去除字符串中的指定字符
 * @param string $str 待处理字符串
 * @param string $chars 需去掉的特殊字符
 * @return string
 */
function sp_strip_chars($str, $chars='?<*.>\'\"'){
	return preg_replace('/['.$chars.']/is', '', $str);
}

/**
 * 发送邮件
 * @param string $address
 * @param string $subject
 * @param string $message
 * @return array<br>
 * 返回格式：<br>
 * array(<br>
 * 	"error"=>0|1,//0代表出错<br>
 * 	"message"=> "出错信息"<br>
 * );
 */
function sp_send_email($address,$subject,$message){
	import("PHPMailer");
	$mail=new \PHPMailer();
	// 设置PHPMailer使用SMTP服务器发送Email
	$mail->IsSMTP();
	$mail->IsHTML(true);
	// 设置邮件的字符编码，若不指定，则为'UTF-8'
	$mail->CharSet='UTF-8';
	// 添加收件人地址，可以多次使用来添加多个收件人
	$mail->AddAddress($address);
	// 设置邮件正文
	$mail->Body=$message;
	// 设置邮件头的From字段。
	$mail->From=C('SP_MAIL_ADDRESS');
	// 设置发件人名字
	$mail->FromName=C('SP_MAIL_SENDER');;
	// 设置邮件标题
	$mail->Subject=$subject;
	// 设置SMTP服务器。
	$mail->Host=C('SP_MAIL_SMTP');
	// 设置SMTP服务器端口。
	$port=C('SP_MAIL_SMTP_PORT');
	$mail->Port=empty($port)?"25":$port;
	// 设置为"需要验证"
	$mail->SMTPAuth=true;
	// 设置用户名和密码。
	$mail->Username=C('SP_MAIL_LOGINNAME');
	$mail->Password=C('SP_MAIL_PASSWORD');
	// 发送邮件。
	if(!$mail->Send()) {
		$mailerror=$mail->ErrorInfo;
		return array("error"=>1,"message"=>$mailerror);
	}else{
		return array("error"=>0,"message"=>"success");
	}
}

/**
 * 转化数据库保存的文件路径，为可以访问的url
 * @param string $file
 * @param boolean $withhost
 * @return string
 */
function sp_get_asset_upload_path($file,$withhost=false){
	if(strpos($file,"http")===0){
		return $file;
	}else if(strpos($file,"/")===0){
		return $file;
	}else{
		$filepath=C("TMPL_PARSE_STRING.__UPLOAD__").$file;
		if($withhost){
			if(strpos($filepath,"http")!==0){
				$http = 'http://';
				$http =is_ssl()?'https://':$http;
				$filepath = $http.$_SERVER['HTTP_HOST'].$filepath;
			}
		}
		return $filepath;
		
	}                    			
                        		
}


function sp_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : C("AUTHCODE"));
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function sp_authencode($string){
	return sp_authcode($string,"ENCODE");
}

function Comments($table,$post_id,$params=array()){
	return  R("Comment/Widget/index",array($table,$post_id,$params));
}
/**
 * 获取评论
 * @param string $tag
 * @param array $where //按照thinkphp where array格式
 */
function sp_get_comments($tag="field:*;limit:0,5;order:createtime desc;",$where=array()){
	$where=array();
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '5';
	$order = !empty($tag['order']) ? $tag['order'] : 'createtime desc';
	
	//根据参数生成查询条件
	$mwhere['status'] = array('eq',1);
	
	if(is_array($where)){
		$where=array_merge($mwhere,$where);
	}else{
		$where=$mwhere;
	}
	
	$comments_model=M("Comments");
	
	$comments=$comments_model->field($field)->where($where)->order($order)->limit($limit)->select();
	return $comments;
}

function sp_file_write($file,$content){
	
	if(sp_is_sae()){
		$s=new SaeStorage();
		$arr=explode('/',ltrim($file,'./'));
		$domain=array_shift($arr);
		$save_path=implode('/',$arr);
		return $s->write($domain,$save_path,$content);
	}else{
		try {
			$fp2 = @fopen( $file , "w" );
			fwrite( $fp2 , $content );
			fclose( $fp2 );
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
}

function sp_file_read($file){
	if(sp_is_sae()){
		$s=new SaeStorage();
		$arr=explode('/',ltrim($file,'./'));
		$domain=array_shift($arr);
		$save_path=implode('/',$arr);
		return $s->read($domain,$save_path);
	}else{
		file_get_contents($file);
	}
}
/*修复缩略图使用网络地址时，会出现的错误。5iymt 2015年7月10日*/
function sp_asset_relative_url($asset_url){
    if(strpos($asset_url,"http")===0){
    	return $asset_url;
	}else{	
	    return str_replace(C("TMPL_PARSE_STRING.__UPLOAD__"), "", $asset_url);
	}
}

function sp_content_page($content,$pagetpl='{first}{prev}{liststart}{list}{listend}{next}{last}'){
	$contents=explode('_ueditor_page_break_tag_',$content);
	$totalsize=count($contents);
	import('Page');
	$pagesize=1;
	$PageParam = C("VAR_PAGE");
	$page = new \Page($totalsize,$pagesize);
	$page->setLinkWraper("li");
	$page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
	$content=$contents[$page->firstRow];
	$data['content']=$content;
	$data['page']=$page->show('default');
	
	return $data;
}


/**
 * 根据广告名称获取广告内容
 * @param string $ad
 * @return 广告内容
 */

function sp_getad($ad){
	$ad_obj= M("Ad");
	$ad=$ad_obj->field("ad_content")->where("ad_name='$ad' and status=1")->find();
	return htmlspecialchars_decode($ad['ad_content']);
}

/**
 * 根据幻灯片标识获取所有幻灯片
 * @param string $slide 幻灯片标识
 * @return array;
 */
function sp_getslide($slide,$limit=5,$order = "listorder ASC"){
    $slide_obj= M("SlideCat");
	$join = "".C('DB_PREFIX').'slide as b on '.C('DB_PREFIX').'slide_cat.cid =b.slide_cid';
    if($order == ''){
		$order = "listorder ASC";
	}
	if ($limit == 0) {
		$limit = 5;
	}
	return $slide_obj->join($join)->where("cat_idname='$slide' and slide_status=1")->order($order)->limit('0,'.$limit)->select();

}

/**
 * 获取所有友情连接
 * @return array
 */
function sp_getlinks(){
	$links_obj= M("Links");
	return  $links_obj->where("link_status=1")->order("listorder ASC")->select();
}

/**
 * 检查用户对某个url,内容的可访问性，用于记录如是否赞过，是否访问过等等;开发者可以自由控制，对于没有必要做的检查可以不做，以减少服务器压力
 * @param number $object 访问对象的id,格式：不带前缀的表名+id;如posts1表示xx_posts表里id为1的记录;如果object为空，表示只检查对某个url访问的合法性
 * @param number $count_limit 访问次数限制,如1，表示只能访问一次
 * @param boolean $ip_limit ip限制,false为不限制，true为限制
 * @param number $expire 距离上次访问的最小时间单位s，0表示不限制，大于0表示最后访问$expire秒后才可以访问
 * @return true 可访问，false不可访问
 */
function sp_check_user_action($object="",$count_limit=1,$ip_limit=false,$expire=0){
	$common_action_log_model=M("CommonActionLog");
	$action=MODULE_NAME."-".CONTROLLER_NAME."-".ACTION_NAME;
	$userid=get_current_userid();
	
	$ip=get_client_ip(0,true);//修复ip获取
	
	$where=array("user"=>$userid,"action"=>$action,"object"=>$object);
	if($ip_limit){
		$where['ip']=$ip;
	}
	
	$find_log=$common_action_log_model->where($where)->find();
	
	$time=time();
	if($find_log){
		$common_action_log_model->where($where)->save(array("count"=>array("exp","count+1"),"last_time"=>$time,"ip"=>$ip));
		if($find_log['count']>=$count_limit){
			return false;
		}
		
		if($expire>0 && ($time-$find_log['last_time'])<$expire){
			return false;
		}
	}else{
		$common_action_log_model->add(array("user"=>$userid,"action"=>$action,"object"=>$object,"count"=>array("exp","count+1"),"last_time"=>$time,"ip"=>$ip));
	}
	
	return true;
}
/**
 * 用于生成收藏内容用的key
 * @param string $table 收藏内容所在表
 * @param int $object_id 收藏内容的id
 */
function sp_get_favorite_key($table,$object_id){
	$auth_code=C("AUTHCODE");
	$string="$auth_code $table $object_id";
	
	return sp_authencode($string);
}


function sp_get_relative_url($url){
	if(strpos($url,"http")===0){
		$url=str_replace(array("https://","http://"), "", $url);
		
		$pos=strpos($url, "/");
		if($pos===false){
			return "";
		}else{
			$url=substr($url, $pos+1);
			$root=preg_replace("/^\//", "", __ROOT__);
			$root=str_replace("/", "\/", $root);
			$url=preg_replace("/^".$root."\//", "", $url);
			return $url;
		}
	}
	return $url;
}

/**
 * 
 * @param string $tag
 * @param array $where
 * @return array
 */

function sp_get_users($tag="field:*;limit:0,8;order:create_time desc;",$where=array()){
	$where=array();
	$tag=sp_param_lable($tag);
	$field = !empty($tag['field']) ? $tag['field'] : '*';
	$limit = !empty($tag['limit']) ? $tag['limit'] : '8';
	$order = !empty($tag['order']) ? $tag['order'] : 'create_time desc';
	
	//根据参数生成查询条件
	$mwhere['user_status'] = array('eq',1);
	$mwhere['user_type'] = array('eq',2);//default user
	
	if(is_array($where)){
		$where=array_merge($mwhere,$where);
	}else{
		$where=$mwhere;
	}
	
	$users_model=M("Users");
	
	$users=$users_model->field($field)->where($where)->order($order)->limit($limit)->select();
	return $users;
}

/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 * @return string
 */
function leuu($url='',$vars='',$suffix=true,$domain=false){
	$routes=sp_get_routes();
	if(empty($routes)){
		return U($url,$vars,$suffix,$domain);
	}else{
		// 解析URL
		$info   =  parse_url($url);
		$url    =  !empty($info['path'])?$info['path']:ACTION_NAME;
		if(isset($info['fragment'])) { // 解析锚点
			$anchor =   $info['fragment'];
			if(false !== strpos($anchor,'?')) { // 解析参数
				list($anchor,$info['query']) = explode('?',$anchor,2);
			}
			if(false !== strpos($anchor,'@')) { // 解析域名
				list($anchor,$host)    =   explode('@',$anchor, 2);
			}
		}elseif(false !== strpos($url,'@')) { // 解析域名
			list($url,$host)    =   explode('@',$info['path'], 2);
		}
		
		// 解析子域名
		//TODO?
		
		// 解析参数
		if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
			parse_str($vars,$vars);
		}elseif(!is_array($vars)){
			$vars = array();
		}
		if(isset($info['query'])) { // 解析地址里面参数 合并到vars
			parse_str($info['query'],$params);
			$vars = array_merge($params,$vars);
		}
		
		$vars_src=$vars;
		
		ksort($vars);
		
		// URL组装
		$depr       =   C('URL_PATHINFO_DEPR');
		$urlCase    =   C('URL_CASE_INSENSITIVE');
		if('/' != $depr) { // 安全替换
			$url    =   str_replace('/',$depr,$url);
		}
		// 解析模块、控制器和操作
		$url        =   trim($url,$depr);
		$path       =   explode($depr,$url);
		$var        =   array();
		$varModule      =   C('VAR_MODULE');
		$varController  =   C('VAR_CONTROLLER');
		$varAction      =   C('VAR_ACTION');
		$var[$varAction]       =   !empty($path)?array_pop($path):ACTION_NAME;
		$var[$varController]   =   !empty($path)?array_pop($path):CONTROLLER_NAME;
		if($maps = C('URL_ACTION_MAP')) {
			if(isset($maps[strtolower($var[$varController])])) {
				$maps    =   $maps[strtolower($var[$varController])];
				if($action = array_search(strtolower($var[$varAction]),$maps)){
					$var[$varAction] = $action;
				}
			}
		}
		if($maps = C('URL_CONTROLLER_MAP')) {
			if($controller = array_search(strtolower($var[$varController]),$maps)){
				$var[$varController] = $controller;
			}
		}
		if($urlCase) {
			$var[$varController]   =   parse_name($var[$varController]);
		}
		$module =   '';
		
		if(!empty($path)) {
			$var[$varModule]    =   array_pop($path);
		}else{
			if(C('MULTI_MODULE')) {
				if(MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST')){
					$var[$varModule]=   MODULE_NAME;
				}
			}
		}
		if($maps = C('URL_MODULE_MAP')) {
			if($_module = array_search(strtolower($var[$varModule]),$maps)){
				$var[$varModule] = $_module;
			}
		}
		if(isset($var[$varModule])){
			$module =   $var[$varModule];
		}
		
		if(C('URL_MODEL') == 0) { // 普通模式URL转换
			$url        =   __APP__.'?'.http_build_query(array_reverse($var));
			
			if($urlCase){
				$url    =   strtolower($url);
			}
			if(!empty($vars)) {
				$vars   =   http_build_query($vars);
				$url   .=   '&'.$vars;
			}
		}else{ // PATHINFO模式或者兼容URL模式
			
			if(empty($var[C('VAR_MODULE')])){
				$var[C('VAR_MODULE')]=MODULE_NAME;
			}
				
			$module_controller_action=strtolower(implode($depr,array_reverse($var)));
			
			$has_route=false;
			$original_url=$module_controller_action.(empty($vars)?"":"?").http_build_query($vars);
			
			if(isset($routes['static'][$original_url])){
			    $has_route=true;
			    $url=__APP__."/".$routes['static'][$original_url];
			}else{
			    if(isset($routes['dynamic'][$module_controller_action])){
			        $urlrules=$routes['dynamic'][$module_controller_action];
			    
			        $empty_query_urlrule=array();
			    
			        foreach ($urlrules as $ur){
			            $intersect=array_intersect_assoc($ur['query'], $vars);
			            if($intersect){
			                $vars=array_diff_key($vars,$ur['query']);
			                $url= $ur['url'];
			                $has_route=true;
			                break;
			            }
			            if(empty($empty_query_urlrule) && empty($ur['query'])){
			                $empty_query_urlrule=$ur;
			            }
			        }
			    
			        if(!empty($empty_query_urlrule)){
			            $has_route=true;
			            $url=$empty_query_urlrule['url'];
			        }
			        
			        $new_vars=array_reverse($vars);
			        foreach ($new_vars as $key =>$value){
			            if(strpos($url, ":$key")!==false){
			                $url=str_replace(":$key", $value, $url);
			                unset($vars[$key]);
			            }
			        }
			        $url=str_replace(array("\d","$"), "", $url);
			    
			        if($has_route){
			            if(!empty($vars)) { // 添加参数
			                foreach ($vars as $var => $val){
			                    if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
			                }
			            }
			            $url =__APP__."/".$url ;
			        }
			    }
			}
			
			$url=str_replace(array("^","$"), "", $url);
			
			if(!$has_route){
				$module =   defined('BIND_MODULE') ? '' : $module;
				$url    =   __APP__.'/'.implode($depr,array_reverse($var));
					
				if($urlCase){
					$url    =   strtolower($url);
				}
					
				if(!empty($vars)) { // 添加参数
					foreach ($vars as $var => $val){
						if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
					}
				}
			}
			
			
			if($suffix) {
				$suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
				if($pos = strpos($suffix, '|')){
					$suffix = substr($suffix, 0, $pos);
				}
				if($suffix && '/' != substr($url,-1)){
					$url  .=  '.'.ltrim($suffix,'.');
				}
			}
		}
		
		if(isset($anchor)){
			$url  .= '#'.$anchor;
		}
		if($domain) {
			$url   =  (is_ssl()?'https://':'http://').$domain.$url;
		}
		
		return $url;
	}
}

/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 * @return string
 */
function UU($url='',$vars='',$suffix=true,$domain=false){
	return leuu($url,$vars,$suffix,$domain);
}


function sp_get_routes($refresh=false){
	$routes=F("routes");
	
	if( (!empty($routes)||is_array($routes)) && !$refresh){
		return $routes;
	}
	$routes=M("Route")->where("status=1")->order("listorder asc")->select();
	$all_routes=array();
	$cache_routes=array();
	foreach ($routes as $er){
		$full_url=htmlspecialchars_decode($er['full_url']);
			
		// 解析URL
		$info   =  parse_url($full_url);
			
		$path       =   explode("/",$info['path']);
		if(count($path)!=3){//必须是完整 url
			continue;
		}
			
		$module=strtolower($path[0]);
			
		// 解析参数
		$vars = array();
		if(isset($info['query'])) { // 解析地址里面参数 合并到vars
			parse_str($info['query'],$params);
			$vars = array_merge($params,$vars);
		}
			
		$vars_src=$vars;
			
		ksort($vars);
			
		$path=$info['path'];
			
		$full_url=$path.(empty($vars)?"":"?").http_build_query($vars);
			
		$url=$er['url'];
		
		if(strpos($url,':')===false){
		    $cache_routes['static'][$full_url]=$url;
		}else{
		    $cache_routes['dynamic'][$path][]=array("query"=>$vars,"url"=>$url);
		}
			
		$all_routes[$url]=$full_url;
			
	}
	F("routes",$cache_routes);
	$route_dir=SITE_PATH."/data/conf/";
	if(!file_exists($route_dir)){
		mkdir($route_dir);
	}
		
	$route_file=$route_dir."route.php";
		
	file_put_contents($route_file, "<?php\treturn " . stripslashes(var_export($all_routes, true)) . ";");
	
	return $cache_routes;
	
	
}

/**
 * 判断是否为手机访问
 * @return  boolean
 */
function sp_is_mobile() {
	static $sp_is_mobile;

	if ( isset($sp_is_mobile) )
		return $sp_is_mobile;

	if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
		$sp_is_mobile = false;
	} elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false ) {
		$sp_is_mobile = true;
	} else {
		$sp_is_mobile = false;
	}

	return $sp_is_mobile;
}

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook,$params=array()){
	tag($hook,$params);
}

/**
 * 处理插件钩子,只执行一个
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook_one($hook,$params=array()){
    return \Think\Hook::listen_one($hook,$params);
}


/**
 * 获取插件类的类名
 * @param strng $name 插件名
 */
function sp_get_plugin_class($name){
	$class = "plugins\\{$name}\\{$name}Plugin";
	return $class;
}

/**
 * 获取插件类的配置
 * @param string $name 插件名
 * @return array
 */
function sp_get_plugin_config($name){
	$class = sp_get_plugin_class($name);
	if(class_exists($class)) {
		$plugin = new $class();
		return $plugin->getConfig();
	}else {
		return array();
	}
}

/**
 * 替代scan_dir的方法
 * @param string $pattern 检索模式 搜索模式 *.txt,*.doc; (同glog方法)
 * @param int $flags
 */
function sp_scan_dir($pattern,$flags=null){
	$files = array_map('basename',glob($pattern, $flags));
	return $files;
}

/**
 * 获取所有钩子，包括系统，应用，模板
 */
function sp_get_hooks($refresh=false){
	if(!$refresh){
		$return_hooks = F('all_hooks');
		if(!empty($return_hooks)){
			return $return_hooks;
		}
	}
	
	$return_hooks=array();
	$system_hooks=array(
		"url_dispatch","app_init","app_begin","app_end",
		"action_begin","action_end","module_check","path_info",
		"template_filter","view_begin","view_end","view_parse",
		"view_filter","body_start","footer","footer_end","sider","comment",'admin_home'
	);
	
	$app_hooks=array();
	
	$apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
	foreach ($apps as $app){
		$hooks_file=SPAPP.$app."/hooks.php";
		if(is_file($hooks_file)){
			$hooks=include $hooks_file;
			$app_hooks=is_array($hooks)?array_merge($app_hooks,$hooks):$app_hooks;
		}
	}
	
	$tpl_hooks=array();
	
	$tpls=sp_scan_dir("themes/*",GLOB_ONLYDIR);
	
	foreach ($tpls as $tpl){
		$hooks_file= sp_add_template_file_suffix("themes/$tpl/hooks");
		if(file_exists_case($hooks_file)){
			$hooks=file_get_contents($hooks_file);
			$hooks=preg_replace("/[^0-9A-Za-z_-]/u", ",", $hooks);
			$hooks=explode(",", $hooks);
			$hooks=array_filter($hooks);
			$tpl_hooks=is_array($hooks)?array_merge($tpl_hooks,$hooks):$tpl_hooks;
		}
	}
	
	$return_hooks=array_merge($system_hooks,$app_hooks,$tpl_hooks);
	
	$return_hooks=array_unique($return_hooks);
	
	F('all_hooks',$return_hooks);
	return $return_hooks;
	
}


/**
 * 生成访问插件的url
 * @param string $url url 格式：插件名://控制器名/方法
 * @param array $param 参数
 */
function sp_plugin_url($url, $param = array(),$domain=false){
	$url        = parse_url($url);
	$case       = C('URL_CASE_INSENSITIVE');
	$plugin     = $case ? parse_name($url['scheme']) : $url['scheme'];
	$controller = $case ? parse_name($url['host']) : $url['host'];
	$action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

	/* 解析URL带的参数 */
	if(isset($url['query'])){
		parse_str($url['query'], $query);
		$param = array_merge($query, $param);
	}

	/* 基础参数 */
	$params = array(
			'_plugin'     => $plugin,
			'_controller' => $controller,
			'_action'     => $action,
	);
	$params = array_merge($params, $param); //添加额外参数

	return U('api/plugin/execute', $params,true,$domain);
}

/**
 * 检查权限
 * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
 * @param uid  int           认证用户的id
 * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
 * @return boolean           通过验证返回true;失败返回false
 */
function sp_auth_check($uid,$name=null,$relation='or'){
	if(empty($uid)){
		return false;
	}

	$iauth_obj=new \Common\Lib\iAuth();
	if(empty($name)){
		$name=strtolower(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME);
	}
	return $iauth_obj->check($uid, $name, $relation);
}

/**
 * 兼容之前版本的ajax的转化方法，如果你之前用参数只有两个可以不用这个转化，如有两个以上的参数请升级一下
 * @param array $data
 * @param string $info
 * @param int $status
 */
function sp_ajax_return($data,$info,$status){
	$return = array();
	$return['data'] = $data;
	$return['info'] = $info;
	$return['status'] = $status;
	$data = $return;
	
	return $data;
}

/**
 * 判断是否为SAE
 */
function sp_is_sae(){
	if(defined('APP_MODE') && APP_MODE=='sae'){
		return true;
	}else{
		return false;
	}
}


function sp_alpha_id($in, $to_num = false, $pad_up = 4, $passKey = null){
	$index = "aBcDeFgHiJkLmNoPqRsTuVwXyZAbCdEfGhIjKlMnOpQrStUvWxYz0123456789";
	if ($passKey !== null) {
		// Although this function's purpose is to just make the
		// ID short - and not so much secure,
		// with this patch by Simon Franz (http://blog.snaky.org/)
		// you can optionally supply a password to make it harder
		// to calculate the corresponding numeric ID

		for ($n = 0; $n<strlen($index); $n++) $i[] = substr( $index,$n ,1);

		$passhash = hash('sha256',$passKey);
		$passhash = (strlen($passhash) < strlen($index)) ? hash('sha512',$passKey) : $passhash;

		for ($n=0; $n < strlen($index); $n++) $p[] =  substr($passhash, $n ,1);

		array_multisort($p,  SORT_DESC, $i);
		$index = implode($i);
	}

	$base  = strlen($index);

	if ($to_num) {
		// Digital number  <<--  alphabet letter code
		$in  = strrev($in);
		$out = 0;
		$len = strlen($in) - 1;
		for ($t = 0; $t <= $len; $t++) {
			$bcpow = pow($base, $len - $t);
			$out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
		}

		if (is_numeric($pad_up)) {
			$pad_up--;
			if ($pad_up > 0) $out -= pow($base, $pad_up);
		}
		$out = sprintf('%F', $out);
		$out = substr($out, 0, strpos($out, '.'));
	}else{
		// Digital number  -->>  alphabet letter code
		if (is_numeric($pad_up)) {
			$pad_up--;
			if ($pad_up > 0) $in += pow($base, $pad_up);
		}

		$out = "";
		for ($t = floor(log($in, $base)); $t >= 0; $t--) {
			$bcp = pow($base, $t);
			$a   = floor($in / $bcp) % $base;
			$out = $out . substr($index, $a, 1);
			$in  = $in - ($a * $bcp);
		}
		$out = strrev($out); // reverse
	}

	return $out;
}

/**
 * 验证码检查，验证完后销毁验证码增加安全性 ,<br>返回true验证码正确，false验证码错误
 * @return boolean <br>true：验证码正确，false：验证码错误
 */
function sp_check_verify_code(){
	$verify = new \Think\Verify();
	return $verify->check($_REQUEST['verify'], "");
}

/**
 * 手机验证码检查，验证完后销毁验证码增加安全性 ,<br>返回true验证码正确，false验证码错误
 * @return boolean <br>true：手机验证码正确，false：手机验证码错误
 */
function sp_check_mobile_verify_code(){
    return true;
}


/**
 * 执行SQL文件  sae 环境下file_get_contents() 函数好像有间歇性bug。
 * @param string $sql_path sql文件路径
 * @author 5iymt <1145769693@qq.com>
 */
function sp_execute_sql_file($sql_path) {
    	
	$context = stream_context_create ( array (
			'http' => array (
					'timeout' => 30 
			) 
	) ) ;// 超时时间，单位为秒
	
	// 读取SQL文件
	$sql = file_get_contents ( $sql_path, 0, $context );
	$sql = str_replace ( "\r", "\n", $sql );
	$sql = explode ( ";\n", $sql );
	
	// 替换表前缀
	$orginal = 'sp_';
	$prefix = C ( 'DB_PREFIX' );
	$sql = str_replace ( "{$orginal}", "{$prefix}", $sql );
	
	// 开始安装
	foreach ( $sql as $value ) {
		$value = trim ( $value );
		if (empty ( $value )){
			continue;
		}
		$res = M ()->execute ( $value );
	}
}

/**
 * 插件R方法扩展 建立多插件之间的互相调用。提供无限可能
 * 使用方式 get_plugns_return('Chat://Index/index',array())
 * @param string $url 调用地址
 * @param array $params 调用参数
 * @author 5iymt <1145769693@qq.com>
 */
function sp_get_plugins_return($url, $params = array()){
	$url        = parse_url($url);
	$case       = C('URL_CASE_INSENSITIVE');
	$plugin     = $case ? parse_name($url['scheme']) : $url['scheme'];
	$controller = $case ? parse_name($url['host']) : $url['host'];
	$action     = trim($case ? strtolower($url['path']) : $url['path'], '/');
	
	/* 解析URL带的参数 */
	if(isset($url['query'])){
		parse_str($url['query'], $query);
		$params = array_merge($query, $params);
	}
	return R("plugins://{$plugin}/{$controller}/{$action}", $params);
}

/**
 * 给没有后缀的模板文件，添加后缀名
 * @param string $filename_nosuffix
 */
function sp_add_template_file_suffix($filename_nosuffix){
    
    
    
    if(file_exists_case($filename_nosuffix.C('TMPL_TEMPLATE_SUFFIX'))){
        $filename_nosuffix = $filename_nosuffix.C('TMPL_TEMPLATE_SUFFIX');
    }else if(file_exists_case($filename_nosuffix.".php")){
        $filename_nosuffix = $filename_nosuffix.".php";
    }else{
        $filename_nosuffix = $filename_nosuffix.C('TMPL_TEMPLATE_SUFFIX');
    }
    
    return $filename_nosuffix;
}

/**
 * 获取当前主题名
 * @param string $default_theme 指定的默认模板名
 * @return string
 */
function sp_get_current_theme($default_theme=''){
    $theme      =    C('SP_DEFAULT_THEME');
    if(C('TMPL_DETECT_THEME')){// 自动侦测模板主题
        $t = C('VAR_TEMPLATE');
        if (isset($_GET[$t])){
            $theme = $_GET[$t];
        }elseif(cookie('think_template')){
            $theme = cookie('think_template');
        }
    }
    $theme=empty($default_theme)?$theme:$default_theme;
    
    return $theme;
}

/**
 * 判断模板文件是否存在，区分大小写
 * @param string $file 模板文件路径，相对于当前模板根目录，不带模板后缀名
 */
function sp_template_file_exists($file){
    $theme= sp_get_current_theme();
    $filepath=C("SP_TMPL_PATH").$theme."/".$file;
    $tplpath = sp_add_template_file_suffix($filepath);
    
    if(file_exists_case($tplpath)){
        return true;
    }else{
        return false;
    }
    
}

/**
*根据菜单id获得菜单的详细信息，可以整合进获取菜单数据的方法(_sp_get_menu_datas)中。
*@param num $id  菜单id，每个菜单id
* @author 5iymt <1145769693@qq.com>
*/
function sp_get_menu_info($id,$navdata=false){
    if(empty($id)&&$navdata){
		//若菜单id不存在，且菜单数据存在。
		$nav=$navdata;
	}else{
		$nav_obj= M("Nav");
		$id= intval($id);
		$nav= $nav_obj->where("id=$id")->find();//菜单数据
	}

	$href=htmlspecialchars_decode($nav['href']);
	$hrefold=$href;

	if(strpos($hrefold,"{")){//序列 化的数据
		$href=unserialize(stripslashes($nav['href']));
		$default_app=strtolower(C("DEFAULT_MODULE"));
		$href=strtolower(leuu($href['action'],$href['param']));
		$g=C("VAR_MODULE");
		$href=preg_replace("/\/$default_app\//", "/",$href);
		$href=preg_replace("/$g=$default_app&/", "",$href);
	}else{
		if($hrefold=="home"){
			$href=__ROOT__."/";
		}else{
			$href=$hrefold;
		}
	}
	$nav['href']=$href;
	return $nav;
}

/**
 * 判断当前的语言包，并返回语言包名
 */
function sp_check_lang(){
    $langSet = C('DEFAULT_LANG');
    if (C('LANG_SWITCH_ON',null,false)){
        
        $varLang =  C('VAR_LANGUAGE',null,'l');
        $langList = C('LANG_LIST',null,'zh-cn');
        // 启用了语言包功能
        // 根据是否启用自动侦测设置获取语言选择
        if (C('LANG_AUTO_DETECT',null,true)){
            if(isset($_GET[$varLang])){
                $langSet = $_GET[$varLang];// url中设置了语言变量
                cookie('think_language',$langSet,3600);
            }elseif(cookie('think_language')){// 获取上次用户的选择
                $langSet = cookie('think_language');
            }elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){// 自动侦测浏览器语言
                preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
                $langSet = $matches[1];
                cookie('think_language',$langSet,3600);
            }
            if(false === stripos($langList,$langSet)) { // 非法语言参数
                $langSet = C('DEFAULT_LANG');
            }
        }
    }
    
    return strtolower($langSet);
    
}

/**
* 删除图片物理路径
* @param array $imglist 图片路径
* @return bool 是否删除图片
* @author 高钦 <395936482@qq.com>
*/
function sp_delete_physics_img($imglist){
    $file_path = C("UPLOADPATH");
    
    if ($imglist) {
        if ($imglist['thumb']) {
            $file_path = $file_path . $imglist['thumb'];
            if (file_exists($file_path)) {
                $result = @unlink($file_path);
                if ($result == false) {
                    $res = TRUE;
                } else {
                    $res = FALSE;
                }
            } else {
                $res = FALSE;
            }
        }
        
        if ($imglist['photo']) {
            foreach ($imglist['photo'] as $key => $value) {
                $file_path = C("UPLOADPATH");
                $file_path_url = $file_path . $value['url'];
                if (file_exists($file_path_url)) {
                    $result = @unlink($file_path_url);
                    if ($result == false) {
                        $res = TRUE;
                    } else {
                        $res = FALSE;
                    }
                } else {
                    $res = FALSE;
                }
            }
        }
    } else {
        $res = FALSE;
    }
    
    return $res;
}

/* 前台 */

	/* redis链接 */
	function connectionRedis(){
		$REDIS_HOST= C('REDIS_HOST');
		$REDIS_AUTH= C('REDIS_AUTH');
		$REDIS_PORT= C('REDIS_PORT');
		$REDIS_DBINDEX=C('REDIS_DBINDEX');
		$redis = new \Redis();
		$redis -> pconnect($REDIS_HOST,$REDIS_PORT);
		$redis -> auth($REDIS_AUTH);
		$redis ->select($REDIS_DBINDEX);

		return $redis;
	}

	function  connectRedis(){
        $REDIS_HOST= C('REDIS_HOST');
        $REDIS_AUTH= C('REDIS_AUTH');
        $REDIS_PORT= C('REDIS_PORT');
        $REDIS_DBINDEX=C('REDIS_DBINDEX');
        $redis = new \Redis();
        $redis -> connect($REDIS_HOST,$REDIS_PORT);
        $redis -> auth($REDIS_AUTH);
        $redis ->select($REDIS_DBINDEX);

        return $redis;
    }
	/* 设置缓存 */
	function setcache($key,$info){
		$config=getConfigPri();
		if($config['cache_switch']!=1){
			return 1;
		}
		$redis=connectionRedis();
		$redis->set($key,json_encode($info));
		$redis->setTimeout($key, $config['cache_time']);
		
		return 1;
	}	
	/* 设置缓存 可自定义时间*/
	function setcaches($key,$info,$time=0){

		$redis=connectionRedis();
		$redis->set($key,json_encode($info));
        if($time > 0){
            $redis->setTimeout($key, $time);
        }
		
		
		return 1;
	}
	/* 获取缓存 */
	function getcache($key){
		$config=getConfigPri();

		$redis=connectionRedis();
		$isexist=$redis->Get($key);
		if($config['cache_switch']!=1){
			$isexist=false;
		}
		
		return json_decode($isexist,true);
	}		
	/* 获取缓存 不判断后台设置 */
	function getcaches($key){
		$redis=connectionRedis();
		$isexist=$redis->Get($key);

		
		return json_decode($isexist,true);
	}
	/* 删除缓存 */
	function delcache($key){
		$redis=connectionRedis();
		$isexist=$redis->delete($key);

		
		return 1;
	}

    /**
     * 删除所有与特定格式匹配的key
     * @param $key
     * @return int
     */
	function delPatternCacheKeys($key){
        $redis=connectionRedis();
        $keys=$redis->keys($key);
        $redis->delete($keys);
        return 1;
    }
	
	/* 去除NULL 判断空处理 主要针对字符串类型*/
	function checkNull($checkstr){
		$checkstr=urldecode($checkstr);
		$checkstr=htmlspecialchars($checkstr);
		$checkstr=trim($checkstr);

		if( strstr($checkstr,'null') || (!$checkstr && $checkstr!=0 ) ){
			$str='';
		}else{
			$str=$checkstr;
		}
		return $str;	
	}
	
	/* 去除emoji表情 */
	function filterEmoji($str){
		$str = preg_replace_callback(
			'/./u',
			function (array $match) {
				return strlen($match[0]) >= 4 ? '' : $match[0];
			},
			$str);
		return $str;
	}



	/* 获取公共配置 */
//	function getConfigPub() {
//		$key='getConfigPub';
//		$config=getcaches($key);
//		if(!$config){
//			$config= M("config")->where("id='1'")->find();
//			setcaches($key,$config);
//		}
//
//        if(is_array($config['live_time_coin'])){
//
//        }else if($config['live_time_coin']){
//            $config['live_time_coin']=preg_split('/,|，/',$config['live_time_coin']);
//        }else{
//            $config['live_time_coin']=array();
//        }
//
//        if(is_array($config['login_type'])){
//
//        }else if($config['login_type']){
//            $config['login_type']=preg_split('/,|，/',$config['login_type']);
//        }else{
//            $config['login_type']=array();
//        }
//
//        if(is_array($config['share_type'])){
//
//        }else if($config['share_type']){
//            $config['share_type']=preg_split('/,|，/',$config['share_type']);
//        }else{
//            $config['share_type']=array();
//        }
//
//        if(is_array($config['live_type'])){
//
//        }else if($config['live_type']){
//            $live_type=preg_split('/,|，/',$config['live_type']);
//            foreach($live_type as $k=>$v){
//                $live_type[$k]=preg_split('/;|；/',$v);
//            }
//            $config['live_type']=$live_type;
//        }else{
//            $config['live_type']=array();
//        }
//
//		return 	$config;
//	}
	
	/* 获取私密配置 */
//	function getConfigPri() {
//		$key='getConfigPri';
//		$config=getcaches($key);
//		if(!$config){
//			$config= M("config_private")->where("id='1'")->find();
////			$config= DI()->notorm->config_private
////					->select('*')
////					->where(" id ='1'")
////					->fetchOne();
//
//			setcaches($key,$config);
//		}
//        if(is_array($config['game_switch'])){
//
//        }else if($config['game_switch']){
//            $config['game_switch']=preg_split('/,|，/',$config['game_switch']);
//        }else{
//            $config['game_switch']=array();
//        }
//		return 	$config;
//	}

    /* 获取平台公共配置 */
    function getConfigPub($tenantId = null) {
        $tenantId = $tenantId ? $tenantId : getTenantId();
        $key=$tenantId.'_'.'getPlatformConfig';
        $config=getcaches($key);
        if(!$config){
            /**
             * 基于平台公有设置和私有设置字段唯一的前提,此处把两个表的配置合起来
             */
            $config= M("platform_config")->where('tenant_id="'.$tenantId.'"')->find();
            $tenantConfig=M("tenant_config")->where('tenant_id="'.$tenantId.'"')->find();

            $tenantConfig = is_array($tenantConfig) ? $tenantConfig : array();
            $config=array_merge($config,$tenantConfig);

            setcaches($key,$config);
        }

        if(is_array($config['live_time_coin'])){

        }else if($config['live_time_coin']){
            $config['live_time_coin']=preg_split('/,|，/',$config['live_time_coin']);
        }else{
            $config['live_time_coin']=array();
        }

        if(is_array($config['live_type'])){

        }else if($config['live_type']){
            $live_type=preg_split('/,|，/',$config['live_type']);
            foreach($live_type as $k=>$v){
                $live_type[$k]=preg_split('/;|；/',$v);
            }
            $config['live_type']=$live_type;
        }else{
            $config['live_type']=array();
        }

        if(is_array($config['login_type'])){

        }else if($config['login_type']){
            $config['login_type']=preg_split('/,|，/',$config['login_type']);
        }else{
            $config['login_type']=array();
        }

        if(is_array($config['share_type'])){

        }else if($config['share_type']){
            $config['share_type']=preg_split('/,|，/',$config['share_type']);
        }else{
            $config['share_type']=array();
        }

        if(is_array($config['game_switch'])){

        }else if($config['game_switch']){
            $config['game_switch']=preg_split('/,|，/',$config['game_switch']);
        }else{
            $config['game_switch']=array();
        }

        $config['cash_account_type'] = explode(',', $config['cash_account_type']);

        return 	$config;
    }

/* 获取租户私密配置 */
    function getConfigPri($tenantId = null) {
        $tenantId = $tenantId ? $tenantId : getTenantIds();
        $key=$tenantId.'_'.'getTenantConfig';
        $config=getcaches($key);
        if(!$config){
            $platformConfig=M("platform_config")->where('tenant_id="'.$tenantId.'"')->find();

            $config= M("tenant_config")->where('tenant_id="'.$tenantId.'"')->find();
            if(empty($config)){
                $config['defult_value'] = 'test';  //如果是超级用户，没有对应的租户设置，默认取个值
            }

            $config=array_merge($platformConfig,$config);

            setcaches($key,$config);
        }

        if(is_array($config['live_type'])){

        }else if($config['live_type']){
            $live_type=preg_split('/,|，/',$config['live_type']);
            foreach($live_type as $k=>$v){
                $live_type[$k]=preg_split('/;|；/',$v);
            }
            $config['live_type']=$live_type;
        }else{
            $config['live_type']=array();
        }

        if(is_array($config['login_type'])){

        }else if($config['login_type']){
            $config['login_type']=preg_split('/,|，/',$config['login_type']);
        }else{
            $config['login_type']=array();
        }

        if(is_array($config['share_type'])){

        }else if($config['share_type']){
            $config['share_type']=preg_split('/,|，/',$config['share_type']);
        }else{
            $config['share_type']=array();
        }

        if(is_array($config['game_switch'])){

        }else if($config['game_switch']){
            $config['game_switch']=preg_split('/,|，/',$config['game_switch']);
        }else{
            $config['game_switch']=array();
        }

        $config['cash_account_type'] = explode(',', $config['cash_account_type']);

        return 	$config;
    }



	/**
	 * 返回带协议的域名
	 */
	function get_host(){
        $config = getTenantInfo(getTenantIds());
		return $config['site'];
	}
    /**
     * 根据登录的账号，判断是 独立还是 集成
     */
    function whichTenat(){
        $config = getTenantInfo(getTenantIds());
        return $config['site_id'];
    }

/**
	 * 转化数据库保存的文件路径，为可以访问的url
	 */
	function get_upload_path($file){
        if($file==''){
            return $file;
        }
		if(strpos($file,"http")===0){
			return $file;
		}else if(strpos($file,"/")===0){
			$filepath= get_host().$file;
			return $filepath;
		}else{
			return $file;
		}
	}	
	/* 获取等级 */
	function getLevelList($tenant_id,$levelid=null){
        $key='level_'.$tenant_id;
		$level=getcaches($key);
		if(!$level){
			$level= M("experlevel")->where(['tenant_id'=>$tenant_id])->order("experience asc")->select();
            
            foreach($level as $k=>$v){
                $v['thumb']=get_upload_path($v['thumb']);
                if($v['colour']){
                    $v['colour']='#'.$v['colour'];
                }else{
                    $v['colour']='#ffdd00';
                }
                $level[$k]=$v;
            }
            
			setcaches($key,$level);			 
		}
        if($levelid && count($level) > 0){
            $level_list = array_column($level,null,'levelid');
            return isset($level_list[$levelid]) ? $level_list[$levelid] : [];
        }
        
        return $level;
    }
	function getLevel($tenant_id,$experience){
		$levelid=1;
		
        $level=getLevelList($tenant_id);

		foreach($level as $k=>$v){
            if($experience >= $v['experience']){
                $levelid=$v['levelid'];
            }
		}

		return $levelid;
	}
	/* 主播等级 */
	function getLevelAnchorList($tenant_id){
		$key='levelanchor_'.$tenant_id;
		$level=getcaches($key);
		if(!$level){
			$level= M("experlevel_anchor")->where(['tenant_id'=>$tenant_id])->order("experience asc")->select();
            foreach($level as $k=>$v){
                $v['thumb']=get_upload_path($v['thumb']);
                $v['thumb_mark']=get_upload_path($v['thumb_mark']);
            }
			setcaches($key,$level);			 
		}
        
        return $level;
    }
	function getLevelAnchor($tenant_id,$experience){
		$levelid=1;
        $level=getLevelAnchorList($tenant_id);
        
		foreach($level as $k=>$v){
            if($experience >= $v['experience']){
                $levelid=$v['levelid'];
            }
		}

		return $levelid;
	}

	/* 判断是否关注 */
	function isAttention($uid,$touid) {
		$id=M("users_attention")->where("uid='$uid' and touid='$touid'")->find();
		if($id){
			return  1;
		}else{
			return  0;
		}			 	
	}
	/*判断是否拉黑*/ 
	function isBlack($uid,$touid){
		$isexist=M("users_black")->where("uid=".$uid." and touid=".$touid)->find();
		if($isexist){
			return 1;
		}else{
			return 0;					
		}
	}
	/* 关注人数 */
	function getFollownums($uid) 
	{
		return M("users_attention")->where("uid='{$uid}' ")->count();
	}
	/* 粉丝人数 */
	function getFansnums($uid) 
	{
		return M("users_attention")->where(" touid='{$uid}'")->count();
	}

    /* 租户用户基本信息,查询当前租户用户或公共主播 */
    function getTenantUserInfo($uid,$tenantId) {
    /**
     * TODO 修改为查询缓存
     */
    $info= M("users")->field(UsersCache::getInstance()->fields)
            ->where("id='{$uid}' and (tenant_id='$tenantId' or isshare='1' )")->find();
    if($info){
        $info['avatar']=get_upload_path($info['avatar']);
        $info['avatar_thumb']=get_upload_path($info['avatar_thumb']);
        $info['level']=getLevel($info['tenant_id'],$info['consumption']);
        $info['level_anchor']=getLevelAnchor($info['tenant_id'],$info['votestotal']);

        $info['vip']=getUserVip($uid);
        $info['liang']=getUserLiang($uid);
    }else{
        //返回空
    }

    return 	$info;
    }


/* 用户基本信息 */
    function getUserInfo($uid) {

        $info = UsersCache::getInstance()->getUserInfoCache($uid);;

		if($info){
            $info['is_set_payment_password'] = $info['payment_password'] ? 1 : 0;
            unset($info['payment_password']);
			$info['level']=getLevel($info['tenant_id'],$info['consumption']);
			$info['level_anchor']=getLevelAnchor($info['tenant_id'],$info['votestotal']);
//
//			$info['vip']=getUserVip($uid);
			$info['liang']=getUserLiang($uid);
		}else{
            $info['id']=$uid;
            $info['user_nicename']='用户不存在';
            $info['avatar']=get_upload_path('/default.jpg');
            $info['avatar_thumb']=get_upload_path('/default_thumb.jpg');
            $info['coin']='0';
            $info['sex']='0';
            $info['signature']='';
            $info['consumption']='0';
            $info['votestotal']='0';
            $info['province']='';
            $info['city']='';
            $info['birthday']='';
            $info['issuper']='0';
            $info['level']='1';
            $info['level_anchor']='1';
        }
        $configInfo = getConfigPub();

        /**
         * 获取 不可转可提现的不可提余额
         */
        $redis = connectRedis();
        $length = $redis->lLen($uid.'_reward_time');
        $redisList = $redis->lRange($uid.'_reward_time',0,$length);
        $totalAmount = 0;
        foreach ($redisList as $value){
            $amount = $redis->get($uid.'_'.$value.'_reward') ;
            if ($amount > 0){
                $totalAmount = bcadd($totalAmount,$amount,2);
            }else{
                $redis->lRem($uid.'_reward_time',$value,0);
            }
        }
        $info['can_be_withdrawn']  =  strval (bcsub ($info['nowithdrawable_coin'] , $totalAmount,2));
        $info['is_open_seeking_slice'] =  $configInfo['is_open_seeking_slice'];// 贴吧是否开启
        $info['posting_strategy'] =  $configInfo['posting_strategy'];// 贴吧发帖策略
        $info['comment_strategy'] =  $configInfo['comment_strategy'];// 贴吧发帖策略
        $info['seeking_slice_strategy'] =  $configInfo['seeking_slice_strategy'];// 贴吧发帖策略
        $info['push_strategy'] =  $configInfo['push_strategy'];// 贴吧发帖策略
        $info['seeking_slice_bonus_min'] =  $configInfo['seeking_slice_bonus_min'];// 贴吧发帖策略
        $info['seeking_slice_bonus_max'] =  $configInfo['seeking_slice_bonus_max'];// 贴吧发帖策略
		return 	$info;		
    }	 
	/*获取收到礼物数量(tsd) 以及送出的礼物数量（tsc） */
	function getgif($uid)
	{
		
    $live=M("users_coinrecord");
		$count=$live->query('select sum(case when touid='.$uid.' then 1 else 0 end) as tsd,sum(case when uid='.$uid.' then 1 else 0 end) as tsc from cmf_users_coinrecord');
		return 	$count;		
	}
	/* 用户信息 含有私密信息 */
   function getUserPrivateInfo($uid) {
        $info= M("users")->field('id,user_login,user_nicename,avatar,avatar_thumb,sex,signature,consumption,votestotal,province,city,coin,votes,token,birthday,issuper')->where("id='{$uid}'")->find();
		if($info){
			$info['lighttime']="0";
			$info['light']=0;
			$info['level']=getLevel($info['tenant_id'],$info['consumption']);
			$info['level_anchor']=getLevelAnchor($info['tenant_id'],$info['votestotal']);
			$info['avatar']=get_upload_path($info['avatar']);
			$info['avatar_thumb']=get_upload_path($info['avatar_thumb']);
			
			$info['vip']=getUserVip($uid);
			$info['liang']=getUserLiang($uid);
		}
		return 	$info;		
    }			
		
		/* 用户信息 含有私密信息 */
    function getUserToken($uid) {
		$info= M("users")->field('token')->where("id='{$uid}'")->find();
		return 	$info['token'];		
    }				
	/* 房间管理员 */
	function getIsAdmin($uid,$showid){
		if($uid==$showid){		
			return 50;
		}
		$showInfo= getUserInfo($showid);
		$isuper=isSuper($uid,$showInfo['tenant_id']);
		if($isuper){
			return 60;
		}
		$id=M("users_livemanager")->where("uid = '$uid' and liveuid = '$showid'")->find();

		if($id)	{
			return 40;					
		}
		return 30;		
	}
	/*判断token是否过期*/
	function checkToken($uid,$token)
	{
		if(!$uid || !$token){
            session('uid',null);		
            session('token',null);
            session('user',null);
            cookie('uid',null);
            cookie('token',null);
			return 700;	
		}
		$userinfo=getcaches("token_".$uid);
		if(!$userinfo){
			$userinfo=M("users")->field('token,expiretime,tenant_id')->where("id =".$uid." and user_type='2'")->find();
			setcaches("token_".$uid,$userinfo);								
		}
		if($userinfo['token']!=$token || $userinfo['expiretime']<time()){
            session('uid',null);		
            session('token',null);
            session('user',null);
            cookie('uid',null);
            cookie('token',null);
			return 700;				
		}else{
			return 	0;				
		} 
	}
	/*前台个人中心判断是否登录*/
	function LogIn()
	{
		$uid=session("uid");
		if($uid<=0)
		{
			$url=$_SERVER['HTTP_HOST'];
			header("Location:http://".$url); 
			exit;
		}
	}
	/* 判断账号是否超管 */
	function isSuper($uid,$tenantId){
		$isexist=M("users_super")->where("uid='{$uid}' and tenant_id='{$tenantId}'")->find();
		if($isexist){
			return 1;
		}			
		return 0;
	}
	/* 判断账号是被禁用 */
	function isBan($uid){
		$status=M("users")->field("user_status")->where("id=".$uid)->find();
		if(!$status || $status['user_status']==0){
			return 0;
		}
		return 1;
	}
	
	/* 过滤关键词 */
	function filterField($field){
		$configpri=getConfigPri();
		
		$sensitive_field=$configpri['sensitive_field'];
		
		$sensitive=explode(",",$sensitive_field);
		$replace=array();
		$preg=array();
		foreach($sensitive as $k=>$v){
			if($v){
				$re='';
				$num=mb_strlen($v);
				for($i=0;$i<$num;$i++){
					$re.='*';
				}
				$replace[$k]=$re;
				$preg[$k]='/'.$v.'/';
			}else{
				unset($sensitive[$k]);
			}
		}
		
		return preg_replace($preg,$replace,$field);
	}
	
	/* 检验手机号 */
	function checkMobile($mobile){
		$ismobile = preg_match("/^1[3|4|5|6|7|8|9]\d{9}$/",$mobile);
		if($ismobile){
			return 1;
		}else{
			return 0;
		}
	}
	
	/* 多维数组排序 */
 	function array_column2($input, $columnKey, $indexKey = NULL){
		$columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
		$indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
		$indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
		$result = array();
 
		foreach ((array)$input AS $key => $row){ 
			if ($columnKeyIsNumber){
				$tmp = array_slice($row, $columnKey, 1);
				$tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
			}else{
				$tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
			}
			if (!$indexKeyIsNull){
				if ($indexKeyIsNumber){
					$key = array_slice($row, $indexKey, 1);
					$key = (is_array($key) && ! empty($key)) ? current($key) : NULL;
					$key = is_null($key) ? 0 : $key;
				}else{
					$key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
				}
			}
			$result[$key] = $tmp;
		}
		return $result;
	}
	/*直播间判断是否开启僵尸粉*/
	function isZombie($uid)
	{
		$userinfo=M("users")->field("iszombie")->where("id=".$uid)->find();
		return $userinfo['iszombie'];		
	}
	/* 时间差计算 */
	function datetime($time){
		$cha=time()-$time;
		$iz=floor($cha/60);
		$hz=floor($iz/60);
		$dz=floor($hz/24);
		/* 秒 */
		$s=$cha%60;
		/* 分 */
		$i=floor($iz%60);
		/* 时 */
		$h=floor($hz/24);
		/* 天 */
		
		if($cha<60){
			 return $cha.'秒前';
		}else if($iz<60){
			return $iz.'分钟前';
		}else if($hz<24){
			return $hz.'小时'.$i.'分钟前';
		}else if($dz<30){
			return $dz.'天前';
		}else{
			return date("Y-m-d",$time);
		}
	}
    
	/* 时长格式化 */
	function getSeconds($cha,$type=0){		 
		$iz=floor($cha/60);
		$hz=floor($iz/60);
		$dz=floor($hz/24);
		/* 秒 */
		$s=$cha%60;
		/* 分 */
		$i=floor($iz%60);
		/* 时 */
		$h=floor($hz/24);
		/* 天 */
        
        if($type==1){
            if($s<10){
                $s='0'.$s;
            }
            if($i<10){
                $i='0'.$i;
            }

            if($h<10){
                $h='0'.$h;
            }
            
            if($hz<10){
                $hz='0'.$hz;
            }
            return $hz.':'.$i.':'.$s; 
        }
        
		
		if($cha<60){
			return $cha.'秒';
		}else if($iz<60){
			return $iz.'分钟'.$s.'秒';
		}else if($hz<24){
			return $hz.'小时'.$i.'分钟'.$s.'秒';
		}else if($dz<30){
			return $dz.'天'.$h.'小时'.$i.'分钟'.$s.'秒';
		}
	}	
    
	/*判断该用户是否已经认证*/
	function auth($uid)
	{
		$users_auth=M("users_auth")->field('uid,status')->where("uid=".$uid)->find();
		if($users_auth)
		{
			return $users_auth["status"];
		}

        return 3;

	}

	/* 获取指定长度的随机字符串 */
	function random($length = 6 , $numeric = 0) {
		PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
		if($numeric) {
			$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
		} else {
			$hash = '';
			$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
			$max = strlen($chars) - 1;
			for($i = 0; $i < $length; $i++) {
				$hash .= $chars[mt_rand(0, $max)];
			}
		}
		return $hash;
	}
	
	
	/* 发送验证码 */
	function sendCode($mobile,$code){
		$rs=array();
		$config = getConfigPri();
        
        if(!$config['sendcode_switch']){
            $rs['code']=667;
			$rs['msg']='123456';
            return $rs;
        }
		/* 互亿无线 */
		$target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
        $content="您的验证码是：".$code."。请不要把验证码泄露给其他人。";
		
		$post_data = "account=".$config['ihuyi_account']."&password=".$config['ihuyi_ps']."&mobile=".$mobile."&content=".rawurlencode($content);
		//密码可以使用明文密码或使用32位MD5加密
		$gets = xml_to_array(Post($post_data, $target));
        file_put_contents(SITE_PATH.'/data/sendCode_'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').' 提交参数信息 gets:'.json_encode($gets)."\r\n",FILE_APPEND);
        
		if($gets['SubmitResult']['code']==2){
            setSendcode(array('type'=>'1','account'=>$mobile,'content'=>$content));
			$rs['code']=0;
		}else{
			$rs['code']=1002;
			$rs['msg']=$gets['SubmitResult']['msg'];
		} 
		return $rs;
	}
	
	function Post($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
	}
	
	function xml_to_array($xml){
		$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
			$count = count($matches[0]);
			for($i = 0; $i < $count; $i++){
			$subxml= $matches[2][$i];
			$key = $matches[1][$i];
				if(preg_match( $reg, $subxml )){
					$arr[$key] = xml_to_array( $subxml );
				}else{
					$arr[$key] = $subxml;
				}
			}
		}
		return $arr;
	}
	/* 发送验证码 */
	
	
	/**导出Excel 表格
   * @param $expTitle 名称
   * @param $expCellName 参数
   * @param $expTableData 内容
   * @throws \PHPExcel_Exception
   * @throws \PHPExcel_Reader_Exception
   */
	function exportExcel($expTitle,$expCellName,$expTableData,$cellName, $return_url=false)
	{
		$xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
		$fileName = $xlsTitle.'_'.date('YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
		$cellNum = count($expCellName);
		$dataNum = count($expTableData);
		vendor("PHPExcel.PHPExcel");
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
		for($i=0;$i<$cellNum;$i++){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
		}
		for($i=0;$i<$dataNum;$i++){
			for($j=0;$j<$cellNum;$j++){
//				$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
                $objPHPExcel->getActiveSheet(0)->setCellValueExplicit($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]],'s');
			}
		}

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        if($return_url === true){
            $dir_path = RUNTIME_PATH.'excel/';
            if(!is_dir($dir_path)){
                mkdir($dir_path,0777);
            }
            $excelPath = $dir_path.md5($xlsTitle).".xls";
            $objWriter->save($excelPath);
            return $excelPath;
        }

		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
		header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
		$objWriter->save('php://output');
		exit;
	}
	/* 密码加密 */
	function setPass($pass){
		$authcode='rCt52pF2cnnKNB3Hkp';
		$pass="###".md5(md5($authcode.$pass));
		return $pass;
	}	
	/* 密码检查 */
	function passcheck($user_pass) {
		$num = preg_match("/^[a-zA-Z]+$/",$user_pass);
		$word = preg_match("/^[0-9]+$/",$user_pass);
		$check = preg_match("/^[a-zA-Z0-9]{6,12}$/",$user_pass);
		if($num || $word ){
			return 2;
		}else if(!$check){
			return 0;
		}		
		return 1;
	}

/* 支付密码加密 */
function signPaymentPassword($payment_password, $salt){
    return md5(md5($salt.$payment_password));
}

/* 加密盐 */
function createSalt(){
    return time().uniqid();
}
	
	/**
	*  @desc 获取推拉流地址
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKeyA($host,$stream,$type){
		$configpri=getConfigPri();
		$cdn_switch=$configpri['cdn_switch'];
		//$cdn_switch=3;
		switch($cdn_switch){
			case '1':
				$url=PrivateKey_ali($host,$stream,$type);
				break;
			case '2':
				$url=PrivateKey_tx($host,$stream,$type);
				break;
			case '3':
				$url=PrivateKey_qn($host,$stream,$type);
				break;
			case '4':
				$url=PrivateKey_ws($host,$stream,$type);
				break;
			case '5':
				$url=PrivateKey_wy($host,$stream,$type);
				break;
			case '6':
				$url=PrivateKey_ady($host,$stream,$type);
				break;
			case '7':
				//自定义服务器
				//$url='rtmp://120.25.106.132:1935/live/livestream';
				$url=PrivateKey_customSrs($host,$stream,$type);
				break;
            case '8': // 青点云
                $url=PrivateKey_qdy($host,$stream,$type);
                break;
            case '9': // rtmps
                $url=PrivateKey_tx_rtmps($host,$stream,$type);
                break;
		}


		return $url;
	}

		/**
	 * 自定义的srs流服务器鉴权
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	 */
	function PrivateKey_customSrs($host,$stream,$type){
		$configpri=getConfigPri();
		$push=$configpri['srs_push_url'];
		$pull=$configpri['srs_pull_url'];
		$key_push=$configpri['srs_push_key'];
		$key_pull=$configpri['srs_pull_key'];
		$flv_pull=$configpri['srs_flv_pull_url'];
		$time=time();

		if($type==1){
			$domain=$host.'://'.$push;
		}else{
			if(strpos($stream,'.flv')){
				$domain=$host.'://'.$flv_pull;
			}
			else{
				$domain=$host.'://'.$pull;
			}
			
		}

		$filename="/".$stream;

		if($type==1){
            if($key_push!=''){
                $sstring = $filename."-".$time."-0-0-".$key_push;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
			if($auth_key){
				$auth_key='?'.$auth_key;
			}
			$url=$domain.$filename.$auth_key;
		}else{
            if($key_pull!=''){
                $sstring = $filename."-".$time."-0-0-".$key_pull;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
			if($auth_key){
				$auth_key='?'.$auth_key;
			}
			$url=$domain.$filename.$auth_key;
		}
		
		return $url;
	}
	
	/**
	*  @desc 阿里云直播A类鉴权
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_ali($host,$stream,$type){
		$configpri=getConfigPri();
		$push=$configpri['push_url'];
		$pull=$configpri['pull_url'];
		$key_push=$configpri['auth_key_push'];
		$length_push=$configpri['auth_length_push'];
		$key_pull=$configpri['auth_key_pull'];
		$length_pull=$configpri['auth_length_pull'];
		if($type==1){
			$domain=$host.'://'.$push;
			$time=time() + $length_push;
		}else{
			$domain=$host.'://'.$pull;
			$time=time() + $length_pull;
		}
		
		$filename="/5showcam/".$stream;
		
		if($type==1){
            if($key_push!=''){
                $sstring = $filename."-".$time."-0-0-".$key_push;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
			if($auth_key){
				$auth_key='?'.$auth_key;
			}
			//$domain.$filename.'?vhost='.$configpri['pull_url'].$auth_key;
			$url=array(
				'cdn'=>urlencode($domain.'/5showcam'),
				'stream'=>urlencode($stream.$auth_key),
			);
		}else{
            if($key_pull!=''){
                $sstring = $filename."-".$time."-0-0-".$key_pull;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
			if($auth_key){
				$auth_key='?'.$auth_key;
			}
			$url=$domain.$filename.$auth_key;
			
			if($type==3){
				$url_a=explode('/'.$stream,$url);
				$url=array(
					'cdn'=>urlencode($url_a[0]),
					'stream'=>urlencode($stream.$url_a[1]),
				);
			}
		}
		
		return $url;
	}
	
	/**
	*  @desc 腾讯云推拉流地址
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_tx($host,$stream,$type){
		$configpri=getConfigPri();
		$bizid=$configpri['tx_bizid'];
		$push_url_key=$configpri['tx_push_key'];
        $push=$configpri['tx_push'];
		$pull=$configpri['tx_pull'];
		
		$stream_a=explode('.',$stream);
		$streamKey = $stream_a[0];
		$ext = $stream_a[1];
		
		//$live_code = $bizid . "_" .$streamKey;    
		$live_code = $streamKey;    
		   	
		$now_time = time() + 3*60*60;
		$txTime = dechex($now_time);

		$txSecret = md5($push_url_key . $live_code . $txTime);
		$safe_url = "&txSecret=" .$txSecret."&txTime=" .$txTime;		

		if($type==1){
			//$push_url = "rtmp://" . $bizid . ".livepush2.myqcloud.com/live/" .  $live_code . "?bizid=" . $bizid . "&record=flv" .$safe_url;	可录像
			//$url = "rtmp://" . $bizid .".livepush2.myqcloud.com/live/" . $live_code . "?bizid=" . $bizid . "" .$safe_url;
			$url=array(
				'cdn'=>urlencode("rtmp://{$push}/live/"),
				'stream'=>urlencode($live_code."?bizid=".$bizid."".$safe_url),
			);
		}else{
			//$url = "http://{$pull}/live/" . $live_code . ".flv";

			if(strpos($stream,'.flv')){
				$url = $host."://{$pull}/live/" . $live_code . ".flv";
			}
			else{
				$url = $host."://{$pull}/live/" . $live_code;
			}
			
			if($type==3){
				$url_a=explode('/'.$live_code,$url);
				$url=array(
					'cdn'=>urlencode($url_a[0]),
					'stream'=>urlencode($live_code.$url_a[1]),
				);
			}
		}
		
		return $url;
	}

/**
 *  @desc 腾讯云推拉流地址（rtmps）
 *  @param string $host 协议，如:http、rtmp
 *  @param string $stream 流名,如有则包含 .flv、.m3u8
 *  @param int $type 类型，0表示播流，1表示推流
 */
function PrivateKey_tx_rtmps($host,$stream,$type){
    $configpri=getConfigPri();
    $bizid=$configpri['tx_rtmps_bizid'];
    $push_url_key=$configpri['tx_rtmps_push_key'];
    $push=$configpri['tx_rtmps_push'];
    $pull=$configpri['tx_rtmps_pull'];

    $stream_a=explode('.',$stream);
    $streamKey = $stream_a[0];
    $ext = $stream_a[1];

    //$live_code = $bizid . "_" .$streamKey;
    $live_code = $streamKey;

    $now_time = time() + 3*60*60;
    $txTime = dechex($now_time);

    $txSecret = md5($push_url_key . $live_code . $txTime);
    $safe_url = "&txSecret=" .$txSecret."&txTime=" .$txTime;

    if($type==1){
        if($host == 'rtmp'){
            $host = 'rtmps';
        }
        //$push_url = "rtmp://" . $bizid . ".livepush2.myqcloud.com/live/" .  $live_code . "?bizid=" . $bizid . "&record=flv" .$safe_url;	可录像
//        $url = "rtmp://" . $bizid .".livepush2.myqcloud.com/live/" . $live_code . "?bizid=" . $bizid . "" .$safe_url;
       /* $url=array(
            'cdn'=>urlencode("rtmp://{$push}/live/"),
            'stream'=>urlencode($live_code."?bizid=".$bizid."".$safe_url),
        );*/
        $url = $host."://{$push}/live/" . $live_code . "?bizid=" . $bizid . "" .$safe_url;
//        $url = $host."://{$push}/live/" . $live_code; // 面跟了那些参数之后就推不上去了，跟参数只能使用rtmp推流
    }else{
        //$url = "http://{$pull}/live/" . $live_code . ".flv";

        if(strpos($stream,'.flv')){
            $url = $host."://{$pull}/live/" . $live_code . ".flv";
        }
        else{
            $url = $host."://{$pull}/live/" . $live_code;
        }

        if($type==3){
            $url_a=explode('/'.$live_code,$url);
            $url=array(
                'cdn'=>urlencode($url_a[0]),
                'stream'=>urlencode($live_code.$url_a[1]),
            );
        }
    }

    return $url;
}

	/**
	*  @desc 七牛云直播
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_qn($host,$stream,$type){
		require_once './api/public/qiniucdn/Pili_v2.php';
		$configpri=getConfigPri();
		$ak=$configpri['qn_ak'];
		$sk=$configpri['qn_sk'];
		$hubName=$configpri['qn_hname'];
		$push=$configpri['qn_push'];
		$pull=$configpri['qn_pull'];
		$stream_a=explode('.',$stream);
		$streamKey = $stream_a[0];
		$ext = $stream_a[1];

		if($type==1){
			$time=time() +60*60*10;
			//RTMP 推流地址
			$url2 = \Qiniu\Pili\RTMPPublishURL($push, $hubName, $streamKey, $time, $ak, $sk);
			$url_a=explode('/',$url2);
			//return $url_a;
			$url=array(
				'cdn'=>urlencode($url_a[0].'//'.$url_a[2].'/'.$url_a[3]),
				'stream'=>urlencode($url_a[4]),
			);
		}else{
			if($ext=='flv'){
				$pull=str_replace('pili-live-rtmp','pili-live-hdl',$pull);
				//HDL 直播地址
				$url = \Qiniu\Pili\HDLPlayURL($pull, $hubName, $streamKey);
			}else if($ext=='m3u8'){
				$pull=str_replace('pili-live-rtmp','pili-live-hls',$pull);
				//HLS 直播地址
				$url = \Qiniu\Pili\HLSPlayURL($pull, $hubName, $streamKey);
			}else{
				//RTMP 直播放址
				$url = \Qiniu\Pili\RTMPPlayURL($pull, $hubName, $streamKey);
			}
			if($type==3){
				$url_a=explode('/'.$stream,$url);
				$url=array(
					'cdn'=>urlencode($url_a[0]),
					'stream'=>urlencode($stream.$url_a[1]),
				);
			}
		}
				
		return $url;
	}
	/**
	*  @desc 网宿推拉流
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_ws($host,$stream,$type){
		$configpri=getConfigPri();
		if($type==1){
			$domain=$host.'://'.$configpri['ws_push'];
			//$time=time() +60*60*10;
			$filename="/".$configpri['ws_apn'];
			$url=array(
				'cdn'=>urlencode($domain.$filename),
				'stream'=>urlencode($stream),
			);
		}else{
			$domain=$host.'://'.$configpri['ws_pull'];
			//$time=time() - 60*30 + $configpri['auth_length'];
			$filename="/".$configpri['ws_apn']."/".$stream;
			$url=$domain.$filename;
			if($type==3){
				$url_a=explode('/'.$stream,$url);
				$url=array(
					'cdn'=>urlencode($url_a[0]),
					'stream'=>urlencode($stream.$url_a[1]),
				);
			}
		}
		return $url;
	}
	
	/**网易cdn获取拉流地址**/
	function PrivateKey_wy($host,$stream,$type)
	{
		$configpri=getConfigPri();
		$appkey=$configpri['wy_appkey'];
		$appSecret=$configpri['wy_appsecret'];
		$nonce =rand(1000,9999);
		$curTime=time();
		$var=$appSecret.$nonce.$curTime;
		$checkSum=sha1($appSecret.$nonce.$curTime);

		$header =array(
			"Content-Type:application/json;charset=utf-8",
			"AppKey:".$appkey,
			"Nonce:" .$nonce,
			"CurTime:".$curTime,
			"CheckSum:".$checkSum,
		);
        
        if($type==1){
            $url='https://vcloud.163.com/app/channel/create';
            $paramarr = array(
                "name"  =>$stream,
                "type"  =>0,
            );
        }else{
            $url='https://vcloud.163.com/app/address';
            $paramarr = array(
                "cid"  =>$stream,
            );
        }
        $paramarr=json_encode($paramarr);
        
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL, $url);
		curl_setopt($curl,CURLOPT_HEADER, 0);
		curl_setopt($curl,CURLOPT_HTTPHEADER, $header); 
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_POST, 1);
		curl_setopt($curl,CURLOPT_POSTFIELDS, $paramarr);
		$data = curl_exec($curl);
		curl_close($curl);
		$url=json_decode($data,1);
		return $url;
	}
	
	/**
	*  @desc 奥点云推拉流
	*  @param string $host 协议，如:http、rtmp
	*  @param string $stream 流名,如有则包含 .flv、.m3u8
	*  @param int $type 类型，0表示播流，1表示推流
	*/
	function PrivateKey_ady($host,$stream,$type){
		$configpri=getConfigPri();
		$stream_a=explode('.',$stream);
		$streamKey = $stream_a[0];
		$ext = $stream_a[1];

		if($type==1){
			$domain=$host.'://'.$configpri['ady_push'];
			//$time=time() +60*60*10;
			$filename="/".$configpri['ady_apn'];
			$url=array(
				'cdn'=>urlencode($domain.$filename),
				'stream'=>urlencode($stream),
			);
		}else{
			if($ext=='m3u8'){
				$domain=$host.'://'.$configpri['ady_hls_pull'];
				//$time=time() - 60*30 + $configpri['auth_length'];
				$filename="/".$configpri['ady_apn']."/".$stream;
				$url=$domain.$filename;
			}else{
				$domain=$host.'://'.$configpri['ady_pull'];
				//$time=time() - 60*30 + $configpri['auth_length'];
				$filename="/".$configpri['ady_apn']."/".$stream;
				$url=$domain.$filename;
			}
			
			if($type==3){
				$url_a=explode('/'.$stream,$url);
				$url=array(
					'cdn'=>urlencode($url_a[0]),
					'stream'=>urlencode($stream.$url_a[1]),
				);
			}
		}
				
		return $url;
	}

/**
 *  @desc 青点云推拉流（copy 网宿推拉流）
 *  @param string $host 协议，如:http、rtmp
 *  @param string $stream 流名,如有则包含 .flv、.m3u8
 *  @param int $type 类型，0表示播流，1表示推流
 */
function PrivateKey_qdy($host,$stream,$type){
    $configpri=getConfigPri();
    if($type==1){
        $domain=$host.'://'.$configpri['qdy_push'];
    }else{
        $domain=$host.'://'.$configpri['qdy_pull'];
    }
    $filename="/"."mb/".$stream;
    $url=$domain.$filename;
    return $url;
}

/* 生成邀请码 */
	function createCode($len=6,$format='ALL2'){
        $is_abc = $is_numer = 0;
        $password = $tmp =''; 
        switch($format){
            case 'ALL':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
            case 'ALL2':
                $chars='ABCDEFGHJKLMNPQRSTUVWXYZ0123456789';
                break;
            case 'CHAR':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 'NUMBER':
                $chars='0123456789';
                break;
            default :
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
        }
        
        while(strlen($password)<$len){
            $tmp =substr($chars,(mt_rand()%strlen($chars)),1);
            if(($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR'){
                $is_numer = 1;
            }
            if(($is_abc <> 1 && preg_match('/[a-zA-Z]/',$tmp)) || $format == 'NUMBER'){
                $is_abc = 1;
            }
            $password.= $tmp;
        }
        if($is_numer <> 1 || $is_abc <> 1 || empty($password) ){
            $password = createCode($len,$format);
        }
        if($password!=''){
            
            $oneinfo=M("users_agent_code")->field("uid")->where("code='{$password}'")->find();
            if(!$oneinfo){
                return $password;
            }            
        }
        $password = createCode($len,$format);
        return $password;
    }


	
	/* 数字格式化 */
	function NumberFormat($num){
		if($num<10000){

		}else if($num<1000000){
			$num=round($num/10000,2).'万';
		}else if($num<100000000){
			$num=round($num/10000,1).'万';
		}else if($num<10000000000){
			$num=round($num/100000000,2).'亿';
		}else{
			$num=round($num/100000000,1).'亿';
		}
		return $num;
	}

/* 数字格式化 */
function NumberCeil($num){

    if ($num>100000000){
        $num=bcdiv(ceil($num/10000000,1),10,1).'亿';
    }elseif ($num>10000){
        $num=bcdiv(ceil($num/1000,1),10,1).'万';
    }
    return $num;

}
	/* 数字格式化 不保留小数*/
	function NumberFormat2($num){
		if($num<10000){
			$num=round($num);
		}else if($num<100000000){
			$num=round($num/10000).'万';
		}else{
			$num=round($num/100000000).'亿';
		}
		return $num;
	}
	
	/* 获取用户VIP */
	function getUserVip($uid){
		$rs=array(
			'type'=>'0',
		);
		$nowtime=time();
		$key='vip_'.$uid;
		$isexist=getcaches($key);
		if(!$isexist){
			$isexist=M("users_vip")->where("uid={$uid}")->find();		
			if($isexist){
				setcaches($key,$isexist);
			}
		}

		if($isexist){
			if($isexist['endtime'] <= $nowtime){
				return $rs;
			}
			$rs['type']='1';
		}

		return $rs;
	}

    /* 获取vip详情 */
    function getVipInfo($vip_id){
        $vipinfo=getcaches('vipinfo_'.$vip_id);
        if(!$vipinfo){
            $vipinfo = M("vip")->where("id={$vip_id}")->find();
            if($vipinfo){
                setcaches('vipinfo_'.$vip_id,$vipinfo);
            }
        }
        return $vipinfo;
    }

	/* 获取用户坐骑 */
	function getUserCar($uid){
		$rs=array(
			'id'=>'0',
			'swf'=>'',
			'swftime'=>'0',
			'words'=>'',
		);
		$nowtime=time();
		$key='car_'.$uid;
		$isexist=getcaches($key);
		if(!$isexist){
			$isexist=M("users_car")->where("uid={$uid} and status=1")->find();
			if($isexist){
				setcaches($key,$isexist);
			}
		}
		if($isexist){
			if($isexist['endtime']<= $nowtime){
				return $rs;
			}
			$key2='carinfo';
			$car_list=getcaches($key2);
			if(!$car_list){
				$car_list=M("car")->order("orderno asc")->select();
				if($car_list){
					setcaches($key2,$car_list);
				}
			}
			$info=array();
			if($car_list){
				foreach($car_list as $k=>$v){
					if($v['id']==$isexist['carid']){
						$info=$v;
					}	
				}
				
				if($info){
					$rs['id']=$info['id'];
					$rs['swf']=get_upload_path($info['swf']) ;
					$rs['swftime']=$info['swftime'];
					$rs['words']=$info['words'];
				}
			}
			
		}
		
		return $rs;
	}

	/* 获取用户靓号 */
	function getUserLiang($uid){
		$rs=array(
			'name'=>'0',
		);
		$nowtime=time();
		$key='liang_'.$uid;
		$isexist=getcaches($key);
		if(!$isexist){
			$isexist=M("liang")->where("uid={$uid} and status=1 and state=1")->find();
			if($isexist){
				setcaches($key,$isexist);
			}
		}
		if($isexist){
			$rs['name']=$isexist['name'];
		}
		
		return $rs;
	}
	
	/* 三级分销 */
	function setAgentProfit($uid,$total){
		/* 分销 */
		$distribut1=0;
		$distribut2=0;
		$distribut3=0;
		$configpri=getConfigPri();
		if($configpri['agent_switch']==1){
			$agent=M("users_agent")->where("uid={$uid}")->find();
			$isinsert=0;
			/* 一级 */
			if($agent['one_uid'] && $configpri['distribut1']){
				$distribut1=$total*$configpri['distribut1']*0.01;
                if($distribut1>0){
                    $profit=M("users_agent_profit")->where("uid={$agent['one_uid']}")->find();
                    if($profit){
                        M()->execute("update __PREFIX__users_agent_profit set one_profit=one_profit+{$distribut1} where uid='{$agent['one_uid']}'");
                    }else{
                        M("users_agent_profit")->add(array('uid'=>$agent['one_uid'],'one_profit' =>$distribut1 ));
                    }
                    M()->execute("update __PREFIX__users set votes=votes+{$distribut1} where id='{$agent['one_uid']}'");
                    $isinsert=1;
                    
                    $insert_votes=[
                        'type'=>'income',
                        'action'=>'agentprofit',
                        'uid'=>$agent['one_uid'],
                        'votes'=>$distribut1,
                        'addtime'=>time(),
                    ];
                    M('users_voterecord')->add($insert_votes);
                }
			}
			/* 二级 */
			if($agent['two_uid'] && $configpri['distribut2']){
				$distribut2=$total*$configpri['distribut2']*0.01;
                if($distribut2>0){
                    $profit=M("users_agent_profit")->where("uid={$agent['two_uid']}")->find();
                    if($profit){
                        M()->execute("update __PREFIX__users_agent_profit set two_profit=two_profit+{$distribut2} where uid='{$agent['two_uid']}'");
                    }else{
                        M("users_agent_profit")->add(array('uid'=>$agent['two_uid'],'two_profit' =>$distribut2 ));
                    }
                    M()->execute("update __PREFIX__users set votes=votes+{$distribut2} where id='{$agent['two_uid']}'");
                    $isinsert=1;
                    
                    $insert_votes=[
                        'type'=>'income',
                        'action'=>'agentprofit',
                        'uid'=>$agent['two_uid'],
                        'votes'=>$distribut2,
                        'addtime'=>time(),
                    ];
                    M('users_voterecord')->add($insert_votes);
                }
			}
			/* 三级 */
			/* if($agent['three_uid'] && $configpri['distribut3']){
				$distribut3=$total*$configpri['distribut3']*0.01;
                if($distribut3>0){
                    $profit=M("users_agent_profit")->where("uid={$agent['three_uid']}")->find();
                    if($profit){
                        M()->execute("update __PREFIX__users_agent_profit set three_profit=three_profit+{$distribut3} where uid='{$agent['three_uid']}'");
                    }else{
                        M("users_agent_profit")->add(array('uid'=>$agent['three_uid'],'three_profit' =>$distribut3 ));
                    }
                    M()->execute("update __PREFIX__users set votes=votes+{$distribut3} where id='{$agent['three_uid']}'");
                    $isinsert=1;
                    
                    $insert_votes=[
                        'type'=>'income',
                        'action'=>'agentprofit',
                        'uid'=>$agent['three_uid'],
                        'votes'=>$distribut3,
                        'addtime'=>time(),
                    ];
                    M('users_voterecord')->add($insert_votes);
                }
			} */
			
			if($isinsert==1){
				$data=array(
					'uid'=>$uid,
					'total'=>$total,
					'one_uid'=>$agent['one_uid'],
					'two_uid'=>$agent['two_uid'],
					'three_uid'=>$agent['three_uid'],
					'one_profit'=>$distribut1,
					'two_profit'=>$distribut2,
					'three_profit'=>$distribut3,
					'addtime'=>time(),
				);
				M("users_agent_profit_recode")->add($data);
			}
		}
		return 1;
		
	}
    
    /* 家族分成 */
    function setFamilyDivide($liveuid,$total){
        $configpri=getConfigPri();
	
		$anthor_total=$total;
		/* 家族 */
		if($configpri['family_switch']==1){
			$users_family=M('users_family')
							->field("familyid,divide_family")
							->where("uid={$liveuid} and state=2")
							->find();

			if($users_family){
				$familyinfo=M('family')
							->field("uid,divide_family")
							->where('id='.$users_family['familyid'])
							->find();
                if($familyinfo){
                    $divide_family=$familyinfo['divide_family'];

                    /* 主播 */
                    if( $users_family['divide_family']>=0){
                        $divide_family=$users_family['divide_family'];
                        
                    }
                    $family_total=$total * $divide_family * 0.01;
                    
                        $anthor_total=$total - $family_total;
                        $addtime=time();
                        $time=date('Y-m-d',$addtime);
                        M('family_profit')
                               ->add(array("uid"=>$liveuid,"time"=>$time,"addtime"=>$addtime,"profit"=>$family_total,"profit_anthor"=>$anthor_total,"total"=>$total,"familyid"=>$users_family['familyid']));

                    if($family_total){
                        M()->execute("update __PREFIX__users set votes=votes+{$family_total} where id='{$familyinfo['uid']}'");
                        
                        $insert_votes=[
                            'type'=>'income',
                            'action'=>'familyprofit',
                            'uid'=>$familyinfo['uid'],
                            'votes'=>$family_total,
                            'addtime'=>time(),
                        ];
                        M('users_voterecord')->add($insert_votes);
                    }
                }
			}
		}
        return $anthor_total;
    }
	
	/* ip限定 */
	function ip_limit(){
		$configpri=getConfigPri();
		if($configpri['iplimit_switch']==0){
			return 0;
		}
		$date = date("Ymd");
		$ip= ip2long($_SERVER["REMOTE_ADDR"]) ; 
		$IP_limit=M("getcode_limit_ip");
		$isexist=$IP_limit->field('ip,date,times')->where("ip={$ip}")->find();
		if(!$isexist){
			$data=array(
				"ip" => $ip,
				"date" => $date,
				"times" => 1,
			);
			$isexist=$IP_limit->add($data);
			return 0;
		}elseif($date == $isexist['date'] && $isexist['times'] > $configpri['iplimit_times'] ){
			return 1;
		}else{
			if($date == $isexist['date']){
				$isexist=$IP_limit->where("ip={$ip}")->setInc('times',1);
				return 0;
			}else{
				$isexist=$IP_limit->where("ip={$ip}")->save(array('date'=> $date ,'times'=>1));
				return 0;
			}
		}	
	}	
    
    /* 验证码记录 */
    function setSendcode($data){
        if($data){
            $data['addtime']=time();
            M('sendcode')->add($data);
        }
    }

    /* 验证码记录 */
    function checkUser($where){
        if($where==''){
            return 0;
        }

        $isexist=M('users')->field('id')->where($where)->find();
        
        if($isexist){
            return 1;
        }
        
        return 0;
    }
    
    
	function LangT($key, $params = array()){
		
		//$rs = isset(LANGUAGE[$key]) ? LANGUAGE[$key] : $key;
        $rs=$key;
        $names = array_keys($params);
		
		if($names){
			foreach($names as $k=>$v){
				$names[$k]='{' . $v . '}';
			}
		}
        //$names = array_map('formatVa111r', $names);
        return str_replace($names, array_values($params), $rs);
	}	
    
    /* 管理员操作日志 */
    function setAdminLog($action, $type = 1, $tenant_id = null){
        $tenant_id = $tenant_id ? $tenant_id : getTenantIds();
        $adminid = isset($_SESSION['ADMIN_ID']) ? $_SESSION['ADMIN_ID'] : 0;
        $admin = isset($_SESSION['name']) ? $_SESSION['name'] : 'system';
        $type_list = array(
            'Indexadmin' => 3,  //'用户信息',
            'Shotvideo' => 4,  //'短视频',
            'Longvideo' => 5,  //'长视频',
            'Manual' => 6,  //'余额调整',
            'Cash' => 7,  //'提现',
            'Charge' => 8,  //'充值',
            'Vip' => 9,  //'vip等级',
            'Ads' => 10,  //'广告',
            'Receptionmeun' => 11,  //'前台菜单',
            'Menu' => 12,  //'后台菜单',
            'TenantConfig' => 14,  //'网站设置',
            'PlatformConfig' => 15,  //'平台设置',
            'Rbac' => 16,  //'角色',
            'Livepushpull' => 17,  //'推拉流线路',
            'Storage' => 18,  //'文件存储',
            'Playbackaddress' => 19,  //'播放下载线路',
            'SystemConfig' => 20,  //'系统配置',
            'RechargeLevel' => 21,  //'层级配置',
            'User' => 22,  //'后台账号',
            'Rate' => 23,  // 汇率
            'Red' => 24,  // 红包
            'Liveing' => 200,  // 直播房间
            'Pay' => 300,  // 支付
        );
        if($type == 1 && isset($type_list[CONTROLLER_NAME])){
            $type = $type_list[CONTROLLER_NAME];
        }

        $data=array(
            'adminid'=>$adminid,
            'admin'=>$admin,
            'action'=>$action,
            'type'=>intval($type),
            'ip'=>get_client_ip(),
            'addtime'=>time(),
            'tenant_id'=>intval($tenant_id),
        );
        $result = M("admin_log")->add($data);
        return $result;
    }

    /*获取用户总的送出钻石数*/
	function getSendCoins($uid){
		$sum=M("users_coinrecord")->where("type='expend' and (action='sendbarrage' or action='sendgift') and uid={$uid}")->sum("totalcoin");
		return number_format($sum);
	}
    
    
    /* 印象标签 */
    function getImpressionLabel(){
        
        $key="getImpressionLabel";
		$list=getcaches($key);
		if(!$list){
            $list=M('impression_label')
				->order("orderno asc,id desc")
				->select();
            foreach($list as $k=>$v){
                $list[$k]['colour']='#'.$v['colour'];
            }
                
			setcaches($key,$list); 
		}

        return $list;
    }       

    /* 获取某人的标签 */
    function getMyLabel($uid){
        
        $key="getMyLabel_".$uid;
		$rs=getcaches($key);
		if(!$rs){
            $rs=array();
            $list=M("users_label")
                    ->field("label")
                    ->where("touid={$uid}")
                    ->select();
            $label=array();
            foreach($list as $k=>$v){
                $v_a=preg_split('/,|，/',$v['label']);
                $v_a=array_filter($v_a);
                if($v_a){
                    $label=array_merge($label,$v_a);
                }
            }

            if(!$label){
                return $rs;
            }
            

            $label_nums=array_count_values($label);
            
            $label_key=array_keys($label_nums);
            
            $labels=getImpressionLabel();
            
            $order_nums=array();
            foreach($labels as $k=>$v){
                if(in_array($v['id'],$label_key)){
                    $v['nums']=(string)$label_nums[$v['id']];
                    $order_nums[]=$v['nums'];
                    $rs[]=$v;
                }
            }
            
            array_multisort($order_nums,SORT_DESC,$rs);
        
			setcaches($key,$rs); 
		}

        return $rs;
        
    }   

    /* 获取用户本场贡献 */
    function getContribut($uid,$liveuid,$showid){
        $sum=M("users_coinrecord")
				->where("action='sendgift' and uid={$uid} and touid={$liveuid} and showid={$showid} ")
				->sum('totalcoin');
        if(!$sum){
            $sum=0;
        }
        
        return (string)$sum;
    }
    
    /* 获取用户守护信息 */
    function getUserGuard($uid,$liveuid){
        $rs=array(
            'type'=>'0',
            'endtime'=>'0',
        );
        $key='getUserGuard_'.$uid.'_'.$liveuid;
        $guardinfo=getcaches($key);
        if(!$guardinfo){
            $guardinfo=M('guard_users')
					->field('type,endtime')
					->where("uid = {$uid} and liveuid={$liveuid}")
					->find();    
            setcaches($key,$guardinfo);
        }
        $nowtime=time();
                    
        if($guardinfo && $guardinfo['endtime']>$nowtime){
            $rs=array(
                'type'=>$guardinfo['type'],
                'endtime'=>$guardinfo['endtime'],
                'endtime_date'=>date("Y.m.d",$guardinfo['endtime']),
            );
        }
        return $rs;
    }
    
    
    /* 对象转数组 */
    function object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)object_to_array($v);
            }
        }
     
        return $obj;
    }
    
    /* 分类路径处理 */
    function setpath($id){
        $len=strlen($id);
        $s='';
        for($i=$len;$i<8;$i++){
            $s.='0';
        }
        $path=$s.$id.';';
        
        return $path;
    }
    
    function shorturl($longurl){
		$host = 'https://dwz.cn';
	    $path = '/admin/v2/create';
	    $url = $host . $path;
	    $method = 'POST';
	    $content_type = 'application/json';
	    
	    // TODO: 设置Token
	    $token = '6a4d5d266e5e57711c92be0140e40c08';
	    // TODO：设置待注册长网址
	    $bodys = array('Url'=>$longurl, 'TermOfValidity'=>'1-year');
	    
	    // 配置headers 
	    $headers = array('Content-Type:'.$content_type, 'Token:'.$token);
	    
	    // 创建连接
	    $curl = curl_init($url);
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($curl, CURLOPT_FAILONERROR, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_HEADER, false);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($bodys));
	    
	    // 发送请求
	    $result = curl_exec($curl);
	    curl_close($curl);
	    //	var_dump($result);
	    // 读取响应  
		$result = json_decode($result, true);  
	
		return $result['ShortUrl'];
	}
	
    function dwz($longurl){
    	$longurl=urlencode($longurl);
	    $url = "http://h5ip.cn/index/api?url=$longurl";
	    //$data=file_get_contents($url);
	    $headerArray =array("Content-type:application/json;","Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($url,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        //var_dump($data);
        $output = json_decode($output,true);
        return $output;
	}

function getCutvideo($tenant_id){
    $rs=array('code'=>0,'msg'=>'');
    $file = $_FILES['file'];        //文件信息
    $filename = $file['name'];      //本地文件名
    $tmpFile = $file['tmp_name'];   //临时文件名
    $fileType = $file['type'];      //文件类型
    $tenant_id = $tenant_id == 0 ? getTenantIds() : $tenant_id;
    $config = getConfigPub($tenant_id);
    $url = $config['url_of_push_to_java_cut_video'] ? trim($config['url_of_push_to_java_cut_video'], '/') : 'https://liveprod-new.jxmm168.com/cut/api/video/cut';
    $uplodainfo = postUploadFile($url,$filename,$tmpFile, 'text/plain');
    $uplodainfo = json_decode($uplodainfo,true);
    if (!isset($uplodainfo['code']) || $uplodainfo['code'] != 200){
        setAdminLog('【视频上传】-失败-'.$url.'-'.json_encode($uplodainfo));
    }
    return $uplodainfo;
}
/**
 * @param string $url 请求地址
 * @param string $filename 文件名
 * @param string $path 文件临时路劲
 * @param string $type 文件类型
 * @return mixed
 */
function postUploadFile($url,$filename,$path,$type = 'text/plain')
{
    //php 5.5以上的用法
    if (class_exists('\CURLFile')) {
        $data = array(
            'file' => new \CURLFile(realpath($path), $type, $filename),
        );
    } else {
        //5.5以下会走到这步
        $data = array(
            'file'=>'@'.realpath($path).";type=".$type.";filename=".$filename,
        );
    }set_time_limit(0);
    ini_set('max_execution_time', '0');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    $return_data = curl_exec($ch);
    curl_close($ch);
    return $return_data;
}

/* 积分明细变更类型 */
function actionType(){
    return array(
        '1' => '首页（点击）',
        '2' => '游戏（点击）',
        '3' => '直播（点击）',
        '4' => '我的（点击）',
        '5' => '启动页广告',
        '6' => '啪啪（点击）',
        '7' => '首页推荐（观看）',
        '8' => '首页精选（点击）',
        '9' => '首页点赞排行（点击）',
        '10' => '首页最新上传（点击）',
        '11' => '首页周榜（点击）',
        '12' => '首页月榜（点击）',
        '13' => '首页最高人气（点击）',
        '14' => '首页最多下载（点击）',
        '15' => '首页最多观看（点击）',
        '16' => '首页广告（点击）',
        '17' => '游戏广告（点击）',
        '18' => '直播广告（点击）',
        '19' => '直播房间X（点击）',
        '20' => '直播标签N（点击）',
        '21' => '啪啪标签（点击）',
        '22' => '啪啪搜索（搜索）',
        '23' => '啪啪下载（点击）',
        '24' => '啪啪历史记录（点击）',
        '25' => '啪啪广告（点击跳转）',
        '26' => '啪啪查看更多（点击）',
        '27' => '啪啪视频列表（点击视频）',
        '28' => '我的设置（点击）',
        '29' => '我的修改昵称（修改昵称）',
        '30' => '我的设置（修改密码）',
        '31' => '我的设置（清除缓存）',
        '32' => '我的设置（检查更新）',
        '33' => '我的设置（用户协议）',
        '34' => '我的设置（退出）',
        '35' => '我的钱包（点击）',
        '36' => '我的钱包（充值）',
        '37' => '我的钱包（提现）',
        '38' => '我的广告（点击）',
        '39' => '我的观看历史（点击）',
        '40' => '我的VIP（VIP购买）',
        '41' => '我的视频（点击）',
        '42' => '我的视频长视频（播放）',
        '43' => '我的视频长视频（上传）',
        '44' => '我的视频短视频（播放）',
        '45' => '我的视频短视频（上传）',
        '46' => '我的收支（查看充值）',
        '47' => '我的收支（查看收支）',
        '48' => '我的收支（查看提现）',
        '49' => '我的收藏长视频（查看）',
        '50' => '我的收藏长视频（播放）',
        '51' => '我的收藏短视频（查看）',
        '52' => '我的收藏短视频（播放）',
        '53' => '我的下载长视频（查看）',
        '54' => '我的下载长视频（播放）',
        '55' => '我的下载短视频（查看）',
        '56' => '我的下载短视频（播放）',
        '57' => '我的福利（查看）',
        '58' => '我的福利（兑换）',
        '59' => '我的常见问题（查看）',
        '60' => '我的推广详情（查看）',
        '61' => '我的推广详情（保存图片）',
        '62' => '我的推广详情（复制链接）',
        '63' => '我的反馈（查看）',
        '64' => '我的反馈（提交）',
        '65' => '我的关注（查看）',
        '66' => '我的关注（播放）',
        '67' => '我的开播（认证）',
        '68' => '我的开播（开播）',
        '77' => '设置语言',
        '69' => '播放数量（记录）',
        '70' => '播放时长（记录）',
        '71' => '播放关注（关注）',
        '72' => '播放点赞（点赞）',
        '73' => '播放评论（评论）',
        '74' => '播放收藏（收藏）',
        '75' => '播放下载（下载）',
        '76' => '播放广告（查看）',
    );
}

/* 积分明细操作类型 */
function integralLogActType(){
    return array(
        '1' => '注册',
        '2' => '兑换',
        '3' => '用户行为',
    );
}

/*
 * bootstrap-select css
 * <link rel="stylesheet" href="/public/bootstrap-select-1.13.9/dist/css/bootstrap.min.css">
 * */
function bootstrap_select_css(){
    return '
            <link rel="stylesheet" href="/public/bootstrap-select-1.13.9/dist/css/bootstrap-select.min.css">
            <style>
            .selectpicker + .layui-form-select{display: none;}
            .dropdown > button{color: #757575 !important;background-color: white !important;}
            .bootstrap-select:not([class*=col-]):not([class*=form-control]):not(.input-group-btn) {width: 182px;}
            </style>';
}

/*
 * bootstrap-select js
 *
 * */
function bootstrap_select_js(){
    return '<script src="/public/bootstrap-select-1.13.9/js/jquery.min.js"></script>
            <script src="/public/bootstrap-select-1.13.9/js/bootstrap-4.1.0.bundle.min.js"></script>
            <script src="/public/bootstrap-select-1.13.9/dist/js/bootstrap-select.min.js"></script>
            <script src="/public/bootstrap-select-1.13.9/js/i18n/defaults-zh_CN.js"></script>';
}

/**
 * 转换成 时:分:秒
 *
 * @param [type] $time
 * @return void
 */
function sec2Time($time)
{
    if (is_numeric($time)) {
        $t = '';
        if ($time >= 3600) {
            $t .= floor($time / 3600) . ":";
            $time = ($time % 3600);
        }else{
            $t .= "00:";
        }
        if ($time >= 60) {
            $t .= floor($time / 60) . ":";
            $time = ($time % 60);
        }else{
            $t .= "00:";
        }
        $t .= floor($time);
        return $t;

    } else {
        return (bool) false;
    }
}

function getTableMenu($menuId,$action){
    $action = strtolower($action);
    $arr = session('menuid_arr');
    if ($menuId){
        if (!isset($arr[$action.'_menu_id']) || $arr[$action.'_menu_id']=='undefined'){
            $arr[$action.'_menu_id'] = $menuId;
            session('menuid_arr',$arr);
        }
    }else{
        $menuId = $arr[$action.'_menu_id'];
    }
    $menuList = M('menu')->where(['parentid'=>$menuId])->order('listorder ASC')->select();
    if (!$menuList){
        unset($arr[$action.'_menu_id']);
        session('menuid_arr',$arr);
        return;
    }
    $html = '';
    foreach ($menuList as $key=>$value){
        $menuAction = $value['app'].'/'.$value['model'].'/'.$value['action'];

        if (sp_auth_check($_SESSION['ADMIN_ID'],$menuAction)){
            if ($action == strtolower($menuAction)){
                $html .= '<li class="active"><a>'.$value['name'].'</a></li>';
            }else{
                $html .= '<li><a href="/'.$value['app']."/".$value['model']."/".$value['action']."/menuid/".$menuId.'">'.$value['name'].'</a></li>';
            }
        }
    }
    echo $html;
}

function dateAdjust($date){
    if ($date){
        $timestamp = strtotime($date);
        if ($timestamp){
            return date('H:i:s',$timestamp).'<br>'.date('Y-m-d',$timestamp);
        }
    }
}

/*
 * 首充活动/分享活动 奖励赠送
 * */
function activity_reward($uid,$recharge_num,$coin,$tenant_id){
    if($recharge_num != 0 || $coin<=0){ // 非首次充值,不进行处理 | 金币小于等于0,不进行处理
        return false;
    }
    $agentinfo = getAgentInfo($uid);
    // type 类型：1 首充活动，2 分享活动
    $fc_act_con_list = M('activity_config')->where("type=1 and tenant_id  = '{$tenant_id}' ")->order('sort_num asc')->select();
    if(count($fc_act_con_list) > 0){
        $reward = $watnum = $wattime = 0;
        foreach ($fc_act_con_list as $key=>$val){
            if($val['min'] <= $coin){
                $reward = $val['reward'];
                $watnum = $val['watnum'];
                $wattime = $val['wattime'];
            }
        }
        $user_info = UsersModel::getInstance()->getUserInfoWithIdAndTid($uid);
        if(!$user_info){
            return false;
        }
        $config=getConfigPub($tenant_id);

        // 更新用户数据
        if ($config['first_charge_award_amount_type'] ==1){
            if($reward>0 || $watnum>0 || $wattime>0) {
                M("users")->where(['id' => intval($uid)])->save([
                    'coin' => array('exp', 'coin+' . floatval($reward)),
                    'watch_num' => array('exp', 'watch_num+' . intval($watnum)),
                    'watch_time' => array('exp', 'watch_time+' . intval($wattime)),
                ]);
            }
            $actionType  = 'income';
        }else{
            if($reward>0 || $watnum>0 || $wattime>0) {
                M("users")->where(['id' => intval($uid)])->save([
                    'nowithdrawable_coin' => array('exp', 'nowithdrawable_coin+' . floatval($reward)),
                    'watch_num' => array('exp', 'watch_num+' . intval($watnum)),
                    'watch_time' => array('exp', 'watch_time+' . intval($wattime)),
                ]);
                $actionType  = 'income_nowithdraw';
                $redis = connectRedis();
                $keytime = time();
                $redis->lPush($uid . '_reward_time', $keytime);// 存用户 时间数据key
                $amount = $redis->get($uid . '_' . $keytime.'_reward');
                $totalAmount = bcadd($reward, $amount, 2);
                $redis->set($uid . '_' . $keytime.'_reward', $totalAmount);// 存佣金
                $expireTime = time() + $config['withdrawal_time'] * 86400;
                /** 86400*/
                $redis->expireAt($uid . '_' . $keytime.'_reward', $expireTime);// 设置过去时间
            }

        }

        // 可提现金币变动记录
        if($reward>0){
            UsersCoinrecordModel::getInstance()->addCoinrecord([
                'type' => $actionType,
                'uid' => intval($uid),
                "user_type" => intval($user_info['user_type']),
                'addtime' => time(),
                'tenant_id' => intval($tenant_id),
                'action' => 'firstrecharge',
                "pre_balance" => floatval($user_info['coin']),
                'totalcoin' => floatval($reward),
                "after_balance" => floatval(bcadd($user_info['coin'], $reward,4)),
            ]);
            delUserInfoCache($uid);
        }
        // 活动赠送明细记录
        if($watnum>0 || $wattime>0){
            M('activity_reward_log')->add([
                'type' => 1,
                'watnum' => intval($watnum),
                'wattime' => intval($wattime),
                'uid' => intval($uid),
                'user_login' => $agentinfo['user_login'],
                'user_type' => $agentinfo['user_type'],
                'reward' => floatval($reward),
                'tenant_id' => intval($tenant_id),
                'ctime' =>time(),
            ]);
        }
    }

    $parent_uid = isset($agentinfo['one_uid']) ? $agentinfo['one_uid'] : 0;
    $parent_info = $parent_uid ? UsersModel::getInstance()->getUserInfoWithIdAndTid($parent_uid) : array();
    $child_uid = $parent_uid ? M('users_agent')->where(['one_uid'=>intval($parent_uid)])->field('uid')->select() : array();
    $child_uids = count($child_uid)>0 ? array_keys(array_column($child_uid,null,'uid')) : array();
    $share_act_con_list = M('activity_config')->where("type=2 and tenant_id  = '{$tenant_id}'")->order('is_over asc,per_num asc')->select();

    if(isset($parent_info['id']) && count($child_uids) > 0 && count($share_act_con_list)>0){
        $reward_child_count = M('users')->where([
            'id'=>['in',$child_uids],
            'firstrecharge_coin'=>['egt',floatval($share_act_con_list[0]['recom_frmin'])]
        ])->count();

        $tenant_id = $parent_info['tenant_id'];
        $reward = $watnum = $wattime = 0;
        foreach ($share_act_con_list as $key=>$val){
            if($val['recom_frmin'] <= $coin && $reward_child_count == $val['per_num']){
                $reward = $val['reward'];
                $watnum = $val['watnum'];
                $wattime = $val['wattime'];
            }
        }
        if ($config['share_award_amount_type'] ==1){ // 分享金额可提现
            if($reward>0 || $watnum>0 || $wattime>0) {
                M("users")->where(['id'=>intval($parent_uid)])->save([
                    'coin' => array('exp','coin+'.$reward),
                    'watch_num' => array('exp','watch_num+'.$watnum),
                    'watch_time' => array('exp','watch_time+'.$wattime),
                ]);
                $actionType  = 'income';
            }
        }else{// 分享不金额可提现
            if($reward>0 || $watnum>0 || $wattime>0) {
                M("users")->where(['id' => intval($parent_uid)])->save([
                    'nowithdrawable_coin' => array('exp', 'nowithdrawable_coin+' . $reward),
                    'watch_num' => array('exp', 'watch_num+' . $watnum),
                    'watch_time' => array('exp', 'watch_time+' . $wattime),
                ]);
                $actionType  = 'income_nowithdraw';
                $redis = connectRedis();
                $keytime = time();
                $redis->lPush($parent_uid . '_reward_time', $keytime);// 存用户 时间数据key
                $amount = $redis->get($parent_uid . '_' . $keytime.'_reward');
                $totalAmount = bcadd($reward, $amount, 2);
                $redis->set($parent_uid . '_' . $keytime.'_reward', $totalAmount);// 存佣金
                $expireTime = time() + $config['withdrawal_time'] * 86400;
                /** 86400*/
                $redis->expireAt($parent_uid . '_' . $keytime.'_reward', $expireTime);// 设置过去时间
                delUserInfoCache($parent_uid);
            }
        }

        // 更新用户数据

        // 可提现金币变动记录
        if($reward>0){
            UsersCoinrecordModel::getInstance()->addCoinrecord([
                'type' => $actionType,
                'uid' => intval($parent_uid),
                "user_type" => intval($parent_info['user_type']),
                'addtime' => time(),
                'tenant_id' => intval($tenant_id),
                'action' => 'share_firstrecharge',
                "pre_balance" => floatval($parent_info['coin']),
                'totalcoin' => floatval($reward),
                "after_balance" => floatval(bcadd($parent_info['coin'], $reward,4)),
            ]);
        }
        // 活动赠送明细记录
        if($watnum>0 || $wattime>0) {
            M('activity_reward_log')->add([
                'type' => 2,
                'watnum' => intval($watnum),
                'wattime' => intval($wattime),
                'uid' => intval($parent_uid),
                'user_login' => $parent_info['user_login'],
                'user_type' => $parent_info['user_type'],
                'reward' => floatval($reward),
                'tenant_id' => intval($tenant_id),
                'ctime' => time(),
            ]);
        }
    }
    return true;
}

/* 清除聊天室配置缓存 */
function delChatRoomConfCache(){
    $redis=connectionRedis();
    $res = $redis->del('chatroomconf_');
    return $res;
}

/* 清除用户基本信息缓存 */
function delUserInfoCache($uid){
    $redis=connectionRedis();
    $res = $redis->del("userinfo_".$uid);
    return $res;
}

/* 清除用户有效vip信息缓存 */
function delUserVipInfoCache($tenant_id, $uid){
    $redis=connectionRedis();
    $res = $redis->hDel('user_vip_info_'.$tenant_id, $uid);
    $res = $redis->hDel('user_vip_checking_info_'.$tenant_id, $uid);
    return $res;
}

/* 获取用户有效vip */
function getUserVipInfo($uid, $tenant_id = null){
    $redis = connectionRedis();
    $tenant_id = $tenant_id ? $tenant_id : getUserInfo($uid)['tenant_id'];
    $config = getConfigPub($tenant_id);
    $users_vip_info = $redis->hGet('user_vip_info_'.$tenant_id, $uid);
    if(!$users_vip_info) {
        if ($config['vip_model'] == 1) {
            $users_vip_info = M("users_vip")
                ->where(['uid' => intval($uid), 'endtime' => ['egt', time()]])
                ->order('grade desc')
                ->find();
        } else {
            $users_vip_info = M("users_vip")
                ->where(['uid' => $uid, 'status' => ['in', [1,2]]])
                ->order('grade desc')
                ->find();
        }
        if($users_vip_info){
            $redis->hSet('user_vip_info_'.$tenant_id, $uid, json_encode($users_vip_info));
        }
    }else{
        $users_vip_info = json_decode($users_vip_info, true);
    }
    return $users_vip_info;
}

/* 获取用户正在审核的vip信息 */
function getUserVipCheckingInfo($tenant_id, $uid){
    $redis = connectionRedis();
    $tenant_id = $tenant_id ? $tenant_id : getUserInfo($uid)['tenant_id'];
    $config = getConfigPub($tenant_id);
    $check_info = $redis->hGet('user_vip_checking_info_'.$tenant_id, $uid);
    if(!$check_info) {
        if ($config['vip_model'] == 1) {

        } else {
            $check_info =M("users_vip")
                ->where(['uid' => $uid, 'status' => ['in', [4]]])
                ->order('grade desc')
                ->find();
        }
        if($check_info){
            $redis->hSet('user_vip_checking_info_'.$tenant_id, $uid, json_encode($check_info));
        }
    }else{
        $check_info = json_decode($check_info, true);
    }
    return $check_info;
}

/* 清除vip等级列表缓存 */
function delVipGradeListCache($tenant_id){
    $redis=connectionRedis();
    $res = $redis->del("vip_grade_list_".$tenant_id);
    return $res;
}

/* 获取vip等级列表 */
function getVipGradeList($tenant_id){
    $list=getcaches('vip_grade_list_'.$tenant_id);
    if(!$list){
        $list = M('vip_grade')->where(['tenant_id'=>intval($tenant_id)])->order('vip_grade asc')->select();
        $list = array_column($list,null,'vip_grade');
        setcaches('vip_grade_list_'.$tenant_id, $list, 60*60*24*7);
    }
    return $list;
}

/* 获取汇率列表 */
function getRateList($tenant_id){
    $list=getcaches('ratelist_'.$tenant_id);
    if(!$list){
        $list = M('rate')->where(['tenant_id'=>intval($tenant_id)])->order('sort asc, id asc')->select();
        $list = array_column($list,null,'code');
        setcaches('ratelist_'.$tenant_id, $list, 60*60*24*7);
    }
    return $list;
}

/* 清除汇率列表缓存 */
function delRateListCache($tenant_id){
    $redis=connectionRedis();
    $res = $redis->del("ratelist_".$tenant_id);
    return $res;
}

/* 清除国家表缓存 */
function delCountryListCache(){
    $redis=connectionRedis();
    $res = $redis->del("country_list");
    return $res;
}

/* 获取国家列表 */
function getCountryList(){
    $list = getcaches('country_list');
    if(!$list){
        $list = M('country')->where(1)->order('sort asc, id asc')->select();
        $list = array_column($list,null,'code');
        setcaches('country_list', $list, 60*60*24*7);
    }
    return $list;
}

/*
 * 获取国家code列表
 * */
function country_code_list(){
    $arr = include(dirname(__FILE__) .'/./country.php');
    return $arr;
}

/*
 * 创建订单号
 * */
function getOrderid($uid){
    $orderid=$uid.'_'.date('YmdHis').rand(100,999);
    return $orderid;
}
/* 设置代理详情缓存 */
function setAgentInfo($uid,$info){
    if(is_array($info) && $uid==$info['uid']){
        setcaches('agentinfo_'.$uid,$info);
        return true;
    }
    return false;
}
/* 删除代理详情缓存 */
function delAgentInfoCache($uid){
    $info=delcache('agentinfo_'.$uid);
    return $info;
}
/* 获取代理详情缓存 */
function getAgentInfo($uid){
    $info=getcaches('agentinfo_'.$uid);
    if(!$info){
        $info = M('users_agent')->where(['uid'=>intval($uid)])->find();
        setcaches('agentinfo_'.$uid,$info);
    }
    return $info;
}

/*
* 判断是否为json
* */
function is_json($string) {
    if(is_string($string)){
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    return false;
}

/*
 * js版本号
 * */
function jsversion($istime=false){
    if($istime==true){
        return time();
    }
    return 1.00027;
}

/*
 * css版本号
 * */
function cssversion($istime=false){
    if($istime==true){
        return time();
    }
    return 1.00027;
}

/*
 * 用户类型
 * */
function user_type_name($user_type){
    $arr = array(
        '1' => 'admin',
        '2' => '会员',
        '3' => '虚拟用户',
        '4' => '游客',
    );
    return isset($arr[$user_type]) ? $arr[$user_type] : $user_type;
}

/*
* 清除骑士缓存
* */
function delCarlistCache($tenant_id){
    delcache('carlist_'.$tenant_id);
}
/*
 * 获取坐骑列表
 * */
function get_carlist($tenant_id,$id=''){
    $list=getcaches('carlist_'.$tenant_id);
    if(!$list){
        if(enableGolangReplacePhp() === true){
            $url = goAdminUrl().goAdminRouter().'/widget_car/get_car_list_all';
            $http_post_map = [
                'tenant_id' => intval($tenant_id),
            ];
            $http_post_res = http_post($url, $http_post_map);
            $list = $http_post_res['Data'];
        }else {
            $list = M('car')->where(['tenant_id' => intval($tenant_id)])->order("orderno asc,id desc")->select();
        }
        if($list){
            setcaches('carlist_'.$tenant_id,$list);
        }
    }
    if($id){
        $carlist = array_column($list,null,'id');
        return isset($carlist[$id]) ? $carlist[$id] : [];
    }
    return $list;
}

/*
 * $data array 是二维数组，如：[['val'=>'reg_withdrawable_coin','prob'=>5], ['val'=>'reg_withdrawable_coin2','prob'=>15], ['val'=>'reg_withdrawable_coin2','prob'=>80]]
 * 根据传过来的概率返回随机的值
 * */
function getProbVal($data = array()){
    $pool = array();
    $count = 0;
    foreach ($data as $key=>$item){
        for($i=0;$i<$item['prob'];$i++){
            array_push($pool,$item['val']);
            $count++;
        }
    }
    return $count > 0 ? $pool[rand(0,$count-1)] : '';
}

/*
 * 时间字符串(00:01:50)转换为秒数(110)
 * */
function str_to_second($str=''){
    $arr = date_parse($str);
    $hour = $arr['hour'] ? $arr['hour'] : 0;
    $minute = $arr['minute'] ? $arr['minute'] : 0;
    $second = $arr['second'] ? $arr['second'] : 0;
    return !$str ? 0 : ($hour*60*60 + $minute*60 + $second);
}

/*
 * 秒数(110)转换为时间字符串(00:01:50)
 * */
function second_to_str($second=0){
    $hour = str_pad(floor($second / (60*60)),2,"0",STR_PAD_LEFT);
    $left = $second % (60*60);
    $minute = str_pad(floor($left / 60),2,"0",STR_PAD_LEFT);
    $left = $left % 60;
    $second = str_pad($left,2,"0",STR_PAD_LEFT);
    return $hour.':'.$minute.':'.$second;
}
/* 删除代理返佣详情缓存 */
function delAgentRebateConf($tenant_id){
    return delcache('agent_rebate_conf_'.$tenant_id);
}
/* 获取代理返佣详情缓存 */
function getAgentRebateConf($tenant_id){
    $info=getcaches('agent_rebate_conf_'.$tenant_id);
    if(!$info){
        $info = M('agent_proportion')->where(['tenant_id'=>intval($tenant_id)])->select();
        setcaches('agent_rebate_conf_'.$tenant_id,$info);
    }
    return $info;
}

/*
 * 获取选择的时间：
 * */
function get_timeselect()
{

    $data['today_start'] = date('Y-m-d 00:00:00');
    $data['today_end'] = date('Y-m-d 23:59:59');

    $data['ytoday_start'] = date('Y-m-d 00:00:00', strtotime('-1 day'));
    $data['ytoday_end'] = date('Y-m-d 23:59:59', strtotime('-1 day'));

    $data['tweek_start'] = date('Y-m-d 00:00:00', strtotime('-1 monday'));
    $data['tweek_end'] = date('Y-m-d 23:59:59', strtotime('sunday'));

    $data['yweek_start'] = date('Y-m-d 00:00:00', strtotime('-1 week', strtotime('-1 monday')));
    $data['yweek_end'] = date('Y-m-d 23:59:59', strtotime('-1 week', strtotime('sunday')));

    $data['tmonth_start'] = date('Y-m-d 00:00:00', strtotime(date("Y-m", time())));
    $data['tmonth_end'] = date('Y-m-d 23:59:59', strtotime('+1 month', strtotime($data['tmonth_start'])) - 1);

    $data['ymonth_start'] = date('Y-m-d 00:00:00', strtotime('-1 month', strtotime($data['tmonth_start'])));
    $data['ymonth_end'] = date('Y-m-d 23:59:59', strtotime($data['tmonth_start']) - 1);

    $data['month3_start'] = date('Y-m-d 00:00:00', strtotime('-2 month', strtotime($data['tmonth_start'])));
    $data['month3_end'] = $data['tmonth_end'];

    $data['month6_start'] = date('Y-m-d 00:00:00', strtotime('-5 month', strtotime($data['tmonth_start'])));
    $data['month6_end'] = $data['tmonth_end'];

    return $data;
}

/* 获取进入直播间公告 */
function getEnterroomNotice($tenant_id){
    $redis = connectionRedis();
    $info=getcaches('enterroom_notice_'.$tenant_id);
    if(!$info){
        $info = M('language')->where(['type'=>1,'tenant_id'=>intval($tenant_id)])->find();
        $redis->set('enterroom_notice_'.$tenant_id,json_encode($info),60*60*24*30);
    }
    return $info;
}

function getAuth($role_id,$rule_name){
    $info = M('auth_rule')->where(['title'=>$rule_name])->find();
    if(!empty($info) && isset($info['name'])){
        $authinfo = M('auth_access')->where(['role_id'=>$role_id,'rule_name'=>$info['name']])->find();
        if(!empty($authinfo)){
            return 1;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}

/*
 * 获取auth_access列表
 * */
function getAuthAccessList($roleId){
    $redis = connectionRedis();
    $list = $redis->hGet('auth_access_list', $roleId);
    $list = $list ? json_decode($list, true) : [];
    if(empty($list)){
        $list = M('auth_access')->field('role_id,rule_name,type')->where(['role_id'=>intval($roleId), 'type'=>'admin_url'])->select();
        if($list){
            foreach ($list as $key=>$val){
                $list[$key]['hash_key'] = md5($val['role_id'].$val['rule_name'].$val['type']);
            }
            $list = count($list) > 0 ? array_column($list,null, 'hash_key') : [];
            foreach ($list as $key=>$val){
                unset($list[$key]['hash_key']);
            }
            $redis->hSet('auth_access_list', $roleId, json_encode($list));
        }
    }
    return $list;
}

/*
 * 判断菜单路径是否有权限
 * return bool: false, true
 * */
function checkMenuPathAuth($MenuPath){
    $roleId = getRoleId();
    $MenuPath = strtolower(trim($MenuPath,'/'));
    $auth_access_list = getAuthAccessList($roleId);
    $hash_key = md5($roleId.$MenuPath.'admin_url');

    return isset($auth_access_list[$hash_key]) ? true : false;
}

/*
 * 页面按钮是否展示
 * */
function showHidden($MenuPath){
    $res = checkMenuPathAuth($MenuPath);
    return $res === true ? 'auth_access_show' : 'auth_access_hidden';
}

/*
 * 获取网络请求协议
 * */
function get_protocal(){
    return is_ssl() ? 'https' : 'http';
}

/*
 * 根据zone获取国家信息
 * */
function country($zone){
    $zone = is_numeric($zone) ? strval($zone) : $zone;
    $arr = include(dirname(__FILE__) .'/./country.php');
    return isset($arr[$zone]) ? $arr[$zone] : ["sc"=>"","code"=>'',"pinyin"=>"","en"=>"","locale"=>"","tc"=>""];
}
/**
 * 发帖数量查询
 */
function barNumLimited($uid){
    $userVip = getUserVipInfo($uid);
    if (empty($userVip)){
        $level_name  = 'vip0';
    }else{
        $vipInfo  = M('vip')
            ->where(['id' => $userVip['vip_id']])
            ->find();
        $level_name  = $vipInfo['name'];

    }
    $level_name_jurisdiction =  M('users_jurisdiction')->where(['grade' => $level_name])
        ->field('jurisdiction_id,bar_number,bar_slice_nuber')->find();
    return $level_name_jurisdiction;
}


function getUserField($field,$uid){
    $info =  M('users')->field($field)
        ->where(['id' => $uid])
        ->find();

    if ($info){
        return  $info[$field];
    }
    return '';

}
function play_or_download_url($type =1 ){
    $tenantId = getTenantIds();
    $playback_address_info =  M('playback_address')
        ->where("is_enable = 1 and type = '{$type}' and tenant_id = '{$tenantId}' ")
        ->find();

    return $playback_address_info;
}


/*
 * 线路分类
 * */
function ct_type_list(){
    return array(
        '1' => '默认',
        '2' => '黄播',
        '3' => '绿播',
        '4' => '赌播',
    );
}

/*
 * 清除推拉流多线路列表缓存
 * */
function delPushpullList(){
    return delcache('live_pushpull_list');
}

/*
 * 获取推拉流多线路列表缓存
 * */
function getPushpullList(){
    $list=getcaches('live_pushpull_list');
    if(!$list){
        $list = M('livepushpull')->where(['status'=>1])->order('id desc')->select();
        $list = array_column($list,null,'id');
        setcaches('live_pushpull_list', $list, 60*60*24*7);
    }
    return $list;
}

/*
 * 清除贵族等级列表缓存
 * */
function delNobleList($tenant_id){
    return delcache('live_noble_list_'.$tenant_id);
}


/*
 * 清除贵族配置缓存
 * */
function delNobleSetting($tenant_id){
    return delcache('live_noble_setting_'.$tenant_id);
}

/*
 * 获取贵族等级列表缓存
 * */
function getNobleList($tenant_id, $level = null){
    $list = getcaches('live_noble_list_'.$tenant_id);
    if(!$list){
        if(enableGolangReplacePhp() === true){
            $url = goAdminUrl().goAdminRouter().'/live_noble/get_noble_level_list_all';
            $http_post_map = [
                'tenant_id' => intval($tenant_id),
            ];
            $http_post_res = http_post($url, $http_post_map);
            $list = $http_post_res['Data'];
        }else{
            $list = M('noble')->where(['tenant_id'=>intval($tenant_id)])->order('level asc')->select();
        }
        $list = array_column($list,null,'level');
        setcaches('live_noble_list_'.$tenant_id, $list, 60*60*24*7);
    }
    if($level){
        return isset($list[$level]) ? $list[$level] : [];
    }
    return $list;
}

/*
 * 清除公聊皮肤表缓存
 * */
function delNobleSkinList($tenant_id){
    return delcache('live_noble_skin_'.$tenant_id);
}

/*
 * 获取公聊皮肤表缓存
 * */
function getNobleSkinList($tenant_id, $id = null){
    $list = getcaches('live_noble_skin_'.$tenant_id);
    if(!$list){
        if(enableGolangReplacePhp() === true){
            $url = goAdminUrl().goAdminRouter().'/live_noble/get_noble_skin_list_all';
            $http_post_map = [
                'tenant_id' => intval($tenant_id),
            ];
            $http_post_res = http_post($url, $http_post_map);
            $list = $http_post_res['Data'];
        }else {
            $list = M('noble_skin')->where(['tenant_id' => intval($tenant_id)])->order('id asc')->select();
        }
        $list = array_column($list,null,'id');
        setcaches('live_noble_skin_'.$tenant_id,$list);
    }
    if($id){
        return isset($list[$id]) ? $list[$id] : [];
    }
    return $list;
}


function time2string($second){
    $hour = floor($second/3600);
    $second = $second%3600;//除去整小时之后剩余的时间
    $minute = floor($second/60);
    $second = $second%60;//除去整分钟之后剩余的时间

    return $hour.':'.$minute.':'.$second;

}

/*
 * http post 请求转到
 * */
function http_post($url,$postData=[],$header=[],$timeOut = 15){
    if(strpos($url,'http') !== 0){
        return 'url 错误';
    }

    if(CustRedis::getInstance()->get('logapi_reqeuest_status') == 1){
        LogComplexModel::getInstance()->add(['url'=>$url, 'postData'=>$postData, 'header'=>$header, 'timeOut'=>$timeOut], '【后台 http_post 请求日志】');
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
    if(!empty($header)){
        curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($curl);
    curl_close($curl);

    $output = is_json($output) ? json_decode($output,true) : $output;
    return $output;
}

/*
 * http get 请求
 * */
function http_get($url, $header=[], $timeOut = 15){
    if(strpos($url,'http') !== 0){
        return 'url 错误';
    }

    if(CustRedis::getInstance()->get('logapi_reqeuest_status') == 1){
        LogComplexModel::getInstance()->add(['url'=>$url, 'header'=>$header, 'timeOut'=>$timeOut], '【后台 http_get 请求日志】');
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
    if(!empty($header)){
        curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($curl);
    curl_close($curl);

    $output = is_json($output) ? json_decode($output,true) : $output;
    return $output;
}


/*
 * http 请求转到 go
 * */
function http_to_go_call($url,$postData=[],$header=[],$timeOut = 5){
    if(strpos($url,'http') !== 0){
        return 'url 错误';
    }
    $postData = json_encode($postData);
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    // 执行后不直接打印出来
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    // 设置请求方式为post
    curl_setopt($ch,CURLOPT_POST,1);
    // post的变量
//    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
    // 请求头，可以传数组
    if(!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    }else{
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            )
        );
    }
    curl_setopt($ch, CURLOPT_HEADER,0);
    curl_setopt($ch,CURLOPT_TIMEOUT,$timeOut);
    // 跳过证书检查
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    // 不从证书中检查SSL加密算法是否存在
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

    $output=curl_exec($ch);
    curl_close($ch);

    $output = is_json($output) ? json_decode($output,true) : $output;
    return $output;
}


/*
 * 清除文件存储缓存
 * */
function delStorage(){
    return delcache('storage_info');
}

/*
 * 获取文件存储缓存
 * */
function getStorage(){
    $info = getcaches('storage_info');
    if(!$info){
        $data = M('options')->where(['option_name'=>'cmf_settings'])->find();
        $data = json_decode($data['option_value'],true);
        setcaches('storage_info',$data);
    }
    return $info;
}

/*
 * 清除文件存储列表缓存（新的）
 * */
function delFileStorage($tenant_id){
    return delcache('file_storage_list_'.$tenant_id);
}

/*
 * 获取文件存储列表缓存（新的）
 * */
function getlFileStorageList($tenant_id){
    $list = getcaches('file_storage_list_'.$tenant_id);
    if(!$list){
        $list = M('file_storage')->where(['status'=>1])->select();
        setcaches('file_storage_list_'.$tenant_id,$list);
    }
    return $list;
}

/*
 * 根据租户id获取直播列表
 * */
function getUserLiveList($tenant_id){
    $redis = connectionRedis();
    $cachekey = 'user_live_list_'.$tenant_id;
    $len = $redis->hLen($cachekey);
    if($len > 0){
        $list = $redis->hGetAll($cachekey);
        foreach ($list as $key => $val){
            $list[$key] = json_decode($val,true);
        }
    }else{
        $list = M('users_live')->where('islive = 1 and (tenant_id = '.intval($tenant_id).' or isshare = 1)')->select();
        foreach ($list as $key=>$val){
            $redis->hSet($cachekey,$val['uid'],json_encode($val));
        }
    }
    return $list;
}

/*
 * 根据租户id获取直播列表
 * */
function getUserLiveInfo($uid, $islive = 1){
    $info = M('users_live')->where(['uid'=>intval($uid),'islive'=>intval($islive)])->find();

    return $info;
}

/*
 * 设置直播列表缓存
 * */
function setUserLiveListCache($uid, $type = '', $num = 0){
    $num += 1;
    if($num > 5){
        return false;
    }
    $redis = connectionRedis();
    $info = M('users_live')->where(['uid'=>intval($uid),'islive'=>1])->find();
    if($info){
        if($info['isshare'] == 1){
            $tenantList = getTenantList();
            foreach ($tenantList as $key=>$val){
                $cachekey = 'user_live_list_'.$val['id'];
                $res = $redis->hSet($cachekey,$uid,json_encode($info));
            }
        }else{
            $cachekey = 'user_live_list_'.$info['tenant_id'];
            $res = $redis->hSet($cachekey,$uid,json_encode($info));
        }
        return isset($res) ? $res : false;
    }else{
        if($type != 'create'){
            $user_info = getUserInfo($uid);
            delUserLiveListCache($user_info['tenant_id'], $uid);
            return false;
        }

        $usleep_time = intval(0.2 * 1000000);
        usleep($usleep_time); // 延迟0.2秒
        $result = setUserLiveListCache($uid, $num); // 递归5次，防止因为从库同步主库数据不及时导致没有查到数据
        if($result !== false){
            return $result;
        }
        if($num == 1){
            $user_info = getUserInfo($uid);
            delUserLiveListCache($user_info['tenant_id'], $uid);
        }
        return false;
    }
}

/*
 * 移出redis直播列表缓存
 * */
function delUserLiveListCache($tenant_id, $uid){
    $redis = connectionRedis();
    $tenantList = getTenantList();
    if(count($tenantList) > 0){
        foreach ($tenantList as $key=>$val){
            $cachekey = 'user_live_list_'.$val['id'];
            $res = $redis->hDel($cachekey, $uid);
        }
    }else{
        $cachekey = 'user_live_list_'.$tenant_id;
        $res = $redis->hDel($cachekey, $uid);
    }
    return isset($res) ? $res : false;
}

/*
 * 根据租户id获取礼物列表
 * */
function getGiftList($tenant_id){
    $key = 'getGiftList_'.$tenant_id;
    $list = getcache($key);
    if(!$list){
        $list = M('gift')->field("*")->where(['tenant_id'=>intval($tenant_id)])->order("orderno asc,addtime desc")->select();
        setcaches($key, $list, 60*60*24*7);
    }
    return $list;
}

/*
 * 根据礼物id获取礼物详情
 * */
function getGiftInfo($tenant_id, $id){
    $list = getGiftList($tenant_id);
    $list = count($list) > 0 ? array_column($list, null, 'id') : [];
    $info = isset($list[$id]) ? $list[$id] : [];
    return $info;
}

/*
 * 设备列表
 * */
function client_list(){
    return array(
        '1'=>'PC',
        '2'=>'H5',
        '3'=>'Android',
        '4'=>'iOS'
    );
}

/*
 * golang后台admin接口路径
 * */
function goAdminRouter(){
    return '/admin/v1';
}

/*
 * golang后台admin接口地址
 * */
function goAdminUrl(){
    return trim(getSystemConf('go_admin_url'), '/');
}

/*
 * golang api app接口地址
 * */
function goAppUrl(){
    return trim(getSystemConf('go_app_url'), '/');
}

/*
 * 是否启用golang替换php代码（1.启用，0.不启用）
 * */
function enableGolangReplacePhp(){
    $enable_golang_replace_php = getSystemConf('enable_golang_replace_php');
    $go_admin_url = goAdminUrl();
    if($enable_golang_replace_php == 1 && $go_admin_url){
        return true;
    }else{
        return false;
    }
}


/**
 * 过滤文本中的emoji表情包（输出到excel文件中会导致问题）
 * @param string $text 原文本
 * @return string 过滤emoji表情包后的文本
 */
function removeEmoji($text){
    $len = mb_strlen($text);
    $newText = '';
    for($i=0;$i<$len;$i++){
        $str = mb_substr($text, $i, 1, 'utf-8');
        if(strlen($str) >= 4) continue;//emoji表情为4个字节
        $newText .= $str;
    }
    return $newText;
}

/*
 * rawurlencode
 *  emoji 处理
 * */
function cust_unicode($str){
    if(!function_exists('mb_strlen')){
        return rawurlencode($str);
    }
    $strEncode = '';
    $length = mb_strlen($str,'utf-8');
    for ($i=0; $i < $length; $i++) {
        $_tmpStr = mb_substr($str,$i,1,'utf-8');
        if(strlen($_tmpStr) >= 4){
            $strEncode .= rawurlencode($_tmpStr);
        }else{
            $strEncode .= $_tmpStr;
        }
    }
    return $strEncode;
}

/*
 * 清除kvconfig val redis缓存
 * */
function delKvconfigVal($tag, $key){
    $redis = connectionRedis();
    return $redis->hDel('kvconfig',$tag.'.'.$key);
}

/*
 * 获取kvconfig val redis缓存
 * */
function getKvconfigVal($tag, $key){
    $redis = connectionRedis();
    $val = $redis->hGet('kvconfig',$tag.'.'.$key);
    if(!$val){
        $info = M('kvconfig')->where(['tag'=>intval($tag), 'key'=>trim($key)])->find();
        if($info){
            $val = $info['val'];
            $redis->hSet('kvconfig',$tag.'.'.$key, $val);
            $redis->expire('kvconfig', 60*60*24*7);
        }
    }
    if(!$val){
        $val = '';
    }
    return $val;
}

/*
 * 获取系统配置
 * */
function getSystemConf($key){
    return getKvconfigVal(1, $key);
}

/**
 * 检测目录
 * @param  string $dir 上传目录
 * @return          // 检测结果，true-通过，否则-失败
 */
function checkSaveDir($dir){
    /* 检测并创建目录 */
    if(is_dir($dir)){
        return true;
    }
    if(mkdir($dir, 0755, true)){
        return true;
    } else {
        return "目录 {$dir} 创建失败！";
    }
}

/*
 * 币种列表
 * */
function currency_list(){
    $arr = array(
        'CNY' => array(
            'name' => '人民币',
            'code' => 'CNY',
            'is_virtual' => '0',
        ),
        'INR' => array(
            'name' => '印度卢比',
            'code' => 'INR',
            'is_virtual' => '0',
        ),
        'PHP' => array(
            'name' => '比索',
            'code' => 'PHP',
            'is_virtual' => '0',
        ),
        'USDT' => array(
            'name' => '泰达币',
            'code' => 'USDT',
            'is_virtual' => '1',
        ),
        'e-CNY' => array(
            'name' => '数字人民币',
            'code' => 'e-CNY',
            'is_virtual' => '0',
        ),
        'HKD' => array(
            'name' => '港币',
            'code' => 'HKD',
            'is_virtual' => '0',
        ),
    );
    return $arr;
}

function cash_account_type_list(){
    $arr = array(
        '1' => array(
            'name' => '银行卡',
            'type' => '1',
        ),
        '2' => array(
            'name' => 'USDT',
            'type' => '2',
        ),
    );
    return $arr;
}

function cash_network_type_list(){
    $arr = array(
        'TRC20',
        'ERC20',
    );
    return $arr;
}

function geturlType(){
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    return $http_type;

}
/*
 * 获取红包金额信息
 * */
function getRedinfo($list){
    //获取批次
    $redis = connectRedis();
    foreach ($list as $key=>$value) {
        $time_now = time();
        $timesend = date('YmdH', time());
        $red_send_key = 'red_send_' . $timesend . '_' . $value['id'];
        $red_sendmark_key = 'red_sendmark_' . $timesend . '_' . $value['id'];

        $redis->del($red_send_key);
        $redis->del($red_sendmark_key);
        // 计算基准设置的红包金额
        if ($value['red_total'] != '0' && $value['red_num'] != '0' && $value['money_max'] != '0' && $value['effect_time_start'] <= $time_now && $time_now <= $value['effect_time_end']) {
            $red_send = _getRandomNumberArray($value['red_num'], $value['red_total'], $value['money_min'], $value['money_max']);
            foreach ($red_send as $k => $money) {
                $redis->lPush($red_send_key, $money);
            }
            $redis->expire($red_send_key, 60 * 60 * 24);
            setcaches($red_sendmark_key, 1, 60 * 60 * 24);
        }


        // 计算不同等级设置的红包金额
        $vip_conf = json_decode($value['vip_conf'], true);
        $vip_conf = is_array($vip_conf) ? $vip_conf : [];
        foreach ($vip_conf as $k=>$v){
            $red_send_k = 'red_send_'.$timesend.'_'.$value['id'].'_'.trim($k,'vip_grade_');
            $red_sendmark_k = 'red_sendmark_'.$timesend.'_'.$value['id'].'_'.trim($k,'vip_grade_');

            $redis->del($red_send_k);
            $redis->del($red_sendmark_k);
            if($value['effect_time_start'] > $time_now || $time_now > $value['effect_time_end']){
                continue;
            }
            if($v['red_total'] == '0' || $v['red_num'] == '0' || $v['money_max'] == '0'){
                continue;
            }
            $vip_grade_red_send = _getRandomNumberArray($v['red_num'], $v['red_total'], $v['money_min'], $v['money_max']);
            foreach ($vip_grade_red_send as $vip_grade_red_send_k => $vip_grade_red_send_money) {
                $redis->lPush($red_send_k, $vip_grade_red_send_money);
            }
            $redis->expire($red_send_k,60*60*24);
            setcaches($red_sendmark_k,1,60*60*24);
        }
    }
}

function _getRandomNumberArray($times,$total , $min, $max)
{
    $data = array();
    if ($min * $times > $total) {
        return array();
    }
    if ($max * $times < $total) {
        return array();
    }
    while ($times >= 1) {
        $times--;
        $kmix = max($min, $total - $times * $max);
        $kmax = min($max, $total - $times * $min);
        $kAvg = $total / ($times + 1);
        //获取最大值和最小值的距离之间的最小值
        $kDis = min($kAvg - $kmix, $kmax - $kAvg);
        //获取0到1之间的随机数与距离最小值相乘得出浮动区间，这使得浮动区间不会超出范围
        $r = ((float)(rand(1, 10000) / 10000) - 0.5) * $kDis * 2;
        $k = round($kAvg + $r);
        $total -= $k;
        $data[] = abs($k);
    }
    return $data;
}


/*
 * 获取用户状态列表
 * */
function user_status_list(){
    $list = array(
        '0' => array(
            'name' => '禁用',
            'color' => '#f00',
        ),
        '1' => array(
            'name' => '正常',
            'color' => '#090',
        ),
        '2' => array(
            'name' => '未验证',
            'color' => '#999',
        ),
    );
    return $list;
}

/*
 * 获取用户类型列表
 * */
function user_type_list(){
    $list = array(
        '2' => array(
            'name' => '真实用户',
            'type' => '2',
            'color' => '#090',
        ),
        '3' => array(
            'name' => '虚拟用户',
            'type' => '3',
            'color' => '#999',
        ),
        '4' => array(
            'name' => '游客',
            'type' => '4',
            'color' => '#999',
        ),
        '5' => array(
            'name' => '包装用户',
            'type' => '5',
            'color' => '#8a522d',
        ),
        '6' => array(
            'name' => '代管账号',
            'type' => '6',
            'color' => '#8a522d',
        ),
        '7' => array(
            'name' => '测试账号',
            'type' => '7',
            'color' => '#0b0908',
        ),
    );
    return $list;
}

/*
 * 余额类型列表
 * */
function balance_type_list(){
    $list = array(
        '1' => array(
            'name' => '可提现',
            'type' => '1',
        ),
        '2' => array(
            'name' => '不可提现',
            'type' => '2',
        ),
    );
    return $list;
}

/*
 * 手动充值，业务类型列表
 * */
function business_type_list(){
    $list = array(
        '1' => array(
            'name' => '充值优惠-手工',
            'type' => '1',
        ),
        '2' => array(
            'name' => '手工充值',
            'type' => '2',
        ),
        '3' => array(
            'name' => '代理返点-手工',
            'type' => '3',
        ),
        '4' => array(
            'name' => '返水优惠-手工',
            'type' => '4',
        ),
        '5' => array(
            'name' => '其他优惠',
            'type' => '5',
        ),
        '6' => array(
            'name' => '异常加减分',
            'type' => '6',
        ),
        '7' => array(
            'name' => '商城保证金',
            'type' => '7',
        ),
        '8' => array(
            'name' => '代理人奖励',
            'type' => '8',
        ),
    );
    return $list;
}

/**
 * 获取任务分类ID
 * @param $key
 * @return int
 */
function getTaskConfig($key){
    $task = [
        'task_1'=>1,
        'task_2'=>2,
        'task_3'=>3,
        'task_4'=>4,
        'task_5'=>5,
    ];
    return $task[$key];
}

/*
* 短视频审核奖励
* */
function ShortVideoCheckReward($videoInfo, $userInfo, $vipInfo){
    if (!$videoInfo || !$userInfo || !$vipInfo || $vipInfo['uplode_video_amount'] <= 0) {
        return false;
    }
    if ($userInfo['upload_video_profit_status'] == 0) { // 判断 上传视频收益 是否开启
        return false;
    }
    $config = getConfigPub($userInfo['tenant_id']);

    $video_uplode_reward_map = ['uid' => $videoInfo['uid']];
    if($vipInfo['video_upload_reward_type'] == 2){ // 奖励模式：1.有偿上传视频数量，2.每天
        $video_uplode_reward_map['add_time'] = ['between', [strtotime(date('Y-m-d 00:00:00')),strtotime(date('Y-m-d 23:59:59'))]];
    }
    $VideoAwardCount = M("video_uplode_reward")->where($video_uplode_reward_map)->count();
    $rewardInfo = M('video_uplode_reward')->where(['video_type' => 1, 'video_id' => $videoInfo['id']])->find();
    $rewardInfo = [];
    if (empty($rewardInfo) && $VideoAwardCount < $vipInfo['uplode_video_num']) { // 该视频没有获取过奖励，并且奖励次数小于配置的次数
        if ($config['video_uplode_amount_type'] == 1) { // 可提现
            M("users")->where(['id' => $videoInfo['uid']])->setInc('coin', $vipInfo['uplode_video_amount']);
            $pre_balance = $userInfo['coin'];
            $after_balance = bcadd($userInfo['coin'], $vipInfo['uplode_video_amount'],4);
            $is_withdrawable = 1;
            $type = 'income';
        } else {
            M("users")->where(['id' => $videoInfo['uid']])->setInc('nowithdrawable_coin', $vipInfo['uplode_video_amount']);
            $pre_balance = $userInfo['nowithdrawable_coin'];
            $after_balance = bcadd($userInfo['nowithdrawable_coin'], $vipInfo['uplode_video_amount'],4);
            $is_withdrawable = 2;
            $type = 'income_nowithdraw';
            $redis = connectRedis();
            $keytime = time();
            $redis->lPush($videoInfo['uid'] . '_reward_time', $keytime);// 存用户 时间数据key
            $amount = $redis->get($videoInfo['uid'] . '_' . $keytime);
            $totalAmount = bcadd($vipInfo['uplode_video_amount'], $amount, 2);
            $redis->set($videoInfo['uid'] . '_' . $keytime, $totalAmount);// 存佣金
            $expireTime = time() + $config['withdrawal_time'] * 86400;
            $redis->expireAt($videoInfo['uid'] . '_' . $keytime, $expireTime);// 设置过去时间
        }
        delUserInfoCache($videoInfo['uid']);
        $rewardData = [
            'video_id' => $videoInfo['id'],
            'video_type' => 1,
            'uid' => $videoInfo['uid'],
            'user_type' => $userInfo['user_type'],
            'add_time' => time(),
            'price' => $vipInfo['uplode_video_amount'],
        ];
        $reward_id = M('video_uplode_reward')->add($rewardData);
        $coinrecordData = [
            'type' => $type,
            'uid' => $videoInfo['uid'],
            'user_type' => $userInfo['user_type'],
            'user_login' => $userInfo['user_login'],
            'giftid' => $reward_id,
            'addtime' => time(),
            'tenant_id' => $userInfo['tenant_id'],
            'action' => 'video_uplode_reward',
            "pre_balance" => floatval($pre_balance),
            'totalcoin' => $vipInfo['uplode_video_amount'],//金额
            "after_balance" => floatval($after_balance),
            "giftcount" => 1,
            'is_withdrawable' => $is_withdrawable,
             "order_id"=> generater(),
        ];
        UsersCoinrecordModel::getInstance()->addCoinrecord($coinrecordData);
        ShortVideoAgencyCommission($videoInfo['uid'], $vipInfo['uplode_video_amount'], $reward_id, $type, $is_withdrawable, 'agent_video_uplode_reward', 4);
    }
    return true;
}

function ShortVideoCheckRewardBytime($videoInfo, $userInfo){
    $config = getConfigPub($userInfo['tenant_id']);

    $video_uplode_reward_map = ['uid' => $videoInfo['uid']];

   // $VideoAwardCount = M("video_uplode_reward")->where($video_uplode_reward_map)->count();
    $rewardInfo = M('video_uplode_reward')->where(['video_type' => 1, 'video_id' => $videoInfo['id']])->find();
    if (empty($rewardInfo)) {
        $playTimeIntArray = explode(':',$videoInfo['duration']);
        $playTimeInt = $playTimeIntArray[0] * 60*60;
        $playTimeInt+= $playTimeIntArray[1] *60;
        $playTimeInt+= $playTimeIntArray[3];
        $reward=  M('cmf_uplode_video_rules')->where('min_time','<=',$playTimeInt)->where('max_time','>=',$playTimeInt)->find();
        if ($reward){
            return true;
        }
        if ($config['video_uplode_amount_type'] == 1) { // 可提现
            M("users")->where(['id' => $videoInfo['uid']])->setInc('coin', $reward['amount']);
            $pre_balance = $userInfo['coin'];
            $after_balance = bcadd($userInfo['coin'], $reward['amount'],4);
            $is_withdrawable = 1;
            $type = 'income';
        } else {
            M("users")->where(['id' => $videoInfo['uid']])->setInc('nowithdrawable_coin', $reward['amount']);
            $pre_balance = $userInfo['nowithdrawable_coin'];
            $after_balance = bcadd($userInfo['nowithdrawable_coin'], $reward['amount'],4);
            $is_withdrawable = 2;
            $type = 'income_nowithdraw';
            $redis = connectRedis();
            $keytime = time();
            $redis->lPush($videoInfo['uid'] . '_reward_time', $keytime);// 存用户 时间数据key
            $amount = $redis->get($videoInfo['uid'] . '_' . $keytime);
            $totalAmount = bcadd($reward['amount'], $amount, 2);
            $redis->set($videoInfo['uid'] . '_' . $keytime, $totalAmount);// 存佣金
            $expireTime = time() + $config['withdrawal_time'] * 86400;
            $redis->expireAt($videoInfo['uid'] . '_' . $keytime, $expireTime);// 设置过去时间
        }
        delUserInfoCache($videoInfo['uid']);
        $rewardData = [
            'video_id' => $videoInfo['id'],
            'video_type' => 1,
            'uid' => $videoInfo['uid'],
            'user_type' => $userInfo['user_type'],
            'add_time' => time(),
            'price' => $reward['amount'],
        ];
        $reward_id = M('video_uplode_reward')->add($rewardData);
        $coinrecordData = [
            'type' => $type,
            'uid' => $videoInfo['uid'],
            'user_type' => $userInfo['user_type'],
            'user_login' => $userInfo['user_login'],
            'giftid' => $reward_id,
            'addtime' => time(),
            'tenant_id' => $userInfo['tenant_id'],
            'action' => 'video_uplode_reward',
            "pre_balance" => floatval($pre_balance),
            'totalcoin' =>$reward['amount'],//金额
            "after_balance" => floatval($after_balance),
            "giftcount" => 1,
            'is_withdrawable' => $is_withdrawable,
        ];
        UsersCoinrecordModel::getInstance()->addCoinrecord($coinrecordData);
        ShortVideoAgencyCommission($videoInfo['uid'], $reward['amount'], $reward_id, $type, $is_withdrawable, 'agent_video_uplode_reward', 4);
    }
    return true;
}

/**
 * 短视频审核代理分成
 * @param $uid 用户id
 * @param $price  金额
 * @param $giftid  操作id
 * @param $type  //  income 可提现 ， income_nowithdraw不可提余额
 * @param $is_withdrawable  1可提现  2  不可提现
 * @param $action   agent_buy_video  购买视频代理收益
 * @param $agentType    1  任务  ，2 购买视频  ， 3 点赞视频，4 上传视频'
 * @return bool
 */
function ShortVideoAgencyCommission($uid,$price,$giftid, $type,$is_withdrawable,$action,$agentType ){
    $userinfo=getUserInfo($uid);
    $RebateConf = getAgentRebateConf($userinfo['tenant_id']);
    if(!$RebateConf){
        return  true;
    }
    $RebateConfByLevel = array_column($RebateConf,null,'agent_level');

    $config = getConfigPub($userinfo['tenant_id']);
    if (!$config['agent_sum']){
        return  true;
    }

    $uids = explode(',',$userinfo['pids']);
    unset($uids[0]);
    if (empty($uids)){
        return  true;
    }
    if ($config['agent_sum']< count($uids)){
        $uids = array_slice($uids,-$config['agent_sum']);
    }

    $uids  =  array_reverse($uids);
    $i = 0;
    $redis = connectRedis();
    foreach ($uids as $key =>$value){
        $agentuser_vip_info = getUserVipInfo($value);
        if (!empty($agentuser_vip_info) && in_array($agentuser_vip_info['status'], [2])) { // 退保证金申请中不能有收益，也不能抢红包，也不能理返点
            continue;
        }
        $agentInfo = UsersModel::getInstance()->getUserInfoWithIdAndTid($value);
        if ($agentInfo['rebate_status'] == 0) { // 判断 代理返点 是否开启
            continue;
        }
        if($agentInfo['user_type'] == 7){ // 测试账号，不做逻辑处理，直接返回
            continue;
        }
        $rebate = bcmul($price,$RebateConfByLevel[$key+1]['rate']/100,2);
        if ($rebate> 0) {
            $agentData['uid'] = $uid;
            $agentData['pid'] = $value;
            $agentData['addtime'] = time();
            $agentData['level'] = $key + 1;
            $agentData['type'] = $agentType;
            $agentData['operation_id'] = $giftid;
            $agentData['status'] = 1;
            $agentData['total_amount'] = $price;
            $agentData['rate'] = $RebateConfByLevel[$key+1]['rate'];
            $agentData['amount'] = $rebate;
            $agentData['tenant_id'] = $agentInfo['tenant_id'];
            if ($is_withdrawable == 1) {
                M('users')->where(['id' => $value])->save(['agent_total_income' => ['exp', 'agent_total_income+' . $rebate], 'coin' => ['exp', 'coin+' . $rebate]]);
            } else {
                M('users')->where(['id' => $value])->save(['agent_total_income' => ['exp', 'agent_total_income+' . $rebate], 'nowithdrawable_coin' => ['exp', 'nowithdrawable_coin+' . $rebate]]);
                $keytime = time();
                $redis->lPush($value . '_reward_time', $keytime);// 存用户 时间数据key
                $amount = $redis->get($value . '_' . $keytime);
                $totalAmount = bcadd($rebate, $amount, 2);
                $redis->set($value . '_' . $keytime, $totalAmount);// 存佣金
                $expireTime = time() + $config['withdrawal_time'] * 86400;
                $redis->expireAt($value . '_' . $keytime, $expireTime);// 设置过去时间
            }
            $agentRewardId = M('agent_reward')->add($agentData);// 代理记录
            $coinrecordData = [
                'type' => $type,
                'uid' => $value,
                'user_type' => $agentInfo['user_type'],
                'user_login' => $agentInfo['user_login'],
                'giftid' => $agentRewardId,
                'addtime' => time(),
                'tenant_id' => $agentInfo['tenant_id'],
                'action' => $action,
                "pre_balance" => floatval($agentInfo['coin']),
                'totalcoin' => $rebate,//金额
                "after_balance" => floatval(bcadd($agentInfo['coin'], $rebate,4)),
                "giftcount" => 1,
                'is_withdrawable' => $is_withdrawable,
            ];
            UsersCoinrecordModel::getInstance()->addCoinrecord($coinrecordData);//  账变记录
            delUserInfoCache($value);
            $i++;
        }
    }
    return true;
}

/*
 * 视频缓存保存数量
 * */
function videoCacheCount($count=100000){
    return $count;
}

/*
 * 图片域名替换
 * */
function str_domain_replace($str){
    $str = str_replace('v8nnq4ihe8.alazhuren.com','d103tsstj71841.cloudfront.net',$str);
    $str = str_replace('duqugapq.lfgtah.cn','duqugapq.dazhuangip.com',$str);
    $str = str_replace('dhqgga.lfgtah.cn','duqugapq.dazhuangip.com',$str);
    $str = str_replace('liveprod-new.jxmm168.com','y7dmnxklfp1w.dazhuangip.com',$str);
    $str = str_replace('duqugapq.dazhuangip.com','ypt39w38q34k.wannice.com',$str);
    return $str;
}

/*
 * 请求足球视频直播列表接口
 * */
function getFootballLiveList($football_live_base_url, $football_live_token){
    if(!$football_live_base_url || !$football_live_token){
        return array();
    }
    $url = trim($football_live_base_url, '/').'/soccer/api/live/video';
    $url .= '?is_streaming=1&time_stamp='.time();
    $http_get_res = http_get($url, ['token:'.trim($football_live_token)]);

    if(isset($http_get_res['code']) && $http_get_res['code'] == 0 && isset($http_get_res['result'])){
        return $http_get_res['result'];
    }else{
        LogComplexModel::getInstance()->add(['url'=>$url, 'header'=>['token:'.trim($football_live_token)], $http_get_res], '【请求足球视频直播列表接口日志】');
        $log_url = get_protocal()."://".$_SERVER['HTTP_HOST'].'/Api/LogComplex/add_complex';
        $data = array(
            'ct' => json_encode(array('url'=>$url, 'header'=>['token:'.trim($football_live_token)], $http_get_res), JSON_UNESCAPED_UNICODE),
            'remark' => '【请求足球视频直播列表接口日志 selfnode】',
        );
        $result = http_post($log_url, $data);
//        echo $log_url.' | '.json_encode($data, JSON_UNESCAPED_UNICODE)."\n";
        return array();
    }
}

/*
 * 请求足球视频直播详情接口
 * */
function getFootballLiveInfo($football_live_base_url, $football_live_token, $match_id){
    if(!$football_live_base_url || !$football_live_token || !$match_id){
        return [];
    }
    $url = trim($football_live_base_url, '/').'/soccer/api/live/video';
    $url .= '?is_streaming=1&time_stamp='.time().'&match_id='.$match_id;
    $http_get_res = http_get($url, ['token:'.trim($football_live_token)]);
    if(isset($http_get_res['code']) && $http_get_res['code'] == 0 && isset($http_get_res['result']) && count($http_get_res['result']) > 0){
        return $http_get_res['result'][0];
    }else{
        return [];
    }
}
function generater(){
    $danhao = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return $danhao;
}