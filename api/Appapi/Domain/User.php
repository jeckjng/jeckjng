<?php

class Domain_User {

	public function getBaseInfo($userId) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->getBaseInfo($userId);

			return $rs;
	}
	
	public function checkName($uid,$name) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->checkName($uid,$name);

			return $rs;
	}
	
	public function userUpdate($uid,$fields) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->userUpdate($uid,$fields);

			return $rs;
	}
	
	public function updatePass($uid,$oldpass,$pass) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->updatePass($uid,$oldpass,$pass);

			return $rs;
	}

    public function checkPaymentPassword($uid, $password) {
        $model = new Model_User();
        $rs = $model->checkPaymentPassword($uid, $password);
        return $rs;
    }

    public function updatePaymentPassword($uid, $old_password, $password, $confirm_password) {
        $model = new Model_User();
        $rs = $model->updatePaymentPassword($uid, $old_password, $password, $confirm_password);
        return $rs;
    }

    public function resetPaymentPassword($uid, $login_password, $password, $confirm_password) {
        $model = new Model_User();
        $rs = $model->resetPaymentPassword($uid, $login_password, $password, $confirm_password);
        return $rs;
    }

    public function getBalance($uid) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->getBalance($uid);

			return $rs;
	}
	
	public function getChargeRules() {
			$rs = array();

			$model = new Model_User();
			$rs = $model->getChargeRules();

			return $rs;
	}
	
	public function getProfit($uid) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->getProfit($uid);

			return $rs;
	}

	public function setCash($data) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->setCash($data);

			return $rs;
	}
	
	public function setAttent($uid,$touid) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->setAttent($uid,$touid);

			return $rs;
	}
	
	public function setBlack($uid,$touid) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->setBlack($uid,$touid);

			return $rs;
	}
	
	public function getFollowsList($uid,$touid,$p) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->getFollowsList($uid,$touid,$p);

			return $rs;
	}
	
	public function getFansList($uid,$touid,$p) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->getFansList($uid,$touid,$p);

			return $rs;
	}

	public function getBlackList($uid,$touid,$p) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->getBlackList($uid,$touid,$p);

			return $rs;
	}

	public function getLiverecord($touid,$p) {
			$rs = array();

			$model = new Model_User();
			$rs = $model->getLiverecord($touid,$p);

			return $rs;
	}
	
	public function getUserHome($uid,$touid) {
		$rs = array();

		$model = new Model_User();
		$rs = $model->getUserHome($uid,$touid);
		return $rs;
	}	
	
	public function getContributeList($touid,$p) {
		$rs = array();

		$model = new Model_User();
		$rs = $model->getContributeList($touid,$p);
		return $rs;
	}	
	
	public function setDistribut($uid,$code) {
		$rs = array();

		$model = new Model_User();
		$rs = $model->setDistribut($uid,$code);
		return $rs;
	}

	public function getImpressionLabel() {
        $rs = array();
                
        $model = new Model_User();
        $rs = $model->getImpressionLabel();

        return $rs;
    }	

	public function getUserLabel($uid,$touid) {
        $rs = array();
                
        $model = new Model_User();
        $rs = $model->getUserLabel($uid,$touid);

        return $rs;
    }	

	public function setUserLabel($uid,$touid,$labels) {
        $rs = array();
                
        $model = new Model_User();
        $rs = $model->setUserLabel($uid,$touid,$labels);

        return $rs;
    }	

	public function getMyLabel($uid) {
        $rs = array();
                
        $model = new Model_User();
        $rs = $model->getMyLabel($uid);

        return $rs;
    }	

	public function getPerSetting() {
        $rs = array();
                
        $model = new Model_User();
        $rs = $model->getPerSetting();

        return $rs;
    }	

	public function getUserAccountList($uid) {
        $rs = array();
                
        $model = new Model_User();
        $rs = $model->getUserAccountList($uid);

        return $rs;
    }
    public function setBetrecord($data) {
        $rs = array();

        $model = new Model_User();
        $rs = $model->setBetrecord($data);

        return $rs;
    }

	public function setUserAccount($data) {
        $rs = array();
                
        $model = new Model_User();
        $rs = $model->setUserAccount($data);

        return $rs;
    }

	public function delUserAccount($data) {
        $rs = array();
                
        $model = new Model_User();
        $rs = $model->delUserAccount($data);

        return $rs;
    }
    public function deleteZmobile($uid) {
        $rs = array();

        $model = new Model_User();
        $rs = $model->deleteZmobile($uid);

        return $rs;
    }

    public function LoginBonus($uid){
		$rs = array();
		$model = new Model_User();
		$rs = $model->LoginBonus($uid);
		return $rs;

	}

	public function getLoginBonus($uid){
		$rs = array();
		$model = new Model_User();
		$rs = $model->getLoginBonus($uid);
		return $rs;

	}
    public function invitationCode($uid) {
        $rs = array();

        $model = new Model_User();
        $rs = $model->invitationCode($uid);

        return $rs;
    }
    public function applyBecomeLive($uid,$fields) {
        $model = new Model_User();
        $rs = $model->applyBecomeLive($uid,$fields);

        return $rs;
    }

    public  function incomeExpenditure($uid,$p){
        $model = new Model_User();
        $rs = $model->incomeExpenditure($uid,$p);

        return $rs;
    }
    public  function incomeExpenditurenew($uid,$p,$type){
        $model = new Model_User();
        $rs = $model->incomeExpenditurenew($uid,$p,$type);

        return $rs;
    }
    public  function incomeUploadvideo($uid,$p){
        $model = new Model_User();
        $rs = $model->incomeUploadvideo($uid,$p);

        return $rs;
    }
    public  function getLiveInfo($uid,$live_id){
        $model = new Model_User();
        $rs = $model->getLiveInfo($uid,$live_id);

        return $rs;
    }

    public function getSubUser($uid,$room_id)
    {
        $model = new Model_User();
        $rs = $model->getSubUser($uid,$room_id);
        return $rs;
    }

    public function userAction($uid, $json_data)
    {
        $model = new Model_User();
        $rs = $model->userAction($uid, $json_data);
        return $rs;
    }

    public function savebeauty($uid,$client,$data_param)
    {
        $model = new Model_User();
        $rs = $model->savebeauty($uid,$client,$data_param);
        return $rs;
    }

    public function searchUser($uid,$keystring,$p){
        $model = new Model_User();
        $rs = $model->searchUser($uid,$keystring,$p);
        return $rs;
    }

    public function getUserLevel($uid){
        $model = new Model_User();
        $rs = $model->getUserLevel($uid);
        return $rs;
    }
    public function getGameuserinfo($game_user_id,$game_tenant_id){
        $model = new Model_User();
        $rs = $model->getGameuserinfo($game_user_id,$game_tenant_id);
        return $rs;
    }

    public function charge_withdrawn($uid){
        $model = new Model_User();
        $rs = $model->charge_withdrawn($uid);
        return $rs;
    }
    public function charge_gift($uid){
        $model = new Model_User();
        $rs = $model->charge_gift($uid);
        return $rs;
    }
    public function chargegift_send($game_user_id,$price){
        $model = new Model_User();
        $rs = $model->chargegift_send($game_user_id,$price);
        return $rs;
    }
    public function chargegift_list($uid){
        $model = new Model_User();
        $rs = $model->chargegift_list($uid);
        return $rs;
    }

    public  function transfer($uid,$touid,$amount,$user_nicename){
        $model = new Model_User();
        $rs = $model->transfer($uid,$touid,$amount,$user_nicename);
        return $rs;
    }

    public function findUser($userdata){
        $model = new Model_User();
        $rs = $model->findUser($userdata);
        return $rs;
    }

    public function getregUrl(){
        $model = new Model_User();
        $rs = $model->getregUrl();
        return $rs;
    }
    public  function transferOutyuebao($uid,$amount,$data,$type){
        $model = new Model_User();
        $rs = $model->transferOutyuebao($uid,$amount,$data,$type);
        return $rs;
    }
    public  function transferInyuebao($uid,$amount,$data,$type){
        $model = new Model_User();
        $rs = $model->transferInyuebao($uid,$amount,$data,$type);
        return $rs;
    }
    public function settlementYuebao(){
        $model = new Model_User();
        $rs = $model->settlementYuebao();
        return $rs;
    }
    public function transferToyuebaoauto(){
        $model = new Model_User();
        $rs = $model->transferToyuebaoauto();
        return $rs;
    }
    public function openYuebao($type,$uid){
        $model = new Model_User();
        $rs = $model->openYuebao($type,$uid);
        return $rs;
    }

    public function getMySubUserList($uid, $type){
        $model = new Model_User();
        $rs = $model->getMySubUserList($uid, $type);
        return $rs;
    }
    public function nftConsumption($uid,$amount, $type){
        $model = new Model_User();
        $rs = $model->nftConsumption($uid,$amount, $type);
        return $rs;
    }
    public function lotteryConsumption($uid,$amount, $type){
        $model = new Model_User();
        $rs = $model->lotteryConsumption($uid,$amount, $type);
        return $rs;
    }
    public function shopConsumption($uid,$amount, $type, $shoppingVoucherId){
        $model = new Model_User();
        $rs = $model->shopConsumption($uid,$amount, $type, $shoppingVoucherId);
        return $rs;
    }
    public function shopuserConsumption($uid, $amount, $type, $shoptoken, $ids, $shop_order_id,$cg_order_id, $shop_order_no, $cg_order_no){
        $model = new Model_User();
        $rs = $model->shopuserConsumption($uid, $amount, $type, $shoptoken, $ids, $shop_order_id,$cg_order_id, $shop_order_no, $cg_order_no);
        return $rs;
    }
    public function shopuserBondpay($uid,$amount, $type,$shoptoken){
        $model = new Model_User();
        $rs = $model->shopuserBondpay($uid,$amount, $type,$shoptoken);
        return $rs;
    }
    public function bindUser($uid,$user_login,$user_pass,$source,$tenantId,$zone,$agent_code){
        $rs = array();
        $model = new Model_User();
        $rs = $model->bindUser($uid,$user_login,$user_pass,$source,$tenantId,$zone,$agent_code);

        return $rs;
    }

    public function sign_in($uid){
        $rs = array();
        $model = new Model_User();
        $rs = $model->sign_in($uid);

        return $rs;
    }

    public function signLog($uid){
        $rs = array();
        $model = new Model_User();
        $rs = $model->signLog($uid);

        return $rs;
    }

    public function signSet(){
        $rs = array();
        $model = new Model_User();
        $rs = $model->signSet();

        return $rs;
    }

    public function accessLog($uid){
        $rs = array();
        $model = new Model_User();
        $rs = $model->accessLog($uid);

        return $rs;
    }

    public function checkUserLogin($user_login){
        $rs = array();
        $model = new Model_User();
        $rs = $model->checkUserLogin($user_login);

        return $rs;
    }
    public function goodsToshopowner(){
        $model = new Model_User();
        $rs = $model->goodsToshopowner();
        return $rs;
    }

    public function getInviteCode($uid)
    {
        $model = new Model_User();
        $code = $model->getInviteCode($uid);
        if(empty($code) || empty($code['agent_code'])){
            $str = $this->generateInviteCode();
            $exists = $model->checkInviteCode($uid,$str);
            while(!$exists){
                $str = $this->generateInviteCode();
                $exists = $model->checkInviteCode($uid,$str);
            }
            $model->userUpdate($uid,array("agent_code"=>$str));
            $code = $model->getInviteCode($uid);
        }
        return $code;

    }

    public function generateInviteCode(){
        $str = '0123456789abcdefghijklmnopqrstuvwxyz';
        $str = substr(str_shuffle($str), 0, 20);
        $str = substr($str,-6);
        return Strtoupper($str);
    }



    public function summaryAgent(){
        $model = new Model_User();
        $rs = $model->summaryAgent();
        return $rs;
    }

    public function summaryDownload(){
        $model = new Model_User();
        $rs = $model->summaryDownload();
        return $rs;
    }
    

    public function addDownload($code){
        $model = new Model_User();
        $rs = $model->addDownload($code);
        return $rs;
    }

    public function AddCoin($uid){
        $model = new Model_User();
        $rs = $model->AddCoin($uid);
        return $rs;
    }

    public function getInvitedList($uid,$p){
        $model = new Model_User();
        $rs = $model->getInvitedList($uid,$p);
        return $rs;
    }

    public function getAttentList($uid,$page)
    {
        $model = new Model_User();
        $rs = $model->getAttentList($uid,$page);
        return $rs;
    }
}
