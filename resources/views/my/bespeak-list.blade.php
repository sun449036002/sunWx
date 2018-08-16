@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/bespeak-list.css")}}"/>

<script>
    $(document).ready(function () {
        $(".bespeak .bespeak-item").on("click", function(){
            window.location.href = "/my/bespeakDetail?id=" + $(this).data("id");
        });
    });
</script>

<div class="bespeak main">
    @foreach($list as $item)
    <div class="bespeak-item" data-id="{{$item->id}}">
        <img src="{{$item->roomSourceCover ?? ""}}" />
        <div class="info">
            <div class="title">{{$item->roomSourceName ?? "未知"}}[{{$item->area ?? "未知"}}]</div>
            <div class="num">{{$item->num}}  {{$item->name}}</div>
            <div class="time">预约时间：{{$item->time}}</div>
        </div>
    </div>
    @endforeach
</div>

@include('components/bottomMenu')