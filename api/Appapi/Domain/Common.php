<?php

class Domain_Common {
    public function getLevelList() {
        $model = new Model_Common();
        $rs = $model->getLevelList();
        return $rs;
    }

    public function getAwardLogList($uid,$page) {
        $model = new Model_Common();
        $rs = $model->getAwardLogList($uid,$page);
        return $rs;
    }
}