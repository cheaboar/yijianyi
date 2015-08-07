<?php
/**
 * Created by PhpStorm.
 * User: Ivy
 * Date: 2015/8/6
 * Time: 9:19
 */

namespace Service\Model;
use Think\Model;

class HospitalModel extends Model
{
    protected $tableName = 'oa_hospital';
    protected $tablePrefix = '';

    //获取医院全称，递归解析
    function getHospitalById($wb_id){
        $condition = array(
           'wb_id' => $wb_id
        );

        $result = $this->where($condition)->getField('wb_id, stationary_name, parent_id');
        $stationary_name = $result[$wb_id]['stationary_name'];

        $parent_id = $result[$wb_id]['parent_id'];

        if($parent_id != 0){
            $parent_name = $this->getHospitalById($parent_id);

            $stationary_name = $parent_name.$stationary_name;

        }

        return $stationary_name;
    }
}