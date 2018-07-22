<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 17:11
 */

namespace App\Http\Controllers;

use EasyWeChat\Factory;
use Illuminate\Http\Request;


class wxController
{
    private $wxapp = null;

    public function __construct()
    {
        $config = [
            'app_id' => 'wx11fe145bfca2b25e',
            'secret' => 'b8fdd5d132a3cc9c550ba40d001c6907',

            'response_type' => 'array',

            'token'   => 'weiphp',// Token
            'aes_key' => 'j87GWXELylXpJuxVGSZrvIm4jqEfYFZHAjm2A56nqAz',// EncodingAESKey，兼容与安全模式下请一定要填写！！！

            'log' => [
                'level' => 'debug',
                'file' => storage_path() . '/wechat.log',
            ],
        ];
        $this->wxapp = Factory::officialAccount($config);
    }
    //服务器配置 验证
    //服务器地址(URL) http://wx.sun.zj.cn/weixin/api
    public function api(Request $request) {

        //接口验证
        $echostr = $request->get("echostr", '');
        if (!empty($echostr)) {
            return $this->wxapp->server->serve();
        }

        //消息 以及事件
        $this->wxapp->server->push(function($message){
            return 'hello world sun';
        });

        return $this->wxapp->server->serve();
    }

    //获取用户列表
    public function users() {
        $users = $this->wxapp->user->list();
        return json_encode($users, JSON_UNESCAPED_UNICODE);
    }
}