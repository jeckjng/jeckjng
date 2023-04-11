<?php
/**
 * Created by PhpStorm.
 * User: bill
 * Date: 2021/5/30
 * Time: 20:38
 */
class Api_Task extends PhalApi_Api
{
    public function getRules()
    {
        return array(
            'getTaskClassification' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'client' => array('name' => 'client', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '客户端：1 PC，2 H5，3 Android，4 iOS'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '任务等级：1初级任务，2中级任务，3高级任务'),
            ),
            'getTask' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'client' => array('name' => 'client', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '客户端：1 PC，2 H5，3 Android，4 iOS'),
                'task_classification_id' => array('name' => 'task_classification_id', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '任务分类ID号'),
            ),
            'addUserTask' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'task_id' => array('name' => 'task_id', 'type' => 'int', 'require' => true, 'desc' => '任务中心任务ID号'),
                'client' => array('name' => 'client', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '客户端：1 PC，2 H5，3 Android，4 iOS'),
            ),
            'getUserTask' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '任务等级：1初级任务，2中级任务，3高级任务'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
            ),
            'getUserTaskInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'user_task_id' => array('name' => 'user_task_id', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '用户任务ID号'),
            ),
            'finishUserTask' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'user_task_id' => array('name' => 'user_task_id', 'type' => 'int', 'require' => true, 'desc' => '用户任务ID号'),
            ),
            'getTaskRewardLog' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '任务等级：1初级任务，2中级任务，3高级任务'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
            ),
            'getTaskRewardLogInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'reward_log_id' => array('name' => 'reward_log_id', 'type' => 'int', 'require' => true, 'default'=>'', 'desc' => '任务明细ID号'),
            ),
        );
    }

    /**
     * 任务中心获取任务列表
     * @desc 用于任务中心获取任务列表
     * @return int code 状态码：0 成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info[0].task_classification_id 任务分类ID号
     * @return int info[0].status 用户完成状态：0初始状态（可点击领取），1进行中，2审核中
     * @return int info[0].price 任务价格
     * @return string info[0].logo 分类LOGO
     * @return string info[0].bgimg 分类背景图
     * @return int info[0].user_task_id 用户任务ID号
     * @return float info[0].unlock_amount 解锁金额
     * @return int info[0].experience_shop 是否体验商城: 0 否，1 是
     */
    public function getTaskClassification()
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
        $domain = new Domain_Task();
        $info = $domain->getTaskClassification($uid,$client,$type);
        $rs['info'] = $info;

        return $rs;
    }

    /**
     * 任务中心随即获取一个任务
     * @desc 用于任务中心随即获取一个任务
     * @return int code 状态码：0 成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info.task_id 任务中心任务ID号
     * @return string info.name 任务名称
     * @return string info.description 任务描述
     * @return float info.price 任务价格
     * @return int info.start_time 生效时间
     * @return int info.end_time 失效时间
     * @return string info.img 图片地址
     * @return float info.reward1 完成奖励1
     * @return int info.reward2_upgrade_vip 完成奖励2：是否升级VIP等级: 0否，1是
     * @return int info.task_details_type 任务详情类型：0富文本，1网页
     * @return string info.task_details 任务详情说明
     * @return float info.unlock_amount 解锁金额（状态码为2006（余额不足）时返回）
     * @return string info.direct_invitation 直邀人数（状态码为2009（直邀人数不够）时返回）
     */
    public function getTask()
    {
        $rs = array('code' => 0, 'msg' => '任务中心随即获取一个任务', 'info' => array());
        $uid = $this->uid;
        $language_id = $this->language_id ? $this->language_id : 101;
        $token = checkNull($this->token);
        $client = $this->client;
        $task_classification_id = $this->task_classification_id;

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Task();
        $info = $domain->getTask($uid,$client,$task_classification_id);
        $rs['info'] = isset($info['info']) ? $info['info'] : $rs['info'];

        if($info['code']==2002){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '任务间隔时间太短';
            return $rs;
        }
        if($info['code']==2003){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '分类状态未开启';
            return $rs;
        }
        if($info['code']==2004){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '分类下有任务未完成';
            return $rs;
        }
        if($info['code']==2005){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '游客不允许操作';
            return $rs;
        }
        if ($info['code'] ===2006){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '余额不足，请及时充值';
            return $rs;
        }
        if($info['code']==2009){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '直邀人数不够';
            return $rs;
        }
        if($info['code']==2017){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'] ? $info['msg'] : '没有可领取的任务';
            return $rs;
        }

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
    public function addUserTask()
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
        $domain = new Domain_Task();
        $info = $domain->addUserTask($uid, $task_id, $client);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取用户任务
     * @desc 用于获取用户任务
     * @return int code 状态码：0 成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return string info[0].user_task_id 用户任务ID号
     * @return string info[0].status 用户任务状态：0已取消，1进行中，2审核中，3审核通过（已完成），4审核拒绝
     * @return float info[0].unlock_amount 解锁金额
     * @return string info[0].name 任务名称
     * @return string info[0].img 图片地址
     * @return string info[0].reward1 完成奖励1
     * @return string info[0].reward2_upgrade_vip 完成奖励2：是否升级VIP等级: 0否，1是
     * @return int info[0].ctime 任务领取时间
     */
    public function getUserTask()
    {
        $rs = array('code' => 0, 'msg' => '获取用户任务', 'info' => array());
        $uid = $this->uid;
        $language_id = $this->language_id ? $this->language_id : 101;
        $type = $this->type;
        $token = checkNull($this->token);
        $p = $this->p;

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Task();
        $info = $domain->getUserTask($uid,$type,$p);
        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 获取用户任务详情
     * @desc 用于获取用户任务详情
     * @return int code 状态码：0 成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return string info.user_task_id 用户任务ID号
     * @return string info.status 用户任务状态：0已取消，1进行中，2审核中，3审核通过（已完成），4审核拒绝
     * @return string info.name 任务名称
     * @return string info.img 图片地址
     * @return string info.reward1 完成奖励1
     * @return string info.reward2_upgrade_vip 完成奖励2：是否升级VIP等级: 0否，1是
     * @return string info.description 任务描述
     * @return string info.price 任务价格
     * @return string info.start_time 生效时间
     * @return string info.end_time 失效时间
     * @return string info.task_details_type 任务详情类型：0富文本，1网页
     * @return string info.task_details 任务详情说明
     */
    public function getUserTaskInfo()
    {
        $rs = array('code' => 0, 'msg' => '获取用户任务详情', 'info' => array());
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
        $domain = new Domain_Task();
        $info = $domain->getUserTaskInfo($uid,$user_task_id);
        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 确认完成任务成功
     * @desc 用于确认完成任务成功
     * @return int code 状态码：0 成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return int info.status 用户任务状态：2审核中，3审核通过（已完成），4审核拒绝（状态码为0时返回）
     * @return float info.commission 佣金（状态码为0时返回）
     * @return float info.price 任务价格（状态码为0或者2006（余额不足）时返回）
     */
    public function finishUserTask()
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
        $domain = new Domain_Task();
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

    /**
     * 获取任务奖励明细
     * @desc 用于获取任务奖励明细
     * @return int code 状态码：0 成功
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return string info[0].reward_log_id 奖励明细ID号
     * @return string info[0].task_name 任务名称
     * @return string info[0].type 任务等级：1初级，2中级，3高级
     * @return string info[0].reward_type 奖励类型：1 奖励1（奖励金币），2 奖励2（vip等级提升）
     * @return string info[0].reward1 奖励1（金额）
     * @return string info[0].reward2_upgrade_vip 奖励2（VIP等级升级）：0否，1是
     * @return string info[0].reward_result 奖励结果
     * @return string info[0].reward_end_vip 奖励后VIP等级
     * @return string info[0].mtime 发放时间
     */
    public function getTaskRewardLog()
    {
        $rs = array('code' => 0, 'msg' => '获取任务奖励明细', 'info' => array());
        $uid = $this->uid;
        $language_id = $this->language_id ? $this->language_id : 101;
        $token = checkNull($this->token);
        $type = $this->type;
        $p = $this->p;

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_Task();
        $info = $domain->getTaskRewardLog($uid,$type,$p);
        $rs['info'] = $info;
        return $rs;
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

}
