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
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $oauth = $this->wxapp->oauth;
        // 未登录
        if (empty($this->user)) {

            session('target_url', '/');

            return $oauth->redirect();
        }

        echo "<pre>";
        print_r($this->user);

        return view('index');
    }

    public function clearAllSession(Request $request) {
        $request->session()->flush();
        echo 'ok';
    }

}