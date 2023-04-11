CREATE TABLE `cmf_channel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `channel_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '''''' COMMENT '渠道名称',
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '渠道编号',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '渠道类型 1：线上2：线下',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态 1：开启 0禁用',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='支付渠道表';



CREATE TABLE `cmf_channel_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道id',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '三方名称',
  `mer_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '商户id',
  `account_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '支付渠道编码',
  `url` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '请求地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1启用 2 禁用',
  `sort` tinyint(4) NOT NULL COMMENT '排序',
  `add_time` int(11) NOT NULL DEFAULT '0',
  `rate` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '渠道汇率',
  `service_charge` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '上游手续费',
  `tenant_id` int(11) NOT NULL DEFAULT '0' COMMENT '租戶id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# 菜单id按照测试数据库添加

INSERT INTO `cmf_menu` VALUES ('', '422', 'Admin', 'Pay', 'channellist', '', '1', '1', '渠道管理', '', '', '0', null);
INSERT INTO `cmf_menu` VALUES ('', '422', 'Admin', 'Pay', 'accountchannellist', '', '1', '1', '子渠道管理', '', '', '0', null);