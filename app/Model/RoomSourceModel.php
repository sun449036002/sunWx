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
            $row->imgJson = str_replace("memberl.whatareu.top", env('MEMBER_DOMAIN'),$row->imgJson);
            $imgs = json_decode($row->imgJson);
            $row->cover = $imgs->cover ?? "";
            $row->imgs = $imgs->imgs ?? [];
            unset($row->imgJson);
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
        $list = parent::getList($columns, $where, $order, $group);
        foreach ($list as $key => $row) {
            if (!empty($row->imgJson)) {
                $row->imgJson = str_replace("memberl.whatareu.top", env('MEMBER_DOMAIN'), $row->imgJson);
                $imgs = json_decode($row->imgJson);
                $row->cover = $imgs->cover ?? "";
                $row->imgs = $imgs->imgs ?? [];
                unset($row->imgJson);
            }
        }

        return $list;
    }
}