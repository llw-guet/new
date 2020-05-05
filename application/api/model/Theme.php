<?php

namespace app\api\model;

use think\Model;

/**
 * 一对一关系总结
 *  belongsTo(), 当前模型 拥有 外键时使用该方法表示一对一关系, foreign key 表示当前模型的外键，key表示关联模型的主键
 *  hasOne(), 当前模型 不含 外键时使用该方法表示一对一关系
 *
 * 多对多总结
 *  belongsToMany('关联模型名','中间表名','中间表关联模型关联键名','中间表当前模型关联键名');
 *
 * Class Theme
 * @package app\api\modely
 */
class Theme extends BaseModel
{
    protected $hidden = ['delete_time', 'update_time','topic_img_id','head_img_id'];

    public function topicImg(){
        return $this->belongsTo('image','topic_img_id','id');
    }
    
    public function headImg(){
        return $this->belongsTo('image','head_img_id','id');
    }
    
    public function products(){
        return $this->belongsToMany('product','theme_product','product_id','theme_id');
    }

    public static function getThemesByIds($ids){
        $ids = explode(',', $ids);
        return self::with(['topicImg', 'headImg'])->select($ids);
    }

    public static function getThemeWithProducts($id){
        return self::with(['topicImg', 'headImg','products'])->find($id);
    }
}
