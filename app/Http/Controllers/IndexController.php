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
use App\Logic\RoomSourceLogic;
use App\Model\AdsModel;
use App\Model\RedPackConfigModel;
use App\Model\RedPackModel;
use App\Model\RedPackRecordModel;
use App\Model\RoomSourceModel;
use App\Model\SigninModel;
use App\Model\UserModel;
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
//        exit('建设中');
        $this->pageData['title'] = "现金红包";

        //获取已经集满的红包数据 (取最近10条记录即可，不重复的用户,不重复的红包ID)
        $model = new RedPackRecordModel();
        $rows = $model->join("red_pack as b", "redPackId", "=", "b.id")
            ->select(['b.userId', 'red_pack_record.money', "red_pack_record.createTime"])
            ->groupBy("red_pack_record.id", "redPackId", "userId", "money", "createTime")
            ->orderBy("red_pack_record.id", "desc")
            ->limit(10)->get();
        if (!empty($rows)) {
            $userModel = new UserModel();
            foreach ($rows as $row) {
                $_u = $userModel->getUserinfoByOpenid($row->userId);
                $row->nickname = $_u['username'] ?? "";
                $row->headImgUrl = $_u['avatar_url'] ?? "";
                $row->time = beforeWhatTime($row->createTime ?? 0);
            }
        }

        $this->pageData['rows'] = $rows;

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
            exit("未关注用户不能领红包");
        }

        $recordModel = new RedPackRecordModel();
        $data = $request->all();
        $this->pageData['from'] = $data['from'] ?? "";
        if (!empty($data['from']) && $data['from'] == "cash-receive") {
            $redPackModel = new RedPackModel();
            //当前是否有未集满的且未过期红包
            $row = $redPackModel->getUnComplete($this->user['id']);
            if (!empty($row->id)) {
                return redirect('/cash-red-pack-info?redPackId=' . $row->id);
            }
            //红包配置
            $redPackConfigModel = new RedPackConfigModel();
            $rdConfig = $redPackConfigModel->getOne(['*'], [['id', '>', 0]]);

            $totalMoney = mt_rand($rdConfig->minMoney ?? 0, $rdConfig->maxMoney ?? 0);
            $min = $totalMoney * (intval($rdConfig->firstMinPercent) / 100) * 100;
            $max = $totalMoney * (intval($rdConfig->firstMaxPercent) / 100) * 100;
            $curReceived = number_format(mt_rand($min, $max)/100, 2);
            $insertData = [
                'userId' => $this->user['id'],
                'total' => $totalMoney,
                'received' => $curReceived,
                'expiredTime' => time() + 86400,
            ];
            $insertId = $redPackModel->insert($insertData);
            if (!empty($insertId)) {
                //记录
                $recordModel->insert([
                    'redPackId' => $insertId,
                    'userId' => $this->user['id'],
                    'type' => 0,
                    'money' => $insertData['received'],
                    'createTime' => time(),
                ]);

                $this->pageData['redPackId'] = $insertId;
                $this->pageData['total'] = $totalMoney;
                $this->pageData['received'] = $insertData['received'];
                $this->pageData['remainingTime'] = $insertData['expiredTime'] - time();

                //本次记录
                $record = new \stdClass();
                $record->userId = $this->user['id'];
                $record->money = $insertData['received'];
                $record->nickname = $this->user['username'] ?? "";
                $record->headImgUrl = $this->user['avatar_url'] ?? "";
                $record->time = beforeWhatTime(1);
                $this->pageData['redPackRecordList'][] = $record;
            } else {
                exit("SQL执行失败");
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
                } else {
                    return redirect('/');
                }
            } else {
                exit("非正常的访问，缺少红包ID");
            }
        }

        //取得所有房源
        $roomSourceModel = new RoomSourceModel();
        $roomList = $roomSourceModel->getList(
            ['id', "type", "roomCategoryId", "name", "areaId", "houseTypeId", "avgPrice", "imgJson"],
            ['isDel' => 0,'isRecommend' => 1],
            ['id', "DESC"]
        );
        $this->pageData['roomList'] = (new RoomSourceLogic())->formatRoomList($roomList);

//        dd($this->pageData['roomList']);

        return view("index/cash-red-pack-info", $this->pageData);
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

        //是否关注
        if (empty($this->user['id']) || empty($this->user['is_subscribe'])) {
            exit(ResultClientJson(101, '未登录或者未关注用户不能助力'));
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

        //红包配置
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
            $nowReceived = $row->received + $recordData['money'];
            $updateData = ['received' => $nowReceived];
            if ($isLast) {
                $updateData['useExpiredTime'] = time() + 30 * 86400;
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
                        'first' => $this->user['username'] . "给你的红包助力啦~",
                        'keyword1' => "现金红包",
                        'keyword2' => $this->user['username'],
                        'keyword3' => $curReceivedMoney,
                        'keyword4' => date("Y-m-d H:i:s"),
                    ],
                ]);
            }

            $jsonData['money'] = $recordData['money'];
            $jsonData['total'] = $nowReceived;
            exit(ResultClientJson(0, '助力成功', $jsonData));
        }

        exit(ResultClientJson(100, '助力失败', $jsonData));
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
     * 模拟用户登录
     * @param Request $request
     */
    public function debug(Request $request) {
        $id = $request->get("id");
        $user = (new UserModel())->getOne(["*"], ['id' => $id]);
        Cookie::queue(CookieConst::WECHAT_USER, json_encode($user, JSON_UNESCAPED_UNICODE), 60 * 24);
    }
}