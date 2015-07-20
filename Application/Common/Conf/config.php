<?php
return array(
	//'配置项'=>'配置值'
   'URL_MODEL' => '2',




    //模版配置
    'TMPL_PARSE_STRING'         => array(
        //amaze ui前端框架模版配置
        '__STATIC__'            => __ROOT__ . '/Public/assets',
        '__STATIC_CSS__'        => __ROOT__ . '/Public/assets/css/',
        '__STATIC_I__'          => __ROOT__ . '/Public/assets/i/',
        '__STATIC_JS__'         => __ROOT__ . '/Public/assets/js/',
        '__STATIC_FONTS__'      => __ROOT__ . '/Public/assets/fonts/',
        '__STATIC_IMG__'        => __ROOT__ . '/Public/assets/img/',

        //semantic ui 前端框架模版配置
        '__SEM_IMG__'           => __ROOT__ . '/Public/public/images/',
        '__SEM_CSS__'           => __ROOT__ . '/Public/public/css/',
        '__SEM_FONTS__'         => __ROOT__ . '/Public/public/fonts/',
        '__SEM_JS__'            => __ROOT__ . '/Public/public/javascript/',

        //


        //log4php一些配置参数
        'LOG4PHP_FILE_PATH'     =>__ROOT__.'/log4php/',
        'LOG4PHP_CONFIG_PATH'   =>__ROOT__.'/Application/Common/Conf/',



    ),
);

