<?php


use app\api\behavior\GameWagerAfter;
use app\api\behavior\RechargeAfter;
use app\api\behavior\UserLoginSuccessful;
use app\api\behavior\UserRegisterSuccessful;
use app\api\behavior\WithdrawAfter;

return [
    // 注册后
    'user_register_successful' => [
        UserRegisterSuccessful::class,
    ],
    // 登录后
    'user_login_successful'    => [
        UserLoginSuccessful::class
    ],
    // 充值后
    'recharge_after'           => [
        RechargeAfter::class
    ],
    // 投注后
    'game_wager_after'         => [
        GameWagerAfter::class
    ],
    // 提现后
    'withdraw_after'           => [
        WithdrawAfter::class
    ],
];

