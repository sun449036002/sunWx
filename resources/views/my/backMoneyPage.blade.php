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
    <link rel="stylesheet" type="text/css" href="{{asset("css/my-red-pack-list.css")}}"/>

    <style type="text/css">
        html {
            font-size: calc(100vw/7.5);
        }
        body {
            margin:0;
            width: 7.5rem;
            font-size: .3rem;
        }
        a {
            text-decoration: none;
        }

        p {
            margin:0;
            padding:0;
        }

        .red-pack-main {
            display: none;
            position: absolute;
            top:0;
            bottom:0;
            left:100vw;
            z-index: 11;
        }
        .red-pack-main .bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            z-index: 12;
            justify-content: space-between;
            padding: 0 .3rem;
        }
        .red-pack-main .red-pack-box {
            margin-top:1.2rem;
        }
        .red-pack-main .item {
            position: relative;
        }
        .red-pack-main .item.selected .selected-icon {
            width: 1rem;
            height: 1rem;
            background-size: cover;
            position: absolute;
            top: 0;
            right: 0;
            background-image: url('{{asset("imgs/selected.png")}}');
        }
        .red-pack-main .bar span {
            padding: 0 .1rem;
            font-weight: 600;
        }
        .red-pack-main .bar .info {
            width:4.5rem;
        }
        .red-pack-main .bar .btn {
            background-color: #1ab394;
            color: #FFF;
            height: .6rem;
            margin-top: .2rem;
            line-height: .6rem;
            padding: 0 .3rem;
            border-radius: .1rem;
        }

        .feedback-form .preview-row2 {
            display: flex;
            flex-wrap: wrap;
        }
        .feedback-form .preview-row2 .img-item {
            position: relative;
            width: 1.8rem;
            height: 1.8rem;
            margin: 0 0 0.5rem 0.5rem;
        }
        .feedback-form .preview-row2 .img-item img {
            width:100%;
            height: 100%;
        }
        .feedback-form .preview-row2 .img-item .del-img {
            position: absolute;
            top: -.3rem;
            right: -.3rem;
            width: 0.5rem;
            height: 0.5rem;
            background-color: rgba(0,0,0,0.8);
            color: #FFF;
            text-align: center;
            line-height: 0.5rem;
            border-radius: .3rem;
        }

    </style>

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
            <label>我的可用红包</label>
            <input id="redPackIdsClick" type="text" class="mui-input-clear" placeholder="点击选取我的红包" readonly>
            <input id="redPackIds" type="hidden" name="redPackIds" value="">
        </div>
        <div class="mui-input-row">
            <label>赠送的红包</label>
            <input id="friendRedPackIdsClick" type="text" class="mui-input-clear" placeholder="点击选取朋友赠送的红包，朋友赠送的红包下拉选择框，好友分组，显示金额，从大到小排序" readonly>
            <input id="friendRedPackIds" type="hidden" name="friendRedPackIds" value="">
        </div>
        <div class="mui-input-row upload-row">
            <div class="mui-row">
                <label>购房凭证</label>
            </div>
            <div class="mui-row">
                <div id="uploadImgs2" class="btn-upload">
                    <img class="upload-img" src="{{asset('imgs/uploadImg.png')}}" alt="" />
                    <input type="hidden" id="imgs2" name="imgs2[]" />
                </div>
                <div id="preview2" class="preview-row2"></div>
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

{{-- 红包展示区域 --}}
<div class="red-pack-main">
    <div class="bar">
        <div class="info">
            选择<span class="num">0</span>个红包，总价值<span class="total-val">0</span>元
        </div>
        <div class="btn btn-select-all">全选</div>
        <div class="btn btn-select-sure">确定</div>
    </div>
    <div class="red-pack-box">
        <div class="list"></div>
    </div>
</div>

