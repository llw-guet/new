<?php


namespace app\api\validate;

class IDMustBePositiveInt extends BaseValidate {
    // 当前验证的规则
    protected $rule = [
        'id' => 'require|isPositiveInt',
    ];

    // 验证提示信息
    protected $message = [
        'id' => 'id必须是正整数'
    ];
}