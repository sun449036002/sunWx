<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/11
 * Time: 15:30
 */

namespace App\Http\Controllers;


use App\Consts\StateConst;
use App\Logic\BespeakLogic;
use App\Logic\RedPackLogic;
use App\Logic\RoomSourceLogic;
use App\Model\BalanceLogModel;
use App\Model\CashbackModel;
use App\Model\RedPackModel;
use App\Model\SuggestionModel;
use App\Model\SystemModel;
use App\Model\WithdrawModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MyController extends Controller
{
    //用户中心
    public function index() {
        $this->pageData['title'] = "我的";

        //我的余额显示可使用的，未过期的红包总额
        $this->pageData['balance'] = (new RedPackLogic())->getRedPackBalance();
//        $this->pageData['balance'] = $this->user['balance'] ?? 0;

        return view('my/index', $this->pageData);
    }

    //我的余额
    public function balance() {
        //我的余额显示可使用的，未过期的红包总额
        $this->pageData['balance'] = (new RedPackLogic())->getRedPackBalance();
//        $this->pageData['balance'] = $this->user['balance'] ?? 0;

        $this->pageData['balanceTypes'] = ['红包收入', '提现申请', '提现支出', '提现驳回'];
        //余额日志
        $orderBy = ["id", "DESC"];
        $balanceLogList = (new BalanceLogModel())->getList(['inOrOut', 'type', 'money', 'createTime'], ['userId' => $this->user['id']], $orderBy);
        $this->pageData['balanceLogList'] = $balanceLogList;

        return view('/my/balanceList', $this->pageData);
    }

    //申请提现页面
    public function withdraw() {
        return view('/my/withdraw', $this->pageData);
    }

    //申请提现操作
    public function doWithdraw(Request $request) {
        $data = $request->all();
        $rule = [
            'buyers' => 'required',
            'tel' => 'required',
            'redPackIds' => 'required',
        ];
        $message = [
            'buyers.required' => '申请人必填',
            'tel.required' => '联系电话必填',
            'redPackIds.required' => '申请提现的红包必填',

        ];
        $validate = Validator::make($data, $rule, $message);
        if (!$validate->passes()) {
            return ResultClientJson(100, $validate->getMessageBag()->getMessages()['redPackIds'][0]);
        }

        //返现金额打款账号
        $paymentMethodList = [
            'alipay' => $data['alipay'],
            'weixin' => $data['weixin'],
            'bankcard' => $data['bankcard'],
        ];

        $withdrawModel = new WithdrawModel();
        $insertId = $withdrawModel->insert([
            'userId' => $this->user['id'],
            'name' => $data['buyers'],
            'tel' => $data['tel'],
            'redPackIds' => $data['redPackIds'],
            'paymentMethod' => json_encode($paymentMethodList, JSON_UNESCAPED_UNICODE),
            'status' => 0,
            'createTime' => time()
        ]);

        if ($insertId) {
            //红包变更为使用中
            $allRedPackIds = explode(",", $data['redPackIds']);
            if (!empty($allRedPackIds)) {
                $redPackModel = new RedPackModel();
                $ok = $redPackModel->updateData(['status' => StateConst::RED_PACK_USING], [["id", "in", $allRedPackIds]]);
                if (empty($ok)) {
                    Log::warning("submitBackMoney", ['msg' => '红包状态更新失败', 'allRedPackIds' => $allRedPackIds]);
                }

                //记录一条余额日志
                $redPackList = $redPackModel->getList(['total'], [['id', 'in', $allRedPackIds]]);
                $totalMoney = 0;
                foreach ($redPackList as $item) {
                    $totalMoney += $item->total;
                }
                if ($totalMoney > 0) {
                    (new BalanceLogModel())->insert([
                        'userId' => $this->user['id'],
                        'inOrOut' => StateConst::BALANCE_OUT,
                        'type' => StateConst::BALANCE_WITHDRAW_APPLY,
                        'money' => -$totalMoney,
                        'createTime' => time()
                    ]);
                }
            }
        }

        return ResultClientJson(0, '申请成功');
    }

    /**
     * 申请提现时，获取前两个月可用的红包
     * @return string
     */
    public function getTwoMonthAgoEnabledRedPackList(Request $request) {
        $where = [
            'userId' => $this->user['id'],
            'status' => StateConst::RED_PACK_FILL_UP,
            ['canUseTime', '<=', time()]
        ];
        $list = (new RedPackModel())->getList(['*'], $where);
        foreach ($list as $key => $item) {
            $list[$key]->createTime = date("Y-m-d H:i:s", $item->createTime);
        }

        return ResultClientJson(0, 'ok', $list);
    }

    /**
     * 预约记录
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function bespeakList() {
        $this->pageData['title'] = "预约记录";
        $this->pageData['list'] = (new BespeakLogic())->getBespeakList();
        return view('my/bespeak-list', $this->pageData);
    }

    /**
     * 预约 详情
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function bespeakDetail(Request $request) {
        $id = $request->get('id');
        $bespeak = (new BespeakLogic())->getById($id);
        if (empty($bespeak)) {
            return redirect('my/bespeakList');
        }

        $this->pageData['bespeak'] = $bespeak;
        $this->pageData['title'] = $bespeak->roomSourceName . "-预约详情";
        return view('my/bespeak-detail', $this->pageData);
    }

    //购房返现表格填写
    public function backMoneyPage() {
        return view('my/backMoneyPage', $this->pageData);
    }

    /**
     * 提交购房返现表格
     */
    public function submitBackMoney(Request $request) {
        $data = $request->all();
        $rule = [
            'houses' => 'required',
            'address' => 'required',
            'buyers' => 'required',
            'tel' => 'required',
            'amount' => 'required',
            'acreage' => 'required',
            'buyTime' => 'required',
            'wxImgs' => 'required',
        ];
        $message = [
            'houses.required' => '楼盘名称必填',
            'address.required' => '楼盘地址必填',
            'buyers.required' => '购房人必填',
            'tel.required' => '联系电话必填',
            'amount.required' => '购房金额必填',
            'acreage.required' => '面积必填',
            'buyTime.required' => '购房时间必填',
            'wxImgs.required' => '购房凭证图片必填',
        ];
        $validate = Validator::make($data, $rule, $message);
        if (!$validate->passes()) {
            return back()->withErrors($validate);
        }

        //保存微信上传的临时素材图片
        $imgs = [];
        $destinationPath = "/images/cash-back-wx/" . date("Ymd");
        $wxImgs = explode(",", $data['wxImgs']);
        foreach ($wxImgs as $mediaId) {
            $stream = $this->wxapp->media->get($mediaId);
            // 自定义文件名，不需要带后缀
            $filename = $mediaId . ".png";
            $stream->saveAs(storage_path()  . "/app" . $destinationPath, $filename);

            $imgs[] = $destinationPath . "/" . $filename;
        }

        //返现金额打款账号
        $paymentMethodList = [
            'alipay' => $data['alipay'],
            'weixin' => $data['weixin'],
            'bankcard' => $data['bankcard'],
        ];

        $model = new CashbackModel();
        $insertId = $model->insert([
            'userId' => $this->user['id'],
            'roomSourceName' => $data['houses'],
            'amount' => $data['amount'],
            'acreage' => $data['acreage'],
            'address' => $data['address'],
            'buyers' => $data['buyers'],
            'tel' => $data['tel'],
            'buyTime' => $data['buyTime'],
            'type' => $data['mortgage'],//购房方式
            'redPackIds' => $data['redPackIds'],
            'friendRedPackIds' => $data['friendRedPackIds'],
            'imgs' => json_encode($imgs, JSON_UNESCAPED_UNICODE),
            'paymentMethod' => json_encode($paymentMethodList, JSON_UNESCAPED_UNICODE),
            'createTime' => time(),
        ]);

        if ($insertId) {
            //红包变更为使用中
            $allRedPackIds = array_filter(array_merge(explode(",", $data['redPackIds']), explode(",", $data['friendRedPackIds'])));
            if (!empty($allRedPackIds)) {
                $ok = (new RedPackModel())->updateData(['status' => StateConst::RED_PACK_USING], [["id", "in", $allRedPackIds]]);
                if (empty($ok)) {
                    Log::warning("submitBackMoney", ['msg' => '红包状态更新失败', 'allRedPackIds' => $allRedPackIds]);
                }
            }

            return ResultClientJson(0, '提交成功');
        }
        return ResultClientJson(100, '提交失败');

    }

    /**
     * 我的红包列表
     * 类型分为：全部，未完成，未使用，已过期
     */
    public function redPackList(Request $request) {
        $type = $request->get("type", 'all');
        $this->pageData['type'] = $type;

        $where = ['userId' => $this->user['id']];
        switch ($type) {
            case 'unFinish':
                $where['status'] = StateConst::RED_PACK_UN_FILL_UP;
                $where[] = ["expiredTime", ">", time()];
                break;
            case 'unUse':
                $where['status'] = StateConst::RED_PACK_FILL_UP;
//                $where[] = ["useExpiredTime", ">", time()];
                break;
            case 'used':
                $where['status'] = StateConst::RED_PACK_USED;
                break;
            case 'expired':
                $where['status'] = StateConst::RED_PACK_FILL_UP;
//                $where[] = ["useExpiredTime", "<=", time()];
                break;
        }

        $list = (new RedPackModel())->getList(['*'], $where, ['status']);
        foreach ($list as $item) {
            $item->expiredTimeStr = date("Y-m-d H:i:s", $item->expiredTime);
            $item->useExpiredTimeStr = $item->useExpiredTime ? date("Y-m-d H:i:s", $item->useExpiredTime) : "";
            if ($item->status == StateConst::RED_PACK_UN_FILL_UP) {
                if ($item->expiredTime >= time()) {
                    $item->type = 'unFinish';
                } else {
                    $item->type = 'expired';
                }
            } else if ($item->status == StateConst::RED_PACK_FILL_UP) {
                $item->canUseTime = date("Y-m-d H:i:s", $item->canUseTime);
                if ($item->useExpiredTime >= time()) {
                    $item->type = 'unUse';
                } else {
                    $item->type = 'useExpired';
                }
            } else if ($item->status == StateConst::RED_PACK_USING) {
                $item->type = "using";
            } else if ($item->status == StateConst::RED_PACK_USED) {
                $item->type = "used";
            } else {
                $item->type = "other";
            }
        }

        $this->pageData['list'] = $list;
        return view('my/redPackList', $this->pageData);

    }

    /**
     *  红包详情
     */
    public function redPackDetail(Request $request) {
        //可赠送 可消费
        $id = $request->get("id");

        $row = (new RedPackModel())->getOne(['id', 'total'], ['id' => $id, 'userId' => $this->user['id']]);
        if (empty($row)) {
            return back()->withErrors("您没有这么一个红包");
        }

        $this->pageData['row'] = $row;

        return view("my/redPackDetail", $this->pageData);

    }


    /**
     * 返现申请中，获取我可用的红包 与 朋友赠送的可用红包
     * @return string
     */
    public function getMyEnabledRedPackList(Request $request) {
        exit('not used');
        $type = $request->get("type", "my");

        if ($type == 'friend') {
            $where = [
                'status' => StateConst::RED_PACK_FILL_UP,
                'userId' => $this->user['id'],
                ["useExpiredTime", ">", time()],
                ['fromUserId', '>', 0]
            ];
            $row = (new RedPackModel())->getOne(['*'], $where);
            $list = empty($row) ? [] : [$row];
        } else {
            $list = (new RedPackLogic())->getMyEnabledRedPacks();
        }

        if (!empty($list)) {
            foreach ($list as $key => $item) {
                $item->type = $type;
                $item->useExpiredTime = date("Y-m-d H:i:s", $item->useExpiredTime);
            }
        }

        return ResultClientJson(0, 'ok', $list);
    }

    /**
     * 我收藏的房源
     */
    public function markRoomList() {
        $list = (new RoomSourceLogic())->getMarkRoomList();
        $this->pageData['title'] = '收藏的房源';
        $this->pageData['list'] = $list;

        return view('my/markRoomList', $this->pageData);
    }

    /**
     * 意见反馈
     */
    public function suggestion() {
        return view("my/suggestion");
    }

    /**
     * 提交意见反馈
     */
    public function suggestionSubmit(Request $request) {
        $data = $request->all();
        $data['userId'] = $this->user['id'];

        unset($data['csrf_token']);
        $insertId = (new SuggestionModel())->insert($data);

        return ResultClientJson(0, '提交成功，谢谢您的建议');
    }

    /**
     * 关于我们
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function aboutUs() {
        $row = (new SystemModel())->getOne(['aboutUs'], []);

        $this->pageData['aboutUs'] = $row->aboutUs ?? "暂无介绍";
        return view("/my/aboutUs", $this->pageData);
    }
}