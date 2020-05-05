<?php


namespace app\api\validate;


class AddressNew extends BaseValidate {
    protected $rule = [
        'name' => 'require|isNotEmpty',
        'mobile' => 'require|isNotEmpty',
        'province' => 'require|isNotEmpty',
        'city' => 'require|isNotEmpty',
        'county' => 'require|isNotEmpty',
        'detail' => 'require|isNotEmpty',
    ];
}