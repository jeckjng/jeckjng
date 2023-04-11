<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/31 0031
 * Time: 14:04
 */

namespace App\WebSocket;

use App\Extend\GoHttpClient;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Socket\AbstractInterface\Controller;
use APP\Extend\DesEcb;
use App\Extend\WebsocketClient;
use App\Extend\UserModel;
use App\Extend\SocketIO;
use App\Extend\SocketIOtest;
use App\Extend\Redis as redisModel;
use App\Model\KaModel;
use App\HttpController\Initialize;

/**
 * Class Index
 *
 * 此类是默认的 websocket 消息解析后访问的 控制器
 *
 * @package App\WebSocket
 */
class Index extends Controller
{

    public function index()
    {

        $model = new  UserModel();//查询
        $recommend = $model->getrecommendcount(3);
        var_dump('=====上推荐的直播间数量====='.count($recommend));echo "\n";
        $recommend = json_decode(json_encode($recommend,JSON_UNESCAPED_UNICODE),true);
        $recommendid =  rand(0,count($recommend)-1);
        $recommendinfo = $recommend[$recommendid];
        var_dump($recommendinfo);
        echo 'succ';
        return false;
        $data =array(
            'uid'=>'21525',
            'token'=>'dsafdsaf3d5d5d55d5d5d55d'
        );
        $datas =array(
            'uid'=>'2152511',
            'token'=>'qqqqqqqqqqqqqqqqqq'
        );
        DesEcb::getInstance()->testlpush(json_encode($data));
        DesEcb::getInstance()->testlpush(json_encode($datas));
        $uinfo = DesEcb::getInstance()->testlpop();
        var_dump($uinfo);
        var_dump(json_decode($uinfo,true));
        echo 'succ';
        return false;
        $model = new  UserModel();//查询
        $id = 2;
        $orderData = $model->getbetinfo($id);
        $orderData = json_decode(json_encode($orderData,JSON_UNESCAPED_UNICODE),true);
        var_dump($orderData);
        $betinfo=array(
            'msgtype'=>1,
            'msgid'=>time(),
            'msgactiontype'=>1,
            'buttoncontent'=>'跟投',
            'buttonlinkurl'=>$orderData['playname'],
        );
        $touzhu_message1 = array(
            '_method_' => 'GameNews',
            'action' => 'GameNews',
            'gamedata' => $betinfo,

        );
        var_dump($touzhu_message1);
        $touzhu_message1 = str_replace('\\\\','',$touzhu_message1);
        var_dump($touzhu_message1);
        $touzhucopyy  = DesEcb::getInstance()->copyQueuedata(json_encode($touzhu_message1));
        echo 'succ';
        return false;

        /*      do {
                  # 过了一定时间后 我需要继续 发话
                  $socket->send('broadcast', json_encode(['msg'=>'第i次广播',]));


              } while (true);*/
        // var_dump($result);die;

        //从虚拟账号中删除一条会员数据
        /*     $effectuser  = DesEcb::getInstance()->deluseUser();
             echo $effectuser;exit;*/
        /* $socket = new SocketIO();
         $result = $socket->send('13.212.71.252', 19968,'broadcast', json_encode(['id'=>'1','name'=>'name_ '.date('Y-m-d H:i:s')]));
         var_dump($result);return false;*/



        /* $client = new \swoole_client(SWOOLE_SOCK_TCP  | SWOOLE_KEEP);
         try{
             $client->connect('13.212.71.252', 19968);
             if( !$client->isConnected())
             {
                 exit("connect failed\n");
             }
             $data=json_encode(['route'=>'demo/test']);
 //            $data='close';
             $client->send($data);
             $client->close(true);

         }catch (\Exception $e) {
             return $e->getMessage();
         }*/



        /* $client = new WebsocketClient('10.0.126.128',19968);

         $client->emit('msg', 'hello');       //socket.io
         $this->response()->setMessage('lawen');*/

        /*   $orderList =  array(
             'code'=>0,
             'msg'=>'获取成功！',
             'data'=>''
         );
         $caller = $this->caller();//获取想应得信息
         $data = $caller->getArgs();
         $uid = $data['uid'];
         $model = new  UserModel();//查询
         $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
         $orderData = $model->getalive($taskinfo['tenant_id']);
         $this->response()->setMessage($orderData);
         DesEcb::getInstance()->setAsOrId($uid,1222);
         foreach($orderData as $key=>$value){
             echo $value['stream'].'==1111==='.$value['liveuid'];
         }*/

        //ServerManager::getInstance()->getSwooleServer()->push($caller->getClient()->getFd(), json_encode($orderData));

        return;
    }

