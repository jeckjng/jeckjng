<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2022/4/20
 * Time: 23:00
 */
class Model_Bar extends PhalApi_Model_NotORM {
    public function barList($game_tenant_id,$type,$uid,$p) {
        $configInfo = getConfigPub();
       if ( $configInfo['is_open_seeking_slice'] == 0){
           return   1000;
       }
        $where = '';
        if($p<1){
            $p=1;
        }
        $pnums=20;
        $start=($p-1)*$pnums;
        $tenantId = getTenantId();
        if ($type == 3 ){//3我的普通贴子
            $where = " uid  = {$uid} and tenant_id = {$tenantId} and status =  2 and  type = 1 ";

        }else if ($type == 4){//4我的求片
            $where = " uid  = {$uid} and tenant_id = {$tenantId} and status =  2 and  type = 2 ";
        }else if ($type == 1){ //1普通贴子
            $where = "  tenant_id = {$tenantId} and status =  2  and type = 1";
        }else if ($type ==2){ //2求片
            $where = "  tenant_id = {$tenantId} and status =  2  and type = 2";
        }
        else{//  审核中
            $where = " uid  = {$uid} and tenant_id = {$tenantId} and status =  1  ";
        }

        $list=DI()->notorm->bar
            ->where($where)
            ->select("*")
            ->order('endtime desc,id desc')
            ->limit($start,$pnums)
            ->fetchAll();
        if (empty($list)){
             return $list;
        }
        $paly_url = play_or_download_url(1);
        $ids = array_column($list, 'id');// 贴吧id
        $uids = array_column($list, 'uid');// 用户id
        $uids =implode(",",$uids);
        if ($uids){
            $users_attention = DI()->notorm->users_attention->where("touid in ({$uids}) and uid = {$uid}")->select('*')->fetchAll();
        }else{
            $users_attention =[];
        }

        $users_attentionUid =   array_column($users_attention, 'touid');// 用户id

        if ($uids){
            $userAvatar =  DI()->notorm->users->where("id in ({$uids})")->select('id,avatar,user_nicename')->fetchAll();
            $userAvatar = array_column($userAvatar,null, 'id');// 用户id
        }else{
            $userAvatar=[];
        }


        $likeList = [];
        if ($ids){
            $ids =implode(",",$ids);
            $likeList = DI()->notorm->bar_likes->where("bar_id in ({$ids}) and uid = {$uid}")->select('*')->fetchAll();
        }
        $barIds = array_column($likeList, 'bar_id');



        foreach ($list  as $key => $value){
            if ($value['status'] == 2 && $value['type'] ==2){
                $list[$key]['down_time'] = $value['endtime'] + $value['validtime']-time();
                if ( $list[$key]['down_time'] <0){
                    $list[$key]['down_time'] = 0;
                }
            }else{
                $list[$key]['down_time'] = 0;
            }
            if ($value[$paly_url['viode_table_field']]){
                $list[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value[$paly_url['viode_table_field']];
            }

            if ($value['img']){
                $imgs =explode(",",$value['img']);

                foreach ($imgs as $imgKey=>  $imgValue){
                    $imgs[$imgKey] =  $paly_url['url'].$imgValue;
                }
            }else{
                $imgs = [];
            }

            $list[$key]['img']= $imgs;
            if ($value['video_img']){
                $list[$key]['video_img'] = $paly_url['url'] . $value['video_img'];
            }
            $list[$key]['user_avatar'] = $userAvatar[$value['uid']]['avatar'];
            $list[$key]['user_nicename'] = $userAvatar[$value['uid']]['user_nicename'];
            if (in_array($value['id'],$barIds)){
                $list[$key]['is_likes'] = 1;
            }else{
                $list[$key]['is_likes'] = 0;
            }
            if (in_array($value['uid'],$users_attentionUid)){
                $list[$key]['is_attention'] = 1;
            }else{
                $list[$key]['is_attention'] = 0;
            }
        }
        return $list;
    }
    public  function  barInfo($game_tenant_id,$uid,$bar_id){
         $list = DI()->notorm->bar
             ->where(" id = {$bar_id} ")
             ->select("*")
             ->fetchOne();
        $paly_url = play_or_download_url(1);
        if ($list[$paly_url['viode_table_field']]){
            $list['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $list[$paly_url['viode_table_field']];
        }
        if ($list['video_img']){
            $list['video_img'] = $paly_url['url'] . $list['video_img'];
        }
        if ($list['img']){
            $imgs =explode(",",$list['img']);
            $newImgs = [];
            foreach ($imgs as $value){
                $newImgs[] =  $paly_url['url'].$value;
            }
            $list['img']  = $newImgs;
        }else{
            $list['img']  = [];
        }
        if ($list['status'] == 2 && $list['type'] ==2){
            $list['down_time'] = $list['endtime'] + $list['validtime']-time();
            if ( $list['down_time'] <0){
                $list['down_time'] = 0;
            }
        }else{
            $list['down_time'] = 0;
        }
        $user =  DI()->notorm->users->where("id = {$list['uid']}")->select('avatar,user_nicename')->fetchOne();
        $list['user_avatar'] = $user['avatar'];
        $list['user_nicename'] = $user['user_nicename'];
        $list['addtime'] = date('m-d',$list['addtime'])  ;//评论的用户昵称
        $users_attention = DI()->notorm->users_attention->where("touid in ({$list['uid']}) and uid = {$uid}")->select('*')->fetchOne();
        $count = DI()->notorm->bar_comment->where(" bar_id = {$bar_id} and is_delete = 0 ")->count();
        $list['count'] = $count;
        if ($users_attention){
            $list['is_attention'] = 1;
        }else{
            $list['is_attention'] = 0;
        }
         return $list;
     }

    /**
     *  贴子回复
     * @param $game_tenant_id
     * @param $bar_id
     * @param $uid
     * @param $p
     * @return mixed
     */
     public  function  commentList($game_tenant_id,$bar_id,$uid,$type,$p){
         if($p<1){
             $p=1;
         }
         $pnums = 20;
         $start=($p-1)*$pnums;
         $list = DI()->notorm->bar_comment
             ->where("bar_id  ={$bar_id} and comment_id = 0 and is_delete = 0 ")
             ->select("*")
             ->order('addtime desc')
             ->limit($start,$pnums)
             ->fetchAll();

         if ($type == 1){ // 普通贴子，需要看评论的下级评论，目前展示3个
             $commentIdArray =  [];
             foreach ($list as $listKey =>   $listValue){  //  为啥需要循环获取下级评论，因为不循环的话，就没法下级评论的分页
                 $comment_desc = DI()->notorm->bar_comment
                     ->where("comment_id  = {$listValue['id']}  and is_delete = 0 ")
                     ->select("*")
                     ->order('addtime desc')
                     ->limit(0,3)
                     ->fetchAll();
                // var_dump(  $list[$listKey]['comment_desc'] );exit;
                 foreach ( $comment_desc as $commentDescKey  =>$commentDescValue ){
                     $uidArray = [];
                     $uidArray[] = $commentDescValue['parent_reply_uid'];// 被回复的用户id
                     $uidArray[] = $commentDescValue['publish_uid']; // 评论者用户id
                     $uidString =implode(",",$uidArray);
                     $userInfo = DI()->notorm->users
                         ->select('user_login,id,user_nicename,avatar')
                         ->where("id in ({$uidString})")
                         ->fetchAll();
                     $userInfoNew = array_column($userInfo,null,'id');
                     $comment_desc[$commentDescKey]['parent_reply_user_nicename'] = $userInfoNew[$commentDescValue['parent_reply_uid']]['user_nicename'] ;//被回复的用户昵称
                     $comment_desc[$commentDescKey]['publish_user_nicename'] = $userInfoNew[$commentDescValue['publish_uid']]['user_nicename'] ;//评论者用户昵称
                     $comment_desc[$commentDescKey]['parent_reply_avatar'] = $userInfoNew[$commentDescValue['parent_reply_uid']]['avatar'] ;//被回复的用户昵称
                     $comment_desc[$commentDescKey]['publish_user_avatar'] = $userInfoNew[$commentDescValue['publish_uid']]['avatar'] ;//评论者用户昵称
                     $comment_desc[$commentDescKey]['addtime'] = get_date_time($commentDescValue['addtime']) ;//评论者用户昵称

                 }
                 $list[$listKey]['comment_desc'] = $comment_desc;
                 $commentIdArray[] = $listValue['publish_uid'];// 评论者用户id
                 $list[$listKey]['addtime'] = get_date_time($listValue['addtime']) ;
             }

             $commentIdString = implode(',',$commentIdArray);

             if ($commentIdString){
                 $userPublishInfo = DI()->notorm->users
                     ->select('id,user_nicename,avatar')
                     ->where("id in ({$commentIdString})")
                     ->fetchAll();
                 $userPublishInfoNew = array_column($userPublishInfo,null,'id');
                 foreach ($list as $keyListKey => $newListValue){ // 获取评论者用户昵称
                     $list[$keyListKey]['publish_user_nicename'] = $userPublishInfoNew[$newListValue['publish_uid']]['user_nicename'] ;//评论的用户昵称
                     $list[$keyListKey]['publish_user_avatar'] = $userPublishInfoNew[$newListValue['publish_uid']]['avatar'] ;//评论的用户昵称

                 }

             }


         }else{
             $shortIdArray =  [];
             $longIdArray =  [];
             foreach ($list as $listKey =>   $listValue){
                 if ($listValue['video_type'] ==1 ){
                     $shortIdArray[] =  $listValue['video_id'];
                 }else{
                     $longIdArray[] =  $listValue['video_id'];
                 }
                 $commentIdArray[] = $listValue['publish_uid'];// 评论者用户id
             }
             $commentIdString = implode(',',$commentIdArray);
             if ($commentIdString) {
                 $userPublishInfo = DI()->notorm->users
                     ->select('id,user_nicename,avatar')
                     ->where("id in ({$commentIdString})")
                     ->fetchAll();
                 $userPublishInfoNew = array_column($userPublishInfo, null, 'id');
             }
             $shortIdString = '';
             if ($shortIdArray) {
                 $shortIdString = implode(',', $shortIdArray);
             }
             $longIdString = '';
             if ($longIdArray){
                 $longIdString = implode(',',$longIdArray);
             }
             $paly_url = play_or_download_url(1);// 视频播放地址
             $shortVideoNewArray = [];
             $longVideoNewArray = [];
             if ($shortIdString){
                 $shortVideo = DI()->notorm->video   ->where("id in ({$shortIdString})")->select("id,title,likes,thumb,watchtimes,{$paly_url['viode_table_field']}")->fetchAll();
                 foreach ($shortVideo as $key =>  $shortValue){
                     $shortVideo[$key]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $shortValue[$paly_url['viode_table_field']];
                     $shortVideo[$key]['thumb'] = $paly_url['url'] . $shortValue['thumb'];
                 }
                 $shortVideoNewArray = array_column($shortVideo,null,'id');
             }
             if ($longIdArray) {
                 $longVideo = DI()->notorm->video_long->where("id in ({$longIdString})")->select("id,thumb,title,likes,watchtimes,{$paly_url['viode_table_field']}")->fetchAll();
                ;
                 foreach ($longVideo as $longKey => $longValue) {
                     $longVideo[$longKey]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $longValue[$paly_url['viode_table_field']];
                     $longVideo[$longKey]['thumb'] = $paly_url['url'] . $longValue['thumb'];
                 }
                 $longVideoNewArray = array_column($longVideo,null,'id');
             }

             $barInfo = DI()->notorm->bar
                 ->where("id  ={$bar_id}  ")
                 ->select("*")
                 ->fetchOne();


             foreach ($list as $listKey =>   $listValue) {
                 if ($listValue['video_type'] ==1   ){
                     $list[$listKey]['href'] =  $shortVideoNewArray[$listValue['video_id']]['href'];
                     $list[$listKey]['thumb'] =  $shortVideoNewArray[$listValue['video_id']]['thumb'];
                     $list[$listKey]['video_likes'] =  $shortVideoNewArray[$listValue['video_id']]['likes'];
                     $list[$listKey]['video_title'] =  $shortVideoNewArray[$listValue['video_id']]['title'];
                     $list[$listKey]['video_watchtimes'] =  $shortVideoNewArray[$listValue['video_id']]['watchtimes'];
                 }else{
                     $list[$listKey]['href'] =  $longVideoNewArray[$listValue['video_id']]['href'];
                     $list[$listKey]['thumb'] =  $longVideoNewArray[$listValue['video_id']]['thumb'];
                     $list[$listKey]['video_likes'] =  $longVideoNewArray[$listValue['video_id']]['likes'];
                     $list[$listKey]['video_title'] =  $longVideoNewArray[$listValue['video_id']]['title'];
                     $list[$listKey]['video_watchtimes'] =  $longVideoNewArray[$listValue['video_id']]['watchtimes'];
                 }
                 $list[$listKey]['publish_user_nicename'] = $userPublishInfoNew[$listValue['publish_uid']]['user_nicename'] ;//评论的用户昵称
                 $list[$listKey]['publish_user_avatar'] = $userPublishInfoNew[$listValue['publish_uid']]['avatar'] ;//评论的用户昵称
                 $list[$listKey]['addtime'] = get_date_time($listValue['addtime'])  ;//评论的用户昵称
                 if ($barInfo['optimum_comment_id']>0){
                     $list[$listKey]['is_set'] = 0;
                 }else{
                     $list[$listKey]['is_set'] = 1;
                 }
                 $list[$listKey]['reward_money'] = $barInfo['reward_money'];
             }

         }

        // $commentIdString = explode(',',$commentIdArray);
        // $commentIs =  [];
         return $list;
     }

     public function  commentDesc($game_tenant_id,$comment_id,$uid,$p){
         if($p<1){
             $p=1;
         }
         $pnums = 20;
         $start=($p-1)*$pnums+3;
         $list = DI()->notorm->bar_comment
             ->where("comment_id  ={$comment_id} and is_delete = 0 ")
             ->select("*")
             ->order('addtime desc')
             ->limit($start,$pnums)
             ->fetchAll();
         if (empty($list)){
             return $list;
         }
         $commentIdArray =[];
         foreach ( $list as $value){
             $commentIdArray[] = $value['publish_uid'];
             $commentIdArray[] = $value['parent_reply_uid'];
         }
         $commentUidString = implode(',',$commentIdArray);
         $userPublishInfo = DI()->notorm->users
             ->select('id,user_nicename,avatar')
             ->where("id in ({$commentUidString})")
             ->fetchAll();

         $userPublishInfoNew = array_column($userPublishInfo,null,'id');
         foreach ( $list as $key =>  $value){
             $list[$key]['parent_reply_user_nicename'] = $userPublishInfoNew[$value['parent_reply_uid']]['user_nicename'];
             $list[$key]['publish_user_nicename'] = $userPublishInfoNew[$value['publish_uid']]['user_nicename'];
             $list[$key]['parent_reply_user_avatar'] = $userPublishInfoNew[$value['parent_reply_uid']]['avatar'];
             $list[$key]['publish_user_avatar'] = $userPublishInfoNew[$value['publish_uid']]['avatar'];
             $list[$key]['addtime'] = get_date_time($value['addtime'])  ;//评论的用户昵称
         }
         return $list;
     }

     public  function postBar($game_tenant_id,$desc,$uid,$img,$fileStoreKey,$video_img,$type,$reward_money){
         $configInfo = getConfigPub();
         if ( $configInfo['is_open_seeking_slice'] == 0){
             return   ['code' =>1000];
         }
         $userModel = new Model_User();
         $userInfo = $userModel->getUserInfoWithIdAndTid($uid);

         if ($type == 2){ // 求片
             if ($userInfo['is_allow_seeking_slice'] == 0){ // 个人是否允许求片
                 return ['code' =>1002];
             }
             if ($userInfo['coin'] < $reward_money){ // 发片余额不足
                 return ['code' =>1001];
             }

             if ($reward_money < $configInfo['seeking_slice_bonus_min'] || $reward_money > $configInfo['seeking_slice_bonus_max'] ){
                 return ['code'=> 1005,'info'=> '悬赏金设置范围为'.$configInfo['seeking_slice_bonus_min'].'-'.$configInfo['seeking_slice_bonus_max']];
             }
             if ($configInfo['seeking_slice_strategy'] ==0){ // 后台配置关闭
                 return ['code' =>1000];
             }elseif ($configInfo['seeking_slice_strategy'] ==2){ // 按vip权限控制
                 $userLimit  = barNumLimited($uid);

                 $jurisdiction_id  = explode(',',$userLimit['jurisdiction_id']);
                 if (!in_array('55',$jurisdiction_id)) { //  没有权限
                     return ['code' =>1003];
                 }
                 $userBarCount =  DI()->notorm->bar->where("uid = {$uid} and type = '2' and  status in (1,2)")->count();
                 if ($userBarCount >=$userLimit['bar_slice_number'] ){ //  求片数量已经最大
                     return ['code' =>1004];
                 }
             }
             $validTime  = $configInfo['seeking_slice_effective_time'];
         }else{ // 发帖
             if ($userInfo['is_allow_post'] == 0){// 个人是否允许发帖
                 return ['code' =>1002];
             }
             if ($configInfo['posting_strategy'] ==0){// 后台配置关闭
                 return   ['code' =>1000];
             }elseif ($configInfo['posting_strategy'] ==2){
                 $userLimit  = barNumLimited($uid);
                 $jurisdiction_id  = explode(',',$userLimit['jurisdiction_id']);
                 if (!in_array('54',$jurisdiction_id)) { //  没有权限
                     return ['code' =>1003];
                 }
                 $userBarCount =  DI()->notorm->bar->where("uid = {$uid} and type = 1 and  status in (1,2)")->count();
                 if ($userBarCount >= $userLimit['bar_number'] ){ //  发帖数量已经最大
                     return ['code' =>1004];
                 }
             }
             $validTime  = 0 ;
         }

         $data = [
             'desc' => $desc,
             'uid' => $uid,
             'user_login' => $userInfo['user_login'],
             'user_type' => $userInfo['user_type'],
             'status' => 1,
             'img' => $img,
             'type' => $type,
             'addtime' => time(),
             'fileStoreKey' => $fileStoreKey,
             'video_img' => $video_img,
             'reward_money' => $reward_money,
             'tenant_id' => getTenantId(),
             'validtime' => $validTime,
         ];
         if ($fileStoreKey){
             $data['video_status'] = 0;
         }else{
             $data['video_status'] = 1;
         }
         $barId = DI()->notorm->bar->insert($data);
         if ($type == 2){
            $insert=array(
                 "type"=>'expend',
                 "action"=>'bar',
                 "uid"=>$uid,
                 'user_login' => $userInfo['user_login'],
                 "user_type" => intval($userInfo['user_type']),
                 "giftid"=>$barId,
                 "pre_balance" => floatval($userInfo['coin']),
                 "totalcoin"=>$reward_money,
                 "after_balance" => floatval(bcadd($userInfo['coin'], -abs($reward_money),4)),
                 "addtime"=>time(),
                 'tenant_id' =>getTenantId(),
            );
            $coinrecordModel = new Model_Coinrecord();
            $coinrecordModel->addCoinrecord($insert);
            DI()->notorm->users ->where("id= {$uid}" ) ->update( array('coin' => new NotORM_Literal("coin - {$reward_money}") ) );;
            delCache("userinfo_".$uid);
         }
         return  ['code' => 1];

     }

     public  function  postComment($game_tenant_id,$desc,$uid,$type,$id,$video_id,$video_type){
         $configInfo = getConfigPub();
         $usersInfo = DI()->notorm->users ->where("id = {$uid}")->select('coin,is_allow_comment,is_allow_push_slice,userlevel')->fetchOne();
         if (in_array($type,[1,2])){ // 评论
             if ($usersInfo['is_allow_comment'] == 0){ // 个人是否允许评论
                 return 1002;
             }
             if ($configInfo['comment_strategy'] ==0){ // 后台配置关闭
                 return   1000;
             }elseif ($configInfo['comment_strategy'] ==2){ // 按vip权限控制
                 $userLimit  = barNumLimited($uid);
                 $jurisdiction_id  = explode(',',$userLimit['jurisdiction_id']);
                 if (!in_array('56',$jurisdiction_id)) { //  没有权限
                     return 1003;
                 }
             }
             if ($type == 1){
                 $barInfo = DI()->notorm->bar ->where("id = {$id}")->select('uid')->fetchOne();
                 $data =[
                     'desc'=> $desc,
                     'parent_comment_id' => '0',//被回复者用户
                     'comment_id' => '0',
                     'bar_id' => $id,
                     'publish_uid' => $uid, //  发布评论者
                     'bar_uid' =>$barInfo['uid'], // 贴子发布者
                     'parent_reply_uid'=> $barInfo['uid'], // 被回复的用户
                     'reply_uid' => $uid ,  //  一级评论用户id
                     'addtime' => time(),
                     'type' => 1,
                     'status' => 1,
                 ];
                 $bar_id =  $id;
             }else{
                 $barComment = DI()->notorm->bar_comment ->where("id = {$id}")->select('*')->fetchOne();

                 $bar_id =  $barComment['bar_id'];
                 $data =[
                     'desc'=> $desc,
                     'parent_comment_id' => $barComment['id'], //id
                     'bar_id' => $barComment['bar_id'],
                     'publish_uid' => $uid, //  发布评论者
                     'bar_uid' =>$barComment['bar_uid'], // 贴子发布者
                     'parent_reply_uid'=> $barComment['publish_uid'], // 被回复的用户
                     'reply_uid' => $barComment['reply_uid'] ,  //  一级评论用户id
                     'addtime' => time(),
                     'type' => 1,
                     'status' => 1,
                 ];
                 if ($barComment['comment_id']){
                     $data['comment_id'] = $barComment['comment_id'];
                 }else{
                     $data['comment_id'] = $id;
                 }

             }
         }else{ // 推片


             if ($usersInfo['is_allow_push_slice'] == 0){// 个人是否允许
                 return 1002;
             }
             if ($configInfo['push_strategy'] == 0){// 后台配置关闭
                 return   1000;
             }elseif ($configInfo['seeking_slice_strategy'] ==2){

                 $userLimit  = barNumLimited($uid);

                 $jurisdiction_id  = explode(',',$userLimit['jurisdiction_id']);

                 if (!in_array('57',$jurisdiction_id)) { //  没有权限
                     return 1003;
                 }

             }
             $barInfo = DI()->notorm->bar ->where("id = {$id}")->select('uid')->fetchOne();
             if ($uid == $barInfo['uid'] ){
                 return 1005;
             }
             $barCommentInfo = DI()->notorm->bar_comment ->where("bar_id ={$id} and  video_type ={$video_type} and publish_uid = {$uid} and  video_id = {$video_id}")->fetchOne();
             if ($barCommentInfo){
                 return 1004;
             }
             $data =[
                 'desc'=> $desc,
                 'parent_comment_id' => 0,//被回复者用户
                 'comment_id' => 0,
                 'bar_id' => $id,
                 'publish_uid' => $uid, //  发布评论者
                 'bar_uid' =>$barInfo['uid'], // 贴子发布者
                 'parent_reply_uid'=> $barInfo['uid'], // 被回复的用户
                 'reply_uid' => $uid ,  //  一级评论用户id
                 'addtime' => time(),
                 'type' => 2,
                 'status' => 1,
                 'video_id' => $video_id,
                 'video_type'=> $video_type
             ];
             $bar_id =  $id;
         }
         DI()->notorm->bar ->where("id = {$bar_id}")	->update( array('comments_number' => new NotORM_Literal("comments_number + 1") ) );;
         DI()->notorm->bar_comment ->insert($data);
         return  true;
     }

     public  function  setOptimumComment($game_tenant_id,$bar_id,$comment_id,$uid){
         $barInfo = DI()->notorm->bar ->where("id = {$bar_id}")->fetchOne();
         if ($barInfo['optimum_comment_id']){
             return  1001;
         }

         if ($barInfo && $barInfo['uid'] !=$uid ){
             return 1000;
         }
         $userInfo = Model_User::getInstance()->getUserInfoWithIdAndTid($uid);
         $commentInfo = DI()->notorm->bar_comment ->where("id = {$comment_id}")->select('*')->fetchOne();
         DI()->notorm->bar_comment ->where("id = {$comment_id}")->update(['status' => 2]);
         DI()->notorm->bar->where("id = {$bar_id}")->update([
             'optimum_uid' => $commentInfo['publish_uid'],
             'optimum_comment_id' => $comment_id,

         ]);
         $configInfo = getConfigPub();
         if ($configInfo['reward_is_withdrawal'] == 1){
                DI()->notorm->users ->where("id = {$commentInfo['publish_uid']}")
                 ->update( array('withdrawable_push_slice' => new NotORM_Literal("withdrawable_push_slice +{$barInfo['reward_money']}")
                 ,'coin' => new NotORM_Literal("coin +{$barInfo['reward_money']}")) );
                $actionType  ='income';

         }else{
             DI()->notorm->users ->where("id = {$commentInfo['publish_uid']}")
                 ->update( array('withdrawable_push_slice' => new NotORM_Literal("withdrawable_push_slice +{$barInfo['reward_money']}")
                 ,'nowithdrawable_coin' => new NotORM_Literal("nowithdrawable_coin +{$barInfo['reward_money']}")) );

             $actionType  ='income_nowithdraw';
             $redis = connectRedis();
             $keytime = time();
             $redis->lPush($commentInfo['publish_uid'] . '_reward_time', $keytime);// 存用户 时间数据key
             $amount = $redis->get($commentInfo['publish_uid'] . '_' . $keytime.'_reward');
             $totalAmount = bcadd($barInfo['reward_money'], $amount, 2);
             $redis->set($commentInfo['publish_uid'] . '_' . $keytime.'_reward', $totalAmount);// 存佣金
             $expireTime = time() + $configInfo['withdrawal_time'];
             /** 86400*/
             $redis->expireAt($commentInfo['publish_uid'] . '_' . $keytime.'_reward', $expireTime);// 设置过去时间
         }
         delUserInfoCache($commentInfo['publish_uid']);
         $insert=array(
             "type"=>$actionType,
             "action"=>'bar',
             "uid"=>$commentInfo['publish_uid'],
             'user_login' => $userInfo['user_login'],
             'user_type' => $userInfo['user_type'],
             "giftid"=>$bar_id,
             "totalcoin"=>$barInfo['reward_money'],
             "addtime"=>time(),
             'tenant_id' =>getTenantId(),
         );
         $coinrecordModel = new Model_Coinrecord();
         $coinrecordModel->addCoinrecord($insert);
         delCache("userinfo_".$commentInfo['publish_uid']);
         return true;
     }

     public  function barLikes($game_tenant_id,$bar_id,$uid){
         $barInfo = DI()->notorm->bar_likes ->where("bar_id = {$bar_id} and uid = {$uid}")->fetchOne();
         if ($barInfo){
             DI()->notorm->bar_likes ->where("bar_id = {$bar_id} and uid = {$uid}")->delete();
             DI()->notorm->bar ->where("id = {$bar_id}")->update( array('like_number' => new NotORM_Literal("like_number - 1")
            ));;
             return ['is_like' => 0];
         }else{
             DI()->notorm->bar_likes ->insert(['bar_id' => $bar_id,'uid'=>$uid ]);
             DI()->notorm->bar ->where("id = {$bar_id}")->update( array('like_number' => new NotORM_Literal("like_number + 1")  ));;
             return ['is_like' => 1];
         }

     }


     public  function myData($game_tenant_id,$uid){
         $users_attention = DI()->notorm->users_attention->where("uid = {$uid} ")->count();
         $users_fans = DI()->notorm->users_attention->where(" touid = {$uid}")->count();
         $bar = DI()->notorm->bar->where(" uid = {$uid}")->select('id')->fetchAll();
         if ($bar){
             $barId = array_column($bar,'id');
             $ids =implode(",",$barId);
             $bar_num = DI()->notorm->bar_likes->where(" bar_id in ({$ids})")->select('id')->count();
         }else{
             $bar_num = 0 ;
         }

         $shotVideo = DI()->notorm->video->where(" uid = {$uid}")->select('id')->fetchAll();
         if ($shotVideo){
             $shotVideoId = array_column($shotVideo,'id');
             $shotVideoIdString =implode(",",$shotVideoId);
             $shotVideoNum = DI()->notorm->users_video_like->where(" videoid in ({$shotVideoIdString}) and video_type = 1")->count();
         }else{
             $shotVideoNum = 0 ;
         }
         $longVideo = DI()->notorm->video_long->where(" uid = {$uid}")->select('id')->fetchAll();
         if ($longVideo){
             $longVideoId = array_column($longVideo,'id');
             $longVideoIdString =implode(",",$longVideoId);
             $longVideoNum = DI()->notorm->users_video_like->where(" videoid in ({$longVideoIdString}) and video_type = 2")->count();
         }else{
             $longVideoNum = 0 ;
         }

         $users_code = DI()->notorm->users_agent_code->where(" uid  = {$uid}  ")->select('code')->fetchOne();
         $data['attention_num'] = (int) $users_attention;
         $data['fans_num'] =  (int) $users_fans;
         $data['like_num'] =  $bar_num + $shotVideoNum+$longVideoNum ;
         $data['code'] = $users_code['code']  ;
         $user =  DI()->notorm->users->where("id = {$uid}")->select('avatar,user_nicename')->fetchOne();
         $data['avatar'] = $user['avatar']  ;
         $data['user_nicename'] = $user['user_nicename']  ;
         return $data;

     }
}