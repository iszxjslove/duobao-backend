<?php


namespace app\common\model;


use think\Model;

/**
 * Class UserMission
 * @package app/common/model
 * @property int id 用户任务ID
 * @property int user_id 用户ID
 * @property int mission_id 任务ID
 * @property string group_name 任务分组
 * @property string mission_name 任务名称
 * @property string method 统计方式
 * @property int level 层级
 * @property string title 标题
 * @property string desc 任务描述
 * @property int times 需求次数
 * @property int count_times 累计次数
 * @property int total 需求合计
 * @property int sum_total 累计总数
 * @property string total_field 合计字段
 * @property float bonus 奖金
 * @property int create_time 创建时间
 * @property int start_time 开始时间
 * @property int end_time 结束时间
 * @property string finish_time 完成时间
 * @property int status 状态 0 进行中 1 结束
 */
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