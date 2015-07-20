<?php
namespace Management\Controller;
use Think\Controller;
//use Management\Model\CatModel;
//use Management\Model\InfoModel;
class LoreController extends Controller {
//    protected $Cat;
////    protected $Info;
//    public function __construct(){
//        parent::__construct();
//        $this->Info = new InfoModel();
//        $this->Cat = new CatModel();
//    }
    public function index(){
    	echo 'wind';
    	$this->display('Index:lore_add');
    }
}