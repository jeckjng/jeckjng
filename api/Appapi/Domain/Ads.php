<?php

class Domain_Ads {
    public function adsList($game_tenant_id) {
        $rs = array();

        $model = new Model_Ads();
        $rs = $model->adsList($game_tenant_id);

        return $rs;
    }

    public function getCarousel($cat_name,$p) {
        $model = new Model_Ads();
        $rs = $model->getCarousel($cat_name,$p);
        return $rs;
    }

    public  function getCreationAds($game_tenant_id,$sid){
        $rs = array();

        $model = new Model_Ads();
        $rs = $model->getCreationAds($game_tenant_id,$sid);

        return $rs;
    }
    public  function getAdsbyname($game_tenant_id,$adname){
        $rs = array();

        $model = new Model_Ads();
        $rs = $model->getAdsbyname($game_tenant_id,$adname);

        return $rs;
    }

    public  function AdsById($game_tenant_id,$adname,$long_video_class_id){
        $rs = array();

        $model = new Model_Ads();
        $rs = $model->AdsById($game_tenant_id,$adname,$long_video_class_id);

        return $rs;
    }
}