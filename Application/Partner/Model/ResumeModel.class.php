<?php
/**
 * Created by PhpStorm.
 * User: wind
 * Date: 2015/8/26
 * Time: 15:34
 */
namespace Partner\Model;
use Think\Model;

class ResumeModel extends Model {
    protected $tableName = 'resume';
    protected $tablePrefix = '';

    public function resumeList(){
        $list = $this->order("id desc")->select();
        return $list;
    }

    public function oneResume($data){
        $condition['user_id'] = $data['user_id'];
        $condition['position_id'] = $data['position_id'];
        $this->where($condition)->find();
    }
}