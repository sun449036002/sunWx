<style type="text/css">
.subscribeBox
{
    display: none;
    position: fixed;
    top:0;
    left:0;
    bottom:0;
    right:0;
    z-index: 999;
    background-color: rgba(0,0,0,0.5);
}

.subscribeBox .qr-box {
    position: absolute;
    left: 1.3rem;
    right: 0;
    bottom: 0;
    top: 50%;
    margin: auto;
    height: 100vh;
}

.subscribeBox .subscribe-tips {
    color: white;
    font-size: .4rem;
    text-align: center;
    width: 65vw;
    height: 0.8rem;
    line-height: 0.8rem;
}
.subscribeBox img {
    width: 65vw;
    margin: auto;
}
</style>
<script>
    /**
     * 获取并显示关注二维码图片 (绑定了推广员的账号ID的二维码图片)
     */
    function showSubscribeQrCode(adminId, fromUserId, redPackId, type) {
        var data = {
            adminId : adminId,
            fromUserId : fromUserId,
            redPackId : redPackId || 0,
            r : type || "receive"
        };
        $.getJSON("weixin/qrCode", data, function (res) {
            var jsonData = res.data || {};
            $(".subscribeBox .qr-code-img").attr("src", jsonData.qrCodeUrl || "");
            $(".subscribeBox").show();
        });
    }
</script>
<div class="subscribeBox">
    <div class="qr-box">
        <div class="subscribe-tips">长按二维码，关注后再操作</div>
        <img class="qr-code-img" src="">
    </div>
</div>