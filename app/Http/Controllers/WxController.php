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
            $file_name = rtrim(substr("QAZWSXEDCRFVTGBYHNUJMIKMOLqwertyuiopasdfghjklzxcvbnmP", -10), '"').".jpg";//取文件名后10位
            $img_name =  substr(md5(time() . mt_rand()), 10, 8) . '_' . $file_name;//最后的文件名;
            file_put_contents("/tmp/$img_name",$response,FILE_APPEND);
            $data = [
                'openid'=>$openid,
                'image_url'=>"/tmp/".$img_name
            ];
            $array = DB::table('sucai')->insert($data);
        }else if($MsgType=='text'){
            if(strpos($Content,"天气")){
                $cityid =101110101;
                $url="https://www.tianqiapi.com/api/?version=v1&$cityid";
                $response = file_get_contents($url);
                $arr = json_decode($response,true);
//                print_r($arr);die;
                $city ="城市：" . $arr['city'];
                $time ="当前时间:" . $arr['update_time'];
                foreach($arr['data'] as $v){
                    $week = $v['week'];
                    $wea ="天气：" . $v['wea'];
//                    print_r($v['air_tips']);die;
//                    $air_tips ="建议：" . $v['air_tips'];
//                    print_r($air_tips);die;
                    $tem1 ="最高气温：" . $v['tem1'];
                    $tem2 ="最低气温：" . $v['tem2'];
                    $win_speed ="风级：" . $v['win_speed'];
                }
                $data = [
                    'city'=>$city,
                    'time'=>$time,
                    'week'=>$week,
                    'wea'=>$wea,
                    'time'=>$wea,
                    'tem1'=>$tem1,
                    'tem2'=>$tem2,
                    'win_speed'=>$win_speed,
                ];
                $string="
                $city \n
                $time $week \n
                $wea \n
                $tem1 \n
                $tem2 \n
                $win_speed ";
                $str = "
                <xml>
                  <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                  <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                  <CreateTime>$CreateTime</CreateTime>
                  <MsgType><![CDATA[text]]></MsgType>
                  <Content><![CDATA[$string]]></Content>
                </xml>";
                echo $str;
            }else{
                $data = [
                    'openid'=>$openid,
                    'content'=>$Content
                ];
                $array = DB::table('sucai')->insert($data);
            }
        }else if($MsgType=='voice'){
            $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$accessToken&media_id=$MediaId";
            $response = file_get_contents($url);
            $file_name = rtrim(substr("QAZWSXEDCRFVTGBYHNUJMIKMOLqwertyuiopasdfghjklzxcvbnmP", -10), '"').".mp3";//取文件名后10位
            $voice_name =  substr(md5(time() . mt_rand()), 10, 8) . '_' . $file_name;//最后的文件名;
            file_put_contents("/tmp/$voice_name",$response,FILE_APPEND);
            $data = [
                'openid'=>$openid,
                'voice_url'=>"/tmp/".$voice_name
            ];
            $array = DB::table('sucai')->insert($data);
        }

    }
    //获取accessToken
    public function accessToken(){
        $key = 'wx_access_token';
        $accessToken = Redis::get($key);
        if($accessToken){

        }else{
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

    /**openid群发*/
    public function openiddo(Request $request){
        $accessToken = $this->accessToken();
        //获取测试号下所有用户的openid
        $userurl = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$accessToken";
        $info = file_get_contents($userurl);
        $arrInfo = json_decode($info, true);
//        var_dump($arrInfo);die;
        $data = $arrInfo['data'];
        $openid = $data['openid'];
//        print_r($openid);die;
        //调用接口根据openid群发
        $msgurl = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$accessToken";
        $content = "想你了";
        $arr = array(
            'touser'=>$openid,
            'msgtype'=>"text",
            'text'=>[
                'content'=>$content,
            ],
        );
        $strjson = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $objurl = new Client();
        $response = $objurl->request('POST',$msgurl,[
            'body' => $strjson
        ]);
        $res_str = $response->getBody();
//        print_r($res_str);die;
        echo $res_str;
    }

}
?>
