<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/6/2
 * Time: 20:29
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class CountryController extends AdminbaseController
{

    function _initialize()
    {
        parent::_initialize();

    }
    public function index()
    {
        $param = I('param.');

        $page_size = isset($param['num']) && $param['num'] >= 5 ? $param['num'] : 20;
        $p = isset($param['p']) && $param['p'] >= 1 ? $param['p'] : 1;
        foreach ($param as $key=>$val){
            $_GET[$key] = $val;
        }

        $map = array();
        if(isset($param['name']) && $param['name'] != ''){
            $map['name'] = $param['name'];
        }
        if(isset($param['value']) && $param['value'] != ''){
            $map['value'] = $param['value'];
        }
        if(isset($param['abridge']) && $param['abridge'] != ''){
            $map['abridge'] = $param['abridge'];
        }
        if(isset($param['code']) && $param['code'] != ''){
            $map['code'] = $param['code'];
        }

        $count = M('country')->where($map)->count();
        $page = $this->page($count, $page_size);
        $list = M('country')
            ->where($map)
            ->order('sort asc, id desc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->assign("list",$list);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->display();
    }

    /*
     * 添加
     */
    public function add()
    {
        if (IS_POST) {
            $param = I('post.');
            if (!isset($param['name']) || !$param['name']) {
                $this->error("请输入国家名称");
            }
            if (!isset($param['value']) || $param['value'] == "") {
                $this->error("请输入国家英文名称");
            }
            if (!isset($param['abridge']) || $param['abridge'] == "") {
                $this->error("请输入国家缩写");
            }
            if (!isset($param['code']) || $param['code'] == "") {
                $this->error("请输入国家code码");
            }

            $info = M('country')->where("name='".$param['name']."' or value='".$param['value']."' or abridge='".$param['abridge']."' or code='".$param['code']."'")->find();
            if ($param['name'] == $info['name']){
                $this->error("国家名称已存在");
            }
            if ($param['value'] == $info['value']){
                $this->error("国家英文名称已存在");
            }
            if ($param['abridge'] == $info['abridge']){
                $this->error("国家缩写已存在");
            }
            if ($param['code'] == $info['code']){
                $this->error("国家code码已存在");
            }

            try {
                M()->startTrans();
                $data = array(
                    'name'          => trim($param['name']),
                    'value'         => trim($param['value']),
                    'abridge'       => trim($param['abridge']),
                    'code'          => trim($param['code']),
                    'operated_by'   => trim(get_current_admin_user_login()),
                    'tenant_id'     => intval(getTenantId()),
                );
                M('country')->add($data);
                M()->commit();
            } catch (\Exception $e) {
                M()->rollback();
                setAdminLog("【添加国家失败】" . json_encode($param) . " | " . $e->getMessage());
                $this->error("操作失败！");
            }
            setAdminLog("【添加国家成功】" . json_encode($param));
            delCountryListCache();
            $this->success("操作成功！", U('index'));
        }

        $this->display();
    }

    /*
      * 编辑
      */
    public function edit(){
        if(IS_POST){
            $param = I('post.');
            if (!isset($param['name']) || !$param['name']) {
                $this->error("请输入国家名称");
            }
            if (!isset($param['value']) || $param['value'] == "") {
                $this->error("请输入国家英文名称");
            }
            if (!isset($param['abridge']) || $param['abridge'] == "") {
                $this->error("请输入国家缩写");
            }
            if (!isset($param['code']) || $param['code'] == "") {
                $this->error("请输入国家code码");
            }

            $info = M('country')->where("id != ".$param['id']." and (name='".$param['name']."' or value='".$param['value']."' or abridge='".$param['abridge']."' or code='".$param['code']."')")->find();
            if ($param['name'] == $info['name']){
                $this->error("国家名称已存在");
            }
            if ($param['value'] == $info['value']){
                $this->error("国家英文名称已存在");
            }
            if ($param['abridge'] == $info['abridge']){
                $this->error("国家缩写已存在");
            }
            if ($param['code'] == $info['code']){
                $this->error("国家code码已存在");
            }

            try {
                M()->startTrans();
                $data = array(
                    'name'          => trim($param['name']),
                    'value'         => trim($param['value']),
                    'abridge'       => trim($param['abridge']),
                    'code'          => trim($param['code']),
                    'operated_by'   => trim(get_current_admin_user_login()),
                );
                M('country')->where(["id"=>intval($param['id'])])->save($data);
                M()->commit();
            }catch (\Exception $e){
                M()->rollback();
                setAdminLog("【添加国家-失败】".json_encode($param).' | '.$e->getMessage());
                $this->error("操作失败！");
            }
            setAdminLog("【添加国家-成功】".json_encode($param));
            delCountryListCache();
            $this->success("操作成功！", U('index'));
        }

        $param = I('param.');
        if(!isset($param['id']) || !$param['id']){
            $this->error("缺少参数");
        }
        $info = M("country")->where(["id"=>intval($param['id'])])->find();

        $this->assign("info", $info);
        $this->display();
    }

    /*
     * 更新排序
     * */
    public function update_sort(){
        if(!IS_POST){
            $this->error("请求方式错误");
        }
        $param = I('post.');
        if(!isset($param['sort']) || !$param['sort']){
            $this->error("缺少参数");
        }

        try {
            M()->startTrans();
            foreach ($param['sort'] as $key => $val) {
                $data['sort'] = $val;
                M("country")->where(["id"=>intval($key)])->save($data);
            }
            M()->commit();
        }catch (\Exception $e){
            M()->rollback();
            setAdminLog("【更新国家排序-失败】".json_encode($param)." | ".$e->getMessage());
            $this->error("操作失败！");
        }

        setAdminLog('【更新国家排序-成功】'.json_encode($param));
        delCountryListCache();
        $this->success("排序更新成功！", U('index'));
    }

}