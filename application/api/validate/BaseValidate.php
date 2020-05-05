<?php


namespace app\api\validate;


use app\lib\exception\ParameterException;
use think\Request;
use think\Validate;

class BaseValidate extends Validate {
    //自定义验证规则
    protected function isPositiveInt($value,$rule='',$data='',$filed=''){
        if(is_numeric($value) && is_int($value + 0) && ($value + 0) > 0){
            return true;
        }else{
            return false;
        }
    }

    protected function isNotEmpty($value,$rule='',$data='',$filed=''){
        if(empty($value)){
            return false;
        }else{
            return true;
        }
    }

    protected function isMobile($value){
        $rule = '^1(3|4|5|7|8)[0-9|]\d{8}$^';
        $result = preg_match($rule, $value);
        if($result){
            return true;
        }else {
            return false;
        }
    }

    public function goCheck(){
        //获取http请求传入的参数
        //对这些参数进行校验
        $request = Request::instance();
        $params = $request->param();

        $result = $this->batch()->check($params);
        if(!$result){
            throw new ParameterException(['msg' => $this->error]);
        }else{
            return true;
        }
    }

    //根据规则来获取数据
    public function getDataByRule($data) {
        if(array_key_exists('user_id', $data) || array_key_exists('uid', $data)){
            throw new ParameterException([
                'msg' => '参数中包含有非法的参数名 user_id 或 uid'
            ]);
        }
        $newData = [];
        foreach ($this->rule as $key => $value) {   //根据定义的规则来获取
            $newData[$key] = $data[$key];
        }
        return $newData;
    }
}