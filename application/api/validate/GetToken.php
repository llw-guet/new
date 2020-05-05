<?php


namespace app\api\validate;


class GetToken extends BaseValidate {
    protected $rule = [
        'code' => 'require|isNotEmpty'
    ];

    protected $message = [
        'code' => '需要传入从微信服务器获取的code才能获取token!'
    ];
}