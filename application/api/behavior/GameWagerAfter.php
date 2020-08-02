<?php


namespace app\api\behavior;

use app\api\model\FeeLog;
use app\common\library\Auth;
use app\common\model\IssueSales;
use app\common\model\User;
use app\common\model\UserStatistics;

class GameWagerAfter
{
    /**
     * @var Auth
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
    }

    private function statistics()
    {
        // 测试
        if ($this->user->group->is_test) {
            return false;
        }
        // 统计数据
        UserStatistics::push($this->project['color'], $this->project['totalprice'], 'wager');
        UserStatistics::push('points', bcsub($this->project['totalprice'], $this->project['contract_amount'], 2), 'wager');
        IssueSales::push($this->project['issue_id'], $this->project['selected'], $this->project['totalprice'], $this->project['contract_amount']);

        // 分佣  ------------ START -------------
        $proportionOfFirstLevel = 0.3;
        $proportionOfSecondaryLevel = 0.2;
        if ($this->user->id) {
            // 一级分佣
            $firstLevelUser = User::get($this->user->pid);
            if ($firstLevelUser) {
                $firstLevelFee = bcmul($this->project['fee'], $proportionOfFirstLevel, 2);
                FeeLog::feeInc($firstLevelFee, $firstLevelUser->id, '一级投注佣金', $this->user->id, '1', $this->project['id']);
                // 统计数据
                UserStatistics::push('fee1', $firstLevelFee, 'fee');
                if ($firstLevelUser->pid) {
                    // 二级分佣
                    $secondaryLevelUser = User::get($firstLevelUser->pid);
                    if ($secondaryLevelUser) {
                        $secondaryLevelFee = bcmul($this->project['fee'], $proportionOfSecondaryLevel, 2);
                        FeeLog::feeInc($secondaryLevelFee, $secondaryLevelUser->id, '二级投注佣金', $this->user->id, '2', $this->project['id']);
                        // 统计数据
                        UserStatistics::push('fee2', $secondaryLevelFee, 'fee');
                    }
                }
            }
        }
        // -------------- END ------------------
        return true;
    }
}