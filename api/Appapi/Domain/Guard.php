<?php

class Domain_Guard {
	public function getGuardList($data) {
		$rs = array();

		$model = new Model_Guard();
		$rs = $model->getGuardList($data);

		return $rs;
	}

	public function getList($tenantId) {
		$rs = array();

		$model = new Model_Guard();
		$rs = $model->getList($tenantId);

		return $rs;
	}

	public function buyGuard($data) {
		$rs = array();

		$model = new Model_Guard();
		$rs = $model->buyGuard($data);

		return $rs;
	}
    public function buyGuardalone($data) {
        $rs = array();

        $model = new Model_Guard();
        $rs = $model->buyGuardalone($data);

        return $rs;
    }

	public function getUserGuard($uid,$liveuid) {
		$rs = array();

		$model = new Model_Guard();
		$rs = $model->getUserGuard($uid,$liveuid);

		return $rs;
	}

	public function getGuardNums($liveuid) {
		$rs = array();

		$model = new Model_Guard();
		$rs = $model->getGuardNums($liveuid);

		return $rs;
	}
    public function getGuardrobot($liveuid) {
        $rs = array();

        $model = new Model_Guard();
        $rs = $model->getGuardrobot($liveuid);

        return $rs;
    }
	
}
