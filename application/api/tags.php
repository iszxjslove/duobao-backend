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
    // 投注后
    'game_wager_after'         => [
        GameWagerAfter::class
    ]
];

