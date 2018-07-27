<?php

namespace App\Http\Controllers;

use App\Model\UserModel;
use EasyWeChat\Factory;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cookie;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var \EasyWeChat\OfficialAccount\Application $wxapp
     */
    protected $wxapp = null;

    /**
     * 微信用户信息
     * @var array
     */
    protected $user = ['id' => 0];

    public function __construct()
    {
        $config = [
            'app_id' => 'wx11fe145bfca2b25e',
            'secret' => 'b8fdd5d132a3cc9c550ba40d001c6907',

            //网页Oauth授权
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => '/oauth-callback',
            ],

            //返回的数据类型
            'response_type' => 'array',

            'token'   => 'weiphp',// Token
//            'aes_key' => 'j87GWXELylXpJuxVGSZrvIm4jqEfYFZHAjm2A56nqAz',// EncodingAESKey，兼容与安全模式下请一定要填写！！！

            'log' => [
                'level' => 'debug',
                'file' => storage_path() . '/wechat.log',
            ],
        ];
        $this->wxapp = Factory::officialAccount($config);
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

        //信息存入Cookie
        Cookie::queue('wechat_user', $user, 60);

        //用户放入对象
        $userInDb = (new UserModel())->getUserinfoByOpenid($user['openid']);
        $this->user = array_merge($user, $userInDb);

        return redirect($request->cookie("target_url", "/"));
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return array
     */
    public function getUserinfo(Request $request) {
        $user = $request->cookie("wechat_user");
        if (empty($user)) {
            return ['id' => 0];
        }
        $userInDb = (new UserModel())->getUserinfoByOpenid($user['openid']);
        return array_merge($user, $userInDb);
    }
}
