<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Banner as BannerModel;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\BannerMissException;


class Banner extends BaseController
{
    public function getBanner($id){
        (new IDMustBePositiveInt())->goCheck();
        //如果查找不到指定id的banner, 则抛出异常
        $banner = BannerModel::getBannerById($id);
        if(!$banner){
            throw new BannerMissException();
        }
        return $banner;
    }
}
