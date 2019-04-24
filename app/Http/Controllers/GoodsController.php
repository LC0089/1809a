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
        $picurl = "http://1809lancong.comcto.com/goodsimg/$good->goods_img";
        $url = "http://1809lancong.comcto.com/goodDetail";
        $title = "秀儿";
        $desc = $good->goods_name;
        $data = [
            'picurl'=>$picurl,
            'url'=>$url,
            'title'=>$title,
            'desc'=>$desc
        ];
        return view('goods.detail',['good'=>$good,'data'=>$data]);
    }
}