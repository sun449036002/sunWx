@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/home.css")}}"/>

<div class="main">
    {{--<form action="#">--}}
    {{--<div class="top">--}}
            {{--@csrf--}}
            {{--<div>关键字</div>--}}
            {{--<input type="text" name="keyword" value="" />--}}
            {{--<button type="submit">搜索</button>--}}
    {{--</div>--}}
    {{--</form>--}}

    <div class="top">
        @include('components/searchBox')
    </div>

    <div class="ads">
        @include('components/slideBox', ['list' => $adsList ?? []])
    </div>
    <div class="recommend-house-box">
        <div class="tips-bar">
            <img src="{{asset('imgs/bar1.png')}}"/>
            <div class="text">推荐房源区</div>
        </div>
        <div class="house-list">
            @foreach($roomList as $item)
                <a href="/room/detail?id={{$item->id}}">
                    <div class="item">
                        <div class="cover" style="background-image: url('{{asset("imgs/fangzi.jpeg")}}')"></div>
                        <div class="info">
                            <div class="name">{{$item->name}}</div>
                            <div class="area">{{$item->area}}</div>
                            <div class="categoryName">{{$item->categoryName ?? "未知"}}</div>
                            <div class="avg-price">均价：{{$item->avgPrice ?? 0}}元/m²</div>
                            <div class="avg-price">总价：{{$item->totalPrice ?? 0}}万元</div>
                            <div class="btn-see-house">预约看房</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

@include('components/bottomMenu')