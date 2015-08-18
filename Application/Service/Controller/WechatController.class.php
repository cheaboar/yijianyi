<?php
namespace Service\Controller;



use Service\Model\AddressModel;
use Service\Model\OrderCollectionModel;
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
use Service\Model\WorkerModel;
use Service\Model\OrderModel;
use Service\Model\ServiceAppointmentModel;
use Think\Exception;
use Org\Util\YunPian;
use Service\Model\HospitalModel;
use Service\Model\CommentModel;
use Service\Model\WorkerOrderModel;

use JsSdk;
use Think\Log;
use UnifiedOrderPub;
use WxPayConfPub;
use WxPayUnifiedOrder;
use WxPayConfig;
use WxPayApi;
use WxPayJsApiPay;
use WxPayException;

use Overtrue\Wechat\Server;
use Overtrue\Wechat\Auth;
use Overtrue\Wechat\Js;
use Overtrue\Wechat\Utils\XML;

require LIB_PATH.'Org/Util/wechat-master/autoload.php';
require_once LIB_PATH."Org/Util/WxpayAPI_php_v3/lib/WxPay.Data.php";
require_once LIB_PATH."Org/Util/WxpayAPI_php_v3/lib/WxPay.Config.php";
require_once LIB_PATH."Org/Util/WxpayAPI_php_v3/lib/WxPay.Api.php";


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
            $user_to_save = serialize($user['openid']);
            $_SESSION['logged_user'] = $user_to_save;

            // 跳转到其它授权才能访问的页面
            $user_id = $this->user->add_wechat_user($user);
            $this->set_log($user_id);
        }

//        if (empty($_SESSION['logged_user'])) {
////            $this->redirect('user_info_test');
//            $user = $this->auth->authorize(null,'snsapi_base'); // 返回用户 Bag
//            $user_to_save = serialize($user);
//            $_SESSION['logged_user'] = $user_to_save;
//            $user_id = null;
//            //判断用户数据是否已经在数据库中了，如果存在数据库中了不需要更行数据库，如果不存在则发起详情页的授权
//            if(!$this->user->is_user_info_exit($user['openid'])){
//                $user = $this->auth->authorize(); // 返回用户 Bag
//                $user_id = $this->user->add_wechat_user($user);
//            }else{
//                //获取用户的id
//                $user_id = $this->user->get_user_id($user['openid']);
//            }
//            // 跳转到其它授权才能访问的页面
//
//            $this->set_log($user_id);
//        }



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
        $user_id = $this->user_id();

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
        layout('Layout/new_layout');
        $title = '我的预约';
        $this->assign('title', $title);


        $user_id  = $this->user_id();
        //获取所有的预约列表
        $my_appointments = $this->service_appointment->get_my_appointment($user_id);
        $result = array();
        foreach($my_appointments as $appointment){
            $result[] = parse_appointment($appointment);
        }



        $this->assign('appointments', $result);

        $this->display('User:user_appointment');
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

//        foreach($user as $i => $value){
//            $user = $value;
//        }

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
        $this->need_login();
        $user_id = $this->user_id();
        //TODO:迁徙数据库表:done
        $user = $this->user->get_user_info($user_id);

