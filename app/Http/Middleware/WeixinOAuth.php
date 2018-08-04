<?php

namespace App\Http\Middleware;

use App\Consts\CookieConst;
use Closure;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Cookie;

class WeixinOAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //未授权用户
        if (empty(Cookie::get(CookieConst::WECHAT_USER))) {

            Cookie::queue('target_url', $request->getRequestUri(), 2);

            $wxapp = Factory::officialAccount(getWxConfig());
            return $wxapp->oauth->redirect();
        }

        return $next($request);
    }
}
