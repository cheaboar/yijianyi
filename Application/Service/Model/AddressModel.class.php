<?php
/**
 * Created by PhpStorm.
 * User: cheaboar
 * Date: 2015/7/2
 * Time: 0:30
 */

namespace Service\Model;
use Think\Model;
use Service\Model\AreasModel;


class AddressModel  extends Model{
    protected $tablePrefix = '';
    protected $tableName = 'oa_address';

    public function save_address_with_name($user_id, $province, $city, $zone, $address='', $is_default=0){
        $areaM = new AreasModel();

        $data = array(
            'user_id' => $user_id,
            'province' => $areaM->get_area_id($province),
            'city' => $areaM->get_area_id($city),
            'area' => $areaM->get_area_id($zone),
            'address' => $address,
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

    public function save_address($user_id, $province, $city, $zone, $address=' ', $is_default=0){
        $data = array(
            'user_id' => $user_id,
            'province' => $province,
            'city' => $city,
            'area' => $zone,
            'address' => $address,
        );

        $address_id = $this->add($data);
        if($this->getDbError() == ''){
            return array(
                'code' => 200,
                'address_id' => $address_id
            );
        }else{
            return array(
                'code' => 500,
                'error_msg' => $this->getDbError()
            );
        }
    }

    //将address对象转化为string
    //input:$address(array):地址对象
    //return (string):返回地址显示格式
    public function address_to_str($address){
        $areaM = new AreasModel();
        $province = $areaM->get_area_name($address['province']);
        $city = $areaM->get_area_name($address['city']);
        $area = $areaM->get_area_name($address['area']);

        $result = $province.' '.$city.' '.$area.' '.$address['address'];

        return $result;
    }

    //获取地址信息
    public function get_address($address_id){
        $condition = array(
            'address_id' => $address_id,
        );

        $address = $this->where($condition)->find();

        return $address;
    }

    public function get_user_address_string($user_id){
        $condition = array(
            'user_id' => $user_id,
            'status' => array('EGT', 0),
        );

        $addresses = $this->where($condition)->order('address_id desc')->select();
        $result = array();
        foreach($addresses as $address){
            $item = array();
            $item['id'] = $address['address_id'];
            $item['address'] = $this->address_to_str($address);

            $result[] = $item;
        }

        return $result;
    }

    public function delete_address($address_id){
        $condition = array(
            'address_id' => $address_id,
        );

        $data = array(
            'status' => -1,
        );

        $this->where($condition)->save($data);

    }

    /*
     * 获取地址信息，包含字符名称
     * */
    public function get_address_info($address_id){
        $condition = array(
            'address_id' => $address_id
        );

        $result = $this->alias('a')->join('oa_areas as a1 on a.province = a1.area_id', 'LEFT')
            ->join('oa_areas as a2 on a.city = a2.area_id', 'LEFT')
            ->join('oa_areas as a3 on a.city = a3.area_id', 'LEFT')
            ->where($condition)->getField('a.address_id, a.*, a1.area_name as provinceName, a2.area_name as cityName, a3.area_name as areaName');

        return $result[$address_id];

    }
}