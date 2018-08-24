<?php
namespace App\Logic;
use App\Consts\StateConst;
use App\Model\RedPackModel;

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
}