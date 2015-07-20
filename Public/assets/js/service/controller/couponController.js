/**
 * Created by Chan on 15/7/3.
 */

var couponCtrl  = appointmentApp.controller('couponCtrl', ['$scope', '$resource', function($scope, $resource){

    var result = $resource('get_my_coupon').query({},function(){
       console.log(result);
        $scope.coupons = result;
        $('.loading-container').hide();
        if(result.length == 0){
            $('.no-coupon').show();
        }else{

        }
    });

}]);