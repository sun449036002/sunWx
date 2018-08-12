<?php
namespace App\Console\Commands;

use App\Consts\StateConst;
use App\Model\RedPackModel;
use App\Model\UserModel;
use EasyWeChat\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

    //提前多少时间通知
    private $notifyBeforeTime = 3 * 3600;

    public function handle() {
        DB::connection()->enableQueryLog(); // 开启查询日志
//        DB::table('red_pack'); // 要查看的sql
        $model = new RedPackModel();
        $list = $model->getList(
            ['id', 'userId', "total", "received", "expiredTime"],
            [
                'isDel' => 0,
                'status' => StateConst::RED_PACK_UN_FILL_UP,
                'expireNotified' => 0,
                ['expiredTime', "<=", time() + $this->notifyBeforeTime]
            ]
        );


//        var_dump(DB::getQueryLog());

        $uids = [];
        if (!empty($list)) {
            foreach ($list as $item) {
                $uids[] = $item->userId;
            }
//            dd($uids);

            $openidList = [];
            $uids = array_unique($uids);
            $userList = (new UserModel())->getList(['id', 'openid'], [['id', "in", $uids]]);
//            var_dump(DB::getQueryLog());
            if ($userList) {
                foreach ($userList as $user) {
                    $openidList[$user->id] = $user->openid;
                }
            }

            $wxapp = Factory::officialAccount(getWxConfig());
            foreach ($list as $item) {
//                var_dump($openidList);
                if (!isset($openidList[$item->userId])) continue;
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

                //更新为已通知
                $model->updateData(['expireNotified' => 1], ['id' => $item->id]);

                $this->info("红包ID：[{$item->id}],通知成功");
            }
        }

        $this->info("没有需要通知的红包");
    }
}