<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_Feedback
{
    public function addFeedback($uid, $title, $version, $source,$content,$thumb,$contact)
    {
        $model = new Model_Feedback();
        $rs = $model->addFeedback($uid, $title, $version, $source,$content,$thumb,$contact);
        return $rs;
    }

    public function getFeedback($uid)
    {
        $model = new Model_Feedback();
        $rs = $model->getFeedback($uid);
        return $rs;
    }


}