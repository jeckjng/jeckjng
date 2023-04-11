<?php

class Api_Log extends PhalApi_Api {

	public function getRules() {
		return array(
            'SocketError' => array(
                'language_id' => array('name' => 'language_id', 'type' => 'int', 'min' => 1, 'desc' => '语言id'),
                'game_tenant_id' => array('name' => 'game_tenant_id', 'type' => 'int', 'min' => 1, 'desc' => '游戏租户id'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'socket_url' => array('name' => 'socket_url', 'type' => 'string', 'desc' => 'socket地址'),
                'event' => array('name' => 'event', 'type' => 'string', 'desc' => '	socket事件'),
                'type' => array('name' => 'type', 'type' => 'int', 'desc' => '类型：1.连接失败，2.重连失败，3.发消息失败'),
                'send_ct' => array('name' => 'send_ct', 'type' => 'string', 'default'=>'', 'desc' => '发送socket消息内容'),
                'description' => array('name' => 'description', 'type' => 'string', 'desc' => '描述'),
            ),
		);
	}

    /**
     * socket错误日志记录
     * @desc 用于记录socket错误日志
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info
     */
    public function SocketError(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $socket_url = checkNull($this->socket_url);
        $event = checkNull($this->event);
        $type = $this->type;
        $send_ct = checkNull($this->send_ct);
        $description = checkNull($this->description);

        $game_tenant_id = $this->game_tenant_id;
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700 && whichTenat($game_tenant_id)!=1) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_Log();
        $info = $domain->SocketError($uid, $socket_url, $event, $type, $send_ct, $description);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }


}
