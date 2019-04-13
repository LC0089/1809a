<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
class WxController extends Controller{
    public function valid(){
        echo $_GET['echostr'];
    }
    public function wxEvent(){
        $content = file_get_contents("php://input");
        $time = date('Y-m-d H:i:s');
        $str = $time . $content . "\n";
        file_put_contents("logs/wx_event.log",$str,FILE_APPEND);

        $objxml = simplexml_load_string($content);
        $ToUserName = $objxml->ToUserName;
        $FromUserName = $objxml->FromUserName;
        $CreateTime = $objxml->CreateTime;
        $MsgType = $objxml->MsgType;
        $Event = $objxml->Event;
        $EventKey = $objxml->EventKey;
        $Content = $objxml->Content;

        $openid = $FromUserName;
        $accessToken = $this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openid&lang=zh_CN";
        $response = file_get_contents($url);
        $arr = json_decode($response,true);
//        print_r($arr);die;
        $name = $arr['nickname'];
        $openid = $arr['openid'];
        $date = DB::table('user')->where('openid',$openid)->first();
        if($date){
            $content = "$name,欢迎回来";
            $str = "
            <xml>
              <ToUserName><![CDATA[$FromUserName]]></ToUserName>
              <FromUserName><![CDATA[$ToUserName]]></FromUserName>
              <CreateTime>$CreateTime</CreateTime>
              <MsgType><![CDATA[text]]></MsgType>
              <Content><![CDATA[$content]]></Content>
            </xml>";
            echo $str;
        }else{
            $data=[
                'user_name'=>$name,
                'openid'=>$openid
            ];
            $array = DB::table('user')->insert($data);
            $content = "$name,欢迎关注";
            $str = "
            <xml>
              <ToUserName><![CDATA[$FromUserName]]></ToUserName>
              <FromUserName><![CDATA[$ToUserName]]></FromUserName>
              <CreateTime>$CreateTime</CreateTime>
              <MsgType><![CDATA[text]]></MsgType>
              <Content><![CDATA[$content]]></Content>
            </xml>";
            echo $str;
        }


    }
    //获取accessToken
    public function accessToken(){

        $key = 'wx_access_token';
        $accessToken = Redis::get($key);
        //if($accessToken){
         //   echo 'Cache:';
       // }else{
           // echo 'NoCache:';
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';
            $response = file_get_contents($url);
            $arr = json_decode($response,true);
//            print_r($arr);die;
            Redis::set($key,$arr['access_token']);
            Redis::expire($key,3600);
            $accessToken = $arr['access_token'];
            print_r($accessToken);
       // }
        return $accessToken;
    }
    public function menu(){
        $accessToken = $this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$accessToken";
        $arr = array(
            "button"=> array(
                array(
                    'name'=>"葫芦娃娃",
                    "type"=>"click",
                    "key"=>"aaaaa",
                    "sub_button"=>array(
                        array(
                            "type"=>"click",
                            "name"=>"大娃娃",
                            "key"=>"iii"
                        ),
                        array(
                            "type"=>"click",
                            "name"=>"小娃娃",
                            "key"=>"iii"
                        ),
                    ),

                ),
                array(
                    'name'=>"玩具",
                    "type"=>"click",
                    "key"=>"bbb",
                    "sub_button"=>array(
                        array(
                            "type"=>"click",
                            "name"=>"店铺",
                            "key"=>"iii"
                        ),
                        array(
                            "type"=>"view",
                            "name"=>"百度",
                            "url"=>"https://www.baidu.com/"
                        ),

                    ),
                ),
                array(
                    'name'=>"推广",
                    "type"=>"click",
                    "key"=>"bbb",
                    "sub_button"=>array(
                        array(
                            "type"=>"scancode_waitmsg",
                            "name"=>"微信扫码",
                            "key"=>"iii"
                        ),
                    ),

                ),
            ),
        );
        $strjson = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $clinet = new Client();
        $response = $clinet ->request("POST",$url,[
            'body'=>$strjson
        ]);
        $res_str = $response->getBody();
        echo $res_str;
    }
}
?>
