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

    //获取用户信息
    public function get_user_info($user_id){
        $condition = array(
            'id' => $user_id
        );
        $result = $this->where($condition)->find();

        return $result;
    }

    //获取用户
    public function get_users($page){
        return $this->page($page, 30)->order('id desc')->select();
    }

    //设置为管理员
    public  function set_as_manager($user_id, $passwd){
        $condition = array(
            'id'    => $user_id
        );
        $data  = array(
            'authority_level'   => '1',
            'passwd'            => $passwd
        );

        $this->where($condition)->save($data);
    }

    //设置为普通用户
    public function set_as_normal_user($user_id){
        $condition = array(
            'id'    => $user_id
        );
        $data  = array(
            'authority_level'   => '0',
            'passwd'            => null
        );

        $this->where($condition)->save($data);
    }

    //搜索用户
    public function search_user($search_text){
        $like_text = '%' . $search_text . '%';
        $condition['user_name'] = array('like', $like_text);
        $condition['nickname'] = array('like', $like_text);
        $condition['telephone'] = array('like', $like_text);
        $condition['_logic'] = 'or';

        return $this->where($condition)->order('id desc')->select();
    }

    //获取管理者
    public function get_managers(){
        $condition = array(
            'authority_level'   => 1
        );
        return $this->where($condition)->select();
    }

    //判断是否管理员
    public function is_manager($user_id){
        $condition = array(
            'id' => $user_id
        );

        $authority_level = $this->where($condition)->getField('authority_level');
        if($authority_level == 1){
            return true;
        }else{
            return false;
        }
    }

}