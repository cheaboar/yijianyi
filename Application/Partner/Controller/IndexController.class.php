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
        $this->Authed = 85;  //用户的session
//        $this->Authed = check_user_msg();   //检查是否获取了用户信息

   }

    //绑定下级通过partnerid解决
    //首页加载
    public function index(){
        $app_id = C('appId');
        $secret = C('secret');
        $js = new Js($app_id, $secret) ;
        $config = $js->config(array('onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareAppMessage'),false, true);
        $this->assign('wechat_config',$config);
        $_SESSION['partnerId'] = I('pid','1');

        $this->assign('id',$this->Authed);
        $this->display('partner:yijiayi');

    }



}
