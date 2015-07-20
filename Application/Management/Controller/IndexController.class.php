<?php
namespace Management\Controller;
use Think\Controller;
use Think\Crypt\Driver\Think;

class IndexController extends Controller {
    public function index(){
        layout('Layout/layout');
        $title = '易简医管理后台';
        $this->assign('title', $title);
        $this->display('index');
    }
    public function knowledge(){
            $this->display('Index:index');
    }
}