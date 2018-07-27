<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 10:28
 */

namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function index()
    {

        $oauth = $this->wxapp->oauth;
        // 未登录
        if (empty($_SESSION['wechat_user'])) {

            session('target_url', '/');

            return $oauth->redirect();
        }

        return view('index');
    }

}