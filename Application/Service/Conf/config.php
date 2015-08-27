<?php

return array(
	//'配置项'=>'配置值'
	//数据库连接
	'DB_TYPE'   => 'mysql', // 数据库类型
	'DB_HOST'   => 'yijianyi.mysql.rds.aliyuncs.com', // 服务器地址
	'DB_NAME'   => 'yijianyi', // 数据库名
	'DB_USER'   => 'yijianyi', // 用户名
	'DB_PWD'    => 'yijianyi2015', // 密码
	'DB_PORT'   => 3306, // 端口
	'DB_PREFIX' => 'yjy_', // 数据库表前缀
	'DB_CHARSET'=> 'utf8', // 字符集
    'READ_DATA_MAP'=>true,//字段自动映射

    //头像路径
    'ICON_DIR' => 'http://master.ecare-easy.com/upload/ico/',

    //数据库表字段一些常量对应的配置
    'SEX' => array(
        1 => '男',
        2 => '女',
    ),
    'SEX_R' => array(
        '男' => 1,
        '女' => 2,
    ),

    'EDUCATION' => array(
        '1' => '本科',
        '2' => '大专',
        '3' => '中专',
        '4' => '高中',
        '5' => '初中',
        '6' => '小学',
        '7' => '不识字',
    ),
    'EDUCATION_R' => array(
         '本科' =>'1',
         '大专'=>'2',
         '中专' =>'3',
         '高中' =>'4',
         '初中' =>'5',
         '小学' =>'6',
        '不识字' => '7',
    ),
    'SERVICE_TYPE' => array(
        '1' => '居家照护',
        '2' => '医疗陪护',
        '3' => '陪诊',
        '4' => '月嫂'
    ),


    'SERVICE_TYPE_R' => array(
         '居家照护'=> '1',
         '医疗陪护'=> '2',
         '陪诊'=> '3',
         '月嫂' =>'4',
    ),

    'ORDER' => array(
        'STATUS' => array(
            '1' => '未确认', //支持查看，指派护工，取消，编辑
            '2' => '进行中', //支持查看，编辑，更换护工，收款
            '3' => '结算中', //支持查看
            '4' => '已完成', //支持查看
            '5' => '已取消',  //支持查看，删除
            '6' => '已确认'//已经确认
        ),
        'SATUS_R' => array(
            '未确认' => '1', //支持查看，指派护工，取消，编辑
            '进行中' => '2', //支持查看，编辑，更换护工，收款
            '结算中' => '3', //支持查看
            '已完成' => '4', //支持查看
            '已取消' => '5',  //支持查看，删除
            '已确认' => '6'
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

    'APPOINTMENT_STATE' =>array(
        '-1' => '删除',  //用户取消
        '1000' => '未确认',//后台未确认
        '2000' => '已确认', //后台已确认
        '3000' => '取消', //后台取消
    ),

    'APPOINTMENT_STATE_R' =>array(
        '删除'   =>   '-1' , //用户取消
        '未确认' => '1000',//后台未确认
        '已确认' => '2000', //后台已确认
        '取消'   => '3000' //后台取消
    ),



    'ORDER_COLLECTION' => array(
        //收款类型
        'COLLECTION_TYPE' => array(
            '1' => '预收款',
            '2' => '结算',
        ),
        'COLLECTION_TYPE_R' => array(
            '预收款' => '1',
            '结算' => '2',
        ),
        //收款状态
        'COLLECTION_STATUS'=> array(
            '1' => '未支付',
            '2' => '已支付',
            '3' => '已取消',
        ),
        'COLLECTION_STATUS_R'=> array(
            '未支付' => '1',
            '已支付' => '2',
            '已取消' => '3',
        ),


        //付款方式
        'PAYMENT_TYPE' => array(
            '1' => '现金',
            '2' => '银行卡',
            '3' => '微信支付',
        ),
        'PAYMENT_TYPE_R' => array(
            '现金' => '1',
            '银行卡' => '2',
            '微信支付' => '3',
        ),



        //票据状态
        'BILL_STATUS'=> array(
            '1' => '未出票',
            '2' => '已出票',
        ),
        'BILL_STATUS_R'=> array(
            '未出票' => '1',
            '已出票' => '2',
        )
    ),


    'SERVICE_TYPE' => array(
        '1' => '居家照护',
        '2' => '医疗陪护',
        '3' => '陪诊',
        '4' => '月嫂'
    ),

    'CUSTOMER_SERVICE_LEVEL' => array(
        '1' => '特级护理',
        '2' => '一级护理',
        '3' => '二级护理',
        '4' => '三级护理'
    ),

    'CUSTOMER_SERVICE_LEVEL_R' => array(
        '特级护理' => '1',
        '一级护理' => '2',
        '二级护理' => '3',
        '三级护理' => '4'
    ),
    'WORKER_ORDER_STATUS' => array(
        '1' => '服务中',
        '2' => '结束',
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

    'MERCHANT' => array(
         'MCHID' => '1244739002',
	     'KEY' => 'e10adc3949ba59abbe56e057f20f883e',
    ),


    'URL_ROUTE_RULES' =>array(
//        'service_info' => 'Service/Index/service_info'

    ),

    'AUTH_URL' => 'http://master.ecare-easy.com/index.php/oa_admin/wx/getUserMsg?url=',


);