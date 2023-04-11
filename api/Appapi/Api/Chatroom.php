<?php
//session_start();
class Api_Chatroom extends PhalApi_Api
{

    public function getRules()
    {
        return array(
            'createChatRoom' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'title' => array('name' => 'title', 'type' => 'string', 'require' => true, 'desc' => '聊天室名称'),
                'avatar' => array('name' => 'avatar', 'type' => 'string', 'require' => true, 'desc' => '聊天室图标（url）'),
            ),
            'editChatRoom' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
                'title' => array('name' => 'title', 'type' => 'string', 'require' => false, 'desc' => '聊天室名称'),
                'avatar' => array('name' => 'avatar', 'type' => 'string', 'require' => false, 'desc' => '聊天室图标（url）'),
            ),
            'addRoomFriends' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
                'friend_id' => array('name' => 'friend_id', 'type' => 'string', 'require' => true, 'desc' => '会员ID（多个会员用英文逗号 , 分隔）'),
            ),
            'getChatRooms' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getRoomFriends' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
            ),
            'setRoomManager' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室房ID'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true, 'desc' => '类型，1设置为普通会员，2设置为管理员	'),
                'friend_id' => array('name' => 'friend_id', 'type' => 'string', 'require' => true, 'desc' => '会员ID（多个会员用英文逗号 , 分隔）'),
            ),
            'kickoutRoomFriend' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
                'friend_id' => array('name' => 'friend_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室会员ID'),
            ),
            'muteRoomFriend' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true, 'desc' => '禁言类型，0全部，1单个用户'),
                'status' => array('name' => 'status', 'type' => 'int', 'require' => true, 'desc' => '状态，0禁言，1正常'),
                'friend_id' => array('name' => 'friend_id', 'type' => 'int', 'desc' => '聊天室会员ID（禁言类型 type 为 1 时必须）'),
            ),
            'getRoomRecord' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
                'record_id' => array('name' => 'record_id', 'type' => 'int', 'require' => false, 'default'=>'0', 'desc' => '聊天记录id（值为0，代表最新记录id）'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => false, 'default'=>1, 'desc' => '类型（record_id不为0时必须）：<br>1 获取record_id之前的记录，2 获取record_id之后的记录'),
                'limit' => array('name' => 'limit', 'type' => 'int', 'require' => false, 'default'=>'15','desc' => '聊天记录条数（可选5 - 100）'),
            ),
            'sendMsg' => array(
                'game_tenant_id' => array('name' => 'game_tenant_id', 'min' => 1, 'max' => 30, 'require' => false, 'desc' => '游戏系统租户id'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'execute_uid' => array('name' => 'execute_uid', 'type' => 'string', 'require' => false, 'desc' => '被执行用户ID （<br>邀请人、踢人、单个禁言、解除单个禁言 必须，<br>邀请人是多个会员用英文逗号 , 分隔）'),
                'cont' => array('name' => 'cont', 'type' => 'string', 'require' => true, 'default'=>'ChatRoom', 'desc' => '固定不变'),
                'type' => array('name' => 'type', 'type' => 'string', 'require' => true, 'default'=>'send_msg', 'desc' => '固定不变'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
                'act_type' => array('name' => 'act_type', 'type' => 'int', 'require' => true, 'desc' => '操作类型（同返回结果的act_type参数,<br>进入聊天室后发送一条消息：act_type为108，<br>当断开重连后发送一条消息：act_type为112 ）'),
                'ct' => array('name' => 'ct', 'type' => 'string', 'require' => true,'desc' => '内容'),
                'ct_img' => array('name' => 'ct_img', 'type' => 'string', 'default'=>'','desc' => '图片内容'),
                'ct_bank' => array('name' => 'ct_bank', 'type' => 'string', 'default'=>'','desc' => '银行卡内容'),
            ),
            'heartBeat' => array(
                'game_tenant_id' => array('name' => 'game_tenant_id', 'min' => 1, 'max' => 30, 'require' => false, 'desc' => '游戏系统租户id'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'cont' => array('name' => 'cont', 'type' => 'string', 'require' => true, 'default'=>'ChatRoom', 'desc' => '固定不变'),
                'type' => array('name' => 'type', 'type' => 'string', 'require' => true, 'default'=>'heart_beat', 'desc' => '固定不变'),
            ),
            'searchFriend' => array(
                'game_tenant_id' => array('name' => 'game_tenant_id', 'min' => 1, 'max' => 30, 'require' => false, 'desc' => '游戏系统租户id'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
                'friend' => array('name' => 'friend', 'type' => 'string', 'require' => true, 'desc' => '会员名称或者会员ID'),
            ),
            'getOrdinaryMember' => array(
                'game_tenant_id' => array('name' => 'game_tenant_id', 'min' => 1, 'max' => 30, 'require' => false, 'desc' => '游戏系统租户id'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => true, 'desc' => '聊天室ID'),
            ),
        );
    }

    /**
     * 创建聊天室
     * @desc 用于创建聊天室
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 聊天室信息
     * @return int info.room_id 聊天室ID
     * @return string info.title 聊天室名称
     * @return string info.avatar 聊天室图标
     * @return string info.chatserver 聊天室socket地址
     * @return string info.chat_list_server 聊天室列表socket地址
     */
    public function createChatRoom()
    {
        $rs = array('code' => 0, 'msg' => '创建聊天室成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $title = checkNull($this->title);
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
        $info = $domain->createChatRoom($uid,$title,$avatar);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 修改聊天室信息
     * @desc 用于聊天室信息（聊天室名称或聊天室图标）
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     */
    public function editChatRoom()
    {
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $room_id = $this->room_id;
        $title = checkNull($this->title);
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
        $info = $domain->editChatRoom($uid,$room_id,$title,$avatar);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 添加会员
     * @desc 用于把下线添加进入聊天室
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息 current_num
     * @return int info.num 成员数量限制（code为2019时返回）
     * @return int info.current_num 当前成员数量（code为2019时返回）
     * @return int info.num 会员数量限制（code为2019时返回）
     * @return int info.current_num 当前会员数量（code为2019时返回）
     */
    public function addRoomFriends()
    {
        $rs = array('code' => 0, 'msg' => '添加成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $room_id = $this->room_id;
        $friend_id = checkNull($this->friend_id);
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
        $info = $domain->addRoomFriends($uid,$room_id,$friend_id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取聊天室列表
     * @desc 用于获取聊天室列表数据
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info.0.room_id 聊天室ID
     * @return string info.0.title 聊天室名称
     * @return string info.0.avatar 聊天室图标
     * @return array info.0.friends 聊天室会员列表
     * @return int info.0.friends.0.id 聊天室会员ID
     * @return string info.0.friends.0.user_nicename 聊天室会员名称
     * @return string info.0.friends.0.avatar_thumb 聊天室会员头像
     * @return int info.0.friends.0.type 聊天室会员类型：1 普通会员，2 管理员, 3房主
     */
    public function getChatRooms()
    {
        $rs = array('code' => 0, 'msg' => '聊天室列表', 'info' => array());

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
        $info = $domain->getChatRooms($uid);

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
     * @return string info[0].chatserver socket地址
     * @return array info[0].lord_info 聊天室房主信息
     * @return array info[0].manager_id 聊天室管理员列表
     * @return array info[0].mute_id 聊天室禁言会员列表
     * @return array info[0].friend_id 聊天室所有会员列表
     * @return array info[0].room_info 聊天室数据
     * @return int info[0].room_info.room_id 聊天室ID
     * @return string info[0].room_info.title 聊天室名称
     * @return string info[0].room_info.avatar 聊天室图标
     * @return int info[0].room_info.status 聊天室状态：0全部禁言，1正常
     * @return int info[0].room_info.current_u_type 当前会员类型: 1 普通会员, 2 管理员, 3 房主
     * @return int info[0].room_info.current_u_status 当前会员状态: 0禁言，1正常
     */
    public function getRoomFriends()
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
        $info = $domain->getRoomFriends($uid,$room_id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 设置管理员
     * @desc 用于聊天室设置管理员
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     */
    public function setRoomManager()
    {
        $rs = array('code' => 0, 'msg' => '设置成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $room_id = $this->room_id;
        $type = $this->type;
        $friend_ids = checkNull($this->friend_id);
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
        $info = $domain->setRoomManager($uid,$room_id,$type,$friend_ids);
        if($info==2018){
            $rs['code'] = 2018;
            $rs['msg'] = '没有权限';
            return $rs;
        }
        if($info==2022){
            $rs['code'] = 2022;
            $rs['msg'] = '管理员只能操作普通成员';
            return $rs;
        }
        if($info==2023){
            $rs['code'] = 2023;
            $rs['msg'] = '不能把群主设置为其他类型';
            return $rs;
        }
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 踢出会员
     * @desc 用于聊天室踢出会员
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     */
    public function kickoutRoomFriend()
    {
        $rs = array('code' => 0, 'msg' => '踢出会员成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $room_id = $this->room_id;
        $friend_id = $this->friend_id;
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
        $info = $domain->kickoutRoomFriend($uid,$room_id,$friend_id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 设置禁言
     * @desc 用于聊天室内设置禁言
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     */
    public function muteRoomFriend()
    {
        $rs = array('code' => 0, 'msg' => '禁言设置成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $room_id = $this->room_id;
        $type = $this->type;
        $status = $this->status;
        $friend_id = $this->friend_id;
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
        $info = $domain->muteRoomFriend($uid,$room_id,$type,$friend_id,$status);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
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
    public function getRoomRecord()
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
        $info = $domain->getRoomRecord($uid,$room_id,$record_id,$type,$limit);

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
     * @return string ct 内容
     * @return int act_type 操作类型:<br> 101文字发言, 102踢人, 103加人, 104单个禁言, 105解除单个禁言, 106全体禁言, 107解除全体禁言,<br> 108进群, 109出群, 110禁聊党政相关, 111退出了群组, 112断开重连, 113设置管理员, 114移除管理员,<br>115修改聊天记录status的值，只针对私密聊天, 116图片发言, 117银行卡发言,
     * @return int code 状态码:<br> 0发言，2039踢出了，2040邀请了，2041禁言了，2042解除了禁言，2043发起全体禁言，2044解除全体禁言，<br>2045进入，2046离开，2047注意：聊天室内禁止聊党政相关的话题！，2048退出了，2049断开重连<br>，2050设置管理员, 2051移除管理员
     */
    public function sendMsg(){
        return false;
    }

    /**
     * 聊天室socket心跳监听
     * @desc 用于聊天室socket心跳监听
     */
    public function heartBeat(){
        return false;
    }

    /**
     * 查询聊天室会员
     * @desc 用于查询聊天室会员
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info.room_id 聊天室ID
     * @return int info.friend_id 会员ID
     * @return string info.user_nicename 会员名称
     * @return string info.room_info.avatar 聊天室图标
     */
    public function searchFriend()
    {
        $rs = array('code' => 0, 'msg' => '查询聊天室会员成功', 'info' => array());

        $uid = $this->uid;
        $room_id = $this->room_id;
        $friend = $this->friend;
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
        $info = $domain->searchFriend($uid,$room_id,$friend);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取聊天室普通会员
     * @desc 用于获取聊天室普通会员
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info.0.friend_id 会员ID
     * @return string info.0.user_nicename 会员名称
     * @return string info.0.avatar_thumb 会员头像
     */
    public function getOrdinaryMember()
    {
        $rs = array('code' => 0, 'msg' => '获取聊天室普通会员成功', 'info' => array());

        $uid = $this->uid;
        $room_id = $this->room_id;
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
        $info = $domain->getOrdinaryMember($uid,$room_id);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }


}
