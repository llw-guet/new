<?php


namespace app\lib\exception;


class ProductException extends BaseException {
    //HTTP状态码
    public $code = 404;

    //错误具体信息
    public $msg = '找不到指定的商品，请检查参数';

    //自定义的错误码
    public $errCode = 20000;
}