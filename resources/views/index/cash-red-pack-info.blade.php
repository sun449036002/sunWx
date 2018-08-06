@include("header")
<!-- Styles -->
<link href="css/cash-red-pack-info.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" charset="utf-8">
    wx.config(<?php echo $wxapp->jssdk->buildConfig(['onMenuShareTimeline','onMenuShareAppMessage', 'sendAppMessage'], true) ?>);



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

    //分享给朋友
    function shareFriend() {
        WeixinJSBridge.invoke('sendAppMessage',{
            "appid": 'wx11fe145bfca2b25e',
            "img_url": '',
            "img_width": "200",
            "img_height": "200",
            "link": '',
            "desc": '描述',
            "title": '标题'
        }, function(res) {
            alert(JSON.stringify(res));
        })
    }

    $(document).ready(function(){
        //显示规则
        $(".cash-red-pack-main .rule").on("click", function(){
            $(".rule-container").show();
        });

        //关闭
        $(".red-pack-info .btn-close").on("click", function(){
            $(".red-pack-container").hide();
        });

        //
        $(".btn-share-friend").on("click", function(){
            shareFriend();
        });

        //WeixinJSBridgeReady
        document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
            alert('WeixinJSBridgeReady ok');
            // 发送给好友
            WeixinJSBridge.on('menu:share:appmessage', function(argv){
                shareFriend();
            });
            // 分享到朋友圈
            WeixinJSBridge.on('menu:share:timeline', function(argv){
//                shareTimeline();
            });
            // 分享到微博
            WeixinJSBridge.on('menu:share:weibo', function(argv){
//                shareWeibo();
            });
        }, false);

        var tg = $(".remainingTime");
        var t = parseInt(tg.html());
        setInterval(function(){
            t = t - 1;
            var m = parseInt(t / 60) % 60;
            var h = parseInt(t / 3600) % 60;
            var s = t % 60;
            tg.html(h + ":" + m + ":" + s);
        }, 1000)
    });
</script>
</head>
<body>
<div class="cash-red-pack-main">
    <div class="tips">天天拆红包 领百元现金</div>
    <div class="rule">活动规则</div>
    <div class="mid" style="padding:0 15px;color:#FFF;font-weight: 600;line-height: 30px">
        <div>进度:{{$received . "/" . $total}}</div>
        <div>倒计时:<span class="remainingTime">{{$remainingTime}}</span></div>

        <div>
            <div>助力列表:</div>
            @foreach($redPackRecordList as $item)
                <div><img style="width: 36px;height: 36px;border-radius: 18px;" src="{{$item->headImgUrl}}"> 助力后，您获得了{{$item->money}}元</div>
            @endforeach
        </div>
    </div>
</div>

<div class="red-pack-container" style="display: {{$from == 'cash-receive' || 1 ? 'block' : 'none'}}">
    <div class="red-pack-bg">
        <div class="red-pack-info">
            <div class="btn-close"></div>
            <div class="tips">恭喜你共获得</div>
            <div class="total">{{$total}}<span>元</span></div>
            <div class="received">已拆得{{$received}}元</div>
            <div class="btn-share-friend">分享给好友帮忙助力</div>
        </div>
    </div>
</div>

@include('index/cash-red-pack-rule')

</body>
</html>
