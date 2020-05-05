<?php


namespace app\lib\exception;


class CategoryException extends BaseException {
    //HTTP状态码
    public $code = 404;

    //错误具体信息
    public $msg = '找不到指定的分类';

    //自定义的错误码
    public $errCode = 50000;
}