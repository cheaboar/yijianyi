<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\LoginModel;
class IndexController extends Controller {
    //用户登录注册界面，如果是用户注册获取上级别id。
    function login(){       
        $check = session('authed');
        if($check!=''){
            $this->success('登录成功，页面跳转中...','/admin/usercenter/user_center',3);
        }else{
            $this->assign('id',session('id'));
            $this->assign('header_url',I('param.pre_url','usercenter/user_center'));	
            $this->display('admin:index');
        }
    }
    function regist(){// user regist,绑定用户的上级id，如果id为空则默认为1
        $this->assign('id',I('param.id','1'));   
        $this->display('admin:regist');
    }

    function index(){
        
        $this->display('admin:admin-help');
    }
    function yijiayi(){
        reset_authed(I('param.id',"1"));//session设置
        $this->assign('id',session('id'));
        $this->display('admin:yijiayi');
    }
}
