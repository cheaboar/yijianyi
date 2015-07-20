/**
 * Created by Administrator on 2015/7/1.
 */

userAddressCtrl = appointmentApp.controller('myAddressCtrl', ['$scope', '$resource', function($scope, $resource){
    $scope.provinces = null;
    //加载地址
    $scope.loadAddress = function(){
        var addresses = $resource('get_my_address').query(function(){
            $scope.addresses = addresses;
        });

    }

    $scope.loadAddress();

    //添加删除监听，弹出modal
    $scope.deleteClick = function(index){
        $scope.delete_id = $scope.addresses[index].id;
        $scope.delete_index = index;
        $('.ui.modal').modal('toggle');
    }

    //添加modal选择事件
    $('.ui.modal').modal('setting',{
        onApprove:function(){
            console.log($scope.delete_id);
            var result = $resource('delete_my_address').get({'id':$scope.delete_id}, function(){
                $scope.addresses[$scope.delete_index].code = 400;
                    $('#item-' + $scope.addresses[$scope.delete_index].id).hide(500);
            })
            $('#item-' + $scope.addresses[$scope.delete_index].id).hide();
            console.log('approve');
        },
        onDeny:function(){
            console.log($scope.delete_id);
            console.log('deny');
        }
    })

    //提前获取省份
    $scope.provinces = $resource('get_province').query({}, function(){
        $('#add-address-btn').show();
    });




    //添加新地址事件
    $scope.addAddressClick = function(){
        $('.add-address-area').show();
        $scope.province = $scope.provinces[0];
        console.log($scope.province);
        console.log($scope.province.area_id)
        $scope.$watch('province', function(newValue, oldValue){
            var province_id = $scope.province.area_id;
            $scope.cities = $resource('get_cities').query({'province_id':province_id}, function(){
                $scope.city = $scope.cities[0];
            })
        })

        $scope.$watch('city', function(newValue, oldValue){
            console.log($scope.city);
            var city_id = $scope.city.area_id;
            $scope.zones = $resource('get_zones').query({'city_id':city_id}, function(){
                $scope.zone = $scope.zones[0];
            })
        })

    }

    //保存地址
    $scope.save_address = function(){
        $scope.saving = false;
        if($scope.saving == true){

        }else{
            $scope.saving = true;
            var data = {
                'province':$scope.province.area_name,
                'province_id':$scope.province.area_id,
                'city':$scope.city.area_name,
                'city_id':$scope.city.area_id,
                'zone':$scope.zone.area_name,
                'zone_id':$scope.zone.area_id,
                'stree':$scope.stree,
                'contact_name': $scope.contact_name,
                'contact_phone':$scope.contact_phone
            }
            var result = $resource('save_address').get(data, function(){
                if(result.code == 200){
                    $scope.loadAddress();
                    $('.add-address-area').hide();
                    $scope.saving = false;
                }
            })
        }

    }
}])