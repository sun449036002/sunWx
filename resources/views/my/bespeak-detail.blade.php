@include('header')

<link rel="stylesheet" type="text/css" href="{{asset("css/room-bespeak.css")}}"/>

<style>
    .main .item {
        height: auto;
    }
</style>

<div class="main">
    <div class="item">
        <div class="room-name">{{$bespeak->roomSourceName ?? ""}}</div>
    </div>
    <div class="item">
        <div class="input-label">姓名</div>
        <div class="input-box">{{$bespeak->name}}</div>
    </div>
    <div class="item">
        <div class="input-label">电话</div>
        <div class="input-box">{{$bespeak->tel}}</div>
    </div>
    <div class="item">
        <div class="input-label">预约人数</div>
        <div class="input-box">{{$bespeak->num}}</div>
    </div>
    <div class="item">
        <div class="input-label">接送地址</div>
        <div class="input-box">{{$bespeak->address}}</div>
    </div>
    <div class="item">
        <div class="input-label">接送时间</div>
        <div class="input-box">{{$bespeak->time}}</div>
    </div>
</div>

@include('components/bottomMenu')