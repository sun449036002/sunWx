@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/my-balance.css")}}"/>

<div class="main">
    <div class="top">
        <div class="info">
            <div class="balance">￥{{number_format($balance, 2)}} <span>元</span></div>
            <div class="btn-withdraw">提现</div>
        </div>
    </div>
    <div class="mid">
        <div class="list">
            @foreach($balanceLogList as $item)
            <div class="item">
                <div class="left">
                    <div class="type">{{$balanceTypes[$item->type] ?? "未知"}}</div>
                    <div class="date">{{date("Y-m-d H:i:s", $item->createTime)}}</div>
                </div>
                <div class="right">{{number_format($item->money, 2)}} 元</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@include('components/bottomMenu')