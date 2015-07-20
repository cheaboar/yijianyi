<?php
namespace Subcribe\Controller;
use Wechat\ErrorCode;
use Think\Controller;
use Wechat\Test;
use Wechat\Wechat;
use Wechat\TPWechat;
use Think\Log;
//vendor('log4php.Logger');

use Overtrue\Wechat\Server;

require LIB_PATH.'Org/Util/wechat-master/autoload.php';




define("TOKEN", "Yijianyi");

class IndexController extends Controller {
    protected $wechat;
    protected $server_wechat;
    protected $test;
    function __construct(){
        parent::__construct();
        $server_options = array(
            'token'=>'yijiayi', //填写你设定的key
            'encodingaeskey'=>'V636dnTxFRxFb0qxwMtFComCPaOwkqtGBU5D8rbrbTE', //填写加密用的EncodingAESKey
            'appid'=>'wx86b3751ad43f4062', //填写高级调用功能的app id
            'appsecret'=>'f52a587fefed285df9244f310eee8a34' //填写高级调用功能的密钥
        );

        $options = array(
            'token'=>'Yijianyi', //填写你设定的key
            'encodingaeskey'=>'OvXZAh5akCf9oxrwgLbsu4a61gw4KmuVdGwZzxa1Cjx', //填写加密用的EncodingAESKey
            'appid'=>'wxb468b850fc76d278', //填写高级调用功能的app id
            'appsecret'=>'b4843edb87fc8e3f0b1034bf946c5189' //填写高级调用功能的密钥
        );

        $access_token = 'iWw4T0WrFvyPUieYU7F4oCv5ywWJ8zIkwRb7qkiFF9PhcYpyOTJikMb6l0jRbofgI1f_4N58S2fRjTSr9T8XUn6B2tYoaLU3VlZDE_7SPrQ';
        $this->server_wechat = new TPWechat($server_options);
        $this->wechat = new TPWechat($options);

        $this->test = new Test();

    }
    public function index(){
//        $this->Lore->sayHello();
//        $text = array(
//            'ds'=>'1',
//            'dss' => 2,
//        );
//        \Think\Log::write($text);
        $QRCode = $this->server_wechat->getQRCode('2333');
        dump($QRCode);
        dump($this->server_wechat->errCode);
        dump($this->server_wechat->errMsg);
        $QRCodeURL = $this->server_wechat->getQRUrl($QRCode['ticket']);
        dump($QRCodeURL);
        $appid = 'wx86b3751ad43f4062';
        $authname = 'wechat_access_token'.$appid;
        dump(S($authname));

        $this->assign('QRURL', $QRCodeURL);
        $this->display('Index:index');
    }


    public function valid()
    {
        $fp = fopen('/var/www/Application/Subcribe/Controller/log.txt', 'a+');
        fwrite($fp, 'dsddf%');
        fclose($fp);

        $appId = 'wx9d24912c87f38ef5';
        $token = 'ChanCheaboar';
        $EncodingAESKey = 'XQSupevFqMLkhcl8rQhzr69bVs62dFYphHhxqdJsrj4';

        $server = new Server($appId, $token, $EncodingAESKey);

        echo $server->serve();


    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
            if(!empty( $keyword ))
            {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{
                echo "Input something...";
            }

        }else {
            echo "";
            exit;
        }
    }

    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

}