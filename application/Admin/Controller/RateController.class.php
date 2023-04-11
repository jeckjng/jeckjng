<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/6/2
 * Time: 20:29
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RateController extends AdminbaseController
{

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
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }
        $map['tenant_id'] = $tenant_id;

        $count = M('rate')->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = M('rate')
            ->where($map)
            ->order('sort asc, id asc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->assign("list",$list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('param',$param);
        $this->assign('tenant_id',$tenant_id);
        $this->display();
    }

    /**
     * 修改状态
     */
    public  function update_status(){
        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            $this->error("缺少参数");
        }
        if(!isset($param['tenant_id']) || !$param['tenant_id']){
            $this->error("缺少参数");
        }

        try {
            M()->startTrans();
            $data = array(
                'status'=>intval($param['status']),
                'mtime'=>time(),
            );
            M('rate')->where(["id"=>intval($param['id']), "tenant_id"=>intval($param['tenant_id'])])->save($data);
            M()->commit();
        }catch (\Exception $e){
            M()->rollback();
            setAdminLog("更新汇率失败【".json_encode($param)."】".$e->getMessage());
            $this->error("操作失败！");
        }
        setAdminLog("更新汇率成功【".json_encode($param)."】");
        $this->success("操作成功！", U('index',array('tenant_id'=>$param['tenant_id'])));
    }

    /*
     * 添加
     */
    public function add()
    {
        if (IS_POST) {
            $param = I('post.');
            if (!isset($param['code']) || !$param['code']) {
                $this->error("请选择币种");
            }
            if (!isset($param['rate']) || $param['rate'] == "") {
                $this->error("请输入汇率");
            }
            if (!isset($param['status'])) {
                $this->error("请选择状态 ");
            }
            if (!isset($param['icon']) || $param['icon'] == "") {
                $this->error("请输入或上传图标");
            }
            $param['tenant_id'] = !empty($param['tenant_id'])?$param['tenant_id']:getTenantIds();
            $info = M('rate')->where(['tenant_id'=>intval($param['tenant_id']), 'code'=>trim($param['code'])])->find();
            if ($info){
                $this->error("此币种已添加！");
            }

            $current_list = currency_list();
            try {
                M()->startTrans();
                $data = array(
                    'name'          => trim($current_list[$param['code']]['name']),
                    'code'          => trim($param['code']),
                    'rate'          => floatval($param['rate']),
                    'status'        => intval($param['status']),
                    'icon'          => trim($param['icon']),
                    'is_virtual'    => intval($current_list[$param['code']]['is_virtual']),
                    'tenant_id'     => intval($param['tenant_id']),
                    'operated_by'   => trim(get_current_admin_user_login()),
                    'ctime'         => time(),
                    'mtime'         => time(),
                );
                M('rate')->add($data);
                M()->commit();
            } catch (\Exception $e) {
                M()->rollback();
                setAdminLog("添加汇率失败【" . json_encode($param) . "】" . $e->getMessage());
                $this->error("操作失败！");
            }
            setAdminLog("添加汇率成功【" . json_encode($param) . "】");
            delRateListCache($param['tenant_id']);
            $this->success("操作成功！", U('index',array('tenant_id'=>$param['tenant_id'])));
        }

        $tenant_id = isset($param['tenant_id']) ? $param['tenant_id'] : getTenantIds();

        $this->assign('tenant_id',$tenant_id);
        $this->assign('role_id',getRoleId());
        $this->assign('tenant_list',getTenantList());
        $this->assign('currency_list', currency_list());
        $this->display();
    }

    /*
      * 编辑
      */
    public function edit(){
        if(IS_POST){
            $param = I('post.');
            if(!isset($param['code']) || !$param['code']){
                $this->error("请选择币种");
            }
            if(!isset($param['rate']) || $param['rate'] == ""){
                $this->error("请输入汇率");
            }
            if(!isset($param['status'])){
                $this->error("请选择状态 ");
            }
            if(!isset($param['icon']) || $param['icon'] == ""){
                $this->error("请输入或上传图标");
            }

            $info = M('rate')->where(['tenant_id'=>intval($param['tenant_id']), 'code'=>trim($param['code']), 'id'=>array('neq',intval($param['id']))])->find();
            if ($info){
                $this->error("此币种已添加！");
            }

            $current_list = currency_list();
            try {
                M()->startTrans();
                $data = array(
                    'name'          =>  trim($current_list[$param['code']]['name']),
                    'code'          =>  trim($param['code']),
                    'rate'          =>  floatval($param['rate']),
                    'status'        =>  intval($param['status']),
                    'icon'          =>  trim($param['icon']),
                    'is_virtual'    =>  intval($current_list[$param['code']]['is_virtual']),
                    'operated_by'   =>  trim(get_current_admin_user_login()),
                    'mtime'         =>  time(),
                );
                M('rate')->where(["id"=>intval($param['id']), "tenant_id"=>intval($param['tenant_id'])])->save($data);
                M()->commit();
            }catch (\Exception $e){
                M()->rollback();
                setAdminLog("添加汇率失败【".json_encode($param)."】".$e->getMessage());
                $this->error("操作失败！");
            }
            setAdminLog("添加汇率成功【".json_encode($param)."】");
            delRateListCache($param['tenant_id']);
            $this->success("操作成功！", U('index',array('tenant_id'=>$param['tenant_id'])));
        }

        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            $this->error("缺少参数");
        }
        if(!isset($param['tenant_id']) || !$param['tenant_id']){
            $this->error("缺少参数");
        }
        $info = M("rate")->where(["id"=>intval($param['id']), "tenant_id"=>intval($param['tenant_id'])])->find();

        $this->assign("info", $info);
        $this->assign('role_id', getRoleId());
        $this->assign('tenant_list', getTenantList());
        $this->assign('currency_list', currency_list());
        $this->display();
    }


    /**
     * 添加渠道
     */
    /*
    public function rate_add_post(){

        $name = I("post.name");
        $code = I("post.code");
        $status = I("post.status");
        $rate= I("post.rate");
        $is_virtual= I("post.is_virtual");
        $id = I("post.id");
        $rateModel = M("rate");
        if ($id){
            $rateInfo = $rateModel->where(['name' =>$name,'id'=>array('neq',$id),'tenant_id'=>getTenantIds() ])->find();
        }else{
            $rateInfo = $rateModel->where(['name' =>$name,'tenant_id'=>getTenantIds() ])->find();
        }

        if ($rateInfo){
            $this->error("此币种已添加！");
        }


        if($_FILES['coin']) {
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
            $upload = new \Think\Upload($config);//
            $info = $upload->upload();
            //开始上传
            if ($info) {
                //上传成功
                //写入附件数据库信息
                $first = array_shift($info);
                if (!empty($first['url'])) {
                    $url = $first['url'];
                } else {
                    $url = C("TMPL_PARSE_STRING.__UPLOAD__") . $savepath . $first['savename'];
                }
                $url = str_replace("http", "https", $url);

            } else {
                //上传失败，返回错误
                $this->error($upload->getError());
            }
        }else{
            $url = I("post.coin_edit");
        }
        if ($id){
            $res = $rateModel->where(array('id' =>$id))->save(
                array(
                    'name' => $name,
                    'code' => $code ,
                    'status' =>$status,
                    'rate' => $rate,
                    'icon' => $url,
                    'is_virtual' =>$is_virtual,
                )
            );
            $action = '修改币种';
            setAdminLog($action);
        }else{
            $rateModel->create();
            $res = $rateModel->add(
                array(
                    'name' => $name,
                    'code' => $code ,
                    'status' =>$status,
                    'rate' => $rate,
                    'icon' => 111,
                    'is_virtual' =>$is_virtual,
                    'tenant_id'=>getTenantIds()
                )
            );
            $action = '添加币种';
            setAdminLog($action);
        }
        if(!$res){
            $this->error("操作失败！");
        }
        delRateListCache();
        $this->success("操作成功！", U("rate/index"));
    }*/

    /*
     * 更新排序
     * */
    public function update_sort(){
        if(!IS_POST){
            $this->error("请求方式错误");
        }
        $param = I('post.');
        if(!isset($param['tenant_id']) || !$param['tenant_id']){
            $this->error("缺少参数");
        }
        if(!isset($param['sort']) || !$param['sort']){
            $this->error("缺少参数");
        }

        try {
            M()->startTrans();
            foreach ($param['sort'] as $key => $val) {
                $data['sort'] = $val;
                M("rate")->where(["id"=>intval($key), "tenant_id"=>intval($param['tenant_id'])])->save($data);
            }
            M()->commit();
        }catch (\Exception $e){
            M()->rollback();
            setAdminLog("更新币种排序失败【".json_encode($param)."】".$e->getMessage());
            $this->error("操作失败！");
        }

        setAdminLog('更新币种排序成功【'.json_encode($param).'】');
        delRateListCache($param['tenant_id']);
        $this->success("排序更新成功！", U('index',array('tenant_id'=>$param['tenant_id'])));

    }


}