<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 17:11
 */

namespace App\Http\Controllers;

use App\Model\UserModel;
use App\User;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Monolog\Logger;


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
//            'aes_key' => 'j87GWXELylXpJuxVGSZrvIm4jqEfYFZHAjm2A56nqAz',// EncodingAESKey，兼容与安全模式下请一定要填写！！！

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
            (new Logger('wxMessageLog'))->info("微信消息", $message);
            switch ($message['MsgType']) {
                case 'event':
                    return '收到事件消息';
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }
        });

        return $this->wxapp->server->serve();
    }

    //处理事件
    private function handleEvent($message) {

    }

    //处理文本消息
    private function handleText($message) {

    }

    //处理图片消息
    private function handleImage($message) {

    }

    //处理地理位置
    private function handleLocation($message) {

    }
}