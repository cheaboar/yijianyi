<?php
namespace Subcribe\Controller;
use Wechat\ErrorCode;
use Think\Controller;
use Wechat\Test;
use Wechat\Wechat;
use Wechat\TPWechat;
use Think\Log;
//vendor('log4php.Logger');






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
//        $this->test->sayHello();
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
//        $this->wechat->valid();
        $this->wechat->getRev();
        $receive_data = $this->wechat->getRevData();
        $receive_str = '';
        foreach($this->wechat->getRevData() as $key=> $value){
            $receive_str =  $receive_str . $key . ':' . $value . "\n";
        }
//        Log::write($receive_str);
        if(isset($receive_data['Content']) && $receive_data['Content'] == 'a'){
            $this->wechat->text('你好')->reply();
        }elseif(isset($receive_data['Content'])){
//            `$this->wechat->text('感谢您的留言，我们正在努力完善相关功能!')->reply();
            $data = array(
                "0" => array(
                    'Title'=>'这些年，你亏欠妈妈多少爱？',
                    'Description' => '母亲节来了，小依免费为大家奉出五套可穿戴健康管理设备',
                    'PicUrl' => 'http://subcribe.ecare-easy.com/Public/assets/img/Subscribe/mother.jpg',
                    'Url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MTYxNjU2Nw==&mid=205115064&idx=1&sn=3aeb446cc3d9de37d25423c0be295b99&scene=1&key=1936e2bc22c2ceb5492ddcf40aa6339224b84e748f837dab1128d7f4bc0667dd1b6793ffce23df1bbcae512591345a3e&ascene=0&uin=MTUyMjgxNDg2Mw%3D%3D&devicetype=iMac+MacBookAir5%2C2+OSX+OSX+10.10.3+build(14D136)&version=11020012&pass_ticket=ExsbpFuyyOfUleOUBzoE4BsUZVyOPiPOuwlpg5HVMelA1HcvUQSHJup%2BS86Xwt%2BL'
                )
            );
            $this->wechat->news($data)->reply();

        }

        if($receive_data['MsgType'] == 'voice'){
            $this->wechat->voice($receive_data['MediaId'])->reply();
        }


        if($receive_data['Event'] == 'subscribe'){
            $data = array(
                "0" => array(
                    'Title'=>'这些年，你亏欠妈妈多少爱？',
                    'Description' => '母亲节来了，小依免费为大家奉出五套可穿戴健康管理设备',
                    'PicUrl' => 'http://subcribe.ecare-easy.com/Public/assets/img/Subscribe/mother.jpg',
                    'Url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MTYxNjU2Nw==&mid=205115064&idx=1&sn=3aeb446cc3d9de37d25423c0be295b99&scene=1&key=1936e2bc22c2ceb5492ddcf40aa6339224b84e748f837dab1128d7f4bc0667dd1b6793ffce23df1bbcae512591345a3e&ascene=0&uin=MTUyMjgxNDg2Mw%3D%3D&devicetype=iMac+MacBookAir5%2C2+OSX+OSX+10.10.3+build(14D136)&version=11020012&pass_ticket=ExsbpFuyyOfUleOUBzoE4BsUZVyOPiPOuwlpg5HVMelA1HcvUQSHJup%2BS86Xwt%2BL'
                )
            );
            $this->wechat->news($data)->reply();

        }


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