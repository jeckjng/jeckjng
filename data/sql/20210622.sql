ALTER TABLE cmf_video_long ADD  duration VARCHAR(100) NOT NULL DEFAULT '' COMMENT '时长' ;
ALTER TABLE cmf_video ADD  duration VARCHAR(100) NOT NULL DEFAULT '' COMMENT '时长' ;

alter  table cmf_video_watch_record  modify  column classify  VARCHAR(100) DEFAULT '' COMMENT '视频分类';
alter  table cmf_video_watch_record  change classify  label varchar(100)DEFAULT '' COMMENT '视频标签';