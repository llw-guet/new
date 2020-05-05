<?php

namespace app\api\model;

use think\Model;

class Product extends BaseModel
{
    //'pivot'字段在表中不存在，但因为是多对多关联查询，TP5框架会自动把中间表关联键生成出来
    protected $hidden = ['create_time','delete_time','update_time',
        'pivot','from','category_id','img_id'];

    //获取器，对main_img_url字段值进行加工
    public function getMainImgUrlAttr($value,$data){
        return $this->prefixImgUrl($value,$data);
    }

    public function imgs(){
        return $this->hasMany('product_image','product_id','id');
    }

    public function properties() {
        return $this->hasMany('product_property','product_id','id');
    }

    //获取指定条数的最新的记录
    public static function getMostRecent($count){
        return self::order('create_time','desc')->limit($count)->select();
    }

    //根据CategoryId 获取指定分类的产品
    public static function getProductsByCategoryID($CategoryID){
        return self::where('category_id', '=', $CategoryID)->select();
    }

    //根据产品id获取产品的详细信息
    public static function getProductDetail($id) {
        //使用查询构建器进行闭包查询
        return self::with(['imgs' => function($query) {
            $query->with('imgUrl')->order('order','asc');
        }])->with(['properties'])->find($id);
    }
}
