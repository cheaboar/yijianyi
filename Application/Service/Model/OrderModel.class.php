<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/17
 * Time: 下午3:54
 */

namespace Service\Model;

use Think\Model;
use Service\Model\Customer;
use Service\Model\FollowModel;

class OrderModel extends Model{
    protected $tablePrefix = '';
    protected $tableName = 'oa_order';


//    订单格式化，将字段的数字部分映射成文字
    private function format_order_detail($order_detail){
        $order_status = C('ORDER.STATUS');
        $order_detail['order_status'] = $order_status[$order_detail['order_status']];

        $order_service_mode = C('ORDER.SERVICE_MODE');
        $order_detail['service_mode'] = $order_service_mode[$order_detail['service_mode']];

        $service_type = C('SERVICE_TYPE');
        $order_detail['service_type'] = $service_type[$order_detail['service_type']];

        $fee_unit = C('ORDER.FEE_UNIT');
        $order_detail['order_fee_unit'] = $fee_unit[$order_detail['order_fee_unit']];

        $order_detail['add_time'] = time_stamp_to_str('Y-m-d H:i:s', $order_detail['add_time']);
        $order_detail['order_start_time'] = time_stamp_to_str('Y-m-d H:i:s', $order_detail['order_start_time']);
        $order_detail['order_end_time'] = time_stamp_to_str('Y-m-d H:i:s', $order_detail['order_end_time']);

        return $order_detail;
    }

    //获取订单的细节
    public function get_order_detail($order_id){
        $customerM = new Customer();
        $condition = array(
            'order_id' => $order_id
        );
        $order_detail = $this->where($condition)->find();

        if(!empty($order_detail)){
            //获取病人信息
            $customer = $customerM->get_customer($order_detail['customer_id']);

            $order_detail['customer_detail'] = $customer;
        }

        $order_detail = $this->format_order_detail($order_detail);


        return $order_detail;
    }

    //获取客户的所有订单,返回order_id列表
    public function get_customer_orders($customer_id){
        $condition = array(
            'customer_id' => $customer_id
        );

        $result = array();

        $orders = $this->where($condition)->order('add_time desc')->select();

        foreach($orders as $order){
            $result[] = $this->format_order_detail($order);
        }

        return $result;

    }

//    获取我关注的订单
    public function get_my_follow_orders($user_id){
        $followM = new FollowModel();
        $customers = $followM->get_follow($user_id);

        $order_lists = array();

        foreach($customers as $customer){
            $item = array();
            $item['customer'] = $customer;


            $item['orders'] = $this->get_customer_orders($customer['customer_id']);
            $order_lists[] = $item;
        }

        return $order_lists;
    }


}