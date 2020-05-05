<?php


namespace app\api\validate;


class IDCollection extends BaseValidate {
    protected $rule = [
        'ids' => 'require|checkIDs'
    ];

    protected $message = [
        'ids' => 'ids参数必须是以逗号分隔的多个正整数'
    ];

    protected function checkIDs($value){
        $value = explode(',', $value);
        if(empty($value)){
            return false;
        }else{
            foreach ($value as $id) {
                if(!$this->isPositiveInt($id)){
                    return false;
                }
            }
            return true;
        }
    }
}