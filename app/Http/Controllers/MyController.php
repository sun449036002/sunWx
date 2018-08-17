<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/11
 * Time: 15:30
 */

namespace App\Http\Controllers;


use App\Logic\BespeakLogic;
use App\Model\CashbackModel;
use App\Model\RedPackModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MyController extends Controller
{
    public function index() {
        $this->pageData['title'] = "我的";
        return view('my/index', $this->pageData);
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
            'type' => $data['mortgage'],
            'imgs' => json_encode($imgs, JSON_UNESCAPED_UNICODE),
            'paymentMethod' => json_encode($paymentMethodList, JSON_UNESCAPED_UNICODE),
            'createTime' => time(),
        ]);

        if ($insertId) {
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
                $where['status'] = 0;
                $where[] = ["expiredTime", "<", time()];
                break;
            case 'unUse':
                $where['status'] = 1;
                $where[] = ["useExpiredTime", ">", time()];
                break;
            case 'expired':
                $where['status'] = 1;
                $where[] = ["useExpiredTime", "<=", time()];
                break;
        }
        $list = (new RedPackModel())->getList(['*'], $where);
        foreach ($list as $item) {
            if ($item->status == 0) {
                if ($item->expiredTime >= time()) {
                    $item->type = 'unFinish';
                } else {
                    $item->type = 'expired';
                }
            } else if ($item->status == 1){
                if ($item->useExpiredTime >= time()) {
                    $item->type = 'unUse';
                } else {
                    $item->type = 'useExpired';
                }
            }
        }

//        dd($list);

        $this->pageData['list'] = $list;
        return view('my/redPackList', $this->pageData);

    }

    /**
     * 获取我可用的红包列表
     * @return string
     */
    public function getMyEnabledRedPackList() {
        $where = [
            'status' => 1,
            'userId' => $this->user['id'],
            ["useExpiredTime", ">", time()]
        ];
        $list = (new RedPackModel())->getList(['*'], $where);

        foreach ($list as $item) {
            $item->useExpiredTime = date("Y-m-d H:i:s", $item->useExpiredTime);
        }

        return ResultClientJson(0, 'ok', $list);
    }
}