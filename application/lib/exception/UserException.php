<?php


namespace app\lib\exception;


class UserException extends BaseException {
    public $code = 404;
    public $msg = '当前用户不存在';
    public $errCode = 60000;
}