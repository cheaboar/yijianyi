<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/3
 * Time: 下午3:27
 */

namespace Service\Model;
use Think\Model;
use Service\Model\Customer;



class FollowModel extends Model{
    protected $tablePrefix = '';
    protected $tableName = 'oa_follow';

    public function get_follow($user_id){
        $customerR = new Customer();
        $result = array();
        $follows = $this->where('user_id='.$user_id)->select();
        foreach($follows as $follow){
            $customer = $customerR->get_customer($follow['customer_id']);
            $follow['customer'] = $customer;
            $result[] = $follow;
        }
        return $result;
    }

    public function exit_follow_connection($user_id, $customer_id){
        $condition = array(
            'user_id' => $user_id,
            'customer_id' => $customer_id
        );

        $result = $this->where($condition)->find();
        if(empty($result)){
            return false;
        }else{
            return true;
        }
    }
}