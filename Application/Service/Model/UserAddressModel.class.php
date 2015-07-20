<?php
/**
 * Created by PhpStorm.
 * User: cheaboar
 * Date: 2015/6/6
 * Time: 17:51
 */

namespace Service\Model;
use Think\Model;

class UserAddressModel extends Model{
    public function save_address($user_id, $province, $city, $zone, $stree, $contact_name=' ', $contact_phone=' '){
        $data = array(
            'user_id'=>$user_id,
            'province' => $province,
            'city' => $city,
            'zone' => $zone,
            'stree' => $stree,
            'contact_name' => $contact_name,
            'contact_phone' => $contact_phone
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

    //获取地址，并将地址给拼接起来
    public function get_address_string($address_id){
        $address = $this->where('id='.$address_id)->find();
        if($address == false || $address == null){
            return false;
        }else{
            $address_str = $address['province'].$address['city'].$address['zone'].$address['stree'].
                '   联系人：'.$address['contact_name'].'     手机：'.$address['contact_phone'];

            return $address_str;
        }
    }

    //获取用户的所有地址
    public function get_user_address_string($user_id){
        $addresses = $this->where('user_id='. $user_id .' AND status >= 0')->order('id desc')->select();

        $result  = array();

        foreach($addresses as $address){

            $address_str = $address['province'].$address['city'].$address['zone'].$address['stree'].
                '   联系人：'.$address['contact_name'].'     手机：'.$address['contact_phone'];

            $temp_address = array(
                'id' => $address['id'],
                'address' => $address_str
            );

            $result[] = $temp_address;
         }

        return $result;
    }

    public function get_address($address_id){
        return $this->where('id='.$address_id)->find();
    }
}