@include("header")
<!-- Styles -->
<link rel="stylesheet" type="text/css" href="{{asset("css/assistance-page.css")}}" />

<script type="text/javascript" charset="utf-8">
    wx.config(<?php echo $wxapp->jssdk->buildConfig(['onMenuShareTimeline','onMenuShareAppMessage'], false) ?>);

    //用ready方法来接收验证成功
    wx.ready(function() {
        // alert(location.href.split('#')[0]);
        // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。

        //分享到微信朋友圈
        wx.onMenuShareTimeline({
            title: '分享到微信朋友圈，赚更多的赏金', // 分享标题
            link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: '{{asset('imgs/share-icon-1.png')}}', // 分享图标
            success: function () {
                // 用户点击了分享后执行的回调函数
            }
        });

        //分享给微信好友
        wx.onMenuShareAppMessage({
            title: '分享给朋友们，赚更多的赏金', // 分享标题
            desc: '朋友点击支持后，双方都可获得赏金', // 分享描述
            link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: '{{asset('imgs/share-icon-1.png')}}', // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // 用户点击了分享后执行的回调函数
            }
        });
    });

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
                showSubscribeQrCode("{{$adminId}}", "{{$redPack->userId}}", "{{$redPack->id}}", "help");
                return false;
            }

            if ($(this).hasClass("is-helped")) {
                @if(!empty($unCompleteRedPackId))
                window.location.href = "/cash-red-pack-info?redPackId={{$unCompleteRedPackId}}";
                @else
                window.location.href = "/cash-red-pack";
                @endif
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
                    if(res.code === 0) {
                        //显示助力成功效果
                        $(".assistance-success-main .assistance-money").html(res.data.money || 0);
                        $(".assistance-success-main .total-money").html(res.data.total || 0);

                        var btnMeToo = $(".assistance-success-main .btn-me-too");
                        var unCompleteRedPackId = res.data.unCompleteRedPackId || 0;
                        if (unCompleteRedPackId > 0) {
                            btnMeToo.html("我的现金红包");
                        }
                        btnMeToo.on("click", function(){
                            if (unCompleteRedPackId > 0) {
                                window.location.href = "/cash-red-pack-info?redPackId=" + unCompleteRedPackId;
                            } else {
                                window.location.href = "/cash-red-pack";
                            }
                        });
                        $(".assistance-success-main").show();
                        return false;
                    } else {
                        alertPopup.show(res);
                    }
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
        <div class="box">
            <div class="red-pack-head"></div>
            <div class="msg">
                <div class="head-img">
                    <img src="{{$redPack->headImgUrl}}" />
                    <span>{{$redPack->nickname}}</span>
                </div>
                <div class="text">
                    @if(!empty($isHelped))
                        <p>您已经助力过了</p>
                        <p>谢谢您</p>
                    @else
                        <p>邀请您帮他拆红包</p>
                        <p>他还差 {{$redPack->total - $redPack->received}} 元</p>
                    @endif
                </div>
                <div class="{{$isHelped ? "is-helped" : ""}} btn-assistance">{{$isHelped ? (!empty($unCompleteRedPackId) ? "我的现金红包" : "我也要领红包") : "帮他助力"}}</div>
            </div>
        </div>
    </div>
</div>

<div class="assistance-success-main">
    <div class="bg">
        <div class="text">助力好友成功，助力<span class="assistance-money">85.99</span>元</div>
        <div class="tips">ta已获得<span class="total-money">698.53</span>元</div>
        <div class="btn-me-too">我也要领现金红包</div>
    </div>
</div>

@include('components/cash-red-pack-rule')
@include("components/subscribe")
@include("components/alertPopup")

</body>
</html>
