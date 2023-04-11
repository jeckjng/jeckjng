ALTER TABLE `cmf_user_task_classification` ADD `amount` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '金额' AFTER `classification`;
ALTER TABLE `cmf_user_task` ADD `experience_shop` TINYINT(2) NOT NULL COMMENT '是否体验商城: 0否，1是' AFTER `classification`;
