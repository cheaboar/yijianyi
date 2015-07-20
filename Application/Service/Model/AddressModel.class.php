<?php
/**
 * Created by PhpStorm.
 * User: cheaboar
 * Date: 2015/7/2
 * Time: 0:30
 */

namespace Service\Model;
use Think\Model;


class AddressModel  extends Model{
    protected $tablePrefix = '';
    protected $tableName = 'oa_address';

    public function save_address($user_id, $province, $city, $zone, $address=' ', $is_default=0){
        $data = array(
            'user_id' => $user_id,
            'province' => $province,
            'city' => $city,
            'area' => $zone
        );

        $this->add($data);
        if($this->getDbError() == ''){
            return array(
                'code' => 200,
            );
        }else{
            return array(
                'code' => 500,
                'error_msg' => $this->getDbError()
            );
        }
    }
}