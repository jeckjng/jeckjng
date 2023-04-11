<?php
/**
 * Created by PhpStorm.
 * User: 哎哟哎哟
 * Date: 2021/6/25
 * Time: 18:00
 */
class Model_Appointment extends PhalApi_Model_NotORM
{
    private $type_name = array(
        '1' => '外围',
        '2' => '会所',
        '3' => '楼风',
    );
    public function appointmentList($class,$type,$title,$shop_id,$uid,$province_id,$city_id,$area_id,$game_tenant_id,$p,$limit) {
        if($p<1){
            $p=1;
        }
        if ($limit){
            $nums = 20;
        }else{
            $nums=$limit;
        }
        $start=($p-1)*$nums;
        $tenantId  = getTenantId();
        $where = "tenant_id = {$tenantId}  and  status  = 1 " ;
        $order = 'sort asc, is_top asc,addtime desc';
        if ($shop_id){
            $where .= " and  shop_id = {$shop_id} ";
        }
        if ($class == 1){
            $order = 'sort asc ,is_top asc ,addtime desc';

        }elseif ($class == 2){
            $order = 'sort asc,is_top asc,unlock_times desc';
            $where .= " and (classification like '%".$class."%'  )";
        }elseif ($class == 3){
            $where .=' and  is_authentication =  1';
            $order = 'sort asc, is_top asc,addtime desc';
            $where .= " and (classification like '%".$class."%'  )";
        }elseif ($class == 4 ){
            $order = 'sort asc, is_top asc,score desc';
            $where .= " and (classification like '%".$class."%'  )";
        }
        if ($province_id){
            $where .= " and  province_id = {$province_id} ";
        }
        if ($city_id){
            $where .= " and  city_id = {$city_id} ";
        }
        if ($area_id){
            $where .= " and  area_id = {$area_id} ";
        }
        if ($type){
            $where .= " and (type like '%".$type."%'  )";
        }

        if ($title){
            $where .= " and (title like '%".$title."%'  )";
        }
        $list=DI()->notorm->appointment
            ->order($order)
            ->where($where)
            ->limit($start,$nums)
            ->fetchAll();


        if ($list){
            $appointmentIdArray = array_column($list,'id');// 约会id
            $appointmentIdString = implode(',',$appointmentIdArray);
            $collectList  =DI()->notorm->appointment_collect
                ->where("uid='{$uid}' and appointment_id in ($appointmentIdString) ")
                ->fetchAll(); // 获取用户收藏约会
            $collectAppointmentId  =  [];
            if ($collectList){
                $collectAppointmentId = array_column($collectList,'appointment_id');
            }
            $provinceList = DI()->notorm->province->fetchAll();
            $cityList = DI()->notorm->city->fetchAll();
            $areaList = DI()->notorm->area->fetchAll();
            $provinceListById = array_column($provinceList,null,'id');
            $cityListById= array_column($cityList,null,'id');
            $areaListById = array_column($areaList,null,'id');
            foreach ($list as $key => $value){
                $list[$key]['province_name'] =  $provinceListById[$value['province_id']]['province'];
                $list[$key]['city_name'] =  $cityListById[$value['city_id']]['city'];
                $list[$key]['area_name'] =  $areaListById[$value['area_id']]['area'];
                if (in_array($value['id'],$collectAppointmentId)){
                    $list[$key]['is_collect'] = 1;
                }else{
                    $list[$key]['is_collect'] = 0;
                }
            }
        }

        return $list;
    }

