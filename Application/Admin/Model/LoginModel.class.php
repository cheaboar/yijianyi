<?php
namespace Admin\Model;
use Think\Model;

class LoginModel extends Model { 
	function test($name,$password){
		$Show =M('cheng_admin','cheng.');
		$condition['name']		= $name;
		$condition['password']	= $password;
		echo($res = $Show->where($condition)->find());
		return $res;
	}
	function register($parent_id){

	} 
	
	
}