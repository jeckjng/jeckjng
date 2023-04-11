<?php

/**
 * 直播分类
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class LiveclassController extends AdminbaseController {
    function index(){
        $param = I('param.');
        $map = array();
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;

        $map['tenant_id'] = $tenant_id;

        if(isset($param['name']) && $param['name'] != ''){
            $map['name'] = $param['name'];
        }

    	$model = M("live_class");
    	$count = $model->where($map)->count();
    	$page = $this->page($count, $page_size);
    	$lists = $model->where($map)->order("orderno asc, id desc")->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach ($lists as $key=>$val){
            $tenantInfo = getTenantInfo($val['tenant_id']);
            $lists[$key]['tenant_name'] = $tenantInfo ? $tenantInfo['name'] : $val['tenant_id'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list',getTenantList());
        $this->assign('param', $param);
    	$this->display();
    }
		
    function del(){
        $id=intval($_GET['id']);
        if($id){
            $info = M('live_class')->where(" id ='{$id}'")->find();
            $result=M("live_class")->delete($id);				
            if($result){
                $action="删除直播分类：{$id}";
                setAdminLog($action);
                $this->resetCache($info['tenant_id']);
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
            M("live_class")->where(array('id' => $key))->save($data);
        }
        $tenant_id = getTenantId();
        $status = true;
        if ($status) {
            $action="更新直播分类排序";
            setAdminLog($action);
            $this->resetCache($tenant_id);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }	
    

    function add(){
        $param = I('param.');

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $this->assign('tenant_id',$tenant_id);
        $this->assign('tenant_list',getTenantList());
        $this->display();				
    }	
    function add_post(){
        if(IS_POST){
            $param = I('param.');
            $name=I("name");
            if($name==''){
                $this->error('请填写名称');
            }
            $thumb=I("thumb");

            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

            /*  if($thumb==''){
                 $this->error('请上传图标');
             }*/
            $Live_class=M("live_class");
            $Live_class->create();
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
                        $Live_class->thumb=$thumb;
                    }else{
                        $Live_class->thumb=$thumb;
                    }


                } else {
                    //上传失败，返回错误
                    $this->error($upload->getError());
                }

            }

            $Live_class->tenant_id = $tenant_id;
            $result=$Live_class->add(); 
            if($result){
                $action="添加直播分类：{$result}";
                setAdminLog($action);
                $this->resetCache($tenant_id);
                $this->success('添加成功', U('index', array('tenant_id'=>$tenant_id)));
            }else{
                $this->error('添加失败');
            }
        }			
    }		
    function edit(){
        $id=intval($_GET['id']);
        if($id){
            $data=M("live_class")->where("id={$id}")->find();
            $this->assign('data', $data);						
        }else{				
            $this->error('数据传入失败！');
        }								  
        $this->display();				
    }
    
    function edit_post(){
        if(IS_POST){		
            $name=I("name");
            if($name==''){
                $this->error('请填写名称');
            }
            $checkinfo = M('live_class')->where(" id ='{$_POST['id']}'")->find();

            $thumb=I("thumb");
            if($thumb=='' && empty($_FILES['thumb']) ){
                $thumb = $checkinfo['thumb'];
            }

            $Live_class=M("live_class");
            $Live_class->create();
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
                        $Live_class->thumb=$thumb;
                    }else{
                        $Live_class->thumb=$thumb;
                    }


                } else {
                    //上传失败，返回错误
                    $this->error($upload->getError());
                }

            }else{
                $Live_class->thumb=$thumb;
            }

            $result=$Live_class->save(); 
            if($result!==false){
                $action="修改直播分类：{$_POST['id']}";
                setAdminLog($action);
                $this->resetCache($checkinfo['tenant_id']);
                $this->success('修改成功', U('index', array('tenant_id'=>$checkinfo['tenant_id'])));
            }else{
                $this->error('修改失败');
            }
        }			
    }
    
    function resetCache($tenant_id){
        $key='getLiveClass_'.$tenant_id;
        $rules= M("live_class")
            ->where(['tenant_id'=>$tenant_id])
            ->order('orderno asc,id desc')
            ->select();
        setcaches($key,$rules);
        return 1;
    }
}
