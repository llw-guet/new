<?php

namespace app\api\model;

use think\Db;
use think\Model;

class Banner extends BaseModel
{
    //设置隐藏字段
    protected $hidden = ['delete_time','update_time'];

    //一对多映射
    public function items(){
        return $this->hasMany('BannerItem', 'banner_id', 'id');
    }

    public static function getBannerById($id){
        //方式1： 原生sql
        // return Db::query('SELECT * FROM banner_item WHERE banner_id = ?', [$id]);

        //方式2： 查询构建器
        // return Db::table('banner_item')->where('banner_id', '=', $id)->select();

        //方式2.1 高级查询：闭包查询
        // return Db::table('banner_item')->where(
        //     function ($query) use ($id){
        //         $query->where('banner_id','=',$id);
        // })->select();
        return self::with(['items','items.img'])->find($id);
    }
}
