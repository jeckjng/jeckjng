ALTER TABLE cmf_video_long ADD  performer VARCHAR(100) NOT NULL DEFAULT '' COMMENT '演员' ;

ALTER TABLE cmf_video_long ADD  is_performer tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否设置演员 1 有 0 没有' ;

