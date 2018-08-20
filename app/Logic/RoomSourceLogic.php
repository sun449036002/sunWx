<?php
namespace App\Logic;
use App\Model\AreaModel;
use App\Model\HouseTypeModel;
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
     * 获取我收藏的房源
     * @return array
     */
    public function getMarkRoomList() {
        $list = (new RoomSourceModel())->join("room_source_mark as m", "room_source.id", "=", "m.roomId")->select("room_source.*")->where("m.userId", $this->user['id'])->get();
        if (!empty($list)) {
            $list = $this->formatRoomList($list);
            return $list;
        }

        return [];
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

        $houseTypeArr = [];
        $houseTypeList = (new HouseTypeModel())->getList(['*'], ['isDel' => 0]);
        foreach ($houseTypeList as $houseType) {
            $houseTypeArr[$houseType->id] = $houseType->name;
        }

        $areaArr = [];
        $areaList = (new AreaModel())->getList(['id', 'name'], ['isDel' => 0]);
        foreach ($areaList as $area) {
            $areaArr[$area->id] = $area->name;
        }

        foreach ($roomList as $key => $row) {
            $row->houseType = empty($row->houseTypeId) ? "" : ($houseTypeArr[$row->houseTypeId] ?? "未知");
            $row->categoryName = empty($row->roomCategoryId) ? "" : ($cateArr[$row->roomCategoryId] ?? "未知");
            $row->area = empty($row->areaId) ? "" : ($areaArr[$row->areaId] ?? "未知");

            //封面图片有缩略图 用缩略图
            if (!empty($row->imgJson)) {
                $img = json_decode($row->imgJson);
                $row->cover = empty($img->cover) ? "" : env('MEMBER_IMG_DOMAIN') . $img->cover;
                $row->cover = str_replace("room-source", 'room-source-thumbnail', $row->cover);

                //其他图片
                $otherImgs = [];
                foreach ($img->imgs as $k => $_img) {
                    $otherImgs[] = env('MEMBER_IMG_DOMAIN') . $_img;
                }
                $row->imgs = $otherImgs;
            }
        }

        return $roomList;
    }
}