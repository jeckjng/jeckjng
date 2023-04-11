ALTER TABLE cmf_users_charge ADD  user_real_name VARCHAR(50) NOT NULL DEFAULT '' COMMENT '姓名' ;

ALTER TABLE cmf_video_label  ADD  sort TINYINT(4) NOT NULL DEFAULT 1 COMMENT '排序' ;
ALTER TABLE cmf_video_label  ADD  type TINYINT(4) NOT NULL DEFAULT 0 COMMENT '1:设为火热标签0：普通' ;

ALTER TABLE cmf_video_label_long  ADD  sort TINYINT(4) NOT NULL DEFAULT 1 COMMENT '排序' ;
ALTER TABLE cmf_video_long_classify  ADD  sort TINYINT(4) NOT NULL DEFAULT 1 COMMENT '排序' ;

