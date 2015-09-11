<?php
/**
 * +----------------------------------------------------------
 * 生存验证码
 *+----------------------------------------------------------
 * @param int       $length  
 * @param string    $type    
 *+----------------------------------------------------------
 *@return string
 *+----------------------------------------------------------
 */
require_once LIB_PATH.'Org/Util/wechat-master/autoload.php';

use Overtrue\Wechat\Auth; //授权页面获取用户信息
use Partner\Model\LoginModel;

 function randCode($length = 5, $type = 0) {
    $arr = array(1 => "0123456789");
    if ($type == 0) {
        array_pop($arr);
        $string = implode("", $arr);
    } elseif ($type == "-1") {
        $string = implode("", $arr);
    } else {
        $string = $arr[$type];
    }
    $count = strlen($string) - 1;
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $string[rand(0, $count)];
    }
    return $code;
 }
 
 /*
*post requert
*/
function https_post($url,$data)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    if (curl_errno($curl)) {
       return 'Errno'.curl_error($curl);
    }
    curl_close($curl);
    return $result;
}

/**
 * 云片通
 * url 为服务的url地址
 * query 为请求串
 */
function sock_post($url, $query) {
    $data = "";
    $info = parse_url($url);
    $fp = fsockopen($info["host"], 80, $errno, $errstr, 30);
    if (!$fp) {
        return $data;
    }
    $head = "POST " . $info['path'] . " HTTP/1.0\r\n";
    $head .= "Host: " . $info['host'] . "\r\n";
    $head .= "Referer: http://" . $info['host'] . $info['path'] . "\r\n";
    $head .= "Content-type: application/x-www-form-urlencoded\r\n";
    $head .= "Content-Length: " . strlen(trim($query)) . "\r\n";
    $head .= "\r\n";
    $head .= trim($query);
    $write = fputs($fp, $head);
    $header = "";
    while ($str = trim(fgets($fp, 4096))) {
        $header .= $str;
    }
    while (!feof($fp)) {
        $data .= fgets($fp, 4096);
    }
    return $data;
}

/**
 * 模板接口发短信
 * apikey 为云片分配的apikey
 * tpl_id 为模板id
 * tpl_value 为模板值
 * mobile 为接受短信的手机号
 */
function tpl_send_sms($apikey, $tpl_id, $tpl_value, $mobile) {
        $url = "http://yunpian.com/v1/sms/tpl_send.json";
        $encoded_tpl_value = urlencode("$tpl_value");
        $post_string = "apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";
        return sock_post($url, $post_string);
    }

/**
 * 普通接口发短信
 * apikey 为云片分配的apikey
 * text 为短信内容
 * mobile 为接受短信的手机号
 */
function send_sms($apikey, $text, $mobile) {
    $url = "http://yunpian.com/v1/sms/send.json";
    $encoded_text = urlencode("$text");
    $post_string = "apikey=$apikey&text=$encoded_text&mobile=$mobile";
    return sock_post($url, $post_string);
}

/*
*用户是否登录检查
*传递当前网页路径以及参数，用作登录检测后的跳转
*user_session('index/login')
*/
function user_session($request_url){
    $user_id = session('authed');
    if($user_id!=''){
        return '';
    }
    else{
        if($request_url==''){
            //$request = 'usercenter/user_center';
            $url = 'Location:/Admin/index/login/usercenter/user_center';

        }
        else{
            //$request = $request_url; 
            $url = 'Location:/Admin/index/login/?pre_url='.$request_url;
        }
       
        header($url);
        exit;
    }
}

//获取用户的id
function user_id(){
    $user_id = session('authed');
    return $user_id;
}

//获取用户的上一级id
function parent_id(){
    //$parent_id = session('id');
    return session('id');
}

//用户登录状态的销毁
function unset_session(){
    session('authed',null); 
}


//分享连接、用户的上线
//传递的参数，当session存在时，把id更新为用户的authed
function reset_authed($uid){   
    $user_id    = session('authed');
    if($user_id!=''){
        if($user_id == $uid){
            $_SESSION['id']     = $uid;//session设置
        }
        else{
            $_SESSION['id']     = $user_id;//存在用户id，则更新为当前用户id
        }
    }else{
        $_SESSION['id']     = $uid;//session设置
    }
    return(session('id'));
}




//改版合伙人计划

//判定是否存在用户信息
function check_user_msg(){
    $user_id = session('authed');
    if(empty($user_id)){
        $auth   = new Auth(C('appId'), C('secret'));
        $user   = $auth->authorize();//获取用户信息
        $res    = (array)json_decode($user);
        $loginM = new LoginModel();
        $checkUser = $loginM->check_user($res['openid']);
        if(empty($checkUser)){
            $res1 = $loginM->add_user_msg($res);
            $_SESSION['authed'] = $res1;//session设置
        }
        else{
            $_SESSION['authed'] = $checkUser['id'];
        }
    }

    return $_SESSION['authed'];
}

function check_manager(){
    $user_id = $_SESSION['authed'];
    $userM = new \Partner\Model\UserModel();

    $is_manager = $userM->is_manager($user_id);

    if($is_manager){
        return true;
    }else{
        redirect('/partner/login/login');
    }
}

