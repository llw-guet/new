<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Category as CategoryModel;

class Category extends BaseController
{
    //获取所有分类
    public function getAllCategories(){
        $categories = CategoryModel::getAllCategories();
        if($categories->isEmpty()){
            throw new CategoryException();
        }
        return $categories;
    }
}
