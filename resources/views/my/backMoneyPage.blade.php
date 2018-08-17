<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <meta name="apple-tel-web-app-capable" content="yes">
    <meta name="apple-tel-web-app-status-bar-style" content="black">
    <title>购房返现</title>
    <link href="{{asset('css/mui.min.css')}}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/mui.picker.min.css')}}" />
    <link href="{{asset('css/mui.styles.css')}}" rel="stylesheet" />

</head>

<body>
<header class="mui-bar mui-bar-nav bgeee">
    <a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left c433"></a>
    <h1 id="title" class="mui-title">买房返现</h1>
</header>
<div class="mui-content">
    <div class="mui-input-group feedback-form">
        <div class="mui-input-row">
            <label for="houses">已购楼盘</label>
            <input id="houses" name="houses" type="text" class="mui-input-clear" placeholder="已购楼盘">
        </div>
        <div class="mui-input-row">
            <label for="address">楼盘地址</label>
            <input id="address" name="address" type="text" class="mui-input-clear" placeholder="具体到几号楼几单位几室">
        </div>
        <div class="mui-input-row">
            <label for="houses">楼盘面积</label>
            <input id="acreage" name="acreage" type="text" class="mui-input-clear" placeholder="单位：㎡">
        </div>
        <div class="mui-input-row">
            <label for="buyTime">购买时间</label>
            <input id="buyTime" name="buyTime" data-options='{"type":"date"}' type="text" class="mui-input-clear" placeholder="请填写购买时间">
        </div>
        <div class="mui-input-row">
            <label for="amount">房款金额</label>
            <input id="amount" name="amount" type="text" class="mui-input-clear" placeholder="单位：万">
        </div>
        <div class="mui-input-row">
            <label for="buyers">购房人</label>
            <input id="buyers" name="buyers" type="text" class="mui-input-clear" placeholder="购房人">
        </div>
        <div class="mui-input-row">
            <label for="type">购房方式</label>
            <input id="type" name="type" type="text" class="mui-input-clear" placeholder="请选择购房方式">
            <input id="type2" name="type2" type="text" style="display: none;" class="mui-input-clear" placeholder="请选择购房方式">
        </div>
        <div class="mui-input-row">
            <label for="tel">联系电话</label>
            <input id="tel" name="tel" type="text" class="mui-input-clear" placeholder="联系电话">
        </div>
        <div class="mui-input-row">
            <label for="tel">我的红包余额</label>
            <input id="tel" name="balance" type="text" class="mui-input-clear" placeholder="我的红包余额，显示最大多少余额">
        </div>
        <div class="mui-input-row">
            <label for="tel">朋友赠送的红包</label>
            <input id="tel" name="friendBalance" type="text" class="mui-input-clear" placeholder="朋友赠送的红包下拉选择框，好友分组，显示金额，从大到小排序">
        </div>
        <div class="mui-input-row upload-row">
            <div class="mui-row">
                <label>购房凭证</label>
            </div>
            <div class="mui-row">
                <div id="preview" class="preview-row"></div>
                <div id="uploadImgs" class="btn-upload">
                    <img class="upload-img" src="{{asset('imgs/uploadImg.png')}}" alt="" />
                    <input type="hidden" id="imgs" name="imgs" />
                </div>
            </div>
        </div>
        <div class="mui-table-view-divider">以下三种支付账号至少填一项</div>
        <div class="mui-input-row">
            <label for="alipay">支付宝</label>
            <input id="alipay" name="alipay" type="text" class="mui-input-clear pay-type" placeholder="支付宝">
        </div>
        <div class="mui-input-row">
            <label for="weixin">微信</label>
            <input id="weixin" name="weixin" type="text" class="mui-input-clear pay-type" placeholder="微信">
        </div>
        <div class="mui-input-row">
            <label for="bankcard">银行账号</label>
            <input id="bankcard" name="bankcard" type="text" class="mui-input-clear pay-type" placeholder="银行账号">
        </div>

        <div class="last-btn-div"><button type="submit" class="mui-btn mui-btn-red" id="submitBtn">提交</button></div>
    </div>
