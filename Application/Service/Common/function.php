<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/17
 * Time: 上午11:50
 */

function test(){
    return 'test';
}

function time_stamp_to_str($format, $time_stamp){
    if(empty($time_stamp) || $time_stamp == 0){
        return null;
    }
    return $time_str = date($format, $time_stamp);

}