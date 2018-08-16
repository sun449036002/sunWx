<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/11
 * Time: 15:30
 */

namespace App\Http\Controllers;


use App\Logic\BespeakLogic;
use Illuminate\Http\Request;

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
}