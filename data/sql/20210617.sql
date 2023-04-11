ALTER TABLE cmf_channel ADD  icon VARCHAR(100) NOT NULL DEFAULT '' COMMENT '图标' ;
ALTER TABLE cmf_channel ADD  is_virtual tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否是虚拟币 1：是 0 不是',

CREATE TABLE `cmf_offlinepay` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '三方名称',
  `reception_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '前台显示名称',
  `bank_branch` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '开户支行',
  `bank_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '银行名称，如是虚拟币就未站点名称',
  `bank_number` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '支付地址 如银卡卡号：虚拟币地址',
  `min_amount` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '最新金额',
  `max_amount` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '最大金额',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1启用 0 禁用',
  `qr_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `addtime` int(11) NOT NULL DEFAULT '0',
  `tenant_id` int(11) NOT NULL COMMENT '租户id',
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道id',
  `service_charge` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '服务费',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='线下支付配置';
