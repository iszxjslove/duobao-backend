<?php


namespace app\common\model;


use think\Model;

class UserMission extends Model
{
    protected $name = 'user_mission';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $append = [
        'create_time_text'
    ];


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ?: $data['create_time'] ?? '';
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    /**
     * @param User $user
     * @param Mission $mission
     * @return UserMission
     */
    public static function receive(User $user, Mission $mission): UserMission
    {
        $insertData = [
            'user_id' => $user->id,
            'mission_id' => $mission->id,
            'group_name' => $mission->name,
            'title' => $mission->title,
            'desc' => $mission->desc,
            'times' => $mission->times,
            'count_times' => 0,
            'total' => $mission->total,
            'sum_total' => 0,
            'standard_conditions' => $mission->standard_conditions,
            'start_time' => $mission->start_time,
            'end_time' => $mission->end_time,
            'finish_time' => 0,
            'finish_status' => 0,
            'status' => 0,
        ];
        $result = self::create($insertData);
        $mission->still_some--;
        $mission->save();
        return $result;
    }
}