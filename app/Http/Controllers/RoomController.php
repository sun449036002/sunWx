<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/11
 * Time: 15:29
 */

namespace App\Http\Controllers;


use App\Logic\RoomSourceLogic;
use App\Model\AreaModel;
use App\Model\HouseTypeModel;
use App\Model\RoomCategoryModel;
use App\Model\RoomSourceModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    //列表
    public function index(Request $request) {
        $this->pageData['title'] = "房源列表";
        $this->pageData['keyword'] = $request->get("keyword", '');
        return view('room/list', $this->pageData);
    }

    /**
     * Ajax 获取房源列表
     * @param Request $request
     * @return string
     */
    public function getRoomList(Request $request) {
//        DB::connection()->enableQueryLog(); // 开启查询日志

        $type = $request->get('type', 1);
        $keyword = $request->get('keyword');
        $isRecommend = $request->get('recommend');
        $minPrice = intval($request->get('minPrice'));
        $maxPrice = intval($request->get('maxPrice'));
        $minAcreage = intval($request->get('minAcreage'));
        $maxAcreage = intval($request->get('maxAcreage'));
        $areaId = $request->get('areaId');
        $houseTypeId = $request->get('houseTypeId');
        $categoryId = $request->get('categoryId');
        $page = $request->get('page', 1);

        $where = ['type' => $type, 'isDel' => 0];
        if (!empty($keyword)) {
            $where[] = ['name', 'like', '%' . trim($keyword) . '%'];
        }
        //是否推荐
        if (!empty($isRecommend)) {
            $where['isRecommend'] = 1;
        }
        //类别
        if (!empty($categoryId)) {
            $where['roomCategoryId'] = $categoryId;
        }
        //户型
        if (!empty($houseTypeId)) {
            $where['houseTypeId'] = $houseTypeId;
        }
        //地域
        if (!empty($areaId)) {
            $where['areaId'] = $areaId;
        }
        //均价范围
        if (!empty($minPrice) && !empty($maxPrice)) {
            $where[] = ["avgPrice", ">=", $minPrice];
            $where[] = ['avgPrice', '<=', $maxPrice];
        } else if (!empty($minPrice)) {
            $where[] = ["avgPrice", "<=", $minPrice];
        } else if(!empty($maxPrice)) {
            $where[] = ['avgPrice', '>=', $maxPrice];
        }
        //面积范围
        if (!empty($minAcreage) && !empty($maxAcreage)) {
            $where[] = ["acreage", ">=", $minAcreage];
            $where[] = ['acreage', '<=', $maxAcreage];
        } else if (!empty($minAcreage)) {
            $where[] = ["acreage", "<=", $minAcreage];
        } else if(!empty($maxAcreage)) {
            $where[] = ['acreage', '>=', $maxAcreage];
        }

        $pageSize =10;
        $offset = ($page - 1) * $pageSize;
        //取得所有推荐的房源
        $roomSourceModel = new RoomSourceModel();
        $roomList = $roomSourceModel->select(['id', "type", "roomCategoryId", "name", "areaId", "avgPrice", "totalPrice", "imgJson"])
            ->where($where)->offset($offset)->limit($pageSize)->get();

//        dd(DB::getQueryLog());

        $roomList = (new RoomSourceLogic())->formatRoomList($roomList);

        return ResultClientJson(0, '数据获取成功', ['list' => $roomList, 'isEnd' => empty($roomList)]);
    }

    public function detail(Request $request) {
        $id = $request->get("id");

        $row = (new RoomSourceModel())->getOne(['*'], ['id' => $id]);
        list($row) = (new RoomSourceLogic())->formatRoomList([$row]);
        $this->pageData['row'] = $row;
        $this->pageData['title'] = '详情 - ' . $row->name;
        return view('room/detail', $this->pageData);
    }

    /**
     * 地域列表
     * @return array
     */
    public function getAreaList() {
        return (new AreaModel())->getList(['*'], ['isDel' => 0]);
    }

    /**
     * 户型列表
     * @return array
     */
    public function getHouseTypeList() {
        return (new HouseTypeModel())->getList(['*'], ['isDel' => 0]);
    }

    /**
     * 房源类别列表
     * @return array
     */
    public function getCategoryList() {
        return (new RoomCategoryModel())->getList(['*'], ['isDel' => 0]);
    }

}