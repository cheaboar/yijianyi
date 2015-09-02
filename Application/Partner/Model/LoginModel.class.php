<?php
namespace Partner\Model;
use Think\Model;

class LoginModel extends Model {
    protected $tableName = 'user';
    protected $tablePrefix = '';

    public function getList(){
        $list = $this->select();
        return $list;
    }

    public function add_user_msg($data){
        $condition['openid']        = $data['openid'] ;
        $condition['nickname']      = $data['nickname'];
        $condition['header_url']    = $data['headimgurl'];
        $res = $this->data($condition)->add();
        return $res;
    }

    public function check_user($openid){
        $condition['openid'] = $openid;
        $res = $this->where($condition)->find();
        return $res;
    }
	
}