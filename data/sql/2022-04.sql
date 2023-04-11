ALTER TABLE cmf_users_share
    ADD COLUMN consumption_name varchar(128) NULL COMMENT '消费方' AFTER beneficiary;


ALTER TABLE cs88_dev.cmf_users_share_log
    ADD COLUMN consumption_name varchar(32) CHARACTER SET ucs2 COLLATE ucs2_general_ci NOT NULL DEFAULT '' COMMENT '消费方' AFTER beneficiary;


ALTER TABLE `cmf_channel_account`
    ADD COLUMN `explain` text NULL COMMENT '充值说明' AFTER `reception_name`;

ALTER TABLE `cmf_offlinepay`
    ADD COLUMN `explain` text NULL COMMENT '充值说明' AFTER bank_user_name;

ALTER TABLE `cmf_platform_config`
    ADD COLUMN `explain` text NULL COMMENT '充值说明' AFTER `auth_length_pull`;

//独立数据库不需要
ALTER TABLE`cmf_users`
    ADD COLUMN `roomtype_name` varchar(50) NULL COMMENT '房间类型id' AFTER `userlevel`;

ALTER TABLE `cmf_video`
    ADD COLUMN `classify` varchar(128) NULL COMMENT '分类' AFTER `playTimeInt`;

ALTER TABLE `cmf_task`
    ADD COLUMN `only_one` tinyint(2) NOT NULL DEFAULT 0 COMMENT '任务策略' AFTER `mtime`;
ALTER TABLE `cmf_user_task`
    ADD COLUMN `only_one` tinyint(2) NOT NULL DEFAULT 0 COMMENT '任务策略' AFTER `submit_time`;
ALTER TABLE `cmf_user_task`
    ADD COLUMN `today_ischeck` tinyint(2) NOT NULL DEFAULT 0 COMMENT '今日是否已经审核' AFTER `only_one`;

ALTER TABLE `cmf_video_classify`
    ADD COLUMN `is_list` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否列表' AFTER `operatename`;


ALTER TABLE `cmf_video`
    ADD COLUMN `price` int(10) NULL COMMENT '价格' AFTER `classify`,
ADD COLUMN `try_watch_time` int(5) NULL COMMENT '试看时间' AFTER `price`;
ALTER TABLE `cmf_video`
    ADD COLUMN `buy_numbers` int(10) NOT NULL DEFAULT 0 COMMENT '购买次数' AFTER `try_watch_time`;

ALTER TABLE `cmf_video_long`
    ADD COLUMN `price` int(10) NULL COMMENT '价格' AFTER `playTimeInt`,
ADD COLUMN `try_watch_time` int(5) NULL COMMENT '试看时间' AFTER `price`,
ADD COLUMN `buy_numbers` int(10) NOT NULL DEFAULT 0 COMMENT '购买次数' AFTER `try_watch_time`;


ALTER TABLE `cmf_users_live`
    ADD COLUMN `stop_time` int(11) NOT NULL DEFAULT 0 COMMENT '累计暂停时长' AFTER `recover_time`;
ALTER TABLE `cmf_users_liverecord`
    ADD COLUMN `stop_time` int(11) NOT NULL DEFAULT 0 COMMENT '暂停时长' AFTER `game_user_id`;
ALTER TABLE `cmf_liveing_log`
    ADD COLUMN `stop_time` int(11) NOT NULL DEFAULT 0 COMMENT '暂停时间' AFTER `status`;

ALTER TABLE `cmf_users_live`
    ADD COLUMN `label_name` varchar(128) NOT NULL DEFAULT '' COMMENT '彩种标签名称' AFTER `stop_time`;



ALTER TABLE `cs88_dev`.`cmf_guard`
    ADD COLUMN `guard_img` varchar(255) NOT NULL DEFAULT '' COMMENT '守护图标' AFTER `tenant_id`;


ALTER TABLE `cmf_live_class`
    ADD COLUMN `is_app` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否展示' AFTER `orderno`;

ALTER TABLE `cmf_guard`
    ADD COLUMN `guard_effect` varchar(255) NOT NULL DEFAULT '' COMMENT '特效svga' AFTER `guard_img`;
ALTER TABLE `cmf_guard_users`
    ADD COLUMN `guard_effect` varchar(255) NOT NULL DEFAULT '' COMMENT '守护特效' AFTER `tenant_id`;
ALTER TABLE `cmf_guard`
    ADD COLUMN `position_first` varchar(128) NULL AFTER `guard_effect`,
    ADD COLUMN `position_second` varchar(128) NULL AFTER `position_first`;

ALTER TABLE `cmf_guard`
    ADD COLUMN `is_gift` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否送礼物0 未开启  1开启' AFTER `position_second`,
