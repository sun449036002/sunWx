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
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->user = $this->getUserinfo($request);
        //获取此用户是否签到过
        $model = new SigninModel();
        $row = $model->getOne("id", ['user_id' => $this->user['id'], 'date' => date("Ymd")]);

        $data['isSignIn'] = !empty($row);
        $data['wxapp'] = $this->wxapp;

        return view('index', $data);
    }
}