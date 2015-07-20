/**
 * Created by Chan on 15/7/3.
 */


adviceCtrl = appointmentApp.controller('adviceCtrl', ['$scope', '$resource', function($scope, $resource){

    $scope.send_advice = function(){
        console.log($scope.adviceStr);

        var data = {
            'advice_str': $scope.adviceStr
        }

        var result = $resource('add_advice').save(data, function(){
            console.log(result);
            if(result.code == 200){
                $('.advice_success').show();
                $('.adivce.ui.form').hide();
            }

        })
    }
}])