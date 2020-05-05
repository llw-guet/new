<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\Theme as ThemeModel;
use app\api\validate\IDCollection;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\ThemeException;

class Theme extends BaseController {
    public function getSimpleList($ids = ''){
        $check = (new IDCollection())->goCheck();
        $themes = ThemeModel::getThemesByIds($ids);
        if($themes->isEmpty()){
            throw new ThemeException();
        }
        return $themes;
    }

    public function getComplexOne($id){
        (new IDMustBePositiveInt())->goCheck();
        $theme = ThemeModel::getThemeWithProducts($id);
        if(!$theme){
            throw new ThemeException();
        }
        return $theme;
    }
}