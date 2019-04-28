<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Controllers\WXBizDataCryptController;
use Illuminate\Support\Str;
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
        $name = $arr['nickname'];
        $openid = $arr['openid'];
        $date = DB::table('user')->where('openid',$openid)->count();

        if($Event=='SCAN'){
            $good = DB::table('shop_goods')->where('goods_up',1)->orderBy('create_time','desc')->first();
            $good_name = $good->goods_name;
            $title = "秀儿";
            $picurl = "http://1809lancong.comcto.com/goodsimg/$good->goods_img";
            $url = "http://1809lancong.comcto.com/goodDetail";
            $str = "<xml>
                          <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                          <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                          <CreateTime>$CreateTime</CreateTime>
                          <MsgType><![CDATA[news]]></MsgType>
                          <ArticleCount>1</ArticleCount>
                          <Articles>
                            <item>
                              <Title><![CDATA[$title]]></Title>
                              <Description><![CDATA[$good_name]]></Description>
                              <PicUrl><![CDATA[$picurl]]></PicUrl>
                              <Url><![CDATA[$url]]></Url>
                            </item>
                          </Articles>
                        </xml>";
            echo $str;
        }

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
            }else if($Content=="最新商品"){
                $good = DB::table('shop_goods')->where('goods_up',1)->orderBy('create_time','desc')->first();
                $good_name = $good->goods_name;
                $title = "秀儿";
                $picurl = "http://1809lancong.comcto.com/goodsimg/$good->goods_img";
                $url = "http://1809lancong.comcto.com/goodDetail";
                $str = "<xml>
                          <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                          <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                          <CreateTime>$CreateTime</CreateTime>
                          <MsgType><![CDATA[news]]></MsgType>
                          <ArticleCount>1</ArticleCount>
                          <Articles>
                            <item>
                              <Title><![CDATA[$title]]></Title>
                              <Description><![CDATA[$good_name]]></Description>
                              <PicUrl><![CDATA[$picurl]]></PicUrl>
                              <Url><![CDATA[$url]]></Url>
                            </item>
                          </Articles>
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
    //自定义菜单
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

    public $weixin_unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';        // 统一下单接口
    public $notify_url = 'http://1809lancong.comcto.com/notify'; // 支付回调
    public function test(){
        $total_fee = 1;         //用户要支付的总金额
//        $order=DB::table('shop_order')->first();
//        $order_id = $order->order_number;
        $order_id = time().mt_rand(11111,99999);            //测试订单号 随机生成
        $order_info = [
            'appid'         =>  env('WEIXIN_APPID_0'),      //微信支付绑定的服务号的APPID
            'mch_id'        =>  env('WEIXIN_MCH_ID'),          // 商户ID
            'nonce_str'     => Str::random(16),             // 随机字符串
            'sign_type'     => 'MD5',
            'body'          => '测试订单-'.mt_rand(1111,9999) . Str::random(6),
            'out_trade_no'  => $order_id,                       //本地订单号
            'total_fee'     => $total_fee,
            'spbill_create_ip'  => $_SERVER['REMOTE_ADDR'],     //客户端IP
            'notify_url'    => $this->notify_url,        //通知回调地址
            'trade_type'    => 'NATIVE'                         // 交易类型
        ];
//        print_r($order_info);die;
        $this->values = [];
        $this->values = $order_info;
        $this->SetSign();
        $xml = $this->ToXml();      //将数组转换为XML
//        print_r($order_info);die;
        $rs = $this->postXmlCurl($xml, $this->weixin_unifiedorder_url,$useCert = false,$second = 30);
//        var_dump($rs);die;
        $data =  simplexml_load_string($rs);
//        var_dump($data);die;
        //将 code_url 返回给前端，前端生成 支付二维码
        $data = [
            'code_url'  => $data->code_url
        ];
        return view('weixin.test',$data);
    }
    protected function ToXml()
    {
        if(!is_array($this->values)
            || count($this->values) <= 0)
        {
            die("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    private  function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//		if($useCert == true){
//			//设置证书
//			//使用证书：cert 与 key 分别属于两个.pem文件
//			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
//			curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
//			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
//			curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
//		}
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            die("curl出错，错误码:$error");
        }
    }
    public function SetSign()
    {
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }
    private function MakeSign()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".env('WEIXIN_MCH_KEY');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    /**
     * 格式化参数格式化成url参数
     */
    protected function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     * 微信支付回调
     */
    public function notify()
    {
        $data = file_get_contents("php://input");
        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_pay_notice.log',$log_str,FILE_APPEND);
        $xml = simplexml_load_string($data);
        if($xml->result_code=='SUCCESS' && $xml->return_code=='SUCCESS'){      //微信支付成功回调
            //验证签名
            $sign = true;
            if($sign){       //签名验证成功
                //TODO 逻辑处理  订单状态更新
            }else{
                //TODO 验签失败
                echo '验签失败，IP: '.$_SERVER['REMOTE_ADDR'];
                // TODO 记录日志
            }
        }
        $response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo $response;
    }

    /**
     * 用户授权
     */
    public function give(){
        $scope = "snsapi_userinfo";
        $url = urlencode("http://1809lancong.comcto.com/code");
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.env('WX_APPID').'&redirect_uri='.$url.'&response_type=code&scope='.$scope.'&state=STATE#wechat_redirect';
        return view('weixin.give',['url'=>$url]);
    }

    public function code(){
        $code = $_GET['code'];
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'&code='.$code.'&grant_type=authorization_code';
        $responser = json_decode(file_get_contents($url),true);

        $accessToken = $responser['access_token'];
        $openid = $responser['openid'];

        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$accessToken&openid=$openid&lang=zh_CN";
        $responser = json_decode(file_get_contents($url),true);

        $openid = $responser['openid'];
        $arr = DB::table('give')->where('openid',$openid)->first();

        if($arr){
            echo "欢迎回来";
        }else{
            $data = [
                'openid'=>$responser['openid'],
                'user_name'=>$responser['nickname'],
                'sex'=>$responser['sex']
            ];
            DB::table('give')->insert($data);
            echo "欢迎";
        }


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
}
?>
