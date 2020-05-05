<?php


namespace app\api\validate;


class PagingParameter extends BaseValidate {
    protected $rule = [
        'page' => 'require|isPositiveInt',
        'size' => 'require|isPositiveInt'
    ];

    protected $message = [
        'page' => '分页参数必须为正整数',
        'size' => '分页参数必须为正整数'
    ];

}