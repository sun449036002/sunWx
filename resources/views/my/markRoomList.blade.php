@include('header')
<link rel="stylesheet" type="text/css" href="{{asset("css/mark-rooms.css")}}"/>

<div class="main">
    <div class="recommend-house-box">
        <div class="house-list">
            @foreach($list as $item)
            <a href="/room/detail?id={{$item->id}}">
                <div class="item">
                    <div class="cover" style="background-image: url('{{$item->cover}}')"></div>
                    <div class="info">
                        <div class="name"> {{$item->name}} </div>
                        <div class="area"> {{$item->area}} </div>
                        <div class="categoryName"> {{$item->categoryName}} </div>
                        <div class="avg-price">均价： {{$item->avgPrice}} 元/m²</div>
                        <div class="avg-price">总价：{{$item->totalPrice}} 万元</div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

@include('components/bottomMenu')