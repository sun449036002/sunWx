<?php

namespace App\Http\Controllers;

use App\Consts\CacheConst;
use App\Consts\CookieConst;
use App\Model\UserModel;
use EasyWeChat\Factory;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redis;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var \EasyWeChat\OfficialAccount\Application $wxapp
     */
    public $wxapp = null;

    /**
     * 模板页数据
     * @var array
     */
    public $pageData = [];

    /**
     * 微信用户信息
     * @var array
     */
    public $user = ['id' => 0];

    //红包各个状态信息
    private $redPackStatusConfig = [
        0 => [
            'status' => '未完成',
            'element-class' => 'warning'
        ],
        1 => [
            'status' => '可使用',
            'element-class' => 'info'
        ],
        2 => [
            'status' => '使用中',
            'element-class' => 'success'
        ],
        3 => [
            'status' => '已使用',
            'element-class' => 'danger'
        ],
        4 => [
            'status' => '作废',
            'element-class' => 'danger'
        ],
    ];


    public function __construct()
    {
        $this->middleware(function($request, $next){
            //获取用户信息
            $this->user = $this->getUserinfo();

            //easy wechat wxapp对象
            $this->wxapp = Factory::officialAccount(getWxConfig());

            $os = get_device_type();
            $this->pageData['os'] = $os;
            $this->pageData['iosClassPrev'] = $os == "ios" ? "ios-" : "";
            $this->pageData['user'] = $this->user;
            $this->pageData['wxapp'] = $this->wxapp;

            //路由中的Admin Id 路由中未带此参数，则默认值为当前用户的admin_id
            $adminId = $request->get("adminId", $this->user['admin_id'] ?? 0);
            if (!empty($this->user['openid'])) {
                $adminIdCacheKey = sprintf(CacheConst::USER_ADMIN_ID, $this->user['openid']);
                if (!empty($adminId)) {
                    Redis::setex($adminIdCacheKey, 86400, $adminId);
                } else {
                    $adminId = intval(Redis::get($adminIdCacheKey));
                }
            }
            $this->pageData['adminId'] = $adminId;

            $this->pageData['redPackStatusConfig'] = $this->redPackStatusConfig;

            return $next($request);
        });
    }

    /**
     * 微信网页授权回调
     * @param Request $request
     * @return null
     */
    public function oauthCallback(Request $request) {
        $oauth = $this->wxapp->oauth;
        $user = $oauth->user()->toArray();
        $user['openid'] = $user['id'];
        unset($user['id']);

        //信息存入Cookie
        Cookie::queue(CookieConst::WECHAT_USER, json_encode($user, JSON_UNESCAPED_UNICODE), 60 * 24);

        return redirect($request->cookie("target_url", "/"));
    }

    /**
     * 获取用户信息
     * @return array
     */
    public function getUserinfo() {
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
        return array_merge($user, $defaultUser);
    }
}
