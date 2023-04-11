<?php
/**
 * Created by PhpStorm.
 * User: jonem
 * Date: 2022/9/12
 * Time: 17:49
 */

namespace Admin\Controller;
use Think\Controller;


class  ConsumptionAwardController extends Controller {
    public function index(){

        $usersList  = M("users")->where(['game_tenant_id'=> 106,'type'=> ['in',[2,3,5,6,7]]])->field('id,pid,coin,tenant_id,user_type')->select(); // 拿出用户用于计余额
        $userById = array_column($usersList,null,'id');
        $tenant_id =  $usersList[0]['tenant_id'];
        $puidArray = [];
        foreach ($usersList as $usersListValue ){
            $puidArray[$usersListValue['pid']][] = $usersListValue['id'];
        }
        unset($puidArray[0]);
        /*
         * 参与统计的用户

        $users  = M("users")->where(['game_tenant_id'=> 106,'type'=> ['in',[2,3,5,6,7]],'pid'=>['gt',0]])->field('id,pid,coin')->select();

        */
        $ysetoday  = strtotime(date('Y-m-d',strtotime("-1 day")));
        $today =  strtotime(date('Y-m-d'));

        if ($puidArray){

            $consumptionList =M('activity_config')->where(['type'=>4,'tenant_id'=>$tenant_id])->order('sort_num asc')->select();
            foreach ($puidArray as $userkey => $userValue){

                $users_video_buy = M('users_video_buy')->where(['uid'=>['in',$userValue],
                    'addtime'=>["between", [$ysetoday, $today]]])->sum('price');
                $reward = $this->consumption($consumptionList,$users_video_buy);
             if ($reward  > 0 ){
                 $award_info = M('award_log')->where(['uid'=> $userkey,'addtime'=> ['egt',$today]])->find();
                 if ($award_info){
                     continue;
                 }
                    $awardLog = [
                        'uid' => $userkey,
                        'type' => 4 ,
                        'amount' => $reward,
                        'original_balance' =>  $userById[$userkey]['coin'],
                        'back_balance' =>  bcadd($userById[$userkey]['coin'],$reward ,2),
                        'completion_value'=> 0,
                        'addtime' => time(),
                        'status' => 1,
                        'award_name' => '好友消费'
                    ];
                    $res  = M('award_log')->add ($awardLog);


                    M('users')->where(['id' => $userkey])->save([ 'coin' => ['exp', 'coin+' . $reward]]);

                    $coinrecordData = [
                        'type' => 'income',
                        'user_type' => $userById[$userkey]['user_type'],
                        'giftid' => $res,
                        'uid' => $userkey,
                        'addtime' => time(),
                        'tenant_id' => $userById[$userkey]['tenant_id'],
                        'action' => 'friend_consumption_award',
                        'totalcoin' => $reward,//金额
                        "giftcount" => 1,
                        'is_withdrawable' => 1,
                        'pre_balance'=> $userById[$userkey]['coin'],
                        'after_balance'=> bcadd($reward,$userById[$userkey]['coin'],2),
                    ];
                 delUserInfoCache($userById[$userkey]);
                   $coinrecord =  M('users_coinrecord')->add($coinrecordData);  //  账变记录

                }

            }
        }




    }

    public  function consumption($data,$amount){
        foreach ($data as $datum){
            if ($amount>=$datum['min'] && $amount<$datum['max'] ){

                return $datum['reward'];
            }
        }
        return 0;
    }
}