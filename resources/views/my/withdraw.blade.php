<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <meta name="apple-tel-web-app-capable" content="yes">
    <meta name="apple-tel-web-app-status-bar-style" content="black">
    <title>提现申请</title>
    <link href="{{asset('css/mui.min.css')}}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/mui.picker.min.css')}}" />
    <link href="{{asset('css/mui.styles.css')}}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset("css/my-red-pack-list.css")}}"/>

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

        .red-pack-main {
            display: none;
            position: absolute;
            top:0;
            bottom:0;
            left:100vw;
            z-index: 11;
        }
        .red-pack-main .bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            z-index: 12;
            justify-content: space-between;
            padding: 0 .3rem;
        }
        .red-pack-main .red-pack-box {
            margin-top:1.2rem;
        }
        .red-pack-main .item {
            position: relative;
        }
        .red-pack-main .item.selected .selected-icon {
            width: 1rem;
            height: 1rem;
            background-size: cover;
            position: absolute;
            top: 0;
            right: 0;
            background-image: url('{{asset("imgs/selected.png")}}');
        }
        .red-pack-main .bar span {
            padding: 0 .1rem;
            font-weight: 600;
        }
        .red-pack-main .bar .info {
            width:4.5rem;
        }
        .red-pack-main .bar .btn {
            background-color: #1ab394;
            color: #FFF;
            height: .6rem;
            margin-top: .2rem;
            line-height: .6rem;
            padding: 0 .3rem;
            border-radius: .1rem;
        }

        .feedback-form .preview-row2 {
            display: flex;
            flex-wrap: wrap;
        }
        .feedback-form .preview-row2 .img-item {
            position: relative;
            width: 1.8rem;
            height: 1.8rem;
            margin: 0 0 0.5rem 0.5rem;
        }
        .feedback-form .preview-row2 .img-item img {
            width:100%;
            height: 100%;
        }
        .feedback-form .preview-row2 .img-item .del-img {
            position: absolute;
            top: -.3rem;
            right: -.3rem;
            width: 0.5rem;
            height: 0.5rem;
            background-color: rgba(0,0,0,0.8);
            color: #FFF;
            text-align: center;
            line-height: 0.5rem;
            border-radius: .3rem;
        }

        .tips {
            padding:.3rem;
            background-color:rgba(0,0,0,0.5);
            color:#FFF;
        }

    </style>

</head>

<body>
<header class="mui-bar mui-bar-nav bgeee">
    <a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left c433"></a>
    <h1 id="title" class="mui-title">提现申请</h1>
</header>
<div class="mui-content">
    <div class="mui-input-group feedback-form">
        <div class="mui-input-row">
            <label for="buyers">申请人</label>
            <input id="buyers" name="buyers" type="text" class="mui-input-clear" placeholder="申请人">
        </div>

        <div class="mui-input-row">
            <label for="tel">联系电话</label>
            <input id="tel" name="tel" type="text" class="mui-input-clear" placeholder="联系电话">
        </div>

        <div class="mui-input-row">
            <label>我的可用红包</label>
            <input id="redPackIdsClick" type="text" class="mui-input-clear" placeholder="点击选择可提现的红包" readonly>
            <input id="redPackIds" type="hidden" name="redPackIds" value="">
        </div>

        <div class="mui-table-view-divider">以下三种支付账号至少填一项</div>
        <div class="mui-input-row">
            <label for="alipay">支付宝</label>
            <input id="alipay" name="alipay" type="text" class="mui-input-clear pay-type" placeholder="支付宝">
        </div>
        <div class="mui-input-row">
            <label for="weixin">微信</label>
            <input id="weixin" name="weixin" type="text" class="mui-input-clear pay-type" placeholder="微信">
        </div>
        <div class="mui-input-row">
            <label for="bankcard">银行账号</label>
            <input id="bankcard" name="bankcard" type="text" class="mui-input-clear pay-type" placeholder="银行账号">
        </div>

        <div class="last-btn-div"><button type="submit" class="mui-btn mui-btn-red" id="submitBtn">提交</button></div>
    </div>

    <div class="tips">只有在两个月前领的取红包才能申请提现，未满两个月的红包，暂时无法提现，请过段时间后再申请</div>
</div>

{{-- 红包展示区域 --}}
<div class="red-pack-main">
    <div class="bar">
        <div class="info">
            选择<span class="num">0</span>个红包，总价值<span class="total-val">0</span>元
        </div>
        <div class="btn btn-select-all">全选</div>
        <div class="btn btn-select-sure">确定</div>
    </div>
    <div class="red-pack-box">
        <div class="list"></div>
    </div>
</div>

