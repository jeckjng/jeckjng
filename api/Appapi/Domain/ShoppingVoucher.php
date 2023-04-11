<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_ShoppingVoucher
{
    public function getShoppingVoucherList($uid)
    {
        $model = new Model_ShoppingVoucher();
        $rs = $model->getShoppingVoucherList($uid);
        return $rs;
    }

}