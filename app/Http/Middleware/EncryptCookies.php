<?php

namespace App\Http\Middleware;

use App\Consts\CookieConst;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        //
        CookieConst::WECHAT_USER,
    ];
}
