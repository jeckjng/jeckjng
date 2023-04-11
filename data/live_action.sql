ALTER TABLE `exp_level` CHANGE `updated_at` `updated_at` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT ''更新时间'';
ALTER TABLE `exp_level` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `exp_level` CHANGE `created_at` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间';
ALTER TABLE `exp_level_anchor` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `exp_level_anchor` CHANGE `updated_at` `updated_at` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间';
ALTER TABLE `exp_level_anchor` CHANGE `created_at` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间';
ALTER TABLE `live_noble_level` ADD UNIQUE `UIDX` (`tenant_id`, `level`);
ALTER TABLE `live_noble_users` CHANGE `purchase_time` `purchase_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `live_noble_users` CHANGE `created_at` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `live_noble_users` CHANGE `updated_at` `updated_at` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `exp_level` CHANGE `operated_by` `operated_by` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '操作者';
ALTER TABLE `exp_level_anchor` CHANGE `operated_by` `operated_by` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '操作者';
ALTER TABLE `live_noble_level` CHANGE `operated_by` `operated_by` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '操作者';


ALTER TABLE `widget_car` ADD `tenant_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`;
ALTER TABLE `widget_car` ADD INDEX `tenant_id` (`id`);
ALTER TABLE `widget_car` CHANGE `sort` `sort` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序';
ALTER TABLE `widget_car` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '名字';
ALTER TABLE `widget_car` CHANGE `type` `type` INT(10) UNSIGNED NOT NULL COMMENT '坐骑类型(贵族专属: 0.否，1.是)';
ALTER TABLE `widget_car` CHANGE `slogan` `slogan` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '口号，进场话术';
ALTER TABLE `widget_car` CHANGE `image` `image` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '图片';
ALTER TABLE `widget_car` CHANGE `image_small` `image_small` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '小图';
ALTER TABLE `widget_car` CHANGE `price` `price` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '价格';
ALTER TABLE `widget_car` CHANGE `swf` `swf` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '动画链接';
ALTER TABLE `widget_car` CHANGE `swf_time` `swf_time` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '动画时间';
ALTER TABLE `widget_car` CHANGE `extra` `extra` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '{}' COMMENT '附加参数';
ALTER TABLE `widget_car` CHANGE `operated_by` `operated_by` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '操作人';
ALTER TABLE `widget_car` CHANGE `updated_at` `updated_at` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间';
ALTER TABLE `widget_car` CHANGE `created_at` `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间';

ALTER TABLE `widget_car` CHANGE `name` `name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '名字';
ALTER TABLE `widget_car` DROP INDEX `tenant_id`, ADD UNIQUE `UIDX` (`tenant_id`, `name`) USING BTREE;

ALTER TABLE `widget_car` CHANGE `name` `name` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名字';

ALTER TABLE `widget_car` CHANGE `tenant_id` `tenant_id` INT(11) UNSIGNED NOT NULL COMMENT '租户ID';
ALTER TABLE `widget_car_user_info` ADD `tenant_id` INT(11) UNSIGNED NOT NULL COMMENT '租户ID' FIRST;
ALTER TABLE `widget_car` CHANGE `price` `price` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '价格';

ALTER TABLE `widget_car` ADD `origin_table_id` INT(11) NOT NULL DEFAULT '0' COMMENT '原数据库表ID' AFTER `extra`;

ALTER TABLE `live_noble_level` ADD `exclu_car_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '专属坐骑ID' AFTER `enable_exclusive_car`,
ADD `exclu_car_nobleicon` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '贵族内图标' AFTER `exclu_car_id`;
ALTER TABLE `live_noble_level` CHANGE `exclu_car_id` `exclu_car_id` INT(11) UNSIGNED NOT NULL COMMENT '专属坐骑ID';
ALTER TABLE `live_noble_level` CHANGE `exclu_car_nobleicon` `exclu_car_nobleicon` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '贵族内图标';
ALTER TABLE `live_noble_level` CHANGE `name_color` `name_color` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '贵族名字颜色';
ALTER TABLE `live_noble_level` CHANGE `price` `price` DECIMAL(20,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '价格';
ALTER TABLE `live_noble_level` CHANGE `renewal_price` `renewal_price` DECIMAL(20,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '续费价格';
ALTER TABLE `live_noble_level` CHANGE `medal` `medal` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '勋章';
ALTER TABLE `live_noble_level` CHANGE `avatar_frame` `avatar_frame` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '头像框';
ALTER TABLE `live_noble_level` CHANGE `background_skin_of_public_chat` `background_skin_of_public_chat` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '公聊背景皮肤';
ALTER TABLE `live_noble_level` CHANGE `upgrade_speed` `upgrade_speed` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '升级加速率（%）';
ALTER TABLE `live_noble_level` CHANGE `enable_special_effect` `enable_special_effect` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '特效开关: 0.否，1.是';
ALTER TABLE `live_noble_level` CHANGE `special_effect_swf` `special_effect_swf` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '特效swf动画';
ALTER TABLE `live_noble_level` CHANGE `special_effect_swf_time` `special_effect_swf_time` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '特效swf动画时长';
ALTER TABLE `live_noble_level` CHANGE `enable_golden_light_of_entry_room` `enable_golden_light_of_entry_room` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '进入房间的金光特效开关: 0.否，1.是';
ALTER TABLE `live_noble_level` CHANGE `enable_stealth_of_entry_room` `enable_stealth_of_entry_room` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '进入房间隐身: 0.否，1.是';
ALTER TABLE `live_noble_level` CHANGE `enable_exclusive_custom_services` `enable_exclusive_custom_services` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开启专属客服: 0.否，1.是';
ALTER TABLE `live_noble_level` CHANGE `enable_exclusive_car` `enable_exclusive_car` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开启专属坐骑: 0.否，1.是';
ALTER TABLE `live_noble_level` CHANGE `enable_stealth_of_ranking` `enable_stealth_of_ranking` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开启排行榜隐身: 0.否，1.是';
ALTER TABLE `live_noble_level` CHANGE `enable_prevent_mute` `enable_prevent_mute` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开启防禁言: 0.否，1.是';
ALTER TABLE `live_noble_level` CHANGE `enable_broadcast` `enable_broadcast` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开启广播: 0.否，1.是';
ALTER TABLE `live_noble_level` CHANGE `gift_of_first_active` `rebate_of_first_active` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '首次激活贵族返利';
ALTER TABLE `live_noble_level` CHANGE `rebate_of_first_active` `rebate_of_first_active` DECIMAL(20,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '首次激活贵族返利';
ALTER TABLE `live_noble_level` CHANGE `gift_of_renewal` `rebate_of_renewal` DECIMAL(20,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '续费贵族返利';

ALTER TABLE `widget_car` CHANGE `price` `price` DECIMAL(20,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '价格';
ALTER TABLE `widget_car` CHANGE `swf_time` `swf_time` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '动画时间';
ALTER TABLE `widget_car` CHANGE `origin_table_id` `origin_table_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '原数据库表ID';





























