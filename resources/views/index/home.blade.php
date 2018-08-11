@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/home.css")}}"/>

<div class="main">
    <form action="#">
    <div class="top">
            @csrf
            <div>关键字</div>
            <input type="text" name="keyword" value="" />
            <button type="submit">搜索</button>
    </div>
    </form>
    <div class="ads">
        @include('components/slideBox', $adList ?? [])
    </div>
    <div class="recommend-house-box">
        <div class="tips-bar">
            <img src="{{asset('imgs/bar1.png')}}"/>
            <div class="text">推荐房源区</div>
        </div>
        <div class="house-list">
            @foreach($roomList as $item)
            <div class="item">
                <div class="cover" style="background-image: url('{{$item->cover}}')"></div>
                <div class="info">
                    <div class="name">{{$item->name}}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@include('components/bottomMenu')