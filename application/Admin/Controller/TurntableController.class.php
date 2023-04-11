<?php

/**
 * VIP管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TurntableController extends AdminbaseController
{

     public function  index(){
         $map['tenant_id'] = getTenantIds();
         $appointmentModel = M("turntable_set");
         if (IS_POST) {
             $param = I("post.");
             $data=[];
             foreach ($param as $key =>$value){
                 $field = explode('_',$key);
                 if ($field[0] == 'exchangenumber'){
                     $data[$field[1]]['exchange_number'] =  $value;
                 }else{
                     $data[$field[1]][$field[0]] =  $value;
                 }
             }
             foreach ($data as $dataKey => $dataValue){
                    if ($dataValue['type'] == 2){
                        $dataValue['number'] = 1;
                        if ($dataValue['exchange_number']<=0){
                           $this->error('请设置'.$dataValue['name'].'碎片的完成数量');
                        }
                    }
                 $appointmentModel->where(['id'=> $dataKey,'tenant_id'=>getTenantIds()])->save($dataValue);
             }
             $action="修改转盘 : ".json_encode($data);   setAdminLog($action);
             $this->success('修改成功');
         }
         $lists = $appointmentModel
             ->where($map)
             ->select();
         $this->assign('lists', $lists);

         $this->display();
     }


     public function turntableConfig(){
         $config=M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->find();
         $config['turntable_desc']  = addslashes(htmlspecialchars_decode($config['turntable_desc']));
         $this->assign('config',$config);
         if (IS_POST) {
             $param = I("post.");
             $data['turntable_is_show'] = $param['turntable_is_show'];
             $data['turntable_desc'] = $param['turntable_desc'];
             $data['turntable_is_effect'] = $param['turntable_is_effect'];
           //  $data['turntable_background_image'] = $param['turntable_background_image'];
             $result  = M("tenant_config")->where('tenant_id="'.getTenantIds().'"')->save($data);
             if ($result !== false){
                 delcache(getTenantIds().'_'.'getTenantConfig');
                 delcache(getTenantIds().'_'."getPlatformConfig");
                 $action="修改转盘配置 : ".json_encode($data);   setAdminLog($action);
                 $this->success('修改成功');
             }
         }
         $this->display();
     }


     public  function program(){
         $map['tenant_id'] = getTenantIds();
         $programModel = M("turntable_program");
         $map['status'] = 1;
         $map['tenant_id'] =  getTenantIds();
         $count=$programModel->where($map)->count();
         $page = $this->page($count, 20);
         $lists = $programModel
             ->where($map)
             ->limit($page->firstRow . ',' . $page->listRows)
             ->select();

         $this->assign('lists', $lists);
         $this->assign("page", $page->show('Admin'));
         $this->assign('formget', $_GET);
         $this->display();
     }

     public  function program_add(){
         $appointmentModel = M("turntable_set");
         $map['tenant_id'] = getTenantIds();
         if (IS_POST){
             $param = I("post.");
             if (empty($param['name'])){
                 $this->error('请填写方案名称');
             }
             $number=  $param['number'];
             $data = [];
             for ($i= 1 ; $i<= $number;$i++){

                 if (isset($param['turntable_id_'.$i])){
                     $data[$i-1]['turntable_id']  = $param['turntable_id_'.$i];
                 }
                 if (isset($param['type_'.$i])){
                     $data[$i-1]['type']  = $param['type_'.$i];
                 }
                 if ($data[$i-1]['type']  == 1){
                     $data[$i-1]['probability']  = 100;
                 }else{
                     if (isset($param['probability_'.$i]) &&  $param['probability_'.$i] !=='' ){
                         $data[$i-1]['probability']  = $param['probability_'.$i];
                     }else{
                         $data[$i-1]['probability']  = 100;
                     }
                 }


             }
             if (empty($data)){
                 $this->error('此方案无具体设置');
             }
             $result  = M("turntable_program")->add([
                 'tenant_id'=>getTenantIds(),
                 'name' => $param['name'],
                 'addtime'=> time(),
                 'number' => count($data)
             ]);
             foreach ($data as $key => $value){
                 $data[$key]['program_id'] = $result;
                 $data[$key]['tenant_id'] = getTenantIds();
                 $data[$key]['times'] = $key+1;

             }

             M("turntable_program_desc")->addAll($data);
             $action="添加转盘方案 : ".json_encode($data);   setAdminLog($action);
             $this->success('添加成功');
         }
         $lists = $appointmentModel
             ->where($map)
             ->select();
         $this->assign('list', $lists);
         $this->display();
     }

     public  function program_edit(){
         $param = I('param.');
         $id = $param['id'];
         $program=  M('turntable_program')->where(['id'=> $id])->find();
         $programDesc  = M('turntable_program_desc')->where(['program_id'=>$id ])->select();
         $count = count($programDesc);
         $map['tenant_id'] = getTenantIds();
             if (IS_POST){
             $param = I("post.");
             if (empty($param['name'])){
                 $this->error('请填写方案名称');
             }
             $number=  $param['number'];
             $dataup = [];
             for ($i= 1 ; $i<= $number;$i++){
                 if (isset($param['desc_id_'.$i])){
                     $dataup[$param['desc_id_'.$i]]['turntable_id'] = $param['turntable_id_'.$i];
                     $dataup[$param['desc_id_'.$i]]['type']  = $param['type_'.$i];
                     if (  $dataup[$param['desc_id_'.$i]]['type']   == 1){
                         $dataup[$param['desc_id_'.$i]]['probability']  = 100;
                     }else{
                         $dataup[$param['desc_id_'.$i]]['probability']  = $param['probability_'.$i];
                     }

                 }else{
                     ++$count ;
                     if (isset($param['turntable_id_'.$i])){
                         $data[$i-1]['turntable_id']  = $param['turntable_id_'.$i];
                     }else{
                         --$count ;
                         continue;

                     }
                     if (isset($param['type_'.$i])){
                         $data[$i-1]['type']  = $param['type_'.$i];
                     }
                     if ( $param['type_'.$i] == 1){
                         $data[$i-1]['probability']  = 100;
                     }else{
                         if (isset($param['probability_'.$i]) &&  $param['probability_'.$i] !=='' ){
                             $data[$i-1]['probability']  = $param['probability_'.$i];
                         }else{
                             $data[$i-1]['probability']  = 100;
                         }
                     }

                     $data[$i-1]['program_id'] = $id;
                     $data[$i-1]['times'] = $count;
                     $data[$i-1]['addtime'] = time();
                     $data[$i-1]['tenant_id'] = getTenantIds();

                 }

             }
             foreach ($dataup as $key => $value){
                 M("turntable_program_desc")->where(['id'=> $key])->save($value);
             }
             M("turntable_program_desc")->addAll($data);
             $result  = M("turntable_program")->where(['id'=> $param['id']])->save([
                 'name' => $param['name'],
                 'number' => count($data)+ count($dataup),
             ]);

             M("turntable_program_desc")->addAll(array_values($data));
             $action="修改转盘方案 : ".json_encode($data).json_encode($dataup);
             setAdminLog($action);
             $this->success('修改成功');
         }
         $appointmentModel = M("turntable_set");
         $lists = $appointmentModel
             ->where($map)
             ->select();
         $this->assign('list', $lists);
         $this->assign('id',$id);
         $this->assign('program', $program);
         $this->assign('program_desc', $programDesc);
         $this->assign('number',   $count);
         $this->display();
     }

     public function  upStatus(){
         $param = I('param.');
         $id = $param['id'];
         $status = $param['status'];
         if ($id){
             M('turntable_program')->where(['id'=> $id])->save(['status'=> $status]);
             if ($status = 1){
                 $action=" 删除: ".$id;
             }

             setAdminLog($action);
             $this->success('操作成功');

         }

     }

}