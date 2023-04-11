ALTER TABLE `live_dev`.`cmf_platform_config`
    ADD COLUMN `yp_apikey` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '云片ApiKey' AFTER `user_agreement`;