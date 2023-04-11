<?php

/**
 * 经验等级
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class LevelController extends AdminbaseController {
	
    protected $experlevel_model;

    private $goServerName = 'ExperienceLevel';
    private $goCallerRouter = '/call';

    function _initialize() {
        parent::_initialize();
        $this->experlevel_model = D("Common/Experlevel");

    }
	
    function experlevel_index(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($_GET['num']) ? $_GET['num'] : 20;
        $p = isset($_GET['p']) ? $_GET['p'] : 1;

        $map['tenant_id'] = $tenant_id;
        if(isset($param['levelid']) && $param['levelid'] != ''){
            $map['levelid'] = $param['levelid'];
        }

    	$count = M('experlevel')->where($map)->count();
    	$page = $this->page($count, $page_size);

    	$lists = M('experlevel')
            ->where($map)
            ->order("levelid asc")
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        foreach($lists as $k=>$v){
            $lists[$k]['thumb']=get_upload_path($v['thumb']);
            if(($k+1) == count($lists) && (ceil($count/$page_size) == $p || $count <= $page_size)){
                $lists[$k]['del'] = 1;
            }else{
                $lists[$k]['del'] = 0;
            }
        }

    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
    	$this->display();
    }

    // 同步数据到golang (已废弃这中欧方式)
    public function sync_data_to_go(){
        if(IS_AJAX) {
            $data['ServerName'] = $this->goServerName;

            $tenant_list = getTenantList();
            $go_level_list = array();
            $config = getConfigPub(getTenantIds());
            foreach ($tenant_list as $key => $val) {
                $tenant_id = $val['id'];

                // 重新加载go缓存
                $data['MethodName'] = 'ReloadCache';
                $data['Params'] = array(
                    'TenantId' => intval($tenant_id),
                );
                $go_call_res = http_to_go_call($config['go_caller_url'], $data);

                // 获取go缓存列表
                $data['Params'] = array('TenantId' => intval($tenant_id));
                $data['MethodName'] = 'SelectCacheByTenantId';
                $go_call_res = http_to_go_call($config['go_caller_url'], $data);
                $temp_list = isset($go_call_res) && is_array($go_call_res['Data']) ? $go_call_res['Data'] : [];
                foreach ($temp_list as $k => $v) {
                    $go_level_list[$v['TenantId'] . '_' . $v['Level']] = $v;
                }
            }
            $go_level_count = count($go_level_list);

            $limit = 500;
            $list = M('experlevel')->order("tenant_id asc, levelid asc")->select();
            $count = count($list);
            $succ_count = 0;
            $num = 0;
            $Params = array();
            foreach ($list as $key => $val) {
                if($num >= $limit){
                    break;
                }
                $union = $val['tenant_id'] . '_' . $val['levelid'];
                if (!isset($go_level_list[$union])) {
                    $num++;
                    $Params[] = array(
                        'TenantId' => intval($val['tenant_id']),
                        'Level' => intval($val['levelid']),
                        'Name' => $val['levelname'],
                        'Color' => $val['colour'],
                        'Thumb' => $val['thumb'],
                        'ThumbMark' => $val['thumb_mark'],
                        'Experience' => intval($val['experience']),
                        'ExpUpperLimit' => intval($val['level_up']),
                        'OperatedBy' => get_current_admin_user_login(),
                    );
                }
            }
            // go批量新增
            $data['MethodName'] = 'InsertMulti';
            $data['Params'] = $Params;
            $go_call_res = http_to_go_call($config['go_caller_url'], $data);
            if(isset($go_call_res['Data']) && isset($go_call_res['Data']['InsertCount'])){
                $succ_count += $go_call_res['Data']['InsertCount'];
            }

            $left_count = $count > ($go_level_count + $succ_count) ? ($count - $go_level_count - $succ_count) : 0;
            $this->error('操作成功【同步 '.$succ_count.' 条，还剩 '.$left_count.' 条】');
        }
    }
		
	function experlevel_del(){
		$id=intval($_GET['id']);
		if($id){
            $info = M("experlevel")->where("id='{$id}'")->find();
            $result=M("experlevel")->where("id='{$id}'")->delete();
			if($result!==false){
                $action="删除会员等级：{$id}";
                setAdminLog($action);
                $this->resetcache(getTenantIds());

                // 同步到golang
                $config = getConfigPub($info['tenant_id']);
                $data['ServerName'] = $this->goServerName;
                $data['MethodName'] = 'DeleteByTenantIdAndLevel';
                $data['Params'] = array(
                    'TenantId' => intval($info['tenant_id']),
                    'Level' => intval($info['levelid']),
                );
                $go_call_res = http_to_go_call($config['go_caller_url'].$this->goCallerRouter, $data);

				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}			
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display();				
	}

	function experlevel_add(){
        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_id',getTenantIds());
		$this->display();				
	}	
	function experlevel_add_post(){
		if(IS_POST){
            $param = I('param.');
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            $leveinfo = M('experlevel')->where(['tenant_id'=>$tenant_id])->field("MAX(levelid) as levelid")->find();

            $_POST['levelid'] = isset($leveinfo['levelid'])&&$leveinfo['levelid']>=0 ? ($leveinfo['levelid']+1) : 1;
            if($_POST['levelid'] > 500){ // 等级不能大于500
                $this->error('等级已经超出上限500');
            }
            $experience=I('experience');
            $colour=I('colour');
            $thumb=I('thumb');
            $thumb_mark=I('thumb_mark');
            if($experience==''){
                $this->error('请填写等级经验');
            }
            if($colour==''){
                $this->error('请填写昵称颜色');
            }
       /*     if($thumb==''){
                $this->error('请上传图标');
            }
            if($thumb_mark==''){
                $this->error('请上传头像角标');
            }*/
            $experlevel=$this->experlevel_model;

			if($experlevel->create()){

                    if($_FILES){
                        $savepath=date('Ymd').'/';
                        //上传处理类
                        $config=array(
                            'rootPath' => './'.C("UPLOADPATH"),
                            'savePath' => $savepath,
                            'maxSize' => 11048576,
                            'saveName'   =>    array('uniqid',''),
                            'exts'       =>    array('svga'),
                            'autoSub'    =>    false,
                        );
                        $upload = new \Think\Upload($config);//
                        $info=$upload->upload();

                        //开始上传
                        if ($info) {
                            //上传成功
                            //写入附件数据库信息

                            if(isset($info['thumb']['url'])){
                                $thumb = $info['thumb']['url'];
                                $thumb = str_replace("http","https",$thumb );
                                $experlevel->thumb=$thumb;
                            }else{
                                $experlevel->thumb=$thumb;
                            }

                            if(isset($info['thumb_mark']['url'])){
                                $thumb_mark = $info['thumb_mark']['url'];
                                $thumb_mark = str_replace("http","https",$thumb_mark );
                                $experlevel->thumb_mark=$thumb_mark;
                            }else{
                                $experlevel->thumb_mark=$thumb_mark;
                            }

                        } else {
                            //上传失败，返回错误
                            $this->error($upload->getError());
                        }

                    }else{
                        $experlevel->thumb=$thumb;
                        $experlevel->thumb_mark=$thumb_mark;
                    }

				$experlevel->addtime=time();
                $experlevel->tenant_id = intval($tenant_id);
				$result=$experlevel->add(); 
				if($result!==false){
                    $action="添加会员等级：{$result}";
                    setAdminLog($action);
                    $this->resetcache($tenant_id);

                    // 同步到golang
                    $info = M('experlevel')->where(["tenant_id"=>$tenant_id, 'levelid'=>$_POST['levelid']])->find();
                    $config = getConfigPub($info['tenant_id']);
                    $data['ServerName'] = $this->goServerName;
                    $data['MethodName'] = 'Insert';
                    $data['Params'] = array(
                        'TenantId' => intval($info['tenant_id']),
                        'Level' => intval($info['levelid']),
                        'Name' => $info['levelname'],
                        'Color' => $info['colour'],
                        'Thumb' => $info['thumb'],
                        'ThumbMark' => $info['thumb_mark'],
                        'Experience' => intval($info['experience']),
                        'ExpUpperLimit' => intval($info['level_up']),
                        'OperatedBy' => get_current_admin_user_login(),
                    );
                    $go_call_res = http_to_go_call($config['go_caller_url'].$this->goCallerRouter, $data);

					$this->success('添加成功',U('experlevel_index',array('tenant_id'=>$tenant_id)));
				}else{
					$this->error('添加失败');
				}						 
				 
			}else{
				$this->error($this->experlevel_model->getError());
			}
		}			
	}		
	function experlevel_edit(){
		$id=intval($_GET['id']);
		if($id){
			$experlevel=M("experlevel")->where("id='{$id}'")->find();
			$this->assign('experlevel', $experlevel);						
		}else{				
			$this->error('数据传入失败！');
		}

        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
		$this->display();				
	}
	
	function experlevel_edit_post(){
		if(IS_POST){
            $checkinfo = M('experlevel')->where(" id ='{$_POST['id']}'")->find();

            $experience=I('experience');
            $colour=I('colour');
            $thumb=I('thumb');
            $thumb_mark=I('thumb_mark');
            if($experience==''){
                $this->error('请填写等级经验');
            }
            if($colour==''){
                $this->error('请填写昵称颜色');
            }
            if($thumb=='' && empty($_FILES['thumb']) ){
                $thumb = $checkinfo['thumb'];
            }
            if($thumb_mark==''&& empty($_FILES['thumb_mark'])  ){
                $thumb_mark = $checkinfo['thumb_mark'];
            }
            
            $experlevel=M("experlevel");
			$experlevel->create();
            if($_FILES){
                $savepath=date('Ymd').'/';
                //上传处理类
                $config=array(
                    'rootPath' => './'.C("UPLOADPATH"),
                    'savePath' => $savepath,
                    'maxSize' => 11048576,
                    'saveName'   =>    array('uniqid',''),
                    'exts'       =>    array('svga'),
                    'autoSub'    =>    false,
                );
                $upload = new \Think\Upload($config);//
                $info=$upload->upload();

                //开始上传
                if ($info) {
                    //上传成功
                    //写入附件数据库信息

                    if(isset($info['thumb']['url'])){
                        $thumb = $info['thumb']['url'];
                        $thumb = str_replace("http","https",$thumb );
                        $experlevel->thumb=$thumb;
                    }else{
                        $experlevel->thumb=$thumb;
                    }

                    if(isset($info['thumb_mark']['url'])){
                        $thumb_mark = $info['thumb_mark']['url'];
                        $thumb_mark = str_replace("http","https",$thumb_mark );
                        $experlevel->thumb_mark=$thumb_mark;
                    }else{
                        $experlevel->thumb_mark=$thumb_mark;
                    }

                } else {
                    //上传失败，返回错误
                    $this->error($upload->getError());
                }

            }else{
                $experlevel->thumb=$thumb;
                $experlevel->thumb_mark=$thumb_mark;
            }

			$result=$experlevel->save();

            if($result!==false){
                $action="修改会员等级：{$_POST['id']}";
                setAdminLog($action);
                $this->resetcache($checkinfo['tenant_id']);

                // 同步到golang
                $info = M('experlevel')->where(" id ='{$_POST['id']}'")->find();
                $config = getConfigPub($info['tenant_id']);
                $data['ServerName'] = $this->goServerName;
                $data['MethodName'] = 'UpdateByTenantIdAndLevel';
                $data['Params'] = array(
                    'TenantId' => intval($info['tenant_id']),
                    'Level' => intval($info['levelid']),
                    'Name' => $info['levelname'],
                    'Color' => $info['colour'],
                    'Thumb' => $info['thumb'],
                    'ThumbMark' => $info['thumb_mark'],
                    'Experience' => intval($info['experience']),
                    'ExpUpperLimit' => intval($info['level_up']),
                    'OperatedBy' => get_current_admin_user_login(),
                );
                $go_call_res = http_to_go_call($config['go_caller_url'].$this->goCallerRouter, $data);

                $this->success('修改成功',U('experlevel_index',array('tenant_id'=>$checkinfo['tenant_id'])));
            }else{
                $this->error('修改失败');
            }					 

        }
			
	}
    
    function resetcache($tenant_id){
		$key='level_'.$tenant_id;

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
        return 1;
    }

    public function importLevel(){
        if(IS_POST){
            $param = I('param.');
            if(!isset($_FILES['level_excel'])){
                $this->error(['msg'=>'参数错误']);
            }
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            $path = './data/upload/';
            $fileName = $path . $_FILES['level_excel']['name'];

            $move = move_uploaded_file($_FILES['level_excel']['tmp_name'],$path . $_FILES['level_excel']['name']);
            if(!$move){
                $this->error('导入失败');
            }

            include dirname(__FILE__) ."/../../../PHPExcel/PHPExcel/IOFactory.php";

            // 载入当前文件
            $phpExcel = \PHPExcel_IOFactory::load($fileName);
            // 设置为默认表
            $phpExcel->setActiveSheetIndex(0);
            // 获取行数
            $row = $phpExcel->getActiveSheet()->getHighestRow();

            $delete_status = false;
            // 行数循环
            for ($i = 1; $i <= $row; $i++) {
                if($phpExcel->getActiveSheet()->getCell('A' . $i)->getValue() == '等级'){
                    continue;
                }
                $temp = array(
                        'levelid' => $phpExcel->getActiveSheet()->getCell('A' . $i)->getValue(),
                        'levelname' => $phpExcel->getActiveSheet()->getCell('C' . $i)->getValue(),
                        'experience' => $phpExcel->getActiveSheet()->getCell('B' . $i)->getValue(),
                        'addtime' => time(),
                        'thumb' => $phpExcel->getActiveSheet()->getCell('D' . $i)->getValue() ? $phpExcel->getActiveSheet()->getCell('D' . $i)->getValue() : '',
                        'colour' => $phpExcel->getActiveSheet()->getCell('E' . $i)->getValue() ? $phpExcel->getActiveSheet()->getCell('E' . $i)->getValue() : '3177a8',
                        'thumb_mark' => $phpExcel->getActiveSheet()->getCell('F' . $i)->getValue() ? $phpExcel->getActiveSheet()->getCell('F' . $i)->getValue() : '',
                        'tenant_id' => intval($tenant_id),
                    );
                if($delete_status == false){
                    M('experlevel')->where(['tenant_id'=>$tenant_id])->delete();
                }
                M('experlevel')->add($temp);
                $delete_status = true;
            }
            $this->resetcache($tenant_id);
            $this->error('导入成功');
        }

        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_id',getTenantIds());
        $this->display();
    }
		
}
