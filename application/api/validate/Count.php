<?php


namespace app\api\validate;


class Count extends BaseValidate {
    protected $rule = [
        'count' => 'isPositiveInt|between: 1,20'
    ];

    protected $message = [
        'count' => '传入的参数必须是1~20中的正整数'
    ];
}