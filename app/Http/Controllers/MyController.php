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
use App\Logic\RoomSourceLogic;
use App\Model\CashbackModel;
use App\Model\RedPackModel;
use App\Model\RoomSourceModel;
use App\Model\SuggestionModel;
use App\Model\SystemModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MyController extends Controller
{
    //用户中心
    public function index() {
        $this->pageData['title'] = "我的";
        return view('my/index', $this->pageData);
    }

    //我的余额
    public function balance() {
        return view('/my/balanceList', $this->pageData);
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
            'img' => 'required',
        ];
        $message = [
            'houses.required' => '楼盘名称必填',
            'address.required' => '楼盘地址必填',
            'buyers.required' => '购房人必填',
            'tel.required' => '联系电话必填',
            'amount.required' => '购房金额必填',
            'acreage.required' => '面积必填',
            'buyTime.required' => '购房时间必填',
            'img.required' => '购房凭证图片必填',
        ];
        $validate = Validator::make($data, $rule, $message);
        if (!$validate->passes()) {
            dd($validate->errors());
            return back()->withErrors($validate);
        }

        //图片
        $imgs = explode(",", $data['img']);

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
                $where[] = ["expiredTime", "<", time()];
                break;
            case 'unUse':
                $where['status'] = StateConst::RED_PACK_FILL_UP;
                $where[] = ["useExpiredTime", ">", time()];
                break;
            case 'expired':
                $where['status'] = StateConst::RED_PACK_FILL_UP;
                $where[] = ["useExpiredTime", "<=", time()];
                break;
        }
        $list = (new RedPackModel())->getList(['*'], $where, ['status']);
        foreach ($list as $item) {
            if ($item->status == StateConst::RED_PACK_UN_FILL_UP) {
                if ($item->expiredTime >= time()) {
                    $item->type = 'unFinish';
                } else {
                    $item->type = 'expired';
                }
            } else if ($item->status == StateConst::RED_PACK_FILL_UP) {
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

//        dd($list);

        $this->pageData['list'] = $list;
        return view('my/redPackList', $this->pageData);

    }

    /**
     * 返现申请中，获取我可用的红包 与 朋友赠送的可用红包
     * @return string
     */
    public function getMyEnabledRedPackList(Request $request) {
        $type = $request->get("type", "my");

        if ($type == 'friend') {
            $where = [
                'status' => StateConst::RED_PACK_FILL_UP,
                'userId' => $this->user['id'],
                ["useExpiredTime", ">", time()],
                ['fromUserId', '>', 0]
            ];
            $list = (new RedPackModel())->getList(['*'], $where);
            $friendTotalMoney = [];
            if (!empty($list)) {
                foreach ($list as $item) {
                    if (empty($friendTotalMoney[$item->fromUserId])) {
                        $friendTotalMoney[$item->fromUserId] = 0;
                    } else {
                        $friendTotalMoney[$item->fromUserId] += $item->total;
                    }
                }
                arsort($friendTotalMoney);
                $useFriendId = array_keys(array_slice($friendTotalMoney, 0, 1, true))[0] ?? 0;
                foreach ($list as $key => $item) {
                    if ($useFriendId != $item->fromUserId || $item->userId == $item->fromUserId) {
                        unset($list[$key]);
                        continue;
                    }
                    $item->type = $type;
                    $item->useExpiredTime = date("Y-m-d H:i:s", $item->useExpiredTime);
                }
            }
        } else {
            $where = [
                'status' => StateConst::RED_PACK_FILL_UP,
                'userId' => $this->user['id'],
                'fromUserId' => 0,
                ["useExpiredTime", ">", time()]
            ];
            $list = (new RedPackModel())->getList(['*'], $where);
            foreach ($list as $item) {
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