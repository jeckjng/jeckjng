<?php

class Domain_Log {
    public function SocketError($uid, $socket_url, $event, $type, $send_ct, $description) {
        $rs = array();

        $model = new Model_Log();
        $rs = $model->SocketError($uid, $socket_url, $event, $type, $send_ct, $description);

        return $rs;
    }

}