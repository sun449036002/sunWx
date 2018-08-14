@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/room-list.css")}}"/>

<script>
    $(document).ready(function(){
        //先加载一页
        var ajaxing = false;
        var isEnd = false;
        var paramsData = {
            page : 1,
            type : 1,
            keyword:"{{$keyword}}"
        };

        //获取第一页
        getRoomList(paramsData);

        //重置高度
        var houseListHeight = $(window).height() - $(".top").height() - $(".bottom-menu-box").height() - $(".cate-list").height() - $(".filter-list").height();
        $(".house-list").height(houseListHeight);
        $(".filter-content-list").height(houseListHeight);

        //切换房源类型
        $(".main .house-box .cate-list .cate-item").on("click", function () {
            $(this).addClass("selected").siblings().removeClass("selected");
            var index = $(this).index();
            if (index > 0) {
                $(".main .house-box .filter-list .filter-item.only-new").hide();
                $(".main .house-box .filter-list .filter-item.only-old").show();
            } else {
                paramsData.houseTypeId = paramsData.minPrice = paramsData.maxPrice = paramsData.minAcreage = paramsData.maxAcreage = "";
                $(".main .house-box .filter-list .filter-item.only-new").show();
                $(".main .house-box .filter-list .filter-item.only-old").hide();
            }


            paramsData.type = $(this).data("type");
            getRoomList(paramsData)
        });

        //房源过滤
        $(".main .house-box .filter-list .filter-item").on("click", function(){
            $(this).addClass("selected").siblings().removeClass("selected");
            var index = $(this).index();
            $(".house-box .filter-content-list").show();
            var cBox = $(".house-box .filter-content-list .c-box");
            cBox.hide();
            cBox.eq(index).show();
        });

        //确认选择
        $(".filter-content-list").on("click", ".c-item", function(){
            var _data = $(this).data();
            Object.assign(paramsData, _data);
            var text = $(this).html();
            if(text === '不限') {
                var index = $(this).parent().prevAll().length;
                text = $(".filter-list .filter-item").eq(index).data("origin-text");
            }
            $(".main .house-box .filter-list .filter-item.selected").html(text);
            $(".main .house-box .filter-list .filter-item").removeClass("selected");
            $(".main .house-box .filter-content-list").hide();

            //再请求新数据
            isEnd = false;
            paramsData.page = 1;
            getRoomList(paramsData);
        });

        //1. 加载地区列表
        $.getJSON("/area/list", function(list){
            $.each(list, function(k, v){
                var html = '<div class="c-item area-item" data-area-id="' + v.id + '">' + v.name + '</div>';
                $(".filter-content-list .c-for-area").append(html);
            });
        });

        //2. 加载户型列表
        $.getJSON("/houseType/list", function(list){
            $.each(list, function(k, v){
                var html = '<div class="c-item house-type-item" data-house-type-id="' + v.id + '">' + v.name + '</div>';
                $(".filter-content-list .c-for-house-type").append(html);
            });
        });

        //3. 加载房源类别
        $.getJSON("/category/list", function(list){
            $.each(list, function(k, v){
                var html = '<div class="c-item category-item" data-category-id="' + v.id + '">' + v.name + '</div>';
                $(".filter-content-list .c-for-category").append(html);
            });
        });

        //搜索功能
        $(".search-wrapper .search-icon").on("click", function(){
            //再请求新数据
            isEnd = false;
            paramsData.page = 1;
            paramsData.keyword = $(".search-wrapper input[name='keyword']").val();
            getRoomList(paramsData);
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
        @include('components/searchBox', ['keyword' => $keyword])
    </div>

    <div class="house-box">
        <div class="cate-list">
            <div class="cate-item selected" data-type="1">新楼盘</div>
            <div class="cate-item" data-type="2">二手房</div>
            <div class="cate-item" data-type="3">出租房</div>
        </div>
        <div class="filter-list">
            <div class="filter-item" data-origin-text="区域">区域</div>
            <div class="filter-item only-new" data-origin-text="类别">类别</div>
            <div class="filter-item only-old" data-origin-text="单价">单价</div>
            <div class="filter-item only-old" data-origin-text="户型">户型</div>
            <div class="filter-item only-old" data-origin-text="面积">面积</div>
        </div>
        <div class="filter-content-list">
            <div class="c-box c-for-area">
                <div class="c-item area-item" data-area-id="">不限</div>
            </div>
            <div class="c-box c-for-category">
                <div class="c-item category-item" data-category-id="">不限</div>
            </div>
            <div class="c-box c-for-avg-price">
                <div class="c-item area-item" data-min-price="" data-max-price="">不限</div>
                <div class="c-item area-item" data-min-price="" data-max-price="8000">0.8 万以下</div>
                <div class="c-item area-item" data-min-price="8000" data-max-price="15000">0.8 ~ 1.5 万</div>
                <div class="c-item area-item" data-min-price="15000" data-max-price="20000">1.5 ~ 2 万</div>
                <div class="c-item area-item" data-min-price="20000" data-max-price="25000">2 ~ 2.5 万</div>
                <div class="c-item area-item" data-min-price="25000" data-max-price="35000">2.5 ~ 3.5 万</div>
                <div class="c-item area-item" data-min-price="35000" data-max-price="50000">3.5 ~ 5 万</div>
                <div class="c-item area-item" data-min-price="50000" data-max-price="">5 万以上</div>
            </div>
            <div class="c-box c-for-house-type">
                <div class="c-item house-type-item" data-house-type-id="">不限</div>
            </div>
            <div class="c-box c-for-acreage">
                <div class="c-item area-item" data-min-acreage="" data-max-acreage="">不限</div>
                <div class="c-item area-item" data-min-acreage="" data-max-acreage="50">50㎡ 以下</div>
                <div class="c-item area-item" data-min-acreage="50" data-max-acreage="70">50 ~ 70㎡ </div>
                <div class="c-item area-item" data-min-acreage="70" data-max-acreage="90">70 ~ 90㎡</div>
                <div class="c-item area-item" data-min-acreage="90" data-max-acreage="110">90 ~ 110㎡</div>
                <div class="c-item area-item" data-min-acreage="110" data-max-acreage="130">110 ~ 130㎡</div>
                <div class="c-item area-item" data-min-acreage="130" data-max-acreage="150">130 ~ 150㎡</div>
                <div class="c-item area-item" data-min-acreage="150" data-max-acreage="200">150 ~ 200㎡</div>
                <div class="c-item area-item" data-min-acreage="200" data-max-acreage="300">200 ~ 300㎡</div>
                <div class="c-item area-item" data-min-acreage="300" data-max-acreage="">300㎡ 以上</div>
            </div>
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