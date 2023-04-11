<?php

/**
 * 坐骑管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class CarController extends AdminbaseController {

    private $goGroup = '/widget_car';

    protected $type_list = array(
        '0' => '否',
        '1' => '是',
    );

    function index(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();
        $param['tenant_id'] = $tenant_id;
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;
        $http_post_map = [
            'tenant_id' => intval($tenant_id),
            'page' => intval($p),
            'page_size' => intval($page_size),
            'type' => -1,
        ];

        $map['tenant_id'] = $tenant_id;
        if(isset($param['type']) && $param['type'] != 100){
            $map['type'] = $param['type'];
            $http_post_map['type'] = intval($param['type']);
        }else{
            $param['type'] = -1;
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_car_list';
            $http_post_res = http_post($url, $http_post_map);
            $Data = $http_post_res['Data'];
            $page = $this->page($Data['count'], $page_size);
            $lists = $Data['list'];
        }else{
            $Car=M("car");
            $count=$Car->where($map)->count();
            $page = $this->page($count, $page_size);
            $lists = $Car
                ->where($map)
                ->order("orderno asc, id desc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        }

    	$type_list = $this->type_list;
    	foreach ($lists as $key=>$val){
    	    $lists[$key]['type_name'] = isset($type_list[$val['type']]) ? $type_list[$val['type']] : $val['type'];
        }

    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('type_list',$type_list);
    	$this->display();
    }

    //排序
    public function listorders() {
        $param = I('request.');
        $ids = $_POST['listorders'];
        foreach ($ids as $id => $sort) {
            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'id' => intval($id),
                    'tenant_id' => intval($param['tenant_id']),
                    'sort' => intval($sort),
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/update_car_sort';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    setAdminLog("更新坐骑排序失败：【".$http_post_res['Desc']."】");
                    $this->error('操作失败: '.$http_post_res['Desc'].json_encode($data));
                }
            }else{
                $data['orderno'] = $sort;
                M("car")->where(array('id' => intval($id)))->save($data);
            }
        }

        $action="更新坐骑排序";
        setAdminLog($action);
        delCarlistCache($param['tenant_id']);
        $this->success("排序更新成功！", U('index',array('tenant_id'=>$param['tenant_id'])));
    }

	function add(){
        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_id',getTenantIds());
        $this->display();
	}

	function add_post(){
		if(IS_POST){
            $param = I('post.');
			if($param['name']==""){
				$this->error("请填写坐骑名称");
			}
			if($param['needcoin']==""){
				$this->error("请填写坐骑所需点数");
			}
			if(!is_numeric($param['needcoin'])){
				$this->error("请确认坐骑所需点数");
			}
			if($param['swftime']==""){
				$this->error("请填写动画时长");
			}
			if(!is_numeric($param['swftime'])){
				$this->error("请确认动画时长");
			}
			if($param['words']==""){
				$this->error("请填写进场话术");
			}
            if($param['thumb']==""){
                $this->error("请选择图片");
            }
            if($param['swf']==""){
                $this->error("请选择动画");
            }

            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'tenant_id' => intval($param['tenant_id']),
                    'sort' => intval($param['orderno']),
                    'name' => $param['name'],
                    'type' => intval($param['type']),
                    'slogan' => $param['words'],
                    'image' => $param['thumb'],
                    'image_small' => $param['thumb'],
                    'price' => floatval($param['needcoin']),
                    'swf' => $param['swf'],
                    'swf_time' => floatval($param['swftime']),
                    'extra' => "{}",
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/add_car';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    setAdminLog("添加坐骑失败：【".$http_post_res['Desc']."】");
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
            }else{
                if(M('car')->where(['tenant_id'=>$tenant_id,'name'=>$param['name']])->find()){
                    $this->error('已存在该名称，请重新输入');
                }

                $Car=M("car");
                $Car->create();
                $Car->tenant_id = $tenant_id;
                $Car->act_uid = get_current_admin_id();
                $Car->addtime=time();
                $result=$Car->add();
                if($result!==false){
                }else{
                    setAdminLog("添加坐骑失败：【".$result."】");
                    $this->error('添加失败');
                }
            }
            setAdminLog("添加坐骑：{$param['name']}");
            delCarlistCache($tenant_id);

            $this->success('添加成功', U('index',array('tenant_id'=>$tenant_id)));

		}			
	}		
	function edit(){
        $param = I('param.');
        if(enableGolangReplacePhp() === true){
            // golang替换
            $info = $this->getGolangCarInfo($param['tenant_id'], $param['id']);
        }else {
            $id = intval($_GET['id']);
            if($id){
                $info = M("car")->find($id);
            }else{
                $this->error('数据传入失败！');
            }
        }

        $this->assign('info',$info);
		$this->display();				
	}
	
	function edit_post(){
        $param = I('param.');
		if(IS_POST){
            if($param['name']==""){
                $this->error("请填写坐骑名称");
            }
            if($param['needcoin']==""){
                $this->error("请填写坐骑所需点数");
            }
            if(!is_numeric($param['needcoin'])){
                $this->error("请确认坐骑所需点数");
            }
            if($param['swftime']==""){
                $this->error("请填写动画时长");
            }
            if(!is_numeric($param['swftime'])){
                $this->error("请确认动画时长");
            }
            if($param['words']==""){
                $this->error("请填写进场话术");
            }
            if($param['thumb']==""){
                $this->error("请选择图片");
            }
            if($param['swf']==""){
                $this->error("请选择动画");
            }

            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'id' => intval($param['id']),
                    'tenant_id' => intval($param['tenant_id']),
                    'sort' => intval($param['orderno']),
                    'name' => $param['name'],
                    'type' => intval($param['type']),
                    'slogan' => $param['words'],
                    'image' => $param['thumb'],
                    'image_small' => $param['thumb'],
                    'price' => floatval($param['needcoin']),
                    'swf' => $param['swf'],
                    'swf_time' => floatval($param['swftime']),
                    'extra' => "{}",
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/update_car';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
            }else{
                $info = M('car')->where(['id'=>intval($param['id'])])->find();
                if(M('car')->where(['tenant_id'=>$info['tenant_id'],'id'=>['neq',intval($param['id'])],'name'=>$param['name']])->find()){
                    $this->error('已存在该名称，请重新输入');
                }

                $Car=M("car");
                $Car->create();
                $Car->act_uid = get_current_admin_id();
                $Car->mtime = time();
                $result=$Car->save();
                if($result!==false){

                }else{
                    $this->error('修改失败');
                }
            }
            setAdminLog("修改坐骑：{$_POST['id']}");
            delCarlistCache($param['tenant_id']);

            $this->success('修改成功',U('index',array('tenant_id'=>$info['tenant_id'])));
		}			
	}

    public function getGolangCarInfo($tenant_id, $id){
        $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_car_info';
        $http_post_res = http_post($url, ['tenant_id'=>intval($tenant_id),'id'=>intval($id)]);
        return $http_post_res['Data'];
    }

    function del(){
        $param = I('param.');
        if(enableGolangReplacePhp() === true){
            // golang替换
            $data = array(
                'tenant_id' => intval($param['tenant_id']),
                'id' => intval($param['id']),
            );
            $http_post_res = http_post(goAdminUrl().goAdminRouter().$this->goGroup.'/del_car', $data);
            if($http_post_res['Code'] != 0){
                $this->error('操作失败: '.$http_post_res['Desc']);
            }
            setAdminLog("删除坐骑：{$param['id']}");
            delCarlistCache($param['tenant_id']);
            $this->success('操作成功',U('index',array('tenant_id'=>$param['tenant_id'])));
        }else{
            $id=intval($_GET['id']);
            if($id){
                $info = M('car')->where(['id'=>intval($id)])->find();
                $count = M("users_car")->where(['carid'=>intval($id),['endtime'=>['gt',time()]]])->count();
                if($count > 0){
                    $this->error('用户购买的坐骑在使用中，不能删除');
                }
                $noble_info = M("noble")->where(['car_id'=>intval($id)])->find();
                if($noble_info){
                    $this->error('贵族等级【'.$noble_info['name'].'】在使用中，不能删除');
                }
                $result=M("car")->delete($id);
                if($result){
                    M("users_car")->where(['carid'=>intval($id)])->delete();
                    setAdminLog("删除坐骑：{$id}");
                    delCarlistCache($info['tenant_id']);
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }else{
                $this->error('数据传入失败！');
            }
        }
        $this->display();
    }

    /*
     * 根据租户编号获取坐骑列表
     * */
    public function get_car_list()
    {
        $param = I('param.');
        if (!isset($param['tenant_id']) || !$param['tenant_id']) {
            $this->error('参数错误');
        }
        $car_list = get_carlist($param['tenant_id']);
        $car_list = is_array($car_list) ? $car_list : [];

        $this->success($car_list);
    }
}
