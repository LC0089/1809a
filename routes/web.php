<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('valid','WxController@valid');
Route::post('valid','WxController@wxEvent');
Route::any('accessToken','WxController@accessToken');
Route::any('menu','WxController@menu');
Route::any('openiddo','WxController@openiddo'); //openid群发
Route::any('give','WxController@give');  //用户授权
Route::any('code','WxController@code');  //code回调

//微信支付
Route::any('test','WxController@test');           //消息群发
Route::any('notify','WxController@notify');       //微信支付回调地址

Route::any('goodList','GoodsController@goodList');
Route::any('details','GoodsController@details');
Route::any('goodDetail','GoodsController@goodDetail');
Route::any('accessToken','GoodsController@accessToken');
//计划任务
Route::any('delOrder','CrontabController@delOrder');  //定时删除订单

Route::any('move','GoodsController@move');  //生成带参数的二维码