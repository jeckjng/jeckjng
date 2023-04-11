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




class AppointmentController extends AdminbaseController {
    private $type_name = array(
        '1' => '外围',
        '2' => '会所',
        '3' => '楼凤',
    );
    private $classification = array(
        '2' => '红榜推荐',
        '3' => '认证女神',
        '4' => '金主推荐',
    );
    public  function index(){
        $param = I('param.');
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }else{

        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if(isset($param['status']) && $param['status'] !=''){
            $map['status']=$param['status'];

        }else{
            $map['status']=array(['neq',3]);
        }
        if(isset($param['is_authentication']) && $param['is_authentication'] !='') {
            $map['is_authentication'] = $param['is_authentication'];
        }
        if(isset($param['is_top']) && $param['is_top'] !='') {
            $map['is_top'] = $param['is_top'];
        }
        if(isset($param['title']) && $param['title'] !='') {
            $map['title'] = $param['title'];
        }

        $map['tenant_id'] = getTenantIds();
        $appointmentModel =M("appointment");
        $count=$appointmentModel->where($map)->where($map)->count();
        $page = $this->page($count);
        $lists = $appointmentModel
            ->where($map)
            ->order('sort asc')
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $province= [];
        $city = [];
        $area = [];

        if ($lists){
            $oper_id = array_column($lists,'oper_id');
            $shop_id = array_column($lists,'shop_id');
            $userList = M('users')->where(['id'=> ['in',$oper_id]])->select();
            $userListById = array_column($userList,null,'id');
            $shopList = M('Shop')->where(['id'=> ['in',$shop_id]])->select();
            $shopListById = array_column($shopList,null,'id');
            foreach ($lists as  $key => $value){
                $typeArray = explode(',',$value['type']);
                $typeName = [];
                foreach ($typeArray as $typeValue ){
                    $typeName[]= $this->type_name[$typeValue];
                }
                $lists[$key]['type_name'] = implode(',',$typeName);

                $classificationArray = explode(',',$value['classification']);
                $classificationName = [];
                foreach ($classificationArray as $classificationArrayValue ){
                    $classificationName[]= $this->classification[$classificationArrayValue];
                }
                $lists[$key]['classification_name'] = implode(',',$classificationName);


                $lists[$key]['admin_name'] =  $userListById[$value['oper_id']]['user_login'];
                $lists[$key]['shop_name'] =  $shopListById[$value['shop_id']]['name'];
                $province[] = $value['province_id'];
                $city[] = $value['city_id'];
                $area[] = $value['area_id'];
            }

            $provinceList =  M('province')->where(['id'=> ['in',$province]])->select();
            $cityList =  M('city')->where(['id'=> ['in',$city]])->select();
            $areaList =  M('area')->where(['id'=> ['in',$area]] )->select();

            $this->assign('province_list', array_column($provinceList,null,'id'));
            $this->assign('city_list', array_column($cityList,null,'id'));
            $this->assign('area_list', array_column($areaList,null,'id'));
        }

        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->assign('param',$param);


        $this->display();
    }

