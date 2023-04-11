DROP TABLE IF EXISTS `cmf_users_charge`;
CREATE TABLE `cmf_users_charge` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `touid` int(11) NOT NULL DEFAULT '0' COMMENT '充值对象ID',
  `money` decimal(11,4) NOT NULL COMMENT '充值金额',
  `rnb_money` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '人民币金额',
  `coin` int(11) NOT NULL DEFAULT '0' COMMENT '钻石数',
  `coin_give` int(11) NOT NULL DEFAULT '0' COMMENT '赠送钻石数',
  `orderno` varchar(50) NOT NULL DEFAULT '' COMMENT '商家订单号',
  `trade_no` varchar(100) NOT NULL DEFAULT '' COMMENT '三方平台订单号',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:待支付，2支付成功，3支付失败',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '支付类型1：线上支付2，线下支付',
  `ambient` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付环境',
  `tenant_id` bigint(20) DEFAULT NULL,
  `actual_money` decimal(11,4) NOT NULL DEFAULT '0.0000' COMMENT '实际到账金额（平台获得金额)',
  `upstream_service_money` decimal(11,4) NOT NULL DEFAULT '0.0000' COMMENT '上游手续费',
  `upstream_service_rate` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '上游手续费比例(上游费率)',
  `rate` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '汇率(支付币种换人民币汇率)',
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '支付渠道',
  `account_channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '支付子渠道',
  `updatetime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


CREATE TABLE `cmf_channel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `channel_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '''''' COMMENT '渠道名称',
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '渠道编号',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '渠道类型 1：线上2：线下',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 1：开启 0禁用',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='支付渠道表';

CREATE TABLE `cmf_channel_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道id',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '三方名称',
  `mer_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '商户id',
  `secret_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '密钥',
  `account_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '支付渠道编码',
  `url` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '请求地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1启用 2 禁用',
  `sort` tinyint(4) NOT NULL COMMENT '排序',
  `add_time` int(11) NOT NULL DEFAULT '0',
  `rate` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '渠道汇率',
  `service_charge` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '上游手续费',
  `tenant_id` int(11) NOT NULL DEFAULT '0' COMMENT '租戶id',
  `currency` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '人民币' COMMENT '支付币种',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
