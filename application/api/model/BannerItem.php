<?php

namespace app\api\model;

use think\Model;

class BannerItem extends BaseModel
{
    protected $hidden = ['id','banner_id','img_id','delete_time','update_time'];

    public function img(){
        return $this->belongsTo('Image','img_id','id');
    }
}
