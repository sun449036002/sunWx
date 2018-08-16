<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/16
 * Time: 14:50
 */

namespace App\Logic;


use App\Consts\CookieConst;
use App\Model\UserModel;
use Illuminate\Support\Facades\Cookie;

class BaseLogic
{
    public $user = ['id' => 0];

    public function __construct()
    {
        $this->user = $this->getUserinfo();
    }

    /**
     * 获取用户信息
     * @return array
     */
    private function getUserinfo() {
        $defaultUser = ['id' => 0];
        $user = Cookie::get(CookieConst::WECHAT_USER);
        if (empty($user)) {
            return $defaultUser;
        }
        if (is_string($user)) {
            $user = json_decode($user, true);
            if (empty($user)) {
                return $defaultUser;
            }
        }

        $userInDb = (new UserModel())->getUserinfoByOpenid($user['openid']);
        if (!empty($userInDb)) {
            return array_merge($user, $userInDb);
        }
        return $defaultUser;
    }

}