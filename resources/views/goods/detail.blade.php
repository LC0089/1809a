<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
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
                <td><img class="lazy" src="{{URL::asset('goodsimg/'.$good->goods_img)}}"></a> </td>
            </tr>
        </table>
        <script src="js/jquery/jquery-1.12.4.min.js"></script>
        <script src="http://res2.wx.qq.com/open/js/jweixin-1.4.0.js "></script>
</body>
</html>
<script>
    wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
            wx.updateAppMessageShareData({
                title: "秀儿", // 分享标题
                desc: "哈喽", // 分享描述
                link: "{{$data['url']}}", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: '{{$data['picurl']}}', // 分享图标
                success: function (msg) {
                    alert('设置成功')
                }
            })
    });
</script>
