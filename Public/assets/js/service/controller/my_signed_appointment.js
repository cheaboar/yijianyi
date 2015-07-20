/**
 * Created by cheaboar on 2015/7/2.
 */

my_appointmentCtr = appointmentApp.controller('mySignedAppointmentCtr', ['$scope', '$resource', function($scope, $resource){

    $scope.my_signed_appointments = $resource('get_my_signed_appointments').query({}, function () {
        if ($scope.my_signed_appointments.length == 0) {
            $('#no-data').show();
        }
    });
}]);