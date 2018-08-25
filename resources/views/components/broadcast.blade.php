<link href="{{asset('css/broadcast.css')}}" rel="stylesheet" type="text/css" />
<script>
    $(document).ready(function(){
        //Ajax获取数据
        $.getJSON("/broadcastList", function(res){
            var list = res.data.list || [];
            $.each(list, function(k,item){
                if (item.nickname.length > 2) {
                    item.nickname = item.nickname.substr(0, 2) + "...";
                }
//                console.log(item.headImgUrl);
                var html = '<div class="item item-' + k + '">' +
                        '<div class="head-img-url" style="background-image: url(\'' + item.headImgUrl + '\')"></div>' +
                        '<div class="nickname">' + item.nickname + '</div>获得' +
                        '<div class="money">' + item.money + '元</div>' +
                    '</div>';
                $(".broadcast-box .list").append(html);
            });

            $(".broadcast-box .list").height(list.length * 0.7 + "rem");

            //开启自动滚动
            var len = list.length;
            var marginTop = 0;
            var index = 0;
            setInterval(function(){
                marginTop -= 0.7;
                $(".broadcast-box .list").animate({
                    marginTop:marginTop + "rem"
                }, {
                    easing: "easeOutBounce",
                    duration: 500,
                    complete:function(){
                        var firstItem = $(".broadcast-box .list .item").eq(index);
                        $(".broadcast-box .list").append(firstItem.clone());
//                        firstItem.remove();
                    }
                });
                index++;
                if (index >= len) {
                    index= 0;
                }
            },3000);
        });
    });
</script>

<div class="broadcast-box">
    <div class="list">
    </div>
</div>