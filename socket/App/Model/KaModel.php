<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 2021/3/13
 * Time: 21:17
 */

namespace App\Model;

use App\Model\Table\kaTable;
use EasySwoole\ORM\DbManager;
use App\Model\Table\payTable;
use EasySwoole\Mysqli\QueryBuilder;
use App\Model\Table\cmfusersTable;
use App\Model\Table\usersChatroomTable;
use App\Model\Table\usersChatroomRecordTable;

class  KaModel
{
    //获取CODE
    public function getCode($token)
    {
        $code = DbManager::getInstance()->invoke(function ($client) use ($token) {
            return kaTable::invoke($client)->field('lhh')->get(['token' => $token]);
        });
        if ($code) {
            return [200, $code];
        }
        return [400, []];
    }

    /**
     * 判断是否开启
     */
    public function getStatus($mobile)
    {
        $status = DbManager::getInstance()->invoke(function ($client) use ($mobile) {
            return kaTable::invoke($client)->field('status,ka,id')->get(['mobile' => $mobile]);
        });
        if ($status['status'] == 1) {
            return [200, $status];
        }
        return [400, []];
    }

    /***
     * 写入金额
     **/
    public function setBalance($mobile, $balance)
    {

        $queryBind=new QueryBuilder();
        $queryBind->raw("update fx_ka set balance= ?  where mobile=?",[$balance,$mobile]);
        $SvaeStatus = DbManager::getInstance()->invoke(function ($client) use ($queryBind) {
            return kaTable::invoke($client)->query($queryBind);
        });
        if ($SvaeStatus == true) {
            return [200, $SvaeStatus];
        }
        return [400, $SvaeStatus];

    }

    /***
     * 设置订单状态
     */
    public function setOrderStatus($id, $status)
    {
        $status = DbManager::getInstance()->invoke(function ($client) use ($id, $status) {
            return payTable::invoke($client)->where(['id' => $id])->update(['status' => $status]);
        });
        if ($status == false) {
            return [400, []];
        }
        return [200, [true]];
    }

    /**
     * 获取KA号
     ***/
    public function getKaNum($mobile)
    {
        if (empty($mobile)) {
            return [400, []];
        }
        $ka = DbManager::getInstance()->invoke(function ($client) use ($mobile) {
            return payTable::invoke($client)->field('ka')->get(['mobile' => $mobile]);
        });
        if (!$ka) {
            return [402, []];
        }
        return [200, $ka];
    }

    /***
     * 操做订单
     ***/
    public function getOrderData($ka)
    {
        if (empty($ka)) {
            return [400, []];
        }
        //返回一条记录
        $orderData = DbManager::getInstance()->invoke(function ($client) use ($ka) {
            return payTable::invoke($client)
                ->field('ddh,money,realname,ka,address,id,notifyurl')
                ->order('id', 'ASC')
                ->get(['kahao' => $ka, 'status' => 0]);
        });
        if (!$orderData) {
            return [402, []];
        }
        return [200, $orderData];
    }

    /**
     * 获取最后一条执行的SQL
     **/
    public function getLastSql()
    {
        return DbManager::getInstance()->getLastQuery();
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo($uid,$field='*')
    {
        $user_info = DbManager::getInstance()->invoke(function ($client) use ($uid,$field) {
            return cmfusersTable::invoke($client)->field($field)->get(['id' => $uid]);
        });

        return $user_info;
    }

    /**
     * 获取聊天室信息
     */
    public function getChatRoomInfo($id,$field='*')
    {
        $room_info = DbManager::getInstance()->invoke(function ($client) use ($id,$field) {
            return usersChatroomTable::invoke($client)->field($field)->get(['id' => $id]);
        });

        return $room_info;
    }

    /**
     * 记录聊天室历史信息
     */
    public function addChatRoomRecord($data)
    {
        $res = DbManager::getInstance()->invoke(function ($client) use ($data) {
            return usersChatroomRecordTable::invoke($client)->data($data)->save();
        });

        return $res;
    }

    /**
     * 获取聊天室历史信息
     */
    public function getChatRoomRecord($where,$field='*')
    {
        $record_list = DbManager::getInstance()->invoke(function ($client) use ($where,$field) {
            return usersChatroomRecordTable::invoke($client)->field($field)->order('id','ASC')->all($where);
        });

        return $record_list;
    }


}