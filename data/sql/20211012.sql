CREATE TABLE `cmf_user_task_classification` (
                                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                                `uid` int(11) NOT NULL COMMENT '用户id',
                                                `classification` int(11) NOT NULL COMMENT '任务分类',
                                                `tenant_id` int(11) NOT NULL COMMENT '租户id',
                                                `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
                                                PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cmf_tenant_config` ADD `official_website_url` varchar(255) NOT NULL DEFAULT '' COMMENT '官网地址' ;
ALTER TABLE `cmf_video_long` ADD `playTimeInt` int(11) NOT NULL DEFAULT '0' COMMENT '时长'
ALTER TABLE `cmf_video` ADD `playTimeInt` int(11) NOT NULL DEFAULT '0' COMMENT '时长'
