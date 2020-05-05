<?php


namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

class Token {
    //获取token
    public static function generateToken(){
        //用三组字符串进行md5加密
        $randStr = getRandChars(32);
        $timestamp = $_SERVER['REQUEST_TIME'];
        //salt 盐
        $salt = config('secure.token_salt');
        return md5($randStr.$timestamp.$salt);
    }

    /**
     * 根据当前客户端传入的token，找到缓存中对应的键值对， 再根据传入的 key 获取对应 token 的值
     * @param $key
     * @return mixed
     * @throws Exception
     * @throws TokenException
     */
    public static function getCurrentTokenVar($key) {
        $token = Request::instance()->header('token');    //约定用户端使用 header传递token，而不是用requestBody
        $vars = Cache::get($token);    //根据token获取缓存数据
        if(!$vars){  //如果查询不到对应的值， 则表示 token 已经失效
            throw new TokenException();
        }else {
            if(!is_array($vars)){   //TP5提供的缓存， 键值对以字符串形式保存，其他缓存机制中的值可能以数组形式存在
                $vars = json_decode($vars, true);
            }
            if(!array_key_exists($key, $vars)){
                throw new Exception('尝试获取的token变量并不存在');
            }else{
                return $vars[$key];
            }
        }
    }

    public static function getCurrentUid() {
        return self::getCurrentTokenVar('uid');
    }

    //需要基础权限
    public static function needPrimaryScope() {
        //根据用户携带token判断当前用户是否具有访问权限
        $scope = self::getCurrentTokenVar('scope');
        if($scope >= ScopeEnum::USER){
            return true;
        }else{
            throw new ForbiddenException();
        }
    }

    //非管理员才能访问的权限
    public static function needExclusiveScope() {
        $scope = self::getCurrentTokenVar('scope');
        if($scope == ScopeEnum::USER){
            return true;
        }else{
            throw new ForbiddenException();
        }
    }

    public static function isValidOperate($checkedUID) {
        $currentUid = self::getCurrentUid();
        if(!$checkedUID){
            throw new Exception('检查UID时必须传入一个非空UID');
        }
        if($currentUid != $checkedUID){
            return false;
        }else{
            return true;
        }
    }

    //验证是否为一个有效token
    public static function verifyToken($token) {
        $exist = Cache::get($token);
        return $exist ? true : false;
    }
}