<?php
namespace App\Console\Commands;

use App\Model\RedPackModel;
use App\Model\UserModel;
use EasyWeChat\Factory;
use Illuminate\Console\Command;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/12
 * Time: 15:33
 */
class NotifyRedPackExpiredCommand extends Command
{
    protected $signature = "NotifyRedPackExpiredCommand";

    protected $description = "通知用户快要过期的红包";

    public function handle() {
        $model = new RedPackModel();
        $list = $model->getList(
            ['id', 'userId', "total", "received", "expiredTime"],
            ['isDel' => 0, 'status' => RED_PACK_UN_FILL_UP, ['expiredTime', ">=", time() + 3 * 3600]]
        );

        $uids = [];
        if (!empty($list)) {
            foreach ($list as $item) {
                $uids[] = $item->userId;
            }

            $openidList = [];
            $uids = array_unique($uids);
            $userList = (new UserModel())->getList(['id', 'openid'], ['id', "in", $uids]);
            if ($userList) {
                foreach ($userList as $user) {
                    $openidList[$user->id] = $user->openid;
                }
            }

            $wxapp = Factory::officialAccount(getWxConfig());
            foreach ($list as $item) {
                $wxapp->template_message->send([
                    'touser' => $openidList[$item->userId],
                    'template_id' => '82y_cNd0iWws8JUkRXgVolIkCVqYXYZkxL34RdBUIVg',
                    'url' => env('APP_URL') . "/cash-red-pack-info?redPackId=" . $item->id,
                    'data' => [
                        'first' => "您有一个{$item->total}元的红包快要过期啦，快去邀请好友助力吧~",
                        'keyword1' => "还差" . ($item->total - $item->received) . "元",
                        'keyword2' => "过期时间:" . date("Y-m-d H:i:s", $item->expiredTime),
                        'keyword3' => '',
                        'keyword4' => date("Y-m-d H:i:s"),
                    ],
                ]);
            }
        }
    }
}