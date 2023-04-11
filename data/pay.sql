DROP TABLE IF EXISTS `cmf_channel`;
CREATE TABLE `cmf_channel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `channel_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '''''' COMMENT '渠道名称',
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '渠道编号',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '渠道类型 1：线上2：线下',
  `currency_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '人民币' COMMENT '币种名称',
  `currency_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'cny' COMMENT '币种简称',
  `rate` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '汇率',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 1：开启 0禁用',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='支付渠道表';

DROP TABLE IF EXISTS `cmf_channel_account`;
CREATE TABLE `cmf_channel_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道id',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '三方名称',
  `mer_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '商户id',
  `secret_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '密钥',
  `account_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '支付渠道编码',
  `url` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '请求地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1启用 2 禁用',
  `sort` tinyint(4) NOT NULL DEFAULT '1' COMMENT '排序',
  `add_time` int(11) NOT NULL DEFAULT '0',
  `rate` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '渠道汇率',
  `service_charge` decimal(10,4) DEFAULT '0.0000' COMMENT '上游手续费',
  `tenant_id` int(11) NOT NULL DEFAULT '0' COMMENT '租戶id',
  `currency` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '人民币' COMMENT '支付币种',
  `notify_ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT '127.0.0.1' COMMENT '回调ip',
  `float_amount` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '浮动金额',
  `white_ip` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '127.0.0.1' COMMENT 'ip白名单',
  `select_amount` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '快速输入金额',
  `reception_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '前台显示名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
