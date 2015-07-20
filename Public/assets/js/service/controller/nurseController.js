/**
 * Created by Chan on 15/6/8.
 */

nurseModule = angular.module('nurseApp', []);

babysitterModule = angular.module('babysitterApp', []);


var header_directive =  function(){
    return {
        restrict : 'E',
        replace : true,
        transclude:true,
        scope:{title: '=navTitle'},
        template:'' +
        '<div class="detail-header ">'+
            '<div class="ui grid">'+
            '<div class="two wide column item">'+
            '<div class="ui item">'+
            '<i class="list icon " id="show-list"></i>'+
            '</div>'+

            '</div>'+
            '<div class="twelve wide column item">'+
            '<div class="item">{{title}}</div>'+
            '</div>'+
            '<div class="two wide column item">'+
            '</div>'+

            '</div>'+

            '<div class="ui red vertical  sidebar menu" id="home-slider">'+
            '<a href="#" class="active item" id="close-sidebar">'+
            '<i class="remove icon" ></i>'+
            '关闭'+
            '</a>'+
            '<a href="service_info" class="  item">'+
            '<i class=" red home icon"></i>'+
            '主页'+
            '</a>'+
            '<a class="  item">'+
            '<i class=" teal  time icon"></i>'+
            '我的预约'+
            '</a>'+

            '<a href="#" class="item">'+
            '<i class="user black icon"></i>'+
            '用户中心'+
            '</a>'+
            '<a class="item">'+
            '<i class="info purple icon"></i>'+
            '关于我们'+
            '</a>'+
            '</div>'+
            '</div>',
        //templateUrl : 'nav_header.html',
        link: function(scope, element, attrs){
            console.log(scope.title)
            scope.a = 'good';
            //$('.ui.dropdown.item').dropdown();
            $('#home-slider').sidebar('setting', {
                onShow:function(){

                },
                overlay: true
            })
            $('#show-list').on('click', function(){
                $('#home-slider').sidebar('toggle');
            })
            $('#close-sidebar').on('click', function(){
                $('#home-slider').sidebar('toggle');
            })

        }
    };
};

nurseModule.directive('myNav', header_directive);


babysitterModule.controller('babysitterCtrl', function($scope){
    $scope.title = '月嫂';

}).directive('myNav', header_directive);

nurseModule.controller('nurseCtrl', ['$scope', function($scope){
    $scope.name = 'good';
    $scope.navName = '家护详情';
}]).directive('ngNav', function() {
    return {
        restrict:'E',
        replace:true,
        template: '<div></div>'
    };
});


nurseModule.controller('Controller', ['$scope', function($scope) {
    $scope.customer = {
        name: 'Naomi',
        address: '1600 Amphitheatre'
    };
}]).directive('whatA', function() {
        return {
            restrict:'E',
            replace:true,
            template: '<div>Name: {{customer.name}} Address: {{customer.address}}</div>'
        };
    });
