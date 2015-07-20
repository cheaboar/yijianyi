<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/3
 * Time: 下午3:31
 */

namespace Service\Model;
use Think\Model;

class Customer extends Model{
    protected $tablePrefix = '';
    protected $tableName = 'oa_customer';

    public function get_customer($costomer_id){
        $result =  $this->where('customer_id='.$costomer_id)->find();
        if($result['customer_sex'] == C('SEX.MALE')){
            $result['customer_sex'] = '男';
        }elseif($result['customer_sex'] == C('SEX.FEMALE')){
            $result['customer_sex'] = '女';
        }else{
            $result['customer_sex'] = '未知';
        }

        return $result;
    }
}