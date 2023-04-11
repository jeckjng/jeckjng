<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_YhTask extends PhalApi_Model_NotORM
{
    public function getYhTaskClassification($uid,$client,$type)
    {
        $prefix = DI()->config->get('dbs.tables.__default__.prefix');
        $time = time();
        $list = DI()->notorm->yh_task
            ->queryAll("select b.id as task_classification_id,b.unlock_amount,b.logo,b.bgimg,b.experience_shop,a.id as task_id,a.name,a.reward1,a.reward1_number,a.reward2_upgrade_vip,a.price,a.img,a.client from {$prefix}yh_task as a 
                        left join {$prefix}yh_task_classification b on a.classification=b.id 
                        where b.type = {$type} and b.status = 1 and a.start_time<{$time} and a.end_time>{$time} and a.status=1");

        $user_task_list = DI()->notorm->yh_user_task->where(['uid'=>$uid,'status'=>[1,2,3,5]])->fetchPairs('task_id');

        foreach ($list as $key=>$val){
            $client_arr = explode(',',$val['client']);
            if(in_array($client,$client_arr)){
                if (array_key_exists($val['task_id'],$user_task_list)){
                    $list[$key]['user_task_status'] = $user_task_list[$val['task_id']]['status'];
                    $list[$key]['user_task_id'] = $user_task_list[$val['task_id']]['id'];
                }else{
                    $list[$key]['user_task_status'] = 0;
                }
               // $list[$key]['user_task_status'] = $val['user_task_status'] == NULL ? 0 : $val['user_task_status'];
            }else{
                unset($list[$key]);
            }
        }
        return $list;

        /*$list = DI()->notorm->yh_task_classification
            ->select("id as task_classification_id,status,logo,bgimg,unlock_amount,experience_shop,limit_max_balance")
            ->where('type=?',$type)
            ->where('status=?',1)
            ->order('id desc')
            ->fetchAll();

        $task_list = DI()->notorm->yh_task
            ->select('id as task_id,name,price,reward1_number,reward1,reward2_upgrade_vip,sort,classification,client,img')
            ->where('start_time<? and end_time>? and status=1 ',time(),time())
            ->order('sort asc')
            ->fetchAll();


        foreach ($list as $key=>$val){

            $user_task_list = DI()->notorm->yh_user_task
                ->select('id,task_id,status,mtime')
                ->where('uid=? and classification=? and status in (1,2,3,5)',intval($uid),intval($val['task_classification_id']))
                ->fetchAll();
            $user_task_ids_1 = $user_task_ids_2 = $user_task_ids_3 = $user_task_ids_5 = [];
            foreach ($user_task_list as $user_task_value){
                if ($user_task_value['status'] == 1){
                    array_push($user_task_ids_1,$user_task_value['task_id']);
                }
                if ($user_task_value['status'] == 2){
                    array_push($user_task_ids_2,$user_task_value['task_id']);
                }
                if ($user_task_value['status'] == 3){
                    array_push($user_task_ids_3,$user_task_value['task_id']);
                }
                if ($user_task_value['status'] == 5){
                    array_push($user_task_ids_5,$user_task_value['task_id']);
                }
            }

            foreach ($task_list as $task_key=>$task_value){
                $client_arr = explode(',',$task_value['client']);
                if(!in_array($client,$client_arr)){
                    continue;
                }

                if ($val['task_classification_id'] == $task_value['classification']){
                    $task_list[$task_key]['user_task_status'] = 0;
                    if (in_array($task_value['task_id'],$user_task_ids_1)){
                        $task_list[$task_key]['user_task_status'] = 1;
                        $task_list[$task_key]['user_task_id'] = 1;
                    }
                    if (in_array($task_value['task_id'],$user_task_ids_2)){
                        $task_list[$task_key]['user_task_status'] = 2;
                    }
                    if (in_array($task_value['task_id'],$user_task_ids_3)){
                        $task_list[$task_key]['user_task_status'] = 3;
                    }
                    if (in_array($task_value['task_id'],$user_task_ids_5)){
                        $task_list[$task_key]['user_task_status'] = 5;
                    }
                    $list[$key] = array_merge($list[$key],$task_list[$task_key]);
                    //$list[$key]['data'][] = $task_list[$task_key];
                }
            }
        }

        return $list;*/
    }

    public function getTask($uid,$client,$task_classification_id,$type='',$user_type='')
    {
        if($user_type==''){
            $user_info = getUserInfo($uid);
            if($user_info['user_type']==4){  // 2 会员，4 游客
                return array('code' => 2005, 'msg' => '', 'info' => array()); // 屏蔽游客
            }
        }

        $classification_info = DI()->notorm->yh_task_classification->where('id=?',intval($task_classification_id))->fetchOne();
        if(isset($classification_info['status']) && $classification_info['status']=='0'){
            return array('code' => 2003, 'msg' => '', 'info' => array()); // 分类状态未开启
        }

        if($type != 'getTaskClassification'){
            // 判断解锁金额
            if($classification_info['experience_shop']=='0' && $classification_info['unlock_amount']>0){ // experience_shop：是否体验商城，unlock_amount：解锁金额（解锁条件1）
                $user_info = DI()->notorm->users->where('coin')->where("id=?",intval($uid))->fetchOne();
                if($user_info['coin'] * 100 < $classification_info['unlock_amount'] * 100){
                    return array('code' => 2006, 'msg' => '', 'info' => array('unlock_amount'=>floatval($classification_info['unlock_amount']))); // 金币不足,这时候需要用户去充值
                }
            }
            // 判断直邀人数
            if($classification_info['experience_shop']=='0' && $classification_info['direct_invitation']>0){
                $direct_invitation = DI()->notorm->users_agent->where('one_uid=?',intval($uid))->count();
                if($direct_invitation < $classification_info['direct_invitation']){
                    return array(
                        'code' => 2009,
                        'msg' => '直邀人数不够，您需要邀请'.$classification_info['direct_invitation'].'直属下级，才能解锁此任务',
                        'info' => array('direct_invitation'=>$classification_info['direct_invitation'])
                    );
                }
            }
        }

        $user_task = DI()->notorm->yh_user_task->select('id,task_id,status,mtime')->where('uid=? and classification=? and status in (1,2,3)',intval($uid),intval($task_classification_id))->fetchAll();
        $user_task_ids = array();
        if(count($user_task)>0){
            foreach ($user_task as $key=>$val){
                array_push($user_task_ids,$val['task_id']);
                if(in_array($val['status'],[1,2])){
                    if($type != 'getTaskClassification' && !(DI()->notorm->yh_task->where('id=?',$val['task_id'])->fetchOne())){
                        continue;
                    }
                    return array('code' => 2004, 'msg' => '', 'info' => array());
                }
                if($val['status'] == 3 && (time()-$val['mtime'])<$classification_info['space_time']){
                    return array('code' => 2002, 'msg' => '', 'info' => array());
                }
            }
        }
        $user_task_ids_sql = count($user_task_ids)>0 ? ' and id NOT IN ('.implode(',',$user_task_ids).')' : '';
        $list = DI()->notorm->yh_task
            ->select("id as task_id,name,description,price,start_time,end_time,img,reward1,reward2_upgrade_vip,client,is_upleveltask,task_details_type,task_details")
            ->where('classification=? and start_time<? and end_time>? and status=1 '.$user_task_ids_sql,intval($task_classification_id),time(),time())
            ->fetchAll();

        $task_list = array();
        foreach ($list as $key=>$val){
            $client_arr = explode(',',$val['client']);
            if(in_array($client,$client_arr)){
                unset($val['is_upleveltask']);
                array_push($task_list,$val);
            }
        }
        if(count($task_list)>0){
            $task_info = $task_list[array_rand($task_list,1)];
            if($user_type==''){
                $task_count =  DI()->notorm->user_task->where('uid=? and status in(1,2,3)',intval($uid))->count();
                $plan = DI()->notorm->task_plan->where('num=? and status=1',intval($task_count +1))->fetchOne();; // where('num=? and status=1',intval($task_count))
                if(isset($plan['type'])){
                    $task_info = $this->plan($task_list,$user_info,$uid,$task_info,$plan);
                }
            }

            unset($task_info['client']);
            $task_info['task_id'] = intval($task_info['task_id']);
            $task_info['price'] = floatval($task_info['price']);
            $task_info['start_time'] = intval($task_info['start_time']);
            $task_info['end_time'] = intval($task_info['end_time']);
            $task_info['reward1'] = floatval($task_info['reward1']);
            $task_info['reward2_upgrade_vip'] = intval($task_info['reward2_upgrade_vip']);
            $task_info['task_details_type'] = intval($task_info['task_details_type']);
            $task_info['task_details'] = htmlspecialchars_decode($task_info['task_details']);
            return array('code' => 0, 'msg' => '', 'info' => $task_info);
        }else{
            return array('code' => 2017, 'msg' => '', 'info' => array());
        }
    }

    /*
     * 取相近值
     * */
    public function plan($task_list,$user_info,$uid,$task_info,$plan){
        $task_list = array_column($task_list,null,'price');
        ksort($task_list);
        $amount = 0;
        if($plan['type']==1){
            $amount = $plan['percent']*$user_info['coin'];
        }
        if($plan['type']==2){
            $amount = $plan['amount'];
        }
        $eq_arr = array();
        $similar_1 = array();
        $similar_2 = array();
        foreach ($task_list as $key=>$val){
            if($amount == $val['price']){
                array_push($eq_arr,$val);
            }
            if($amount>$val['price']){
                $similar_1['key'] = $key;
                $similar_1['val'] = $val;
            }
            if($amount<$val['price']){
                $similar_2['key'] = $key;
                $similar_2['val'] = $val;
            }
        }
        if(count($eq_arr) > 0){
            $task_info = $eq_arr[array_rand($task_list,1)];
        }else{
            if($similar_1 && !$similar_2){
                $task_info = $similar_1;
            }
            if(!$similar_1 && $similar_2){
                $task_info = $similar_2;
            }
            if($similar_1 && $similar_2){
                $differ_1 = abs($amount-$similar_1['key']);
                $differ_2 = abs($amount-$similar_2['key']);
                $task_info = $differ_1 <= $differ_2 ? $similar_1['val'] : $similar_2['val'];
            }
        }

        return $task_info;
    }

    public function addUserTask($uid, $task_id, $client)
    {
        $user_info = getUserInfo($uid);
        if($user_info['user_type']==4){
            return array('code' => 2005, 'msg' => codemsg('2005'), 'info' => array()); // 屏蔽游客
        }

        $is_exist = DI()->notorm->yh_user_task->where('uid=? and task_id=? and status in(1,2,3,5)',$uid,$task_id)->fetchOne();
        if($is_exist){
            return array('code' => 2015, 'msg' => codemsg('2015'), 'info' => array()); // 任务已领取
        }

        $task_info = DI()->notorm->yh_task->where('id=?',intval($task_id))->fetchOne();

        if($task_info['start_time'] > time() || $task_info['end_time'] < time()){
            return array('code' => 2016, 'msg' => codemsg('2016'),  'info' => array());  // 当前时间 不在该任务的 任务时间内
        }

        $classification_info = DI()->notorm->yh_task_classification->where('id=?',intval($task_info['classification']))->fetchOne();

        // is_upleveltask：是否需要上一级任务完成：0否，1是，任务类型：1初级任务，2中级任务，3高级任务
        if($classification_info['experience_shop']=='0' && $classification_info['type']>1 && $task_info['is_upleveltask']=='1'){
            $uptype = $classification_info['type']-1;
            $usertask_list =  DI()->notorm->yh_user_task->select('task_id')->where('uid=? and task_type=? and status=3 and experience_shop=0',intval($uid),intval($uptype))->fetchAll();
            $usertask_ids = count($usertask_list) > 0 ? array_keys(array_column($usertask_list,null,'task_id')) : [];
            $task_list =  DI()->notorm->yh_task->select('id')->where(
                'start_time<=? and end_time>=? and client=? and type=? and status=1',
                time(),time(),$task_info['client'],$uptype)->fetchAll();

            foreach ($task_list as $key=>$val){
                if(!in_array($val['id'],$usertask_ids)){
                    return array('code' => 2060, 'msg' => codemsg('2060'), 'info' => array());
                }
            }
        }

        // 判断任务价格
        if($classification_info['experience_shop']=='0' && $task_info['price']>0){ // experience_shop：是否体验商城，任务价格大于 0
            $user_info = DI()->notorm->users->where('coin')->where("id=?",intval($uid))->fetchOne();
            if($user_info['coin'] * 100 < $task_info['price'] * 100){
                return array('code' => 2006, 'msg' => codemsg('2006'), 'info' => array('price'=>floatval($task_info['price'])));  // 金币不足,这时候需要跳转去充值
            }
        }
        // 每日任务数限制
        if($classification_info['experience_shop']=='0' && $classification_info['daily_task']>0){ // experience_shop：是否体验商城，任务价格大于 0
            $user_task_count = DI()->notorm->yh_user_task->where(
                'uid=? and classification=? and ctime>=? and ctime<=? and status in(1,2,3,5)',
                intval($uid),intval($classification_info['id']),strtotime(date('Y-m-d').' 00:00:00'),strtotime(date('Y-m-d').' 23:59:59'))
                ->count();
            if($user_task_count >= $classification_info['daily_task']){
                return array('code' => 2010, 'msg' => codemsg('2010'), 'info' => array()); // 每日任务已达上限
            }
        }

        $users_vip_info = DI()->notorm->users_vip->where('uid=? and endtime>?',intval($uid),time())->order('grade desc')->fetchOne();
        $vip_id = isset($users_vip_info['vip_id']) ? $users_vip_info['vip_id'] : 0;

        $status = 1;
        if ($user_info['user_type']==2 && intval($task_info['classification']) == getTaskConfig('task_1')){
            $status = 5;
        }

        $data = array(
            'uid' => intval($uid),
            'vip_id' => intval($vip_id),
            'task_id' => intval($task_id),
            'task_name' => $task_info['name'],
            'task_type' => intval($task_info['type']),
            'client' => intval($client),
            'classification' => intval($task_info['classification']),
            'experience_shop' => intval($classification_info['experience_shop']),
            'commission_rate' => floatval($classification_info['commission_rate']),
            'price' => floatval($task_info['price']),
            'reward1' => intval($task_info['reward1']),
            'reward1_number' => intval($task_info['reward1_number']),
            'reward2_upgrade_vip' => intval($task_info['reward2_upgrade_vip']),
            'start_time' => intval($task_info['start_time']),
            'end_time' => intval($task_info['end_time']),
            'status' => $status,
            'act_uid' => intval($uid),
            'tenant_id' => intval(getTenantId()),
            'ctime' => time(),
            'mtime' => time(),
            'only_one'=>$task_info['only_one'],
        );

        $res = DI()->notorm->yh_user_task->insert($data);
        if(!isset($res['id'])){
            return array('code' => 1007, 'msg' => codemsg('1007'), 'info' => array());
        }
        return array('code' => 0, 'msg' => '', 'info' => array('user_task_id'=>intval($res['id'])));
    }

    public function getUserTask($uid,$type,$p)
    {
        $p = $p > 1 ? $p : 1;
        $limit = 2;
        $pstart = ($p-1)*$limit;
        $list = DI()->notorm->user_task
            ->select("id as user_task_id,task_id,task_name,status,classification,reward1,reward2_upgrade_vip,ctime")
            ->where('uid=? and task_type=?',$uid,$type)
            ->order('ctime desc,id desc')
            ->limit($pstart,$limit)
            ->fetchAll();

        $data_list = array();
        foreach ($list as $key=>$val){
            $task_info = DI()->notorm->task->where('id=?',$val['task_id'])->fetchOne();
            if(!$task_info){
                continue;
            }
            $classification_info = DI()->notorm->task_classification->where('id=?',intval($val['classification']))->fetchOne();

            $list[$key]['unlock_amount'] = $classification_info['unlock_amount'];
            $list[$key]['img'] = $task_info['img'];

            $list[$key]['name'] = $task_info['name'];
            if(!$val['task_name']){
                $list[$key]['reward1'] = $task_info['reward1'];
                $list[$key]['reward2_upgrade_vip'] = $task_info['reward2_upgrade_vip'];
            }

            unset($list[$key]['classification']);
            unset($list[$key]['task_name']);
            array_push($data_list,$list[$key]);
        }
        return $data_list;
    }

    public function getUserTaskInfo($uid,$user_task_id)
    {
        $user_task_info = DI()->notorm->user_task
            ->select("id as user_task_id,task_id,task_name,price,reward1,reward2_upgrade_vip,status")
            ->where('uid=? and id=?',intval($uid),intval($user_task_id))
            ->fetchOne();

        $task_info = DI()->notorm->task->where('id=?',intval($user_task_info['task_id']))->fetchOne();
        $user_task_info['name'] = $task_info['name'];
        if(!$user_task_info['task_name']){
            $user_task_info['price'] = $task_info['price'];
            $user_task_info['reward1'] = $task_info['reward1'];
            $user_task_info['reward2_upgrade_vip'] = $task_info['reward2_upgrade_vip'];
        }

        $user_task_info['img'] = $task_info['img'];
        $user_task_info['description'] = $task_info['description'];
        $user_task_info['start_time'] = $task_info['start_time'];
        $user_task_info['end_time'] = $task_info['end_time'];
        $user_task_info['task_details_type'] = $task_info['task_details_type'];
        $user_task_info['task_details'] = htmlspecialchars_decode($task_info['task_details']);

        unset($user_task_info['task_name']);

        return $user_task_info;
    }

    public function finishUserTask($uid,$user_task_id)
    {
        $user_task_info = DI()->notorm->yh_user_task->where('uid=? and id=?',intval($uid),intval($user_task_id))->fetchOne();
        if (!$user_task_info){
            return array('code' => 3001, 'msg' => '', 'info' => array()); // 任务不存在
        }
        $task_info = DI()->notorm->yh_task->where('id=?',intval($user_task_info['task_id']))->fetchOne();
        $tenant_id = getTenantId();

        if($user_task_info['status']!=5){
            return array('code' => 3000, 'msg' => '', 'info' => array()); // 状态不是待提交
        }

        if($task_info['end_time'] < time()){
            $data = array(
                'status' => 4,
                'mtime' => time(),
                'remark' => '任务已超时',
                'submit_time' => time(),
            );
            DI()->notorm->yh_user_task->where('uid=? and id=? and status=?',intval($uid),intval($user_task_id),1)->update($data);
            return array('code' => 2012, 'msg' => '', 'info' => array()); // 任务已超时
        }
        $price = 0;
        $commission_reward = 0;
        if($user_task_info['experience_shop']=='1' && $task_info['is_manual_check'] == '0'){ //  experience_shop：是否体验商城，is_manual_check: 人工审核：0否，1是
            $user_task_data = array('status'=>3 ,'remark' => '自动审核','mtime'=>time(), 'submit_time'=>time());
            $res = DI()->notorm->yh_user_task->where('uid=? and id=? and status=?',intval($uid),intval($user_task_id),5)->update($user_task_data);
            if($res!==1){
                return array('code' => 1007, 'msg' => '', 'info' => array());
            }
        }else  if($user_task_info['experience_shop']=='1' && $task_info['is_manual_check'] == '1'){ //  experience_shop：是否体验商城，is_manual_check: 人工审核：0否，1是
            $user_task_data = array('status'=>2 ,'remark' => '','mtime'=>time(), 'submit_time'=>time());
            $res = DI()->notorm->yh_user_task->where('uid=? and id=? and status=?',intval($uid),intval($user_task_id),5)->update($user_task_data);
            if($res!==1){
                return array('code' => 1007, 'msg' => '', 'info' => array());
            }
        }else if($user_task_info['experience_shop']=='0' && $task_info['is_manual_check'] == '1'){ //  experience_shop：是否体验商城，is_manual_check: 人工审核：0否，1是
            //该任务只能完成一次
            if($task_info['only_one']  == 0 ){
                $user_task_data = array('status'=>2 ,'remark' => '手动审核，仅能完成一次','mtime'=>time(), 'submit_time'=>time());
            }else{
                //该任务每天可以完成一次
                $start = mktime(0,0,0,date('m'),date('d'),date('Y'));
                $end = mktime(23,59,59,date('m'),date('d'),date('Y'));
                $user_task_data = array('status'=>1, 'remark'=>'手动审核_该任务每天都能完成一次', 'mtime'=>time(), 'submit_time'=>time(),'today_ischeck'=>0);
                $user_task_already = DI()->notorm->yh_user_task->where('uid=? and id=? and submit_time  BETWEEN ? and ? ',intval($uid),intval($user_task_id),$start,$end)->fetchOne();
                if($user_task_already){
                    return array('code' => 2013, 'msg' => '', 'info' => array()); // 每天只能完成一次
                }
            }

            $res = DI()->notorm->yh_user_task->where('uid=? and id=? and status=?',intval($uid),intval($user_task_id),5)->update($user_task_data);
            if($res!==1){
                return array('code' => 1007, 'msg' => '', 'info' => array());
            }
            if ($user_task_info['price'] > 0){
                $user_info = getUserInfo($uid);
                if($user_info['coin']*100 < $user_task_info['price']*100){ // 余额不足
                    return array('code' => 2006, 'msg' => '', 'info' => array('price'=>floatval($user_task_info['price'])));
                }
                // 扣除任务价格
                DI()->notorm->users->where('id=?',intval($uid))->update( array('coin' => new NotORM_Literal("coin - {$user_task_info['price']}") ));
                $price = $user_task_info['price'];
                $users_coinrecord0 = array(
                    "type"=>'expend',
                    "action"=>'taskprice',
                    "uid"=>intval($uid),
                    "totalcoin"=>floatval($user_task_info['price']),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $coinrecordModel = new Model_Coinrecord();
                $coinrecordModel->addCoinrecord($users_coinrecord0);

                $commission_reward = bcmul($user_task_info['price'],$user_task_info['commission_rate'],2);

                delUserInfoCache($uid);
            }
        }else if($user_task_info['experience_shop']=='0' && $task_info['is_manual_check'] == '0'){ //  experience_shop：是否体验商城，is_manual_check: 人工审核：0否，1是
            $user_info = getUserInfo($uid);

            if($user_info['nowithdrawable_coin']*100 < $user_task_info['reward1']*100){ // 不可提现金额不足
                return array('code' => 2007, 'msg' => '', 'info' => array());
            }

            if($user_info['coin']*100 < $user_task_info['price']*100){ // 余额不足
                return array('code' => 2006, 'msg' => '', 'info' => array('price'=>floatval($user_task_info['price'])));
            }
            //该任务只能完成一次
            if($task_info['only_one']  == 0 ){
                $user_task_data = array('status'=>3 , 'remark'=>'自动审核', 'mtime'=>time(), 'submit_time'=>time());
            }else{
                //该任务每天可以完成一次
                $start = mktime(0,0,0,date('m'),date('d'),date('Y'));
                $end = mktime(23,59,59,date('m'),date('d'),date('Y'));
                $user_task_data = array('status'=>1 , 'remark'=>'自动审核_该任务每天都能完成一次', 'mtime'=>time(), 'submit_time'=>time(),'today_ischeck'=>1);
                $user_task_already = DI()->notorm->yh_user_task->where('uid=? and id=? and submit_time  BETWEEN ? and ? ',intval($uid),intval($user_task_id),$start,$end)->fetchOne();
                if($user_task_already){
                    return array('code' => 2013, 'msg' => '', 'info' => array()); // 每天只能完成一次
                }
            }

            $res = DI()->notorm->yh_user_task->where('uid=? and id=? and status=?',intval($uid),intval($user_task_id),5)->update($user_task_data);
            if($res!==1){
                return array('code' => 1007, 'msg' => '', 'info' => array());
            }

            if ($user_task_info['price'] > 0){
                // 扣除任务价格
                DI()->notorm->users->where('id=?',intval($uid))->update( array('coin' => new NotORM_Literal("coin - {$user_task_info['price']}") ));
                $price = $user_task_info['price'];
                $users_coinrecord0 = array(
                    "type"=>'expend',
                    "action"=>'taskprice',
                    "uid"=>intval($uid),
                    "totalcoin"=>floatval($user_task_info['price']),
                    "addtime"=>time(),
                    'tenant_id' =>getTenantId(),
                );
                $coinrecordModel = new Model_Coinrecord();
                $coinrecordModel->addCoinrecord($users_coinrecord0);
            }

            $commission_reward = bcmul($user_task_info['price'],$user_task_info['commission_rate'],2);

            // 额外奖励（分类 佣金比例）
            if($commission_reward>0){
                DI()->notorm->users->where('id=?',intval($uid))->update( array('coin' => new NotORM_Literal("coin + {$commission_reward}") ));
                $users_coinrecord2 = array(
                    'type' => 'income',
                    'uid' => intval($uid),
                    'addtime' => time(),
                    'tenant_id' => intval($tenant_id),
                    'action' => 'taskcommission',
                    'totalcoin' => floatval($commission_reward),
                );
                $coinrecordModel = new Model_Coinrecord();
                $coinrecordModel->addCoinrecord($users_coinrecord2); // 可提现金币变动记录
            }
            delUserInfoCache($uid);

            $user_info = getUserInfo($uid);
            $reward1 = $user_task_info['reward1'];

            $user_vip = DI()->notorm->users_vip->where('uid=? and endtime>?',intval($uid),time())->order('grade desc')->fetchOne();
            $vip_id = isset($user_vip['vip_id']) ? $user_vip['vip_id'] : 0;

            $reward1_number = intval($user_task_info['reward1_number']);

            if($reward1>0){  // 完成奖励1 大于 0 才执行
                addAward(intval($uid),1,$reward1,1,'新手任务-'.$user_task_info['task_name']);
                DI()->notorm->users->where('id=?',intval($uid))->update(
                    array(
                        'coin' => new NotORM_Literal("coin + ".$reward1),
                        //'nowithdrawable_coin' => new NotORM_Literal("nowithdrawable_coin - {$reward1}"),
                    )
                );
                delUserInfoCache($uid);
                // 任务奖励
                $users_coinrecord1 = array(
                    'type' => 'income',
                    'uid' => intval($uid),
                    'addtime' => time(),
                    'tenant_id' => intval($tenant_id),
                    'action' => 'task',
                    'totalcoin' => floatval($reward1),
                );
                $coinrecordModel = new Model_Coinrecord();
                $coinrecordModel->addCoinrecord($users_coinrecord1); // 可提现金币变动记录

                $task_rewardlog_data = array(
                    'uid' => intval($uid),
                    'vip_id' => intval($vip_id),
                    'type' => intval($user_task_info['task_type']),
                    'task_id' => intval($task_info['id']),
                    'task_name' => $user_task_info['task_name'],
                    'user_task_id' => intval($user_task_info['id']),
                    'reward_type' => 1, // 1 奖励1, 2 奖励2
                    'reward1' => floatval($user_task_info['reward1']),
                    'reward2_upgrade_vip' => intval($user_task_info['reward2_upgrade_vip']),
                    'start_time' => intval($task_info['start_time']),
                    'end_time' => intval($task_info['end_time']),
                    'reward_start_amount' => floatval($user_info['coin']),
                    'reward_result' => '奖励金额：'.$reward1,
                    'reward_end_amount' => bcadd($user_info['coin'],$reward1,2),
                    'reward_end_vip' => intval($vip_id),
                    'giveout_type' => 0, // 发放类型： 0系统自动发放，1人工审核
                    'status' => 1,
                    'tenant_id' => intval($tenant_id),
                    'mtime' => time(),
                );
                DI()->notorm->task_rewardlog->insert($task_rewardlog_data); // 奖励明细记录
            }

            if($user_task_info['reward2_upgrade_vip']=='1'){ // 升级VIP等级
                $next_vip_info = $this->get_next_vip($vip_id);
                $exist_users_vip = DI()->notorm->users_vip->where(['uid'=>intval($uid),'grade'=>intval($next_vip_info['orderno'])])->fetchOne();

                // 如果购买同等级的就延长时间，购买搞等级的，就把低等级到期的时间延长
                if($vip_id != 0 && $user_vip['grade'] == $next_vip_info['orderno']){
                    $endtime = strtotime ("+".$next_vip_info['length']." month", $user_vip['endtime']);
                }else{
                    $endtime = strtotime ("+".$next_vip_info['length']." month", time());
                }
                if ($next_vip_info['give_data']){
                    $endtime = strtotime ("+{$next_vip_info['give_data']} day", $endtime);
                }

                if($vip_id == 0 || ($vip_id != 0 && $user_vip['grade'] != $next_vip_info['orderno'])){
                    if(isset($exist_users_vip['id'])){
                        DI()->notorm->users_vip->where(['id'=>intval($exist_users_vip['id'])])->update([
                            'addtime' => time(),
                            'endtime' => intval($endtime),
                            'vip_id' => intval($next_vip_info['id']),
                        ]);
                    }else{
                        $users_vip_data = array(
                            'uid' => intval($uid),
                            'addtime' => time(),
                            'endtime' => intval($endtime),
                            'tenant_id' => intval($user_info['tenant_id']),
                            'vip_id' => intval($next_vip_info['id']),
                            'grade' => intval($next_vip_info['orderno']),
                        );
                        DI()->notorm->users_vip->insert($users_vip_data);
                    }
                }
                if($vip_id != 0 && $user_vip['grade'] == $next_vip_info['orderno']){
                    DI()->notorm->users_vip->where(['id'=>intval($user_vip['id'])])->update([
                        'addtime' => time(),
                        'endtime' => intval($endtime),
                        'vip_id' => intval($next_vip_info['id']),
                    ]);
                }

                // 小于升级的等级，延长时间
                $nowTime = time();
                $historyVip =  DI()->notorm->users_vip->where("uid = '{$uid}' and endtime > '{$nowTime}'")->order('grade desc')->fetchAll(); // 获取用户全部没过期的vip历史
                foreach ($historyVip as $key =>  $val){
                    if ($key > 0){  // 刚升级的用户等级不参与计算
                        $val['endtime'] = strtotime ("+".$next_vip_info['length']." month", $val['endtime']);
                        if ($next_vip_info['give_data']){
                            $val['endtime'] = strtotime ("+{$next_vip_info['give_data']} day",  $val['endtime']);
                        }
                        DI()->notorm->users_vip->where("id = '{$val['id']}'")->update(array('endtime'=>$val['endtime']));
                    }
                }

                $task_rewardlog_data1 = array(
                    'uid' => intval($uid),
                    'vip_id' => intval($vip_id),
                    'type' => intval($user_task_info['task_type']),
                    'task_id' => intval($task_info['id']),
                    'task_name' => $user_task_info['task_name'],
                    'user_task_id' => intval($user_task_info['id']),
                    'reward_type' => 2, // 1 奖励1, 2 奖励2
                    'reward1' => floatval($user_task_info['reward1']),
                    'reward2_upgrade_vip' => intval($user_task_info['reward2_upgrade_vip']),
                    'start_time' => intval($task_info['start_time']),
                    'end_time' => intval($task_info['end_time']),
                    'reward_start_amount' => bcadd($user_info['coin'],$reward1,2),
                    'reward_result' => 'VIP等级升级',
                    'reward_end_amount' => bcadd($user_info['coin'],$reward1,2),
                    'reward_end_vip' => intval($next_vip_info['id']),
                    'giveout_type' => 0, // 发放类型： 0系统自动发放，1人工审核
                    'status' => 1,
                    'tenant_id' => intval($tenant_id),
                    'mtime' => time(),
                );
                DI()->notorm->task_rewardlog->insert($task_rewardlog_data1); // 奖励明细记录
                $this->agent_rabate($user_info,$user_task_info['price']); // 代理返佣
            }

            if($reward1_number>0){  // 完成奖励3 大于 0 才执行
                DI()->notorm->users->where('id=?',intval($uid))->update(
                    array(
                        'turntable_times' => new NotORM_Literal("turntable_times + ".$reward1_number),
                    )
                );
                delUserInfoCache($uid);

                addAward(intval($uid),1,$reward1_number,3,'新手任务-'.$user_task_info['task_name']);

                // 任务记录
                $task_rewardlog_data = array(
                    'uid' => intval($uid),
                    'vip_id' => intval($vip_id),
                    'type' => intval($user_task_info['task_type']),
                    'task_id' => intval($task_info['id']),
                    'task_name' => $user_task_info['task_name'],
                    'user_task_id' => intval($user_task_info['id']),
                    'reward_type' => 3, // 1 奖励1, 2 奖励2
                    'reward1' => floatval($user_task_info['reward1']),
                    'reward2_upgrade_vip' => intval($user_task_info['reward2_upgrade_vip']),
                    'start_time' => intval($task_info['start_time']),
                    'end_time' => intval($task_info['end_time']),
                    'reward_start_amount' => floatval($user_info['coin']),
                    'reward_result' => '奖励转盘次数：'.$reward1_number,
                    'reward_end_amount' => bcadd($user_info['coin'],$reward1,2),
                    'reward_end_vip' => intval($vip_id),
                    'reward1_number' => $reward1_number,
                    'reward_end_number' => bcadd($user_info['turntable_times'],$reward1_number),
                    'giveout_type' => 0, // 发放类型： 0系统自动发放，1人工审核
                    'status' => 1,
                    'tenant_id' => intval($tenant_id),
                    'mtime' => time(),
                );
                DI()->notorm->yh_task_rewardlog->insert($task_rewardlog_data); // 奖励明细记录
            }
        }

        $user_task_info = DI()->notorm->yh_user_task->where('uid=? and id=?',intval($uid),intval($user_task_id))->fetchOne();

        return array('code' => 0, 'msg' => '', 'info' => array('status'=>intval($user_task_info['status']),'price'=>$price,'commission'=>floatval($commission_reward)));
    }

    public function get_next_vip($vip_id){
        if($vip_id==0){
            return DI()->notorm->vip->where(['orderno'=>1])->order('length asc')->limit(1)->fetch();
        }
        $curr_vip_info = DI()->notorm->vip->where(['id'=>intval($vip_id)])->fetchOne();
        if(!$curr_vip_info){
            return DI()->notorm->vip->where(['orderno'=>1])->order('length asc')->limit(1)->fetch();
        }
        $vip_list = DI()->notorm->vip->order('length asc')->fetchAll();
        $vip = array();
        foreach ($vip_list as $key=>$val){
            if(empty($vip) && $curr_vip_info['orderno']==$val['orderno'] && $curr_vip_info['length']<$val['length']){
                $vip = $val;
            }
        }
        if(empty($vip)){
            switch ($curr_vip_info['orderno']){
                case '1':
                    $next_vip_orderno = '2';
                    break;
                case '2':
                    $next_vip_orderno = '3';
                    break;
                case '3':
                    $next_vip_orderno = '4';
                    break;
                case '4':
                    $next_vip_orderno = '6';
                    break;
                case '6':
                    $next_vip_orderno = '6';
                    break;
                default :
                    $next_vip_orderno = '';
            }
            if($next_vip_orderno==''){
                $vip = $curr_vip_info;
            }else{
                $vip = DI()->notorm->vip->where(['orderno'=>$next_vip_orderno])->order('length asc')->limit(1)->fetch();
                $vip = $vip ? $vip : $curr_vip_info;
            }
        }
        return $vip;
    }

    /*
    * 代理返佣
    * */
    public function agent_rabate($user_info,$price=0){
        $agentinfo = getAgentInfo($user_info['id']);
        $RebateConf = getAgentRebateConf($user_info['tenant_id']);
        if(!$RebateConf){
            return ;
        }
        $arr = array(   array('uid'=>$agentinfo['one_uid'],'profit'=>$RebateConf['one_profit']),
            array('uid'=>$agentinfo['two_uid'],'profit'=>$RebateConf['two_profit']),
            array('uid'=>$agentinfo['three_uid'],'profit'=>$RebateConf['three_profit']),
            array('uid'=>$agentinfo['four_uid'],'profit'=>$RebateConf['four_profit']),
            array('uid'=>$agentinfo['five_uid'],'profit'=>$RebateConf['five_profit']) );
        foreach ($arr as $key=>$val){
            $rebate = bcmul($price,$val['profit']/100,2);
            if($val['uid'] && $val['profit'] > 0 && $rebate > 0){
                $u_info = getUserInfo($val['uid']);
                DI()->notorm->users->where('id=?',intval($u_info['id']))->update(
                    array(  'coin' => new NotORM_Literal("coin + ".$rebate))
                );
                delUserInfoCache($val['uid']);
                $users_coinrecord1 = array(
                    'type' => 'move',
                    'uid' => intval($u_info['id']),
                    'addtime' => time(),
                    'tenant_id' => intval($u_info['tenant_id']),
                    'action' => 'agent_rebate',
                    'totalcoin' => floatval($rebate),
                );
                $coinrecordModel = new Model_Coinrecord();
                $coinrecordModel->addCoinrecord($users_coinrecord1); // 可提现金币变动记录
            }
        }
        return ;
    }


    public function getTaskRewardLog($uid,$type,$p)
    {
        $p = $p > 1 ? $p : 1;
        $limit = 2;
        $pstart = ($p-1)*$limit;
        $list = DI()->notorm->task_rewardlog
            ->select("id as reward_log_id,task_id,task_name,type,reward_type,reward1,reward2_upgrade_vip,reward_result,reward_end_vip,mtime")
            ->where('uid=? and type=?',intval($uid),intval($type))
            ->order('mtime desc,id desc')
            ->limit($pstart,$limit)
            ->fetchAll();

        $data_list = array();
        foreach ($list as $key=>$val){
            $task_info = DI()->notorm->task->where('id=?',$val['task_id'])->fetchOne();
            if(!$task_info){
                continue;
            }
            $list[$key]['task_name'] = $task_info['name'];
            if(!$val['task_name']){
                $list[$key]['reward1'] = $task_info['reward1'];
                $list[$key]['reward2_upgrade_vip'] = $task_info['reward2_upgrade_vip'];
            }
            unset($list[$key]['task_id']);

            array_push($data_list,$list[$key]);
        }

        return $data_list;
    }

    public function getTaskRewardLogInfo($uid,$reward_log_id)
    {
        $reward_log_info = DI()->notorm->task_rewardlog
            ->select("task_id,user_task_id,task_name,vip_id as reward_start_vip,reward_end_vip,type,reward_type,reward1,reward2_upgrade_vip,reward_start_amount,reward_result,reward_end_amount,giveout_type,mtime")
            ->where('uid=? and id=?',intval($uid),intval($reward_log_id))
            ->fetchOne();

        $task_info = DI()->notorm->task->where('id=?',intval($reward_log_info['task_id']))->fetchOne();

        $reward_log_info['name'] = $task_info['name'];
        if(!$reward_log_info['task_name']){
            $reward_log_info['reward1'] = $task_info['reward1'];
            $reward_log_info['reward2_upgrade_vip'] = $task_info['reward2_upgrade_vip'];
        }

        $vip_start_info = DI()->notorm->vip->where('id=?',intval($reward_log_info['reward_start_vip']))->fetchOne();
        $reward_log_info['reward_start_vip'] = $vip_start_info['name'].'（'.$vip_start_info['length'].'个月）';

        $vip_end_info = DI()->notorm->vip->where('id=?',intval($reward_log_info['reward_end_vip']))->fetchOne();
        $reward_log_info['reward_end_vip'] = $vip_end_info['name'].'（'.$vip_end_info['length'].'个月）';

        unset($reward_log_info['task_name']);

        return $reward_log_info;
    }


    //-----------------------

}