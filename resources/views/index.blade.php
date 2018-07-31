@include("header")

<!-- Styles -->
<link href="css/index.css" rel="stylesheet" type="text/css" />

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

    $(document).ready(function(){
        //打开红包
        $(".btn-open-red-pack").on("click", function(){
            var num = Math.random().toFixed(2) * 100;
            $(".red-pack-area").show();
            var target = $(".red-pack-area .red-pack");
            var wValue= "60vw";
            var hValue= "50vh";
            target.animate({width: wValue,height: hValue}, 50);
        });
        //关闭红包
        $(".red-pack,.red-pack.mask").on("click", function(){
            var target = $(".red-pack-area .red-pack");
            var wValue= "30vw";
            var hValue= "25vh";
            target.animate({width: wValue,height: hValue}, 50);
            setTimeout(function(){
                $(".red-pack-area").hide();
            }, 60)

        });
    });
</script>
</head>
<body>
<div class="main">
    <div class="top">
        <div class="progress-bar">
            <div class="sub-progress-bar spb-day1"></div>
        </div>
        <div class="info">
            <div class="money">5.20 元</div>
            <div class="withdraw-type">提现方式</div>
        </div>
        @if(!empty($isSignIn))
            <div class="btn-sign btn-share">分享给好友，继续赚现金</div>
        @else
            <div class="btn-sign btn-open-red-pack">签到领现金</div>
        @endif
    </div>
    <div class="mid">
        <div class="tips">- 现金抵扣区 -</div>
        <div class="product-list">
            <div class="item">
                <div class="img"><img src="./imgs/1.jpg"/></div>
                <div class="product-info">
                    <div class="info">
                        <div class="in-time">限时特价</div>
                        <div class="price-box">
                            <div class="new-price">特价 <span class="price">32</span></div>
                            <div class="old-price">原价 <span class="price">168</span></div>
                        </div>
                    </div>
                    <div class="btn-buy">抢</div>
                </div>
            </div>
            <div class="item">
                <div class="img"><img src="./imgs/2.jpg"/></div>
                <div class="product-info">
                    <div class="info">
                        <div class="in-time">限时特价</div>
                        <div class="price-box">
                            <div class="new-price">特价 <span class="price">32</span></div>
                            <div class="old-price">原价 <span class="price">168</span></div>
                        </div>
                    </div>
                    <div class="btn-buy">抢</div>
                </div>
            </div>
            <div class="item">
                <div class="img"><img src="./imgs/2.jpg"/></div>
                <div class="product-info">
                    <div class="info">
                        <div class="in-time">限时特价</div>
                        <div class="price-box">
                            <div class="new-price">特价 <span class="price">32</span></div>
                            <div class="old-price">原价 <span class="price">168</span></div>
                        </div>
                    </div>
                    <div class="btn-buy">抢</div>
                </div>
            </div>
        </div>
    </div>
    <div class="bottom"></div>
</div>

{{-- 红包区 --}}
<div class="red-pack-area">
    <div class="mask"></div>
    <div class="red-pack">
        <div class="red-pack-info">
            <div class="price"><span class="num">0.66</span><span>元</span></div>
            <div class="tips">已经存入余额</div>
        </div>
    </div>
</div>
</body>
</html>