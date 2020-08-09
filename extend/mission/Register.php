<?php


namespace mission;


use think\exception\DbException;

class Register extends \Mission
{

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
            $mission->count_times++;
            if ($this->checkComplete($mission)) {
                $this->setSuccess($mission);
            }
        }
    }
}