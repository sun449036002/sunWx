@include("header")
<!-- Styles -->
<link href="css/cash-red-pack.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" charset="utf-8">

    //滚动插件
    (function($) {
        $.fn.extend({
            Scroll: function(opt, callback) {
                //参数初始化
                if (!opt) var opt = {};
                var _this = this.eq(0).find("ul:first");
                var lineH = _this.find("li:first").outerHeight(true), //获取行高
                    line = opt.line ? parseInt(opt.line, 10) : parseInt(this.height() / lineH, 10), //每次滚动的行数，默认为一屏，即父容器高度
                    speed = opt.speed ? parseInt(opt.speed, 10) : 500, //卷动速度，数值越大，速度越慢（毫秒）
                    timer = opt.timer ? parseInt(opt.timer, 10) : 3000; //滚动的时间间隔（毫秒）
                if (line == 0) line = 1;
                var upHeight = 0 - line * lineH;
                //滚动函数
                scrollUp = function() {
                    _this.animate({
                        marginTop: "-0.9rem"
//                        marginTop: upHeight
                    }, speed, function() {
                        for (i = 1; i <= line; i++) {
                            _this.find("li:first").appendTo(_this);
                        }
                        _this.css({
                            marginTop: 0
                        });
                    });
                }
                //鼠标事件绑定
                _this.hover(function() {
                    clearInterval(timerID);
                }, function() {
                    timerID = setInterval("scrollUp()", timer);
                }).mouseout();
            }
        });
    })(jQuery);

    $(document).ready(function(){
        //点击领取按钮
        $(".btn-receive").on("click", function () {
            location.href = "/cash-red-pack-info?from=cash-receive"
        });
        //自动滚动
        $("#withdraw-list").Scroll({
            line: 1,
            speed: 500,
            timer: 3000
        });
        //显示规则
        $(".cash-red-pack-main .rule").on("click", function(){
            $(".rule-container").show();
        });
    });
</script>
</head>
<body>
<div class="cash-red-pack-main">
    <div class="tips">您有一个现金红包未领取</div>
    <div class="rule">活动规则</div>
    <div class="mid">
        <div class="red-pack-info">
            <div class="btn-receive">领取</div>
            <div class="withdraw-list-box" id="withdraw-list">
                <ul class="withdraw-list">
                    @if($rows)
                        @foreach($rows as $row)
                        <li class="item">
                            <div class="head-img-url" style="background-image: url('{{$row->headImgUrl}}')"></div>
                            <div class="withdraw-info"><span class="nickname">{{$row->nickname}}</span>提现{{$row->money}}元</div>
                        </li>
                        @endforeach
                        @endif

                </ul>
            </div>
        </div>
        <div class="msg">最高可领100元现金~</div>
    </div>
</div>

@include('index/cash-red-pack-rule')

</body>
</html>
