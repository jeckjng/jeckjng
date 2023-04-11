<?php
namespace Admin\Model;

use Think\Controller;
use Admin\Model\AdminModelBaseModel;
use Admin\Model\UsersCoinrecordModel;
use Admin\Model\UsersVipModel;
use Admin\Model\UsersCashrecordModel;
use Admin\Model\UsersChargeAdminModel;
use Admin\Model\UserTransferYuebaoModel;
use Admin\Model\YuebaoRateModel;
use Admin\Model\VideoModel;
use Admin\Model\VideoLongModel;
use Admin\Model\VideoUplodeRewardModel;
use Admin\Model\AgentRewardModel;
use Admin\Model\UsersVideoBuyModel;
use Admin\Model\VideoProfitModel;
use Admin\Model\UsersAgentModel;

class CommonModel extends AdminModelBaseModel {

    private static $instance;

    static public function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function updateUserTypeWithUid($uid, $user_type){
        UsersCoinrecordModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        UsersChargeModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        UsersVipModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        UsersChargeAdminModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        UserTransferYuebaoModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        YuebaoRateModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        VideoModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        VideoLongModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        VideoUplodeRewardModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        AgentRewardModel::getInstance()->updateUserTypeWithPid($uid, $user_type);

        UsersVideoBuyModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        VideoProfitModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        UsersCashrecordModel::getInstance()->updateUserTypeWithUid($uid, $user_type);

        UsersAgentModel::getInstance()->updateUserTypeWithUid($uid, $user_type);
    }

    public function autoUpdateUserType(){
        UsersCoinrecordModel::getInstance()->autoUpdateUserType();

        UsersChargeModel::getInstance()->autoUpdateUserType();

        UsersVipModel::getInstance()->autoUpdateUserType();

        UsersCashrecordModel::getInstance()->autoUpdateUserType();

        UsersChargeAdminModel::getInstance()->autoUpdateUserType();

        UserTransferYuebaoModel::getInstance()->autoUpdateUserType();

        YuebaoRateModel::getInstance()->autoUpdateUserType();

        VideoModel::getInstance()->autoUpdateUserType();

        VideoLongModel::getInstance()->autoUpdateUserType();

        VideoUplodeRewardModel::getInstance()->autoUpdateUserType();

        AgentRewardModel::getInstance()->autoUpdateUserType();

        UsersVideoBuyModel::getInstance()->autoUpdateUserType();

        VideoProfitModel::getInstance()->autoUpdateUserType();

        UsersAgentModel::getInstance()->autoUpdateUserType();
    }


}
