<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/17
 * Time: 上午11:26
 */

namespace Service\Controller;
use Think\Model;
use Think\Controller;
use Service\Model\OrderModel;

class OrderController extends Controller {


    //关注的订单详情
    public function order_detail(){

        $order_id = $_GET['order_id'];
        $orderM = new OrderModel();
        $order_detail = $orderM->get_order_detail($order_id);


        $this->assign('order', $order_detail);
        $this->assign('title', '订单');
        $this->display('Order:order_detail');
    }


    //我关注的订单
    public function my_orders(){
        $user_id = 17;
        $orderM = new OrderModel();

        $follow_orders = $orderM->get_my_follow_orders($user_id);
        $this->assign('orders', $follow_orders);
        $this->display('Order:my_follow_orders');
    }


}