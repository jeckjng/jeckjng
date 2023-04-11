<?php
//session_start();
class Api_User extends PhalApi_Api
{

    public function getRules()
    {
        return array(
            'iftoken' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'isUserauth' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
            ),
            'deletezmobile' => array(
                'uid' => array('name' => 'uid', 'type' => 'string', 'require' => true, 'desc' => '用户ID'),
            ),
            'getBaseInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'version_ios' => array('name' => 'version_ios', 'type' => 'string', 'desc' => 'IOS版本号'),
                'version' => array('name' => 'version', 'type' => 'string','default'=>'', 'desc' => 'App版本'),
                'client' => array('name' => 'client', 'type' => 'int', 'default'=>'','desc' => '客户端：1 PC，2 H5，3 Android，4 iOS'),
            ),

            'updateAvatar' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'file' => array('name' => 'file', 'type' => 'file', 'min' => 0, 'max' => 1024 * 1024 * 30, 'range' => array('image/jpg', 'image/jpeg', 'image/png'), 'ext' => array('jpg', 'jpeg', 'png')),
            ),

            'updateFields' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'fields' => array('name' => 'fields', 'type' => 'string', 'require' => true, 'desc' => '修改信息，json字符串'),
            ),
            'updatePass' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'oldpass' => array('name' => 'oldpass', 'type' => 'string', 'desc' => '旧密码'),
                'pass' => array('name' => 'pass', 'type' => 'string', 'require' => true, 'desc' => '新密码'),
                'pass2' => array('name' => 'pass2', 'type' => 'string', 'require' => true, 'desc' => '确认密码'),
            ),
            'checkPaymentPassword' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'password' => array('name' => 'password', 'type' => 'string', 'require' => true, 'desc' => '支付密码'),
            ),
            'updatePaymentPassword' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'old_password' => array('name' => 'old_password', 'type' => 'string', 'require' => true, 'desc' => '原支付密码'),
                'password' => array('name' => 'password', 'type' => 'string', 'require' => true, 'desc' => '支付密码'),
                'confirm_password' => array('name' => 'confirm_password', 'type' => 'string', 'require' => true, 'desc' => '确认密码'),
            ),
              'resetPaymentPassword' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'login_password' => array('name' => 'login_password', 'type' => 'string', 'require' => true, 'desc' => '登录密码'),
                'password' => array('name' => 'password', 'type' => 'string', 'require' => true, 'desc' => '支付密码'),
                'confirm_password' => array('name' => 'confirm_password', 'type' => 'string', 'require' => true, 'desc' => '确认密码'),
            ),
            'getBalance' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),

            'getProfit' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getProfitStat' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'start_time' => array('name' => 'start_time', 'type' => 'string', 'require' => false, 'desc' => '开始时间,格式为yyyy-MM-dd HH:mm:ss'),
                'end_time' => array('name' => 'end_time', 'type' => 'string', 'require' => false, 'desc' => '结束时间,格式为yyyy-MM-dd HH:mm:ss'),
            ),

            'setCash' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'accountid' => array('name' => 'accountid', 'type' => 'int', 'require' => true, 'desc' => '账号ID'),
                'cashvote' => array('name' => 'cashvote', 'type' => 'int', 'require' => true, 'desc' => '提现的票数'),
            ),

            'setAttent' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
            ),
            "getAttentList" => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'page' => array('name' => 'page', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),

            'isAttent' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
            ),

            'isBlacked' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
            ),
            'checkBlack' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
            ),

            'setBlack' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
            ),

            'getBindCode' => array(
                'mobile' => array('name' => 'mobile', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '手机号'),
            ),

            'setMobile' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'mobile' => array('name' => 'mobile', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '手机号'),
                'code' => array('name' => 'code', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '验证码'),
                'agent_code' => array('name' => 'agent_code', 'type' => 'string', 'default'=>'','require' => false, 'desc' => '邀请码'),
            ),

            'getFollowsList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
            ),

            'getFansList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
            ),

            'getBlackList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
            ),

            'getLiverecord' => array(
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
            ),

            'getAliCdnRecord' => array(
                'id' => array('name' => 'id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '直播记录ID'),
            ),

            'getUserHome' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
            ),

            'getContributeList' => array(
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
                'p' => array('name' => 'p', 'type' => 'int', 'default' => '1', 'desc' => '页数'),
            ),

            'getPmUserInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
            ),

            'getMultiInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'uids' => array('name' => 'uids', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '用户ID，多个以逗号分割'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true, 'desc' => '关注类型，0 未关注 1 已关注'),
            ),

            'getUidsInfo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'uids' => array('name' => 'uids', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '用户ID，多个以逗号分割'),
            ),
            'Bonus' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'getBonus' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'setDistribut' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'code' => array('name' => 'code', 'type' => 'string', 'require' => true, 'desc' => '邀请码'),
            ),

            'getUserLabel' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
            ),

            'setUserLabel' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '对方ID'),
                'labels' => array('name' => 'labels', 'type' => 'string', 'require' => true, 'desc' => '印象标签ID，多个以逗号分割'),
            ),

            'getMyLabel' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),

            'getUserAccountList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),

            'setUserAccount' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true, 'desc' => '账号类型，1表示支付宝，2表示微信，3表示银行卡'),
                'account_bank' => array('name' => 'account_bank', 'type' => 'string', 'default' => '', 'desc' => '银行名称'),
                'account' => array('name' => 'account', 'type' => 'string', 'require' => true, 'desc' => '账号'),
                'name' => array('name' => 'name', 'type' => 'string', 'default' => '', 'desc' => '姓名'),
            ),

            'delUserAccount' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'id' => array('name' => 'id', 'type' => 'int', 'require' => true, 'desc' => '账号ID'),
            ),
            'sendGiftDetail' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'start_time' => array('name' => 'start_time', 'type' => 'string', 'require' => false, 'desc' => '开始时间,格式为yyyy-MM-dd HH:mm:ss'),
                'end_time' => array('name' => 'end_time', 'type' => 'string', 'require' => false, 'desc' => '结束时间,格式为yyyy-MM-dd HH:mm:ss'),
                'page' => array('name' => 'page', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '页数'),
            ),
            'receiveGiftDetail' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'start_time' => array('name' => 'start_time', 'type' => 'string', 'require' => false, 'desc' => '开始时间,格式为yyyy-MM-dd HH:mm:ss'),
                'end_time' => array('name' => 'end_time', 'type' => 'string', 'require' => false, 'desc' => '结束时间,格式为yyyy-MM-dd HH:mm:ss'),
                'page' => array('name' => 'page', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '页数'),
            ),
            'editName' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'user_nicename' => array('name' => 'user_nicename', 'type' => 'string', 'require' => true, 'desc' => '用户名'),
            ),
            'invitationCode' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'setBetrecord' => array(
                'type' => array('name' => 'type', 'type' => 'string', 'require' => true, 'desc' => '收支类型（固定传 expend）'),
                'action' => array('name' => 'action', 'type' => 'string', 'require' => true, 'desc' => '收支行为 （固定传 bet）'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'touid' => array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播ID'),
                'playname' => array('name' => 'playname', 'type' => 'string', 'require' => true, 'desc' => '彩票名称（ID）-玩法名称（ID）-投注内容'),
                'giftcount' => array('name' => 'giftcount', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '数量'),
                'totalcoin' => array('name' => 'totalcoin', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '总价'),
                'showid' => array('name' => 'showid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '直播ID'),
            ),
            'applyBecomeLive' => array(
                'real_name' => array('name' => 'real_name', 'type' => 'string', 'require' => true, 'desc' => '姓名'),
                'mobile' => array('name' => 'mobile', 'type' => 'string', 'require' => true, 'desc' => '手机号码'),
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'cer_no' => array('name' => 'cer_no', 'type' => 'string',  'require' => true, 'desc' => '身份证号'),
                'front_view' => array('name' => 'front_view', 'type' => 'file', 'require' => true, 'desc' => '身份证正面'),
                'back_view' => array('name' => 'back_view', 'type' => 'file',  'require' => true, 'desc' => '身份证反面'),
                'handset_view' => array('name' => 'handset_view', 'type' => 'file', 'require' => true, 'desc' => '手持身份证'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'wchat' => array('name' => 'wchat', 'type' => 'string', 'require' => true, 'desc' => '微信号'),
            ),
            'incomeExpenditure' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
            ),
            'incomeExpenditurenew' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
                'type' => array('name' => 'type', 'type' => 'int', 'desc' => '0 代表明细【充值，提现 ，购买长视频】，1代表充值明细，【充值，购买长视频vip】'),
            ),
            'incomeUploadvideo' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'p' => array('name' => 'p', 'type' => 'int', 'min' => 1, 'default' => 1, 'desc' => '页数'),
            ),
            'getLiveInfo' => array(

                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'live_id'  => array('name' => 'live_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '主播id'),
            ),
            'getUserAgreement' => array(),
            'getSubUser' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户Token'),
                'room_id' => array('name' => 'room_id', 'type' => 'int', 'require' => false, 'desc' => '聊天室ID（聊天室添加成员时必填）'),
            ),
            'isupdate' => array(
                'channel' => array('name' => 'channel', 'type' => 'string',  'require' => true, 'desc' => 'ios/android'),
                'version' => array('name' => 'version', 'type' => 'string', 'require' => true, 'desc' => '版本号'),
            ),
            'userAction' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'json_data' => array('name' => 'json_data', 'type' => 'string',  'require' => true, 'desc' => 'json数据，例如：<br><br>
                                                                                                                [{"action_type":2,"action_time":1,"ctime":[1632382716,1632382718]},{"action_type":3,"action_time":1,"ctime":[1632382715,1632382719]}]<br><br>
                                                                                                                action_type: 用户行为<br>
                                                                                                                action_time：操作数量/观看时长<br>
                                                                                                                ( 用户行为是“播放“，传播放时间，单位：秒，其他则传 1 )<br>
                                                                                                                ctime：操作时间<br>
                                                                                                                （时间戳，数组，有几个时间就对应于几次操作 相同的行为）'),
            ),
            'isAnchorAuthentication' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),

            ),
            'savebeauty' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'client' => array('name' => 'client', 'type' => 'int', 'require' => true, 'desc' => '客户端：1 PC，2 H5，3 Android，4 iOS'),
                'data_param' => array('name' => 'data_param', 'type' => 'string', 'require' => false, 'desc' => '美颜参数，json字符串，如：<br>{"sprout_white":30,"sprout_skin":30,"sprout_saturated":30,<br>"sprout_pink":30,"sprout_eye":10,"sprout_face":21,"select_tifilter":1}'),
            ),
            'getcoinName' => array(

            ),
            'searchUser' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'desc' => '用户token'),
                'key' => array('name' => 'key', 'type' => 'string', 'require' => true, 'desc' => '关键词'),
                'p' => array('name' => 'p', 'type' => 'int', 'default' => 1, 'desc' => '页码'),
            ),
            'getUserLevel' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'issuper' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),

            ),
            'insertBet' => array(
                'liveuid' => array('name' => 'liveuid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'content' => array('name' => 'content', 'type' => 'string', 'require' => true, 'desc' => '中奖人昵称，彩种，金额'),
            ),
            'getGameuserinfo' => array(
                'game_user_id' => array('name' => 'game_user_id', 'type' => 'string', 'min' => 1, 'require' => true, 'max'=>'30', 'desc' => '游戏系统账号id'),
            ),
            'charge_withdrawn' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'charge_gift' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'chargegift_send' => array(
                'game_user_id' => array('name' => 'game_user_id', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '彩票会员用户ID'),
                'price' => array('name' => 'price', 'type' => 'int', 'require' => true, 'desc' => '首充豪礼id'),
            ),
            'chargegift_list' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'transfer' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'touid'=>   array('name' => 'touid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '转给那个用户'),
                'amount'=>   array('name' => 'amount', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '金额'),
                'user_nicename'=> array('name' => 'user_nicename', 'type' => 'string',  'require' => true, 'desc' => '用户昵称'),
            ),
            'findUser' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'userdata'=>   array('name' => 'userdata', 'type' => 'string', 'min' => 1, 'require' => true, 'desc' => '用户id或用户名'),
            ),
            'getregUrl'=> array(

            ),
            'transferOutyuebao' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'amount'=>   array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '金额'),
                'bankname'=>   array('name' => 'bankname', 'type' => 'string', 'desc' => '银行卡名称'),
                'banknumber' => array('name' => 'banknumber', 'type' => 'int', 'min' => 1, 'desc' => '银行卡账号'),
                'realname'=>   array('name' => 'realname', 'type' => 'string', 'desc' => '用户真实姓名'),
                'phonenumber'=>   array('name' => 'phonenumber', 'type' => 'int', 'desc' => '手机号'),
                'type' => array('name' => 'type', 'type' => 'int',  'require' => true,'desc' => '1是转出到余额，2是转出到银行卡'),
            ),
            'transferInyuebao' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'amount'=>   array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '金额'),
                'bankname'=>   array('name' => 'bankname', 'type' => 'string', 'desc' => '银行卡名称'),
                'banknumber' => array('name' => 'banknumber', 'type' => 'int', 'min' => 1, 'desc' => '银行卡账号'),
                'realname'=>   array('name' => 'realname', 'type' => 'string', 'desc' => '用户真实姓名'),
                'phonenumber'=>   array('name' => 'phonenumber', 'type' => 'int', 'desc' => '手机号'),
                'type' => array('name' => 'type', 'type' => 'int',  'require' => true,'desc' => '1是转入到余额，2是转入到银行卡'),
            ),
            'settlementYuebao'=> array(

            ),
            'transferToyuebaoauto'=> array(

            ),
            'openYuebao' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'type' => array('name' => 'type', 'type' => 'int',  'require' => true,'desc' => '1判断是否开通米利宝，2去开通米利宝'),
            ),
            'getMySubUserList' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'type' => array('name' => 'type', 'type' => 'int', 'require' => true, 'desc' => '级数：0.下级和下下级，1.下级，2.下下级'),
            ),
            'nftConsumption' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'amount'=>   array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '金额'),
                'type' => array('name' => 'type', 'type' => 'int',  'require' => true,'desc' => '消费类型 1 增加余额，2 减去余额 3,只减去冻结金额nft'),
            ),
            'shopConsumption' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'amount'=>   array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '金额'),
                'type' => array('name' => 'type', 'type' => 'int',  'require' => true,'desc' => '消费类型 1 退款 退货 增加余额，2小黄车下单, 减去余额'),
                'shoppingVoucherId' => array('name' => 'shoppingVoucherId', 'type' => 'string', 'desc' => '购物券id, 多个id用英文逗号 “,” 分隔'),
            ),
            'shopuserConsumption' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'amount'=>   array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '金额'),
                'type' => array('name' => 'type', 'type' => 'int',  'require' => true,'desc' => '消费类型 1 确认收货 增加余额，2 代发待支付订单，减去余额'),
                'ids' => array('name' => 'ids', 'type' => 'string',  'desc' => '订单id  支付订单需要，多个订单，英文逗号 “,” 分隔'),
                'shoptoken' => array('name' => 'shoptoken', 'type' => 'string', 'desc' => '代付token 支付订单需要'),
                'shop_order_id' => array('name' => 'shop_order_id', 'type' => 'int',  'desc' => '商城订单表id'),
                'cg_order_id' => array('name' => 'cg_order_id', 'type' => 'string',  'desc' => '待采购订单号'),
                'shop_order_no' => array('name' => 'shop_order_no', 'type' => 'string',  'desc' => '商城订单号'),
                'cg_order_no' => array('name' => 'cg_order_no', 'type' => 'string',  'desc' => '代发(采购)订单号'),
            ),
            'shopuserBondpay' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'amount'=>   array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '金额'),
                'type' => array('name' => 'type', 'type' => 'int',  'require' => true,'desc' => '消费类型 1 保证金缴纳'),
                'shoptoken' => array('name' => 'shoptoken', 'type' => 'string', 'desc' => '代付token 支付订单需要'),

            ),
            'lotteryConsumption' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'amount'=>   array('name' => 'amount', 'type' => 'string', 'require' => true, 'desc' => '金额'),
                'type' => array('name' => 'type', 'type' => 'int',  'require' => true,'desc' => '消费类型 1 增加余额，2 减去余额 '),
            ),
            'bindUser' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
                'user_login' => array('name' => 'user_login', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '6',  'max'=>'30', 'desc' => '账号'),
                'user_pass' => array('name' => 'user_pass', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '密码'),
                'user_pass2' => array('name' => 'user_pass2', 'type' => 'string', 'min' => 1, 'require' => true,  'min' => '1',  'max'=>'30', 'desc' => '确认密码'),
                'code' => array('name' => 'code', 'type' => 'string',  'require' => false,   'desc' => '验证码'),
                'agent_code' => array('name' => 'agent_code', 'type' => 'string', 'default'=>'','require' => false, 'desc' => '邀请码'),
                'source' => array('name' => 'source', 'type' => 'string',  'default'=>'pc', 'desc' => '来源设备'),
                'zone' => array('name' => 'zone', 'type' => 'string', 'min' => 1, 'require' => true,   'desc' => '区号'),
            ),
            'sign_in' =>  array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'signLog' => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),
            ),
            'signSet' => array(),
            'accessLog'=>array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => true, 'desc' => '用户token'),

            ),
            'checkUserLogin'=> array(
                    'user_login' => array('name' => 'user_login', 'type' => 'string', 'require' => true),

            ),
            'goodsToshopowner'=> array(

            ),

            "getInviteCode" => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => false, 'desc' => '用户token'),
            ),

            "summaryAgent" => array(

            ),
            "downloadFromH5" => array(
                'agent_code' => array('name' => 'agent_code', 'type' => 'string', 'default'=>'','require' => true, 'desc' => '邀请码'),
            ),
            "summaryDownload"=> array(
                
            ),
            "AddCoin" => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => false, 'desc' => '用户token'),
            ),
            "getInvitedList" => array(
                'uid' => array('name' => 'uid', 'type' => 'int', 'min' => 1, 'require' => true, 'desc' => '用户ID'),
                'token' => array('name' => 'token', 'type' => 'string', 'require' => false, 'desc' => '用户token'),
                'page' => array('name' => 'page', 'type' => 'int', 'default' => 1, 'desc' => '页码'),
            ),
        );
    }

    /**
     * 查询用户送礼记录
     * @desc 查询用户送礼记录
     * @return int code 操作码，0表示成功， 1表示用户不存在
     * @return array info
     * @return string gift_total 赠送的礼物总价值钻石
     * @return string start_time 开始时间,格式为yyyy-MM-dd HH:mm:ss 如请求参数未传递,则为空
     * @return string end_time 结束时间,格式为yyyy-MM-dd HH:mm:ss 如请求参数未传递,则为空
     * @return array info[0] 送礼信息
     * @return string info[0].uid 送礼用户ID
     * @return string info[0].touid 收礼用户ID
     * @return string info[0].giftid 礼物id
     * @return string info[0].total 礼物价值钻石
     * @return string info[0].addtime 赠送时间 格式为 yyyy-MM-dd HH:mm:ss
     * @return string info[0].giftinfo 礼物信息
     * @return string info[0].giftinfo.giftname 礼物名称
     * @return string info[0].userinfo 收礼用户信息
     * @return string info[0].userinfo.id 收礼用户id
     * @return string info[0].userinfo.user_nicename 收礼用户昵称
     * @return string info[0].userinfo.avatar 收礼用户头像
     * @return string info[0].userinfo.avatar_thumb 收礼用户头像缩略图
     * @return string msg 提示信息
     * @return int nums 记录列表长度
     * @return int isscroll 是否还有下一页 0-还有下一页 1-没有下一页
     */
    public function sendGiftDetail()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $uid = $this->uid;

        $p = checkNull($this->page);
        $start_time = checkNull($this->start_time);
        $end_time = checkNull($this->end_time);

        $rs['start_time'] = $start_time;
        $rs['end_time'] = $end_time;

        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time);
        $pnums = 50;
        $start = ($p - 1) * $pnums;

        $Coinrecird = DI()->notorm->users_coinrecord;

        if ($start_time != false) {
            $Coinrecird->where("addtime>={$start_time}");
        }
        if ($end_time != false) {
            $Coinrecird->where("addtime<={$end_time}");
        }
        $giftInfoArray = array();
        $userInfoArray = array();
        $list = $Coinrecird->select("uid,touid,giftid,totalcoin as total,addtime")->where("action='sendgift' and uid={$uid}")->order("addtime desc")->limit($start, $pnums)->fetchAll();

        $Coinrecirds = DI()->notorm->users_coinrecord;

        if ($start_time != false) {
            $Coinrecirds->where("addtime>={$start_time}");
        }
        if ($end_time != false) {
            $Coinrecirds->where("addtime<={$end_time}");
        }
        $giftTotal = $Coinrecirds->select("IFNULL(sum(totalcoin),0) as gift_total")->where("action='sendgift' and uid={$uid}")->fetchOne();
        $config=getConfigPub();
        foreach ($list as $k => $v) {
            $giftinfo = $giftInfoArray[$v['giftid']];
            if (empty($giftinfo)) {
                $giftinfo = DI()->notorm->gift->select("giftname")->where("id={$v['giftid']}")->fetchOne();
                if (!$giftinfo) {
                    $giftinfo = array(
                        "giftname" => '礼物已删除'
                    );
                }
                $giftInfoArray[$v['giftid']] = $giftinfo;
            }
            $list[$k]['giftinfo'] = $giftinfo;
            $list[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
            $list[$k]['total'] = round($v['total']/$config['money_rate'],2);

            $userinfo = $userInfoArray[$v['touid']];
            if (empty($userinfo)) {
                $userinfo = getUserInfo($v['touid']);
                if (!$userinfo) {
                    $userinfo = array(
                        "user_nicename" => '用户已删除'
                    );
                }
                $userInfoArray[$v['touid']] = $userinfo;
            }
            $list[$k]['userinfo'] = $userinfo;
        }

        $nums = count($list);
        if ($nums < $pnums) {
            $isscroll = 0;
        } else {
            $isscroll = 1;
        }

        $rs['info'] = $list;
        $rs['nums'] = $nums;
        $rs['isscroll'] = $isscroll;
        $rs['gift_total'] = round($giftTotal['gift_total']/$config['money_rate'],2);

        if (!empty($start_time)) {
            $rs['start_time'] = date('Y-m-d H:i:s', $start_time);
        }
        if (!empty($end_time)) {
            $rs['end_time'] = date('Y-m-d H:i:s', $end_time);
        }

        return $rs;

    }

    /**
     * 查询用户收礼记录
     * @desc 查询用户收礼记录
     * @return int code 操作码，0表示成功， 1表示用户不存在
     * @return array info
     * @return string gift_total 收到的礼物总价值钻石
     * @return string start_time 开始时间,格式为yyyy-MM-dd HH:mm:ss 如请求参数未传递,则为空
     * @return string end_time 结束时间,格式为yyyy-MM-dd HH:mm:ss 如请求参数未传递,则为空
     * @return array info[0] 送礼信息
     * @return string info[0].uid 送礼用户ID
     * @return string info[0].touid 收礼用户ID
     * @return string info[0].giftid 礼物id
     * @return string info[0].total 礼物价值钻石
     * @return string info[0].addtime 赠送时间 格式为 yyyy-MM-dd HH:mm:ss
     * @return string info[0].giftinfo 礼物信息
     * @return string info[0].giftinfo.giftname 礼物名称
     * @return string info[0].userinfo 送礼用户信息
     * @return string info[0].userinfo.id 送礼用户id
     * @return string info[0].userinfo.user_nicename 送礼用户昵称
     * @return string info[0].userinfo.avatar 送礼用户头像
     * @return string info[0].userinfo.avatar_thumb 送礼用户头像缩略图
     * @return string msg 提示信息
     * @return int nums 记录列表长度
     * @return int isscroll 是否还有下一页 0-还有下一页 1-没有下一页
     */
    public function receiveGiftDetail()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $uid = $this->uid;

        $p = checkNull($this->page);
        $start_time = checkNull($this->start_time);
        $end_time = checkNull($this->end_time);

        $rs['start_time'] = $start_time;
        $rs['end_time'] = $end_time;

        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time);
        $pnums = 50;
        $start = ($p - 1) * $pnums;

        $Coinrecird = DI()->notorm->users_coinrecord;
        if ($start_time != false) {
            $Coinrecird->where("addtime>={$start_time}");
        }
        if ($end_time != false) {
            $Coinrecird->where("addtime<={$end_time}");
        }
        $giftInfoArray = array();
        $userInfoArray = array();
        $list = $Coinrecird->select("uid,touid,giftid,anthor_total as total,addtime")->where("action='sendgift' and touid={$uid}")->order("addtime desc")->limit($start, $pnums)->fetchAll();
        $Coinrecirds = DI()->notorm->users_coinrecord;
        if ($start_time != false) {
            $Coinrecirds->where("addtime>={$start_time}");
        }
        if ($end_time != false) {
            $Coinrecirds->where("addtime<={$end_time}");
        }
        $giftTotal = $Coinrecirds->select("IFNULL(sum(anthor_total),0) as gift_total")->where("action='sendgift' and touid={$uid}")->fetchOne();

        foreach ($list as $k => $v) {
            $giftinfo = $giftInfoArray[$v['giftid']];
            if (empty($giftinfo)) {
                $giftinfo = DI()->notorm->gift->select("giftname")->where("id={$v['giftid']}");
                if (!$giftinfo) {
                    $giftinfo = array(
                        "giftname" => '礼物已删除'
                    );
                }
                $giftInfoArray[$v['giftid']] = $giftinfo;
            }
            $list[$k]['giftinfo'] = $giftinfo;
            $list[$k]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);

            $userinfo = $userInfoArray[$v['uid']];
            if (empty($userinfo)) {
                $userinfo = getUserInfo($v['uid']);
                if (!$userinfo) {
                    $userinfo = array(
                        "user_nicename" => '用户已删除'
                    );
                }
                $userInfoArray[$v['uid']] = $userinfo;
            }
            $list[$k]['userinfo'] = $userinfo;
        }

        $nums = count($list);
        if ($nums < $pnums) {
            $isscroll = 0;
        } else {
            $isscroll = 1;
        }

        $rs['info'] = $list;
        $rs['nums'] = $nums;
        $rs['isscroll'] = $isscroll;
        $rs['gift_total'] = $giftTotal['gift_total'];

        if (!empty($start_time)) {
            $rs['start_time'] = date('Y-m-d H:i:s', $start_time);
        }
        if (!empty($end_time)) {
            $rs['end_time'] = date('Y-m-d H:i:s', $end_time);
        }
        return $rs;

    }

    /**
     * 判断token
     * @desc 用于判断token
     * @return int code 操作码，0表示成功， 1表示用户不存在
     * @return array info
     * @return string msg 提示信息
     */
    public function iftoken()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        return $rs;
    }

    /**
     * 获取用户信息
     * @desc 用于获取单个用户基本信息
     * @return int code 操作码，0表示成功， 1表示用户不存在
     * @return string msg 提示信息
     * @return array info
     * @return array info[0] 用户信息
     * @return int info[0].id 用户ID
     * @return string info[0].level 等级
     * @return string info[0].lives 直播数量
     * @return string info[0].follows 关注数
     * @return string info[0].fans 粉丝数
     * @return string info[0].agent_switch 分销开关
     * @return string info[0].family_switch 家族开关
     * @return string info[0].long_view_times 观看长视频数量
     * @return string info[0].short_view_times 观看短视频数量
     * @return string info[0].total_view_times 观看视频总数
     *  @return string info[0].short_surplus_view_times 短视频观看剩余次数
     * @return string info[0].long_surplus_view_times 长视频观看剩余次数
     * @return string info[0].total_view_times 观看视频总数
     * @return string info[0].watch_history 观看历史
     * @return string info[0].coin 可提现余额
     * @return string info[0].withdrawable_coin 可提现余额
     * @return string info[0].nowithdrawable_coin 不可提现余额
     * @return string info[0].totalcoin 总资产
     * @return object info[0].beauty 上次直播美颜
     * @return object info[0].level_info 等级详情
     * @return object info[0].noble 贵族详情
     * @return int info[0].user_vip_status 用户vip状态：0.未缴纳，1.生效中，2.退款中，3.已退款，4.审核中
     * @return int info[0].user_vip_action_type 用户vip购买行为：1.直接购买，2.升级
     * @return string info[0].user_vip_create_time 购买时间
     * @return string info[0].user_vip_update_time 更新时间
     * @return string info[0].user_vip_refund_time 退款时间
     * @return int info[0].user_vip_checking_level 正在审核中的vip等级
     * @return float info[0].like_deposit 已缴纳的点赞保证金
     * @return int info[0].like_deposit_status 点赞保证金状态: 0.未支付，1.申请中，2.生效中，3.退款中，4.已退款
     */
    public function getBaseInfo()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = $this->uid;
        $version = checkNull($this->version);
        $client = $this->client;
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        // 更新用户客户端版本
        $domain = new Domain_Home();
        $result = $domain->updateVersion($version, $client, $uid);

        $domain = new Domain_User();
        $info = $domain->getBaseInfo($uid);
        /*$videoDomain = new Domain_Video();
        $info['watch_history'] = $videoDomain->watchHistory($this->uid);*/

        if (!$info) {
            $rs['code'] = 700;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $configpri = getConfigPri();
        $configpub = getConfigPub();
        $agent_switch = $configpri['agent_switch'];
        $family_switch = $configpri['family_switch'];
        $ios_shelves = $configpub['ios_shelves'];

        $info['agent_switch'] = $agent_switch;
        $info['family_switch'] = $family_switch;

        /* 个人中心菜单 */
        $version_ios = $this->version_ios;
        $list = array();
        $list1 = array();
        $list2 = array();
        $list3 = array();
        $shelves = 1;
        $gameTenantId = getGameTenantId();
        $gameTenantParam = '&game_tenant_id=' . $gameTenantId;
        if ($version_ios && $version_ios == $ios_shelves) {
            $agent_switch = 0;
            $family_switch = 0;
            $shelves = 0;
        }
        $list1[] = array('id' => '19', 'name' => '我的视频', 'thumb' => get_upload_path("/public/appapi/images/personal/video.png"), 'href' => '');
        if ($shelves) {
            $list1[] = array('id' => '1', 'name' => '我的收益', 'thumb' => get_upload_path("/public/appapi/images/personal/votes.png"), 'href' => '');
        }

        $list1[] = array('id' => '2', 'name' => '我的' . $configpub['name_coin'], 'thumb' => get_upload_path("/public/appapi/images/personal/coin.png"), 'href' => '');
        $list1[] = array('id' => '3', 'name' => '我的等级', 'thumb' => get_upload_path("/public/appapi/images/personal/level.png"), 'href' => get_upload_path("/index.php?g=Appapi&m=Level&a=index" . $gameTenantParam));


        if ($shelves) {
            $list1[] = array('id' => '14', 'name' => '我的明细', 'thumb' => get_upload_path("/public/appapi/images/personal/detail.png"), 'href' => get_upload_path("/index.php?g=Appapi&m=Detail&a=index" . $gameTenantParam));
            $list2[] = array('id' => '4', 'name' => '在线商城', 'thumb' => get_upload_path("/public/appapi/images/personal/shop.png"), 'href' => get_upload_path("/index.php?g=Appapi&m=Mall&a=index" . $gameTenantParam));
            $list2[] = array('id' => '5', 'name' => '装备中心', 'thumb' => get_upload_path("/public/appapi/images/personal/equipment.png"), 'href' => get_upload_path("/index.php?g=Appapi&m=Equipment&a=index" . $gameTenantParam));
        }

        $list1[] = array('id' => '11', 'name' => '我的认证', 'thumb' => get_upload_path("/public/appapi/images/personal/auth.png"), 'href' => get_upload_path("/index.php?g=Appapi&m=Auth&a=index" . $gameTenantParam));

        if ($family_switch) {
            $list2[] = array('id' => '6', 'name' => '家族中心', 'thumb' => get_upload_path("/public/appapi/images/personal/family.png"), 'href' => get_upload_path("/index.php?g=Appapi&m=Family&a=index2" . $gameTenantParam));
            $list2[] = array('id' => '7', 'name' => '家族驻地', 'thumb' => get_upload_path("/public/appapi/images/personal/family2.png"), 'href' => get_upload_path("/index.php?g=Appapi&m=Family&a=home" . $gameTenantParam));
        }

        if ($agent_switch) {
            $list2[] = array('id' => '8', 'name' => '三级分销', 'thumb' => get_upload_path("/public/appapi/images/personal/agent.png"), 'href' => get_upload_path("/index.php?g=Appapi&m=Agent&a=index" . $gameTenantParam));
            //	$list2[]=array('id'=>'9','name'=>'推广二维码','thumb'=>get_upload_path("/public/appapi/images/personal/agent.png") ,'href'=>get_upload_path("/index.php?g=Appapi&m=Agent&a=ewm"));
        }


        //$list[]=array('id'=>'12','name'=>'关于我们','thumb'=>get_upload_path("/public/appapi/images/personal/about.png") ,'href'=>get_upload_path("/index.php?g=portal&m=page&a=lists"));
        $list3[] = array('id' => '13', 'name' => '个性设置', 'thumb' => get_upload_path("/public/appapi/images/personal/set.png"), 'href' => '');

        $list[] = $list1;
        $list[] = $list2;
        $list[] = $list3;
        $info['list'] = $list;
        $rs['info'][0] = $info;

        return $rs;
    }

    /**
     * 头像上传 (七牛)
     * @desc 用于用户修改头像
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string list[0].avatar 用户主头像
     * @return string list[0].avatar_thumb 用户头像缩略图
     * @return string msg 提示信息
     */
    public function updateAvatar()
    {
        $rs = array('code' => 0, 'msg' => '设置头像成功', 'info' => array());

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $userDomain = new Domain_User();
        $userInfo = $userDomain->getBaseInfo($this->uid);
        if ($userInfo['user_type'] ==3){
            $rs['code'] = 1003;
            $rs['msg'] = '虚拟会员不能修改头像';
            return $rs;
        }
        if (!isset($_FILES['file'])) {
            $rs['code'] = 1001;
            $rs['msg'] = T('miss upload file');
            return $rs;
        }

        if ($_FILES["file"]["error"] > 0) {
            $rs['code'] = 1002;
            $rs['msg'] = T('failed to upload file with error: {error}', array('error' => $_FILES['file']['error']));
            DI()->logger->debug('failed to upload file with error: ' . $_FILES['file']['error']);
            return $rs;
        }

        $result = upload_file($_FILES['file']);
        $data['avatar'] = $result['url'];
        $data['avatar_thumb'] = $result['url_thumb'];

        /* 清除缓存 */
        delCache("userinfo_" . $this->uid);
        $domain = new Domain_User();
        $info = $domain->userUpdate($this->uid, $data);

        $rs['info'][0] = $data;

        return $rs;

    }

    /**
     * 修改用户信息
     * @desc 用于修改用户信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string list[0].msg 修改成功提示信息
     * @return string msg 提示信息
     */
    public function updateFields()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $userDomain = new Domain_User();
        $userInfo = $userDomain->getBaseInfo($this->uid);
        if ($userInfo['user_type'] ==3){
            $rs['code'] = 1003;
            $rs['msg'] = '游客不能修改资料';
            return $rs;
        }
        $fields = json_decode($this->fields, true);

        $domain = new Domain_User();
        foreach ($fields as $k => $v) {
            $fields[$k] = htmlspecialchars_decode(checkNull($v));
        }

        if (array_key_exists('user_nicename', $fields)) {
            if ($fields['user_nicename'] == '') {
                $rs['code'] = 1002;
                $rs['msg'] = '昵称不能为空';
                return $rs;
            }
            $isexist = $domain->checkName($this->uid, $fields['user_nicename']);
            if (!$isexist) {
                $rs['code'] = 1002;
                $rs['msg'] = '昵称重复，请修改';
                return $rs;
            }
            $fields['user_nicename'] = filterField($fields['user_nicename']);
        }

        $info = $domain->userUpdate($this->uid, $fields);

        if ($info === false) {
            $rs['code'] = 1001;
            $rs['msg'] = '修改失败';
            return $rs;
        }
        /* 清除缓存 */
        delCache("userinfo_" . $this->uid);
        $rs['info'][0]['msg'] = '修改成功';
        return $rs;
    }

    /**
     * 修改密码
     * @desc 用于修改用户信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string list[0].msg 修改成功提示信息
     * @return string msg 提示信息
     */
    public function updatePass()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = $this->uid;
        $token = $this->token;
        $oldpass = $this->oldpass;
        $pass = $this->pass;
        $pass2 = $this->pass2;
        if ($oldpass ==$pass ){
            $rs['code'] = 1007;
            $rs['msg'] = '新密码不能与原密码相同，请重新输入';
            return $rs;
        }
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $userDomain = new Domain_User();
        $userInfo = $userDomain->getBaseInfo($this->uid);
        if ($userInfo['user_type'] ==3){
            $rs['code'] = 1006;
            $rs['msg'] = '游客不能修改密码';
            return $rs;
        }
        if ($pass != $pass2) {
            $rs['code'] = 1002;
            $rs['msg'] = '两次新密码不一致';
            return $rs;
        }

        $check = passcheck($pass);
        if ($check == 0) {
            $rs['code'] = 1004;
            $rs['msg'] = '密码为6-12位数字与字母组合';
            return $rs;
        } else if ($check == 2) {
            $rs['code'] = 1005;
            $rs['msg'] = '密码不能纯数字或纯字母';
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->updatePass($uid, $oldpass, $pass);

        if ($info == 1003) {
            $rs['code'] = 1003;
            $rs['msg'] = '旧密码错误';
            return $rs;
        } else if ($info === false) {
            $rs['code'] = 1001;
            $rs['msg'] = '修改失败';
            return $rs;
        }

        $rs['info'][0]['msg'] = '修改成功';
        return $rs;
    }

    /**
     * 校验支付密码
     * @desc 用于校验支付密码
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function checkPaymentPassword()
    {
        $rs = array('code' => 0, 'msg' => '校验成功', 'info' => array());

        $uid = $this->uid;
        $token = $this->token;
        $password = checkNull($this->password);

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->checkPaymentPassword($uid, $password);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 修改支付密码
     * @desc 用于修改支付密码
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function updatePaymentPassword()
    {
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = $this->uid;
        $token = $this->token;
        $old_password = checkNull($this->old_password);
        $password = checkNull($this->password);
        $confirm_password = checkNull($this->confirm_password);

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->updatePaymentPassword($uid, $old_password, $password, $confirm_password);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 重置支付密码
     * @desc 用于重置支付密码
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function resetPaymentPassword()
    {
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = $this->uid;
        $token = $this->token;
        $login_password = checkNull($this->login_password);
        $password = checkNull($this->password);
        $confirm_password = checkNull($this->confirm_password);

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->resetPaymentPassword($uid, $login_password, $password, $confirm_password);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 我的钻石
     * @desc 用于获取用户余额,充值规则 支付方式信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].coin 用户可提现余额
     * @return string info[0].withdrawable_coin 用户可提现余额
     * @return string info[0].nowithdrawable_coin 用户不可提现余额
     * @return string info[0].totalcoin 用户总资产
     * @return array info[0].rules 充值规则
     * @return string info[0].rules[].id 充值规则
     * @return string info[0].rules[].coin 钻石
     * @return string info[0].rules[].money 价格
     * @return string info[0].rules[].money_ios 苹果充值价格
     * @return string info[0].rules[].product_id 苹果项目ID
     * @return string info[0].rules[].give 赠送钻石，为0时不显示赠送
     * @return string info[0].aliapp_switch 支付宝开关，0表示关闭，1表示开启
     * @return string info[0].aliapp_partner 支付宝合作者身份ID
     * @return string info[0].aliapp_seller_id 支付宝帐号
     * @return string info[0].aliapp_key_android 支付宝安卓密钥
     * @return string info[0].aliapp_key_ios 支付宝苹果密钥
     * @return string info[0].wx_switch 微信支付开关，0表示关闭，1表示开启
     * @return string info[0].wx_appid 开放平台账号AppID
     * @return string info[0].wx_appsecret 微信应用appsecret
     * @return string info[0].wx_mchid 微信商户号mchid
     * @return string info[0].wx_key 微信密钥key
     * @return string msg 提示信息
     */
    public function getBalance()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {

            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->getBalance($this->uid);

        $key = 'getChargeRules';
        $rules = getcaches($key);
        if (!$rules) {
            $rules = $domain->getChargeRules();
            setcaches($key, $rules);
        }
        $info['rules'] = $rules;

        $configpri = getConfigPri();

        $info['aliapp_switch'] = $configpri['aliapp_switch'];
        $info['aliapp_partner'] = $configpri['aliapp_switch'] == 1 ? $configpri['aliapp_partner'] : '';
        $info['aliapp_seller_id'] = $configpri['aliapp_switch'] == 1 ? $configpri['aliapp_seller_id'] : '';
        $info['aliapp_key_android'] = $configpri['aliapp_switch'] == 1 ? $configpri['aliapp_key_android'] : '';
        $info['aliapp_key_ios'] = $configpri['aliapp_switch'] == 1 ? $configpri['aliapp_key_ios'] : '';

        $info['wx_switch'] = $configpri['wx_switch'];
        $info['wx_appid'] = $configpri['wx_switch'] == 1 ? $configpri['wx_appid'] : '';
        $info['wx_appsecret'] = $configpri['wx_switch'] == 1 ? $configpri['wx_appsecret'] : '';
        $info['wx_mchid'] = $configpri['wx_switch'] == 1 ? $configpri['wx_mchid'] : '';
        $info['wx_key'] = $configpri['wx_switch'] == 1 ? $configpri['wx_key'] : '';


        $rs['info'][0] = $info;
        return $rs;
    }

    /**
     * 我的收益
     * @desc 用于获取用户收益，包括可体现金额，今日可提现金额
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].votes 可提取映票数
     * @return string info[0].votestotal 总映票
     * @return string info[0].cash_rate 映票兑换比例
     * @return string info[0].total 可体现金额
     * @return string info[0].tips 温馨提示
     * @return string msg 提示信息
     */
    public function getProfit()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->getProfit($this->uid);

        $rs['info'][0] = $info;
        return $rs;
    }

    /**
     * 我的收益统计
     * @desc 用于获取用户在一个时间段内的收益信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].uid 用户id
     * @return string info[0].votes 获得映票数
     * @return string start_time 开始时间,格式为yyyy-MM-dd HH:mm:ss 如请求参数未传递,则为空
     * @return string end_time 结束时间,格式为yyyy-MM-dd HH:mm:ss 如请求参数未传递,则为空
     * @return string msg 提示信息
     */
    public function getProfitStat()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());


        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {

            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $uid = $this->uid;

        $start_time = checkNull($this->start_time);
        $end_time = checkNull($this->end_time);
        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time);

        $Voterecord = DI()->notorm->users_voterecord;
        if ($start_time != false) {
            $Voterecord->where("addtime>={$start_time}");
        }
        if ($end_time != false) {
            $Voterecord->where("addtime<={$end_time}");
        }
        $list = $Voterecord->select("uid,sum(votes) as votes")->where("uid={$uid}")->order("addtime desc")->fetchAll();

        if (!empty($start_time)) {
            $rs['start_time'] = date('Y-m-d H:i:s', $start_time);
        }
        if (!empty($end_time)) {
            $rs['end_time'] = date('Y-m-d H:i:s', $end_time);
        }

        $rs['info'][0] = $list;

        return $rs;
    }

    /**
     * 用户提现
     * @desc 用于进行用户提现
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].msg 提现成功信息
     * @return string msg 提示信息
     */
    public function setCash()
    {
        $rs = array('code' => 0, 'msg' => '提现成功', 'info' => array());

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $accountid = checkNull($this->accountid);
        $cashvote = checkNull($this->cashvote);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        if (!$accountid) {
            $language = DI()->config->get('language.select_withdraw');
            $rs['code'] = 1001;
            $rs['msg'] = $language[$language_id];//银行名称不能为空;
            return $rs;
        }

        if (!$cashvote) {
            $language = DI()->config->get('language.effctive_piao');
            $rs['code'] = 1002;
            $rs['msg'] = $language[$language_id];//请输入有效的提现票数;
            return $rs;
        }

        $data = array(
            'uid' => $uid,
            'accountid' => $accountid,
            'cashvote' => $cashvote,
        );
        $config = getConfigPri();
        $domain = new Domain_User();

        $userInfo = $domain->getBaseInfo($this->uid);
        if ($userInfo['user_type'] ==3){
            $rs['code'] = 1008;
            $rs['msg'] = '游客不能提现';
            return $rs;
        }
        $info = $domain->setCash($data);
        if ($info == 1001) {
            $language = DI()->config->get('language.balance_error');
            $rs['code'] = 1001;
            $rs['msg'] = $language[$language_id];//余额不足;
            return $rs;
        } else if ($info == 1003) {
            $language = DI()->config->get('language.balance_authenticate');
            $rs['code'] = 1003;
            $rs['msg'] = $language[$language_id];//请先进行身份认证;
            return $rs;
        } else if ($info == 1004) {
            $language1 = DI()->config->get('language.balance_limit');
            $language2 = DI()->config->get('language.balance_unit');
            $rs['code'] = 1004;
            $rs['msg'] = $language1[$language_id] . $config['cash_min'] . $language2[$language_id];//'提现最低额度为'.$config['cash_min'].'元'
            return $rs;
        } else if ($info == 1005) {
            $language = DI()->config->get('language.withdraw_outday');
            $rs['code'] = 1005;
            $rs['msg'] = $language[$language_id];//不在提现期限内，不能提现
            return $rs;
        } else if ($info == 1006) {
            $language1 = DI()->config->get('language.withdraw_everymonth');
            $language2 = DI()->config->get('language.withdraw_time');
            $rs['code'] = 1006;
            $rs['msg'] = $language1[$language_id] . $config['cash_max_times'] . $language2[$language_id];//'每月只可提现'.$config['cash_max_times'].'次,已达上限';
            return $rs;
        } else if ($info == 1007) {
            $language = DI()->config->get('language.withdraw_infoerror');
            $rs['code'] = 1007;
            $rs['msg'] = $language[$language_id];//提现账号信息不正确
            return $rs;
        } else if (!$info) {
            $language = DI()->config->get('language.withdraw_error');
            $rs['code'] = 1007;
            $rs['msg'] = $language[$language_id];//提现失败，请重试
            return $rs;
        }
        $language = DI()->config->get('language.withdraw_succ');
        $rs['info'][0] = $info;
        $rs['msg'] = $language[$language_id];
        return $rs;
    }

    /**
     * 判断是否关注
     * @desc 用于判断是否关注
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].isattent 关注信息，0表示未关注，1表示已关注
     * @return string msg 提示信息
     */
    public function isAttent()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $info = isAttention($this->uid, $this->touid);

        $rs['info'][0]['isattent'] = (string)$info;
        return $rs;
    }

    /**
     * 关注/取消关注
     * @desc 用于关注/取消关注
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].isattent 关注信息，0表示未关注，1表示已关注
     * @return string msg 提示信息
     */
    public function setAttent()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        if ($this->uid == $this->touid) {
            $rs['code'] = 1001;
            $rs['msg'] = '不能关注自己';
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->setAttent($this->uid, $this->touid);
        if ($info == 1002) {
            $rs['code'] = 1002;
            $rs['msg'] = '当日取关次数已达3次，无法取关';
            return $rs;
        }
        $rs['info'][0]['isattent'] = (string)$info;
        return $rs;
    }

    /**
     * 判断是否拉黑
     * @desc 用于判断是否拉黑
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].isattent  拉黑信息,0表示未拉黑，1表示已拉黑
     * @return string msg 提示信息
     */
    public function isBlacked()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $info = isBlack($this->uid, $this->touid);

        $rs['info'][0]['isblack'] = (string)$info;
        return $rs;
    }

    /**
     * 检测拉黑状态
     * @desc 用于私信聊天时判断私聊双方的拉黑状态
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].u2t  是否拉黑对方,0表示未拉黑，1表示已拉黑
     * @return string info[0].t2u  是否被对方拉黑,0表示未拉黑，1表示已拉黑
     * @return string msg 提示信息
     */
    public function checkBlack()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $u2t = isBlack($this->uid, $this->touid);
        $t2u = isBlack($this->touid, $this->uid);

        $rs['info'][0]['u2t'] = (string)$u2t;
        $rs['info'][0]['t2u'] = (string)$t2u;
        return $rs;
    }

    /**
     * 拉黑/取消拉黑
     * @desc 用于拉黑/取消拉黑
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].isblack 拉黑信息,0表示未拉黑，1表示已拉黑
     * @return string msg 提示信息
     */
    public function setBlack()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $domain = new Domain_User();
        $info = $domain->setBlack($this->uid, $this->touid);

        $rs['info'][0]['isblack'] = (string)$info;
        return $rs;
    }

    /**
     * 获取找回密码短信验证码
     * @desc 用于找回密码获取短信验证码
     * @return int code 操作码，0表示成功,2发送失败
     * @return array info
     * @return array info[0]
     * @return string msg 提示信息
     */

    public function getBindCode()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $mobile = $this->mobile;

        $ismobile = checkMobile($mobile);
        if (!$ismobile) {
            $rs['code'] = 1001;
            $rs['msg'] = '请输入正确的手机号';
            return $rs;
        }

        if ($_SESSION['set_mobile'] == $mobile && $_SESSION['set_mobile_expiretime'] > time()) {
            $rs['code'] = 1002;
            $rs['msg'] = '验证码5分钟有效，请勿多次发送';
            return $rs;
        }

        $mobile_code = random(6, 1);

        /* 发送验证码 */
        $result = sendCode($mobile, $mobile_code);
        if ($result['code'] === 0) {
            $_SESSION['set_mobile'] = $mobile;
            $_SESSION['set_mobile_code'] = $mobile_code;
            $_SESSION['set_mobile_expiretime'] = time() + 60 * 5;
        } else if ($result['code'] == 667) {
            $_SESSION['set_mobile'] = $mobile;
            $_SESSION['set_mobile_code'] = $result['msg'];
            $_SESSION['set_mobile_expiretime'] = time() + 60 * 5;

            $rs['code'] = 0;
            $rs['msg'] = '验证码为：' . $result['msg'];

        } else {
            $rs['code'] = 1002;
            $rs['msg'] = $result['msg'];
        }


        return $rs;
    }

    /**
     * 绑定手机号
     * @desc 用于用户绑定手机号
     * @return int code 操作码，0表示成功，非0表示有错误
     * @return array info
     * @return object info[0].msg 绑定成功提示
     * @return string msg 提示信息
     */
    public function setMobile()
    {
        $redis = connectionRedis();
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $sm_key = 'shortmsg_'.$_SESSION['session_id'].$this->mobile;
        $data = $redis->get($sm_key);
        if(!empty($data)){
            $data = json_decode($data);
            if ($this->mobile != $data->reg_mobile) {
                $rs['code'] = 1001;
                $rs['msg'] = '手机号码不一致';
                return $rs;
            }
    
            if ($this->code != $data->reg_mobile_code) {
                $rs['code'] = 1002;
                $rs['msg'] = '验证码错误';
                return $rs;
            }
        }else{
            if ($_SESSION['set_mobile'] != $this->mobile) {
                $rs['code'] = 1001;
                $rs['msg'] = '手机号不一致';
                return $rs;
            }


            if( $_SESSION['set_mobile_expiretime'] < time()){
                $rs['code'] = 1003;
                $rs['msg'] = '验证码已过期';
                return $rs;
            }

            if($this->code !=$_SESSION['set_mobile_code']){
                $rs['code'] = 1002;
                $rs['msg'] = '验证码错误';
                return $rs;
            }
        }


        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        // $checkToken = checkToken($this->uid, $this->token);
        // if ($checkToken == 700) {
        //     $rs['code'] = $checkToken;
        //     $language = DI()->config->get('language.tokenerror');
        //     $rs['msg'] = $language[$language_id];
        //     return $rs;
        // }

        $domain = new Domain_User();
        $userInfo = $domain->getBaseInfo($this->uid);
        if ($userInfo['user_type'] ==3){
            $rs['code'] = 1003;
            $rs['msg'] = '虚拟会员不能绑定手机号';
            return $rs;
        }
        $mobile = $this->mobile;

        //更新数据库//更新数据库
        if($userInfo['user_type'] ==4){
            $data = array("mobile" => $mobile,"user_login"=>$mobile,"user_type"=>2);
            $agent_code=$this->agent_code;
            if(!empty($agent_code)){
                $agent_uid = DI()->notorm->users
                                 ->select('id')->where("agent_code=?",$agent_code)->fetchOne();
                if(!empty($agent_uid['id'])){
                    if($agent_uid['id'] != $userInfo['id']){
                        $agent_data = array(
                            "agent_id" => $agent_uid['id'],
                            "invited_id" => $userInfo['id'],
                            "addtime" => time(),
                            "status" => 0
                        );
                        DI()->notorm->users_invite->insert($agent_data);
                    }
                        

                }


            }



        }else{
            $data = array("mobile" => $mobile,"user_login"=>$mobile);
        }

        //$data = array("mobile" => $mobile);
        $result = $domain->userUpdate($this->uid, $data);
        if ($result === false) {
            $rs['code'] = 1003;
            $rs['msg'] = '绑定失败';
            return $rs;
        }

        $rs['info'][0]['msg'] = '绑定成功';

        return $rs;
    }

    /**
     * 关注列表
     * @desc 用于获取用户的关注列表
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[].isattent 是否关注,0表示未关注，1表示已关注
     * @return string msg 提示信息
     */
    public function getFollowsList()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $domain = new Domain_User();
        $info = $domain->getFollowsList($this->uid, $this->touid, $this->p);

        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 粉丝列表
     * @desc 用于获取用户的关注列表
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[].isattent 是否关注,0表示未关注，1表示已关注
     * @return string msg 提示信息
     */
    public function getFansList()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $domain = new Domain_User();
        $info = $domain->getFansList($this->uid, $this->touid, $this->p);

        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 黑名单列表
     * @desc 用于获取用户的名单列表
     * @return int code 操作码，0表示成功
     * @return array info 用户基本信息
     * @return string msg 提示信息
     */
    public function getBlackList()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $domain = new Domain_User();
        $info = $domain->getBlackList($this->uid, $this->touid, $this->p);

        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 直播记录
     * @desc 用于获取用户的直播记录
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[].nums 观看人数
     * @return string info[].datestarttime 格式化的开播时间
     * @return string info[].dateendtime 格式化的结束时间
     * @return string info[].video_url 回放地址
     * @return string info[].file_id 回放标示
     * @return string msg 提示信息
     */
    public function getLiverecord()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $domain = new Domain_User();
        $info = $domain->getLiverecord($this->touid, $this->p);

        $rs['info'] = $info;
        return $rs;
    }

    /**
     *获取阿里云cdn录播地址
     * @desc 如果使用的阿里云cdn，则使用该接口获取录播地址
     * @return int code 操作码，0表示成功
     * @return string info[0].url 录播视频地址
     * @return string msg 提示信息
     */
    public function getAliCdnRecord()
    {

        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $domain = new Domain_Cdnrecord();
        $info = $domain->getCdnRecord($this->id);

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }

        if (!$info['video_url']) {
            $language = DI()->config->get('language.video_return');
            $rs['code'] = 1002;
            $rs['msg'] = $language[$language_id];//直播回放不存在;
            return $rs;
        }

        $rs['info'][0]['url'] = $info['video_url'];

        return $rs;
    }


    /**
     * 个人主页
     * @desc 用于获取个人主页数据
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].follows 关注数
     * @return string info[0].fans 粉丝数
     * @return string info[0].isattention 是否关注，0表示未关注，1表示已关注
     * @return string info[0].isblack 我是否拉黑对方，0表示未拉黑，1表示已拉黑
     * @return string info[0].isblack2 对方是否拉黑我，0表示未拉黑，1表示已拉黑
     * @return array info[0].contribute 贡献榜前三
     * @return array info[0].contribute[].avatar 头像
     * @return string info[0].islive 是否正在直播，0表示未直播，1表示直播
     * @return string info[0].videonums 视频数
     * @return string info[0].livenums 直播数
     * @return array info[0].liveinfo 直播信息
     * @return array info[0].liverecord 直播记录
     * @return array info[0].label 印象标签
     * @return string msg 提示信息
     */
    public function getUserHome()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = checkNull($this->uid);
        $touid = checkNull($this->touid);

        $domain = new Domain_User();
        $info = $domain->getUserHome($uid, $touid);

        /* 守护 */
        $data = array(
            "liveuid" => $touid,
        );

        $domain_guard = new Domain_Guard();
        $guardlist = $domain_guard->getGuardList($data);

        $info['guardlist'] = array_slice($guardlist, 0, 3);

        /* 标签 */
        $key = "getMyLabel_" . $touid;
        $label = getcaches($key);
        if (!$label) {
            $label = $domain->getMyLabel($touid);
            setcaches($key, $label);
        }

        $labels = array_slice($label, 0, 3);

        $info['label'] = $labels;

        /* 视频 */
        $domain_video = new Domain_Video();
        $video = $domain_video->getHomeVideo($uid, $touid, 1);

        $info['videolist'] = $video;

        $rs['info'][0] = $info;
        return $rs;
    }

    /**
     * 个人主页
     * @desc 用于获取个人主页数据
     * @return int code 操作码，0表示成功
     * @return array info 排行榜列表
     * @return string info[].total 贡献总数
     * @return string info[].userinfo 用户信息
     * @return string msg 提示信息
     */
    public function getContributeList()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $domain = new Domain_User();
        $info = $domain->getContributeList($this->touid, $this->p);

        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 私信用户信息
     * @desc 用于获取其他用户基本信息
     * @return int code 操作码，0表示成功，1表示用户不存在
     * @return array info
     * @return string info[0].id 用户ID
     * @return string info[0].isattention 我是否关注对方，0未关注，1已关注
     * @return string info[0].isattention2 对方是否关注我，0未关注，1已关注
     * @return string msg 提示信息
     */
    public function getPmUserInfo()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $info = getUserInfo($this->touid);
        if (empty($info)) {
            $rs['code'] = 1001;
            $rs['msg'] = T('user not exists');
            return $rs;
        }
        $info['isattention2'] = (string)isAttention($this->touid, $this->uid);
        $info['isattention'] = (string)isAttention($this->uid, $this->touid);

        $rs['info'][0] = $info;

        return $rs;
    }

    /**
     * 获取多用户信息
     * @desc 用于获取获取多用户信息
     * @return int code 操作码，0表示成功
     * @return array info 排行榜列表
     * @return string info[].utot 是否关注，0未关注，1已关注
     * @return string info[].ttou 对方是否关注我，0未关注，1已关注
     * @return string msg 提示信息
     */
    public function getMultiInfo()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uids = explode(",", $this->uids);

        foreach ($uids as $k => $userId) {
            if ($userId) {
                $userinfo = getUserInfo($userId);
                if ($userinfo) {
                    $userinfo['utot'] = isAttention($this->uid, $userId);

                    $userinfo['ttou'] = isAttention($userId, $this->uid);

                    if ($userinfo['utot'] == $this->type) {
                        $rs['info'][] = $userinfo;
                    }
                }
            }
        }

        return $rs;
    }

    /**
     * 获取多用户信息
     * @desc 用于获取多用户信息(不区分是否关注)
     * @return int code 操作码，0表示成功
     * @return array info 排行榜列表
     * @return string info[].utot 是否关注，0未关注，1已关注
     * @return string info[].ttou 对方是否关注我，0未关注，1已关注
     * @return string msg 提示信息
     */
    public function getUidsInfo()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uids = explode(",", $this->uids);

        foreach ($uids as $k => $userId) {
            if ($userId) {
                $userinfo = getUserInfo($userId);
                if ($userinfo) {
                    $userinfo['utot'] = isAttention($this->uid, $userId);

                    $userinfo['ttou'] = isAttention($userId, $this->uid);

                    $rs['info'][] = $userinfo;

                }
            }
        }
        return $rs;
    }

    /**
     * 登录奖励
     * @desc 用于用户登录奖励
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].bonus_switch 登录开关，0表示未开启
     * @return string info[0].bonus_day 登录天数,0表示已奖励
     * @return string info[0].count_day 连续登陆天数
     * @return string info[0].bonus_list 登录奖励列表
     * @return string info[0].bonus_list[].day 登录天数
     * @return string info[0].bonus_list[].coin 登录奖励
     * @return string msg 提示信息
     */
    public function Bonus()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->LoginBonus($uid);

        $rs['info'][0] = $info;

        return $rs;
    }

    /**
     * 登录奖励
     * @desc 用于用户登录奖励
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].bonus_switch 登录开关，0表示未开启
     * @return string info[0].bonus_day 登录天数,0表示已奖励
     * @return string msg 提示信息
     */
    public function getBonus()
    {
        $rs = array('code' => 0, 'msg' => '领取成功', 'info' => array());

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {

            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->getLoginBonus($uid);

        if (!$info) {
            $rs['code'] = 1001;
            $rs['msg'] = '领取失败';
            return $rs;
        }

        return $rs;
    }

    /**
     * 设置分销上级
     * @desc 用于用户首次登录设置分销关系
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].msg 提示信息
     * @return string msg 提示信息
     */
    public function setDistribut()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $code = checkNull($this->code);

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        if ($code == '') {
            $rs['code'] = 1001;
            $rs['msg'] = '请输入邀请码';
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->setDistribut($uid, $code);
        if ($info == 1004) {
            $rs['code'] = 1004;
            $rs['msg'] = '已设置，不能更改';
            return $rs;
        }

        if ($info == 1002) {
            $rs['code'] = 1002;
            $rs['msg'] = '邀请码错误';
            return $rs;
        }

        if ($info == 1003) {
            $rs['code'] = 1003;
            $rs['msg'] = '不能填写自己下级的邀请码';
            return $rs;
        }

        $rs['info'][0]['msg'] = '设置成功';

        return $rs;
    }

    /**
     * 获取用户间印象标签
     * @desc 用于获取用户间印象标签
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[].id 标签ID
     * @return string info[].name 名称
     * @return string info[].colour 色值
     * @return string info[].ifcheck 是否选择
     * @return string msg 提示信息
     */
    public function getUserLabel()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = checkNull($this->uid);
        $touid = checkNull($this->touid);

        $key = "getUserLabel_" . $uid . '_' . $touid;
        $label = getcaches($key);

        if (!$label) {
            $domain = new Domain_User();
            $info = $domain->getUserLabel($uid, $touid);
            $label = $info['label'];
            setcaches($key, $label);
        }

        $label_check = preg_split('/,|，/', $label);

        $label_check = array_filter($label_check);

        $label_check = array_values($label_check);


        $key2 = "getImpressionLabel";
        $label_list = getcaches($key2);
        if (!$label_list) {
            $domain = new Domain_User();
            $label_list = $domain->getImpressionLabel();
        }

        foreach ($label_list as $k => $v) {
            $ifcheck = '0';
            if (in_array($v['id'], $label_check)) {
                $ifcheck = '1';
            }
            $label_list[$k]['ifcheck'] = $ifcheck;
        }

        $rs['info'] = $label_list;

        return $rs;
    }


    /**
     * 获取用户间印象标签
     * @desc 用于获取用户间印象标签
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[].id 标签ID
     * @return string info[].name 名称
     * @return string info[].colour 色值
     * @return string msg 提示信息
     */
    public function setUserLabel()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $touid = checkNull($this->touid);
        $labels = checkNull($this->labels);

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        if ($uid == $touid) {
            $rs['code'] = 1003;
            $rs['msg'] = '不能给自己设置标签';
            return $rs;
        }

        if ($labels == '') {
            $rs['code'] = 1001;
            $rs['msg'] = '请选择印象';
            return $rs;
        }

        $labels_a = preg_split('/,|，/', $labels);
        $labels_a = array_filter($labels_a);
        $nums = count($labels_a);
        if ($nums > 3) {
            $rs['code'] = 1002;
            $rs['msg'] = '最多只能选择3个印象';
            return $rs;
        }


        $domain = new Domain_User();
        $result = $domain->setUserLabel($uid, $touid, $labels);

        if ($result) {
            $key = "getUserLabel_" . $uid . '_' . $touid;
            setcaches($key, $labels);

            $key2 = "getMyLabel_" . $touid;
            delcache($key2);
        }


        $rs['msg'] = '设置成功';

        return $rs;
    }


    /**
     * 获取自己所有的印象标签
     * @desc 用于获取自己所有的印象标签
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[].id 标签ID
     * @return string info[].name 名称
     * @return string info[].colour 色值
     * @return string info[].nums 数量
     * @return string msg 提示信息
     */
    public function getMyLabel()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $key = "getMyLabel_" . $uid;
        $info = getcaches($key);
        if (!$info) {
            $domain = new Domain_User();
            $info = $domain->getMyLabel($uid);

            setcaches($key, $info);
        }

        $rs['info'] = $info;

        return $rs;
    }


    /**
     * 获取个性设置列表
     * @desc 用于获取个性设置列表
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function getPerSetting()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $domain = new Domain_User();
        $info = $domain->getPerSetting();
        $info[] = array('id' => '17', 'name' => '意见反馈', 'thumb' => '', 'href' => get_upload_path('/index.php?g=Appapi&m=feedback&a=index'));
        $info[] = array('id' => '15', 'name' => '修改密码', 'thumb' => '', 'href' => '');
        $info[] = array('id' => '14', 'name' => '开播设置', 'thumb' => '', 'href' => '');
        $info[] = array('id' => '18', 'name' => '清除缓存', 'thumb' => '', 'href' => '');
        $info[] = array('id' => '16', 'name' => '检查更新', 'thumb' => '', 'href' => '');


        $rs['info'] = $info;

        return $rs;
    }

    /**
     * 获取用户提现账号
     * @desc 用于获取用户提现账号
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[].id 账号ID
     * @return string info[].type 账号类型
     * @return string info[].account_bank 银行名称
     * @return string info[].account 账号
     * @return string info[].name 姓名
     * @return string msg 提示信息
     */
    public function getUserAccountList()
    {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }


        $domain = new Domain_User();
        $info = $domain->getUserAccountList($uid);

        $rs['info'] = $info;

        return $rs;
    }

    /**
     * 获取用户提现账号
     * @desc 用于获取用户提现账号
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function setUserAccount()
    {
        $rs = array('code' => 0, 'msg' => '添加成功', 'info' => array());

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);

        $type = checkNull($this->type);
        $account_bank = checkNull($this->account_bank);
        $account = checkNull($this->account);
        $name = checkNull($this->name);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        if ($type == 3) {
            if ($account_bank == '') {
                $language = DI()->config->get('language.bank_notnull');
                $rs['code'] = 1001;
                $rs['msg'] = $language[$language_id];//银行名称不能为空;
                return $rs;
            }
        }

        if ($account == '') {
            $language = DI()->config->get('language.accout_notnull');
            $rs['code'] = 1002;
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }

        $data = array(
            'uid' => $uid,
            'type' => $type,
            'account_bank' => $account_bank,
            'account' => $account,
            'name' => $name,
            'addtime' => time(),
        );

        $domain = new Domain_User();
        $result = $domain->setUserAccount($data);

        if (!$result) {
            $language = DI()->config->get('language.add_fail');
            $rs['code'] = 1003;
            $rs['msg'] = $language[$language_id];//添加失败，请重试;
            return $rs;
        }

        $rs['info'][0] = $result;
        $language = DI()->config->get('language.add_succ');
        $rs['msg'] = $language[$language_id];
        return $rs;
    }


    /**
     * 删除用户提现账号
     * @desc 用于删除用户提现账号
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function delUserAccount()
    {
        $rs = array('code' => 0, 'msg' => '删除成功', 'info' => array());

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);

        $id = checkNull($this->id);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $language = DI()->config->get('language.tokenerror');
            $rs['code'] = $checkToken;
            $rs['msg'] = $language[$language_id];//银行名称不能为空;
            return $rs;
        }

        $data = array(
            'uid' => $uid,
            'id' => $id,
        );

        $domain = new Domain_User();
        $result = $domain->delUserAccount($data);

        if (!$result) {
            $language = DI()->config->get('language.delete_fail');
            $rs['code'] = 1003;
            $rs['msg'] = $language[$language_id];//'删除失败，请重试';
            return $rs;
        }
        $language = DI()->config->get('language.delete_succ');
        $rs['msg'] = $language[$language_id]; //删除成功
        return $rs;
    }

    /**
     * 判断是否主播认证
     * @desc 判断是否主播认证
     * @return int code 操作码，0表示认证成功， 1表示未认证
     * @return array info
     * @return string msg 提示信息
     */
    public function isUserauth()
    {
        $rs = array('code' => 0, 'msg' => '已认证', 'info' => array());
        $uid = checkNull($this->uid);
        $authid = checkauth($uid);
        if (empty($authid ) ) {
            $rs['code'] = 1;
            $rs['msg'] = '未认证！';
            return $rs;
        }else if ($authid['status'] == 0){
            $rs['code'] = 2;
            $rs['msg'] = '已提交申请,等待审核！';
            return $rs;
        }else if ($authid['status'] == 2){
            $rs['code'] = 1;
            $rs['msg'] = '认证失败，请重新认证！';
            return $rs;
        }
        return $rs;
    }

    /**
     * 清理僵尸粉
     * @desc 清理僵尸粉
     * @return int code 操作码，0表示删除成功
     * @return array info
     * @return string msg 提示信息
     */
    public function deleteZmobile()
    {
        $rs = array('code' => 0, 'msg' => '清理成功', 'info' => array());
        $uid = checkNull($this->uid);

        $data = array(
            'game_user_id' => $uid,
        );

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $domain = new Domain_User();
        $result = $domain->deleteZmobile($uid);

        if (!$result) {
            $language = DI()->config->get('language.delete_fail');
            $rs['code'] = 1003;
            $rs['msg'] = $language[$language_id];//'删除失败，请重试';
            return $rs;
        }
        $language = DI()->config->get('language.delete_succ');
        $rs['msg'] = $language[$language_id]; //删除成功

        return $rs;
    }
    /**
     *修改用户名
     * @desc 修改用户名
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string list[0].user_nicename 用户名
     * @return string msg 提示信息
     */
    public function editName()
    {
        $rs = array('code' => 0, 'msg' => '修改用户名成功', 'info' => array());

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $dataArray = array('user_nicename'=>$_REQUEST['user_nicename'] );
        $domain = new Domain_User();
        $userInfo = $domain->getBaseInfo($this->uid);
        if ($userInfo['user_type'] ==3){
            $rs['code'] = 1003;
            $rs['msg'] = '虚拟会员不能修改用户名';
            return $rs;
        }
        $info = $domain->userUpdate($this->uid,$dataArray );
        if (!$info) {
            $rs['code'] = 1003;
            $rs['msg'] = '修改失败，请稍候重试';
            return $rs;
        }
        /* 清除缓存 */
        delCache("userinfo_" . $this->uid);


        $rs['info'][0] = $dataArray;

        return $rs;
    }
    /**
     * 邀请码
     * @desc 邀请码
     * @return int code 操作码，0表示成功， 1表示用户不存在
     * @return array info
     * @return string msg 提示信息
     * @return string info[0].code 邀请码
     * @return string info[0].tenant_id 租户id
     * @return string info[0].url 邀请链接
     */
    public  function invitationCode(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $game_tenant_id = checkNull($this->game_tenant_id);
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $userDomain = new Domain_User();
        $userInfo = $userDomain->getBaseInfo($uid);
        if ($userInfo['user_type'] ==4){
            $rs['code'] = 800;
            $rs['msg'] = '该功能不对游客开放，请注册会员后使用';
            return $rs;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->invitationCode($uid);
        $config = getConfigPri();
        $url = isset($config['wx_siteurl']) && $config['wx_siteurl'] ? $config['wx_siteurl'] : 'http://'.$_SERVER['HTTP_HOST'].'/?page=register';
        $info['url'] = $url.'&code='.$info['code'].'&game_tenant_id='.$game_tenant_id.'&zone='.$info['zone'];
        unset($info['zone']);
        $rs['info'][0] = $info;

        return $rs;
    }

    /**
     * 彩票下注数据，写入直播后台 接口
     * @desc 彩票直播间下注
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[].playname 彩票类型
     * @return string info[].giftcount 数量
     * @return string info[].totalcoin 总价
     * @return string msg 提示信息
     */
    public function setBetrecord()
    {
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $type = checkNull($this->type);
        $action = checkNull($this->action);
        $uid = checkNull($this->uid);
        $touid = checkNull($this->touid);
        $playname= checkNull($this->playname);
        $giftcount = checkNull($this->giftcount);
        $totalcoin = checkNull($this->totalcoin);
        $showid = checkNull($this->showid);
        $game_tenant_id = checkNull($this->game_tenant_id);

        $userinfo = getUserInfo($uid);


        $data = array(
            'type' => $type,
            'action' => $action,
            'uid' => $uid,
            'touid' => $touid,
            'playname' => $playname,
            'giftcount' => $giftcount,
            'totalcoin' => $totalcoin,
            'showid' => $showid,
            'addtime' => time(),
            'tenant_id'=>$userinfo['tenant_id'],
            'receive_tenant_id' =>$game_tenant_id,
        );
        $domain = new Domain_User();
        $info = $domain->setBetrecord($data);

        $rs['info'][0] = $info;

        return $rs;

    }
    /**
     * 主播申请
     * @desc 主播申请
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public  function applyBecomeLive(){
        $rs = array('code' => 0, 'msg' => '申请成功', 'info' => array());

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $userDomain = new Domain_User();
        $userInfo = $userDomain->getBaseInfo($this->uid);
        if ($userInfo['user_type'] ==3){
            $rs['code'] = 1003;
            $rs['msg'] = '虚拟会员不能成为主播';
            return $rs;
        }
        $game_tenant_id = $this->game_tenant_id;
        $uid = $this->uid;
        $real_name = $this->real_name;
        $mobile = $this->mobile;
        $wchat = $this->wchat;
        $cer_no = $this->cer_no;
        if ($mobile !=$userInfo['mobile'] ){
            $rs['code'] = 1006;
            $rs['msg'] = '请填写注册时的手机号';
            return $rs;
        }
        if(!checkIdCard($cer_no)){
            $rs['code'] = 1005;
            $rs['msg'] = '您的身份证号码有误，请重新填写';
            return $rs;
        }
        if (!isset($_FILES['front_view']) || !isset($_FILES['back_view']) || !isset($_FILES['handset_view']) ) {
            $rs['code'] = 1001;
            $rs['msg'] = T('miss upload file');
            return $rs;
        }

        if ($_FILES["front_view"]["error"] > 0 || $_FILES["back_view"]["error"] > 0 || $_FILES["handset_view"]["error"] > 0) {
            $rs['code'] = 1002;
            if ($_FILES["front_view"]["error"] > 0){
                $error = array('error' => $_FILES['front_view']['error']);
            }
            if ($_FILES["back_view"]["error"] > 0){
                $error = array('error' => $_FILES['back_view']['error']);
            }
            if ($_FILES["handset_view"]["error"] > 0){
                $error = array('error' => $_FILES['handset_view']['error']);
            }
            $rs['msg'] = T('failed to upload file with error: {error}',$error);
            DI()->logger->debug('failed to upload file with error: ' . $error);
            return $rs;
        }

        $uptype = DI()->config->get('app.uptype');

        if ($uptype == 1) {
            //七牛
            $front_view_url = DI()->qiniu->uploadFile($_FILES['front_view']['tmp_name']);
            $back_view_url = DI()->qiniu->uploadFile($_FILES['back_view']['tmp_name']);
            $handset_view_url = DI()->qiniu->uploadFile($_FILES['handset_view']['tmp_name']);
            if (!empty($front_view_url) && !empty($back_view_url) && !empty($handset_view_url) ) {
                $front_view_img = $front_view_url . '?imageView2/2/w/600/h/600'; //600 X 600
                $front_view_img_thumb = $front_view_url . '?imageView2/2/w/200/h/200'; // 200 X 200
                $back_view_img = $back_view_url . '?imageView2/2/w/600/h/600'; //600 X 600
               // $back_view_img_thumb = $back_view_url . '?imageView2/2/w/200/h/200'; // 200 X 200
               // $handset_view_img = $handset_view_url . '?imageView2/2/w/600/h/600'; //600 X 600
                //$handset_view_img_thumb = $handset_view_url . '?imageView2/2/w/200/h/200'; // 200 X 200
                $data = array(
                    "front_view" => $front_view_img,
                    "front_view_thumb" => $front_view_img_thumb,
                    "back_view" => $back_view_img,
                   // "back_view_thumb" => $back_view_img_thumb,
                   // "handset_view" => $handset_view_img,
                   // "handset_view_thumb" => $handset_view_img_thumb,
                );
                $data2 = array(
                    "front_view" => $front_view_img,
                    "front_view_thumb" => $front_view_img_thumb,
                    "back_view" => $back_view_img,
                   // "back_view_thumb" => $back_view_img_thumb,
                   // "handset_view" => $handset_view_img,
                    //"handset_view_thumb" => $handset_view_img_thumb,
                );
            }
        } else if ($uptype == 2) {

            DI()->ucloud->set('save_path', '/idcard/' . date("Ymd"));
            //新增修改文件名设置上传的文件名称
            // DI()->ucloud->set('file_name', $this->uid);

            //上传表单名
            $front_view_res = DI()->ucloud->upfile($_FILES['front_view']);
            $front_view_files = '../upload' . $front_view_res['file'];
            $front_view_newfiles = str_replace(".png", "_thumb.png", $front_view_files);
            $front_view_newfiles = str_replace(".jpg", "_thumb.jpg", $front_view_newfiles);
            $front_view_newfiles = str_replace(".gif", "_thumb.gif", $front_view_newfiles);
            $front_view_PhalApi_Image = new Image_Lite();
            //打开图片
            $front_view_PhalApi_Image->open($front_view_files);
            $front_view_PhalApi_Image->thumb(660, 660, IMAGE_THUMB_SCALING);
            $front_view_PhalApi_Image->save($front_view_files);
            $front_view_PhalApi_Image->thumb(200, 200, IMAGE_THUMB_SCALING);
            $front_view_PhalApi_Image->save($front_view_newfiles);
            $front_view = $front_view_res['url']; //600 X 600
            //$front_view_thumb = str_replace(".png", "_thumb.png", $front_view);
           // $front_view_thumb = str_replace(".jpg", "_thumb.jpg", $front_view_thumb);
            //$front_view_thumb = str_replace(".gif", "_thumb.gif", $front_view_thumb);
            $front_view2 = '/api/upload' . $front_view_res['file']; //600 X 600
            //$front_view_thumb2 = str_replace(".png", "_thumb.png", $front_view2);
            //$front_view_thumb2 = str_replace(".jpg", "_thumb.jpg", $front_view_thumb2);
            //$front_view_thumb2 = str_replace(".gif", "_thumb.gif", $front_view_thumb2);

            $back_view_res = DI()->ucloud->upfile($_FILES['back_view']);
            $back_view_files = '../upload' . $back_view_res['file'];
            $back_view_newfiles = str_replace(".png", "_thumb.png", $back_view_files);
            $back_view_newfiles = str_replace(".jpg", "_thumb.jpg", $back_view_newfiles);
            $back_view_newfiles = str_replace(".gif", "_thumb.gif", $back_view_newfiles);
            $back_view_PhalApi_Image = new Image_Lite();
            //打开图片
            $back_view_PhalApi_Image->open($back_view_files);
            $back_view_PhalApi_Image->thumb(660, 660, IMAGE_THUMB_SCALING);
            $back_view_PhalApi_Image->save($back_view_files);
            $back_view_PhalApi_Image->thumb(200, 200, IMAGE_THUMB_SCALING);
            $back_view_PhalApi_Image->save($back_view_newfiles);
            $back_view = $back_view_res['url']; //600 X 600
            //$back_view_thumb = str_replace(".png", "_thumb.png", $back_view);
            //$back_view_thumb = str_replace(".jpg", "_thumb.jpg", $back_view_thumb);
           // $back_view_thumb = str_replace(".gif", "_thumb.gif", $back_view_thumb);
            $back_view2 = '/api/upload' . $back_view_res['file']; //600 X 600
            //$back_view_thumb2 = str_replace(".png", "_thumb.png", $back_view2);
           // $back_view_thumb2 = str_replace(".jpg", "_thumb.jpg", $back_view_thumb2);
            //$back_view_thumb2 = str_replace(".gif", "_thumb.gif", $back_view_thumb2);

            $handset_view_res = DI()->ucloud->upfile($_FILES['handset_view']);
            $handset_view_files = '../upload' . $handset_view_res['file'];
            $handset_view_newfiles = str_replace(".png", "_thumb.png", $handset_view_files);
            $handset_view_newfiles = str_replace(".jpg", "_thumb.jpg", $handset_view_newfiles);
            $handset_view_newfiles = str_replace(".gif", "_thumb.gif", $handset_view_newfiles);
            $handset_view_PhalApi_Image = new Image_Lite();
            //打开图片
            $handset_view_PhalApi_Image->open($handset_view_files);
            $handset_view_PhalApi_Image->thumb(660, 660, IMAGE_THUMB_SCALING);
            $handset_view_PhalApi_Image->save($handset_view_files);
            $handset_view_PhalApi_Image->thumb(200, 200, IMAGE_THUMB_SCALING);
            $handset_view_PhalApi_Image->save($handset_view_newfiles);
            $handset_view = $handset_view_res['url']; //600 X 600
           // $handset_view_thumb = str_replace(".png", "_thumb.png", $handset_view);
           // $handset_view_thumb = str_replace(".jpg", "_thumb.jpg", $handset_view_thumb);
          //  $handset_view_thumb = str_replace(".gif", "_thumb.gif", $handset_view_thumb);
            $handset_view2 = '/api/upload' . $handset_view_res['file']; //600 X 600
          //  $handset_view_thumb2 = str_replace(".png", "_thumb.png", $handset_view2);
           // $handset_view_thumb2 = str_replace(".jpg", "_thumb.jpg", $handset_view_thumb2);
           // $handset_view_thumb2 = str_replace(".gif", "_thumb.gif", $handset_view_thumb2);

            $data = array(
                "front_view" => $front_view,
                "back_view" => $back_view,

                "handset_view" => $handset_view,

            );

            $data2 = array(
                "front_view" => $front_view2,
                "back_view" => $back_view2,
                "handset_view" => $handset_view2,

            );
        }


        @unlink($_FILES['front_view']['tmp_name']);
        @unlink($_FILES['back_view']['tmp_name']);
        @unlink($_FILES['handset_view']['tmp_name']);
        $domain = new Domain_User();
        if (!$data) {
            $rs['code'] = 1003;
            $rs['msg'] = '申请失败，请稍候重试';
            return $rs;
        }
        $data2['tenant_id'] = getTenantId();
        $data2['real_name'] = $real_name;
        $data2['uid'] = $uid;
        $data2['mobile'] = $mobile;
        $data2['cer_no'] = $cer_no;
        $data2['addtime'] = time();
        $data2['status'] = 0;
        $data2['wchat'] =$wchat;
        $info = $domain->applyBecomeLive($this->uid, $data2);


        if ($info == 1004){
            $rs['code'] = 1004;
            $rs['msg'] = '已提交申请或已是主播,请勿重复提交';
            return $rs;
        }

        if ($info){
            return $rs;
        }else{
            $rs['code'] = 1006;
            $rs['msg'] = '系统错误,请联系管理员';
            return $rs;
        }

    }
    /**
     * 收支明细
     * @desc 收支明细
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     * @return array info[0].
     */
    public  function incomeExpenditure (){
        $rs = array('code' => 0, 'msg' => '收支列表', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p = $this->p;
        $domain = new Domain_User();
        $rs['info']= $domain->incomeExpenditure($this->uid,$p);
        return  $rs;
    }
    /**
     * 用户明细收支明细（新接口）
     * @desc 收支明细
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     * @return array info[0].
     */
    public  function incomeExpenditurenew (){
        $rs = array('code' => 0, 'msg' => '收支列表', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        $type = $this->type;
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p = $this->p;
        $domain = new Domain_User();
        $rs['info']= $domain->incomeExpenditurenew($this->uid,$p,$type);
        return  $rs;
    }
    /**
     * 用户上传长视频的 所有收益
     * @desc 长视频的 所有收益收支明细
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     * @return array info[0].
     */
    public  function incomeUploadvideo (){
      
        $rs = array('code' => 0, 'msg' => '上传视频列表', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }
        $p = $this->p;
        $domain = new Domain_User();
        $res = $domain->incomeUploadvideo($this->uid,$p);

        $rs['info'] = $res['lists'];
        $rs['today_income'] = $res['today_income'];
        $rs['total_income'] = $res['total_income'];
        return  $rs;
    }

    /**
     * 主播信息
     * @desc 主播信息 如果用户是svip 可就是最高级vip 就看查看到主播信息，如果不是返回字段都为空
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     * @return array info[0].sex 性别
     * @return array info[0].birthday 生日
     * @return array info[0].signature 个性签名
     * @return array info[0].province 省
     * @return array info[0].city 市
     * @return array info[0].real_name 真实姓名
     * @return array info[0].mobile 电话
     * @return array info[0].wchat 微信
     */
    public  function getLiveInfo(){
        $rs = array('code' => 0, 'msg' => '主播信息', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }  $rs = array('code' => 0, 'msg' => '主播信息', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($this->uid, $this->token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];
            return $rs;
        }

        $domain = new Domain_User();
        $rs['info']= $domain->getLiveInfo($this->uid,$this->live_id);
        return  $rs;
    }
    /**
     * 用户协议
     * @desc 用户协议

     */
    public  function  getUserAgreement(){
        $rs = array('code' => 0, 'msg' => '用户协议', 'info' => array());
        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $config=getConfigPub();
        $rs['info'] =  array($config['user_agreement']);
        return  $rs;
    }

    /**
     * 获取下线成员
     * @desc 用于获取下线成员
     * @return string msg 提示信息
     * @return array info 列表数据
     * @return array info[0].directly_sub 直属会员
     * @return array info[0].undirectly_sub 非直属会员
     * @return array info[0].all_sub 代理下线（包括所有直属和非直属会员）
     * @return int info[0].all_sub.0.id 用户ID
     * @return string info[0].all_sub.0.user_nicename 用户昵称
     * @return string info[0].all_sub.0.avatar_thumb 用户头像
     * @return int info[0].all_sub.0.is_friend 是否已经是聊天室会员：0否，1是
     */
    public function getSubUser(){
        $rs = array('code' => 0, 'msg' => '代理下线', 'info' => array());
        $uid = $this->uid;
        $token = checkNull($this->token);
        $room_id = $this->room_id;

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->getSubUser($uid,$room_id);
        $rs['info'][0] = $info;
        return $rs;
    }

    /**
     * 是否需要更新版本号
     * @desc
     * @return int code 状态码
     * @return string msg 提示信息
     * @return object info 详情
     * @return string info.url 下载链接
     * @return int info.code 类型：0 不用更新 1 需要强制更新 ，2 不需要强制更新
     * @return string info.official_website_url 官网地址
     * @return string info.des APK版本更新说明
     * @return string info.latest_version 最新版本号
     */
    public function isupdate(){
        $data =  getConfigPub();
        $rs = array(
            'code' => 0,
            'msg' => '无需更新',
            'info' => array(
                'code' => 0,
                'msg' => '无需更新',
                'official_website_url'=>$data['official_website_url'],
                'url'=>'',
                'des'=>'',
                'latest_version'=>'',
            )
        );
        $channel = $this->channel;
        $version = $this->version;

       if ($channel =='ios'){
           if ( $data['ipa_ver'] != $version){
               if ($data['ipa_forced_update'] == 1){
                   $rs['info']['code'] = 1;
               }else{
                   $rs['info']['code'] = 2;
               }
               $rs['msg'] ='请更新app';
               $rs['info']['msg'] ='请更新app';
           }
           $rs['info']['url'] = $data['ipa_url'];
           $rs['info']['des'] = $data['ipa_des'];
           $rs['info']['latest_version'] = $data['ipa_ver'];
       }
        if ($channel =='android'){
            if ( $data['apk_ver'] != $version){
                if ($data['apk_forced_update'] == 1){
                    $rs['info']['code'] = 1;
                }else{
                    $rs['info']['code'] = 2;
                }
                $rs['msg'] ='请更新app';
                $rs['info']['msg'] ='请更新app';
            }
            $rs['info']['url'] = $data['apk_url'];
            $rs['info']['des'] = $data['apk_des'];
            $rs['info']['latest_version'] = $data['apk_ver'];
        }
        return $rs;
    }

    /**
     * 记录用户行为
     * @desc 记录用户行为
     * @return string msg 提示信息
     * @return array info 列表数据
     */
    public function userAction()
    {
        $rs = array('code' => 0, 'msg' => '记录用户行为成功', 'info' => array());
        $uid = $this->uid;
        $language_id = $this->language_id;
        $token = checkNull($this->token);
        $json_data = checkNull($this->json_data);

        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        if (empty($language_id)) {
            $language_id = 101;
        }

        $domain = new Domain_User();
        $info = $domain->userAction($uid, $json_data);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 是否需要主播认证
     * @desc 是否需要主播认证
     * @return string msg 提示信息
     * @return array info.code 1 :需要认证 ：0 不需要认证
     */
    public function isAnchorAuthentication(){
        $rs = array('code' => 0, 'msg' => '不需要认证', 'info' => array());
        $uid = $this->uid;
        $token = checkNull($this->token);
        $checkToken=checkToken($uid,$token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = '您的登陆状态失效，请重新登陆！';
            return $rs;
        }
        $config = getConfigPri();
        if ($config['auth_islimit']==1){
            $rs['code'] =2027;
            $rs['msg'] ='需要认证';
        }

        return $rs;
    }

    /**
     * 获取币种名称及汇率
     * @desc 是否需要主播认证
     * @return string msg 提示信息
     * @return array info.name_coin 用户使用币种  name_votes 主播获取的 money_rate 比例
     */
    public  function coinName(){
        $rs = array('code' => 0, 'msg' => '不需要认证', 'info' => array());
        $config = getConfigPri();
        $rs['info']['name_coin'] = $config['name_coin'];
        $rs['info']['name_votes'] = $config['name_votes'];
        $rs['info']['money_rate'] = $config['money_rate'];
        return $rs;
    }

    /**
     * 保存主播美颜效果参数
     * @desc 用于保存主播美颜效果参数
     * @return int code 操作码，0表示成功
     * @return string msg 提示信息
     */
    public function savebeauty(){
        $rs = array('code' => 0, 'msg' => '保存成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $client = $this->client;
        $data_param = checkNull($this->data_param);
        $param = $_REQUEST;

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        if(!isset($param['data_param']) && isset($param['sprout_white']) && isset($param['sprout_skin']) && isset($param['sprout_saturated']) && isset($param['sprout_pink']) && isset($param['sprout_eye']) && isset($param['sprout_face']) && isset($param['select_tifilter'])){
            $data_param = array(
                'sprout_white' => intval($param['sprout_white']),
                'sprout_skin' => intval($param['sprout_skin']),
                'sprout_saturated' => intval($param['sprout_saturated']),
                'sprout_pink' => intval($param['sprout_pink']),
                'sprout_eye' => intval($param['sprout_eye']),
                'sprout_face' => intval($param['sprout_face']),
                'select_tifilter' => intval($param['select_tifilter']),
            );
            $data_param = json_encode($data_param);
        }

        $domain = new Domain_User();
        $info = $domain->savebeauty($uid,$client,$data_param);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 搜索会员
     * @desc 用于搜索会员
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string info[0].id 会员ID
     * @return string info[0].user_nicename 会员昵称
     * @return string info[0].avatar 会员昵称
     * @return string info[0].level_anchor 用户等级
     * @return string info[0].haslive 是否直播：0.暂无直播，1.直播中
     * @return string info[0].stream 流名（haslive为1时返回）
     * @return string msg 提示信息
     */
    public function searchUser() {
        $rs = array('code' => 0, 'msg' => '', 'info' => array());

        $uid=checkNull($this->uid);
        $token=checkNull($this->token);
        $key=checkNull($this->key);
        $p=checkNull($this->p);
        if(!$p){
            $p=1;
        }

        if(!$key){
            $rs['code']=2055;
            $rs['msg']=codemsg('2055');
            return $rs;
        }

        if($uid && $token){
            $checkToken = checkToken($uid, $token);
            if ($checkToken == 700) {
                $rs['code'] = $checkToken;
                $rs['msg'] = codemsg($checkToken);
                return $rs;
            }
        }

        $domain = new Domain_User();
        $info = $domain->searchUser($uid,$key,$p);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取会员的用户等级信息
     * @desc 用于获取会员的用户等级信息
     * @return int code 操作码，0表示成功
     * @return array info
     * @return array info.level_info 会员的用户等级信息
     * @return array info.next_level_info 会员的下一个用户等级信息
     * @return array info.anchor_level_info 会员的主播等级信息
     * @return array info.anchor_next_level_info 会员的下一个主播等级信息
     * @return string msg 提示信息
     */
    public function getUserLevel(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=checkNull($this->uid);
        $token=checkNull($this->token);

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->getUserLevel($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }
    public function issuper(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid=checkNull($this->uid);
        $token=checkNull($this->token);

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }


        $info = isSuper($uid);
        $rs['info'][0]['issuper'] = $info;
   
        return $rs;
    }
    /**
     * 中奖消息写入队列
     * @desc 中奖消息写入队列
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function insertBet(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $redis = connectionRedis();
        $liveuid=checkNull($this->liveuid);
        $content=checkNull($this->content);
        if($content == ''){
            $rs = array('code' => 1001, 'msg' => 'content 字段数据错误', 'info' => array());
        }
        logapi(['updateResult'=>$content],'【java中奖信息接口】');  // 接口日志记录
        $redis->lPush('winning_queues',$content);



        return $rs;
    }
    /**
     * 根据game_user_id获取会员信息
     * @desc 中奖消息写入队列
     * @return int code 操作码，0表示成功
     * @return array info
     * @return string msg 提示信息
     */
    public function getGameuserinfo(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $game_tenant_id = checkNull($this->game_tenant_id);
        $game_user_id=checkNull($this->game_user_id);
        $domain = new Domain_User();
        $info = $domain->getGameuserinfo($game_user_id,$game_tenant_id);
        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 不可提现余额转提现
     * 根据game_user_id获取会员信息
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     */
    public  function charge_withdrawn(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->charge_withdrawn($uid);
        if ($info['code'] == 1000){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'];
            return $rs;
        }
        $rs['info'] = $info['info'];
        return $rs;
    }

    /**
     * 首充送豪礼列表
     *  @desc   根据租户ID，返回首充豪礼列表
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     */
    public  function charge_gift(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->charge_gift($uid);
        if ($info['code'] == 1000){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'];
            return $rs;
        }

        if(!empty($info['info'])){
             $rs['info'] = $info['info'];
        }
        return $rs;
    }
    /**
         * 首充回调接口
         * @desc   给java回调，用于给会员添加首充豪礼
         * 根据game_user_id获取会员信息
         *  @return array code  0 成功
         * @return array info
         * @return string msg 提示信息
    */
    public  function chargegift_send(){
            $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
            $game_user_id= checkNull($this->game_user_id);
            $game_tenant_id = $this->game_tenant_id;
            $price = $this->price;


            $domain = new Domain_User();
            $whichTenant= whichTenat($game_tenant_id);
            if($whichTenant==1){//集成 发放首充礼物
                $info = $domain->chargegift_send($game_user_id,$price);
            }else{
                $info = $domain->chargegift_sendalone($game_user_id);
            }

            if ($info['code'] == 1001){
                $rs['code'] = $info['code'];
                $rs['msg'] = $info['msg'];
                return $rs;
            }

            $rs['info'] = $info['info'];
            return $rs;
     }
    /**
     * 首充背包
     * @desc 用于获取会员已经获得的首充豪礼
     * 根据game_user_id获取会员信息
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     */
    public  function chargegift_list(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $language_id = $_REQUEST['language_id'];
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->chargegift_list($uid);
        if ($info['code'] == 1000){
            $rs['code'] = $info['code'];
            $rs['msg'] = $info['msg'];
            return $rs;
        }

        $rs['info'] = $info['info'];
        if(empty( $info['info'])){
            $rs['info']= (object)[];
        }
        return $rs;
    }
    /**
     * 用户给用户转账
     * @desc 用户给用户转账
     *
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     */
    public  function transfer(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $touid= checkNull($this->touid);
        $user_nicename= checkNull($this->user_nicename);
        $amount = checkNull($this->amount);
        $language_id = $_REQUEST['language_id'];
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->transfer($uid,$touid,$amount,$user_nicename);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }
    /**
     * 查找用户
     * @desc 查找用户
     *
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     */
    public  function findUser(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $userdata= checkNull($this->userdata);

        $language_id = $_REQUEST['language_id'];
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->findUser($userdata);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 返回注册域名
     * @desc 返回注册域名
     *
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     * @return array info.0 reg_url域名
     * @return array info.0 reg_key域名
     */
    public  function getregUrl(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());


        $language_id = $_REQUEST['language_id'];

        $domain = new Domain_User();
        $info = $domain->getregUrl();
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 余额宝转出
     * @desc 用户转出余额宝
     *
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     */

    public  function transferOutyuebao(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $amount=$this->amount;
        $bankname=$this->bankname;
        $banknumber=$this->banknumber;
        $realname=$this->realname;
        $phonenumber=$this->phonenumber;
        $type=$this->type;
        if($amount==0){
            $rs['code'] = 1002;
            $rs['msg'] ='金额不能为0';
            return $rs;
        }
        $language_id = $_REQUEST['language_id'];
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        if($type==2){
            if(empty($bankname) || empty($banknumber)){
                $rs['code'] = 1001;
                $rs['msg'] ='转出到银行卡，银行卡名称和卡号不能为空';
                return $rs;
            }
        }
        $data= array(
            'bankname'=>$bankname,
            'banknumber'=>$banknumber,
            'realname'=>$realname,
            'phonenumber'=>$phonenumber,

        );

        $domain = new Domain_User();
        $info = $domain->transferOutyuebao($uid,$amount,$data,$type);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }
    /**
     * 余额宝转入
     * @desc 用户转入余额宝
     *
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     */

    public  function transferInyuebao(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $amount=$this->amount;
        $bankname=$this->bankname;
        $banknumber=$this->banknumber;
        $realname=$this->realname;
        $phonenumber=$this->phonenumber;
        $type=$this->type;;
        if($amount==0){
            $rs['code'] = 1002;
            $rs['msg'] ='金额不能为0';
            return $rs;
        }
        $language_id = $_REQUEST['language_id'];
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        if($type==2){
            if(empty($bankname) || empty($banknumber)){
                $rs['code'] = 1001;
                $rs['msg'] ='转出到银行卡，银行卡名称和卡号不能为空';
                return $rs;
            }
        }
        $data= array(
            'bankname'=>$bankname,
            'banknumber'=>$banknumber,
            'realname'=>$realname,
            'phonenumber'=>$phonenumber,

        );

        $domain = new Domain_User();
        $info = $domain->transferInyuebao($uid,$amount,$data,$type);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }
    /**
     * 结算利息
     * @desc 返回注册域名
     *
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     * @return array info.0 reg_url域名
     * @return array info.0 reg_key域名
     */
    public  function settlementYuebao(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $domain = new Domain_User();
        $info = $domain->settlementYuebao();
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }
    /**
     * 自动转入到余额宝
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息

     */
    public  function transferToyuebaoauto(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $domain = new Domain_User();
        $info = $domain->transferToyuebaoauto();
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 开通余额宝
     * @desc 开通余额宝type=1 查询  type=2开通
     *
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息
     * @return array info.0 yeb_isopen=0未开通
     * @return array info.0 yeb_isopen=1开通
     */
    public  function openYuebao(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $type=$this->type;

        $language_id = $_REQUEST['language_id'];
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        logapi(['bet info '=>$type],'【开通余额宝】');
        $domain = new Domain_User();
        $info = $domain->openYuebao($type,$uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * 获取我的下级用户列表
     * @desc 获取我的下级用户列表
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     * @return object info.0
     * @return array info.0.list
     * @return int info.0.one_sub_count 我的下级数量
     * @return int info.0.one_sub_count 我的下下级数量
     * @return int info.0.list.uid 用户ID
     * @return string info.0.list.user_nicename 用户昵称
     * @return int info.0.list.type 1.我的下级，2.我的下下级
     */
    public  function getMySubUserList(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());

        $uid = $this->uid;
        $token = checkNull($this->token);
        $type = $this->type;

        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->getMySubUserList($uid, $type);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    /**
     * cp消费接口
     * @desc 获取nft消费接口
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     * @return object info.0
     */
    public  function lotteryConsumption(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array(),'balance'=>0);

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $amount=$this->amount;
        $type=$this->type;;
      /*  if($amount==0){
            $rs['code'] = 1002;
            $rs['msg'] ='金额不能为0';
            return $rs;
        }*/

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $rs['msg'] = codemsg($checkToken);
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->lotteryConsumption($uid,$amount, $type);
        $domain2 = new Domain_User();
        $coin=$domain2->getBalance($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['balance'] = $coin['coin'] ? $coin['coin'] : $rs['balance'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];

        return $rs;
    }
    /**
     * nft消费接口
     * @desc 获取nft消费接口
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     * @return object info.0
     */
    public  function nftConsumption(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array(),'balance'=>0);

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $amount=$this->amount;
        $type=$this->type;;
        if($amount==0){
            $rs['code'] = 1002;
            $rs['msg'] ='金额不能为0';
            return $rs;
        }

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        if ($token != md5('b1f0e37dbf5566403b94ae15f0314f41')) {
            $rs['code'] = 700;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->nftConsumption($uid,$amount, $type);
        $domain2 = new Domain_User();
        $coin=$domain2->getBalance($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['balance'] = $coin['coin'] ? $coin['coin'] : $rs['balance'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];

        return $rs;
    }
    /**
     * 商城消费接口
     * @desc 获取商城消费接口
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     * @return object info.0
     */
    public  function shopConsumption(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array(),'balance'=>0);

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $amount=$this->amount;
        $shoppingVoucherId=$this->shoppingVoucherId;
        $type=$this->type;;
        if($amount==0){
            $rs['code'] = 1002;
            $rs['msg'] ='金额不能为0';
            return $rs;
        }

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        if ($token != md5('b1f0e37dbf5566403b94ae15f0314f41')) {
            $rs['code'] = 700;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->shopConsumption($uid,$amount, $type,$shoppingVoucherId);
        $domain2 = new Domain_User();
        $coin=$domain2->getBalance($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['balance'] = $coin['coin'] ? $coin['coin'] : $rs['balance'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];

        return $rs;
    }
    /**
     * 商城 店铺 消费接口
     * @desc 获取商城消费接口
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     * @return object info.0
     */
    public  function shopuserConsumption(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array(),'balance'=>0);
        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $ids = checkNull($this->ids);
        $ids = trim($ids,',');
        $shoptoken = $this->shoptoken;
        $shop_order_id = $this->shop_order_id;
        $cg_order_id = $this->cg_order_id;
        $shop_order_no = checkNull($this->shop_order_no);
        $cg_order_no = checkNull($this->cg_order_no);
        $amount=$this->amount;
        $type=$this->type;;
        if($amount==0){
            $rs['code'] = 1002;
            $rs['msg'] ='金额不能为0';
            return $rs;
        }

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        //保留128字节，超出不写入
        if(strlen($cg_order_id)>128){
            $cg_order_id =  substr($cg_order_id,0,128);
        }

        if ($token != md5('b1f0e37dbf5566403b94ae15f0314f41')) {
            $checkToken = checkToken($uid, $token);
            if ($checkToken == 700) {
                $rs['code'] = $checkToken;
                $language = DI()->config->get('language.tokenerror');
                $rs['msg'] = '用户token失效';//账号不能为空;
                return $rs;
            }
        }

        $domain = new Domain_User();
        $info = $domain->shopuserConsumption($uid, $amount, $type, $shoptoken, $ids, $shop_order_id,$cg_order_id, $shop_order_no, $cg_order_no);
        $domain2 = new Domain_User();
        $coin=$domain2->getBalance($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['balance'] = $coin['coin'] ? $coin['coin'] : $rs['balance'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];

        return $rs;
    }


    /**
     * 商城  保证金缴纳
     * @desc 获取商城消费接口
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     * @return object info.0
     */
    public  function shopuserBondpay(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array(),'balance'=>0);

        $uid = checkNull($this->uid);
        $token = checkNull($this->token);
        $shoptoken = $this->shoptoken;

        $amount=$this->amount;
        $type=$this->type;;
     /*   if($amount==0){
            $rs['code'] = 1002;
            $rs['msg'] ='金额不能为0';
            return $rs;
        }*/

        $language_id = $_REQUEST['language_id'];
        if (empty($language_id)) {
            $language_id = 101;
        }
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = '用户token失效';//账号不能为空;
            return $rs;
        }

        $domain = new Domain_User();
        $info = $domain->shopuserBondpay($uid,$amount, $type,$shoptoken);
        $domain2 = new Domain_User();
        $coin=$domain2->getBalance($uid);

        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['balance'] = $coin['coin'] ? $coin['coin'] : $rs['balance'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];

        return $rs;
    }
    /**
     * 游客绑定账号
     * @desc 游客绑定账号
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     * @return object info.0
     */
    public function bindUser(){
        $rs = array('code' => 0, 'msg' => '绑定成功', 'info' => array());
        $redis = connectionRedis();

        $user_login=checkNull($this->user_login);
        $user_pass=checkNull($this->user_pass);
        $user_pass2=checkNull($this->user_pass2);
        $source=checkNull($this->source);
        $code=checkNull($this->code);
        $tenantId=getTenantId();
        $language_id=$this->language_id;
        $zone=checkNull($this->zone);
        $agent_code=checkNull($this->agent_code);
        $uid = $this->uid;
        $token = $this->token;
        $checkToken = checkToken($uid, $token);
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = '用户token失效';//账号不能为空;
            return $rs;
        }
        $sm_key = 'shortmsg_'.$_SESSION['session_id'].$user_login;
        $sm_data = getcache($sm_key);
        $config = getConfigPri();

        if($config['sendcode_switch']){
            if(!$sm_data){
                $rs['code'] = 2067;
                $rs['msg'] = codemsg('2067');
                return $rs;
            }
            if(!$sm_data || !$sm_data['reg_mobile'] || !$sm_data['reg_mobile_code']){
                $rs['code'] = 2067;
                $rs['msg'] = codemsg('2067');
                return $rs;
            }
            if($user_login!=$sm_data['reg_mobile']){
                $rs['code'] = 2068;
                $rs['msg'] = codemsg('2068');
                return $rs;
            }
            if($code!=$sm_data['reg_mobile_code']){
                $rs['code'] = 2069;
                $rs['msg'] = codemsg('2069');
                return $rs;
            }
        }

        if (empty($language_id)){
            $language_id = 101;
        }
        if($user_pass!=$user_pass2){
            $language = DI()->config->get('language.userreg_passcheck');
            $rs['code'] = 1003;
            $rs['msg'] = $language[$language_id];//两次输入的密码不一致
            return $rs;
        }

        $check = passcheck($user_pass);

        if($check==0){
            $language = DI()->config->get('language.userreg_passlength');
            $rs['code'] = 1004;
            $rs['msg'] = $language[$language_id];//密码6-12位数字与字母
            return $rs;
        }else if($check==2){
            $language = DI()->config->get('language.userreg_onlynum');
            $rs['code'] = 1005;//
            $rs['msg'] = $language[$language_id];//密码不能纯数字或纯字母
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->bindUser($uid,$user_login,$user_pass,$source,$tenantId,$zone,$agent_code);

        if($info==1006){
            $language = DI()->config->get('language.userreg_already');
            $rs['code'] = 1006;
            $rs['msg'] = $language[$language_id];//该手机号已被注册
            return $rs;
        }else if($info==1002){
            $language = DI()->config->get('language.agent_code_error');
            $rs['code'] = 1002;
            $rs['msg'] = $language[$language_id];//邀请码错误
            return $rs;
        }else if($info==1007){
            $language = DI()->config->get('language.userreg_fail');
            $rs['code'] = 1007;
            $rs['msg'] = $language[$language_id];//注册失败，请重试
            return $rs;
        }else if ($info==2122){

            $rs['code'] = 2122;
            $rs['msg'] = '邀请码用户为游客';//注册失败，请重试
            return $rs;
        }
        else if ($info==2123){

            $rs['code'] = 2123;
            $rs['msg'] = '此账号已绑定';//注册失败，请重试
            return $rs;
        }
       /* $login_domain = new Domain_Login();
        $info = $login_domain->userGetcode($user_login);*/



        $redis->del($sm_key);

        return  $rs;
    }
    /**
     * 签到
     * @desc 签到
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info
     * @return object info.0
     */
    public function sign_in(){
        $rs = array('code' => 0, 'msg' => '签到成功', 'info' => array());
        $uid = $this->uid;
        $token = $this->token;
        $checkToken = checkToken($uid, $token);
        $language_id=$this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->sign_in($uid);


        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        return  $rs;
    }

    /**
     * 签到记录
     * @desc 签到  如果 没有签到 或者断签就返回空数据
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return array info.times 签到次数
     * @return array info.is_sign_type   1  今日已签到 2  昨日 签到
     */
    public function signLog(){
        $rs = array('code' => 0, 'msg' => '签到记录', 'info' => array());
        $uid = $this->uid;
        $token = $this->token;
        $checkToken = checkToken($uid, $token);
        $language_id=$this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->signLog($uid);
        $rs['info'] = $info;
        return $rs;
    }
    /**
     * 签到配置
     * @desc 签到配置
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return string info.type  1  奖励装盘 2 奖励砖石 提示信息
     */

    public function signSet(){
        $rs = array('code' => 0, 'msg' => '签到记录', 'info' => array());
        $domain = new Domain_User();
        $info = $domain->signSet();
        $rs['info'] = $info;
        return $rs;
    }

    /**
     * 用户访问
     * @desc 用户访问
     * @return int code  0 成功
     * @return string msg 提示信息
     * @return string info.type  1  奖励装盘 2 奖励砖石 提示信息
     */

    public function accessLog(){
        $rs = array('code' => 0, 'msg' => '用户访问', 'info' => array());
        $uid = $this->uid;
        $token = $this->token;
        $checkToken = checkToken($uid, $token);
        $language_id=$this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->accessLog($uid);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        return  $rs;
        return $rs;
    }

    public function checkUserLogin(){
        $rs = array('code' => 0, 'msg' => '', 'info' => array());
        $user_login = $this->user_login;
        $domain = new Domain_User();
        $info = $domain->checkUserLogin($user_login);
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        return  $rs;
    }
    /**
     * 确认收货，七日后转入 店铺账号
     *  @return array code  0 成功
     * @return array info
     * @return string msg 提示信息

     */
    public  function goodsToshopowner(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $domain = new Domain_User();
        $info = $domain->goodsToshopowner();
        $rs['code'] = $info['code'] ? $info['code'] : $rs['code'];
        $rs['msg'] = $info['msg'] ? $info['msg'] : $rs['msg'];
        $rs['info'] = $info['info'] ? $info['info'] : $rs['info'];
        return $rs;
    }

    public function getInviteCode(){
        ini_set("display_errors","On");
        $domain = new Domain_User();
        $code = $domain->getInviteCode($this->uid);
        if(empty($code)){
            $code = $domain->getInviteCode($this->uid);
        }
        return $code;

    }

    public function summaryAgent(){
        ini_set('display_errors','on');
        $domain = new Domain_User();
        $domain->summaryAgent(); 
    }


    public function summaryDownload(){
        ini_set('display_errors','on');
        $domain = new Domain_User();
        $domain->summaryDownload(); 
    }

    public function downloadFromH5(){
        $rs = array('code' => 0, 'msg' => '操作成功', 'info' => array());
        $invite_code = $this->agent_code;
        $domain = new Domain_User();
        $rs['info'][] = $domain->addDownload($invite_code);
        return $rs;
    }

    // 下载送一天会员
    public function AddCoin(){
        $rs = array('code' => 0, 'msg' => '下载app送一天会员', 'info' => array());
        $uid = $this->uid;
        $token = $this->token;
        $checkToken = checkToken($uid, $token);
        $language_id=$this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->AddCoin($uid);
        $rs['info'] = $info;
        return $rs;
    }

    public function getInvitedList(){
        $rs = array('code' => 0, 'msg' => '获取邀请注册列表', 'info' => array());
        $uid = $this->uid;
        $token = $this->token;
        $checkToken = checkToken($uid, $token);
        $language_id=$this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        $p=$this->page;
        $domain = new Domain_User();
        $info = $domain->getInvitedList($uid,$p);
        $rs['info'] = $info;
        return $rs;
    }

    public function getAttentList(){
        $rs = array('code' => 0, 'msg' => '关注的用户', 'info' => array());
        $uid = $this->uid;
        $page = $this->page;
        $token = $this->token;
        $checkToken = checkToken($uid, $token);
        $language_id=$this->language_id;
        if ($checkToken == 700) {
            $rs['code'] = $checkToken;
            $language = DI()->config->get('language.tokenerror');
            $rs['msg'] = $language[$language_id];//账号不能为空;
            return $rs;
        }
        $domain = new Domain_User();
        $info = $domain->getAttentList($uid,$page);
        $rs['info']= $info;
        return $rs;
    }

}