</div>
<script src="{{asset('js/zepto.min.js')}}"></script>
<script src="{{asset('js/h5upload.js')}}"></script>
<script type="text/javascript">
    $.fileUpload({
        filebutton: "#uploadImgs",
        previewZoom: "#preview",
        multiple: true,
        uploadButton: "#fileImage",
        uploadButtonName: 'fileImage',
        fileInfoId: '#imgs',
        csrf_token:"{{csrf_token()}}",
        uploadComplete: uploadIndexComplete
    });

    var imgs = [];
    function uploadIndexComplete(res) {
        res = JSON.parse(res);
        if (res.code === 0) {
            if ((res.imgs || []).length > 0) {
                imgs.push(res.imgs[0]);
            }
        }
        console.log(imgs);
        $("#imgs").val(imgs);
    }
</script>

<script src="{{asset('js/jquery-2.1.1.js')}}"></script>
<script src="{{asset('js/mui.min.js')}}"></script>
<script src="{{asset('js/mui.picker.min.js')}}"></script>
<script type="text/javascript">
    mui.init();

    $(document).ready(function() {
        mui(".last-btn-div").on('tap', '#submitBtn', checkForm);

        /*选择看房时间*/
        mui(".mui-input-row").on('tap', '#buyTime', function() {

            var optionsJson = this.getAttribute('data-options') || '{}';
            var options = JSON.parse(optionsJson);

            var picker = new mui.DtPicker(options);

            picker.show(function(rs) {

                $("#buyTime").val(rs.text);

                picker.dispose();
            });
        });
    });

    /*表单 验证*/
    function checkForm() {

        var $this = $(this);

        $this.text('正在提交...');

        var check = true;

        var houses = $("#houses").val();
        var buyTime = $("#buyTime").val();
        var amount = $("#amount").val();
        var buyers = $("#buyers").val();
        var tel = $("#tel").val();
        var alipay = $("#alipay").val();
        var weixin = $("#weixin").val();
        var bankcard = $("#bankcard").val();
        var address = $("#address").val();
        var acreage = $("#acreage").val();
        var img=$("#imgs").val();
        var mortgage=$('#type2').val();
        var payTypeNum = 0;

        mui(".feedback-form input[type=text]").each(function() {
            //若当前input为空，则alert提醒
            if($(this).hasClass("pay-type") && this.value.trim() != "") {
                payTypeNum++;
            }

            if(!this.value || this.value.trim() == "") {
                if(!$(this).hasClass("pay-type")) {
                    var label = this.previousElementSibling;
                    mui.alert(label.innerText + "不允许为空");
                    check = false;
                    return false;
                }
            }
        }); //校验通过，继续执行业务逻辑
        //				console.log(payTypeNum)
        if(!check) {
            $this.text('提交');
            return false;
        }

        if(isNaN(acreage)) {
            mui.alert('请输入正确的面积！');
            $this.text('提交');
            return false;
        }

        if(!(/^1[3|4|5|7|8]\d{9}$/.test(tel))) {

            mui.alert('请输入正确的手机号！');
            $this.text('提交');
            return false;
        }
        if(payTypeNum < 1) {
            mui.alert('三种支付账号至少填一项');
            $this.text('提交');
            return false;
        }

        if(isNaN(amount)) {
            mui.alert('请填写数字');
            $this.text('提交');
            return false;
        }

        $.ajax({
            type : 'post',
            url : "/my/submitBackMoney",
            data : {
                houses: houses,
                buyTime: buyTime,
                amount: amount,
                buyers: buyers,
                tel: tel,
                alipay: alipay,
                weixin: weixin,
                bankcard: bankcard,
                address: address,
                acreage: acreage,
                img:img,
                mortgage:mortgage
            },
            dataType : "json",
            headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
            success : function(res){
                if (res.code > 0) {
                    mui.alert(res.msg);
                } else {
                    mui.alert("提交成功", function(){
                        window.location.href = "/my"
                    });
                }
            }
        });

    }
</script>
<script>
    (function($, doc) {
        $.init();
        $.ready(function() {
            //普通示例
            var userPicker = new $.PopPicker();
            userPicker.setData([{
                value: '0',
                text: '全额付款'
            }, {
                value: '1',
                text: '按揭'
            }]);
            var showUserPickerButton = doc.getElementById('type');
            var userResult = doc.getElementById('type2');
            showUserPickerButton.addEventListener('tap', function(event) {
                userPicker.show(function(items) {
                    var reg = new RegExp('"', "g");
                    var st = JSON.stringify(items[0].value).replace(reg, "");
                    var st2 = JSON.stringify(items[0].text).replace(reg, "");
                    userResult.value = st;
                    showUserPickerButton.value=st2
                    //返回 false 可以阻止选择框的关闭
                    //return false;
                });
            }, false);
        });
    })(mui, document);
</script>
</body>

</html>