    /***
     * 开启任务，登录--进入房间
     **/
    public function loginF()
    {
        $caller = $this->caller();//获取相应的信息
        $data = $caller->getArgs();
        $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
        ServerManager::getInstance()->getSwooleServer()->push($caller->getClient()->getFd(), json_encode($taskinfo));
        if($taskinfo){
            echo $data['id'].' 号任务开始推送------';echo "\n";
            $timeinfo = $taskinfo['enterroom']*1000;
            /*
             * 进入房间定时任务开始
             */

            swoole_timer_tick($timeinfo, function($timer_id){
                $caller = $this->caller();//获取想应得信息
                $data = $caller->getArgs();
                $GoHttp = new GoHttpClient(DesEcb::getInstance()->getUrl());//
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                if($taskinfo['status']==2){
                    echo $timer_id.' 进入房间定时任务结束------';echo "\n";
                    swoole_timer_clear($timer_id);
                    return false;
                }
                echo $timer_id.' 进入房间定时任务开始------';echo "\n";
                //登录账号
                $url = 'https://'.$data['origins'].'/api/public/?service=Login.userLoginvutar';

                //从虚拟账号中取一条会员数据
                $effectuser  = DesEcb::getInstance()->getuseUser();
                //执行登录操作
                $body = $GoHttp->HttpPosturl(['user_login' => array_keys($effectuser)[0],"game_tenant_id"=>1,"zone"=>86, 'user_pass' => 'abc123456'],$url);

                echo 'login message'."\n";
                var_dump($body);
                echo "\n";

                //登录后删除掉这个可用的uid
                $deluid =array_keys($effectuser)[0];
                DesEcb::getInstance()->zremuseUser($deluid);

                echo 'delete user massage'."\n";;echo $deluid."\n";


                /**
                进入直播间操作
                 */
                $body = json_decode($body,true);
                $model = new  UserModel();//查询
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                $orderData = $model->getalive($taskinfo['tenant_id']);    //通过当前任务配置的租户，查询对应租户的正在直播的数据
                foreach($orderData as $key=>$value){
                    echo $value['stream'].'==进入房间==='.$value['uid']."\n";

                    $entercount=  DesEcb::getInstance()->getUserstremsize($value['stream']);

                    if($entercount>= $taskinfo['num'] ){
                        echo $value['stream'].' 该房间已经达到指定人数------房间人数：'.$entercount.'--设置人数：'.$taskinfo['num'];echo "\n";
                        continue;
                    }

                    $enterurl = 'https://'.$data['origins'].'/api/public/?service=Live.enterRoomvutar';
                    //请求进入房间接口
                    $enterbody = $GoHttp->HttpPosturl(["uid" =>$body["data"]["info"][0]["id"],"token" => $body["data"]["info"][0]["token"],"game_tenant_id"=>1,"zone"=>86, "liveuid" => $value["uid"], "stream" => $value["stream"] ],$enterurl);
                    ServerManager::getInstance()->getSwooleServer()->push($caller->getClient()->getFd(), json_encode($enterbody));
                    $enterdata = array(
                        'uid' =>$body["data"]["info"][0]["id"],
                        'token' =>$body["data"]["info"][0]["token"],
                        'liveuid' =>$value['uid'],
                        'roomnum' =>$value['uid'],
                        'stream' =>$value['stream'],
                        'language' =>101,
                        'isRobot'=>true,

                    );

                    //广播
                    $borderdata = array(
                        'retcode' => '000000',
                        'retmsg'=> 'ok',
                        'msg'=>array(
                            '_method_'=> 'requestFans',
                            'action'=>'',
                            'msgtype'=>'',
                        ),
                        'isRobot'=>true,
                        'roomnum'=>$value['uid'],
                        'token' =>$body["data"]["info"][0]["token"],
                    );
                    //登录的token和uid写入到redis里面(替换成 队列 list )
                    $pushdata = array(
                        'uid' =>$body["data"]["info"][0]["id"],
                        'token' =>$body["data"]["info"][0]["token"],
                        'user_nicename' =>$body["data"]["info"][0]["user_nicename"],
                        'avatar' =>$body["data"]["info"][0]["avatar"],
                        'stream' =>$value['stream'],
                    );
                    DesEcb::getInstance()->testlpush($pushdata);


                    //进入房间信息连接
                    $socket = new SocketIOtest();
                    $result = $socket->init($data['hostaddress'], 19968);
                    $result = $socket->send( 'conn', json_encode($enterdata));
                    //进入房间信息推送，通知直播间其它用户有新进来的会员
                    $socket = new SocketIOtest();
                    $result = $socket->init($data['hostaddress'], 19968);
                    $result= $socket->send('broadcast', json_encode($borderdata));


                    /*   $socket = new SocketIO();
                       $result = $socket->send('13.212.71.252', 19968,'conn', json_encode($enterdata));
                       $result= $socket->send('13.212.71.252', 19968,'broadcast', json_encode($borderdata));*/
                    // $result = $socket->send('13.212.71.252', 19968,'broadcast', json_encode(['id'=>'1','name'=>'name_ '.date('Y-m-d H:i:s')]));

                }
                /*
                 * 进入房间任务结束
                 */



            });


            /*
             * 发言定时任务开始
             */
            $timebarrage = $taskinfo['sendbarrage']*1000;
            swoole_timer_tick($timebarrage, function($timer_id) {
                //广播
                $caller = $this->caller();//获取想应得信息
                $data = $caller->getArgs();
                //发言任务结束
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                if($taskinfo['status']==2){
                    echo $timer_id.' 发言任务结束------';echo "\n";
                    swoole_timer_clear($timer_id);
                    return false;
                }
                echo $timer_id.' 发言定时任务开始------';echo "\n";
                $model = new  UserModel();//查询
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                $orderData = $model->getalive($taskinfo['tenant_id']);    //通过当前任务配置的租户，查询对应租户的正在直播的数据
                foreach($orderData as $key=>$value){
                    echo $value['stream'].'==发言定时任务==='.$value['uid']."\n";
                    $smgdata = array(
                        'stream'=>$value['stream'],
                    );
                    //从登录的直播间中去一条数据，只要不为空就行，拿到token后，推送发言
                    $msgtoken = DesEcb::getInstance()->testlpop($value['stream']);
                    $msgtoken = json_decode($msgtoken,true);
                    DesEcb::getInstance()->testlpush($msgtoken);
                    if(empty($msgtoken)){
                        echo '------没有虚拟会员--发言-----';echo "\n";
                        continue;
                    }

                    $wordstoken = $msgtoken['token'];
                    $uid = $msgtoken['uid'];

                    if(!isset($msgtoken['user_nicename'])){
                        $msgtoken['user_nicename'] = '一指禅20210731005';
                    }
                    if(!isset($msgtoken['avatar'])){
                        $msgtoken['avatar'] = 'https://livebackprd.meibocms.com/public/images/head_121.png';
                    }

                    echo $uid.'=======发言uid========'.$wordstoken.'====发言名称===='.$msgtoken['user_nicename']."\n";
                    $sendmsginfo = array(
                        '我就发个言，主播真美',
                        '大家听我说',
                        '主播叫什么',
                        '我就看看不说话',
                        '哈哈哈，笑死了',
                        '北京欢迎你',
                        '一条小青龙',
                        '北上广深',
                        '主播是个大美女',
                        '天空飘过五个字，那都不是事'
                    );
                    $mt_round = mt_rand(0,9);



                    $bordersend_message = array(
                        'retcode' => '000000',
                        'retmsg' => 'ok',
                        'roomnum'=> strval($value['uid']),
                        'isRobot'=> true,
                        'token'=>$wordstoken,//获取的登录账号的token
                        'msg' => array(
                            0=>
                                array(
                                    '_method_' => 'SendMsg',
                                    'action' => '0',
                                    'msgtype' => '2',
                                    'usertype' => '30',
                                    'isAnchor' => '0',
                                    'level' => '1',
                                    'uname' => $msgtoken['user_nicename'],
                                    'uhead'=>$msgtoken['avatar'],
                                    'uid' => $uid,     //获取的登录账号的uid
                                    'liangname' => '0',
                                    'vip_type' => '0',
                                    'guard_type' => '0',
                                    'ct' =>  $sendmsginfo[$mt_round],
                                )
                        ),

                    );

                    $socket = new SocketIOtest();
                    $result = $socket->init($data['hostaddress'], 19968);
                    $result= $socket->send('broadcast', json_encode($bordersend_message));







                }


            });
            //发言结束

            /*
            * 发送礼物任务开始
            */
            $timesendgift = $taskinfo['sendgift']*1000;
            swoole_timer_tick($timesendgift, function($timer_id) {
                //广播
                $caller = $this->caller();//获取想应得信息
                $data = $caller->getArgs();
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                if($taskinfo['status']==2){
                    echo $timer_id.'  发送礼物结束------';echo "\n";
                    swoole_timer_clear($timer_id);
                    return false;
                }
                echo $timer_id.' 送礼物定时任务开始------';echo "\n";
                $model = new  UserModel();//查询
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                $orderData = $model->getalive($taskinfo['tenant_id']);    //通过当前任务配置的租户，查询对应租户的正在直播的数据

                foreach($orderData as $key=>$value){
                    echo $value['stream'].'==送礼物定时任务==='.$value['uid']."\n";
                    $smgdata = array(
                        'stream'=>$value['stream'],
                    );
                    $msgtoken = DesEcb::getInstance()->testlpop($value['stream']);
                    $msgtoken = json_decode($msgtoken,true);
                    DesEcb::getInstance()->testlpush($msgtoken);
                    if(empty($msgtoken)){
                        echo '------没有虚拟会员--送礼-----';echo "\n";
                        continue;
                    }

                    $wordstoken = $msgtoken['token'];
                    $uid = $msgtoken['uid'];

                    if(!isset($msgtoken['avatar'])){
                        $msgtoken['avatar'] = 'https://livebackprd.meibocms.com/public/images/head_121.png';
                    }
                    if(!isset($msgtoken['user_nicename'])){
                        $msgtoken['user_nicename'] = '北风之神20210712005';
                    }
                    echo $uid.'=======送礼uid========'.$wordstoken.'====送礼名称===='.$msgtoken['user_nicename']."\n";

                    $GoHttp = new GoHttpClient(DesEcb::getInstance()->getUrl());//
                    $gifturl = 'https://'.$data['origins'].'/api/public/?service=Live.sendGiftvatul';
                    //请求进入房间接口
                    $sendgiftinfo = $GoHttp->HttpPosturl(["uid" =>$uid,"token" => $wordstoken,"game_tenant_id"=>1,"zone"=>86, "liveuid" => $value["uid"], "stream" => $value["stream"], "giftid" => 102, "giftcount" => 1 ],$gifturl);
                    $sendgiftinfo = json_decode($sendgiftinfo,true);



                    $sendgift_message = array(
                        'retcode' => '000000',
                        'retmsg' => 'ok',
                        'roomnum'=>$value['uid'],
                        'isRobot'=> true,
                        'token'=>$wordstoken,
                        'msg' => array(
                            0=>
                                array(
                                    '_method_' => 'SendGift',
                                    'action' => '0',
                                    'msgtype' => '1',
                                    'level' => '1',
                                    'uname' => $msgtoken['user_nicename'],
                                    'uid' => $uid,
                                    'uhead'=>$msgtoken['avatar'],
                                    'evensend' => '0',
                                    'liangname' => '0',
                                    'vip_type' => '0',
                                    'ct' => $sendgiftinfo["data"]["info"][0]["gifttoken"],
                                )
                        )
                    );

                    $socket = new SocketIOtest();
                    $result = $socket->init($data['hostaddress'], 19968);
                    $result= $socket->send('broadcast', json_encode($sendgift_message));






                }


            });
            //发送礼物结束


            /*
             * 退出房间
             */
            $timelogout = $taskinfo['logout']*1000;
            swoole_timer_tick($timelogout, function($timer_id) {
                //广播
                $caller = $this->caller();//获取想应得信息
                $data = $caller->getArgs();
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                if($taskinfo['status']==2){
                    echo $timer_id.'  退出房间结束------';echo "\n";
                    swoole_timer_clear($timer_id);
                    return false;
                }
                echo $timer_id.' 退出房间任务开始------';echo "\n";
                $model = new  UserModel();//查询
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                $orderData = $model->getalive($taskinfo['tenant_id']);    //通过当前任务配置的租户，查询对应租户的正在直播的数据

                foreach($orderData as $key=>$value){

                    $logdata['stream'] = $value['stream'];

                    $msgtoken = DesEcb::getInstance()->testlpop($value['stream']);
                    $msgtoken = json_decode($msgtoken,true);
                    if(empty($msgtoken)){
                        echo '------没有虚拟会员--退出-----';echo "\n";
                        continue;
                    }

                    $uid = $msgtoken['uid'];

                    $logdata['uid'] = $uid;
                    DesEcb::getInstance()->delUserstrem($logdata);
                }


            });
            //退出房间直播结束





            /*
              * 投注信息分享开始
              */
            $timebet = $taskinfo['timebet']*1000;
            swoole_timer_tick($timebet, function($timer_id) {
                //广播
                $caller = $this->caller();//获取想应得信息
                $data = $caller->getArgs();
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                if($taskinfo['status']==2){
                    echo $timer_id.' 投注信息分享结束------';echo "\n";
                    swoole_timer_clear($timer_id);
                    return false;
                }
                echo $timer_id.' 投注信息分享任务开始------';echo "\n";
                $model = new  UserModel();//查询

                $orderData = $model->getalive($taskinfo['tenant_id']);    //通过当前任务配置的租户，查询对应租户的正在直播的数据

                /*
                 * 获取该租户的全部uid集合
                 */
                $uids = ''; //变量赋值为空
                foreach($orderData as $key=>$value) {
                    $uids .= $value['uid'] . ',';
                }
                $uids = rtrim($uids, ',');
                var_dump($uids);echo'==房间信息1==='."\n";


                /*随机生成中奖和跟投消息，不用读库*/
                $nickname = array(
                    '风清扬','黄药师','andy','lawen','北风之神','meibo','一条小青龙','xuzhu','历尽','一指禅'
                );
                $mt_nickname = mt_rand(0,9);
                $timeinfo = date('Ymd',time());
                $mtcode = mt_rand(1,10000);
                $gentounickname = $nickname[$mt_nickname].$timeinfo.$mtcode;
                $mt_nickname = mt_rand(0,9);
                $timeinfo = date('Ymd',time());
                $mtcode = mt_rand(1,10000);
                $zhongjiangnickname = $nickname[$mt_nickname].$timeinfo.$mtcode;

                var_dump($gentounickname.'=====跟投和中奖账号====='.$zhongjiangnickname);echo "\n";


                $model = new  UserModel();//查询
                $betcount = $model->getbetinfos($taskinfo['tenant_id']);
                var_dump('=====跟投模板数量====='.count($betcount));echo "\n";
                $betcount = json_decode(json_encode($betcount,JSON_UNESCAPED_UNICODE),true);
                $randbetid =  rand(0,count($betcount)-1);
                $id = $betcount[$randbetid]['id'];

                $betData = $model->getbetinfo($id);
                $betData = json_decode(json_encode($betData,JSON_UNESCAPED_UNICODE),true);

                //随机生成1和2，1代表跟投信息，2代表中奖信息
                $gentoumoney = rand(10,10000);
                $zhongjiangmoney = rand(10,10000);

                $betinfo=array(
                    'msgtype'=>1,
                    'msgid'=>time(),
                    'msgcontent'=> "<font color=#8DE7FF>".$gentounickname."</font><font color=#ffffff>在</font><font color=#dd4b85>".$betData['name']."</font><font color=#ffffff>投注</font><font color=#ffce64>" .$gentoumoney."金币</font>",
                    'msgactiontype'=>1,
                    'buttoncontent'=>'跟投',
                    'buttonlinkurl'=> $betData['playname'],
                    'game_tenant_id'=>$betData['game_tenant_id'],
                    'newmsgcontent'=>array('nickname'=>$gentounickname,'playname'=>$betData['name'],'money'=>$gentoumoney),
                );

                $betinfos=array(
                    'msgtype'=>2,
                    'msgtitle'=>'中奖',
                    'msgid'=>time(),
                    'msgcontent'=> "<font color=#FFFFFF>恭喜</font><font color=#8DE7FF>".$zhongjiangnickname."</font><font color=#FFFFFF>在</font><font color=#dd4b85>".$betData['name']."</font><font color=#FFFFFF>中了".$zhongjiangmoney."元</font>",
                    'game_tenant_id'=>$betData['game_tenant_id'],
                    'newmsgcontent'=>array('nickname'=>$zhongjiangnickname,'playname'=>$betData['name'],'money'=>$zhongjiangmoney),
                );


                $nodeJsToken = '1234567';


                echo  '=======投注彩种信息========'.$betData['name']."\n";
                var_dump($uids);echo'====='."\n";
                $touzhu_message = array(
                    '_method_' => 'GameNews',
                    'action' => 'GameNews',
                    'gamedata' => $betinfo,
                    'roomnums'=>  $uids,
                    'token' =>   $nodeJsToken,
                );
                $touzhu_message1 = array(
                    '_method_' => 'GameNews',
                    'action' => 'GameNews',
                    'gamedata' => $betinfos,
                    'roomnums'=>  $uids,
                    'token' =>   $nodeJsToken,
                );
                $touzhuinfo  = DesEcb::getInstance()->addQueuedata(json_encode($touzhu_message));
                $touzhucopyy  = DesEcb::getInstance()->copyQueuedata(json_encode($touzhu_message));
                $touzhumessage  = DesEcb::getInstance()->addQueuedata(json_encode($touzhu_message1));
                $touzhumessagecopy  = DesEcb::getInstance()->copyQueuedata(json_encode($touzhu_message1));
                var_dump($touzhucopyy);echo'==备份消息==='."\n";
                /* $socket = new SocketIOtest();
                $result = $socket->init($data['hostaddress'], 19968);
                $result= $socket->send('broadcast', json_encode($touzhu_message));*/


            });
            //发送礼物结束

            //推荐直播开始
            $recommend = $taskinfo['recommend']*1000;
            swoole_timer_tick($recommend, function($timer_id) {
                $caller = $this->caller();//获取想应得信息
                $data = $caller->getArgs();
                $taskinfo = DesEcb::getInstance()->getAtotask($data['id']);
                if($taskinfo['status']==2){
                    echo $timer_id.' 推荐直播结束------';echo "\n";
                    swoole_timer_clear($timer_id);
                    return false;
                }
                echo $timer_id.' 推荐直播任务开始------';echo "\n";

                $model = new  UserModel();//查询
                $orderData = $model->getalive($taskinfo['tenant_id']);

                /*
                 * 获取该租户的全部uid集合
                 */
                $uids = ''; //变量赋值为空
                foreach($orderData as $key=>$value) {
                    $uids .= $value['uid'] . ',';
                }
                $uids = rtrim($uids, ',');
                var_dump($uids);echo'===推荐直播房间信息=='."\n";


                //随机去一条数据
                $recommend = $model->getrecommendcount($taskinfo['tenant_id']);
                var_dump('=====上推荐的直播间数量====='.count($recommend));echo "\n";
                $recommend = json_decode(json_encode($recommend,JSON_UNESCAPED_UNICODE),true);
                $recommendid =  rand(0,count($recommend)-1);
                $recommendinfo = $recommend[$recommendid];

                $nickname = array(
                    '风清扬','黄药师','andy','lawen','北风之神','meibo','一条小青龙','xuzhu','历尽','一指禅'
                );
                $caizhong = array(
                    '腾讯分分彩','极速分分赛车','分分飞艇','澳门分分六合','分分快三','分分快三','幸运28','幸运28','极速分分赛车','极速分分赛车'
                );
                $mt_nickname = mt_rand(0,9);
                $timeinfo = date('Ymd',time());
                $names = $nickname[$mt_nickname].$timeinfo;
                $betDataname = $caizhong[$mt_nickname];
                $sendmoney = mt_rand(1,10000);
                var_dump($names.'=========='.$betDataname.'========'.$sendmoney.'======'.$recommendinfo['liveuid']);echo "\n";
                $room_data = array(
                    'msgtype'=>99,
                    'msgid'=>time(),
                    'msgcontent'=> "<font color=#8DE7FF>".$names."</font><font color=#ffffff>在</font><font color=#ffce64>".$betDataname."</font><font color=#ffffff>赢得了</font><font color=#ffce64>" .$sendmoney."元！简直是天上掉馅饼</font>",
                    'liveuid'=>$recommendinfo['liveuid'],
                    'stream' =>$recommendinfo['stream'],
                    'tenant_id' =>$recommendinfo['game_tenant_id'],  //记住，是彩票那边的id，不要搞错
                    'newmsgcontent'=>array('nickname'=>$names,'playname'=>$betDataname,'money'=>$sendmoney),
                );
                $nodeJsToken = '1234567';
                $recommond_message = array(
                    '_method_' => 'GameNews',
                    'action' => 'GameNews',
                    'gamedata' => $room_data,
                    'token' =>   $nodeJsToken,
                    'roomnums'=>  $uids,
                );
                $isrecomond  = DesEcb::getInstance()->addQueuedata(json_encode($recommond_message));
                $isrecomondcopyy  = DesEcb::getInstance()->copyQueuedata(json_encode($recommond_message));

                echo'==直播间推荐备份消息==='."\n";



            });
            //推荐直播间结束

        }
        return false;

    }

