<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_Pushpull
{
    public function getPushpull($ct_type)
    {
        $model = new Model_Pushpull();
        $rs = $model->getPushpull($ct_type);
        return $rs;
    }

    public function getPushpullAuthToken($liveuid)
    {
        $model = new Model_Pushpull();
        $rs = $model->getPushpullAuthToken($liveuid);
        return $rs;
    }

}