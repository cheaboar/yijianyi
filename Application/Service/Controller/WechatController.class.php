<?php
namespace Service\Controller;



use Service\Model\AddressModel;
use Think\Controller;
use Think\Crypt\Driver\Think;
use Service\Model\ServiceInfoModel;
use Service\Model\CouponModel;
use Service\Model\FollowModel;
use Service\Model\Customer;
use Service\Model\UserAddressModel;
use Service\Model\AdviceModel;
use Service\Model\UserModel;
use Service\Model\AreasModel;
use Service\Model\OrderModel;
use Service\Model\ServiceAppointmentModel;
use Think\Exception;
use Org\Util\YunPian;

use Overtrue\Wechat\Server;
use Overtrue\Wechat\Auth;
use Overtrue\Wechat\Url;

require LIB_PATH.'Org/Util/wechat-master/autoload.php';

class WechatController extends Controller {
    protected $service_info;
    protected $user;
    protected $user_address;
    protected $service_appointment;
    protected $areas;
    protected $auth;
    protected $address;

    public function __construct(){
        parent::__construct();
        $this->service_info = new ServiceInfoModel();
        $this->user = new UserModel();
        $this->user_address = new UserAddressModel();
        $this->service_appointment = new ServiceAppointmentModel();
        $this->areas = new AreasModel();
        $this->address = new AddressModel();

        $this->auth = new Auth(C('SERVICE.APPID'), C('SERVICE.SECRET'));

    }

    public function index(){
        layout('Layout/layout');
        $title = '一家依服务平台';
        $this->assign('title', $title);
        $this->display('index');
    }

    public function user_info_test(){

        $user = $this->auth->authorize(); // 返回用户 Bag
        $user_to_save = serialize($user);
        $_SESSION['logged_user'] = $user_to_save;
//        $this->redirect('service_info');
//        echo 'fuck';
    }

    //授权获取用户的信息。
    private function get_user_info(){
        // 请一定要自己存储用户的登录信息，不要每次都授权
        if (empty($_SESSION['logged_user'])) {
//            $this->redirect('user_info_test');
            $user = $this->auth->authorize(); // 返回用户 Bag
            $user_to_save = serialize($user);
            $_SESSION['logged_user'] = $user_to_save;

            // 跳转到其它授权才能访问的页面
            $user_id = $this->user->add_wechat_user($user);
            $this->set_log($user_id);
        }


        return unserialize($_SESSION['logged_user']);
    }

    public function service_info(){
//        $this->get_user_info();
        layout('Layout/layout_home');
        $title = '一家依服务平台';

        $result = $this->service_info->order('priority')->select();
//        dump($result);
        $this->assign('services', $result);
        $this->assign('title', $title);
        $this->display('index');
    }

    public function service_detail(){
        layout('Layout/layout');
        $id = $_GET['id'];
        $service = $this->service_info->where('id='.$id)->find();
        $this->assign('service_id', $id);
        if($service['type'] >= 1000 and $service['type'] < 2000){
            layout('Layout/layout');
            $title = '月嫂服务详情';
            $this->assign('title', $title);
            $this->assign('main_image', $service['main_image']);
            $this->assign('max_price', $service['price_per_unit_max']);
            $this->assign('min_price', $service['price_per_unit_min']);
            $this->display('babysitter');
        }elseif($service['type'] >= 2000 and $service['type'] < 3000){
            $title = '服务详情';
            $service_name = '';
            $this->assign('title', $title);
            $this->assign('service_name', $service['name']);
            $this->assign('service_id', $id);
            $this->display('nurse_detail');
        
        }elseif($service['type'] >= 3000 and $service['type'] < 4000){
            $title = '服务详情';
            $service_name = '';
            $this->assign('title', $title);
            $this->assign('service_name', $service['name']);
            $this->assign('service_id', $id);
            $this->display('hospital_nurse_detail');
        }

    }

    public function appointment(){
//        $user = $this->get_user_info();
//        $this->need_login();
        $this->need_bind_phone();
//        $user_id = $this->user_id($user['openid']);
//        $this->assign('user_id', $user_id);
        layout('Layout/layout');
        $service_id = $_GET['service_id'];
        $title = '预约服务';
        $service = $this->service_info->where('id='.$service_id)->find();
        $service_name = $service['name'];
        $service_price_per_unit = $service['price_per_unit'];
        $service_unit = $service['unit'];

        $this->assign('service_name', $service_name);
        $this->assign('service_price_per_unit', $service_price_per_unit);
        $this->assign('service_unit', $service_unit);
        $this->assign('title', $title);
        $this->assign('service_id', $service_id);

        $this->display('appointment');
    }