    public function appointmentTotal($game_tenant_id){
        $tenantId  = getTenantId();
        $todayTime = strtotime(date('Y-m-d'));
        $typeOne =DI()->notorm->appointment->where("type like '%1%'  and tenant_id = {$tenantId} and  status  = 1  ")->count(); //外围 总数
        $typeTwo =DI()->notorm->appointment->where("type like '%2%'  and tenant_id = {$tenantId} and  status  = 1  ")->count(); //会所总数
        $typeThree =DI()->notorm->appointment->where("type like '%3%'   and tenant_id =  {$tenantId} and  status  = 1 ")->count(); //楼风总数


        $typeTodayOne =DI()->notorm->appointment->where("type like '%1%'  and tenant_id = '{$tenantId}' and addtime > '{$todayTime}'  and  status  = 1  ")->count(); //外围 总数
        $typeTodayTwo =DI()->notorm->appointment->where("type like '%2%'  and tenant_id = '{$tenantId}' and addtime > '{$todayTime}'  and  status  = 1  ")->count(); //会所总数
        $typeTodayThree =DI()->notorm->appointment->where("type like '%3%' and tenant_id =  '{$tenantId}' and addtime > '{$todayTime}' and  status  = 1  ")->count(); //楼风总数
        $orderCount = DI()->notorm->users_video_buy->where("addtime > '{$todayTime}' and tenant_id = '{$tenantId}'")->count();
        $config = getConfigPub();
        $orderCount = $config['long_video_order_count'] + $orderCount;
        $total =[
            'waiwei_total'=> $typeOne,
            'huisuo_total' => $typeTwo,
            'fenglou_total' => $typeThree,
            'waiwei_today'=> $typeTodayOne,
            'huisuo_today' => $typeTodayTwo,
            'fenglou_today' => $typeTodayThree,
            'order_total' =>$orderCount

        ];
        return  $total;

    }
    public function getAddress($game_tenant_id){
        $tenantId  = getTenantId();
        $provinceList = DI()->notorm->province->fetchAll();
        $cityList = DI()->notorm->city->fetchAll();
        $areaList = DI()->notorm->area->fetchAll();
        foreach ($cityList as $key =>  $cityValue ){
            foreach ($areaList as $areaValue){
                if ($areaValue['father_id'] == $cityValue['city_id']){
                    $cityList[$key]['arae'][]= $areaValue;
                }
            }
        }
        foreach ($provinceList as $provinceKey =>  $provinceValue ){
            foreach ($cityList as $cityValue){
                if ($cityValue['father_id'] == $provinceValue['province_id']){
                    $provinceList[$provinceKey]['city'][]= $cityValue;
                }
            }
        }
        return $provinceList;
    }

    public function appointmentInfo($game_tenant_id,$id,$uid){
        $info=DI()->notorm->appointment
            ->where("id = {$id} and status = 1")
            ->fetchOne();
        if (empty($info)){
            return['code'=> 2123,'msg'=> codemsg(2123) ];
        }
        if ($uid){
            $browse =DI()->notorm->appointment_browse_log
                ->where("uid = {$uid} and  appointment_id = {$id} and status  = 1 ")
                ->fetchOne();// 是否 浏览
            if ($browse){ //重复浏览
                DI()->notorm->appointment_browse_log
                    ->where("uid = {$uid} and  appointment_id = {$id} and status  = 1 ")
                    ->update(array('frequency' => new NotORM_Literal("frequency + 1") ));
            }else{ // 第一次浏览
                DI()->notorm->appointment_browse_log->insert([
                    'uid'=> $uid,
                    'appointment_id'=> $id,
                    'status' => 1,
                    'frequency' => 1,
                    'addtime'=> time(),
                ]);
                DI()->notorm->appointment
                    ->where("id = {$id}")
                    ->update(array('viewing_times' => new NotORM_Literal("viewing_times + 1") ));
            }
        }
        $provinceInfo= DI()->notorm->province->where("id = {$info['province_id']}")->fetchOne();
        $cityList = DI()->notorm->city->where("id = {$info['city_id']}")->fetchOne();
        $areaList = DI()->notorm->area->where("id = {$info['area_id']}")->fetchOne();
        $collectInfo  =DI()->notorm->appointment_collect
            ->where("uid='{$uid}' and appointment_id = {$info['id']} ")
            ->fetchOne(); // 获取用户收藏约会
        $info['province_name'] =   $provinceInfo['province'];
        $info['city_name'] = $cityList['city'];
        $info['area_name']   = $areaList['area'];
        if ($collectInfo){
            $info['is_collect']   = 1;
        }else{
            $info['is_collect']   = 0;
        }

        return ['code'=> 0,'info'=> $info];
    }


