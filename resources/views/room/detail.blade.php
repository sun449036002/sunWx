@include('header')
<link rel="stylesheet" type="text/css" href="{{asset("css/room-detail.css")}}"/>
<link rel="stylesheet" type="text/css" href="{{asset("css/idangerous.swiper.css")}}"/>
<script src="{{asset('js/idangerous.swiper.min.js')}}" type="text/javascript" charset="utf-8"></script>

<script>
    $(document).ready(function () {
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

        //预约看房
        $(".btn-box .btn-see").on("click", function(){
            window.location.href = "/room/bespeak?roomId={{$row->id}}";
        });

        //致电案场经理
        $(".btn-box .btn-tel").on("click", function(){
            window.location.href = "/room/customServiceList";
        });

        //户型图点击
        $(".house-box .item.house-type").on("click", function(){
            window.location.href = "/room/houseTypeImgs?id={{$row->id}}";
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
                    <div>价格</div>
                </div>
                <div class="house-type item">
                    <div class="val">{{$row->houseType}}</div>
                    <div>户型图</div>
                </div>
            </div>
            <div class="desc-box">
                <div class="label">楼盘介绍</div>
                <div class="name"><span>楼盘名称:</span>{{$row->name}} [{{$row->categoryName}}]</div>
                <div class="area"><span>楼盘面积:</span>{{$row->acreage}} m²</div>
                <div class="area"><span>楼盘总价:</span>{{$row->totalPrice}} 万元</div>
                <div class="area"><span>所在区域:</span>{{$row->area}}</div>
            </div>
            <div class="detail-box">
                <div class="label">房源描述</div>
                <div class="content"></div>
            </div>
        </div>
    </div>
    <div class="btn-box">
        <div class="btn btn-tel">致电案场经理</div>
        <div class="btn btn-see">预约看房</div>
    </div>
</div>