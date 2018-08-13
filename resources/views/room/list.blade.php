@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/room-list.css")}}"/>

<script>
    $(document).ready(function(){
        //重置高度
        var houseListHeight = $(window).height() - $(".top").height() - $(".bottom-menu-box").height() - $(".cate-list").height() - $(".filter-list").height();
        $(".house-list").height(houseListHeight);

        //切换房源类型
        $(".main .house-box .cate-list .cate-item").on("click", function () {
            $(this).addClass("selected").siblings().removeClass("selected");
        });

        //房源过滤
        $(".main .house-box .filter-list .filter-item").on("click", function(){
            $(this).addClass("selected");
        });

        //先加载一页
        var ajaxing = false;
        var page = 1;
        var isEnd = false;
        getRoomList();

        //滚动加载方法1
        $('.house-list').scroll(function() {
//            console.log(($(this)[0].scrollTop + $(this).height() + 60) >= $(this)[0].scrollHeight)
            //当时滚动条离底部60px时开始加载下一页的内容
            if (($(this)[0].scrollTop + $(this).height() + 60) >= $(this)[0].scrollHeight) {
                if (!ajaxing && !isEnd) {
                    ajaxing = true;
                    page++;
                    $(".loading").css("display", "flex");
                    setTimeout(function () {
                        getRoomList({
                            'page' : page
                        }, function(res){
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

    <div class="house-box">
        <div class="cate-list">
            <div class="cate-item selected" >新楼盘</div>
            <div class="cate-item">二手房</div>
            <div class="cate-item">出租房</div>
        </div>
        <div class="filter-list">
            <div class="filter-item">区域</div>
            <div class="filter-item">单价</div>
            <div class="filter-item">户型</div>
            <div class="filter-item">面积</div>
        </div>
        <div class="house-list">
            <div class="loading">
                <div id="preloader_1">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>


    </div>
</div>

@include('components/bottomMenu')