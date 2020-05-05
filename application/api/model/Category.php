<?php

namespace app\api\model;

use think\Model;

class Category extends BaseModel
{
    protected $hidden = ['topic_img_id','delete_time','update_time'];

    //与Image模型一对一关联
    public function img(){
        return $this->belongsTo('image','topic_img_id','id');
    }

    //获取所有的分类
    public static function getAllCategories(){
        return self::all([],'img');
    }
}
