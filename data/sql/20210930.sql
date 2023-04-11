ALTER TABLE cmf_video_long  ADD `hot_searches` int(11) NOT NULL DEFAULT '0' COMMENT '热搜次数';
ALTER TABLE cmf_video_long  ADD  `first_watch_time` int(11) NOT NULL DEFAULT '0' COMMENT '第一次观看时间';
ALTER TABLE cmf_video_long  ADD  `last_watch_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次观看时间';
ALTER TABLE cmf_video_long  ADD  `total_warch_time` int(11) NOT NULL DEFAULT '0' COMMENT '全部用户观看时长（预留字段。暂未用到）';


ALTER TABLE cmf_video  ADD `hot_searches` int(11) NOT NULL DEFAULT '0' COMMENT '热搜次数';
ALTER TABLE cmf_video  ADD  `first_watch_time` int(11) NOT NULL DEFAULT '0' COMMENT '第一次观看时间';
ALTER TABLE cmf_video  ADD  `last_watch_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次观看时间';
ALTER TABLE cmf_video  ADD  `total_warch_time` int(11) NOT NULL DEFAULT '0' COMMENT '全部用户观看时长（预留字段。暂未用到）';



