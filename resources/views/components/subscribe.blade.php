<style type="text/css">
.subscribeBox
{
    display: none;
    position: fixed;
    top:0;
    left:0;
    bottom:0;
    right:0;
    background-color: rgba(0,0,0,0.5);

}

.subscribeBox .subscribe-tips {
    color:white;
    font-size: 20px;
    text-align: center;
    width:60vw;
}
    .subscribeBox img {
        width:60vw;
        height: 60vw;
        margin: auto;
    }
</style>
<script>
    /**
     * 获取并显示关注二维码图片 (绑定了推广员的账号ID的二维码图片)
     */
    function showSubscribeQrCode() {
        $.getJson("weixin/qrCode", function (res) {
            console.log(res);
        })
    }
</script>
<div class="subscribeBox">
    <div class="subscribe-tips">长按二维码，关注后再操作</div>
    <img class="qr-cdoe-img" src="">
</div>