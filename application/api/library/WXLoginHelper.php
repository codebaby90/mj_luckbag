<?php
namespace app\api\library;

use app\api\library\Auth;
use fast\Http;
use fast\Random;
use think\Config;
use wlt\wxmini\ErrorCode;

/**
 * author:mjrw
 */

class WXLoginHelper
{

    //默认配置
    protected $config = [
        'url' => "https://api.weixin.qq.com/sns/jscode2session", //微信获取session_key接口url
        'appid' => 'wx8ba1e334e79e4919', // APPId
        'appid' => 'wx7db882e0eb8f27d3', // APPId
        'secret' => '84681ce1ddc93a852238bc136848a843', // 秘钥
        'secret' => 'b2d9ba4541dafa590977fe7fd62b8f7b', // 秘钥
        'grant_type' => 'authorization_code', // grant_type，一般情况下固定的
    ];

    protected $token;

    /**
     * 构造函数
     * WXLoginHelper constructor.
     */
    public function __construct()
    {
        //可设置配置项 wxmini, 此配置项为数组。
        if ($wx = Config::get('wx')) {
            $this->config = array_merge($this->config, $wx);
        }
        $token = request()->param('token');
        $this->token = $token ? $token : Config::get('token.token');
    }

    public function checkLogin($code, $rawData, $signature, $encryptedData, $iv)
    {
        /**
         * 4.server调用微信提供的jsoncode2session接口获取openid, session_key, 调用失败应给予客户端反馈
         * , 微信侧返回错误则可判断为恶意请求, 可以不返回. 微信文档链接
         * 这是一个 HTTP 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。其中 session_key 是对用户数据进行加密签名的密钥。
         * 为了自身应用安全，session_key 不应该在网络上传输。
         * 接口地址："https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code"
         */

        $params = [
            'appid' => $this->config['appid'],
            'secret' => $this->config['secret'],
            'js_code' => $code,
            'grant_type' => $this->config['grant_type'],
        ];
        //return $params;

        $res = $this->makeRequest($this->config['url'], $params);
        /*$res = Http::get($this->config['url'], $params);
        $res = json_decode($res,true);*/
        if ($res['code'] !== 200 || !isset($res['result']) || !isset($res['result'])) {
            return ['code' => ErrorCode::$RequestTokenFailed, 'errMsg' => '请求Token失败'];
        }
        $reqData = json_decode($res['result'], true);
        if (!isset($reqData['session_key'])) {
            return ['code' => ErrorCode::$RequestTokenFailed, 'errMsg' => '请求Token失败'];
        }
        $sessionKey = $reqData['session_key'];

        /**
         * 5.server计算signature, 并与小程序传入的signature比较, 校验signature的合法性, 不匹配则返回signature不匹配的错误. 不匹配的场景可判断为恶意请求, 可以不返回.
         * 通过调用接口（如 wx.getUserInfo）获取敏感数据时，接口会同时返回 rawData、signature，其中 signature = sha1( rawData + session_key )
         *
         * 将 signature、rawData、以及用户登录态发送给开发者服务器，开发者在数据库中找到该用户对应的 session-key
         * ，使用相同的算法计算出签名 signature2 ，比对 signature 与 signature2 即可校验数据的可信度。
         */
        //$signature2 = sha1($rawData . $sessionKey);

        //if ($signature2 !== $signature) return ['code'=>ErrorCode::$SignNotMatch, 'message'=>'签名不匹配'];

        /**
         *
         * 6.使用第4步返回的session_key解密encryptData, 将解得的信息与rawData中信息进行比较, 需要完全匹配,
         * 解得的信息中也包括openid, 也需要与第4步返回的openid匹配. 解密失败或不匹配应该返回客户相应错误.
         * （使用官方提供的方法即可）
         */
        /*$pc = new WXBizDataCrypt($this->config['appid'], $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );*/

        /*if ($errCode !== 0) {
        return ['code'=>ErrorCode::$EncryptDataNotMatch, 'message'=>'解密信息错误'];
        }*/

        /**
         * 7.生成第三方3rd_session，用于第三方服务器和小程序之间做登录态校验。为了保证安全性，3rd_session应该满足：
         * a.长度足够长。建议有2^128种组合，即长度为16B
         * b.避免使用srand（当前时间）然后rand()的方法，而是采用操作系统提供的真正随机数机制，比如Linux下面读取/dev/urandom设备
         * c.设置一定有效时间，对于过期的3rd_session视为不合法
         *
         * 以 $session3rd 为key，sessionKey+openId为value，写入memcached
         */
        //$data = json_decode($data, true);
        $data = $reqData;
        //$session3rd = $this->randomFromDev(16);
        $access_token = cache('mg_' . $data['openid']) ? cache('mg_' . $data['openid']) : Random::alpha(32);

        $data['access_token'] = $access_token;
        $data['code'] = 0;
        cache('mg_' . $access_token, $data['openid'], 7200);
        cache('mg_' . $data['openid'], $access_token, 7200);
        $auth = new Auth();
        $rawData = json_decode($rawData, true);
        $rawData['nickName'] = $this->filterEmoji($rawData['nickName']);
        $rawData['token'] = $this->token;
        $auth->register($data['openid'], $rawData);
        unset($data['session_key']);
        unset($data['openid']);
        return $data;
    }

