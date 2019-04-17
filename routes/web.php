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
Route::any('openiddo','WxController@openiddo');

//微信支付
Route::get('test','WxController@test');           //消息群发
Route::post('notify','WxController@notify');       //微信支付回调地址
