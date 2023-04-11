ALTER TABLE cmf_users_agent  ADD  `user_login` varchar(100) NOT NULL DEFAULT '' COMMENT '用户名';
alter table cmf_users_cashrecord modify money  decimal(12,4) DEFAULT '0.0000';
ALTER TABLE cmf_users_cashrecord  ADD  `currency_code` varchar(20) NOT NULL DEFAULT '' COMMENT '币种简称';