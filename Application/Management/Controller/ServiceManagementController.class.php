<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/5/29
 * Time: 下午2:45
 */

namespace Management\Controller;
use Think\Controller;
use Management\Model\ServiceInfoModel;

class ServiceManagementController extends Controller{
    private $service;
    public function __construct(){
        parent::__construct();
        $this->service = new ServiceInfoModel();
    }

    public function service_management(){
        layout('Layout/layout');
        $result = $this->service->select();
        $this->display('ServiceManagement:service_management');
    }

    public function get_service_json(){
        if(IS_POST){
            $input = json_decode(file_get_contents( "php://input"), true);
            $this->service->add($input);
            $this->service->save();
            dump($this->service->getDbError());
            $this->ajaxReturn($input);
        }elseif(IS_GET){
            $page = $_GET['page'];
            if($page == null){
                $page = 0;
            }
            $result = $this->service->page($page)->select();
            $this->ajaxReturn($result, 'json');
        }
//        layout('Layout/layout');

    }
}