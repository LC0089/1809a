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
</body>
</html>