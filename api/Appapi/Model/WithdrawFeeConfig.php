<?php
//session_start();
class Model_WithdrawFeeConfig extends PhalApi_Model_NotORM
{

    public function getWithdrawFeeConfig($uid){

        $tenant_id = getTenantId();
        $withdrawFeeConfigList = Cache_WithdrawFeeConfig::getInstance()->getWithdrawFeeConfigList($tenant_id);
        $data = array();
        foreach ($withdrawFeeConfigList as $key=>$val){
            $temp = array(
                'amount' => floatval($val['amount']),
                'type' => $val['type'],
                'fee' => floatval($val['fee']),
            );
            array_push($data, $temp);
        }
        array_multisort(array_column($data, 'amount'), SORT_ASC, $data);

        return array('code' => 0, 'msg' => '', 'info' => $data);
    }

}
