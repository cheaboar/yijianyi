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

    /*
     * 添加评论数据
     * */
    public function addComment($order_id, $content, $attitude, $profession, $discipline, $worker_id, $user_id){
        $data = array(
            'comment_order_id' => $order_id,
            'comment_content' => $content,
            'attitude_level' => $attitude,
            'profession_level' => $profession,
            'discipline_level' => $discipline,
            'comment_worker_id' => $worker_id,
            'comment_user_id' => $user_id,
            'comment_time' => time(),
        );

        $this->add($data);
    }

    /*
     * 判断是否已经评论过了
     * */
    public function is_commented($order_id, $worker_id, $user_id){
        $condition = array(
            'comment_order_id' => $order_id,
            'comment_worker_id' => $worker_id,
            'comment_user_id' => $user_id
        );

        $result = $this->where($condition)->find();
        if(empty($result)){
            return false;
        }else{
            return true;
        }
    }


    /*
     * 获取工作人员的评论
     * */
    public function get_comment_content($order_id, $worker_id){
        $condition = array(
            'comment_order_id' => $order_id,
            'comment_worker_id' => $worker_id
        );

        return $this->where($condition)->find();
    }
}