    public  function getShopByType($type,$game_tenant_id,$p,$limit){
        $tenantId  = getTenantId();
        if($p<1){
            $p=1;
        }
        if ($limit){
            $nums = 20;
        }else{
            $nums=$limit;
        }
        $start=($p-1)*$nums;
        $order = 'addtime  desc ';
        $where = "tenant_id = {$tenantId}  and  status  = 1 " ;
        if ($type){
            $where.= ' and  is_top = 1 ';
            $order = 'sort  desc ';
        }

        $shopList = DI()->notorm->shop
            ->where($where)
            ->limit($start,$nums)
            ->order($order)->fetchAll();
        if ($shopList){
            $shopId =  array_column($shopList,'id');
            $shopString =implode(',', $shopId);
            $appointmentList  =DI()->notorm->appointment
                ->where("shop_id in ($shopString) and  status = 1 ")
                ->order('sort asc,is_top asc')
                ->fetchAll();
            $shopListById  =  array_column($shopList,null,'id');
            foreach ($appointmentList as $appointmentValue ){
                $shopListById[$appointmentValue['shop_id']]['appointment_list'][] = $appointmentValue;

            }
            foreach ($shopListById as $shopKey =>  $shopValue){
                $shopListById[$shopKey]['authentication_count'] =DI()->notorm->appointment
                    ->where("shop_id = {$shopValue['id']} and   is_authentication = 1 and status = 1 ")
                    ->count();
                $shopListById[$shopKey]['shop_order_count'] =DI()->notorm->appointment
                    ->where("shop_id = {$shopValue['id']} ")
                    ->sum('unlock_times');
            }
            return  array_values($shopListById);

        }else{
            return $shopList;
        }

    }

    public  function placeorder($uid,$appointment_id,$game_tenant_id){

        $info=DI()->notorm->appointment
            ->where("id = {$appointment_id}")
            ->fetchOne();
       /* $userInfo  = DI()->notorm->users
            ->where("id = {$uid}")  ->fetchOne();
        if ($userInfo['coin']< $info['price']){
            return array('code' => 2006, 'msg' => codemsg(2006), 'info' => ['余额不足']);
        }
        $userInfo  = DI()->notorm->users
            ->where("id = {$uid}")  ->update(['coin'=>  new NotORM_Literal("coin - {$info['price']}")]);
      */
        $userInfo =getUserInfo($uid);
        if ($userInfo['user_type']== 4){
            return ['code'=>'2126', 'msg'=> codemsg(2126)];
        }
        DI()->notorm->appointment
            ->where("id = {$appointment_id}")
            ->update(['unlock_times'=>  new NotORM_Literal("unlock_times + 1")]);
        DI()->notorm->shop
            ->where("id = {$info['shop_id']}")
            ->update(['deal_num'=>  new NotORM_Literal("deal_num + 1")]);
        $data = [
            'order_number'=> getOrderid($uid),
            'uid' => $uid,
            'appointment_id' => $appointment_id,
            'price' => $info['price'],
            'addtime' => time(),
            'status' => 1,
        ];
        DI()->notorm->appointment_order->insert($data);
        yhTaskFinish($uid,getTaskConfig('task_3'));
        return array('code' => 0, 'msg' => '', 'info' => ['操作成功']);
    }

    public function myOrder($uid,$p,$limit,$game_tenant_id){
        $tenantId  = getTenantIds();
        if($p<1){
            $p=1;
        }
        if ($limit){
            $nums = 20;
        }else{
            $nums=$limit;
        }
        $start=($p-1)*$nums;
        $orderList =  DI()->notorm->appointment_order
            ->where("uid = $uid and tenant_id  = $tenantId")
            ->limit($start,$nums)
         ->fetchAll();
        $appointmentId = array_column($orderList,'appointment_id');
        $appointmentIdString  = implode(',',$appointmentId);
        $appointmentList =DI()->notorm->appointment
            ->where("id in  ({$appointmentIdString})")
            ->fetchAll();
        $appointmentListById = array_column($appointmentList,null,'id');
        foreach ($orderList as $key =>  $orderValue){
            $orderList[$key]['appointment_title'] = $appointmentListById[$orderValue['title']];
        }
        return  $orderList;

    }

