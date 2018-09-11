@include('header')
<link rel="stylesheet" type="text/css" href="{{asset("css/room-detail.css")}}"/>

<script>
    $(document).ready(function () {
        wx.config(<?php echo $wxapp->jssdk->buildConfig(['onMenuShareTimeline','onMenuShareAppMessage'], false) ?>);

        //用ready方法来接收验证成功
        wx.ready(function() {
            // alert(location.href.split('#')[0]);
            // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。

            var shareTitle = "{{$row->name}}";
            var shareDesc = "{{mb_substr(strip_tags($row->desc), 0 , 60)}}";
            var shareImg = "{{$row->cover}}";
            //分享到微信朋友圈
            wx.onMenuShareTimeline({
                title: shareTitle, // 分享标题
                link: window.location.href + "&adminId={{$adminId}}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: shareImg, // 分享图标
                success: function () {
                    // 用户点击了分享后执行的回调函数
                }
            });

            //分享给微信好友
            wx.onMenuShareAppMessage({
                title: shareTitle, // 分享标题
                desc: shareDesc, // 分享描述
                link: window.location.href + "&adminId={{$adminId}}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: shareImg, // 分享图标
                type: 'link', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                    // 用户点击了分享后执行的回调函数
                }
            });
        });

        //房源内容HTML
        $(".detail-box .content").html(htmlDecode("{{$row->desc}}"));

        //收藏
        $(".house-box .btn-mark").on("click", function(){
            var self = $(this);
            var isMark = $(this).hasClass("marked");
            $.ajax({
                type : 'post',
                url : "/room/mark",
                data : {
                    roomId : "{{$row->id}}",
                    markStatus : isMark ? 0 : 1
                },
                dataType : "json",
                headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
                success : function(res){
                    if (res.code === 0) {
                        isMark ? self.removeClass("marked") : self.addClass("marked");
                    }
                }
            });
        });

        //详情的预约看房
        $(".btn-box .btn-see").on("click", function(){
            window.location.href = "/room/bespeak?roomId={{$row->id}}";
        });

        //相似房源列表中的预约看房
        $(".house-list").on("click", ".btn-see-house", function(){
            window.location.href = "/room/bespeak?roomId=" + $(this).data("id");
            return false;
        });

        //致电案场经理
        $(".btn-box .btn-tel").on("click", function(){
            window.location.href = "/room/customServiceList";
        });

        //户型图点击
        $(".house-box .item.house-type").on("click", function(){
            window.location.href = "/room/houseTypeImgs?id={{$row->id}}";
        });

        //相似房源
        getRoomList({
            exceptedId : "{{$row->id}}",
            categoryId : "{{$row->roomCategoryId}}"
        });
    });
</script>

<div class="main">
    <div class="ads">
        @include('components/slideBox', ['list' => $row->imgs])
    </div>
    <div class="house-box">
        <div class="title-box">
            <div class="info">
                <div class="title">[{{$row->name}}]{{$row->area}}</div>
            </div>
            <div class="btn-mark {{$isMark ? "marked" : ""}}"></div>
        </div>
        <div class="mid-box">
            <div class="price-box">
                <div class="price item">
                    <div class="val">{{$row->avgPrice}} / m²</div>
                    <div>均价</div>
                </div>
                <div class="house-type item">
                    <div class="val">{{$row->houseType}}</div>
                    <div>户型图</div>
                </div>
            </div>
            <div class="desc-box">
                <div class="label">楼盘介绍</div>
                <div class="name"><span>名称:</span>{{$row->name}} <span class="category-name">[{{$row->categoryName}}]</span> [{{$row->decoration ? "精装" : "毛柸"}}]</div>
                <div class="area"><span>区域:</span>{{$row->area}}</div>
                <div class="area"><span>面积:</span>{{$row->acreage}}</div>
                <div class="area"><span>总价:</span>{{$row->totalPrice}} 万元起</div>
                <div class="area"><span>首付:</span>{{$row->firstPay}}</div>
                <div class="tag-list">
                    <span>标签:</span>
                    <div class="tag">
                        @foreach($row->tagNameList as $tagName)
                            <span class="tag-name">{{$tagName}}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="detail-box">
                <div class="label">房源描述</div>
                <div class="content"></div>
            </div>

            {{-- 相似房源 --}}
            <div class="similar-box">
                <div class="label">相似房源</div>
                <div class="house-list">
                    <div class="loading"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="btn-box">
        @if(!empty($row->adminTel))
        <a href="tel:{{$row->adminTel}}"><div class="btn btn-tel">致电案场经理</div></a>
        @else
        <div class="btn btn-tel">致电案场经理</div>
        @endif
        <div class="btn btn-see">预约看房</div>
    </div>
</div>