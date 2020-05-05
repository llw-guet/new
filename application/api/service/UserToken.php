<?php


namespace app\api\service;


use app\api\model\User as UserModel;
use app\lib\enum\ScopeEnum;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use think\Exception;

class UserToken extends Token {
    protected $code;
    protected $wxAppId;
    protected $wxAppSecret;
    protected $wxLoginUrl;

    public function __construct($code) {
        $this->code = $code;
        $this->wxAppId = config('wx.app_id');
        $this->wxAppSecret = config('wx.app_secret');
        $this->wxLoginUrl = sprintf(config('wx.login_url'),
            $this->wxAppId, $this->wxAppSecret, $this->code);
    }

    /**
     * @return string   向服务器端返回  token
     * @throws Exception
     * @throws WeChatException
     */
    public function get() {
        //向微信服务器发送请求
        $result = curl_get($this->wxLoginUrl);
        //json_decode(string, bool)   将一个JSON字符串转换成一个对象或者数组, true则转换成关联数组
        $wxResult = json_decode($result, true);
        //如果获取到的结果为空，则请求失败
        if(empty($wxResult)){
            throw new Exception('获取session_key和openid时异常，微信服务器请求失败！');
        }else{
            //如果请求失败，则返回结果包含errcode字段
            if(array_key_exists('errcode',$wxResult)){
                $this->processLoginError($wxResult);
            }else{
                return $this->grantToken($wxResult);
            }
        }
    }

    private function grantToken($wxResult){
        //获取到结果数组中的 openid, 去本地数据库中查看该openid是否已经存在，
        //如果已经存在，则不处理；如果不存在，则往数据库中添加一条记录
        //生成令牌，保存到缓冲数据中， 然后再把令牌返回到客户端
        //存入缓存键值对：  key=令牌， value=[wxResult,uid,scope] (scope表示该用户的访问权限，为正整数，通常数值越大权限越大)
        $openid = $wxResult['openid'];
        $user = UserModel::getUserByOpenId($openid);
        if($user){
            $uid = $user->id;
        }else{
            $uid = (UserModel::addUser($openid))->id;
        }
        $token = self::generateToken();   //生成token
        $cacheValue = $this->prepareCacheValue($wxResult, $uid);   //生成数据
        $this->saveToCache($token, $cacheValue);    //把数据键值对保存到缓存中
        return $token;  //把token返回
    }

    //以字符串键值对保存到缓存数据中，以缓存过期时间作为用户token过期时间
    private function saveToCache($token, $cacheValue){
        $value = json_encode($cacheValue);  //json_encode(), 传入一个数组或对象, 转化成一个字符串格式的JSON数据
        $expire_in = config('setting.token_expire_in');

        $cache = cache($token, $value, $expire_in);
        if(!$cache){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errCode' => '10005'
            ]);
        }
    }

    //准备好缓存数据，把缓存数据放在一个数组中
    private function prepareCacheValue($wxResult, $uid){
        $cacheValue = $wxResult;
        $cacheValue['uid'] = $uid;
        $cacheValue['scope'] = ScopeEnum::USER;
        return $cacheValue;
    }

    private function processLoginError($wxResult){
        throw new WeChatException([
            'msg' => $wxResult['errmsg'],
            'errCode' => $wxResult['errcode']
        ]);
    }
}