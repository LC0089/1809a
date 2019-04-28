<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>秀儿</title>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/qrcode.min.js"></script>
</head>
<body>
        <table border="1">
            <tr>
                <td>名称</td>
                <td>价格</td>
                <td>图片</td>
            </tr>
            <tr>
                <td>{{$good->goods_name}}</td>
                <td>{{$good->goods_selfprice}}</td>
                <td ><img class="lazy" src="{{URL::asset('goodsimg/'.$good->goods_img)}}"></a> </td>
            </tr>
        </table>

</body>
</html>
<script src="js/jquery/jquery-1.12.4.min.js"></script>
<script src="http://res2.wx.qq.com/open/js/jweixin-1.4.0.js "></script>
<script>
    wx.config({
        debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: "{{$js_config['appId']}}", // 必填，公众号的唯一标识
        timestamp: "{{$js_config['timestamp']}}", // 必填，生成签名的时间戳
        nonceStr: "{{$js_config['nonceStr']}}", // 必填，生成签名的随机串
        signature: "{{$js_config['signature']}}",// 必填，签名
        jsApiList: ['updateAppMessageShareData'] // 必填，需要使用的JS接口列表
    });
    wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
            wx.updateAppMessageShareData({
                title: "{{$arr['title']}}", // 分享标题
                desc: "{{$arr['desc']}}", // 分享描述
                link: "{{$arr['url']}}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: "{{$arr['picurl']}}", // 分享图标
                success: function (msg) {
                    alert('设置成功')
                }
            })
    });
</script>
{{--<script>--}}
    {{--var qrcode = new QRCode('qrcode',{--}}
        {{--text:'{{$url}}',--}}
        {{--width:256,--}}
        {{--height:256,--}}
        {{--colorDark : '#000000',--}}
        {{--colorLight : '#ffffff',--}}
        {{--correctLevel : QRCode.CorrectLevel.H--}}
    {{--});--}}

    {{--//qrcode.clear();--}}
    {{--//qrcode.makeCode('new $wpayurl');--}}
{{--</script>--}}
{{--<div width="300px" height="300px" border="1">--}}
    {{--<div id="qrcode"></div>--}}
{{--</div>--}}

