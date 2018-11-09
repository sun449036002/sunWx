<link rel="stylesheet" type="text/css" href="{{asset("css/idangerous.swiper.css")}}"/>
<script src="{{asset('js/idangerous.swiper.min.js')}}" type="text/javascript" charset="utf-8"></script>

<style type="text/css">
    .swiper-container {
        height: 300px;
        width: 100vw;
    }
    .content-slide {
        padding: 20px;
        color: #fff;
    }
    .title {
        font-size: 25px;
        margin-bottom: 10px;
    }
    .pagination {
        position: absolute;
        left: 0;
        text-align: center;
        bottom:5px;
        width: 100%;
    }
    .swiper-pagination-switch {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 10px;
        background: #999;
        box-shadow: 0px 1px 2px #555 inset;
        margin: 0 3px;
        cursor: pointer;
    }
    .swiper-active-switch {
        background: #fff;
    }

    .swiper-slide {
        position: relative;
    }
    .swiper-slide .title {
        position: absolute;
        color: white;
        background-color: rgba(0,0,0,0.6);
        width: 100vw;
        bottom: 0;
        padding: .1rem .3rem;
        font-size: 0.4rem;
        height: .6rem;
        line-height: .6rem;
    }
</style>

<script>
$(document).ready(function () {
    var mySwiper = new Swiper('.swiper-container',{
        pagination: '.pagination',
        loop:false,
        grabCursor: true,
        paginationClickable: true
    });

    $(".swiper-container .swiper-slide").on("click", function(){
        var url = $(this).data("url");
        if (url) {
            window.location.href = url;
        }
    })
});
</script>

<div class="swiper-container">
    <div class="swiper-wrapper">
        {{-- 视频 --}}
        @foreach($videos as $video)
        <div class="swiper-slide video">
            <video src="{{$video}}" width="100%" height="100%" controls autoplay></video>
        </div>
        @endforeach

        {{-- 图片 --}}
        @foreach($list as $img)
        <div class="swiper-slide" data-url="{{$img->url ?? ""}}">
            <img width="100%" height="100%" src="{{$img->img ?? $img}}">
            @if(!empty($img->title) && false)
            <div class="title">{{$img->title}}</div>
            @endif
        </div>
        @endforeach
    </div>
    <div class="pagination"></div>
</div>