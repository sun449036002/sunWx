<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 17:11
 */

namespace App\Http\Controllers;

use EasyWeChat\Factory;


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
    public function api() {
        return $this->wxapp->server->serve();
    }

    //消息 以及事件
    public function server() {
        $this->wxapp->server->push(function($message){
            return 'hello world sun' . json_encode($message, JSON_UNESCAPED_UNICODE);
        });

        return $this->wxapp->server->serve();
    }
}