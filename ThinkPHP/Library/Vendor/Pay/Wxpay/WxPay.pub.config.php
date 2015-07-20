<?php
/**
* 	配置账号信息
*/

class WxPayConf_pub
{
	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = 'wx86b3751ad43f4062';
	//受理商ID，身份标识
	const MCHID = '1244739002';
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	const KEY = 'chiputaobutuputaopiyijianyi20155';
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = 'f52a587fefed285df9244f310eee8a34';
	const JS_API_CALL_URL = 'http://$_SERVER[HTTP_HOST]/wxpay/index' ;
	
	
	
	const SSLCERT_PATH = '{$path}/cacert/apiclient_cert.pem';
	const SSLKEY_PATH = '{$path}/cacert/apiclient_key.pem';

	const NOTIFY_URL = 'http://$_SERVER[HTTP_HOST]/wxpay/index/notify';
	const CURL_TIMEOUT = 60;
}

	
?>