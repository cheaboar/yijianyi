<?php
namespace Partner\Controller;
use Partner\Model\UserModel;
use Partner\Model\ApplicationModel;
use Partner\Model\CommissionModel;
use Symfony\Component\Console\Application;
use Think\Controller;
use Partner\Model\LoginModel;


class UsercenterController extends Controller{
    protected  $User;
    protected $Commission;
    protected $Application;

    public function __construct(){
        parent::__construct();
        $this->User = new UserModel();
        $this->Commission = new CommissionModel();
        $this->Application = new ApplicationModel();
        define('COMMISSION_TYPE_AT_ONCE', 0);
        define('COMMISSION_TYPE_ON_FIRST_MONTH', 1);
        define('COMMISSION_TYPE_ON_FIRST_MONTH_FOR_GRAND', 2);
        define('USER_AUTHORITY_LEVEL_OUT_OF_WORK', -1);
    }

    //验证管理员身份信息
    public function is_manager($user_id){
        $level = $this->User->field('authority_level')->where('id='.$user_id)->find();
        if($level['authority_level'] == 1){
            return true;
        }elseif($level['authority_level'] == 0){
            return false;
        }
    }

    //获取所有未支付的费用
    public function get_all_commission_unpayed($user_id){
//        $Commission = new CommissionModel();
        $all_commission_unpayed_temp = $this->Commission->alias('c')->join('user u on c.owner_id = u.id')
            ->field('user_name, SUM(fee) as sum, COUNT(fee) as count, u.id as user_id, telephone')->where('c.status = 0')->group('user_name')->select();



        $all_commission_unpayed = array();
        foreach($all_commission_unpayed_temp as $commission){
            //添加详细列表
            $lists = $this->Commission->field('type as commission_type, fee, date_to_pay, id as commission_id, application_id')->where('status=0 AND owner_id='.$commission['user_id'])->select();


            $commission['lists'] = $this->filter_commissions($lists);
            array_push($all_commission_unpayed, $commission);
        }

        return $all_commission_unpayed;
    }


    //筛选掉还没到上岗一个月就辞职的的佣金。
    public function filter_commissions($before_filter_commissions_unpayed){
        $commissions_unpayed = [];
        //筛选工作不到一个月就离职的
        foreach($before_filter_commissions_unpayed as $commission) {
            if ($commission['commission_type'] == COMMISSION_TYPE_AT_ONCE) {
                $commissions_unpayed[] = $commission;
            } elseif ($commission['commission_type'] == COMMISSION_TYPE_ON_FIRST_MONTH or $commission['commission_type'] == COMMISSION_TYPE_ON_FIRST_MONTH_FOR_GRAND) {
                $result = $this->User->alias('u')->join('application a on u.id = a.owner_id')->where('a.id=' . $commission['application_id'])->find();

                $time_begin = strtotime($result['last_date_start_work']);
                $time_end = time();
                if ($result['date_out_of_work'] != null) {
                    $time_end = $result['date_out_of_work'];
                }

                $date_count_duration = ($time_end - $time_begin) / 3600 / 24;
                if ($date_count_duration >= 30) {
                    array_push($commissions_unpayed, $commission);
                } else {
                    if ($result['authority_level'] != USER_AUTHORITY_LEVEL_OUT_OF_WORK) {
                        $commission['need_to_pay'] = '1';
                        $commissions_unpayed[] = $commission;
                    }
                }


            }
        }
        return $commissions_unpayed;
    }

    public function get_commission($user_id){
        //获取佣金清单,分为未支付的清单和已支付的清单
        $Commission = new CommissionModel();
        $before_filter_commissions_unpayed = $Commission->alias('a')->join('application b on a.application_id = b.id')
            ->join('user c on b.owner_id = c.id')->where('a.owner_id='.$user_id.' AND a.status=0')->order('date_to_pay desc')
            ->field('a.application_id as application_id, c.user_name as user_name, a.fee, date_to_pay, a.status as status, a.type as commission_type')->select();
//        $commissions_unpayed = $before_filter_commissions_unpayed;

        $commissions_unpayed = $this->filter_commissions($before_filter_commissions_unpayed);

        $commissions_payed = $Commission->alias('a')->join('application b on a.application_id = b.id')
            ->join('user c on b.owner_id = c.id')->where('a.owner_id='.$user_id.' AND a.status=1')->order('date_to_pay desc')
            ->field('c.user_name as user_name, a.fee, date_to_pay, a.status as status , a.type as commission_type')->select();
        return array('payed'=>$commissions_payed, 'unpayed'=>$commissions_unpayed);
    }

