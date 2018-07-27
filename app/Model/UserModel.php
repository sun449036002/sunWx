<?php
namespace App\Model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/22
 * Time: 20:18
 */
class UserModel extends BaseModel
{
    protected $table = "user";

    /**
     * 根据 openid 获取用户信息
     * @param $openid
     * @return mixed
     */
    public function getUserinfoByOpenid($openid = '') {
        $row = $this->getOne(['id'], ['openid' => $openid]);
        if (!empty($row)) {
            return $row->toArray();
        }
        return [];
    }

}