<?php

/**
 * 守护
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class GuardController extends AdminbaseController {

    var $type_a=array(
        '1'=>'普通守护',
        '2'=>'尊贵守护',
    );
    var $length_type_a=array(
        '0'=>'天',
        '1'=>'月',
        '2'=>'年',
    );
    
    var $length_time_a=array(
        '0'=>60*60*24,
        '1'=>60*60*24*30,
        '2'=>60*60*24*365,
    );
    function index(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map=array();

        $map['tenant_id'] = $tenant_id;
        if(isset($param['name']) && $param['name'] != ''){
            $map['name'] = $param['name'];
            $http_post_map['name'] = intval($param['name']);
        }

        $model = M("guard");
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
    	$lists = $model->where($map)->order("orderno asc")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach($lists as $key=>$val){
            $tenantInfo = getTenantInfo($val['tenant_id']);
            $lists[$key]['tenant_name'] = $tenantInfo ? $tenantInfo['name'] : $val['tenant_id'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
    	$this->assign('type_a', $this->type_a);
    	$this->assign('length_type_a', $this->length_type_a);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
    	$this->display();
    }		
		
	function del(){
		$id=intval($_GET['id']);
		if($id){
			$result=M("guard")->where("id='{$id}'")->delete();				
				if($result){
                    $action="删除守护：{$id}";
                    setAdminLog($action);
                    $this->resetCache();

                    $this->success('删除成功');
				 }else{
                    $this->error('删除失败');
				 }						
		}else{				
			$this->error('数据传入失败！');
		}								  
		$this->display();				
	}		
    //排序
    public function listorders() { 
		
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("guard")->where(array('id' => $key))->save($data);
        }
				
        $status = true;
        if ($status) {
            $action="更新守护排序";
            setAdminLog($action);
            $this->resetCache();
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }	
	
    function add(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        //查询守护礼物
        $listsgift = M("gift")->where(['tenant_id'=>$tenant_id, 'type'=>'2'])->order("orderno, addtime desc")->select();

        $this->assign('type_a', $this->type_a);
        $this->assign('tenant_list', getTenantList());
    	$this->assign('length_type_a', $this->length_type_a);
        $this->assign('listsgift', $listsgift);
        $this->assign('tenant_id',$tenant_id);
        $this->assign('param',$param);
		$this->display();
    }	
	
	function do_add(){
		if(IS_POST){	
            $name=I('name');
            $coin=I('coin');
            $length=I('length');
            $length_type=I('length_type');
            $tenant_id=I('tenantinfo');
            $giftname = I('giftname');

            $giftarr = '';
            if(!empty($giftname)){
               $giftarr = implode(',',$giftname);
            }

            if($name==''){
                $this->error('请输入名称');
            }
            if($coin=='' || (int)$coin<1){
                $this->error('请输入有效价格');
            }
            if($length=='' || (int)$length<1){
                $this->error('请输入有效时长');
            }
            $guard_img = '';
            $guard_effect = '';
			$guard=M("guard");
			$guard->create();

			$guard->length_time=$length * $this->length_time_a[$length_type];
            $guard->type=$length_type;
			$guard->addtime=time();
			$guard->uptime=time();
            $guard->tenant_id=$tenant_id;
            $guard->giftarr=$giftarr;

            if($_FILES) {
                $savepath = date('Ymd') . '/';
                //上传处理类
                $config = array(
                    'rootPath' => './' . C("UPLOADPATH"),
                    'savePath' => $savepath,
                    'maxSize' => 11048576,
                    'saveName' => array('uniqid', ''),
                    'exts' => array('svga'),
                    'autoSub' => false,
                );
                $upload = new \Think\Upload($config);
                $info = $upload->upload();
                //开始上传
                if ($info) {
                    //上传成功
                    //写入附件数据库信息

                    if(isset($info['guard_img']['url'])){
                        $guard_img= $info['guard_img']['url'];
                        if(strpos($guard_img,'https') == false){
                            $guard_img = str_replace("http","https",$guard_img );
                        }
                        $guard->guard_img=$guard_img;
                    }else{
                        $guard->guard_img=$guard_img;
                    }

                    if(isset($info['guard_effect']['url'])){
                        $guard_effect= $info['guard_effect']['url'];
                        if(strpos($guard_effect,'https') == false){
                            $guard_effect = str_replace("http","https",$guard_effect );
                        }
                        $guard->guard_effect=$guard_effect;
                    }else{
                        $guard->guard_effect=$guard_effect;
                    }


                } else {
                    //上传失败，返回错误
                    $this->error($upload->getError());
                }

            }
			$result=$guard->add(); 
			if($result){
                $action="添加守护：{$result}";
                setAdminLog($action);
                $this->resetCache();
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}				
    }		
    function edit(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
		$id=intval($_GET['id']);
		if($id){
			$data = M("guard")->where("id='{$id}'")->find();
			if($data){
                $guard_gift = explode(',',$data['giftarr']);
            }

            $listsgift = M("gift")->where(['tenant_id'=>$tenant_id, 'type'=>'2'])->order("orderno, addtime desc")->select();

            $this->assign('data', $data);
            $this->assign('listsgift',$listsgift);
            $this->assign('guard_gift',$guard_gift);
            $this->assign('type_a', $this->type_a);
            $this->assign('length_type_a', $this->length_type_a);
		}else{				
			$this->error('数据传入失败！');
		}								      	
    	$this->display();
    }		
	
	function do_edit(){
		if(IS_POST){	
            $name=I('name');
            $coin=I('coin');
            $length=I('length');
            $length_type=I('length_type');
            $guard_img=I('old_guard_img');
            $guard_effect=I('old_guard_effect');
            $giftname = I('giftname');

            $giftarr = '';
            if(!empty($giftname)){
                $giftarr = implode(',',$giftname);
            }
            if($name==''){
                $this->error('请输入名称');
            }
            if($coin=='' || (int)$coin<1){
                $this->error('请输入有效价格');
            }
            if($length=='' || (int)$length<1){
                $this->error('请输入有效时长');
            }

			 $guard=M("guard");
			 $guard->create();


             $guard->uptime=time();
             $guard->giftarr=$giftarr;
             if($_FILES) {
                $savepath = date('Ymd') . '/';
                //上传处理类
                $config = array(
                    'rootPath' => './' . C("UPLOADPATH"),
                    'savePath' => $savepath,
                    'maxSize' => 11048576,
                    'saveName' => array('uniqid', ''),
                    'exts' => array('svga'),
                    'autoSub' => false,
                );
                $upload = new \Think\Upload($config);
                $info = $upload->upload();
                //开始上传
                if ($info) {
                    //上传成功
                    //写入附件数据库信息

                    if(isset($info['guard_img']['url'])){
                        $guard_img= $info['guard_img']['url'];
                        if(strpos($guard_img,'https') == false){
                            $guard_img = str_replace("http","https",$guard_img );
                        }
                        $guard->guard_img=$guard_img;
                    }else{
                        $guard->guard_img=$guard_img;
                    }

                    if(isset($info['guard_effect']['url'])){
                        $guard_effect= $info['guard_effect']['url'];
                        if(strpos($guard_effect,'https') == false){
                            $guard_effect = str_replace("http","https",$guard_effect );
                        }
                        $guard->guard_effect=$guard_effect;
                    }else{
                        $guard->guard_effect=$guard_effect;
                    }

                } else {
                    //上传失败，返回错误
                    $this->error($upload->getError());
                }

            }else{
                 $guard->guard_img=$guard_img;
                 $guard->guard_effect=$guard_effect;
             }

			 $result=$guard->save(); 
			 if($result){
                 $action="修改守护：{$_POST['id']}";
                    setAdminLog($action);
                    $this->resetCache();   
				  $this->success('修改成功');
			 }else{
				  $this->error('修改失败');
			 }
		}	
    }	

    function resetCache(){
        $key='guard_list';
        $list= M("guard")
            ->field('id,name,type,coin')
            ->order('orderno asc')
            ->select();
        setcaches($key,$list);
        return 1;
    }
    function guardRecord(){
        $param = I('param.');
        $currency_list = currency_list();

        $map = array();
        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']){
                $map['tenant_id'] = intval($param['tenant_id']);
            }
        }else{
            //租户id条件
            $map['tenant_id'] = intval(getTenantIds());
        }

        $param['tenant_id'] = isset($param['tenant_id']) ? $param['tenant_id'] : '';;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        if($_REQUEST['liveuid']!=''){
            $map['liveuid']=$_REQUEST['liveuid'];
        }
        if($_REQUEST['uid']!=''){
            $map['uid']=$_REQUEST['uid'];
        }

        $model = M("guard_users");
        $count = $model->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $model->where($map)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach ($lists as $key=> $value){
            $userinfo = getUserInfo($value['uid']);
            $userliveinfo = getUserInfo($value['liveuid']);
            $lists[$key]['user_nicename'] = $userinfo['user_nicename'];
            $lists[$key]['live_nicename'] = $userliveinfo['user_nicename'];

            $tenantInfo = getTenantInfo($value['tenant_id']);
            $lists[$key]['tenant_name'] = $tenantInfo ? $tenantInfo['name'] : $value['tenant_id'];
        }

        $guard_name	= M("guard")->field('name')->select();

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign('lists', $lists);
        $this->assign('guard_name', $guard_name);
        $this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->display();
    }
}
