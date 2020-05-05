<?php


namespace app\lib\exception;


class TokenException extends BaseException {
    //HTTP状态码
    public $code = 401;

    //错误具体信息
    public $msg = 'TOKEN已过期或TOKEN无效';

    //自定义的错误码
    public $errCode = 10001;
}