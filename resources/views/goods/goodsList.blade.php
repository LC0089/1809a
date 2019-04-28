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
        <td>详情</td>
    </tr>
    @foreach($arr as $k=>$v)
    <tr>
        <td>{{$v->goods_name}}</td>
        <td>{{$v->goods_selfprice}}</td>
        <td><img class="lazy" src="{{URL::asset('goodsimg/'.$v->goods_img)}}"></td>
        <td><a href="details/?$goods_id={{ $v->goods_id }}">详情</a></td>
    </tr>
     @endforeach
</table>
</body>
</html>