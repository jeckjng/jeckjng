<?php
//session_start();
class Model_Pushpull extends PhalApi_Model_NotORM
{

    public function getPushpull($ct_type){

        $info = $this->getPushpullInfo($ct_type);

        $data['pushpull_id'] = $info['id'];
        $data['appid'] = $info['appid'];

        return array('code' => 0, 'msg' => '', 'info' => [$data]);
    }

    // 增加历史累积使用次数
    public function addUsenum($id){
        try {
            $res = DI()->notorm->livepushpull->where(['id'=>intval($id)])->update(array(
                'usenum'=>new NotORM_Literal("usenum+1"),
            ));
        }catch (\Exception $e){
            return array('code' => 0, 'msg' => '', 'info' => [$e->getMessage()]);
        }

        return array('code' => 0, 'msg' => '', 'info' => [$res]);
    }

    /*
     *  主播开播分流
        青点云1(老)，青点云6(新)，12群3
        那第一个开播，随机获取一个，第二个开播就获取另外两个，第三个开播就获取另一个，这样轮一圈，然后再重新来过；
        第一轮过去，因为青点云(老)是1，所以第二轮，就只有青点云6(新)、12群3;
        等第三轮过去，第四轮就只有青点云(新)，然后第6轮就完成了一个大轮转，然后都归零，再重新轮
     * */
    public function bypass_ratio($cur_ct_type_arr){
        $redis = connectionRedis();
        $all_live_num = true;
        $all_live_last = true;
        $can_live_arr = array();
        $can_live_arr_2 = array();
        foreach ($cur_ct_type_arr as $key=>$val){
            if($val['live_num'] < $val['bypass_ratio']){
                $all_live_num = false;
            }
            if($val['live_last'] == 1){
                $all_live_last = false;
            }
            if($val['live_num'] < $val['bypass_ratio'] && $val['live_last'] != 1){
                array_push($can_live_arr,$val);
            }
            if($val['live_num'] < $val['bypass_ratio'] && $val['live_last'] == 1){
                array_push($can_live_arr_2,$val);
            }
        }
        $can_live_arr = !empty($can_live_arr) ? $can_live_arr : $can_live_arr_2;
        // 如果分流比例都满了，那就随机获取一个，并把未获取到的 开播数量 和 是否已开播过 重置为0
        if($all_live_num === true){
            $info = count($cur_ct_type_arr) > 0 ? $cur_ct_type_arr[rand(0, count($cur_ct_type_arr) - 1)] : [];
            foreach ($cur_ct_type_arr as $key=>$val) {
                if($info['id'] == $val['id']){
                    $redis->hSet('pushpull_live_num', $val['id'], 1);
                    $redis->hSet('pushpull_live_last', $val['id'], 1);
                }else{
                    $redis->hSet('pushpull_live_num', $val['id'], 0);
                    $redis->hSet('pushpull_live_last', $val['id'], 0);
                }
            }
        }else{
            // 如果都开播过一遍，那就随机获取一个，并把未获取到的 是否已开播 过重置为0
            if($all_live_last === true){
                $info = count($can_live_arr) > 0 ? $can_live_arr[rand(0, count($can_live_arr) - 1)] : [];
                foreach ($can_live_arr as $key=>$val) {
                    if($info['id'] == $val['id']) {
                        $redis->hIncrBy('pushpull_live_num', $val['id'], 1);
                        $redis->hSet('pushpull_live_last', $val['id'], 1);
                    }else{
                        $redis->hSet('pushpull_live_last', $val['id'], 0);
                    }
                }
            }else{
                // 如果有未开播过，那就随机获取一个，并把 是否已开播过 设置为1
                $info = count($can_live_arr) > 0 ? $can_live_arr[rand(0, count($can_live_arr) - 1)] : [];
                $redis->hIncrBy('pushpull_live_num', $info['id'], 1);
                $redis->hSet('pushpull_live_last', $info['id'], 1);
            }
        }
        return isset($info) || !empty($info) ? $info : [];
    }

