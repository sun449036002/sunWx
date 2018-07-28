<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>首页</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <link href="css/index.css" rel="stylesheet" type="text/css" />

        <script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js" type="text/javascript" charset="utf-8"></script>
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
            })
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
            <div class="btn-sign btn-sign-in">签到领现金</div>
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
                <div class="price">0.66<span>元</span></div>
                <div class="tips">已经存入余额</div>
            </div>
        </div>
    </div>
    </body>
</html>
