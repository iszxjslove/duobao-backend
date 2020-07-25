<?php


namespace app\common\library;


use app\common\model\User;
use app\common\model\UserMission;
use app\common\model\UserMissionLog;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Request;

/**
 * Class Mission
 * @package app\common\library
 */
class Mission
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $names = [];

    /**
     * @var array
     */
    protected $userInfo = [];

    /**
     * @var int 要合计的量
     */
    protected $amount = 0;

    /**
     * @var array
     */
    protected $record = [];

    /**
     * @var false|string
     */
    protected $now;

    /**
     * @var array
     */
    protected $logs = [];

    /**
     * @var array
     */
    protected $parents = [];

    /**
     * @var array
     */
    protected $userMissions = [];

    /**
     * Mission constructor.
     * @param User $user
     * @throws Exception
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->userInfo = $user->visible(['id', 'username', 'nickname', 'loginip'])->toArray();
        $this->now = date('Y-m-d H:i:s');
    }

    /**
     * @throws DbException
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function loadParents()
    {
        $this->parents = User::getParentsByUser($this->user);
    }

    /**
     * @throws DbException
     * @throws Exception
     */
    public function insertLogs()
    {
        $this->loadParents();
        $this->loadMissions();

        $this->logs = [];
        foreach ($this->userMissions as $userMissions) {
            if ($userMissions->end_time < $this->now) {
                // 更新过期任务状态
                $this->setMissionEnd($userMissions);
                continue;
            }
            // 标记为进行中
            // 状态 0 接受 1 进行中 2 结束
            $userMissions->status = 1;
            if ($userMissions->user_id === $this->user->id && in_array($userMissions->name, $this->names['private'], true)) {
                // 自己的任务 （排除父级的）
                $this->setLogs($userMissions);
            } elseif (in_array($userMissions->name, $this->names['parent'], true) && in_array($userMissions->user_id, array_column((array)$this->parents, 'id'), true)) {
                // 父级的 下级任务 （排除自己的）
                $this->setLogs($userMissions);
            }else{
                continue;
            }
        }
        if(count($this->logs)){
            (new UserMissionLog)->insertAll($this->logs);
        }
    }

    /**
     * @param UserMission $userMission
     */
    public function setLogs(UserMission $userMission)
    {
        $this->logs[] = [
            'content'         => json_encode(array_merge($this->userInfo, $this->record)),
            'user_mission_id' => $userMission->id,
            'create_time' => $this->now
        ];
        ++$userMission->count_times;
        $userMission->sum_total += $this->amount;

        //达标条件 1 times 2 total 3 times & total 4 times | total
        switch ($userMission->standard_conditions) {
            case 1:
                if ($userMission->count_times >= $userMission->times) {
                    $this->setMissionEnd($userMission, 1);
                }
                break;
            case 3:
                if ($userMission->count_times >= $userMission->times && $userMission->sum_total >= $userMission->total) {
                    $this->setMissionEnd($userMission, 1);
                }
                break;
            case 2:
                if ($userMission->sum_total >= $userMission->total) {
                    $this->setMissionEnd($userMission, 1);
                }
                break;
            case 4:
                if ($userMission->count_times >= $userMission->times || $userMission->sum_total >= $userMission->total) {
                    $this->setMissionEnd($userMission, 1);
                }
                break;
        }
        $userMission->save();
    }

    /**
     * @param UserMission $userMission
     * @param int $finish
     */
    public function setMissionEnd(UserMission $userMission, $finish = 0)
    {
        $userMission->status = 2;
        $userMission->finish_status = $finish;
        if ($finish === 1) {
            $userMission->finish_time = $this->now;
        }
        $userMission->save();
    }

    /**
     * status 状态 0 接受 1 进行中 2 结束
     * @throws DbException
     */
    public function loadMissions()
    {
        $names = array_merge($this->names['private'], $this->names['parent']);
        $ids = array_column($this->getTeam(), 'id');
        $condition['status'] = ['<', 2];
        if (count($ids) > 1) {
            $condition['user_id'] = ['in', $ids];
        } else {
            $condition['user_id'] = $ids[0];
        }
        if ($names && count($names) > 1) {
            $condition['name'] = ['in', $names];
        } else {
            $condition['name'] = $names[0];
        }
        $this->userMissions = UserMission::all($condition);
    }

    /**
     * 获取团队
     * @return array
     */
    public function getTeam(): array
    {
        return array_merge((array)$this->parents, [$this->user]);
    }
}