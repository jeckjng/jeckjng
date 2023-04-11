<?php

/**
 * 直播记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class FileStorageController extends AdminbaseController {

    protected $type_list = array(
        '1' => '本机',
        '2' => '七牛云',
        '3' => '阿里云Oss',
        '4' => '腾讯云',
        );
    protected $status_list = array(
        '0' => '关闭',
        '1' => '启用',
    );

/*CREATE TABLE `cmf_file_storage` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(32) NOT NULL COMMENT '名称',
`type` varchar(20) NOT NULL COMMENT '服务商：1:本机,2:七牛云,3:阿里云,4:腾讯云',
`accesskey` varchar(255) NOT NULL DEFAULT '' COMMENT 'AccessKey',
`secretkey` varchar(255) NOT NULL DEFAULT '' COMMENT 'SecretKey',
`domain` varchar(255) NOT NULL DEFAULT '' COMMENT '空间域名(下载、播放、展示使用)',
`bucket` varchar(255) NOT NULL DEFAULT '' COMMENT '空间名称',
`uphost` varchar(255) NOT NULL DEFAULT '' COMMENT '区域上传域名(endpoint服务端)',
`status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态: 0.关闭，1.启用',
`operated_by` varchar(255) NOT NULL DEFAULT '' COMMENT '操作者',
`tenant_id` int(11) NOT NULL DEFAULT '0' COMMENT '租户id',
`updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
PRIMARY KEY (`id`),
UNIQUE KEY `name` (`name`),
KEY `tenant_id` (`id`),
KEY `type` (`id`),
KEY `status` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;*/



    public function index(){
        $redis = connectionRedis();
        $param = I('param.');
        $map=array();
        //判断是否为超级管理员
        if(getRoleId() == 1){
            if(isset($param['tenant_id']) && $param['tenant_id']!=''){
                $map['tenant_id'] = $param['tenant_id'];
            }else{
                $param['tenant_id'] = '';
            }
        }else{
            $tenant_id = getTenantIds();
            $param['tenant_id'] = $tenant_id;
            $map['tenant_id'] = $tenant_id;
        }
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        if(isset($param['name']) && $param['name']){
            $map['name'] = $param['name'];
        }
        if(isset($param['type']) && $param['type']){
            $map['type'] = $param['type'];
        }
        if(isset($param['status']) && $param['status'] != '100'){
            $map['status'] = $param['status'];
        }else{
            $param['status'] = '100';
        }

        $count = M('file_storage')->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = M('file_storage')
                    ->where($map)
                    ->order("status desc,type asc,id desc")
                    ->limit($page->firstRow . ',' . $page->listRows)
                    ->select();

    	$status_list = $this->status_list;
        $type_list = $this->type_list;
        foreach($lists as $key=>$val){
            $userinfo = getUserInfo($val['act_uid']);
            $lists[$key]['act_uid'] = $userinfo['user_login'];
            $lists[$key]['status_name'] = isset($status_list[$val['status']]) ? $status_list[$val['status']] : $val['status'];
            $lists[$key]['type_name'] = isset($type_list[$val['type']]) ? $type_list[$val['type']] : $val['type'];
            $lists[$key]['domain_rows'] = count(explode("\n",$lists[$key]['domain']));
        }

    	$this->assign('lists', $lists);
        $this->assign('status_list', $status_list);
        $this->assign('type_list', $type_list);
        $this->assign("role_id", getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param', $param);
    	$this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
     * 新增
     * */
    public function add(){
        if(IS_POST){
            $param = I('post.');
            foreach ($param as $key=>$val){
                $param[$key] = trim($val);
            }
            if(!$param['type']){
                $this->error('请选择服务商');
            }
            if(!$param['name']){
                $this->error('名称不能为空');
            }
            if(mb_strlen($param['name']) > 32){
                $this->error('流名称长度不能超过32');
            }

            $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

            if(M("file_storage")->where(['tenant_id'=>intval($tenant_id), 'name'=>$param['name']])->find()){
                $this->error('名称已存在');
            }

            try{
                $data['name'] = $param['name'];
                $data['type'] = $param['type'];
                $data['accesskey'] = $param['accesskey'];
                $data['secretkey'] = $param['secretkey'];
                $data['domain'] = $param['domain'];
                $data['bucket'] = $param['bucket'];
                $data['uphost'] = $param['uphost'];
                $data['status'] = intval($param['status']);
                $data['operated_by'] = get_current_admin_id();
                $data['tenant_id'] = intval($tenant_id);

                $res = M("file_storage")->add($data);
            }catch (\Exception $e){
                $error_msg = $e->getMessage();
                setAdminLog('新增文件存储 失败，'.$error_msg);
                $this->error('操作失败');
            }
            delPushpullList(); // 清除文件存储列表缓存
            setAdminLog('新增文件存储 成功【'.$param['name'].'】');
            $this->success('操作成功',U('index'));
        }
        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $this->assign('type_list', $this->type_list);
        $this->assign("role_id", getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('tenant_id',$tenant_id);
        $this->display();
    }

    /*
     * 编辑
     * */
    public function edit(){
        if(IS_POST){
            $param = I('post.');
            foreach ($param as $key=>$val){
                $param[$key] = trim($val);
            }
            if(!$param['id']){
                $this->error('缺少id');
            }
            if(!$param['type']){
                $this->error('请选择服务商');
            }
            if(!$param['name']){
                $this->error('名称不能为空');
            }
            if(mb_strlen($param['name']) > 32){
                $this->error('名称长度不能超过32');
            }
            $id = $param['id'];

            $info = M("file_storage")->where(['id'=>intval(I('id'))])->find();
            if(!$info){
                $this->error('参数错误');
            }

            if(M("file_storage")->where(['tenant_id'=>intval($info['tenant_id']),'id'=>['neq',$id],'name'=>$param['name']])->find()){
                $this->error('名称已存在');
            }

            try{
                $data = array();
                if($param['name'] != $info['name']){
                    $data['name'] = $param['name'];
                }
                if($param['type'] != $info['type']){
                    $data['type'] = $param['type'];
                }
                if($param['accesskey'] != $info['accesskey']){
                    $data['accesskey'] = $param['accesskey'];
                }
                if($param['secretkey'] != $info['secretkey']){
                    $data['secretkey'] = $param['secretkey'];
                }
                if($param['domain'] != $info['domain']){
                    $data['domain'] = $param['domain'];
                }
                if($param['bucket'] != $info['bucket']){
                    $data['bucket'] = $param['bucket'];
                }
                if($param['uphost'] != $info['uphost']){
                    $data['uphost'] = $param['uphost'];
                }
                if($param['status'] != $info['status']){
                    $data['status'] = intval($param['status']);
                }
                if(get_current_admin_id() != $info['operated_by']){
                    $data['operated_by'] = get_current_admin_id();
                }

                $res = M("file_storage")->where(['id'=>intval($id)])->save($data);
            }catch (\Exception $e){
                $error_msg = $e->getMessage();
                setAdminLog('修改文件存储 失败，'.$error_msg);
                $this->error('操作失败');
            }
            delPushpullList(); // 清除文件存储列表缓存
            setAdminLog('修改文件存储 成功【'.$param['name'].'】');
            $this->success('操作成功',U('index'));
        }
        if(!I('id')){
            $this->error('缺少参数');
        }
        $info = M("file_storage")->where(['id'=>intval(I('id'))])->find();
        $info['domain_rows'] = count(explode("\n",$info['domain'])) + 1;

        $this->assign('info', $info);
        $this->assign('type_list', $this->type_list);
        $this->assign('id', I('id'));
        $this->display();
    }

    /*
     * 删除
     * */
    public function del(){
        if(!I('id')){
            $this->error('缺少参数');
        }
        $info = M("file_storage")->where(['id'=>intval(I('id'))])->find();
        if(!$info){
            $this->error('参数错误');
        }
        try{
            $res = M("file_storage")->where(['id'=>intval(I('id'))])->delete();
        }catch (\Exception $e){
            $error_msg = $e->getMessage();
            setAdminLog('删除文件存储 失败，'.$error_msg);
            $this->error('操作失败');
        }
        delPushpullList(); // 清除文件存储列表缓存
        setAdminLog('删除文件存储 成功【'.I('name').'】');
        $this->success('操作成功',U('index'));
    }

}
