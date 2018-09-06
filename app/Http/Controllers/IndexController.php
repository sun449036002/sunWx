<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 10:28
 */

namespace App\Http\Controllers;

use App\Consts\CacheConst;
use App\Consts\CookieConst;
use App\Consts\StateConst;
use App\Consts\WxConst;
use App\Logic\RedPackLogic;
use App\Logic\RoomSourceLogic;
use App\Model\AdsModel;
use App\Model\RedPackConfigModel;
use App\Model\RedPackModel;
use App\Model\RedPackRecordModel;
use App\Model\RoomSourceModel;
use App\Model\SigninModel;
use App\Model\UserModel;
use Hamcrest\ResultMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function home() {
        $this->pageData['title'] = env('APP_NAME');
        //取得所有推荐的房源
        $roomSourceModel = new RoomSourceModel();
        $roomList = $roomSourceModel->getList(
            ['id', "type", "roomCategoryId", "name", "areaId", "avgPrice", "imgJson"],
            ['isDel' => 0, 'isRecommend' => 1]
        );
        $this->pageData['roomList'] = $roomList;

        //取得所有可用的广告
        $adsList = (new AdsModel())->getList(['*'], ['isDel' => 0]);
        foreach ($adsList as $ad) {
            $ad->img = env('MEMBER_IMG_DOMAIN') . $ad->img;
        }
        $this->pageData['adsList'] = $adsList;

        return view('index/home', $this->pageData);
    }

    /**
     * 签到领现金
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        //获取此用户是否签到过
        $model = new SigninModel();
        $row = $model->getOne("id", ['userId' => $this->user['id'], 'date' => date("Ymd")]);

        $this->pageData['title'] = '签到领现金';
        $this->pageData['isSignIn'] = !empty($row);

        return view('index/index', $this->pageData);
    }

    /**
     * 现金红包
     */
    public function cashRedPack() {
        $this->pageData['title'] = "现金红包";

        //获取最新的助力记录 (取最近10条记录即可，不重复的用户,不重复的红包ID)
        $this->pageData['rows'] = (new RedPackLogic())->getBroadcastList();

        return view("index/cash-red-pack", $this->pageData);
    }

    /**
     * 现金红包详情页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cashRedPackInfo(Request $request) {
        $this->pageData['title'] = "天天拆红包 领百元现金";

        if (empty($this->user['id'])) {
            return ResultClientJson(900, '未关注用户不能领红包');
        }

        $recordModel = new RedPackRecordModel();
        $data = $request->all();
        $this->pageData['from'] = $data['from'] ?? "";
        if (!empty($data['from']) && $data['from'] == "cash-receive") {
            $redPackModel = new RedPackModel();
            //当前是否有未集满的且未过期红包
            $row = $redPackModel->getUnComplete($this->user['id']);
            if (!empty($row->id)) {
                return ResultClientJson(0, '您有未完成的红包，快去分享吧', ['redPackId' => $row->id]);
            }

            //当天是否签到（领过红包）过，有则明天才能再领
            $signInModel = new SigninModel();
            $todaySignInCount = $signInModel->where("userId", $this->user['id'])->where("date", date("Ymd"))->count();
            if ($todaySignInCount > 0) {
                return ResultClientJson(100, '今天已经签到过，请明天再来吧~');
            }

            //红包配置
            $redPackConfigModel = new RedPackConfigModel();
            $rdConfig = $redPackConfigModel->getOne(['*'], [['id', '>', 0]]);

            /*$totalMoney = mt_rand($rdConfig->minMoney ?? 0, $rdConfig->maxMoney ?? 0);
            $min = $totalMoney * (intval($rdConfig->firstMinPercent) / 100) * 100;
            $max = $totalMoney * (intval($rdConfig->firstMaxPercent) / 100) * 100;
            $curReceived = number_format(mt_rand($min, $max)/100, 2);*/

            $signInCountCacheKey = sprintf(CacheConst::USER_UNINTERRUPTED_SIGN_IN_COUNT, $this->user['id']);
            $signInCount = Redis::get($signInCountCacheKey);
            //根据连续签到时间，累计金额，前10天50，第11~20天60，第21~30天70，封顶100元
            $stepMoney = 50 + intval($signInCount / 10) * 10;
            $totalMoney = StateConst::RED_PACK_INIT_MONEY + $stepMoney;
            $curReceived = StateConst::RED_PACK_INIT_MONEY;

            $nowTime = time();
            $expiredTime = strtotime(date("Y-m-d 00:00:00", strtotime("next day")));
            $insertData = [
                'userId' => $this->user['id'],
                'total' => $totalMoney,
                'received' => $curReceived,
                'expiredTime' => $expiredTime,//隔天过期
                'createTime' => $nowTime,
                'canUseTime' => strtotime("+2 month")
            ];
            $insertId = $redPackModel->insert($insertData);
            if (!empty($insertId)) {
                //签到累计 第二天最后时刻过期
                $newestSignInCount = Redis::incr($signInCountCacheKey);
                Redis::expire($signInCountCacheKey, ($expiredTime - time()) + 86400);

                //签到记录表
                $signInModel->insert([
                    'userId' => $this->user['id'],
                    'date' => date("Ymd"),
                    'createTime' => time()
                ]);

                //记录
                $recordModel->insert([
                    'redPackId' => $insertId,
                    'userId' => $this->user['id'],
                    'type' => 0,
                    'money' => $insertData['received'],
                    'createTime' => time(),
                ]);

                //发送签到提醒
                $this->wxapp->template_message->send([
                    'touser' => $this->user['openid'],
                    'template_id' => WxConst::TEMPLATE_ID_FOR_USER_SIGN_IN,
                    'url' => env('APP_URL') . "/cash-red-pack-info?redPackId=" . $insertId,
                    'data' => [
                        'first' => [
                            "value" => "签到成功 ,连续签到可增加红包初始金额 》》\r",
                            "color" => "#169ADA"
                        ],
                        'keyword1' => '现金红包',
                        'keyword2' => '',
                        'keyword3' => "当前第 {$newestSignInCount} 天签到",
                        'keyword4' => '现金红包签到',
                        'keyword5' => date("Y年m月d日 H时i分s秒")
                    ],
                ]);

                return ResultClientJson(0, '领取成功', ['redPackId' => $insertId]);
            } else {
                return ResultClientJson(100, '领取失败');
            }
        } else {
            if (!empty($data['redPackId'])) {
                //查询当前红包的信息
                $redPackData = (new RedPackModel())->getOne(['*'], ['id' => $data['redPackId']]);
                if (!empty($redPackData)) {
                    $this->pageData['redPackId'] = $redPackData->id;
                    $this->pageData['total'] = $redPackData->total;
                    $this->pageData['received'] = $redPackData->received;
                    $this->pageData['remainingTime'] = $redPackData->expiredTime - time();
                    $this->pageData['redPackRecordList'] = $recordModel->getAssistanceRecords($redPackData->id);

                    //取得所有房源
                    $roomSourceModel = new RoomSourceModel();
                    $roomList = $roomSourceModel->getList(
                        ['id', "type", "roomCategoryId", "name", "areaId", "houseTypeId", "avgPrice", "imgJson"],
                        ['isDel' => 0,'isRecommend' => 1],
                        ['id', "DESC"]
                    );
                    $this->pageData['roomList'] = (new RoomSourceLogic())->formatRoomList($roomList);
                    return view("index/cash-red-pack-info", $this->pageData);
                } else {
                    return redirect('/');
                }
            } else {
                exit("非正常的访问，缺少红包ID");
            }
        }
    }

    //红包助力页
    public function assistancePage(Request $request) {
        $data = $request->all();
        if (empty($data['redPackId'])) {
            exit('redPackId不存在');
        }

        //查询红包信息
        $model = new RedPackModel();
        $redPack = $model->getOne(['*'], ['id' => $data['redPackId']]);
        if (empty($redPack)) {
            exit('此红包不存在');
        }

        if ($redPack->userId == $this->user['id']) {
            return redirect('/cash-red-pack-info?redPackId=' . $data['redPackId']);
        }

        $_u = (new UserModel())->getUserinfoByOpenid($redPack->userId);
        $redPack->nickname = $_u['username'] ?? "";
        $redPack->headImgUrl = $_u['avatar_url'] ?? "";
        $this->pageData['redPack'] = $redPack;

        //今天是否助力过
        $cacheKey = sprintf(CacheConst::RED_PACK_HAS_ASSISTANCE, $data['redPackId']);
        $helpedUserIds = Redis::get($cacheKey);
        $helpedUserIds = empty($helpedUserIds) ? [] : $helpedUserIds;
        $helpedUserIds = is_string($helpedUserIds) ? json_decode($helpedUserIds, true) : $helpedUserIds;
        $isHelped = in_array($this->user['id'], $helpedUserIds);
        $this->pageData['isHelped'] = $isHelped;

        //红包是否已经集满
        $this->pageData['isFull'] = $redPack->total == $redPack->received;

        //助力的红包ID
        $this->pageData['title'] = "好友助力";
        $this->pageData['redPackId'] = $data['redPackId'];

        //查询当前用户是否有未完成的红包
        $unCompleteRedPack = $model->getUnComplete($this->user['id']);
        $this->pageData['unCompleteRedPackId'] = !empty($unCompleteRedPack->id) ? $unCompleteRedPack->id : 0;

        return view("index/assistance-page", $this->pageData);

    }

    //发起助力操作
    public function assistance(Request $request) {
        $data = $request->all();

        //有无红包ID
        if (empty($data['redPackId'])) {
            exit(ResultClientJson(100, '红包不得为空'));
        }

        //已经关注过的用户不能再次助力
        if(!empty($this->user['id'])) {
            exit(ResultClientJson(101, '已经关注过的用户不能再次助力'));
        }

        //查询当前用户是否有未完成的红包
        $redPackModel = new RedPackModel();
        $unCompleteRedPack = $redPackModel->getUnComplete($this->user['id']);
        $jsonData['unCompleteRedPackId'] = !empty($unCompleteRedPack->id) ? $unCompleteRedPack->id : 0;

        $redPackRecordModel = new RedPackRecordModel();

        //今天是否助力过
        $cacheKey = sprintf(CacheConst::RED_PACK_HAS_ASSISTANCE, $data['redPackId']);
        $helpedUserIds = Redis::get($cacheKey);
        $helpedUserIds = empty($helpedUserIds) ? [] : $helpedUserIds;
        $helpedUserIds = is_string($helpedUserIds) ? json_decode($helpedUserIds, true) : $helpedUserIds;
        $isHelped = in_array($this->user['id'], $helpedUserIds);
        if ($isHelped) {
            exit(ResultClientJson(101, '您已经帮他助力过，不能重复助力'));
        }

        //若当前红包已不需要助力，则跳过
        $row = $redPackModel->getOne(['id', 'userId','total', 'received'], ['id' => $data['redPackId'], 'status' => 0, ['expiredTime', ">", time()]]);
        if (empty($row->id)) {
            exit(ResultClientJson(100, '此红包已不需要助力', $jsonData));
        }

        exit();

        /*//红包配置
        $redPackConfigModel = new RedPackConfigModel();
        $rdConfig = $redPackConfigModel->getOne(['*'], [['id', '>', 0]]);

        //增加一次助力
        $minMoney = ($rdConfig->minAssistanceMoney ?? 0) * 100;
        $maxMoney = ($rdConfig->maxAssistanceMoney ?? 0) * 100;

        //若配置了最低助力限制 且 剩余的金额低于这个值，则取新的助力范围
        $remainderAssistanceMoney = intval($rdConfig->remainderAssistanceMoney);
        $remainderMoney = $row->total - $row->received;
        if ($remainderAssistanceMoney > 0 &&  $remainderMoney <= $remainderAssistanceMoney) {
            if (!empty($rdConfig->secondMinAssistanceMoney) && !empty($rdConfig->secondMaxAssistanceMoney)) {
                $minMoney = ($rdConfig->secondMinAssistanceMoney) * 100;
                $maxMoney = ($rdConfig->secondMaxAssistanceMoney) * 100;
            }
        }

        //颠倒则互换
        if ($minMoney > $maxMoney) {
            list($minMoney, $maxMoney) = [$maxMoney, $minMoney];
        }

        //最近一次助力后，获得的金额若超过总金额，则用总金额相减的金额
        $curReceivedMoney = number_format(mt_rand($minMoney, $maxMoney) / 100, 2);
        $isLast = $row->received + $curReceivedMoney >= $row->total;
        if ($isLast) {
            $curReceivedMoney = $row->total - $row->received;
        }
        $recordData = [
            'redPackId' => $data['redPackId'],
            'userId' => $this->user['id'],
            'type' => 1,
            'money' => $curReceivedMoney,
            'createTime' => time(),
        ];
        $newRecordId = $redPackRecordModel->insert($recordData);
        if ($newRecordId) {
            //设置助力缓存
            array_push($helpedUserIds, $this->user['id']);
            Redis::setex($cacheKey, 2 * 86400, json_encode($helpedUserIds, JSON_UNESCAPED_UNICODE));

            //增加received金额
            $nowReceived = $row->received + $curReceivedMoney;
            $updateData = ['received' => $nowReceived];
            if ($isLast) {
                //设置为下个月月底过期
                $lastDayOfNextMonth = date("Y-m-d 23:59:59", strtotime("last day of next month"));
                $updateData['useExpiredTime'] = strtotime($lastDayOfNextMonth);
                $updateData['status'] = 1;
            }
            $redPackModel->updateData($updateData, ['id' => $data['redPackId']]);

            //发送模板消息，通知红包所属人进度
            $who = (new UserModel())->getOne(['openid'], ['id' => $row->userId]);
            if (!empty($who)) {
                Log::info("who", [$this->user]);
                $this->wxapp->template_message->send([
                    'touser' => $who->openid,
                    'template_id' => WxConst::TEMPLATE_ID_FOR_SEND_HELP_MSG,
                    'url' => env('APP_URL') . "/cash-red-pack-info?redPackId=" . $data['redPackId'],
                    'data' => [
                        'first' => [
                            "value" => $this->user['username'] . "给你的红包助力啦 >>\r\n",
                            "color" => "#169ADA"
                        ],
                        'keyword1' => "现金红包",
                        'keyword2' => $this->user['username'],
                        'keyword3' => [
                            "value" => $curReceivedMoney . "元",
                            'color' => '#d22e20'
                        ],
                        'keyword4' => date("Y-m-d H:i:s"),
                    ],
                ]);
            }

            $jsonData['money'] = number_format($curReceivedMoney, 2);
            $jsonData['total'] = number_format($nowReceived, 2);
            exit(ResultClientJson(0, '助力成功', $jsonData));
        }

        exit(ResultClientJson(100, '助力失败', $jsonData));*/
    }

    /**
     *
     * 红包规则
     */
    public function rule() {
        $row = (new RedPackConfigModel())->getOne(['rule'], []);

        return ResultClientJson(0, 'ok', ['rule' => $row->rule ?? ""]);
    }


    /**
     * 设置赠送凭证缓存
     */
    public function initGrantRedPack(Request $request) {
        exit("not used");
        //红包ID
        $id = $request->post("id");
        if (!empty($id)) {
            $count = (new RedPackModel())->where("id", $id)->count();
            if ($count) {
                $cacheKey = sprintf(CacheConst::RED_PACK_GRANT_TICKET, $id);
                $ticket = Redis::get($cacheKey);
                if (empty($ticket)) {
                    $ticket = md5(date("YmdHis") . "-" . $id . "-" . $this->user['id']);
                    Redis::setex($cacheKey, 30 * 86400, $ticket);
                }
                return ResultClientJson(0, 'cache ok', ['ticket' => $ticket]);
            }
            return ResultClientJson(100 , '未找到相应数据');
        }

        return ResultClientJson(100, '参数错误');
    }
    /**
     * 赠送红包页面
     */
    public function grantRedPack(Request $request) {
        exit("not used");
        $ticket = $request->get("ticket");
        $redPackId = $request->get("redPackId");

        //未关注用户，存下关系，关注后消息中使用
        if (!empty($ticket) && empty($this->user['id'])) {
            //可能是从微信的消息中跳转过来，需要从缓存中获取
            $cacheKey = sprintf(CacheConst::MY_TEMP_TICKET, $this->user['openid'], $redPackId);
            Redis::setex($cacheKey, 7 * 86400, $ticket);
        }

        $row = (new RedPackModel())->getOne(['id', 'total', 'userId'], ['id' => $redPackId]);
        if(!empty($row)) {
            $user = (new UserModel())->getUserinfoByOpenid($row->userId);
            $row->nickname = mb_substr($user['username'], 0, 4);
        }

        $this->pageData['row'] = $row;
        $this->pageData['ticket'] = $ticket;
        $this->pageData['redPackId'] = $redPackId;
        return view('index/grantRedPack', $this->pageData);
    }
    /**
     * 领取赠送的红包
     */
    public function receiveGrantRedPack(Request $request) {
        exit('not used');
        $ticket = $request->post("ticket");
        $redPackId = $request->post("redPackId");

        if (empty($ticket) || empty($redPackId)) {
            return ResultClientJson(100, '领取赠送的红包时，参数错误');
        }

        //TICKET 凭证检测
        $cacheKey = sprintf(CacheConst::RED_PACK_GRANT_TICKET, $redPackId);
        $ticketInCache = Redis::get($cacheKey);
        if (empty($ticketInCache)) {
            return ResultClientJson(100, '来晚了，红包已被人抢先领走啦~');
        }
        if (trim($ticket) != $ticketInCache) {
            return ResultClientJson(100, '您不能领取此红包');
        }

        //领取时，判断红包的状态，是否为可用状态,可用才能领取
        $redPackModel = new RedPackModel();
        $redPack = $redPackModel->getOne(['userId', 'useExpiredTime', 'status'], ['id' => $redPackId, 'isDel' => 0]);
        if (empty($redPack)) {
            return ResultClientJson(100, '此红包已被删除或不存在');
        }
        if ($redPack->status != StateConst::RED_PACK_FILL_UP) {
            return ResultClientJson(100, '此红包已被使用');
        }
        if (time() >= $redPack->useExpiredTime) {
            return ResultClientJson(100, '此红包已过期，无法领取');
        }
        if ($redPack->userId == $this->user['id']) {
            return ResultClientJson(100, "赠送给好友的红包,自己不能领取");
        }

        //分享的时候设置一个缓存，有这个缓存，则能领取，没有这个缓存，则不能领取(限制只能一个人能够领取成功)，缓存可设置为30天后过期，即分享后，30内未领取，则不能再领取
        Redis::del($cacheKey);

        //更新红包信息
        $redPackModel->updateData([
            'userId' => $this->user['id'],
            'fromUserId' => $redPack->userId,
        ], ['id' => $redPackId]);

        return ResultClientJson(0, '领取成功');
    }

    /**
     * 播报列表
     */
    public function broadcastList() {
        //获取最新的助力记录 (取最近10条记录即可，不重复的用户,不重复的红包ID)
        $list =  (new RedPackLogic())->getBroadcastList();

        return ResultClientJson(0, 'ok', ['list' => $list]);
    }


    /**
     * 模拟用户登录
     * @param Request $request
     */
    public function debug(Request $request) {
        $id = $request->get("id");
        $user = (new UserModel())->getOne(["*"], ['id' => $id]);
        Cookie::queue(CookieConst::WECHAT_USER, json_encode($user, JSON_UNESCAPED_UNICODE), 60 * 24);
    }
}