    public function  recommendRoom(){


        swoole_timer_tick(60000, function($timer_id) {
            $tenant_id = 1;
            $model = new  UserModel();//查询
            $recommonddata = $model->getrecommend($tenant_id);    //查询推荐主播
            $recommonddata = json_decode(json_encode($recommonddata,JSON_UNESCAPED_UNICODE),true);


            if(empty($recommonddata)){
                echo'==推荐主播租户id==='."\n";return false;
            }
            /*
             * 获取该租户的全部uid集合
             */
            $recommonduids = ''; //变量赋值为空
            foreach($recommonddata as $key=>$value) {
                $recommonduids = $value['uid'];
                $stream = $value['stream'];
                $tenant_id=$value['tenant_id'];
            }

            var_dump($recommonduids);var_dump($stream);echo'==推荐主播信息==='."\n";

            $model = new  UserModel();//查询
            $orderData = $model->getalive($tenant_id);    //

            /*
             * 获取该租户的全部uid集合
             */
            $uids = ''; //变量赋值为空
            foreach($orderData as $key=>$value) {
                $uids .= $value['uid'] . ',';
            }
            $uids = rtrim($uids, ',');
            var_dump($uids);echo'===推荐直播房间信息=='."\n";

            $tenant_id = $tenant_id;
            $tenantdata = $model->getrecommendtid($tenant_id);    //查询推荐主播
            foreach ($tenantdata as $key=>$value){
                $game_tenant_id = $value['game_tenant_id'];
                $tenant_name = $value['name'];
            }
            var_dump($game_tenant_id);echo'==推荐主播租户id==='."\n";
            var_dump($tenant_name);echo'==推荐租户名称==='."\n";
            $names = 'lawen201525';
            $betDataname = '腾讯分分彩';
            $sendmoney = '999';
            $room_data = array(
                'msgtype'=>99,
                'msgid'=>time(),
                'msgcontent'=> "<font color=#8DE7FF>".$names."</font><font color=#ffffff>在</font><font color=#ffce64>".$betDataname."</font><font color=#ffffff>赢得了</font><font color=#ffce64>" .$sendmoney."元！简直是天上掉馅饼</font>",
                'liveuid'=>$recommonduids,
                'stream' =>$stream,
                'tenant_id' =>$game_tenant_id,  //记住，是彩票那边的id，不要搞错
                'newmsgcontent'=>array('nickname'=>$names,'playname'=>$betDataname,'money'=>$sendmoney),
            );
            $nodeJsToken = '1234567';
            $recommond_message = array(
                '_method_' => 'GameNews',
                'action' => 'GameNews',
                'gamedata' => $room_data,
                'token' =>   $nodeJsToken,
                'roomnums'=>  $uids,
            );
            $isrecomond  = DesEcb::getInstance()->addQueuedata(json_encode($recommond_message));
            $isrecomondcopyy  = DesEcb::getInstance()->copyQueuedata(json_encode($recommond_message));

            echo'==直播间推荐备份消息==='."\n";



        });
        return false;
        //推荐直播结束

    }

