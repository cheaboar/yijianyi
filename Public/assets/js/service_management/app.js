/**
 * Created by Chan on 15/6/4.
 */

var tableApp = angular.module('tableApp', ['ngResource']);

tableApp.config(['$resourceProvider', function($resourceProvider) {
    // Don't strip trailing slashes from calculated URLs
    $resourceProvider.defaults.stripTrailingSlashes = false;
}]);


tableApp.factory('serviceType', function($resource){
    result = $resource('/management/service_management/get_service_json', null,null);
    return result;
});

tableApp.controller('tableController', function($scope, serviceType){
    $scope.name = 'good time';
    $scope.items = [
        {title: 'Paint pots', quantity: 8, price: 3.95},
        {title: 'Polka dots', quantity: 17, price: 12.95},
        {title: 'Pebbles', quantity: 5, price: 6.95}
    ];
    $scope.serviceTypes = serviceType.query()
    //console.log($scope.items)
    console.log($scope.serviceTypes)

    var service = new serviceType();
    service.type = 1000;
    service.name = 'TTD';
    service.description = 'This is a description';
    service.price_per_unit = 234;
    service.unit = '元';
    service.appointment_count = 233;
    service.priority = 1005;
    console.log(service);
    var r= service.$save();
    console.log(r);
});