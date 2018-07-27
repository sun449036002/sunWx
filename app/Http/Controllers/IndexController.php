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
        return view('index');
    }

}