    /**
     * 心跳监听
     ***/
    public function syncF()
    {
        $caller = $this->caller();//获取想应得信息
        $data = $caller->getArgs();
        //$server = ServerManager::getInstance()->getSwooleServer();
        $uid = $data['userMoblie'];
        //$uid = isset($data['userMoblie']) ? $data['userMoblie'] : $server->getClientInfo($caller->getClient()->getFd())['uid'];
        if ($uid == 0) {
            return false;
        }
        if (DesEcb::getInstance()->getLock($uid) == 0) {//不等于0发起请求查询APP执行结果
            //操作订单
            $cardNumber = DesEcb::getInstance()->getKaNum($uid);//读取可操作的卡号
            if (empty($cardNumber)) {
                return false;
            }
            $orderData = DesEcb::getInstance()->getOrder($uid, $cardNumber);
            if (empty($orderData)) {
                return false;
            }
            $orderList = [
                'type' => 'new_order',
                'card_number' => $orderData['ka'],
                'amount' => $orderData['money'],
                'name' => $orderData['realname'],
                'remark' => 'DF_' . $orderData['id']
            ];
            //FD加锁
            DesEcb::getInstance()->setLock($uid);
            //存储该订单ID
            DesEcb::getInstance()->setOrderId($uid, $orderData['id']);
            //推送消息到APP
            ServerManager::getInstance()->getSwooleServer()->push($caller->getClient()->getFd(), json_encode($orderList));

        }
        return;
    }

