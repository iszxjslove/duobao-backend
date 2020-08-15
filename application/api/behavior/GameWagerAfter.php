<?php


namespace app\api\behavior;

use app\api\model\FeeLog;
use app\common\model\IssueSales;
use app\common\model\User;
use app\common\model\UserStatistics;
use think\Config;

class GameWagerAfter
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $project = [];

    public function run(&$user, $data)
    {
        $this->user = $user;
        $this->project = $data;
        // 统计数据
        $this->statistics();
        (new \Mission($user, 'wager', $data))->execute();
    }

    private function statistics(): bool
    {
        // 测试账户
        if ($this->user->group->is_test) {
            return false;
        }
        // 统计下注手续费
        UserStatistics::push('wager_points', $this->project['fee'], 'wager');
        UserStatistics::push('wager_totalprice', $this->project['fee'], 'wager');
        // 奖期销售数据
        IssueSales::push($this->project['issue_id'], $this->project['selected'], $this->project['totalprice'], $this->project['contract_amount']);

        // 分佣  ------------ START -------------
        $team_fees = Config::get('site.team_fees');
        $parents = User::getParentsByUser($this->user);
        foreach ($parents as $parent) {
            $lv = $this->user->depth - $parent['depth'];
            $rate = $team_fees[$lv] ?? 0;
            if ($rate) {
                $fee = bcmul($this->project['fee'], bcdiv($rate, 100, 2), 2);
                FeeLog::feeInc($fee, $parent['id'], "{$lv}级投注佣金", $this->user->id, $lv, $this->project['id']);
                // 统计数据
                UserStatistics::push("wager_fee{$lv}", $fee, 'wager_fee');
            }
        }
        // -------------- END ------------------
        return true;
    }
}