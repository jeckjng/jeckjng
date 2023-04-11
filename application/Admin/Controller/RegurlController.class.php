<?php

/**
 * 红包
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RegurlController extends AdminbaseController {
    function index(){

        $map['tenant_id']=getTenantIds();
        $RegurlModel  =  M('users_reg_url');
        $count=$RegurlModel->where($map)->count();
        $page = $this->page($count, 20);
        $lists = $RegurlModel
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->assign('lists', $lists);
        $this->assign('formget', $_GET);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }

    function add(){
        if(IS_POST) {
            $reg_url = I('reg_url');
            $reg_key =  I('reg_key');
            $data= ['reg_key' =>$reg_key,
                'reg_url' => $reg_url ,
                'tenant_id' => getTenantIds(),
                'addtime' => time(),
                'status'=> 1,
                ];
            $RegurlModel  =  M('users_reg_url');
            $key  =$RegurlModel->where(['reg_key'=> $reg_key])->find();
            if ($key){
                $this->error('此key已存在，请重新生成');
            }
            $res  =$RegurlModel->add($data);
            if ($res){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }

        }
        $this->display();
    }
    public function upstatus(){
        if(IS_POST) {
            $id = I('id');
            $status =  I('status');

            $RegurlModel  =  M('users_reg_url');
            $res  =$RegurlModel->where(['id'=> $id])->save(['status'=> $status]);
            if ($res !== false){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }

        }

    }
}