<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 17:11
 */

namespace App\Http\Controllers;

use App\Consts\CacheConst;
use App\Consts\WxConst;
use App\Model\UserModel;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
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

                    $qr_scene_data=[];
                    $qr_scene_str = $userinfo['qr_scene_str'];
                    foreach (explode(";", $qr_scene_str) as $item) {
                        $arr = explode("=", $item);
                        if (count($arr) > 1) {
                            $qr_scene_data[$arr[0]] = $arr[1];
                        }
                    }
                    Log::info('qr_scene_str', [$qr_scene_str, $qr_scene_data]);

                    $adminId = $qr_scene_data['aid'] ?? 0;
                    $newId = $userModel->insert([
                        'type' => 1,
                        'uri' => generateUri(16),
                        'username' => $userinfo['nickname'] ?? "",
                        'avatar_url' => $avatar_url ?: ($userinfo['headimgurl'] ?? ""),
                        'openid' => $message['FromUserName'],
                        'user_json' => json_encode($userinfo, JSON_UNESCAPED_UNICODE) ?? "",
                        'admin_id' => $adminId,//推广员后台账户ID
                        'is_subscribe' => 1,
                        'subscribe_time' => time(),
                    ]);
                    if ($newId) {
                        //累计今日关注用户数
                        $cacheKey = sprintf(CacheConst::TODAY_SUBSCRIBE_NUM, date("Ymd"));
                        Redis::incr($cacheKey);

                        //发送相应的图文消息
                        $subscribeType = $qr_scene_data['r'] ?? "";
                        switch ($subscribeType) {
                            case 'receive':
                                $news = new News([
                                    new NewsItem([
                                        'title'       => '现金大礼包待您领取~',
                                        'description' => '',
                                        'url'         => env('APP_URL') . "/cash-red-pack",
                                        'image'       => asset("imgs/big-red-pack.png"),
                                    ])
                                ]);
                                break;
                            case 'help':
                                $news = new News([
                                    new NewsItem([
                                        'title'       => '点击此处为好友助力~',
                                        'description' => '',
                                        'url'         => env('APP_URL') . "/assistance-page?redPackId=" . $qr_scene_data['rid'] ?? 0,
                                        'image'       => asset("imgs/big-red-pack.png"),
                                    ])
                                ]);
                                break;
                            case 'accept':
                                $news = new News([
                                    new NewsItem([
                                        'title'       => '您朋友赠送给您一个大礼包~',
                                        'description' => '',
                                        'url'         => env('APP_URL') . "/",
                                        'image'       => asset("imgs/big-red-pack.png"),
                                    ])
                                ]);
                                break;
                            default:
                                break;
                        }

                        if (!empty($news)) {
                            //发送图文消息
                            $this->wxapp->customer_service->message($news)->to($message['FromUserName'])->send();
                            return '';
                        }
                    }
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
    /**
     * 获取并显示关注二维码图片 (绑定了推广员的账号ID的二维码图片)
     *  adminId 后台管理员ID
     *  fromUserId 来源红包用户ID
     *  type 关注类型 receive 领取红包，help 助力, accept 接受赠送红包
     */
    public function getQrCode(Request $request) {
        $adminId = $request->get("adminId", 0);
        $redPackId = $request->get("redPackId", 0);
        $fromUserId = $request->get("fromUserId");
        $type = $request->get("r", "receive");

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
        $expiredTime = 3 * 86400;
        if (empty($redPackId)) {
            $expiredTime = 29 * 86400;
        }
        $cacheKey = sprintf(WxConst::QR_CODE_FOR_ADMIN_USER, $adminId, $redPackId);
        $qrCodeUrl = Redis::get($cacheKey);
        if (empty($qrCodeUrl)) {
            //场景值
            $sceneStr = "aid=" . $adminId . ";r=" . $type;
            if (!empty($redPackId)) {
                $sceneStr .=";rid=" . $redPackId;
            }
            $result = $this->wxapp->qrcode->temporary($sceneStr, $expiredTime);
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