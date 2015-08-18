<?php
/**
 * Created by PhpStorm.
 * User: cheaboar
 * Date: 2015/6/7
 * Time: 23:46
 */

namespace Service\Model;
use Think\Model;
use Service\Model\AddressModel;
use Service\Model\ServiceInfoModel;

class ServiceAppointmentModel extends Model{
    //获取用户的预约

    private function parse_address($address){
        if($address == false || $address == null){
            return false;
        }else{
            $address_str = $address['province'].$address['city'].$address['zone'].$address['stree'].
                '   联系人：'.$address['contact_name'].'     手机：'.$address['contact_phone'];

            return $address_str;
        }
    }


    public function get_my_appointments($user_id, $state){
        $addressM = new AddressModel();
        $service_info = new ServiceInfoModel();
        $result = array();
        $condition['user_id'] = $user_id;
        $condition['state'] = $state;
        $my_appointments =  $this->where($condition)->order('id desc')->select();

        foreach($my_appointments as $appointment){
            $address = $addressM->get_address($appointment['address_id']);

            $appointment['address_str'] = $addressM->address_to_str($address);
            $appointment['address'] = $address;
            $appointment['service_name'] = $service_info->get_service_name($appointment['service_id']);
            $result[] = $appointment;
        }

        return $result;
    }

    //TODO:删除预约，这里可以要设置state还是直接删除待讨论，暂且设置state
    public function delete_appointment($id){

        $condition = array(
            'id' => $id,
            'state' => 1000
        );

        $data = array(
            'state' =>  -1
        );

        $this->where($condition)->save($data);

    }

    /*
     * 获取所有未删除的服务
     * */
    public function get_my_appointment($user_id){
//        $stateR = C('APPOINTMENT_STATE_R');

//        $delete_state = $stateR['删除'];
        $condition = array(
            'state' => array('NEQ', -1),
            'user_id' => $user_id
        );

        return $this->where($condition)->order('id desc')->select();
    }

    /*
     * 添加服务预约
     * 返回新增的id
     * */
    public function add_appointment($user_id, $address_id, $name, $phone, $service_type){
        $data = array(
            'user_id'       => $user_id,
            'address_id'    => $address_id,
            'name'          => $name,
            'phone'         => $phone,
            'service_type'  => $service_type,
            'state'         => 1000,
            'create_time'   => time(),
        );

        $id = $this->add($data);
        return $id;
    }

    /*
     * 获取预约细节
     * */
    public function get_appointment_detail($appointment_id){
        $condition = array(
            'id' => $appointment_id,
//            'user_id' => $user_id
        );

        $result =  $this->alias('appointment')->join('oa_address as address on appointment.address_id = address.address_id', 'LEFT')
            ->join('oa_areas as a1 on address.province = a1.area_id', 'LEFT')
            ->join('oa_areas as a2 on address.city = a2.area_id', 'LEFT')
            ->join('oa_areas as a3 on address.area = a3.area_id', 'LEFT')
            ->where($condition)->getField('appointment.*, a1.area_name as provinceName, a2.area_name as cityName, a3.area_name as areaName, address.address as stree');

        return $result[$appointment_id];
    }
}