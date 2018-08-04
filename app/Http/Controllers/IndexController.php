<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 10:28
 */

namespace App\Http\Controllers;

use App\Model\RedPackModel;
use App\Model\RedPackRecordModel;
use App\Model\SigninModel;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    private $pageData = [];
    public function __construct(Request $request)
    {
        parent::__construct();

        //获取用户信息
        $this->user = $this->getUserinfo($request);

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
        $row = $model->getOne("id", ['user_id' => $this->user['id'], 'date' => date("Ymd")]);

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
            //TODO
//            exit("未关注用户不能领红包");
        }

        $data = $request->all();
        $this->pageData['from'] = $data['from'] ?? "";
        if (!empty($data['from']) && $data['from'] == "cash-receive") {
            $redPackModel = new RedPackModel();
            //当前是否有未集满的且未过期红包
            $row = $redPackModel->getOne(['id'], ['user_id' => $this->user['id'], 'status' => 0, ['expiredTime', '>', time()]]);
            if (!empty($row->id)) {
                return redirect('/cash-red-pack-info?redPackId=' . $row->id);
            }
            $totalMoney = mt_rand(50, 200);
            $insertData = [
                'user_id' => $this->user['id'],
                'total' => $totalMoney,
                'received' => mt_rand(10, $totalMoney / 2),
                'expiredTime' => time() + 86400,
            ];
            $insertId = $redPackModel->insert($insertData);
            if (!empty($insertId)) {
                //记录
                $recordModel = new RedPackRecordModel();
                $recordModel->insert([
                    'redPackId' => $insertId,
                    'userId' => $this->user['id'],
                    'type' => 0,
                    'money' => $insertData['received']
                ]);

                $this->pageData['redPackId'] = $insertId;
                $this->pageData['total'] = $totalMoney;
                $this->pageData['received'] = $insertData['received'];
                $this->pageData['remainingTime'] = $insertData['expiredTime'] - time();
            } else {
                exit("SQL执行失败");
            }
        } else {
            if (!empty($data['redPackId'])) {
                //查询当前红包的信息
                $redPackData = (new RedPackModel())->getOne(['*'], ['id' => $data['redPackId']]);
                if (!empty($redPackData)) {
                    $this->pageData['redPackId'] = $redPackData->total;
                    $this->pageData['total'] = $redPackData->total;
                    $this->pageData['received'] = $redPackData->received;
                    $this->pageData['remainingTime'] = $redPackData->expiredTime - time();
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

        //若当前红包已不需要助力，则跳过
        $redPackModel = new RedPackModel();
        $row = $redPackModel->getOne(['id', 'total', 'received'], ['id' => $data['redPackId'], 'status' => 0, ['expiredTime', ">", time()]]);
        if (empty($row['id'])) {
            exit(ResultClientJson(100, '此红包已不需要助力'));
        }

        //增加一次助力
        $minMoney = 10;
        $maxMoney = $data['total'] - $data['received'];
        if ($minMoney > $maxMoney) {
            list($minMoney, $maxMoney) = [$maxMoney, $minMoney];
        }

        $redPackRecordModel = new RedPackRecordModel();
        $recordData = [
            'redPackId' => $data['redPackId'],
            'userId' => $this->user['id'],
            'type' => 1,
            'money' => mt_rand($minMoney, $maxMoney)
        ];
        $newRecordId = $redPackRecordModel->insert($recordData);
        if ($newRecordId) {
            //增加received金额
            $nowReceived = $row->received + $recordData['money'];
            $redPackModel->updateData(['received' => $nowReceived], ['id' => $data['redPackId']]);
            exit(ResultClientJson(0, 'ok'));
        }

        exit(ResultClientJson(100, '助力失败'));
    }
}