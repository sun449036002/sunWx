@include('header')

<style>
    .mui-content a {
        color: black;
    }
    .mui-content .item {
        padding: .3rem;
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #ccc;
    }
</style>

<link rel="stylesheet" type="text/css" href="{{asset("css/mui.min.css")}}"/>
<script type="text/javascript" src="{{asset("js/mui.min.js")}}"></script>

<header class="mui-bar mui-bar-nav bgeee">
    <a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left c433"></a>
    <h1 id="title" class="mui-title">客服列表</h1>
</header>

<div class="mui-content">
   @if(!empty($customServiceList))
       @foreach($customServiceList as $item)
       <a href="tel:{{$item->tel}}">
        <div class="item" data-tel="{{$item->tel}}">
            <div class="input-label">{{$item->name}}</div>
            <div class="input-box">电话:{{$item->tel}}</div>
        </div>
       </a>
       @endforeach
       @endif
</div>