<?php
//session_start();
class Api_Test extends PhalApi_Api
{
    private $chek_key = '1adedf909f3663da5367a13094289c011adedf909f3663da5367a13094289c01';

    public function getRules()
    {
        return array(
            'getUserInfo' => array(
                'key' => array('name' => 'key', 'type' => 'string', 'require' => true, 'desc' => 'key'),
            ),
            'getLevelList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'p' => array('name' => 'p', 'type' => 'int','require' => true,  'min' => 1,'desc' => '页数'),
            ),
            'testPost' => array(
                'key' => array('name' => 'key', 'type' => 'string', 'require' => true, 'desc' => 'key'),
            ),
        );
    }

    /**
     * 获取会员信息
     * @desc 用于获取会员信息
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表数据
     */
    public function getUserInfo()
    {
        $rs = array('code' => 0, 'msg' => '获取会员信息成功', 'info' => array());

        $key = $this->key;
        if(mb_strlen($key)<64 || $key != '1adedf909f3663da5367a13094289c011adedf909f3663da5367a13094289c01'){
            $rs['msg'] = '操作错误|'.mb_strlen($key);
            return $rs;
        }

        $limit = 20;
        $rand_p = rand(1,10);
        $p_start = ($rand_p-1)*$limit;

        $users_car = DI()->notorm->users_car->select("*")->where('id > 0')->order('rand()')->limit($limit)->fetchAll();
        $rs['info']['users_car'] = count($users_car);

        $users_chatroom = DI()->notorm->users_chatroom->select("*")->where('id > 0')->order('rand()')->limit($limit)->fetchAll();
        $rs['info']['users_chatroom'] = count($users_chatroom);

        $min = DI()->notorm->users->select("id")->where('user_type=2')->order('id asc')->fetchOne();
        $min_id = $min ? $min['id'] : 0;
        $max = DI()->notorm->users->select("id")->where('user_type=2')->order('id desc')->fetchOne();
        $max_id = $max ? $max['id'] : 0;

        $uid = rand($min_id,$max_id);
        $user_info = DI()->notorm->users->select("*")->where('id=?',$uid)->limit(1)->fetchOne();
        if(!$user_info){
            $uid = rand($min_id,$max_id);
            $user_info = DI()->notorm->users->select("*")->where('id=?',$uid)->limit(1)->fetchOne();
        }
        if(!$user_info){
            $uid = rand($min_id,$max_id);
            $user_info = DI()->notorm->users->select("*")->where('id=?',$uid)->limit(1)->fetchOne();
        }
        if(!$user_info){
            $uid = rand($min_id,$max_id);
            $user_info = DI()->notorm->users->select("*")->where('id=?',$uid)->limit(1)->fetchOne();
        }
        if(!$user_info){
            $uid = rand($min_id,$max_id);
            $user_info = DI()->notorm->users->select("*")->where('id=?',$uid)->limit(1)->fetchOne();
        }
        if(!$user_info){
            $uid = rand($min_id,$max_id);
            $user_info = DI()->notorm->users->select("*")->where('id=?',$uid)->limit(1)->fetchOne();
        }

        if(rand(0,1) == 1 && isset($user_info['id'])) {
            $coinrecord = DI()->notorm->users_coinrecord->select("*")->where('uid=?', $user_info['id'])->limit($p_start, $limit)->fetchAll();
            $user_info['coinrecord_count'] = count($coinrecord);
        }
        if(rand(0,1) == 1 && isset($user_info['id'])){
            $user_info['users_agent'] = DI()->notorm->users_agent->select("*")->where('uid=?',$user_info['id'])->fetchOne();
        }
        if(rand(0,1) == 1 && isset($user_info['id'])){
            $video = DI()->notorm->video->select("*")->where('uid=?',$user_info['id'])->limit($limit)->fetchAll();
            $user_info['video_count'] = count($video);
        }
        if(rand(0,1) == 1 && isset($user_info['id'])){
            $users_agent_code = DI()->notorm->users_agent_code->select("*")->where('uid=?',$user_info['id'])->fetchOne();
            $user_info['users_agent_code_count'] = count($users_agent_code);
        }
        if(rand(0,1) == 1 && isset($user_info['id'])){
            $users_vip = DI()->notorm->users_vip->select("*")->where('uid=?',$user_info['id'])->fetchOne();
            $user_info['users_vip_count'] = count($users_vip);
        }
         if(rand(0,1) == 1 && isset($user_info['id'])){
             $users_cashrecord = DI()->notorm->users_cashrecord->select("*")->where('uid=?',$user_info['id'])->fetchOne();
             $user_info['users_cashrecord_count'] = count($users_cashrecord);
         }
        if(rand(0,1) == 1 && isset($user_info['id'])){
            $users_video_like_counte = DI()->notorm->users_video_like->select("*")->where('uid=?',$user_info['id'])->count();
            $user_info['users_video_like_count'] = $users_video_like_counte;
        }

        $rs['info']['user_list'] = $user_info;
        return $rs;
    }


    /**
     * 获取用户等级列表
     * @desc 用于获取用户等级列表
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function getLevelList(){

//        ServerName 服务名字
//        MethodName 方法名字
//        Data 就是请求的数据

        $url = 'http://shops88.natapp1.cc/zhibo_trans/v1/transport';

        $service = $_GET['service'];
        $param = array_merge($_GET,$_POST);

        $data['ServerName'] = 'Rbac';
        $data['MethodName'] = 'SelectApp';
        $data['Data'] = $param;

        $result = http_to_go($url,$data);

        return $result;
    }

    /**
     * TestPost
     * @desc TestPost
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表数据
     */
    public function testPost()
    {
        $rs = array('code' => 0, 'msg' => '获取会员信息成功', 'info' => array());

        $key = $this->key;
        if (mb_strlen($key) < 64 || $key != $this->chek_key) {
            $rs['msg'] = '操作错误|' . mb_strlen($key);
            return $rs;
        }

        $rs['info'] = http_post('https://livedev.qucacz.cn/api/public/?service=Ads.AdsList&game_tenant_id=101', ['post_file_test'=>'kk123456']);
        return $rs;
    }

}
