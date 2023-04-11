<?php

/**
 * 主播等级
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class LevelanchorController extends AdminbaseController {

    private $goServerName = 'ExperienceLevelAnchor';
    private $goCallerRouter = '/call';

    function index(){
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

    	$experlevel=M("experlevel_anchor");
    	$count=$experlevel->where($map)->count();
    	$page = $this->page($count, $page_size);
    	$lists = $experlevel
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
		
	function del(){
		$id=intval($_GET['id']);
		if($id){
            $info = M("experlevel_anchor")->where("id='{$id}'")->find();
			$result=M("experlevel_anchor")->where("id='{$id}'")->delete();				
			if($result!==false){
                $action="删除主播等级：{$id}";
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



	function add(){
        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_id',getTenantIds());
		$this->display();				
	}	
	function add_post(){
		if(IS_POST){
            $param = I('param.');
            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
            $leveinfo = M('experlevel_anchor')->where(['tenant_id'=>$tenant_id])->field("MAX(levelid) as levelid")->find();

            $_POST['levelid'] = isset($leveinfo['levelid'])&&$leveinfo['levelid']>=0 ? ($leveinfo['levelid']+1) : 1;
            if($_POST['levelid'] > 20){ // 等级不能大于20
                $this->error('等级已经超出上限20');
            }
			/*if($_POST['levelid'] == ''){
				$this->error('等级不能为空');
			}else{
				$check = M('experlevel_anchor')->where("levelid='{$_POST['levelid']}'")->find();
				if($check){
					$this->error('等级不能重复');
				}
			}		*/
            
            $experience=I('experience');
            $thumb=I('thumb');
            $thumb_mark=I('thumb_mark');
            if($experience==''){
                $this->error('请填写等级经验');
            }
          /*  if($thumb==''){
                $this->error('请上传图标');
            }
            if($thumb_mark==''){
                $this->error('请上传头像角标');
            }*/
            
			$experlevel=M("experlevel_anchor");
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
                    $action="添加主播等级：{$result}";
                    setAdminLog($action);
                    $this->resetcache($tenant_id);

                    // 同步到golang
                    $info = M('experlevel_anchor')->where(["tenant_id"=>$tenant_id, 'levelid'=>$_POST['levelid']])->find();
                    $config = getConfigPub($info['tenant_id']);
                    $data['ServerName'] = $this->goServerName;
                    $data['MethodName'] = 'Insert';
                    $data['Params'] = array(
                        'TenantId' => intval($info['tenant_id']),
                        'Level' => intval($info['levelid']),
                        'Name' => $info['levelname'],
                        'Thumb' => $info['thumb'],
                        'ThumbMark' => $info['thumb_mark'],
                        'Experience' => intval($info['experience']),
                        'ExpUpperLimit' => intval($info['level_up']),
                        'OperatedBy' => get_current_admin_user_login(),
                    );
                    $go_call_res = http_to_go_call($config['go_caller_url'].$this->goCallerRouter, $data);

					$this->success('添加成功',U('index',array('tenant_id'=>$tenant_id)));
				}else{
					$this->error('添加失败');
				}						 
				 
			}else{

			}
		}			
	}		
	function edit(){
		$id=intval($_GET['id']);
		if($id){
			$experlevel=M("experlevel_anchor")->where("id='{$id}'")->find();
			$this->assign('experlevel', $experlevel);						
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display();				
	}
	
	function edit_post(){
		if(IS_POST){
			/*if($_POST['levelid'] == ''){
				$this->error('等级不能为空');
			}else{
				$check = M('experlevel_anchor')->where("levelid='{$_POST['levelid']}' and id !='{$_POST['id']}'")->find();
				if($check){
					$this->error('等级不能重复');
				}
			}*/
            $checkinfo = M('experlevel_anchor')->where("  id ='{$_POST['id']}'")->find();

            $experience=I('experience');
            $thumb=I('thumb');
            $thumb_mark=I('thumb_mark');
            if($experience==''){
                $this->error('请填写等级经验');
            }
            if($thumb=='' && empty($_FILES['thumb']) ){
                $thumb = $checkinfo['thumb'];
            }
            if($thumb_mark==''&& empty($_FILES['thumb_mark'])  ){
                $thumb_mark = $checkinfo['thumb_mark'];
            }


            $experlevel=M("experlevel_anchor");
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
                $action="编辑主播等级：{$_POST['id']}";
                setAdminLog($action);
                $this->resetcache($checkinfo['tenant_id']);

                // 同步到golang
                $info = M('experlevel_anchor')->where(" id ='{$_POST['id']}'")->find();
                $config = getConfigPub($info['tenant_id']);
                $data['ServerName'] = $this->goServerName;
                $data['MethodName'] = 'UpdateByTenantIdAndLevel';
                $data['Params'] = array(
                    'TenantId' => intval($info['tenant_id']),
                    'Level' => intval($info['levelid']),
                    'Name' => $info['levelname'],
                    'Thumb' => $info['thumb'],
                    'ThumbMark' => $info['thumb_mark'],
                    'Experience' => intval($info['experience']),
                    'ExpUpperLimit' => intval($info['level_up']),
                    'OperatedBy' => get_current_admin_user_login(),
                );
                $go_call_res = http_to_go_call($config['go_caller_url'].$this->goCallerRouter, $data);

				$this->success('修改成功',U('index',array('tenant_id'=>$checkinfo['tenant_id'])));
			}else{
				$this->error('修改失败');
			}					 
		}	
	}

    function resetcache($tenant_id){
        $key = 'levelanchor_'.$tenant_id;

        $level= M("experlevel_anchor")->where(['tenant_id'=>$tenant_id])->order("experience asc")->select();
        foreach($level as $k=>$v){
            $v['thumb']=get_upload_path($v['thumb']);
            $v['thumb_mark']=get_upload_path($v['thumb_mark']);

            $level[$k]=$v;
        }
        setcaches($key,$level);
        $config = getConfigPri($tenant_id);
        $url = $config['go_admin_url'].'/admin/v1/live_sync_cache/reload_experlevel_anchor_list_cache';
        $http_post_res = http_post($url,['TenantId'=>$tenant_id]);

        return 1;
    }    
}
