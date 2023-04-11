

 ALTER TABLE `cmf_vip_grade`
ADD COLUMN `uplode_video_num`  int(11) NOT NULL DEFAULT 0 COMMENT '上传多少内的视频数量有奖励' AFTER `is_super_member`,
ADD COLUMN `uplode_video_amount`  decimal(12,2) NOT NULL DEFAULT 0 COMMENT '规定数量内上传视频奖励' AFTER `uplode_video_num`;


ALTER TABLE `cmf_users_jurisdiction`
MODIFY COLUMN `bar_number`  int(11) NOT NULL DEFAULT 10 COMMENT '发帖数量' AFTER `watchnum_ad`,
MODIFY COLUMN `bar_slice_number`  int(11) NOT NULL DEFAULT 10 COMMENT '发帖评论数量' AFTER `bar_number`;



ALTER TABLE `cmf_tenant_config` ADD COLUMN `vip_model` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 购买模式，2保证金模式';
ALTER TABLE `cmf_vip_grade`
ADD COLUMN `price`  decimal(12,2) NOT NULL DEFAULT 0 COMMENT 'vip价格（保证金模式才会在有）' AFTER `uplode_video_amount`;


ALTER TABLE `cmf_users_coinrecord`
MODIFY COLUMN `action`  varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '收支行为' AFTER `type`;

ALTER TABLE `cmf_users_vip`
ADD COLUMN `status`  tinyint(4) NOT NULL DEFAULT '1 ' COMMENT '1 生效中 2 退款中 3 已取消' AFTER `is_free`;

ALTER TABLE `cmf_users`
ADD COLUMN `vip_margin`  decimal(12,2) NOT NULL DEFAULT '0 ' NULL AFTER `watchtime`;

 ALTER TABLE `cmf_users_vip` ADD COLUMN  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT ' 价格';

