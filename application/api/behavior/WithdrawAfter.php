<?php


namespace app\api\behavior;


use app\common\model\User;
use app\common\model\UserStatistics;

class WithdrawAfter
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
        UserStatistics::push('withdraw_amount', $this->order->amount);
        (new \Mission($user, 'withdraw', $order))->execute();
    }
}