    public function addCollect($uid,$appointment_id,$game_tenant_id){
            $tenant_id  =getTenantId();
            $like=DI()->notorm->appointment_collect
                ->select("id")
                ->where("uid='{$uid}' and appointment_id='{$appointment_id}' and tenant_id ='{$tenant_id}' ")
                ->fetchOne();

            if($like){
                DI()->notorm->appointment_collect
                    ->where("uid='{$uid}' and appointment_id='{$appointment_id}' and tenant_id='{$tenant_id}' ")
                    ->delete();

                DI()->notorm->appointment
                    ->where("id = '{$appointment_id}'")
                    ->update( array('collection' => new NotORM_Literal("collection - 1") ) );

            }else{
                DI()->notorm->appointment_collect
                    ->insert(array("uid"=>$uid,"appointment_id"=>$appointment_id,"addtime"=>time(),"tenant_id"=>$tenant_id ));

                DI()->notorm->appointment
                    ->where("id = '{$appointment_id}'")
                    ->update( array('collection' => new NotORM_Literal("collection + 1") ) );
                yhTaskFinish($uid,getTaskConfig('task_4'));
            }

            return ['code'=> 0,'操作成功'];
        }
    public function collectList($uid,$game_tenant_id,$p,$limit){
        $tenant_id  =getTenantId();
        if($p<1){
            $p=1;
        }
        if ($limit){
            $nums = 20;
        }else{
            $nums=$limit;
        }
        $start=($p-1)*$nums;
        $list  =DI()->notorm->appointment_collect
            ->where("uid='{$uid}' ")
            ->limit($start,$nums)
            ->fetchAll();
        if ($list){
            $appointmentId = array_column($list,'appointment_id');
            $appointmentIdString = implode(',',$appointmentId);
            $appointmentList =DI()->notorm->appointment
                ->where("id in ($appointmentIdString) ")
                ->fetchAll();
            $appointmentListById = array_column($appointmentList,null,'id');
            $provinceList = DI()->notorm->province->fetchAll();
            $cityList = DI()->notorm->city->fetchAll();
            $areaList = DI()->notorm->area->fetchAll();
            $provinceListById = array_column($provinceList,null,'id');
            $cityListById= array_column($cityList,null,'id');
            $areaListById = array_column($areaList,null,'id');
            foreach ($list as $key => $value){
                $list[$key]['title'] = $appointmentListById[$value['appointment_id']]['title'];
                $list[$key]['age'] = $appointmentListById[$value['appointment_id']]['age'];
                $list[$key]['score'] = $appointmentListById[$value['appointment_id']]['score'];
                $list[$key]['price'] = $appointmentListById[$value['appointment_id']]['price'];
                $list[$key]['service_items'] = $appointmentListById[$value['appointment_id']]['service_items'];
                $list[$key]['province_name'] = $provinceListById[$appointmentListById[$value['appointment_id']]['province_id']]['province'];
                $list[$key]['city_name'] = $cityListById[$appointmentListById[$value['appointment_id']]['city_id']]['city'];
                $list[$key]['area_name'] = $areaListById[$appointmentListById[$value['appointment_id']]['area_id']]['area'];
                $list[$key]['is_authentication'] = $appointmentListById[$value['appointment_id']]['is_authentication'];
                $list[$key]['viewing_times'] = $appointmentListById[$value['appointment_id']]['viewing_times'];
                $list[$key]['unlock_times'] = $appointmentListById[$value['appointment_id']]['unlock_times'];
                $list[$key]['img'] = $appointmentListById[$value['appointment_id']]['img'];
            }
        }

        return $list;

    }
    public function commentList($appointment_id,$p,$limit){
        if($p<1){
            $p=1;
        }
        if ($limit){
            $nums = 20;
        }else{
            $nums=$limit;
        }
        $start=($p-1)*$nums;
        $list = DI()->notorm->appointment_comment ->where("status  = 1 and  appointment_id = '{$appointment_id}'")->limit($start,$nums)->fetchAll();
        if ($list){
            $uidArray = array_column($list,'uid');
            $uidString =  implode(',',$uidArray);
            $userList = DI()->notorm->users->select('id,user_login,user_nicename,avatar')->where(" id  in ({$uidString})")->fetchAll();

            $userListById =  array_column($userList,null,'id');
            foreach ($list as $key =>  $value){
                $list[$key]['user_nicename'] = $userListById[$value['uid']]['user_nicename'];
                $list[$key]['user_login']  =$userListById[$value['uid']]['user_login'];
                $list[$key]['avatar']  = get_upload_path($userListById[$value['uid']]['avatar']);
            }

        }
        return $list;
    }
    public function browseLog($uid,$game_tenant_id,$p,$limit){
        $tenant_id  =getTenantId();
        if($p<1){
            $p=1;
        }
        if ($limit){
            $nums = 20;
        }else{
            $nums=$limit;
        }
        $start=($p-1)*$nums;
        $list  =DI()->notorm->appointment_browse_log
            ->where("uid='{$uid}' ")
            ->limit($start,$nums)
            ->fetchAll();
        if ($list){
            $appointmentId = array_column($list,'appointment_id');
            $appointmentIdString = implode(',',$appointmentId);
            $appointmentList =DI()->notorm->appointment
                ->where("id in ($appointmentIdString) ")
                ->fetchAll();
            $appointmentListById = array_column($appointmentList,null,'id');
            $provinceList = DI()->notorm->province->fetchAll();
            $cityList = DI()->notorm->city->fetchAll();
            $areaList = DI()->notorm->area->fetchAll();
            $provinceListById = array_column($provinceList,null,'id');
            $cityListById= array_column($cityList,null,'id');
            $areaListById = array_column($areaList,null,'id');
            foreach ($list as $key => $value){
                $list[$key]['title'] = $appointmentListById[$value['appointment_id']]['title'];
                $list[$key]['age'] = $appointmentListById[$value['appointment_id']]['age'];
                $list[$key]['score'] = $appointmentListById[$value['appointment_id']]['score'];
                $list[$key]['price'] = $appointmentListById[$value['appointment_id']]['price'];
                $list[$key]['service_items'] = $appointmentListById[$value['appointment_id']]['service_items'];
                $list[$key]['province_name'] = $provinceListById[$appointmentListById[$value['appointment_id']]['province_id']]['province'];
                $list[$key]['city_name'] = $cityListById[$appointmentListById[$value['appointment_id']]['city_id']]['city'];
                $list[$key]['area_name'] = $areaListById[$appointmentListById[$value['appointment_id']]['area_id']]['area'];
                $list[$key]['is_authentication'] = $appointmentListById[$value['appointment_id']]['is_authentication'];
                $list[$key]['viewing_times'] = $appointmentListById[$value['appointment_id']]['viewing_times'];
                $list[$key]['unlock_times'] = $appointmentListById[$value['appointment_id']]['unlock_times'];
                $list[$key]['img'] = $appointmentListById[$value['appointment_id']]['img'];
            }
        }

        return $list;

    }

