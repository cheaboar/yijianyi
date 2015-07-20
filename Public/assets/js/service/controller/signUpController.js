/**
 * Created by wind on 2015/6/11.
 */

appointmentApp.controller('signUpCtrl', ['$scope', '$resource', function($scope, $resource){
    $scope.signUpUserName ='';
    $scope.signUpPasswd1 = '';
    $scope.signUpPasswd2 = '';
    $scope.show_return_msg = false;
    var signUpResource = $resource('sign_up');
    $scope.signUpObj = new signUpResource();
    $scope.signUp = function(){
        if($scope.signUpPasswd1 == $scope.signUpPasswd2 && $scope.signUpPasswd1.length >= 6){
            $scope.signUpObj.userName = $scope.signUpUserName;
            $scope.signUpObj.passwd = $scope.signUpPasswd1;

            $scope.signUpObj.$save(function(data){
                console.log(data);
                if(data['status'] == 200){
                    $scope.show_return_msg = true;
                    $scope.return_msg = '注册成功';
                    document.location = 'service_info'

                }else if(data['status'] == 300){
                    $scope.show_return_msg = true;
                    $scope.return_msg = '用户名已经存在，请使用其他用户名注册';
                }else if(data['status'] == 500){
                    $scope.show_return_msg = true;
                    $scope.return_msg = '服务器出错';
                }
            });
        }

    }

}])