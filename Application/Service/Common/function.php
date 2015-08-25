<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/17
 * Time: 上午11:50
 */

use Service\Model\HospitalModel;

use JsSdk;
use UnifiedOrderPub;
use WxPayConfPub;
use WxPayUnifiedOrder;
use WxPayConfig;
use WxPayApi;
use WxPayJsApiPay;
use WxPayException;

require_once LIB_PATH."Org/Util/WxpayAPI_php_v3/lib/WxPay.Data.php";
require_once LIB_PATH."Org/Util/WxpayAPI_php_v3/lib/WxPay.Config.php";
require_once LIB_PATH."Org/Util/WxpayAPI_php_v3/lib/WxPay.Api.php";

//define('WECHATSDK_PATH', require LIB_PATH.'Org/Util/wechat-master/');
define('APPID', 'wx86b3751ad43f4062');
define('APPSECRET', 'f52a587fefed285df9244f310eee8a34');
require_once LIB_PATH.'Org/Util/wechat-master/autoload.php';

use Overtrue\Wechat\Notice;   //微信模板信息

/**
 * 发送模版信息
 *$userId string     用户微信openid
 *$templateId string 微信模版id
 * $url              模版跳转链接，可以设置为空
 * $data array       模板数据，格式如下：
 *                   array("first" => "恭喜你购买成功！")
 *                   或
 *                   array("first" => array("value" => "恭喜你购买成功！", "color" => '#555555'))
 */
function templateSend($userId, $templateId, $url, $data)
{
    $appid = C('SERVICE.APPID');
    $secret = C('SERVICE.SECRET');
    $notice = new Notice($appid, $secret);
    $res  = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
    return $res;
}


function GetJsApiParameters($UnifiedOrderResult)
{
    ini_set('date.timezone','Asia/Shanghai');
    if(!array_key_exists("appid", $UnifiedOrderResult)
        || !array_key_exists("prepay_id", $UnifiedOrderResult)
        || $UnifiedOrderResult['prepay_id'] == "")
    {
        throw new WxPayException("参数错误");
    }
    $jsapi = new WxPayJsApiPay();
    $jsapi->SetAppid($UnifiedOrderResult["appid"]);
    $timeStamp = time();
    $jsapi->SetTimeStamp("$timeStamp");
    $jsapi->SetNonceStr(WxPayApi::getNonceStr());
    $jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
    $jsapi->SetSignType("MD5");
    $jsapi->SetPaySign($jsapi->MakeSign());
//    $parameters = json_encode($jsapi->GetValues());
    $parameters = $jsapi->GetValues();
    return $parameters;
}

function get_pay_sign_info($body, $fee, $out_trade_no, $openid, $attach='order-collection', $notify_url='http://subcribe.ecare-easy.com/Service/wechat/pay_notify'){
    $input = new WxPayUnifiedOrder();
    $input->SetBody($body);
    $input->SetAttach($attach);
    $input->SetOut_trade_no($out_trade_no);
    $input->SetTotal_fee($fee);

    $input->SetNotify_url($notify_url);
    $input->SetTrade_type("JSAPI");
    $input->SetOpenid($openid);

    $order = WxPayApi::unifiedOrder($input);

    $signInfo = GetJsApiParameters($order);

    return $signInfo;
}


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
    $worker['worker_icon'] = C('ICON_DIR').$worker['worker_icon'];
    return $worker;
}


function parse_customer_info($customer_info){
    $sexM = C('SEX');
    $serviceTypeM = C('SERVICE_TYPE');
    $customerServiceLevelM = C('CUSTOMER_SERVICE_LEVEL');

    $customer_info['customer_sex_str'] = $sexM[$customer_info['customer_sex']];
    $customer_info['customer_service_type_str'] = $serviceTypeM[$customer_info['customer_service_type']];
    $customer_info['customer_service_level_str'] = $customerServiceLevelM[$customer_info['customer_service_level']];

    $hospitalM = new HospitalModel();

    $customer_info['customer_hospital_str'] = $hospitalM->getHospitalNameById($customer_info['customer_hospital']);
    $customer_info['customer_hospital_department_str'] = $hospitalM->getHospitalNameById($customer_info['customer_hospital_department']);


    return $customer_info;
}


function parse_appointment($appointment){
    $serviceTypeM = C('SERVICE_TYPE');
    $stateM = C('APPOINTMENT_STATE');
    $appointment['service_type_str'] = $serviceTypeM[$appointment['service_type']];
    $appointment['state_str'] = $stateM[$appointment['state']];
    $appointment['create_time_str'] = time_stamp_to_str('Y-m-d H:i:s', $appointment['create_time']);
    return $appointment;
}

function get_wechat_user_info(){
    //判断是否缓存了用户信息

    //是，不需要发送请求
    //否
    //缓存用户信息

}