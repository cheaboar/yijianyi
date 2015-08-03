<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/31
 * Time: 下午3:58
 */

namespace Service\Model;
use Think\Model;

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


}