    public  function inviteSet(){
        $list =DI()->notorm ->activity_config->where(['type'=>3,'tenant_id'=>getTenantId()])->order('sort_num asc')->fetchAll();
        return $list;
    }

    public  function consumptionSet(){
        $list =DI()->notorm ->activity_config->where(['type'=>4,'tenant_id'=>getTenantId()])->order('sort_num asc')->fetchAll();

        return $list;
    }


    public  function stationList($uid,$game_tenant_id,$p,$limit){
        $tenant_id  =getTenantId();
        if($p<1){
            $p=1;
        }
        if ($limit){
            $nums = 20;
        }else{
            $nums=$limit;
        }
        $start=($p-1)*$nums;
        $list  =DI()->notorm->station_user
            ->where("uid='{$uid}' and status != 3 ")
            ->limit($start,$nums)
            ->fetchAll();
        if ($list){
            $station_id  = array_column($list,'station_id');
            $stationIdString =  implode(',',$station_id);
            $station =  DI()->notorm->station_letter->where("id  in ($stationIdString)")->fetchAll();
            $stationByIdList  = array_column($station,null,'id');

            foreach ($list as $key => $value){
                if(isset($stationByIdList[$value['station_id']])){
                    $list[$key]['title'] = $stationByIdList[$value['station_id']]['title'];
                    $list[$key]['desc'] = html_entity_decode($stationByIdList[$value['station_id']]['desc']);
                    $list[$key]['type'] = $stationByIdList[$value['station_id']]['type'];
                }else{
                    unset($list[$key]);
                    DI()->notorm->station_user->where("id = ?", intval($value['id']))->delete();
                }
            }
            if ($game_tenant_id !=  106){
                DI()->notorm->station_user
                    ->where("uid ='{$uid}'")
                    ->update(['status'=> 2,'update_time'=> time()]);
            }
           // $list['total'] = $count;

           // $list['no_read_total'] = $noReadCount;
        }
        $count  =DI()->notorm->station_user->where("uid='{$uid}' and status != 3 ")->count();
        $noReadCount = DI()->notorm->station_user->where("uid='{$uid}' and status = 1 ")->count();
        return ['list'=> $list,'total'=>$count,'no_read_total'=>$noReadCount];

    }

