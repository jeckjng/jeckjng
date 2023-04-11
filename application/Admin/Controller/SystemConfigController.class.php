<?php

/**
 * 经验等级
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

class SystemConfigController extends AdminbaseController {

    private $key_list = array(
        'forbiden_dmain' => '禁用域名',
        'menu_auth_rule_syn' => '菜单同步地址',
        'menu_auth_ruleaction' => '是否记录菜单操作sql',
        'go_app_url' => 'golang app 接口地址',
        'go_caller_url' => 'golang caller 接口地址',
        'go_admin_url' => 'golang admin 接口地址',
        'socket_node_count' => 'socket节点数量',
        'open_tenant_go_api' => '是否开启租户信息golang接口',
        'open_tenant_config_go_api' => '是否开启网站信息golang接口',
        'open_storage_go_api' => '是否开启文件存储golang接口',
        'open_playback_address_go_api' => '是否开启播放下载配置golang接口',
        'open_short_video_go_api' => '是否开启短视频golang接口',
        'open_users_go_api' => '是否开启用户信息golang接口',
        'open_vip_go_api' => '是否开启vip golang接口',
        'open_atmosphere_go_api' => '是否开启直播氛围 golang接口',
    );

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
        $param = I('param.');
        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 100;
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $map['tag'] = 1;
        if(isset($param['key']) && $param['key'] != ''){
            $map['key'] = $param['key'];
        }

        if(getRoleId() != 1){
            $list = [];
        }else{
            $count = M('kvconfig')->where($map)->count();
            $page = $this->page($count, $page_size);
            $list = M('kvconfig')
                ->where($map)
                ->order("id desc")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();
        }
        $tag_list = $this->tag_list;
        $key_list = $this->key_list;
        foreach ($list as $key=>$val){
            $list[$key]['tag_name'] = isset($tag_list[$val['tag']]) ? $tag_list[$val['tag']] : $val['tag'];
            $list[$key]['key_name'] = isset($key_list[$val['key']]) ? $key_list[$val['key']] : $val['key'];
        }

    	$this->assign('list', $list);
    	$this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->assign('tag_list', $tag_list);
        $this->assign('key_list', $key_list);
    	$this->display();
    }

    public function add(){
		if(IS_POST){
            $param = I('post.');
            if(!isset($param['key']) || !$param['key']){
                $this->error('请选择类型');
            }
            if(mb_strlen($param['key']) > 64){
                $this->error('键名太长，键名长度不能已超出64');
            }
            if(!isset($param['val']) || $param['val'] == ''){
                $this->error('请输入值');
            }

            if(M('kvconfig')->where(['tag'=>1, 'key'=>$param['key']])->find()){
                $this->error('该已存在该键名，请重新输入');
            }
            $data = array(
                'tag' => 1,
                'key' => trim($param['key']),
                'val' => trim($param['val']),
                'desc' => trim($param['desc']),
                'operated_by' => get_current_admin_user_login(),
            );

            try{
                M('kvconfig')->add($data);
            }catch (\Exception $e){
                setAdminLog('新增系统配置失败：'.$e->getMessage());
                $this->error('操作失败');
            }
            setAdminLog('新增系统配置【'.$param['key'].'】');
            delKvconfigVal(1, $param['key']); // 清除redis缓存

            $this->success('操作成功', U('index'));
		}

        $this->assign('key_list', $this->key_list);
        $this->display();
    }

    public function edit(){
        $param = I('param.');
        if(IS_POST){
            if(!isset($param['id']) || !$param['id']){
                $this->error('参数错误');
            }
            if(!isset($param['val']) || $param['val'] == ''){
                $this->error('请输入值');
            }

            $info = M('kvconfig')->where(['id'=>intval($param['id'])])->find();
            $data = array(
                'val' => trim($param['val']),
                'desc' => trim($param['desc']),
                'operated_by' => get_current_admin_user_login(),
            );

            try{
                M('kvconfig')->where(['id'=>intval($param['id'])])->save($data);
            }catch (\Exception $e){
                setAdminLog('编辑系统配置失败：'.$e->getMessage());
                $this->error('操作失败');
            }
            setAdminLog('编辑系统配置【'.$info['key'].'】');
            delKvconfigVal(1, $info['key']); // 清除redis缓存

            $this->success('操作成功', U('index'));
        }

        if(!isset($param['id'])){
            $this->error('参数错误');
        }
        $info = M('kvconfig')->where(['id'=>intval($param['id'])])->find();

        $this->assign('info',$info);
        $this->assign('tag_list', $this->tag_list);
        $this->display();
	}

    public function del(){
        $param = I('param.');
        if(IS_AJAX && isset($param['id'])){
            $info = M('kvconfig')->where(['tag'=>1, 'id'=>intval($param['id'])])->find();
            if(!$info){
                $this->error('参数错误');
            }
            try{
                $res = M('kvconfig')->where(['id'=>intval($param['id'])])->delete();
            }catch (\Exception $e){
                setAdminLog('删除系统配置失败：'.$e->getMessage());
                $this->error('操作失败');
            }
            delKvconfigVal(1, $info['key']); // 清除redis缓存
            setAdminLog('删除系统配置成功【'.$info['key'].'】');

            $this->success('操作成功',U('index'));
        }else{
            $this->error('参数错误');
        }
    }

}
