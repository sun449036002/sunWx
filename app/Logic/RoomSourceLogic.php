<?php
namespace App\Logic;
use App\Model\AreaModel;
use App\Model\RoomCategoryModel;
use App\Model\RoomSourceModel;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 13:30
 */
class RoomSourceLogic extends BaseLogic
{

    public function getRoomSouceList() {
        $list = (new RoomSourceModel())->getList(['*'], ['isDel' => 0]);
        return $this->formatRoomList($list);
    }
    /**
     * 格式化房源列表
     * @param $roomList
     * @return array
     */
    public function formatRoomList($roomList) {
        if (empty($roomList)) return [];

        $cateArr = [];
        $categoryList = (new RoomCategoryModel())->getList(['id', 'name'], ['isDel' => 0]);
        foreach ($categoryList as $cate) {
            $cateArr[$cate->id] = $cate->name;
        }

        $areaArr = [];
        $areaList = (new AreaModel())->getList(['id', 'name'], ['isDel' => 0]);
        foreach ($areaList as $area) {
            $areaArr[$area->id] = $area->name;
        }

        foreach ($roomList as $key => $row) {
            $row->categoryName = empty($row->roomCategoryId) ? "" : ($cateArr[$row->roomCategoryId] ?? "未知");
            $row->area = empty($row->areaId) ? "" : ($areaArr[$row->areaId] ?? "未知");
            $row->cover = str_replace("http://picl.whatareu.top", env('IMG_DOMAIN'),$row->cover ?? "");
            $row->imgs = $row->imgs ?? [];
        }

        return $roomList;
    }

}