<script src="{{asset('js/jquery-2.1.1.js')}}"></script>
<script src="{{asset('js/mui.min.js')}}"></script>
<script src="{{asset('js/mui.picker.min.js')}}"></script>
<script src="{{asset('js/jweixin-1.2.0.js')}}" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    mui.init();

    $(document).ready(function() {
        wx.config(<?php echo $wxapp->jssdk->buildConfig(['chooseImage', 'uploadImage', 'downloadImage'], false) ?>);

        wx.ready(function(){
            var serverIds = [];
            //图片上传
            $("#uploadImgs2").on("click", function(){
                console.log('upload img 2 clicked', wx);
                wx.chooseImage({
                    count: 9, // 默认9
                    sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
                    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
                    success: function (res) {
                        var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                        $.each(localIds, function(k, localId){
                            //上传图片到微信服务器
                            wx.uploadImage({
                                localId: localId, // 需要上传的图片的本地ID，由chooseImage接口获得
                                isShowProgressTips: 1, // 默认为1，显示进度提示
                                success: function (res) {
                                    var serverId = res.serverId; // 返回图片的服务器端ID
                                    serverIds.push(serverId);
                                    console.log(serverIds.length, serverIds.length > 0);
                                    $("#imgs2").val(serverIds);
                                    console.log('serverId:', serverId);

                                    var imgHtml = "<div class='img-item' data-server-id='" + serverId + "'><img src='" + localId + "' /><div class='del-img'>X</div></div>";
                                    $("#preview2").append(imgHtml);
                                }
                            });
                        });
                    }

                });
            });
        });

        //删除已上传的图片
        $(".feedback-form").on("click", ".preview-row2 .img-item .del-img", function(){
            console.log('del img clicked');
            $(this).parent().remove();

            //重置input中的值
            var _serverIds = [];
            $(".feedback-form .preview-row2 .img-item").each(function(k, item){
                var _serverId = $(item).data("serverId");
                _serverIds.push(_serverId);
                console.log("now server id is :" + _serverId);
            });
            $("#imgs2").val(_serverIds);
        });

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

        function showRedPackList(list) {
            var redPackListDom = $(".red-pack-main .list");
            redPackListDom.empty();
            $.each(list, function(k,item){
                console.log(item);
                var html = '<div class="item ' + item.type + '" data-id="' + item.id + '">' +
                    '<div class="selected-icon"></div>' +
                    '<div class="bg"></div>' +
                    '<div class="data">' +
                        '<div class="money"><span>' + item.total + '</span>元</div>' +
                        '<div class="from">活动</div>' +
                        '<div class="expiredTime">过期时间:' + item.useExpiredTime + '</div>' +
                    '</div>' +
                    '</div>';
                redPackListDom.append(html);

            });
        }

        //我的红包选择
        $("#redPackIdsClick").on("click", function(){
            var self = $(this);
            //清空原先的数据
            self.val('');
            $("#redPackIds").val('');
            $(".red-pack-main .bar .num").text(0);
            $(".red-pack-main .bar .total-val").text(0);

            //获取数据
            $.ajax({
                type : 'get',
                url : "{{route('/my/getMyEnabledRedPackList')}}",
                data : {},
                dataType : "json",
                headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
                success : function(res){
                    if (res.code > 0) {
                        mui.alert(res.msg);
                    } else {
                        var list = res.data || [];
                        if (list.length === 0) {
                            self.val("暂无可用红包");
                            return false;
                        } else {
                            showRedPackList(list);
                            $(".red-pack-main").show().animate({
                                left : 0
                            }, 100, "linear");
                            $(document).scrollTop(0);
                        }
                    }
                }
            });
        });
        //朋友赠送的红包选择
        $("#friendRedPackIdsClick").on("click", function(){
            var self = $(this);
            //清空原先的数据
            self.val('');
            $("#friendRedPackIds").val('');
            $(".red-pack-main .bar .num").text(0);
            $(".red-pack-main .bar .total-val").text(0);

            //获取数据
            $.ajax({
                type : 'get',
                url : "{{route('/my/getMyEnabledRedPackList')}}",
                data : {
                    type : 'friend'
                },
                dataType : "json",
                headers : {"X-CSRF-TOKEN" : "{{csrf_token()}}"},
                success : function(res){
                    if (res.code > 0) {
                        mui.alert(res.msg);
                    } else {
                        var list = res.data || [];
                        if (list.length === 0) {
                            self.val("暂无可用红包");
                            return false;
                        } else {
                            showRedPackList(list);
                            $(".red-pack-main").show().animate({
                                left : 0
                            }, 100, "linear");
                            $(document).scrollTop(0);
                        }
                    }
                }
            });
        });

        //红包选择
        $(".red-pack-main").on("click", ".item", function(e){
            $(this).toggleClass("selected");
            var totalMoney = 0;
            var selectedItems = $(".red-pack-main .item.selected");
            var redPackIds = [];
            selectedItems.each(function(){
                var v = $(this).find(".money span").text();
                totalMoney += parseInt(v);
                redPackIds.push($(this).data("id"));
            });
            $(".red-pack-main .bar .num").text(selectedItems.length);
            $(".red-pack-main .bar .total-val").text(totalMoney);

            //区别是我的红包选择，还是朋友红包的选择
            if($(this).hasClass("friend")) {
                $("#friendRedPackIds").val(redPackIds);
                $("#friendRedPackIdsClick").val("您选择了" + selectedItems.length + "个红包，价值" + totalMoney + '元');
            } else {
                $("#redPackIds").val(redPackIds);
                $("#redPackIdsClick").val("您选择了" + selectedItems.length + "个红包，价值" + totalMoney + '元');
            }
        });

        //确认选择
        $(".red-pack-main").on("click", ".btn-select-sure", function(e){
            $(".red-pack-main").animate({
                left : "100vw"
            }).hide();
        });

        //全选
        $(".red-pack-main").on("click", ".btn-select-all", function(e){
            $(".red-pack-main .red-pack-box .item").trigger("click");
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
        var wxImgs=$("#imgs2").val();
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
                wxImgs:wxImgs,
                mortgage:mortgage,
                redPackIds : $("#redPackIds").val(),
                friendRedPackIds : $("#friendRedPackIds").val()
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