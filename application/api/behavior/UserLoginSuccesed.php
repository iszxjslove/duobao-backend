<?php


namespace app\api\behavior;


use app\common\library\mission\Login;
use app\common\model\User;
use app\common\model\UserMission;
use think\Config;
use think\Exception;
use think\Request;

class UserLoginSuccesed
{
    /**
     * @var User
     */
    protected $user;

    public function run(&$user)
    {
        $this->user = $user;

        // 登录任务
//        $loginMission = new Login($user);
//        $loginMission->insertLogs();
    }
}