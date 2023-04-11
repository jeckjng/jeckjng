ALTER TABLE `live_dev`.`cmf_tenant_config`
ADD COLUMN `like_rate_zero` decimal(6, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '视频点赞 视频发布者赠送比例',
ADD COLUMN `like_rate_one` decimal(6, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '视频点赞 视频发布者上一级赠送比例',
ADD COLUMN `like_rate_two` decimal(6, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '视频点赞 视频发布者上二级赠送比例',
ADD COLUMN `like_rate_three` decimal(6, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '视频点赞 视频发布者上三级赠送比例';


CREATE TABLE `cmf_video_profit`  (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `video_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '视频类型 1=短视频 2=长视频',
     `video_id` int(11) NOT NULL DEFAULT 0 COMMENT '视频ID',
     `video_like_uid` int(11) NOT NULL DEFAULT 0 COMMENT '点赞会员UID',
     `video_uid_zero` int(11) NOT NULL DEFAULT 0 COMMENT '视频作者UID',
     `video_profit_zero` decimal(6, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '视频作者收益',
     `video_uid_one` int(11) NOT NULL DEFAULT 0 COMMENT '视频作者上一级UID',
     `video_profit_one` decimal(6, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '视频作者上一级收益',
     `video_uid_two` int(11) NOT NULL DEFAULT 0 COMMENT '视频作者上二级UID',
     `video_profit_two` decimal(6, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '视频作者上二级收益',
     `video_uid_three` int(11) NOT NULL DEFAULT 0 COMMENT '视频作者上三级UID',
     `video_profit_three` decimal(6, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '视频作者上三级收益',
     `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
     PRIMARY KEY (`id`),
     INDEX `video_type`(`video_type`) USING BTREE,
     INDEX `video_like_uid`(`video_like_uid`) USING BTREE,
     INDEX `video_id`(`video_id`) USING BTREE
);

ALTER TABLE `live_dev`.`cmf_video_profit`
    ADD COLUMN `tenant_id` bigint(20) NULL DEFAULT 0 COMMENT '游戏系统租户ID';