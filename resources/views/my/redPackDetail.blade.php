@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/my-red-pack-detail.css")}}"/>

<script>
    $(document).ready(function () {
        wx.config(<?php echo $wxapp->jssdk->buildConfig(['onMenuShareTimeline','onMenuShareAppMessage'], false) ?>);

        //设置一个缓存作为赠送凭证
        var initGrantRedPack = function (cb) {
            $.ajax({
                type : 'post',
                url : "/index/initGrantRedPack",
                data : {
                    id : "{{$row->id}}"
                },
                dataType : "json",
                headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
                success : function(res){
                    cb(res);
                }
            });
        };

        //使用
        $(".red-pack-detail.main .btn-use").on("click", function () {
            window.location.href = "/my/backMoneyPage";
        });

        //赠送
        $(".red-pack-detail.main .btn-grant").on("click", function () {
            initGrantRedPack(function(res){
                if (res.code > 0) {
                    mui.alert(res.msg);
                    return false
                }
                //用ready方法来接收验证成功
                wx.ready(function() {
                    // alert(location.href.split('#')[0]);
                    // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。

                    var link = "{{env('APP_URL')}}/index/grantRedPack?redPackId={{$row->id}}&ticket=" + res.data.ticket;
                    //分享到微信朋友圈
                    wx.onMenuShareTimeline({
                        title: '我赠送给你了{{$row->total}}元，快来领取吧~', // 分享标题
                        link: link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                        imgUrl: '{{asset('imgs/share-icon-1.png')}}', // 分享图标
                        success: function () {
                            // 用户点击了分享后执行的回调函数
                            $(".share-layer").hide();
                        }
                    });

                    //分享给微信好友
                    wx.onMenuShareAppMessage({
                        title: '我赠送给你了{{$row->total}}元，快来领取吧~', // 分享标题
                        desc: '先到先得哦，抓紧领取哦', // 分享描述
                        link: link, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                        imgUrl: '{{asset('imgs/share-icon-1.png')}}', // 分享图标
                        type: 'link', // 分享类型,music、video或link，不填默认为link
                        dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                        success: function (res) {
                            alert(res);
                            // 用户点击了分享后执行的回调函数
                            $(".share-layer").hide();
                        }
                    });
                });

                $(".share-layer").show();
            });
        });

    });
</script>

<div class="red-pack-detail main">
    <div class="mid">
        <div class="box">
            <div class="text">价值{{$row->total}}元的现金红包</div>
            <div class="btn btn-use">立即使用</div>
            <div class="btn btn-grant">赠送给好友</div>
        </div>
    </div>
    <div class="tips">
        <div class="title">红包规则</div>
        <div class="content">
            <p>1.可通过分享此页面的方式赠送此红包给好友</p>
            <p>2.当好友领取后，将从您账户转移此红包给好友，您将不再拥有此红包</p>
            <p>3.一个红包分享给多个好友后，只有最先领取的好友才能获得，其他好友不会再获得此红包</p>
            <p>4.赠送的红包，不会延长使用过期时间</p>
        </div>
    </div>
</div>

@include('components/shareLayer', ['msg' => '通过分享赠送给好友~'])