<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/1
 * Time: 16:22
 */

namespace Service\Model;
use Think\Model;

class AreasModel extends Model{
    protected $tablePrefix = '';
    protected $tableName = 'oa_areas';

    public function get_provinces(){
        return $this->where('parent_id=0')->select();
    }

    public function get_cities($provice_id){
        return $this->where('parent_id='.$provice_id)->select();
    }

    public function get_zones($city_id){
        return $this->where('parent_id='.$city_id)->select();
    }

    public function get_area_id($area_name){
        $condition = array(
            'area_name' => array('like', $area_name.'%')
        );

        return $this->where($condition)->getField('area_id');
    }
}