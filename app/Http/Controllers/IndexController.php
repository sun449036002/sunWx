<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 10:28
 */

namespace App\Http\Controllers;

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

        return view('index', $this->pageData);
    }


    /**
     * 现金红包
     */
    public function cashRedPack() {
        $this->pageData['title'] = "现金红包";

        return view("cash-red-pack", $this->pageData);
    }
}