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

    public $timestamps = false;

    /**
     * 根据 openid 获取用户信息
     * @param $openid || $id
     * @return mixed
     */
    public function getUserinfoByOpenid($openid = '') {
        $fields = ['id',"username", "is_subscribe", "type", "uri", "avatar_url", "admin_id", "balance"];
        if (is_numeric($openid)) {
            $id = $openid;
            $row = $this->getOne($fields, ['id' => $id]);
        } else {
            $row = $this->getOne($fields, ['openid' => $openid]);
        }
        $user = [];
        if (!empty($row)) {
            foreach ($row as $key => $val) {
                if ($key == 'avatar_url') {
                    $user[$key] = headImgUrl($val);
                } else {
                    $user[$key] = $val;
                }
            }
        }
        return $user;
    }

}