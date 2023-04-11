<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_Bank extends PhalApi_Model_NotORM {


    public function bankList() {
        $list=DI()->notorm->bank
            ->fetchAll();
        return $list;
    }

}