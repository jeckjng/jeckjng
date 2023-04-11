<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 22:59
 */
class Domain_Chatroom
{
    public function createChatRoom($uid,$title,$avatar)
    {
        $model = new Model_Chatroom();
        $rs = $model->createChatRoom($uid,$title,$avatar);
        return $rs;
    }

    public function createPrivateChatRoom($uid,$avatar)
    {
        $model = new Model_Chatroom();
        $rs = $model->createPrivateChatRoom($uid,$avatar);
        return $rs;
    }


    public  function createPrivateChatRoomP($uid,$puid)
    {
        $model = new Model_Chatroom();
        $rs=$model->createPrivateChatRoomP($uid,$puid);
        return $rs;
    }


    public function getPrivateChatRooms($uid)
    {
        $model = new Model_Chatroom();
        $res=$model->getPrivateChatRooms($uid);
        return $res;
    }

    public function getUsersCustomerS($uid)
    {
        $model = new Model_Chatroom();
        $res=$model->getUsersCustomerS($uid);
        return $res;
    }
    public function getCustomerVideo($puid,$uid,$p)
    {
        $model = new Model_Chatroom();
        $res=$model->getCustomerVideo($puid,$uid,$p);
        return $res;
    }
    public function getPrivateChatRoomRecords($uid)
    {
        $model = new Model_Chatroom();
        $res=$model->getPrivateChatRoomRecords($uid);
        return $res;
    }
    public function getUsersCustomerList($uid)
    {
        $model = new Model_Chatroom();
        $res= $model->getUsersCustomerList($uid);
        return $res;
    }
    public function editChatRoom($uid,$room_id,$title,$avatar)
    {
        $model = new Model_Chatroom();
        $rs = $model->editChatRoom($uid,$room_id,$title,$avatar);
        return $rs;
    }

    public function addRoomFriends($uid,$room_id,$friend_id)
    {
        $model = new Model_Chatroom();
        $rs = $model->addRoomFriends($uid,$room_id,$friend_id);
        return $rs;
    }

    public function getChatRooms($uid)
    {
        $model = new Model_Chatroom();
        $rs = $model->getChatRooms($uid);
        return $rs;
    }

    public function getRoomFriends($uid,$room_id)
    {
        $model = new Model_Chatroom();
        $rs = $model->getRoomFriends($uid,$room_id);
        return $rs;
    }

    public function setRoomManager($uid,$room_id,$type,$friend_ids)
    {
        $model = new Model_Chatroom();
        $rs = $model->setRoomManager($uid,$room_id,$type,$friend_ids);
        return $rs;
    }

    public function kickoutRoomFriend($uid,$room_id,$friend_id)
    {
        $model = new Model_Chatroom();
        $rs = $model->kickoutRoomFriend($uid,$room_id,$friend_id);
        return $rs;
    }

    public function muteRoomFriend($uid,$room_id,$type,$friend_id,$status)
    {
        $model = new Model_Chatroom();
        $rs = $model->muteRoomFriend($uid,$room_id,$type,$friend_id,$status);
        return $rs;
    }

    public function getRoomRecord($uid,$room_id,$record_id,$type,$limit)
    {
        $model = new Model_Chatroom();
        $rs = $model->getRoomRecord($uid,$room_id,$record_id,$type,$limit);
        return $rs;
    }

    public function searchFriend($uid,$room_id,$friend)
    {
        $model = new Model_Chatroom();
        $rs = $model->searchFriend($uid,$room_id,$friend);
        return $rs;
    }

    public function getOrdinaryMember($uid,$room_id)
    {
        $model = new Model_Chatroom();
        $rs = $model->getOrdinaryMember($uid,$room_id);
        return $rs;
    }

}