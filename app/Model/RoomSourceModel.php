<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/1
 * Time: 20:17
 */

namespace App\Model;


class RoomSourceModel extends BaseModel
{
    protected $table = "room_source";

    /**
     * @param $columns
     * @param $where
     * @param array $order
     * @param array $group
     * @return object
     */
    public function getOne($columns, $where, $order = [], $group = []) {
        $row = parent::getOne($columns, $where, $order, $group);
        if (!empty($row->imgJson)) {
            $imgs = json_decode($row->imgJson);
            $row->cover = $imgs->cover ?? "";
            $row->imgs = $imgs->imgs ?? [];
        }
        return $row;
    }

    /**
     * @param $columns
     * @param array $where
     * @param array $order
     * @param array $group
     * @return array
     */
    public function getList($columns, $where, $order = [], $group = []) {
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

        $list = parent::getList($columns, $where, $order, $group);
        foreach ($list as $key => $row) {
            $row->categoryName = empty($row->roomCategoryId) ? "" : ($cateArr[$row->roomCategoryId] ?? "未知");
            $row->area = empty($row->areaId) ? "" : ($areaArr[$row->areaId] ?? "未知");
            if (!empty($row->imgJson)) {
                $imgs = json_decode($row->imgJson);
                $row->cover = $imgs->cover ?? "";
                $row->imgs = $imgs->imgs ?? [];
            }
        }

        return $list;
    }
}