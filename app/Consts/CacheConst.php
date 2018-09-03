<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20
 * Time: 10:27
 */

namespace App\Consts;

class CacheConst {

    //今日关注用户数 %s => Ymd
    const TODAY_SUBSCRIBE_NUM = "today_subscribe_num_%s";

    //红包赠送凭证 %s => 红包ID 值为用户ID
    const RED_PACK_GRANT_TICKET = "red_pack_grant_ticket_%s";

    //我的临时凭证缓存 $s => openid, %s => 红包ID
    const MY_TEMP_TICKET = "my_temp_ticket_%s_%s";

    //今天是否助力过此红包 %s => 红包ID
    const RED_PACK_HAS_ASSISTANCE = "red_pack_has_assistance_%s";

    //红包助力关注数据 %s => red pack id    关注后，入缓存，定时脚本每分钟消费此缓存，插入助力数据 有一分种左右的延时
    const RED_PACK_ASSISTANCE_LIST = "red_pack_assistance_list_%s";

    //用户不间断签到计数器  %s => 用户ID
    const USER_UNINTERRUPTED_SIGN_IN_COUNT = 'user_uninterrupted_sign_in_count_%s';

    //用户ID 对接的推广二维码 %s ===> adminId (推广业务员的后台账号ID)
    const QR_CODE_FOR_ADMIN_USER = 'qr_code_for_admin_user_%s';

    //关注二维码场景数据 %s 当前将要关注用户的openid
    const QR_CODE_SCENE_DATA = "qr_code_scene_data_%s";

    //用户opneid 关联 Admin ID %s => openid
    const USER_ADMIN_ID = 'user_admin_id_%s';
}