    public function filterEmoji($str)
    {
        $str = preg_replace_callback( '/./u',
                function (array $match) {
                    return strlen($match[0]) >= 4 ? '' : $match[0];
                },
                $str);

         return $str;
     }

    /**
     * 发起http请求
     * @param string $url 访问路径
     * @param array $params 参数，该数组多于1个，表示为POST
     * @param int $expire 请求超时时间
     * @param array $extend 请求伪造包头参数
     * @param string $hostIp HOST的地址
     * @return array    返回的为一个请求状态，一个内容
     */
    public function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
    {
        if (empty($url)) {
            return array('code' => '100');
        }

        $_curl = curl_init();
        $_header = array(
            'Accept-Language: zh-CN',
            'Connection: Keep-Alive',
            'Cache-Control: no-cache',
        );
        // 方便直接访问要设置host的地址
        if (!empty($hostIp)) {
            $urlInfo = parse_url($url);
            if (empty($urlInfo['host'])) {
                $urlInfo['host'] = substr(DOMAIN, 7, -1);
                $url = "http://{$hostIp}{$url}";
            } else {
                $url = str_replace($urlInfo['host'], $hostIp, $url);
            }
            $_header[] = "Host: {$urlInfo['host']}";
        }

        // 只要第二个参数传了值之后，就是POST的
        if (!empty($params)) {
            curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($_curl, CURLOPT_POST, true);
        }

        if (substr($url, 0, 8) == 'https://') {
            curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($_curl, CURLOPT_URL, $url);
        curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($_curl, CURLOPT_USERAGENT, 'API PHP CURL');
        curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);

        if ($expire > 0) {
            curl_setopt($_curl, CURLOPT_TIMEOUT, $expire); // 处理超时时间
            curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire); // 建立连接超时时间
        }

        // 额外的配置
        if (!empty($extend)) {
            curl_setopt_array($_curl, $extend);
        }

        $result['result'] = curl_exec($_curl);
        $result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
        $result['info'] = curl_getinfo($_curl);
        if ($result['result'] === false) {
            $result['result'] = curl_error($_curl);
            $result['code'] = -curl_errno($_curl);
        }

        curl_close($_curl);
        return $result;
    }

    /**
     * 读取/dev/urandom获取随机数
     * @param $len
     * @return mixed|string
     */
    public function randomFromDev($len)
    {
        $fp = @fopen('/dev/urandom', 'rb');
        $result = '';
        if ($fp !== false) {
            $result .= @fread($fp, $len);
            @fclose($fp);
        } else {
            trigger_error('Can not open /dev/urandom.');
        }
        // convert from binary to string
        $result = base64_encode($result);
        // remove none url chars
        $result = strtr($result, '+/', '-_');

        return substr($result, 0, $len);
    }
}
