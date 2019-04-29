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
        <tr>
            <td>openid</td>
            <td>签到时间</td>
        </tr>
        @foreach($arr as $k=>$v)
        <tr>
            <td>{{$v['openid']}}</td>
            <td>{{$v['create_time']}}</td>
        </tr>
        @endforeach
</body>
</html>