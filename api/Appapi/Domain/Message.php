<?php

class Domain_Message {
	public function getList($uid,$p) {
		$rs = array();

		$model = new Model_Message();
		$rs = $model->getList($uid,$p);

		return $rs;
	}

    public function pushgamedata($data) {
        $rs = array();

        $model = new Model_Message();
        $rs = $model->pushgamedata($data);

        return $rs;
    }
	
}
