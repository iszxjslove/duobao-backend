<?php


namespace app\api\behavior;


use app\common\model\User;
use app\common\model\UserStatistics;

class UserRegisterSuccessed
{
    /**
     * @var User
     */
    protected $user;

    public function run(&$user)
    {
        $this->user = $user;
        // 统计注册
        UserStatistics::push(1,'register','register','注册用户');
    }
}