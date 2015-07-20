/**
 * Created by Chan on 15/7/3.
 */


myFollowCtrl = appointmentApp.controller('myFollowCtrl',['$scope', '$resource', function($scope, $resource){

    var result = $resource('get_my_follows').query(function(){
        console.log(result);
        $scope.follows = result;
        $('.loading-container').hide();
        $('.no-follow').show();
    });

}] );