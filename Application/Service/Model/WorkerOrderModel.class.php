<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/8/13
 * Time: 下午5:02
 */

namespace Service\Model;
use Think\Model;

class WorkerOrderModel extends Model{
    protected $tableName = 'oa_worker_order';
    protected $tablePrefix = '';

    public function get_worker_order($order_id){
        $condition = array(
            'order_id' => $order_id
        );

        $result = $this->join('oa_worker on oa_worker.worker_id = oa_worker_order.worker_id')->where($condition)->getField('oa_worker_order.order_id, oa_worker_order.*, oa_worker.worker_name');

        return $result[$order_id];
    }

    /*
     * 判断单号和工作人员是否匹配，且为在服务人员(这里不清楚需不需要)TODO:讨论
     * */
    public function is_order_worker_match($order_id, $worker_id){
        $condition = array(
            'order_id' => $order_id,
            'worker_id' => $worker_id,
        );

        $result = $this->where($condition)->find();

        if(empty($result)){
            return false;
        }else{
            return true;
        }

    }

}