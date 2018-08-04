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
     * @param $openid || $id
     * @return mixed
     */
    public function getUserinfoByOpenid($openid = '') {
        $fields = ['id', "is_subscribe", "type", "uri", "avatar_url"];
        if (is_numeric($openid)) {
            $id = $openid;
            $row = $this->getOne($fields, ['id' => $id]);
        } else {
            $row = $this->getOne($fields, ['openid' => $openid]);
        }
        $user = [];
        if (!empty($row)) {
            foreach ($row as $key => $val) {
                $user[$key] = $val;
            }
        }
        return $user;
    }

}