<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 15/3/21
 * Time: 上午9:34
 */

namespace Partner\Model;
use Think\Model;

class UserModel extends Model {
    protected $trueTableName = 'user';
    protected $dbName = 'yijiayi';


    //获取用户名字
    public function get_user_name($user_id){
        $condition = array(
            'id' => $user_id
        );
        $result = $this->where($condition)->getField('user_name');

        return $result;
    }
}