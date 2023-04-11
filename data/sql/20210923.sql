CREATE TABLE `cmf_vip_grade` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vip_grade` int(11) NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `is_super_member` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1是超级会员 0 不是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE cmf_users_jurisdiction  ADD     `vip_grade_id` int(11) NOT NULL DEFAULT '0';