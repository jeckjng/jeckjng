ALTER TABLE `cmf_menu` CHANGE `action` `action` CHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作名称';

ALTER TABLE `cmf_users_action` CHANGE `ctime` `ctime` INT(11) NOT NULL DEFAULT '0' COMMENT '用户行为时间';
