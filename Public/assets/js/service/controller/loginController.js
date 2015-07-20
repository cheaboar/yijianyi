/**
 * Created by wind on 2015/6/11.
 */

//loginModule = angular.module('loginApp', ['ngResource']);

appointmentApp.controller('loginCtrl', ['$scope', '$resource', function($scope, $resource){
    $scope.loginR =  $resource('login');
    $scope.loginObj = new $scope.loginR();
    $scope.userName = '';
    $scope.passwd = '';
    $scope.show_error_msg = false;
    console.log($scope.loginObj);
    $scope.login = function(){
        $scope.loginObj.name = $scope.userName;
        $scope.loginObj.passwd = $scope.passwd;

        $scope.loginObj.$save(function(data){
            console.log(data);
            if(data['status'] == 200){
                document.location = 'service_info';
                console.log('success')
            }else if(data['status'] == 300){
                $scope.show_error_msg = true;
                $scope.login_error_msg = '用户名与密码不匹配'
            }
        })
    }
}])
