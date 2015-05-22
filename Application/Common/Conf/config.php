<?php
return array(
	//'配置项'=>'配置值'
   'URL_MODEL' => '2',

    //模版配置
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/assets',
        '__STATIC_CSS__' => __ROOT__ . '/Public/assets/css/',
        '__STATIC_I__' => __ROOT__ . '/Public/assets/i/',
        '__STATIC_JS__' => __ROOT__ . '/Public/assets/js/',
        '__STATIC_FONTS__' => __ROOT__ . '/Public/assets/fonts/',
        '__STATIC_IMG__' => __ROOT__ . '/Public/assets/img/',

        //log4php一些配置参数
        'LOG4PHP_FILE_PATH'=>__ROOT__.'/log4php/',
        'LOG4PHP_CONFIG_PATH'=>__ROOT__.'/Application/Common/Conf/',

    ),
);

