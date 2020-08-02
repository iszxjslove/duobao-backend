<?php


use app\api\behavior\GameWagerAfter;
use app\api\behavior\RechargeAfter;
use app\api\behavior\UserLoginSuccesed;
use app\api\behavior\UserRegisterSuccessed;
use app\api\behavior\WithdrawAfter;

return [
    // 注册后
    'user_register_successed' => [
        UserRegisterSuccessed::class,
    ],
    // 登录后
    'user_login_successed'    => [
        UserLoginSuccesed::class
    ],
    // 充值后
    'recharge_after'          => [
        RechargeAfter::class
    ],
    // 投注后
    'game_wager_after'        => [
        GameWagerAfter::class
    ],
    // 提现后
    'withdraw_after'          => [
        WithdrawAfter::class
    ],
];

