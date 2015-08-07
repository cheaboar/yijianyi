<?php
/**
 * Created by PhpStorm.
 * User: Ivy
 * Date: 2015/8/6
 * Time: 15:15
 */

namespace Service\Model;
use Think\Model;

class CommentModel extends Model
{
    protected $tableName= 'oa_comment';
    protected $tablePrefix = '';

    //��ȡְ��������
    public function getWorkerCommentLevels($worker_id){
        $condition = array(
            'comment_worker_id' => $worker_id,
        );
        return $this->where($condition)->getField('comment_id, profession_level, attitude_level, discipline_level');
    }

    //��ȡԱ������
    public function getWorkerComments($worker_id, $page, $page_step=5){
        $condition = array(
            'comment_worker_id' => $worker_id,
        );
        return $this->alias('c')->join('oa_user as u on c.comment_user_id = u.user_id', 'LEFT')->where($condition)->page($page, $page_step)->order('comment_id')
            ->getField('c.comment_id, c.* ,c.attitude_level,u.user_nickname');
    }

}