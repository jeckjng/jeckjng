<?php

class Domain_Longvideo {
	public function setVideo($data) {
		$rs = array();

		$model = new Model_Longvideo();
		$rs = $model->setVideo($data);

		return $rs;
	}
	
    public function setComment($data) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->setComment($data);

        return $rs;
    }
    public function delVideolabel($data) {
        $rs = array();

        $model = new Model_Shotvideo();
        $rs = $model->delVideolabel($data);

        return $rs;
    }
    public function deleteWatchrecord($uid,$id,$isdelete_all) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->deleteWatchrecord($uid,$id,$isdelete_all);

        return $rs;
    }
    public function getVideohomelabel($uid,$label) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideohomelabel($uid,$label);

        return $rs;
    }
    public function getVideolabel($uid,$label) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideolabel($uid,$label);

        return $rs;
    }
    public function getVideolabelnew($uid,$label) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideolabelnew($uid,$label);

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

        $model = new Model_Longvideo();
        $rs = $model->addLike($uid,$videoid,$game_tenant_id);

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

        $model = new Model_Longvideo();
        $rs = $model->addCommentLike($uid,$commentid,$game_tenant_id);

        return $rs;
    }
    public function getVideoList($uid,$p,$classify,$label,$iscoding,$watchmax,$likemax,$release_time,$duration,$url,$timedesc) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideoList($uid,$p,$classify,$label,$iscoding,$watchmax,$likemax,$release_time,$duration,$url,$timedesc);

        return $rs;
    }
    public function getSearchContent($uid,$p,$searchcontent,$url) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getSearchContent($uid,$p,$searchcontent,$url);

        return $rs;
    }
    public function getVideojingxuan($uid,$p,$classify,$label,$iscoding,$watchmax,$likemax){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideojingxuan($uid,$p,$classify,$label,$iscoding,$watchmax,$likemax);

        return $rs;
    }
    public function getVideobylabel($p,$label,$iscoding,$classify,$is_today_recommendation,$url) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideobylabel($p,$label,$iscoding,$classify,$is_today_recommendation,$url);

        return $rs;
    }
    public function getVideobylabelnew($p,$label) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideobylabelnew($p,$label);

        return $rs;
    }
    public function getAllclssifyandlabel() {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getAllclssifyandlabel();

        return $rs;
    }
    public function getVideobyclassify($p,$label) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideobyclassify($p,$label);

        return $rs;
    }
	public function getAttentionVideo($uid,$p) {
        $rs = array();

        $model = new Model_Video();
        $rs = $model->getAttentionVideo($uid,$p);

        return $rs;
    }
	public function getVideo($uid,$videoid,$is_search) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideo($uid,$videoid,$is_search);

        return $rs;
    }
	public function getComments($uid,$videoid,$p,$game_tenant_id) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getComments($uid,$videoid,$p,$game_tenant_id);

        return $rs;
    }
    public function addCollection($uid,$videoid,$game_tenant_id) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->addCollection($uid,$videoid,$game_tenant_id);

        return $rs;
    }
    public function getCollection($uid,$p,$game_tenant_id) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getCollection($uid,$p,$game_tenant_id);

        return $rs;
    }

    public function getWatchrecord($uid,$game_tenant_id,$label,$p) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getWatchrecord($uid,$game_tenant_id,$label,$p);

        return $rs;
    }

    public function getReplys($uid,$commentid,$p) {
        $rs = array();

        $model = new Model_Longvideo();
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

    public function setConversion($videoid,$uid,$game_tenant_id,$is_search,$is_record){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->setConversion($videoid,$uid,$game_tenant_id,$is_search,$is_record);

        return $rs;
    }

    public function watchHistory($uid,$p,$game_tenant_id){
        $model = new Model_Longvideo();
        $rs = $model->watchHistory($uid,$p,$game_tenant_id);

        return $rs;
    }

    /** 视频分类
     * @param $uid
     * @param $label
     * @return array
     */
    public function getVideoClassify($label) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getVideoClassify($label);

        return $rs;
    }

    public function getMyLongVideo($uid,$tenant_id, $p,$status){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getMyLongVideo($uid,$tenant_id,$p,$status);

        return $rs;
    }

    public function downloadVideo($uid,$videoid,$game_tenant_id) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->downloadVideo($uid,$videoid,$game_tenant_id);
        return $rs;
    }
    public function getMydownload($uid,$tenant_id, $p){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getMydownload($uid,$tenant_id,$p);

        return $rs;
    }
    public function delMydownload($uid,$id,$isdelete_all) {
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->delMydownload($uid,$id,$isdelete_all);

        return $rs;
    }

    public function guessLikeLongVide($uid, $p){
        $rs = array();
        $model = new Model_Longvideo();
        $rs = $model->guessLikeLongVide($uid,$p);

        return $rs;
    }
    public function getBanner($label ){
        $rs = array();
        $model = new Model_Longvideo();
        $rs = $model->getBanner($label);

        return $rs;
    }
    public function getHotPerformer($uid,$tenant_id, $p){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getHotPerformer($uid,$tenant_id,$p);

        return $rs;
    }
    public function getHotVideo($uid,$tenant_id, $p,$url){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getHotVideo($uid,$tenant_id,$p,$url);

        return $rs;
    }
    public  function uploadVideo($uid,$label,$title,$classify,$performer,$desc,$region,$years){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->uploadVideo($uid,$label,$title,$classify,$performer,$desc,$region,$years);

        return $rs;
    }

    public function getperformer(){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getperformer();

        return $rs;
    }

    public  function getRandomVideo($url){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getRandomVideo($url);

        return $rs;
    }
    public  function buyLongvideo($uid,$videoid){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->buyLongvideo($uid,$videoid);

        return $rs;
    }

    public function watchVodeo($uid,$videoid,$game_tenant_id){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->watchVodeo($uid,$videoid,$game_tenant_id);

        return $rs;
    }
    public function getLongvideovip($game_tenant_id){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getLongvideovip($game_tenant_id);

        return $rs;
    }
    public function getLongvideosearch($game_tenant_id){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getLongvideosearch($game_tenant_id);

        return $rs;
    }
    public function getSearchbyuser($uid){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->getSearchbyuser($uid);

        return $rs;
    }
    public function delSearchbyuser($uid,$search_id){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->delSearchbyuser($uid,$search_id);

        return $rs;
    }
    public  function buyLongvideovip($uid,$vip_grade){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->buyLongvideovip($uid,$vip_grade);

        return $rs;
    }
    public  function buyLongvideovipList($uid){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->buyLongvideovipList($uid);

        return $rs;
    }
    public  function updateVideoInfo($uid, $video_id, $file_store_key){
        $rs = array();

        $model = new Model_Longvideo();
        $rs = $model->updateVideoInfo($uid, $video_id, $file_store_key);

        return $rs;
    }


}
