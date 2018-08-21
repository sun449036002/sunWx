<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 10:28
 */

namespace App\Http\Controllers;

use App\Consts\CookieConst;
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
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Monolog\Handler\IFTTTHandler;

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
        $recordModel = new RedPackRecordModel();
        $isHelped = $recordModel->where([
            "redPackId" => $data['redPackId'],
            'userId' => $this->user['id'],
        ])->count();
        $this->pageData['isHelped'] = $isHelped;

        //红包是否已经集满
        $this->pageData['isFull'] = $redPack->total == $redPack->received;

        //助力的红包ID
        $this->pageData['title'] = "好友助力";
        $this->pageData['redPackId'] = $data['redPackId'];

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
        $isHelped = $redPackRecordModel->where([
            "redPackId" => $data['redPackId'],
            'userId' => $this->user['id'],
        ])->count();
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
            //增加received金额
            $nowReceived = $row->received + $recordData['money'];
            $updateData = ['received' => $nowReceived];
            if ($isLast) {
                $updateData['useExpiredTime'] = time() + 86400;
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
     * 模拟用户登录
     * @param Request $request
     */
    public function debug(Request $request) {
        $id = $request->get("id");
        $user = (new UserModel())->getOne(["*"], ['id' => $id]);
        Cookie::queue(CookieConst::WECHAT_USER, json_encode($user, JSON_UNESCAPED_UNICODE), 60 * 24);
    }

    public function clearCookie() {
        setcookie(CookieConst::WECHAT_USER, '', -1, '/');
    }

    public function getCookie() {
        var_dump(Cookie::get(CookieConst::WECHAT_USER));
    }

}