<script src="{{asset('js/jquery-2.1.1.js')}}"></script>
<script src="{{asset('js/mui.min.js')}}"></script>
<script src="{{asset('js/mui.picker.min.js')}}"></script>
<script src="{{asset('js/jweixin-1.2.0.js')}}" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    mui.init();

    $(document).ready(function() {
        mui(".last-btn-div").on('tap', '#submitBtn', checkForm);

        function showRedPackList(list) {
            var redPackListDom = $(".red-pack-main .list");
            redPackListDom.empty();
            $.each(list, function(k,item){
                console.log(item);
                var html = '<div class="item ' + item.type + '" data-id="' + item.id + '">' +
                    '<div class="selected-icon"></div>' +
                    '<div class="bg"></div>' +
                    '<div class="data">' +
                        '<div class="money"><span>' + item.total + '</span>元</div>' +
                        '<div class="from">活动</div>' +
                        '<div class="expiredTime">领取时间:' + item.createTime + '</div>' +
                    '</div>' +
                    '</div>';
                redPackListDom.append(html);

            });
        }

        //我的红包选择
        $("#redPackIdsClick").on("click", function(){
            var self = $(this);
            //清空原先的数据
            self.val('');
            $("#redPackIds").val('');
            $(".red-pack-main .bar .num").text(0);
            $(".red-pack-main .bar .total-val").text(0);

            //获取数据
            $.ajax({
                type : 'get',
                url : "{{route('/my/getTwoMonthAgoEnabledRedPackList')}}",
                data : {},
                dataType : "json",
                headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
                success : function(res){
                    if (res.code > 0) {
                        mui.alert(res.msg);
                    } else {
                        var list = res.data || [];
                        if (list.length === 0) {
                            self.val("暂无可用红包");
                            return false;
                        } else {
                            showRedPackList(list);
                            $(".red-pack-main").show().animate({
                                left : 0
                            }, 100, "linear");
                            $(document).scrollTop(0);
                        }
                    }
                }
            });
        });

        //红包选择
        $(".red-pack-main").on("click", ".item", function(e){
            $(this).toggleClass("selected");
            var totalMoney = 0;
            var selectedItems = $(".red-pack-main .item.selected");
            var redPackIds = [];
            selectedItems.each(function(){
                var v = $(this).find(".money span").text();
                totalMoney += parseInt(v);
                redPackIds.push($(this).data("id"));
            });
            $(".red-pack-main .bar .num").text(selectedItems.length);
            $(".red-pack-main .bar .total-val").text(totalMoney);

            //区别是我的红包选择，还是朋友红包的选择
            $("#redPackIds").val(redPackIds);
            $("#redPackIdsClick").val("您选择了" + selectedItems.length + "个红包，价值" + totalMoney + '元');

        });

        //确认选择
        $(".red-pack-main").on("click", ".btn-select-sure", function(e){
            $(".red-pack-main").animate({
                left : "100vw"
            }).hide();
        });

        //全选
        $(".red-pack-main").on("click", ".btn-select-all", function(e){
            $(".red-pack-main .red-pack-box .item").trigger("click");
        });
    });

    /*表单 验证*/
    function checkForm() {
        var $this = $(this);
        $this.text('正在提交...');
        var check = true;
        var payTypeNum = 0;

        var buyers = $("#buyers").val();
        var tel = $("#tel").val();
        var alipay = $("#alipay").val();
        var weixin = $("#weixin").val();
        var bankcard = $("#bankcard").val();

        mui(".feedback-form input[type=text]").each(function() {
            //若当前input为空，则alert提醒
            if($(this).hasClass("pay-type") && this.value.trim() !== "") {
                payTypeNum++;
            }

            if(!this.value || this.value.trim() === "") {
                if(!$(this).hasClass("pay-type")) {
                    var label = this.previousElementSibling;
                    mui.alert(label.innerText + "不允许为空");
                    check = false;
                    return false;
                }
            }
        }); //校验通过，继续执行业务逻辑

        if (payTypeNum === 0) {
            mui.alert('至少需要提供一种打款账号！');
            check = false;
        }

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
            type : 'post',
            url : "/my/withdraw",
            data : {
                buyers: buyers,
                tel: tel,
                alipay: alipay,
                weixin: weixin,
                bankcard: bankcard,
                redPackIds : $("#redPackIds").val()
            },
            dataType : "json",
            headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
            success : function(res){
                if (res.code > 0) {
                    mui.alert(res.msg);
                } else {
                    mui.alert("提交成功", function(){
                        window.location.href = "/my/balance";
                    });
                }
            }
        });

    }
</script>
</body>
</html>