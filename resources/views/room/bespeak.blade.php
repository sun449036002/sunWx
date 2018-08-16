@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/mui.min.css")}}"/>
<link rel="stylesheet" type="text/css" href="{{asset("css/room-bespeak.css")}}"/>
<link rel="stylesheet" type="text/css" href="{{asset("css/mui.picker.min.css")}}"/>
<script type="text/javascript" src="{{asset("js/mui.min.js")}}"></script>
<script type="text/javascript" src="{{asset("js/mui.picker.min.js")}}"></script>
<script>
    $(document).ready(function () {
        @if(empty($room->id))
            mui.alert("您预约的房源不存在", function(){
                window.location.href = "/room/list";
                return false;
            });
        @endif

        var speaking = false;

        //提交
        $(".main .input-btn").on("click", function(){
            checkForm();
        })

        /*选择看房时间*/
        mui(".mui-input-row").on('tap','#time',function(){
            console.log('tap');

            var picker = new mui.DtPicker();
            picker.show(function(rs) {
                /*
                 * rs.value 拼合后的 value
                 * rs.text 拼合后的 text
                 * rs.y 年，可以通过 rs.y.vaue 和 rs.y.text 获取值和文本
                 * rs.m 月，用法同年
                 * rs.d 日，用法同年
                 * rs.h 时，用法同年
                 * rs.i 分（minutes 的第二个字母），用法同年
                 */

                $("#time").val(rs.text);
                /*
                 * 返回 false 可以阻止选择框的关闭
                 * return false;
                 */
                /*
                 * 释放组件资源，释放后将将不能再操作组件
                 * 通常情况下，不需要示放组件，new DtPicker(options) 后，可以一直使用。
                 * 当前示例，因为内容较多，如不进行资原释放，在某些设备上会较慢。
                 * 所以每次用完便立即调用 dispose 进行释放，下次用时再创建新实例。
                 */
                picker.dispose();
            });
        });

        /*表单 验证*/
        function checkForm(){
            if (speaking) {
                mui.alert("请勿重复提交");
                return false;
            }
            speaking = true;

            var name = $("input[name='name']").val();
            var tel = $("input[name='tel']").val();
            var num = $("input[name='num']").val();
            var address = $("input[name='address']").val();
            var time = $("input[name='time']").val();
            var regx = /^[1-9]\d*$/ ;//判断正整数

            if(name == ""){
                speaking = false;
                mui.alert("姓名不能为空");
                return false;
            }
            if(tel == ""){
                speaking = false;
                mui.alert("手机号不能为空");
                return false;
            } else {
                if (!(/^1[3|4|5|7|8]\d{9}$/.test(tel))) {
                    speaking = false;
                    mui.alert('请输入正确的手机号码！');
                    return false;
                }
            }
            if(num == ""){
                speaking = false;
                mui.alert("预约人数不能为空");
                return false;
            }
            if(!regx.test(num)){
                speaking = false;
                mui.alert("请填写正确的预约人数");
                return false;
            }
            if(address==""){
                speaking = false;
                mui.alert("接送地址不能为空");
                return false;
            }
            if(time==""){
                speaking = false;
                mui.alert("接送时间不能为空");
                return false;
            }

            $.ajax({
                type : 'post',
                url : "/room/bespeaking",
                data : {
                    roomId: "{{$room->id ?? 0}}",
                    name:name,
                    tel:tel,
                    num: num,
                    address:address,
                    time: time
                },
                dataType : "json",
                headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
                success : function(res){
                    if (res.code > 0) {
                        mui.alert("预约成功", function(){
                            window.location.href = "/my/bespeak-list"
                        });
                    } else {
                        mui.alert(res.msg);
                    }
                    speaking = false;
                },
                fail :function(){
                    speaking = false;
                }
            });
        }
    });
</script>

<div class="main">
    <div class="item">
        <div class="room-name">{{$room->name ?? ""}}</div>
    </div>
    <div class="item">
        <div class="input-label">姓名</div>
        <div class="input-box"><input type="text" name="name" value="" placeholder="填写姓名"/></div>
    </div>
    <div class="item">
        <div class="input-label">电话</div>
        <div class="input-box"><input type="text" name="tel" value=""  placeholder="填写电话"/></div>
    </div>
    <div class="item">
        <div class="input-label">预约人数</div>
        <div class="input-box"><input type="text" name="num" value="" min="0" max="99" placeholder="填写预约人数"/></div>
    </div>
    <div class="item">
        <div class="input-label">接送地址</div>
        <div class="input-box"><input type="text" name="address" value=""  placeholder="填写接送地址"/></div>
    </div>
    <div class="item">
        <div class="input-label">接送时间</div>
        <div class="input-box mui-input-row"><input type="text" id="time" name="time" value=""  placeholder="填写接送时间"/></div>
    </div>
    <div class="item">
        <div class="input-btn">提交</div>
    </div>
</div>