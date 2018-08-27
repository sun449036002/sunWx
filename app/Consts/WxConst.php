<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/12
 * Time: 18:58
 */

namespace App\Consts;


class WxConst
{
    //助力消息模板ID
    const TEMPLATE_ID_FOR_SEND_HELP_MSG = '82y_cNd0iWws8JUkRXgVolIkCVqYXYZkxL34RdBUIVg';

    //红包过期提醒消息模板ID
    const TEMPLATE_ID_FOR_SEND_RED_PACK_EXPIRE_MSG = '82y_cNd0iWws8JUkRXgVolIkCVqYXYZkxL34RdBUIVg';

    //用户ID 对接的推广二维码 %s ===> adminId (推广业务员的后台账号ID) %s => 红包ID
    const QR_CODE_FOR_ADMIN_USER = 'qr_code_for_admin_user_%s_%s';

    //腾讯短信APP ID
    const TX_SMS_APP_ID = "1400133557";

    //腾讯短信APP KEY
    const TX_SMS_APP_KEY = "f5d9df69f328d35406c7e8d7a296764d";
}