<style type="text/css">
    /* 规则界面  */
    .rule-container {
        display:none;
        position: fixed;
        left:0;
        top:0;
        width:100vw;
        height:100vh;
        background-color:rgba(0,0,0,0.8);
        z-index:99;
    }

    .rule-container .rule-box {
        position: relative;
        width: 6rem;
        height: 7rem;
        margin: 2rem auto;
        background-color: white;
        border-radius: .3rem;
        padding: .3rem;
    }

    .rule-container .rule-box .rule-title {
        height: .66rem;
        text-align: center;
        font-weight: 600;
        font-size: 0.39rem;
        color: red;
    }

    .rule-container .rule-box .btn-close-rule {
        position: absolute;
        top: -0.7rem;
        right: -0.1rem;
        width: .6rem;
        height: .6rem;
        line-height: .6rem;
        border: .03rem solid white;
        border-radius: .3rem;
        text-align: center;
        color: white;
        font-size: 0.4rem;
    }

    .rule-container .rule-content {
        width: 6rem;
        height: 6.3rem;
        overflow: scroll;
        font-size: 0.3rem;
    }
</style>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function(){
        //关闭规则
        $(".btn-close-rule").on("click", function(){
            $(".rule-container").hide();
        });

        //ajax获取规则内容
        $.getJSON("/red-pack/rule", function (res) {
            if (res.code === 0) {
                $(".rule-container .rule-content").html(res.data.rule);
            }
        })
    });
</script>

<div class="rule-container">
    <div class="rule-box">
        <div class="btn-close-rule">X</div>
        <div class="rule-title">活动规则</div>
        <div class="rule-content">
        </div>
    </div>
</div>