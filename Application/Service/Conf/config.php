<?php

return array(
	//'配置项'=>'配置值'
	//数据库连接
	'DB_TYPE'   => 'mysql', // 数据库类型
	'DB_HOST'   => 'yijianyi.mysql.rds.aliyuncs.com', // 服务器地址
	'DB_NAME'   => 'yijiayi', // 数据库名
	'DB_USER'   => 'yijianyi', // 用户名
	'DB_PWD'    => 'yijianyi2015', // 密码
	'DB_PORT'   => 3306, // 端口
	'DB_PREFIX' => 'yjy_', // 数据库表前缀
	'DB_CHARSET'=> 'utf8', // 字符集
    'READ_DATA_MAP'=>true,//字段自动映射

    //数据库表字段一些常量对应的配置
    'SEX' => array(
        'MALE' => 1,
        'FEMALE' => 2,
    ),

    'ORDER' => array(
        'STATUS' => array(
            '1' => '未确认', //支持查看，指派护工，取消，编辑
            '2' => '进行中', //支持查看，编辑，更换护工，收款
            '3' => '结算中', //支持查看
            '4' => '已完成', //支持查看
            '5' => '已取消'  //支持查看，删除
        ),
        'SERVICE_MODE' => array(
            '1' => '一对一',
            '2' => '一对多',
            '3' => '多对一',
        ),
        'FEE_UNIT' => array(
            '1' => '月',
            '2' => '天',
            '3' => '小时',
        ),
    ),


    'SERVICE_TYPE' => array(
        '1' => '居家照护',
        '2' => '医疗陪护',
        '3' => '陪诊',
        '4' => '月嫂'
    ),


    //云片配置
    'YUNPIAN' => array(
        'APIKEY' => 'cf34160f4719430181a3d387f9dda3c8'
    ),

    'SUBSCRIBE' => array(
        'APPID' => 'wxb468b850fc76d278',
        'SECRET'=> 'b4843edb87fc8e3f0b1034bf946c5189',
        'TOKEN' => 'Yijianyi',
        'ENCODING_AES_KEY' => 'OvXZAh5akCf9oxrwgLbsu4a61gw4KmuVdGwZzxa1Cjx'
    ),
    'SERVICE' => array(
        'APPID' => 'wx86b3751ad43f4062',
        'SECRET' => 'f52a587fefed285df9244f310eee8a34',
    ),


    'URL_ROUTE_RULES' =>array(
//        'service_info' => 'Service/Index/service_info'

    ),

    'AUTH_URL' => 'http://master.ecare-easy.com/index.php/oa_admin/wx/getUserMsg?url=',


);