//        foreach($user as $i => $value){
//            $user = $value;
//        }
//
//        if($user['sex'] == 1){
//            $user['sex'] = '男';
//        }elseif($user['sex'] == 2){
//            $user['sex'] = '女';
//        }else{
//            $user['sex'] = '未知';
//        }
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
//        $this->need_login();
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
//        layout('Layout/layout');
//        $this->display('my_address');
        layout('Layout/new_layout');

        $user_id = $this->user_id();
        $addresses = $this->address->get_user_address_string($user_id);

        $this->assign('addresses', $addresses);

        $this->display('User:user_address');
    }

    public function delete_my_address(){
        $this->need_login();
//        $user_id = $this->user_id();

//        $data = array();
//        $data['status'] = -1;

//        $this->user_address->where('id='.$_GET['id'] .' AND user_id='.$user_id)->save($data);
        $this->address->delete_address($_GET['id']);
        $result = array();
        if($this->user_address->getDbError() == ''){
             $result = array(
                'code' => 200,
                'id' => $_GET['id']
            );
        }else{
            $result = array(
                'code' => 500
            );
        }

        $this->ajaxReturn($result);


    }

    public function get_province(){
        $provice = $this->areas->get_provinces();
        $this->ajaxReturn($provice);
    }

    public function get_cities(){
        $province_name = $_GET['province_name'];
        $province_id = $_GET['province_id'];

        if(!empty($province_name)){
            $province_id = $this->areas->get_area_id($province_name);

        }

        $cities = $this->areas->get_cities($province_id);


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
        $user_id = $this->user_id();
        $result = $this->address->save_address($user_id, $province, $city, $zone, $stree);
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
        $user_id = $this->user_id();
        layout('Layout/new_layout');

        //获取优惠券
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

        $this->assign('coupons', $return);

        $this->display('User:user_coupon');
    }

    //获取我的优惠券
    public function get_my_coupon(){
//        $this->need_login();
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
        layout('Layout/new_layout');

        $this->assign('title', '吐槽建议');
        $this->display('Service:advice');
    }

    public function add_advice(){
        $this->need_login();
        $user_id = $this->user_id();
        $adviceR = new AdviceModel();

        $result = $adviceR->add_advice($user_id, $_POST['advice_str']);

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
        $this->need_login();
        $order_id = $_GET['order_id'];
        $orderM = new OrderModel();
        $user_id = $this->user_id();
        $order_detail = $orderM->get_order_detail($order_id, $user_id);


        $this->assign('order', $order_detail);
        $this->assign('title', '订单');
        $this->display('Order:order_detail');
    }


    //我关注的订单
    public function my_orders(){
        $this->need_login();
        $user_id  = $this->user_id();
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
        $this->need_login();
        $user_id = $this->user_id();
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
        $this->need_login();
        //此处除了需要验证登录外还需要确认user_id
        $order_id = $_GET['order_id'];
        $orderM = new OrderModel();
        $user_id = $this->user_id();
        $order_detail = $orderM->get_order_detail($order_id, $user_id);

        //获取护工指派
        $workerOrderM = new WorkerOrderModel();

        $workerOrderInfo = $workerOrderM->get_worker_order($order_id);
        $workerOrderInfo['start_time_str'] = time_stamp_to_str('Y-m-d H:i:s', $workerOrderInfo['start_time']);
        $workerOrderInfo['end_time_str'] = time_stamp_to_str('Y-m-d H:i:s', $workerOrderInfo['end_time']);

        $this->assign('worker', $workerOrderInfo);

        //获取支付列表
        $collectionM = new OrderCollectionModel();
        $paymet_list_temp = $collectionM->get_order_payment($order_id);

        $paymet_list = array();


        foreach($paymet_list_temp as $collection){
            $item = parse_order_collection($collection);
            $paymet_list[] = $item;
        }



        //预处理一下支付列表，使用公共函数，因为其他地方有可能会使用到
        $this->assign('payments', $paymet_list);
        $this->assign('order', $order_detail);
        $this->assign('title', '订单');
        layout('Layout/new_layout');
        $this->display('Order:my_order_detail');
    }

    //获取collection的支付验证
    public function get_pay_sign_info(){
        \Think\Log::record('获取支付验证码','INFO');
        //判断当前支付条目是否已经支付
        //获取当前用户的openid
        //TODO:获取当前用户id
        $user_id  = $this->user_id();
        $openId = $this->user->get_user_openid($user_id);

        $collection_id = $_GET['collection_id'];
        //获取此支付的金额
        $collectionM = new OrderCollectionModel();
        $collection = $collectionM->get_collection($collection_id);
        $collection_fee = $collection['collection_amount'] * 100;
//        dump($collection);
        //生成out_trade_no并更新到数据库中
        $out_trade_no = $collection['order_no'].$collection_id.date('YmdHis');
        $collectionM->update_out_trade_no($collection_id, $out_trade_no);

        //生成attach,用以在order_collection使用
        $attach = $collection_id;

        //生成验证信息并以json的形式返回
        //获取服务预约名称
        $body = $collectionM->get_service_type_str($collection_id);
        $paySignInfo = get_pay_sign_info($body, 1, $out_trade_no, $openId, $attach);
        Log::write($paySignInfo['paySign'], 'INFO');
        $this->ajaxReturn($paySignInfo);

    }

    public function new_service_info(){
        layout('Layout/new_layout');

        $this->display('service_info');
    }



    //添加关注客户
    public function add_follows(){
        echo '添加关注的人';
    }

    //修改个人信息
    public function user_info(){
        layout('Layout/new_layout');
        $this->need_login();
        $user_id = $this->user_id();
        $user_info = $this->user->get_user_info($user_id);

        //获取所有的省份
        $provinces = $this->areas->get_provinces();

        $this->assign('provinces', $provinces);

//        $app_id = C('SERVICE.APPID');
//        $secret = C('SERVICE.SECRET');
//        $js = new Js($app_id, $secret) ;
//        $config = $js->config(array('onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareAppMessage'),true, true);

        $this->assign('user', $user_info);
//        $this->assign('config', $config);

        $this->display('User:user_info');
    }

    public function save_name(){
        $this->need_login();
        $user_id = $this->user_id();
        $name = $_GET['name'];

        $this->user->update_name($user_id, $name);

        $result = array();
        $error_msg = $this->user->getDbError();
        if(empty($error_msg)){
            $result = array(
                'code' => '200'
            );
        }else{
            $result = array(
                'code' => 500,
                'error_msg' => $error_msg
            );
        }

        $this->ajaxReturn($result);
    }

    public function  save_sex(){
        $this->need_login();

        $user_id = $this->user_id();
        $sex = $_GET['sex'];
        if($sex == '男'){
            $sex = 1;
        }else{
            $sex = 2;
        }
        $this->user->update_sex($user_id, $sex);

        $result = array();
        $error_msg = $this->user->getDbError();
        if(empty($error_msg)){
            $result = array(
                'code' => '200'
            );
        }else{
            $result = array(
                'code' => 500,
                'error_msg' => $error_msg
            );
        }

        $this->ajaxReturn($result);
    }


