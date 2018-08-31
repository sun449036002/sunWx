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

                    //场景数据
                    $sceneDataCacheKey = sprintf(CacheConst::QR_CODE_SCENE_DATA, $message['FromUserName']);
                    $qr_scene_data = json_decode(Redis::get($sceneDataCacheKey), true);
                    Log::info('qr_scene_str', [$qr_scene_data]);

                    $fromUserId = $qr_scene_data['fromUserId'] ?? 0;
                    $adminId = $qr_scene_data['adminId'] ?? 0;
                    $newId = $userModel->insert([
                        'type' => 1,
                        'uri' => generateUri(16),
                        'username' => $userinfo['nickname'] ?? "",
                        'avatar_url' => $avatar_url ?: ($userinfo['headimgurl'] ?? ""),
                        'openid' => $message['FromUserName'],
                        'user_json' => json_encode($userinfo, JSON_UNESCAPED_UNICODE) ?? "",
                        'from_user_id' => $fromUserId,//通过哪个用户ID关注的
                        'admin_id' => $adminId,//推广员后台账户ID
                        'is_subscribe' => 1,
                        'subscribe_time' => time(),
                    ]);
                    if ($newId) {
                        //累计今日关注用户数
                        $cacheKey = sprintf(CacheConst::TODAY_SUBSCRIBE_NUM, date("Ymd"));
                        Redis::incr($cacheKey);
                        Redis::expire($cacheKey, 86400);

                        $redPackId = $qr_scene_data['redPackId'] ?? 0;

                        //插入助力数据
                        Redis::rpush(sprintf(CacheConst::RED_PACK_ASSISTANCE_LIST, $redPackId), 2 * 86400,
                            json_encode(['userId' => $newId], JSON_UNESCAPED_UNICODE)
                        );

                        //发送相应的图文消息
                        $subscribeType = $qr_scene_data['type'] ?? "";
                        switch ($subscribeType) {
                            case 'help':
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
                            /*case 'help':
                                $news = new News([
                                    new NewsItem([
                                        'title'       => '点击此处为好友助力~',
                                        'description' => '',
                                        'url'         => env('APP_URL') . "/assistance-page?redPackId=" . $redPackId,
                                        'image'       => asset("imgs/big-red-pack.png"),
                                    ])
                                ]);
                                break;*/
                            case 'accept':
                                $cacheKey = sprintf(CacheConst::MY_TEMP_TICKET, $message['FromUserName'], $redPackId);
                                $ticket = Redis::get($cacheKey);
                                $news = new News([
                                    new NewsItem([
                                        'title'       => '您朋友赠送给您一个大礼包~',
                                        'description' => '',
                                        'url'         => env('APP_URL') . "/index/grantRedPack?redPackId=" . ($redPackId) . "&ticket=" . $ticket,
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
        $expiredTime = 29 * 86400;
        $cacheKey = sprintf(CacheConst::QR_CODE_FOR_ADMIN_USER, $adminId);
        $qrCodeUrl = Redis::get($cacheKey);
        if (empty($qrCodeUrl)) {
            $result = $this->wxapp->qrcode->temporary('', $expiredTime);
            $ticket = $result['ticket'] ?? '';
            if (!empty($ticket)) {
                $qrCodeUrl = $this->wxapp->qrcode->url($ticket);
                $ttl = $result['expire_seconds'] - 3600;//比腾讯提前一小时过期
                Redis::setex($cacheKey, $ttl, $qrCodeUrl);
            }
        }

        //将场景值转入缓存
        $sceneData = [
            'type' => $type,
            'adminId' => $adminId,
            'fromUserId' => $fromUserId,
            'redPackId' => $redPackId
        ];
        $sceneDataCacheKey = sprintf(CacheConst::QR_CODE_SCENE_DATA, $this->user['openid']);
        Redis::setex($sceneDataCacheKey, 86400, json_encode($sceneData, JSON_UNESCAPED_UNICODE));

        return ResultClientJson(0, 'ok', ['qrCodeUrl' => $qrCodeUrl]);
    }
}