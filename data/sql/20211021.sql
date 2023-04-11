ALTER TABLE `cmf_users_vip` ADD `grade` tinyint(4) NOT NULL DEFAULT '0' COMMENT '会员等级';
ALTER TABLE `cmf_users_vip` ADD `is_free` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已经免费领取 0还没领取 1 已领取';

