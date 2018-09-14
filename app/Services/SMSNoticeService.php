<?php
namespace App\Services;
use App\Consts\WxConst;
use App\Model\SystemModel;
use Illuminate\Support\Facades\Log;
use Qcloud\Sms\SmsSingleSender;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 10:51
 */
class SMSNoticeService
{

    /**
     * @param int $templateId 短信模板ID，需要在短信应用中申请
     * @param array $params [$name, $dateTime];//对应模板里面的{1}和{2}的位置，对应替换成相应内容
     * @return bool
     */
    public function sendNotice($templateId, $params) {
        if (empty($templateId) || empty($params)) {
            Log::info('短信发送参数：', ['必要参数为空', $templateId, $params]);
            return false;
        }

        $systemModel = new SystemModel();
        $system = $systemModel->getOne(['smsTel'], null);
        $telNumber = $system->smsTel ?? "";
        if (!empty($telNumber) && is_numeric($telNumber)) {
            // 签名
            $smsSign = "雍今利杭州房地产公司"; // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`
            // 单发短信
            try {
                $ssender = new SmsSingleSender(WxConst::TX_SMS_APP_ID, WxConst::TX_SMS_APP_KEY);
                $result = $ssender->sendWithParam("86", $telNumber, $templateId, $params, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
                Log::info('短信发送结果：', [$result]);
                return true;
            } catch(\Exception $e) {
                Log::info('短信发送异常：', [$e]);
                return false;
            }
        }

        return false;
    }
}