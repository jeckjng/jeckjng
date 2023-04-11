ALTER TABLE `cmf_vip_grade`
ADD COLUMN `uplode_video_num`  int(11) NOT NULL DEFAULT 0 COMMENT '上传多少内的视频数量有奖励' AFTER `is_super_member`,
ADD COLUMN `uplode_video_amount`  decimal(12,2) NOT NULL DEFAULT 0 COMMENT '规定数量内上传视频奖励' AFTER `uplode_video_num`;


ALTER TABLE `cmf_users_jurisdiction`
MODIFY COLUMN `bar_number`  int(11) NOT NULL DEFAULT 10 COMMENT '发帖数量' AFTER `watchnum_ad`,
MODIFY COLUMN `bar_slice_number`  int(11) NOT NULL DEFAULT 10 COMMENT '发帖评论数量' AFTER `bar_number`;

