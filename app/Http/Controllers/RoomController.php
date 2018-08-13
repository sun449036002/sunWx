<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/11
 * Time: 15:29
 */

namespace App\Http\Controllers;


use App\Model\RoomSourceModel;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    //列表
    public function index() {

    }

    public function detail(Request $request) {
        $id = $request->get("id");

        $row = (new RoomSourceModel())->getOne(['*'], ['id' => $id]);
        $this->pageData['row'] = $row;
        $this->pageData['title'] = '详情 - ' . $row->name;
        return view('room/detail', $this->pageData);
    }

}