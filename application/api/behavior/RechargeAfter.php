<?php


namespace app\api\behavior;


use app\common\model\User;
use app\common\model\UserMission;
use app\common\model\UserStatistics;
use think\Config;
use think\Exception;

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
//        $this->mission();

        // 充值、首充任务 recharge first_recharge
        $now_time = time();
        $userMissionModel = new UserMission();
        $defaultStatus = $userMissionModel->getCurrentTableFieldConfig('status.default.value');
        $missions = UserMission::all(['user_id' => $user->id, 'mission_name' => 'recharge', 'status' => $defaultStatus]);
        foreach ($missions as $mission) {
            if(strtotime($mission->end_time) < $now_time){
                // 已结束的改变状态为未完成或已完成
                continue;
            }
        }
    }

    /**
     * 任务
     */
    public function mission()
    {
        $maxLevel = Config::get('site.max_team_level');
        $parents = (new \Nested($this->user))->getParent($this->user->id, $maxLevel - 1);
        $teams = [$this->user->id => $this->user];
        foreach ($parents as $parent) {
            $teams[$parent['id']] = $parent;
        }
        $ids = array_column($teams, 'id');
        $missions = UserMission::all(['user_id' => ['in',$ids], 'mission_name' => ['in',['login','sublogin']], 'status' => ['<', 3]]);

        if ($this->user->first_recharge) {
            // 首充
            $this->user->first_recharge = 0;
            UserStatistics::push('first_payment');
        }
        UserStatistics::push('payment_amount', $this->order->amount);
        User::money($this->user->id, $this->order->amount, '充值');
    }
}