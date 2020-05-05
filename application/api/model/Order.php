<?php

namespace app\api\model;

use think\Model;

class Order extends BaseModel
{
    protected $hidden = ['user_id','delete_time','update_time'];
    protected $autoWriteTimestamp = true;

    public function getSnapItemsAttr($value){
        if(!$value){
            return null;
        }
        return json_decode($value);
    }

    public function getSnapAddressAttr($value){
        if(!$value){
            return null;
        }
        return json_decode($value);
    }

    public static function getSummaryByUser($uid, $page = 1, $size = 15) {
        return self::where('user_id','=',$uid)
            ->order('create_time','desc')
            ->paginate($size, true, ['page' => $page]);
    }
}