    public function getPushpullAuthToken($liveuid){
        $domain = new Model_Live();
        $live_info = $domain->getLiveInfo($liveuid);
        $pushpull_id = isset($live_info['pushpull_id']) ? $live_info['pushpull_id'] : '';

        $list = getPushpullList();
        $auth_token = '';
        $pushpull_info = array();
        foreach ($list as $key=>$val){
            if($val['id'] == $pushpull_id){
                $pushpull_info = $val;
            }
        }
        if(empty($pushpull_info)){
            return array('code' => 2055, 'msg' => codemsg('2055'), 'info' => array());
        }
        if($pushpull_info['code'] == 10){ // 10 声网推拉流服务商，获取鉴权token给前端
            $auth_token = $this->ws_auth_token($pushpull_info,$liveuid,$live_info['stream']);
        }

        return array('code' => 0, 'msg' => '', 'info' => array(['auth_token'=>$auth_token]));
    }

    /*
    * 获取声网鉴权token
    * */
    public function ws_auth_token($pushpul_info = array(),$uid,$stream){
        if(empty($pushpul_info)){
            return '';
        }
        include(dirname(__FILE__) ."/../../extend/livetokensw/src/RtcTokenBuilder.php");

        $appID = $pushpul_info['appid'];
        $appCertificate = $pushpul_info['certificate'];
        $channelName = $stream;
        $uid = intval($uid); // 待鉴权用户的用户 ID 32 位无符号整数，范围为1到 (2³² - 1)， 并保证唯一性。 如不需对用户 ID 进行鉴权，即客户端使用任何 uid 都可加入频道，请把 uid 设为 0。
        $uidStr = strval($uid);
        $role = RtcTokenBuilder::RoleAttendee;
        $expireTimeInSeconds = 60*60; // 测试阶段 1分钟
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

    //        $token = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $uidStr, $role, $privilegeExpiredTs);
        return $token;
    }

    public function getPushpullInfo($ct_type = 1){
        $redis = connectionRedis();
        $list = getPushpullList();

        $default_arr = array();
        $cur_ct_type_arr = array();
        foreach ($list as $key=>$val){
            $live_num = $redis->hGet('pushpull_live_num',$val['id']); // 开播数量
            $live_last = $redis->hGet('pushpull_live_last',$val['id']); // 是否已开播过：0.否，1.是
            $val['live_num'] = $live_num ? $live_num : 0;
            $val['live_last'] = $live_last ? $live_last : 0;
            if($val['ct_type'] == $ct_type){
                array_push($cur_ct_type_arr,$val);
            }
            if($val['ct_type'] == 1){
                array_push($default_arr,$val);
            }
        }
        $info = $this->bypass_ratio($cur_ct_type_arr);
        $info = empty($info) && count($default_arr) > 0 ? $this->bypass_ratio($default_arr) : $info;

        return $info;
    }

    public function getPushpullInfoWithId($id){
        $list = getPushpullList();

        $info = array();
        foreach ($list as $key=>$val){
            if($val['id'] == $id){
                $info = $val;
            }
        }
        if(empty($info) || !$info){
            $info = DI()->notorm->livepushpull->where(['id'=>$id])->fetchOne();
        }
        return $info;
    }

