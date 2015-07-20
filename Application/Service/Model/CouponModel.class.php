<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/3
 * Time: 上午9:47
 */

namespace Service\Model;
use Think\Model;


class CouponModel extends Model{

    protected $tablePrefix = '';
    protected $tableName = 'oa_coupon';

    public function get_coupon($user_id){
        return $this->where('user_id='.$user_id)->order('coupon_expire desc')->select();

    }
}