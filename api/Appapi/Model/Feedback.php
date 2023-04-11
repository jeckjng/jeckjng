<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_Feedback extends PhalApi_Model_NotORM
{

    public function addFeedback($uid, $title, $version, $model,$content,$thumb,$contact)
    {
        $data = array(
            "uid" => $uid,
            "title" => $title,
            "version" => $version,
            "model" => $model,
            'content' => $content,
            'thumb' => $thumb,
            'status' => 0,
            "addtime" => time(),
            'tenant_id' => getTenantId(),
            'contact' => $contact,
        );

        $rs=DI()->notorm->feedback->insert($data);
        if(!$rs){
            return 1007;
        }
        return 1;
    }

    public function getFeedback($uid)
    {
        $list=DI()->notorm->feedback
            ->select("title,content,addtime,status,thumb")
            ->where('uid=?',$uid)
            ->order('id desc')
            ->limit(5);
        return $list;
    }

}