    function user_center(){
        $user_id = check_user_msg();

        $User = new UserModel();

        $user = $User->get_user_info($user_id);
        $this->assign('user', $user);

        if($this->is_manager($user_id)){
            $this->assign('see_public_job', 'inherit');
            $this->assign('see_all_commission', 'inherit');
            $this->assign('see_all_application', 'inherit');
        }else{
            $this->assign('see_public_job', 'none');
            $this->assign('see_all_commission', 'none');
            $this->assign('see_all_application', 'none');
        }

        $this->display('partner:home_page');
    }



    //导出csv文件
    function export_csv(){
        /**
         * 生成默认以逗号分隔的CSV文件
         * 解决：内容中包含逗号(,)、双引号("")
         */
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment;filename=CSV数据.csv ");

        //获取选中的内容
        $selected_commission_ids = array_values($_GET);
        $result = array();
        $result[] = array("收款人, 手机, 银行名称,银行账号,应聘者,费用, 应付日期, 佣金编号, 是否已付（已付为1， 未付为0）");
        if($this->is_manager(check_user_msg())){
            foreach($selected_commission_ids as $id){
                $result[] = $this->Commission->alias('c')->join('user u1 on c.owner_id=u1.id')->where('c.owner_id=user.id')->join('application a on a.id=c.application_id')
                    ->join('user u2 on u2.id=a.owner_id')
                    ->field('u1.user_name as owner_name ,u1.telephone,u1.bank, u1.bank_card_id,u2.user_name ,c.fee, c.date_to_pay, c.id as commission_id, c.status as status')
                    ->where('c.id='.$id)->find();
            }
        }


        $str = '';
        foreach ($result as $row) {
            $str_arr = array();
            foreach ($row as $column) {
                $str_arr[] = str_replace('"', '""', $column) ;
            }
            $str.=implode(',', $str_arr) . PHP_EOL;
        }
        echo $str;
    }

    //设置为已费用已支付
    function set_commission_payed(){
        session_start();
        $user_id = check_user_msg();
        $selected_commission_ids = $_POST['selected_commission_id'];


        if(!is_null($user_id)){
            //验证管理员身份信息
            if($this->is_manager($user_id)){
                //设置为已支付
                $data['status'] = 1;
                foreach($selected_commission_ids as $commission_id){
                    $this->Commission->where('id='.$commission_id)->save($data);
                }

                $this->ajaxReturn(['result'=>'True', 'code'=>'1'], 'JSON');
            }else{
                $this->ajaxReturn(['result'=>"User is not manager", 'code'=>'0'], 'JSON');
            }

        }else{
            $this->ajaxReturn(['result'=>"User ID is not exit", 'code'=>'-1'], 'JSON');
        }
    }

    function application_management(){
        $this->display('partner:application_management');
    }

