@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/mui.min.css")}}"/>
<link rel="stylesheet" type="text/css" href="{{asset("css/room-bespeak.css")}}"/>
<script type="text/javascript" src="{{asset("js/mui.min.js")}}"></script>
<script>
    $(document).ready(function () {
        var applying = false;
        
        //提交
        $(".main .input-btn").on("click", function(){
            checkForm();
        });

        /*表单 验证*/
        function checkForm(){
            if (applying) {
                mui.alert("请勿重复提交");
                return false;
            }
            applying = true;

            var name = $("input[name='name']").val();
            var tel = $("input[name='tel']").val();

            if(name === ""){
                applying = false;
                mui.alert("姓名不能为空");
                return false;
            }
            if(tel === ""){
                applying = false;
                mui.alert("手机号不能为空");
                return false;
            } else {
                if (!(/^1[3|4|5|7|8]\d{9}$/.test(tel))) {
                    applying = false;
                    mui.alert('请输入正确的手机号码！');
                    return false;
                }
            }

            $.ajax({
                type : 'post',
                url : "/broker_apply",
                data : {
                    name:name,
                    tel:tel
                },
                dataType : "json",
                headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
                success : function(res){
                    if (res.code > 0) {
                        mui.alert(res.msg);
                    } else {
                        mui.alert(res.msg, function(){
                            window.location.href = "/"
                        });
                    }
                    applying = false;
                },
                fail :function(){
                    applying = false;
                }
            });
        }
    });
</script>

<div class="main">
    <div class="item">
        <div class="room-name">全民经纪人报名表</div>
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
        <div class="input-btn">提交</div>
    </div>
</div>