    /**
     *  @desc 获取推拉流地址
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function PrivateKeyA($host,$stream,$type,$info)
    {
        if($info && isset($info['pull'])){
            if($info['pull']){
                $arr_pill = explode("\n",$info['pull']);
                $info['pull'] = $arr_pill[rand(0,count($arr_pill)-1)];
            }
            if($info['pull_url']){
                $arr_pull_url = explode("\n",$info['pull_url']);
                $info['pull_url'] = $arr_pull_url[rand(0,count($arr_pull_url)-1)];
            }
            if($info['flv_pull_url']){
                $arr_flv_pull_url = explode("\n",$info['flv_pull_url']);
                $info['flv_pull_url'] = $arr_flv_pull_url[rand(0,count($arr_flv_pull_url)-1)];
            }
        }

        switch ($info['code']){
            case '1': // 阿里云
                $url = $this->get_ali($host,$stream,$type,$info);
                break;
            case '2': // 腾讯云
                $url = $this->get_tx($host,$stream,$type,$info);
                break;
            case '3': // 七牛云
                $url = $this->get_qn($host,$stream,$type,$info);
                break;
            case '4': // 网宿
                $url = $this->get_ws($host,$stream,$type,$info);
                break;
            case '5':// 网易
                $url = $this->get_wy($host,$stream,$type,$info);
                break;
            case '6': // 奥点云
                $url = $this->get_ady($host,$stream,$type,$info);
                break;
            case '7':// 自建SRS，自定义服务器 $url='rtmp://120.25.106.132:1935/live/livestream';
                $url = $this->get_customSrs($host,$stream,$type,$info);
                break;
            case '8': // 青点云
                $url = $this->get_qdy($host,$stream,$type,$info);
                break;
            case '9': //  腾讯云 rtmps
                $url = $this->get_tx_rtmps($host,$stream,$type,$info);
                break;
            case '10': // 声网
                $url = $this->get_sw($host,$stream,$type,$info);
                break;
            case '11': // 12群
                $url = $this->get_qun12($host,$stream,$type,$info);
                break;
            default:
                $url = '';
        }
        return $url;
    }

    /**
     *  @desc 阿里云直播A类鉴权
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function get_ali($host,$stream,$type,$info){
        $push=$info['push'];
        $pull=$info['pull'];
        $key_push=$info['push_key'];
        $length_push=$info['push_length'];
        $key_pull=$info['pull_key'];
        $length_pull=$info['pull_length'];
        $hubName=$info['appname'];

        if($type==1){
            $domain=$host.'://'.$push;
            $time=time() + $length_push;
        }else{
            $domain=$host.'://'.$pull;
            $time=time() + $length_pull;
        }

        $filename="/5showcam/".$stream;

        if($type==1){
            if($key_push!=''){
                $sstring = $filename."-".$time."-0-0-".$key_push;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
            if($auth_key){
                $auth_key='?'.$auth_key;
            }
            $url=$domain.$filename.$auth_key;
        }else{
            if($key_pull!=''){
                $sstring = $filename."-".$time."-0-0-".$key_pull;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
            if($auth_key){
                $auth_key='?'.$auth_key;
            }
            $url=$domain.$filename.$auth_key;
        }

        return $url;
    }

    /**
     *  @desc 腾讯云推拉流地址
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function get_tx($host,$stream,$type,$info){
        $bizid=$info['bizid'];
        $push_url_key=$info['push_key'];
        $push=$info['push'];
        $pull=$info['pull'];
        $appname = $info['appname'];
        $stream_a=explode('.',$stream);
        $streamKey = $stream_a[0];
        $ext = isset($stream_a[1])?$stream_a[1]:'';

        //$live_code = $bizid . "_" .$streamKey;
        $live_code = $streamKey;
        $now_time = time() + 3*60*60;
        $txTime = dechex($now_time);

        $txSecret = md5($push_url_key . $live_code . $txTime);
        $safe_url = "&txSecret=" .$txSecret."&txTime=" .$txTime;

        if($type==1){
            //$push_url = "rtmp://" . $bizid . ".livepush2.myqcloud.com/live/" .  $live_code . "?bizid=" . $bizid . "&record=flv" .$safe_url;	可录像
            $url = "rtmp://{$push}/live/" . $live_code . "?bizid=" . $bizid . "" .$safe_url;
        }else{
            //$url = "http://{$pull}/live/" . $live_code . ".flv";
            //$url = "$host.://{$pull}/live/" . $live_code . ".flv";

            if(strpos($stream,'.flv')){
                $url = $host."://{$pull}/live/" . $live_code . ".flv";
            }else if(strpos($stream,'.m3u8')){
                $url = "";
            }else{
                $url = $host."://{$pull}/live/" . $live_code;
            }
        }

        return $url;
    }

    /**
     *  @desc 七牛云直播
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function get_qn($host,$stream,$type,$info){
        $ak=$info['accesskey'];
        $sk=$info['secretkey'];
        $hubName=$info['appname'];
        $push=$info['push'];
        $pull=$info['pull'];
        $stream_a=explode('.',$stream);
        $streamKey = $stream_a[0];
        $ext = $stream_a[1];

        if($type==1){
            $time=time() +60*60*10;
            //RTMP 推流地址
            $url = \Qiniu\Pili\RTMPPublishURL($push, $hubName, $streamKey, $time, $ak, $sk);
        }else{
            if($ext=='flv'){
                $pull=str_replace('pili-live-rtmp','pili-live-hdl',$pull);
                //HDL 直播地址
                $url = \Qiniu\Pili\HDLPlayURL($pull, $hubName, $streamKey);
            }else if($ext=='m3u8'){
                $pull=str_replace('pili-live-rtmp','pili-live-hls',$pull);
                //HLS 直播地址
                $url = \Qiniu\Pili\HLSPlayURL($pull, $hubName, $streamKey);
            }else{
                //RTMP 直播放址
                $url = \Qiniu\Pili\RTMPPlayURL($pull, $hubName, $streamKey);
            }
        }

        return $url;
    }

    /**
     *  @desc 网宿推拉流
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function get_ws($host,$stream,$type,$info){
        $push=$info['push'];
        $pull=$info['pull'];
        $appname = $info['appname'];
        if($type==1){
            $domain=$host.'://'.$push;
            //$time=time() +60*60*10;
        }else{
            $domain=$host.'://'.$pull;
            //$time=time() - 60*30 + $configpri['auth_length'];
        }

        $filename="/".$appname."/".$stream;

        $url=$domain.$filename;

        return $url;
    }

    /**网易cdn获取拉流地址**/
    public function get_wy($host,$stream,$type,$info){
        $appkey = $info['accesskey'];
        $appSecret = $info['secretkey'];
        $appname = $info['appname'];
        $nonce =rand(1000,9999);
        $curTime=time();
        $var=$appSecret.$nonce.$curTime;
        $checkSum=sha1($appSecret.$nonce.$curTime);

        $header =array(
            "Content-Type:application/json;charset=utf-8",
            "AppKey:".$appkey,
            "Nonce:" .$nonce,
            "CurTime:".$curTime,
            "CheckSum:".$checkSum,
        );
        if($type==1){
            $url='https://vcloud.163.com/app/channel/create';
            $paramarr = array(
                "name"  =>$stream,
                "type"  =>0,
            );
        }else{
            $url='https://vcloud.163.com/app/address';
            $paramarr = array(
                "cid"  =>$stream,
            );
        }
        $paramarr=json_encode($paramarr);

        $curl=curl_init();
        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_HEADER, 0);
        curl_setopt($curl,CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_POST, 1);
        curl_setopt($curl,CURLOPT_POSTFIELDS, $paramarr);
        $data = curl_exec($curl);
        curl_close($curl);
        $rs=json_decode($data,1);
        return $rs;
    }

    /**
     *  @desc 奥点云推拉流
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function get_ady($host,$stream,$type,$info){
        $push=$info['push'];
        $pull=$info['pull'];
        $appname = $info['appname'];
        $hls_pull = $info['hls_pull'];
        $stream_a=explode('.',$stream);
        $streamKey = $stream_a[0];
        $ext = $stream_a[1];

        if($type==1){
            $domain=$host.'://'.$push;
            //$time=time() +60*60*10;
            $filename="/".$appname.'/'.$stream;
            $url=$domain.$filename;
        }else{
            if($ext=='m3u8'){
                $domain=$host.'://'.$hls_pull;
                //$time=time() - 60*30 + $configpri['auth_length'];
                $filename="/".$appname."/".$stream;
                $url=$domain.$filename;
            }else if(strpos($stream,'.m3u8')){
                $url = "";
            }else{
                $domain=$host.'://'.$pull;
                //$time=time() - 60*30 + $configpri['auth_length'];
                $filename="/".$appname."/".$stream;
                $url=$domain.$filename;
            }
        }

        return $url;
    }

    /**
     * 自定义的srs流服务器鉴权
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function get_customSrs($host,$stream,$type,$info){
        $push=$info['push_url'];
        $pull=$info['pull_url'];
        $key_push=$info['push_key'];
        $key_pull=$info['pull_key'];
        $flv_pull=$info['flv_pull_url'];
        $appname = $info['appname'];
        $time=time();

        if($type==1){
            $domain=$host.'://'.$push;
        }else{
            if(strpos($stream,'.flv')){
                $domain=$host.'://'.$flv_pull;
            }
            else{
                $domain=$host.'://'.$pull;
            }

        }

        $filename="/".$stream;

        if($type==1){
            if($key_push!=''){
                $sstring = $filename."-".$time."-0-0-".$key_push;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
            if($auth_key){
                $auth_key='?'.$auth_key;
            }
            $url=$domain.$filename.$auth_key;
        }else{
            if($key_pull!=''){
                $sstring = $filename."-".$time."-0-0-".$key_pull;
                $md5=md5($sstring);
                $auth_key="auth_key=".$time."-0-0-".$md5;
            }
            if($auth_key){
                $auth_key='?'.$auth_key;
            }
            $url=$domain.$filename.$auth_key;
        }

        return $url;
    }

    /**
     *  @desc 青点云推拉流（copy 网宿推拉流）
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function get_qdy($host,$stream,$type,$info){

        $push=$info['push'];
        $pull=$info['pull'];
        $appname = $info['appname'];
        $appname = $appname ? $appname : 'mb';

        if($type==1){
            $url = $host.'://'.$push."/".$appname."/".$stream;
        }else{
            if(strpos($stream,'.flv')){
                $url = $host.'://'.$pull."/".$appname."/".$stream;
            }else if(strpos($stream,'.m3u8')){
                $url = $host.'://'.$pull."/".$appname."/".$stream;
            }else{
                $url = $host.'://'.$pull."/".$appname."/".$stream;
            }
        }

        return $url;
    }

    /**
     *  @desc 腾讯云推拉流地址 rtmps
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function get_tx_rtmps($host,$stream,$type,$info){
        $bizid=$info['bizid'];
        $push_url_key=$info['push_key'];
        $push=$info['push'];
        $pull=$info['pull'];
        $appname = $info['appname'];
        $stream_a=explode('.',$stream);
        $streamKey = $stream_a[0];
        $ext = isset($stream_a[1])?$stream_a[1]:'';

        //$live_code = $bizid . "_" .$streamKey;
        $live_code = $streamKey;
        $now_time = time() + 3*60*60;
        $txTime = dechex($now_time);

        $txSecret = md5($push_url_key . $live_code . $txTime);
        $safe_url = "&txSecret=" .$txSecret."&txTime=" .$txTime;

        if($type==1){
            if($host == 'rtmp'){ // 只有推流域名是rtpms，拉流还是rtpm
                $host = 'rtmps';
            }

            //$push_url = "rtmp://" . $bizid . ".livepush2.myqcloud.com/live/" .  $live_code . "?bizid=" . $bizid . "&record=flv" .$safe_url;	可录像
            $url = $host."://{$push}/live/" . $live_code . "?bizid=" . $bizid . "" .$safe_url;

//        $url = $host."://{$push}/live/" . $live_code; // 面跟了那些参数之后就推不上去了，跟参数只能使用rtmp推流
        }else{
            //$url = "http://{$pull}/live/" . $live_code . ".flv";
            //$url = "$host.://{$pull}/live/" . $live_code . ".flv";

            if(strpos($stream,'.flv')){
                $url = $host."://{$pull}/live/" . $live_code . ".flv";
            }else if(strpos($stream,'.m3u8')){
                $url = "";
            }else{
                $url = $host."://{$pull}/live/" . $live_code;
            }
        }

        return $url;
    }

    /**
     *  @desc 声网的推拉流
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
     */
    public function get_sw($host,$stream,$type,$info){
        $push_url_key=$info['push_key'];
        $push=$info['push'];
        $pull=$info['pull'];
        $appname = $info['appname'];
        $stream_a=explode('.',$stream);
        $streamKey = $stream_a[0];

        $live_code = $streamKey;
        $valid_time = time() + 3*60*60;
        $token = md5($live_code . $valid_time . $push_url_key);

        if($type==1){
            $url = $host."://{$push}/live/" . $live_code;
        }else{
            if(strpos($stream,'.flv')){
                $url = $host."://{$pull}/live/" . $live_code . ".flv";
            }else if(strpos($stream,'.m3u8')){
                $url = "";
            }else{
                $url = $host."://{$pull}/live/" . $live_code;
            }
        }

        return $url;
    }

    /**
     *  @desc 12群的推拉流
     *  @param string $host 协议，如:http、rtmp
     *  @param string $stream 流名,如有则包含 .flv、.m3u8
     *  @param int $type 类型，0表示播流，1表示推流
    500T以下0.3/G  500T-1P  0.25/G  1P以上0.2/G  前期先付一些跑通 后期上量在按P付
    token 计算说明
    md5(流的名称+鉴权有效期+鉴权key)
    其中鉴权有效期为unix epoch，超过这个时间即过期
    推流鉴权没有超时
    推流：
    rtmp://推流二级域名.主域名/live/流的名称?t=鉴权有效期&token=鉴权token
    播放：
    rtmp://拉流二级域名.主域名/live/流的名称?t=鉴权有效期&token=鉴权token
    http(s)://拉流二级域名.主域名/live/流的名称.flv?app=live&stream=流的名称&t=鉴权有效期&token=鉴权token
    http(s)://拉流二级域名.主域名/hls/流的名称.m3u8?t=鉴权有效期&token=鉴权token
     *
    wsTime  播放 URL 的有效时间  格式为16进制 UNIX 时间。
    KEY  MD5 计算方式的密钥  可以自定义。
    wsSecret  播放 URL 中的加密参数  值是通过将 key，URI，wsTime 依次拼接的字符串进行 MD5 加密算法得出。wsSecret = MD5（key+URI+wsTime）。
    有效时间  地址有效时间  有效时间设置必须大于0。假设 wsTime 设置为当前时间，有效时间设置为500s，则播放 URL 过期时间为当前时间 + 500s。
     *
    密文参数名称：wsSecret
    时间参数名称：wsABSTime
    KEY：ksdfmnkgfjerfgm
    加密时间格式：UNIX时间戳
    有效时间：按绝对时间
    密文组合方式：KEY+路径+时间
     */
    public function get_qun12($host,$stream,$type,$info){
        $push_url_key = $info['push_key'];
        $pull_key_key = $info['pull_key'];
        $push = $info['push'];
        $pull = $info['pull'];
        $appname = $info['appname'];
        $auth_status = $info['auth_status'];
        $stream_a = explode('.',$stream);
        $streamKey = $stream_a[0];

        $live_code = $streamKey;

        // 拉流的鉴权
        $URI = "/live/" . $live_code;
        $wsABSTime = time() + 60*60*3;
        $push_wsSecret = md5($push_url_key . $URI . $wsABSTime);
        $pull_wsSecret = md5($pull_key_key . $URI . $wsABSTime);

        if($type==1){
            $url = $host."://{$push}/live/" . $live_code;
            if($auth_status == 1 && $push_url_key){
                $url = $url."?wsSecret=" . $push_wsSecret . "&wsABSTime=" .$wsABSTime;
            }
        }else{
            if(strpos($stream,'.flv')){
                $url = $host."://{$pull}/live/" . $live_code . ".flv";
                if($auth_status == 1 && $pull_key_key){
                    $URI = "/live/" . $live_code . ".flv";
                    $pull_wsSecret = md5($pull_key_key . $URI . $wsABSTime);
                    $url = $url."?wsSecret=" . $pull_wsSecret . "&wsABSTime=" .$wsABSTime;
                }
            }else if(strpos($stream,'.m3u8')){
                $url = $host."://{$pull}/live/" . $live_code . ".m3u8";
                if($auth_status == 1 && $pull_key_key){
                    $URI = "/live/" . $live_code . ".m3u8";
                    $pull_wsSecret = md5($pull_key_key . $URI . $wsABSTime);
                    $url = $url."?wsSecret=" . $pull_wsSecret . "&wsABSTime=" .$wsABSTime;
                }
            }else{
                $url = $host."://{$pull}/live/" . $live_code;
                if($auth_status == 1 && $pull_key_key){
                    $url = $url."?wsSecret=" . $pull_wsSecret . "&wsABSTime=" .$wsABSTime;
                }
            }
        }

        return $url;
    }

}
