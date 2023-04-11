ALTER TABLE cmf_video  ADD  `download_address` varchar(1000) NOT NULL DEFAULT '' COMMENT '视频下载地址';
ALTER TABLE cmf_video_long  ADD  `download_address` varchar(1000) NOT NULL DEFAULT '' COMMENT '视频下载地址';
ALTER TABLE cmf_video  ADD  `is_downloadable` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0等待上传成功，1上传成功，2上传失败,3文件不存在';
ALTER TABLE cmf_video_long  ADD  `is_downloadable` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0等待上传成功，1上传成功，2上传失败,3文件不存在';

ALTER TABLE cmf_video  ADD  `filestorekey` varchar(100) NOT NULL DEFAULT '' COMMENT '用户请求请求视频下载地址';
ALTER TABLE cmf_video_long  ADD  `filestorekey` varchar(100) NOT NULL DEFAULT '' COMMENT '用户请求请求视频下载地址';



ALTER TABLE cmf_welfare  ADD  `original_integral` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '原积分';