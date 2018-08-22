<style type="text/css">
    .share-layer {
        display: none;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0,0,0,0.8);
    }

    .share-layer img {
        position: absolute;
        top: .5rem;
        right: 0;
        width: 4rem;
    }

    .share-layer .share-tips {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        position: absolute;
        top: 3rem;
        left: 0.375rem;
        width: 6rem;
        height: 2rem;
        padding: 0.3rem;
        border-radius: 0.1rem;
        margin: auto;
        background-color: white;
        text-align: center;

    }
</style>
<script>
    $(document).ready(function(){
        $(".share-layer").on("click", function(){
            $(this).hide();
        })
    });
</script>
<div class="share-layer">
    <img src="{{asset("imgs/jiantou.png")}}" />
    <div class="share-tips">
        <div>点击右上角按钮</div>
        <div>{{$msg ?? "邀请更多好友来助力吧~"}}</div>
    </div>
</div>