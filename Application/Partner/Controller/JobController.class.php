<?php
namespace Partner\Controller;
use Think\Controller;
use Partner\Model\ResumeModel;
use Partner\Model\PositionModel;
class jobController extends Controller {
    protected $Position;
    protected $Resume;
    public function __construct(){
        parent::__construct();
        $this->Resume = new ResumeModel();
        $this->Position = new PositionModel();
        $this->Authed = '85';
    }


    /**
    *待修改
     */
    function index(){//加载主页
        reset_authed(I('param.id',"1"));//session设置
        $this->assign('data',$this->Position->positionList());
        $this->display('partner:job_list');
	} 

    function introduction(){//招聘信息简介
        $this->assign('data',$this->Position->onePosition(I('param.pid')));
        $this->display('partner:introduction');
    }


    function resumeCheck(){//点击我要应聘，检测是否存在用户,是否登录
        $check = $this->Authed;
        if($check==''){
            $res_c['msg']       = "need_login";
        }else{
            $data['user_id']      = $check;
            $data['position_id']    =I('param.resume_id');
            $res = $this->Resume->oneResume($data);
            if($res==''){
                $res_c['msg']   = "need_create";
            }
            else{
                $res_c['msg']   = "sure";
            }
        }
        $this->ajaxReturn($res_c);
    }


    //todoing
    function push_check(){//点击推荐用户时是检测是否登录
        $check = $this->Authed;
        if($check==''){
            $res_p['msg']       = "need_login";
        }else{
            $res_p['msg']       = "sure";
        }
        $this->ajaxReturn($res_p);
    }



    function resumeCreate(){//创建简历页面加载
        check_user_msg();
        $position_id            = I('param.pid','');
        if($position_id==''){
            $this->error('信息错误', '/partner/job/index');
        }else{
            $this->assign('position_id',$position_id);
            $this->display('partner:resume');
        }        
    }

    function pushsomeone(){//推荐人页面加载
        check_user_msg();
        $position_id            = I('param.pid','');
        if($position_id==''){
            $this->error('信息错误', '/partner/job/index');
        }else{
            $this->assign('position_id',$position_id);
            $this->display('partner:recommend');
        }
    }

    
    function new_resume(){//创建个人简历后台处理

//        user_session('job/index');//检测是否已经登录
        $data['user_id']        = user_id();
        $data['phone']          = I('param.phone');
        $data['name']           = I('param.name');
        $data['age']            = I('param.age');
        $data['degree']         = I('param.degree');
        $data['email']          = I('param.email');
        $data['about']          = I('param.about');
        $data['sex']            = I('param.sex');
        $data['position_id']    = I('param.position_id');        
        $res_create_resume  = M('resume','yijiayi.')->data($data)->add();
        if($res_create_resume!=''){
            $res_date['msg']    = 'ok';
            $res_date['re_id']  = $res_create_resume;
        }
        else{
            $res_date['msg']    = 'false';
        }
        $this->ajaxReturn($res_date);
    }

    
    function look_resume(){//查看简历信息以及招聘信息页面
        $position_msg['id']       = I('param.pid');//简历的id
        $resume_msg['user_id']    = user_id();//用户sessionid
        $resume_msg['position_id']= I('param.pid');//简历的id

        $position                 = M('position','yijiayi.')->where($position_msg)->find(); //查询简历
        
        $resume                   = M('resume','yijiayi.')->where($resume_msg)->order('id desc')->find(); 

        $this->assign('position',$position);
        $this->assign('resume',$resume);
        $this->display('partner:look_resume');
    }

    function application(){
        $data_a['date_of_create']   = date('y-m-d',time());
        $data_a['owner_id']         = user_id();
        $data_a['position_id']      = I('param.position_id');
        $data_a['resume_id']        = I('param.resume_id');
        $res = M('application','yijiayi.')->data($data_a)->add();
        if($res!=''){
            $rt_da['msg'] = 'ok';
        }
        else{
            $rt_da['msg'] = 'false';
        }
        $this->ajaxReturn($rt_da);        
    }

    //清除session函数
    function outlogin(){
        unset_session('authed');
        unset_session('id');
        $this->success('注销成功', '/partner/index/login');
    }

    //加载发布简历页面
    function publish(){
        $this->display('partner:publish');
    }

    //简历发布后台处理
    function create_publish(){
        $data['describe']       = I('param.describe');
        $data['need']           = I('param.need');
        $data['salary']         = I('param.salary');
        $data['job']            = I('param.job');
        $data['degree']         = I('param.degree');
        $data['work_place']     = I('param.work_place');
        $data['number']         = I('param.number');
        $data['experience']     = I('param.experience');
        $data['time']           = date('y-m-d h',time());
        //$data['territory']      = I('param.territory');
        $res = M('position','yijiayi.')->data($data)->add();
        if($res!=''){
            $rrturn_create['msg'] = 'ok';
        }
        else{
            $rrturn_create['msg'] = 'false';
        }
        $this->ajaxReturn($rrturn_create);        
    }

    //推荐人信息处理,更新user application resume，以及简历
    function recommend_create(){
        $name           = I('param.name');
        $phone          = I('param.phone');
        $position_id    = I('param.position_id');//招聘简历id
        
        //判定是否存在用户或者已经被注册了
        $res_check['telephone'] = $phone;
        //$res_check['password']  = 'null';
        
        $res_check_phone        = M('user','yijiayi.')->where($res_check)->find();
        if($res_check_phone!=''){
            $return_msg['msg']  = 'having';
        }
        else{
            //创建简历
            $data['phone']          = $phone;;
            $data['name']           = $name;
            $data['age']            = I('param.age');
            $data['sex']            = I('param.sex');
            $data['degree']         = I('param.degree');
            $data['email']          = I('param.email');
            $data['about']          = I('param.about');
            $data['position_id']    = $position_id;        
            $resume_id  = M('resume','yijiayi.')->data($data)->add();

            //更新数据到user表标注为推荐者
            $recommend_date['user_name']    = '被推荐者：'.$name;
            $recommend_date['telephone']    = $phone;
            $recommend_date['status']       = '1';
            $recommend_date['parent_id']    = user_id();
            $recommend_date['type']         = '1';
            $recommend_user_id = M('user','yijiayi.')->data($recommend_date)->add();
            
            //更新数据到application表标注为推荐者
            $data_a['date_of_create']       = date('y-m-d',time());
            $data_a['owner_id']             = $recommend_user_id;//用户owner_id
            $data_a['position_id']          = $position_id;
            $data_a['resume_id']            = $resume_id;//用户简历id
            $update_resume_id               = M('application','yijiayi.')->data($data_a)->add();
            
            $update_resume_data['user_id']  = $update_resume_id;
            $update_resume['id']            = $resume_id;
            M('resume','yijiayi.')->where($update_resume)->data($update_resume_data)->save();    

            $return_msg['msg'] = 'ok';
        }
        $this->ajaxReturn($return_msg);
    }
}