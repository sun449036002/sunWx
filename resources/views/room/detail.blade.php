@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/home.css")}}"/>

<div class="main">
    <div class="ads">
        @include('components/slideBox', ['imgList' => $row->imgs])
    </div>
    <div class="house-box">

    </div>
</div>

@include('components/bottomMenu')