<?php
namespace App\Console\Commands;

use App\Consts\StateConst;
use App\Consts\WxConst;
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
    /**
     * type 参数分为normal和use ，分别代表未集满过期与未使用过期
     * @var string
     */
    protected $signature = "NotifyRedPackExpiredCommand {--type=}";

    protected $description = "通知用户快要过期的红包";

    //提前多少时间通知
    private $notifyBeforeTime = 3 * 3600;
    private $useNotifyBeforeTime = 86400;

    public function handle() {
        $type = $this->option('type');
        if (empty($type)) {
            $this->info("type参数错误,不得为空");
            exit();
        }
        DB::connection()->enableQueryLog(); // 开启查询日志

        if ($type == 'use') {
            $where = [
                'isDel' => 0,
                'status' => StateConst::RED_PACK_FILL_UP,
                'useExpireNotified' => 0,
                ['useExpiredTime', "<=", time() + $this->useNotifyBeforeTime]
            ];
        } else if($type == 'normal') {
            $where = [
                'isDel' => 0,
                'status' => StateConst::RED_PACK_UN_FILL_UP,
                'expireNotified' => 0,
                ['expiredTime', "<=", time() + $this->notifyBeforeTime]
            ];
        }
        if (empty($where)) {
            $this->info('type参数错误,type=' . $type);
            exit();
        }

        $model = new RedPackModel();
        $list = $model->getList(['id', 'userId', "total", "received", "expiredTime", "useExpiredTime"],$where);

        $uids = [];
        if (!empty($list)) {
            foreach ($list as $item) {
                $uids[] = $item->userId;
            }

            $openidList = [];
            $userUriList  = [];
            $userNameList = [];
            $uids = array_unique($uids);
            $userList = (new UserModel())->getList(['id', 'uri', 'openid', 'username'], [['id', "in", $uids]]);
            if ($userList) {
                foreach ($userList as $user) {
                    $openidList[$user->id] = $user->openid;
                    $userNameList[$user->id] = $user->username;
                    $userUriList[$user->id] = $user->uri;
                }
            }

            $wxapp = Factory::officialAccount(getWxConfig());
            foreach ($list as $item) {
                if (!isset($openidList[$item->userId])) {
                    $this->warn("当前用户ID:" . $item->userId . "不在" . json_encode($openidList) .  "数组里面");
                    continue;
                }

                if ($type == 'use') {
                    //使用过期消息提醒
                    $wxapp->template_message->send([
                        'touser' => $openidList[$item->userId],
                        'template_id' => WxConst::TEMPLATE_ID_FOR_SEND_RED_PACK_EXPIRE_MSG,
                        'url' => env('APP_URL') . "/cash-red-pack-info?redPackId=" . $item->id,
                        'data' => [
                            'first' => [
                                'value' => "您有一个{$item->total}元的红包还未使用，快去看看吧~\r\n",
                                'color' => "#d22e20"
                            ],
                            'keyword1' => "",
                            'keyword2' => "过期时间:" . date("Y-m-d H:i:s", $item->useExpiredTime),
                            'keyword3' => '',
                            'keyword4' => date("Y-m-d H:i:s"),
                        ],
                    ]);

                    //更新为已通知
                    $model->updateData(['useExpireNotified' => 1], ['id' => $item->id]);

                } else if($type == 'normal') {
                    //收集过期消息提醒
                    $wxapp->template_message->send([
                        'touser' => $openidList[$item->userId],
                        'template_id' => WxConst::TEMPLATE_ID_FOR_SEND_RED_PACK_EXPIRE_MSG,
                        'url' => env('APP_URL') . "/cash-red-pack-info?redPackId=" . $item->id,
                        'data' => [
                            'first' => [
                                'value' => "您有一个{$item->total}元的红包快要过期啦，快去邀请好友助力吧 >> \r\n",
                                'color' => "#d22e20"
                            ],
                            'keyword1' => $userNameList[$item->userId] ?? "",
                            'keyword2' => $userUriList[$item->userId] ?? "",
                            'keyword3' => '现金红包',
                            'keyword4' => date("Y-m-d H:i:s", $item->expiredTime),
                        ],
                    ]);

                    //更新为已通知
                    $model->updateData(['expireNotified' => 1], ['id' => $item->id]);

                } else {
                    continue;
                }

                $this->info("红包ID：[{$item->id}],通知成功");
            }
        } else {
            $this->info("没有需要通知的红包");
        }
    }
}