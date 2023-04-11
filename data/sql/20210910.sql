ALTER TABLE `cmf_platform_config`
    CHANGE COLUMN `ihuyi_account` `yd_secretid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '易盾secretId' AFTER `cache_time`,
    CHANGE COLUMN `ihuyi_ps` `yd_secretkey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '易盾secretKey' AFTER `yd_secretid`,
    ADD COLUMN `yd_businessid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '易盾businessId' AFTER `yd_secretkey`;

