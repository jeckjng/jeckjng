ALTER TABLE cmf_video_watch_record  ADD updatetime int(11) NOT NULL DEFAULT '0' COMMENT '最后观看时间';
ALTER TABLE cmf_video_watch_record  ADD  `watch_count` tinyint(4) DEFAULT '1' COMMENT '观看次数';
ALTER TABLE cmf_video_watch_record  ADD    `viewing_duration` int(10) NOT NULL DEFAULT '0' COMMENT '观看时长（单位秒）';
alter table cmf_users_cashrecord modify votes  decimal(12,4) DEFAULT '0.0000';
alter table cmf_users_cashrecord modify coin_number  decimal(12,4) DEFAULT '0.0000';