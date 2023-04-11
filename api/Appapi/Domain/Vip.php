<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_Vip
{
    public function vipList()
    {

        $model = new Model_Vip();
        $rs = $model->vipList();

        return $rs;
    }
    public function buyVip($uid,$vip_id,$coin,$game_tenant_id)
    {

        $model = new Model_Vip();
        $rs = $model->buyVip($uid,$vip_id,$coin,$game_tenant_id);

        return $rs;
    }

    public  function welfareList(){
        $model = new Model_Vip();
        $rs = $model->welfareList();

        return $rs;
    }

    public  function welfareInfo($welfare_id){
        $model = new Model_Vip();
        $rs = $model->welfareInfo($welfare_id);

        return $rs;
    }


    public  function exchangeWelfare($uid,$welfare_id,$consignee,$phone,$address,$game_tenant_id){
        $model = new Model_Vip();
        $rs = $model->exchangeWelfare($uid,$welfare_id,$consignee,$phone,$address,$game_tenant_id);

        return $rs;
    }
    public  function exchangeWelfareLog($uid,$p){
        $model = new Model_Vip();
        $rs = $model->exchangeWelfareLog($uid,$p);

        return $rs;
    }

    public  function freeVip($uid){
    $model = new Model_Vip();
    $rs = $model->freeVip($uid);
    return $rs;


    }
    public  function refundVip($uid){
        $model = new Model_Vip();
        $rs = $model->refundVip($uid);
        return $rs;
    }

}