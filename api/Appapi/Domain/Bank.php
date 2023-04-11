<?php
/**
 * Created by PhpStorm.
 * User: 哎哟哎哟
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_Bank {
    public function BankList() {

        $model = new Model_Bank();
        $rs = $model->BankList();

        return $rs;
    }

    public  function bindingBank($data, $uid, $real_name, $bank_number){
        $model = new Model_Bankcard();
        $rs = $model->bindingBank($data, $uid, $real_name, $bank_number);

        return $rs;
    }
    public  function myBlanCard($uid,$game_tenant_id){
        $model = new Model_Bankcard();
        $rs = $model->myBlanCard($uid,$game_tenant_id);

        return $rs;
    }

}
