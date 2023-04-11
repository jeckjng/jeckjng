<?php
/**
 * 靓号管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class LineconfigController extends AdminbaseController{

    public  function index(){
        $map = array();

        $map['tenant_id'] = getTenantIds();
        $lineList = M('line_config')->where($map)->find();
        $lineList['play_line'] = empty($lineList['play_line'])?$lineList['play_line']:explode(',',$lineList['play_line']);
        $play_line_count = count($lineList['play_line']);
        $lineList['download_line'] = empty($lineList['download_line'])? $lineList['download_line']: explode(',',$lineList['download_line']);
        $download_line_count = count($lineList['download_line']);

        $tenantList = M("tenant")->field('id,name')->select();
        $this->assign("tenant_list",$tenantList);
        $this->assign("line_list",$lineList);
        $this->assign("play_line_count",$play_line_count);
        $this->assign("download_line_count",$download_line_count);
        $this->display();
    }

    public  function add_post(){
        if(IS_POST){
            foreach ($_POST['play_line'] as $key => $value){
                if (empty($value)){
                    unset($_POST['play_line'][$key]);
                }
            }
            foreach ($_POST['download_line'] as $key => $value){
                if (empty($value)){
                    unset($_POST['download_line'][$key]);
                }
            }
            $play_line = implode(',',$_POST['play_line']);
            $download_line = implode(',',$_POST['download_line']);

            $tenant_id = $_POST['tenant_id'];
            $lineList = M('line_config')->where(array(['tenant_id' =>$tenant_id ]))->find();
            if (empty($lineList)){
                $status = M('line_config')->add(array('play_line'=>$play_line,'download_line'=>$download_line,'tenant_id'=>$tenant_id));
            }else{
                $status = M('line_config')->where(array(['tenant_id' =>$tenant_id ]))->save(array('play_line'=>$play_line,'download_line'=>$download_line));
            }
            if ($status){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }

        }
    }

    public  function tenantline(){
        $map = array();

        $map['tenant_id'] = $_POST['tenant_id'];
        $lineList = M('line_config')->where($map)->find();
        $lineList['play_line'] = empty($lineList['play_line'])?$lineList['play_line']:explode(',',$lineList['play_line']);

        $lineList['download_line'] = empty($lineList['download_line'])? $lineList['download_line']: explode(',',$lineList['download_line']);

        $res['code'] = 0;
        $res['msg'] = '获取成功';
        $res['info']= $lineList;
        //  var_dump(json_encode($res));
        echo json_encode($res);exit;
    }
}