    public function address_test(){
        $user_id = 17;

        $address = $this->address->get_user_address_string($user_id);
//            $address = $this->user_address->get_user_address_string($user_id);
        $this->ajaxReturn($address);
    }
    public function user_address(){
        if(IS_GET){
            $user_id = $this->user_id();

            $address = $this->address->get_user_address_string($user_id);
//            $address = $this->user_address->get_user_address_string($user_id);
            $this->ajaxReturn($address);
        }
        if(IS_POST){
            $params = json_decode(file_get_contents( "php://input"), true);
            $params['user_id'] = $this->user_id();
//            dump($params);
            $province = $params['province'];
            $city = $params['city'];
            $zone = $params['zone'];
            $stree = $params['stree'];
            $contact_name = $params['contact_name'];
            $contact_phone = $params['contact_phone'];
//            $this->user_address->add($params);
            $restul = $this->address->save_address($this->user_id(),$province, $city, $zone, $stree);
//            $restul = array();
//            $restul['status'] = 200;
            $this->ajaxReturn($restul);
        }

    }

    public function commit_appointment(){
        $params = json_decode(file_get_contents( "php://input"), true);
        $address_id = $params['addressId'];
        $data = Array();
        $data['user_id'] = $this->user_id();
        $data['name'] = $params['name'];
        $data['easy_time'] = $params['easy_time'];
        $data['phone'] = $params['phone'];
        $data['relationship'] = $params['relationship'];
        $data['address_id'] = $params['addressId'];
        $data['service_id'] = $params['serviceId'];
        $data['confirm_time'] = date('y-m-d h:i:s',time());
        $appointment_id = $this->service_appointment->add($data);

        $result = Array();
        if($this->service_appointment->getError() == ''){
            $result['status'] = 200;
            $result['appointment_id'] = $appointment_id;
        }else{
            $result['status'] = 500;
        };

        $this->ajaxReturn($result);

    }


    public function nurse_detail(){
        layout('Layout/layout');
        $this->display('nurse_detail');
    }

    public function babysitter(){
        layout('Layout/layout');
        $title = '月嫂服务详情';
        $this->assign('title', $title);
        $this->display('babysitter');
    }

    public function my_appointment(){
        $this->need_login();
        layout('Layout/layout');
        $title = '我的预约';
        $this->assign('title', $title);
        $this->display('my_appointment');
    }

    public function my_signed_appointment(){
        $this->need_login();
        layout('Layout/layout');
        $title = '我的服务';
        $this->assign('title', $title);
        $this->display('my_signed_appointment');
    }


    private function set_log($user_id){
        session('login', $user_id);
    }

    private function is_log(){
        return session('login');
    }

    private function user_id(){
        return session('login');
    }

    private function  set_log_out(){
        session('login', null);
    }

    public function login(){
        if(IS_POST){
            $result = Array();
            $data = json_decode(file_get_contents('php://input'), true);
            //TODO:迁徙数据库表：done
            $user_data = $this->user->get_passwd_user_id($data['name']);

            if(md5($data['passwd']) == $user_data['passwd']){
                $result['status'] = 200;
                $this->set_log($user_data['user_id']);
            }else{
                $result['status'] = 300;
                $result['message'] = '密码和用户名不匹配';
            }
            $this->ajaxReturn($result);

        }elseif(IS_GET){
            layout('Layout/layout');
            $title = '登录';
            $this->assign('title', $title);
            $this->display('login');
        }

    }


    private function need_login(){
//        if($this->is_log()){
//            return ture;
//        }else{
//            $this->redirect('login');
//        }
        $this->get_user_info();
    }

//    返回值：status：200->正常
//            status：500 -> 插入不成功
//            status：300->用户名已存在
    public function sign_up(){
        if(IS_GET){
            layout('Layout/layout');
            $title = '注册';
            $this->assign('title', $title);
            $this->display('sign_up');
        }elseif(IS_POST){
            $params = json_decode(file_get_contents('php://input'), true);
            $user_name = $params['userName'];
            //TODO:迁徙数据库表:done
//            $user = $this->user->where('login_name="'.$user_name.'"')->select();
            $return = array();
            if(!$this->user->user_exit($user_name)){
                //没有用户
                $data = array();
                //TODO:迁徙数据库表：done
                $user_id = $this->user->add_user($user_name, $params['passwd']);
//                dump($user_id);

                if($user_id > 0){
                    $return['status'] = 200;
                    $this->set_log($user_id);
                }else{

                    $return['status'] = 500;
                }
            }else{
                $return['status'] = 300;
            }

            $this->ajaxReturn($return);


        }

    }

