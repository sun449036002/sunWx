<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 20:00
 */

namespace App\Model;


class RedPackRecordModel extends BaseModel
{
    //红包折得助力记录表
    protected $table = "red_pack_record";

    /**
     * @param $columns
     * @param array $where
     * @param array $order
     * @param array $group
     * @return array
     */
    public function getList($columns, $where = [], $order = [], $group = []) {
        $list = parent::getList($columns, $where, $order, $group);
        $userModel = new UserModel();
        foreach ($list as $key => $item) {
            $_u = $userModel->getUserinfoByOpenid($item->userId);
            $list[$key]->nickname = $_u['username'] ?? "";
            $list[$key]->headImgUrl = headImgUrl($_u['avatar_url'] ?? "");
            $list[$key]->time = beforeWhatTime($item->createTime ?? 0);
        }
        //取得用户头像
        return $list;
    }

    //获取助力记录
    public function getAssistanceRecords($redPackId) {
        $list = $this->getList(['userId', 'money', 'createTime'], ['redPackId' => $redPackId]);

        $userModel = new UserModel();
        foreach ($list as $key => $item) {
            $_u = $userModel->getUserinfoByOpenid($item->userId);
            $list[$key]->headImgUrl = headImgUrl($_u['avatar_url'] ?? "");
            $list[$key]->time = beforeWhatTime($item->createTime);
        }
        //取得用户头像
        return $list;
    }
}