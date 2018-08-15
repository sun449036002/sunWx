<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 17:11
 */

namespace App\Http\Controllers;

use App\Consts\WxConst;
use App\Model\UserModel;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;


class wxController extends Controller
{
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
        switch (strtolower($message['Event'])) {
            case 'location':
                //地址位置上报
                break;
            case 'subscribe':
                //关注
                $where['openid'] = $message['FromUserName'];
                $userModel = new UserModel();
                $user = $userModel->getOne("id", $where);
                if (!empty($user)) {
                    $userModel->updateData(['is_subscribe' => 1], ['id' => $user->id]);
                    return '欢迎回来';
                } else {
                    $userinfo = $this->wxapp->user->get($message['FromUserName']);

                    //微信头像保存到本地
                    $avatar_url = "";
                    if (!empty($userinfo['headimgurl'])) {
                        $saleFilePath = "/images/wxUserHead/" . date("Ymd/") . date("His_") . mt_rand(10000000, 99999999) . ".jpeg";
                        $client = new Client(['verify' => false]);  //忽略SSL错误
                        $data = $client->request('get', $userinfo['headimgurl'])->getBody()->getContents();
                        $ok = Storage::put($saleFilePath, $data);
                        if ($ok) {
                            $avatar_url = $saleFilePath;
                        }
                    }
                    $newId = $userModel->insert([
                        'type' => 1,
                        'uri' => generateUri(16),
                        'username' => $userinfo['nickname'] ?? "",
                        'avatar_url' => $avatar_url ?: ($userinfo['headimgurl'] ?? ""),
                        'openid' => $message['FromUserName'],
                        'user_json' => json_encode($userinfo, JSON_UNESCAPED_UNICODE) ?? "",
                        'is_subscribe' => 1,
                    ]);
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

    //获取关注的二维码图片
    public function getQrCode(Request $request) {
        $adminId = $request->get("adminId", 0);
        $fromUserId = $request->get("fromUserId");
        if (empty($adminId)) {
            $userModel = new UserModel();
            $u = $userModel->getUserinfoByOpenid($fromUserId);
            if (!empty($u['admin_id'])) {
                $adminId = $u['admin_id'];
            }
        }

        if (empty($adminId)) {
            Log::warning('[getQrCode]', ['adminId' => $adminId, 'msg' => '当前用户获取的关注二维码未指定推广员']);
        }

        //获取缓存中的二维码图片
        $expiredTime = 29 * 86400;
        $cacheKey = sprintf(WxConst::QR_CODE_FOR_ADMIN_USER, $adminId);
        $qrCodeUrl = Redis::get($cacheKey);
        if (empty($qrCodeUrl)) {
            $result = $this->wxapp->qrcode->temporary(json_encode(['adminId' => $adminId], JSON_UNESCAPED_UNICODE), $expiredTime);
            $ticket = $result['ticket'] ?? '';
            if (!empty($ticket)) {
                $qrCodeUrl = $this->wxapp->qrcode->url($ticket);
                $ttl = $result['expire_seconds'] - 3600;//比腾讯提前一小时过期
                Redis::setex($cacheKey, $ttl, $qrCodeUrl);
            }
        }

        return ResultClientJson(0, 'ok', ['qrCodeUrl' => $qrCodeUrl]);
    }
}