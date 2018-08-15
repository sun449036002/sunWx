<?php

namespace App\Http\Controllers;

use App\Consts\CookieConst;
use App\Model\UserModel;
use EasyWeChat\Factory;
use function foo\func;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

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


    public function __construct()
    {
        $this->middleware(function($request, $next){
            //获取用户信息
            $this->user = $this->getUserinfo();

            //wxapp对象
            $this->wxapp = Factory::officialAccount(getWxConfig());

            $this->pageData['user'] = $this->user;
            $this->pageData['wxapp'] = $this->wxapp;
            $this->pageData['adminId'] = $request->get("adminId", 0);

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
        Log::info('user in db',[$userInDb]);
        if (!empty($userInDb)) {
            $user = array_merge($user, $userInDb);
        }
        return $user;
    }
}
