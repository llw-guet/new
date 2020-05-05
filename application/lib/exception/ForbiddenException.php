<?php


namespace app\lib\exception;


class ForbiddenException extends BaseException {
    public $code = 403;
    public $msg = '您没有访问权限';
    public $errCode = 10001;
}