<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/11
 * Time: 15:29
 */

namespace App\Http\Controllers;


use App\Model\RoomCategoryModel;
use App\Model\RoomSourceModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    //列表
    public function index() {
        $this->pageData['title'] = "房源列表";
        return view('room/list', $this->pageData);

    }

    /**
     * Ajax 获取房源列表
     * @param Request $request
     * @return string
     */
    public function getRoomList(Request $request) {
        $keyword = $request->get('keyword');
        $isRecommend = $request->get('recommend');
        $page = $request->get('page', 1);

        $where = ['isDel' => 0];
        if (!empty($keyword)) {
            $where[] = ['name', 'like', '%' . trim($keyword) . '%'];
        }

        if (!empty($isRecommend)) {
            $where['recommend'] = 1;
        }

        $pageSize =10;
        $offset = ($page - 1) * $pageSize;
        //取得所有推荐的房源
        $roomSourceModel = new RoomSourceModel();
        $roomList = $roomSourceModel->select(['id', "type", "roomCategoryId", "name", "area", "avgPrice", "totalPrice", "imgJson"])
            ->where($where)->offset($offset)->limit($pageSize)->get();

        $cateArr = [];
        $categoryList = (new RoomCategoryModel())->getList(['id', 'name'], ['isDel' => 0]);
        foreach ($categoryList as $cate) {
            $cateArr[$cate->id] = $cate->name;
        }

        foreach ($roomList as $key => $row) {
            $row->categoryName = empty($row->roomCategoryId) ? "" : ($cateArr[$row->roomCategoryId] ?? "未知");
            if (!empty($row->imgJson)) {
                $imgs = json_decode($row->imgJson);
                $row->cover = $imgs->cover ?? "";
                $row->imgs = $imgs->imgs ?? [];
                unset($row->imgJson);
            }
        }

        return ResultClientJson(0, '数据获取成功', ['list' => $roomList, 'isEnd' => empty($roomList)]);
    }

    public function detail(Request $request) {
        $id = $request->get("id");

        $row = (new RoomSourceModel())->getOne(['*'], ['id' => $id]);
        $this->pageData['row'] = $row;
        $this->pageData['title'] = '详情 - ' . $row->name;
        return view('room/detail', $this->pageData);
    }

}