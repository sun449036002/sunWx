@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/my-red-pack-list.css")}}"/>

<script type="text/javascript">
    $(document).ready(function () {
        $(".red-pack-main .bar-item").on("click", function(){
            window.location.href = "{{route('/my/redPackList')}}?type=" + $(this).data("type");
        });

        //红包点击跳转
        $(".red-pack-main .red-pack-box .item").on("click", function(){
            var status = parseInt($(this).data("status"));
            switch (status) {
                case 0://未完成
                    window.location.href = "/cash-red-pack-info?redPackId=" + $(this).data("redPackId");
                    break;
                case 1://未使用
//                    window.location.href = "/my/redPackDetail?id=" + $(this).data("redPackId");
                    break;
                case 2://使用中
                case 3://已使用
                case 4://作废
                default:
                    return false;
                    break;
            }
        })

    });
</script>
<div class="red-pack-main">
    <div class="bar">
        <div class="bar-item all {{$type == 'all' ? "selected" : ""}}" data-type="all">全部</div>
        <div class="bar-item un-finish  {{$type == 'unFinish' ? "selected" : ""}}" data-type="unFinish">未完成</div>
        <div class="bar-item un-use  {{$type == 'unUse' ? "selected" : ""}}" data-type="unUse">未使用</div>
        <div class="bar-item expired  {{$type == 'expired' ? "selected" : ""}}" data-type="expired">已过期</div>
    </div>
    <div class="red-pack-box">
        <div class="list">
            @foreach($list as $item)
            <div class="item" data-red-pack-id="{{$item->id}}" data-status="{{$item->status}}">
                <div class="bg" style='background-image: url("{{asset('imgs/my-red-pack-bg.png')}}");'></div>
                <div class="data">
                    <div class="money">{{$item->type == 'unFinish' ? $item->received . "/" . $item->total : $item->total}}元</div>
                    <div class="from">来源:{{$item->fromUserId == 0 ? "活动" : "好友赠送"}}</div>
                    <div class="from">状态:{{$redPackStatusConfig[$item->status]['status']}} {{in_array($item->type, ['expired', 'useExpired']) ? "【已过期】" : ""}}</div>
                    <div class="expiredTime">过期时间: {{$item->type == 'unFinish' ? $item->expiredTimeStr : $item->useExpiredTimeStr }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@include('components/bottomMenu')