    public function add(){
        $province =  M('province')->select();

        $city =  M('city')->where(['father_id'=>$province[0]['province_id']])->select();
        $area =  M('area')->where(['father_id'=>$city[0]['city_id']] )->select();
        $shop =  M('shop')->where(['status'=> array('neq',2),'tenant_id'=> getTenantIds()] )->select();
        if (IS_POST){
            $param = I("post.");
            $title = $param['title'];
            $sort = $param['sort'];
            if ($title == ''){
                $this->error('请输入标题名称');
            }
            $age= $param['age'];
            if ($age == ''){
                $this->error('请输入妹妹年龄');
            }
            if($age< 10 || $age > 99){
                $this->error('妹妹年龄输入不合常规');
            }
            $price= $param['price'];
            if ($price == ''){
                $this->error('请输入价格');
            }
            //$score = $param['score'];
            $type= $param['type'];
            $typeString = implode(',',$type);

            $classification = $param['classification'];
            $classificationString = implode(',',$classification);

            $service_items= $param['service_items'];
            if ($service_items == ''){
                $this->error('请输入服务项目');
            }
            $phone = $param['phone'];
            if ($phone == ''){
                $this->error('请输入联系方式');
            }
            $address= $param['address'];
            $video = $param['video'];
            $img = $param['img'];
            if (count($img)< 1){
                $this->error('至少上传一张图片');
            }
            $img =  array_filter($img);
            $imgString = implode(',',$img);

            $data =[
                'title' => $title,
                'age' => $age,
                'price' => $price,
                //'score' => $score,
                'type' => $typeString,
                'classification' => $classificationString,
                'phone' => $phone,
                'address' => $address,
                'img' => $imgString,
                'video' => $video,
                'service_items' =>$service_items,
                'viewing_times' => $param['viewing_times'],
                'unlock_times' => $param['unlock_times'],
                'is_top' => $param['is_top'],
                'is_authentication' => $param['is_authentication'],
                'status' => $param['status'],
                'shop_id' => $param['shop_id'],
                'province_id' => $param['province'],
                'city_id' => $param['city'],
                'area_id' => $param['area'],
                'oper_id' =>$_SESSION['ADMIN_ID'],
                'addtime'=> time(),
                'tenant_id' => getTenantIds(),
                'endtime' => time(),
                'sort'=> $sort,
            ];
            $res = M('appointment')->add($data);
            if ($res !== false){
                setAdminLog('添加约会');
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
        $this->assign('area',$area);
        $this->assign('city',$city);
        $this->assign('shop',$shop);
        $this->assign('type',$this->type_name);
        $this->assign('classification',$this->classification);
        $this->assign('province',$province);

        $this->display();
    }


    public function edit(){
        $param = I('param.');
        $id = $param['id'];
        if (!$id){
            $this->error('参数有误');
        }
        $info = M('appointment')->where(['id'=> $id])->find();
        $province =  M('province')->select();
        $provinceInfo  =  M('province')->where(['id'=>$info['province_id']])->find();
        $city =  M('city')->where(['father_id'=>$provinceInfo['province_id']])->select();
        $cityInfo =  M('city')->where(['id'=>$info['city_id']])->find();
        $area =  M('area')->where(['father_id'=>$cityInfo['city_id']] )->select();
        $shop =  M('shop')->where(['status'=> array('neq',2),'tenant_id'=> getTenantIds()] )->select();
        if (IS_POST){
            $param = I("post.");
            $id = $param['id'];
            $sort = $param['sort'];
            $title = $param['title'];
            if ($title == ''){
                $this->error('请输入标题名称');
            }
            $age= $param['age'];
            if ($age == ''){
                $this->error('请输入妹妹年龄');
            }
            $price= $param['price'];
            if ($price == ''){
                $this->error('请输入价格');
            }
         //   $score = $param['score'];
            $type= $param['type'];
            $typeString = implode(',',$type);
            $classification = $param['classification'];
            $classificationString = implode(',',$classification);
            $service_items= $param['service_items'];
            if ($service_items == ''){
                $this->error('请输入服务项目');
            }
            $phone = $param['phone'];
            if ($phone == ''){
                $this->error('请输入联系方式');
            }
            $address= $param['address'];
            $video = $param['video'];
            $img = $param['img'];
            if (count($img)< 1){
                $this->error('至少上传一张图片');
            }
            $img =  array_filter($img);
            $imgString = implode(',',$img);
            $data =[
                'title' => $title,
                'age' => $age,
                'price' => $price,
              //  'score' => $score,
                'type' => $typeString,
                'phone' => $phone,
                'address' => $address,
                'img' => $imgString,
                'classification' => $classificationString,
                'service_items' =>$service_items,
                'video' => $video,
                'viewing_times' => $param['viewing_times'],
                'unlock_times' => $param['unlock_times'],
                'is_top' => $param['is_top'],
                'is_authentication' => $param['is_authentication'],
                'status' => $param['status'],
                'shop_id' => $param['shop_id'],
                'province_id' => $param['province'],
                'city_id' => $param['city'],
                'area_id' => $param['area'],
                'oper_id' =>$_SESSION['ADMIN_ID'],
                'tenant_id' => getTenantIds(),
                'endtime' => time(),
                'sort' => $sort,
            ];
            $res = M('appointment')->where(['id'=> $id])->save($data);
            if ($res !== false){
                setAdminLog('修改约会 id：'.$id);
                $this->success('修改成功');
            }else{
                $this->error('添加失败');
            }
        }
        $info['type'] = explode(',',$info['type']);
        $info['classification'] = explode(',',$info['classification']);

        $info['img'] = explode(',',$info['img']);

        $this->assign('info',$info);

        $this->assign('area',$area);
        $this->assign('city',$city);
        $this->assign('shop',$shop);
        $this->assign('type',$this->type_name);
        $this->assign('classification',$this->classification);
        $this->assign('province',$province);

        $this->display();
    }
    public function getcity(){
        $pid = I('father_id');
        $type =     I('type');  // 1  城市  2 区
        if ($type ==1) {
            $res['city'] = M('city')->where(['father_id' => $pid])->select();
            $res['area'] =  M('area')->where(['father_id'=>$res['city'][0]['city_id']] )->select();

        }else{
            $res['area'] =  M('area')->where(['father_id'=>$pid] )->select();
        }

        $data = $res;
        $this->ajaxReturn($data);
    }
    public  function del(){
        $param = I('param.');
        $id = $param['id'];
        if (!$id){
            $this->error('参数有误');
        }
        $res = M('appointment')->where(['id'=> $id])->save(['status'=>3 ]);
        setAdminLog('删除约会:id 为'.$id);
        $this->success('操作成功');
    }
    /**
     * 下载模板
     */
    public function export(){

        $export_data[]=[

            'title'=>'',
            'age'=>'',
            'province_id'=>'',
            'city_id'=>'',
            'area_id'=>'',
            'price'=>'',
            //  'score'=>'',
            'shop_id'=>'',
            'type'=>'',
            'service_items'=>'',
            'phone'=>'',
            'address'=>'',
            'img'=>'',
            'video'=>'',
            'viewing_times'=>'',
            'unlock_times'=>'',
            'is_top'=>'',
            'is_authentication'=>'',
            'classification'=> '',
             'sort'=> ''

        ];
        $header=array(
            'title' => array(
                'title'=>'标题 :5',
                'age'=>'年龄:5',
                'province_id'=>'省:10',
                'city_id'=>'市:10',
                'area_id'=>'区:10',
                'price'=>'价格:10',
                // 'score'=>'综合评分',
                'shop_id'=>'店铺:10',
                'type'=>'类型 1外围 2，会所，3楼风:40',
                'service_items'=>'服务项目:30',
                'phone'=>'联系方式:30',
                'address'=>'地址信息:30',
                'img'=>'图片 多个视频用,隔开:50',
                'video'=>'视频 多个视频用|隔开:50',
                'viewing_times'=>'浏览量：20',
                'unlock_times'=>'解锁量：20',
                'is_top'=>'是否置顶 1是 2 否:50',
                'is_authentication'=>'是否显示 1是 2 否:50',
                'classification'=> '分类 2红榜推荐，3认证女神，4，金主推荐:60',
                'sort'=> '排序',

            ),
        );
        $filename="appointment";
        $return_url = count($export_data) > 10000 ? true : false;
        $excel_filname = $return_url == true ? $filename : $filename;
        include EXTEND_PATH ."util/UtilPhpexcel.php";
        $Phpexcel = new \UtilPhpexcel();
        $excel_filname = iconv("utf-8", "gb2312", $excel_filname);
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
           // $highestRow = $sheet->getHighestRow();
            $worksheetData = $objReader->listWorksheetInfo($savePath . $file_name);
           // $totalRows     = $worksheetData[0]['totalRows'];
          //  $totalColumns  = $worksheetData[0]['totalColumns'];
            $highestRow = $worksheetData[0]['totalRows'];
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $data = array();
            $provinceList = M('province')->select();
            $provinceListById =array_combine(array_column($provinceList, 'id'), array_column($provinceList, 'province'));

            $cityList =  M('city')->select();
            $cityListById =array_combine(array_column($cityList, 'id'), array_column($cityList, 'city'));

            $areaList =  M('area')->select();
            $areaListById =array_combine(array_column($areaList, 'id'), array_column($areaList, 'area'));
            $shopList = M("shop")->where(['tenant_id' =>getTenantIds()])->select();
            $shopListById =array_combine(array_column($shopList, 'id'), array_column($shopList, 'name'));
            M("appointment")->startTrans();
            try {
                for ($row = 2; $row <= $highestRow; $row++) {
                 /*   'title'=>'',
                'age'=>'',
                'province_id'=>'',
                'city_id'=>'',
                'area_id'=>'',
                'price'=>'',
                'score'=>'',
                'shop_id'=>'',
                'type'=>'',
                'service_items'=>'',
                'phone'=>'',
                'address'=>'',
                'img'=>'',
                'video'=>'',
                'viewing_times'=>'',
                'unlock_times'=>'',
                'is_top'=>'',
                'is_authentication'=>'',*/
                    $data['title'] = $sheet->getCellByColumnAndRow(0, $row)->getValue();
                    if(is_object(  $data['title']))    $data['title']=   $data['title']->__toString();
                    $data['age'] = $sheet->getCellByColumnAndRow(1, $row)->getValue();
                    $province = trim($sheet->getCellByColumnAndRow(2, $row)->getValue());
                    if(is_object(  $province))   $province=   $province->__toString();
                    if (!in_array($province,$provinceListById)){
                        M("appointment")->rollback();
                        $this->error($province.'没有对应数据,请核对1');
                    }
                    $data['province_id'] = array_search($province,$provinceListById);

                    $city = trim($sheet->getCellByColumnAndRow(3, $row)->getValue());
                    if(is_object(  $city))   $city=   $city->__toString();
                    if (!in_array($city,$cityListById)){
                        M("appointment")->rollback();
                        $this->error($city.'没有对应数据,请核对2');
                    }
                    $data['city_id'] = array_search($city,$cityListById);
                    $area = $sheet->getCellByColumnAndRow(4, $row)->getValue();
                    if(is_object(  $area))   $area=   $area->__toString();
                    if (!in_array($area,$areaListById)){
                        M("appointment")->rollback();
                        $this->error($area.'没有对应数据,请核对3');
                    }
                    $data['area_id'] =   trim($data['city'] = array_search($area,$areaListById));;
                    $data['price'] = $sheet->getCellByColumnAndRow(5, $row)->getValue();;
                  //  $data['score'] = $sheet->getCellByColumnAndRow(6, $row)->getValue();;
                    $shopName = trim($sheet->getCellByColumnAndRow(6, $row)->getValue());

                    if (!in_array($shopName,$shopListById)){
                        M("appointment")->rollback();
                        $this->error($shopName.'没有对应店铺,请核对4');
                    }
                    $data['shop_id'] = array_search($shopName,$shopListById);
                    $data['type'] = $sheet->getCellByColumnAndRow(7, $row)->getValue()?$sheet->getCellByColumnAndRow(7, $row)->getValue():0;

                    $data['service_items'] = $sheet->getCellByColumnAndRow(8, $row)->getValue()?$sheet->getCellByColumnAndRow(8, $row)->getValue():'';
                    if(is_object(  $data['service_items']))    $data['service_items']=   $data['service_items']->__toString();
                    $data['phone'] = $sheet->getCellByColumnAndRow(9, $row)->getValue()?$sheet->getCellByColumnAndRow(9, $row)->getValue():'';
                    $data['address'] = $sheet->getCellByColumnAndRow(10, $row)->getValue()?$sheet->getCellByColumnAndRow(10, $row)->getValue():'';;
                    if(is_object(  $data['address']))    $data['address']=   $data['address']->__toString();
                    $data['img'] = $sheet->getCellByColumnAndRow(11, $row)->getValue()?$sheet->getCellByColumnAndRow(11, $row)->getValue():0;;
                    $data['video'] = $sheet->getCellByColumnAndRow(12, $row)->getValue()?$sheet->getCellByColumnAndRow(12, $row)->getValue():'';;
                    $data['viewing_times'] = $sheet->getCellByColumnAndRow(13, $row)->getValue()?$sheet->getCellByColumnAndRow(13, $row)->getValue():0;;
                    $data['unlock_times'] = $sheet->getCellByColumnAndRow(14, $row)->getValue()?$sheet->getCellByColumnAndRow(14, $row)->getValue():0;;
                    $data['is_top'] = $sheet->getCellByColumnAndRow(15, $row)->getValue()?$sheet->getCellByColumnAndRow(15, $row)->getValue():1;;
                    $data['is_authentication'] = $sheet->getCellByColumnAndRow(16, $row)->getValue()?$sheet->getCellByColumnAndRow(16, $row)->getValue():1;;
                    $data['classification'] = $sheet->getCellByColumnAndRow(17, $row)->getValue()?$sheet->getCellByColumnAndRow(17, $row)->getValue():1;;
                    $data['srot'] = $sheet->getCellByColumnAndRow(18, $row)->getValue()?$sheet->getCellByColumnAndRow(18, $row)->getValue():1;;

                    $data['addtime'] = time();
                    $data['endtime'] = time();
                    $data['status'] = 1;
                    $data['tenant_id'] = getTenantIds();
                    $data['oper_id'] = $_SESSION['ADMIN_ID'];
                    $result = M("appointment")->add($data);
                }
            }catch (Exception $exception){
                M("appointment")->rollback();
                $this->error($exception->getMessage());
            }
            M("appointment")->commit();
        }
        $this->success('导入成功');
    }


    public  function comment(){
        $param = I('param.');
        $id = $param['id'];
        if (!$id){
            $this->error('参数有误');

        }
        $map['appointment_id'] = $id;
        $param['id'] = $id;
        if(isset($param['start_time']) && $param['start_time'] != ''){
            $map['addtime'] = array("egt", strtotime($param['start_time']));
        }else{

        }
        if(isset($param['end_time']) && $param['end_time']!=''){
            $map['addtime'] = array("elt", strtotime($param['end_time'])+86399);
        }
        if(isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] != '' && $param['end_time'] != ''){
            $map['addtime'] = array("between", array(strtotime($param['start_time']), strtotime($param['end_time'])+86399));
        }
        if(isset($param['user_nicename']) && $param['user_nicename'] !=''){
            $info= M("users")
                ->field("id")->where(['user_nicename'=> $param['user_nicename']])->find();
            if($info){
                $map['uid']= $info['id'];
            }else{
                $map['uid']=0;
            }
        }

        $map['tenant_id'] = getTenantIds();
        $appointmentModel =M("appointment_comment");
        $count=$appointmentModel->where($map)->where($map)->count();
        $page = $this->page($count);
        $lists = $appointmentModel
            ->where($map)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        if ($lists){
            $uid = array_column($lists,'uid');
            $userList = M("users")->where(['id'=>['in',$uid],])->field('id,user_nicename')->select();
            $this->assign('user_list',array_column( $userList,null,'id'));
        }


        $this->assign('lists', $lists);
        $this->assign('param', $param);
        $this->assign("page", $page->show('Admin'));
        $this->display();
    }

