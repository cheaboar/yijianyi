/**
 * Created by Chan on 15/7/21.
 */

//记时器
var util = {
    wait: 90,
    hsTime: function (that) {

        _this = this;

        if (_this.wait == 1) {
            $(that).removeAttr("disabled").text('重发短信验证码');
            _this.wait = 90;

        } else {
            var _this = this;
            $(that).attr("disabled", true).text('在' + _this.wait + '秒后点此重发');
            _this.wait--;
            setTimeout(function () {
                _this.hsTime(that);
            }, 1000)
        }
    }
}

var formValid = {
    isPhone : function(phone){
        reg = /1\d{10,11}/;
        return reg.test(phone);
    },
    isEmpty: function(str){
        if(str.length == 0){
            return true;
        }else{
            return false;
        }
    }
}