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
}