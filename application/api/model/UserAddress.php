<?php


namespace app\api\model;


class UserAddress extends BaseModel {
    protected $hidden = ['id','user_id','delete_time','update_time'];
    protected $autoWriteTimestamp = true;
}