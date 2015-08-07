<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/17
 * Time: 上午11:50
 */

use Service\Model\HospitalModel;

function time_stamp_to_str($format, $time_stamp){
    if(empty($time_stamp) || $time_stamp == 0){
        return null;
    }
    return $time_str = date($format, $time_stamp);

}


function return_db_operation_result($db_obj){
    $result = array();
    $error_msg = $db_obj->getDbError();
    if(empty($error_msg)){
        $result = array(
            'code' => '200'
        );
    }else{
        $result = array(
            'code' => 500,
            'error_msg' => $error_msg
        );
    }

    return $result;
}

//解析支付列表用户不友好字段
function parse_order_collection($collection){
    $collection_type_conf = C('ORDER_COLLECTION.COLLECTION_TYPE');
    $payment_type_conf = C('ORDER_COLLECTION.PAYMENT_TYPE');
    $collection_status_conf = C('ORDER_COLLECTION.COLLECTION_STATUS');
    $bill_status_conf = C('ORDER_COLLECTION.BILL_STATUS');

    $collection['collection_type_str'] = $collection_type_conf[$collection['collection_type']];
    $collection['payment_type_str'] = $payment_type_conf[$collection['payment_type']];
    $collection['collection_status_str'] = $collection_status_conf[$collection['collection_status']];
    $collection['bill_status_str'] = $bill_status_conf[$collection['bill_status']];

    $collection['add_time'] = time_stamp_to_str('Y-m-d H:i:s', $collection['add_time']);
    $collection['payment_time'] = time_stamp_to_str('Y-m-d H:i:s', $collection['payment_time']);

    return $collection;

}

//解析工作人员信息
function parse_worker_info($worker){
    $HospitalM = new HospitalModel();
    $worker['stationary_str'] = $HospitalM->getHospitalById($worker['worker_stationary']);
    $sexM = C('SEX');
    $educationM = C('EDUCATION');
    $serviceTypeM = C('SERVICE_TYPE');
    $worker['worker_sex_str'] = $sexM[$worker['worker_sex']];
    $worker['worker_education_str'] = $educationM[$worker['worker_education']];
    $worker['worker_service_str'] = $serviceTypeM[$worker['worker_service']];

    return $worker;
}



function get_wechat_user_info(){
    //判断是否缓存了用户信息

    //是，不需要发送请求
    //否
    //缓存用户信息

}