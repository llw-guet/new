<?php


namespace app\api\validate;


use app\lib\exception\ParameterException;

class OrderPlace extends BaseValidate {
    protected $rule = [
        'products' => 'checkProducts'
    ];

    //自定义验证规则
    protected function checkProducts($value){
        if(empty($value)){
            throw new ParameterException(['msg' => '商品列表参数不能为空']);
        }else{
            foreach ($value as $product) {
                $this->checkProduct($product);
            }
            return true;
        }
    }

    //单独验证传入数组中单个的产品数组
    private function checkProduct(array $product){
        //使用独立验证
        $validate = new BaseValidate([
            'product_id' => 'require|isPositiveInt',
            'count' => 'require|isPositiveInt'
        ]);
        if($validate->check($product)){
            return true;
        }else{
            throw new ParameterException(['msg' => '商品列表参数错误']);
        }
    }
}