    public  function stationInfo($id){
        DI()->notorm->station_user
            ->where("id='{$id}'")
            ->update(['status'=> 2,'update_time'=> time()]);
        $info  =DI()->notorm->station_user
            ->where("id='{$id}'")
            ->fetchOne();
        $station =  DI()->notorm->station_letter->where("id  =  {$info['station_id']}")->fetchOne();
        $info['title'] = $station['title'];
        $info['desc'] =html_entity_decode( $station['desc']);
        $info['type'] = $station['type'];
        return  $info;
    }

    public function popStation($uid){
        $info  =DI()->notorm->station_user
            ->where("uid='{$uid}' and  type  = 2")
            ->order('id desc')
            ->fetchOne();
       /* DI()->notorm->station_user
            ->where("id='{$info['id']}'")
            ->update(['status'=> 2,'addtime'=> time()]);*/
       if ($info){
           $tenant_id  = getTenantId();
           $station =  DI()->notorm->station_letter->where("tenant_id = {$tenant_id} and  id = {$info['station_id']}  and type = 2 and status = 1")->order('id desc')->fetchOne();
           $station['desc']= html_entity_decode($station['desc']);
       }else{
          return [];
        }


        return  $station;
    }

    public function newStation($uid){
        $count  =DI()->notorm->station_user
            ->where("uid='{$uid}' and  status = 1")
            ->order('id desc')
            ->count();
        /* DI()->notorm->station_user
             ->where("id='{$info['id']}'")
             ->update(['status'=> 2,'addtime'=> time()]);*/


        return  ['code'=> 0,'info'=>['count'=> $count] ];
    }
    public function delstation($id){
       $res =  DI()->notorm->station_user->where("id='{$id}'")->update(['status'=> 3,'update_time'=> time()]);
       return  $res;

    }

    public  function shopInfo($id,$p,$limit){
        $shopInfo = DI()->notorm->shop
            ->where(['id'=>$id ])->fetchOne();
        $shopInfo['authentication_count'] =DI()->notorm->appointment
            ->where("shop_id = {$shopInfo['id']} and   is_authentication = 1 and status = 1 ")
            ->count();
        $shopInfo['shop_order_count'] =DI()->notorm->appointment
            ->where("shop_id = {$shopInfo['id']} ")
            ->sum('unlock_times');

        if($p<1){
            $p=1;
        }
        if ($limit){
            $nums = 20;
        }else{
            $nums=$limit;
        }
        $start=($p-1)*$nums;
        $shopInfo['appointment_list']=DI()->notorm->appointment
            ->where("shop_id = {$id}")
            ->limit($start,$nums)
            ->fetchOne();
        return $shopInfo;

    }

    public function turntableConfig(){
        $list = DI()->notorm->turntable_set
            ->where(['tenant_id'=>getTenantId(),'status'=> 1])
            ->fetchAll();
        return $list;
    }

