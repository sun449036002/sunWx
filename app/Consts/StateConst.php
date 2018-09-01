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


    //余额状态 - 红包收入
    const BALANCE_RED_PACK_INCOME = 0;

    //余额状态 - 提现申请
    const BALANCE_WITHDRAW_APPLY = 1;

    //余额状态 - 提现支出
    const BALANCE_WITHDRAW_EXPENDITURE = 2;

    //余额状态 - 红包收入
    const BALANCE_WITHDRAW_REJECT = 3;

    //余额收入支出状态 - 收入
    const BALANCE_IN = 1;

    //余额收入支出状态 - 支出
    const BALANCE_OUT = 0;

}