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
}