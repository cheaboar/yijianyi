<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/31
 * Time: 下午3:58
 */

namespace Service\Model;
use Think\Model;
use Service\Model\OrderModel;

class OrderCollectionModel extends Model{
    protected $tableName = 'oa_order_collection';
    protected $tablePrefix = '';

    //获取订单的支付记录,这里的格式化放在公共函数中应该比较好的
    public function get_order_payment($order_id){
        $condition = array(
            'order_id' => $order_id,
        );
        return $this->where($condition)->select();
    }

    //获取订单的价格
    public function get_collection($collection_id){
        $condition = array(
            'collection_id' => $collection_id,
        );

        return $this->where($condition)->find();
    }

    //跟新订单out_trade_no
    public function update_out_trade_no($collection_id , $out_trade_no){
        $condition = array(
            'collection_id' => $collection_id,
        );
        $data = array(
            'out_trade_no' => $out_trade_no,
        );

        $this->where($condition)->save($data);
    }

    //标志订单为支付了,并且更新order的收款选项
    public function set_collection_paid($collection_id, $out_trade_no, $total_fee){
        $condition = array(
            'collection_id' => $collection_id,
            'out_trade_no' => $out_trade_no,
        );


//        $status = C('ORDER_COLLECTION.COLLECTION_STATUS');
        $status_r = C('ORDER_COLLECTION.COLLECTION_STATUS_R');
        $payment_type_r = C('ORDER_COLLECTION.PAYMENT_TYPE_R');
        $collection_type_r = C('ORDER_COLLECTION.COLLECTION_TYPE_R');

        $data = array(
            'collection_status' => $status_r['已支付'],
            'payment_time' => time(),
            'payment_type' => $payment_type_r['微信支付'],
        );
        //获取当前的order_id
        $collection = $this->where($condition)->find();



//        为了数据库冗余数据相同，这里更新order的预收款应该使用事务操作
        $orderM = new OrderModel();
        $condition_new = array(
            'order_id' => $collection['order_id']
        );
        //判断是否已经处理过了，处理过的就不需要更新order表，并且要判断支付类型
        if($collection['collection_status'] == $status_r['已支付']){
            //已支付的就不需要更新状态了

        }elseif($collection['collection_type'] == $collection_type_r['预收款']){
            $this->where($condition)->save($data);

            $orderM->where($condition_new)->setInc('order_advance_payment', $total_fee);
        }elseif($collection['collection_type'] == $collection_type_r['结算']){
            //结算
            //TODO:结算，改变订单转态
            $this->where($condition)->save($data);
            $orderM->pay_for_order($collection['order_id'], $total_fee);
        }



    }


    /*获取订单的服务名称
     * @collection_id :支付条目id
     * 返回：订单的服务名称
     */
    public function get_service_type_str($collection_id){
        $service_type = C('SERVICE_TYPE');
        $condition = array(
            'collection_id' => $collection_id
        );

        $type = $this->alias('c')->join('oa_order as o on c.order_id = o.order_id', 'left')->where($condition)->getField('service_type');

        return $service_type[$type];
    }
}