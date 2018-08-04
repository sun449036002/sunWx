@include("header")
<!-- Styles -->
<link href="css/cash-red-pack-info.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" charset="utf-8">
    wx.config(<?php echo $wxapp->jssdk->buildConfig(['onMenuShareTimeline','onMenuShareAppMessage'], true) ?>);

    //用ready方法来接收验证成功
    wx.ready(function() {
        // alert(location.href.split('#')[0]);
        // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。

        //分享到微信朋友圈
        wx.onMenuShareTimeline({
            title: '分享到微信朋友圈，赚更多的现金', // 分享标题
            link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: 'http://www.gumama120.com/uploads/allimg/180531/1-1P531155024455.jpg', // 分享图标
            success: function () {
                // 用户点击了分享后执行的回调函数
            }
        });



        //分享给微信好友
        wx.onMenuShareAppMessage({
            title: '分享给朋友们，赚更多的现金', // 分享标题
            desc: '朋友点击支持后，双方都可获得赏金', // 分享描述
            link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: 'http://www.gumama120.com/uploads/allimg/180531/1-1P531155024455.jpg', // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // 用户点击了分享后执行的回调函数
            }
        });
    });

    //滚动插件
    (function($) {
        $.fn.extend({
            Scroll: function(opt, callback) {
                //参数初始化
                if (!opt) var opt = {};
                var _this = this.eq(0).find("ul:first");
                var lineH = _this.find("li:first").outerHeight(true), //获取行高
                    line = opt.line ? parseInt(opt.line, 10) : parseInt(this.height() / lineH, 10), //每次滚动的行数，默认为一屏，即父容器高度
                    speed = opt.speed ? parseInt(opt.speed, 10) : 500, //卷动速度，数值越大，速度越慢（毫秒）
                    timer = opt.timer ? parseInt(opt.timer, 10) : 3000; //滚动的时间间隔（毫秒）
                if (line == 0) line = 1;
                var upHeight = 0 - line * lineH;
                //滚动函数
                scrollUp = function() {
                    _this.animate({
                        marginTop: upHeight
                    }, speed, function() {
                        for (i = 1; i <= line; i++) {
                            _this.find("li:first").appendTo(_this);
                        }
                        _this.css({
                            marginTop: 0
                        });
                    });
                }
                //鼠标事件绑定
                _this.hover(function() {
                    clearInterval(timerID);
                }, function() {
                    timerID = setInterval("scrollUp()", timer);
                }).mouseout();
            }
        });
    })(jQuery);

    $(document).ready(function(){
        //显示规则
        $(".cash-red-pack-main .rule").on("click", function(){
            $(".rule-container").show();
        });

        //关闭
        $(".red-pack-info .btn-close").on("click", function(){
            $(".red-pack-container").hide();
        });

        //帮他助力
        $(".btn-assistance").on("click", function(){
            var isSubscribe = parseInt("{{$user['is_subscribe'] ?? 0}}");
            if (!isSubscribe) {
                $(".subscribeBox").show();
                return false;
            }
            $.ajax({
                type : 'post',
                url : "/assistance",
                data : {
                    redPackId:"{{$redPackId}}"
                },
                dataType : "json",
                headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
                success : function(res){
                    res.cb = function (res) {
                        var unCompleteRedPackId = res.data.unCompleteRedPackId || 0;
                        if (unCompleteRedPackId > 0) {
                            window.location.href = "/cash-red-pack-info?redPackId=" + unCompleteRedPackId;
                        }
                    };
                    alertPopup.show(res);
                }
            });
        });
    });
</script>
</head>
<body>
<div class="cash-red-pack-main">
    <div class="tips">天天拆红包 领百元现金</div>
    <div class="rule">活动规则</div>
    <div class="mid">
        <div class=""><button class="btn-assistance" style="width:30vw;height: 10vh;" value="帮他助力">帮他助力</button></div>
    </div>
</div>

@include('index/cash-red-pack-rule')

@include("components/subscribe")
@include("components/alertPopup")

</body>
</html>
