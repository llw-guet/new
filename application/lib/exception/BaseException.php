<?php


namespace app\lib\exception;

//需要返回到用户端异常的基类
use think\Exception;
use Throwable;

class BaseException extends Exception {
    //HTTP状态码
    public $code = 400;

    //错误具体信息
    public $msg = '参数错误';

    //自定义的错误码
    public $errCode = 10000;

    //自定义构造函数
    public function __construct($params = []) {
        if(!is_array($params)){
            return; //如果传入的不是一个数组，则使用默认值
        }
        if(array_key_exists('code',$params)){
            $this->code = $params['code'];
        }
        if(array_key_exists('msg',$params)){
            $this->msg = $params['msg'];
        }
        if(array_key_exists('errCode',$params)){
            $this->errCode = $params['errCode'];
        }
    }
}