ALTER TABLE `live_dev`.`cmf_offlinepay`
    MODIFY COLUMN `bank_branch` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '开户支行',
    MODIFY COLUMN `bank_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '银行名称',
    MODIFY COLUMN `bank_number` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '银卡卡号',
    ADD COLUMN `usdt_type` varchar(255) NULL COMMENT '链类型' AFTER `pay_type`,
    ADD COLUMN `usdt_address` varchar(255) NULL COMMENT '链地址' AFTER `usdt_type`;

ALTER TABLE `live_dev`.`cmf_offlinepay` ADD COLUMN `orderno` int(11) NOT NULL DEFAULT 0 COMMENT '排序';
ALTER TABLE `live_dev`.`cmf_offlinepay` ADD COLUMN `bank_user_name` varchar(255) NULL COMMENT '持卡人姓名';


ALTER TABLE `live_dev`.`cmf_channel_account` ADD COLUMN `orderno` int(11) NOT NULL DEFAULT 0 COMMENT '排序';

INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (543, 127, 'Admin', 'Pay', 'usdtpay', '', 1, 1, '虚拟币支付', '', '', 7, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (544, 127, 'Admin', 'Pay', 'usdtadd', '', 1, 1, '添加虚拟币支付', '', '', 8, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (545, 130, 'Admin', 'Pay', 'listorders', '', 1, 0, '排序', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (546, 130, 'Admin', 'Pay', 'accountchanneledit', '', 1, 0, '编辑', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (547, 130, 'Admin', 'Pay', 'account_channel_edit_post', '', 1, 0, '编辑操作', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (548, 132, 'Admin', 'Pay', 'offlineorders', '', 1, 0, '排序', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (549, 132, 'Admin', 'Pay', 'offlinepayedit', '', 1, 0, '编辑', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (550, 132, 'Admin', 'Pay', 'offline_edit_post', '', 1, 0, '编辑操作', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (551, 543, 'Admin', 'Pay', 'usdtorders', '', 1, 0, '排序', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (552, 543, 'Admin', 'Pay', 'usdtedit', '', 1, 0, '编辑', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (553, 543, 'Admin', 'Pay', 'usdt_edit_post', '', 1, 0, '编辑操作', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (554, 543, 'Admin', 'Pay', 'usdtupstatus', '', 1, 0, '修改状态', '', '', 0, NULL);
INSERT INTO `live_dev`.`cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`, `tenant_id`) VALUES (555, 544, 'Admin', 'Pay', 'usdt_add_post', '', 1, 0, '添加操作', '', '', 0, NULL);

INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (634, 'Admin', 'admin_url', 'admin/pay/usdtpay', NULL, '虚拟币支付', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (635, 'Admin', 'admin_url', 'admin/pay/usdtadd', NULL, '添加虚拟币支付', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (636, 'Admin', 'admin_url', 'admin/pay/listorders', NULL, '排序', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (637, 'Admin', 'admin_url', 'admin/pay/accountchanneledit', NULL, '编辑', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (638, 'Admin', 'admin_url', 'admin/pay/account_channel_edit_post', NULL, '编辑操作', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (639, 'Admin', 'admin_url', 'admin/pay/offlineorders', NULL, '排序', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (640, 'Admin', 'admin_url', 'admin/pay/offlinepayedit', NULL, '编辑', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (641, 'Admin', 'admin_url', 'admin/pay/offline_edit_post', NULL, '编辑操作', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (642, 'Admin', 'admin_url', 'admin/pay/usdtorders', NULL, '排序', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (643, 'Admin', 'admin_url', 'admin/pay/usdtedit', NULL, '编辑', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (644, 'Admin', 'admin_url', 'admin/pay/usdt_edit_post', NULL, '编辑操作', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (645, 'Admin', 'admin_url', 'admin/pay/usdtupstatus', NULL, '修改状态', 1, '', NULL);
INSERT INTO `live_dev`.`cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`, `tenant_id`) VALUES (646, 'Admin', 'admin_url', 'admin/pay/usdt_add_post', NULL, '添加操作', 1, '', NULL);
