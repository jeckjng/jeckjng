ALTER TABLE `cmf_users_agent` ADD `four` INT(11) NOT NULL DEFAULT '0' COMMENT '上上上上级id' AFTER `three_uid`, ADD `five` INT(11) NOT NULL DEFAULT '0' COMMENT '上上上上上级id' AFTER `four`;
ALTER TABLE `cmf_users_agent` CHANGE `four` `four_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '上上上上级id';
ALTER TABLE `cmf_users_agent` CHANGE `five` `five_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '上上上上上级id';

ALTER TABLE `cmf_users_agent_code` ADD UNIQUE(`code`);

ALTER TABLE `cmf_feedback` ADD `contact` VARCHAR(128) NOT NULL COMMENT '联系方式' AFTER `tenant_id`;
ALTER TABLE `cmf_feedback` CHANGE `contact` `contact` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系方式';

CREATE TABLE `cmf_users_chatroom` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `uid` int(11) NOT NULL COMMENT '用户id(群主)',
                                      `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT '聊天室标题',
                                      `status` tinyint(1) NOT NULL COMMENT '状态，0禁言，1正常',
                                      `addtime` int(11) NOT NULL COMMENT '添加时间',
                                      PRIMARY KEY (`id`),
                                      KEY `uid` (`uid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `cmf_users_chatroom_friends` (
                                              `id` int(11) NOT NULL AUTO_INCREMENT,
                                              `room_id` bigint(17) NOT NULL COMMENT '交友房间号',
                                              `sub_uid` int(11) NOT NULL COMMENT '下线id',
                                              `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '成员类型，1 普通成员，2 管理员',
                                              `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态，0禁言，1正常',
                                              `addtime` int(11) NOT NULL COMMENT '添加时间	',
                                              PRIMARY KEY (`id`),
                                              KEY `room_id` (`room_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cmf_platform_config`
    ADD COLUMN `chatroomserver`  varchar(255) NULL DEFAULT '聊天室socket地址' AFTER `chatserver`;
ALTER TABLE `cmf_platform_config` CHANGE `chatroomserver` `chatroomserver` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '聊天室socket地址';

CREATE TABLE `cmf_users_chatroom_record` (
                                             `id` int(11) NOT NULL AUTO_INCREMENT,
                                             `room_id` int(11) NOT NULL,
                                             `uid` int(11) NOT NULL,
                                             `user_nicename` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
                                             `avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                                             `act_type` tinyint(3) NOT NULL,
                                             `ct` text COLLATE utf8_unicode_ci,
                                             `addtime` int(11) NOT NULL,
                                             PRIMARY KEY (`id`),
                                             KEY `room_id` (`room_id`) USING BTREE,
                                             KEY `uid` (`uid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cmf_users_chatroom_friends` CHANGE `type` `type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '成员类型，1 普通成员，2 管理员, 3群主';

ALTER TABLE `cmf_users_chatroom` ADD `avatar` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房间头像' AFTER `title`;

CREATE TABLE `cmf_integral_config` (
                                       `id` int(11) NOT NULL AUTO_INCREMENT,
                                       `level_1` int(11) NOT NULL DEFAULT '0' COMMENT '1级',
                                       `level_2` int(11) NOT NULL DEFAULT '0' COMMENT '2级',
                                       `level_3` int(11) NOT NULL DEFAULT '0' COMMENT '3级',
                                       `level_4` int(11) NOT NULL DEFAULT '0' COMMENT '4级',
                                       `level_5` int(11) NOT NULL DEFAULT '0' COMMENT '5级',
                                       `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '类型：1注册',
                                       PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `cmf_integral_log` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `uid` int(11) NOT NULL COMMENT '用户id',
                                    `start_integral` bigint(20) DEFAULT '0' COMMENT '发生前积分',
                                    `change_integral` bigint(20) DEFAULT '0' COMMENT '发生积分',
                                    `end_integral` bigint(20) DEFAULT '0' COMMENT '操作后积分',
                                    `change_type` tinyint(3) NOT NULL DEFAULT '63' COMMENT '变更类型: 1首页,2游戏,63新用户注册',
                                    `act_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '操作类型: 1注册，2兑换',
                                    `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态：0进行中，1已完成',
                                    `remark` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '备注',
                                    `ctime` int(11) DEFAULT '0' COMMENT '操作时间',
                                    `act_uid` int(11) DEFAULT '0' COMMENT '操作人',
                                    PRIMARY KEY (`id`),
                                    KEY `uid` (`uid`),
                                    KEY `act_uid` (`act_uid`) USING BTREE,
                                    KEY `ctime` (`ctime`) USING BTREE,
                                    KEY `act_type` (`act_type`),
                                    KEY `change_type` (`change_type`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=96 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cmf_users` CHANGE `integral` `integral` BIGINT(20) NULL DEFAULT '0' COMMENT '积分';

CREATE TABLE `cmf_users_action` (
                                    `id` int(10) NOT NULL AUTO_INCREMENT,
                                    `uid` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
                                    `vip_id` int(10) NOT NULL DEFAULT '1' COMMENT '会员等级',
                                    `change_type` tinyint(3) NOT NULL COMMENT '用户行为',
                                    `act_type` tinyint(3) NOT NULL COMMENT '操作类型: 1注册，2兑换',
                                    `action_num` bigint(20) NOT NULL DEFAULT '0' COMMENT '操作数量/观看时长',
                                    `tenant_id` int(11) NOT NULL COMMENT '租户',
                                    `ctime` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
                                    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

ALTER TABLE `cmf_users_action`
DROP COLUMN `vip_id`;

ALTER TABLE `cmf_integral_log` CHANGE `act_type` `act_type` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '操作类型: 1注册，2兑换，3用户行为';
ALTER TABLE `cmf_integral_config` CHANGE `type` `type` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '类型：1注册，2用户行为';
ALTER TABLE `cmf_integral_config` ADD `val` VARCHAR(10000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '值' AFTER `tenant_id`;
ALTER TABLE `cmf_integral_config` ADD `vip_id` INT(11) NULL DEFAULT NULL COMMENT 'vip等级 id' AFTER `tenant_id`;
ALTER TABLE `cmf_users_action`
DROP COLUMN `act_type`,
CHANGE COLUMN `change_type` `action_type`  tinyint(3) NOT NULL COMMENT '用户行为' AFTER `uid`;

ALTER TABLE `cmf_users`
    ADD COLUMN `addup_integral`  bigint(20) NULL DEFAULT 0 COMMENT '累计积分' AFTER `integral`;

ALTER TABLE `cmf_users_action`
    ADD COLUMN `start_integral`  bigint(20) NOT NULL DEFAULT 0 COMMENT '发生前积分' AFTER `tenant_id`,
ADD COLUMN `change_integral`  bigint(20) NOT NULL DEFAULT 0 COMMENT '发生积分' AFTER `start_integral`,
ADD COLUMN `end_integral`  bigint(20) NOT NULL DEFAULT 0 COMMENT '操作后积分' AFTER `change_integral`;

ALTER TABLE `cmf_users_action` CHANGE `action_num` `action_time` INT(11) NOT NULL DEFAULT '0' COMMENT '操作数量/观看时长';
ALTER TABLE `cmf_users_action`
    ADD COLUMN `status`  tinyint(2) NOT NULL DEFAULT 1 COMMENT '状态：0未发放，1已发放' AFTER `end_integral`;

CREATE TABLE `cmf_task` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `name` varchar(160) CHARACTER SET utf8 NOT NULL COMMENT '任务名称',
                            `description` varchar(160) CHARACTER SET utf8 NOT NULL COMMENT '任务描述',
                            `start_time` int(11) NOT NULL COMMENT '生效时间',
                            `end_time` int(11) NOT NULL COMMENT '失效时间',
                            `client` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '客户端',
                            `type` tinyint(2) NOT NULL COMMENT '任务类型：1初级任务，2中级任务，3高级任务',
                            `sort` tinyint(4) NOT NULL DEFAULT '1' COMMENT '排序',
                            `img` varchar(256) CHARACTER SET utf8 NOT NULL COMMENT '图片地址',
                            `reward1` int(11) NOT NULL DEFAULT '0' COMMENT '完成奖励1',
                            `reward2_upgrade_vip` int(11) NOT NULL DEFAULT '0' COMMENT '完成奖励2：是否升级VIP等级: 0否，1是',
                            `is_manual_check` tinyint(2) NOT NULL DEFAULT '0' COMMENT '人工审核：0否，1是',
                            `is_upleveltask` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否需要上一级任务完成：0否，1是',
                            `task_details_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '任务详情类型：0富文本，1网页',
                            `task_details` text CHARACTER SET utf8 COMMENT '任务详情说明',
                            `status` tinyint(2) NOT NULL COMMENT '任务状态：0失效，1生效',
                            `act_uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人id',
                            `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
                            PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `cmf_task_basicsetting` (
                                         `id` int(11) NOT NULL AUTO_INCREMENT,
                                         `primary_num` tinyint(4) NOT NULL DEFAULT '0' COMMENT '任务名称',
                                         `intermediate_num` tinyint(4) NOT NULL DEFAULT '0' COMMENT '中级任务',
                                         `advanced_num` tinyint(4) NOT NULL DEFAULT '0' COMMENT '高级任务',
                                         `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '类型：1任务基础配置',
                                         `act_uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人id',
                                         `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
                                         PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `cmf_task_loginreward` (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `reg_withdrawable_coin` tinyint(4) NOT NULL DEFAULT '0' COMMENT '注册奖励-可提现金币',
                                        `reg_withdrawable_coding` tinyint(4) NOT NULL DEFAULT '0' COMMENT '注册奖励-可提现金币打码量',
                                        `reg_nowithdrawable_coin` tinyint(4) NOT NULL DEFAULT '0' COMMENT '注册奖励-不可提现金币',
                                        `firstlog_withdrawable_coin` tinyint(4) DEFAULT '0' COMMENT '首次登录-可提现金币',
                                        `firstlog_withdrawable_coding` tinyint(4) DEFAULT '0' COMMENT '首次登录-可提现金币打码量',
                                        `firstlog_nowithdrawable_coin` tinyint(4) DEFAULT '0' COMMENT '首次登录-不可提现金币',
                                        `otherlog_withdrawable_coin` tinyint(4) DEFAULT '0' COMMENT '非首次登录-可提现金币',
                                        `otherlog_withdrawable_coding` tinyint(4) DEFAULT '0' COMMENT '非首次登录-可提现金币打码量',
                                        `otherlog_nowithdrawable_coin` tinyint(4) DEFAULT '0' COMMENT '非首次登录-不可提现金币',
                                        `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '类型：1任务基础配置',
                                        `act_uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人id',
                                        `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
                                        PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `cmf_task_rewardlog` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `uid` int(11) NOT NULL COMMENT '用户id',
                                      `vip_id` int(11) NOT NULL COMMENT '会员等级',
                                      `type` tinyint(2) NOT NULL COMMENT '任务类型：1初级任务，2中级任务，3高级任务	',
                                      `task_id` int(11) NOT NULL COMMENT '任务id',
                                      `reward_type` tinyint(2) NOT NULL COMMENT '奖励类型：1奖励1类型奖励，2奖励2类型奖励',
                                      `reward_start_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '奖励前金额',
                                      `reward_result` varchar(160) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '奖励结果',
                                      `reward_end_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '奖励后金额',
                                      `reward_end_vip` int(11) NOT NULL DEFAULT '0' COMMENT '奖励后VIP等级',
                                      `giveout_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发放类型： 0系统自动发放，1人工审核',
                                      `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态：发放0未发放，1已发放',
                                      `tenant_id` int(11) NOT NULL COMMENT '租户id',
                                      `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
                                      PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE `cmf_user_task` (
                                 `id` int(11) NOT NULL AUTO_INCREMENT,
                                 `uid` int(11) NOT NULL COMMENT '用户id',
                                 `vip_id` int(11) NOT NULL COMMENT '会员等级',
                                 `task_id` int(11) NOT NULL COMMENT '任务id',
                                 `task_type` tinyint(2) NOT NULL COMMENT '任务类型：1初级任务，2中级任务，3高级任务',
                                 `status` tinyint(2) NOT NULL COMMENT '任务状态：1待审核，2审核通过，3审核拒绝',
                                 `remark` varchar(160) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
                                 `act_uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人id',
                                 `tenant_id` int(11) NOT NULL COMMENT '租户id',
                                 `ctime` int(11) NOT NULL COMMENT '提交时间',
                                 `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
                                 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cmf_users` ADD `nowithdrawable_coin` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '不可提现金币' AFTER `coin`, ADD `withdrawable_coding` INT(20) NOT NULL DEFAULT '0' COMMENT '可提现金币打码量' AFTER `nowithdrawable_coin`;
ALTER TABLE `cmf_users` ADD `login_num` INT(11) NOT NULL DEFAULT '0' COMMENT '登录次数' AFTER `last_login_time`;

ALTER TABLE `cmf_task_rewardlog` ADD `start_time` INT(11) NOT NULL DEFAULT '0' COMMENT '生效时间' AFTER `reward_type`, ADD `end_time` INT(11) NOT NULL DEFAULT '0' COMMENT '失效时间' AFTER `start_time`;
ALTER TABLE `cmf_task` ADD `classification` INT(11) NOT NULL COMMENT '任务分类' AFTER `type`;
ALTER TABLE `cmf_task` ADD `price` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '任务价格' AFTER `description`;
ALTER TABLE `cmf_users_action` ADD `addup_integral` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '累计积分' AFTER `uid`;

CREATE TABLE `cmf_task_classification` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT,
                                           `type` tinyint(2) NOT NULL COMMENT '任务等级',
                                           `name` varchar(160) CHARACTER SET utf8 NOT NULL COMMENT '分类名称',
                                           `description` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '分类描述',
                                           `commission_rate` double(4,3) NOT NULL DEFAULT '0.000' COMMENT '佣金比例（不要设置百分比，直接写0.01）',
  `daily_task` int(11) NOT NULL COMMENT '每日任务数限制',
  `unlock_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '解锁金额（解锁条件1）',
  `direct_invitation` int(11) NOT NULL DEFAULT '0' COMMENT '直邀人数（解锁条件2，不需要可设置为0）',
  `max_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '订单最高金额',
  `min_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '订单最低金额',
  `space_time` int(11) NOT NULL DEFAULT '0' COMMENT '任务间隔（秒）',
  `shop_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '商城状态',
  `default_shop` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否默认商城',
  `experience_shop` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否体验商城',
  `logo` varchar(655) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '分类LOGO（不用修改）',
  `act_uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人id',
  `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cmf_task` CHANGE `client` `client` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '客户端：1PC，2H5，3Android，4iOS';

ALTER TABLE `cmf_user_task` CHANGE `status` `status` TINYINT(2) NOT NULL COMMENT '用户任务状态：0已取消，1进行中，2审核中，3审核通过（已完成），4审核拒绝';
ALTER TABLE `cmf_user_task` ADD `classification` INT(11) NOT NULL COMMENT '任务分类' AFTER `task_type`;

ALTER TABLE `cmf_integral_config`
    CHANGE COLUMN `vip_id` `vip`  int(11) NULL DEFAULT NULL COMMENT 'vip等级' AFTER `tenant_id`;

ALTER TABLE `cmf_users_action` ADD `vip` INT(11) NOT NULL DEFAULT '1' COMMENT 'vip等级' AFTER `uid`;

ALTER TABLE `cmf_task_classification` CHANGE `shop_status` `status` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '分类状态';
ALTER TABLE `cmf_task_classification` DROP `default_shop`;
ALTER TABLE `cmf_user_task` ADD `submit_time` INT(11) NOT NULL DEFAULT '0' COMMENT '任务提交时间' AFTER `mtime`;

ALTER TABLE `cmf_users_action` CHANGE `ctime` `ctime` INT(10) NOT NULL DEFAULT '0' COMMENT '用户行为时间';
ALTER TABLE `cmf_users_action` ADD `giveout_time` INT(11) NOT NULL DEFAULT '0' COMMENT '发放时间' AFTER `ctime`;


ALTER TABLE `cmf_users` ADD `watch_num` INT(11) NOT NULL DEFAULT '0' COMMENT '赠送观影次数' AFTER `addup_integral`, ADD `watch_time` INT(11) NOT NULL DEFAULT '0' COMMENT '赠送观影时长' AFTER `watch_num`;
ALTER TABLE `cmf_users` ADD `charge_num` INT(11) NOT NULL DEFAULT '0' COMMENT '充值次数' AFTER `watch_time`;
ALTER TABLE `cmf_users` ADD `first_charge_coin` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充金额' AFTER `charge_num`;
ALTER TABLE `cmf_users` CHANGE `charge_num` `recharge_num` INT(11) NOT NULL DEFAULT '0' COMMENT '充值次数';
ALTER TABLE `cmf_users` CHANGE `first_charge_coin` `firstrecharge_coin` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充金额';

CREATE TABLE `cmf_activity_reward_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) NOT NULL COMMENT '类型：1 首充活动，2: 分享活动',
  `watnum` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数',
  `wattime` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `reward` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额',
  `tenant_id` int(11) NOT NULL COMMENT '用户所在租户ID',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `cmf_activity_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fro_min` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充奖励1（最小值）',
  `fro_max` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充奖励1（最大值）',
  `fro_reward` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额（首充奖励1）',
  `fro_watnum` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（首充奖励1）',
  `fro_wattime` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（首充奖励1），单位：分钟',
  `frt_min` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充奖励2（最小金额）',
  `frt_max` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充奖励2（最大金额）',
  `frt_reward` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额（首充奖励2）',
  `frt_watnum` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（首充奖励2）',
  `frt_wattime` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（首充奖励2）',
  `recom_frmin` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励首充最低充值金额',
  `recom_rr_1` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充1人]-奖励金额',
  `recom_rr_2` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充2人]-奖励金额',
  `recom_rr_3` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充3人]-奖励金额',
  `recom_rr_4` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充4人]-奖励金额',
  `recom_rr_5` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充5人]-奖励金额',
  `recom_rr_6` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充6人]-奖励金额',
  `recom_rr_0` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充超过6人]-奖励金额	',
  `recom_watnum_1` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（推荐奖励[推荐首充1人]）',
  `recom_wattime_1` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（推荐奖励[推荐首充1人]）',
  `recom_watnum_2` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（推荐奖励[推荐首充2人]）',
  `recom_wattime_2` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（推荐奖励[推荐首充2人]）',
  `recom_watnum_3` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（推荐奖励[推荐首充3人]）',
  `recom_wattime_3` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（推荐奖励[推荐首充3人]）',
  `recom_watnum_4` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（推荐奖励[推荐首充4人]）',
  `recom_wattime_4` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（推荐奖励[推荐首充4人]）',
  `recom_watnum_5` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（推荐奖励[推荐首充5人]）',
  `recom_wattime_5` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（推荐奖励[推荐首充5人]）',
  `recom_watnum_6` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（推荐奖励[推荐首充6人]）',
  `recom_wattime_6` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（推荐奖励[推荐首充6人]）',
  `recom_watnum_0` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（推荐奖励[推荐首充超过6人]）',
  `recom_wattime_0` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（推荐奖励[推荐首充超过6人]）',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '类型：1 首充活动，2 分享活动',
  `act_uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人',
  `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `cmf_activity_config` ADD `sort_num` INT(11) NOT NULL DEFAULT '0' COMMENT '序号' AFTER `id`, ADD `min` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '最小值' AFTER `sort_num`, ADD `max` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '最大值' AFTER `min`, ADD `reward` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额' AFTER `max`, ADD `watnum` INT(11) NOT NULL DEFAULT '0' COMMENT '赠送观影次数' AFTER `reward`, ADD `wattime` INT(11) NOT NULL DEFAULT '0' COMMENT '赠送观影时长' AFTER `watnum`;

ALTER TABLE `cmf_activity_config` DROP `fro_min`;
ALTER TABLE `cmf_activity_config` DROP `fro_max`;
ALTER TABLE `cmf_activity_config` DROP `fro_reward`;
ALTER TABLE `cmf_activity_config` DROP `fro_watnum`;
ALTER TABLE `cmf_activity_config` DROP `fro_wattime`;
ALTER TABLE `cmf_activity_config` DROP `frt_min`;
ALTER TABLE `cmf_activity_config` DROP `frt_max`;
ALTER TABLE `cmf_activity_config` DROP `frt_reward`;
ALTER TABLE `cmf_activity_config` DROP `frt_watnum`;
ALTER TABLE `cmf_activity_config` DROP `frt_wattime`;


ALTER TABLE `cmf_activity_config` ADD `per_num` INT(11) NOT NULL DEFAULT '0' COMMENT '人数' AFTER `id`;
ALTER TABLE `cmf_activity_config` ADD `is_over` TINYINT(2) NOT NULL COMMENT '是否是超过人数' AFTER `recom_frmin`;

ALTER TABLE `cmf_activity_config` DROP `recom_rr_1`;
ALTER TABLE `cmf_activity_config` DROP `recom_rr_2`;
ALTER TABLE `cmf_activity_config` DROP `recom_rr_3`;
ALTER TABLE `cmf_activity_config` DROP `recom_rr_4`;
ALTER TABLE `cmf_activity_config` DROP `recom_rr_5`;
ALTER TABLE `cmf_activity_config` DROP `recom_rr_6`;
ALTER TABLE `cmf_activity_config` DROP `recom_rr_0`;

ALTER TABLE `cmf_activity_config` DROP `recom_watnum_1`;
ALTER TABLE `cmf_activity_config` DROP `recom_watnum_2`;
ALTER TABLE `cmf_activity_config` DROP `recom_watnum_3`;
ALTER TABLE `cmf_activity_config` DROP `recom_watnum_4`;
ALTER TABLE `cmf_activity_config` DROP `recom_watnum_5`;
ALTER TABLE `cmf_activity_config` DROP `recom_watnum_6`;
ALTER TABLE `cmf_activity_config` DROP `recom_watnum_0`;

ALTER TABLE `cmf_activity_config` DROP `recom_wattime_1`;
ALTER TABLE `cmf_activity_config` DROP `recom_wattime_2`;
ALTER TABLE `cmf_activity_config` DROP `recom_wattime_3`;
ALTER TABLE `cmf_activity_config` DROP `recom_wattime_4`;
ALTER TABLE `cmf_activity_config` DROP `recom_wattime_5`;
ALTER TABLE `cmf_activity_config` DROP `recom_wattime_6`;
ALTER TABLE `cmf_activity_config` DROP `recom_wattime_0`;


ALTER TABLE `cmf_activity_config` CHANGE `is_over` `is_over` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '是否是超过人数';


ALTER TABLE `cmf_users_chatroom` ADD `tenant_id` INT(11) NULL DEFAULT NULL COMMENT '租户ID' AFTER `addtime`, ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '修改时间' AFTER `tenant_id`;
ALTER TABLE `cmf_users_chatroom` ADD `act_uid` INT(11) NULL DEFAULT NULL COMMENT '操作人id' AFTER `tenant_id`;
ALTER TABLE `cmf_users_chatroom_friends` ADD `tenant_id` INT(11) NULL DEFAULT NULL COMMENT '租户ID' AFTER `addtime`, ADD `act_uid` INT(11) NULL DEFAULT NULL COMMENT '操作人id' AFTER `tenant_id`, ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '修改时间' AFTER `act_uid`;
ALTER TABLE `cmf_users_chatroom_record` ADD `tenant_id` INT(11) NULL DEFAULT NULL COMMENT '租户ID' AFTER `ct`;


CREATE TABLE `cmf_users_chatroom_conf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enter_msg` varchar(10000) CHARACTER SET utf8 NOT NULL DEFAULT '注意：聊天室内禁止聊党政相关的话题！' COMMENT '进群提示消息',
  `num` int(11) NOT NULL DEFAULT '500' COMMENT '成员数量限制',
  `tenant_id` int(11) DEFAULT NULL COMMENT '租户ID',
  `act_uid` int(11) DEFAULT NULL COMMENT '操作人id',
  `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `cmf_users_chatroom_friends` CHANGE `status` `status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '状态，0禁言，1正常，2踢出';
ALTER TABLE `cmf_users_chatroom_friends` CHANGE `type` `type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '会员类型：1 普通会员，2 管理员, 3房主';
ALTER TABLE `cmf_users_chatroom` CHANGE `uid` `uid` INT(11) NOT NULL COMMENT '房主id';
ALTER TABLE `cmf_users_chatroom` CHANGE `title` `title` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '聊天室名称';
ALTER TABLE `cmf_users_chatroom` CHANGE `avatar` `avatar` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '聊天室图标';
ALTER TABLE `cmf_users_chatroom_friends` CHANGE `room_id` `room_id` BIGINT(17) NULL COMMENT '聊天室ID';


ALTER TABLE `cmf_users_chatroom` CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '聊天室标题';

ALTER TABLE `cmf_users_chatroom_record` ADD `execute_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '被执行用户ID（踢人、单个禁言、解除单个禁言 必须）' AFTER `ct`;


ALTER TABLE `cmf_users_chatroom` CHANGE `status` `status` TINYINT(1) NOT NULL DEFAULT '1';


ALTER TABLE `cmf_users_chatroom_record` DROP `avatar`;
ALTER TABLE `cmf_users_chatroom_record` DROP `user_nicename`;
ALTER TABLE `cmf_users_chatroom_record` ADD `code` INT(11) NOT NULL DEFAULT '0' COMMENT '状态码' AFTER `ct`;


ALTER TABLE `cmf_users_chatroom_conf` ADD `recordnum` INT(11) NOT NULL DEFAULT '10' COMMENT '聊天记录保存条数' AFTER `num`, ADD `exptime` INT(11) NOT NULL DEFAULT '30' COMMENT '聊天记录保存时间' AFTER `recordnum`;


ALTER TABLE `cmf_users_chatroom` ADD `chattime` INT(11) NOT NULL DEFAULT '0' COMMENT '聊天时间' AFTER `mtime`, ADD INDEX `chattime` (`chattime`);


ALTER TABLE `cmf_platform_config` ADD `cash_hour_star` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '提现时间（开始）' AFTER `cash_end`, ADD `cash_hour_end` TINYINT(2) NOT NULL DEFAULT '24' COMMENT '提现时间（结束）' AFTER `cash_hour_star`, ADD `charge_hour_star` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '充值时间（开始）' AFTER `cash_hour_end`, ADD `charge_hour_end` TINYINT(2) NOT NULL DEFAULT '24' COMMENT '充值时间（结束）' AFTER `charge_hour_star`;
ALTER TABLE `cmf_platform_config` ADD `cash_check` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '提现订单检测：0否，1是' AFTER `charge_hour_end`, ADD `cash_nosucc` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '订单数（提现订单检测）' AFTER `cash_check`;
ALTER TABLE `cmf_task_classification` ADD `limit_max_balance` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '上限金额(账户资金余额)' AFTER `min_amount`;


CREATE TABLE `cmf_task_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '第几单',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '类型：1打针计划，2派单功能',
  `percent` decimal(3,2) NOT NULL DEFAULT '0.00' COMMENT '本金百分比',
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '任务价格',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态：0关闭，1开启',
  `act_uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人id',
  `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cmf_users_charge_admin` ADD `money` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '充值金额' AFTER `coin`, ADD `rnb_money` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '人民币金额' AFTER `money`, ADD `currency_code` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '币种简称' AFTER `rnb_money`;
ALTER TABLE `cmf_users_charge_admin` CHANGE `coin` `coin` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '钻石数';
ALTER TABLE `cmf_users_charge_admin` ADD INDEX(`touid`);
ALTER TABLE `cmf_users_charge_admin` ADD `orderno` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号' AFTER `id`;
ALTER TABLE `cmf_users_charge_admin` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id' AFTER `addtime`;
ALTER TABLE `cmf_users_charge_admin` DROP `admin`;
ALTER TABLE `cmf_users_charge_admin` ADD `rate` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT '汇率' AFTER `money`;
ALTER TABLE `cmf_rate` ADD UNIQUE(`code`);
ALTER TABLE `cmf_users_charge_admin` ADD INDEX(`currency_code`);
ALTER TABLE `cmf_users_charge` ADD `currency_code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '币种简称' AFTER `money`;


ALTER TABLE `cmf_users_agent` ADD INDEX(`one_uid`);
ALTER TABLE `cmf_users_agent` CHANGE `tenant_id` `tenant_id` BIGINT(20) NULL DEFAULT '0';
ALTER TABLE `cmf_users_agent` ADD INDEX(`tenant_id`);


ALTER TABLE `cmf_bet_config` MODIFY COLUMN `playname`  varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name`;


ALTER TABLE `cmf_users` CHANGE `user_type` `user_type` SMALLINT(1) NOT NULL DEFAULT '1' COMMENT '用户类型，1:admin,2:会员,4:游客';


ALTER TABLE `cmf_users` CHANGE `user_type` `user_type` SMALLINT(1) NOT NULL DEFAULT '1' COMMENT '用户类型，1:admin,2:会员,3:虚拟用户,4:游客';

ALTER TABLE `cmf_platform_config` ADD `speak_level` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '发言等级' AFTER `level_limit`;


ALTER TABLE `cmf_users_share` ADD `type` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播 ' AFTER `anchors_name`;
ALTER TABLE `cmf_users_coinrecord` ADD `t_share_id` INT(11) NULL DEFAULT '0' COMMENT '主播平台分润id(主播所属租户)' AFTER `playname`, ADD `r_share_id` INT(11) NULL DEFAULT '0' COMMENT '消费平台分润id(消费者所属租户)' AFTER `t_share_id`, ADD `a_share_id` INT(11) NOT NULL DEFAULT '0' COMMENT '主播分润id' AFTER `r_share_id`;
ALTER TABLE `cmf_users_share` ADD `r_tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户id' AFTER `type`;
ALTER TABLE `cmf_users_coinrecord` ADD `t_profit_ratio` DECIMAL(6,2) NOT NULL DEFAULT '0.00' COMMENT '主播平台分成比例' AFTER `a_share_id`, ADD `r_profit_ratio` DECIMAL(6,2) NOT NULL DEFAULT '0.00' COMMENT '消费平台分成比例' AFTER `t_profit_ratio`, ADD `a_profit_ratio` DECIMAL(6,2) NOT NULL DEFAULT '0.00' COMMENT '主播分成比例' AFTER `r_profit_ratio`;


ALTER TABLE `cmf_users_share` CHANGE `status` `status` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '状态：0.处理中，1.转账成功';
ALTER TABLE `cmf_users_share` CHANGE `money` `money` DECIMAL(20,2) NULL DEFAULT '0.00' COMMENT '金额';
ALTER TABLE `cmf_users_share` ADD `transfer_money` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '转账金额' AFTER `money`;
ALTER TABLE `cmf_users_share` ADD INDEX(`status`);
CREATE TABLE `cmf_users_share_log` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `share_id` INT(11) NOT NULL COMMENT '分润id' , `transfer_money` DECIMAL(20,2) NULL DEFAULT '0.00' COMMENT '转账金额' , `tenant_id` INT(11) NOT NULL COMMENT '租户id' , PRIMARY KEY (`id`)) ENGINE = MyISAM;
ALTER TABLE `cmf_users_share_log` ADD `admin_id` INT(11) NOT NULL COMMENT '操作人id' AFTER `tenant_id`;
ALTER TABLE `cmf_users_share_log` ADD `ctime` INT(11) NOT NULL COMMENT '创建时间' AFTER `admin_id`;
ALTER TABLE `cmf_users_share_log` ADD `action` TINYINT(1) NOT NULL COMMENT '行为: 1.彩票分润结算' AFTER `transfer_money`;
ALTER TABLE `cmf_users_share_log` ADD `type` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '类型：1:主播平台(主播所属租户),2:消费平台(消费者所属租户),3:主播 ' AFTER `transfer_money`;
ALTER TABLE `cmf_users_share_log` ADD `beneficiary` VARCHAR(32) CHARACTER SET ucs2 COLLATE ucs2_general_ci NOT NULL DEFAULT '' COMMENT '收款方' AFTER `transfer_money`;
ALTER TABLE `cmf_users_share` ADD `uid` INT(11) NOT NULL DEFAULT '0' COMMENT '主播用户ID' AFTER `r_tenant_id`;
ALTER TABLE `cmf_users_share_log` ADD `beneficiary_id` INT(11) NOT NULL DEFAULT '0' COMMENT '收款方id(直播用户id,或租户id)' AFTER `beneficiary`;

ALTER TABLE `cmf_users_coinrecord` ADD INDEX(`t_share_id`);
ALTER TABLE `cmf_users_coinrecord` ADD INDEX(`r_share_id`);
ALTER TABLE `cmf_users_coinrecord` ADD INDEX(`a_share_id`);

ALTER TABLE `cmf_users` ADD `beauty` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '美颜效果设置' AFTER `fans`;
ALTER TABLE `cmf_users` ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '修改时间' AFTER `beauty`;

ALTER TABLE `cmf_users_coinrecord` ADD `cd_ratio` VARCHAR(10) NOT NULL DEFAULT '1:1' COMMENT '金币砖石比例' AFTER `a_profit_ratio`;

ALTER TABLE `cmf_users_car` ADD INDEX(`uid`);
ALTER TABLE `cmf_users_car` ADD INDEX(`carid`);

CREATE TABLE `cmf_family_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `real_name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
  `mobile` varchar(50) NOT NULL DEFAULT '' COMMENT '电话',
  `cer_no` varchar(50) NOT NULL DEFAULT '' COMMENT '身份证号',
  `front_view` varchar(255) NOT NULL DEFAULT '' COMMENT '正面',
  `back_view` varchar(255) NOT NULL DEFAULT '' COMMENT '反面',
  `handset_view` varchar(255) NOT NULL DEFAULT '' COMMENT '手持',
  `reason` text COMMENT '审核说明',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `tenant_id` bigint(20) DEFAULT NULL,
  `wchat` varchar(50) NOT NULL DEFAULT '' COMMENT '微信号',
  `addtime` int(12) NOT NULL DEFAULT '0' COMMENT '提交时间',
  `uptime` int(12) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

ALTER TABLE `cmf_family_auth` ADD `familyid` INT(11) NOT NULL COMMENT '家族ID' AFTER `uid`;

ALTER TABLE `cmf_users` ADD `admin_type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '管理员类型：0.通用，1.家族族长' AFTER `user_type`;
ALTER TABLE `cmf_users` ADD `familyids` TEXT NULL DEFAULT NULL COMMENT '管理家族id' AFTER `beauty`;
ALTER TABLE `cmf_users` ADD `ctime` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间' AFTER `act_uid`;


ALTER TABLE cmf_users ADD COLUMN operate_name varchar(50) NULL COMMENT '操作人' AFTER familyids, ADD COLUMN remark varchar(128) NULL COMMENT '备注' AFTER operate_name;

ALTER TABLE `cmf_integral_log` CHANGE `start_integral` `start_integral` DECIMAL(20,2) NULL DEFAULT '0.00' COMMENT '发生前积分';
ALTER TABLE `cmf_integral_log` CHANGE `change_integral` `change_integral` DECIMAL(20,2) NULL DEFAULT '0.00' COMMENT '发生积分';
ALTER TABLE `cmf_integral_log` CHANGE `end_integral` `end_integral` DECIMAL(20,2) NULL DEFAULT '0.00' COMMENT '操作后积分';
ALTER TABLE `cmf_users` CHANGE `integral` `integral` DECIMAL(20,2) NULL DEFAULT '0.00' COMMENT '积分';
ALTER TABLE `cmf_users` CHANGE `addup_integral` `addup_integral` DECIMAL(20,2) NULL DEFAULT '0.00' COMMENT '累计积分';


ALTER TABLE `cmf_tenant_config` ADD `cust_service_addr` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '客服地址' AFTER `official_website_url`;
ALTER TABLE `cmf_tenant_config` ADD `notice_pernum` INT(11) NOT NULL DEFAULT '0' COMMENT '每多少次弹窗提示(针对游客)' AFTER `cust_service_addr`;

ALTER TABLE `cmf_users_coinrecord`
DROP COLUMN `t_share_id`,
DROP COLUMN `r_share_id`,
DROP COLUMN `a_share_id`,
DROP COLUMN `t_profit_ratio`,
DROP COLUMN `r_profit_ratio`,
DROP COLUMN `a_profit_ratio`;

ALTER TABLE `cmf_family_auth` ADD `anchor_photo` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '直播照片' AFTER `handset_view`;
ALTER TABLE `cmf_family_auth` ADD `anchor_video` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '主播视频' AFTER `anchor_photo`;
ALTER TABLE `cmf_family_auth` ADD `remark` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '备注' AFTER `wchat`;

ALTER TABLE `cmf_tenant_config` ADD `ad_link` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '广告链接' AFTER `notice_pernum`;

ALTER TABLE `cmf_task_loginreward` ADD `reg_withdrawable_coin2` INT(11) NOT NULL DEFAULT '0' COMMENT '二等奖' AFTER `otherlog_nowithdrawable_coin`, ADD `reg_withdrawable_coin3` INT(11) NOT NULL DEFAULT '0' COMMENT '三等奖' AFTER `reg_withdrawable_coin2`, ADD `reg_nowithdrawable_coin2` INT(11) NOT NULL DEFAULT '0' COMMENT '二等奖' AFTER `reg_withdrawable_coin3`, ADD `reg_nowithdrawable_coin3` INT(11) NOT NULL DEFAULT '0' COMMENT '三等奖' AFTER `reg_nowithdrawable_coin2`, ADD `firstlog_withdrawable_coin2` INT(11) NOT NULL DEFAULT '0' COMMENT '二等奖' AFTER `reg_nowithdrawable_coin3`, ADD `firstlog_withdrawable_coin3` INT(11) NOT NULL DEFAULT '0' COMMENT '三等奖' AFTER `firstlog_withdrawable_coin2`, ADD `firstlog_nowithdrawable_coin2` INT(11) NOT NULL DEFAULT '0' COMMENT '二等奖' AFTER `firstlog_withdrawable_coin3`, ADD `firstlog_nowithdrawable_coin3` INT(11) NOT NULL DEFAULT '0' COMMENT '三等奖' AFTER `firstlog_nowithdrawable_coin2`, ADD `otherlog_withdrawable_coin2` INT(11) NOT NULL DEFAULT '0' COMMENT '二等奖' AFTER `firstlog_nowithdrawable_coin3`, ADD `otherlog_withdrawable_coin3` INT(11) NOT NULL DEFAULT '0' COMMENT '三等奖' AFTER `otherlog_withdrawable_coin2`, ADD `otherlog_nowithdrawable_coin2` INT(11) NOT NULL DEFAULT '0' COMMENT '二等奖' AFTER `otherlog_withdrawable_coin3`, ADD `otherlog_nowithdrawable_coin3` INT(11) NOT NULL DEFAULT '0' COMMENT '三等奖' AFTER `otherlog_nowithdrawable_coin2`;

ALTER TABLE `cmf_user_task` ADD `client` TINYINT(2) NOT NULL COMMENT '客户端：1PC，2H5，3Android，4iOS' AFTER `classification`;

ALTER TABLE  cmf_users
ADD COLUMN isshutup tinyint(2) NULL DEFAULT 0 COMMENT '是否禁言' AFTER remark,
ADD COLUMN isforbidlive tinyint(2) NULL DEFAULT 0 COMMENT '是否禁播' AFTER isshutup,
ADD COLUMN userlevel int(12) NULL COMMENT '会员等级' AFTER isforbidlive;

ALTER TABLE  cmf_users_sharedetail
ADD COLUMN type int(12) NULL COMMENT '详情类型' AFTER share_id;

ALTER TABLE `cmf_users_coinrecord` ADD `familyhead_total` FLOAT(16,3) NULL DEFAULT NULL COMMENT '家族长分成' AFTER `cd_ratio`;


ALTER TABLE  cmf_users
    ADD COLUMN isshutup tinyint(2) NULL DEFAULT 0 COMMENT '是否禁言' AFTER remark,
ADD COLUMN isforbidlive tinyint(2) NULL DEFAULT 0 COMMENT '是否禁播' AFTER isshutup,
ADD COLUMN userlevel int(12) NULL COMMENT '会员等级' AFTER isforbidlive;


ALTER TABLE  cmf_users_sharedetail
    ADD COLUMN type int(12) NULL COMMENT '详情类型' AFTER share_id;

ALTER TABLE `cmf_users_agent_profit` CHANGE `tenant_id` `tenant_id` INT(11) NOT NULL;
ALTER TABLE `cmf_users_agent_profit` CHANGE `one_profit` `one_profit` DECIMAL(4,1) NOT NULL DEFAULT '0.0' COMMENT '上一级收益';
ALTER TABLE `cmf_users_agent_profit` CHANGE `two_profit` `two_profit` DECIMAL(4,1) NOT NULL DEFAULT '0.0' COMMENT '上二级收益';
ALTER TABLE `cmf_users_agent_profit` CHANGE `three_profit` `three_profit` DECIMAL(4,1) NOT NULL DEFAULT '0.0' COMMENT '上三级收益';
ALTER TABLE `cmf_users_agent_profit` CHANGE `uid` `uid` INT(11) NOT NULL COMMENT '用户ID';
ALTER TABLE `cmf_users_agent_profit` ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `tenant_id`;
ALTER TABLE `cmf_users_agent_profit` ADD `act_uid` INT(11) NOT NULL COMMENT '操作人id' AFTER `tenant_id`;
ALTER TABLE `cmf_users_agent_profit` ADD `four_profit` DECIMAL(4,1) NOT NULL DEFAULT '0.0' COMMENT '上四级收益' AFTER `three_profit`, ADD `five_profit` DECIMAL(4,1) NOT NULL DEFAULT '0.0' COMMENT '上五级收益' AFTER `four_profit`;
ALTER TABLE `cmf_users_agent_profit` DROP `uid`;

ALTER TABLE `cmf_users` CHANGE `beauty` `beauty` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '美颜效果设置';

ALTER TABLE `cmf_users_family` ADD `clearheat_time` INT(11) NOT NULL DEFAULT '0' COMMENT '清除热度时间' AFTER `tenant_id`;

ALTER TABLE `cmf_users` ADD INDEX(`user_type`);

ALTER TABLE `cmf_tenant_config` ADD `ad_time` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '广告时间，单位：秒' AFTER `ad_link`;

ALTER TABLE `cmf_family` ADD `game_user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '游戏系统用户id' AFTER `uid`;
ALTER TABLE `cmf_users_family` ADD `game_user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '游戏系统用户id' AFTER `uid`;
ALTER TABLE `cmf_family_auth` ADD `game_user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '游戏系统用户id' AFTER `uid`;
ALTER TABLE `cmf_commission_set` ADD `game_user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '游戏系统用户id' AFTER `uid`;

ALTER TABLE `cmf_ads` CHANGE `des` `des` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述';

ALTER TABLE `cmf_platform_config`
ADD `tickets_limit_min` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '门票房间限额(最小值)' AFTER `yp_apikey`,
ADD `tickets_limit_max` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '门票房间限额(最大值)' AFTER `tickets_limit_min`,
ADD `charoom_min_livetime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '收费房间最低直播时间' AFTER `tickets_limit_max`,
ADD `uncommonroom_mintime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '非普通房间试看最低时间' AFTER `charoom_min_livetime`;
ALTER TABLE `cmf_users_live` CHANGE `type` `type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '直播类型：0.普通房间，1.密码房间，2.门票房间，3.计时房间';
ALTER TABLE `cmf_ads` CHANGE `des` `des` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述';

ALTER TABLE `cmf_platform_config` CHANGE `uncommonroom_mintime` `trywatchtime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '非普通房间试看时间';

ALTER TABLE `cmf_platform_config` ADD `live_nums_min` INT(11) NOT NULL DEFAULT '0' COMMENT '直播初始人数(min)' AFTER `trywatchtime`, ADD `live_nums_max` INT(11) NOT NULL DEFAULT '0' COMMENT '直播初始人数(max)' AFTER `live_nums_min`;

CREATE TABLE `cmf_log_api` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `server` varchar(70) NOT NULL DEFAULT '',
  `url` text,
  `ct` text,
  `ip` varchar(64) NOT NULL DEFAULT '',
  `method` varchar(32) NOT NULL DEFAULT '',
  `ctime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB AUTO_INCREMENT=9134 DEFAULT CHARSET=utf8mb4;
ALTER TABLE `cmf_log_api` CHANGE `server` `service` VARCHAR(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '';
ALTER TABLE `cmf_log_api` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' AFTER `method`;
ALTER TABLE `cmf_log_api` ADD `remark` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '备注' AFTER `ct`;
ALTER TABLE `cmf_log_api` CHANGE `url` `uri` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `cmf_log_api` ADD `root_url` VARCHAR(300) NOT NULL DEFAULT '' COMMENT 'url根地址' AFTER `service`;
ALTER TABLE `cmf_log_api` ADD INDEX(`service`);

ALTER TABLE `cmf_users_live` ADD `top` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否置顶：0.否，1.是' AFTER `isrecommendroom`, ADD `ly_recommend` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否光年推荐：0.否，1.是' AFTER `top`;

ALTER TABLE `cmf_users_live` ADD `toptime` INT(11) NOT NULL DEFAULT '0' COMMENT '置顶时间' AFTER `top`;

ALTER TABLE `cmf_tenant_config` CHANGE `sprout_key` `sprout_key` VARCHAR(5000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '萌颜授权码';

ALTER TABLE `cmf_users_coinrecord` CHANGE `tenant_id` `tenant_id` BIGINT(20) NULL DEFAULT '0' COMMENT '租户id';

ALTER TABLE `cmf_users_chatroom_friends` CHANGE `sub_uid` `sub_uid` INT(11) NOT NULL COMMENT '会员id';

CREATE TABLE `cmf_complex_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timecharge_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '计时房间费用(直播间数据)',
  `timecharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '计时房间人数(直播间数据)',
  `roomcharge_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '门票房间费用(直播间数据)',
  `roomcharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '门票房间人数(直播间数据)',
  `sendbarrage_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '弹幕发言费用(直播间数据)',
  `sendbarrage_num` int(11) NOT NULL DEFAULT '0' COMMENT '弹幕发言人数(直播间数据)',
  `sendgift_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '礼物费用(直播间数据)',
  `sendgift_num` int(11) NOT NULL DEFAULT '0' COMMENT '礼物人数(直播间数据)',
  `buycar_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '坐骑购买(直播间数据)',
  `buycar_num` int(11) NOT NULL DEFAULT '0' COMMENT '坐骑购买人数(直播间数据)',
  `pa_roomcharge_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '房间费用分成(主播分成)',
  `pa_roomcharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '房间费用人数(主播分成)',
  `pa_sendbarrage_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '弹幕发言分成(主播分成)',
  `pa_sendbarrage_num` int(11) NOT NULL DEFAULT '0' COMMENT '弹幕发言人数(主播分成)',
  `pa_sendgift_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '礼物分成(主播分成)',
  `pa_sendgift_num` int(11) NOT NULL DEFAULT '0' COMMENT '礼物人数(主播分成)',
  `pa_bet_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '游戏分成(主播分成)',
  `pa_bet_num` int(11) NOT NULL DEFAULT '0' COMMENT '游戏人数(主播分成)',
  `pf_roomcharge_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '房间费用分成(家族长分成)',
  `pf_roomcharge_num` int(11) NOT NULL DEFAULT '0' COMMENT '房间费用人数(家族长分成)',
  `pf_sendbarrage_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '弹幕发言分成(家族长分成)',
  `pf_sendbarrage_num` int(11) NOT NULL DEFAULT '0' COMMENT '弹幕发言人数(家族长分成)',
  `pf_sendgift_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '礼物分成(家族长分成)',
  `pf_sendgift_num` int(11) NOT NULL DEFAULT '0' COMMENT '礼物人数(家族长分成)',
  `pf_bet_am` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '游戏分成(家族长分成)',
  `pf_bet_num` int(11) NOT NULL DEFAULT '0' COMMENT '游戏人数(家族长分成)',
  `collet_day` varchar(32) NOT NULL DEFAULT '' COMMENT '统计时间（天，格式化）',
  `collet_day_time` int(11) NOT NULL DEFAULT '0' COMMENT '统计时间（天，当天初始秒数）',
  `collet_week` varchar(32) NOT NULL DEFAULT '' COMMENT '统计时间（周，格式化）',
  `collet_week_time` int(11) NOT NULL DEFAULT '0' COMMENT '统计时间（周，当周初始秒数）	',
  `collet_month` varchar(32) NOT NULL DEFAULT '' COMMENT '统计时间（月，格式化）',
  `collet_month_time` int(11) NOT NULL DEFAULT '0' COMMENT '统计时间（月，当月初始秒数）	',
  `tenant_id` int(11) NOT NULL DEFAULT '0' COMMENT '租户编号',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `collet_day_time` (`collet_day_time`),
  KEY `collet_week_time` (`collet_week_time`),
  KEY `collet_month_time` (`collet_month_time`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cmf_users` CHANGE `beauty` `beauty` VARCHAR(5000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '美颜效果设置';

ALTER TABLE `cmf_platform_config` ADD `iospush_resolution` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'IOS推流分辨率' AFTER `live_nums_max`, ADD `iospush_codere` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'IOS推流码率' AFTER `iospush_resolution`, ADD `adpush_resolution` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '安卓推流分辨率' AFTER `iospush_codere`, ADD `adpush_codere` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '安卓推流码率' AFTER `adpush_resolution`;

ALTER TABLE `cmf_users_live` CHANGE `islive` `islive` INT(1) NOT NULL DEFAULT '0' COMMENT '直播状态: 0.直播结束，1.直播中，2.暂停中';

ALTER TABLE `cmf_tenant_config` ADD `ad_link_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '广告链接类型：1.图片，2.视频' AFTER `ad_link`;

CREATE TABLE `cmf_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zh` varchar(500) NOT NULL DEFAULT '' COMMENT '中文',
  `en` varchar(500) DEFAULT '' COMMENT '英文',
  `vn` varchar(500) NOT NULL DEFAULT '' COMMENT '越南',
  `th` varchar(500) NOT NULL DEFAULT '' COMMENT '泰文',
  `my` varchar(500) NOT NULL DEFAULT '' COMMENT '马来西亚',
  `ind` varchar(500) NOT NULL DEFAULT '' COMMENT '印度尼西亚',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '类型：1.进入直播间公告',
  `tenant_id` int(11) NOT NULL COMMENT '租户编号',
  `act_uid` int(11) NOT NULL COMMENT '操作人id',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cmf_platform_config` CHANGE `iospush_codere` `iospush_codere` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'IOS推流码率(视频码率)';
ALTER TABLE `cmf_platform_config` CHANGE `adpush_codere` `adpush_codere` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '安卓推流码率(视频码率)';

ALTER TABLE `cmf_platform_config`
ADD `iosprev_coll_frame` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'IOS预览采集帧率' AFTER `adpush_codere`,
ADD `ios_coll_frame` VARCHAR(32) NULL DEFAULT '' COMMENT 'IOS采集帧率' AFTER `iosprev_coll_frame`,
ADD `ios_coll_resolution` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'IOS采集分辨率' AFTER `ios_coll_frame`,
ADD `iospush_coll_resolution` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'IOS推流采集帧率' AFTER `ios_coll_resolution`,
ADD `iosliveprev_resolution` VARCHAR(100) NULL DEFAULT '' COMMENT 'IOS直播预览分辨率' AFTER `iospush_coll_resolution`,
ADD `adprev_coll_frame` VARCHAR(32) NULL DEFAULT '' COMMENT '安卓预览采集帧率' AFTER `iosliveprev_resolution`,
ADD `ad_coll_frame` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '安卓采集帧率' AFTER `adprev_coll_frame`,
ADD `ad_coll_resolution` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '安卓采集分辨率' AFTER `ad_coll_frame`,
ADD `adpush_coll_resolution` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '安卓推流采集帧率' AFTER `ad_coll_resolution`,
ADD `adliveprev_resolution` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '安卓直播预览分辨率' AFTER `adpush_coll_resolution`;

ALTER TABLE `cmf_platform_config`
ADD `qdy_push` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '青点云推流地址' AFTER `adliveprev_resolution`,
ADD `qdy_pull` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '青点云播流地址' AFTER `qdy_push`,
ADD `qdy_apn` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '青点云AppName' AFTER `qdy_pull`;

ALTER TABLE `cmf_language` CHANGE `zh` `zh` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '中文';
ALTER TABLE `cmf_language` CHANGE `en` `en` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '英文';
ALTER TABLE `cmf_language` CHANGE `vn` `vn` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '越南';
ALTER TABLE `cmf_language` CHANGE `th` `th` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '泰文';
ALTER TABLE `cmf_language` CHANGE `my` `my` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '马来西亚';
ALTER TABLE `cmf_language` CHANGE `ind` `ind` VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '印度尼西亚';

ALTER TABLE `cmf_users_family` ADD `user_nicename` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '昵称' AFTER `user_login`;

ALTER TABLE `cmf_platform_config`
ADD `live_fans_min` INT(11) NOT NULL DEFAULT '0' COMMENT '直播初始粉丝人数(最小)' AFTER `qdy_apn`,
ADD `live_fans_max` INT(11) NOT NULL DEFAULT '0' COMMENT '直播初始粉丝人数(最大)' AFTER `live_fans_min`,
ADD `live_votestotal_min` INT(11) NOT NULL DEFAULT '0' COMMENT '直播初始收入打赏(最小)' AFTER `live_fans_max`,
ADD `live_votestotal_max` INT(11) NOT NULL DEFAULT '0' COMMENT '直播初始收入打赏(最大)' AFTER `live_votestotal_min`;

ALTER TABLE `cmf_experlevel` ADD `experience` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '经验值' AFTER `levelname`;
ALTER TABLE `cmf_experlevel_anchor` ADD `experience` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '经验值' AFTER `levelname`;

ALTER TABLE `cmf_platform_config` ADD `push_encode_profile` TINYINT(1) NOT NULL DEFAULT '3' COMMENT '安卓功耗' AFTER `adliveprev_resolution`;
ALTER TABLE cmf_users_chatroom ADD roomtype tinyint(6) NOT NULL DEFAULT '0' COMMENT '聊天室类型 0:普通,1:私密';
ALTER TABLE `cmf_users_chatroom_friends` ADD `roomtype` tinyint(6) NOT NULL DEFAULT '0' COMMENT '聊天室类型 0:普通,1:私密';
CREATE TABLE `cmf_users_customer` (
                                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                      `uid` bigint(20) NOT NULL DEFAULT '0' COMMENT '客服UID',
                                      `puid` bigint(20) NOT NULL DEFAULT '0' COMMENT '上级代理UID',
                                      `addtime` int(11) NOT NULL COMMENT '添加时间',
                                      `adminname` varchar(60) CHARACTER SET utf8 NOT NULL DEFAULT 'null' COMMENT '操作员',
                                      `pusername` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '代理线会员名',
                                      `username` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '用户昵称',
                                      `title` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '封面标题',
                                      `avatar` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '封面图标',
                                      PRIMARY KEY (`id`),
                                      KEY `uid` (`uid`) USING BTREE,
                                      KEY `puid` (`puid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `cmf_users` ADD `isjurisdiction` tinyint(3) NOT NULL DEFAULT '0' COMMENT '聊天室权限 0:关闭,1:开启';

ALTER TABLE `cmf_users_liverecord` ADD `game_user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '游戏系统用户id' AFTER `tenant_id`;
ALTER TABLE `cmf_users_live` ADD `game_user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '游戏系统用户id' AFTER `tenant_id`;

ALTER TABLE `cmf_slide` CHANGE `slide_cid` `cat_name` VARCHAR(32) NOT NULL COMMENT '分类';
ALTER TABLE `cmf_slide` ADD `ctime` INT(11) NOT NULL DEFAULT '0' AFTER `tenant_id`, ADD `mtime` INT(11) NOT NULL DEFAULT '0' AFTER `ctime`;
ALTER TABLE `cmf_slide` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id' AFTER `tenant_id`;
ALTER TABLE `cmf_users_live` ADD `game_recommend` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '游戏推荐' AFTER `ly_recommend`;
ALTER TABLE `cmf_users_live` CHANGE `game_recommend` `game_recommend` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '游戏推荐：0.否，1.是';

ALTER TABLE `cmf_tenant_config` DROP `push_url`;
ALTER TABLE `cmf_tenant_config` DROP `pull_url`;

ALTER TABLE `cmf_users_jurisdiction` ADD `watchnum_ad` INT(11) NOT NULL DEFAULT '0' COMMENT '每观看多少部弹广告' AFTER `vip_grade_id`;

ALTER TABLE `cmf_tenant_config` ADD `voice_notice` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '语音播报开关：0.否，1是';

ALTER TABLE `cmf_platform_config` ADD `tx_rtmps_appid` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '腾讯云appid(rtmps)' AFTER `tx_pull`,
ADD `tx_rtmps_bizid` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '腾讯云bizid(rtmps)' AFTER `tx_rtmps_appid`,
ADD `tx_rtmps_push_key` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '腾讯云推流防盗链Key(rtmps)' AFTER `tx_rtmps_bizid`,
ADD `tx_rtmps_push` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '腾讯云推流域名(rtmps)' AFTER `tx_rtmps_push_key`,
ADD `tx_rtmps_pull` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '腾讯云播流域名(rtmps)' AFTER `tx_rtmps_push`;

ALTER TABLE `cmf_platform_config` ADD `tx_licenceurl` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '腾讯云licenceUrl' AFTER `tx_pull`,
ADD `tx_licencekey` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '腾讯云licenseKey' AFTER `tx_licenceurl`;
ALTER TABLE `cmf_platform_config` ADD `tx_rtmps_licenceurl` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '腾讯云licenceUrl(rtmps)' AFTER `tx_rtmps_pull`,
ADD `tx_rtmps_licencekey` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '腾讯云licenseKey(rtmps)' AFTER `tx_rtmps_licenceurl`;

ALTER TABLE `cmf_platform_config` DROP `tx_licenceurl`;
ALTER TABLE `cmf_platform_config` DROP `tx_licencekey`;

ALTER TABLE `cmf_platform_config` CHANGE `tx_rtmps_licenceurl` `tx_licenceurl` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '腾讯云licenceUrl(rtmps)';
ALTER TABLE `cmf_platform_config` CHANGE `tx_rtmps_licencekey` `tx_licencekey` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '腾讯云licenseKey(rtmps)';

ALTER TABLE `cmf_platform_config` DROP `qdy_apn`;

ALTER TABLE `cmf_users` ADD `isglr` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否领取首次登录奖励：0.否，1.是';

ALTER TABLE `cmf_users_live` ADD `push` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '推流地址' AFTER `pull`;

CREATE TABLE `cmf_livepushpull` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL COMMENT '推拉流名称',
  `code` tinyint(4) NOT NULL COMMENT '推拉流标志',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型：1.默认，2.yellow,3.green',
  `push` varchar(255) NOT NULL DEFAULT '' COMMENT '推流域名',
  `pull` varchar(255) NOT NULL DEFAULT '' COMMENT '播流域名',
  `appname` varchar(64) NOT NULL DEFAULT '' COMMENT '直播空间名称',
  `appid` varchar(255) NOT NULL DEFAULT '' COMMENT '直播appid',
  `bizid` varchar(255) NOT NULL DEFAULT '' COMMENT '直播bizid',
  `push_key` varchar(255) NOT NULL DEFAULT '' COMMENT '推流鉴权KEY/appkey',
  `push_length` int(11) NOT NULL DEFAULT '0' COMMENT '推流鉴权有效时长',
  `pull_key` varchar(255) NOT NULL DEFAULT '' COMMENT '播流鉴权KEY',
  `pull_length` int(11) NOT NULL DEFAULT '0' COMMENT '播流鉴权有效时长',
  `accesskey` varchar(255) NOT NULL DEFAULT '' COMMENT 'AccessKey',
  `secretkey` varchar(255) NOT NULL DEFAULT '' COMMENT 'SecretKey',
  `push_url` varchar(255) NOT NULL DEFAULT '' COMMENT '推流地址',
  `pull_url` varchar(255) NOT NULL DEFAULT '' COMMENT '播流地址',
  `flv_pull_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'flv_pull_url',
  `hls_pull` varchar(255) NOT NULL DEFAULT '' COMMENT 'hls_pull',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态: 0.关闭，1.启用',
  `act_uid` int(11) NOT NULL COMMENT '操作人id',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `use_time` int(11) NOT NULL DEFAULT '0' COMMENT '使用时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cmf_livepushpull` CHANGE `name` `name` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称';
ALTER TABLE `cmf_livepushpull` CHANGE `code` `code` TINYINT(4) NOT NULL COMMENT '推拉流服务商：1,2,3,4,5,6,7,8,9';
ALTER TABLE `cmf_livepushpull` CHANGE `type` `type` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '直播内容类型：1.默认，2.yellow,3.green';
ALTER TABLE `cmf_livepushpull` CHANGE `name` `name` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '线路名称';
ALTER TABLE `cmf_livepushpull` CHANGE `type` `ct_type` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '线路类型：1.默认，2.yellow,3.green';
ALTER TABLE `cmf_family_auth` ADD `ct_type` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '线路类型：1.默认，2.yellow,3.green' AFTER `familyid`;
ALTER TABLE `cmf_users_auth` ADD `ct_type` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '线路类型：1.默认，2.yellow,3.green';

ALTER TABLE `cmf_users_live` ADD `pushpull_id` INT(11) NOT NULL DEFAULT '0' COMMENT '直播推拉流id' AFTER `thumb`;

ALTER TABLE `cmf_livepushpull` ADD `certificate` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'App证书' AFTER `hls_pull`;

DELETE FROM `cmf_virtualname` WHERE `cmf_virtualname`.`id` = 2150;
DELETE FROM `cmf_virtualname` WHERE `cmf_virtualname`.`id` = 2198;







ALTER TABLE cmf_users_video_buy ADD `ex_user_price`  float DEFAULT '0' COMMENT '作者收益';
ALTER TABLE cmf_users_video_buy ADD `ex_users` varchar(100) DEFAULT NULL COMMENT '作者信息';
ALTER TABLE cmf_users_video_buy ADD  `one_price` float DEFAULT '0' COMMENT '1级收益';
ALTER TABLE cmf_users_video_buy ADD  `one_user` varchar(100) DEFAULT NULL;
ALTER TABLE cmf_users_video_buy ADD  `two_price` float DEFAULT '0';
ALTER TABLE cmf_users_video_buy ADD   `two_user` varchar(100) DEFAULT NULL;
ALTER TABLE cmf_users_video_buy ADD   `three_price` float DEFAULT '0' COMMENT '3级收益';
ALTER TABLE cmf_users_video_buy ADD `tree_user` varchar(100) DEFAULT NULL;

CREATE TABLE `cmf_users_video_rate` (
                                        `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                        `rate` float(5,1) NOT NULL DEFAULT '0.0' COMMENT '作者本人收益',
  `one_uid_rate` float(5,1) NOT NULL DEFAULT '0.0' COMMENT '一级代理收益率',
  `two_uid_rate` float(5,1) NOT NULL DEFAULT '0.0' COMMENT '2级代理收益',
  `three_uid_rate` float(5,1) NOT NULL DEFAULT '0.0' COMMENT '3及代理收益',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

ALTER TABLE `cmf_livepushpull` ADD `auth_status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '是否鉴权' AFTER `certificate`;

ALTER TABLE `cmf_family` ADD `id_type` TINYINT(1) NOT NULL DEFAULT '2' COMMENT '类型：1 直播会员ID， 2 彩票会员ID' AFTER `operate_time`;

ALTER TABLE `cmf_experlevel` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户编号' AFTER `thumb_mark`;
ALTER TABLE `cmf_experlevel_anchor` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户编号' AFTER `thumb_mark`;
ALTER TABLE `cmf_platform_config` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户编号' AFTER `live_votestotal_max`;
ALTER TABLE `cmf_platform_config` ADD UNIQUE(`tenant_id`);


ALTER TABLE `cmf_experlevel` CHANGE `id` `id` INT(11) NOT NULL auto_increment FIRST;
ALTER TABLE `cmf_experlevel_anchor` CHANGE `id` `id` INT(11) NOT NULL auto_increment FIRST;

ALTER TABLE `cmf_platform_config` CHANGE `money_rate` `money_rate` DECIMAL(16,2) NULL DEFAULT '0.00' COMMENT '钻石与钱的比例(单位:钻石/元)';
ALTER TABLE `cmf_platform_config` CHANGE `anchor_tenant_profit_ratio` `anchor_tenant_profit_ratio` DECIMAL(16,2) NOT NULL DEFAULT '0.00' COMMENT '主播所属租户分润比例';
ALTER TABLE `cmf_platform_config` CHANGE `auth_length_push` `auth_length_push` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '阿里云直播推流鉴权有效时长';
ALTER TABLE `cmf_platform_config` CHANGE `anchor_platform_ratio` `anchor_platform_ratio` DECIMAL(16,2) NOT NULL DEFAULT '0.00' COMMENT '同平台主播分润比例';
ALTER TABLE `cmf_platform_config` CHANGE `anchor_profit_ratio` `anchor_profit_ratio` DECIMAL(16,2) NOT NULL DEFAULT '0.00' COMMENT '主播分润比例';
ALTER TABLE `cmf_platform_config` CHANGE `anchor_platform_profit_ratio` `anchor_platform_profit_ratio` DECIMAL(16,2) NOT NULL DEFAULT '0.00' COMMENT '同平台主播所属租户分润比例';
ALTER TABLE `cmf_platform_config` CHANGE `anchor_platform_ratio` `anchor_platform_ratio` DECIMAL(16,2) NOT NULL DEFAULT '0.00' COMMENT '同平台主播分润比例';
ALTER TABLE `cmf_platform_config` CHANGE `anchor_platform_profit_ratio` `anchor_platform_profit_ratio` DECIMAL(16,2) NOT NULL DEFAULT '0.00' COMMENT '同平台主播所属租户分润比例';
ALTER TABLE `cmf_platform_config` CHANGE `user_platform_profit_ratio` `user_platform_profit_ratio` DECIMAL(16,2) NULL DEFAULT '0.00' COMMENT '同平台消费者所属租户分润比例';

ALTER TABLE `cmf_users_live` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id'
ALTER TABLE `cmf_users` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id' AFTER `beauty`;

CREATE TABLE `cmf_noble` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL COMMENT '等级',
  `name` varchar(32) NOT NULL COMMENT '名称',
  `medal` varchar(255) NOT NULL DEFAULT '' COMMENT '贵族勋章',
  `knighthoodcard` varchar(255) NOT NULL DEFAULT '' COMMENT '爵位牌',
  `special_effect` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开通特效: 0.否，1.是',
  `golden_light` tinyint(1) NOT NULL DEFAULT '0' COMMENT '进房金光: 0.否，1.是',
  `exclu_custsevice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '专属客服: 0.否，1.是',
  `avatar_frame` varchar(255) NOT NULL DEFAULT '' COMMENT '头像框',
  `upgrade_speed` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '升级加速',
  `broadcast` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开通广播: 0.否，1.是',
  `pubchat_bgskin` varchar(255) NOT NULL DEFAULT '' COMMENT '公聊背景皮肤',
  `enter_stealth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '进场隐身: 0.否，1.是',
  `exclu_card` varchar(255) NOT NULL DEFAULT '' COMMENT '专属座驾',
  `exclu_card_nobleicon` varchar(255) NOT NULL DEFAULT '' COMMENT '贵族内图标',
  `exclu_card_bagicon` varchar(255) NOT NULL DEFAULT '' COMMENT '背包座驾图标',
  `exclu_card_swf` varchar(255) NOT NULL DEFAULT '' COMMENT 'SVG动画',
  `exclu_card_swftime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '动画时长',
  `exclu_card_words` varchar(255) NOT NULL DEFAULT '' COMMENT '进场话术',
  `ranking_stealth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '榜单隐身: 0.否，1.是',
  `prevent_mute` tinyint(1) NOT NULL DEFAULT '0' COMMENT '防禁言: 0.否，1.是',
  `price` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '开通价格',
  `renewal_price` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '续费价格',
  `act_uid` int(11) NOT NULL COMMENT '操作人id',
  `tenant_id` int(11) NOT NULL COMMENT '租户编号',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cmf_users_noble` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '会员id',
  `user_login` varchar(64) NOT NULL COMMENT '用户名',
  `game_tanant_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩票会员ID',
  `noble_id` int(11) NOT NULL COMMENT '贵族等级id',
  `tenant_id` int(11) NOT NULL COMMENT '租户编号',
  `stime` int(11) NOT NULL COMMENT '开通时间',
  `etime` int(11) NOT NULL COMMENT '到期时间',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `game_tanant_id` (`game_tanant_id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_login` (`user_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cmf_users_noble_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '会员id',
  `user_login` varchar(64) NOT NULL COMMENT '用户名',
  `game_tanant_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩票会员ID',
  `noble_id` int(11) NOT NULL COMMENT '贵族等级id	',
  `type` tinyint(1) NOT NULL COMMENT '开通方式：1.正常开通，2.续费，3.升级',
  `price` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
  `tenant_id` int(11) NOT NULL COMMENT '租户编号',
  `ctime` int(11) NOT NULL COMMENT '付费时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `user_login` (`user_login`),
  KEY `game_tanant_id` (`game_tanant_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cmf_noble_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '贵族开关：0.关闭，1.开启',
  `details` text COMMENT '贵族说明',
  `tenant_id` int(11) NOT NULL COMMENT '租户编号',
  `act_uid` int(11) NOT NULL COMMENT '操作人id',
  `ctime` int(11) NOT NULL COMMENT '创建时间',
  `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cmf_users_noble` ADD `level` INT(11) NOT NULL COMMENT '等级' AFTER `noble_id`;
ALTER TABLE `cmf_users_noble` ADD UNIQUE(`uid`);
ALTER TABLE `cmf_users_noble_log` CHANGE `game_tanant_id` `game_user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '彩票会员ID';
ALTER TABLE `cmf_users_noble` CHANGE `game_tanant_id` `game_user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '彩票会员ID';
ALTER TABLE `cmf_users_noble_log` ADD `level` INT(11) NOT NULL COMMENT '贵族等级' AFTER `noble_id`;

ALTER TABLE `cmf_users_liverecord` ADD `isvideo` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否假视频：0.否，1是' AFTER `type_val`;
ALTER TABLE `cmf_users_live` CHANGE `isvideo` `isvideo` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否假视频：0.否，1是';

ALTER TABLE `cmf_car` ADD `type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '贵族专属: 0.否，1.是' AFTER `words`;

UPDATE `cmf_car` SET `tenant_id`='28' WHERE 1;

ALTER TABLE `cmf_car` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id' AFTER `tenant_id`;
ALTER TABLE `cmf_car` ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `act_uid`;
ALTER TABLE `cmf_noble` ADD `car_id` INT(11) NOT NULL DEFAULT '0' COMMENT '坐骑id' AFTER `exclu_car`;
ALTER TABLE `cmf_noble` CHANGE `exclu_card` `exclu_car` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '专属座驾';
ALTER TABLE `cmf_users_car` ADD `type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '贵族专属: 0.否，1.是';
ALTER TABLE `cmf_noble` CHANGE `exclu_car` `exclu_car` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '专属座驾: 0.否，1.是 ';

ALTER TABLE `cmf_users_live` ADD `pause_time` INT(11) NOT NULL DEFAULT '0' COMMENT '暂停时间' AFTER `act_uid`;
ALTER TABLE `cmf_users_live` ADD `recover_time` INT(11) NOT NULL DEFAULT '0' COMMENT '恢复时间' AFTER `pause_time`;

ALTER TABLE `cmf_noble` ADD `name_color` VARCHAR(16) NOT NULL DEFAULT '' COMMENT '名称颜色' AFTER `name`;
ALTER TABLE `cmf_noble` CHANGE `exclu_card_nobleicon` `exclu_car_nobleicon` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '贵族内图标';
ALTER TABLE `cmf_noble` ADD `special_effect_swf` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '特效图片' AFTER `special_effect`;
ALTER TABLE `cmf_noble` ADD `special_effect_swftime` INT(11) NOT NULL DEFAULT '0' COMMENT '特效动画时间' AFTER `special_effect_swf`;

ALTER TABLE `cmf_noble` DROP `exclu_card_bagicon`;
ALTER TABLE `cmf_noble` DROP `exclu_card_swf`;
ALTER TABLE `cmf_noble` DROP `exclu_card_swftime`;
ALTER TABLE `cmf_noble` DROP `exclu_card_words`;

ALTER TABLE `cmf_livepushpull` ADD `bypass_ratio` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '分流比例，单位：%' AFTER `status`;
ALTER TABLE `cmf_livepushpull` CHANGE `bypass_ratio` `bypass_ratio` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分流比例，单位：%';

ALTER TABLE `cmf_platform_config` ADD `yd_templateid` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '易盾模板ID' AFTER `yd_businessid`;
ALTER TABLE `cmf_platform_config` ADD `yd_type` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '易盾业务类型：1.国内验证码短信，2.国内通知类短信，3.国内营销类短信，4.国际验证码短信，5.国际通知类短信' AFTER `yd_secretkey`;

ALTER TABLE `cmf_users_super` CHANGE `tenant_id` `tenant_id` BIGINT(20) NULL DEFAULT '0';

ALTER TABLE `cmf_bet_config` ADD `loss_rate` VARCHAR(255) NOT NULL DEFAULT '1' COMMENT '赔率，多个赔率用,隔开' AFTER `playname`;
ALTER TABLE `cmf_bet_config` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id' AFTER `tenant_name`;
ALTER TABLE `cmf_bet_config` ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `add_time`;
ALTER TABLE `cmf_bet_config` CHANGE `tenant_id` `tenant_id` INT(12) NOT NULL COMMENT '租户id';


ALTER TABLE `cmf_atmosphere_live` CHANGE `addtime` `addtime` INT(12) NULL DEFAULT '0' COMMENT '开启时间';
ALTER TABLE `cmf_atmosphere_live` CHANGE `starttime` `starttime` INT(12) NULL DEFAULT '0' COMMENT '开始时间';

ALTER TABLE `cmf_atmosphere_live` ADD `uid` INT(11) NOT NULL DEFAULT '0' COMMENT '主播id' AFTER `endtime`,
ADD `givegiftparallelnum` INT(11) NOT NULL DEFAULT '0' COMMENT '送礼并行数量' AFTER `uid`,
ADD `givegiftrate` INT(11) NOT NULL DEFAULT '0' COMMENT '送礼概率' AFTER `givegiftparallelnum`,
ADD `givegiftmininterval` INT(11) NOT NULL DEFAULT '0' COMMENT '送礼最少间隔' AFTER `givegiftrate`,
ADD `givegiftxaxinterval` INT(11) NOT NULL DEFAULT '0' COMMENT '送礼最大间隔' AFTER `givegiftmininterval`,
ADD `autotalkingparallelnum` INT(11) NOT NULL DEFAULT '0' COMMENT '自动发言并行数量' AFTER `givegiftxaxinterval`,
 ADD `autotalkingrate` INT(11) NOT NULL DEFAULT '0' COMMENT '自动发言概率' AFTER `autotalkingparallelnum`,
 ADD `autotalkingmininterval` INT(11) NOT NULL DEFAULT '0' COMMENT '自动发言最少间隔' AFTER `autotalkingrate`,
 ADD `autotalkingmaxinterval` INT(11) NOT NULL DEFAULT '0' COMMENT '自动发言最大间隔' AFTER `autotalkingmininterval`;


ALTER TABLE `cmf_users_live` CHANGE `pull` `pull` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '播流地址';
ALTER TABLE `cmf_users_live` CHANGE `push` `push` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '推流地址';
ALTER TABLE `cmf_users_live` CHANGE `flvpull` `flvpull` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'flv播流地址';

ALTER TABLE `cmf_platform_config` ADD `go_root_url` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'golang根地址' AFTER `tenant_id`;

ALTER TABLE `cmf_atmosphere_live` ADD `enterroomparallelnum` INT(11) NOT NULL DEFAULT '0' COMMENT '进入房间并行数量' AFTER `uid`,
ADD `enterroomrate` INT(11) NOT NULL DEFAULT '0' COMMENT '进入房间概率' AFTER `enterroomparallelnum`,
ADD `enterroommininterval` INT(11) NOT NULL DEFAULT '0' COMMENT '进入房间最少间隔' AFTER `enterroomrate`,
ADD `enterroommaxinterval` INT(11) NOT NULL DEFAULT '0' COMMENT '进入房间最大间隔' AFTER `enterroommininterval`;

ALTER TABLE `cmf_atmosphere_live` ADD `leaveroomparallelnum` INT(11) NOT NULL DEFAULT '0' COMMENT '退出房间并行数量' AFTER `autotalkingmaxinterval`,
ADD `leaveroomrate` INT(11) NOT NULL DEFAULT '0' COMMENT '退出房间概率' AFTER `leaveroomparallelnum`,
ADD `leaveroommininterval` INT(11) NOT NULL DEFAULT '0' COMMENT '退出房间最少间隔' AFTER `leaveroomrate`,
 ADD `leaveroommaxinterval` INT(11) NOT NULL DEFAULT '0' COMMENT '退出房间最大间隔 ' AFTER `leaveroommininterval`,
 ADD `timebetparallelnum` INT(11) NOT NULL DEFAULT '0' COMMENT '中奖跟投并行数量' AFTER `leaveroommaxinterval`,
 ADD `timebetrate` INT(11) NOT NULL DEFAULT '0' COMMENT '中奖跟投概率' AFTER `timebetparallelnum`,
  ADD `timebetmininterval` INT(11) NOT NULL DEFAULT '0' COMMENT '中奖跟投最少间隔' AFTER `timebetrate`,
  ADD `timebetmaxinterval` INT(11) NOT NULL DEFAULT '0' COMMENT '中奖跟投最大间隔 ' AFTER `timebetmininterval`,
  ADD `recomparallelnum` INT(11) NOT NULL DEFAULT '0' COMMENT '推荐直播间并行数量' AFTER `timebetmaxinterval`,
  ADD `recomrate` INT(11) NOT NULL DEFAULT '0' COMMENT '推荐直播间概率' AFTER `recomparallelnum`,
  ADD `recommininterval` INT(11) NOT NULL DEFAULT '0' COMMENT '推荐直播间最少间隔' AFTER `recomrate`,
  ADD `recommaxinterval` INT(11) NOT NULL DEFAULT '0' COMMENT '推荐直播间最大间隔 ' AFTER `recommininterval`;

ALTER TABLE `cmf_atmosphere_live` CHANGE `num` `num` INT(12) NOT NULL DEFAULT '0' COMMENT '设置用户人数';
ALTER TABLE `cmf_atmosphere_live` CHANGE `enterroom` `enterroom` INT(12) NOT NULL DEFAULT '0' COMMENT '设置进入房间时长';
ALTER TABLE `cmf_atmosphere_live` CHANGE `sendgift` `sendgift` INT(12) NOT NULL DEFAULT '0' COMMENT '设置送礼物时长';
ALTER TABLE `cmf_atmosphere_live` CHANGE `sendbarrage` `sendbarrage` INT(12) NOT NULL DEFAULT '0' COMMENT '设置发消息时长';
ALTER TABLE `cmf_atmosphere_live` CHANGE `logout` `logout` INT(12) NOT NULL DEFAULT '0' COMMENT '设置退出用户时长';
ALTER TABLE `cmf_atmosphere_live` CHANGE `timebet` `timebet` INT(12) NOT NULL DEFAULT '0' COMMENT '设置中奖跟投时长';
ALTER TABLE `cmf_atmosphere_live` CHANGE `recommend` `recommend` INT(12) NOT NULL DEFAULT '0' COMMENT '设置推荐直播间时长';

ALTER TABLE `cmf_atmosphere_live` CHANGE `givegiftxaxinterval` `givegiftmaxinterval` INT(11) NOT NULL DEFAULT '0' COMMENT '送礼最大间隔';

ALTER TABLE `live_dev`.`cmf_users_live` DROP PRIMARY KEY, ADD UNIQUE `uid` (`uid`) USING BTREE;
ALTER TABLE `cmf_users_live` ADD `id` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

ALTER TABLE `cmf_options` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id' AFTER `autoload`,
ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户编号' AFTER `act_uid`,
ADD `ctime` INT(11) NOT NULL DEFAULT '0' COMMENT '新增时间' AFTER `tenant_id`,
 ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '修改时间' AFTER `ctime`;

ALTER TABLE `cmf_users` CHANGE `consumption` `consumption` DECIMAL(20,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '消费总额';


ALTER TABLE `cmf_livepushpull` CHANGE `pull` `pull` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '播流域名';
ALTER TABLE `cmf_livepushpull` CHANGE `pull_url` `pull_url` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '播流地址';
ALTER TABLE `cmf_livepushpull` CHANGE `flv_pull_url` `flv_pull_url` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'flv_pull_url';
ALTER TABLE `cmf_livepushpull` ADD `usenum` INT(11) NOT NULL DEFAULT '0' COMMENT '历史累积使用次数' AFTER `use_time`;

ALTER TABLE `cmf_atmosphere_live` ADD `type` TINYINT(1) NOT NULL DEFAULT '2' COMMENT '类型：1.单个直播间，2.租户所有直播间' AFTER `id`;

ALTER TABLE `cmf_platform_config` ADD `livet_timeout` INT(11) NOT NULL DEFAULT '0' COMMENT '直播暂停超时时间' AFTER `trywatchtime`;

ALTER TABLE `cmf_tenant_config` CHANGE `sprout_key` `sprout_key` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '萌颜授权码';

ALTER TABLE `cmf_users_live` ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '修改时间' AFTER `act_uid`;

CREATE TABLE `cmf_noble_skin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `val` varchar(500) NOT NULL DEFAULT '''' COMMENT ''皮肤值'',
  `tenant_id` int(11) NOT NULL COMMENT ''租户id'',
  `act_uid` int(11) NOT NULL COMMENT ''操作人id'',
  `ctime` int(11) NOT NULL COMMENT ''创建时间'',
  `mtime` int(11) NOT NULL DEFAULT ''0'' COMMENT ''更新时间'',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;


ALTER TABLE `cmf_users` ADD `version` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'app版本' AFTER `isglr`;
ALTER TABLE `cmf_users` ADD `client` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '用户客户端' AFTER `version`;

ALTER TABLE `cmf_users` DROP INDEX `user_login`, ADD UNIQUE `user_login` (`user_login`, `tenant_id`, `user_type`) USING BTREE;
ALTER TABLE `cmf_users` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`) USING BTREE;
ALTER TABLE `cmf_users` DROP INDEX `user_type_2`;

ALTER TABLE `cmf_users_live` ADD `pushpull_type` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '直播线路类型：1.默认，2.黄播,3.绿播,4.赌播' AFTER `thumb`;

ALTER TABLE `cmf_users_car` CHANGE `tenant_id` `tenant_id` BIGINT(20) NOT NULL DEFAULT '0';

ALTER TABLE `cmf_users_live` CHANGE `tenant_id` `tenant_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '租户id';
ALTER TABLE `cmf_users_live` ADD INDEX `tenant_id` (`tenant_id`);

ALTER TABLE `cmf_users` CHANGE `tenant_id` `tenant_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '租户id';

ALTER TABLE `cmf_users_noble` DROP INDEX `uid_2`;
ALTER TABLE `cmf_users_noble` DROP INDEX `uid_3`;
ALTER TABLE `cmf_users_noble` DROP INDEX `uid`, ADD UNIQUE `uid` (`uid`) USING BTREE;

ALTER TABLE `cmf_noble` ADD `handsel` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '首开赠送' AFTER `renewal_price`,
ADD `renewal_handsel` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '续费赠送' AFTER `handsel`;

ALTER TABLE `cmf_platform_config` ADD `go_socket_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'golang的socket地址' AFTER `chatroomserver`;

ALTER TABLE `cmf_users_noble_log` ADD `handsel` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额' AFTER `price`;

ALTER TABLE `cmf_users_live` ADD UNIQUE `uid-unique` (`uid`);

ALTER TABLE `cmf_admin_log` ADD `type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '类型：1.默认，2.弹窗警告' AFTER `action`;
ALTER TABLE `cmf_admin_log` CHANGE `ip` `ip` VARCHAR(64) NOT NULL COMMENT 'IP地址';
ALTER TABLE `cmf_log_api` ADD INDEX `tenant_id` (`tenant_id`);
ALTER TABLE `cmf_admin_log` ADD INDEX `addtime` (`addtime`);
ALTER TABLE `cmf_admin_log` CHANGE `tenant_id` `tenant_id` BIGINT(20) NULL DEFAULT '0';
ALTER TABLE `cmf_admin_log` ADD INDEX `tenant_id` (`tenant_id`);

ALTER TABLE `cmf_gift` CHANGE `tenant_id` `tenant_id` BIGINT(20) NULL DEFAULT '0';
ALTER TABLE `cmf_gift` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id' AFTER `tenant_id`,
ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `act_uid`;
ALTER TABLE `cmf_gift` CHANGE `swftype` `swftype` INT(4) NULL DEFAULT '0' COMMENT 'gif动画类型';

ALTER TABLE `cmf_experlevel` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id' AFTER `tenant_id`,
ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `act_uid`;
ALTER TABLE `cmf_experlevel_anchor` ADD `act_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人id' AFTER `tenant_id`,
ADD `mtime` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `act_uid`;

ALTER TABLE `cmf_platform_config` ADD `go_caller_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'go服务caller地址' AFTER `go_socket_url`;
ALTER TABLE `cmf_platform_config` ADD `go_admin_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'go后台地址' AFTER `go_caller_url`;

ALTER TABLE `cmf_platform_config` ADD `go_app_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'go接口地址' AFTER `go_admin_url`;

CREATE TABLE `cmf_file_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL COMMENT '名称',
  `type` varchar(20) NOT NULL COMMENT '服务商：1:本机,2:七牛云,3:阿里云,4:腾讯云',
  `accesskey` varchar(255) NOT NULL DEFAULT '' COMMENT 'AccessKey',
  `secretkey` varchar(255) NOT NULL DEFAULT '' COMMENT 'SecretKey',
  `domain` varchar(255) NOT NULL DEFAULT '' COMMENT '空间域名(下载、播放、展示使用)',
  `bucket` varchar(255) NOT NULL DEFAULT '' COMMENT '空间名称',
  `uphost` varchar(255) NOT NULL DEFAULT '' COMMENT '区域上传域名(endpoint服务端)',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态: 0.关闭，1.启用',
  `operated_by` varchar(255) NOT NULL DEFAULT '' COMMENT '操作者',
  `tenant_id` int(11) NOT NULL DEFAULT '0' COMMENT '租户id',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `tenant_id` (`id`),
  KEY `type` (`id`),
  KEY `status` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cmf_users_chatroom` CHANGE `tenant_id` `tenant_id` INT(11) NULL DEFAULT '0' COMMENT '租户ID';
ALTER TABLE `cmf_users_chatroom` CHANGE `act_uid` `act_uid` INT(11) NULL DEFAULT '0' COMMENT '操作人id';
ALTER TABLE `cmf_users_chatroom_conf` CHANGE `tenant_id` `tenant_id` INT(11) NULL DEFAULT '0' COMMENT '租户ID';
ALTER TABLE `cmf_users_chatroom_conf` CHANGE `act_uid` `act_uid` INT(11) NULL DEFAULT '0' COMMENT '操作人id';
ALTER TABLE `cmf_users_chatroom_friends` CHANGE `tenant_id` `tenant_id` INT(11) NULL DEFAULT '0' COMMENT '租户ID';
ALTER TABLE `cmf_users_chatroom_friends` CHANGE `act_uid` `act_uid` INT(11) NULL DEFAULT '0' COMMENT '操作人id';

ALTER TABLE `cmf_users_chatroom_friends` ADD INDEX `tenant_id` (`id`);
ALTER TABLE `cmf_users_chatroom_friends` DROP INDEX `tenant_id`, ADD INDEX `tenant_id` (`tenant_id`) USING BTREE;
ALTER TABLE `cmf_users_chatroom_friends` ADD INDEX `roomtype` (`roomtype`);
ALTER TABLE `cmf_users_chatroom_friends` CHANGE `room_id` `room_id` BIGINT(17) NULL DEFAULT '0' COMMENT '聊天室ID';
ALTER TABLE `cmf_users_chatroom` ADD INDEX `tenant_id` (`tenant_id`);
ALTER TABLE `cmf_users_chatroom` ADD INDEX `roomtype` (`roomtype`);
ALTER TABLE `cmf_users_chatroom` ADD INDEX `status` (`status`);
ALTER TABLE `cmf_users_chatroom` CHANGE `avatar` `avatar` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '聊天室图标';

ALTER TABLE `cmf_noble` ADD UNIQUE `UINDX` (`tenant_id`, `level`);
ALTER TABLE `cmf_experlevel` ADD UNIQUE `UINDX` (`tenant_id`, `levelid`);
ALTER TABLE `cmf_experlevel_anchor` ADD UNIQUE `UINDX` (`tenant_id`, `levelid`);
ALTER TABLE `cmf_car` ADD UNIQUE `UINDX` (`tenant_id`, `name`);

ALTER TABLE `cmf_platform_config` ADD `chat_list_server` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '聊天室列表socket地址（php）' AFTER `chatroomserver`;
ALTER TABLE `cmf_platform_config` CHANGE `chatroomserver` `chatroomserver` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '聊天室socket地址';
ALTER TABLE `cmf_platform_config` ADD `go_chat_room_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '聊天室socket地址(golang)' AFTER `go_socket_url`,
    ADD `go_chat_list_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '聊天室列表socket地址(golang)' AFTER `go_chat_room_url`;

ALTER TABLE `cmf_tenant` CHANGE `appid` `appid` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'appid';
ALTER TABLE `cmf_tenant` CHANGE `appkey` `appkey` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'appkey';
ALTER TABLE `cmf_tenant` CHANGE `balance_query_url` `balance_query_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '余额查询接口地址';
ALTER TABLE `cmf_tenant` CHANGE `balance_update_url` `balance_update_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '余额更新接口地址';
ALTER TABLE `cmf_tenant` CHANGE `site` `site` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '域名';
ALTER TABLE `cmf_tenant` CHANGE `name` `name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '租户名称';
ALTER TABLE `cmf_tenant` CHANGE `status` `status` TINYINT(1) NOT NULL COMMENT '状态 0-禁用 1-启用';
ALTER TABLE `cmf_tenant` CHANGE `game_tenant_id` `game_tenant_id` BIGINT(20) NOT NULL COMMENT '游戏系统租户id';
ALTER TABLE `cmf_tenant` CHANGE `initial_admin` `initial_admin` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '初始管理员帐号';
ALTER TABLE `cmf_tenant` CHANGE `initial_admin_email` `initial_admin_email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '初始管理员邮箱';
ALTER TABLE `cmf_tenant` CHANGE `bank_card` `bank_card` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '收款银行卡';
ALTER TABLE `cmf_tenant` CHANGE `account_name` `account_name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '户名';
ALTER TABLE `cmf_tenant` CHANGE `account_bank` `account_bank` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '开户行';
ALTER TABLE `cmf_tenant` CHANGE `site_id` `site_id` TINYINT(1) NOT NULL COMMENT '租户类型：1 彩票租户 2 独立租户';

ALTER TABLE `tenant` ADD `third_appid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '三方appid' AFTER `third_tenant_id`;
ALTER TABLE `tenant` ADD `third_appkey` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '三方appkey' AFTER `third_appid`;

ALTER TABLE `cmf_gift` CHANGE `tenant_id` `tenant_id` INT(11) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `cmf_gift` ADD UNIQUE `UINDX` (`tenant_id`, `giftname`);

ALTER TABLE `cmf_platform_config` ADD `chatroom_socket_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '聊天室socket类型：1.php,2.node.js,3.golang' AFTER `socket_type`;
ALTER TABLE `cmf_platform_config` CHANGE `socket_type` `socket_type` INT(10) NOT NULL DEFAULT '0' COMMENT '直播间socket类型';
ALTER TABLE `cmf_platform_config` CHANGE `chatserver` `chatserver` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '聊天服务器带端口' AFTER `socket_type`, CHANGE `chat_list_server` `chat_list_server` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '聊天室列表socket地址（php）' AFTER `chatserver`, CHANGE `chatroomserver` `chatroomserver` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '聊天室socket地址' AFTER `chat_list_server`, CHANGE `go_chat_list_url` `go_chat_list_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '聊天室列表socket地址(golang)' AFTER `go_socket_url`;
ALTER TABLE `cmf_platform_config` CHANGE `chatserver` `chatserver` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '直播间socket地址(php/node.js)';
ALTER TABLE `cmf_platform_config` CHANGE `go_socket_url` `go_socket_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '直播间socket地址(golang)';
ALTER TABLE `cmf_platform_config` CHANGE `go_socket_url` `go_socket_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '直播间socket地址(golang)' AFTER `chatserver`, CHANGE `chatroom_socket_type` `chatroom_socket_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '聊天室socket类型：1.php,2.golang' AFTER `go_socket_url`;
ALTER TABLE `cmf_platform_config` CHANGE `socket_type` `socket_type` INT(10) NOT NULL DEFAULT '1' COMMENT '直播间socket类型：1.php,2.node.js,3.golang';
ALTER TABLE `cmf_platform_config` CHANGE `propellingserver` `propellingserver` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '机器人自动推送socket地址(php)';
ALTER TABLE `cmf_platform_config` CHANGE `chatroomserver` `chatroomserver` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '聊天室socket地址(php)';


UPDATE `cmf_tenant` SET `appid`='' WHERE `appid` is null;
UPDATE `cmf_tenant` SET `appkey`='' WHERE `appkey` is null;
UPDATE `cmf_tenant` SET `balance_query_url`='' WHERE `balance_query_url` is null;
UPDATE `cmf_tenant` SET `balance_update_url`='' WHERE `balance_update_url` is null;

ALTER TABLE `cmf_tenant` CHANGE `appid` `appid` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'appid';
ALTER TABLE `cmf_tenant` CHANGE `appkey` `appkey` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'appkey';
ALTER TABLE `cmf_tenant` CHANGE `balance_query_url` `balance_query_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '余额查询接口地址';
ALTER TABLE `cmf_tenant` CHANGE `balance_update_url` `balance_update_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '余额更新接口地址';

ALTER TABLE `cmf_tenant` CHANGE `type` `type` TINYINT(1) NOT NULL COMMENT '租户类型 0-平台 1-租户';
ALTER TABLE `cmf_tenant` CHANGE `update_time` `update_time` DATETIME on update CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间';

ALTER TABLE `cmf_car`
    MODIFY COLUMN `name`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称' AFTER `id`,
    MODIFY COLUMN `thumb`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '图片链接' AFTER `name`,
    MODIFY COLUMN `swftime`  decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '动画时长' AFTER `swf`,
    MODIFY COLUMN `tenant_id`  bigint(20) NULL AFTER `uptime`;

ALTER TABLE `cmf_car`
    MODIFY COLUMN `swf`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '动画链接' AFTER `thumb`,
    MODIFY COLUMN `words`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '进场话术' AFTER `addtime`,
    MODIFY COLUMN `tenant_id`  bigint(20) NULL AFTER `uptime`;

ALTER TABLE `cmf_noble_setting`
    ADD UNIQUE INDEX `UINDEX-tenant_id` (`tenant_id`) USING BTREE ;

ALTER TABLE `cmf_noble_skin`
    MODIFY COLUMN `val`  varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '皮肤值（颜色，格式：#AAAAAA）' AFTER `tenant_id`,
    ADD UNIQUE INDEX `UINDEX-tenant_id-val` (`tenant_id`, `val`) USING BTREE ;

ALTER TABLE `cmf_users_live`
    ADD COLUMN `m3u8pull`  varchar(500) NOT NULL DEFAULT '' COMMENT 'm3u8播流地址' AFTER `flvpull`;

CREATE TABLE `cmf_kvconfig` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `tag` tinyint(3) NOT NULL COMMENT '标签: 1.系统配置',
                                `key` varchar(64) NOT NULL COMMENT '键',
                                `val` tinytext NOT NULL COMMENT '值',
                                `desc` varchar(4096) NOT NULL DEFAULT '' COMMENT '描述',
                                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `operated_by` varchar(64) NOT NULL COMMENT '操作人',
                                PRIMARY KEY (`id`) USING BTREE,
                                UNIQUE KEY `UINDEX-tag-key` (`tag`,`key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cmf_kvconfig`
    ADD UNIQUE INDEX `UINDEX-tag` (`tag`) USING BTREE ;

ALTER TABLE `cmf_platform_config` ADD `url_of_push_to_java_cut_video` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '上传视频到java切片处理地址' AFTER `go_root_url`,
    ADD `url_of_get_video_info_from_java` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '定时器定时从java获取切片视频信息地址' AFTER `url_of_push_to_java_cut_video`;

ALTER TABLE `cmf_video` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户ID' ;
ALTER TABLE `cmf_video_long` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户ID' ;

ALTER TABLE `cmf_menu` CHANGE `model` `model` CHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '控制器';
ALTER TABLE `cmf_menu` CHANGE `action` `action` CHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作名称';
ALTER TABLE `cmf_auth_rule` CHANGE `module` `module` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '规则所属module';

ALTER TABLE `cmf_rate` ADD UNIQUE `UINDEX-code-tenant_id` (`code`, `tenant_id`);
ALTER TABLE `cmf_rate` ADD UNIQUE `UINDEX-name-tenant_id` (`name`, `tenant_id`);
ALTER TABLE `cmf_rate` ADD `ctime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间' AFTER `tenant_id`, ADD `mtime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `ctime`;
ALTER TABLE `cmf_rate` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作者' AFTER `tenant_id`;

ALTER TABLE `cmf_users_cashrecord` ADD `cash_account_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '提现账号类型：1.银行卡，2.USDT' AFTER `account`;
ALTER TABLE `cmf_users_cashrecord` ADD `network_type` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '网络类型：TRC20, ERC20' AFTER `cash_account_type`;
ALTER TABLE `cmf_platform_config` ADD `cash_account_type` VARCHAR(32) NOT NULL DEFAULT '1' COMMENT '提现账号类型：1.银行卡，2.USDT（多个类型用英文逗号“,”分隔）' AFTER `cash_rate`;
ALTER TABLE `cmf_users_cashrecord` ADD `virtual_coin_address` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '虚拟币地址' AFTER `network_type`;
ALTER TABLE `cmf_users_cashrecord` ADD `qr_code_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '二维码地址' AFTER `virtual_coin_address`;

ALTER TABLE `cmf_users_cashrecord`
    MODIFY COLUMN `name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '姓名' AFTER `account`;

ALTER TABLE `cmf_video` ADD `remark` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注' AFTER `tenant_id`;

ALTER TABLE `cmf_platform_config` ADD `cash_network_type` VARCHAR(16) NOT NULL DEFAULT 'TRC20' COMMENT '提现网络类型：TRC20, ERC20' AFTER `cash_account_type`;
ALTER TABLE `cmf_users_cashrecord` CHANGE `network_type` `cash_network_type` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '网络类型：TRC20, ERC20';

ALTER TABLE `cmf_users` CHANGE `coin` `coin` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '金币';
ALTER TABLE `cmf_users` CHANGE `nowithdrawable_coin` `nowithdrawable_coin` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '不可提现金币';
ALTER TABLE `cmf_users` CHANGE `withdrawable_money` `withdrawable_money` DECIMAL(20,4) NULL DEFAULT '0.0000' COMMENT '可提现金额';
ALTER TABLE `cmf_users` CHANGE `votes` `votes` DECIMAL(20,4) UNSIGNED NOT NULL DEFAULT '0.0000' COMMENT '映票余额';
ALTER TABLE `cmf_users` CHANGE `votestotal` `votestotal` DECIMAL(20,4) UNSIGNED NOT NULL DEFAULT '0.0000' COMMENT '映票总额';
ALTER TABLE `cmf_users` CHANGE `integral` `integral` DECIMAL(20,4) NULL DEFAULT '0.0000' COMMENT '积分';
ALTER TABLE `cmf_users` CHANGE `addup_integral` `addup_integral` DECIMAL(20,4) NULL DEFAULT '0.0000' COMMENT '累计积分';
ALTER TABLE `cmf_users` CHANGE `firstrecharge_coin` `firstrecharge_coin` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '首充金额';
ALTER TABLE `cmf_users` CHANGE `agent_total_income` `agent_total_income` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '代理总收入';
ALTER TABLE `cmf_users` CHANGE `withdrawable_push_slice` `withdrawable_push_slice` DECIMAL(20,4) NULL DEFAULT '0.0000' COMMENT '可提现推片赏金（已加到余额）';
ALTER TABLE `cmf_users` CHANGE `no_withdrawable_push_slice` `no_withdrawable_push_slice` DECIMAL(20,4) NULL DEFAULT '0.0000' COMMENT '不可提现推片赏金（已加到不可提现余额）';
ALTER TABLE `cmf_users` CHANGE `recharge_total` `recharge_total` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '充值总额';
ALTER TABLE `cmf_users` CHANGE `actual_recharge_total` `actual_recharge_total` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '实际到账 总额';

ALTER TABLE `cmf_users_coinrecord` CHANGE `totalcoin` `totalcoin` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '总价';

ALTER TABLE `cmf_users`
    MODIFY COLUMN `pid`  int(11) NOT NULL DEFAULT 0 COMMENT '上级用户 ' AFTER `pids`;

ALTER TABLE `cmf_users_vip`
    MODIFY COLUMN `status`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '0.审核中，1.生效中，2.退款中，3.已取消' AFTER `is_free`;

ALTER TABLE `cmf_users`
    MODIFY COLUMN `vip_margin`  decimal(20,4) NOT NULL DEFAULT 0.0000 COMMENT '保证金' AFTER `watchtime`;

ALTER TABLE `cmf_users_vip`
    MODIFY COLUMN `status`  tinyint(4) NOT NULL DEFAULT 4 COMMENT '1.生效中，2.退款中，3.已取消，4.审核中' AFTER `is_free`;

ALTER TABLE `cmf_vip_grade`
    MODIFY COLUMN `tenant_id`  int(10) NOT NULL DEFAULT 28 AFTER `id`,
    ADD COLUMN `created_at`  datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间' AFTER `tenant_id`,
    ADD COLUMN `updated_at`  datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间' AFTER `created_at`,
    ADD COLUMN `operation_by`  varchar(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `updated_at`,
    ADD COLUMN `upgrade_need_upload_video_num`  int(11) NOT NULL DEFAULT 0 COMMENT '升级所需上传视频数量' AFTER `status`;

ALTER TABLE `cmf_users_vip` CHANGE `price` `price` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT ' 价格';
ALTER TABLE `cmf_users_vip` ADD `actual_amount` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '实际支付金额（追加金额）' ;

ALTER TABLE `cmf_users_vip` ADD `action_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '操作类型：1.直接购买，2.升级' AFTER `actual_amount`;

ALTER TABLE `cmf_vip_grade` CHANGE `upgrade_need_upload_video_num` `upgrade_need_sub_user_vip_count` INT(11) NOT NULL DEFAULT '0' COMMENT '升级所需下级创作者数量';
ALTER TABLE `cmf_vip_grade` ADD `upgrade_need_sub_user_vip_grade` INT(11) NOT NULL DEFAULT '0' COMMENT '升级所需下级创作者等级' AFTER `upgrade_need_sub_user_vip_count`;

ALTER TABLE `cmf_vip_grade` ADD `video_upload_reward_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '奖励模式：1.有偿上传视频数量，2.每天' AFTER `upgrade_need_sub_user_vip_grade`;

ALTER TABLE `cmf_video`
    ADD COLUMN `top`  tinyint(1) NULL DEFAULT 0 COMMENT '是否置顶' AFTER `remark`;

CREATE TABLE `cmf_menu_auth_rule_action` (
                                             `id` int(11) NOT NULL AUTO_INCREMENT,
                                             `operated_by` varchar(64) NOT NULL COMMENT '操作人',
                                             `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                             `action_sql` text NOT NULL COMMENT '要执行的sql语句',
                                             `action_type` tinyint(1) NOT NULL COMMENT '操作类型：1.INSERT，2.UPDATE，3.DELETE',
                                             `table_name` varchar(64) NOT NULL COMMENT '操作的表名',
                                             PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cmf_users` ADD `payment_password` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '支付密码' AFTER `user_pass`;
ALTER TABLE `cmf_users` ADD `salt` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '加密盐' AFTER `payment_password`;
ALTER TABLE `cmf_users` ADD `payment_password_err_count` INT(11) NOT NULL DEFAULT '0' COMMENT '支付密码错误次数' AFTER `payment_password`;

ALTER TABLE `cmf_tenant`
    MODIFY COLUMN `balance_nft_url`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'nft接口地址' AFTER `live_jurisdiction`;
ALTER TABLE `cmf_tenant` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' ;

ALTER TABLE `cmf_country`
    MODIFY COLUMN `sort`  int(11) NOT NULL DEFAULT 100 COMMENT '排序' AFTER `code`;

ALTER TABLE `cmf_getcode_limit_ip` ADD `mobile` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '手机号' AFTER `tenant_id`;
ALTER TABLE `cmf_getcode_limit_ip` ADD `origin_ip` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '原ip' AFTER `mobile`;

ALTER TABLE `cmf_country` ADD `tenant_id` INT(11) NOT NULL COMMENT '租户ID' AFTER `id`;

ALTER TABLE `cmf_users` CHANGE `user_type` `user_type` SMALLINT(1) NOT NULL DEFAULT '1' COMMENT '用户类型，1:admin,2:会员,3:虚拟用户,4:游客 ,5包装账号，6代管账号，7测试账号';
ALTER TABLE `cmf_users_cashrecord` MODIFY COLUMN `tenant_id`  bigint(20) NULL DEFAULT NULL AFTER `id`;
ALTER TABLE `cmf_users_cashrecord` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `des`;

ALTER TABLE `cmf_tenant_config` ADD `is_use_visitor` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否使用游客：0.否，1.是' AFTER `initial_membership`;

ALTER TABLE `cmf_users_charge` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `tenant_id`;
ALTER TABLE `cmf_users_charge` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;
ALTER TABLE `cmf_users_cashrecord` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;

ALTER TABLE `cmf_users_charge_admin` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `tenant_id`;
ALTER TABLE `cmf_users_charge_admin` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `touid`;

ALTER TABLE `cmf_user_transfer_yuebao` MODIFY COLUMN `tenant_id`  int(11) NOT NULL DEFAULT 0 COMMENT '租户id' AFTER `status`;
ALTER TABLE `cmf_user_transfer_yuebao` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `tenant_id`;
ALTER TABLE `cmf_user_transfer_yuebao` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;

ALTER TABLE `cmf_yuebao_rate` MODIFY COLUMN `tenant_id`  int(11) NOT NULL DEFAULT 0 COMMENT '租户id';
ALTER TABLE `cmf_yuebao_rate` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;

ALTER TABLE `cmf_users_coinrecord` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;

ALTER TABLE `cmf_users_vip` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;
ALTER TABLE `cmf_users_vip` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `tenant_id`;
ALTER TABLE `cmf_users_vip` ADD `updated_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `action_type`;

ALTER TABLE `cmf_video` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;
ALTER TABLE `cmf_video` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `tenant_id`;

ALTER TABLE `cmf_video_long` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;
ALTER TABLE `cmf_video_long` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `tenant_id`;

ALTER TABLE `cmf_video_uplode_reward` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;

ALTER TABLE `cmf_agent_reward` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT 'p 用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `pid`;

ALTER TABLE `cmf_users_video_buy` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;

ALTER TABLE `cmf_video_profit` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `video_uid`;

ALTER TABLE `cmf_red_record_detail` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `uid`;


ALTER TABLE `cmf_users_cashrecord` CHANGE `money` `money` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT '提现币种金额';

ALTER TABLE `cmf_red_setting` ADD `vip_grade` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'vip等级（多个等级用英文逗号“,”分隔）' AFTER `game_tenant_id`;
ALTER TABLE `cmf_red_setting` ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `game_tenant_id`;
ALTER TABLE `cmf_red_setting` ADD `uids` TEXT NULL DEFAULT NULL COMMENT '用户id列表' AFTER `vip_grade`;
ALTER TABLE `cmf_red_setting` ADD `multiple` INT(11) NOT NULL DEFAULT '1' COMMENT '倍数（实际领取的红包就是抢到金额的M倍）' AFTER `game_tenant_id`;

ALTER TABLE `cmf_red_setting` ADD UNIQUE `UINDEX-tenant_id` (`tenant_id`);

ALTER TABLE `cmf_red_setting` ADD `name` VARCHAR(64) NOT NULL COMMENT '红包名称' AFTER `id`;
ALTER TABLE `cmf_red_setting` CHANGE `second_time` `second_time` INT(10) NULL DEFAULT '0' COMMENT '抢红包分钟数';
ALTER TABLE `cmf_red_setting` CHANGE `effect_time` `effect_time` INT(10) NULL DEFAULT '0' COMMENT '抢红包有效时间';
ALTER TABLE `cmf_red_setting` CHANGE `win_time` `win_time` INT(10) NULL DEFAULT '0' COMMENT '能抢到红包时间';
ALTER TABLE `cmf_red_setting` CHANGE `tenant_id` `tenant_id` INT(11) NULL DEFAULT '0' COMMENT '租户id';
ALTER TABLE `cmf_red_setting` DROP INDEX `UINDEX-tenant_id` , ADD UNIQUE INDEX `UINDEX-tenant_id-name` (`tenant_id`, `name`) USING BTREE ;
ALTER TABLE `cmf_red_setting` ADD `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `addtime`;
ALTER TABLE `cmf_red_setting` MODIFY COLUMN `tenant_id`  int(11) NULL DEFAULT 0 COMMENT '租户id' AFTER `id`;
ALTER TABLE `cmf_red_setting` CHANGE `effect_time` `effect_time` INT(10) NULL DEFAULT '0' COMMENT '红包有效时长'
ALTER TABLE `cmf_red_setting` ADD `vip_conf` TEXT NULL DEFAULT NULL COMMENT 'vip金额配置（json）' AFTER `uids`;
ALTER TABLE `cmf_red_setting` ADD `effect_time_start` INT(11) NOT NULL DEFAULT '0' COMMENT '生效时间' AFTER `vip_conf`,
    ADD `effect_time_end` INT(11) NOT NULL DEFAULT '0' COMMENT '结束时间' AFTER `effect_time_start`;

ALTER TABLE `cmf_tenant_config` ADD `red_packet_icon` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '红包图标' AFTER `is_use_visitor`,
    ADD `red_packet_special_effects` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '红包特效（图片或动画）' AFTER `red_packet_icon`;

ALTER TABLE `cmf_users_agent` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型：用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `tenant_id`;

ALTER TABLE `cmf_platform_config` CHANGE `url_of_push_to_java_cut_video` `url_of_push_to_java_cut_video` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '上传视频到java切片处理地址';

ALTER TABLE `cmf_users_charge_admin` ADD `status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '状态：1.已调整' AFTER `currency_code`,
    ADD `business_type` TINYINT(3) NOT NULL DEFAULT '2' COMMENT '业务类型：1.充值优惠-手工，2.手工充值，3.代理返点-手工，4.返水优惠-手工，5.其他优惠，6.异常加减分' AFTER `status`;
ALTER TABLE `cmf_users_charge_admin` DROP `act_uid`;
ALTER TABLE `cmf_users_charge_admin` ADD `balance_type` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '余额类型：1.可提现余额，2.不可提现余额' AFTER `user_type`;
ALTER TABLE `cmf_users_charge_admin` ADD `after_balance` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '调整后余额' AFTER `coin`;
ALTER TABLE `cmf_users_charge_admin` ADD `remark` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '备注' AFTER `operated_by`;
ALTER TABLE `cmf_users_charge_admin` ADD `pre_balance` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '调整前余额' AFTER `coin`;
ALTER TABLE `cmf_users_coinrecord` ADD `pre_balance` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '变动前余额' AFTER `giftcount`;
ALTER TABLE `cmf_users_coinrecord` ADD `after_balance` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '变动后余额' AFTER `totalcoin`;
ALTER TABLE `cmf_red_record_detail` ADD `pre_balance` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '领取前余额' AFTER `datenum`;
ALTER TABLE `cmf_red_record_detail` ADD `after_balance` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '领取后余额' AFTER `coin`;
ALTER TABLE `cmf_users_coinrecord` ADD `remark` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '备注' AFTER `is_withdrawable`;

ALTER TABLE `cmf_video` ADD `is_advertise` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否为广告: 0.否，1.是' AFTER `top`;
ALTER TABLE `cmf_video` ADD INDEX `INDEX-tenant_id` (`tenant_id`);
ALTER TABLE `cmf_video` ADD INDEX `INDEX-top` (`top`);
ALTER TABLE `cmf_video` ADD INDEX `INDEX-is_advertise` (`is_advertise`);
ALTER TABLE `cmf_video` MODIFY COLUMN `tenant_id`  int(11) NOT NULL DEFAULT 0 COMMENT '租户ID' AFTER `id`;
ALTER TABLE `cmf_video` MODIFY COLUMN `operated_by`  varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '操作人' AFTER `tenant_id`;
ALTER TABLE `cmf_video` ADD INDEX `INDEX-status` (`status`);
ALTER TABLE `cmf_video` ADD `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间' AFTER `tenant_id`,
    ADD `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `create_time`;
ALTER TABLE `cmf_video` CHANGE `status` `status` TINYINT(8) NULL DEFAULT '1' COMMENT '状态：-1.上传中，1.待审核，2.审核通过，3.审核未通过，4.已删除';
ALTER TABLE `cmf_video` CHANGE `is_downloadable` `is_downloadable` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '上传状态：0.未完成，1.已完成，2.上传失败，3.文件不存在，4.解析失败';
UPDATE `cmf_video` SET `create_time`=UNIX_TIMESTAMP(create_date) WHERE 1;
UPDATE `cmf_video` SET `update_time`=UNIX_TIMESTAMP(create_date) WHERE 1;
ALTER TABLE `cmf_video` CHANGE `origin` `origin` TINYINT(2) NULL DEFAULT '1' COMMENT '来源1 前台 2 后台';
ALTER TABLE `cmf_video` ADD INDEX `INDEX-origin` (`origin`);
ALTER TABLE `cmf_users_jurisdiction` ADD `watchnum_show_ad_video` INT(11) NOT NULL DEFAULT '0' COMMENT '观看多少次出现一次广告视频' AFTER `tenant_id`;
ALTER TABLE `cmf_tenant_config` ADD `agent_line_visible` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否视频代理线可见：0.否，1.是';
ALTER TABLE `cmf_tenant_config` ADD `top_shiort_vide_count` INT(11) NOT NULL DEFAULT '3' COMMENT '置顶视频数量' AFTER `agent_line_visible`;


ALTER TABLE `cmf_playback_address` ADD `replace_domain` TEXT NULL DEFAULT NULL COMMENT '要替换的旧域名，视频文件里的旧域名换成新域名' AFTER `tenant_id`;

ALTER TABLE `cmf_users` MODIFY COLUMN `tenant_id`  bigint(20) NULL DEFAULT NULL COMMENT '租户id' AFTER `id`;
ALTER TABLE `cmf_users` CHANGE `pids` `pids` VARCHAR(4000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '上级用户id';

ALTER TABLE `cmf_tenant_config` CHANGE `top_shiort_vide_count` `top_short_vide_count` INT(11) NOT NULL DEFAULT '3' COMMENT '置顶视频数量';
ALTER TABLE `cmf_tenant_config` ADD `auto_check_short_video` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '自动审核：0.否，1.是' AFTER `top_short_vide_count`;
ALTER TABLE `cmf_tenant_config` CHANGE `top_short_vide_count` `top_short_video_count` INT(11) NOT NULL DEFAULT '3' COMMENT '置顶视频数量';

ALTER TABLE `cmf_users_jurisdiction` ADD `limit_upload_video_count` INT(11) NOT NULL DEFAULT '0' COMMENT '限制上传视频数量' AFTER `watchnum_show_ad_video`;
ALTER TABLE `cmf_users_jurisdiction` CHANGE `tenant_id` `tenant_id` INT(10) NOT NULL DEFAULT '28' COMMENT '租户ID' AFTER id;
ALTER TABLE `cmf_users_jurisdiction` ADD `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间' AFTER `tenant_id`,
    ADD `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `create_time`,
    ADD `operated_by` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `update_time`;
ALTER TABLE `cmf_users_jurisdiction` ADD UNIQUE `INDEX-tenant_id-grade` (`tenant_id`, `grade`);
ALTER TABLE `cmf_menu_auth_rule_action` ADD `menu_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '菜单类型：1.后台菜单，2.前端菜单' AFTER `table_name`;

ALTER TABLE `cmf_users_vip` ADD `refund_time` INT(11) NOT NULL DEFAULT '0' COMMENT '退款时间' AFTER `updated_time`;
ALTER TABLE `cmf_users_vip` CHANGE `status` `status` TINYINT(4) NOT NULL DEFAULT '4' COMMENT '1.生效中，2.退款中，3.已退款，4.审核中';

CREATE TABLE `cmf_log_complex` (
                                   `id` bigint(20) NOT NULL AUTO_INCREMENT,
                                   `tenant_id` int(11) NOT NULL DEFAULT '0',
                                   `ctime` int(11) NOT NULL DEFAULT '0',
                                   `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型：1.默认，2.红包',
                                   `ct` text,
                                   `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
                                   `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
                                   `user_acount` varchar(64) NOT NULL DEFAULT '' COMMENT '用户账号',
                                   `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '后台管理员id',
                                   `admin_acount` varchar(64) NOT NULL DEFAULT '' COMMENT '后台管理员账号',
                                   PRIMARY KEY (`id`),
                                   KEY `ctime` (`ctime`),
                                   KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=306477 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cmf_video_profit` ADD `video_like_user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '点赞会员账号' AFTER `video_like_uid`;
ALTER TABLE `cmf_video_profit` ADD `video_user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '作者账号' AFTER `video_uid`;
ALTER TABLE `cmf_video` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_users_video_buy` CHANGE `username` `user_login` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '';
ALTER TABLE `cmf_users_video_buy` CHANGE `video_type` `video_type` TINYINT(8) NULL DEFAULT NULL COMMENT '视频类型：1.短视频，2.长视频';
ALTER TABLE `cmf_users_action` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_users_charge` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_users_charge` ADD `to_user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '对方用户账号' AFTER `touid`;
ALTER TABLE `cmf_users_cashrecord` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_users_charge_admin` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `touid`;
ALTER TABLE `cmf_user_transfer_yuebao` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_yuebao_rate` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_red_record_detail` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_activity_reward_log` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_activity_reward_log` ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `user_login`;
ALTER TABLE `cmf_users_coinrecord` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_users_coinrecord` ADD `to_user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '对方用户账号' AFTER `touid`;

ALTER TABLE `cmf_video_long` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`;
ALTER TABLE `cmf_bar` ADD `user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户账号' AFTER `uid`,
    ADD `user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号 ' AFTER `user_login`;

ALTER TABLE `cmf_tenant_config` CHANGE `top_short_video_count` `top_short_video_count` INT(11) NOT NULL DEFAULT '1' COMMENT '置顶视频数量';

ALTER TABLE `cmf_platform_config` ADD `bank_bind_limit_count` INT(11) NOT NULL DEFAULT '0' COMMENT '同一银行卡限时绑定时间内最多绑定次数，0不限制';
ALTER TABLE `cmf_platform_config` ADD `bank_bind_limit_day` INT(11) NOT NULL DEFAULT '0' COMMENT '同一银行卡限绑定时时间（单位：天）' AFTER `bank_bind_limit_count`;
ALTER TABLE `cmf_platform_config` ADD `bank_realname_bind_limit_count` INT(11) NOT NULL DEFAULT '0' COMMENT '同一银行卡姓名绑定限时时间内最多绑定次数，0不限制';
ALTER TABLE `cmf_platform_config` ADD `bank_realname_bind_limit_day` INT(11) NOT NULL DEFAULT '0' COMMENT '同一银行卡姓名绑定限时时间（单位：天）' AFTER `bank_realname_bind_limit_count`;
ALTER TABLE `live_dev`.`cmf_bank_card` ADD INDEX `INDEX-uid` (`uid`);
ALTER TABLE `live_dev`.`cmf_bank_card` ADD INDEX `INDEX-tenant_id` (`tenant_id`);
ALTER TABLE `live_dev`.`cmf_bank_card` ADD INDEX `INDEX-bank_number` (`bank_number`);
ALTER TABLE `live_dev`.`cmf_bank_card` ADD INDEX `INDEX-addtime` (`addtime`);

ALTER TABLE `cmf_admin_log` CHANGE `type` `type` INT(11) NOT NULL DEFAULT '1' COMMENT '类型：1.默认，2.弹窗警告';

ALTER TABLE `cmf_users` ADD `upload_video_profit_status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '上传视频收益：0.关闭，1.开启';
ALTER TABLE `cmf_users` ADD `grab_red_packet_status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '抢红包：0.关闭，1.开启' AFTER upload_video_profit_status;
ALTER TABLE `cmf_users` ADD `rebate_status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '代理返点：0.关闭，1.开启' AFTER grab_red_packet_status;


CREATE TABLE `cmf_withdraw_fee_config` (
                                           `id` int(11) NOT NULL AUTO_INCREMENT,
                                           `tenant_id` int(11) NOT NULL DEFAULT '28' COMMENT '租户id',
                                           `create_time` int(11) NOT NULL COMMENT '创建时间',
                                           `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
                                           `operated_by` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '操作人',
                                           `amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
                                           `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1.百分比，2.固定值',
                                           `fee` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '手续费',
                                           PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;

ALTER TABLE `cmf_users_cashrecord` ADD `received_money` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '到账金额' AFTER `money`;
ALTER TABLE `cmf_users_cashrecord` ADD `service_fee` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '手续费' AFTER `received_money`;
ALTER TABLE `cmf_offlinepay` ADD `limit_charge_total_money` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '限制收款卡号总充值金额' AFTER `max_amount`;
ALTER TABLE `cmf_offlinepay` ADD `already_charge_total_money` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '已经充值总金额（银行卡修改后重置为0）' AFTER `limit_charge_total_money`;


ALTER TABLE `cmf_red_setting` ADD `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '奖品类型：1.金钱，2.购物券' AFTER `win_time`;
ALTER TABLE `cmf_tenant_config` ADD `red_packet_shopvoucher_special_effects` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '红包购物券特效图标' AFTER `red_packet_special_effects`;
CREATE TABLE `cmf_shopping_voucher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL COMMENT '租户id',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `user_login` varchar(64) NOT NULL DEFAULT '' COMMENT '用户账号',
  `user_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号	',
  `amount` decimal(20,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '金额',
  `datenum` varchar(64) NOT NULL DEFAULT '' COMMENT '批次',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1.未使用，2.已使用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;


ALTER TABLE `cmf_users_agent` ADD UNIQUE `UNIQUE-uid-tenant_id` (`uid`, `tenant_id`)

ALTER TABLE `cmf_users_agent_code` DROP INDEX `code`, ADD UNIQUE `UNIQUE-code` (`code`) USING BTREE;
ALTER TABLE `cmf_users_agent_code` DROP PRIMARY KEY, ADD UNIQUE `UNIQUE-uid` (`uid`) USING BTREE;

ALTER TABLE `cmf_video_classify` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户id' AFTER `id`;
ALTER TABLE `cmf_video_classify` ADD `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `tenant_id`;
ALTER TABLE `cmf_video_classify` CHANGE `classify` `classify` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分类名称';
ALTER TABLE `cmf_video_classify` ADD `is_upperlevel` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '仅允许上级' AFTER `is_lowerlevel`;
ALTER TABLE `cmf_video_classify` CHANGE `is_upperlevel` `agent_line_visible` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '代理线可见';
ALTER TABLE `cmf_video_classify` CHANGE `is_lowerlevel` `is_lowerlevel` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '仅允许下级';

CREATE TABLE `cmf_usdt_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '1',
  `user_login` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户账号',
  `user_type` int(3) NOT NULL COMMENT '用户类型',
  `tenant_id` int(11) NOT NULL COMMENT '租户id',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `operated_by` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '操作人',
  `address` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'USDT地址',
  `network_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '网络类型：TRC20, ERC20',
  `qrcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'USDT二维码URL',
  `status` tinyint(4) NOT NULL COMMENT '状态：1.启用，2.禁用',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `UNIQUE-uid-address` (`uid`,`address`),
  KEY `INDEX-uid` (`uid`),
  KEY `INDEX-tenant_id` (`tenant_id`),
  KEY `INDEX-create_time` (`create_time`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

ALTER TABLE `cmf_usdt_address` CHANGE `network_type` `network_type` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '网络类型：TRC20, ERC20';
ALTER TABLE `cmf_platform_config` ADD `usdt_address_bind_limit_count` INT(11) NOT NULL DEFAULT '0' COMMENT '同一USDT地址限时时间内最多绑定次数，0不限制' AFTER `bank_realname_bind_limit_day`, ADD `usdt_address_bind_limit_day` INT(11) NOT NULL DEFAULT '0' COMMENT '同一USDT地址限时时间（单位：天）' AFTER `usdt_address_bind_limit_count`;

ALTER TABLE `cmf_video` CHANGE `origin` `origin` TINYINT(2) NULL DEFAULT '1' COMMENT '来源: 1.用户上传, 2.后台上传, 3.后台手动添加链接';
ALTER TABLE `cmf_video` CHANGE `top` `top` TINYINT(1) NULL DEFAULT '0' COMMENT '是否置顶：0.否，1.是';

ALTER TABLE `cmf_log_complex` ADD `ip` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'IP' AFTER `admin_acount`;


ALTER TABLE `live_dev`.`cmf_users_cashrecord` ADD INDEX `INDEX-currency_code` (`id`);

ALTER TABLE `cmf_video` CHANGE `classify` `classify` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '分类';
ALTER TABLE `cmf_video` ADD INDEX `INDEX-classify` (`classify`);

ALTER TABLE `cmf_playback_address` ADD `space_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '空间名称，如：liveprod-store-1039' AFTER `replace_domain`;

ALTER TABLE `cmf_users` ADD `like_deposit` DECIMAL(20,4) NOT NULL DEFAULT '0.0000' COMMENT '已缴纳的点赞保证金' AFTER `vip_margin`;

CREATE TABLE `cmf_like_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL COMMENT '租户id',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `operated_by` varchar(64) NOT NULL DEFAULT '' COMMENT '操作人',
  `reward_amount` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '点赞奖励金额',
  `reward_count` int(11) NOT NULL DEFAULT '0' COMMENT '奖励次数',
  `reward_type` tinyint(1) NOT NULL DEFAULT '2' COMMENT '奖励模式：1.总次数, 2.每天',
  `reward_amount_type` int(11) NOT NULL DEFAULT '1' COMMENT '奖励金额类型：1.可提现，2.不可提现',
  `deposit` decimal(20,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '保证金',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE-tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cmf_users_like` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `user_login` varchar(64) NOT NULL COMMENT '用户账号',
  `user_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `tenant_id` int(11) DEFAULT '0' COMMENT '租户id',
  `operated_by` varchar(64) NOT NULL DEFAULT '' COMMENT '操作人',
  `like_config_id` int(11) NOT NULL COMMENT '点赞配置id',
  `status` tinyint(4) NOT NULL DEFAULT '4' COMMENT '状态：1.申请中，2.生效中，3.退款中，4.已退款',
  `deposit` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '保证金',
  `checked_time` int(11) NOT NULL DEFAULT '0' COMMENT '审核时间',
  `refund_time` int(11) NOT NULL DEFAULT '0' COMMENT '退款时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `UNIQUE-uid` (`uid`) USING BTREE,
  UNIQUE KEY `INDEX-user_type` (`user_type`),
  KEY `INDEX-create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

ALTER TABLE `cmf_log_complex` CHANGE `type` `type` INT(11) NOT NULL DEFAULT '1' COMMENT '类型：1.默认，2.红包，300.点赞';
ALTER TABLE `cmf_video_profit` DROP INDEX `video_type`, ADD INDEX `INDEX-video_type` (`video_type`) USING BTREE;
ALTER TABLE `cmf_video_profit` DROP INDEX `video_like_uid`, ADD INDEX `INDEX-video_like_uid` (`video_like_uid`) USING BTREE;
ALTER TABLE `cmf_video_profit` DROP INDEX `video_id`, ADD INDEX `INDEX-video_id` (`video_id`) USING BTREE;
ALTER TABLE `cmf_video_profit` ADD INDEX `INDEX-create_time` (`create_time`);
ALTER TABLE `cmf_video_profit` ADD INDEX `INDEX-video_uid` (`video_uid`);

ALTER TABLE `cmf_platform_config` ADD `bank_bind_count_one_user` INT(11) NOT NULL DEFAULT '0' COMMENT '同一会员最多绑定银行卡数量，0不限制' AFTER `usdt_address_bind_limit_day`,
ADD `usdt_bind_count_one_user` INT(11) NOT NULL DEFAULT '0' COMMENT '同一会员最多绑定usdt地址数量，0不限制' AFTER `bank_bind_count_one_user`;

ALTER TABLE `cmf_ads` ADD `url_jump_type` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '外部链接跳转类型：1.新窗口，2.本窗口' AFTER `url`;


ALTER TABLE `cmf_users_liverecord` CHANGE `tenant_id` `tenant_id` INT(11) NULL DEFAULT '0';
ALTER TABLE `cmf_users_liverecord` ADD INDEX `INDEX-tenant_id` (`tenant_id`);
ALTER TABLE `cmf_users_liverecord` MODIFY COLUMN `tenant_id`  int(11) NULL DEFAULT 0 AFTER `id`;

ALTER TABLE `cmf_commission_set` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户id' AFTER `id`;

ALTER TABLE `cmf_live_class` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户id' AFTER `id`;

ALTER TABLE `cmf_users_basicsalary` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户id' AFTER `id`;
ALTER TABLE `cmf_consumption_collect` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户id' AFTER `id`;
ALTER TABLE `cmf_users_share_log` CHANGE `tenant_id` `tenant_id` INT(11) NULL DEFAULT '0' COMMENT '租户id';
ALTER TABLE `cmf_users_share_log` ADD INDEX `INDEX-tenant_id` (`tenant_id`);

ALTER TABLE `cmf_users_basicsalary` ADD INDEX `INDEX-tenant_id` (`tenant_id`);
ALTER TABLE `cmf_consumption_collect` ADD INDEX `INDEX-tenant_id` (`tenant_id`);


ALTER TABLE `cmf_user_keyword` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户id' AFTER `id`;
ALTER TABLE `cmf_user_keywordset` ADD `tenant_id` INT(11) NOT NULL DEFAULT '0' COMMENT '租户id' AFTER `id`;

ALTER TABLE `cmf_user_keywordset` ADD UNIQUE `UNIQUE-tenant_id-content` (`tenant_id`, `content`);

ALTER TABLE `cmf_shop_manager` CHANGE `amount` `amount` FLOAT(16,2) NULL DEFAULT '0.00' COMMENT '金额';
ALTER TABLE `cmf_shop_manager` ADD `goods_purchase_price` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '订单商品采购金额' AFTER `amount`;


CREATE TABLE `cmf_shop_order_purchase` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(12) DEFAULT '0' COMMENT '租户id',
  `shop_order_id` int(11) NOT NULL DEFAULT '0' COMMENT '商城订单id',
  `goods_purchase_price` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '订单商品采购金额',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `INDEX-tenant_id` (`tenant_id`),
  KEY `INDEX-shop_order_id` (`shop_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

ALTER TABLE `cmf_guard` CHANGE `tenant_id` `tenant_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '租户id';

ALTER TABLE `cmf_shop_manager` ADD `shop_order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '商城订单号' AFTER `user_login`,
ADD `cg_order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '代发(采购)订单号' AFTER `shop_order_id`;
ALTER TABLE `cmf_shop_manager` ADD INDEX `INDEX-uid` (`uid`);
ALTER TABLE `cmf_shop_manager` ADD INDEX `INDEX-addtime` (`addtime`);
ALTER TABLE `cmf_shop_manager` ADD INDEX `INDEX-tenant_id` (`tenant_id`);
ALTER TABLE `cmf_shop_manager` ADD `user_type` INT(11) NOT NULL DEFAULT '0' COMMENT '用户类型: 2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号 ' AFTER `user_login`;
ALTER TABLE `cmf_shop_order_purchase` ADD `cg_order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '代发(采购)订单号' AFTER `shop_order_id`


ALTER TABLE `cmf_platform_config` ADD `football_live_base_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '足球视频直播基础url' AFTER `long_video_order_count`,
ADD `football_live_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '足球视频直播token' AFTER `football_live_base_url`;
ALTER TABLE `cmf_users_live` ADD `is_football` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否是足球直播：0.否，1.是' AFTER `isvideo`;
ALTER TABLE `cmf_users_liverecord` ADD `is_football` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否是足球直播：0.否，1.是' AFTER `isvideo`;
ALTER TABLE `cmf_users_live` ADD `football_live_match_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '足球视频直播比赛ID' AFTER `is_football`;
ALTER TABLE `cmf_users_liverecord` ADD `football_live_match_id` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '足球视频直播比赛ID' AFTER `is_football`;
ALTER TABLE `cmf_users_live` ADD `football_live_time_stamp` INT(11) NOT NULL DEFAULT '0' COMMENT '足球视频直播时间戳' AFTER `football_live_match_id`;
ALTER TABLE `cmf_users_liverecord` ADD `football_live_time_stamp` INT(11) NOT NULL DEFAULT '0' COMMENT '足球视频直播时间戳' AFTER `football_live_match_id`;


CREATE TABLE `cmf_auto_live_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL COMMENT '租户id',
  `operated_by` varchar(32) NOT NULL COMMENT '操作人',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '自动开播类型：1.足球',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `user_login` varchar(32) NOT NULL COMMENT '用户账号',
  `game_user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '游戏系统用户id',
  PRIMARY KEY (`id`),
  KEY `INDEX-tenant_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;


ALTER TABLE `cmf_users_cashrecord` ADD `superior_uid` INT(11) NOT NULL DEFAULT '0' COMMENT '上级用户id' AFTER `user_type`;
ALTER TABLE `cmf_users_cashrecord` ADD `superior_user_login` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '上级用户账号' AFTER `superior_uid`,
ADD `superior_user_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '上级用户类型：2.会员, 3.虚拟用户, 4.游客, 5.包装账号, 6.代管账号, 7.测试账号' AFTER `superior_user_login`;

ALTER TABLE `cmf_auto_live_user` ADD `thumb` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '封面图' AFTER `game_user_id`;
ALTER TABLE `cmf_auto_live_user` ADD `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `create_time`;

ALTER TABLE `cmf_shop_manager` CHANGE `shop_order_id` `shop_order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '商城订单id';
ALTER TABLE `cmf_shop_manager` CHANGE `cg_order_id` `cg_order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '代发(采购)订单id';
ALTER TABLE `cmf_shop_order_purchase` CHANGE `cg_order_id` `cg_order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '代发(采购)订单id';
ALTER TABLE `cmf_users_coinrecord` ADD `shop_order_no` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '商城订单号' AFTER `order_id`;
ALTER TABLE `cmf_users_coinrecord` CHANGE `order_id` `order_id` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '代发(采购)订单号';
ALTER TABLE `cmf_users_coinrecord` ADD INDEX `INDEX-user_type` (`user_type`);
ALTER TABLE `cmf_users_coinrecord` ADD INDEX `INDEX-user_login` (`user_login`);

ALTER TABLE `cmf_shop_manager` ADD `shop_order_no` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '商城订单号' AFTER `cg_order_id`,
ADD `cg_order_no` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '代发(采购)订单号' AFTER `shop_order_no`;

ALTER TABLE `cmf_shop_order_purchase` ADD `shop_order_no` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '商城订单号' AFTER `cg_order_id`,
ADD `cg_order_no` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '代发(采购)订单号' AFTER `shop_order_no`;


ALTER TABLE `cmf_users` ADD `ctime` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间' AFTER `create_time`;
-- UPDATE `cmf_users` SET ctime = UNIX_TIMESTAMP(create_time) WHERE 1;


ALTER TABLE `cmf_platform_config` ADD `shop_h5_url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '商城H5url' AFTER `yp_apikey`;
ALTER TABLE `cmf_platform_config` ADD `shop_h5_name` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '商城H5名称' AFTER `shop_h5_url`;


ALTER TABLE `cmf_users_coinrecord` CHANGE `order_id` `order_id` VARCHAR(4000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '代发(采购)订单号';
ALTER TABLE `cmf_users_coinrecord` CHANGE `shop_order_no` `shop_order_no` VARCHAR(4000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商城订单号';

















































































































