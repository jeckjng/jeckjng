<?php
/**
 * Created by PhpStorm.
 * User: 哎哟哎哟
 * Date: 2021/6/25
 * Time: 18:00
 */
class Domain_Appointment
{

    public function appointmentList($class,$type,$title,$shop_id,$uid,$province_id,$city_id,$area_id,$game_tenant_id,$p,$limit) {

        $model = new Model_Appointment();
        $rs = $model->appointmentList($class,$type,$title,$shop_id,$uid,$province_id,$city_id,$area_id,$game_tenant_id,$p,$limit);

        return $rs;
    }
    public  function appointmentTotal($game_tenant_id){
        $model = new Model_Appointment();
        $rs = $model->appointmentTotal($game_tenant_id);
        return $rs;
    }
    public  function getAddress($game_tenant_id){
        $model = new Model_Appointment();
        $rs = $model->getAddress($game_tenant_id);
        return $rs;
    }
    public  function appointmentInfo($game_tenant_id,$id,$uid){
        $model = new Model_Appointment();
        $rs = $model->appointmentInfo($game_tenant_id,$id,$uid);
        return $rs;
    }
    public  function getShopByType($type,$game_tenant_id,$p,$limit){
        $model = new Model_Appointment();
        $rs = $model->getShopByType($type,$game_tenant_id,$p,$limit);
        return $rs;
    }
    public  function placeorder($uid,$appointment_id,$game_tenant_id){
        $model = new Model_Appointment();
        $rs = $model->placeorder($uid,$appointment_id,$game_tenant_id);
        return $rs;
    }

    public  function myOrder($uid,$p,$limit,$game_tenant_id)
    {
        $model = new Model_Appointment();
        $rs = $model->myOrder($uid,$p,$limit,$game_tenant_id);
        return $rs;
    }

    public  function addCollect($uid,$appointment_id,$game_tenant_id){
        $model = new Model_Appointment();
        $rs = $model->addCollect($uid,$appointment_id,$game_tenant_id);
        return $rs;
    }

    public function collectList($uid,$game_tenant_id,$p,$limit){
        $model = new Model_Appointment();
        $rs = $model->collectList($uid,$game_tenant_id,$p,$limit);
        return $rs;
    }
    public function commentList($appointment_id,$p,$limit){
        $model = new Model_Appointment();
        $rs = $model->commentList($appointment_id,$p,$limit);
        return $rs;
    }
    public function browseLog($uid,$game_tenant_id,$p,$limit){
        $model = new Model_Appointment();
        $rs = $model->browseLog($uid,$game_tenant_id,$p,$limit);
        return $rs;
    }

    public function inviteSet(){
        $model = new Model_Appointment();

        $rs = $model->inviteSet();
        return $rs;
    }
    public function consumptionSet(){
        $model = new Model_Appointment();

        $rs = $model->consumptionSet();
        return $rs;
    }

    public function stationList($uid,$game_tenant_id,$p,$limit){
        $model = new Model_Appointment();
        $rs = $model->stationList($uid,$game_tenant_id,$p,$limit);
        return $rs;

    }

    public  function stationInfo($id){
        $model = new Model_Appointment();
        $rs = $model->stationInfo($id);
        return $rs;
    }

    public  function popStation($uid){
        $model = new Model_Appointment();
        $rs = $model->popStation($uid);
        return $rs;
    }
    public  function newStation($uid){
        $model = new Model_Appointment();
        $rs = $model->newStation($uid);
        return $rs;
    }



    public function  delstation($id){
        $model = new Model_Appointment();
        $rs = $model->delstation($id);
        return $rs;
    }

    public  function shopInfo($id,$p,$limit){
        $model = new Model_Appointment();
        $rs = $model->shopInfo($id,$p,$limit);
        return $rs;
    }


    public  function turntableConfig(){
        $model = new Model_Appointment();
        $rs = $model->turntableConfig();
        return $rs;
    }

    public function turntableaward($uid,$game_tenant_id){
        $model = new Model_Appointment();
        $rs = $model->turntableaward($uid,$game_tenant_id);
        return $rs;
    }

}