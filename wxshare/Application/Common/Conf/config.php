<?php

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Home',
    'MODULE_DENY_LIST'   => array('Common', 'User'),
    'MODULE_ALLOW_LIST'  => array('Home','Admin'), 


    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    /* 数据库配置 */
    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => '172.18.120.50', // 服务器地址
    'DB_NAME'   => 'cs88_dev', // 数据库名
    'DB_USER'   => 'cs88', // 用户名
    'DB_PWD'    => 'Yh3yxyz@',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'cmf_', // 数据库表前缀
		
	'REDIS_HOST'=>'172.18.120.50',
	'REDIS_AUTH'=>'Yh3yxyz@',
    'REDIS_PORT'=>'6379',
    'REDIS_DBINDEX'=>'1'		

);
