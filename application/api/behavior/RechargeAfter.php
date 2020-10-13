<?php


namespace app\api\behavior;


use app\common\model\User;
use app\common\model\UserMission;
use app\common\model\UserStatistics;
use think\Config;
use think\Exception;
use think\Log;

class RechargeAfter
{
    /**
     * @var User
     */
    protected $user;

    protected $order;

    public function run(&$user, $order)
    {
        $this->user = $user;
        $this->order = $order;
        (new \Mission($user, 'recharge', $order))->execute();
    }
}