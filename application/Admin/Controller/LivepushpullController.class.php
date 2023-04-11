<?php

/**
 * 直播记录
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class LivepushpullController extends AdminbaseController {

    protected $pushpull_list = array(
        '1' => '阿里云',
        '2' => '腾讯云',
        '3' => '七牛云',
        '4' => '网宿',
        '5' => '网易',
        '6' => '奥点云',
        '7' => '自建SRS',
        '8' => '青点云',
        '9' => '腾讯云(rtmps)',
        '10' => '声网',
        '11' => '12群',
    );
    protected $status_list = array(
        '0' => '关闭',
        '1' => '启用',
    );

    public function index(){
        $redis = connectionRedis();
        $param = I('param.');
        foreach ($param as $key=>$val){
            $_GET[$key] = trim($val);
            $param[$key] = trim($val);
        }
        $map = array();
        if(isset($param['name']) && $param['name']){
            $map['name'] = $param['name'];
        }
        if(isset($param['code']) && $param['code']){
            $map['code'] = $param['code'];
        }
        if(isset($param['ct_type']) && $param['ct_type']){
            $map['ct_type'] = $param['ct_type'];
        }
        if(isset($param['status']) && $param['status'] != '100'){
            $map['status'] = $param['status'];
        }else{
            $param['status'] = '100';
        }

        $count = M('livepushpull')->where($map)->count();
    	$page = $this->page($count, 20);
    	$lists = M('livepushpull')
                    ->where($map)
                    ->order("status desc,ct_type asc,id desc")
                    ->limit($page->firstRow . ',' . $page->listRows)
                    ->select();

    	$status_list = $this->status_list;
        foreach($lists as $key=>$val){
            $userinfo = getUserInfo($val['act_uid']);
            $lists[$key]['act_uid'] = $userinfo['user_login'];
            $lists[$key]['status_name'] = isset($status_list[$val['status']]) ? $status_list[$val['status']] : $val['status'];

            $lists[$key]['play_url'] = $val['pull'] ? $val['pull'] : $val['pull_url'];
            $lists[$key]['play_url_rows'] = count(explode("\n",$lists[$key]['play_url']));
            $lists[$key]['today_usenum'] = $redis->get('pushpull_usenum_'.date('Ymd').$val['id']);
            $lists[$key]['today_usenum'] = $lists[$key]['today_usenum'] ? $lists[$key]['today_usenum'] : 0;
        }

    	$this->assign('lists', $lists);
        $this->assign('ct_type_list', ct_type_list());
        $this->assign('status_list', $status_list);
        $this->assign('pushpull_list', $this->pushpull_list);
        $this->assign('param', $param);
    	$this->assign("page", $page->show('Admin'));
        $this->display();
    }

    /*
     * 新增
     * */
    public function add(){
        if(IS_POST){
            $param = I('post.');
            foreach ($param as $key=>$val){
                $param[$key] = trim($val);
            }
            if(!$param['code']){
                $this->error('请选择推拉流');
            }
            if(!$param['name']){
                $this->error('推拉流名称不能为空');
            }
            if(mb_strlen($param['name']) > 32){
                $this->error('推拉流名称长度不能超过32');
            }
            if($param['bypass_ratio'] < 0 || $param['bypass_ratio'] > 100){
                $this->error('分流比例错误，请输入范围：0 - 100');
            }

            if(M("livepushpull")->where(['name'=>$param['name']])->find()){
                $this->error('名称已存在');
            }

            try{
                $data['name'] = $param['name'];
                $data['code'] = intval($param['code']);
                $data['ct_type'] = intval($param['ct_type']);
                $data['push'] = $param['push'];
                $data['pull'] = $param['pull'];
                $data['appname'] = $param['appname'];
                $data['appid'] = $param['appid'];
                $data['bizid'] = $param['bizid'];
                $data['push_key'] = $param['push_key'];
                $data['push_length'] = intval($param['push_length']);
                $data['pull_key'] = $param['pull_key'];
                $data['pull_length'] = intval($param['pull_length']);
                $data['accesskey'] = $param['accesskey'];
                $data['secretkey'] = $param['secretkey'];
                $data['push_url'] = $param['push_url'];
                $data['pull_url'] = $param['pull_url'];
                $data['flv_pull_url'] = $param['flv_pull_url'];
                $data['hls_pull'] = $param['hls_pull'];
                $data['certificate'] = $param['certificate'];
                $data['auth_status'] = intval($param['auth_status']);
                $data['status'] = intval($param['status']);
                $data['bypass_ratio'] = intval($param['bypass_ratio']);
                $data['act_uid'] = get_current_admin_id();
                $data['ctime'] = time();

                if($data['pull']){
                    $arr_pull = explode("\n",$data['pull']);
                    $temp_pull = array();
                    foreach ($arr_pull as $key=>$val){
                        $val = trim($val);
                        if($val){
                           array_push($temp_pull,$val);
                        }
                    }
                    $data['pull'] = implode("\n",$temp_pull);
                }
                if($data['pull_url']){
                    $arr_pull_url = explode("\n",$data['pull_url']);
                    $temp_pull_url = array();
                    foreach ($arr_pull_url as $key=>$val){
                        $val = trim($val);
                        if($val){
                            array_push($temp_pull_url,$val);
                        }
                    }
                    $data['pull_url'] = implode("\n",$temp_pull_url);
                }
                if($data['flv_pull_url']){
                    $arr_flv_pull_url = explode("\n",$data['flv_pull_url']);
                    $temp_flv_pull_url = array();
                    foreach ($arr_flv_pull_url as $key=>$val){
                        $val = trim($val);
                        if($val){
                            array_push($temp_flv_pull_url,$val);
                        }
                    }
                    $data['flv_pull_url'] = implode("\n",$temp_flv_pull_url);
                }

                $res = M("livepushpull")->add($data);
            }catch (\Exception $e){
                $error_msg = $e->getMessage();
                setAdminLog('新增推拉流 失败，'.$error_msg);
                $this->error('操作失败');
            }
            delPushpullList(); // 清除推拉流多线路列表缓存
            setAdminLog('新增推拉流 成功【'.$param['name'].'】');
            $this->success('操作成功',U('index'));
        }

        $this->assign('ct_type_list', ct_type_list());
        $this->assign('pushpull_list', $this->pushpull_list);
        $this->display();
    }

    /*
     * 编辑
     * */
    public function edit(){
        if(IS_POST){
            $param = I('post.');
            foreach ($param as $key=>$val){
                $param[$key] = trim($val);
            }
            if(!$param['id']){
                $this->error('缺少id');
            }
            if(!$param['code']){
                $this->error('请选择推拉流');
            }
            if(!$param['name']){
                $this->error('推拉流名称不能为空');
            }
            if(mb_strlen($param['name']) > 32){
                $this->error('推拉流名称长度不能超过32');
            }
            if($param['bypass_ratio'] < 0 || $param['bypass_ratio'] > 100){
                $this->error('分流比例错误，请输入范围：0 - 100');
            }
            $id = $param['id'];
            unset($param['id']);

            if(M("livepushpull")->where(['id'=>['neq',$id],'name'=>$param['name']])->find()){
                $this->error('名称已存在');
            }

            try{
                $data['name'] = $param['name'];
                $data['code'] = intval($param['code']);
                $data['ct_type'] = intval($param['ct_type']);
                $data['push'] = $param['push'];
                $data['pull'] = $param['pull'];
                $data['appname'] = $param['appname'];
                $data['appid'] = $param['appid'];
                $data['bizid'] = $param['bizid'];
                $data['push_key'] = $param['push_key'];
                $data['push_length'] = intval($param['push_length']);
                $data['pull_key'] = $param['pull_key'];
                $data['pull_length'] = intval($param['pull_length']);
                $data['accesskey'] = $param['accesskey'];
                $data['secretkey'] = $param['secretkey'];
                $data['push_url'] = $param['push_url'];
                $data['pull_url'] = $param['pull_url'];
                $data['flv_pull_url'] = $param['flv_pull_url'];
                $data['hls_pull'] = $param['hls_pull'];
                $data['certificate'] = $param['certificate'];
                $data['auth_status'] = intval($param['auth_status']);
                $data['status'] = intval($param['status']);
                $data['bypass_ratio'] = intval($param['bypass_ratio']);
                $data['act_uid'] = get_current_admin_id();
                $data['mtime'] = time();

                if($data['pull']){
                    $arr_pull = explode("\n",$data['pull']);
                    $temp_pull = array();
                    foreach ($arr_pull as $key=>$val){
                        $val = trim($val);
                        if($val){
                            array_push($temp_pull,$val);
                        }
                    }
                    $data['pull'] = implode("\n",$temp_pull);
                }
                if($data['pull_url']){
                    $arr_pull_url = explode("\n",$data['pull_url']);
                    $temp_pull_url = array();
                    foreach ($arr_pull_url as $key=>$val){
                        $val = trim($val);
                        if($val){
                            array_push($temp_pull_url,$val);
                        }
                    }
                    $data['pull_url'] = implode("\n",$temp_pull_url);
                }
                if($data['flv_pull_url']){
                    $arr_flv_pull_url = explode("\n",$data['flv_pull_url']);
                    $temp_flv_pull_url = array();
                    foreach ($arr_flv_pull_url as $key=>$val){
                        $val = trim($val);
                        if($val){
                            array_push($temp_flv_pull_url,$val);
                        }
                    }
                    $data['flv_pull_url'] = implode("\n",$temp_flv_pull_url);
                }

                $res = M("livepushpull")->where(['id'=>intval($id)])->save($data);
            }catch (\Exception $e){
                $error_msg = $e->getMessage();
                setAdminLog('修改推拉流 失败，'.$error_msg);
                $this->error('操作失败');
            }
            delPushpullList(); // 清除推拉流多线路列表缓存
            setAdminLog('修改推拉流 成功【'.$param['name'].'】');
            $this->success('操作成功',U('index'));
        }
        if(!I('id')){
            $this->error('缺少参数');
        }
        $info = M("livepushpull")->where(['id'=>intval(I('id'))])->find();
        $info['pull_rows'] = count(explode("\n",$info['pull'])) + 1;
        $info['pull_url_rows'] = count(explode("\n",$info['pull_url'])) + 1;
        $info['flv_pull_url_rows'] = count(explode("\n",$info['flv_pull_url'])) + 1;

        $this->assign('info', $info);
        $this->assign('ct_type_list', ct_type_list());
        $this->assign('pushpull_list', $this->pushpull_list);
        $this->assign('id', I('id'));
        $this->display();
    }

    /*
     * 删除
     * */
    public function del(){
        if(!I('id')){
            $this->error('缺少参数');
        }

        try{
            $res = M("livepushpull")->where(['id'=>intval(I('id'))])->delete();
        }catch (\Exception $e){
            $error_msg = $e->getMessage();
            setAdminLog('删除推拉流 失败，'.$error_msg);
            $this->error('操作失败');
        }
        delPushpullList(); // 清除推拉流多线路列表缓存
        setAdminLog('删除推拉流 成功【'.I('name').'】');
        $this->success('操作成功',U('index'));
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
            $info = M("livepushpull")->where(['id'=>$id])->find();
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
        $appname = trim($info['appname']);
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
