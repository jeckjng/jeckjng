<?php

class  Api_PrivateChatRoom extends PhalApi_Api
{

    public function getRules()
    {

        return array(
            'createPrivateChatRoom' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'avatar' => array('name' => 'avatar', 'type' => 'string', 'require' => true, 'desc' => '聊天室图标（url）'),
            ),
            'createPrivateChatRoomP'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'puid' => array('name' => 'puid', 'type' => 'int', 'require' => true, 'desc' => '群主ID|代理客服ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                //'avatar' => array('name' => 'avatar', 'type' => 'string', 'require' => true, 'desc' => '聊天室图标（url）'),
            ),

            'getPrivateRoomFriends' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
            ),

            'getPrivateRoomRecord' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
                'record_id' => array('name' => 'record_id', 'type' => 'int', 'require' => false, 'default' => '0', 'desc' => '聊天记录id（值为0，代表最新记录id）'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => false, 'default' => 1, 'desc' => '类型（record_id不为0时必须）：<br>1 获取record_id之前的记录，2 获取record_id之后的记录'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'require' => false, 'default' => '15', 'desc' => '聊天记录条数（可选5 - 100）'),
            ),
            'sendMsg' => array(
                'game_tenant_id' => array('name' => 'game_tenant_id', 'min' => 1, 'max' => 30, 'require' => false, 'desc' => '游戏系统租户id'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'execute_uid' => array('name' => 'execute_uid', 'type' => 'string', 'require' => false, 'desc' => '被执行用户ID （<br>邀请人、踢人、单个禁言、解除单个禁言 必须，<br>邀请人是多个会员用英文逗号 , 分隔）'),
                'cont' => array('name' => 'cont', 'type' => 'string', 'require' => true, 'default' => 'ChatRoom', 'desc' => '固定不变'),
                'type' => array('name' => 'type', 'type' => 'string', 'require' => true, 'default' => 'send_msg', 'desc' => '固定不变'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
                'act_type' => array('name' => 'act_type', 'type' => 'int', 'require' => true, 'desc' => '操作类型（同返回结果的act_type参数,<br>进入聊天室后发送一条消息：act_type为108，<br>当断开重连后发送一条消息：act_type为112 ）'),
                'ct' => array('name' => 'ct', 'type' => 'string', 'require' => true, 'desc' => '内容'),
                'ct_img' => array('name' => 'ct_img', 'type' => 'string', 'default'=>'','desc' => '图片内容'),
                'ct_bank' => array('name' => 'ct_bank', 'type' => 'string', 'default'=>'','desc' => '银行卡内容'),
            ),
            'heartBeatPrivate' => array(
                'game_tenant_id' => array('name' => 'game_tenant_id', 'min' => 1, 'max' => 30, 'require' => false, 'desc' => '游戏系统租户id'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'cont' => array('name' => 'cont', 'type' => 'string', 'require' => true, 'default' => 'ChatRoom', 'desc' => '固定不变'),
                'type' => array('name' => 'type', 'type' => 'string', 'require' => true, 'default' => 'heart_beat', 'desc' => '固定不变'),
            ),

            'getPrivateRoomCount' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getPrivateChatRooms'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getPrivateChatRoomRecords'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getCustomerVideo'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'puid' => array('name' => 'puid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '代理线客服用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default'=>1, 'desc' => '页数'),
            ),
            'getUsersCustomerS'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getUsersCustomerList'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getSocketServer'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
        );
    }

    /**
     * 创建私密聊天室
     * @desc 用于创建聊天室
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 聊天室信息
     * @return int info.room_ids.0 聊天室ID
     * @return string info.avatar 聊天室图标
     * @return string info.chatserver 聊天室socket地址
     */
    public function createPrivateChatRoom()
    {
        $rs = array('code' => 0, 'msg' => '创建聊天室成功', 'info' => array());
        $uid = $this->uid;
        $token = checkNull($this->token);
        $avatar = checkNull($this->avatar);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Chatroom();
        $info = $domain->createPrivateChatRoom($uid,  $avatar);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 创建私密聊天室
     * @desc 用于创建聊天室
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 聊天室信息
     * @return int info.0.room_id 聊天室ID
     * @return string info.0.title 客服昵称|客服昵称
     * @return string info.0.avatar 聊天室图标
     * @return string info.0.chatserver 聊天室socket地址
     * @return int info.0.uid 房主ID
     * @return int info.0.puid 客服ID|群主ID
     */
    public function createPrivateChatRoomP()
    {
        $rs = array('code' => 0, 'msg' => '创建聊天室成功', 'info' => array());
        $uid = $this->uid;
        $puid=$this->puid;
        $token = checkNull($this->token);
       // $avatar = checkNull($this->avatar);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Chatroom();
        $info = $domain->createPrivateChatRoomP($uid,$puid);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取聊天室数据
     * @desc 用于获取聊天室数据
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return string info.chatserver socket地址
     * @return array info.lord_info 聊天室房主信息
     * @return array info.manager_id 聊天室管理员列表
     * @return array info.mute_id 聊天室禁言会员列表
     * @return array info.friend_id 聊天室所有会员列表
     * @return array info.room_info 聊天室数据
     * @return int info.room_info.room_id 聊天室ID
     * @return string info.room_info.title 聊天室名称
     * @return string info.room_info.avatar 聊天室图标
     * @return int info.room_info.status 聊天室状态：0全部禁言，1正常
     * @return int info.room_info.current_u_type 当前会员类型: 1 客服, 3 房主
     * @return int info.room_info.current_u_status 当前会员状态: 0禁言，1正常
     */
    public function getPrivateRoomFriends()
    {
        $rs = array('code' => 0, 'msg' => '聊天室数据', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $room_id = $this->room_id;
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_Chatroom();
        $info = $domain->getRoomFriends($uid, $room_id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'][]= $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }



    /**
     * 获取聊天室聊天记录
     * @desc 用于获取聊天室聊天记录
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info.0.id 聊天记录ID
     * @return int info.0.uid 会员ID
     * @return string info.0.user_nicename 会员昵称
     * @return string info.0.avatar 会员头像
     * @return int info.0.execute_uid 被执行会员ID
     * @return string info.0.e_user_nicename 被执会员昵称
     * @return string info.0.e_avatar 被执会员头像
     * @return string info.0.ct 文字内容
     * @return string info.0.ct_img 图片内容
     * @return string info.0.ct_bank 银行卡内容
     * @return int info.0.time 聊天记录时间
     * @return string info.0.uniqid 聊天记录唯一标识
     * @return int info.0.act_type 操作类型:<br> 101发言, 102踢人, 103加人, 104单个禁言, 105解除单个禁言, 106全体禁言, 107解除全体禁言,<br> 108进群, 109出群, 110禁聊党政相关, 111退出了群组, 112断开重连, 113设置管理员, 114移除管理员,<br>115修改聊天记录status的值,只针对私密聊天, 116图片发言, 117银行卡发言,
     * @return int info.0.code 状态码:<br> 0发言，2039踢出了，2040邀请了，2041禁言了，2042解除了禁言，2043发起全体禁言，2044解除全体禁言，<br>2045进入，2046离开，2047注意：聊天室内禁止聊党政相关的话题！，2048退出了，2049断开重连<br>，2050设置管理员, 2051移除管理员, 2052修改聊天记录status的值,只针对私密聊天, 2053图片发言, 2054银行卡发言
     */
    public function getPrivateRoomRecord()
    {
        $rs = array('code' => 0, 'msg' => '聊天室聊天记录', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $room_id = $this->room_id;
        $record_id = isset($this->record_id) ? $this->record_id : 0;
        $type = $this->type;
        $limit = $this->limit;

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_Chatroom();
        $info = $domain->getRoomRecord($uid, $room_id, $record_id, $type, $limit);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 聊天室socket发言
     * @desc 用于聊天室socket发言
     * @return int uid 会员ID
     * @return string user_nicename 会员昵称
     * @return string avatar 会员头像
     * @return int execute_uid 被执行会员ID
     * @return string e_user_nicename  被执会员昵称
     * @return string e_avatar 被执会员头像
     * @return string ct 文字内容
     * @return string ct_img 图片内容
     * @return string ct_bank 银行卡内容
     * @return int time 聊天记录时间
     * @return string uniqid 聊天记录唯一标识
     * @return int act_type 操作类型:<br> 101发言, 102踢人, 103加人, 104单个禁言, 105解除单个禁言, 106全体禁言, 107解除全体禁言,<br> 108进群, 109出群, 110禁聊党政相关, 111退出了群组, 112断开重连, 113设置管理员, 114移除管理员,<br>115修改聊天记录status的值,只针对私密聊天, 116图片发言, 117银行卡发言,
     * @return int code 状态码:<br> 0发言，2039踢出了，2040邀请了，2041禁言了，2042解除了禁言，2043发起全体禁言，2044解除全体禁言，<br>2045进入，2046离开，2047注意：聊天室内禁止聊党政相关的话题！，2048退出了，2049断开重连<br>，2050设置管理员, 2051移除管理员, 2052修改聊天记录status的值,只针对私密聊天, 2053图片发言, 2054银行卡发言
     */
    public function sendMsg()
    {
        return false;
    }

    /**
     * 聊天室socket心跳监听
     * @desc 用于聊天室socket心跳监听
     */
    public function heartBeatPrivate()
    {
        return false;
    }

    /**
     *私密聊天列表
     *
     * @desc 私密聊天列表
     * @return int code 操作码，0表示成功 200创建聊天室 400没有上级
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info.room_id 聊天室ID
     * @return string info.title 聊天室名称
     * @return string info.avatar 聊天室图标
     * @return interesting info.record_len 未读记录
     * @return string info.record_last 最近的一条聊天记录
     * @return string info.0.record_time 时间
     * @return array info.friends 聊天室会员列表
     * @return int info.friends.0.id 聊天室会员ID
     * @return string info.friends.0.user_nicename 聊天室会员名称
     * @return string info.friends.0.avatar_thumb 聊天室会员头像
     * @return int info.friends.0.type 聊天室会员类型：1 普通会员，2 管理员, 3房主
     **/

    public function getPrivateChatRooms()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid = $this->uid;
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Chatroom();
        $rs=$domain->getPrivateChatRooms($uid);//获取上级用户的私密房间号
        return $rs;
    }

    /**
     *私密未读小计
     *
     * @desc 私密未读小计
     * @return int code 操作码，0表示成功 200创建聊天室 400没有上级
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return interesting info.0.record_len 小计
     **/

    public function getPrivateChatRoomRecords()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid = $this->uid;
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Chatroom();
        $rs=$domain->getPrivateChatRoomRecords($uid);
        return  $rs;
    }

    /**
     *代理线客服列表
     *
     * @desc 代理线客服列表
     * @return int code 操作码，0表示成功 200创建聊天室 400没有上级
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return  int info.0.puid 代理线客服ID
     * @return string info.0.nicename 代理线客服昵称
     * @return  string info.0.title 代理线封面标题
     * @return  string info.0.avatar 代理线封面图标
     **/

    public function getUsersCustomerS()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid = $this->uid;
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Chatroom();
        $rs=$domain->getUsersCustomerS($uid);
        return  $rs;
    }

    /**
     * 获取客服会员所有视频
     * @desc 获取客服会员所有视频
     * @return int code 操作码，0表示成功，1000表示视频不存在
     * @return array userinfo  会员信息（头像，昵称等等等）
     * @return array info[0] 视频详情
     * @return object info[0].uid   会员id
     * @return object info[0].title  视频标题
     * @return string info[0].id 视频id
     * @return string info[0].href   m3u8地址
     * @return string info[0].download_address 视频下载地址
     * @return string info[0].label 视频标签
     * @return string info[0].comments  评论数
     * @return string info[0].likes   喜欢数
     * @return string info[0].is_likes  自己喜欢数
     * @return string info[0].is_collection  喜欢数
     * @return string info[0].is_follow  收藏数
     * @return string info[0].is_download  下载数
     * @return string info[0].thumb 封面地址
     * @return string msg 提示信息
     * @return string count 返回标签下面的视频数量信息
     * @return string label  对应的标签名称
     * @return string labelnums 标签下视频数量
     */
    public  function getCustomerVideo()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid = $this->uid;
        $puid=$this->puid;
        $p=$this->p;
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_Chatroom();
        $rs=$domain->getCustomerVideo($puid,$uid,$p);
        $info['count']=$rs[1];
        $info['info']=$rs[0];
        $info['code']=0;
        return  $info;
    }

    /**
     * 私密聊天客服列表
     * @desc 私密聊天客服列表
     * @return  int code 操作码，0表示成功 200创建聊天室 400没有上级
     * @return  string msg 提示信息
     * @return  array info 列表数据
     * @return  int info.0.puid 代理线客服ID
     * @return  string info.0.user_nicename 代理线客服昵称
     * @return  string info.0.avatar 头像
     * @return  int info.0.record_len 未读信息统计
     * @return  string info.0.record_last 最近一条记录
     * @return  string info.0.record_time 时间
     */
    public function getUsersCustomerList()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid = $this->uid;
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_Chatroom();
        $rs=$domain->getUsersCustomerList($uid);
        return  $rs;
    }

    /**
     * 获取socket地址
     * @desc 获取socket地址
     * @return  int code 操作码，0表示成功 200创建聊天室 400没有上级
     * @return  string msg 提示信息
     * @return  array info 列表数据
     * @return string info.0.chatserver 聊天室socket地址
     * @return string info.0.chat_list_server 聊天室列表socket地址
     **/
    public function getSocketServer()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());
        $uid = $this->uid;
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $configpri= getConfigPri();
        $res['chatserver'] = $configpri['chatroomserver'];
        $res['chat_list_server'] = $configpri['chat_list_server'];

        // 判断是否使用golang的socket
        if($configpri['chatroom_socket_type'] == 3 && $configpri['go_chat_room_url'] && $configpri['go_chat_list_url']){
            $res['chatserver'] = $configpri['go_chat_room_url'];
            $res['chat_list_server'] = $configpri['go_chat_list_url'];
        }

        return  ['code'=>0,'msg'=>'','info'=>[$res]];
    }
}
