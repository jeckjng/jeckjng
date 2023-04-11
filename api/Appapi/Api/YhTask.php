<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 20:38
 */
class Api_YhTask extends PhalApi_Api
{
    public function getRules()
    {
        return array(
            'getYhTaskClassification' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'client' => array('name' => 'client', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '客户端：1 PC，2 H5，3 Android，4 iOS'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '任务等级：1初级任务，2中级任务，3高级任务 （暂时固定传1）'),
            ),
            'addYhUserTask' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'task_id' => array('name' => 'task_id', 'type' => 'int', 'require' => true, 'desc' => '任务中心任务ID号'),
                'client' => array('name' => 'client', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '客户端：1 PC，2 H5，3 Android，4 iOS'),
            ),
            'finishYhUserTask' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'user_task_id' => array('name' => 'user_task_id', 'type' => 'int', 'require' => true, 'desc' => '用户任务ID号'),
            ),

            'getTaskRewardLogInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'reward_log_id' => array('name' => 'reward_log_id', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '任务明细ID号'),
            ),
        );
    }

    /**
     * 获取奖励明细详情
     * @desc 用于获取奖励明细详情
     * @return int code 状态码：0 成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return string info.task_id 任务ID号
     * @return string info.user_task_id 用户任务ID号
     * @return string info.reward_start_vip 用户奖励前VIP等级
     * @return string info.reward_end_vip 用户奖励后VIP等级
     * @return string info.type 任务类型：1初级任务，2中级任务，3高级任务
     * @return string info.name 任务名称
     * @return string info.reward_type 奖励类型：1 奖励1（奖励金币），2 奖励2（vip等级提升）
     * @return string info.reward1 奖励1（金额）
     * @return string info.reward2_upgrade_vip 奖励2（VIP等级升级）：0否，1是
     * @return string info.reward_start_amount 奖励前金额
     * @return string info.reward_result 奖励结果
     * @return string info.reward_end_amount 奖励后金额
     * @return string info.giveout_type 发放类型： 0系统自动发放，1人工审核
     * @return string info[0].mtime 发放时间
     */
    public function getTaskRewardLogInfo()
    {
        $rs = array('code' => 0, 'msg' => '获取奖励明细详情', 'info' => array());
        $uid = $this->uid;
        $language_id = $this->language_id ? $this->language_id : 101;
        $token = checkNull($this->token);
        $reward_log_id = $this->reward_log_id;

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Task();
        $info = $domain->getTaskRewardLogInfo($uid,$reward_log_id);
        $rs['info'] = $info;
        return $rs;
    }

    //-------------------------------------------

    /**
     * 任务中心获取任务列表
     * @desc 用于任务中心获取任务列表
     * @return int code 状态码：0 成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info[0].task_classification_id 任务分类ID号
     * @return float info[0].unlock_amount 解锁金额
     * @return string info[0].logo 分类LOGO
     * @return string info[0].bgimg 分类背景图
     * @return int info[0].experience_shop 是否体验商城: 0 否，1 是
     * @return int info[0].task_id 任务ID号
     * @return string info[0].name 任务名称
     * @return int info[0].reward1 奖励1 奖励钻石数量 大于0展示
     * @return int info[0].reward1_number 奖励3 转盘次数 大于0展示
     * @return float info[0].price 任务价格
     * @return float info[0].reward2_upgrade_vip 奖励2 完成任务是否升级VIP 0否 1是-暂时不展示
     * @return string info[0].img 任务图片
     * @return int info[0].user_task_status 用户完成状态：0初始状态（可点击领取），1进行中，2审核中 3.已完成 5.待提交（这个状态用户可以提交完成任务）
     * @return int info[0].user_task_id 用户任务ID
     */
    public function getYhTaskClassification()
    {
        $rs = array('code' => 0, 'msg' => '任务中心获取任务列表', 'info' => array());

        $uid = $this->uid;
        $language_id = $this->language_id ? $this->language_id : 101;
        $token = checkNull($this->token);
        $type = $this->type;
        $client = $this->client;

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_YhTask();
        $info = $domain->getYhTaskClassification($uid,$client,$type);
        $rs['info'] = $info;

        return $rs;
    }

    /**
     * 新增一个用户任务
     * @desc 用于领取一个新任务
     * @return int code 状态码：0 成功
     * @return string msg 提示信息（code: 2006，msg：金币不足，这时候需要跳转去充值）
     * @return array info 列表数据
     * @return float info.price 任务价格（状态码为2006（余额不足）时返回）
     * @return int info.user_task_id 用户任务ID号（状态码为0时返回）
     */
    public function addYhUserTask()
    {
        $rs = array('code' => 0, 'msg' => '新增任务成功', 'info' => array());
        $uid = $this->uid;
        $language_id = $this->language_id ? $this->language_id : 101;
        $token = checkNull($this->token);
        $task_id = $this->task_id;
        $client = $this->client;

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_YhTask();
        $info = $domain->addUserTask($uid, $task_id, $client);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 确认完成任务成功
     * @desc 用于确认完成任务成功
     * @return int code 状态码：0 成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info.status 用户任务状态：2审核中，3审核通过（已完成），4审核拒绝（状态码为0时返回）5.待提交
     * @return float info.commission 佣金（状态码为0时返回）
     * @return float info.price 任务价格（状态码为0或者2006（余额不足）时返回）
     */
    public function finishYhUserTask()
    {
        $rs = array('code' => 0, 'msg' => '确认完成任务成功', 'info' => array());
        $uid = $this->uid;
        $language_id = $this->language_id ? $this->language_id : 101;
        $token = checkNull($this->token);
        $user_task_id = $this->user_task_id;

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_YhTask();
        $info = $domain->finishUserTask($uid,$user_task_id);
        $rs['info'] = isset($info['info']) ? $info['info'] : $rs['info'];

        if($info['code']==1007){
            $language = DI()->config->get('language.action_fail');
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : $language[$language_id];
            return $rs;
        }
        if ($info['code'] ===2006){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '余额不足，请及时充值';
            return $rs;
        }
        if($info['code']==2007){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '不可提现金额不足,请联系客服';
            return $rs;
        }
        if($info['code']==2011){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '状态不是进行中';
            return $rs;
        }
        if($info['code']==2012){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '已超时';
            return $rs;
        }
        if($info['code']==2013){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '每天只能完成一次';
            return $rs;
        }
        return $rs;
    }
}
