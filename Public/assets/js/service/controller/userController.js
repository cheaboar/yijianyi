/**
 * Created by Administrator on 2015/6/15.
 */



appointmentApp.controller('userCtrl',['$scope', '$resource', function($scope, $resource){
    $scope.edit_label = '编辑';
    $scope.edit_icon = 'edit teal icon';
    $scope.edit_status = false;
    $scope.bind_btn = '绑定';
    $scope.sex_temp = 1;

    $('#select-sex').dropdown({
        onChange: function () {
            $scope.sex_temp = $(this).dropdown('get value');
        }
    })


    //记时器
    var util = {
        wait: 90,
        hsTime: function (that) {

            //_this = $(this);
            _this = this;

            if (_this.wait == 1) {
                $(that).removeAttr("disabled").text('重发短信验证码');
                _this.wait = 90;
            } else {
                var _this = this;
                $scope.bind_btn = _this.wait + 's';
                $(that).attr("disabled", true).text('在' + _this.wait + '秒后点此重发');
                _this.wait--;
                setTimeout(function () {
                    _this.hsTime(that);
                }, 1000)
            }
        }
    }

    var phone_test = function(phone){
        var regExp = /^1\d{10}/
        return regExp.test(phone);
    }

    $scope.is_send_msg = false;

    $scope.send_msg = function(){
        if(!$scope.is_send_msg){
            if(!phone_test($scope.new_phone)){
                $('#new-phone').popup({
                    position:'bottom right',
                    content:'请输入正确手机号'
                }).popup('show');
                return;
            }
            $scope.bind_btn = '这在发送验证短信';
            var result = $resource('send_msg').get({'new_phone':$scope.new_phone},function(){
                $('#validate').show();
                if(result.code == 200){
                    util.hsTime('#bind-btn-text');
                }else{
                    console.log(result);
                }
            });
        }else{

        }

    }


    $scope.send_validate_code = function(){
        var reseult = $resource('validate_phone_code').get({'code':$scope.send_code}, function(){
            if(reseult.code == 200){
                $('#binded-phone').show();
                $('#bind-input').hide();
                console.log('good');
            }
        })
    }

    $scope.edit = function(){
        if(!$scope.edit_status){
            $scope.nick_name_temp = $scope.nick_name;
            $scope.edit_label = '完成';
            $scope.edit_icon = 'save teal icon';
            $('#edit-erea').show();
            $('#cancel_edit').show();
            $('#edit-area-show').hide();
            $scope.edit_status = true;
        }else{
            //保存修改

            var user_info = $resource('save_user_info');
            console.log($scope.sex_temp);
            var result = user_info.save({'sex':$scope.sex_temp, 'nick_name':$scope.nick_name_temp, 'user_id':$scope.user_id},function(){
                if(result.code == 200){
                    $scope.nick_name = $scope.nick_name_temp;
                    if($scope.sex_temp == 1){
                        $scope.sex = '男';
                    }else if($scope.sex_temp == 2){
                        $scope.sex = '女';
                    }else{
                        $scope.sex = '男';
                    }
                }
            })

            $scope.edit_label = '编辑';
            $('#edit-erea').hide();
            $('#cancel_edit').hide();
            $scope.edit_icon = 'edit teal icon';
            $('#edit-area-show').show();
            $scope.edit_status = false;
        }
    }

    $scope.cancel_edit = function(){
        $('#cancel_edit').hide();
        $scope.edit_icon = 'edit teal icon';
        $('#edit-erea').hide();
        $('#edit-area-show').show();
        $scope.edit_status = false;

    }
}])
