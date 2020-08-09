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
        $this->mission();
    }

    /**
     * 任务
     * @param int $amount
     */
    public function mission()
    {
        User::hold_balance($this->user->id, -$this->order->amount, '提现');
        UserStatistics::push('withdraw_amount', $this->order->amount);
    }
}