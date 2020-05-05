<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{
    //对图片url加上前缀
    public function prefixImgUrl($value, $data){
        if($data['from'] == 1){
            return config('setting.img_prefix') . $value;
        }else{
            return $value;
        }
    }
}
