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
        $MediaId = $objxml->MediaId;

        $openid = $FromUserName;
        $accessToken = $this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openid&lang=zh_CN";
        $response = file_get_contents($url);
        $arr = json_decode($response,true);
//        print_r($arr);die;
        $name = $arr['nickname'];
        $openid = $arr['openid'];
        $date = DB::table('user')->where('openid',$openid)->count();
//        print_r($date);die;
        if($Event=='subscribe'){
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

        if($MsgType=='image'){
            $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$accessToken&media_id=$MediaId";
            $response = file_get_contents($url);
            file_put_contents("/tmp/$time.jpg",$response,FILE_APPEND);
        }else if($MsgType=='text'){
            $data = [
                'openid'=>$openid,
                'content'=>$Content
            ];
            $array = DB::table('sucai')->insert($data);
        }else if($MsgType=='voice'){
            $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$accessToken&media_id=$MediaId";
            $response = file_get_contents($url);
            file_put_contents("/tmp/$time.mp3",$response,FILE_APPEND);
        }

    }
    //获取accessToken
    public function accessToken(){
        $key = 'wx_access_token';
        $accessToken = Redis::get($key);
        if($accessToken){
            echo 'Cache:';
        }else{
            echo 'NoCache:';
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';
            $response = file_get_contents($url);
            $arr = json_decode($response,true);
            $access = $arr['access_token'];
//            print_r($arr);die;
            Redis::set($key,$access);
            Redis::expire($key,3600);
           $accessToken = $arr['access_token'];
//        print_r($accessToken);
        }
        return $accessToken;
    }
    public function menu(){
        $accessToken = $this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$accessToken";
        $arr = array(
            "button"=> array(
                array(
                    "name"=>"发送位置",
                    "type"=> "location_select",
                    "key"=> "rselfmenu_2_0"
                ),
                array(
                    'name'=>"发图",
                    "sub_button"=>array(
                        array(
                            "type"=>"pic_sysphoto",
                            "name"=>"系统拍照发图",
                            "key"=>"rselfmenu_1_0",
                            "sub_button"=>[ ]
                        ),
                        array(
                            "type"=>"pic_photo_or_album",
                            "name"=>"拍照或者相册发图",
                            "key"=>"rselfmenu_1_1",
                            "sub_button"=>[ ]
                        ),
                        array(
                            "type"=>"pic_weixin",
                            "name"=>"微信相册发图",
                            "key"=>"rselfmenu_1_2",
                            "sub_button"=>[ ]
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
