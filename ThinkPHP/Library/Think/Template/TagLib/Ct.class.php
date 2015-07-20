<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 陈奇波
// +----------------------------------------------------------------------
namespace Think\Template\TagLib;
use Think\Template\TagLib;
defined('THINK_PATH') or exit();
/**
 * 自定义标签库解析类
 */
class Ct extends TagLib {

    // 标签定义
    protected $tags   =  array(
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'nav_test'       =>  array('attr'=>'name'),

    );

    public function _for_test($tag, $content){
        $parseStr = '<div>'.$tag['name'].'</div>';
        return $parseStr;
    }
    /**
     * php标签解析
     * @access public
     * @param array $tag 标签属性
     * @param string $content  标签内容
     * @return string
     */
    public function _nav_test($tag, $content) {
        $parseStr = '<div>'.$tag['name'].$content.'</div>';
        return $parseStr;
    }



}
