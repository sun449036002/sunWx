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
    <h1 id="title" class="mui-title">户型图片</h1>
</header>

<div class="mui-content">
   @if(!empty($row->houseTypeImgs))
       @foreach($row->houseTypeImgs as $img)
        <div class="item">
            <img src="{{$img}}" width="100%" height="100%"/>
        </div>
       @endforeach
       @else
       <div class="item">暂无户型图</div>
       @endif

</div>