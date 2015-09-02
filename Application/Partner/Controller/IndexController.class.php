<?php
namespace Partner\Controller;
use Think\Controller;
use Partner\Model\LoginModel;

use Overtrue\Wechat\Js;

require LIB_PATH.'Org/Util/wechat-master/autoload.php';
//if(session('authed')==null){check_user_msg();}

class IndexController extends Controller {
    protected $Login;
    protected $Authed;
    public function __construct(){
        parent::__construct();
        $this->Login = new LoginModel();    //login数据库操作
        $this->Authed = '85';
//        $this->Authed = session('authed');  //用户的session
//        check_user_msg();   //检查是否获取了用户信息

   }

    //绑定下级通过partnerid解决
    //首页加载
    public function index(){
        var_dump($this->Authed);

        $app_id = 'wx86b3751ad43f4062';
        $secret = 'f52a587fefed285df9244f310eee8a34';
        $js = new Js($app_id, $secret) ;
        $config = $js->config(array('onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareAppMessage'),false, true);
        $this->assign('wechat_config',$config);
        $_SESSION['partnerId'] = I('pid','1');

        $this->assign('id',$this->Authed);
        $this->display('partner:yijiayi');

    }



    //用户登录注册界面，如果是用户注册获取上级别id。
//    function login(){
//        $check = session('authed');
//        if($check!=''){
//            $this->success('登录成功，页面跳转中...','/partner/usercenter/user_center',3);
//        }else{
//            $this->assign('id',session('id'));
//            $this->assign('header_url',I('param.pre_url','usercenter/user_center'));
//            $this->display('partner:index');
//        }
//    }
//    function regist(){// user regist,绑定用户的上级id，如果id为空则默认为1
//        $this->assign('id',I('param.id','1'));
//        $this->display('partner:regist');
//    }
//
//    function yijiayi(){
//        reset_authed(I('param.id',"1"));//session设置
//        $this->assign('id',session('id'));
//        $this->display('partner:yijiayi');
//    }
}
