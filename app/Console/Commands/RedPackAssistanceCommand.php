<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/31
 * Time: 9:47
 */

namespace App\Console\Commands;


use App\Consts\CacheConst;
use App\Consts\WxConst;
use App\Model\RedPackModel;
use App\Model\RedPackRecordModel;
use App\Model\UserModel;
use EasyWeChat\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedPackAssistanceCommand extends Command
{
    protected $signature = "RedPackAssistanceCommand";

    protected $description = "用户关注后，后续的助力数据插入到对应的红包";

    private $wxapp = null;

    //最大助力次数
    private $maxAssistanceTimes = 5;

    public function handle() {
        $redPackModel = new RedPackModel();
        $userModel = new UserModel();

        //所有未完成且未过期的红包
        $redPackList = $redPackModel->getList(['id', 'userId', 'total', 'received'], ['isDel' => 0, 'status' => 0, ['expiredTime', '>', time()]]);
        if (!empty($redPackList)) {
            $this->wxapp = Factory::officialAccount(getWxConfig());

            $redPackRecordModel = new RedPackRecordModel();
            foreach ($redPackList as $redPack) {
                //剩余金额
                $remainderMoney = $redPack->total - $redPack->received;

                //助力用户
                $nowReceived = 0;
                $total = 0;
                $cacheKey = sprintf(CacheConst::RED_PACK_ASSISTANCE_LIST, $redPack->id);
                while ($item = Redis::lpop($cacheKey)) {
                    $this->info($item);
                    $assistanceData = json_decode($item, true);
                    //查询此红包是否助力了5次了
                    $total = $redPackRecordModel->where("redPackId", $redPack->id)->where('type', 1)->count();
                    if ($total >= $this->maxAssistanceTimes) {
                        $this->info('红包ID：' . $redPack->id . "，已经助力了" . $total . "次, 不再累计金额");
                        break;
                    }
                    $avgMoney = intval($remainderMoney / ($this->maxAssistanceTimes - $total));
                    $remainderMoney -= $avgMoney;

                    //助力记录
                    $redPackRecordModel->insert([
                        'redPackId' => $redPack->id,
                        'userId' => $assistanceData['userId'],
                        'type' => 1,
                        'money' => $avgMoney,
                        'createTime' => time()
                    ]);
                    //累计当前助力的金额
                    $nowReceived += $avgMoney;
                }
                if ($nowReceived > 0) {
                    //更新红包  原始助力的金额 + 当前助力金额
                    $redPackModel->updateData(['received' => $redPack->received + $nowReceived], ['id' => $redPack->id]);

                    //发送助力通知消息给用户
                    $who = $userModel->getOne(['openid'], ['id' => $redPack->userId]);
                    if (!empty($who)) {
                        $curAssistanceNum = $total + 1;
                        if ($curAssistanceNum < $this->maxAssistanceTimes) {
                            $this->wxapp->template_message->send([
                                'touser' => $who->openid,
                                'template_id' => WxConst::TEMPLATE_ID_FOR_SEND_HELP_MSG,
                                'url' => env('APP_URL') . "/cash-red-pack-info?redPackId=" . $redPack->id,
                                'data' => [
                                    'first' => [
                                        "value" => "有人给你的红包助力啦，\r当前共有{$curAssistanceNum}人助力",
                                        "color" => "#169ADA"
                                    ],
                                    'keyword1' => "现金红包",
                                    'keyword2' => '',
                                    'keyword3' => [
                                        "value" => "还差" . ($this->maxAssistanceTimes - $curAssistanceNum) . "人",
                                        'color' => '#d22e20'
                                    ],
                                    'keyword4' => date("Y-m-d H:i:s"),
                                ],
                            ]);
                        } else {
                            $this->wxapp->template_message->send([
                                'touser' => $who->openid,
                                'template_id' => WxConst::TEMPLATE_ID_FOR_SEND_HELP_MSG,
                                'url' => env('APP_URL') . "/cash-red-pack-info?redPackId=" . $redPack->id,
                                'data' => [
                                    'first' => [
                                        "value" => "您的红包已经助力满啦，快去看看吧~ 》》 \r\n",
                                        "color" => "#169ADA"
                                    ],
                                    'keyword1' => "现金红包",
                                    'keyword2' => '',
                                    'keyword3' => [
                                        "value" => "",
                                        'color' => '#d22e20'
                                    ],
                                    'keyword4' => date("Y-m-d H:i:s"),
                                ],
                            ]);
                        }
                    }


                    $this->info('红包ID：' . $redPack->id . "，当前助力金额为:" . ($redPack->received + $nowReceived));
                } else {
                    $this->info('红包ID：' . $redPack->id . "，此次缓存中没有助力金额");
                }
            }
        } else {
            $this->info('无红包数据');
        }
    }
}