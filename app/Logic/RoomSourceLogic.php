<?php
namespace App\Logic;
use App\Model\AreaModel;
use App\Model\HouseTypeModel;
use App\Model\RoomCategoryModel;
use App\Model\RoomSourceModel;
use App\Model\RoomTagModel;

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

        //分类
        $cateArr = [];
        $categoryList = (new RoomCategoryModel())->getList(['id', 'name'], ['isDel' => 0]);
        foreach ($categoryList as $cate) {
            $cateArr[$cate->id] = $cate->name;
        }

        //户型
        $houseTypeArr = [];
        $houseTypeList = (new HouseTypeModel())->getList(['*'], ['isDel' => 0]);
        foreach ($houseTypeList as $houseType) {
            $houseTypeArr[$houseType->id] = $houseType->name;
        }

        //所在区域
        $areaArr = [];
        $areaList = (new AreaModel())->getList(['id', 'name'], ['isDel' => 0]);
        foreach ($areaList as $area) {
            $areaArr[$area->id] = $area->name;
        }

        //平台所有房源标签
        $tags = (new RoomTagModel())->getList(['*'], ['isDel' => 0]);

        foreach ($roomList as $key => $row) {
            $row->houseType = empty($row->houseTypeId) ? "" : ($houseTypeArr[$row->houseTypeId] ?? "未知");
            $row->categoryName = empty($row->roomCategoryId) ? "" : ($cateArr[$row->roomCategoryId] ?? "未知");
            $row->area = empty($row->areaId) ? "" : ($areaArr[$row->areaId] ?? "未知");

            //处理内容中的字体px 转 rem
            if (!empty($row->desc)) {
                $row->desc = preg_replace_callback("/font-size:([ ]*\d+)px/", function($res) {
                    if (!empty($res) && count($res) > 1) {
                        $originFontSize = $res[1];
                        if (is_numeric($originFontSize)) {
                            return str_replace($originFontSize . "px", $originFontSize * 2 / 100 . "rem", $res[0]);
                        }
                    }
                }, $row->desc);
            }

            //标签
            $tagNameList = [];
            if (!empty($row->roomTagIds)) {
                $tagIds = explode(",", $row->roomTagIds);
                foreach ($tags as $tag) {
                    if (in_array($tag->id, $tagIds)) {
                        $tagNameList[] = $tag->name;
                    }
                }
            }
            $row->tagNameList = $tagNameList;

            //图片有缩略图 用缩略图
            if (!empty($row->imgJson)) {
                $img = json_decode($row->imgJson);
                $row->cover = empty($img->cover) ? asset('imgs/no-img.jpg') : env('MEMBER_IMG_DOMAIN') . $img->cover;
                $row->cover = str_replace("room-source", 'room-source-thumbnail', $row->cover);

                //详情轮播图
                $otherImgs = [];
                foreach ($img->imgs as $k => $_img) {
                    $_img = str_replace("room-source", 'room-source-thumbnail', $_img);
                    $otherImgs[] = env('MEMBER_IMG_DOMAIN') . $_img;
                }
                $row->imgs = $otherImgs;

                //户型图
                $houseTypeImgs = [];
                foreach ($img->houseTypeImgs ?? [] as $k => $_img) {
                    $_img = str_replace("room-source", 'room-source-thumbnail', $_img);
                    $houseTypeImgs[] = env('MEMBER_IMG_DOMAIN') . $_img;
                }
                $row->houseTypeImgs = $houseTypeImgs;
            }
        }

        return $roomList;
    }
}