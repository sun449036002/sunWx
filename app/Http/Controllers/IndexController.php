<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 10:28
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

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
        $this->user = $this->getUserinfo($request);
        if (empty($this->user['id'])) {

            Cookie::queue('target_url', '/', 2);

            return $oauth->redirect();
        }

        echo "<pre>";
        print_r($this->user);

        return view('index');
    }
}