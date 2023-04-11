<?php

class Domain_Home {

    public function getSlide($tenantId,$cat_name) {
        $rs = array();
        $model = new Model_Home();
        $rs = $model->getSlide($tenantId,$cat_name);
        return $rs;
    }
		
	public function getHot($p,$tenantId,$liveclassid,$ishot,$isrecommend) {
        $rs = array();

        $model = new Model_Home();
        $rs = $model->getHot($p,$tenantId,$liveclassid,$ishot,$isrecommend);
				
        return $rs;
    }

    public function getHotList($tenantId) {
        $rs = array();

        $model = new Model_Home();
        $rs = $model->getHot(null,$tenantId);

        return $rs;
    }
		
	public function getFollow($uid,$p,$tenantId) {
        $rs = array();
				
        $model = new Model_Home();
        $rs = $model->getFollow($uid,$p,$tenantId);
				
        return $rs;
    }
		
	public function getNew($lng,$lat,$p,$tenantId) {
        $rs = array();

        $model = new Model_Home();
        $rs = $model->getNew($lng,$lat,$p,$tenantId);
				
        return $rs;
    }
		
	public function search($uid,$key,$p,$tenantId) {
        $rs = array();

        $model = new Model_Home();
        $rs = $model->search($uid,$key,$p,$tenantId);
				
        return $rs;
    }
	
	public function getNearby($lng,$lat,$p,$tenantId) {
        $rs = array();

        $model = new Model_Home();
        $rs = $model->getNearby($lng,$lat,$p,$tenantId);
				
        return $rs;
    }
	
	public function getRecommend($tenantId) {
        $rs = array();

        $model = new Model_Home();
        $rs = $model->getRecommend($tenantId);
				
        return $rs;
    }
	
	public function attentRecommend($uid,$touid) {
        $rs = array();

        $model = new Model_Home();
        $rs = $model->attentRecommend($uid,$touid);
				
        return $rs;
    }

    public function profitList($uid,$type,$p){
        $rs = array();

        $model = new Model_Home();
        $rs = $model->profitList($uid,$type,$p);
                
        return $rs;
    }

    public function consumeList($uid,$type,$p,$touid){
        $rs = array();

        $model = new Model_Home();
        $rs = $model->consumeList($uid,$type,$p,$touid);
                
        return $rs;
    }
    public function consumeListall($uid,$type,$p){
        $rs = array();

        $model = new Model_Home();
        $rs = $model->consumeListall($uid,$type,$p);

        return $rs;
    }

    public function getClassLive($liveclassid,$p,$tenantId){
        $rs = array();

        $model = new Model_Home();
        $rs = $model->getClassLive($liveclassid,$p,$tenantId);
                
        return $rs;
    }

    public function getLive($tenantId){

        $model = new Model_Home();
        $rs = $model->getLive($tenantId);
        return $rs;
    }
    public function getBetinfo($tenantId) {
        $rs = array();
        $model = new Model_Home();
        $rs = $model->getBetinfo($tenantId);
        return $rs;
    }
    public function recommendRoom($tenantId){
        $model = new Model_Home();
        $rs = $model->recommendRoom($tenantId);
        return $rs;
    }
    public function getAutotask($tenantId){
        $model = new Model_Home();
        $rs = $model->getAutotask($tenantId);
        return $rs;
    }
    public function shareCollcet(){
        $model = new Model_Home();
        $rs = $model->shareCollcet();
        return $rs;
    }
    public function basicsalaryCollcet(){
        $model = new Model_Home();
        $rs = $model->basicsalaryCollcet();
        return $rs;
    }
    public function consumptionCollcet(){
        $model = new Model_Home();
        $rs = $model->consumptionCollcet();
        return $rs;
    }

    public function appHeartbeat($version, $client, $uid){
        $model = new Model_Home();
        $rs = $model->appHeartbeat($version, $client, $uid);
        return $rs;
    }

    public function updateVersion($version, $client, $uid){
        $model = new Model_Home();
        $rs = $model->updateVersion($version, $client, $uid);
        return $rs;
    }


}
