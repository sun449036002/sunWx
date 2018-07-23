<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 17:11
 */

namespace App\Http\Controllers;

use App\Model\UserModel;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


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
            Log::info("message", $message);
            switch ($message['MsgType']) {
                case 'event':
                    return self::handleEvent($message);
                    break;
                case 'text':
                    return self::handleText($message);
                    break;
                case 'image':
                    return self::handleImage($message);
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return self::handleLocation($message);
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
        Log::warning("sub", [strtolower($message['Event'])]);
        switch (strtolower($message['Event'])) {
            case 'location':
                //地址位置上报
                break;
            case 'subscribe':
                //关注
                $where['openid'] = $message['FromUserName'];
                $userModel = new UserModel();
                $user = $userModel->getOne("id", $where);
                Log::info("sub", [$user, !empty($user)]);
                if (!empty($user)) {
                    $userModel->updateData(['is_subscribe' => 1], ['id' => $user->id]);
                    return '欢迎回来';
                } else {
                    Log::info("23132131999");
                    $newId = $userModel->insert([
                        'type' => 1,
                        'uri' => generateUri(16),
                        'openid' => $message['FromUserName'],
                        'is_subscribe' => 1,
                    ]);
                    Log::info("sub", [$newId]);
                }
                return '欢迎加入我们~!';
                break;
            case 'unsubscribe':
                //取消关注
                $where['openid'] = $message['FromUserName'];
                (new UserModel())->updateData(['is_subscribe' => 0], $where);
                break;
        }
        return '';
    }

    //处理文本消息
    private function handleText($message) {
        return $message['Content'];
    }

    //处理图片消息
    private function handleImage($message) {
        return '';
    }

    //处理地理位置
    private function handleLocation($message) {
        return '';
    }
}