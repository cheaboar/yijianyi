<?php
return array(
	//'配置项'=>'配置值'
	//数据库连接
	'DB_TYPE'   => 'mysql', // 数据库类型
	'DB_HOST'   => 'wind1748.mysql.rds.aliyuncs.com', // 服务器地址
	'DB_NAME'   => 'cheng', // 数据库名
	'DB_USER'   => 'wind', // 用户名
	'DB_PWD'    => 'wind1748', // 密码
	'DB_PORT'   => 3306, // 端口
	'DB_PREFIX' => 'cheng.', // 数据库表前缀
	'DB_CHARSET'=> 'utf8', // 字符集

    //E家照护配置信息
    'APP_ID'=>'wxb468b850fc76d278',//订阅号的id
    'APP_SECRET'=>'b4843edb87fc8e3f0b1034bf946c5189',
    'ENCODING_AESKEY'=>'OvXZAh5akCf9oxrwgLbsu4a61gw4KmuVdGwZzxa1Cjx',
    'TOKEN'=>'Yijianyi',

    //log4php
    'LOG4PHP_CONFIG_PATH'=>__ROOT__.'/Application/Common/Conf/log4php_config.xml',

    'LAYOUT_ON'=>true,
    'LAYOUT_NAME'=>'Layout/layout',
);