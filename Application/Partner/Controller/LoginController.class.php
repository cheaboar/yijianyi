<?php
namespace Partner\Controller;
use Think\Controller;
//use Partner\Model\LoginModel;
class LoginController extends Controller {

    /**
     *短信验证处理
     *
     *Return $msg，ok为正常处理，pass为次数超出 ，having 为已经注册了,time 为时间限定条件，三分钟内不发
     */
    public function phonecodesend(){
        $phone                  = I('param.phone','null');
        $code                   = strtolower(randCode());//随机验证码
        $condition['telephone'] = $phone;
        $usercheck              = M('user','yijiayi.')->where($condition)->find();//用户是否存在
        $sessionValue           = array('phone'=>$phone,'time'=>time(),'value'=>$code,'number'=>'1');//缓存数据设置
        $checktruecode          = session('getsession');
        $codeTime               = date("d",$checktruecode['time']);
        $nowTime                = date("d",time());
        /**
         *判定是否存在session 空，发
         *判定session时间有效性
         */
        $tpl_value  = '【一家依】您的验证码是'.$code;//发送信息的母板
        if(empty($usercheck)){
            if(empty($checktruecode)){//session 为空

                echo 'need to setting session';
                $_SESSION['getsession'] = $sessionValue;
                send_sms('cf34160f4719430181a3d387f9dda3c8',  $tpl_value,$phone);//发送信息
                $res_code = "ok";  //操作成功标注
            }
            else if($codeTime!=$nowTime){//当天没发送记录
                send_sms('cf34160f4719430181a3d387f9dda3c8',  $tpl_value,$phone);
                $_SESSION['getsession'] = $sessionValue;
                $res_code = "ok";
            }
            else{
                if($checktruecode['number']<='5'){//当天没发送记录>5
                    $setNumber = $checktruecode['number']+"1";
                    $_SESSION['getsession'] = array('phone'=>$phone,'time'=>time(),'value'=>$code,'number'=>$setNumber);
                    send_sms('cf34160f4719430181a3d387f9dda3c8',  $tpl_value,$phone);
                    $type = "ok";
                }
                else{
                    $type = "pass";
                }
                $res_code = $type;
            }
        }
        else{
            $res_code = "having";
        }
        $res_data['msg'] = $res_code;
        $this->ajaxReturn($res_data);
    }

    function login_check(){//登录检验
		$condition['user_name|telephone'] = I('param.name');
		$condition['password']	          = I('param.password');
        session('getsession');
		$res = M('user','yijiayi.')->where($condition)->find();
        
        session_start();
        if(!empty($res)){
            $_SESSION['authed'] = $res['id'];//session设置
            $data['msg']  = "ok";
        }else{
            $data['msg']  = "false";
        } 
        $this->ajaxReturn($data);
    }

    function check_user(){  //用户是否已经存在
        $data['user_name']      = I('param.name');
        $res = M('user','yijiayi.')->where($data)->find();
        if(!empty($res)){
            $res_d['msg']       = "having";//已经存在用户名
        }
        else{
            $res_d['msg']       = "ok";//用户名可用
        }
        $this->ajaxReturn($res_d);
    }


    function check_code(){   //验证码验证
        $phone = I('param.phone');
        $value = strtolower(I('param.code'));
        $checktruecode          = session('getsession');
        if(($checktruecode['phone']=$phone)&&($checktruecode['value']=$value)){
            $res_c['msg']   = "ok";
        }
        else{
            $res_c['msg']   = "false";
        }
        $this->ajaxReturn($res_c);
    }


    function user_regist(){         //用户注册
        $user_date = array();
        if(parent_id()=='1'){
            $user_date['type'] == '0';
        }
        else{
            $user_date['type'] == '1';
        }
        $password                   = I('param.password','');
        $phone                      = I('param.phone');
        $user_date['user_name']     = I('param.name');
        $user_date['password']      = $password;
        $user_date['telephone']     = $phone;
        $user_date['parent_id']     = I('param.parent');
        $code                       = strtolower(I('param.code'));
        $User_new                   = M('user','yijiayi.');
        $checktruecode              = session('getsession');
        if(($checktruecode['phone']=$phone)&&($checktruecode['value']=$code)){
            $res_user               = $User_new->data($user_date)->add();
            $_SESSION['authed']     = $res_user;    //用户session设置
            $data['msg']            = "ok";
            $_SESSION['getsession']     = null;    //清空验证码session设置

        }
        else{
            $data['msg']            = "code_false";
        } 
        $this->ajaxReturn($data);
    }
}