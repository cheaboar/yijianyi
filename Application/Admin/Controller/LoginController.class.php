<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\LoginModel;
class LoginController extends Controller {    
    
    function login_check(){//登录检验
		$condition['user_name|telephone'] = I('param.name');
		$condition['password']	          = I('param.password');
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
        //$Code_find          = M('user','yijiayi.');
        $att_code['phone']  = I('param.phone');  
        $att_code['code']   = strtolower(I('param.code'));       
        $res                =  M('user','yijiayi.')->where($att_code)->find();
        if(!empty($res)){
            $res_c['msg']   = "ok";       //验证码正确      
        }
        else{
            $res_c['msg']   = "false";    //验证码错误              
        }
        $this->ajaxReturn($res_c);
    }
    
    
    function user_regist(){         //用户注册
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
        $phone_w['phone']           = $phone;
        $phone_w['code']            = strtolower(I('param.code'));
        $phone_c['code']            = '~~~~~`````~~~';        //清除手机验证码  
        $phone_d['phone']           = $phone;       //验证码对应的手机号码的验证码更新为~~~~~~~~ 
        
        $User_new                   = M('user','yijiayi.');      
        $Phone_del                  = M('phone_code','yijiayi.');
                
        $code_res                   = $Phone_del->where($phone_w)->find();//验证码是否正确
        if(!empty($code_res)){
            $res_user               = $User_new->data($user_date)->add();
            $_SESSION['authed']     = $res_user;    //session设置       
            $Phone_del->where($phone_d)->save($phone_c);//验证码更新为完成            
            $data['msg']            = "ok";          
        }
        else{
            $data['msg']            = "code_false";
        } 
        $this->ajaxReturn($data);
    }

    /**
    *短信验证处理
    *$phone为用户传递进来的手机号码
    *返回参数msg，ok为正常处理，pass为次数超出 ，having 为已经注册了,time 为时间限定条件，三分钟内不发
    */   
    function phone_code(){
        $phone      = I('param.phone','null');
        $code       = strtolower(randCode());//随机验证码

        //$Phone_res  = M('user','yijiayi.');
        $condition['telephone'] = $phone;
        //$condition['status'] = '1';
        $res_user   = M('user','yijiayi.')->where($condition)->find();
        if(empty($res_user)){
            $Phone_code     = M('phone_code','yijiayi.');
            $res            = $Phone_code->where('phone='.$phone)->find();
            if(empty($res)){
                //用户没有注册过的情况 为空的情况
                $condition['phone']     = $phone;
                $condition['time']      = time();
                $condition['code']      = $code;
                $condition['number']    = "1";

                $Phone_code->data($condition)->add();
                
                $res_code = "ok";  //操作成功标注         
            }
            else{
                //用户已经注册    no为空的情况
                $pd     = date("d",time());
                $hd     = date("d",$res['time']);
                if($hd!=$pd){//不是同一天的情况
                    $notnull['time']    = time();
                    $notnull['code']    = $code;
                    $notnull['number']  = "1";

                    $Phone_code->where('phone='.$phone)->save($notnull);

                    $res_code           = "ok";   //操作成功标注
                }
                else if($res['number']<"4"){//同一天
                    $condition_time     = time()-$res['time'];//时间判定条件                   
                    if($condition_time>"180"){
                        $have_r['time']     = time();
                        $have_r['code']     = $code;
                        $have_r['number']   = ($res['number']+"1");

                        $Phone_code->where('phone='.$phone)->save($have_r);
                        
                        $res_code           = "ok";   //操作成功标注
                    }
                    else{
                        $res_code           = "time";
                    }
                }
                else{
                    $res_code   = "pass"; //操作成功标注
                }                       
            }
        }
        else{
            $res_code = "having";
        }
        if($res_code=="ok"){
            
            $tpl_value  = '#code#='.$code;//发送信息的母板

            tpl_send_sms('3eed45bdcaf4f017a2ddc4d9eefb71f6', '719281', $tpl_value,$phone);
        }
        $res_data['msg']    = $res_code;             
        $this->ajaxReturn($res_data);
    }
}