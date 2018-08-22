@include('header')

<style>
    .red-pack-detail.main {
        position: absolute;
        width: 100vw;
        min-height:100vh;
        background-image: url("{{asset('imgs/bg2.png')}}");
    }
    .red-pack-detail.main .mid {
        position: relative;
        width:80vw;
        height:58vh;
        margin:.6rem auto 0 auto;
        border-radius: .5rem;
        background-image: url("{{asset("imgs/red-pack-grant.png")}}");
        background-position: center;
        background-repeat: no-repeat;
        -webkit-background-size:cover;
        background-size:cover;
    }
    .red-pack-detail.main .mid .box {
        position: absolute;
        width: 100%;
        height: 4.5rem;
        bottom: 0;
        text-align: center;
    }
    .red-pack-detail.main .mid .box .text {
        font-size: .4rem;
        font-weight: 600;
        letter-spacing: .1rem;
        height:1rem;
        line-height:1rem;
        color: #ffe76d;
    }
    .red-pack-detail.main .mid .box .btn {
        width: 39vw;
        height: .6rem;
        line-height:.6rem;
        color: #fe3231;
        font-weight: 600;
        background-color: #ffe76d;
        -webkit-border-radius:.15rem;
        -moz-border-radius:.15rem;
        border-radius:.5rem;
        margin: .6rem auto;
        box-shadow: 2px 2px 2px #000;
    }
    .red-pack-detail.main .mid .box .btn.btn-use {
        width: 60vw;
        height:.8rem;
        line-height: .8rem;
    }


    .red-pack-detail.main .tips {
        background-color: #ffe76d;
        width:80vw;
        margin:.5rem auto;
        -webkit-border-radius: .3rem;
        -moz-border-radius: .3rem;
        border-radius: .3rem;
        padding:.3rem;
    }
    .red-pack-detail.main .tips .title {
        text-align: center;
        color:#fe3231;
    }
    .red-pack-detail.main .tips .content {
        color: #fe3434;
        font-size: .24rem;
        line-height: .5rem;
    }
</style>

<div class="red-pack-detail main">
    <div class="mid">
        <div class="box">
            <div class="text">价值{{$row->total}}元的现金红包</div>
            <div class="btn btn-use">立即使用</div>
            <div class="btn btn-grant">赠送给好友</div>
        </div>
    </div>
    <div class="tips">
        <div class="title">红包规则</div>
        <div class="content">
            <p>1.可通过分享此页面的方式赠送此红包给好友</p>
            <p>2.当好友领取后，将从您账户转移此红包给好友，您将不再拥有此红包</p>
            <p>3.一个红包分享给多个好友后，只有最先领取的好友才能获得，其他好友不会再获得此红包</p>
            <p>4.赠送的红包，不会延长使用过期时间</p>
        </div>
    </div>
</div>