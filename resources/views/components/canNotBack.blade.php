<style type="text/css">
    .mySweetAlert {
        display: none;
        position: fixed;
        top:0;
        bottom:0;
        left:0;
        right:0;
        text-align: center;
        background-color: rgba(0,0,0,0.8);
    }

    .mySweetAlert .box {
        display: flex;
        flex-direction: column;
        width:5rem;
        margin: 3rem auto;
        padding:0.3rem;
        border-radius: 0.15rem;
        background-color: #FFF;
        height: 2rem;
        justify-content: space-around;
    }
    .mySweetAlert .box .tips {
        height: 1.5rem;
        line-height: 0.6rem;
    }

    .mySweetAlert .box .buttons {
        display: flex;
        justify-content: space-around;
    }

    .mySweetAlert .box .buttons div {
        padding: .15rem .3rem;
    }
    .mySweetAlert .box .buttons .cancel {
        color:#6E6E6E;
    }

    .mySweetAlert .box .buttons .go-on {
        color:#d22e20;
    }

</style>
<script>
    $(document).ready(function(){
        debugger;
        //禁止退出
        if (window.history && window.history.pushState) {
            $(window).on('popstate', function () {
                debugger;
                var hashLocation = location.hash;
                var hashSplit = hashLocation.split("#!/");
                var hashName = hashSplit[1];
                if (hashName !== '') {
                    var hash = window.location.hash;
                    if (hash === '') {
                        $(".mySweetAlert").show();
                        return false;
                    }
                }
            });
            window.history.pushState('forward', null, window.location.href);
        }

        //放弃
        $(".mySweetAlert .cancel").on("click", function(){
            if (window.history.state === 'forward') {
                window.history.back();
            } else {
                WeixinJSBridge.call('closeWindow');
            }
        });

        //继续分享
        $(".mySweetAlert .go-on").on("click", function(){
            $(".mySweetAlert").hide();
        });
    });
</script>

<div class="mySweetAlert">
    <div class="box">
        <div class="tips">
            <p>您的红包还差一点就能集满啦</p>
            <p>确定要离开吗？</p>
        </div>
        <div class="buttons">
            <div class="cancel">放弃</div>
            <div class="go-on">继续邀请好友助力</div>
        </div>
    </div>
</div>