<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/12
 * Time: 17:20
 */

namespace App\Consts;


class StateConst {

    //红包未集满状态
    const RED_PACK_UN_FILL_UP = 0;

    //红包集满状态
    const RED_PACK_FILL_UP = 1;

    //红包使用审核中
    const RED_PACK_USING = 2;

    //红包已使用
    const RED_PACK_USED = 3;

    //红包的初始金额 单位元
    const RED_PACK_INIT_MONEY = 50;

}