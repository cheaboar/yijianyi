<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/7/3
 * Time: 下午4:20
 */

namespace Service\Model;
use Think\Model;


class AdviceModel extends  Model{
    protected $tablePrefix = '';
    protected $tableName = 'oa_advice';

    function get_advices(){
        return $this->where('id > 0')->select();
    }

    function add_advice($user_id, $advice){
        $data = array(
            'user_id' => $user_id,
            'advice' => $advice
        );

        $this->add($data);

        if($this->getDbError() == ''){
            return array(
                'code' => 200
            );
        }else{
            return array(
                'code' => 500,
                'error_msg' => $this->getDbError()
            );
        }
    }
}