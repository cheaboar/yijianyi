<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/5/29
 * Time: 上午10:35
 */

namespace Service\Model;
use Think\Model;

class ServiceInfoModel extends Model {

    public function get_service_name($id){
        return $this->where('id='.$id)->getField('name');
    }


}