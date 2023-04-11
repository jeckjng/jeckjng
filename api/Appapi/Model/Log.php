<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_Log extends PhalApi_Model_NotORM {

    /*
     * socket错误日志记录
     * */
    public function SocketError($uid, $socket_url, $event, $type, $send_ct, $description){
        if($description=='' || $description==null){
            return array('code' => 703, 'msg' => codemsg(703), 'info' => []);
        }

        try {
            //开始数据库事务
            beginTransaction();

            $data = array(
                'socket_url' => $socket_url,
                'event' => $event,
                'uid' => intval($uid),
                'type' => intval($type),
                'send_ct' => $send_ct,
                'description' => $description,
                'remark' => "",
                'ip' => get_client_ip(0, true),
                'tenant_id' => intval(getTenantId()),
                'ctime' => time(),
            );
            $res = DI()->notorm->log_socket->insert($data);
            commitTransaction();
        }catch (Exception $ex) {
            rollbackTransaction();
            return array('code' => 2034, 'msg' => codemsg(2034), 'info' => ['socket记录错误【'.$ex->getMessage().'】']);
        }

        return array('code' => 0, 'msg' => '', 'info' => [$res]);
    }


}