<?php
namespace App\Extend;

use  App\Model\Table\kaTable;
use App\Model\Table\usersbetTable;
use EasySwoole\ORM\DbManager;
use App\Model\Table\payTable;
use EasySwoole\Mysqli\QueryBuilder;
use App\Model\Table\cmfusersTable;
use App\Model\Table\usersliveTable;
use App\Model\Table\tenantTable;
use App\Model\Table\recommendTable;

class UserModel{

    public function getOrderData($uid){
        if (empty($uid)) {
            return [400, []];
        }
        //返回一条记录
        $orderData = DbManager::getInstance()->invoke(function ($client) use ($uid) {
            return cmfusersTable::invoke($client)
                ->field('*')
                ->get(['id' => $uid]);
        });
        if (!$orderData) {
            return [402, []];
        }
        return [200, $orderData];


    }
    public function getalive($tenant_id){
        $tenant_id = $tenant_id;
        if (empty($tenant_id)) {
            return [400, []];
        }
        //返回一条记录
        $orderData = DbManager::getInstance()->invoke(function ($client) use ($tenant_id) {
            return usersliveTable::invoke($client)
                ->field('*')
                ->all(['islive' => 1,'tenant_id'=>$tenant_id]);
        });
        if (!$orderData) {
            return [402, []];
        }
        return $orderData;



    }

    public function getrecommend($tenant_id){
        $tenant_id = $tenant_id;
        if (empty($tenant_id)) {
            return [400, []];
        }
        //返回一条记录
        $orderData = DbManager::getInstance()->invoke(function ($client) use ($tenant_id) {
            return usersliveTable::invoke($client)
                ->field('*')
                ->all(['islive' => 1,'isrecommendroom'=>1]);
        });
        if (!$orderData) {
            return [];
        }
        return $orderData;



    }
    public function getrecommendtid($tenant_id){
        $tenant_id = $tenant_id;
        if (empty($tenant_id)) {
            return [400, []];
        }
        //返回一条记录
        $orderData = DbManager::getInstance()->invoke(function ($client) use ($tenant_id) {
            return tenantTable::invoke($client)
                ->field('*')
                ->all(['status' => 1,'id'=>$tenant_id]);
        });
        if (!$orderData) {
            return [402, []];
        }
        return $orderData;



    }
    public function getbetinfo($id){


        $id = $id;
        if (empty($id)) {
            return [400, []];
        }
        //返回一条记录
        $orderData = DbManager::getInstance()->invoke(function ($client) use ($id) {
            return usersbetTable::invoke($client)
                ->field('*')
                ->get(['id'=>$id]);
        });
        if (!$orderData) {
            return [402, []];
        }
        return $orderData;



    }

    public function getbetinfos($tenant_id){

        if (empty($tenant_id)) {
            return [400, []];
        }
        //返回一条记录
        $orderData = DbManager::getInstance()->invoke(function ($client) use ($tenant_id) {
            return usersbetTable::invoke($client)
                ->field('id')
                ->all(['tenant_id'=>$tenant_id]);
        });
        if (!$orderData) {
            return [402, []];
        }
        return $orderData;




    }
    public function getrecommendcount($tenant_id){

        if (empty($tenant_id)) {
            return [400, []];
        }
        //返回一条记录
        $orderData = DbManager::getInstance()->invoke(function ($client) use ($tenant_id) {
            return recommendTable::invoke($client)
                ->field('*')
                ->all(['tenant_id'=>$tenant_id]);
        });
        if (!$orderData) {
            return [402, []];
        }
        return $orderData;


    }





}

?>
