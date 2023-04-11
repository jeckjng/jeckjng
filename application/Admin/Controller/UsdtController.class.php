<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/6/2
 * Time: 20:29
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

class UsdtController extends AdminbaseController
{
    private $network_type_list = array(
        '1' => 'TRC20',
        '2' => 'ERC20',
    );

    function _initialize()
    {
        parent::_initialize();

    }
    public function index()
    {
        $param = I('param.');
        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());
        $param['tenant_id'] = $tenant_id;

        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;


        $map['tenant_id'] = $tenant_id;
        if(isset($param['network_type']) && $param['network_type'] !=''){
            $map['network_type'] = $param['network_type'];
        }
        if(isset($param['uid']) && $param['uid'] !=''){
            $map['uid'] = $param['uid'];
        }
        if(isset($param['user_login']) && $param['user_login'] !=''){
            $map['user_login'] = $param['user_login'];
        }
        if(isset($param['address']) && $param['address'] !=''){
            $map['address'] = $param['address'];
        }

        $model = M('usdt_address');
        $count = $model->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = $model->where($map)->order('id asc')->limit($page->firstRow . ',' . $page->listRows)->select();

        foreach ($list as $key=>$val){
            $list[$key]['create_time_date'] = date('Y-m-d H:i:s', $val['create_time']);
            $list[$key]['update_time_date'] = $val['update_time'] ? date('Y-m-d H:i:s', $val['update_time']) : '-';
        }

        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $this->assign("list",$list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('network_type_list',$this->network_type_list);
        $this->display();
    }

    public function edit(){
        $param = I('param.');
        if(IS_POST){
            if(!isset($param['id']) || !$param['id']){
                $this->error('参数错误');
            }
            if(!isset($param['address']) || !$param['address']){
                $this->error('USDT地址 不能为空');
            }
            if(!isset($param['network_type']) || !$param['network_type']){
                $this->error('网络类型 不能为空');
            }
            if(!isset($param['qrcode']) || !$param['qrcode']){
                $this->error('USDT二维码 不能为空');
            }

            $info = M('usdt_address')->where(['id'=>intval($param['id'])])->find();

            if(M('usdt_address')->where(['uid'=>$info['uid'], 'id'=>['neq',intval($param['id'])], 'address'=>$param['address']])->find()){
                $this->error('已存在该USDT地址');
            }

            $data = array(
                'address' => trim($param['address']),
                'network_type' => trim($param['network_type']),
                'qrcode' => trim(urldecode($param['qrcode'])),
                'update_time' => time(),
                'operated_by' => get_current_admin_user_login(),
            );

            try{
                M('usdt_address')->where(['id'=>intval($param['id'])])->save($data);
            }catch (\Exception $e){
                setAdminLog('【编辑用户USDT】失败：'.$e->getMessage());
                $this->error('操作失败');
            }
            setAdminLog('【编辑用户USDT】成功：'.json_encode($param, JSON_UNESCAPED_UNICODE));
            $this->success('操作成功', U('index',array('tenant_id'=>$info['tenant_id'])));
        }

        $id = I('id');
        if(!$id){
            $this->error('参数错误');
        }
        $info = M('usdt_address')->where(['id'=>intval($id)])->find();

        $this->assign('info',$info);
        $this->assign('network_type_list',$this->network_type_list);
        $this->display();
    }

    public function del(){
        $param = I('param.');

        $id = intval($param['id']);
        if($id){
            $info = M('usdt_address')->where(['id'=>intval($id)])->find();
            try{
                $res = M('usdt_address')->where(['id'=>intval($id)])->delete();
            }catch (\Exception $e){
                setAdminLog('【删除用户USDT】失败：'.$e->getMessage());
                $this->error('操作失败');
            }
            setAdminLog('【删除用户USDT】成功：'.json_encode($info));
            $this->success('操作成功', U('index', array('tenant_id'=>$info['tenant_id'])));
        }else{
            $this->error('参数错误');
        }

    }

}