@include('header')

<style>
    .mui-content a {
        color: black;
    }
    .mui-content .content {
        padding: .3rem;
    }
</style>

<link rel="stylesheet" type="text/css" href="{{asset("css/mui.min.css")}}"/>
<script type="text/javascript" src="{{asset("js/mui.min.js")}}"></script>

<script>
    $(document).ready(function () {
        $(".mui-content .content").html(htmlDecode("{{$aboutUs}}"))
    });
</script>

<header class="mui-bar mui-bar-nav bgeee">
    <a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left c433"></a>
    <h1 id="title" class="mui-title">关于我们</h1>
</header>

<div class="mui-content">
    <div class="content"></div>
</div>