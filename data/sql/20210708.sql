ALTER TABLE cmf_vip  ADD `give_data` int(11) NOT NULL DEFAULT '0' COMMENT '赠送天数';
ALTER TABLE cmf_vip  ADD  `is_super_member` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是超级会员 1 是';

ALTER TABLE cmf_users_vip  ADD `vip_id` int(10) NOT NULL DEFAULT '1' COMMENT '会员等级';

CREATE TABLE `cmf_welfare` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `desc` text COLLATE utf8_unicode_ci NOT NULL COMMENT '描述',
  `integral` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '所需积分',
  `img` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `frequency` int(10) NOT NULL DEFAULT '0' COMMENT '兑换次数',
  `tenant_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1启用 0禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='福利表';

CREATE TABLE `cmf_welfare_exchange_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `welfare_id` int(11) NOT NULL DEFAULT '0',
  `consignee` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '收货人',
  `phone` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '收货地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1申请中：2兑换成功，3兑换失败',
  `express_order` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `tenant_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;