<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/31
 * Time: 9:47
 */

namespace App\Console\Commands;


use App\Consts\CacheConst;
use App\Model\RedPackModel;
use App\Model\RedPackRecordModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedPackAssistanceCommand extends Command
{
    protected $signature = "RedPackAssistanceCommand";

    protected $description = "用户关注后，后续的助力数据插入到对应的红包";

    //最大助力次数
    private $maxAssistanceTimes = 5;

    public function handle() {
        $redPackModel = new RedPackModel();
        $redPackList = $redPackModel->getList(['id', 'total', 'received'], ['isDel' => 0, 'status' => 0, ['expiredTime', '>', time()]]);
        if (!empty($redPackList)) {
            $redPackRecordModel = new RedPackRecordModel();
            foreach ($redPackList as $redPack) {
                //剩余金额
                $remainderMoney = $redPack->total - $redPack->received;

                //助力用户
                $nowReceived = 0;
                $cacheKey = sprintf(CacheConst::RED_PACK_ASSISTANCE_LIST, $redPack->id);
                while ($item = Redis::lpop($cacheKey)) {
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
                    $nowReceived += $remainderMoney;
                }
                if ($nowReceived > 0) {
                    //更新红包  原始助力的金额 + 当前助力金额
                    $redPackModel->updateData(['received' => $redPack->received + $nowReceived], ['id' => $redPack->id]);
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