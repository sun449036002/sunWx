<style type="text/css">
    div.slideBox {
        position:relative;
        width: 100vw;
        height: 5rem;
        overflow:hidden;
    }
    div.slideBox ul.items {
        position:absolute;
        float:left;
        background:none;
        list-style:none;
        padding:0;
        margin:0;
    }
    div.slideBox ul.items li {
        float:left;
        background:none;
        list-style:none;
        padding:0;
        margin:0;
    }
    div.slideBox ul.items li a {
        float:left;
        line-height:normal !important;
        padding:0 !important;
        border:none/*For IE.ADD.JENA.201206300844*/;
    }
    div.slideBox ul.items li a img {
        width:100vw;
        height: 5rem;
        margin:0 !important;
        padding:0 !important;
        display:block;
        border:none/*For IE.ADD.JENA.201206300844*/;
    }
    div.slideBox div.tips {
        position:absolute;
        bottom:0;
        width:100vw;
        height:1rem;
        background-color:#000;
        overflow:hidden;
    }
    div.slideBox div.tips div.title {
        position:absolute;
        left:0;
        top:0;
        height:100%;
    }
    div.slideBox div.tips div.title a {
        color: #FFF;
        font-size: .36rem;
        line-height: 1rem;
        margin-left: .2rem;
        text-decoration: none;
    }
    div.slideBox div.tips div.title a:hover {
        text-decoration:underline !important;
    }
    div.slideBox div.tips div.nums {
        position:absolute;
        right:0;
        top:0;
        height:100%;
    }
    div.slideBox div.tips div.nums a {
        display: inline-block;
        width: .4rem;
        height: .4rem;
        background-color: #FFF;
        text-indent: -99999px;
        margin: .3rem .2rem 0 0;
    }
    div.slideBox div.tips div.nums a.active {
        background-color:#093;
    }

</style>

<script src="{{asset("js/jquery.slideBox.js")}}"></script>
<script>
$(document).ready(function () {
    $('#demo').slideBox({

        duration : 0.3,//滚动持续时间，单位：秒

        easing : 'linear',//swing,linear//滚动特效

        delay : 5,//滚动延迟时间，单位：秒

        hideClickBar : false,//不自动隐藏点选按键

        clickBarRadius : 10

    });
});
</script>

<div id="demo" class="slideBox">
    <ul class="items">
        @if($list)
            @foreach($list as $item)
                @isset($item->img)
                <li><a href="{{$item->url ?? "#"}}" title="{{$item->title ?? ""}}"><img src="{{$item->img}}"></a></li>
                @else
                <li><a href="#" title=""><img src="{{$item}}"></a></li>
                @endisset
            @endforeach
        @endif
    </ul>
</div>