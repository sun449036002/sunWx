@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/my-index.css")}}"/>

<script>
    $(document).ready(function () {
        @if(empty($user['id']))
        showSubscribeQrCode("{{$adminId}}", 0, 0, "others");
        @endif
    });
</script>

<div class="main">
    <div class="top">
        <div class="head-img" style="background-image: url('{{$user["avatar_url"] ?? ''}}')"></div>
        <div class="info">
            <div class="nickname">{{$user['username'] ?? ''}}</div>
            <div class="balance">余额：{{number_format($balance ?? 0)}} 元</div>
        </div>
    </div>
    <div class="mid">

        <div class="red-pack">
            <div class="title">我的红包</div>
            <div class="red-pack-items">
                <a href="{{route('/my/redPackList')}}">
                <div class="red-pack-item">
                    <span class="icon all"></span>
                    <span class="text">全部</span>
                </div>
                </a>
                <a href="{{route('/my/redPackList')}}?type=unFinish">
                    <div class="red-pack-item">
                        <span class="icon un-complete"></span>
                        <span class="text">未完成</span>
                    </div>
                </a>
                <a href="{{route('/my/redPackList')}}?type=unUse">
                    <div class="red-pack-item">
                        <span class="icon un-use"></span>
                        <span class="text">未使用</span>
                    </div>
                </a>
                {{--<a href="#">--}}
                    {{--<div class="red-pack-item">--}}
                        {{--<span class="icon used"></span>--}}
                        {{--<span class="text">已使用</span>--}}
                    {{--</div>--}}
                {{--</a>--}}
                <a href="{{route('/my/redPackList')}}?type=expired">
                    <div class="red-pack-item">
                        <span class="icon expired"></span>
                        <span class="text">已过期</span>
                    </div>
                </a>
            </div>
        </div>

        <div class="items">
            <a href="{{route('/my/markRooms')}}">
            <div class="item">
                <div class="left-side user-icon-1">我的收藏</div>
                <div class="right-side"></div>
            </div>
            </a>
            <a href="{{route('/my/bespeakList')}}">
            <div class="item">
                <div class="left-side user-icon-2">预约记录</div>
                <div class="right-side"></div>
            </div>
            </a>
            <a href="{{route('/my/balance')}}">
            <div class="item">
                <div class="left-side user-icon-3">我的余额</div>
                <div class="right-side"></div>
            </div>
            </a>
            <a href="{{route('/my/backMoneyPage')}}">
            <div class="item">
                <div class="left-side user-icon-4">购房返现</div>
                <div class="right-side"></div>
            </div>
            </a>
            <a href="{{route("/my/suggestion")}}">
            <div class="item">
                <div class="left-side user-icon-5">意见反馈</div>
                <div class="right-side"></div>
            </div>
            </a>
            <a href="{{route('/aboutUs')}}">
            <div class="item">
                <div class="left-side user-icon-6">关于我们</div>
                <div class="right-side"></div>
            </div>
            </a>
        </div>
    </div>
</div>

@include('components/bottomMenu')
@include('components/subscribe')