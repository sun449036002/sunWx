<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/11
 * Time: 15:30
 */

namespace App\Http\Controllers;


class MyController extends Controller
{
    public function index() {
        $this->pageData['title'] = "我的";
        return view('my/index', $this->pageData);
    }
}