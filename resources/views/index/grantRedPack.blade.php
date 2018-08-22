@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/my-red-pack-detail.css")}}"/>

<style>
    .red-pack-detail.main .mid .box .btn.btn-receive {
        width: 60vw;
        height: .8rem;
        line-height: .8rem;
        margin-top: 1.6rem;
        font-size: 0.35rem;
    }
</style>

<script>
    $(document).ready(function () {
        //立即领取
        $(".red-pack-detail.main .btn-receive").on("click", function () {
            //未关注
            var isSubscribe = parseInt("{{$user['is_subscribe'] ?? 0}}");
            if (!isSubscribe) {
                showSubscribeQrCode("{{$adminId}}", "{{$row->userId}}", "{{$row->id}}", "help");
                return false;
            }

            //请求领取
            $.ajax({
                type : 'post',
                url : "/index/receiveGrantRedPack",
                data : {
                    redPackId : "{{$row->id}}",
                    ticket : "{{$ticket}}"
                },
                dataType : "json",
                headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
                success : function(res){
                    alertPopup.show({
                        msg : res.msg,
                        cb : function(){
                            window.location.href = res.code > 0 ? "/" : "/my";
                        }
                    });
                }
            });
        });
    });
</script>

<div class="red-pack-detail main">
    <div class="mid">
        <div class="box">
            <div class="text">
                <p>{{$row->nickname}}赠送给您一个</p>
                <p>价值{{$row->total}}元的现金红包</p>
            </div>
            <div class="btn btn-receive">立即领取</div>
        </div>
    </div>
    <div class="tips">
        <div class="title">红包规则</div>
        <div class="content">
            <p>1.关注用户才能领取红包</p>
            <p>2.领取后的红包会立即转入您的个人中心</p>
            <p>3.一个红包分享给多个好友后，只有最先领取的好友才能获得，其他好友不会再获得此红包</p>
            <p>4.赠送的红包，不会延长使用过期时间</p>
        </div>
    </div>
</div>

@include("components/subscribe")
@include('components/alertPopup')
@include('components/shareLayer', ['msg' => '通过分享赠送给好友~'])
