@include('header')
<link rel="stylesheet" type="text/css" href="{{asset("css/room-detail.css")}}"/>

<script>
    $(document).ready(function () {
        //房源内容HTML
        $(".detail-box .content").html(htmlDecode("{{$row->desc}}"));
    });
</script>

<div class="main">
    <div class="ads">
        @include('components/slideBox', ['imgList' => $row->imgs])
    </div>
    <div class="house-box">
        <div class="title-box">
            <div class="info">
                <div class="title">[{{$row->name}}]{{$row->area}}</div>
                <div class="create-time">发布:{{date("Y-m-d H:i:s", $row->createTime)}}</div>
            </div>
            <div class="btn-mark">收藏</div>
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
                <div class="name"><span>楼盘名称:</span>{{$row->name}}</div>
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