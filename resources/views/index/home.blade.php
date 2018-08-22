@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/home.css")}}"/>

<script>
    $(document).ready(function () {
        wx.config(<?php echo $wxapp->jssdk->buildConfig(['onMenuShareTimeline','onMenuShareAppMessage'], false) ?>);

        //用ready方法来接收验证成功
        wx.ready(function() {
            // alert(location.href.split('#')[0]);
            // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。

            //分享到微信朋友圈
            wx.onMenuShareTimeline({
                title: '这里有很多好的推荐房源', // 分享标题
                link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: '{{asset('imgs/logo.png')}}', // 分享图标
                success: function () {
                    // 用户点击了分享后执行的回调函数
                }
            });

            //分享给微信好友
            wx.onMenuShareAppMessage({
                title: '这里有很多很好的推荐房源', // 分享标题
                desc: '房源多多，另有500元大礼包待您领取', // 分享描述
                link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: '{{asset('imgs/logo.png')}}', // 分享图标
                type: 'link', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                    // 用户点击了分享后执行的回调函数
                }
            });
        });


        //先加载一页
        var ajaxing = false;
        var isEnd = false;
        var paramsData = {
            page : 1,
            type : 1,
            recommend : 1,
            keyword:""
        };

        getRoomList(paramsData);

        //重置高度
        var houseListHeight = $(window).height() - $(".ads").height() - $(".recommend-house-box .tips-bar").height() - $(".bottom-menu-box").height();
        $(".house-list").height(houseListHeight);

        //搜索功能
        $(".search-wrapper .search-icon").on("click", function(){
            window.location.href = "/room/list?keyword=" + $(".search-wrapper input[name='keyword']").val();
        });

        //滚动加载方法1
        $('.house-list').scroll(function() {
//            console.log(($(this)[0].scrollTop + $(this).height() + 60) >= $(this)[0].scrollHeight)
            //当时滚动条离底部60px时开始加载下一页的内容
            if (($(this)[0].scrollTop + $(this).height() + 60) >= $(this)[0].scrollHeight) {
                if (!ajaxing && !isEnd) {
                    ajaxing = true;
                    paramsData.page++;
                    $(".loading").css("display", "flex");
                    setTimeout(function () {
                        getRoomList(paramsData, function(res){
//                            console.log(res);
//                            console.log("callback");
                            ajaxing = false;
                            isEnd = res.isEnd;
                            $(".loading").hide();
                        });
                    }, 200);
                }
            }
        });
    });
</script>

<div class="main">
    <div class="top">
        @include('components/searchBox')
    </div>

    <div class="ads">
        @include('components/slideBox', ['list' => $adsList ?? []])
    </div>
    <div class="recommend-house-box">
        <div class="tips-bar">
            <img src="{{asset('imgs/bar1.png')}}"/>
            <div class="text">推荐房源</div>
        </div>
        <div class="house-list">
            <div class="loading"><img src="{{asset('imgs/loading.svg')}}" /></div>
        </div>
    </div>
</div>

@include('components/bottomMenu')