<?php
/**
 * Created by PhpStorm.
 * User: cheaboar
 * Date: 2015/6/6
 * Time: 17:50
 */

namespace Service\Model;
use Think\Model;
use Service\Model\AreasModel;


class UserModel extends Model {
    protected $tablePrefix = '';
    protected $tableName = 'oa_user';


    //不能匹配相同的，否则不返回，只能返回时的映射，不能用直接用于查询。
    protected $_map = array(
        'id' => 'user_id',
        'phone' => 'user_phone',
        'sex' => 'user_sex',
//        'wechat_openid' => 'user_weixin',
      );

    //更新数据库
    public function update($condition,$data){
        $this->where($condition)->save($data);
    }

    //更新用户信息
    public function update_user_info($user_id, $user_name, $sex){
        $data = array(
            'user_name' => $user_name,
            'user_sex' => $sex
        );
        $this->where('user_id='.$user_id)->save($data);
    }

    //获取用户信息
    public function get_user_info($user_id){
        $result = $this->alias('user')
            ->join('left join oa_areas as a1 on user.user_province = a1.area_id')
            ->join('left join oa_areas as a2 on user.user_city = a2.area_id')
            ->where('user.user_id='.$user_id)->getField('user_id, user_name, user_phone as phone,
            a1.area_name as provinceName, a2.area_name as cityName,
             user_icon, user_nickname, user_sex as sex');

        foreach($result as $i => $value){
            $result = $value;
        }

        $sex_conf = C('SEX');

        $result['sex'] = $sex_conf[$result['sex']];

        return $result;
    }




    //新增微信用户，判断数据库是否存在此用户，存在则更新用户信息，不存在则插入会此用户的信息。
    //@user:微信用户信息
    //return:插入情况。
    public function add_wechat_user($user){
        $condition = array(
            'wechat_openid' => $user['openid']
        );

        $area = new AreasModel();

        $user_in_db = $this->where($condition)->find();

        if(empty($user_in_db)){
            //创建新用户
            $data = array(
                'user_name' => $user['nickname'],
                'user_nickname' => $user['nickname'],
//                'wechat_head_image' => $user['headimgurl'],
                'user_icon' => $user['headimgurl'],
                'wechat_openid' => $user['openid'], //这两个字段还不确定要哪个，目前，使用了user_weixin
                'user_weixin' => $user['openid'],   //
                'user_sex' => $user['sex'],
                'user_province' => $area->get_area_id($user['province']),
                'user_city' => $area->get_area_id($user['city']),
                'user_country' => $user['country'],
                'wechat_privilege' => $user['privilege'],
                'wechat_unionid' => $user['unionid'],
                'user_last_visit_time' => time()

            );
            $this->add($data);

        }else{
            //更新用户信息,性别、地区不更新，因为在用户中心那里用户可以修改自己的性别和地区
            $data = array(
                'user_nickname' => $user['nickname'],
//                'wechat_head_image' => $user['headimgurl'],//这个字段确定不用了
                'user_icon' => $user['headimgurl'],
                'user_last_visit_time' => time()

            );
            $this->where($condition)->save($data);
        }

        return $this->where($condition)->getField('user_id');
    }


    //新增用户
    public function add_user($user_name, $passwd){
        $data['user_name'] = $user_name;
        $data['password'] = md5($passwd);

        $user_id = $this->add($data);
//        dump($this->getDbError());
//        dump($this->getLastSql());
        return $user_id;
    }

    //根据登录名判断用户是否存在
    public function user_exit($user_name){
        $result = $this->where('user_name="'.$user_name.'"')->select();
        if($result == null){
            return false;
        }else{
            return true;
        }


    }

    //获取用户密码,已经废弃了
    //$user_name:用户登录名
    public function get_passwd_user_id($user_name){

        $data = $this->where('user_name="'. $user_name . '"')->find();


        $result['user_id'] = $data['id'];
        $result['passwd'] = $data['password'];
        return $result;
    }

    //将微信用户号码绑定如果系统有原来的号码，则将原来的信息保存，微信账户删除，删除之前应该把涉及到的user_id的表给更新一下
    //TODO:长久的TODO，因为用户表设计的缺陷导致的在合并用户信息的时候需要更新相关的表。
    private function user_merge($persist_user_id, $delete_user_id){

        $model = new Model();

        $update_sql = array(
            'oa_address' => 'user_id',
            'oa_advice' => 'user_id',
            'oa_coupon' => 'user_id',
            'oa_evaluate' => 'user_id',
            'oa_follow' => 'user_id',
            'oa_order' => 'user_id',
            'yjy_service_appointment' => 'user_id',
        );

        foreach($update_sql as $key => $value){
            $sql = 'update '.$key.' set '.$value. '=' .$persist_user_id. ' where '.$value .'='.$delete_user_id;
            $model->execute($sql);
        }


    }

    //需要判断数据库中是否已经有此手机号码了，如果有，则将用户合并用户
    public function update_phone($user_id, $phone){
        $condition = array(
            'user_phone' => $phone
        );

        $temp_user = $this->where($condition)->find();

        if(!empty($temp_user)){
            //如果号码存在，那么微信用户信息要更新到原有的用户中，并将微信账户的信息删除，变化部分应该封装起来
            //将微信信息更新到原有用户中去
            $new_condition = array(
                'user_id' => $user_id,
            );

            $wechat_user_data = $this->where($new_condition)->getField('user_id, user_icon, user_nickname, user_weixin, wechat_openid');
            $wechat_user_data = $wechat_user_data[$user_id];
            $update_data = array(
                'user_icon' => $wechat_user_data['user_icon'],
                'user_weixin' => $wechat_user_data['user_weixin'],
                'wechat_openid' => $wechat_user_data['wechat_openid'],
                'user_nickname' => $wechat_user_data['user_nickname'],
            );
            $this->where($condition)->save($update_data);
            //封装变化
            $this->user_merge($temp_user['id'], $user_id);

            //删除微信账户信息
            $this->where($new_condition)->delete();

            //更新session
            session('login', $temp_user['id']);
        }

        $this->where('user_id='.$user_id)->save(array(
            user_phone => $phone
        ));

        $return_data = array();
        if($this->getDbError() == ''){
            $return_data['code'] = 200;
        }else{
            $return_data['code'] = 500;
            $return_data['error_msg'] = $this->getDbError();
        }

        return $return_data;
    }
    //是否已经绑定了手机
    public function is_bind_phone($user_id){
        $condition = array(
            'user_id' => $user_id,
        );
        $phone = $this->where($condition)->getField('user_phone');

        if(empty($phone)){
            return false;
        }else{
            return true;
        }
    }

    public function update_name($user_id, $name){
        $conditon = array(
            'user_id' => $user_id
        );

        $data = array(
            'user_name' => $name
        );

        $this->where($conditon)->save($data);
    }

    public function update_sex($user_id, $sex){
        $conditon = array(
            'user_id' => $user_id
        );

        $data = array(
            'user_sex' => $sex
        );

        $this->where($conditon)->save($data);
    }

    //更新用户的地址
    public function update_user_area($user_id, $privince_id, $city_id){
        $condition = array(
            'user_id' => $user_id,
        );

        $data = array(
            'user_province' => $privince_id,
            'user_city' => $city_id,
        );

        $this->where($condition)->save($data);
    }

    public function get_user_openid($user_id){
        $condition = array(
            'user_id' => $user_id,
        );

        return $this->where($condition)->getField('user_weixin');
    }

    public function get_user_id($open_id){
        $condition = array(
            'user_weixin' => $open_id,
        );

        return $this->where($condition)->getField('user_id');
    }


    /*
     * 判断用户是否存在数据库中了
     * */

    public function is_user_info_exit($open_id){
        $condition = array(
            'user_weixin' => $open_id,
        );
        $result = $this->where($condition)->find();

        if(empty($result)){
            return false;
        }else{
            return true;
        }
    }
}