    public  function addcomment()
    {
        $param = I('param.');
        if (IS_POST){
            $param = I("post.");
            $id = $param['id'];
            $info= M("users")->where(['id'=> $param['uid'],'tenant_id'=>getTenantIds()])->find();
            if (!$info){
                $this->error('用户id不存在');
            }
            $technology_value = $param['technology_value'];
            $face_value  =  $param['face_value'];
            $surroundings_value  = $param['surroundings_value'];
            if ($technology_value<= 0 ||  $technology_value> 10){
                $this->error('技术评分0到10之间');;
            }
            if ($face_value< 0 ||  $face_value> 10){
                $this->error('颜值评分0到10之间');
            }
            if ($surroundings_value< 0 ||  $face_value> 10){
                $this->error('环境评分0到10之间');
            }
            $data = [
                'uid'=> $param['uid'],
                'appointment_id'=> $id,
                'desc' => $param['desc'],
                'technology_value' => $technology_value,
                'face_value' => $face_value,
                'surroundings_value' => $surroundings_value,
                'type' => 2,
                'addtime' => time(),
                'tenant_id'=> getTenantIds(),
                'status' => 1,
            ];
            $res = M("appointment_comment")->add($data);
            $appointmentList  =  M("appointment_comment")->where(['appointment_id'=>$id ])->select();
            $technologySum =  array_sum(array_column($appointmentList, 'technology_value'));
            $faceSum =  array_sum(array_column($appointmentList, 'face_value'));
            $surroundingSum =  array_sum(array_column($appointmentList, 'surroundings_value'));

            $technologyEva = bcdiv($technologySum,count($appointmentList),2);
            $faceEva = bcdiv($faceSum,count($appointmentList),2);
            $surroundingEva = bcdiv($surroundingSum,count($appointmentList),2);
            $score  = bcdiv(bcadd( bcadd($technologyEva,$faceEva,2),$surroundingEva,2),3,2);
            $res = M("appointment")->where(['id'=> $id])
                ->save(['score'=> $score,'technology_value'=> $technologyEva,'face_value'=> $faceEva,'surroundings_value'=> $surroundingEva]);

            if ($res !== false){
                $this->success('操作成功');
             }   $res = M("appointment_comment")->add($data);
        }
        $this->assign('id',$param['id']);
        $this->display();
    }

