<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <meta name="apple-tel-web-app-capable" content="yes">
    <meta name="apple-tel-web-app-status-bar-style" content="black">
    <title>意见反馈</title>
    <link href="{{asset('css/mui.min.css')}}" rel="stylesheet" />
    <link href="{{asset('css/mui.styles.css')}}" rel="stylesheet" />

    <style type="text/css">
        html {
            font-size: calc(100vw/7.5);
        }
        body {
            margin:0;
            width: 7.5rem;
            font-size: .3rem;
        }
        a {
            text-decoration: none;
        }

        p {
            margin:0;
            padding:0;
        }
    </style>

</head>

<body>
<header class="mui-bar mui-bar-nav bgeee">
    <a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left c433"></a>
    <h1 id="title" class="mui-title">意见反馈</h1>
</header>
<div class="mui-content">
    <div class="mui-input-group feedback-form">
        <div class="mui-input-row">
            <label for="name">姓名</label>
            <input id="name" name="name" type="text" class="mui-input-clear" placeholder="姓名">
        </div>

        <div class="mui-input-row">
            <label for="tel">电话</label>
            <input id="tel" name="tel" type="text" class="mui-input-clear" placeholder="电话">
        </div>

        <div class="mui-input-row">
            <label for="desc">反馈内容</label>
            <textarea id="desc" name="desc" type="text" class="mui-input-clear" placeholder="反馈内容"></textarea>
        </div>

        <div class="last-btn-div"><button type="submit" class="mui-btn mui-btn-red" id="submitBtn">提交</button></div>
    </div>
</div>

<script src="{{asset('js/jquery-2.1.1.js')}}"></script>
<script src="{{asset('js/mui.min.js')}}"></script>
<script type="text/javascript">
    mui.init();

    $(document).ready(function() {
        mui(".last-btn-div").on('tap', '#submitBtn', checkForm);
    });

    /*表单 验证*/
    function checkForm() {

        var $this = $(this);

        $this.text('正在提交...');

        var check = true;

        var name = $("#name").val();
        var tel = $("#tel").val();
        var desc = $("#desc").val();

        mui(".feedback-form input[type=text]").each(function() {
            //若当前input为空，则alert提醒
            if(!this.value || this.value.trim() === "") {
                var label = this.previousElementSibling;
                mui.alert(label.innerText + "不允许为空");
                check = false;
                return false;
            }
        }); //校验通过，继续执行业务逻辑

        if(!check) {
            $this.text('提交');
            return false;
        }

        if(!(/^1[3|4|5|7|8]\d{9}$/.test(tel))) {

            mui.alert('请输入正确的手机号！');
            $this.text('提交');
            return false;
        }

        $.ajax({
            type : 'POST',
            url : "/my/suggestionSubmit",
            data : {
                name: name,
                tel: tel,
                desc: desc
            },
            dataType : "json",
            headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
            success : function(res){
                if (res.code > 0) {
                    mui.alert(res.msg);
                } else {
                    mui.alert(res.msg, function(){
                        window.location.href = "/my"
                    });
                }
            }
        });
    }
</script>
</body>
</html>