<?php

class Domain_Bar {
    public function barList($game_tenant_id,$type,$uid,$p) {
        $rs = array();

        $model = new Model_Bar();
        $rs = $model->barList($game_tenant_id,$type,$uid,$p);

        return $rs;
    }

    public function barInfo($game_tenant_id,$uid,$bar_id) {
        $rs = array();

        $model = new Model_Bar();
        $rs = $model->barInfo($game_tenant_id,$uid,$bar_id);

        return $rs;
    }

    public function commentList($game_tenant_id,$bar_id,$uid,$type,$p) {
        $rs = array();

        $model = new Model_Bar();
        $rs = $model->commentList($game_tenant_id,$bar_id,$uid,$type,$p);

        return $rs;
    }
    public  function commentDesc($game_tenant_id,$comment_id,$uid,$p){
        $rs = array();

        $model = new Model_Bar();
        $rs = $model->commentDesc($game_tenant_id,$comment_id,$uid,$p);

        return $rs;
    }

    public  function postBar($game_tenant_id,$desc,$uid,$img,$fileStoreKey,$video_img,$type,$reward_money){
        $rs = array();

        $model = new Model_Bar();
        $rs = $model->postBar($game_tenant_id,$desc,$uid,$img,$fileStoreKey,$video_img,$type,$reward_money);

        return $rs;
    }
    public  function postComment($game_tenant_id,$desc,$uid,$type,$id,$video_id,$video_type){
        $rs = array();

        $model = new Model_Bar();
        $rs = $model->postComment($game_tenant_id,$desc,$uid,$type,$id,$video_id,$video_type);

        return $rs;
    }
    public  function setOptimumComment($game_tenant_id,$bar_id,$comment_id,$uid){
        $rs = array();

        $model = new Model_Bar();
        $rs = $model->setOptimumComment($game_tenant_id,$bar_id,$comment_id,$uid);

        return $rs;
    }

    public  function barLikes($game_tenant_id,$bar_id,$uid){
        $rs = array();

        $model = new Model_Bar();
        $rs = $model->barLikes($game_tenant_id,$bar_id,$uid);

        return $rs;
    }

    public  function myData($game_tenant_id,$uid){
        $rs = array();

        $model = new Model_Bar();
        $rs = $model->myData($game_tenant_id,$uid);

        return $rs;
    }

}