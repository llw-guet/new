<?php


namespace app\lib\exception;


class ThemeException extends BaseException {
    //HTTP状态码
    public $code = 404;

    //错误具体信息
    public $msg = '指定的主题不存在, 请检查主题ID';

    //自定义的错误码
    public $errCode = 30000;
}