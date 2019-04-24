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
     * 商品详情
     */
    public function goodDetail(){
        $data = DB::table('shop_goods')->get();
    }
}