<script>
    var alertPopup = {
        show : function(obj) {
            $(".alert-popup .msg").html(obj.msg || "");
            $(".alert-popup").show();

            $(".alert-popup .btn-sure").on("click", function(){
                if(typeof obj !== "undefined" && typeof obj.cb === "function") {
                    $(".alert-popup").hide(obj.cb(obj));
                } else {
                    $(".alert-popup").hide();
                }
            });
        }
    };
</script>
<style type="text/css">
    .alert-popup {
        display: none;
        position: fixed;
        top:0;
        left:0;
        width:100vw;
        height:100vh;
        background-color: rgba(0,0,0,0.5);
    }

    .alert-popup .main {
        position: absolute;
        width:100vw;
        bottom:0;
        text-align: center;
    }
    .alert-popup .msg {
        width: 95vw;
        height: .6rem;
        line-height: .6rem;
        margin: auto;
        font-size: .4rem;
        background-color: #fff;
        border-radius: .1rem;
        padding: .02rem 0;
        color: #777;
    }


    .alert-popup .btn-sure {
        width: 95vw;
        height: .6rem;
        line-height: .6rem;
        margin: .3rem auto;
        font-size: .4rem;
        background-color: #fff;
        border-radius: .1rem;
        padding: .1rem 0;
        color: rgb(6, 188, 7);
    }
</style>
<div class="alert-popup">
    <div class="main">
        <div class="msg">您确定吗？</div>
        <div class="btn-sure">确定</div>
    </div>
</div>