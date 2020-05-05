<?php


namespace app\api\model;


class ProductImage extends BaseModel {
    protected $hidden = ['delete_time', 'img_id','product_id','id'];

    public function imgUrl(){
        return $this->belongsTo('image','img_id','id');
    }
}