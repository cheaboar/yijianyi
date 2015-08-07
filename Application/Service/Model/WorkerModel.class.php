<?php
/**
 * Created by PhpStorm.
 * User: Chan
 * Date: 15/8/5
 * Time: 下午3:43
 */

namespace Service\Model;
use Think\Model;

class WorkerModel extends Model{
    protected $tablePrefix = '';
    protected $tableName = 'oa_worker';

    //获取用户的信息详情
    public function getWrokerInfo($worker_id){
        $condition = array(
            'worker_id' => 1
        );

        return $this->alias('w')->join('oa_areas as a1 on w.worker_domicile_province = a1.area_id', 'LEFT')
            ->join('oa_areas as a2 on w.worker_domicile_city = a2.area_id', 'LEFT')
            ->where($condition)->getField('w.*, a1.area_name as provinceName, a2.area_name as cityName');
    }
}