    public function about_us(){
        layout('Layout/layout');
        $this->display('about_us');
    }
    public function user_center(){
        $this->need_login();
        $user_id = $this->user_id();
        //TODO:迁徙数据库表:done
        $user = $this->user->get_user_info($user_id);

        foreach($user as $i => $value){
            $user = $value;
        }

        if($user['sex'] == 1){
            $user['sex'] = '男';
        }elseif($user['sex'] == 2){
            $user['sex'] = '女';
        }else{
            $user['sex'] = '未知';
        }
        $this->assign('user', $user);
//        dump($user);
        layout('Layout/layout');
        $this->display('user_center');
    }

    public function new_user_center(){
//        $this->need_login();
        $user_id = 17;
        //TODO:迁徙数据库表:done
        $user = $this->user->get_user_info($user_id);

        foreach($user as $i => $value){
            $user = $value;
        }

        if($user['sex'] == 1){
            $user['sex'] = '男';
        }elseif($user['sex'] == 2){
            $user['sex'] = '女';
        }else{
            $user['sex'] = '未知';
        }
        $this->assign('user', $user);
//        dump($user);
        layout('Layout/new_layout');
        $this->display('User:user_center');
    }

    public function logout(){
        $this->set_log_out();
        $this->redirect('service_info');
    }

    public function save_user_info(){
        $this->need_login();
        $params = json_decode(file_get_contents('php://input'), true);
        $data = array(
            'user_name' => $params['user_name'],
            'sex' => $params['sex']
        );
        //TODO:迁徙数据库表:done
        $this->user->update_user_info($this->user_id(), $params['user_name'], $params['sex']);
//        $this->user->update_user_info($this->user_id(), $params['nick_name'], $params['sex']);

        $result = array();
        if($this->user->getDbError() == ''){
            $result['code'] = 200;
        }else{
            $result['code'] = 500;
            $result['error_msg'] = $this->user->getDbError();
        }
        $this->ajaxReturn($result);
    }

    /*
     * 将地址拼接起来
     * @ $address数据库查询的一行结果
     * */
    private function address_join($address){
        $result = array();
        try{
            $result['code'] = 200;
            $result['address'] = $address['province'] . '' . $address['city'].''.$address['area'].$address['twon'].'   '.
                $address['stree'] .'联系人:'.$address['contact_name'].'   电话：'.$address['contact_phone'];

            return $result;
        }catch (Exception $e){
            $result['code'] = 500;
            $result['error_msg'] = $e->getMessage();
        }
    }

    public function get_my_address(){
        $this->need_login();
        $user_id = $this->user_id();
//        $addresses = $this->user_address->where('user_id='.$user_id.' AND status >= 0')->order('id desc')->select();
        $addresses = $this->address->get_user_address_string($user_id);
//        $result = array();
//        foreach($addresses as $address){
//            $new_address = '';
//            $parsed_address = $this->address_join($address);
//            if($parsed_address['code'] == 200){
//                $new_address = $parsed_address['address'];
//            }elseif($parsed_address['code'] == 500){
//                $new_address = '';
//                dump($parsed_address['error_msg']);
//            }
//            $address_item = array(
//                'id' => $address['id'],
//                'code'=>200,
//                'address' => $new_address
//            );
//            $result[] = $address_item;
//        }

        $this->ajaxReturn($addresses);
    }

    public function my_address(){
        $this->need_login();
        layout('Layout/layout');
        $this->display('my_address');
    }

    public function delete_my_address(){
        $this->need_login();
//        $user_id = $this->user_id();

//        $data = array();
//        $data['status'] = -1;

//        $this->user_address->where('id='.$_GET['id'] .' AND user_id='.$user_id)->save($data);
        $this->address->delete_address($_GET['id']);
        if($this->user_address->getDbError() == ''){
            return array(
                'code' => 200
            );
        }else{
            return array(
                'code' => 500
            );
        }


    }

