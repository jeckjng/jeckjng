<?php
/**
 * Created by PhpStorm.
 * User:bill
 * Date: 2021/5/30
 * Time: 23:00
 */
class Model_Usdt extends PhalApi_Model_NotORM {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function bindingUsdtAddress($uid, $address, $network_type, $qrcode){
        $tenant_id = getTenantId();

        if(!in_array($network_type, ['TRC20', 'ERC20'])){
            return array('code' => 703, 'msg' => codemsg(703), 'info' => []);
        }

        $rs = DI()->notorm->usdt_address
            ->where("uid = ? and address = ?", intval($uid), trim($address))
            ->fetchOne();
        if($rs){
            return array('code' => 620, 'msg' => codemsg(620), 'info' => []);
        }

        $config = getConfigPub();
        // 同一USDT地址绑定限制
        if($config['usdt_address_bind_limit_count'] > 0 && $config['usdt_address_bind_limit_day'] > 0 ){
            $start_time = time() - $config['usdt_address_bind_limit_day'] * 60*60*24;
            $end_time = time();
            $usdt_address_bind_count = DI()->notorm->usdt_address->where("address = ? and create_time >= ? and create_time <= ? and uid != ?", trim($address), $start_time, $end_time, intval($uid))->count();
            if(($usdt_address_bind_count+1) > $config['usdt_address_bind_limit_count']){
                return array('code' => 621, 'msg' => codemsg(621), 'info' => []);
            }
        }
        // 同一会员绑定限制
        if($config['usdt_bind_count_one_user'] > 0){
            $one_user_bind_count = DI()->notorm->usdt_address->where("uid = ?", intval($uid))->count();
            if(($one_user_bind_count+1) > $config['usdt_bind_count_one_user']){
                return array('code' => 623, 'msg' => codemsg(623), 'info' => []);
            }
        }

        $user_info = getUserInfo($uid);
        $data = array(
            'uid' => intval($uid),
            'user_login' => $user_info['user_login'],
            'user_type' => $user_info['user_type'],
            'tenant_id' => intval($tenant_id),
            'create_time' => time(),
            'address' => trim($address),
            'network_type' => trim($network_type),
            'qrcode' => trim(urldecode($qrcode)),
            'status' => 1,
        );
        try{
            DI()->notorm->usdt_address->insert($data);
        }catch (\Exception $e){
            logapi(['err_msg'=>$e->getMessage(), 'insert_data'=>$data],'【USDTaddress绑定失败】');
            return array('code' => 2034, 'msg' => codemsg(2034), 'info' => [$e->getMessage()]);
        }

        return array('code' => 0, 'msg' => '', 'info' => []);
    }

    public function myUsdtAddress($uid){
        $tenant_id =getTenantId();
        $list = DI()->notorm->usdt_address
            ->select('id,address,network_type,qrcode')
            ->where("uid = ? and tenant_id = ?", intval($uid), intval($tenant_id))
            ->fetchAll();

        foreach ($list as $key=>$val){
            $list[$key]['id'] = intval($val['id']);
        }

        return array('code' => 0, 'msg' => '', 'info' => $list);
    }

}