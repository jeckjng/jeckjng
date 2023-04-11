ALTER TABLE cmf_video_long  ADD  tourist_time int(10) NOT NULL DEFAULT '0' COMMENT '游客观看时间(秒)';
ALTER TABLE cmf_platform_config  ADD  `user_agreement` text COMMENT '用户协议';
ALTER TABLE cmf_users  ADD   `integral` int(11) NOT NULL DEFAULT '0' COMMENT '积分(用于注册，及兑换福利）';
ALTER TABLE cmf_users MODIFY coin decimal(16,2) NOT NULL DEFAULT 0;