    /**
     * 订单查询修改动作
     **/
    public function queryF()
    {
        $caller = $this->caller();//获取想应得信息
        $data = $caller->getArgs();
        $server = ServerManager::getInstance()->getSwooleServer();
        $uid = $server->getClientInfo($caller->getClient()->getFd())['uid'];
        //获取ORDERID
        $_ordId = DesEcb::getInstance()->getOrderId($uid);
        $ordId = explode('_', $data['id']);
        $status = 1;
        $msg = '制单成功';
        if ($ordId > 0 && ($_ordId == $ordId[1])) {
            $status = 0;
            $msg = '';
        }
        $pushData = [
            'result' => [
                'code' => 1000,
                'data' => [
                    'msg' => $msg,
                    'status' => $status
                ],
                'msg' => '成功调用',
                'timestamp' => time()
            ],
            'id' => 'DF_' . $ordId[1],
            'type' => 'queryResult'
        ];
        $server->push($caller->getClient()->getFd(), json_encode($pushData));
        \Swoole\Coroutine::sleep(210);// 随机设置休眠时间
        DesEcb::getInstance()->setAsOrId($uid,$ordId[1]);
        return;
    }

    /***
     * 订单回调动作
     ***/
    public function ordersList()
    {
        $caller = $this->caller();//获取想应得信息
        $data = $caller->getArgs();
        //开始处理数据
        $uid = ServerManager::getInstance()->getSwooleServer()->getClientInfo($caller->getClient()->getFd())['uid'];
        if (empty($data['arrays'])) {//表示数据已经处理
            if(DesEcb::getInstance()->getOrderId($uid)===DesEcb::getInstance()->getAsOrId($uid)){
                if(DesEcb::getInstance()->setOrderStatus(DesEcb::getInstance()->getOrderId($uid))==200){
                    DesEcb::getInstance()->unLock($uid);//解锁动作
                    DesEcb::getInstance()->delOrderId($uid);
                }
            }
            return false;
        }
        //写入金额
        if (is_numeric($data['balance'])) {
            DesEcb::getInstance()->setBalance($uid, $data['balance']);
            //写入数据库
            DesEcb::getInstance()->setBalanceData($uid, $data['balance']);
            // var_dump($status);
        }

        $_data = DesEcb::getInstance()->getOrderData($uid);
        if (empty($_data)) {
            return false;
        }
        DesEcb::getInstance()->delOrderId($uid);
        DesEcb::getInstance()->unLock($uid);//解锁动作
        $GoHttp = new GoHttpClient(DesEcb::getInstance()->getUrl());//
        $GoHttp->HttpPost(['id' =>  $_data['id'], 'sign' => md5('DF_API' .  $_data['id'])]);
        return false;
    }
}