ALTER TABLE cmf_tenant_config  ADD  `apk_forced_update` tinyint(4) NOT NULL DEFAULT '0' COMMENT '安卓是否需要强制更新 1 需要 0 不需要';
ALTER TABLE cmf_tenant_config  ADD  `ipa_forced_update` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'ios是否需要强制更新 1 需要 0 不需要';

CREATE TABLE `cmf_performer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '演员名称',
  `avatar` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '头像',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `desc` text COLLATE utf8_unicode_ci NOT NULL COMMENT '描述',
  `popularity` int(11) NOT NULL DEFAULT '0' COMMENT '人气',
  `age` tinyint(4) NOT NULL DEFAULT '0',
  `label` int(11) NOT NULL DEFAULT '0' COMMENT '标签',
  `region` varchar(50) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '国家地区',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