    public function turntableaward($uid,$game_tenant_id){
        $programList = DI()->notorm->turntable_program->where(['status' => 1 ,'tenant_id'=>getTenantId()])->fetchAll();
        if (!$programList){
               return array('code' => 2128, 'msg' => codemsg(2128));
        }
        $canProgram =  [];
        $userInfo  = getUserInfo($uid);
        if ($game_tenant_id == 106){
            if ($userInfo['turntable_times']<  1){
                return array('code' => 2121, 'msg' => codemsg(2121));
            }
        }
        $config = getConfigPub();
        if ($userInfo['coin']<$config['turntable_consumption_amout']){
            return array('code' => 2006, 'msg' => codemsg(2006));
            }
        if ($config['turntable_consumption_amout']> 0){
            $consumptionData = [
                'type' => 'expend',
                'user_type' => $userInfo['user_type'],
                'giftid' => 0,
                'uid'=> $uid,
                'addtime' => time(),
                'tenant_id' => $userInfo['tenant_id'],
                'action' => 'turntable_consumption',
                'totalcoin' =>$config['turntable_consumption_amout'],//金额
                "giftcount" => 1,
                'is_withdrawable' => 1,
                "pre_balance" => $userInfo['coin'],
                "after_balance" => bcsub($userInfo['coin'], $config['turntable_consumption_amout'],2),
            ];
            DI()->notorm ->users_coinrecord->insert($consumptionData);  //  账变记录
        }
        $amount  =  $config['turntable_consumption_amout']?$config['turntable_consumption_amout']:0;
        foreach ($programList as $value){
            if ($value['number'] > $value['used_number'] ){
                $canProgram[] = $value;
            }
        }
        if ($canProgram){ // 有可以用次数的方案
            $randProgram = mt_rand(0,count($canProgram)-1);
            $program_desc = DI()->notorm->turntable_program_desc
            ->where([
                'program_id'=> $canProgram[$randProgram]['id'],
                'times' =>$canProgram[$randProgram]['used_number'] +1
            ])->fetchOne();
            $randNumber = mt_rand(1,100);
            if ($randNumber<=$program_desc['probability']){ // 获奖
                $turntable =  DI()->notorm->turntable_set->where(['id'=>  $program_desc['turntable_id'],'tenant_id'=>getTenantId()])->fetchOne(); // 奖品
                if ($turntable['status'] == 1){  // 奖品被禁用 就不中奖
                    DI()->notorm->turntable_program->where(['id' => $canProgram[$randProgram]['id'] ])
                        ->update( array('used_number' => new NotORM_Literal("used_number + 1 ") ) ); // 方案使用次数加 1
                    $res = addAward($uid,5 ,$turntable['number'],$turntable['type'], $turntable['name'],
                        $turntable['exchange_number']);// 添加奖励
                    if ($turntable['type'] == 1){ // 金额奖励

                        $amount = $turntable['number']- $config['turntable_consumption_amout'];
                        if ($game_tenant_id == 106){
                            $userData = array(
                                "coin"          =>new NotORM_Literal("coin + {$amount}"),
                                "turntable_times" =>new NotORM_Literal("turntable_times -1")
                            );
                        }else{
                            $userData = array(
                                "coin"   =>new NotORM_Literal("coin + {$amount}"),
                            );
                        }
                        $coinrecordData = [
                            'type' => 'income',
                            'user_type' => $userInfo['user_type'],
                            'giftid' => $res,
                            'uid'=> $uid,
                            'addtime' => time(),
                            'tenant_id' => $userInfo['tenant_id'],
                            'action' => 'turntable_lottery',
                            'totalcoin' => $turntable['number'],//金额
                            "giftcount" => 1,
                            'is_withdrawable' => 1,
                            "pre_balance" => bcsub($userInfo['coin'], $config['turntable_consumption_amout'],2),
                            "after_balance" => bcadd($userInfo['coin'],$amount,2),
                        ];
                        DI()->notorm ->users_coinrecord ->insert($coinrecordData);  //  账变记录
                    }else{
                        if ($game_tenant_id == 106) {
                            $userData = array(
                                "turntable_times" => new NotORM_Literal("turntable_times  -1"),
                                "coin" => new NotORM_Literal("coin  -{$amount}")
                            );
                        }else{
                            $userData = array(
                                "coin" => new NotORM_Literal("coin  -{$amount}")
                            );
                        }
                    }
                }else{ //奖品被禁用 就不中奖
                    if ($game_tenant_id == 106) {
                        $userData = array(
                            "turntable_times" => new NotORM_Literal("turntable_times  -1"),
                            "coin" => new NotORM_Literal("coin  -{$amount}")
                        );
                    }else{
                        $userData = array(
                            "coin" => new NotORM_Literal("coin  -{$amount}")
                        );
                    }
                    $turntable =  DI()->notorm->turntable_set->where(['number'=>  0,'tenant_id'=>getTenantId()])->fetchOne();// 没有奖品
                }
            }else{
                $userData = array(
                    "turntable_times"=>new NotORM_Literal("turntable_times  -1"),
                    "coin" => new NotORM_Literal("coin  -{$amount}")
                );
                $turntable =  DI()->notorm->turntable_set->where(['number'=>  0,'tenant_id'=>getTenantId()])->fetchOne();// 没有奖品
            }
            $lottery = [
                'uid'=> $uid,
                'program_id' => $canProgram[$randProgram]['id'],
                'program_desc_id' => $program_desc['id'],
                'turntable_id' => $program_desc['turntable_id'],
                'name' => $turntable['name'],
                'type' =>$turntable['type'],
                'number' =>$turntable['number'],
                'addtime' => time(),
            ];
        }else{
            $randProgram = mt_rand(0,count($programList) -1);
            $program_desc = DI()->notorm->turntable_program_desc
                ->where([
                    'program_id'=> $programList[$randProgram]['id'],
                    'times' => 1
                ])->fetchOne();
            $randNumber = mt_rand(1,100);
            if ($randNumber<=$program_desc['probability']){ // 获奖
                $turntable =  DI()->notorm->turntable_set->where(['id'=>  $program_desc['turntable_id'],'tenant_id'=>getTenantId()])->fetchOne(); // 奖品
                if ($turntable['status'] == 1){
                    DI()->notorm->turntable_program->where(['id' => $programList[$randProgram]['id'] ])
                    ->update( array('used_number' => 1  ) ); // 方案使用次数加 1
                    $res = addAward($uid,5 ,$turntable['number'],$turntable['type'],$turntable['name'],$turntable['exchange_number']);// 添加奖励
                    if ($turntable['type'] == 1){ // 金额奖励
                        $amount = $turntable['number']- $config['turntable_consumption_amout'];
                        if ($game_tenant_id == 106) {
                            $userData = array(
                                "coin" => new NotORM_Literal("coin + {$amount}"),
                                "turntable_times" => new NotORM_Literal("turntable_times -1")
                            );
                        }else{
                            $userData = array(
                                "coin" => new NotORM_Literal("coin + {$amount}"),

                            );
                        }
                        $coinrecordData = [
                            'type' => 'income',
                            'user_type' => $userInfo['user_type'],
                            'giftid' => $res,
                            'uid'=> $uid,
                            'addtime' => time(),
                            'tenant_id' => $userInfo['tenant_id'],
                            'action' => 'turntable_lottery',
                            'totalcoin' => $turntable['number'],//金额
                            "giftcount" => 1,
                            'is_withdrawable' => 1,
                            "pre_balance" => bcsub($userInfo['coin'], $config['turntable_consumption_amout'],2),
                            "after_balance" => bcadd($userInfo['coin'],$amount,2),


                        ];
                        DI()->notorm ->users_coinrecord->insert($coinrecordData);  //  账变记录
                    }else{ // 奖品禁用
                        if ($game_tenant_id == 106) {
                            $userData = array(
                                "turntable_times" => new NotORM_Literal("turntable_times -1"),
                                "coin" => new NotORM_Literal("coin - {$amount}"),
                            );
                        }else{
                            $userData = array(
                                "coin" => new NotORM_Literal("coin  -{$amount}")
                            );
                        }
                    }
                }else{
                    $turntable =  DI()->notorm->turntable_set->where(['number'=>  0,'tenant_id'=>getTenantId()])->fetchOne();// 没有奖品
                    if ($game_tenant_id == 106) {
                        $userData = array(
                            "turntable_times" => new NotORM_Literal("turntable_times -1"),
                            "coin" => new NotORM_Literal("coin - {$amount}"),
                        );
                    }else{
                        $userData = array(
                            "coin" => new NotORM_Literal("coin  -{$amount}")
                        );
                    }
                }
            }else{
                $turntable =  DI()->notorm->turntable_set->where(['number'=>  0,'tenant_id'=>getTenantId()])->fetchOne();// 没有奖品
                if ($game_tenant_id == 106) {
                    $userData = array(
                        "turntable_times" => new NotORM_Literal("turntable_times -1"),
                        "coin" => new NotORM_Literal("coin - {$amount}"),
                    );
                }else{
                    $userData = array(
                        "coin" => new NotORM_Literal("coin  -{$amount}")
                    );
                }
            }
            $lottery = [
                'uid'=> $uid,
                'program_id' => $programList[$randProgram]['id'],
                'program_desc_id' => $program_desc['id'],
                'turntable_id' => $program_desc['turntable_id'],
                'name' => $turntable['name'],
                'type' =>$turntable['type'],
                'number' =>$turntable['number'],
                'addtime' => time(),
            ];
        }

        DI()->notorm->users->where("id  = '$uid'")->update($userData);
        delUserInfoCache($uid);
        DI()->notorm->users_lottery->insert($lottery);
        return  ['code'=> 0 ,'info'=>$turntable];
    }
}