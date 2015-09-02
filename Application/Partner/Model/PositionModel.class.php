<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/26
 * Time: 15:46
 */
namespace Partner\Model;
use Think\Model;

class PositionModel extends Model {
    protected $tableName = 'position';
    protected $tablePrefix = '';

    public function positionList(){
        $list = $this->order("id desc")->select();
        return $list;
    }

    public function onePosition($id){
        $condition['id'] = $id;
        $res = $this->where($condition)->find();
        return $res;
    }
}