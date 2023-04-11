ALTER TABLE cmf_video_watch_record ADD `video_type` TINYINT (4) DEFAULT '1' COMMENT '1:长视频 2短视频';

ALTER TABLE cmf_platform_config
    ADD COLUMN `socket_type` int(10) NOT NULL DEFAULT 0 COMMENT 'socket服务类型' AFTER propellingserver;
ALTER TABLE cmf_users
    ADD COLUMN follows int(11) NOT NULL DEFAULT 0 COMMENT '虚拟会员关注人数' AFTER withdrawable_money,
ADD COLUMN fans int(11) NOT NULL COMMENT '虚拟会员粉丝数' AFTER follows;
