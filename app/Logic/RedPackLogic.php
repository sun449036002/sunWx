<?php
namespace App\Logic;
use App\Consts\StateConst;
use App\Model\RedPackModel;
use App\Model\RedPackRecordModel;
use App\Model\UserModel;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/4
 * Time: 9:47
 */
class RedPackLogic extends BaseLogic
{

    /**
     * 获取我可用的红包
     * @return array
     */
    public function getMyEnabledRedPacks() {
        $where = [
            'status' => StateConst::RED_PACK_FILL_UP,
            'userId' => $this->user['id'],
            'fromUserId' => 0,
            ["useExpiredTime", ">", time()]
        ];
        $list = (new RedPackModel())->getList(['*'], $where);
        return $list;
    }

    /**
     * 获取我的红包可用余额 （排除朋友送的）
     */
    public function getRedPackBalance() {
        $list = $this->getMyEnabledRedPacks();
        $balance = 0;
        foreach ($list as $item) {
            $balance += $item->total;
        }

        return $balance;
    }

    /**
     * 红包播报列表
     */
    public function getBroadcastList($pageSize = 10) {
        $model = new RedPackRecordModel();
        $rows = $model->join("red_pack as b", "redPackId", "=", "b.id")
            ->select(['b.userId', 'red_pack_record.money', "red_pack_record.createTime"])
            ->groupBy("red_pack_record.id", "redPackId", "userId", "money", "createTime")
            ->orderBy("red_pack_record.id", "desc")
            ->limit($pageSize)->get();
        if (!empty($rows)) {
            $userModel = new UserModel();
            foreach ($rows as $row) {
                $_u = $userModel->getUserinfoByOpenid($row->userId);
                $row->nickname = $_u['username'] ?? "";
                $row->headImgUrl = $_u['avatar_url'] ?? "";
                $row->time = beforeWhatTime($row->createTime ?? 0);
            }
        }

        return $rows;
    }
}