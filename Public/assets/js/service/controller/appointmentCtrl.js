/**
 * Created by Administrator on 2015/6/24.
 */

appointmentApp.controller('appointmentCtrl', ['$scope', 'addressFactory','commitFactory','$resource',function($scope, addressFactory, commitFactory, $resource){



    $scope.selected_time = '工作时间';
    $scope.time_type = '选择';
    $scope.time_start = null;
    $scope.time_end = null;

    $('.ui.dropdown').dropdown({
        onChange:function(){
            var value = $(this).dropdown('get value');
            if(value == 'option1'){
                $('#time-picker').hide();
                $scope.selected_time = '工作时间';
                $('#selected_time').html($scope.selected_time);
            }else if(value == 'option2'){
                $('#time-picker').show();

                $scope.selected_time = $scope.time_start.getHours() + ":" + $scope.time_start.getMinutes()
                + '-' + $scope.time_end.getHours() + ":" + $scope.time_end.getMinutes();

                $('#selected_time').html($scope.selected_time);
            }
        }
    });

    $scope.$watch('time_start', function(newValue, oldValue){
        $scope.selected_time = $scope.time_start.getHours() + ":" + $scope.time_start.getMinutes()
        + '-' + $scope.time_end.getHours() + ":" + $scope.time_end.getMinutes();
    });
    $scope.$watch('time_end', function(newValue, oldValue){
        $scope.selected_time = $scope.time_start.getHours() + ":" + $scope.time_start.getMinutes()
        + '-' + $scope.time_end.getHours() + ":" + $scope.time_end.getMinutes();
    });



    $scope.myClass = 'active';
    //$scope.user_id = $('#user-id').attr('value');

    $scope.addresses = addressFactory.query();


    $scope.showSelectedAddress = false;
    $scope.selectedAddressId = 0;
    $scope.selectedAddressStr;
    $scope.itemClass = new Array();
    $scope.labelClass = new Array();
    $scope.showAddressNeededMsg = false;
    $scope.serviceUnit = $('#service-unit').attr('value');
    $scope.unitPrice = $('#service-price-per-unit').attr('value');
    $scope.serviceName = $('#service-name').attr('value');

    var initAddressClass = function(){
        for(var i= 0; i < $scope.addresses.length; i++){
            $scope.itemClass[i] = '';
            $scope.labelClass[i] = '';
        }
    };
    initAddressClass();

    $scope.addressSelected = function(index){
        initAddressClass();
        $scope.showAddressNeededMsg = false;
        console.log($scope.addresses[index]);
        $scope.selectedAddressId = $scope.addresses[index].id;
        //$scope.selectedAddressStr = $scope.addresses[index].province + '省' + $scope.addresses[index].city + '市' + $scope.addresses[index].zone + $scope.addresses[index].town +  $scope.addresses[index].stree +'  联系人：' + $scope.addresses[index].contact_name + '  ' + $scope.addresses[index].contact_phone;
        $scope.selectedAddressStr = $scope.addresses[index].address;
        $scope.showSelectedAddress = true;
        $scope.itemClass[index] = 'active';
        $scope.labelClass[index] = 'red';
    }

    $scope.showAddressForm = false;
    $scope.addAddressClick = function(){
        $scope.showAddressForm = true;
    }

    $scope.province = '';
    $scope.city = '';
    $scope.zone = '';
    $scope.town = '';
    $scope.stree = '';
    $scope.contactName = '';
    $scope.contactPhone = '';
    $scope.provinceClass='';
    $scope.cityClass='';
    $scope.streeClass='';
    $scope.contactNameClass = '';
    $scope.contactPhoneClass = '';
    $scope.showAddAddressMsg = false;

    //提前获取省份
    $scope.provinces = $resource('get_province').query({}, function(){
        //$('#add-address-btn').show();
    });

    $scope.province = $scope.provinces[0];

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

    //添加新地址事件
    //$scope.addAddressClick = function(){
    //    //$('.add-address-area').show();
    //    $scope.province = $scope.provinces[0];
    //
    //    $scope.$watch('province', function(newValue, oldValue){
    //        var province_id = $scope.province.area_id;
    //        $scope.cities = $resource('get_cities').query({'province_id':province_id}, function(){
    //            $scope.city = $scope.cities[0];
    //        })
    //    })
    //
    //    $scope.$watch('city', function(newValue, oldValue){
    //        console.log($scope.city);
    //        var city_id = $scope.city.area_id;
    //        $scope.zones = $resource('get_zones').query({'city_id':city_id}, function(){
    //            $scope.zone = $scope.zones[0];
    //        })
    //    })
    //
    //}

    $scope.addAddress = function(){
        $scope.emptyCount = 0;
        if($scope.province == '' || $scope.province === undefined){

            $scope.emptyCount = $scope.emptyCount + 1;
            $scope.provinceClass = 'warning-input';
        }else{
            $scope.provinceClass = '';
        }

        if($scope.city == '' || $scope.city === undefined){
            $scope.emptyCount = $scope.emptyCount + 1;
            $scope.cityClass = 'warning-input';
        }else{
            $scope.cityClass = '';
        }

        if($scope.stree == '' || $scope.stree === undefined){
            $scope.emptyCount = $scope.emptyCount + 1;
            $scope.streeClass = 'warning-input';
        }else{
            $scope.streeClass = '';
        }

        if($scope.contactName == '' || $scope.contactName === undefined){
            $scope.emptyCount = $scope.emptyCount + 1;
            $scope.contactNameClass = 'warning-input';
        }else{
            $scope.contactNameClass = '';
        }

        if($scope.contactPhone == '' || $scope.contactPhone === undefined){
            $scope.emptyCount = $scope.emptyCount + 1;
            $scope.contactPhoneClass = 'warning-input';
        }else{
            $scope.contactPhoneClass = '';
            console.log($scope.contactPhone);
        }

        $scope.addAddressWarnStr = '请填写红框的信息。';
        console.log($scope.emptyCount)
        if($scope.emptyCount != 0){
            $scope.showAddAddressMsg = true;

        }else{
            $scope.showAddAddressMsg = false;
            $scope.newAddress = new addressFactory();
            //$scope.newAddress = new $resource('user_address');
            $scope.newAddress.province = $scope.province.area_name;
            $scope.newAddress.zone = $scope.zone.area_name;
            $scope.newAddress.city = $scope.city.area_name;
            $scope.newAddress.town = $scope.town;
            $scope.newAddress.stree = $scope.stree;
            $scope.newAddress.contact_name = $scope.contactName;
            $scope.newAddress.contact_phone = $scope.contactPhone;

            $scope.newAddress.$save(function(data){
                if(data.code == 200){
                    //添加展示地址
                    $scope.showAddressForm = false;
                    $scope.addresses = addressFactory.query({user_id:$scope.user_id});
                }
            });
        }
    }


    $scope.count = 2;

    $scope.service_price = function(){
        return $scope.unitPrice * $scope.count;
    }
    $scope.count_minus = function(){
        if($scope.count <= 2){
            $scope.showCountAlertMessage = true;
        }else{
            $scope.showCountAlertMessage = false;
            $scope.count = $scope.count - 1;
        }

    }

    $scope.addressSelectedConfirm = function(){
        $scope.showAddressForm = false;
        $('#address-selector').sidebar('hide');
    }

    $scope.addressSelectedCancle = function(){
        $scope.showAddressForm = false;
        $scope.showSelectedAddress = false;
        $scope.selectedAddressId = 0;
        $('#address-selector').sidebar('hide');
    }


    $scope.count_plus = function(){
        $scope.count = $scope.count + 1;
        if($scope.count >= 2){
            $scope.showCountAlertMessage = false;
        }
    }

    function test(){
        console.log('test')
    }

    $scope.$watch($scope.service_price, test);

    //提交服务预约
    $scope.commitAppointment = function(service_id){
        //TODO:添加更多的信息，如服务时间量，金额，预定日期；
        var commitServer = new commitFactory();
        if($scope.selectedAddressId != 0){

            commitServer.addressId = $scope.selectedAddressId;
            commitServer.serviceId = service_id;
            commitServer.name = $scope.name?$scope.name:'';
            commitServer.phone = $scope.phone?$scope.phone:'';
            commitServer.relationship = $scope.relationship?$scope.relationship:'';
            commitServer.easy_time = $scope.selected_time?$scope.selected_time:'';
            console.log(commitServer);
            commitServer.$save(function(data){
                if(data.status == 200){
                    //处理成功
                    window.location.href = 'my_appointment';
                }
            })



        }else{
            $scope.showAddressNeededMsg = true;
            //$('#address-needed-msg').transition('shake');
            //$('#address-needed-msg').transition('pulse');
        }

    };
}])
