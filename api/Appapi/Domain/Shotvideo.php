<?php

class Domain_Shotvideo {
	public function setVideo($data) {
		$rs = array();

		$model = new Model_Shotvideo();
		$rs = $model->setVideo($data);

		return $rs;
	}
	
    public function setComment($data) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->setComment($data);

        return $rs;
    }
    public function delVideolabel($data) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->delVideolabel($data);

        return $rs;
    }
    public function getVideoclassify($uid) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getVideoclassify($uid);

        return $rs;
    }
    public function addView($uid,$videoid) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->addView($uid,$videoid);

        return $rs;
    }
    public function addLike($uid,$videoid,$game_tenant_id) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->addLike($uid,$videoid,$game_tenant_id);

        return $rs;
    }
    public function addCollection($uid,$videoid,$game_tenant_id) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->addCollection($uid,$videoid,$game_tenant_id);

        return $rs;
    }


    public function addStep($uid,$videoid) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->addStep($uid,$videoid);

        return $rs;
    }
    public function addShare($uid,$videoid) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->addShare($uid,$videoid);

        return $rs;
    }

    public function setBlack($uid,$videoid) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->setBlack($uid,$videoid);

        return $rs;
    }

    public function addCommentLike($uid,$commentid,$game_tenant_id) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->addCommentLike($uid,$commentid,$game_tenant_id);

        return $rs;
    }
	public function getVideoList($uid,$p,$url) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getVideoList($uid,$p,$url);

        return $rs;
    }
    public function getVideobyclassify($p,$classify,$uid) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getVideobyclassify($p,$classify,$uid);

        return $rs;
    }
	public function getAttentionVideo($uid,$p) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->getAttentionVideo($uid,$p);

        return $rs;
    }
	public function getVideo($uid,$videoid,$is_search,$url) {
        $rs = array();
        $model = new Model_Shotvideo();
        $rs = $model->getVideo($uid,$videoid,$is_search,$url);

        return $rs;
    }
	public function getComments($uid,$videoid,$p,$game_tenant_id) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getComments($uid,$videoid,$p,$game_tenant_id);

        return $rs;
    }
    public function getCollection($uid,$p,$game_tenant_id) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getCollection($uid,$p,$game_tenant_id);

        return $rs;
    }

	public function getReplys($uid,$commentid,$p) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getReplys($uid,$commentid,$p);

        return $rs;
    }

	public function getMyVideo($uid,$p) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->getMyVideo($uid,$p);

        return $rs;
    }
	
	public function del($uid,$videoid) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->del($uid,$videoid);

        return $rs;
    }
 
	public function getHomeVideo($uid,$touid,$p) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->getHomeVideo($uid,$touid,$p);

        return $rs;
    }
 
    public function report($data) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->report($data);

        return $rs;
    }

    public function getRecommendVideos($uid,$p,$isstart){
        $rs = array();

        $model = new Model_Video();
        $rs = $model->getRecommendVideos($uid,$p,$isstart);

        return $rs;
    }

    
 
	 public function test() {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->test();

        return $rs;
    }

    public function getNearby($uid,$lng,$lat,$p){
        $rs = array();

        $model = new Model_Video();
        $rs = $model->getNearby($uid,$lng,$lat,$p);
        
        return $rs;
    }

    public function getReportContentlist() {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->getReportContentlist();

        return $rs;
    }

    public function setConversion($videoid,$uid ,$game_tenant_id,$is_search,$is_record){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->setConversion($videoid,$uid,$game_tenant_id,$is_search,$is_record);

        return $rs;
    }

    public  function getMyShotVideo($uid,$tenant_id,$p,$status){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getMyShotVideo($uid,$tenant_id,$p,$status);

        return $rs;
    }

    public function downloadVideo($uid,$videoid,$game_tenant_id) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->downloadVideo($uid,$videoid,$game_tenant_id);

        return $rs;
    }
    public function getMydownload($uid,$tenant_id, $p){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getMydownload($uid,$tenant_id,$p);

        return $rs;
    }

    public function guessLikeShotVide($uid, $p){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->guessLikeShotVide($uid,$p);

        return $rs;
    }
/*    public function getShotVideoLabel(){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getShotVideoLabel();

        return $rs;
    }*/

    public function getShotVideoRecommend($uid,$p,$url){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getShotVideoRecommend($uid,$p,$url);

        return $rs;
    }
    public function getShotVideoByLikes($uid,$dataType,$cycle,$isSurge,$p){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getShotVideoByLikes($uid,$dataType,$cycle,$isSurge,$p);

        return $rs;
    }
    public function getHotLabelData($uid,$dataType,$p){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getHotLabelData($uid,$dataType,$p);

        return $rs;
    }
    public function getSearchContent($uid,$p,$searchcontent,$url) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getSearchContent($uid,$p,$searchcontent,$url);

        return $rs;
    }

    public function getHotPerformer($uid,$tenant_id, $p){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getHotPerformer($uid,$tenant_id,$p);

        return $rs;
    }

    public function getHotVideo($uid,$tenant_id, $p,$url){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getHotVideo($uid,$tenant_id,$p,$url);

        return $rs;
    }

    public  function uploadVideo($uid,$label,$title,$desc,$region,$years){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->uploadVideo($uid,$label,$title,$desc,$region,$years);

        return $rs;
    }

    public  function addNewVideo($uid,$classify,$title,$desc,$years){
        $model = new Model_Shotvideo();
        $rs = $model->addNewVideo($uid,$classify,$title,$desc,$years);
        return $rs;
    }

    public  function updateVideoInfo($uid, $video_id, $file_store_key){
        $model = new Model_Shotvideo();
        $rs = $model->updateVideoInfo($uid, $video_id, $file_store_key);
        return $rs;
    }

    public  function delMyVideo($uid,$videoid,$type) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->delMyVideo($uid,$videoid,$type);

        return $rs;
    }

    public  function getconcentrationVideo($p,$url){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getconcentrationVideo($p,$url);

        return $rs;
    }

    public  function getAuthurVideo($uid,$tenant_id,$p,$url){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getAuthurVideo($uid,$tenant_id,$p,$url);

        return $rs;
    }
    public  function getMyVideoStatistics($uid){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->getMyVideoStatistics($uid);

        return $rs;
    }
    public  function buyShotvideo($uid,$videoid){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->buyShotvideo($uid,$videoid);

        return $rs;
    }
    public  function buyHistory($uid,$p){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->buyHistory($uid,$p);

        return $rs;
    }
    public  function bindShop($shoptype,$shop_value,$videoid,$shop_url){
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->bindShop($shoptype,$shop_value,$videoid,$shop_url);

        return $rs;
    }

    public function getWatchVideoNum($uid,$type)
    {
        $model = new Model_Shotvideo();
        
        return $model->getWatchVideoNum($uid,$type);
    }

    public function autoPass()
    {
        $model = new Model_Shotvideo();
        return $model->autoPass();
    }
}