    public function get_application($type){
         $result = $this->Application->alias('a')->join('user u on a.owner_id=u.id')->join('position p on p.id=a.position_id')
             ->join('resume r on r.id = a.resume_id')->field('user_name,  r.about as self_describe,
          a.position_id , job ,date_of_process, telephone, date_of_work, a.status as status, a.id as application_id')->where('a.status='.$type)->order('a.id desc')->select();

//        dump($this->Application->getDbError());
//        dump($result);
         return $result;
    }

    function load_application(){
        $type = $_GET['type'];
        $result = array();
        if($type == 'unprocessed'){
            //获取未处理的应聘申请
            $result = $this->get_application(0);

        }elseif($type == 'waiting'){
            //获取等待面试的应聘
            $result = $this->get_application(1);

        }elseif($type == 'done'){
            //获取已经处理好的应聘申请
            $result = [];
            for($i = 5; $i >= 2; $i--){
                $temp = $this->get_application($i);
                if($temp != null){

                    $result = array_merge($temp,$result);
                }

            }
            $result_six =  $this->get_application(6);
            if($result_six != null){
                $result = array_merge($result, $result_six);
            }



        }

        $this->ajaxReturn($result, 'JSON');
    }

//    生成佣金信息
     public function produce_commission($application_id){
         if($this->is_manager(check_user_msg())){
             //判断是否有上线
             $result = $this->Application->alias('a')->join('user u on a.owner_id = u.id')->field('parent_id, owner_id')->where('a.id='.$application_id)
                 ->find();

             $parent_id = $result['parent_id'];
             $worker_id = $result['owner_id'];
             if($parent_id != 1 && $parent_id != null){
                 //判断是否一年内入过职，入过则不进行下一步
                 $info = $this->User->field('last_date_start_work, type')->where('id='.$worker_id)->find();
                 $user_type = $info['type'];
                 $last_date_start_work = $info['last_date_start_work'];

                 $days_count_to_now = 366;
                 if($last_date_start_work != null){
                     $today = strtotime(date('Y-m-d'));
                     $work_date = strtotime($last_date_start_work);
                     $days_count_to_now = ($today - $work_date)/3600/24;
                 }

                 if($days_count_to_now >= 365){
                     //这里的推荐人不能与应聘是同一个人
                     //生成三种类型的佣金，首付佣金，满一个月佣金，还有给上线的佣金，三种类型的用佣金通过type区别
                     if($user_type == '1'){
                         //此用户是推荐类型的。
                         $commission_first = [];
                         $commission_monthly = [];
                         $commission_grand = [];
                         if($worker_id != $parent_id){
                             $commission_first['owner_id'] = $parent_id;
                             $commission_first['application_id'] = $application_id;
                             $commission_first['fee'] = 50;
                             $commission_first['date_to_pay'] = date('Y-m-d');
                             $commission_first['status'] = 0;
                             $commission_first['type'] = 0;

                             $commission_monthly['owner_id'] = $parent_id;
                             $commission_monthly['application_id'] = $application_id;
                             $commission_monthly['fee'] = 50;
                             $commission_monthly['date_to_pay'] =  date("Y-m-d",strtotime("+1months"));
                             $commission_monthly['status'] = 0;
                             $commission_monthly['type'] = 1;


                             $grand_id = $this->User->where('id='.$parent_id)->field('parent_id')->find()['parent_id'];
                             if($grand_id != 1 and $grand_id != null and $parent_id != $grand_id){
                                 $commission_grand['owner_id'] = $grand_id;
                                 $commission_grand['application_id'] = $application_id;
                                 $commission_grand['fee'] = 20;
                                 $commission_grand['date_to_pay'] = date("Y-m-d",strtotime("+1months"));
                                 $commission_grand['status'] = 0;
                                 $commission_grand['type'] = 2;
                             }
                         }

                         $data = $this->Commission->data($commission_first)->add();
                         $data = $this->Commission->data($commission_monthly)->add();
                         $data = $this->Commission->data($commission_grand)->add();

                         //更新员工上岗日期
                         $this->User->where('id='.$worker_id)->save(['last_date_start_work' => date('Y-m-d')]);
                     }elseif($user_type == '0'){
                         //用户是通过连接进来的
                         $commission = [];
                         if($worker_id != $parent_id){
                             $commission['owner_id'] = $parent_id;
                             $commission['application_id'] = $application_id;
                             $commission['fee'] = 20;
                             $commission['date_to_pay'] = date('Y-m-d');
                             $commission['status'] = 0;
                             $commission['type'] = 3;

                             $data = $this->Commission->data($commission)->add();
                             dump($this->Commission->getDbError());

                             //更新员工上岗日期
                             $this->User->where('id='.$worker_id)->save(['last_date_start_work' => date('Y-m-d')]);
                         }
                     }

                 }else{

                     echo $days_count_to_now;
                 }


             }


         }else{
             echo 'no manager';
         }


    }

    function sign_application_status(){
        $result = array();
        if($this->is_manager(check_user_msg())){
            $application_id = $_GET['application_id'];
            $status = $_GET['status'];
            $data = array();

            if($status == 'waiting'){
                $data['status'] = 1;
                $data['date_of_process'] = date('Y-m-d');
            }elseif($status == 'no_interview'){
                $data['status'] = 3;
            }elseif($status == 'not-fit'){
                $data['status'] = 4;
            }elseif($status == 'waiting-for-work'){
                $data['status'] = 2;
                $data['date_of_work'] = $_GET['date'];
            }elseif($status == 'on-work'){
                $data['status'] = 5;
                $data['date_of_work'] = $_GET['date'];

                $this->Application->where('id='.$application_id)->save($data);
                $user_id = $this->Application->where('id='.$application_id)->field('owner_id')->find();

                $this->User->where('id='.$user_id['owner_id'])->save(['certification_id'=> $_GET['ID']]);

                //生成佣金信息
                $this->produce_commission($application_id);

            }elseif($status == 'no-working'){
                $data['status'] = 6;
            }

            $this->Application->where('id='.$application_id)->save($data);
            if($this->Application->getDbError() == null){
                $result['status'] = 'True';
                $result['code'] = '1';
                $this->ajaxReturn($result, 'JSON');
            }else{
                $result['status'] = 'False';
                $result['code'] = '0';
                $result['msg'] = $this->Application->getDbError() . $this->User->getDbError();
                $this->ajaxReturn($result, 'JSON');
            }
        }else{
            $result['msg'] = 'Not Manager, the System is terrified!';
            $result['status'] = 'False';
            $result['error_code'] = '-1';
            $this->ajaxReturn($result, 'JSON');
        }

    }


    function job_list(){
        $this->display('partner:job_list');
    }

    function user_info(){
        $user_id = check_user_msg();
        $user_info = $this->User->where('id='.$user_id)->find();
        $this->assign('user_info', $user_info);
        $this->display('partner:user_info');
    }

    function update_user_info(){
        $email = $_POST['email'];
        $tel = $_POST['tel'];
        $bank = $_POST['bank'];
        $bank_card_id = $_POST['bank_card_id'];
        $name = $_POST['name'];

        $ID = $_POST['ID'];

        $data['email'] = $email;
        $data['telephone'] = $tel;
        $data['user_name'] = $name;
        $data['certification_id'] = $ID;
        $data['bank'] = $bank;
        $data['bank_card_id'] = $bank_card_id;

        $user_id = check_user_msg();
        $this->User->where('id='.$user_id)->save($data);

        $result = [];
        if($this->User->getDbError() == null){
            $result['status'] = '1';
        }else{
            $result['status'] = '0';
        }

        $this->ajaxReturn($result, 'json');

    }

    function my_application(){
        $user_id = check_user_msg();
        $applications = $this->Application->alias('a')->join('position p on a.position_id=p.id')->field('a.id as application_id, status, job, salary, date_of_create')
            ->where('owner_id='.$user_id)->order('a.id desc')->select();

        $this->assign('applications', $applications);
        $this->display('partner:my_application');

    }

    function application_detail(){
        $application_id = $_GET['id'];
        $user_id = check_user_msg();

        $result = $this->Application->alias('a')->join('user  u on u.id = a.owner_id')->join('position p on a.position_id = p.id')
            ->join('resume r on a.resume_id = r.id')->field('job, describe, experience, need, salary, date_of_create, a.status as status,
            user_name, about, age, r.email as email, phone')->where('a.id='.$application_id . ' and u.id='.$user_id)->find();

        $this->assign('result', $result);

        $this->display('partner:application_detail');

    }

    function my_under_line(){
        $user_id = check_user_msg();

        $my_recommend = $this->User->page(1, 10)->where('parent_id='.$user_id . ' and type=1')->field('user_name, telephone, recommend_date')->select();


        $my_fans = $this->User->page(1, 10)->where('parent_id='.$user_id . ' and type=0')->field('user_name, telephone')->select();
        $is_fans_end = 0;
        $is_recommend_end = 0;
        if($my_fans == null or count($my_fans) < 10){
            $is_fans_end = 1;
        }
        if($my_recommend == null or count($my_recommend) < 10){
            $is_recommend_end = 1;
        }

        $this->assign('recommend', $my_recommend);
        $this->assign('recommend_end', $is_recommend_end);
        $this->assign('fans', $my_fans);
        $this->assign('fans_end', $is_fans_end);
        $this->display('partner:my_under_line');
    }

    function read_more_fans(){
        $user_id = check_user_msg();
        $page = $_GET['page'];
        $my_fans = $this->User->page($page, 10)->where('parent_id='.$user_id . ' and type=0')->field('user_name, telephone')->select();

        $is_fans_end = 0;
        if($my_fans == null or count($my_fans) < 10){
            $is_fans_end = 1;
        }
        $result['fans'] = $my_fans;
        $result['is_end'] = $is_fans_end;
        $this->ajaxReturn($result, 'json');
    }

    function read_more_recommend(){
        $user_id = check_user_msg();
        $page = $_GET['page'];
        $my_recommend = $this->User->page($page, 10)->where('parent_id='.$user_id . ' and type=1')->field(' user_name, telephone, recommend_date')->select();

        $is_recommend_end = 0;
        if($my_recommend == null or count($my_recommend) < 10){
            $is_recommend_end = 1;
        }
        $result['recommends'] = $my_recommend;
        $result['is_end'] = $is_recommend_end;
        $this->ajaxReturn($result, 'json');
    }

    function my_commission(){
        $user_id = check_user_msg();
//        $user_id = 85;
        $firs_pay_fees = $this->Commission->where('owner_id='. $user_id . ' and type=0 and status=0')->select();
        $second_pay_fees = $this->Commission->where('owner_id='. $user_id . ' and type=1 and status=0')->select();

        $basic_fee_info = [];
        $basic_fee_sum = 0;
        //获取推荐的资料，并且看是否已经入职一个月了
        foreach($firs_pay_fees as $fee_info){
            $result = $this->Application->alias('a')->join('user u on a.owner_id = u.id')
                ->where('a.id='.$fee_info['application_id'])->find();
            $info['user_name'] = $result['user_name'];
            $info['date_beigin_to_work'] = $result['date_of_work'];//入职时间
            $info['date_begin_on_work'] = $result['last_date_start_work']?$result['last_date_start_work']:'-';//上岗时间
            $info['fee1'] = $fee_info['fee'];
            $basic_fee_sum += $fee_info['fee'];
            $info['fee2'] = '-';

            $start_work_time = strtotime($result['last_date_start_work']);
            $date_out_of_work_time = strtotime($fee_info['date_out_of_work']);
            $now = strtotime(date('Y-m-d'));
            $date_count = ($now - $start_work_time)/3600/24;
            $date_during_work = 0;

            if($date_out_of_work_time != null){
                $date_during_work = ($date_out_of_work_time - $start_work_time)/3600/24;
            }
            if(($result['authority_level'] != -1 and $date_count >= 30) or ($result['authority_level'] == -1 and $date_during_work >= 30)){
                //在职，而且满30天的工作期
                $info['fee2'] = $fee_info['fee'];
                $basic_fee_sum += $fee_info['fee'];
                foreach($second_pay_fees as $fee2_info){
                    if($fee2_info['application_id'] == $fee_info['application_id']){
                        $info['fee2'] = $fee2_info['fee'];
                        $basic_fee_sum -= $fee_info['fee'];
                        $basic_fee_sum += $fee2_info['fee'];
                    }
                }


            }
            $basic_fee_info[] = $info;

        }

        //获取额外佣金
        $extral_fee_info = [];
        $extral_fee_sum = 0;

        $extral_fee = $this->Commission->where('owner_id='. $user_id . ' and (type=2 or type=3)and status=0')->select();

        foreach($extral_fee as $fee_info){
            $info = [];
            $result = $this->Application->alias('a')->join('user u on a.owner_id = u.id')
                ->where('a.id='.$fee_info['application_id'])->find();
            $info['user_name'] = $result['user_name'];
            $info['date_beigin_to_work'] = $result['date_of_work'];//入职时间
            $info['date_begin_on_work'] = $result['last_date_start_work']?$result['last_date_start_work']:'-';//上岗时间
            $info['fee'] = '-';
            $if_enough_month = false;
            $start_work_time = strtotime($result['last_date_start_work']);
            $date_out_of_work_time = strtotime($fee_info['date_out_of_work']);
            $now = strtotime(date('Y-m-d'));
            $date_count = ($now - $start_work_time)/3600/24;
            $date_during_work = 0;

            if($date_out_of_work_time != null){
                $date_during_work = ($date_out_of_work_time - $start_work_time)/3600/24;
            }
            if(($result['authority_level'] != -1 and $date_count >= 30) or ($result['authority_level'] == -1 and $date_during_work >= 30)){
                //在职，而且满30天的工作期
                $info['fee'] = $fee_info['fee'];
                $extral_fee_sum += $fee_info['fee'];
            }
            $extral_fee_info[] = $info;
        }

        //获取所有已经支付的佣金
        $payed_fee_info = $this->Commission->alias('c')->join('application a on a.id = c.application_id')->
                join('user u on u.id = a.owner_id')->where('c.owner_id='.$user_id . ' and c.status = 1')
            ->field('a.date_of_work as date_beigin_to_work, u.last_date_start_work as date_begin_on_work, c.fee as fee, u.user_name as user_name')->select();
        $payed_fee_sum = 0;
        foreach($payed_fee_info as $c){
            $payed_fee_sum += $c['fee'];
        }

        $this->assign('basic_fee_info', $basic_fee_info);
        $this->assign('extral_fee_info', $extral_fee_info);
        $this->assign('payed_fee_info', $payed_fee_info);
        $this->assign('basic_fee_sum', $basic_fee_sum);
        $this->assign('extral_fee_sum', $extral_fee_sum);
        $this->assign('payed_fee_sum', $payed_fee_sum);
        $this->display('partner:my_commission');
    }


    function commission_management(){
        $user_id = 139;
        $unpayed_fees = [];

        if($this->is_manager($user_id)){
            $result = $this->Commission->alias('c')->join('user u1 on u1.id=c.owner_id')->join('application a on a.id = c.application_id')
                ->join('user u2 on u2.id= a.owner_id')->where('c.status = 0')->field('c.id as commission_id, u1.user_name as owner_name, u2.user_name as user_name , u1.id as owner_id, u2.id as user_id,
                c.fee as fee, a.date_of_work as entry_work_date, u2.last_date_start_work as on_work_date, u2.date_out_of_work, u2.authority_level, c.type as type')->select();
            foreach($result as $fee_info){
                $start_work_time = strtotime($fee_info['on_work_date']);
                $date_out_of_work_time = strtotime($fee_info['date_out_of_work']);
                $now = strtotime(date('Y-m-d'));
                $date_count = ($now - $start_work_time)/3600/24;
                $date_during_work = 0;
                if($date_out_of_work_time != null){
                    $date_during_work = ($date_out_of_work_time - $start_work_time)/3600/24;
                }

                if((($fee_info['type'] == 1 or $fee_info['type'] == 2 or  $fee_info['type'] == 3) and
                    (( $fee_info['authority_level'] != -1 and $date_count < 30) or ($fee_info['authority_level'] == -1 and $date_during_work < 30)))){

                }else{
                    $unpayed_fees[] = $fee_info;
                }
            }

        }
        $this->assign('unpayed_fees', $unpayed_fees);
        $this->display('partner:commission_management');
    }

    public function user_management(){
        //判断是否为超级管理员
        check_manager();
        //获取所有用户，最新的30名
        $users = $this->User->get_users(1);
        $this->assign('users', $users);
        $display_more = 'none';
        if($this->User->count() > 30){
            $display_more = 'inline-block';
        }
        $this->assign('display_more', $display_more);

        $this->display('partner:user_management');
    }

    public function manager_user_management(){
        //判断是否为超级管理员
        check_manager();
        //获取所有管理员用户
        $users = $this->User->get_managers();
        $this->assign('users', $users);
        $this->display('partner:manager_user_management');
    }

    //设置为普通用户
    public function set_as_normal_user(){
        check_manager();
        $user_id = $_GET['user_id'];
        $this->User->set_as_normal_user($user_id);
        $error_msg = $this->User->getDbError();
        $return = array();
        if(empty($error_msg)){
            $return['code'] = 200;
        }else{
            $return['code'] = 500;
            $return['msg'] = $error_msg;
        }

        $this->ajaxReturn($return);
    }

    public function get_more_users(){
        //判断是否为超级管理员
        check_manager();
        $page = $_GET['page'];
        $users = $this->User->get_users($page);
        $result_users = array();

        //删减一下敏感信息
        foreach($users as $user){
            $result_users[] = array(
                'id'            => $user['id'],
                'header_url'    => $user['header_url'],
                'user_name'     => $user['user_name'],
                'telephone'     => $user['telephone'],
            );
        }

        $user_count = $this->User->count();
        $display_more = 'none';
        if($user_count > 30 * $page){
            $display_more = 'inline-block';
        }

        $result = array(
            'display_more' => $display_more,
            'users' => $result_users
        );

        $this->ajaxReturn($result);
    }

    public function set_as_manager(){
        $user_id = $_GET['user_id'];
        $passwd = $_GET['passwd'];

        //检查是否为超级管理员
        check_manager();
        $this->User->set_as_manager($user_id, $passwd);
        $error_msg = $this->User->getDbError();
        $return = array();
        if(empty($error_msg)){
            $return['code'] = 200;
        }else{
            $return['code'] = 500;
            $return['msg'] = $error_msg;
        }

        $this->ajaxReturn($return);
    }

    public function search_user(){
        //判断是否为管理员
        check_manager();

        $search_text = $_GET['search_text'];
        $users = $this->User->search_user($search_text);
        $result_users = array();

        //删减一下敏感信息
        foreach($users as $user){
            $result_users[] = array(
                'id'            => $user['id'],
                'header_url'    => $user['header_url'],
                'user_name'     => $user['user_name'],
                'telephone'     => $user['telephone'],
            );
        }

        $this->ajaxReturn($result_users);
    }

}//class end