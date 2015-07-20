<?php
namespace Management\Controller;
use Think\Controller;
use Management\Model\CatModel;
use Management\Model\InfoModel;
class KnowledgeController extends Controller {
    protected $Cat;
    protected $Info;
    public function __construct(){
        parent::__construct();
        $this->Info = new InfoModel();
        $this->Cat = new CatModel();
    }
    public function index(){
        dump('hello');
        echo "cheng";
        $res_all = $this->Cat->join(' yjy_knowledge_info ON yjy_knowledge_info.cat_id = yjy_knowledge_cat.cat_id')->order('cat_pid desc')->select();
        dump($res_all);
        echo $this->Cat->getLastSql();
        $this->assign('know',$res_all);
        //$this->display('Lore:lore');
        //$this->display('Lore:index');
        //$this->display('Index:knowldege_add');
    }

    //知识库添加信息
    public function knowledge_add(){
        $this->display('Lore:lore_add');
    }

    //知识库添加类目
    public function knowledge_type_add(){
        $res_type = $this->Cat->where('cat_pid=0')->select();
        $this->assign('type_list',$res_type);
        $this->display('Lore:lore_type_add');
    }

    //知识库修改
    public function knowledge_change(){
        $this->display('Lore:lore_change');
    }
}
