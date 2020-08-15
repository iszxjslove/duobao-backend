<?php


namespace app\api\behavior;


use app\common\model\Mission;
use app\common\model\UserMission;
use app\common\model\UserStatistics;

class UserRegisterSuccessful
{
    public function run(&$user)
    {
        // 统计注册
        UserStatistics::push('register');

        // 获取首次登录任务自动领取
        $firstLoginMission = Mission::getMission('first_login');
        foreach ($firstLoginMission as $item) {
            UserMission::receive($user->id, $item);
        }
        // 触发注册任务
        (new \Mission($user, 'register', ''))->execute();
    }
}