//    更新用户地区信息
    public function update_user_area(){
        $this->need_login();
        $user_id = $this->user_id();
        $province = $_GET['province'];
        $city = $_GET['city'];

        //此处可以优化成一个sql请求
        $province_id = $this->areas->get_area_id($province);
        $city_id = $this->areas->get_area_id($city);

        $this->user->update_user_area($user_id, $province_id, $city_id);
        $result = return_db_operation_result($this->user);

        $this->ajaxReturn($result);
    }

//    将订单改变为进行中
    public function confirm_order(){
        $order_id = $_POST['order_id'];

        $user_id = $this->user_id();
        $orderM = new OrderModel();
        $status_conf = C('ORDER.SATUS_R');
        $orderM->change_order_satus($order_id, $user_id, $status_conf['已确认']);

        $resutl = return_db_operation_result($orderM);

        $this->ajaxReturn($resutl);
    }
//    将订单改变为取消
    public function cancel_order(){
        $order_id = $_POST['order_id'];

        $user_id = $this->user_id();
        $orderM = new OrderModel();
        $status_conf = C('ORDER.SATUS_R');
        $orderM->change_order_satus($order_id, $user_id, $status_conf['已取消']);

        $resutl = return_db_operation_result($orderM);

        $this->ajaxReturn($resutl);
    }


    public function pay_test(){

        $openId = 'o2DIYuBqdKzF316FXZxZZc2tjsM0';
        dump($openId);
        ini_set('date.timezone','Asia/Shanghai');

        $signInfo = get_pay_sign_info('hahaha', 2, 'dddshjssd12313411', $openId);
        dump($signInfo);
        $this->assign('signInfo', $signInfo);

        $this->display('pay_test');
    }

    //支付成功通知路径
    public function pay_notify(){
        //解析返回数据
        $postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postArr = XML::parse($postStr);

        $out_trade_no = $postArr['out_trade_no'];
        $collection_id = $postArr['attach'];
        $total_fee = $postArr['total_fee'] /100;
        $result_code = $postArr['result_code'];

        if($result_code == 'SUCCESS'){
            //更新数据库
            $collectionM = new OrderCollectionModel();
            $collectionM->set_collection_paid($collection_id, $out_trade_no, $total_fee);
        }

        //返回给服务器，表示已经处理
        $return_data = array(
            'return_code' => 'SUCCESS',
            'return_msg' => 'OK',
        );

        $return_data_xml = XML::build($return_data);

        echo $return_data_xml;

    }


    public function babysitter_service(){
        layout('Layout/new_layout');

        $this->display('Service:babysitter_service');
    }


    public function worker_info(){
        layout('Layout/new_layout');

        $worker_id = $_GET['worker_id'];

        //获取员工的信息
        $workerM = new WorkerModel();
        $worker = $workerM->getWrokerInfo($worker_id);
        $worker = $worker[$worker_id];



        //获取工作人员评分
        $commentM = new CommentModel();
        $levels = $commentM->getWorkerCommentLevels($worker_id);
        $level_count = count($levels);
        $attitude_level_sum = 0;
        $profession_level_sum = 0;
        $discipline_level_sum = 0;
        foreach($levels as $key => $value){
            $attitude_level_sum += $value['attitude_level'];
            $profession_level_sum += $value['profession_level'];
            $discipline_level_sum += $value['discipline_level'];

        }
        $attitude_level_avg = round($attitude_level_sum/$level_count, 2);
        $profession_level_avg = round($profession_level_sum/$level_count, 2);
        $discipline_level_avg = round($discipline_level_sum/$level_count, 2);
        $comment_level = round(($attitude_level_avg + $profession_level_avg + $discipline_level_avg)/3, 2);
        $this->assign('comment_level', $comment_level);
        $this->assign('attitude_level', $attitude_level_avg);
        $this->assign('profession_level', $profession_level_avg);
        $this->assign('discipline_level', $discipline_level_avg);
        //格式化员工的信息
        $worker = parse_worker_info($worker);
        //获取员工的前5条评论
        $result = $commentM->getWorkerComments($worker_id, 1);
        $comments = array();
        foreach($result as $k => $v){
            $v['avg_level'] = ($v['attitude_level'] + $v['profession_level'] +$v['discipline_level'])/3;
            $v['comment_time_str'] = time_stamp_to_str('m-d H:i', $v['comment_time']);

            $comments[] = $v;
        };
        //显示加载更多按钮，如果评论数等于5则显示
        if(count($comments) < 5){
            $this->assign('display', 'none');
        }


        $this->assign('comments', $comments);
        $this->assign('worker', $worker);
        $this->display('User:worker');
    }

    //获取评论
    public function get_comment_page(){
        $worker_id = $_GET['worker_id'];
        $page = $_GET['page'];
        $page_step = 5;

        $commentM = new CommentModel();
        $comments = $commentM->getWorkerComments($worker_id, $page, $page_step);
        $result = array();
        foreach($comments as $k => $v){
            $v['avg_level'] = ($v['attitude_level'] + $v['profession_level'] +$v['discipline_level'])/3;
            $v['comment_time_str'] = time_stamp_to_str('m-d H:i', $v['comment_time']);

            $result[] = $v;
        }

        $this->ajaxReturn($result);
    }

    public function home_page(){
        layout('Layout/new_layout');

        $this->display('home_page');
    }

    //健康管理、需要验证查看者身份
    public function customer_detail(){
        layout('Layout/new_layout');
        $this->need_login();
        $customer_id = $_GET['customer_id'];

//        $user_id = $this->user_id();
        $user_id = $this->user_id();

        $followM = new FollowModel();
        $exit_connection = $followM->exit_follow_connection($user_id, $customer_id);

        if(!$exit_connection){
            $this->redirect('customer_detail_no_right');
        }

        $customer_id = $_GET['customer_id'];
        $customerM = new Customer();
        $customer_info = parse_customer_info($customerM->get_customer_info($customer_id));

        $this->assign('customer', $customer_info);
        $this->display('User:health-management');
    }

    //没有查看的权限跳转的页面
    public function customer_detail_no_right(){
        layout('Layout/new_layout');
        echo '您没有权限查看该用户信息';
    }

    public function hospital_care(){
        layout('Layout/new_layout');

        $this->display('Service:hospital_care');
    }

    public function home_care(){
        layout('Layout/new_layout');

        $this->display('Service:home_care');
    }

    public function service_evaluation(){
        //需要登录验证
        $this->need_login();
        layout('Layout/new_layout');
        $worker_id = $_GET['worker_id'];
        $order_id = $_GET['order_id'];

        $user_id = $this->user_id();

        //获取员工的信息
        $workerM = new WorkerModel();
        $worker = $workerM->getWrokerInfo($worker_id);
        $worker = $worker[$worker_id];



        //获取工作人员评分
        $commentM = new CommentModel();
        $levels = $commentM->getWorkerCommentLevels($worker_id);
        $level_count = count($levels);
        $attitude_level_sum = 0;
        $profession_level_sum = 0;
        $discipline_level_sum = 0;
        foreach($levels as $key => $value){
            $attitude_level_sum += $value['attitude_level'];
            $profession_level_sum += $value['profession_level'];
            $discipline_level_sum += $value['discipline_level'];

        }
        $attitude_level_avg = round($attitude_level_sum/$level_count, 2);
        $profession_level_avg = round($profession_level_sum/$level_count, 2);
        $discipline_level_avg = round($discipline_level_sum/$level_count, 2);
        $comment_level = round(($attitude_level_avg + $profession_level_avg + $discipline_level_avg)/3, 2);
        $worker = parse_worker_info($worker);


        //判断是否已经评论过了，评论过的则显示评论内容
        $is_commented = $commentM->is_commented($order_id, $worker_id, $user_id);
        $is_commented_str = 'true';
        $comment = array();
        if(!$is_commented){
            $is_commented_str = 'false';
        }else{
            //获取评论内容
            $comment = $commentM->get_comment_content($order_id, $worker_id);
        }


        $this->assign('comment', $comment);
        $this->assign('display_comment_input', $is_commented_str);
        $this->assign('comment_level', $comment_level);
        $this->assign('worker', $worker);
        $this->assign('order_id', $order_id);

        $this->display('Service:service_evaluation');
    }

    //提交员工评论
    public function commit_worker_evaluation(){
        //评论人需要登录
        $this->need_login();
//        $user_id = $this->user_id();
        $user_id = $this->user_id();
        $order_id = $_GET['order_id'];
        $worker_id = $_GET['worker_id'];

        $attitude = $_GET['attitude'];
        $profession = $_GET['profession'];
        $discipline = $_GET['discipline'];
        $evaluate_text = $_GET['evaluate_text'];

        $result = array();



        //评论人必须有权限评论，评论人是订单的负责人
        $orderM = new OrderModel();
        $is_user_order_match = $orderM->is_user_order_match($order_id, $user_id);
        if($is_user_order_match){
            //工作人员与订单的指派相对应
            $workerOrderM = new WorkerOrderModel();
            $is_woker_order_match = $workerOrderM->is_order_worker_match($order_id, $worker_id);
            if($is_woker_order_match){
                //验证成功
                //判断是否已经评论过了，如果评论过则退出

                $commentM = new CommentModel();
                $is_commented = $commentM->is_commented($order_id, $worker_id, $user_id);
                if($is_commented){
                    $result['code'] = 100;
                    $result['error_msg'] = '已经评论过了';
                }else{
                    $commentM->addComment($order_id, $evaluate_text, $attitude, $profession, $discipline, $worker_id, $user_id);
                    $error_msg = $commentM->getDbError();
                    if(empty($error_msg)){
                        $result['code'] = 200;
                    }else{
                        $result['code'] = 500;
                        $result['error_msg'] = $error_msg;
                    }

                }

            }

        }else{
            $result['code'] = 300;
        }

        $this->ajaxReturn($result);

    }


    public function new_appointment(){
        $service_type = $_GET['service_type'];

        $service_typeM = C('SERVICE_TYPE');

        $service_type_str = $service_typeM[$service_type];

        //获取所有的地址
        $this->need_login();
        $user_id = $this->user_id();
        $addresses = $this->address->get_user_address_string($user_id);

        $this->assign('addresses', $addresses);

        $title = '预约服务';
        $this->assign('service_type_str', $service_type_str);
        $this->assign('service_type', $service_type);
        $this->assign('title', $title);
        layout('Layout/new_layout');
        $this->display('Service:appointment');
    }

    public function add_appointment(){
        //需要验证
//        $this->need_login();

        $user_id = $this->user_id();
        //获取参数
        $address_id = $_GET['address_id'];
        $phone = $_GET['phone'];
        $name = $_GET['name'];
        $service_type = $_GET['service_type'];

        $id = $this->service_appointment->add_appointment($user_id, $address_id, $name, $phone, $service_type);

        $result = array();
        if(!empty($id)){
            $result['code'] = 200;
            $result['id'] = $id;
        }else{
            $result['code'] = 500;
            $result['error_msg'] = $this->service_appointment->getDbError();
        }

        $this->ajaxReturn($result);
    }

    public  function appointment_detail(){
        $appointment_id = $_GET['appointment_id'];

        //需要验证用户id
        $user_id = $this->user_id();

        $appointment = $this->service_appointment->get_appointment_detail($appointment_id);

        if($user_id == $appointment['user_id']){
            $appointment = parse_appointment($appointment);
            $this->assign('appointment', $appointment);
        }else{
            //用户不相干，没有权限查看，不返回数据
        }
        $title = '预约详情';
        $this->assign('title', $title);
        layout('Layout/new_layout');

        $this->display('Service:appointment_detail');
    }
}