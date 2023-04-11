<?php

class Domain_Live {
	
	public function createRoom($uid,$data) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->createRoom($uid,$data);
		return $rs;
	}
	
	public function getFansIds($touid) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->getFansIds($touid);
		return $rs;
	}
	
	public function changeLive($uid,$stream,$status) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->changeLive($uid,$stream,$status);
		return $rs;
	}
	
	public function changeLiveType($uid,$stream,$data) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->changeLiveType($uid,$stream,$data);
		return $rs;
	}

	public function stopRoom($uid,$stream,$liveself,$acttype,$is_forbidden) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->stopRoom($uid,$stream,$liveself,$acttype,$is_forbidden);
		return $rs;
	}
	
	public function stopInfo($stream) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->stopInfo($stream);
		return $rs;
	}
	
	public function checkLive($uid,$liveuid,$stream,$language_id) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->checkLive($uid,$liveuid,$stream,$language_id);
		return $rs;
	}
    public function updateLivestatus($liveuid,$stream,$old_status,$status){
        $rs = array();

        $model = new Model_Live();
        $rs = $model->updateLivestatus($liveuid,$stream,$old_status,$status);
        return $rs;
    }
    public function confirmLivestatus($liveuid,$version){
        $rs = array();

        $model = new Model_Live();
        $rs = $model->confirmLivestatus($liveuid,$version);
        return $rs;
    }
    public function confirmLive($liveuid){
        $rs = array();

        $model = new Model_Live();
        $rs = $model->confirmLive($liveuid);
        return $rs;
    }
	
	public function roomCharge($uid,$token,$liveuid,$stream) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->roomCharge($uid,$token,$liveuid,$stream);
		return $rs;
	}
    public function roomChargealone($uid,$token,$liveuid,$stream) {
        $rs = array();

        $model = new Model_Live();
        $rs = $model->roomChargealone($uid,$token,$liveuid,$stream);
        return $rs;
    }
	
	public function getUserCoin($uid) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->getUserCoin($uid);
		return $rs;
	}
	
	public function isZombie($uid) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->isZombie($uid);
		return $rs;
	}
	
	public function getZombie($stream,$where) {
        $rs = array();
				
        $model = new Model_Live();
        $rs = $model->getZombie($stream,$where);

        return $rs;
    }	

	public function getPop($touid) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->getPop($touid);
		return $rs;
	}

	public function getGiftList() {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->getGiftList();
		return $rs;
	}
	
	public function sendGift($uid,$liveuid,$stream,$giftid,$giftcount,$send_type) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->sendGift($uid,$liveuid,$stream,$giftid,$giftcount,$send_type);
		return $rs;
	}
    public function sendGiftalone($uid,$liveuid,$stream,$giftid,$giftcount) {
        $rs = array();

        $model = new Model_Live();
        $rs = $model->sendGiftalone($uid,$liveuid,$stream,$giftid,$giftcount);
        return $rs;
    }
    public function sendGiftalonevutar($uid,$liveuid,$stream,$giftid,$giftcount) {
        $rs = array();

        $model = new Model_Live();
        $rs = $model->sendGiftvutar($uid,$liveuid,$stream,$giftid,$giftcount);
        return $rs;
    }

	public function sendBarrage($uid,$liveuid,$stream,$giftid,$giftcount,$content) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->sendBarrage($uid,$liveuid,$stream,$giftid,$giftcount,$content);
		return $rs;
	}
    public function sendBarragealone($uid,$liveuid,$stream,$giftid,$giftcount,$content) {
        $rs = array();

        $model = new Model_Live();
        $rs = $model->sendBarragealone($uid,$liveuid,$stream,$giftid,$giftcount,$content);
        return $rs;
    }
	public function setAdmin($liveuid,$touid) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->setAdmin($liveuid,$touid);
		return $rs;
	}
	
	public function getAdminList($liveuid) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->getAdminList($liveuid);
		return $rs;
	}
	
	public function getUserHome($uid,$touid) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->getUserHome($uid,$touid);
		return $rs;
	}
    
	public function getReportClass() {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->getReportClass();
		return $rs;
	}

	public function setReport($uid,$touid,$content) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->setReport($uid,$touid,$content);
		return $rs;
	}

	public function getVotes($liveuid) {
		$rs = array();

		$model = new Model_Live();
		$rs = $model->getVotes($liveuid);
		return $rs;
	}
    public function getReward($liveuid) {
        $rs = array();

        $model = new Model_Live();
        $rs = $model->getReward($liveuid);
        return $rs;
    }
	public function superStopRoom($uid,$token,$liveuid,$type) {
		$rs = array();
		$model = new Model_Live();
		$rs = $model->superStopRoom($uid,$token,$liveuid,$type);
		return $rs;
	}	

	public function getContribut($uid,$liveuid,$showid) {
		$rs = array();
		$model = new Model_Live();
		$rs = $model->getContribut($uid,$liveuid,$showid);
		return $rs;
	}
    public function getBetinfo($tenant_id) {
        $rs = array();
        $model = new Model_Live();
        $rs = $model->getBetinfo($tenant_id);
        return $rs;
    }

    public  function liveInfo($liveuid){
        $rs = array();
        $model = new Model_Live();
        $rs = $model->liveInfo($liveuid);
        return $rs;
    }
    public function liveset($data) {
        $rs = array();
        $model = new Model_Live();
        $rs = $model->liveset($data);
        return $rs;
    }

    public function autoUpdatevotes() {
        $rs = array();
        $model = new Model_Live();
        $rs = $model->autoUpdatevotes();
        return $rs;
    }

    public function keywordcheck($uid,$content) {
        $rs = array();
        $model = new Model_Live();
        $rs = $model->keywordcheck($uid,$content);
        return $rs;
    }
    public function  addAnchornamecard($uid,$declaration,$position,$limit_price,$telephone,$is_open,$type) {
        $rs = array();
        $model = new Model_Live();
        $rs =  $model->addAnchornamecard($uid,$declaration,$position,$limit_price,$telephone,$is_open,$type);
        return $rs;
    }
    public function  getAnchornamecards($uid,$liveuid) {
        $rs = array();
        $model = new Model_Live();

        $rs =  $model->getAnchornamecards($uid,$liveuid);
        return $rs;
    }

    public function getCarList($uid)
    {
        $model = new Model_Live();
        $rs = $model->getCarList($uid);
        return $rs;
    }

    public function getUserCarList($uid)
    {
        $model = new Model_Live();
        $rs = $model->getUserCarList($uid);
        return $rs;
    }

    public function buyCar($uid,$carid)
    {
        $model = new Model_Live();
        $rs = $model->buyCar($uid,$carid);
        return $rs;
    }

    public function buyCaralone($uid,$carid)
    {
        $model = new Model_Live();
        $rs = $model->buyCaralone($uid,$carid);
        return $rs;
    }

    public function rideCar($uid,$id)
    {
        $model = new Model_Live();
        $rs = $model->rideCar($uid,$id);
        return $rs;
    }
    public function getroomtype($uid)
    {
        $model = new Model_Live();
        $rs = $model->getroomtype($uid);
        return $rs;
    }

    public function leaveRoom($uid,$liveuid,$stream,$watchtime){
        $model = new Model_Live();
        $rs = $model->leaveRoom($uid,$liveuid,$stream,$watchtime);
        return $rs;
    }

    public function getLiveGameInfo($uid,$liveuid){
        $model = new Model_Live();
        $rs = $model->getLiveGameInfo($uid,$liveuid);
        return $rs;
    }

    public function getEnterroomNotice($uid,$language_id){
        $model = new Model_Live();
        $rs = $model->getEnterroomNotice($uid,$language_id);
        return $rs;
    }

    public function getLiveInfo($liveuid){
        $model = new Model_Live();
        $rs = $model->getLiveInfo($liveuid);
        return $rs;
    }

    public function getNobleList($uid){
        $model = new Model_Live();
        $rs = $model->getNobleList($uid);
        return $rs;
    }

    public function buyNoble($uid,$liveuid,$level,$type){
        $model = new Model_Live();
        $rs = $model->buyNoble($uid,$liveuid,$level,$type);
        return $rs;
    }

    public function buyNoblealone($uid,$liveuid,$level,$type){
        $model = new Model_Live();
        $rs = $model->buyNoblealone($uid,$liveuid,$level,$type);
        return $rs;
    }

    public function getNobleSetting($uid){
        $model = new Model_Live();
        $rs = $model->getNobleSetting($uid);
        return $rs;
    }

    public function ExeculationLivestatus(){
        $model = new Model_Live();

        $rs = $model->ExeculationLivestatus();
        return $rs;
    }

    public function LiveTimeOut(){
        $model = new Model_Live();

        $rs = $model->LiveTimeOut();
        return $rs;
    }

    public function updateLivetype($uid,$stream,$type,$type_val,$tryWatchTime){
        $model = new Model_Live();
        $rs = $model->updateLivetype($uid,$stream,$type,$type_val,$tryWatchTime);
        return $rs;
    }

}
