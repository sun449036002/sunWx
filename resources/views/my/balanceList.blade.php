@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/my-balance.css")}}"/>

<div class="main">
    <div class="top">
        <div class="info">
            <div class="balance">￥{{number_format($balance, 2)}} <span>元</span></div>
        </div>
    </div>
    <div class="mid"></div>
</div>

@include('components/bottomMenu')