ALTER TABLE `cmf_users_coinrecord` ADD `currency_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 充值币种（金币）2直播消费币种（钻石）';
ALTER TABLE `cmf_users_coinrecord` ADD `proportion` tinyint(4) NOT NULL DEFAULT '1' COMMENT '金币砖石比例';
ALTER TABLE `cmf_users_coinrecord` ADD `diamonds_sum` float(16,3) NOT NULL DEFAULT '0.000' COMMENT '砖石数量，如果消费的金币，此值为空';

 ALTER TABLE cmf_users_coinrecord DROP currency_type;
 ALTER TABLE cmf_users_coinrecord DROP proportion;
  ALTER TABLE cmf_users_coinrecord DROP diamonds_sum;
