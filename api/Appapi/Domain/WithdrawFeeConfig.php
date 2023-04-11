<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_WithdrawFeeConfig
{
    public function getWithdrawFeeConfig($uid)
    {
        $model = new Model_WithdrawFeeConfig();
        $rs = $model->getWithdrawFeeConfig($uid);
        return $rs;
    }

}