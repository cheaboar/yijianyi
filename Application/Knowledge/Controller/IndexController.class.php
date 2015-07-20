<?php
namespace Knowledge\Controller;
use Think\Controller;
use Knowledge\Model\CatModel;
use Knowledge\Model\InfoModel;

class IndexController extends Controller {
    protected $Cat;
    protected $Info;
    public function __construct(){
        parent::__construct();
        $this->Info = new InfoModel();
        $this->Cat = new CatModel();
    }

    //一级标题
    public function knowledge(){
        $knowledge_nav = S('knowledge_nav');// 获取缓存数据
        if(empty($knowledge_nav)){
            $get_first_nav  = $this->Cat->where('cat_pid=0')->select();
            $second_nav     = array();
            foreach ($get_first_nav as $k_first => $v_first){
                $condition['cat_pid'] = $v_first['cat_id'];  
                $get_second_nav = $this->Cat->where($condition)->select();
                array_push($second_nav, array(
                    'cat_id'        => $v_first['cat_id'],
                    'cat_name'      => $v_first['cat_name'],
                    'cat_second'    => $get_second_nav,
                    )
                );
            }
            //缓存设置
            S('knowledge_nav',$second_nav,10800);
            //缓存输出
            $res_nav = $second_nav; // 设置缓存
        }
        else{
            $res_nav = $knowledge_nav;//缓存输出
        }
        $this->assign('second_nav',$res_nav);
        $this->display('health:type');
    }

    //二级标题
    public function knowledge_title(){
        $id['cat_pid']          = I('id');
        $title['cat_id']        = I('id');

        $res_title  = $this->Cat->where($title)->find();
        $res        = $this->Cat->where($id)->select();
        $this->assign('knowledge_title',$res_title['cat_name']);
        $this->assign('knowledge_title_nav',$res);
        $this->display('health:knowledge_title_nav');
    }

    //三级标题以及展示内容
    public function knowledge_content(){
        //是否还存在下级菜单
        $nav_id['cat_pid']  = I('id');
        $nav_res            = $this->Cat->where($nav_id)->select();
        if(is_array($nav_res)){
            $nav_title['cat_id'] = I('id');
            $res_title      = $this->Cat->where($nav_title)->find();
            $this->assign('knowledge_title',$res_title['cat_name']);
            $this->assign('knowledge_title_nav',$nav_res);
            $this->display('health:knowledge_title_nav');
        } 
        else {
            $id['cat_id']   = I('id');
            $res            = $this->Info->where($id)->select();
            $res_title      =  $this->Cat->where($id)->find();
            $this->assign('title',$res_title['cat_name']);
            $this->assign('knowledge_content',$res);
            $this->display('health:knowledge_content');
        }   
    }  
}

