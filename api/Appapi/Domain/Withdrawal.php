<?php

class Domain_Withdrawal
{
    public function getCoin() {
        $rs = array();

        $model = new Model_Withdrawal();
        $rs = $model->getCoin();

        return $rs;
    }
    public function applyWithdrawal($uid, $bank_id, $coin, $amount, $game_tenant_id, $type, $cash_account_type, $cash_network_type, $virtual_coin_address, $qr_code_url) {
        $rs = array();

        $model = new Model_Withdrawal();
        $rs = $model->applyWithdrawal($uid, $bank_id, $coin, $amount, $game_tenant_id, $type, $cash_account_type, $cash_network_type, $virtual_coin_address, $qr_code_url);

        return $rs;
    }

    public  function withdrawalLog($uid,$status,$p){
        $rs = array();

        $model = new Model_Withdrawal();
        $rs = $model->withdrawalLog($uid,$status,$p);

        return $rs;
    }
}