<?php

class Api_Pushpull extends PhalApi_Api
{

    public function getRules()
    {
        return array(
            'getPushpull' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getPushpullAuthToken' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
            ),
        );
    }

    /**
     * 获取直播推拉流线路
     * @desc 用于获取直播推拉流线路
     * @return int code 操作码，0表示成功
     * @return array info
     * @return array info[0].pushpull_id 推拉流线路id
     * @return array info[0].appid App ID
     * @return string msg 提示信息
     */
    public function getPushpull(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=checkNull($this->uid);
        $token=checkNull($this->token);

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $auth_info = isAuth($uid);
        $ct_type = isset($auth_info['ct_type']) ? $auth_info['ct_type'] : 1;  // 直播线路类型：1.默认，2.yellow,3.green

        $domain = new Domain_Pushpull();
        $info = $domain->getPushpull($ct_type);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }


    /**
     * 获取推流服务商鉴权token
     * @desc 用于获取推流服务商鉴权token
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return array info[0].auth_token 鉴权token
     */
    public function getPushpullAuthToken()
    {
        $rs = array('code' => 0, 'msg' => '获取成功', 'info' => array());

        $uid = $this->uid;
        $token = $this->token;
        $liveuid = $this->liveuid;
        $language_id = $this->language_id;
        if (empty($language_id)){
            $language_id = 101;
        }

        $checkToken=checkToken($uid,$token);
        if($checkToken==700){
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_Pushpull();
        $info = $domain->getPushpullAuthToken($liveuid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

}
