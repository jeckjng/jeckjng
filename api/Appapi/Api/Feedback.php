<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 20:38
 */
class Api_Feedback extends PhalApi_Api
{
    public function getRules()
    {
        return array(
            'addFeedback' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'title' => array('name' => 'title', 'type' => 'string', 'require' => true, 'desc' => '反馈类型'),
                'version' => array('name' => 'version', 'type' => 'string', 'require' => true, 'desc' => '系统版本号'),
                'source' => array('name' => 'source', 'type' => 'string', 'require' => true, 'default'=>'pc', 'desc' => '设备'),
                'content' => array('name' => 'content', 'type' => 'string', 'require' => true, 'desc' => '内容'),
                'thumb' => array('name' => 'thumb', 'type' => 'string', 'require' => false, 'desc' => '图片（多张图片用 ; 分隔）'),
                'contact' => array('name' => 'contact', 'type' => 'string', 'require' => false, 'desc' => '联系方式'),
            ),
            'getFeedback' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
            ),
        );
    }

    /**
     * 提交反馈信息
     * @desc 提交反馈信息
     * @return string msg 提示信息
     * @return array info 列表数据
     */
    public function addFeedback()
    {
        $rs = array('code' => 0, 'msg' => '提交反馈信息成功', 'info' => array());
        $uid = $this->uid;
        $language_id = $this->language_id;
        $token = checkNull($this->token);
        $title = checkNull($this->title);
        $version = checkNull($this->version);
        $source = checkNull($this->source);
        $content = checkNull($this->content);
        $thumb = checkNull($this->thumb);
        $contact = checkNull($this->contact);

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Feedback();
        $info = $domain->addFeedback($uid, $title, $version, $source,$content,$thumb,$contact);
        if($info==1007){
            $language = DI()->config->get('language.add_fail');
            $rs['code'] = 1007;
            $rs['msg'] = $language[$language_id];//提交反馈信息失败，请重试
            return $rs;
        }
        return $rs;
    }

    /**
     * 历史反馈信息
     * @desc 历史反馈信息
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return string info[0].title 反馈类型
     * @return string info[0].content 内容
     * @return string info[0].addtime 提交时间
     * @return string info[0].status 状态：0处理中，1已处理
     * @return string info[0].thumb 图片
     */
    public function getFeedback()
    {
        $rs = array('code' => 0, 'msg' => '历史反馈信息', 'info' => array());
        $uid = $this->uid;
        $token = checkNull($this->token);

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Feedback();
        $info = $domain->getFeedback($uid);
        $rs['info'] = $info;
        return $rs;
    }

}
