<?php

/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_Chatroom extends PhalApi_Model_NotORM
{
    public function createChatRoom($uid, $title, $avatar)
    {
        $code = codemsg();
        //权限判断
        $isjurisdiction = DI()->notorm->users->select('isjurisdiction')->where("id=?", $uid)->fetchOne();
        if ($isjurisdiction['isjurisdiction'] != 1) {
            return array('code' => 2090, 'msg' => '未开启权限!', 'info' => array());
        }
        $vip = DI()->notorm->users_vip->select('id')->where('uid=?', $uid)->fetchOne();
        if (empty($vip['id'])) {
            return array('code' => 2091, 'msg' => '不是VIP', 'info' => array());
        }
        if (!$title) {
            return array('code' => 2037, 'msg' => $code['2037'], 'info' => array());
        }
        if (mb_strlen($title, 'utf-8') > 255) {
            return array('code' => 2036, 'msg' => $code['2036'], 'info' => array());
        }
        $title = $this->cust_unicode($title);
        $room_info = DI()->notorm->users_chatroom
            ->select("id,uid")
            ->where('uid=? and title=?', $uid, $title)
            ->fetchOne();
        if ($room_info) {
            return array('code' => 2029, 'msg' => $code['2029'], 'info' => array());
        }

        $data = array(
            'uid' => intval($uid),
            'title' => $title,
            'avatar' => $avatar,
            'addtime' => time(),
            'tenant_id' => getTenantId(),
            'act_uid' => intval($uid),
            'mtime' => time(),
            'chattime' => time(),
            'roomtype' => 0,
        );
        $res = DI()->notorm->users_chatroom->insert($data);
        if (isset($res['id'])) {
            $res['room_id'] = intval($res['id']);
            unset($res['id']);

            $data = array(
                'room_id' => intval($res['room_id']),
                'sub_uid' => intval($uid),
                'type' => 3, // 房主
                'status' => 1,
                'addtime' => time(),
                'tenant_id' => getTenantId(),
                'act_uid' => intval($uid),
                'mtime' => time(),
                'roomtype' => 0,
            );
            DI()->notorm->users_chatroom_friends->insert($data);
        }
        if (isset($res['addtime'])) {
            unset($res['addtime']);
        }
        if (isset($res['uid'])) {
            unset($res['uid']);
        }
        if (isset($res['tenant_id'])) {
            unset($res['tenant_id']);
        }
        if (isset($res['act_uid'])) {
            unset($res['act_uid']);
        }
        if (isset($res['mtime'])) {
            unset($res['mtime']);
        }

        // 清除用户所在的群聊房间room_ids缓存
        delChatRoomIdsCahche(getTenantId(), $uid);

        $configpri = getConfigPri();
        $res['chatserver'] = $configpri['chatroomserver'];
        $res['chat_list_server'] = $configpri['chat_list_server'];

        // 判断是否使用golang的socket
        if($configpri['chatroom_socket_type'] == 3 && $configpri['go_chat_room_url'] && $configpri['go_chat_list_url']){
            $res['chatserver'] = $configpri['go_chat_room_url'];
            $res['chat_list_server'] = $configpri['go_chat_list_url'];
        }

        $res['titile'] = rawurldecode($res['titile']);
        return array('code' => 0, 'msg' => '', 'info' => [$res]);
    }

    public function cust_unicode($str)
    {
        $strEncode = '';
        $length = mb_strlen($str, 'utf-8');
        for ($i = 0; $i < $length; $i++) {
            $_tmpStr = mb_substr($str, $i, 1, 'utf-8');
            if (strlen($_tmpStr) >= 4) {
                $strEncode .= rawurlencode($_tmpStr);
            } else {
                $strEncode .= $_tmpStr;
            }
        }
        return $strEncode;
    }

    public function editChatRoom($uid, $room_id, $title, $avatar)
    {
        $code = codemsg();
        if (mb_strlen($title, 'utf-8') > 255) {
            return array('code' => 2036, 'msg' => $code['2036'], 'info' => array());
        }
        $f_info = DI()->notorm->users_chatroom_friends->where(['sub_uid' => intval($uid), 'room_id' => intval($room_id)])->fetchOne();
        if (!in_array($f_info['type'], [2, 3])) {
            return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
        }


        if ($title) {
            $title = $this->cust_unicode($title);
            $data['title'] = $title;
            $is_exist = DI()->notorm->users_chatroom
                ->select("id,uid")
                ->where('uid=? and title=? and id<>?', $uid, $title, $room_id)
                ->fetchOne();
            if ($is_exist) {
                return array('code' => 2029, 'msg' => $code['2029'], 'info' => array());
            }
        }
        if ($avatar) {
            $data['avatar'] = $avatar;
        }
        $data['act_uid'] = intval($uid);
        $data['mtime'] = time();

        if (!isset($data)) {
            return array('code' => 2030, 'msg' => $code['2030'], 'info' => array());
        }
        $data['act_uid'] = intval($uid);
        $data['mtime'] = time();
        $res = DI()->notorm->users_chatroom->where('id=?', intval($room_id))->update($data);
        if ($res == 0) {
            return array('code' => 2034, 'msg' => $code['2034'], 'info' => array());
        }
        return array('code' => 0, 'msg' => '', 'info' => array());
    }

    public function addRoomFriends($uid, $room_id, $friend_id)
    {
        $code = codemsg();
        $f_info = DI()->notorm->users_chatroom_friends->where(['sub_uid' => intval($uid), 'room_id' => intval($room_id)])->fetchOne();
        if (!in_array($f_info['type'], [2, 3])) {
            return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
        }

        $friend_id = explode(',', $friend_id);
        $friend_id = array_unique($friend_id);

        $sub_list = DI()->notorm->users_chatroom_friends->select("id,sub_uid,status")->where(['room_id' => intval($room_id)])->fetchAll();
        $sub_list = array_column($sub_list, null, 'sub_uid');

        $config = DI()->notorm->users_chatroom_conf->fetchOne();
        if ($config['num'] < (count($friend_id) + count($sub_list))) {
            return array('code' => 2019, 'msg' => $code['2019'], 'info' => array('num' => intval($config['num']), 'current_num' => count($sub_list)));
        }

        if (count($sub_list) > 0) {
            foreach ($friend_id as $key => $val) {
                if (!$val) {
                    unset($friend_id[$key]);
                }
                if (isset($sub_list[$val])) {
                    if ($sub_list[$val]['status'] == 2) {
                        DI()->notorm->users_chatroom_friends->where(['id' => intval($sub_list[$val]['id'])])->update(['status' => 1]);
                    }
                    unset($friend_id[$key]);
                }
            }
        }

        foreach ($friend_id as $key => $val) {
            if ($val) {
                $data = array(
                    'room_id' => intval($room_id),
                    'sub_uid' => intval($val),
                    'type' => 1,
                    'addtime' => time(),
                    'tenant_id' => getTenantId(),
                    'act_uid' => intval($uid),
                    'mtime' => time(),
                );
                DI()->notorm->users_chatroom_friends->insert($data);
                // 清除用户所在的群聊房间room_ids缓存
                delChatRoomIdsCahche(getTenantId(), $val);
            }
        }

        // 清除用户所在的群聊房间room_ids缓存
        delChatRoomIdsCahche(getTenantId(), $uid);
        // 清除聊天室成员uids缓存
        delChatRoomUidsCahche(getTenantId(), $room_id);

        return array('code' => 0, 'msg' => '', 'info' => array());
    }

    public function getChatRooms($uid)
    {
        $room_ids = getChatRoomIds(getTenantId(), $uid);
        $room_list = DI()->notorm->users_chatroom->select("id as room_id,title,avatar")->where('id', $room_ids)->order('chattime desc,id desc')->fetchAll();

        $room_list = array_column($room_list, null, 'room_id');
        if (count($room_list) <= 0) {
            return array('code' => 0, 'msg' => '', 'info' => array());
        }

        $sub_list = DI()->notorm->users_chatroom_friends->select("room_id,sub_uid,type")->where('status!=2 and room_id', $room_ids)->order('sub_uid asc')->fetchAll();
        $sub_uids = array_unique(array_keys(array_column($sub_list, null, 'sub_uid')));

        $user_list = DI()->notorm->users->select("id,user_nicename,avatar_thumb")->where('id', $sub_uids)->fetchAll();
        $user_list = array_column($user_list, null, 'id');

        foreach ($sub_list as $key => $val) {
            $val['type'] = intval($val['type']);
            $temp = array();
            if ($room_list[$val['room_id']]) {
                $temp = $val;
                unset($temp['room_id']);
                unset($temp['sub_uid']);
            }
            if (isset($user_list[$val['sub_uid']])) {
                $temp = array_merge($user_list[$val['sub_uid']], $temp);
                $temp['id'] = intval($temp['id']);
                if (isset($temp['sub_uid'])) {
                    unset($temp['sub_uid']);
                }
            }
            $room_list[$val['room_id']]['friends'][] = $temp;
        }
        foreach ($room_list as $key => $val) {
            $room_list[$key]['room_id'] = intval($val['room_id']);
            $room_list[$key]['title'] = rawurldecode($val['title']);
            if (!isset($val['friends'])) {
                $room_list[$key]['friends'] = array();
            }
        }

        $room_list = array_values($room_list);
        return array('code' => 0, 'msg' => '', 'info' => $room_list);
    }

    public function getRoomFriends($uid, $room_id)
    {
        $code = codemsg();
        $info = array('room_info' => array(), 'lord_info' => array(), 'manager_id' => array(), 'mute_id' => array(), 'friend_id' => array());

        $f_info = DI()->notorm->users_chatroom_friends->where(['sub_uid' => intval($uid), 'room_id' => intval($room_id)])->fetchOne();
        if (isset($f_info['status']) && $f_info['status'] == 2) {
            return array('code' => 2033, 'msg' => $code['2033'], 'info' => array()); // 您已被踢，无权进入该房间
        }

        $info['room_info'] = DI()->notorm->users_chatroom->select("id as room_id,uid,title,avatar")->where('id=?', intval($room_id))->fetchOne();
        if (!$info['room_info']) {
            return array('code' => 2031, 'msg' => $code['2031'], 'info' => array());
        }

        $info['room_info']['room_id'] = intval($info['room_info']['room_id']);
        $info['room_info']['title'] = rawurldecode($info['room_info']['title']);

        $friends_list = DI()->notorm->users_chatroom_friends->select("sub_uid,type,status")->where('status!=2 and room_id=?', intval($room_id))->order('type desc,id desc')->fetchAll();
        $friends_uids = array_keys(array_column($friends_list, null, 'sub_uid'));
        if (!in_array($uid, $friends_uids)) {
            if ($info['room_info']['uid'] == $uid) {
                $data = array(
                    'room_id' => intval($room_id),
                    'sub_uid' => intval($uid),
                    'type' => 3, // 房主
                    'status' => 1,
                    'addtime' => time(),
                    'tenant_id' => getTenantId(),
                    'act_uid' => intval($uid),
                    'mtime' => time(),
                );
                DI()->notorm->users_chatroom_friends->insert($data);
                $friends_list = array(array('sub_uid' => intval($uid), 'type' => 3, 'status' => 1));
                $friends_uids = array($uid);
            } else {
                return array('code' => 2032, 'msg' => $code['2032'], 'info' => array());
            }
        }
        unset($info['room_info']['uid']);

        $user_list = DI()->notorm->users->select("id,user_nicename,avatar_thumb")->where('id', $friends_uids)->fetchAll();
        $info['friend_id'] = array_column($user_list, null, 'id');

        $info['room_info']['status'] = 0;
        foreach ($friends_list as $key => $val) {
            $val['type'] = intval($val['type']);
            $val['status'] = intval($val['status']);
            $temp = array('id' => '', 'user_nicename' => '', 'avatar_thumb' => '', 'type' => '');
            if (isset($info['friend_id'][$val['sub_uid']])) {
                $info['friend_id'][$val['sub_uid']]['id'] = intval($info['friend_id'][$val['sub_uid']]['id']);
                $info['friend_id'][$val['sub_uid']]['type'] = $val['type'];
                $info['friend_id'][$val['sub_uid']]['status'] = $val['status'];
                $temp = $info['friend_id'][$val['sub_uid']];
            }
            if ($val['type'] == 3) {
                $info['lord_info'] = $temp;
            } else {
                if ($val['status'] == 1) {
                    $info['room_info']['status'] = 1;
                }
            }
            if ($val['type'] == 2) {
                $info['manager_id'][] = $temp;
            }
            if ($info['room_info']['status'] == 1 && $val['status'] == 0) {
                $info['mute_id'][] = $temp;
            }
            if ($val['sub_uid'] == $uid) {
                $info['room_info']['current_u_type'] = $val['type']; // 当前会员类型
                $info['room_info']['current_u_status'] = $val['status'];
            }
        }
        $info['friend_id'] = array_values($info['friend_id']);

        $configpri = getConfigPri();
        $info['chatserver'] = $configpri['chatroomserver'];
        return array('code' => 0, 'msg' => '', 'info' => [$info]);
    }

    public function setRoomManager($uid, $room_id, $type, $friend_ids)
    {
        $code = codemsg();
        $f_info = DI()->notorm->users_chatroom_friends->where(['sub_uid' => intval($uid), 'room_id' => intval($room_id)])->fetchOne();
        if (!in_array($f_info['type'], [2, 3])) {
            return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
        }

        $friend_ids = explode(',', $friend_ids);
        foreach ($friend_ids as $key => $friend_id) {
            if ($uid == $friend_id) {
                return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
            }
            $friend_info = DI()->notorm->users_chatroom_friends->where(['sub_uid' => intval($friend_id), 'room_id' => intval($room_id)])->fetchOne();
            if ($f_info['type'] == 2 && $friend_info['type'] == 2) {
                return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
            }
            if ($friend_info['type'] == 3) {
                return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
            }
        }

        $res = DI()->notorm->users_chatroom_friends
            ->where('room_id=' . intval($room_id) . ' and sub_uid', $friend_ids)
            ->update(array("type" => intval($type), "act_uid" => intval($uid), "mtime" => time()));
        if ($res == 0) {
            return array('code' => 2034, 'msg' => $code['2034'], 'info' => array());
        }
        return array('code' => 0, 'msg' => '', 'info' => array());
    }

    public function kickoutRoomFriend($uid, $room_id, $friend_id)
    {
        $code = codemsg();
        if (!$room_id || !$friend_id) {
            return false;
        }
        if ($uid == $friend_id) {
            return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
        }
        $f_info = DI()->notorm->users_chatroom_friends->where(['sub_uid' => intval($uid), 'room_id' => intval($room_id)])->fetchOne();
        if (!in_array($f_info['type'], [2, 3])) {
            return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
        }
        $friend_info = DI()->notorm->users_chatroom_friends->where(['sub_uid' => intval($friend_id), 'room_id' => intval($room_id)])->fetchOne();
        if ($f_info['type'] == 2 && $friend_info['type'] == 2) {
            return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
        }
        if ($friend_info['type'] == 3) {
            return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
        }
        $res = DI()->notorm->users_chatroom_friends->where('room_id=? and sub_uid=?', $room_id, $friend_id)->update(['type' => 1, 'status' => 2, "act_uid" => intval($uid), "mtime" => time()]);

        if ($res == 0) {
            return array('code' => 2034, 'msg' => $code['2034'], 'info' => array());
        }

        // 清除聊天室成员uids缓存
        delChatRoomUidsCahche(getTenantId(), $room_id);

        return array('code' => 0, 'msg' => '', 'info' => array());
    }

    public function muteRoomFriend($uid, $room_id, $type, $friend_id, $status)
    {
        $code = codemsg();
        $u_info = DI()->notorm->users_chatroom_friends->where(['sub_uid' => intval($uid), 'room_id' => intval($room_id)])->fetchOne();
        if (!in_array($u_info['type'], [2, 3])) {
            return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
        }

        if ($type == 0) { // 全部禁言设置，仅房主可全体禁言设置
            if ($u_info['type'] != 3) {
                return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
            }
            $res = DI()->notorm->users_chatroom_friends
                ->where('room_id=? and sub_uid<>?', $room_id, $uid)
                ->update(array("status" => intval($status), "act_uid" => intval($uid), "mtime" => time()));
        } else if ($type == 1) { // 单个用户禁言设置
            if ($uid == $friend_id) {
                return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
            }
            $f_info = DI()->notorm->users_chatroom_friends->where(['sub_uid' => intval($friend_id), 'room_id' => intval($room_id)])->fetchOne();
            if ($u_info['type'] == 2 && $f_info['type'] == 2) {
                return array('code' => 2018, 'msg' => $code['2018'], 'info' => array()); // 您没有该权限
            }
            $res = DI()->notorm->users_chatroom_friends
                ->where('room_id=? and sub_uid=?', $room_id, $friend_id)
                ->update(array("status" => intval($status), "act_uid" => intval($uid), "mtime" => time()));
        }

        if ($res == 0) {
            return array('code' => 2034, 'msg' => $code['2034'], 'info' => array());
        }
        return array('code' => 0, 'msg' => ($status == 0 ? '禁言设置成功' : '解除禁言设置成功'), 'info' => array());
    }

    public function getRoomRecord($uid, $room_id, $record_id, $type, $limit)
    {
        $redis = connectionRedis();
        $limit = $limit >= 5 && $limit <= 100 ? $limit : 15;
        $type = $type ? $type : 1;

        $len = $redis->lLen('users_chatroom_record' . $room_id);
        if ($record_id > 0) {
            if ($type == 1) {
                $star = $record_id >= $limit ? ($record_id - $limit) : 0;
                $end = $record_id - 1;
            } else {
                $star = $record_id + 1;
                $end = $len >= ($record_id + $limit) ? ($record_id + $limit) : $len;
            }
            $record_list = $redis->lRange('users_chatroom_record' . $room_id, $star, $end);
        } else {
            $star = $len >= $limit ? ($len - $limit) : 0;
            $end = $len;
            $record_list = $redis->lRange('users_chatroom_record' . $room_id, $star, $end);
        }

        $list = array();
        foreach ($record_list as $key => $val) {
            $val = json_decode($val, true);
            unset($val['room_id']);
            unset($val['tenant_id']);
            $val['ct'] = rawurldecode($val['ct']);
            $val['ct_img'] = $val['ct_img'];
            $val['ct_bank'] = $val['ct_bank'];
            $val['id'] = $star;
            $val['time'] = $val['time'];
            $val['uniqid'] = $val['uniqid'];
            $u_list = DI()->notorm->users->select("id,user_nicename,avatar")->where('id', [$val['uid'], $val['execute_uid']])->fetchAll();
            $u_list = array_column($u_list, null, 'id');
            $val['user_nicename'] = isset($u_list[$val['uid']]) ? $u_list[$val['uid']]['user_nicename'] : '';
            $val['avatar'] = isset($u_list[$val['uid']]) ? $u_list[$val['uid']]['avatar'] : '';
            $val['e_user_nicename'] = isset($u_list[$val['execute_uid']]) ? $u_list[$val['execute_uid']]['user_nicename'] : '';
            $val['e_avatar'] = isset($u_list[$val['execute_uid']]) ? $u_list[$val['execute_uid']]['avatar'] : '';

            array_push($list, $val);
            $star++;
        }

        return array('code' => 0, 'msg' => '', 'info' => array_values($list));
    }

    public function searchFriend($uid, $room_id, $friend)
    {
        $code = codemsg();
        $user_info = DI()->notorm->users->where('id=? or user_nicename=?', $friend, $friend)->fetchOne();
        if (!isset($user_info['id'])) {
            return array('code' => 2035, 'msg' => $code['2035'], 'info' => array());
        }
        $is_exist = DI()->notorm->users_chatroom_friends->where('room_id=? and sub_uid=? and type=1 and status<>2', intval($room_id), intval($user_info['id']))->fetchOne();
        if (!$is_exist) {
            return array('code' => 2035, 'msg' => $code['2035'], 'info' => array());
        }

        $info['room_id'] = intval($room_id);
        $info['friend_id'] = intval($user_info['id']);
        $info['user_nicename'] = $user_info['user_nicename'];
        $info['avatar'] = $user_info['avatar'];
        return array('code' => 0, 'msg' => '', 'info' => $info);
    }

    public function getOrdinaryMember($uid, $room_id)
    {
        $code = codemsg();
        $list = DI()->notorm->users_chatroom_friends->select('sub_uid as friend_id')->where('room_id=? and type=1 and status<>2', intval($room_id))->fetchAll();
        $list = array_column($list, null, 'friend_id');
        if (!isset($list[$uid])) {
            $friend_info = DI()->notorm->users_chatroom_friends->where('room_id=? and sub_uid=?', intval($room_id), intval($uid))->fetchOne();
            if (!$friend_info) {
                return array('code' => 2034, 'msg' => $code['2034'], 'info' => array());
            }
        }
        $user_list = DI()->notorm->users->select("id,user_nicename,avatar_thumb")->where('id', array_keys($list))->fetchAll();
        $user_list = array_column($user_list, null, 'id');
        foreach ($list as $key => $val) {
            $list[$key]['friend_id'] = intval($val['friend_id']);
            if (isset($user_list[$val['friend_id']])) {
                $list[$key]['user_nicename'] = $user_list[$val['friend_id']]['user_nicename'];
                $list[$key]['avatar_thumb'] = $user_list[$val['friend_id']]['avatar_thumb'];
            }
        }
        return array('code' => 0, 'msg' => '', 'info' => array_values($list));
    }

    public function getPrivateChatRooms($uid)
    {
        $room_ids = getChatPrivateRoomIds(getTenantId(), $uid);
        $room_list = DI()->notorm->users_chatroom->select("id as room_id,title,avatar,roomtype")->where('id', $room_ids)->order('chattime desc,id desc')->fetchAll();
        $room_list = array_column($room_list, null, 'room_id');
        if (count($room_list) <= 0) {
            return array('code' => 0, 'msg' => '', 'info' => array());
        }

        $sub_list = DI()->notorm->users_chatroom_friends->select("room_id,sub_uid,type")->where('status!=2 and room_id', $room_ids)->order('sub_uid asc')->fetchAll();
        $sub_uids = array_unique(array_keys(array_column($sub_list, null, 'sub_uid')));

        $user_list = DI()->notorm->users->select("id,user_nicename,avatar_thumb")->where('id', $sub_uids)->fetchAll();
        $user_list = array_column($user_list, null, 'id');

        foreach ($sub_list as $key => $val) {
            $val['type'] = intval($val['type']);
            $temp = array();
            if ($room_list[$val['room_id']]) {
                $temp = $val;
                unset($temp['room_id']);
                unset($temp['sub_uid']);
            }
            if (isset($user_list[$val['sub_uid']])) {
                if($user_list[$val['sub_uid']]['id']!=$uid){
                    $room_list[$val['room_id']]['title']=isset($user_list[$val['sub_uid']]['user_nicename'])?$user_list[$val['sub_uid']]['user_nicename']:'';
                }
                $temp = array_merge($user_list[$val['sub_uid']], $temp);
                $temp['id'] = intval($temp['id']);
                if (isset($temp['sub_uid'])) {
                    unset($temp['sub_uid']);
                }
            }
            $room_list[$val['room_id']]['friends'][] = $temp;
        }
        foreach ($room_list as $key => $val) {
            $room_list[$key]['room_id'] = intval($val['room_id']);
            $room_list[$key]['title'] = rawurldecode($val['title']);
            $room_list[$key]['roomtype'] = intval($val['roomtype']);
            $Record = $this->getChatroomRecord(intval($val['room_id']), $uid);//获取未读消息
            $room_list[$key]['record_len'] = isset($Record[0])?$Record[0]:0;
            $room_list[$key]['record_last'] = isset($Record[1]['ct'])?$Record[1]['ct']:'';
            $room_list[$key]['record_time'] = isset($Record[1]['time'])?$Record[1]['time']:'';

            /*$room_list[$key]['record_len']=$this->getChatroomRecord(intval($val['room_id']));
            $room_list[$key]['record_last']=$this->getRecordLast(intval($val['room_id']))['ct'];
            $room_list[$key]['record_time']=$this->getRecordLast(intval($val['room_id']))['time'];*/
            if (!isset($val['friends'])) {
                $room_list[$key]['friends'] = array();
            }
        }

        $room_list = array_values($room_list);
        return array('code' => 0, 'msg' => '', 'info' => $room_list);
    }


    /****/
    public function getPrivateChatRoomRecords($uid)
    {
        $lord_room_list = DI()->notorm->users_chatroom->select("id as room_id")->where('uid=? and  roomtype=?', $uid, 1)->order('id asc')->fetchAll();
        $up_list = DI()->notorm->users_chatroom_friends->select("room_id")->where('sub_uid=? and roomtype=?', $uid, 1)->fetchAll();
        $list = array_merge($lord_room_list, $up_list);
        $room_ids = count($list) > 0 ? array_keys(array_column($list, null, 'room_id')) : [];
        if (count($room_ids) <= 0) {
            return array('code' => 0, 'msg' => '', 'info' => [['record_len' => 0]]);
        }
        $count = 0;
        foreach ($room_ids as $v) {
            $count = $count + $this->getChatroomRecord($v, $uid)[0];
        }
        $res[]['record_len'] = $count;
        return array('code' => 0, 'msg' => '', 'info' => $res);
    }

    /****/
    public function createPrivateChatRoom($uid, $avatar)
    {
        $code = codemsg();
        //获取上级信息
        //查询上级
        $agent = DI()->notorm->users_agent->select('one_uid')->where('uid=?', $uid)->fetchOne();

        if (!$agent['one_uid']) {
            return ['code' => 404, 'msg' => '无上级', 'info' => []];
        }
        //查看是否在列表中
        $res = DI()->notorm->users_customer
            ->select('uid')
            ->where('puid=?', $agent['one_uid'])->fetchAll();

        if (empty($res)) {
            return ['code' => 404, 'msg' => '无上级客服', 'info' => []];
        }
        //查看是否创建过私密聊天
        $titleS = [];
        foreach ($res as $k => $v) {
            $titleS[$k] = 'private_' . $uid . '_' . $v['uid'];
        }

        $room_infoS = DI()->notorm->users_chatroom
            ->select("id,uid,title")
            ->where('uid=? and  roomtype=?', $uid, 1)
            ->where('title', $titleS)
            ->fetchAll();
        $data = [];

        if (!$room_infoS) {//不存在写入数据
            foreach ($res as $rk => $rv) {
                $data[$rk] = [
                    'uid' => intval($uid),
                    'title' => 'private_' . $uid . '_' . $rv['uid'],
                    'avatar' => $avatar,
                    'addtime' => time(),
                    'tenant_id' => getTenantId(),
                    'act_uid' => intval($uid),
                    'mtime' => time(),
                    'chattime' => time(),
                    'roomtype' => 1
                ];
            }
            $rowCount = DI()->notorm->users_chatroom->insert_multi($data);
            if ($rowCount > 0) {
                $dataUid = [];
                $dataPuidS = [];
                $room_infoS = DI()->notorm->users_chatroom
                    ->select("id,uid,title")
                    ->where('uid=? and  roomtype=?', $uid, 1)
                    ->where('title', $titleS)
                    ->fetchAll();
                foreach ($room_infoS as $kk => $kv) {
                    //获取puid
                    $puids = explode('_', $kv['title']);
                    $dataUid[$kk] = [
                        'room_id' => intval($kv['id']),
                        'sub_uid' => intval($uid),
                        'type' => 3, // 房主
                        'status' => 1,
                        'addtime' => time(),
                        'tenant_id' => getTenantId(),
                        'act_uid' => intval($uid),
                        'mtime' => time(),
                        'roomtype' => 1
                    ];
                    $dataPuidS[$kk] = [
                        'room_id' => intval($kv['id']),
                        'sub_uid' => intval($puids[1]),
                        'type' => 1, // 非房主
                        'status' => 1,
                        'addtime' => time(),
                        'tenant_id' => getTenantId(),
                        'act_uid' => intval($uid),
                        'mtime' => time(),
                        'roomtype' => 1
                    ];
                }
                //写入数据
                DI()->notorm->users_chatroom_friends->insert_multi($dataUid);
                DI()->notorm->users_chatroom_friends->insert_multi($dataPuidS);

                foreach ($room_infoS as $kk => $kv) {
                    $puids = explode('_', $kv['title']);
                    // 清除用户所在的私聊房间room_ids缓存
                    delChatPrivateRoomIdsCahche(getTenantId(), $puids[1]);
                }
            }
        } else {//补充数据
            $titles = array_column($room_infoS, 'title');//获取对应的ID
            $titleDiff = array_diff($titleS, $titles);

            if (!empty($titleDiff)) {
                //创建房主
                $data = [];
                foreach ($titleDiff as $uk => $uv) {
                    $data[$uk] = [
                        'uid' => intval($uid),
                        'title' => $uv,
                        'avatar' => $avatar,
                        'addtime' => time(),
                        'tenant_id' => getTenantId(),
                        'act_uid' => intval($uid),
                        'mtime' => time(),
                        'chattime' => time(),
                        'roomtype' => 1
                    ];
                }
                $rowCount = DI()->notorm->users_chatroom->insert_multi($data);
                if ($rowCount > 0) {//查询记录
                    $_room_infoS = DI()->notorm->users_chatroom
                        ->select("id,uid,title")
                        ->where('uid=? and  roomtype=?', $uid, 1)
                        ->where('title', $titleDiff)
                        ->fetchAll();
                    //继续遍历数据
                    $dataUid = [];
                    $dataPuidS = [];
                    foreach ($_room_infoS as $_rk => $_rv) {
                        $puids = explode('_', $_rv['title']);
                        $dataUid[$_rk] = [
                            'room_id' => intval($_rv['id']),
                            'sub_uid' => intval($uid),
                            'type' => 3, // 房主
                            'status' => 1,
                            'addtime' => time(),
                            'tenant_id' => getTenantId(),
                            'act_uid' => intval($uid),
                            'mtime' => time(),
                            'roomtype' => 1
                        ];
                        $dataPuidS[$_rk] = [
                            'room_id' => intval($_rv['id']),
                            'sub_uid' => intval($puids[1]),
                            'type' => 1, // 非房主
                            'status' => 1,
                            'addtime' => time(),
                            'tenant_id' => getTenantId(),
                            'act_uid' => intval($uid),
                            'mtime' => time(),
                            'roomtype' => 1
                        ];
                    }

                    DI()->notorm->users_chatroom_friends->insert_multi($dataUid);
                    DI()->notorm->users_chatroom_friends->insert_multi($dataPuidS);

                    foreach ($_room_infoS as $_rk => $_rv) {
                        $puids = explode('_', $_rv['title']);
                        // 清除用户所在的私聊房间room_ids缓存
                        delChatPrivateRoomIdsCahche(getTenantId(), $puids[1]);
                    }

                    //合并数组
                    $room_infoS = array_merge($room_infoS, $_room_infoS);
                }
            }
        }
        $room_info = array_column($room_infoS, 'id');

        // 清除用户所在的私聊房间room_ids缓存
        delChatPrivateRoomIdsCahche(getTenantId(), $uid);

        //开始处理数据
        $configpri = getConfigPri();
        $roomList = [
            'room_ids' => $room_info,
            'avatar' => $avatar,
            'chatserver' => $configpri,
            //'title'=>$title,
        ];
        return array('code' => 0, 'msg' => '', 'info' => $roomList);
    }

    /****/
    public function createPrivateChatRoomP($uid, $puid)
    {
        //获取用户信息
        $userData = DI()->notorm->users->where('id=?', $puid)
            ->select('user_nicename,avatar')
            ->fetchOne();
        if (empty($userData)) {
            return array('code' => 0, 'msg' => '查询为空', 'info' => []);
        }
        $title=$uid.'_'.$puid;
        $configpri = getConfigPri();
        $room_info = DI()->notorm->users_chatroom
            ->select("id,uid,avatar")
            ->where('title=? and roomtype=?', $title,1)
            ->fetchOne();
        if ($room_info) {
            $roomList = [
                'room_id' => $room_info['id'],
                'avatar' => $userData['avatar'],
                'chatserver' => $configpri,
                'title' => $userData['user_nicename'],
                'uid' => $uid,
                'puid' => $puid,

            ];
            return array('code' => 0, 'msg' => '', 'info' => [$roomList]);
        }
        //反复查询
        $puidroomInfo= DI()->notorm->users_chatroom
            ->select("id,uid,avatar")
            ->where('title=? and roomtype=?',$puid.'_'.$uid,1)
            ->fetchOne();
        if($puidroomInfo){
            $roomList = [
                'room_id' => $puidroomInfo['id'],
                'avatar' =>  $puidroomInfo['avatar'],
                'chatserver' => $configpri,
                'title' => $userData['user_nicename'],
                'uid' => $uid,
                'puid' => $puid,
            ];
            return array('code' => 0, 'msg' => '', 'info' => [$roomList]);
        }
        $data = array(
            'uid' => intval($uid),
            'title' => $title,
            'avatar' => $userData['avatar'],
            'addtime' => time(),
            'tenant_id' => getTenantId(),
            'act_uid' => intval($uid),
            'mtime' => time(),
            'chattime' => time(),
            'roomtype' => 1,
        );
        $res = DI()->notorm->users_chatroom->insert($data);
        if (isset($res['id'])) {
            $data = [
                [
                    'room_id' => intval($res['id']),
                    'sub_uid' => intval($uid),
                    'type' => 3, // 房主
                    'status' => 1,
                    'addtime' => time(),
                    'tenant_id' => getTenantId(),
                    'act_uid' => intval($uid),
                    'mtime' => time(),
                    'roomtype' => 1,
                ],
                [
                    'room_id' => intval($res['id']),
                    'sub_uid' => intval($puid),
                    'type' => 1, // 非房主
                    'status' => 1,
                    'addtime' => time(),
                    'tenant_id' => getTenantId(),
                    'act_uid' => intval($uid),
                    'mtime' => time(),
                    'roomtype' => 1,
                ],
            ];
            DI()->notorm->users_chatroom_friends->insert_multi($data);
        }

        // 清除用户所在的私聊房间room_ids缓存
        delChatPrivateRoomIdsCahche(getTenantId(), $uid);
        delChatPrivateRoomIdsCahche(getTenantId(), $puid);

        $roomList = [
            'room_id' => $res['id'],
            'avatar' => $userData['avatar'],
            'chatserver' => $configpri,
            'title' => $userData['user_nicename'],
            'uid' => $uid,
            'puid' => $puid,

        ];
        return array('code' => 0, 'msg' => '', 'info' => [$roomList]);
    }


    /****/
    public function getUsersCustomerS($uid)
    {
        $agent = DI()->notorm->users_agent->select('one_uid,two_uid,three_uid,four_uid,five_uid')->where('uid=?', $uid)->fetchOne();
        if (empty($agent)) {
            return ['code' => 0, 'msg' => '无上级', 'info' => []];
        }
        $puids = [];
        foreach ($agent as $v) {
            if (!empty($v)) {
                $puids[] = $v;
            }
        }
        //查看是否在列表中
        $res = DI()->notorm->users_customer->select('uid,username,title,avatar')->where('puid', $puids)->fetchAll();
        if (empty($res)) {
            return ['code' => 0, 'msg' => '无上级客服', 'info' => []];
        }
        $info = [];
        foreach ($res as $k => $v) {
            if($uid!=$v['uid']){
                $info[] = [
                    'puid' => intval($v['uid']),
                    'nicename' => $v['username'],
                    'title' => $v['title'],
                    'avatar' => $v['avatar'],
                ];
            }
        }
        return ['code' => 0, 'msg' => '查询成功', 'info' => $info];
    }

    /****/

    public function getCustomerVideo($puid, $uid, $p)
    {
        if ($p < 1) {
            $p = 1;
        }
        $nums = 20;
        $start = ($p - 1) * $nums;
        $_video = DI()->notorm->video
            ->select("id,uid,title,video_id,href,href_1,href_2,href_3,href_4,
            href_5,href_6,href_7,href_8,href_9,label,comments,likes,collection,download_address,download_address_1,
            download_address_2,origin,thumb,price,buy_numbers,total_warch_time,last_watch_time,first_watch_time,hot_searches,try_watch_time,backstage_thumb")
            ->where('status=2 and uid=?', $puid)
            ->order("id desc")
            ->limit($start, $nums)
            ->fetchAll();
        if (empty($_video)) {
            return ['code' => 0, 'msg' => '该用户没有上传视频', 'info' => []];
        }

        $paly_url = play_or_download_url(1);
        $download_url = play_or_download_url(2);
        foreach ($_video as $k => $v) {
            $userinfo = getUserInfo($v['uid']);
            if (!$userinfo) {
                $userinfo['user_nicename'] = "已删除";
            }
            $video[$k]['id'] = $v['id'];
            $video[$k]['uid'] = $v['uid'];
            $video[$k]['title'] = $v['title'];
            $video[$k]['label'] = $v['label'];
            $video[$k]['comments'] = $v['comments'];
            $video[$k]['likes'] = $v['likes'];
            $video[$k]['collection'] = $v['collection'];
            $video[$k]['price']=$v['price'];//
            $video[$k]['buy_numbers']=$v['buy_numbers'];//
            $video[$k]['total_warch_time']=$v['total_warch_time'];//
            $video[$k]['last_watch_time']=$v['last_watch_time'];//
            $video[$k]['first_watch_time']=$v['first_watch_time'];//
            $video[$k]['hot_searches']=$v['hot_searches'];//
            $video[$k]['try_watch_time']=$v['try_watch_time'];//
            $video[$k]['backstage_thumb']=$v['backstage_thumb'];//
            $video[$k]['isBuy'] = static::isBuy($uid,$v['id']);
            $video[$k]['is_collection'] = static::isCollection($uid, $v['id']);//
            $video[$k]['is_like'] = static::isLike($uid, $v['id']);//
            $video[$k]['is_download'] = static::isDownload($uid, $v['id']);//
            $video[$k]['is_follow'] = static::isAttention($uid, $v['uid']);//
            if ($v['origin'] != 3) {
                $video[$k]['href'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $v[$paly_url['viode_table_field']];
                if($paly_url['name'] == 'minio' && strrpos($v['thumb'], '/liveprod-store-1039') === false){ // 是 minio, 同时不存在 /liveprod-store-1039
                    $video[$k]['thumb'] = $paly_url['url'] .'/liveprod-store-1039'. $v['thumb'];
                }else{
                    $video[$k]['thumb'] = $paly_url['url'] . $v['thumb'];
                }
                $video[$k]['download_address'] = $download_url['url'] . $v[$download_url['viode_table_field']];
            }else{
                $video[$k]['href'] = $v['href'];
                $video[$k]['thumb'] = $v['thumb'];
                $video[$k]['download_address'] = $v['download_address'];
            }
            $video[$k]['userinfo'] = $userinfo;
        }
        $video = array_values($video);
        $count = empty(count($_video)) ? 0 : count($_video);
        return [$video, $count];
    }

    /****/
    public function getUsersCustomerList($uid)
    {
        $agent = DI()->notorm->users_agent->select('one_uid,two_uid,three_uid,four_uid,five_uid')->where('uid=?', $uid)->fetchOne();
        if (empty($agent)) {
            return ['code' => 0, 'msg' => '无上级', 'info' => []];
        }
        $puids = [];
        foreach ($agent as $v) {
            if (!empty($v)) {
                $puids[] = $v;
            }
        }
        $res = DI()->notorm->users_customer->select('uid')->where('puid', $puids)->fetchAll();
        if (empty($res)) {
            return ['code' => 0, 'msg' => '无上级客服', 'info' => []];
        }
        //查询昵称
        $uids = array_column($res, 'uid');
        $user = DI()->notorm->users->select('id ,user_nicename,avatar')->where('id', $uids)->fetchAll();

        //查询用户是否创建私聊
        $friends = DI()->notorm->users_chatroom_friends
            ->select('room_id,sub_uid')
            ->where('roomtype=1 and sub_uid', $uids)
            ->fetchAll();
        $friendsInfo = count($friends) > 0 ? array_column($friends, null, 'sub_uid') : [];
        //
        $_userInfo = [];
        foreach ($user as $k => $value) {
            if ($uid!=$value['id']){
                $_userInfo[$k]['pid'] = $value['id'];
                $_userInfo[$k]['user_nicename'] = $value['user_nicename'];
                $_userInfo[$k]['avatar'] = $value['avatar'];
            }


        }
        return ['code' => 0, 'msg' => '查询成功', 'info' => $_userInfo];
    }

    /*****/
    private function getChatroomRecord($room_id, $uid)
    {

        $len = DI()->redis->lLen('users_chatroom_record' . $room_id);
        $data['ct'] = '';
        $data['time'] = '';
        $count = 0;
        if (empty($len)) {
            return [$count, '', ''];
        }
        $redisDat = DI()->redis->lRange('users_chatroom_record' . $room_id, 0, -1);
        foreach ($redisDat as $v) {
            $jsonData = json_decode($v, true);
            if ($uid != $jsonData['uid'] && $jsonData['status'] == 0) {
                $count += 1;
            }
            $data['ct'] = $jsonData['ct'];
            $data['ct_img'] = $jsonData['ct_img'];
            $data['ct_bank'] = $jsonData['ct_bank'];
            $time_sec = intval($jsonData['time']/1000);
            if (date('Y-m-d') != date('Y-m-d', $time_sec)) {
                $data['time'] = date('m/d H:i:s', $time_sec);
            }
            $data['time'] = date('H:i:s', $time_sec);
            $data['uniqid'] = $jsonData['uniqid'];
        }
        return [$count, $data];
    }
    /****/
    /*private  function  getRecordLast($room_id)
    {
        $json=DI()->redis->LINDEX('users_chatroom_record'.$room_id,-1);
        $data=json_decode($json,true);
        if($data){
            $d=date('H:i:s',$data['addtime']);
            if(date('Y-m-d')!=date('Y-m-d',$data['addtime'])){
                $d=date('m/d H:i:s',$data['addtime']);
            }
            return [
                'ct'=>$data['ct'],
                'time'=>$d,
            ];
        }else{
            return[
                'ct'=>'',
                'time'=>'',
            ];
        }
    }*/
    /****/
    /* private  function getChatroomRecord($room_id)
     {
         $len =  DI()->redis->lLen('users_chatroom_record'.$room_id);
         if(empty($len)){
             return 0 ;
         }
         $count=0;
         $redisDat=DI()->redis->lRange('users_chatroom_record'.$room_id,0,-1);
         foreach ($redisDat as $v){
             $jsonData=json_decode($v,true);
             if($jsonData['status']==0){
                     $count+=1;
             }
         }
         return  $count;
     }*/

    private static function isAttention($uid, $videoUid)
    {
        // 是否关注
        $touids = DI()->notorm->users_attention
            ->where("uid= '{$uid}' and  touid  = '{$videoUid}'")
            ->fetchOne();
        if ($touids) {
            return 1;
        } else {
            return 0;
        }
    }

    private static function isCollection($uid, $videoId)
    {
        // 是否收藏
        $is_collection = DI()->notorm->users_video_collection
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 1 ")
            ->fetchOne();
        if ($is_collection) {
            return 1;
        } else {
            return 0;
        }
    }

    private static function isLike($uid, $videoId)
    {
        // 是否喜欢
        $is_like = DI()->notorm->users_video_like
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 1 ")
            ->fetchOne();
        if ($is_like) {
            return 1;
        } else {
            return 0;
        }
    }

    private static function isDownload($uid, $videoId)
    {
        // 是否下载
        $is_download = DI()->notorm->video_download
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 1 ")
            ->fetchOne();
        if ($is_download) {
            return 1;
        } else {
            return 0;
        }
    }

    private  static function isBuy($uid,$videoId){
        // 是否购买

        $is_buy  = DI()->notorm->users_video_buy
            ->where("videoid  = '{$videoId}' and uid = '{$uid}' and video_type = 1 ")
            ->fetchOne();
        if ($is_buy){
            return 1;
        }else{
            return 0;
        }
    }
}