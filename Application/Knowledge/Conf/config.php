<?php
return array(
	//'配置项'=>'配置值'
	//数据库连接
    //数据库配置
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => 'yijianyi.mysql.rds.aliyuncs.com', // 服务器地址
    'DB_NAME'   => 'yijiayi', // 数据库名
    'DB_USER'   => 'yijianyi', // 用户名
    'DB_PWD'    => 'yijianyi2015', // 密码
    'DB_PORT'   => 3306, // 端口
    'DB_PREFIX' => '', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集

    //模版配置
    'TMPL_PARSE_STRING' => array(
        '__STATIC_CSS__' => __ROOT__ . '/Public/assets/css/',
        '__STATIC_I__' => __ROOT__ . '/Public/assets/i/',
        '__STATIC_JS__' => __ROOT__ . '/Public/assets/js/',
        '__STATIC_FONTS__' => __ROOT__ . '/Public/assets/fonts/',
        '__STATIC_IMG__' => __ROOT__ . '/Public/assets/img/',
    ),
);