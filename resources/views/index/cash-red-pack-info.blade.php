@include("header")
<!-- Styles -->
<link href="css/cash-red-pack-info.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" charset="utf-8">
    wx.config(<?php echo $wxapp->jssdk->buildConfig(['onMenuShareTimeline','onMenuShareAppMessage'], false) ?>);

    //用ready方法来接收验证成功
    wx.ready(function() {
        // alert(location.href.split('#')[0]);
        // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。

        //分享到微信朋友圈
        wx.onMenuShareTimeline({
            title: '分享到微信朋友圈，赚更多的现金', // 分享标题
            link: "{{env('APP_URL')}}/assistance-page?redPackId={{$redPackId}}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: 'http://www.gumama120.com/uploads/allimg/180531/1-1P531155024455.jpg', // 分享图标
            success: function () {
                // 用户点击了分享后执行的回调函数
                $(".share-layer").hide();
            }
        });

        //分享给微信好友
        wx.onMenuShareAppMessage({
            title: '分享给朋友们，赚更多的现金', // 分享标题
            desc: '朋友点击支持后，双方都可获得赏金', // 分享描述
            link: "{{env('APP_URL')}}/assistance-page?redPackId={{$redPackId}}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: 'http://www.gumama120.com/uploads/allimg/180531/1-1P531155024455.jpg', // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // 用户点击了分享后执行的回调函数
                $(".share-layer").hide();
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

        //分享
        $(".btn-share-friend, .go-on-share").on("click", function(){
            $(".share-layer").show();
        });

        //切换房源与助力团
        $(".sale-tab-title .tab").on("click",function(){
            $(this).addClass("selected").siblings().removeClass("selected");
            var class1 = $(this).data("for-class");
            var class2 = $(this).siblings().data("for-class");
            $("." + class1).show();
            $("." + class2).hide();
        });

        //倒计时
        var tg = $(".remainingTime");
        var t = parseInt("{{$remainingTime}}");
        setInterval(function(){
            t = t - 1;
            var m = parseInt(t / 60) % 60;
            var h = parseInt(t / 3600) % 60;
            var s = t % 60;
            m = (m + "").length === 1 ? "0" + m : m;
            h = (h + "").length === 1 ? "0" + h : h;
            s = (s + "").length === 1 ? "0" + s : s;
            tg.find(".h").html(h);
            tg.find(".m").html(m);
            tg.find(".s").html(s);
        }, 1000);

        //进度条
        $('.progressbar').each(function(){
            var t = $(this),
                dataperc = t.attr('data-perc'),
                barperc = dataperc * 0.056;
//            console.log(dataperc);
            t.find('.bar').animate({width:barperc+"rem"}, dataperc*25);
            t.find('.label').append('<div class="perc"></div>');

            function perc(){
                var length = t.find('.bar').css('width'),
                    perc = Math.round(parseInt(length)/5.56),
                    labelpos = (parseInt(length)-2);
//                console.log(labelpos);
                t.find('.label').css('left',  labelpos - 40);
                t.find('.perc').text('还差{{$total - $received}}元');
            }
            perc();
            setInterval(perc, 0);
        });
    });
</script>
</head>
<body>
<div class="cash-red-pack-main">
    <div class="tips">天天拆红包 领百元现金</div>
    <div class="rule">活动规则</div>
    <div class="mid">

        <div class="red-pack-box">
            <div class="received-box">已经拆得<span>{{$received}}</span>元</div>
            <div class="progressbar" data-perc="{{intval(($received / $total) * 100)}}">
                <div class="bar color3"><span></span></div>
                <div class="label"><span></span></div>
            </div>
            <div class="time-box">
                <span class="remainingTime">
                    <span class="h">00</span>:<span class="m">00</span>:<span class="s">00</span>
                </span>
                后失效，赶紧找人助力~
            </div>
            <div class="go-on-share">再找人助力</div>
        </div>
    </div>

    <div class="sale-main">
        <div class="sale-tab-title">
            <div class="tab selected" data-for-class="product-list">推荐房源</div>
            <div class="tab" data-for-class="help-list">我的助力团</div>
        </div>
        <div class="product-list">
            @foreach($roomList as $room)
                <a href="/room-source/detail?id={{$room->id}}">
                <div class="item">
                    <div class="img"><img src="{{$room->cover}}"/></div>
                    <div class="product-info">
                        <div class="info">
                            <div class="in-time">{{$room->name}}</div>
                            <div class="price-box">
                                <div class="new-price">均价 <span class="price">{{$room->avgPrice}}元</span></div>
                                <div class="old-price"><span class="price">{{$room->area}}</span></div>
                            </div>
                        </div>
                        <div class="btn-buy">抢</div>
                    </div>
                </div>
                </a>
            @endforeach
        </div>

        <div class="help-list" style="display: none;">
            @foreach($redPackRecordList as $item)
                <div class="help-item">
                    <div class="head-img">
                        <img src="{{$item->headImgUrl}}">
                        <div class="nickname">明白清风,在{{$item->time}}</div>
                    </div>
                    <div class="text">助力{{$item->money}}元</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="red-pack-container" style="display: {{$from == 'cash-receive' ? 'block' : 'none'}}">
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

@include('components/cash-red-pack-rule')
@include('components/shareLayer')
@include('components/canNotBack')

</body>
</html>