    public function get_province(){
        $provice = $this->areas->get_provinces();
        $this->ajaxReturn($provice);
    }

    public function get_cities(){
        $cities = $this->areas->get_cities($_GET['province_id']);

        $this->ajaxReturn($cities);
    }

    public function get_zones(){
        $zones = $this->areas->get_zones($_GET['city_id']);
        $this->ajaxReturn($zones);
    }

    public function save_address(){
        $this->need_login();
//        $result = $this->user_address->save_address(
//            $this->user_id(), $_GET['province'], $_GET['city'], $_GET['zone'], $_GET['stree'],
//            $_GET['contact_name'],$_GET['contact_phone']);
//        $address = new AddressModel();

        $province = $_GET['province_id'];
        $city = $_GET['city_id'];
        $zone = $_GET['zone_id'];
        $stree = $_GET['stree'];

        $result = $this->address->save_address($this->user_id(), $province, $city, $zone, $stree);
        $this->ajaxReturn($result);
    }

    public function get_my_appointments(){
        $this->need_login();
        $user_id = $this->user_id();
        $result = $this->service_appointment->get_my_appointments($user_id, 1000);
        $this->ajaxReturn($result);
    }

    public function get_my_signed_appointments(){
        $this->need_login();
        $user_id = $this->user_id();
        $result = $this->service_appointment->get_my_appointments($user_id, 3000);
        $this->ajaxReturn($result);
    }

    public function delete_appointment(){
        $this->service_appointment->delete_appointment($_GET['id']);
        if($this->service_appointment->getDbError() == ''){
            $this->ajaxReturn(array('code' => 200));
        }else{
            $this->ajaxReturn(array('code' => 500, 'error_msg'=>$this->service_appointment->getDbError()));
        }
    }

    public function test(){
        dump($this->user_address->get_address_string(18));
        dump($this->user_address->get_address(18));
//        dump($this->service_appointment->get_my_appointments(103));

        layout('Layout/layout');
        $this->display('test');
    }


    public function my_coupon(){
        $this->need_login();
        layout('Layout/layout');
        $this->display('my_coupon');
    }

    //获取我的优惠券
    public function get_my_coupon(){
        $this->need_login();
        $user_id = $this->user_id();
        $coupon = new CouponModel();
        $results = $coupon->get_coupon($user_id);
        $return = array();
        foreach($results as $result){

//            $result['coupon_expire'] = time();

            $is_out_of_line = false;
            if($result['coupon_expire'] < time()){
                $today = getdate();
                $expire_day = getdate($result['coupon_expire']);

                if($today['year'] == $expire_day['year'] && $today['mon'] == $expire_day['mon'] && $today['mday'] == $expire_day['mday']){
                    $is_out_of_line = false;
                }

                $is_out_of_line = true;
            }

            if(!$is_out_of_line && $result['has_used'] == 0){
                $result['state'] = '未使用';
            }elseif(!$is_out_of_line && $result['has_used'] == 1){
                $result['state'] = '已使用';
            }else if($is_out_of_line ){
                $result['state'] = '已过期';
            }

            $result['expire'] = date('Y-m-d', $result['coupon_expire']);

            $return[] = $result;
        }




        $this->ajaxReturn($return);

    }

    public function my_follow(){
        $this->need_login();
        $user_id = $this->user_id();
        layout('Layout/layout');

        $this->display('my_follow');
    }


    public function get_my_follows(){
        $this->need_login();
        $user_id = $this->user_id();
        $followR = new FollowModel();
        $result = $followR->get_follow($user_id);
        $this->ajaxReturn($result);
    }


    public function advice(){
        $this->need_login();
        $user_id = $this->user_id();
        layout('Layout/layout');
        $this->display('advice');
    }

    public function add_advice(){
        $this->need_login();
        $user_id = $this->user_id();
        $adviceR = new AdviceModel();
        $params = json_decode(file_get_contents('php://input'), true);

        $result = $adviceR->add_advice($user_id, $params['advice_str']);

        $this->ajaxReturn($result);
    }

    //设置验证码
    private function set_validate_code($random_code, $expire=90){
        $user_id = $this->user_id();
        $key = 'validate_user_id_'.$user_id;

        S($key, $random_code, $expire);
    }

    //获取验证码
    private function get_validate_code(){
        $user_id = $this->user_id();
        $key = 'validate_user_id_'.$user_id;
        return S($key);
    }

