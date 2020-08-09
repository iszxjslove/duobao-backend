<?php


namespace app\common\model;


use think\Model;

class UserMission extends Base
{
    protected $name = 'user_mission';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = false;

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
     * @param $user_id
     * @param Mission $mission
     * @return UserMission
     */
    public static function receive($user_id, Mission $mission): UserMission
    {
        $start_time = time();
        $insertData = [
            'user_id'      => $user_id,
            'mission_id'   => $mission->id,
            'group_name'   => $mission->group_name,
            'mission_name' => $mission->mission_name,
            'method'       => $mission->method,
            'level'        => $mission->level,
            'title'        => $mission->title,
            'desc'         => $mission->desc,
            'times'        => $mission->times,
            'count_times'  => 0,
            'total'        => $mission->total,
            'sum_total'    => 0,
            'bonus'        => $mission->bonus,
            'start_time'   => $start_time,
            'end_time'     => $mission->cycle_time ? $start_time + $mission->cycle_time : 0,
            'status'       => (new self)->getCurrentTableFieldConfig('status.default.value'),
        ];
        $result = self::create($insertData);
        $mission->received_amount++;
        if ($mission->amount_limit) {
            $mission->surplus_amount--;
        }
        $mission->save();
        return $result;
    }
}