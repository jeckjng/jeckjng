<?php

/**
 * Menu(菜单管理)
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Admin\Model\MenuAuthRuleActionModel;

class MenuController extends AdminbaseController {

    protected $menu_model;
    protected $auth_rule_model;

    private $sign_key = '230eb23718974713afa2eb12002d70b6-c11301ff38b407c4510a3cc42abf4419-ADCAFD57C5A9A808DB3DC2B90CB3B80F-66ACC0D685695CD8C9520D19F0E2BECC';

    function _initialize() {
        parent::_initialize();
        $this->menu_model = D("Common/Menu");
        $this->auth_rule_model = D("Common/AuthRule");
    }

    /**
     *  显示菜单
     */
    public function index() {
    	$_SESSION['admin_menu_index']="Menu/index";
        $result = $this->menu_model->order(array("listorder" => "ASC"))->select();
        import("Tree");
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        
        $newmenus=array();
        foreach ($result as $m){
        	$newmenus[$m['id']]=$m;
        	 
        }
        foreach ($result as $n=> $r) {
        	
        	$result[$n]['level'] = $this->_get_level($r['id'], $newmenus);
        	$result[$n]['parentid_node'] = ($r['parentid']) ? ' class="child-of-node-' . $r['parentid'] . '"' : '';
        	
            $result[$n]['str_manage'] = '<a href="' . U("Menu/add", array("parentid" => $r['id'], "menuid" => I("get.menuid"))) . '">'.L('ADD_SUB_MENU').'</a> | <a target="_blank" href="' . U("Menu/edit", array("id" => $r['id'], "menuid" => I("get.menuid"))) . '">'.L('EDIT').'</a> | <a class="js-ajax-delete del_color" href="' . U("Menu/delete", array("id" => $r['id'], "menuid" => I("get.menuid")) ). '">'.L('DELETE').'</a> ';
            $result[$n]['status'] = $r['status'] ? L('DISPLAY') : L('HIDDEN');
            if(APP_DEBUG){
            	$result[$n]['app']=$r['app']."/".$r['model']."/".$r['action'];
            }
        }

        $tree->init($result);
        $str = "<tr id='node-\$id' \$parentid_node>
					<td style='padding-left:20px;'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
					<td>\$id</td>
        			<td>\$app</td>
					<td>\$spacer\$name</td>
				    <td>\$status</td>
					<td>\$str_manage</td>
				</tr>";
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        $this->display();
    }
    
    /**
     * 获取菜单深度
     * @param $id
     * @param $array
     * @param $i
     */
    protected function _get_level($id, $array = array(), $i = 0) {
    
    	if ($array[$id]['parentid']==0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid']==$id){
    		return  $i;
    	}else{
    		$i++;
    		return $this->_get_level($array[$id]['parentid'],$array,$i);
    	}
    
    }
    
    public function lists(){
    	$_SESSION['admin_menu_index']="Menu/lists";
    	$result = $this->menu_model->order(array("app" => "ASC","model" => "ASC","action" => "ASC"))->select();
    	$this->assign("menus",$result);
    	$this->display();
    }

    /**
     *  添加
     */
    public function add() {
    	import("Tree");
    	$tree = new \Tree();
    	$parentid = intval(I("get.parentid"));
    	$result = $this->menu_model->order(array("listorder" => "ASC"))->select();
    	foreach ($result as $r) {
    		$r['selected'] = $r['id'] == $parentid ? 'selected' : '';
    		$array[] = $r;
    	}
    	$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
    	$tree->init($array);
    	$select_categorys = $tree->get_tree(0, $str);
    	$this->assign("select_categorys", $select_categorys);
    	$this->display();
    }
    
    /**
     *  添加
     */
    public function add_post() {
    	if (IS_POST) {
    		if ($this->menu_model->create()) {
    			if ($this->menu_model->add()!==false) {
    				$app=I("post.app");
    				$model=I("post.model");
    				$action=I("post.action");
    				$name=strtolower("$app/$model/$action");
    				$menu_name=I("post.name");
    				$mwhere=array("name"=>$name);
    				
    				$find_rule=$this->auth_rule_model->where($mwhere)->find();
    				if(!$find_rule){
    					$this->auth_rule_model->add(array("name"=>$name,"module"=>$app,"type"=>"admin_url","title"=>$menu_name));//type 1-admin rule;2-user rule
    				}
    				$to=empty($_SESSION['admin_menu_index'])?"Menu/index":$_SESSION['admin_menu_index'];
                    // 保存操作sql，用于同步菜单
                    $menu_res = MenuAuthRuleActionModel::getInstance()->addAll(array(
                        array(
                            'action_sql' => $this->menu_model->getLastSql(),
                            'action_type' => 1, // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                            'table_name' => 'menu',
                        ),
                        array(
                            'action_sql' => $this->auth_rule_model->getLastSql(),
                            'action_type' => 1, // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                            'table_name' => 'auth_rule',
                        ),
                    ), 1);
                    $action="添加菜单";
                    setAdminLog($action);
    				$this->success("添加成功！", U($to));
    			} else {
    				$this->error("添加失败！");
    			}
    		} else {
    			$this->error($this->menu_model->getError());
    		}
    	}
    }

    /**
     *  删除
     */
    public function delete() {
        $id = intval(I("get.id"));
        $count = $this->menu_model->where(array("parentid" => $id))->count();
        if ($count > 0) {
            $this->error("该菜单下还有子菜单，无法删除！");
        }
        $info = $this->menu_model->where(array("id" => intval($id)))->find();
        if(!$info){
            $this->error("找不到该菜单");
        }
        if ($this->menu_model->delete($id)!==false) {
            $auth_rule = M('auth_rule');
            $auth_rule->where(["name"=>strtolower($info['app']."/".$info['model']."/".$info['action'])])->delete();
            $action="删除菜单：{$id}";
            setAdminLog($action);
            // 保存操作sql，用于同步菜单
            $menu_res = MenuAuthRuleActionModel::getInstance()->addAll(array(
                array(
                    'action_sql' => $this->menu_model->getLastSql(),
                    'action_type' => 3, // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                    'table_name' => 'menu',
                ),
                array(
                    'action_sql' => $auth_rule->getLastSql(),
                    'action_type' => 3, // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                    'table_name' => 'auth_rule',
                ),
            ), 1);
            $this->success("删除菜单成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     *  编辑
     */
    public function edit() {
        import("Tree");
        $tree = new \Tree();
        $id = intval(I("get.id"));
        $rs = $this->menu_model->where(array("id" => $id))->find();
        $result = $this->menu_model->order(array("listorder" => "ASC"))->select();
        foreach ($result as $r) {
        	$r['selected'] = $r['id'] == $rs['parentid'] ? 'selected' : '';
        	$array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $select_categorys = $tree->get_tree(0, $str);
        $this->assign("data", $rs);
        $this->assign("select_categorys", $select_categorys);
        $this->display();
    }
    
    /**
     *  编辑
     */
    public function edit_post() {
    	if (IS_POST) {
    		if ($this->menu_model->create()) {
    			if ($this->menu_model->save() !== false) {
    				$app=I("post.app");
    				$model=I("post.model");
    				$action=I("post.action");
    				$name=strtolower("$app/$model/$action");
    				$menu_name=I("post.name");
    				$mwhere=array("name"=>$name);
    				
    				$find_rule=$this->auth_rule_model->where($mwhere)->find();
    				if(!$find_rule){
    					$this->auth_rule_model->add(array("name"=>$name,"module"=>$app,"type"=>"admin_url","title"=>$menu_name));//type 1-admin rule;2-user rule
    				}else{
    					$this->auth_rule_model->where($mwhere)->save(array("name"=>$name,"module"=>$app,"type"=>"admin_url","title"=>$menu_name));//type 1-admin rule;2-user rule
    				}
    				// 保存操作sql，用于同步菜单
                    $menu_res = MenuAuthRuleActionModel::getInstance()->addAll(array(
                                    array(
                                        'action_sql' => $this->menu_model->getLastSql(),
                                        'action_type' => 2, // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                                        'table_name' => 'menu',
                                    ),
                                    array(
                                        'action_sql' => $this->auth_rule_model->getLastSql(),
                                        'action_type' => !$find_rule ? 1 : 2, // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                                        'table_name' => 'auth_rule',
                                    ),
                                ), 1);
    				$action="编辑菜单：{$_POST['id']}";
                    setAdminLog($action);
    				$this->success("更新成功！");
    			} else {
    				$this->error("更新失败！");
    			}
    		} else {
    			$this->error($this->menu_model->getError());
    		}
    	}
    }

    //排序
    public function listorders() {
//        $status = parent::_listorders($this->menu_model);
        if ($_POST['listorders']) {
            $model = M('menu');
            $pk = $model->getPk(); //获取主键名称
            $ids = $_POST['listorders'];
            foreach ($ids as $key => $r) {
                $data['listorder'] = $r;
                $res = $model->where(array($pk => $key))->save($data);
                if($res){
                    // 保存操作sql，用于同步菜单
                    $menu_res = MenuAuthRuleActionModel::getInstance()->addAll(array(
                        array(
                            'action_sql' => $model->getLastSql(),
                            'action_type' => 2, // 操作类型：1.INSERT，2.UPDATE，3.DELETE
                            'table_name' => 'menu',
                        ),
                    ), 1);
                }
            }
            $action="更新菜单排序";
            setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
    
    public function spmy_export_menu(){
    	$menus=$this->menu_model->get_menu_tree(0);
    	
    	$menus_str= var_export($menus,true);
    	$menus_str=preg_replace("/\s+\d+\s=>\s(\n|\r)/", "\n", $menus_str);

    	foreach ($menus as $m){
    		$app=$m['app'];
    		$menudir=SPAPP.$app."/Menu";
    		if(!file_exists($menudir)){
    			mkdir($menudir);
    		}
    		$model=strtolower($m['model']);
    		
    		$menus_str= var_export($m,true);
    		$menus_str=preg_replace("/\s+\d+\s=>\s(\n|\r)/", "\n", $menus_str);
    		
    		file_put_contents($menudir."/admin_$model.php", "<?php\nreturn $menus_str;");
    		
    	}
    	$this->display("export_menu");
    }
    
    public function spmy_export_menu_lang(){
    	$apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
    	foreach ($apps as $app){
    		if(is_dir(SPAPP.$app)){
    			$lang_dirs=sp_scan_dir(SPAPP."$app/Lang/*",GLOB_ONLYDIR);
    			
    			$menus = $this->menu_model->where(array("app"=>$app))->order(array("listorder"=>"ASC","app" => "ASC","model" => "ASC","action" => "ASC"))->select();
    			foreach ($lang_dirs as $lang_dir){
    				$admin_menu_lang_file=SPAPP.$app."/Lang/".$lang_dir."/admin_menu.php";
    				$lang=array();
    				if(is_file($admin_menu_lang_file)){
    					$lang=include $admin_menu_lang_file;
    				}
    				
    				foreach ($menus as $menu){
    					$lang_key=strtoupper($menu['app'].'_'.$menu['model'].'_'.$menu['action']);
    					if(!isset($lang[$lang_key])){
    						$lang[$lang_key]=$menu['name'];
    					}
    				}
    				
    				$lang_str= var_export($lang,true);
    				$lang_str=preg_replace("/\s+\d+\s=>\s(\n|\r)/", "\n", $lang_str);
    		
    				file_put_contents($admin_menu_lang_file, "<?php\nreturn $lang_str;");
    			}
    			
    		}
    	}
    	
    	echo "success!";
    }
    /* public function dev_import_menu(){
    	$menus=F("Menu");
    	if(!empty($menus)){
    		$table_menu=C('DB_PREFIX')."menu";
    		$this->menu_model->execute("TRUNCATE TABLE $table_menu;");
    		 
    		foreach($menus as $menu){
    			$this->menu_model->add($menu);
    		}
    	}
    	
    	$this->display();
    } */
    
    private function _import_menu($menus,$parentid=0,&$error_menus=array()){
    	foreach ($menus as $menu){
    	
    		$app=$menu['app'];
    		$model=$menu['model'];
    		$action=$menu['action'];
    			
    		$where['app']=$app;
    		$where['model']=$model;
    		$where['action']=$action;
    		$children=isset($menu['children'])?$menu['children']:false;
    		unset($menu['children']);
    		$find_menu=$this->menu_model->where($where)->find();
    		if($find_menu){
    			$newmenu=array_merge($find_menu,$menu);
    			$result=$this->menu_model->save($newmenu);
    			if($result===false){
    				$error_menus[]="$app/$model/$action";
    				$parentid2=false;
    			}else{
    				$parentid2=$find_menu['id'];
    			}
    		}else{
    			$menu['parentid']=$parentid;
    			$result=$this->menu_model->add($menu);
    			if($result===false){
    				$error_menus[]="$app/$model/$action";
    				$parentid2=false;
    			}else{
    				$parentid2=$result;
    			}
    		}
    		
    		$name=strtolower("$app/$model/$action");
    		$mwhere=array("name"=>$name);
    		
    		$find_rule=$this->auth_rule_model->where($mwhere)->find();
    		if(!$find_rule){
    			$this->auth_rule_model->add(array("name"=>$name,"module"=>$app,"type"=>"admin_url","title"=>$menu['name']));//type 1-admin rule;2-user rule
    		}else{
    			$this->auth_rule_model->where($mwhere)->save(array("module"=>$app,"type"=>"admin_url","title"=>$menu['name']));//type 1-admin rule;2-user rule
    		}
    		
    		if($children && $parentid!==false){
    			$this->_import_menu($children,$parentid2,$error_menus);
    		}
    	}
    	
    }
    
    public function spmy_import_menu(){
    	
    	$apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
    	$error_menus=array();
    	foreach ($apps as $app){
    		if(is_dir(SPAPP.$app)){
    			$menudir=SPAPP.$app."/Menu";
    			$menu_files=sp_scan_dir($menudir."/admin_*.php",null);
    			if(count($menu_files)){
    				foreach ($menu_files as $mf){
    					//是php文件
    					$mf_path=$menudir."/$mf";
    					if(file_exists($mf_path)){
    						$menudatas=include   $mf_path;
    						if(is_array($menudatas) && !empty($menudatas)){
    							$this->_import_menu(array($menudatas),0,$error_menus);
    						}
    					}
    						
    						
    				}
    			}
    			 
    		}
    	}
		$this->assign("errormenus",$error_menus);
    	$this->display("import_menu");
    }
    
    private function _import_submenu($submenus,$parentid){
    	foreach($submenus as $sm){
    		$data=$sm;
    		$data['parentid']=$parentid;
    		unset($data['items']);
    		$id=$this->menu_model->add($data);
    		if(!empty($sm['items'])){
    				$this->_import_submenu($sm['items'],$id);
    		}else{
    			return;
    		}
    	}
    }
    
    private function _generate_submenu(&$rootmenu,$m){
    	$parentid=$m['id'];
    	$rm=$this->menu_model->menu($parentid);
    	unset($rootmenu['id']);
    	unset($rootmenu['parentid']);
    	if(count($rm)){
    		
    		$count=count($rm);
    		for($i=0;$i<$count;$i++){
    			$this->_generate_submenu($rm[$i],$rm[$i]);
    			
    		}
    		$rootmenu['items']=$rm;
    		
    	}else{
    		return ;
    	}
    	
    }
    
    
    public function spmy_getactions(){
    	$apps_r=array("Comment");
    	$groups=C("MODULE_ALLOW_LIST");
    	$group_count=count($groups);
    	$newmenus=array();
    	$g=I("get.app");
    	if(empty($g)){
    		$g=$groups[0];
    	}
    	
    	if(in_array($g, $groups)){
    		if(is_dir(SPAPP.$g)){
    			if(!(strpos($g, ".") === 0)){
    				$actiondir=SPAPP.$g."/Controller";
    				$actions=sp_scan_dir($actiondir."/*");
    				if(count($actions)){
    					foreach ($actions as $mf){
    						if(!(strpos($mf, ".") === 0)){
    							if($g=="Admin"){
    								$m=str_replace("Controller.class.php", "",$mf);
    								$noneed_models=array("Public","Index","Main");
    								if(in_array($m, $noneed_models)){
    									continue;
    								}
    							}else{
    								if(strpos($mf,"adminController.class.php") || strpos($mf,"Admin")===0){
    									$m=str_replace("Controller.class.php", "",$mf);
    								}else{
    									continue;
    								}
    							}
    							$class=A($g."/".$m);
    							$adminbaseaction=new \Common\Controller\AdminbaseController();
    							$base_methods=get_class_methods($adminbaseaction);
    							$methods=get_class_methods($class);
    							$methods=array_diff($methods, $base_methods);
    							
    							foreach ($methods as $a){
    								if(!(strpos($a, "_") === 0) && !(strpos($a, "spmy_") === 0)){
    									$where['app']=$g;
    									$where['model']=$m;
    									$where['action']=$a;
    									$count=$this->menu_model->where($where)->count();
    									if(!$count){
    										$data['parentid']=0;
    										$data['app']=$g;
    										$data['model']=$m;
    										$data['action']=$a;
    										$data['type']="1";
    										$data['status']="0";
    										$data['name']="未知";
    										$data['listorder']="0";
    										$result=$this->menu_model->add($data);
    										if($result!==false){
    											$newmenus[]=   $g."/".$m."/".$a."";
    										}
    									}
    									
    									$name=strtolower("$g/$m/$a");
    									$mwhere=array("name"=>$name);
    									
    									$find_rule=$this->auth_rule_model->where($mwhere)->find();
    									if(!$find_rule){
    										$this->auth_rule_model->add(array("name"=>$name,"module"=>$g,"type"=>"admin_url","title"=>""));//type 1-admin rule;2-user rule
    									}
    								}
    							}
    						}
    						 
    		
    					}
    				}
    			}
    		}
    		
    		$index=array_search($g, $groups);
    		$nextindex=$index+1;
    		$nextindex=$nextindex>=$group_count?0:$nextindex;
    		if($nextindex){
    			$this->assign("nextapp",$groups[$nextindex]);
    		}
    		$this->assign("app",$g);
    	}
    	 
    	$this->assign("newmenus",$newmenus);
    	$this->display("getactions");
    	
    }

    public function menu_auth_rule_syn(){
        if(getRoleId() != 1){
            $this->error('操作不合法');
        }
        $redis = connectionRedis();
        $menu_auth_rule_syn_action = $redis->get('menu_auth_rule_syn_action');
        if($menu_auth_rule_syn_action){
            $this->error('有人在操作了：'.$menu_auth_rule_syn_action);
        }
        $redis->set('menu_auth_rule_syn_action', get_current_admin_user_login(), 60*60); // 过期1小时
        $newest_id = M('menu_auth_rule_action')->order('id desc')->find();
        $data = array(
            'id' => (!$newest_id || !isset($newest_id['id'])) ? 0 : intval($newest_id['id']),
        );
        ksort($data);
        $sign_str = '';
        foreach ($data as $key=>$val){
            if($key == 'sign'){
                continue;
            }
            $sign_str .= $key.'='.$val.'&';
        }
        $sign_str .= 'd='.date('Y-m-d').'&';
        $sign_str .= '&key='.$this->sign_key;
        $sign = md5($sign_str);
        $data['sign'] = $sign;
        $url = getKvconfigVal(1, 'menu_auth_rule_syn');
        if(!$url){
            $redis->del('menu_auth_rule_syn_action');
            $this->success('同步地址不能为空');
        }
        $result = http_post($url, $data);
        if(!isset($result['code']) || $result['code'] != 20000){
            $redis->del('menu_auth_rule_syn_action');
            $this->error('获取需要更新的菜单数据失败'.json_encode($result));
        }
        if(!isset($result['data']) ||count($result['data']) <= 0){
            $redis->del('menu_auth_rule_syn_action');
            $this->error('没有需要更新的菜单数据'.json_encode($result));
        }

        $syn_sql = array();
        try {
            M()->startTrans();

            $insert_data = array();
            foreach ($result['data'] as $key=>$val) {
                if(strpos($val['action_sql'], $val['table_name']) !== false){

                }else{
                    throw new \Exception("sql 操作，表名不匹配");
                }
                switch ($val['action_type']){
                    case 1:
                        if(strpos($val['action_sql'],'INSERT') !== 0){
                            throw new \Exception("INSERT 行为，第一个字符串没有包含INSERT【".$val['action_sql']."】");
                        }
                        break;
                    case 2:
                        if(strpos($val['action_sql'],'UPDATE') !== 0){
                            throw new \Exception("UPDATE 行为，第一个字符串没有包含UPDATE【".$val['action_sql']."】");
                        }
                        break;
                    case 3:
                        if(strpos($val['action_sql'],'DELETE') !== 0){
                            throw new \Exception("DELETE 行为，第一个字符串没有包含DELETE【".$val['action_sql']."】");
                        }
                        break;
                    default:
                        throw new \Exception("action_type 不合法：".$val['action_type']);
                }
                $temp_insert_data = $val;
                $temp_insert_data['operated_by'] = get_current_admin_user_login();
                unset($temp_insert_data['created_at']);
                array_push($insert_data, $temp_insert_data);
                array_push($syn_sql, $val['action_sql']);
                M()->execute($val['action_sql']);
            }
            M('menu_auth_rule_action')->addAll($insert_data);
            M()->commit();
        }catch (\Exception $e){
            M()->rollback();
            $redis->del('menu_auth_rule_syn_action');
            setAdminLog('【同步后台菜单-失败】'.$e->getMessage());
            $this->error('操作失败');
        }
        $redis->del('menu_auth_rule_syn_action');
        // 删除菜单缓存
        F("Menu", null);
        setAdminLog('【同步后台菜单-成功】'.json_encode($syn_sql, JSON_UNESCAPED_UNICODE));
        $this->success('操作成功');
    }

}