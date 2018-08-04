<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 20:00
 */

namespace App\Model;


class RedPackModel extends BaseModel
{
    protected $table = "redPack";

    //获取当前用户未完成的且未过期的红包
    public function getUnComplete($userId) {
        $row = $this->getOne(['id'], ['userId' => $userId, 'status' => 0, ['expiredTime', '>', time()]]);
        return $row;
    }
}