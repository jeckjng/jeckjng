ALTER TABLE `cmf_task_classification` ADD `bgimg` VARCHAR(655) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '分类背景图' AFTER `logo`;

ALTER TABLE `cmf_users_coinrecord` CHANGE `totalcoin` `totalcoin` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '总价';

ALTER TABLE `cmf_task_rewardlog` ADD `user_task_id` INT(11) NOT NULL COMMENT '用户任务id' AFTER `task_id`;
