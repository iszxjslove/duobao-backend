<?php


namespace app\api\behavior;


use app\common\model\User;
use app\common\model\UserMission;
use mission\Login;
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
        // 触发登录任务
        (new Login($user, 'login', ''))->execute();
    }
}