ADD COLUMN `is_shutup` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否禁言0 未开启  1开启' AFTER `is_gift`,
ADD COLUMN `renewal_coin` int(11) NOT NULL DEFAULT 0 COMMENT '续费价格' AFTER `is_shutup`;


ALTER TABLE `cs88_dev`.`cmf_guard`
    ADD COLUMN `giftarr` varchar(125) NULL COMMENT '守护绑定的礼物' AFTER `renewal_coin`;


ALTER TABLE `cmf_users`
    ADD COLUMN `watchtime` int(10) NOT NULL DEFAULT 0 COMMENT '会员观看直播时长' AFTER `client`;
ALTER TABLE `cmf_charge_gift`
    ADD COLUMN `orderno` int(10) NOT NULL DEFAULT 0 COMMENT '排序' AFTER `is_open`;


ALTER TABLE `cmf_platform_config`
    ADD COLUMN `transfer_switch` tinyint(1) NOT NULL DEFAULT 0 COMMENT '转账开关' AFTER `explain`;


ALTER TABLE `cmf_platform_config`
    ADD COLUMN `yuebao_switch` tinyint(1) NOT NULL DEFAULT 0 COMMENT '米利宝开关' AFTER `transfer_switch`;

ALTER TABLE `cmf_platform_config`
    ADD COLUMN `yuebao_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '米粒宝文案' AFTER `yuebao_switch`;

ALTER TABLE `cmf_platform_config`
    ADD COLUMN `yuebao_rate` varchar(12) NULL COMMENT '米利宝利息' AFTER `yuebao_name`;
ALTER TABLE `cmf_platform_config`
    ADD COLUMN `yuebao_explain` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '米利宝规则说明' AFTER `yuebao_rate`;

ALTER TABLE `cmf_user_transfer_yuebao`
    ADD COLUMN `mark` varchar(255) NULL COMMENT '备注' AFTER `phonenumber`;

ALTER TABLE cmf_users
    ADD COLUMN yeb_balance  decimal(12,2) NOT NULL DEFAULT 0 COMMENT '余额宝余额' AFTER certification_name;
ALTER TABLE `cmf_users`
    ADD COLUMN `yeb_isopen` tinyint(1) NOT NULL DEFAULT 0 COMMENT '余额宝是否开启' AFTER `yeb_balance`;

ALTER TABLE `cmf_red_setting`
    ADD COLUMN `second_time` int(10) NULL COMMENT '抢红包分钟数' AFTER `game_tenant_id`,
ADD COLUMN `effect_time` int(10) NULL COMMENT '抢红包有效时间' AFTER `second_time`,
ADD COLUMN `win_time` int(10) NULL COMMENT '能抢到红包时间' AFTER `effect_time`;


ALTER TABLE `cmf_tenant`
    ADD COLUMN `balance_nft_url` varchar(255) NULL COMMENT 'nft接口地址' AFTER `live_jurisdiction`;

ALTER TABLE  `cmf_video`
    ADD COLUMN `shoptype` int(12) NOT NULL DEFAULT 0 COMMENT '绑定商城类型' AFTER `is_advertise`,
ADD COLUMN `shop_value` int(12) NOT NULL DEFAULT 0 COMMENT '绑定商城值' AFTER `shoptype`;


ALTER TABLE `cmf_users`
    ADD COLUMN `nftwithdrawable_coin` decimal(16, 2) NULL DEFAULT 0.00 COMMENT 'nft不可提现金币' AFTER `coin`;


ALTER TABLE `cmf_vip_grade`
    ADD COLUMN `nft_rate` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '中签率' AFTER `video_upload_reward_type`;


ALTER TABLE `cmf_tenant`
    ADD COLUMN `shop_url` varchar(255) NULL COMMENT '商城接口地址' AFTER `balance_nft_url`,
ADD COLUMN `daifu_url` varchar(255) NULL COMMENT '代付接口地址' AFTER `shop_url`;


ALTER TABLE `cmf_tenant`
    ADD COLUMN `lottery_id` int(12) NULL COMMENT '彩票租户id' AFTER `daifu_url`,
ADD COLUMN `lottery_url` varchar(255) NULL COMMENT '彩票租户地址' AFTER `lottery_id`;

ALTER TABLE  `cmf_video`
    ADD COLUMN `shop_url` varchar(255) NULL COMMENT '商城网址' AFTER `shop_value`;


ALTER TABLE `cmf_platform_config`
    ADD COLUMN `shop_explain` text NULL COMMENT '商城说明配置' AFTER `transfer_auto`;
ALTER TABLE `cmf_platform_config`
    ADD COLUMN `shop_switch` tinyint(1) NOT NULL DEFAULT 0 COMMENT '前端是否展示绑定按钮' AFTER `shop_explain`;

ALTER TABLE cmf_users_coinrecord`
    ADD COLUMN `order_id` varchar(128) NULL COMMENT '商城订单号' AFTER `remark`;