<?php


namespace app\api\behavior;


use app\common\model\User;
use app\common\model\UserStatistics;

class UserRegisterSuccessed
{
    public function run(&$user)
    {
        // 统计注册
        UserStatistics::push('register');
    }
}