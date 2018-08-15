@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/home.css")}}"/>

<script>
    $(document).ready(function () {
        //先加载一页
        var ajaxing = false;
        var isEnd = false;
        var paramsData = {
            page : 1,
            type : 1,
            recommend : 1,
            keyword:""
        };

        getRoomList(paramsData);

        //重置高度
        var houseListHeight = $(window).height() - $(".ads").height() - $(".recommend-house-box .tips-bar").height() - $(".bottom-menu-box").height();
        $(".house-list").height(houseListHeight);

        //搜索功能
        $(".search-wrapper .search-icon").on("click", function(){
            window.location.href = "/room/list?keyword=" + $(".search-wrapper input[name='keyword']").val();
        });

        //滚动加载方法1
        $('.house-list').scroll(function() {
//            console.log(($(this)[0].scrollTop + $(this).height() + 60) >= $(this)[0].scrollHeight)
            //当时滚动条离底部60px时开始加载下一页的内容
            if (($(this)[0].scrollTop + $(this).height() + 60) >= $(this)[0].scrollHeight) {
                if (!ajaxing && !isEnd) {
                    ajaxing = true;
                    paramsData.page++;
                    $(".loading").css("display", "flex");
                    setTimeout(function () {
                        getRoomList(paramsData, function(res){
//                            console.log(res);
//                            console.log("callback");
                            ajaxing = false;
                            isEnd = res.isEnd;
                            $(".loading").hide();
                        });
                    }, 200);
                }
            }
        });
    });
</script>

<div class="main">
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
            <div class="loading"><img src="{{asset('imgs/loading.svg')}}" /></div>
        </div>
    </div>
</div>

@include('components/bottomMenu')