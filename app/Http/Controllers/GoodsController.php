<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Controllers\WXBizDataCryptController;
use Illuminate\Support\Str;
class GoodsController extends Controller{
    /**
     * 最新商品详情
     */
    public function goodDetail(){
        $good = DB::table('shop_goods')->where('goods_up',1)->orderBy('create_time','desc')->first();

        $jsapi_ticket = $this->JsapiTicket();
        $nonceStr = Str::random(10);
        $timestamp = time();
        $current_cul = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $string1 = "jsapi_ticket=$jsapi_ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$current_cul";
//        print_r($string1);die;
        $sign = sha1($string1);
//        dump($sign);die;
        $js_config = [
            'appId'=>env('WX_APPID'), //公众号IP
            'timestamp'=>$timestamp,
            'nonceStr'=>$nonceStr,  //随机字符串
            'signature'=>$sign,  //签名
//            'jsApiList'=>['chooseImage'],  //要使用的表功能
        ];
        $data = [
            'js_config'=>$js_config
        ];
        $picurl = "http://1809lancong.comcto.com/goodsimg/$good->goods_img";
        $url = "http://1809lancong.comcto.com/goodDetail";
        $title = "秀儿";
        $desc = $good->goods_name;
        $arr = [
            'picurl'=>$picurl,
            'url'=>$url,
            'title'=>$title,
            'desc'=>$desc
        ];
        return view('goods.detail',['good'=>$good,'data'=>$data,'arr'=>$arr]);
    }

    public function JsapiTicket()
    {
        $key = 'wx_jsapi_ticket';
        $jsapi_ticket = Redis::get($key);
        if ($jsapi_ticket) {
            return $jsapi_ticket;
        } else {
            $accessToken = $this->accessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$accessToken&type=jsapi";
            $jsapi_ticket = json_decode(file_get_contents($url), true);

            if (isset($jsapi_ticket['ticket'])) {
                Redis::set($key, $jsapi_ticket['ticket']);
                Redis::expire($key, 3600);
                return $jsapi_ticket['ticket'];
            } else {
                return false;
            }
        }
    }
    //获取accessToken
    public function accessToken(){
        $key = 'wx_access_token';
        $accessToken = Redis::get($key);
        if($accessToken){
            return $accessToken;
        }else{
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';
            $response = file_get_contents($url);
            $arr = json_decode($response,true);
            $access = $arr['access_token'];
//            print_r($arr);die;
            Redis::set($key,$access);
            Redis::expire($key,3600);
            $accessToken = $arr['access_token'];
            return $accessToken;
        }
    }
}