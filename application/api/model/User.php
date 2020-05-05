<?php


namespace app\api\model;


class User extends BaseModel {
    protected $hidden = ['openid','create_time','update_time','delete_time'];

    public function address(){
        //当前模型不含外键，所以使用 hasOne 而不是 belongsTo
        return $this->hasOne('user_address', 'user_id', 'id');
    }

    public static function getUserByOpenId($openid) {
        return self::where('openid', '=', $openid)->find();
    }

    public static function addUser($openid){
        return self::create([
            'openid' => $openid
        ]);
    }
}