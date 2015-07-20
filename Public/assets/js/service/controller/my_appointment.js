/**
 * Created by Administrator on 2015/6/12.
 */

my_appointmentCtr = appointmentApp.controller('myAppointmentCtr', ['$scope', '$resource', function($scope
        , $resource){

    $scope.my_appointments = $resource('get_my_appointments').query({},function(){
        //console.log($scope.my_appointments);
        $('.loading-container').hide();
        if($scope.my_appointments.length == 0){
            $('#no-data').show();
        }

    });




    //删除选中项
    $scope.delete = function(index){
        $scope.delete_index = index;
        $('.ui.modal').modal('toggle');
    }

    //定义模态选择响应函数
    $('.ui.modal').modal({
        onDeny:function(){
            console.log('deny');
        },
        onApprove:function(){
            var result = $resource('delete_appointment').get({'id':$scope.my_appointments[$scope.delete_index].id}, function(){
                if(result.code == 200){
                    $('#item-' + $scope.my_appointments[$scope.delete_index].id).hide(500);
                }
            });

        }
    });
}])
