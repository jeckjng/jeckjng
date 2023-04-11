ALTER TABLE `cmf_channel` drop COLUMN `currency_name`;
ALTER TABLE `cmf_channel` drop COLUMN `currency_code`;
ALTER TABLE `cmf_channel` drop COLUMN `rate`;
ALTER TABLE `cmf_channel` drop COLUMN `is_virtual`;
ALTER TABLE `cmf_channel` drop COLUMN `icon`;
ALTER TABLE `cmf_channel_account` drop COLUMN `rate`;
ALTER TABLE `cmf_channel_account` drop COLUMN `currency`;
ALTER TABLE cmf_channel  ADD  coin_id int(11) NOT NULL DEFAULT '0' COMMENT '币种id';

CREATE TABLE `cmf_rate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '币种名称',
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '币种简称',
  `rate` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT '汇率',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1启用：2禁用',
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
  `is_virtual` int(4) NOT NULL DEFAULT '0' COMMENT '是否为虚拟币，1是 0不是',
  `sort` tinyint(4) NOT NULL DEFAULT '1' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `cmf_users` drop COLUMN `frozen_money`;
ALTER TABLE `cmf_users` drop COLUMN `withdrawable_money`;
