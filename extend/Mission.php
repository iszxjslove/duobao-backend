<?php


use app\common\model\UserMission;
use app\common\model\UserMissionLog;
use think\Config;
use think\exception\DbException;

class Mission
{
    protected $user;

    /**
     * @var string 任务组名称
     */
    protected $group_name;

    protected $model;

    protected $status;

    protected $record;

    /**
     * @var string 错误信息
     */
    protected $_error = '';

    /**
     * @var int 当前时间
     */
    protected $now_time = 0;

    protected $parents = [];

    protected $team = [];

    public function __construct(\app\common\model\User $user, $group_name, $record)
    {
        $this->user = $user;
        $this->group_name = $group_name;
        $this->record = $record;
        $this->model = new UserMission();
        $this->status = $this->model->getCurrentTableFieldConfig('status');
        $this->now_time = time();
        $maxLevel = Config::get('site.max_team_level');
        $this->parents = (new \Nested($this->user))->getParent($this->user->id, $maxLevel - 1);
        $this->team = [$this->user->id => $this->user];
        foreach ($this->parents as $parent) {
            $this->team[$parent['id']] = $parent;
        }
    }

    /**
     * 用户进行中的任务
     * @param $user_ids
     * @return UserMission[]|false
     * @throws DbException
     */
    public function getAllMission($user_ids)
    {
        return UserMission::all(['user_id' => ['in', $user_ids], 'group_name' => $this->group_name, 'status' => $this->status['default']['value']]);
    }

    /**
     * @throws DbException
     */
    public function execute()
    {
        $missions = $this->getAllMission(array_column($this->team, 'id'));
        foreach ($missions as $mission) {
            if (!$this->isExecutable($mission)) {
                continue;
            }
            $this->setInc($mission);
            if ($this->checkComplete($mission)) {
                $this->setSuccess($mission);
            }
        }
    }

    public function isExecutable($mission): bool
    {
        // 状态不是进行中\未开始退出
        if (!$mission || $mission->status === $this->status['default'] || $mission->start_time > $this->now_time) {
            return false;
        }
        // 已完成的
        if ($mission->end_time < $this->now_time) {
            // 设置状态
            $this->setOver($mission);
            return false;
        }
        // 私有任务但不是自己的
        if ($mission->method === 'private' && $mission->user_id !== $this->user->id) {
            return false;
        }
        // 父级任务但是自己的
        if ($mission->method === 'parent' && $mission->user_id === $this->user->id) {
            return false;
        }

        if ($mission->level < $this->getLevel($mission->user_id)) {
            return false;
        }
        return true;
    }

    /**
     * @param $user_id
     * @return bool|int
     */
    protected function getLevel($user_id)
    {
        if (empty($this->team[$user_id])) {
            return false;
        }
        return $this->user->depth - $this->team[$user_id]['depth'];
    }

    /**
     * 设置状态
     * @param $mission
     * @return bool|mixed
     */
    protected function setOver($mission)
    {
        // 检查完成条件
        if ($this->checkComplete($mission)) {
            return $this->setSuccess($mission);
        }
        return $this->setFail($mission);
    }

    /**
     * 设置失败
     * @param $mission
     * @return mixed
     */
    protected function setFail($mission)
    {
        $mission->status = $this->status['fail']['value'];
        return $mission->save();
    }

    /**
     * 设置成功
     * @param $mission
     * @return bool
     */
    protected function setSuccess($mission): bool
    {
        $mission->status = $this->status['success']['value'];
        $mission->finish_time = date('Y-m-d H-i-s');
        if ($mission->bonus) {
            \app\common\model\User::money($mission->user_id, $mission->bonus, "任务奖励：{$mission->title}");
        }
        UserMission::update(['id' => $mission['id'], 'status' => $this->status['success']['value'], 'finish_time' => date('Y-m-d H-i-s')]);
        return true;
    }

    protected function setInc($mission)
    {
        if ($mission->times) {
            $mission->count_times++;
        }
        $amount = 0;
        if ($mission->total) {
            $amount = $this->record[$mission['total_field']];
            $mission->sum_total += $amount;
        }
        $record = $this->record ?: [];
        if (is_object($this->record)) {
            $record = $this->record->toArray();
        }
        UserMissionLog::create([
            'user_id'         => $mission->user_id,
            'user_mission_id' => $mission->id,
            'content'         => json_encode($record),
            'amount'          => $amount
        ]);
        return $mission->save();
    }

    /**
     * 检查完成条件
     * @param $mission
     * @return bool
     */
    protected function checkComplete($mission): bool
    {
        return (!$mission->total || $mission->sum_total >= $mission->total) && (!$mission->times || $mission->count_times >= $mission->times);
    }

    /**
     * @param string $msg
     * @throws \think\Exception
     */
    protected function setError($msg = '')
    {
        $this->_error = $msg;
        throw new \think\Exception($msg);
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }
}