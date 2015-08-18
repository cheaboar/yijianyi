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

class OrderModel extends Model
{
    protected $tablePrefix = '';
    protected $tableName = 'oa_order';


//    订单格式化，将字段的数字部分映射成文字
    private function format_order_detail($order_detail)
    {
        $order_status = C('ORDER.STATUS');
        $order_detail['order_status_code'] = $order_detail['order_status'];

        $order_detail['order_status_str'] = $order_status[$order_detail['order_status']];

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
    public function get_order_detail($order_id, $user_id)
    {
        $customerM = new Customer();
        $condition = array(
            'order_id' => $order_id,
            'user_id' => $user_id,
        );
        $order_detail = $this->where($condition)->find();

        if (!empty($order_detail)) {
            //获取病人信息
            $customer = $customerM->get_customer($order_detail['customer_id']);

            $order_detail['customer_detail'] = $customer;
        }

        $order_detail = $this->format_order_detail($order_detail);


        return $order_detail;
    }

    //获取客户的所有订单,返回order_id列表
    public function get_customer_orders($customer_id)
    {
        $condition = array(
            'customer_id' => $customer_id
        );

        $result = array();

        $orders = $this->where($condition)->order('add_time desc')->select();

        foreach ($orders as $order) {
            $result[] = $this->format_order_detail($order);
        }

        return $result;

    }

//    获取我关注的订单
    public function get_my_follow_orders($user_id)
    {
        $followM = new FollowModel();
        $customers = $followM->get_follow($user_id);

        $order_lists = array();

        foreach ($customers as $customer) {
            $item = array();
            $item['customer'] = $customer;


            $item['orders'] = $this->get_customer_orders($customer['customer_id']);
            $order_lists[] = $item;
        }

        return $order_lists;
    }

    //获取用户的所有order，这里指的是负责人的order，用于推动订单的详情
    public function get_user_orders($user_id)
    {
        $condition = array(
            user_id => $user_id,
        );

        $result = $this->alias('o')->join('oa_customer as c on c.customer_id = o.customer_id')
            ->where($condition)->order('add_time desc')
            ->getField('order_id, order_no, customer_name, service_type, order_status, add_time');
        return $result;
    }

    //改变订单的状态，这里同样需要身份的认证
    public function change_order_satus($order_id, $user_id, $status){
        $condition = array(
            'order_id' => $order_id,
            'user_id' => $user_id
        );

        $data = array(
            'order_status' => $status
        );

        $this->where($condition)->save($data);
    }

    /*支付订单
     * @order_id：订单id
     * @fee:支付的费用
     *return:空
     * 如果费用加上预付款比总费用少则将款项添加到预付款，订单状态不变。
     * **/
    public function pay_for_order($order_id, $fee){
        $condition = array(
            'order_id' => $order_id
        );

        $result = $this->where($condition)->getField('order_id, order_advance_payment, order_total_cost');

        $fees = $result[$order_id];

        $advance_payment = $fees['order_advance_payment'];
        $total_cost = $fees['order_total_cost'];
        $end_payment = $advance_payment + $fee;

        if($end_payment < $total_cost){
            //只更新预付款
            $data = array(
                'order_advance_payment' => $end_payment,
            );

            $this->where($condition)->save($data);
        }else{
            //更新预付款并且更行订单状态
            $status_r = C('ORDER.STATUS_R');
            $data = array(
                'order_advance_payment' => $end_payment,
                'order_status' => $status_r['已完成']
            );

            $this->where($condition)->save($data);
        }
    }

    /*
     * 判断订单id和负责人id是否相符
     * 相符返回：true，否则返回：false；
     * */
    public function is_user_order_match($order_id, $user_id){
        $condition = array(
            'order_id' => $order_id,
            'user_id' => $user_id
        );

        $result = $this->where($condition)->select();

        if(empty($result)){
            return false;
        }else{
            return true;
        }
    }

}