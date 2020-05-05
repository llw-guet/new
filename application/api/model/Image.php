<?php

namespace app\api\model;

use think\Model;

class Image extends BaseModel
{
    protected $hidden = ['id','from','delete_time','update_time'];

    /**
     * @param $value   获取器获取到的字段值
     * @param $data     获取器获得的记录，数组类型
     * @return string    对字段值进行加工后返回的值
     */
    public function getUrlAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }
}
