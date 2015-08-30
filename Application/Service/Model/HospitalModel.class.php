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

    //��ȡҽԺȫ�ƣ��ݹ����
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

    //获取科室或医院名，返回数组
    function getHospitalNameById($wb_id){
        $condition = array(
            'wb_id' => $wb_id
        );

        $result = $this->where($condition)->getField('wb_id, stationary_name, parent_id');
        $stationary_name = $result[$wb_id]['stationary_name'];

        return $stationary_name;
    }

    //返回医院列表
    function getHospitals(){
        $condition = array(
            'parent_id' => 0
        );

        $result = $this->where($condition)->select();

        return $result;
    }

    //返回科室
    function getDepartments($hospital_id){
        $condition = array(
            'parent_id' => $hospital_id
        );

        $result = $this->where($condition)->select();

        return $result;
    }
}