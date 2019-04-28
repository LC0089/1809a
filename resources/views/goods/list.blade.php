<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="/js/jquery.min.js"></script>
    <script src="/js/qrcode.min.js"></script>
</head>
<body>
<table border="1">
    <tr>
        <td>id</td>
        <td>{{$data->goods_id}}</td>
    </tr>
    <tr>
        <td>名称</td>
        <td>{{$data->goods_name}}</td>
    </tr>
    <tr>
        <td>价格</td>
        <td>{{$data->goods_selfprice}}</td>
    </tr><tr>
        <td>库存</td>
        <td>{{$data->goods_num}}</td>
    </tr><tr>
        <td>赠送积分</td>
        <td>{{$data->goods_integral}}</td>
    </tr><tr>
        <td>图片</td>
        <td><img class="lazy" src="{{URL::asset('goodsimg/'.$data->goods_img)}}">}</td>
    </tr><tr>
        <td>二维码</td>
        <td><div id="qrcode"></div></td>
    </tr>
</table>
<div width="300px" height="300px" border="1">
    <div id="qrcode"></div>
</div>

</body>
</html>
<script>
    var qrcode = new QRCode('qrcode',{
        text:'{{$code_url}}',
        width:256,
        height:256,
        colorDark : '#000000',
        colorLight : '#ffffff',
        correctLevel : QRCode.CorrectLevel.H
    });

    //qrcode.clear();
    //qrcode.makeCode('new $wpayurl');
</script>
