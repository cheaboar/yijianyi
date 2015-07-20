<?php
/**
 * Created by PhpStorm.
 * User: cheaboar
 * Date: 2015/6/7
 * Time: 23:46
 */

namespace Service\Model;
use Think\Model;
use Service\Model\UserAddressModel;
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
        $user_address = new UserAddressModel();
        $service_info = new ServiceInfoModel();
        $result = array();
        $condition['user_id'] = $user_id;
        $condition['state'] = $state;
        $my_appointments =  $this->where($condition)->order('id desc')->select();
//        dump($this->getLastSql());
        foreach($my_appointments as $appointment){
            $address = $user_address->get_address($appointment['address_id']);

            $appointment['address_str'] = $this->parse_address($address);
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
}