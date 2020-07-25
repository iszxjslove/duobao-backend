<?php


namespace app\api\behavior;


use app\common\model\User;
use app\common\model\UserMission;
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
        $this->mission();
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
dump($ids);
//        $missions = UserMission::all(['user_id' => ['in',$ids], 'mission_name' => ['in',['login','sublogin']], 'status' => ['<', 3]]);

    }
}