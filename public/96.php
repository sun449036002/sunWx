<?php
//调用wx类中的sign方法获取签名所需参数
//引入类文件
include 'sdk/Wx.php';
$obj = new Wx();
$result = $obj->sign();
//echo '<pre>';
print_r($result);die;


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>这个是测试标题</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="{dede:field name='description'  function='html2text(@me)'/}" />
<script src="http://libs.baidu.com/jquery/1.2.3/jquery.min.js"></script>
<script type="text/javascript" src="jweixin-1.2.0.js"></script>
<body>
 
 这个是测试内容，欢迎光临。

 
 

<script>

wx.config({
            debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: '<?php echo $result["appid"]; ?>', // 必填，公众号的唯一标识
            timestamp: <?php echo $result["timestamp"]; ?>, // 必填，生成签名的时间戳
            nonceStr: '<?php echo $result["nonceStr"]; ?>', // 必填，生成签名的随机串
            signature: '<?php echo $result["signature"]; ?>',// 必填，签名
            jsApiList: [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',

            ] // 必填，需要使用的JS接口列表
        });
		
		
        //用ready方法来接收验证成功
        wx.ready(function() {
            // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
    
	
	        //分享到微信朋友圈
            wx.onMenuShareTimeline({
                title: '好好学习 天天向上', // 分享标题
                link: '<?php echo $result["url"]; ?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: 'http://www.gumama120.com/uploads/allimg/180531/1-1P531155024455.jpg', // 分享图标
                success: function () {
                    // 用户点击了分享后执行的回调函数
                },
            });



            //分享给微信好友
            wx.onMenuShareAppMessage({
                title: '好好学习 天天向上', // 分享标题
                desc: '专注老师讲课 天天考试满分', // 分享描述
                link: '<?php echo $result["url"]; ?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: 'http://www.gumama120.com/uploads/allimg/180531/1-1P531155024455.jpg', // 分享图标
                type: 'link', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                     // 用户点击了分享后执行的回调函数
                }
            });
        })		




		
		
</script>















    





</body>

</html>