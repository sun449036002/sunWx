<style type="text/css">
    .bottom-menu-box {
        position: fixed;
        display: flex;
        justify-content: space-around;
        width: 100vw;
        bottom: 0;
        height: 1rem;
        line-height: 0.5rem;
        background-color: #eee;
        border-top: 1px solid #ccc;
    }

    .bottom-menu-box .item {
        color:#888;
        width: 25vw;
        text-align: center;
        display: flex;
        flex-direction: column;
    }

    .bottom-menu-box .item.selected {
        color:#169ADA;
    }

    .bottom-menu-box .item img {
        width:.48rem;
        height:.48rem;
        align-self: center;
        margin-top: .05rem;
    }
</style>

<script>
    $(document).ready(function () {

    });
</script>

<div class="bottom-menu-box">
    <a href="/">
        <div class="item selected">
            <img src="{{asset("imgs/home-selected.png")}}"/>
            <span>首页</span>
        </div>
    </a>
    <a href="/room/list">
        <div class="item">
            <img src="{{asset("imgs/house-selected.png")}}"/>
            <span>房源</span>
        </div>
    </a>
    <a href="/cash-red-pack">
        <div class="item">
            <img src="{{asset("imgs/red-pack-selected.png")}}"/>
            <span>红包</span>
        </div>
    </a>
    <a href="/my">
        <div class="item">
            <img src="{{asset("imgs/my-selected.png")}}"/>
            <span>我的</span>
        </div>
    </a>
</div>