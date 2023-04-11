
ALTER TABLE `cmf_tenant_config`
ADD COLUMN `start_page_countdown`  int(11) NOT NULL DEFAULT 0 COMMENT '启动页广告倒计时' AFTER `second_stop`;

ALTER TABLE `cmf_tenant_config`
ADD COLUMN `video_stop_ads`  int(11) NULL DEFAULT 0 COMMENT '视频播放到什么时候弹出广告' AFTER `start_page_countdown`;

ALTER TABLE `cmf_ads` ADD COLUMN  `long_video_class_id` int(4) DEFAULT '0' COMMENT '长视频分类id'