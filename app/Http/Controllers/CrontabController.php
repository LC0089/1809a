<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Controllers\WXBizDataCryptController;
use Illuminate\Support\Str;
class CrontabController extends Controller{
    /**
     * 删除过期订单
     */
    public function delOrder(){
        $order = DB::table('shop_order')->get()->toArray();
//        print_r($order);die;
        foreach ($order as $k=>$v){
            if(time() - $v->create_time >1800 && $v->pay_status == 0){
                //删除
                $data = DB::table('shop_order')->where(['order_id'=>$v->order_id])->update(['is_del'=>1]);
            }
        }
    }
}