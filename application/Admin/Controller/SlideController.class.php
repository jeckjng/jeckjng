<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SlideController extends AdminbaseController{
	
	protected $slide_model;
	protected $slidecat_model;
	protected $cat_list = array(
        'recom_carousel' => '推荐页面轮播图',
        'recom_top'=>'顶部轮播图',
        'recom_longvideo'=> "长视频列表广告",
    );
	
	function _initialize() {
		parent::_initialize();
		$this->slide_model = D("Common/Slide");
		$this->slidecat_model = D("Common/SlideCat");
		
	}
	
	function index(){
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map = array();

        if(getRoleId() != 1) {
            $map['tenant_id'] = getTenantIds();
        }

        if(isset($param['cat_name']) && $param['cat_name']){
            $map['cat_name'] = $param['cat_name'];
        }

        $count = M('Slide')->where($map)->count();
        $page = $this->page($count);
        $list = M('Slide')->where($map)->limit($page->firstRow . ',' . $page->listRows)->order("listorder ASC")->select();

        $cat_list = $this->cat_list;
        foreach ($list as $key=>$val){
            $list[$key]['cat_name'] = isset($cat_list[$val['cat_name']]) ? $cat_list[$val['cat_name']] : $val['cat_name'];
        }

        $this->assign("page", $page->show('Admin'));
		$this->assign('list',$list);
        $this->assign("cat_list",$cat_list);
        $this->assign('param', $param);
		$this->display();
	}

    function add(){
        $param = I('param.');
        if(IS_POST) {
            if(!$param['cat_name']){
                $this->error('请选择分类');
            }
           
            if(M('slide')->where(['cat_name'=>$param['cat_name'],'slide_name'=>$param['slide_name']])->find()){
                $this->error('该分类下已存在该标题的轮播图');
            }
            $data = array(
                'cat_name' => $param['cat_name'],
                'slide_name' => $param['slide_name'],
                'slide_url' => $param['slide_url'],
                'slide_des' => $param['slide_des'],
                'slide_content' => $param['slide_content'],
                'slide_pic' => $param['slide_pic'],
                'act_uid' => get_current_admin_id(),
                'tenant_id' => getTenantIds(),
                'ctime' => time(),
            );
            if(isset($param['slide_status'])){
                $data['slide_status'] = $param['slide_status'];
            }

            try{
                M('slide')->where(['slide_id'=>$param['slide_id']])->add($data);
            }catch (\Exception $e){
                setAdminLog('新增轮播失败'.$e->getMessage());
                $this->error("操作失败");
            }
            delcache(getTenantIds().'_'.'getSlide');
            setAdminLog('新增轮播图成功');
            $this->success("操作成功", U("slide/index"));
        }

        $this->assign("cat_list",$this->cat_list);
        $this->display();
    }
	
	function edit(){
        $param = I('param.');
        if(IS_POST) {
            if(!$param['slide_id']){
                $this->error('缺少参数');
            }
            if(!$param['cat_name']){
                $this->error('请选择分类');
            }
            $data = array(
                'cat_name' => $param['cat_name'],
                'slide_name' => $param['slide_name'],
                'slide_url' => $param['slide_url'],
                'slide_des' => $param['slide_des'],
                'slide_content' => $param['slide_content'],
                'slide_pic' => $param['slide_pic'],
                'act_uid' =>get_current_admin_id(),
                'mtime' => time(),
            );
            if(isset($param['slide_status'])){
                $data['slide_status'] = $param['slide_status'];
            }
            $info = M('slide')->where(['slide_id'=>$param['slide_id']])->find();
            try{
                M('slide')->where(['slide_id'=>$param['slide_id']])->save($data);
            }catch (\Exception $e){
                setAdminLog('编辑轮播失败【'.$param['slide_id'].'】'.$e->getMessage());
                $this->error("操作失败");
            }
            delcache($info['tenant_id'].'_'.'getSlide');
            setAdminLog('编辑轮播图成功【'.$param['slide_id'].'】');
            $this->success("操作成功", U("slide/index"));
        }

		$info = M('Slide')->where(["slide_id"=>$param['slide_id']])->find();

        $this->assign("info",$info);
        $this->assign("cat_list",$this->cat_list);
		$this->display();
	}
	
	function delete(){
        $param = I('param.');
		if(isset($param['slide_id'])){
            $info = M('slide')->where(['slide_id'=>$param['slide_id']])->find();
		    try{
                M('slide')->where(['slide_id'=>$param['slide_id']])->delete();
            }catch (\Exception $e){
                setAdminLog('删除轮播失败【'.$param['slide_id'].'】'.$e->getMessage());
                $this->error("操作失败");
            }
            delcache($info['tenant_id'].'_'.'getSlide');
            setAdminLog('删除轮播成功【'.$param['slide_id'].'】');
            $this->success("操作成功", U("slide/index"));
		}else{
            $this->error("参数错误");
		}
		
	}

	//排序
	public function listorders() {
		$status = parent::_listorders($this->slide_model);
		if ($status) {
            if(getRoleId() == 1){
                delPatternCacheKeys('*'.'_'.'getSlide');
            }else{
                delcache(getTenantIds().'_'.'getSlide');
            }
            setAdminLog("更新轮播排序");
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
    

}