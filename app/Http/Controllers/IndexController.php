<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 10:28
 */

namespace App\Http\Controllers;

use App\Consts\CookieConst;
use App\Model\RedPackConfigModel;
use App\Model\RedPackModel;
use App\Model\RedPackRecordModel;
use App\Model\SigninModel;
use App\Model\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class IndexController extends Controller
{
    private $pageData = [];
    public function __construct()
    {
        parent::__construct();

        //获取用户信息
        $this->user = $this->getUserinfo();

        $this->pageData['user'] = $this->user;
        $this->pageData['wxapp'] = $this->wxapp;
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
            $min = ($rdConfig->minAssistanceMoney ?? 0) * 100;
            $max = ($rdConfig->maxAssistanceMoney ?? 0) * 100;
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
                $record->headImgUrl = $this->user['avatar_url'] ?? "";
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
                }
            } else {
                exit("非正常的访问，缺少红包ID");
            }
        }
        return view("index/cash-red-pack-info", $this->pageData);
    }

    //红包助力页
    public function assistancePage(Request $request) {
        $data = $request->all();
        if (empty($data['redPackId'])) {
            exit('redPackId不存在');
        }

        //助力的红包ID
        $this->pageData['title'] = "好友助力";
        $this->pageData['redPackId'] = $data['redPackId'];

        //查看将被助力的红包是否已经集满，满则跳转到首页

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

        //TODO 检测今天是否已经对此用户助力过


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
        if ($minMoney > $maxMoney) {
            list($minMoney, $maxMoney) = [$maxMoney, $minMoney];
        }

        //最近一次助力后，获得的金额若超过总金额，则用总金额相减的金额
        $curReceivedMoney = number_format(mt_rand($minMoney, $maxMoney) / 100, 2);
        $isLast = $row->received + $curReceivedMoney >= $row->total;
        if ($isLast) {
            $curReceivedMoney = $row->total - $row->received;
        }
        $redPackRecordModel = new RedPackRecordModel();
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
                    'template_id' => '82y_cNd0iWws8JUkRXgVolIkCVqYXYZkxL34RdBUIVg',
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