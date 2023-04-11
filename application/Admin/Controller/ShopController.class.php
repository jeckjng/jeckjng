<?php

/**
 * 短视频
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Composer\Package\Loader\ValidatingArrayLoader;
use QCloud\Cos\Api;
//use QCloud\Cos\Auth;
use PHPExcel\PHPExcel\APC;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Driver\FFProbeDriver;

use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
use Think\Exception;


class ShopController extends AdminbaseController {
    public  function index(){
        $param = I('param.');

        $tenant_id = isset($param['tenant_id']) ? intval($param['tenant_id']) : intval(getTenantIds());

        $param['tenant_id'] = $tenant_id;

        $map[] = ['tenant_id'=>$tenant_id];

        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }

        /*   if(isset($param['status']) && $param['status'] !=''){
               $map['status']=$param['status'];

           }*/
        if(isset($param['is_top']) && $param['is_top'] !=''){
            $map['is_top']=$param['is_top'];

        }
        $map['status']=array(['neq',3]);
        if(isset($param['name']) && $param['name']!=''){
            $map['name']=$param['name'];
        }


        $shopModel =M("Shop");
        $count=$shopModel->where($map)->where($map)->count();
        $page = $this->page($count);

        $lists = $shopModel
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();

        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);
        $this->assign('tenant_id',$tenant_id);

        $this->display();
    }

    public function add(){

        if (IS_POST){
            $param = I("post.");
            $name = $param['name'];
            if ($name == ''){
                $this->error('请输入商品名称');
            }
            $content= $param['content'];
            if ($content == ''){
                $this->error('请输入服务范围');
            }
            $introduction= $param['introduction'];
            if ($introduction == ''){
                $this->error('请输入店铺简介');
            }
            $deal_num = $param['deal_num'];
            $data =[
                'name' => $name,
                'content' => $content,
                'introduction' => $introduction,
                'deal_num' => $deal_num,
                'is_top' => $param['is_top'],
                'status' => $param['status'],
                'addtime'=> time(),
                'tenant_id' => getTenantIds(),
                'endtime' => time(),
            ];
            $res = M('shop')->add($data);
            if ($res !== false){
                setAdminLog('添加商铺');
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
        $this->display();
    }

    public function edit(){
        $param = I('param.');
        $id = $param['id'];
        if (!$id){
            $this->error('参数有误');
        }
        if (IS_POST){
            $param = I("post.");
            $id = $param['id'];
            $name = $param['name'];
            if ($name == ''){
                $this->error('请输入商品名称');
            }
            $content= $param['content'];
            if ($content == ''){
                $this->error('请输入服务范围');
            }
            $introduction= $param['introduction'];
            if ($introduction == ''){
                $this->error('请输入店铺简介');
            }
            $deal_num = $param['deal_num'];
            $data =[
                'name' => $name,
                'content' => $content,
                'introduction' => $introduction,
                'deal_num' => $deal_num,
                'is_top' => $param['is_top'],
                'status' => $param['status'],
                'addtime'=> time(),
                'tenant_id' => getTenantIds(),
                'endtime' => time(),
            ];
            $res = M('shop')->where(['id'=> $id])->save($data);
            if ($res !== false){
                setAdminLog('修改商铺:id 为'.$id);
                $this->success('修改成功');
            }else{
                $this->error('添加失败');
            }
        }
        $info = M('shop')->where(['id'=> $id])->find();
        $this->assign('info',$info);
        $this->assign('param',$param);
        $this->display();
    }

    /**
     * 下载模板
     */
    public function export(){
        $export_data[]=[
            'name'=>'',
            'content'=>'',
            'introduction'=>'',
            'deal_num'=>'',
            'is_top'=>'',


        ];
        $header=array(
            'title' => array(
                'name'=>'店铺名称:30',
                'content'=>'服务范围:30',
                'introduction'=>'店铺简介:30',
                'deal_num'=>'成交量:30',
                'is_top'=>'是否置顶  1是 2 否:30',
            ),
        );
        $filename="shop";
        $return_url = count($export_data) > 10000 ? true : false;
        $excel_filname = $return_url == true ? $filename : $filename;
        include EXTEND_PATH ."util/UtilPhpexcel.php";
        $Phpexcel = new \UtilPhpexcel();
        $downurl = "/".$Phpexcel::export_excel_v1($export_data, $header,$excel_filname, $return_url);

        if($downurl){
            $output_filename = $filename;
            header('pragma:public');
            header("Content-Disposition:attachment;filename=".$output_filename.".xls"); //下载文件，filename 为文件名
            echo file_get_contents($downurl);
            exit;
        }
    }

    public function import(){
        $tmp_file = $_FILES ['file'] ['tmp_name'];
        $type = strstr( $_FILES ['file']['name'],'.');
        if ($type != '.xls' && $type != '.xlsx') {
            $this->error('请上传excel文件');
        }
        if (is_uploaded_file($tmp_file)) {
            /*设置上传路径*/
            $savePath = './data/upload/';
            /*以时间来命名上传的文件*/
            $str = date('Ymdhis');
            $file_name = $str . "." . $type;
            /*是否上传成功*/
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $savePath . $file_name)) {
                $this->error('上传失败');
            }
            vendor("PHPExcel.PHPExcel.IOFactory");
            $iofactory = new \PHPExcel_IOFactory();
            $objReader = $iofactory::createReaderForFile($savePath . $file_name);
            $objPHPExcel = $objReader->load($savePath . $file_name);
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $data = array();
            M("shop")->startTrans();
            try {

                for ($row = 2; $row <= $highestRow; $row++) {
                    $data['name'] = $sheet->getCellByColumnAndRow(0, $row)->getValue();
                    if(is_object(  $data['name']))    $data['name']=   $data['name']->__toString();
                    $data['content'] = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                    if(is_object(  $data['content']))    $data['content']=   $data['content']->__toString();
                    $data['introduction'] = $sheet->getCellByColumnAndRow(2, $row)->getValue();
                    if(is_object(  $data['introduction']))    $data['introduction']=   $data['introduction']->__toString();
                    $data['deal_num'] = $sheet->getCellByColumnAndRow(3, $row)->getValue();
                    $data['is_top'] = $sheet->getCellByColumnAndRow(4, $row)->getValue();
                    $data['tenant_id'] = getTenantIds();
                    $data['addtime'] = time();
                    $data['endtime'] = time();
                    $data['status'] = 1;
                    $result = M("shop")->add($data);
                }
            }catch (Exception $exception){
                M("shop")->rollback();
                $this->error($exception->getMessage());
            }
            M("shop")->commit();
        }
        $this->success('导入成功');
    }

    public  function del(){
        $param = I('param.');
        $id = $param['id'];
        if (!$id){
            $this->error('参数有误');
        }
        $res = M('shop')->where(['id'=> $id])->save(['status'=>3 ]);
        setAdminLog('删除商铺:id 为'.$id);
        $this->success('操作成功');
    }

}