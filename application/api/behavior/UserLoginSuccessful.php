<?php


namespace app\api\behavior;


use app\common\model\User;

class UserLoginSuccessful
{
    /**
     * @var User
     */
    protected $user;

    public function run(&$user)
    {
        $this->user = $user;
        // 触发登录任务
        (new \Mission($user, 'login', ''))->execute();
    }
}