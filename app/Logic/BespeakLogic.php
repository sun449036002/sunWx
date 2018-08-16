<?php
namespace App\Logic;
use App\Model\BespeakModel;
use App\Model\RoomSourceModel;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/16
 * Time: 14:38
 */
class BespeakLogic extends BaseLogic
{
    public function getBespeakList() {
        $list = (new BespeakModel())->getList(['*'], ['userId' => $this->user['id']]);
        if (!empty($list)) {
            $roomSourceModel = new RoomSourceModel();
            foreach ($list as $item) {
                $room = $roomSourceModel->getOne(['name', 'imgJson', 'areaId', 'roomCategoryId', 'houseTypeId'], ['id' => $item->roomId]);
                if (empty($room)) continue;
                list($room) = (new RoomSourceLogic())->formatRoomList([$room]);
                $item->roomSourceName = $room->name ?? "";
                $item->roomSourceCover = $room->cover ?? "";
                $item->area = $room->area ?? "";
            }
        }
//        dd($list);

        return $list;
    }

    /**
     * 获取预约详情
     * @param $id
     * @return object
     */
    public function getById($id) {
        $row = (new BespeakModel())->getOne(['*'], ['id' => $id]);
        if (!empty($row)) {
            $room = (new RoomSourceModel())->getOne(['name', 'imgJson', 'areaId', 'roomCategoryId', 'houseTypeId'], ['id' => $row->roomId]);
            list($room) = (new RoomSourceLogic())->formatRoomList([$room]);
            $row->roomSourceName = $room->name ?? "";
            $row->roomSourceCover = $room->cover ?? "";
            $row->area = $room->area ?? "";
        }

        return $row;
    }

}