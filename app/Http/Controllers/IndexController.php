<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 10:28
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $oauth = $this->wxapp->oauth;
        // 未登录
        $this->user = $request->session()->get("wechat_user");
        if (empty($this->user)) {

            session('target_url', '/');

            return $oauth->redirect();
        }

        print_r($request->session()->all());

        return view('index');
    }

}