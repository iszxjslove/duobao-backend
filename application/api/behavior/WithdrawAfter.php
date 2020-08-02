<?php


namespace app\api\behavior;


use app\common\model\UserStatistics;

class WithdrawAfter
{


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
    public function mission($amount = 10)
    {
        UserStatistics::push('withdraw', $amount);
    }
}