    public function editcomment(){
        $param = I('param.');
        $id = $param['id'];
        $info= M("appointment_comment")->where(['id'=>$id])->find();
        if (IS_POST){
            $param = I("post.");

            $users= M("users")->where(['id'=> $param['uid'],'tenant_id'=>getTenantIds()])->find();
            if (!$users){
                $this->error('用户id不存在');
            }
            $technology_value = $param['technology_value'];
            $face_value  =  $param['face_value'];
            $surroundings_value  = $param['surroundings_value'];
            if ($technology_value<= 0 ||  $technology_value> 10){
                $this->error('技术评分0到10之间');;
            }
            if ($face_value< 0 ||  $face_value> 10){
                $this->error('颜值评分0到10之间');
            }
            if ($surroundings_value< 0 ||  $face_value> 10){
                $this->error('环境评分0到10之间');
            }
            $data = [
                'uid'=> $param['uid'],
                'desc' => $param['desc'],
                'type' => 2,
                'addtime' => time(),
                'technology_value' => $technology_value,
                'face_value' => $face_value,
                'surroundings_value' => $surroundings_value,
                'tenant_id'=> getTenantIds(),
                'status' => 1,
            ];
            $res = M("appointment_comment")->where(['id'=>$id])->save($data);

            $appointmentList  =  M("appointment_comment")->where(['appointment_id'=>$info['appointment_id'] ])->select();
            $technologySum =  array_sum(array_column($appointmentList, 'technology_value'));
            $faceSum =  array_sum(array_column($appointmentList, 'face_value'));
            $surroundingSum =  array_sum(array_column($appointmentList, 'surroundings_value'));
            $technologyEva = bcdiv($technologySum,count($appointmentList),2);
            $faceEva = bcdiv($faceSum,count($appointmentList),2);
            $surroundingEva = bcdiv($surroundingSum,count($appointmentList),2);
            $score  = bcdiv(bcadd( bcadd($technologyEva,$faceEva,2),$surroundingEva,2),3,2);
            $res = M("appointment")->where(['id'=> $info['appointment_id']])
                ->save(['score'=> $score,'technology_value'=> $technologyEva,'face_value'=> $faceEva,'surroundings_value'=> $surroundingEva]);

            if ($res !== false){
                $this->success('操作成功');
            }
        }
        $this->assign('info',$info);
        $this->display();
    }

    public function delcomment(){
        $param = I('param.');
        $res = M("appointment_comment")->where(['id'=>$param['id']])->delete();
        if ($res !== false) {
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        //$id
    }

    public  function batchDel(){
        $ids = $_POST['ids'];
        if (empty($ids)) {
            return $this->error('请勾选数据');
        }

        $result=M("appointment")->where(['id'=>['in',$ids]])->save(['status'=>3]);
        if ($result !== false){
            $this->success('操作成功');

        }else{
            return $this->success('操作失败');
        }
    }

    public  function listorders(){
        $ids = $_POST['sort'];
        foreach ($ids as $key => $r) {
            $data['sort'] = $r;
            M("appointment")->where(array('id' => $key))->save($data);
        }

        $status = true;
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

}