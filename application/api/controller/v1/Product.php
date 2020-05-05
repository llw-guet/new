<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Product as ProductModel;
use app\api\validate\Count;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\ProductException;

class Product extends BaseController
{
    public function getRecent($count = 15){
        (new Count())->goCheck();
        $products = ProductModel::getMostRecent($count);
        //数据集返回类型在 database 配置文件中已改成
        if($products->isEmpty()){
            throw new ProductException();
        }
        return $products->hidden(['summary']);
    }

    public function getAllInCategory($id){
        (new IDMustBePositiveInt())->goCheck();
        $products = ProductModel::getProductsByCategoryID($id);
        if($products->isEmpty()){
            throw new ProductException();
        }
        return $products->hidden(['summary']);
    }

    public function getOne($id) {
        (new IDMustBePositiveInt())->goCheck();
        return ProductModel::getProductDetail($id);
    }
}