    //缓存注册的手机号码
    private function set_temp_phone($phone, $expire=90){
        $user_id = $this->user_id();
        $key = $user_id.'phone';
        S($key, $phone, $expire);
    }

    //获取缓存的手机号码
    private function get_temp_phone(){
        $user_id = $this->user_id();
        $key = $user_id.'phone';
        return S($key);
    }

    public function send_msg(){
        $this->need_login();
        $user_id = $this->user_id();
        $yunpian = new YunPian();

        $phone = $_GET['new_phone'];

        $random_code = rand(1000, 9999);

        $result = $yunpian->send_sms(C('YUNPIAN.APIKEY'),  '【一家依】您的验证码是'.$random_code, $phone);

        $result = array(
            code => 0
        );
        if($result['code'] == 0){
            $this->set_validate_code($random_code, 90);
            $this->set_temp_phone($phone, 90);
            $this->ajaxReturn(array(
                'code' => 200,
            ));
        }else{
            $this->ajaxReturn( array(
                'code' => $result['code'],
                'error_msg' => $result['msg']
            ));
        }
    }

    public function validate_phone_code(){
        $code = $_GET['code'];
        $cache_code = $this->get_validate_code();

        if($cache_code == $code){
            //存储手机号码
            $phone = $this->get_temp_phone();
            //TODO:迁徙数据库表:done
            $result = $this->user->update_phone($this->user_id(), $phone);
            if($result['code'] == 200){
                $this->ajaxReturn( array(
                    code => 200,
                ));
            }elseif($result['code'] == 500){
                $this->ajaxReturn(array(
                    code => 500,
                    error_msg => $result['error_msg']
                ));
            }


        }else{
            $this->ajaxReturn(array(
                code => 500,
                error_msg => '验证码不正确'
            ));
        }
    }



    //关注的订单详情
    public function order_detail(){
//        $this->need_login();
        $order_id = $_GET['order_id'];
        $orderM = new OrderModel();
        $order_detail = $orderM->get_order_detail($order_id);


        $this->assign('order', $order_detail);
        $this->assign('title', '订单');
        $this->display('Order:order_detail');
    }


    //我关注的订单
    public function my_orders(){
        $this->need_login();
        $user_id  = $this->user_id();
//        $user_id  = 17;
        $orderM = new OrderModel();

        $follow_orders = $orderM->get_my_follow_orders($user_id);
        $this->assign('orders', $follow_orders);
        $this->display('Order:my_follow_orders');
    }

    //需要绑定手机
    private function need_bind_phone(){
        session('request', $_SERVER['REQUEST_URI']);

        $this->need_login();
        $user_id = $this->user_id();

        if($this->user->is_bind_phone($user_id)){
            //已经绑定手机则不作为
        }else{
            //未绑定手机，先存储当前访问的路径，绑定后跳转
            $this->redirect('bind_phone');
        }

    }

    //绑定手机
    public function bind_phone(){

        $this->need_login();

        $this->assign('request_uri', session('request'));
        $this->display('bind_phone');
    }

    public function new_my_service(){
//        $this->need_login();
        $user_id = 17;
        //获取订单列表
        $orderM = new OrderModel();
        $orders = $orderM->get_user_orders($user_id);
        $result = array();

        $order_status = C('ORDER.STATUS');

        foreach($orders as $order){
            $order['order_status'] = $order_status[$order['order_status']];
            $order['add_time'] = time_stamp_to_str('Y-m-d H-i-s',$order['add_time']);
            $result[] = $order;

        }
        //获取关注用户
        $followM = new FollowModel();
        $follows = $followM->get_follow($user_id);

        layout('Layout/new_layout');
        $this->assign('orders', $result);
        $this->assign('follows', $follows);

        $this->display('Order:my_service');
    }

    public function my_order_detail(){
//        $this->need_login();
        $order_id = $_GET['order_id'];
        $orderM = new OrderModel();
        $order_detail = $orderM->get_order_detail($order_id);


        $this->assign('order', $order_detail);
        $this->assign('title', '订单');
        layout('Layout/new_layout');
        $this->display('Order:my_order_detail');
    }

    public function new_service_info(){
        layout('Layout/new_layout');

        $this->display('service_info');
    }

    //客户详细信息
    public function customer_detail(){
        $customer_id = $_GET['customer_id'];
        echo '用户详细信息';
    }

    //添加关注客户
    public function add_follow_customer(){
        echo '添加关注的人';
    }


}