<?php

/**
 * 礼物
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use PhpParser\Node\Expr\Variable;

class GiftController extends AdminbaseController {

    private $goGroup = '/live_gift';

    private $type_list = array(
                            "0"=>"普通礼物",
                            "1"=>"豪华礼物",
                            "2"=>"守护礼物"
                        );
    private $mark_list = array(
                            "0"=>"普通",
                            "1"=>"热门",
                            "2"=>"守护"
                        );
    private $swftype_list = array(
                                "0"=>"GIF",
                                "1"=>"SVGA"
                            );

    public function index(){
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
            'mark' => -1,
            'swf_type' => -1,
            'name' => "",
        ];

        $map['tenant_id'] = $tenant_id;
        if(isset($param['type']) && $param['type'] != ''){
            $map['type'] = $param['type'];
            $http_post_map['type'] = intval($param['type']);
        }else{
            $param['type'] = -1;
        }
        if(isset($param['mark']) && $param['mark'] != ''){
            $map['mark'] = $param['mark'];
            $http_post_map['mark'] = intval($param['mark']);
        }else{
            $param['mark'] = -1;
        }
        if(isset($param['swftype']) && $param['swftype'] != ''){
            $map['swftype'] = $param['swftype'];
            $http_post_map['swf_type'] = intval($param['swftype']);
        }else{
            $param['swftype'] = -1;
        }
        if(isset($param['giftname']) && $param['giftname'] != ''){
            $map['giftname'] = $param['giftname'];
            $http_post_map['name'] = $param['giftname'];
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        if(enableGolangReplacePhp() === true){
            // golang替换
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_gift_list';
            $http_post_res = http_post($url, $http_post_map);
            $Data = $http_post_res['Data'];
            $page = $this->page($Data['count'], $page_size);
            $lists = $Data['list'];
        }else {
            $model = M("gift");
            $count = $model->where($map)->count();
            $page = $this->page($count, 20);
            $lists = $model->where($map)
                ->order("orderno, addtime desc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        }

    	$this->assign('lists', $lists);
    	$this->assign('type_list', $this->type_list);
    	$this->assign('mark_list', $this->mark_list);
    	$this->assign('swftype_list', $this->swftype_list);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
    	$this->display();
    }

    //排序
    public function listorders() {
        $param = I('request.');
        $enable_golang_replace_php = getSystemConf('enable_golang_replace_php');
        $go_admin_url = getSystemConf('go_admin_url');
        $ids = $_POST['listorders'];
        $id = 0;
        foreach ($ids as $id => $sort) {
            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'id' => intval($id),
                    'tenant_id' => intval($param['tenant_id']),
                    'sort' => intval($sort),
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/update_gift_sort';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    setAdminLog("更新礼物排序失败：【".$http_post_res['Desc']."】");
                    $this->error('操作失败: '.$http_post_res['Desc'].json_encode($data));
                }
            }else {
                $data['orderno'] = $sort;
                M("gift")->where(array('id' => intval($id)))->save($data);
            }
        }

        $action="更新礼物排序";
        setAdminLog($action);
        $this->resetcache($param['tenant_id']);
        $this->success("排序更新成功！", U('index',array('tenant_id'=>$param['tenant_id'])));
    }

    public function add(){
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $this->assign('tenant_list',getTenantList());
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_id',$tenant_id);
        $this->assign('type_list', $this->type_list);
        $this->assign('mark_list', $this->mark_list);
        $this->assign('swftype_list', $this->swftype_list);
        $this->display();
    }

    public function add_post(){
        if(IS_POST){
            $param = I('param.');
            if ($param['needcoin'] < 1){
                $this->error('所需点数需不能小于1');
            }

            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'tenant_id' => intval($param['tenant_id']),
                    'mark' => intval($param['mark']),
                    'type' => intval($param['type']),
                    'name' => $param['giftname'],
                    'price' =>  floatval($param['needcoin']),
                    'icon_mini' => isset($param['gifticon_mini']) ? $param['gifticon_mini'] : '',
                    'icon' => $param['gifticon'],
                    'sort' => intval($param['orderno']),
                    'swf' => $param['swf'],
                    'swf_time' => floatval($param['swftime']),
                    'swf_type' => intval($param['swftype']),
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/add_gift';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    setAdminLog("添加礼物失败：【".$http_post_res['Desc']."】");
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
            }else {
                $info = M('gift')->where(['tenant_id' => intval($tenant_id), 'giftname' => $param['giftname']])->find();
                if ($info) {
                    $this->error('已存在该名称，请重新输入');
                }

                $data = array(
                    'mark' => intval($param['mark']),
                    'type' => intval($param['type']),
                    'sid' => isset($param['sid']) ? intval($param['sid']) : 0,
                    'giftname' => $param['giftname'],
                    'needcoin' => intval($param['needcoin']),
                    'gifticon_mini' => isset($param['gifticon_mini']) ? $param['gifticon_mini'] : '',
                    'gifticon' => $param['gifticon'],
                    'orderno' => intval($param['orderno']),
                    'addtime' => time(),
                    'swf' => $param['swf'],
                    'swftime' => floatval($param['swftime']),
                    'swftype' => intval($param['swftype']),
                    'tenant_id' => intval($tenant_id),
                    'act_uid' => get_current_admin_id(),
                    'mtime' => time(),
                );

                try {
                    M('gift')->add($data);
                } catch (\Exception $e) {
                    setAdminLog('添加礼物失败：' . $e->getMessage());
                    $this->error('操作失败');
                }
            }
            $this->resetcache($tenant_id);
            $this->success('操作成功',U('index',array('tenant_id'=>$tenant_id)));
        }
    }

    public function edit(){
        $param = I('param.');
        if(enableGolangReplacePhp() === true){
            // golang替换
            $info = $this->getGolangGiftInfo($param['tenant_id'], $param['id']);
        }else {
            $id = intval($_GET['id']);
            if ($id) {
                $info = M("gift")->find($id);
            } else {
                $this->error('数据传入失败！');
            }
        }

        $this->assign('info', $info);
        $this->assign('type_list', $this->type_list);
        $this->assign('mark_list', $this->mark_list);
        $this->assign('swftype_list', $this->swftype_list);
        $this->display();
    }

    public function edit_post(){
        $param = I('param.');
        if(IS_POST){
            if ($param['needcoin'] < 1){
                $this->error('所需点数需不能小于1');
            }

            if(enableGolangReplacePhp() === true){
                // golang替换
                $data = array(
                    'id' => intval($param['id']),
                    'tenant_id' => intval($param['tenant_id']),
                    'mark' => intval($param['mark']),
                    'type' => intval($param['type']),
                    'name' => $param['giftname'],
                    'price' =>  floatval($param['needcoin']),
                    'icon_mini' => isset($param['gifticon_mini']) ? $param['gifticon_mini'] : '',
                    'icon' => $param['gifticon'],
                    'sort' => intval($param['orderno']),
                    'swf' => $param['swf'],
                    'swf_time' => floatval($param['swftime']),
                    'swf_type' => intval($param['swftype']),
                    'operated_by' => get_current_admin_user_login(),
                );
                $url = goAdminUrl().goAdminRouter().$this->goGroup.'/update_gift';
                $http_post_res = http_post($url, $data);
                if($http_post_res['Code'] != 0){
                    $this->error('操作失败: '.$http_post_res['Desc']);
                }
            }else {
                $info = M('gift')->where(['id' => intval($param['id'])])->find();
                if (!$info) {
                    $this->error('参数id错误');
                }
                $tenant_id = $info['tenant_id'];

                $data = array(
                    'mark' => intval($param['mark']),
                    'type' => intval($param['type']),
                    'sid' => isset($param['sid']) ? intval($param['sid']) : 0,
                    'giftname' => $param['giftname'],
                    'needcoin' => intval($param['needcoin']),
                    'gifticon_mini' => isset($param['gifticon_mini']) ? $param['gifticon_mini'] : '',
                    'gifticon' => $param['gifticon'],
                    'orderno' => intval($param['orderno']),
                    'swf' => $param['swf'],
                    'swftime' => floatval($param['swftime']),
                    'swftype' => intval($param['swftype']),
                    'act_uid' => get_current_admin_id(),
                    'mtime' => time(),
                );

                try {
                    M('gift')->where(['id' => intval($param['id'])])->save($data);
                } catch (\Exception $e) {
                    setAdminLog('编辑礼物失败：' . $e->getMessage());
                    $this->error('操作失败');
                }
            }
            $this->resetcache($tenant_id);
            $this->success('操作成功', U('index',array('tenant_id'=>$tenant_id)));
        }
    }

    public function getGolangGiftInfo($tenant_id, $id){
        $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_gift_info';
        $http_post_res = http_post($url, ['tenant_id'=>intval($tenant_id),'id'=>intval($id)]);
        return $http_post_res['Data'];
    }

    public function del(){
        $param = I('param.');
        if(enableGolangReplacePhp() === true){
            // golang替换
            $data = array(
                'tenant_id' => intval($param['tenant_id']),
                'id' => intval($param['id']),
            );
            $http_post_res = http_post(goAdminUrl().goAdminRouter().$this->goGroup.'/del_gift', $data);
            if($http_post_res['Code'] != 0){
                $this->error('操作失败: '.$http_post_res['Desc']);
            }
            setAdminLog("删除礼物：{$param['id']}");
            $this->resetcache($param['tenant_id']);
            $this->success('操作成功',U('index',array('tenant_id'=>$param['tenant_id'])));
        }else {
            $id = intval($_GET['id']);
            if ($id) {
                $info = M("gift")->where(array('id' => intval($id)))->find();
                $tenant_id = $info['tenant_id'];
                $result = M("gift")->delete(intval($id));
                if ($result) {
                    $action = "删除礼物：{$id}";
                    setAdminLog($action);
                    $this->resetcache($tenant_id);

                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
            } else {
                $this->error('数据传入失败！');
            }
        }
        $this->display();
    }

    public function resetcache($tenant_id){
        $tenant_id = $tenant_id ? $tenant_id : getTenantIds();
        $key3 = 'getGiftList_'.$tenant_id;
        $key2 = 'getGiftall_'.$tenant_id;

        if(enableGolangReplacePhp() === true){
            $url = goAdminUrl().goAdminRouter().$this->goGroup.'/get_gift_list_all';
            $http_post_map = [
                'tenant_id' => intval($tenant_id),
            ];
            $http_post_res = http_post($url, $http_post_map);
            $list = $http_post_res['Data'];
        }else {
            $list = M('gift')->field("*")->where('tenant_id=' . $tenant_id)->order("orderno asc,addtime desc")->select();
        }
		foreach($list as $key=>$val){
            $list[$key]['gifticon']=get_upload_path($val['gifticon']);
            $list[$key]['gifticon_mini']=get_upload_path($val['gifticon_mini']);
            $list[$key]['swf']=get_upload_path($val['swf']);
		}
        if($list){
            setcaches($key3,$list);
            setcaches($key2,$list);
        }
        return 1;
    }

    public function sort_index(){
    	$gift_sort=M("gift_sort");
    	$count=$gift_sort->count();
    	$page = $this->page($count, 20);
    	$lists = $gift_sort
    	//->where()
    	->order("orderno asc")
    	->limit($page->firstRow . ',' . $page->listRows)
    	->select();
    	$this->assign('lists', $lists);
    	$this->assign("page", $page->show('Admin'));
    	
    	$this->display();
    }

    public function sort_del(){
        $id=intval($_GET['id']);
        if($id){
            $result=M("gift_sort")->delete($id);
                if($result){
                    $action="删除礼物分类：{$id}";
                    setAdminLog($action);
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
    public function sort_listorders() {
        $ids = $_POST['listorders'];
        foreach ($ids as $key => $r) {
            $data['orderno'] = $r;
            M("gift_sort")->where(array('id' => $key))->save($data);
        }
				
        $status = true;
        if ($status) {
            $action="更新礼物分类排序";
            setAdminLog($action);
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    public function sort_add(){
		    	
    	$this->display();
    }

    public function do_sort_add(){
        if(IS_POST){
            if($_POST['sortname']==''){
                $this->error('分类名称不能为空');
            }

            $gift_sort=M("gift_sort");
            $gift_sort->create();
            $gift_sort->addtime=time();

            $result=$gift_sort->add();
            if($result){
                $action="添加礼物分类：{$result}";
                setAdminLog($action);
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
    }

    public function sort_edit(){
        $id=intval($_GET['id']);
        if($id){
            $sort	=M("gift_sort")->find($id);
            $this->assign('sort', $sort);
        }else{
            $this->error('数据传入失败！');
        }
    	$this->display();
    }

    public function do_sort_edit(){
        if(IS_POST){
            $gift_sort=M("gift_sort");
            $gift_sort->create();
            $result=$gift_sort->save();
            if($result){
                $action="编辑礼物分类：{$_POST['id']}";
                setAdminLog($action);
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
    }
}
